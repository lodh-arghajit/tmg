<?php

namespace Drupal\tmg_utility\Plugin\WebformElement;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\field\FieldConfigInterface;
use Drupal\webform\Element\WebformTermReferenceTrait;
use Drupal\webform\Plugin\WebformElement\WebformTermSelect;
use Drupal\webform\Utility\WebformOptionsHelper;
use Drupal\webform\WebformSubmissionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\webform\Element\WebformTermSelect as TermSelectElement;

/**
 * Provides a 'webform_term_field_value' element.
 *
 * @WebformElement(
 *   id = "webform_term_field_value",
 *   label = @Translation("Term Field Value"),
 *   description = @Translation("Provides a form element to select term field value."),
 *   category = @Translation("Entity reference elements"),
 *   dependencies = {
 *     "taxonomy",
 *   }
 * )
 */
class WebformTermFieldValue extends WebformTermSelect {

  use WebformTermReferenceTrait;

  /**
   * The entity type repository.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * The webform element plugin manager.
   *
   * @var \Drupal\webform\Plugin\WebformElementManagerInterface
   */
  protected $elementManager;

  protected $request;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->entityFieldManager = $container->get('entity_field.manager');
    $instance->elementManager = $container->get('plugin.manager.webform.element');
    $instance->request = \Drupal::request();
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  protected function defineDefaultProperties() {
    $properties = [
      'value_label_field' => '',
    ] + parent::defineDefaultProperties();
    return $properties;
  }


  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $element = $form_state->get('element');

    // Alter element properties.
    if ($form_state->isRebuilding()) {
      // Get entity reference value from user input because
      // $form_state->getValue() does not always contain every input's value.
      $user_input = $form_state->getUserInput();
      $target_type = $user_input['properties']['vocabulary'];

    }
    else {
      $element = $form_state->get('element');

      // Set default #target_type and #selection_handler.
      if (empty($this->getElementProperty($element, 'vocabulary'))) {
        if ($vocabularies = Vocabulary::loadMultiple()) {
          $vocabulary = reset($vocabularies);
          $target_type = $vocabulary->id();
        }
      }
      else {
        $target_type = $this->getElementProperty($element, 'vocabulary');
      }

    }
    $fields = $this->entityFieldManager->getFieldDefinitions("taxonomy_term", $target_type);
    $options = [];
    foreach ($fields as $name => $definition) {
      if ($definition->isComputed()) {
        continue;
      }

      $storage = $definition->getFieldStorageDefinition();
      if ($storage->isMultiple()) {
        continue;
      }
      if ($storage->isMultiple()) {
        continue;
      }
      if ($storage->getType() != "string") {
        continue;
      }
      $options[$name] = $definition->getLabel();
    }
    $form['term_reference']['value_label_field'] = [
      '#type' => 'select',
      '#title' => $this->t('Select Field'),
      '#options' => $options,
    ];

    $ajax_id = 'webform-entity-reference';
    $this->buildAjaxElementWrapper($ajax_id, $form['term_reference']);
    $this->buildAjaxElementUpdate($ajax_id, $form['term_reference']);
    $this->buildAjaxElementTrigger($ajax_id, $form['term_reference']['vocabulary']);
    return $form;
  }


  /****************************************************************************/

  /**
   * {@inheritdoc}
   */
  protected function setOptions(array &$element) {
    $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
    if (!empty($element['#options'])) {
      return;
    }
    if (!\Drupal::moduleHandler()->moduleExists('taxonomy') || empty($element['#vocabulary'])) {
      $element['#options'] = [];
      return;
    }
    $element['#options'] = static::getOptionsTree($element, $language);

    $element_plugin = $this->elementManager->getElementInstance($element);
    $element_key = $element_plugin->getKey($element);
    if ($this->request->query->has($element_key)) {
      $value[] = $this->request->query->get($element_key);
      $option_values = WebformOptionsHelper::validateOptionValues($element['#options'], $value);
      if (!empty($option_values)) {
        $element['#default_value'] = $this->request->query->get($element_key);
      }
    }
    // Add the vocabulary to the cache tags.
    // Issue #2920913: The taxonomy_term_list cache should be invalidated
    // on a vocabulary-by-vocabulary basis.
    // @see https://www.drupal.org/project/drupal/issues/2920913
    $element['#cache']['tags'][] = 'taxonomy_term_list';
  }

  /**
   * Get options to term tree.
   *
   * @param array $element
   *   The term reference element.
   * @param string $language
   *   The language to be displayed.
   *
   * @return array
   *   An associative array of term options formatted as a tree.
   */
  protected static function getOptionsTree(array $element, $language) {
    $element += ['#tree_delimiter' => '-'];

    /** @var \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository */
    $entity_repository = \Drupal::service('entity.repository');
    $tree = static::loadTree($element['#vocabulary']);
    $field = $element['#value_label_field'];
    $options[""] = "--None--";
    foreach ($tree as $item) {
      // Set the item in the correct language for display.
      $item = $entity_repository->getTranslationFromContext($item);
      if (!$item->access('view')) {
        continue;
      }
      $value = $field && $item->get($field)->first() ? $item->get($field)->first()->value : "";
      if (!empty($value) && !in_array($value, $options)) {
        $options[$value] = $value;
      }
    }
    
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function getTargetEntities(array $element, WebformSubmissionInterface $webform_submission, array $options = []) {
    $value = $this->getValue($element, $webform_submission, $options);
    if (empty($value)) {
      return [];
    }

    if (!is_array($value)) {
      $value = [$value];
    }

    $target_type = $this->getTargetType($element);

    $bundle = $element['#vocabulary'];
    $field = $element['#value_label_field'];
    $query = \Drupal::entityQuery($target_type);
    $query->condition('vid', $bundle);

    $query->condition($field, $value, "IN");

    $ids = $query->execute();
    if (empty($ids)) {
      return [];
    }

    $entities = $this->entityTypeManager->getStorage($target_type)->loadMultiple($ids);

    foreach ($entities as $entity_id => $entity) {
      // Set the entity in the correct language for display.
      $entities[$entity_id] = $this->entityRepository->getTranslationFromContext($entity);
    }
    return $entities;
  }

}

<?php

namespace Drupal\tmg_utility\Plugin\EntityReferenceSelection;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\views\Plugin\EntityReferenceSelection\ViewsSelection;
use Drupal\views\Views;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\Xss;
use Drupal\views\Render\ViewsRenderPipelineMarkup;

/**
 * Plugin implementation of the 'selection' entity_reference.
 *
 * @EntityReferenceSelection(
 *   id = "tmg_utility",
 *   label = @Translation("Views: Filter by an entity reference view modified"),
 *   group = "tmg_utility",
 *   weight = 0
 * )
 */
class ViewsSelectionModified extends ViewsSelection {

  /**
   * Constructs a new ViewsSelection object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, ModuleHandlerInterface $module_handler, AccountInterface $current_user, RendererInterface $renderer) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager, $module_handler, $current_user, $renderer);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('module_handler'),
      $container->get('current_user'),
      $container->get('renderer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'entity_field' => '',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $default = $this->getConfiguration()['entity_field'];
    $options = ['id' => 'id', 'label' => 'label'];
    $form['entity_field'] = [
      '#type' => 'select',
      '#title' => $this->t('Choose option value source field for select list'),
      '#required' => TRUE,
      '#options' => $options,
      '#default_value' => $default,
      '#description' => '<p>' . $this->t('Choose option value source field for select list') . '</p>',
    ];

    return $form;
  }


  /**
   * Strips all admin and anchor tags from a result list.
   *
   * These results are usually displayed in an autocomplete field, which is
   * surrounded by anchor tags. Most tags are allowed inside anchor tags, except
   * for other anchor tags.
   *
   * @param array $results
   *   The result list.
   *
   * @return array
   *   The provided result list with anchor tags removed.
   */
  protected function stripAdminAndAnchorTagsFromResults(array $results) {
    $allowed_tags = Xss::getAdminTagList();
    if (($key = array_search('a', $allowed_tags)) !== FALSE) {
      unset($allowed_tags[$key]);
    }
    $stripped_results = [];
    $field_name = $this->getConfiguration()['entity_field'];
    foreach ($results as $id => $row) {
      $entity = $row['#row']->_entity;
      $key = $id;
      if ($field_name == "label") {
        $key = $entity->label();
      }
      $stripped_results[$entity->bundle()][$key] = ViewsRenderPipelineMarkup::create(
        Xss::filter($this->renderer->renderPlain($row), $allowed_tags)
      );
    }

    return $stripped_results;
  }
  /**
   * {@inheritdoc}
   */
  public function validateReferenceableEntities(array $ids) {

    $field_name = $this->getConfiguration()['entity_field'];
    // map the label to id
    if ($field_name == "label") {
      $entity_type = $this->getConfiguration()['target_type'];
      $entity_storage = $this->entityTypeManager->getStorage($entity_type);
      $label_property = $entity_storage->getEntityType()->getKey('label');
      $entities = $entity_storage->loadByProperties([$label_property => $ids]);

      if ($entities) {
        return $ids;
      }

      return [];
    }

    $entities = $this->getDisplayExecutionResults(NULL, 'CONTAINS', 0, $ids);
    $result = [];
    if ($entities) {
      $result = array_keys($entities);
    }

    return $result;
  }


}

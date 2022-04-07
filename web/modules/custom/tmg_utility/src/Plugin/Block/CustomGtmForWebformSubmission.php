<?php

namespace Drupal\tmg_utility\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use Drupal\webform\Utility\WebformFormHelper;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Provides a Gtm block for webform submission.
 *
 * @Block(
 *   id = "webform_submission_for_salesforce",
 *   admin_label = @Translation("Webform submission to salesforce"),
 *   category = @Translation("Utility")
 * )
 */
class CustomGtmForWebformSubmission extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The database connection.
   *
   * @var Connection
   */
  protected $connection;

  /**
   * @var Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * @var Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;


  /**
   * Constructs a new CustomStyleBlock instance.
   *
   * @param array $configuration
   *   The plugin configuration, i.e. an array with configuration values keyed
   *   by configuration option name. The special key 'context' may be used to
   *   initialize the defined contexts by setting it to an array of context
   *   values keyed by context names.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param RequestStack $request_stack
   * @param EntityTypeManagerInterface $entity_type_manager
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RequestStack $request_stack, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->request = $request_stack->getCurrentRequest();
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('request_stack'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $data = [];
    $submission_message = $this->configuration['event_name'];
    $form['submission_message'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Initial submission message'),
      '#default_value' => $submission_message ?? '',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->configuration = $values;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $token  = $this->request->get("token");
    $libraries[] = 'tmg_utility/auto_submit_form';
    if (empty($token)) {
      return [
        '#theme' => 'sales_force_submission',
        '#hidden' => [],
        '#url' => "",
        '#cache' => [
          'max-age' => 0,
          'contexts' => [],
          'tags' => [],
        ]
      ];
    }
    $web_form_submissions = $this->entityTypeManager
        ->getStorage('webform_submission')
        ->loadByProperties(['token' => $token]);
    if (empty($web_form_submissions)) {
      return [
        '#theme' => 'sales_force_submission',
        '#hidden' => [],
        '#url' => "",
        '#cache' => [
          'max-age' => 0,
          'contexts' => [],
          'tags' => [],
        ]
      ];
    }

    $web_form_submission = reset($web_form_submissions);
    $web_form = $web_form_submission->getWebform();
    $elements = $web_form->getElementsOriginalDecoded();
    $form["#url"] = $elements["#sales_force_submission_url"];
    $web_form_submission_data = $web_form_submission->getOriginalData();
    $elements = WebformFormHelper::flattenElements($elements);
    $form["#hidden"] = [];

    foreach ($elements as $key => $value) {
      if (empty($value["#sales_force_name"])) {
        continue;
      }
      $form_value = $web_form_submission_data[$key];
      if ($value["#sales_data_type"] == "date") {

        $date = new DrupalDateTime($form_value);

        $form_value = \Drupal::service('date.formatter')->format($date->getTimestamp(), 'custom', 'd/m/Y');
      }

      $form["#hidden"][$value["#sales_force_name"]] = [
        '#name' => $value["#sales_force_name"],
        '#value' => $form_value,
        '#type' => 'hidden',
      ];
    }

    $form["#hidden"]['submit'] = array(
      '#type' => 'submit',
      '#value' => 'Submit',
      '#attributes' => ['class' => ['d-none']]
    );

    return [
      '#theme' => 'sales_force_submission',
      '#hidden' => $form["#hidden"],
      '#url' => $form["#url"],
      '#attached' => array(
        'library' => $libraries,
      ),
      '#cache' => [
        'max-age' => 0,
        'contexts' => [],
        'tags' => [],
      ]
    ];
  }
}

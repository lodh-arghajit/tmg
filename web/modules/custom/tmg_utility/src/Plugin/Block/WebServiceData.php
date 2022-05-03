<?php

namespace Drupal\tmg_utility\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Link;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\Renderer;
use Drupal\Core\Session\AccountProxy;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\file\Entity\File;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'web service data' block.
 *
 * @Block(
 *  id = "web_service_data",
 *  admin_label = @Translation("Web servive data"),
 * )
 */
class WebServiceData extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The form_builder service.
   *
   * @var FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * The current_user service.
   *
   * @var AccountProxy
   */
  protected $currentUser;

  /**
   * The renderer service.
   *
   * @var Renderer
   */
  protected $renderer;

  /**
   * Drupal\Core\KeyValueStore instance.
   *
   * @var KeyValueFactoryInterface
   */
  protected $keyValueFactory;

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface instance.
   *
   * @var EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new WebServiceData.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param FormBuilderInterface $form_builder
   *   The form_builder service.
   * @param AccountProxy $currentUser
   *   The current_user service.
   * @param Renderer $renderer
   *   The renderer service.
   * @param KeyValueFactoryInterface $keyValueFactory
   * Drupal\Core\KeyValueStore\Client instance.
   * @param EntityTypeManagerInterface $entity_type_manager
   * Drupal\Core\Entity\EntityTypeManagerInterface
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, FormBuilderInterface $form_builder, AccountProxy $currentUser, Renderer $renderer, KeyValueFactoryInterface $key_value_expirable_service, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->formBuilder = $form_builder;
    $this->currentUser = $currentUser;
    $this->renderer = $renderer;
    $this->keyValueFactory = $key_value_expirable_service;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * @inheritDoc
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $config = $this->getConfiguration();

    $form['image_type'] = [
      '#type' => 'radios',
      '#title' => $this->t('Image Type.'),
      '#required' => TRUE,
      '#options' => [
        0 => $this->t('Banner'),
        1 => $this->t('Product'),
      ],
      '#default_value' => $config['image_type'] ?? 0,
      '#attributes' => [
        'id' => 'field_block_choice',
      ],
    ];


    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);

    $values = $form_state->getValues();
    $this->configuration['image_type'] = $values['image_type'];

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('form_builder'),
      $container->get('current_user'),
      $container->get('renderer'),
      $container->get('keyvalue'),
      $container->get('entity_type.manager')

    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $news = [];
    $images = [];
    $services = [];
    $key_value_factory_data = $this->keyValueFactory->get('tmg_utility');
    $payload = $key_value_factory_data->get(WEB_SERVICE_DATA);
    $config = \Drupal::config("tmg_utility.web_pull_settings");
    $base_url = $config->get("api_base_url") ?? "";
    $config = $this->getConfiguration();

    if ($payload) {
      $news = $payload["news"];
      $images = $config['image_type'] == "1" ? $payload["marketing_tools"] : $payload["banner"];
    }

    $query = \Drupal::entityQuery('node')
      ->condition('status', 1)
      ->condition('type', 'services')
      ->sort('field_weight', DESC)
      ->range(0, 6);
    $results = $query->execute();
    foreach ($results as $result) {
      $service = $this->entityTypeManager->getStorage('node')->load($result);
      $image_target_id = $service->field_service_image->target_id;
      $file = File::load($image_target_id);
      $image_url = \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri());
      $services[] = ["title" => $service->label(),
        "image_uri" => $image_url,
        "url" => $service->field_more_link->uri,
        "target" => $service->field_more_link[0]->get('options')->getValue()["attributes"]["target"] ?? "_blank",
        ];

    }

    return [
      '#theme' => 'web_service_integration',
      "#remote_url" => $base_url,
      '#news' => $news,
      '#services' => $services,
      '#images' => $images,
      '#cache' => [
        'tags' => [
          'node_list:services',
          'key_value_factory:' . WEB_SERVICE_DATA,
        ]
      ]
    ];
  }

}

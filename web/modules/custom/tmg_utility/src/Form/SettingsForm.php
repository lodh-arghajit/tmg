<?php

namespace Drupal\tmg_utility\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure GD Find a Retailer settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * Constructs a \Drupal\statistics\StatisticsSettingsForm object.
   *
   * @param ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory

  ) {
    parent::__construct($config_factory);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'tmg_web_pull_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['tmg_utility.web_pull_settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['api_base_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Api base URL'),
      '#description' => $this->t('Api base URL without trailing /.'),
      '#default_value' => $this->config('tmg_utility.web_pull_settings')->get('api_base_url'),
    ];

    $form['show_specific_news'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show specific news.'),
      '#default_value' => $this->config('tmg_utility.web_pull_settings')->get('show_specific_news') ?? FALSE,
    ];
    $form['specific_news_ids'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Specific ID of news'),
      '#description' => $this->t('Please enter comma seperated values'),
      '#default_value' => $this->config('tmg_utility.web_pull_settings')->get('specific_news_ids'),
      '#states' => [
        'visible' => [
          ':input[name="show_specific_news"]' => ['checked' => TRUE],
        ],
      ]
    ];

    $form['show_specific_promotional_images'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show specific promotional images.'),
      '#default_value' => $this->config('tmg_utility.web_pull_settings')->get('show_specific_promotional_images') ?? FALSE,
    ];
    $form['specific_promotional_image_ids'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Specific ID of promotional images'),
      '#description' => $this->t('Please enter comma seperated values'),
      '#default_value' => $this->config('tmg_utility.web_pull_settings')->get('specific_promotional_image_ids'),
      '#states' => [
      'visible' => [
        ':input[name="show_specific_promotional_images"]' => ['checked' => TRUE],
       ],
      ]
    ];

    $form['show_specific_marketing_tools'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show specific marketing images.'),
      '#default_value' => $this->config('tmg_utility.web_pull_settings')->get('show_specific_marketing_tools') ?? FALSE,
    ];

    $form['specific_marketing_tool_ids'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Specific ID of marketing'),
      '#description' => $this->t('Please enter comma seperated values'),
      '#default_value' => $this->config('tmg_utility.web_pull_settings')->get('specific_marketing_tool_ids'),
      '#states' => [
        'visible' => [
          ':input[name="show_specific_marketing_tools"]' => ['checked' => TRUE],
        ],
      ]
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('tmg_utility.web_pull_settings')
      ->set('api_base_url', $form_state->getValue('api_base_url'))
      ->set('show_specific_news', $form_state->getValue('show_specific_news') ?? FALSE)
      ->set('specific_news_ids', $form_state->getValue('specific_news_ids'))
      ->set('show_specific_promotional_images', $form_state->getValue('show_specific_promotional_images') ?? FALSE)
      ->set('specific_promotional_image_ids', $form_state->getValue('specific_promotional_image_ids'))
      ->set('show_specific_marketing_tools', $form_state->getValue('show_specific_marketing_tools') ?? FALSE)
      ->set('specific_marketing_tool_ids', $form_state->getValue('specific_marketing_tool_ids'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}

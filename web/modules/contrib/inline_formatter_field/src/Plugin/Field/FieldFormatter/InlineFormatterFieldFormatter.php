<?php

namespace Drupal\inline_formatter_field\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Utility\Token;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Session\AccountProxy;
use Drupal\Core\Config\ConfigFactory;

/**
 * Plugin implementation of the 'html_inject_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "inline_formatter_field_formatter",
 *   label = @Translation("Inline Formatter Field Formatter"),
 *   field_types = {
 *     "inline_formatter_field"
 *   }
 * )
 */
class InlineFormatterFieldFormatter extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * The token object.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Session\AccountProxy
   */
  protected $currentUser;

  /**
   * Drupal LoggerFactory service container.
   *
   * @var Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * Constructs a StringFormatter instance.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Any third party settings settings.
   * @param \Drupal\Core\Utility\Token $token
   *   The token object.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The handle of module objects.
   * @param \Drupal\Core\Session\AccountProxy $current_user
   *   The current user.
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   *   The config factory.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, Token $token, ModuleHandlerInterface $module_handler, AccountProxy $current_user, ConfigFactory $config_factory) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->configFactory = $config_factory;
    $this->token = $token;
    $this->moduleHandler = $module_handler;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('token'),
      $container->get('module_handler'),
      $container->get('current_user'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'formatted_field' => '<h1>Hello World!</h1>',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {

    $form = parent::settingsForm($form, $form_state);

    if ($this->currentUser->hasPermission('edit inline formmater field formats')) {
      $config = $this->configFactory->get('inline_formatter_field.settings');
      $form['#attached']['library'][] = 'inline_formatter_field/inline_formatter_display';

      // Add the ace editor settings to drupalsettings.
      $form['#attached']['drupalSettings']['inline_formatter_field']['ace_editor']['setting'] = [
        'ace_source' => $config->get('ace_source'),
        'theme' => $config->get('ace_theme'),
        'mode' => $config->get('ace_mode'),
        'options' => $config->get('extra_options'),
      ];

      $form['#prefix'] = '<div class="AceEditorFormatterSettings">';
      $form['#suffix'] = '</div>';

      $form['ace_options'] = [
        '#type' => 'details',
        '#title' => $this->t("Ace Editor Options"),
      ];
      $form['ace_options']['theme'] = [
        '#type' => 'select',
        '#title' => $this->t('Theme'),
        '#options' => $config->get('available_themes'),
        '#default_value' => $config->get('ace_theme'),
        '#attributes' => [
          'class' => ['ace-theme-select'],
        ],
      ];
      $form['ace_options']['mode'] = [
        '#type' => 'select',
        '#title' => $this->t('Mode'),
        '#options' => $config->get('available_modes'),
        '#default_value' => $config->get('ace_mode'),
        '#attributes' => [
          'class' => ['ace-mode-select'],
        ],
      ];
      $form['ace_options']['extra'] = [
        '#type' => 'fieldset',
        '#title' => $this->t("Extra Options"),
      ];
      $form['ace_options']['extra']['help'] = [
        '#markup' => $this->t('Options can be found on <a href=":url">Configuring Ace</a>.<br>Booleans should be entered "true" for true and "false" for false.', [
          ':url' => 'https://github.com/ajaxorg/ace/wiki/Configuring-Ace',
        ]),
      ];
      foreach ($config->get('extra_options') as $option => $value) {
        $form['ace_options']['extra'][$option] = [
          '#type' => 'textfield',
          '#title' => $option,
          '#default_value' => $value,
          '#attributes' => [
            'class' => ['ace-option-field'],
            'data-option-name' => $option,
          ],
        ];
      }

      $form['formatted_field'] = [
        '#title' => $this->t('HTML or Twig Format'),
        '#type' => 'textarea',
        '#default_value' => $this->getSetting('formatted_field'),
        '#description' => $this->t("Enter any HTML or Twig here along with token patterns.<br>Use 'opt + tab' and 'opt + shift + tab' to navigate through and back through the editor."),
        '#attributes' => [
          'class' => ['AceEditorTextarea'],
        ],
      ];

      $form['ace_editor'] = [
        '#type' => 'inline_template',
        '#template' => "<div class='EditorWrapper'><div id='AceEditor'></div><button class='fa fa-window-maximize ButtonSize'></button></div>",
      ];

      if ($this->moduleHandler->moduleExists('token')) {
        $form['token_help'] = [
          '#theme' => 'token_tree_link',
          '#token_types' => 'all',
          '#global_types' => FALSE,
          '#dialog' => TRUE,
        ];
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $summary_format = $this->getSetting('formatted_field');
    $format_arr = array_slice(explode(PHP_EOL, $summary_format), 0, 6);

    foreach ($format_arr as $key => $line) {
      if ($key < 5) {
        $summary[] = strlen($line) > 50 ? substr($line, 0, 50) . "..." : $line;
      }
      else {
        $summary[] = "...";
      }
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];
    $settings = $this->getSettings();
    $formatted_field = $settings['formatted_field'];

    foreach ($items as $delta => $item) {
      // Get entity.
      $entity = $item->getEntity();

      // If the token module is enabled then do token replacement.
      if ($this->moduleHandler->moduleExists('token')) {
        $token_data = [
          $entity->getEntityTypeId() => $entity,
        ];
        $formatted_field = $this->token->replace($formatted_field, $token_data, ['clear' => FALSE]);
      }

      // Uses the inline template feature from Drupal to be able to use Twig.
      $element[$delta] = [
        '#type' => 'inline_template',
        '#template' => $formatted_field,
        '#context' => [
          $entity->getEntityTypeId() => $entity,
          'current_user' => $this->currentUser,
        ],
      ];
    }

    return $element;
  }

}

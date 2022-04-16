<?php

namespace Drupal\inline_formatter_field\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Creates the settings form for the inline formatter field settings.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'inline_formatter_field.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'inline_formatter_field_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('inline_formatter_field.settings');
    $ace_default = $config->get('ace_source') ? $config->get('ace_source') : 'cdn';
    $fa_default = $config->get('fa_source') ? $config->get('fa_source') : 'cdn';
    $themes_arr = !empty($form_state->getValue('theme_values')) ? $form_state->getValue('theme_values') : ($config->get('available_themes') ? $config->get('available_themes') : ["theme" => "Theme"]);
    $theme_default = !empty($form_state->getValue('ace_theme')) ? $form_state->getValue('ace_theme') : ($config->get('ace_theme') ? $config->get('ace_theme') : $themes_arr[0]);
    $modes_arr = !empty($form_state->getValue('mode_values')) ? $form_state->getValue('mode_values') : ($config->get('available_modes') ? $config->get('available_modes') : ["mode" => "Mode"]);
    $mode_default = !empty($form_state->getValue('ace_mode')) ? $form_state->getValue('ace_mode') : ($config->get('ace_mode') ? $config->get('ace_mode') : $modes_arr[0]);
    $extra_options = !empty($form_state->getValue('extra_options')) ? $form_state->getValue('extra_options') : ($config->get('extra_options') ? $config->get('extra_options') : []);

    // Attache library for settings form styles.
    $form['#attached']['library'][] = 'inline_formatter_field/settings_form';

    // Sources for libraries.
    $form['ace_source'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Ace Editor source'),
      '#description' => $this->t('The source to get the Ace Editor library, a web address including "http://" or "https://", or it needs to be a relative location from Drupal root to the JavaScript file.<br>ex. "https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.3/ace.js" or "/libraries/ace/ace.js"'),
      '#default_value' => $ace_default,
      '#required' => TRUE,
    ];
    $form['fa_source'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Font Awesome source'),
      '#description' => $this->t('The source to get the Font Awesome library, a web address including "http://" or "https://", or it needs to be a relative location from Drupal root to the CSS file.<br>ex. "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" or "/libraries/fontawesome/css/all.min.css"'),
      '#default_value' => $fa_default,
      '#required' => TRUE,
    ];

    // Ace Editor themes.
    $form['ace_theme'] = [
      '#type' => 'value',
      '#value' => $theme_default,
    ];
    $form['themes'] = [
      '#prefix' => '<div id="settings-form-themes">',
      '#suffix' => '</div>',
    ];
    $form['themes']['available_themes'] = [
      '#type' => 'details',
      '#title' => $this->t("Themes - @theme_name (@theme_val)", [
        '@theme_name' => $themes_arr[$theme_default],
        '@theme_val' => $form_state->getValue('theme_table') ? $form_state->getValue('theme_table')[$theme_default]['key'] : $theme_default,
      ]),
    ];
    $form['themes']['available_themes']['theme_values'] = [
      '#type' => 'value',
      '#value' => $themes_arr,
    ];
    $form['themes']['available_themes']['theme_table'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Key'),
        $this->t('Theme'),
        $this->t('Weight'),
        $this->t('Actions'),
      ],
      '#empty' => $this->t('Sorry, There are no themes.'),
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'theme-table-sort-weight',
        ],
      ],
    ];
    foreach ($themes_arr as $key => $theme) {
      $weight = array_search($key, array_keys($themes_arr));
      $form['themes']['available_themes']['theme_table'][$key]['#attributes']['class'][] = 'draggable';
      $form['themes']['available_themes']['theme_table'][$key]['#weight'] = $weight;
      $form['themes']['available_themes']['theme_table'][$key]['key'] = [
        '#type' => 'textfield',
        '#default_value' => $key,
        '#required' => TRUE,
      ];
      $form['themes']['available_themes']['theme_table'][$key]['name'] = [
        '#type' => 'textfield',
        '#default_value' => $theme,
        '#required' => TRUE,
      ];
      $form['themes']['available_themes']['theme_table'][$key]['weight'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Weight for @title', ['@title' => $key]),
        '#title_display' => 'invisible',
        '#default_value' => $weight,
        '#size' => 3,
        '#attributes' => ['class' => ['theme-table-sort-weight']],
      ];
      if ($theme_default !== $key) {
        $form['themes']['available_themes']['theme_table'][$key]['actions'] = [
          '#theme_wrappers' => ['dropbutton_wrapper'],
        ];
        $form['themes']['available_themes']['theme_table'][$key]['actions']['prefix'] = [
          '#markup' => '<ul class="dropbutton">',
          '#weight' => '-999',
        ];
        $form['themes']['available_themes']['theme_table'][$key]['actions']['suffix'] = [
          '#markup' => '</ul>',
          '#weight' => '999',
        ];
        $form['themes']['available_themes']['theme_table'][$key]['actions']['default'] = [
          '#type' => 'submit',
          '#value' => $this->t("Make Default"),
          '#submit' => ['::makeDefaultTheme'],
          '#name' => 'default-theme-' . $key,
          '#prefix' => '<li class="default">',
          "#suffix" => '</li>',
          '#attributes' => [
            'theme_key' => $key,
          ],
          '#ajax' => [
            'wrapper' => 'settings-form-themes',
            'callback' => '::reloadThemeForm',
            'progress' => [
              'type' => 'throbber',
              'message' => NULL,
            ],
          ],
        ];
      }
      if (count($themes_arr) > 1) {
        $form['themes']['available_themes']['theme_table'][$key]['actions']['remove'] = [
          '#type' => 'submit',
          '#value' => $this->t("Remove"),
          '#submit' => ['::removeTheme'],
          '#name' => 'remove-theme-' . $key,
          '#prefix' => $theme_default !== $key ? '<li class="remove">' : '',
          "#suffix" => $theme_default !== $key ? '</li>' : '',
          '#attributes' => [
            'theme_key' => $key,
          ],
          '#ajax' => [
            'wrapper' => 'settings-form-themes',
            'callback' => '::reloadThemeForm',
            'progress' => [
              'type' => 'throbber',
              'message' => NULL,
            ],
          ],
        ];
      }
    }
    $form['themes']['available_themes']['add'] = [
      '#type' => 'submit',
      '#value' => $this->t("Add theme"),
      '#name' => 'add-theme',
      '#submit' => ['::addTheme'],
      '#ajax' => [
        'wrapper' => 'settings-form-themes',
        'callback' => '::reloadThemeForm',
        'progress' => [
          'type' => 'throbber',
          'message' => NULL,
        ],
      ],
    ];

    // Ace Editor modes.
    $form['ace_mode'] = [
      '#type' => 'value',
      '#value' => $mode_default,
    ];
    $form['modes'] = [
      '#prefix' => '<div id="settings-form-modes">',
      '#suffix' => '</div>',
    ];
    $form['modes']['available_modes'] = [
      '#type' => 'details',
      '#title' => $this->t("Modes - @mode_name (@mode_val)", [
        '@mode_name' => $modes_arr[$mode_default],
        '@mode_val' => $form_state->getValue('theme_table') ? $form_state->getValue('mode_table')[$mode_default]['key'] : $mode_default,
      ]),
    ];
    $form['modes']['available_modes']['mode_values'] = [
      '#type' => 'value',
      '#value' => $modes_arr,
    ];
    $form['modes']['available_modes']['mode_table'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Key'),
        $this->t('Mode'),
        $this->t('Weight'),
        $this->t('Actions'),
      ],
      '#empty' => $this->t('Sorry, There are no modes.'),
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'mode-table-sort-weight',
        ],
      ],
    ];
    foreach ($modes_arr as $key => $mode) {
      $weight = array_search($key, array_keys($modes_arr));
      $form['modes']['available_modes']['mode_table'][$key]['#attributes']['class'][] = 'draggable';
      $form['modes']['available_modes']['mode_table'][$key]['#weight'] = $weight;
      $form['modes']['available_modes']['mode_table'][$key]['key'] = [
        '#type' => 'textfield',
        '#default_value' => $key,
        '#required' => TRUE,
      ];
      $form['modes']['available_modes']['mode_table'][$key]['name'] = [
        '#type' => 'textfield',
        '#default_value' => $mode,
        '#required' => TRUE,
      ];
      $form['modes']['available_modes']['mode_table'][$key]['weight'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Weight for @title', ['@title' => $key]),
        '#title_display' => 'invisible',
        '#default_value' => $weight,
        '#size' => 3,
        '#attributes' => ['class' => ['mode-table-sort-weight']],
      ];
      if ($mode_default !== $key) {
        $form['modes']['available_modes']['mode_table'][$key]['actions'] = [
          '#theme_wrappers' => ['dropbutton_wrapper'],
        ];
        $form['modes']['available_modes']['mode_table'][$key]['actions']['prefix'] = [
          '#markup' => '<ul class="dropbutton">',
          '#weight' => '-999',
        ];
        $form['modes']['available_modes']['mode_table'][$key]['actions']['suffix'] = [
          '#markup' => '</ul>',
          '#weight' => '999',
        ];
        $form['modes']['available_modes']['mode_table'][$key]['actions']['default'] = [
          '#type' => 'submit',
          '#value' => $this->t("Make Default"),
          '#submit' => ['::makeDefaultMode'],
          '#name' => 'default-mode-' . $key,
          '#prefix' => '<li class="remove">',
          "#suffix" => '</li>',
          '#attributes' => [
            'mode_key' => $key,
          ],
          '#ajax' => [
            'wrapper' => 'settings-form-modes',
            'callback' => '::reloadModeForm',
            'progress' => [
              'type' => 'throbber',
              'message' => NULL,
            ],
          ],
        ];
      }
      if (count($modes_arr) > 1) {
        $form['modes']['available_modes']['mode_table'][$key]['actions']['remove'] = [
          '#type' => 'submit',
          '#value' => $this->t("Remove"),
          '#submit' => ['::removeMode'],
          '#name' => 'remove-mode-' . $key,
          '#prefix' => $mode_default !== $key ? '<li class="remove">' : '',
          "#suffix" => $mode_default !== $key ? '</li>' : '',
          '#attributes' => [
            'mode_key' => $key,
          ],
          '#ajax' => [
            'wrapper' => 'settings-form-modes',
            'callback' => '::reloadModeForm',
            'progress' => [
              'type' => 'throbber',
              'message' => NULL,
            ],
          ],
        ];
      }
    }
    $form['modes']['available_modes']['add'] = [
      '#type' => 'submit',
      '#value' => $this->t("Add mode"),
      '#name' => 'add-mode',
      '#submit' => ['::addMode'],
      '#ajax' => [
        'wrapper' => 'settings-form-modes',
        'callback' => '::reloadModeForm',
        'progress' => [
          'type' => 'throbber',
          'message' => NULL,
        ],
      ],
    ];

    // Other Aced Editor options.
    $form['extras'] = [
      '#prefix' => '<div id="settings-form-extra">',
      '#suffix' => '</div>',
    ];
    $form['extras']['available_extras'] = [
      '#type' => 'details',
      '#title' => $this->t("Extra Options"),
    ];
    $form['extras']['available_extras']['help'] = [
      '#markup' => $this->t('A list of available options to add can be found on <a href=":url">Configuring Ace</a>.<br>Booleans should be entered "true" for true and "false" for false.', [
        ':url' => 'https://github.com/ajaxorg/ace/wiki/Configuring-Ace',
      ]),
    ];
    $form['extras']['available_extras']['extra_options'] = [
      '#type' => 'value',
      '#value' => $extra_options,
    ];
    $form['extras']['available_extras']['extra_table'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Key'),
        $this->t('Value'),
        $this->t('Weight'),
        $this->t('Remove'),
      ],
      '#empty' => $this->t('There are no extra options.'),
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'extra-table-sort-weight',
        ],
      ],
    ];
    foreach ($extra_options as $key => $value) {
      $weight = array_search($key, array_keys($extra_options));
      $form['extras']['available_extras']['extra_table'][$key]['#attributes']['class'][] = 'draggable';
      $form['extras']['available_extras']['extra_table'][$key]['#weight'] = $weight;
      $form['extras']['available_extras']['extra_table'][$key]['key'] = [
        '#type' => 'textfield',
        '#default_value' => $key,
        '#required' => TRUE,
      ];
      $form['extras']['available_extras']['extra_table'][$key]['value'] = [
        '#type' => 'textfield',
        '#default_value' => $value,
        '#required' => TRUE,
      ];
      $form['extras']['available_extras']['extra_table'][$key]['weight'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Weight for @title', ['@title' => $key]),
        '#title_display' => 'invisible',
        '#default_value' => $weight,
        '#size' => 3,
        '#attributes' => ['class' => ['extra-table-sort-weight']],
      ];
      $form['extras']['available_extras']['extra_table'][$key]['actions']['remove'] = [
        '#type' => 'submit',
        '#value' => $this->t("Remove"),
        '#submit' => ['::removeExtra'],
        '#name' => 'remove-extra-' . $key,
        '#attributes' => [
          'extra_key' => $key,
        ],
        '#ajax' => [
          'wrapper' => 'settings-form-extra',
          'callback' => '::reloadExtraForm',
          'progress' => [
            'type' => 'throbber',
            'message' => NULL,
          ],
        ],
      ];
    }
    $form['extras']['available_extras']['add'] = [
      '#type' => 'submit',
      '#value' => $this->t("Add Extra Option"),
      '#name' => 'add-extra',
      '#submit' => ['::addExtra'],
      '#ajax' => [
        'wrapper' => 'settings-form-extra',
        'callback' => '::reloadExtraForm',
        'progress' => [
          'type' => 'throbber',
          'message' => NULL,
        ],
      ],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * Adds a new theme to the theme values.
   *
   * @param array $form
   *   The settings form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state of the form.
   *
   * @return array
   *   The settings form.
   */
  public function addTheme(array $form, FormStateInterface $form_state) {
    $themes_arr = static::getThemes($form_state);
    $themes_arr['key_' . uniqid()] = 'theme';
    $form_state->setValue('theme_values', $themes_arr);
    $form_state->setRebuild();
    return $form;
  }

  /**
   * Removes a theme set in the triggering element from the form.
   *
   * @param array $form
   *   The settings form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state of the form.
   *
   * @return array
   *   The settings form.
   */
  public function removeTheme(array $form, FormStateInterface $form_state) {
    $themes_arr = static::getThemes($form_state);
    $theme_key = $form_state->getTriggeringElement()['#attributes']['theme_key'];

    unset($themes_arr[$theme_key]);
    $form_state->setValue('theme_values', $themes_arr);

    if ($form_state->getValue('ace_theme') === $theme_key) {
      $form_state->setValue('ace_theme', array_keys($themes_arr)[0]);
    }

    $form_state->setRebuild();
    return $form;
  }

  /**
   * Sets the theme's key as the default theme value.
   *
   * @param array $form
   *   The settings form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state of the form.
   *
   * @return array
   *   The settings form.
   */
  public function makeDefaultTheme(array $form, FormStateInterface $form_state) {
    $theme_key = $form_state->getTriggeringElement()['#attributes']['theme_key'];
    $form_state->setValue('ace_theme', $theme_key);
    $form_state->setRebuild();
    return $form;
  }

  /**
   * Returns the theme part of the settings form.
   *
   * @param array $form
   *   The settings form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state of the form.
   *
   * @return array
   *   The theme part of the settings form.
   */
  public function reloadThemeForm(array $form, FormStateInterface $form_state) {
    $form['themes']['available_themes']['#attributes']['open'] = 'open';
    return $form['themes'];
  }

  /**
   * Adds a new mode to the mode values.
   *
   * @param array $form
   *   The settings form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state of the form.
   *
   * @return array
   *   The settings form.
   */
  public function addMode(array $form, FormStateInterface $form_state) {
    $modes_arr = static::getModes($form_state);
    $modes_arr['key_' . uniqid()] = 'mode';
    $form_state->setValue('mode_values', $modes_arr);
    $form_state->setRebuild();
    return $form;
  }

  /**
   * Removes a mode set in the triggering element from the form.
   *
   * @param array $form
   *   The settings form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state of the form.
   *
   * @return array
   *   The settings form.
   */
  public function removeMode(array $form, FormStateInterface $form_state) {
    $modes_arr = static::getModes($form_state);
    $mode_key = $form_state->getTriggeringElement()['#attributes']['mode_key'];

    unset($modes_arr[$mode_key]);
    $form_state->setValue('mode_values', $modes_arr);

    if ($form_state->getValue('ace_mode') === $mode_key) {
      $form_state->setValue('ace_mode', array_keys($modes_arr)[0]);
    }

    $form_state->setRebuild();
    return $form;
  }

  /**
   * Sets the mode's key as the default mode value.
   *
   * @param array $form
   *   The settings form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state of the form.
   *
   * @return array
   *   The settings form.
   */
  public function makeDefaultMode(array $form, FormStateInterface $form_state) {
    $mode_key = $form_state->getTriggeringElement()['#attributes']['mode_key'];
    $form_state->setValue('ace_mode', $mode_key);
    $form_state->setRebuild();
    return $form;
  }

  /**
   * Returns the mode part of the settings form.
   *
   * @param array $form
   *   The settings form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state of the form.
   *
   * @return array
   *   The mode part of the settings form.
   */
  public function reloadModeForm(array $form, FormStateInterface $form_state) {
    $form['modes']['available_modes']['#attributes']['open'] = 'open';
    return $form['modes'];
  }

  /**
   * Adds a new extra option to the extra option values.
   *
   * @param array $form
   *   The settings form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state of the form.
   *
   * @return array
   *   The settings form.
   */
  public function addExtra(array $form, FormStateInterface $form_state) {
    $extra_options = static::getExtras($form_state);
    $extra_options['key_' . uniqid()] = 'value';
    $form_state->setValue('extra_options', $extra_options);
    $form_state->setRebuild();
    return $form;
  }

  /**
   * Removes an exra option set in the triggering element from the form.
   *
   * @param array $form
   *   The settings form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state of the form.
   *
   * @return array
   *   The settings form.
   */
  public function removeExtra(array $form, FormStateInterface $form_state) {
    $extra_options = static::getExtras($form_state);
    $extra_key = $form_state->getTriggeringElement()['#attributes']['extra_key'];
    unset($extra_options[$extra_key]);
    $form_state->setValue('extra_options', $extra_options);
    $form_state->setRebuild();
    return $form;
  }

  /**
   * Returns the extra options part of the settings form.
   *
   * @param array $form
   *   The settings form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state of the form.
   *
   * @return array
   *   The extra options part of the settings form.
   */
  public function reloadExtraForm(array $form, FormStateInterface $form_state) {
    $form['extras']['available_extras']['#attributes']['open'] = 'open';
    return $form['extras'];
  }

  /**
   * Returns array of key value pairs for themes.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state of the form.
   *
   * @return array
   *   An associate array of available themes.
   */
  public static function getThemes(FormStateInterface $form_state) {
    $themes = [];
    $themes_values = $form_state->getValue('theme_table');
    // Sort by the weight.
    uasort($themes_values, function ($a, $b) {
      return $a['weight'] <=> $b['weight'];
    });
    foreach ($themes_values as $theme) {
      $themes[$theme['key']] = $theme['name'];
    }
    return $themes;
  }

  /**
   * Returns array of key value pairs for modes.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state of the form.
   *
   * @return array
   *   An associative array of available modes.
   */
  public static function getModes(FormStateInterface $form_state) {
    $modes = [];
    $mode_values = $form_state->getValue('mode_table');
    // Sort by the weight.
    uasort($mode_values, function ($a, $b) {
      return $a['weight'] <=> $b['weight'];
    });
    foreach ($mode_values as $mode) {
      $modes[$mode['key']] = $mode['name'];
    }
    return $modes;
  }

  /**
   * Returns array of key value pairs for extra options.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state of the form.
   *
   * @return array
   *   An associative array of available modes.
   */
  public static function getExtras(FormStateInterface $form_state) {
    $options = [];
    $extra_options = $form_state->getValue('extra_table');
    // Sort by the weight.
    uasort($extra_options, function ($a, $b) {
      return $a['weight'] <=> $b['weight'];
    });
    foreach ($extra_options as $option) {
      $options[$option['key']] = $option['value'];
    }
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    $ace_source = $form_state->getValue('ace_source');
    $fa_source = $form_state->getValue('fa_source');

    // Check the ace editor source.
    if (substr($ace_source, 0, 7) !== 'http://' && substr($ace_source, 0, 8) !== 'https://' && !file_exists(DRUPAL_ROOT . $ace_source)) {
      $form_state->setErrorByName('ace_source', $this->t('The Ace Editor source needs to be an web address including "http://" or "https://", or it needs to be a relative location from Drupal root to the JavaScript file.'));
    }
    // Check the font awesome source.
    if (substr($fa_source, 0, 7) !== 'http://' && substr($fa_source, 0, 8) !== 'https://' && !file_exists(DRUPAL_ROOT . $fa_source)) {
      $form_state->setErrorByName('fa_source', $this->t('The Font Awesome source needs to be an web address including "http://" or "https://", or it needs to be a relative location from Drupal root to the CSS file.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $themes = static::getThemes($form_state);
    $modes = static::getModes($form_state);
    $extra_options = static::getExtras($form_state);
    $theme_default = $form_state->getValue('ace_theme');
    $mode_default = $form_state->getValue('ace_mode');

    $this->config('inline_formatter_field.settings')
      ->set('ace_source', $form_state->getValue('ace_source'))
      ->set('fa_source', $form_state->getValue('fa_source'))
      ->set('available_themes', $themes)
      ->set('ace_theme', $form_state->getValue('theme_table')[$theme_default]['key'])
      ->set('available_modes', $modes)
      ->set('ace_mode', $form_state->getValue('mode_table')[$mode_default]['key'])
      ->set('extra_options', $extra_options)
      ->save();
  }

}

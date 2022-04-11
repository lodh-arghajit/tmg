<?php

namespace Drupal\tmg_utility\Plugin\Tamper;

use Drupal\Core\Form\FormStateInterface;
use Drupal\tamper\TamperableItemInterface;
use Drupal\tamper\TamperBase;

/**
 * Plugin implementation for converting text value to boolean value.
 *
 * @Tamper(
 *   id = "convert_boolean_modified",
 *   label = @Translation("Convert to Boolean modified"),
 *   description = @Translation("Convert to boolean modified."),
 *   category = "Text"
 * )
 */
class ConvertBooleanModified extends TamperBase {

  const SETTING_TRUE_TEXT = 'true_value_text';

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $config = parent::defaultConfiguration();
    $config[self::SETTING_TRUE_TEXT] = '';

    return $config;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form[self::SETTING_TRUE_TEXT] = [
      '#type' => 'textfield',
      '#title' => $this->t('The value set to true'),
      '#default_value' => $this->getSetting(self::SETTING_TRUE_TEXT),
      '#description' => $this->t('The value set to true. Please add the value seperated by comma.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    $this->setConfiguration([
      self::SETTING_TRUE_TEXT => $form_state->getValue(self::SETTING_TRUE_TEXT),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function tamper($data, TamperableItemInterface $item = NULL) {
    // Copy field value in case 'pass' is set.
    $truth_value_texts =  explode(",", $this->getSetting(self::SETTING_TRUE_TEXT));
    if (in_array($data, $truth_value_texts)) {
      return TRUE;
    }
    return FALSE;
  }

}

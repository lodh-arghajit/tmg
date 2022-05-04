<?php

namespace Drupal\tmg_utility\Plugin\Tamper;

use Drupal\Core\Form\FormStateInterface;
use Drupal\tamper\TamperableItemInterface;
use Drupal\tamper\TamperBase;

/**
 * Plugin implementation for converting text value to boolean value.
 *
 * @Tamper(
 *   id = "empty_value_replace_with_other_property",
 *   label = @Translation("Empty value replace with other property"),
 *   description = @Translation("Empty value replace with other property."),
 *   category = "Text"
 * )
 */
class EmptyValueReplaceWithOtherProperty extends TamperBase {

  const SETTING_PROPERTY_MAIN = 'property_primary';
  const SETTING_PROPERTY_SECONDARY = 'property_secondary';

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $config = parent::defaultConfiguration();
    $config[self::SETTING_PROPERTY_MAIN] = '';
    $config[self::SETTING_PROPERTY_SECONDARY] = '';

    return $config;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form[self::SETTING_PROPERTY_MAIN] = [
      '#type' => 'textfield',
      '#title' => $this->t('Primary property name'),
      '#default_value' => $this->getSetting(self::SETTING_PROPERTY_MAIN),
      '#description' => $this->t('Value to replace with primary property.'),
    ];
    $form[self::SETTING_PROPERTY_SECONDARY] = [
      '#type' => 'textfield',
      '#title' => $this->t('Secondary property'),
      '#default_value' => $this->getSetting(self::SETTING_PROPERTY_SECONDARY),
      '#description' => $this->t('Value to replace with secondary property.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    $this->setConfiguration([
      self::SETTING_PROPERTY_MAIN => $form_state->getValue(self::SETTING_PROPERTY_MAIN),
      self::SETTING_PROPERTY_SECONDARY => $form_state->getValue(self::SETTING_PROPERTY_SECONDARY),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function tamper($data, TamperableItemInterface $item = NULL) {
    // Copy field value in case 'pass' is set.
    $property_primary =  $item->getSource()[$this->getSetting(self::SETTING_PROPERTY_MAIN)];
    $property_secondary =  $item->getSource()[$this->getSetting(self::SETTING_PROPERTY_SECONDARY)];

    if (empty($data) &&  $property_primary) {
      return $property_primary;
    }
    if (empty($data) &&  $property_secondary) {
      return $property_secondary;
    }
    return $data;
  }

}

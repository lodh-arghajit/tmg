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

  const SETTING_PROPERTY_NAME = 'property_name';

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $config = parent::defaultConfiguration();
    $config[self::SETTING_PROPERTY_NAME] = '';

    return $config;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form[self::SETTING_PROPERTY_NAME] = [
      '#type' => 'textfield',
      '#title' => $this->t('Property name'),
      '#default_value' => $this->getSetting(self::SETTING_PROPERTY_NAME),
      '#description' => $this->t('Value to replace with property.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    $this->setConfiguration([
      self::SETTING_PROPERTY_NAME => $form_state->getValue(self::SETTING_PROPERTY_NAME),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function tamper($data, TamperableItemInterface $item = NULL) {
    // Copy field value in case 'pass' is set.
    $property_value =  $item->getSource()[$this->getSetting(self::SETTING_PROPERTY_NAME)];

    if (empty($data) &&  $property_value) {
      return $property_value;
    }
    return $data;
  }

}

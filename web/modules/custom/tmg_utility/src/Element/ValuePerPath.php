<?php

namespace Drupal\tmg_utility\Element;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Serialization\Yaml;
use Drupal\webform\Element\WebformElementOptions;
use Drupal\webform\Entity\WebformOptions as WebformOptionsEntity;
use Drupal\webform\Utility\WebformElementHelper;
use Drupal\webform\Utility\WebformFormHelper;

/**
 * Provides a 'value_per_path'.
 *
 * @FormElement("value_per_path")
 */
class ValuePerPath extends WebformElementOptions {

  /**
   * @inheritDoc
   */
  public function getInfo() {
    return parent::getInfo();
  }

  /**
   * @inheritDoc
   */
  public static function processWebformElementOptions(&$element, FormStateInterface $form_state, &$complete_form) {
    $element['#tree'] = TRUE;
    $default_value = $element['#value'];

    if (isset($default_value['default'])) {
      unset($default_value['default']);
    }

    $lines = Yaml::encode($default_value);
    $element['custom'] = [
      '#type' => 'webform_codemirror',
      '#mode' => 'yaml',
      '#default_value' => $lines,
    ];


    $element['default_value'] = [
      '#type' => 'textfield',
      '#title' => t('Default Value'),
      '#required' => TRUE,
      '#default_value' => $element['#value']['default'] ?? '',
    ];

    // Add validate callback.
    $element += ['#element_validate' => []];

    array_unshift($element['#element_validate'], [get_called_class(), 'validateWebformElementOptions']);

    if (!empty($element['#states'])) {
      WebformFormHelper::processStates($element, '#wrapper_attributes');
    }

    return $element;
  }



  /**
   * @inheritDoc
   */
  public static function validateWebformElementOptions(&$element, FormStateInterface $form_state, &$complete_form) {
    $custom_values = NestedArray::getValue($form_state->getValues(), $element['custom']['#parents']);

    $custom_values = Yaml::decode($custom_values);

    $default_value = NestedArray::getValue($form_state->getValues(), $element['default_value']['#parents']);

    if (Element::isVisibleElement($element) && $element['#required'] && empty($custom_values)) {

      WebformElementHelper::setRequiredError($element, $form_state);
    }

    if (in_array('default', array_keys($custom_values))) {
      $form_state->setError($element, t('Value "default" is not allowed. Please use the Default Value field below instead.'));
    }

    $custom_values['default'] = $default_value;
    $element['#value'] = $custom_values;

    $form_state->setValueForElement($element, $custom_values);
  }

  /**
   * {@inheritdoc}
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    if ($input && !isset($input['custom'])) {
      return [];
    }
    if (!$input && !isset($element['#default_value'])) {
      return [];
    }
    if ($input === FALSE) {
        if (is_string($element['#default_value'])) {
          return (WebformOptionsEntity::load($element['#default_value'])) ? $element['#default_value'] : [];
        }
        else {
          return $element['#default_value'];
        }
    }
    if (isset($input['custom'])) {
      return $input['custom'];
    }

  }
}

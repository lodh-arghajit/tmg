<?php

namespace Drupal\tmg_utility\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\Select;

/**
 * Provides a webform element for a term select menu.
 *
 * @FormElement("webform_term_field_value")
 */
class WebformTermFieldValue extends Select {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    return [
        '#vocabulary' => '',
        '#tree_delimiter' => '-',
        '#breadcrumb' => FALSE,
        '#breadcrumb_delimiter' => ' › ',
        '#depth' => NULL,
        '#value_label_field' => '',
      ] + parent::getInfo();
  }


  /**
   * {@inheritdoc}
   */
  public static function processSelect(&$element, FormStateInterface $form_state, &$complete_form) {

    $element = parent::processSelect($element, $form_state, $complete_form);

    // Must convert this element['#type'] to a 'select' to prevent
    // "Illegal choice %choice in %name element" validation error.
    // @see \Drupal\Core\Form\FormValidator::performRequiredValidation
    $element['#type'] = 'select';

    return $element;


  }

}

<?php

namespace Drupal\tmg_utility\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Url;
use Drupal\webform\Utility\WebformArrayHelper;
use Drupal\webform\WebformInterface;
use Symfony\Component\HttpFoundation\Response;
use Drupal\tmg_utility\Ajax\Command\AjaxRedirect;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\webform\Utility\WebformFormHelper;
use Drupal\user\Entity\User;


class UserProfileEditForm {

  const MOBILE_NUMBER_VALIDATION = '/^\d{2,3}\d{7}$/';

  const LANDLINE_NUMBER_VALIDATION = '/^\d{1,2}\d{8}$/';

  public static function alter($form, FormStateInterface $form_state) {
    $class = static::class;
    $form['#validate'][] = [$class, 'validateForm'];
    return $form;
  }


  /**
   * {@inheritdoc}
   */
  public static function validateForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValue([]);
    $hp_no = trim($values['field_mobile_no'][0]["value"]);
    $office_number = trim($values['field_landline_no'][0]["value"]);

    if (!empty($hp_no)) {
      if (preg_match(static::MOBILE_NUMBER_VALIDATION, $hp_no)) {
        return;
      }
      $form_state->setErrorByName('field_mobile_no', 'Invalid HP no.');
    }
    if (!empty($office_number)) {
      if (preg_match(static::LANDLINE_NUMBER_VALIDATION, $office_number)) {
        return;
      }
      $form_state->setErrorByName('field_landline_no', 'Invalid office no.');
    }

  }


}

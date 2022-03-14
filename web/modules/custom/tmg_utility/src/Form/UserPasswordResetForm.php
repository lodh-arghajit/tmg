<?php

namespace Drupal\tmg_utility\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Url;
use Drupal\webform\Utility\WebformFormHelper;

/**
 * Form controller for the user password forms.
 *
 * Users followed the link in the email, now they can enter a new password.
 *
 * @internal
 */
class UserPasswordResetForm {

  const FORGET_PASSWORD_STEP = 'password_page';
  const FORGET_PASSWORD_STEP_CONFIRMATION = 'password_confirmation_page';

  /**
   * {@inheritdoc}
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param \Drupal\Core\Session\AccountInterface $user
   *   User requesting reset.
   * @param string $expiration_date
   *   Formatted expiration date for the login link, or NULL if the link does
   *   not expire.
   * @param int $timestamp
   *   The current timestamp.
   * @param string $hash
   *   Login link hash.
   */
  public static function alter(array $form, FormStateInterface $form_state) {
    $class = static::class;
    $form_step = $form_state->get('current_page') ?? static::VERIFICATION_EMAIL_STEP;
    if ($form_step == static::FORGET_PASSWORD_STEP_CONFIRMATION) {
      unset($form['actions']);
      return $form;
    }
    $form['#disable_inline_form_errors_summary'] = TRUE;
    $form['account']['pass'] = [
      '#type' => 'password_confirm',
      '#size' => 25,
      '#required' => TRUE,
    ];
    $form['actions']['wizard_next']['#submit'][] = [$class, 'submitForm'];
    $form['actions']['wizard_prev'] = [];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public static function submitForm(array &$form, FormStateInterface $form_state) {
    $user = $form_state->getFormObject()->getEntity()->getData()['user'];

    $password_to_update = $form_state->getValues()['pass'];

    $user->setPassword($password_to_update);
    $user->save();

  }

}

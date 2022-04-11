<?php

namespace Drupal\tmg_utility\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Url;

/**
 * Form controller for the user password forms.
 *
 * Users followed the link in the email, now they can enter a new password.
 *
 * @internal
 */
class UserPasswordResetFormOverride extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'user_pass_reset_override';
  }

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
  public function buildForm(array $form, FormStateInterface $form_state, AccountInterface $user = NULL, $expiration_date = NULL, $timestamp = NULL, $hash = NULL) {
    $form['account']['pass'] = [
      '#type' => 'password_confirm',
      '#size' => 25,
      '#required' => TRUE,
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#submit' => ['::submitForm',],
    ];
    return $form;
  }
  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $user = \Drupal::entityTypeManager()->getStorage('user')->load(\Drupal::currentUser()->id());
    $validationReport = \Drupal::service('password_policy.validator')->validatePassword(
      $form_state->getValue('pass', ''),
      $user,
      []
    );

    if ($validationReport->isInvalid()) {
      $form_state->setErrorByName('pass', t('The password does not satisfy the password policies.'));
    }

  }
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // This form works by submitting the hash and timestamp to the user.reset
    // route with a 'login' action.
    $user = \Drupal::entityTypeManager()->getStorage('user')->load(\Drupal::currentUser()->id());
    $password_to_update = $form_state->getValues()['pass'];
    $user->setPassword($password_to_update);
    $user->save();
    $form_state->setRedirect("<current>");
    $this->messenger()
      ->addStatus($this->t('Password has been updated successfully.'));
  }

}

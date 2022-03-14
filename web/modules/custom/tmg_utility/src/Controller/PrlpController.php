<?php

namespace Drupal\tmg_utility\Controller;

use Drupal\Core\Form\FormState;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Url;
use Drupal\user\Controller\UserController;
use Drupal\tmg_utility\Form\UserPasswordResetForm;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\encrypt\Entity\EncryptionProfile;

/**
 * Controller routines for prlp routes.
 */
class PrlpController extends UserController {

  /**
   * Override resetPassLogin() to redirect to the configured path.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   * @param string $hash
   *   Login link hash.
   *
   * @return array
   *    Array of page elements to render.
   */
  public function prlpResetPassLogin(Request $request, $hash) {
    try {
      $encryption_profile = EncryptionProfile::load("site_encrypt_decrypt_profile");
      if (!$encryption_profile) {
        throw new AccessDeniedHttpException();
      }
      $user_data = \Drupal::service('encryption')->decrypt($hash, $encryption_profile);
      $user_data = explode(":", $user_data);
      $uid = $user_data[0];

      $timestamp = (int) $user_data[1];
      /** @var \Drupal\user\UserInterface $user */
      $user = $this->userStorage->load($uid);
      // Verify that the user exists and is active.
      if ($user === NULL || !$user->isActive()) {
        // Blocked or invalid user ID, so deny access. The parameters will be in
        // the watchdog's URL for the administrator to check.
        throw new AccessDeniedHttpException();
      }
      // validate the timestamp.
      $timeout = $this->config('user.settings')->get('password_reset_timeout');
      $expiration_time = $timestamp + $timeout;
      $request_time = \Drupal::time()->getCurrentTime();
      if ($request_time > $expiration_time) {
        $this->messenger()->addError($this->t('You have tried to use a one-time login link that has expired. Please request a new one using the form below.'));
        throw new AccessDeniedHttpException();
      }

      $account = $this->currentUser();
      // When processing the one-time login link, we have to make sure that a user
      // isn't already logged in.
      if ($account->isAuthenticated()) {
        user_logout();
      }
      $web_form = \Drupal::entityTypeManager()->getStorage('webform')->load('user_password_reset');
      $values['data']['user'] = $user;
    }
    catch (\Exception $exception) {
    }
    return $web_form->getSubmissionForm($values);
  }

}

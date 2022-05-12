<?php

namespace Drupal\tmg_utility\Controller;

use Drupal\Core\Form\FormState;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\user\Controller\UserController;
use Drupal\tmg_utility\Form\UserPasswordResetForm;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\encrypt\Entity\EncryptionProfile;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Component\Utility\UrlHelper;
use Symfony\Component\HttpFoundation\Response;


/**
 * Controller routines for prlp routes.
 */
class PrlpController extends UserController {

  /**
   * Override resetPassLogin() to redirect to the configured path.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return RedirectResponse
   *    Array of page elements to render.
   */
  public function pageLoadUrl(Request $request) {
    $content = "Page is still loading";

    return new Response($content);
  }

  /**
   * Override resetPassLogin() to redirect to the configured path.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   * @param string $hash
   *   Login link hash.
   *
   * @return RedirectResponse
   *    Array of page elements to render.
   */
  public function prlpAdminAutoLogin(Request $request, $hash) {
    try {
      // Disable page cache.
      \Drupal::service('page_cache_kill_switch')->trigger();
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
      user_login_finalize($user);
      $destination = $this->getDestination($user);
    }
    catch (\Exception $exception) {
      throw new AccessDeniedHttpException();
    }
    return $this->redirect($destination['route_name'], $destination['route_parameters']);
  }

  /**
   * Override resetPassLogin() to redirect to the configured path.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   * @param string $hash
   *   Login link hash.
   *
   * @return RedirectResponse
   *    Array of page elements to render.
   */
  public function prlpEmailActivation(Request $request, $hash) {
    try {
      // Disable page cache.
      \Drupal::service('page_cache_kill_switch')->trigger();
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
      if ($user === NULL) {
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
      $user->activate();
      $user->save();
      user_login_finalize($user);
      $node_id =  $this->config('tmg_utility.web_pull_settings')->get('user_activation_node_id') ?? NULL;
      if (!empty($node_id)) {
        $routeName = 'entity.node.canonical';
        $routeParameters = ['node' => $node_id];
        $url = Url::fromRoute($routeName, $routeParameters, [
          'absolute' => TRUE,
        ]);

        \Drupal::requestStack()->getCurrentRequest()->query->set('destination', '');
        return new RedirectResponse($url->toString());
      }
      $destination = $this->getDestination($user);
    }
    catch (\Exception $exception) {
      throw new AccessDeniedHttpException();
    }
    return $this->redirect($destination['route_name'], $destination['route_parameters']);
  }


  /**
   * Override resetPassLogin() to redirect to the configured path.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   * @param string $hash
   *   Login link hash.
   *
   * @return RedirectResponse
   *    Array of page elements to render.
   */
  public function prlpResetPassLogin(Request $request, $hash) {
    try {
      // Disable page cache.
      \Drupal::service('page_cache_kill_switch')->trigger();
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
      throw new AccessDeniedHttpException();
    }
    return $web_form->getSubmissionForm($values);
  }

  /**
   * Override resetPassLogin() to redirect to the configured path.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   * @param string $hash
   *   Login link hash.
   *
   * @return RedirectResponse
   *    Array of page elements to render.
   */
  public function prlpLogin(Request $request) {
    try {
      // Disable page cache.
      \Drupal::service('page_cache_kill_switch')->trigger();
      $session = $request->getSession();
      $uid = $session->get('login_user_id', 0);
      if (empty($uid)) {
        throw new AccessDeniedHttpException();
      }
      $user = $this->userStorage->load($uid);
      // Verify that the user exists and is active.
      if ($user === NULL || !$user->isActive()) {
        // Blocked or invalid user ID, so deny access. The parameters will be in
        // the watchdog's URL for the administrator to check.
        throw new AccessDeniedHttpException();
      }
      user_login_finalize($user);
      $destination = $this->getDestination($user);
    }
    catch (\Exception $exception) {
      throw new AccessDeniedHttpException();
    }
    return $this->redirect($destination['route_name'], $destination['route_parameters']);
  }

  private function getDestination($account) {
    $destination['route_name'] = "<front>";
    $destination['route_parameters'] = [];
    try {
      $service = \Drupal::service('login_destination.manager');
      $destination_object = $service->findDestination("login", $account);
      $router = \Drupal::service('router');
      $result = $router->match($destination_object->destination_path);
      if ($result) {
        $destination['route_name'] = $result['_route'];
        $destination['route_parameters'] = $result['_raw_variables']->all();
      }
    }
    catch (\Exception $exception) {
    }
    \Drupal::requestStack()->getCurrentRequest()->query->set('destination', '');
    return $destination;
  }

}

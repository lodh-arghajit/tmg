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


class VerificationForm {

  const VERIFICATION_EMAIL_STEP = 'email';
  const VERIFICATION_ACCOUNT_BLOCKED_STEP = 'user_block_message';
  const VERIFICATION_PASSWORD_STEP = 'password_step';
  const EXISTING_CUSTOMER_STEP = 'tmw_customer';
  const NON_EXISTING_CUSTOMER_STEP = 'display_message_for_non_existing_customer';
  const COLLECT_CUSTOMER_INFORMATION_STEP = 'collect_customer_information';
  const CUSTOMER_PROFILE_INFORMATION_STEP = 'customer_profile_confirmation';
  const REGISTRATION_SUCCESS_STEP = 'registration_success';
  const REGISTRATION_USER_NOT_AVAILABLE_INTO_CPC = 'user_not_available_into_cpc';
  const FORGET_PASSWORD_STEP = 'forget_password_step';

  const BRN_ROC = 12;
  const BRN_REGULAR_EXPRESSION = '/^(PS)\/\d{7}\-(A|D|H|K|M|P|T|U|V|W|X)$/';
  const BRN_ROB_1_OR_2  = 11;
  const BRN_ROB_1_REGULAR_EXPRESSION = '/^00\d{7}\-(A|D|H|K|M|P|T|U|V|W|X)$/';
  const BRN_ROB_2_REGULAR_EXPRESSION = '/^(IP|AS|JM|KT|CA|MA|LA|NS|PG|RA|SA|TR)\d{7}\-(A|D|H|K|M|P|T|U|V|W|X)$/';
  const BRN_OTHER_REGULAR_EXPRESSION = '/^[A-Z|0-9][A-Z|0-9|\/]*[A-Z|0-9]$/';
  const WHOLESALE_ID_EXPRESSION = '/^[0-9]{5}$/';

  const MOBILE_NUMBER_VALIDATION = '/^\d{2,3}\d{7}$/';

  const LANDLINE_NUMBER_VALIDATION = '/^\d{1,2}\d{8}$/';

  public static function alter($form, FormStateInterface $form_state) {
    $form['#disable_inline_form_errors_summary'] = TRUE;
    $form_step = $form_state->get('current_page') ?? static::VERIFICATION_EMAIL_STEP;
    $form['#attached']['library'][] = 'tmg_utility/tracking';
    $class = static::class;
    switch ($form_step) {
      case static::VERIFICATION_EMAIL_STEP:
        $form['actions']['wizard_next']['#validate'][] = [$class, 'validateEmail'];
        break;
      case static::VERIFICATION_PASSWORD_STEP:
        // Due to security purpose, password will not be stored into db
        $form['elements']['password_step']['password'] = ['#type' => 'password',
          '#title' => 'Password',
          "#title_display" => 'after',
          "#description" => '<p class="text-right"><a class="btn-fp" href="#">Forgot Password ?</a></p>',
          "#attributes" => ['placeholder' => 'Password'],
        ];
        $form['actions']['wizard_next']['#validate'][] = [$class, 'validatePassword'];
        $submit_handlers = $form['actions']['wizard_next']['#submit'];
        $form['actions']['wizard_next']['#submit'] = [];
        $form['actions']['wizard_next']['#submit'][] = [$class, 'redirectForm'];
        foreach ($submit_handlers as $submit_handler) {
          $form['actions']['wizard_next']['#submit'][] = $submit_handler;
        }
        $elements = &WebformFormHelper::flattenElements($form['elements']);
        $mark_up = $elements['markup_01']['#markup'];
        $elements['markup_01']['#markup'] = str_replace("[mail]", $form_state->getValues()['user_email'], $mark_up);
        break;
      case static::EXISTING_CUSTOMER_STEP:
        $submit_handlers = $form['actions']['wizard_next']['#submit'];
        $form['actions']['wizard_next']['#submit'] = [];
        $form['actions']['wizard_next']['#submit'][] = [$class, 'skipCustomer'];
        foreach ($submit_handlers as $submit_handler) {
          $form['actions']['wizard_next']['#submit'][] = $submit_handler;
        }
        break;
      case static::CUSTOMER_PROFILE_INFORMATION_STEP:
      case static::COLLECT_CUSTOMER_INFORMATION_STEP:
        $submit_handlers = $form['actions']['wizard_next']['#submit'];
        $form['actions']['wizard_next']['#submit'] = [];
        $form['actions']['wizard_next']['#submit'][] = [$class, 'createUser'];
        foreach ($submit_handlers as $submit_handler) {
            $form['actions']['wizard_next']['#submit'][] = $submit_handler;
         }
         $form['actions']['wizard_next']['#validate'][] = [$class, 'validateCustomer'];
         if ($form_step == static::CUSTOMER_PROFILE_INFORMATION_STEP) {
           $elements = &WebformFormHelper::flattenElements($form['elements']);
           $elements['brn_confirm']['#default_value'] = $form_state->getValues()['brn'];
           $elements['wholesale_id_confirm']['#default_value'] = $form_state->getValues()['wholesale_id'];
           $elements['company_name_confirm']['#default_value'] = $form_state->getValues()['company_name'];
           $elements['email_confirm']['#default_value'] = $form_state->getValues()['user_email'];
         }
        break;
      case static::NON_EXISTING_CUSTOMER_STEP:
      case static::REGISTRATION_SUCCESS_STEP:
      case static::VERIFICATION_ACCOUNT_BLOCKED_STEP:
      case static::FORGET_PASSWORD_STEP:
         // there will be no further step after these/'s step
         unset($form['actions']);
         break;
      default:
        break;
    }
    $form['actions']['wizard_prev'] = [];
    return $form;
  }

  public static function createUser(&$form, $form_state) {
    $current_page = $form_state->get('current_page');

    if ($current_page == static::COLLECT_CUSTOMER_INFORMATION_STEP) {
      $form_state->set('current_page', static::REGISTRATION_USER_NOT_AVAILABLE_INTO_CPC);
    }
  }

  public static function skipCustomer(&$form, $form_state) {
    $values = $form_state->getValue([]);
    $existing_customer = trim($values['tmw_customer_element']);
    if ($existing_customer == "yes") {
       $form_state->set('current_page', static::NON_EXISTING_CUSTOMER_STEP);
    }
  }

  public static function redirectForm(&$form, FormStateInterface $form_state) {
    $step = trim($form_state->getValue('step'));
    if ($step === 'forget_password') {
      $email = trim($form_state->getValue('user_email'));
      static::resetPassword($email);
      $form_state->set('current_page', static::REGISTRATION_SUCCESS_STEP);
      return;
    }
    if (empty($uid = $form_state->get('uid'))) {
      return;
    }
    $account = \Drupal::service('entity_type.manager')->getStorage('user')->load($uid);
    user_login_finalize($account);
    $form_state->set('current_page', static::FORGET_PASSWORD_STEP);
  }

  public static function validateEmail(&$form, $form_state) {
    static::validateUserNameEmail($form, $form_state);
  }

  public static function validatePassword(&$form, FormStateInterface $form_state) {
    static::validateAuthentication($form, $form_state);
  }

  public static function validateCustomer(&$form, FormStateInterface $form_state) {
    static::validateWithBackend($form, $form_state);
  }

  /**
   * Function validating username and email is exist or not.
   */
  private static function validateUserNameEmail(&$form, FormStateInterface $form_state) {
    $values = $form_state->getValue([]);
    $email = trim($values['user_email']);
    $user_storage = \Drupal::service('entity_type.manager')->getStorage('user');
      // Try to load by email.
    $users = $user_storage->loadByProperties(['mail' => $email]);
    if (empty($users)) {
      // No success, try to load by name.
      $users = $user_storage->loadByProperties(['name' => $email]);
    }
    $account = reset($users);
    // Flattening the elements makes it much easier to access nested elements.
    $elements = &WebformFormHelper::flattenElements($form['elements']);
    if ($account && $account->id()) {
      // Blocked accounts cannot request a new password.
      if (!$account->isActive()) {
        $form_state->set('current_page', static::VERIFICATION_PASSWORD_STEP);
        return;
      }

    }
    else {
      $form_state->set('current_page', static::VERIFICATION_ACCOUNT_BLOCKED_STEP);
    }
  }

  /**
   * Checks supplied username/password against local users table.
   *
   * If successful, $form_state->get('uid') is set to the matching user ID.
   */
  private static function validateAuthentication(array &$form, FormStateInterface $form_state) {
    $step = trim($form_state->getValue('step'));
    if ($step === 'forget_password') {
       return;
    }
    $password = trim($form_state->getValue('password'));
    if (empty($password)) {
      $form_state->setErrorByName('password', 'Invalid username or password.');
      return;
    }
    $flood_config = \Drupal::config('user.flood');
    $user_flood_control = \Drupal::service('user.flood_control');
    $user_storage = \Drupal::service('entity_type.manager')->getStorage('user');
    $form_object = $form_state->getFormObject();
    $web_form_submission = $form_object->getEntity();
    $email = $web_form_submission->getElementData("user_email") ?? '';
    if (!empty($email) && strlen($password) > 0) {
      // Do not allow any login from the current user's IP if the limit has been
      // reached. Default is 50 failed attempts allowed in one hour. This is
      // independent of the per-user limit to catch attempts from one IP to log
      // in to many different user accounts.  We have a reasonably high limit
      // since there may be only one apparent IP for all users at an institution.
      if (!$user_flood_control->isAllowed('user.failed_login_ip', $flood_config->get('ip_limit'), $flood_config->get('ip_window'))) {
        $form_state->set('flood_control_triggered', 'ip');
        return;
      }
      $users = $user_storage->loadByProperties(['mail' => $email]);
      if (empty($users)) {
        // No success, try to load by name.
        $users = $user_storage->loadByProperties(['name' => $email]);
      }
      $account = reset($users);
      if ($account) {
        if ($flood_config->get('uid_only')) {
          // Register flood events based on the uid only, so they apply for any
          // IP address. This is the most secure option.
          $identifier = $account->id();
        }
        else {
          // The default identifier is a combination of uid and IP address. This
          // is less secure but more resistant to denial-of-service attacks that
          // could lock out all users with public user names.
          $identifier = $account->id() . '-' . \Drupal::request()->getClientIP();
        }
        $form_state->set('flood_control_user_identifier', $identifier);

        // Don't allow login if the limit for this user has been reached.
        // Default is to allow 5 failed attempts every 6 hours.
        if (!$user_flood_control->isAllowed('user.failed_login_user', $flood_config->get('user_limit'), $flood_config->get('user_window'), $identifier)) {
          $form_state->set('flood_control_triggered', 'user');
          return;
        }
      }
      // We are not limited by flood control, so try to authenticate.
      // Store $uid in form state as a flag for self::validateFinal().
      $user_auth = \Drupal::service('user.auth');
      $user_name = $account->getAccountName();
      $uid = $user_auth->authenticate($user_name, $password);

      if (empty($uid)) {
        $form_state->setErrorByName('password', 'Invalid username or password.');
        return;
      }
      $form_state->set('uid', $uid);

    }
  }

  private static function validateWithBackend(array &$form, FormStateInterface $form_state) {
    $form_step = $form_state->get('current_page') ?? static::VERIFICATION_EMAIL_STEP;
    if ($form_step == static::COLLECT_CUSTOMER_INFORMATION_STEP) {
      $values = $form_state->getValue([]);
      $brn_id = trim($values['brn']);
      $wholesale_id = trim($values['wholesale_id']);
      $company_name = trim($values['company_name']);
      if (!empty($brn_id)) {
        $brn_length = strlen($brn_id);
        if ($brn_length == static::BRN_ROC && preg_match(static::BRN_REGULAR_EXPRESSION, $brn_id) ) {
          return;
        }
        if ($brn_length == static::BRN_ROB_1_OR_2 && (preg_match(static::BRN_ROB_1_REGULAR_EXPRESSION, $brn_id) ||  preg_match(static::BRN_ROB_2_REGULAR_EXPRESSION, $brn_id))) {
          return;
        }
        if (preg_match(static::BRN_OTHER_REGULAR_EXPRESSION, $brn_id)) {
          return;
        }
        $form_state->setErrorByName('brn', 'Invalid BRN id.');
      }
      if (!empty($wholesale_id)) {
        if (preg_match(static::WHOLESALE_ID_EXPRESSION, $wholesale_id)) {
          return;
        }
        $form_state->setErrorByName('wholesale_id', 'Invalid Wholesale ID.');
      }
    }
    if ($form_step == static::CUSTOMER_PROFILE_INFORMATION_STEP) {
      $values = $form_state->getValue([]);
      $hp_no = trim($values['hp_no']);
      $office_number = trim($values['office_number']);
      if (!empty($hp_no)) {
        if (preg_match(static::MOBILE_NUMBER_VALIDATION, $hp_no)) {
          return;
        }
        $form_state->setErrorByName('hp_no', 'Invalid HP no.');
      }
      if (!empty($office_number)) {
        if (preg_match(static::LANDLINE_NUMBER_VALIDATION, $office_number)) {
          return;
        }
        $form_state->setErrorByName('office_number', 'Invalid office no.');
      }
    }
  }

  private static function resetPassword($email) {
    if (!$email) {
      throw new \UnexpectedValueException("Email can't be empty");
    }
    $user_storage = \Drupal::service('entity_type.manager')->getStorage('user');
    // Try to load by email.
    $users = $user_storage->loadByProperties(['mail' => $email]);
    if (empty($users)) {
      // No success, try to load by name.
      $users = $user_storage->loadByProperties(['name' => $email]);
    }
    $account = reset($users);
    if (!$account) {
      throw new \UnexpectedValueException("Account can't be empty");
    }
    _user_mail_notify('password_reset', $account);
  }

}

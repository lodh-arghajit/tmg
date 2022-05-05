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
         if ($form_step == static::COLLECT_CUSTOMER_INFORMATION_STEP) {
           $elements = &WebformFormHelper::flattenElements($form['elements']);
           $elements['brn']['#attributes']['class'][] = 'search-element';
           $elements['wholesale_id']['#attributes']['class'][] = 'search-element';
           $elements['company_name']['#attributes']['class'][] = 'search-element';
           $options = [];
           $storage = $form_state->getStorage();
           if ($cpc_nodes = $storage['cpc_nodes']) {
             $options = $cpc_nodes;
           }
           $elements['select_company']['#options'] = $options;
         }
         if ($form_step == static::CUSTOMER_PROFILE_INFORMATION_STEP) {
           $elements = &WebformFormHelper::flattenElements($form['elements']);
           $values = $form_state->getValue([]);

           $company_internal_id = $values['select_company'];
           $node = \Drupal::entityTypeManager()->getStorage('node')->load($company_internal_id);
           $elements['brn_confirm']['#default_value'] = $node->get('field_cpc_id_num')->getString();
           $elements['wholesale_id_confirm']['#default_value'] = $node->get('field_wholesale_id')->getString();
           $elements['company_name_confirm']['#default_value'] = $node->get('field_company_name')->getString();
           $elements['email_confirm']['#default_value'] = $form_state->getValues()['user_email'];
         }
        break;
      case static::NON_EXISTING_CUSTOMER_STEP:
      case static::REGISTRATION_SUCCESS_STEP:
      case static::VERIFICATION_ACCOUNT_BLOCKED_STEP:
      case static::FORGET_PASSWORD_STEP:
      case static::REGISTRATION_USER_NOT_AVAILABLE_INTO_CPC:
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
      $values = $form_state->getValue([]);
      $storage = $form_state->getStorage();
      $cpc_nodes = $storage['cpc_nodes'];
      $select_company = $values['select_company'];
      if (!empty($cpc_nodes) ) {
        if (empty($select_company)) {
          $form_state->set('current_page', static::NON_EXISTING_CUSTOMER_STEP);
          return;
        }
        $form_state->set('current_page', static::REGISTRATION_USER_NOT_AVAILABLE_INTO_CPC);
      }
    }
    if ($current_page == static::CUSTOMER_PROFILE_INFORMATION_STEP) {
      $form_object = $form_state->getFormObject();
      $web_form_submission = $form_object->getEntity();
      $email = $web_form_submission->getElementData("user_email") ?? '';
      $web_form = $web_form_submission->getWebform();
      $elements = $web_form->getElementsOriginalDecoded();
      $elements = &WebformFormHelper::flattenElements($elements);
      $values = $form_state->getValue([]);
      $user_data_save = [];
      foreach ($elements as $key => $value) {
        if (empty($value["#user_field_name"])) {
          continue;
        }
        $submission_value = trim($values[$key]);
        if (str_contains($value["#user_field_name"], ":") === TRUE) {
          $fields = explode(":", $value["#user_field_name"]);
          $user_data_save[$fields[0]] = [$fields[1] => $submission_value];
        }
        else {
          $user_data_save[$value["#user_field_name"]] = $submission_value;
        }

      }
      if (!empty($user_data_save)) {
        $user_data_save['status'] = 0;
        $user_data_save['field_admin_approved'] = 0;
        $user_data_save['name'] = $user_data_save['mail'];
        $user_data_save['roles'][]["target_id"] = "tm_user";
        $user = User::create($user_data_save);
        $user->save();
      }
      // send mail to approver users
      $ids = \Drupal::entityQuery('user')
        ->condition('status', 1)
        ->condition('roles', 'approver')
        ->execute();
      $admin_users = User::loadMultiple($ids);
      foreach($admin_users as $admin_user){
        $params['account'] = $user;
        $params['admin_user'] = $admin_user;
        \Drupal::service('plugin.manager.mail')->mail('tmg_utility', 'register_pending_approval_admin', $admin_user->getEmail(), \Drupal::languageManager()->getDefaultLanguage()->getId(), $params);
      }
      // end
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
    $request = \Drupal::request();
    $session = $request->getSession();
    $session->set('login_user_id', $account->id());
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
      $select_company = trim($values['select_company']);
      $storage = $form_state->getStorage();


      if (!empty($brn_id)) {
        $brn_length = strlen($brn_id);
        $brn_id_entered = $storage['brn_id_entered'];
        if (!empty($brn_id_entered) && $brn_id_entered == $brn_id && empty($select_company)) {

          $form_state->setErrorByName('select_company', 'Please select valid company.');
        }

        $data_load = TRUE;
//        if ($brn_length == static::BRN_ROC && preg_match(static::BRN_REGULAR_EXPRESSION, $brn_id) ) {
//          $data_load = TRUE;
//        }
//        if ($brn_length == static::BRN_ROB_1_OR_2 && (preg_match(static::BRN_ROB_1_REGULAR_EXPRESSION, $brn_id) ||  preg_match(static::BRN_ROB_2_REGULAR_EXPRESSION, $brn_id))) {
//          $data_load = TRUE;
//        }
//        if (preg_match(static::BRN_OTHER_REGULAR_EXPRESSION, $brn_id)) {
//          $data_load = TRUE;
//        }
        if ($data_load) {
          $cpc_nodes = static::searchCPCData($brn_id, 'field_cpc_id_num');

          $storage["brn_id_entered"] = $brn_id;
          $storage["cpc_nodes"] = $cpc_nodes;
          $form_state->setStorage($storage);

          return;
        }
        $form_state->setErrorByName('brn', 'Invalid BRN id.');
      }
      if (!empty($wholesale_id)) {


        $wholesale_id_entered = $storage['wholesale_id_entered'];
        if (!empty($wholesale_id_entered) && $wholesale_id_entered == $wholesale_id && empty($select_company)) {

          $form_state->setErrorByName('select_company', 'Please select valid company.');
        }


        if (preg_match(static::WHOLESALE_ID_EXPRESSION, $wholesale_id)) {
          $cpc_nodes = static::searchCPCData($wholesale_id, 'field_wholesale_id');

          $storage["wholesale_id_entered"] = $brn_id;
          $storage["cpc_nodes"] = $cpc_nodes;
          $form_state->setStorage($storage);
          return;
        }
        $form_state->setErrorByName('wholesale_id', 'Invalid Wholesale ID.');
      }
      if (!empty($company_name)) {

        $company_name_entered = $storage['company_name_entered'];
        if (!empty($company_name_entered) && $company_name_entered == $company_name && empty($select_company)) {

          $form_state->setErrorByName('select_company', 'Please select valid company.');
        }
        $cpc_nodes = static::searchCPCData("%$company_name%", 'field_company_name', "LIKE");

        $storage["company_name_entered"] = $company_name;
        $storage["cpc_nodes"] = $cpc_nodes;
        $form_state->setStorage($storage);
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
  public static function searchCPC(array &$form, FormStateInterface $form_state) {
    $elements = &WebformFormHelper::flattenElements($form['elements']);
    return $elements['select_company'];
  }

  private static function searchCPCData($value, $property_to_search, $match_operator = "=") {
    $query = \Drupal::entityTypeManager()->getStorage('node')
      ->getQuery()
      ->condition('status', '1')
      ->condition('field_customer_status', TRUE)
      ->condition('type', 'cpc_data')
      ->condition($property_to_search, $value, $match_operator);
    $entity_ids = $query->execute();
    $company_data = [];
    if (!empty($entity_ids)) {
      $nodes = \Drupal::entityTypeManager()->getStorage('node')->loadMultiple($entity_ids);
      foreach ($nodes as $node) {
        $company_data[$node->id()] = $node->get('field_company_name')->getString() . " (" . $node->get('field_wholesale_id')->getString() . " )";
      }
    }
    return $company_data;

  }



}

<?php

namespace Drupal\otp\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Flood\FloodInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\user\UserDataInterface;
use Drupal\Core\Link;

/**
 * Class OTPVerifyForm.
 *
 * Provides form to verify otp send to the user.
 *
 * @package Drupal\otp\Form
 *
 * @ingroup otp
 */
class OTPVerifyForm extends FormBase {

  /**
   * The token service.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The flood handler.
   *
   * @var \Drupal\Core\Flood\FloodInterface
   */
  protected $flood;

  /**
   * The user data handler.
   *
   * @var \Drupal\user\UserDataInterface
   */
  protected $userData;

  /**
   * Constructs an ExampleForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entityTypeManager.
   * @param \Drupal\Core\Flood\FloodInterface $flood
   *   The flood control handler.
   * @param \Drupal\user\UserDataInterface $userData
   *   The user data handler.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, FloodInterface $flood, UserDataInterface $userData) {
    $this->entityTypeManager = $entityTypeManager;
    $this->flood = $flood;
    $this->userData = $userData;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('entity_type.manager'), $container->get('flood'), $container->get('user.data'));
  }

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'otp_verify_form';
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $destination = isset($values['destination']) ? $values['destination'] : '';
    if (trim($values['user_id']) == '') {
      $user = $this->entityTypeManager->getStorage('user')->loadByProperties(['mail' => $values['user_email']]);
    }
    else {
      $user = $this->entityTypeManager->getStorage('user')->load($values['user_id']);
    }
    $user->status = TRUE;
    $user->save();
    $this->userData->delete('otp', $user->id(), 'otp_user_register_random_otp');
    $this->userData->delete('otp', $user->id(), 'otp_user_register_random_otp_time');
    if ($this->currentUser()->hasPermission('administer site configuration')) {
      $this->messenger()->addMessage($this->t('User account activated'));
      return;
    }
    user_login_finalize($user);
    unset($_SESSION['otp_user_register_uid']);
    $this->messenger()->addMessage($this->t('Registration successful. You are now logged in.'));
    if ($destination) {
      $form_state->setRedirectUrl(Url::fromUri($destination));
    }
    else {
      $form_state->setRedirect('user.page');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $uid = '';
    $administer_permission = $this->currentUser()->hasPermission('administer site configuration');
    if (!$administer_permission) {
      if (isset($_SESSION['otp_user_register_uid'])) {
        $uid = $_SESSION['otp_user_register_uid'];
      }
      else {
        $query_params = $this->getRouteMatch()->getParameters();
        if ($query_params->get('u')) {
          $user = $this->entityTypeManager->getStorage('user')->loadByProperties(['uuid' => $query_params->get('u')]);
          if ($user) {
            $uid = $user[0];
          }
        }
      }
    }
    $form['user_id'] = [
      '#type' => 'textfield',
      '#title' => 'User id',
      '#default_value' => $uid,
      '#access' => $administer_permission,
    ];

    $form['user_email'] = [
      '#type' => 'textfield',
      '#title' => 'User Email',
      '#access' => $uid ? $administer_permission : TRUE,
    ];
    $form['otp'] = [
      '#name' => 'otp',
      '#type' => 'textfield',
      '#attributes' => [
        'autofocus' => 'autofocus',
      ],
      '#title' => $this->t("Please enter the 6-digit verification code we've just sent to your email address"),
      '#required' => TRUE,
      '#default_value' => '',
      '#size' => 60,
      '#description' => $this->t("If you can't find the code in your main email folder, search your spam/junk/promotions folder",
        [
          '%email' => (isset($_SESSION['otp_user_register_form_state']['values']['mail']) ? $_SESSION['otp_user_register_form_state']['values']['mail'] : 'email'),
        ]
      ),
      '#maxlength' => 15,
    ];
    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Verify'),
    ];
    $form['actions']['resend'] = [
      '#type' => 'submit',
      '#value' => $this->t('Resend verification code'),
      '#limit_validation_errors' => [],
      '#submit' => ['::otpResendOtp'],
    ];
    $form['actions']['cancel'] = [
      '#markup' => Link::createFromRoute($this->t('Cancel'), 'user.register')->toString(),
    ];
    if ($this->getRequest()->get('destination')) {
      $form['destination'] = [
        '#type' => 'hidden',
        '#value' => $this->getRequest()->get('destination'),
      ];
    }
    $form['need_help'] = [
      '#markup' => "<div class='help-block'>Need help? Email, or contact support</div>",
      '#weight' => 1000,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    if ($this->currentUser()->hasPermission('administer site configuration')) {
      if (trim($values['user_id']) == '' && trim($values['user_email']) == '') {
        $form_state->setErrorByName('user_id', $this->t('Please enter user id or user email'));
      }
    }
    if (trim($values['user_id']) == '') {
      $user = $this->entityTypeManager->getStorage('user')->loadByProperties(['mail' => $values['user_email']]);
    }
    else {
      $user = $this->entityTypeManager->getStorage('user')->load($values['user_id']);
    }
    $config = $this->config('otp.settings');
    if (!$this->flood->isAllowed('user_otp_submit', $config->get('user_otp_submit_threshold'), 60 * 60 * $config->get('user_otp_submit_time_window'))) {
      // We allow only 15 submits in 3 hours.
      $form_state->setErrorByName('otp', $this->t("It looks like you have tried to validate otp many times, please wait for few hours and retry"));
      return FALSE;
    }
    $this->flood->register('user_otp_submit', 60 * 60 * $config->get('user_otp_submit_time_window'));

    if ($user->getLastLoginTime()) {
      $form_state->setErrorByName('otp', $this->t("It looks like you've already completed this step. If you're trying to @login_url and/or reset your password.", [
        '@login_url' => Link::createFromRoute('login to your account click here', 'user.login')->toString(),
      ]));
    }

    if ($this->userData->get('otp', $user->id(), 'otp_user_register_random_otp') != md5($values['otp'])) {
      $form_state->setErrorByName('otp', $this->t('You entered an incorrect activation code. Please enter the activation code that we sent to your email %email.', ['%email' => $user->getEmail()]));
    }
    elseif ($this->userData->get('otp', $user->id(), 'otp_user_register_random_otp_time') == NULL || $this->userData->get('otp', $user->id(), 'otp_user_register_random_otp_time') < (time() - 24 * 60 * 60)) {
      $form_state->setErrorByName('otp', $this->t("The activation code you entered has expired. @resend_verification", [
        '@resend_verification' => Link::createFromRoute('Click here to send a new code to your email address.', 'otp.user_register_otp_resend', ['uuid' => $user->uuid()])->toString(),
      ]));
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * Submit handler for resend otp button.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   */
  public function otpResendOtp(array &$form, FormStateInterface $form_state) {
    $user = $this->entityTypeManager->getStorage('user')->load($form['user_id']['#default_value']);
    $otp_send = _otp_generate_otp($user, TRUE);
    if (is_array($otp_send) && $otp_send['email_send']) {
      $this->messenger()->addMessage($this->t('A new verification code has been sent to %email', ['%email' => $user->getEmail()]));
    }
    elseif (!$otp_send) {
      $this->messenger()->addError($this->t("You've reached the maximum number of attempts. Please retry after few hours or contact support."));
    }
    else {
      $this->messenger()->addError($this->t('Some error occurred in sending OTP'));
    }
  }

}

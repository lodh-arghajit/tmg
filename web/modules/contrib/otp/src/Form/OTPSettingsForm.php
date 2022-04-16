<?php

namespace Drupal\otp\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Utility\Token;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class OTPSettingsForm.
 *
 * Provides configuration form for OTP module.
 *
 * @package Drupal\otp\Form
 *
 * @ingroup otp
 */
class OTPSettingsForm extends ConfigFormBase {

  /**
   * The token service.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * Constructs an ExampleForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\Core\Utility\Token $token
   *   The entityTypeManager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, Token $token) {
    parent::__construct($config_factory);
    $this->token = $token;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('config.factory'), $container->get('token'));
  }

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'otp_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['otp.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->configFactory->getEditable('otp.settings')
      ->set('otp_no_of_digits', $values['otp_no_of_digits'])
      ->set('otp_otp_mail_subject', $values['otp_otp_mail_subject'])
      ->set('otp_otp_mail_body', $values['otp_otp_mail_body'])
      ->set('user_otp_generate_threshold', $values['user_otp_generate_threshold'])
      ->set('user_otp_generate_time_window', $values['user_otp_generate_time_window'])
      ->set('user_otp_submit_threshold', $values['user_otp_submit_threshold'])
      ->set('user_otp_submit_time_window', $values['user_otp_submit_time_window'])
      ->save();
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('otp.settings');
    $no_of_digits = $config->get('otp_no_of_digits');
    $form['otp_no_of_digits'] = [
      '#type' => 'select',
      '#options' => [
        1 => 1,
        2 => 2,
        3 => 3,
        4 => 4,
        5 => 5,
        6 => 6,
        7 => 7,
        8 => 8,
        9 => 9,
        10 => 10,
      ],
      '#required' => TRUE,
      '#default_value' => $no_of_digits ? $no_of_digits : 6,
      '#title' => $this->t('Number of digits in OTP'),
      '#description' => $this->t('The otp will be generated of these many numbers'),
    ];

    $otp_generate_threshold = $config->get('user_otp_generate_threshold');
    $form['user_otp_generate_threshold'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#default_value' => $otp_generate_threshold ? $otp_generate_threshold : 4,
      '#title' => $this->t('Number of times one can generate OTP'),
      '#description' => $this->t('The otp can be generated of these many times'),
    ];

    $otp_generate_time_window = $config->get('user_otp_generate_time_window');
    $form['user_otp_generate_time_window'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#default_value' => $otp_generate_time_window ? $otp_generate_time_window : 3,
      '#title' => $this->t('Time period for restricting regeneration of otp'),
      '#description' => $this->t('This is threshold window in hours for requesting OTP, example allow 15 request for 3 hours'),
    ];

    $otp_form_submit_threshold = $config->get('user_otp_submit_threshold');
    $form['user_otp_submit_threshold'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#default_value' => $otp_form_submit_threshold ? $otp_form_submit_threshold : 15,
      '#title' => $this->t('Number of times one can submit verify otp form'),
      '#description' => $this->t('The otp verify form can be submitted these many times'),
    ];

    $otp_form_submit_time_window = $config->get('user_otp_submit_time_window');
    $form['user_otp_submit_time_window'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#default_value' => $otp_form_submit_time_window ? $otp_form_submit_time_window : 1,
      '#title' => $this->t('Time period for restricting submitting verify otp form'),
      '#description' => $this->t('This is threshold window in hours for submitting verify otp, example allow 15 request for 3 hours'),
    ];

    $email_token_help = $this->t('Available variables are: [user:otp], [user:otp_form_url], [site:name], [site:url], [user:name], [user:mail], [site:login-url], [site:url-brief], [user:edit-url].');

    $form['otp_email'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('OTP Email'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
      '#description' => $this->t('Edit the otp email send to the user.') . ' ' . $email_token_help,
    ];

    $config_email_subject = $config->get('otp_otp_mail_subject');
    $form['otp_email']['otp_otp_mail_subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('OTP Email Subject'),
      '#required' => TRUE,
      '#description' => $this->t('Email subject which will be send to the user'),
      '#default_value' => $config_email_subject ? $config_email_subject : 'Your Requested One Time Password',
    ];
    $email_text = _otp_otp_email();
    $config_email_text = $config->get('otp_otp_mail_body');
    $form['otp_email']['otp_otp_mail_body'] = [
      '#type' => 'textarea',
      '#title' => $this->t('OTP Email Body'),
      '#required' => TRUE,
      '#description' => $this->t('This email will be send with [user:otp] which will be replaced with otp value'),
      '#default_value' => $config_email_text ? $config_email_text : $email_text,
      '#rows' => 15,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $tokens = $this->token->scan($values['otp_otp_mail_body']);
    if (!isset($tokens['user']['otp'])) {
      $form_state->setErrorByName('otp_otp_mail_body', $this->t('Email body should have [user:otp] token'));
    }
    parent::validateForm($form, $form_state);
  }

}

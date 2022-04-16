<?php

namespace Drupal\password_eye\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * {@inheritdoc}
 */
class PasswordEyeSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {

    return 'password_eye_settings_form';

  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form = parent::buildForm($form, $form_state);

    $config = $this->config('password_eye.settings');

    $form['form_id_password'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Enter the form id here.'),
      '#description' => $this->t('Please enter the form id(s) by separating it with a comma. Here the default is user_login_form. You can remove and save the form if you do not want to display the password for this form.'),
      '#default_value' => $config->get('password_eye.form_id_password'),
    ];

    return $form;

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $config = $this->config('password_eye.settings');
    $config->set('password_eye.form_id_password', $form_state->getValue('form_id_password'));
    $config->save();
    return parent::submitForm($form, $form_state);

  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {

    return [
      'password_eye.settings',
    ];

  }

}

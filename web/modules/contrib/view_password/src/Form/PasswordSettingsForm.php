<?php

namespace Drupal\view_password\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * {@inheritdoc}
 */
class PasswordSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {

    return 'password_settings_form';

  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form = parent::buildForm($form, $form_state);

    $config = $this->config('view_password.settings');

    $form['form_id_pwd'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Enter the form id here.'),
      '#description' => $this->t('Please enter the form id(s) by separating it with a comma. Here the default is user_login_form. You can remove and save the form if you do not want to display the password for this form.'),
      '#default_value' => $config->get('form_ids'),
    ];

    $form['span_classes'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Enter the form class here.'),
      '#description' => $this->t('Please enter the icon span class(es) by separating it with a space.'),
      '#default_value' => $config->get('span_classes'),
    ];

    return $form;

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('view_password.settings')
      ->set('form_ids', $form_state->getValue('form_id_pwd'))
      ->set('span_classes', $form_state->getValue('span_classes'))
      ->save();
    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'view_password.settings',
    ];
  }

}

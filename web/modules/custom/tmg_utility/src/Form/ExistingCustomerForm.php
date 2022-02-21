<?php

namespace Drupal\tmg_utility\Form;

use Drupal\Core\Ajax\InsertCommand;
use Drupal\Core\Flood\FloodInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\user\UserAuthInterface;
use Drupal\user\UserInterface;
use Drupal\user\UserStorageInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\tmg_utility\Ajax\Command\AjaxRedirect;

/**
 * Provides class extending Base class implementing system configuration forms.
 */
class MultiStepForm extends ConfigFormBase {
  protected $step = 1;

  protected $loginFailed = FALSE;
  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;
  /**
   * The user storage.
   *
   * @var \Drupal\user\UserStorageInterface
   */
  protected $userStorage;
  /**
   * The flood service.
   *
   * @var \Drupal\Core\Flood\FloodInterface
   */
  protected $flood;
  /**
   * The user authentication object.
   *
   * @var \Drupal\user\UserAuthInterface
   */
  protected $userAuth;

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'multi_step_form';
  }

  /**
   * Constructs a new UserPasswordBlock plugin.
   *
   * @param \Drupal\Core\Form\FormBuilderInterface $formBuilder
   *   The form builder.
   * @param \Drupal\user\UserStorageInterface $user_storage
   *   The user storage.
   * @param \Drupal\user\UserAuthInterface $user_auth
   *   The user authentication object.
   * @param \Drupal\Core\Flood\FloodInterface $flood
   *   The flood service.
   */
  public function __construct(FormBuilderInterface $formBuilder, UserStorageInterface $user_storage, UserAuthInterface $user_auth, FloodInterface $flood) {
    $this->userStorage = $user_storage;
    $this->formBuilder = $formBuilder;
    $this->userAuth = $user_auth;
    $this->flood = $flood;

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('form_builder'),
      $container->get('entity_type.manager')->getStorage('user'),
      $container->get('user.auth'),
      $container->get('flood')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $form['email'] = [
          '#type' => 'email',
          '#title' => $this->t('Email'),
          '#required' => TRUE,
    ];
    $button_label = $this->t('Next');
    $form['actions']['submit']['#value'] = $button_label;
    $form['#prefix']  = '<div id="form-wrapper">';
    $form['#suffix']  = '</div>';
    $form['actions']['submit']['#ajax'] = [
          'callback' => [$this, 'loginStep'],
          'wrapper' => 'form-wrapper',
          'effect' => 'fade',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
      $this->validateusernameEmail($form, $form_state);

  }

  /**
   * Function validating username and email is exist or not.
   */
  public function validateusernameEmail($form, FormStateInterface $form_state) {
    $values = $form_state->getValue([]);
    $name_value = trim($values['email']);
    $session = \Drupal::service('session');
    $user_login = $session->set("check_initial_pass", $name_value);
    // Try to load by email.
    $users = $this->userStorage->loadByProperties(['mail' => $name_value]);
    if (empty($users)) {
      // No success, try to load by name.
      $users = $this->userStorage->loadByProperties(['name' => $name_value]);
    }
    $account = reset($users);
    if ($account && $account->id()) {
      // Blocked accounts cannot request a new password.
      if (!$account->isActive()) {
        $form_state->setErrorByName('name', $this->t('%name is blocked or has not been activated yet.', ['%name' => $name_value]));
        $this->loginFailed = TRUE;
      }
    }
    else {
      $this->loginFailed = TRUE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * Ajax callback to load new step.
   *
   * @param array $form
   *   Form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state interface.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Ajax response.
   */
  public function loginStep(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    if ($this->loginFailed) {
      $response->addCommand(new AjaxRedirect('register'));
    }
    else {
      $response->addCommand(new AjaxRedirect('login'));
    }
    return $response;
  }

}

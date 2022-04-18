<?php

namespace Drupal\tmg_utility\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Link;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\Renderer;
use Drupal\Core\Session\AccountProxy;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'LoginFormPopup' block.
 *
 * @Block(
 *  id = "login_form_popup",
 *  admin_label = @Translation("Login Form Popup"),
 * )
 */
class LoginFormPopup extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The form_builder service.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * The current_user service.
   *
   * @var \Drupal\Core\Session\AccountProxy
   */
  protected $currentUser;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * Constructs a new WelcomeUserNameBlock.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form_builder service.
   * @param \Drupal\Core\Session\AccountProxy $currentUser
   *   The current_user service.
   * @param \Drupal\Core\Render\Renderer $renderer
   *   The renderer service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, FormBuilderInterface $form_builder, AccountProxy $currentUser, Renderer $renderer) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->formBuilder = $form_builder;
    $this->currentUser = $currentUser;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('form_builder'),
      $container->get('current_user'),
      $container->get('renderer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {


    $host = \Drupal::request()->getSchemeAndHttpHost();

    $build = [];
    if ($this->currentUser->isAnonymous()) {
      $url = Url::fromUri("$host/form/verification-process");
      $options = ['dialogClass' => 'user_login', 'drupalAutoButtons' =>  FALSE];
      $link_options = [
        'attributes' => [
          'class' => [
            'use-ajax',
            'login-popup-form',
            'url-show-after-page-load',
            'js-hide',
          ],
          'data-dialog-type' => 'bootstrap4_modal',
          'data-dialog-options' => json_encode($options),
        ]
      ];
      $url->setOptions($link_options);
      $link = Link::fromTextAndUrl($this->t('Log in / Register'), $url)->toString();
      $build['login_popup_block']['#markup'] = '<div class="Login-popup-link">' . $link . '</div>';
    }
    else {
      $url = Url::fromUri("$host/user/logout");
      $options = ['dialogClass' => 'user_login', 'drupalAutoButtons' =>  FALSE];
      $link_options = [
        'attributes' => [
          'class' => [
            'login-popup-form',
          ],

        ]
      ];
      $url->setOptions($link_options);
      $link = Link::fromTextAndUrl($this->t('Logout'), $url)->toString();
      $build['login_popup_block']['#markup'] = '<div class="Login-popup-link">' . $link . '</div>';
    }
    $build['login_popup_block']['#attached']['library'][] = 'core/drupal.dialog.ajax';

    return $build;
  }

}

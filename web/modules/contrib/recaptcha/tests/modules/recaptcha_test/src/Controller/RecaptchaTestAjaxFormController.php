<?php

namespace Drupal\recaptcha_test\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

class RecaptchaTestAjaxFormController extends ControllerBase implements ContainerInjectionInterface {

  public function __construct(FormBuilderInterface $form_builder) {
    $this->formBuilder = $form_builder;
  }

  public static function create(ContainerInterface $container){
    return new static(
      $container->get('form_builder')
    );
  }

  public function button() {
    $output = [];

    $output['container'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'recaptcha-test-container',
      ]
    ];

    $url = Url::fromRoute('recaptcha_test.ajax', []);

    $output['container']['ajax_link'] = [
      '#id' => 'load-ajax-form',
      '#type' => 'link',
      '#title' => $this->t('Load Ajax Form'),
      '#url' => $url,
      '#attributes' => [
        'class' => ['use-ajax', 'button', 'secondary', 'btn', 'btn-secondary'],
      ],
    ];

    $output['#attached']['library'][] = 'core/drupal.ajax';

    // @see https://api.drupal.org/api/drupal/core%21core.api.php/group/ajax/8.2.x
    return $output;
  }

  /**
   * Ajax callback returning a form.
   *
   * @return AjaxResponse
   */
  public function ajaxForm() {
    $form = $this->formBuilder->getForm('Drupal\recaptcha_test\Form\RecaptchaTestAjaxForm');

    $ajax = new AjaxResponse();
    $ajax->addCommand(new ReplaceCommand('#recaptcha-test-container', $form));
    return $ajax;
  }

}

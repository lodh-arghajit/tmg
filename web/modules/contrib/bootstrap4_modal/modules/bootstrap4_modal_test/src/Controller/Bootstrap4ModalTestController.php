<?php

namespace Drupal\bootstrap4_modal_test\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Component\Serialization\Json;

class Bootstrap4ModalTestController extends ControllerBase {
    public function render() {
        $content = [];
        $content['markup'] = [
          '#markup' => '<h1>' . $this->t('Bootstrap 4 Modal Test') . '</h1>',
        ];

        $content['link_drupal_dialog_to_bs4_modal'] = ['#type' => 'container'];
        $content['link_drupal_dialog_to_bs4_modal']['title'] = ['#markup' => '<h2>' . $this->t('Sample 1') . '</h2>'];
        $content['link_drupal_dialog_to_bs4_modal']['description'] = ['#markup' => '<p>' . $this->t('Converts drupal dialog to bootstrap 4 modal using hook_link_alter. See bootstrap4_modal_test_link_alter().') . '</p>'];
        $content['link_drupal_dialog_to_bs4_modal']['link'] = Link::fromTextAndUrl(
          $this->t('Drupal dialog to Bootstrap 4 Modal'),
          Url::fromRoute(
            'bootstrap4_modal_test.test_bootstrap4_modal_footer_buttons',
            [],
            [
              'attributes' => [
                'class' => ['use-ajax'],
                'data-dialog-options' => Json::encode(['width' => 400]),
                'data-dialog-type' => 'modal',
              ]
            ]
          )
        )->toRenderable();

        $content['link_with_custom_buttons'] = ['#type' => 'container'];
        $content['link_with_custom_buttons']['title'] = ['#markup' => '<h2>' . $this->t('Sample 2') . '</h2>'];
        $content['link_with_custom_buttons']['description'] = ['#markup' => '<p>' . $this->t('Opens Bootstrap 4 Modal with custom buttons passed thru data-dialog-options.') . '</p>'];
        $content['link_with_custom_buttons']['link'] = Link::fromTextAndUrl(
          $this->t('Bootstrap 4 Modal custom buttons'),
          Url::fromRoute(
            'bootstrap4_modal_test.test_bootstrap4_modal_footer_buttons',
            [],
            [
              'attributes' => [
                'class' => ['use-ajax'],
                'data-dialog-options' => Json::encode([
                  'buttons' => [
                    ['text' => $this->t('Button 1'), 'class' => ['btn btn-primary'], 'attributes' => ['data-dismiss' =>  'modal']],
                    ['text' => $this->t('Button 2')],
                  ],
                ]),
                'data-dialog-type' => 'bootstrap4_modal',
              ]
            ]
          )
        )->toRenderable();

        $content['link_with_auto_buttons'] = ['#type' => 'container'];
        $content['link_with_auto_buttons']['title'] = ['#markup' => '<h2>' . $this->t('Sample 3') . '</h2>'];
        $content['link_with_auto_buttons']['description'] = ['#markup' => '<p>' . $this->t('Opens Bootstrap 4 Modal with buttons from form actions.') . '</p>'];
        $content['link_with_auto_buttons']['link'] = Link::fromTextAndUrl(
          $this->t('Bootstrap 4 Modal auto buttons'),
          Url::fromRoute(
            'bootstrap4_modal_test.test_bootstrap4_modal_footer_buttons',
            [],
            [
              'attributes' => [
                'class' => ['use-ajax'],
                'data-dialog-type' => 'bootstrap4_modal',
              ]
            ]
          )
        )->toRenderable();

        return $content;
    }
}

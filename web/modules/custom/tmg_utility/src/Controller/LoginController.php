<?php

namespace Drupal\prepaypower_utility\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\prepaypower_payment\PaymentMethodInterface;
use Drupal\prepaypower_payment\PaymentStatus\PaymentStatusFailure;
use Drupal\prepaypower_payment\PaymentStatus\PaymentStatusSuccess;
use Drupal\prepaypower_payment\PluginManagerPaymentMethod;
use Drupal\webform\WebformInterface;
use Drupal\webform\WebformSubmissionStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\webform\Entity\Webform;
use Drupal\webform\Entity\WebformSubmission;
use Drupal\Core\Ajax\AjaxResponse;
use Symfony\Component\HttpFoundation\Cookie;
use Drupal\prepaypower_utility\Ajax\Command\ClickToActionCommand;
use Drupal\webform\WebformSubmissionForm;
use Drupal\Component\Utility\Xss;

class SwitchNowController extends ControllerBase {

  /**
   * Request handler for call to action to save the webformsubmission.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Drupal\Core\Ajax\AjaxResponse|\Symfony\Component\HttpFoundation\Response
   */
  public function callToAction(Request $request) {
    $data = $request->get('data') ? json_decode($request->get('data'), true) :  '';
    $display_time = $data["click_to_call_display_time"];
    $campaign_codes = $data["campaign_codes"];
    $referer = $request->headers->get('referer');
    $base_url = Request::createFromGlobals()->getSchemeAndHttpHost();
    $referer_alias = substr($referer, strlen($base_url));
    $campaign_code = $campaign_codes[$referer_alias] ?? $campaign_codes["default"] ;
    $webform_id = 'switch_now';
    $webform = Webform::load($webform_id);
    $gclid = $request->cookies->get("_tracking_id", '');
    $values = [
      'webform_id' => $webform->id(),
      'data' => [
        'formdisplayedraw' => $display_time,
        'formdisplayed' => \Drupal::service("date.formatter")->format($display_time, "tz_date_format"),
        'campaign_code' => $campaign_code,
        'urllandingpage' => $referer,
        'urlsubmitpage' => $request->getUri(),
        'leadoriginid' => 2,
        'holdingfield1' => $data["mobile_no"] ?? '',
        'gclid' => $gclid ? Xss::filter($gclid) : '',
      ],
    ];
    $webform_submission = WebformSubmission::create($values);
    $webform_submission->save();
    $response = new AjaxResponse();
    return $response;
  }

  /**
   * Request handler for tracking the landing page and set the required parameter for call to action anchor tag.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Drupal\Core\Ajax\AjaxResponse|\Symfony\Component\HttpFoundation\Response
   */
  public function tracking(Request $request) {
    $landing_page_url = $request->cookies->get('landing_page_url', FALSE);
    $response = new AjaxResponse();
    $base_url = Request::createFromGlobals()->getSchemeAndHttpHost();
    $values = [];
    if (!$landing_page_url) {
      $webform = \Drupal::entityTypeManager()->getStorage('webform')->load('switch_now');
      $campaign_codes = $webform->getElementsDecoded()["campaign_code"]['#value'];
      $referer = $request->headers->get('referer');
      $referer_alias = substr($referer, strlen($base_url));
      $campaign_code = $campaign_codes[empty($referer_alias) ? '/' : $referer_alias] ?? $campaign_codes['default'] ;

      $values = [
        'landing_page_url' => $request->headers->get('referer'),
        'landing_page_url_time' => \Drupal::service("date.formatter")->format(time(), "tz_date_format"),
        'landing_page_campaign_code' => $campaign_code,
      ];

    }
    $gclid = $request->query->get("gclid", '');
    if(!empty($gclid)) {
      $values["_tracking_id"] = Xss::filter($gclid);
    }
    if (!empty($values)) {
      $this->setCookie($response, $values);
    }
    $current_time = \Drupal::time()->getCurrentTime();
    $webform = \Drupal::entityTypeManager()->getStorage('webform')->load('switch_now');
    $campaign_codes = $webform->getElementsDecoded()["campaign_code"]['#value'];
    $settings['click_to_call_display_time'] = $current_time;
    $settings['click_to_call_display_time_in_tz_format'] = \Drupal::service("date.formatter")->format($current_time, "tz_date_format");
    $settings['base_url'] = $base_url;
    $settings['campaign_codes'] = $campaign_codes;
    $response->addCommand(new ClickToActionCommand($settings));
    return $response;
  }

  /**
   * set the required cookie values.
   *
   * @param \Drupal\Core\Ajax\AjaxResponse|\Symfony\Component\HttpFoundation\Response
   * @param $cookies
   *
   */
  private function setCookie(AjaxResponse $response, $cookies) {
    foreach ((array) $cookies as $key => $value) {
      $cookie = new Cookie($key, $value);
      $response->headers->setCookie($cookie);
    }
  }
}

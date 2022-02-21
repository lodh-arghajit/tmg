/**
 * @file
 * JavaScript behaviors for other elements.
 */

(function ($, Drupal) {

  'use strict';

  var prepaypower;
  if (typeof prepaypower === "undefined") {
    prepaypower = {};
  }
  prepaypower.tracking = {
    getQueryString: function (name) {
      var match = RegExp('[?&]' + name + '=([^&]*)').exec(window.location.search);
      return match && decodeURIComponent(match[1].replace(/\+/g, ' '));
    },
  };

  /**
   *
   * @type {Drupal~behavior}
   *
   */
  Drupal.behaviors.tracking = {
    attach: function (context) {

      let gclid = prepaypower.tracking.getQueryString('gclid');
      let tracking_url = 'switch_now/tracking';
      if (gclid) {
        tracking_url = tracking_url + "?gclid=" + gclid;
      }
      let settings = {
        url: Drupal.url(tracking_url),
      };
      Drupal.ajax(settings).execute();
    }
  };
  $('a[href^="tel:"]').on( "click", function() {
    var $linkElement = $(this);
    var data = $linkElement.attr('click_to_call_attributes');
    var settings = {
      url: Drupal.url('switch_now/call_to_action'),
      submit: {
        js: true,
        data: data,
      }
    };
    Drupal.ajax(settings).execute();
  });
  Drupal.AjaxCommands.prototype.clickToCall = function (ajax, response, status) {
    $('a[href^="tel:"]').each(function() {
      var click_to_call_attributes = JSON.parse(response.click_to_call_attributes);
      var mobile_no = $(this).attr("href").replace("tel:", "");
      click_to_call_attributes.mobile_no = mobile_no;
      console.log(click_to_call_attributes);
      var landing_page = document.referrer;
      if (landing_page == "") {
        landing_page = click_to_call_attributes.base_url
      }
      $(this).attr("click_to_call_attributes", JSON.stringify(click_to_call_attributes));
      $(".webform-submission-switch-now-form").find("input[name=formdisplayed]").val(click_to_call_attributes.click_to_call_display_time_in_tz_format);
      $(".webform-submission-switch-now-form").find("input[name=formdisplayedraw]").val(click_to_call_attributes.click_to_call_display_time);
      $(".webform-submission-switch-now-form").find("input[name=urllandingpage]").val(landing_page);
    });

  };

})(jQuery, Drupal);

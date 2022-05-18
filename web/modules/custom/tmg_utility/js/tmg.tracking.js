/**
 * @file
 * JavaScript behaviors for other elements.
 */

(function ($, Drupal) {

  'use strict';
  Drupal.behaviors.tmg_utility = {
    attach(context) {
      $('.btn-fp', context)
        .once('forget_password')
        .on('click', function() {
          $("input[name=\"step\"]").val('forget_password');
          $('.webform-button--next').trigger('click');
        });
      $('.btn-login', context)
        .once('back_to_login_step')
        .on('click', function() {
          $('.webform-button--previous').trigger('click');
        });
      $('.search-element', context)
        .once('trigger_search')
        .on('change', function() {
          if ($(this).val() == "") {
            return;
          }
          $('.webform-button--next').trigger('click');
        });
    }
  };
  Drupal.AjaxCommands.prototype.AjaxRedirect = function (ajax, response, status) {
    var settings = {
      url: Drupal.url(response.url),
      submit: {
        js: true
      }
    };
    Drupal.ajax(settings).execute();
  };


})(jQuery, Drupal);

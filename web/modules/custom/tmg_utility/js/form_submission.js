/**
 * @file
 * GD GTM behaviors.
 */

(function ($, Drupal, drupalSettings) {
  'use strict';
  Drupal.behaviors.webformRequiredErrorForClientSideValidation = {
    attach: function (context) {

      $(context).find('.url-show-after-page-load').once('js-show').each(function() {
        if ( $(this).hasClass("js-hide")) {
          $(this).removeClass("js-hide");
        }
      });
      setTimeout(function(){
        $(context).find('input.js-webform-telephone-international, input.js-webform-telephone-international-extended').once('webform-telephone-international-limit').each(function () {
          var $telephone = $(this);
          $($telephone).focus(function () {
            if ($(this).val() == "") {
              var $instance = $(this);
              var prefil = "+" + $instance.intlTelInput('getSelectedCountryData').dialCode;
              $instance.val(prefil);
            }
          });
        });
      }, 500);
      $(context).find(':input[data-webform-required-error]').once('webform-required-error').each(function() {
        $(this).attr("required", "required");
        var message = $(this).attr("data-webform-required-error");
        if (message) {
          $(this).attr("data-msg-required", message);
        }
      });
    }
  };
  Drupal.behaviors.auto_submit_form = {
    attach: function (context, settings) {
      $('.auto_submit_form').find('button[type="submit"]').trigger('click');
    }

  };

})(jQuery, Drupal, drupalSettings);

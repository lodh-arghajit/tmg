/**
 * @file
 * GD GTM behaviors.
 */

(function ($, Drupal, drupalSettings) {
  'use strict';
  Drupal.behaviors.webformRequiredErrorForClientSideValidation = {
    attach: function (context) {
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

/**
 * @file
 * GD GTM behaviors.
 */

(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.auto_submit_form = {
    attach: function (context, settings) {
      $('.auto_submit_form').find('button[type="submit"]').trigger('click');
    }

  };

})(jQuery, Drupal, drupalSettings);

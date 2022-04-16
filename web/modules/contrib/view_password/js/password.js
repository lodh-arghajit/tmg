/**
 * @file
 * Contains \Drupal\view_password\password.js.
 */

(function($, Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.pwd = {
    attach(context) {
      var span_classes_custom = drupalSettings.view_password.span_classes || '';

      $('.pwd-see :password', context).after(
        `<button type="button" class="shwpd ${span_classes_custom} eye-close" aria-label="${drupalSettings.view_password.showPasswordLabel}"></button>`
      );
      $('.shwpd', context)
        .once('view_password')
        .on('click', function() {
          // To toggle the images.
          $(this).toggleClass('eye-close eye-open');

          if ($(this).hasClass('eye-open')) {
            $('.eye-open', context)
              .prev(':password')
              .prop('type', 'text');
            $('button.shwpd').attr('aria-label', drupalSettings.view_password.hidePasswordLabel);
          } else if ($(this).hasClass('eye-close')) {
            $('.eye-close', context)
              .prev(':text')
              .prop('type', 'password');
            $('button.shwpd').attr('aria-label', drupalSettings.view_password.showPasswordLabel);
          }
        });
    }
  };
})(jQuery, Drupal, drupalSettings);

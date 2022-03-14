/**
 * @file
 * JavaScript behaviors for other elements.
 */

(function ($, Drupal) {

  'use strict';

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

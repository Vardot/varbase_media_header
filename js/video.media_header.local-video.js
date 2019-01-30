/**
 * @file
 * Behaviors of Varbase Media Header for local video scripts.
 */

(function ($, _, Drupal, drupalSettings) {
  "use strict";

  Drupal.behaviors.varbaseMediaHeader_local_video = {
    attach: function (context, settings) {

      // Play local video on load of the page.
      $(".vmh-background video").trigger('play');
 
    }
  }

})(window.jQuery, window._, window.Drupal, window.drupalSettings);

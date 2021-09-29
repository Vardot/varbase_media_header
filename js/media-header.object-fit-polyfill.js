/**
 * @file
 * Behaviors Varbase Media Header Object-Fit Polyfill fix scripts.
 */

(function ($, _, Drupal, objectFitPolyfill) {
  // Configure Object-Fit Polyfill behaviors for Varbase Media Header.
  Drupal.behaviors.VarbaseMediaHeaderObjectFitPolyfill = {
    attach: function (context) {
      objectFitPolyfill($(".vmh-background img.bg", context));
      objectFitPolyfill($(".vmh-background picture.bg", context));
      objectFitPolyfill($(".vmh-background video.bg", context));
    }
  };
})(window.jQuery, window._, window.Drupal, window.objectFitPolyfill);

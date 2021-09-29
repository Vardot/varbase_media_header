/**
 * @file
 * Behaviors of Varbase Media Header for vimeo embeded videos scripts.
 */

(function ($, _, Drupal) {
  Drupal.behaviors.varbaseMediaHeader_vimeo = {
    attach: function (context) {
      if (context === window.document) {
        $(document).ready(function () {
          if (
            $(".vmh-background").find(
              '.media--type-remote-video iframe[src*="vimeo.com"]'
            ).length > 0
          ) {
            const closestVimeoIframe = $(".vmh-background")
              .find('.media--type-remote-video iframe[src*="vimeo.com"]')
              .get(0).contentWindow;
            closestVimeoIframe.postMessage("play", "*");
          }
        });
      }
    }
  };
})(window.jQuery, window._, window.Drupal, window.drupalSettings);

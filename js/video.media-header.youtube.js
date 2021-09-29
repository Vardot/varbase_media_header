/**
 * @file
 * Behaviors of Varbase Media Header for Youtube video scripts.
 */

(function ($, _, Drupal) {
  Drupal.behaviors.varbaseMediaHeader_youtube = {
    attach: function (context) {
      if (context === window.document) {
        $(document).ready(function () {
          if (
            $(".vmh-background").find(
              '.media--type-remote-video iframe[src*="youtube.com"]'
            ).length > 0
          ) {
            const closestYoutubeIframe = $(".vmh-background")
              .find('.media--type-remote-video iframe[src*="youtube.com"]')
              .get(0).contentWindow;
            closestYoutubeIframe.postMessage("play", "*");
          }
        });
      }
    }
  };
})(window.jQuery, window._, window.Drupal);

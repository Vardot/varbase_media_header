/**
 * @file
 * Behaviors of Varbase Media Header for Youtube video scripts.
 */

(function varbaseMediaHeaderYouTube($, _, Drupal) {
  Drupal.behaviors.varbaseMediaHeader_youtube = {
    attach(context) {
      if (context === window.document) {
        $(document).ready(function onDocumentReady() {
          if (
            $('.vmh-background').find(
              '.media--type-remote-video iframe[src*="youtube.com"]',
            ).length > 0
          ) {
            const closestYoutubeIframe = $('.vmh-background')
              .find('.media--type-remote-video iframe[src*="youtube.com"]')
              .get(0).contentWindow;
            closestYoutubeIframe.postMessage('play', '*');
          }
        });
      }
    },
  };
})(window.jQuery, window._, window.Drupal);

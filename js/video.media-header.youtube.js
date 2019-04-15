/**
 * @file
 * Behaviors of Varbase Media Header for Youtube video scripts.
 */

(function ($, _, Drupal, drupalSettings) {
  "use strict";

  Drupal.behaviors.varbaseMediaHeader_youtube = {
    attach: function (context, settings) {
      if(context === window.document){
        $(document).ready(function(){
          if ($('.vmh-background').find('.media--type-remote-video iframe[src*="youtube.com"]').length > 0) {
            var closestYoutubeIframe = $('.vmh-background').find('.media--type-remote-video iframe[src*="youtube.com"]').get(0).contentWindow;
            closestYoutubeIframe.postMessage('play', Drupal.url().toAbsolute);
          }
        })
      }
    }
  }

})(window.jQuery, window._, window.Drupal, window.drupalSettings);

/**
 * @file
 * Behaviors of Varbase Media Header for vimeo embeded videos scripts.
 */

(function ($, _, Drupal, drupalSettings) {
  "use strict";

  Drupal.behaviors.varbaseMediaHeader_vimeo = {
    attach: function (context, settings) {
      if(context === window.document){
        if ($('.vmh-background').find('.media--type-remote-video iframe[src*="vimeo.com"]').length > 0) {
          var closestVimeoIframe = $('.vmh-background').find('.media--type-remote-video iframe[src*="vimeo.com"]').get(0).contentWindow;
          closestVimeoIframe.postMessage('play', Drupal.url().toAbsolute);
        }
      }
    }
  }

})(window.jQuery, window._, window.Drupal, window.drupalSettings);

/**
 * @file
 * Behaviors of Varbase Media Header for local video scripts.
 */

(function varbaseMediaHeaderLocalVideo($, _, Drupal) {
  Drupal.behaviors.varbaseMediaHeader_local_video = {
    attach() {
      function onPause() {
        $('.vmh-background video').trigger('play');
      }

      // Play when finished.
      function onFinish() {
        $('.vmh-background video').trigger('play');
      }

      const player = $('.vmh-background video').get(0);
      // Play local video on load of the page.
      if (player) {
        player.play();
        player.onpause = onPause;
        player.onended = onFinish;
      }
    },
  };
})(window.jQuery, window._, window.Drupal);

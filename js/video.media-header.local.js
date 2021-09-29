/**
 * @file
 * Behaviors of Varbase Media Header for local video scripts.
 */

(function ($, _, Drupal) {
  Drupal.behaviors.varbaseMediaHeader_local_video = {
    attach: function () {
      const player = $(".vmh-background video").get(0);
      // Play local video on load of the page.
      if (player) {
        player.play();
        player.onpause = onPause;
        player.onended = onFinish;
      }

      function onPause() {
        $(".vmh-background video").trigger("play");
      }

      // Play when finished.
      function onFinish() {
        $(".vmh-background video").trigger("play");
      }
    }
  };
})(window.jQuery, window._, window.Drupal);

function ready(fn) {
  if (document.readyState != 'loading'){
    fn();
  } else if (document.addEventListener) {
    document.addEventListener('DOMContentLoaded', fn);
  } else {
    document.attachEvent('onreadystatechange', function() {
      if (document.readyState != 'loading')
        fn();
    });
  }
}

var tag = document.createElement('script');
tag.src = "//youtube.com/player_api";
var firstScriptTag = document.getElementsByTagName('script')[0];
firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

ready(function() {

  var media_iframe = document.querySelector('iframe');
  media_iframe.setAttribute('id', 'media-oembed-iframe');

  var player_confgured = false;
  var youtube_player;

  function actionProcessor(evt) {

    // Manage Youtube video.
    if (evt.data === "play") {
      var youtube_iframe = document.querySelector('iframe[src*="youtube.com"]');
      if (youtube_iframe !== undefined && youtube_iframe.src !== undefined) {

        if (!player_confgured) {
          var youtubeURL = String(youtube_iframe.src);
          youtubeURL = youtubeURL.replace(/autoplay=0/gi, "autoplay=1");
          youtubeURL = youtubeURL.replace(/controls=0/gi, "controls=1");
          youtube_iframe.src = youtubeURL;
          youtubeURL = undefined; 

          youtube_player = new YT.Player(youtube_iframe.id, {
            events: {
              'onReady': onPlayerReady,
              'onStateChange': onPlayerStateChange
            }
          });

          function onPlayerReady(event) {
            event.target.playVideo();
          }

          function onPlayerStateChange(event) {

            if (event.data === YT.PlayerState.PLAYING) {
              youtube_player.isPlaying = true;
            }
            else {
              youtube_player.isPlaying = false;
            }
          }

          player_confgured = true;
        }

      }
    }
  }

  // Setup the event listener for messaging.
  if (window.addEventListener) {
    window.addEventListener("message", actionProcessor, false);
  }
  else {
    window.attachEvent("onmessage", actionProcessor);
  }
});

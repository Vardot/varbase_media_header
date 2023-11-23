<?php

/**
 * @file
 * Contains varbase_media_header_update_9###(s) hook updates.
 */

/**
 * Issue #3291994: Change to conditionally.
 *
 * Attache only needed local or remote video libraries.
 */
function varbase_media_header_update_90001() {
  $entity_view_display_media_video = \Drupal::service('config.factory')->getEditable('core.entity_view_display.media.video.varbase_media_header');
  $media_video_autoplay = $entity_view_display_media_video->get('content.field_media_video_file.settings.autoplay');

  if ($media_video_autoplay === FALSE) {
    $entity_view_display_media_video->set('content.field_media_video_file.settings.autoplay', TRUE)->save(TRUE);
  }
}

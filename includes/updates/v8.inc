<?php

/**
 * @file
 * Contains varbase_media_header_update_8###(s) hook updates.
 */

/**
 * Issue #3180926: Add the ability to hide the breadcrumbs from Media Header.
 */
function varbase_media_header_update_87001() {
  // Save the settings value for the hide breadcrumbs.
  \Drupal::configFactory()->getEditable('varbase_media_header.settings')
    ->set('hide_breadcrumbs', FALSE)
    ->save();
}

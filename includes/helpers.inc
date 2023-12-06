<?php

/**
 * @file
 * Contains List of all varbase_media_header helpers.
 *
 * Add custom needed helper functions.
 */

use Drupal\Core\Form\FormStateInterface;

/**
 * Varbase Media Header page form node add/edit.
 *
 * To group fields in add or edit form.
 *
 * @param mixed $form
 *   The form.
 * @param \Drupal\Core\Form\FormStateInterface $form_state
 *   The status of the form.
 * @param string $form_id
 *   The form id.
 */
function varbase_media_header__node_group_form(&$form, FormStateInterface $form_state, $form_id) {

  // Have landing page extra fields in a group in the right-hand column.
  $form['vmh_group'] = [
    '#type' => 'details',
    '#title' => t('Media Header'),
    '#group' => 'advanced',
    '#attributes' => [
      'class' => ['node-form-options'],
    ],
    '#attached' => [
      'library' => ['node/drupal.node'],
    ],
    '#weight' => -10,
    '#optional' => TRUE,
    '#open' => TRUE,
  ];

  if (isset($form['field_page_header_style'])) {
    $form['field_page_header_style']['#group'] = 'vmh_group';
  }

  if (isset($form['field_media'])) {
    $form['field_media']['#group'] = 'vmh_group';
  }

}

/**
 * Varbase Media Header page form taxonomy term add/edit.
 *
 * To group fields in add or edit form.
 *
 * @param mixed $form
 *   The form.
 * @param \Drupal\Core\Form\FormStateInterface $form_state
 *   The status of the form.
 * @param string $form_id
 *   The form id.
 */
function varbase_media_header__taxonomy_term_group_form(&$form, FormStateInterface $form_state, $form_id) {

  // Have landing page extra fields in a group in the right-hand column.
  $form['vmh_group'] = [
    '#type' => 'details',
    '#title' => t('Media Header'),
    '#group' => 'advanced',
    '#attributes' => [
      'class' => ['taxonomy_term-form-options'],
    ],
    '#attached' => [
      'library' => ['taxonomy/drupal.taxonomy'],
    ],
    '#weight' => 1,
    '#optional' => TRUE,
    '#open' => TRUE,
  ];

  if (isset($form['field_page_header_style'])) {
    $form['field_page_header_style']['#group'] = 'vmh_group';
  }

  if (isset($form['field_media'])) {
    $form['field_media']['#group'] = 'vmh_group';
  }

}

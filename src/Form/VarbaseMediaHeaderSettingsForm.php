<?php

namespace Drupal\varbase_media_header\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * VarbaseMediaHeaderSettingsForm Class.
 */
class VarbaseMediaHeaderSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'varbase_media_header_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('varbase_media_header.settings');

    $entity_info = \Drupal::entityManager()->getDefinitions();
    $vmh_settings = $config->get(VARBASE_MEDIA_HEADER_SETTINGS);
    $allowed_entity_types = unserialize(VARBASE_MEDIA_HEADER_ENTITY_TYPES);

    $form[VARBASE_MEDIA_HEADER_SETTINGS] = [
      '#type' => 'fieldset',
      '#title' => t('Select entity types which are going to use the varbase media header'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
      '#tree' => TRUE,
      '#description' => t('Enable varbase media header for these entity types and bundles.'),
    ];

    foreach ($entity_info as $entity_type_key => $entity_type) {
      $bundle_options = [];

      // Skip not allowed entity types.
      if (in_array($entity_type_key, $allowed_entity_types)) {
        $bundles = \Drupal::entityManager()->getBundleInfo($entity_type_key);
        foreach ($bundles as $bundle_key => $bundle) {
          $bundle_options[$bundle_key] = $bundle['label'];
        }

        $form[VARBASE_MEDIA_HEADER_SETTINGS][$entity_type_key] = [
          '#type' => 'checkboxes',
          '#title' => $entity_type->getLabel(),
          '#options' => $bundle_options,
          '#default_value' => !empty($vmh_settings[$entity_type_key]) ?
          array_keys(array_filter($vmh_settings[$entity_type_key])) : [],
        ];
      }

    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('varbase_media_header.settings')
      ->set(VARBASE_MEDIA_HEADER_SETTINGS, $form_state->getValue(VARBASE_MEDIA_HEADER_SETTINGS))
      ->save();

    $this->applyDefaultVarbaseMediaHeaderSettingsForActivatedEntityTypes();

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['varbase_media_header.settings'];
  }

  /**
   * Apply Default Varbase Media Header Settings For Activated Entity Types.
   */
  public function applyDefaultVarbaseMediaHeaderSettingsForActivatedEntityTypes() {
    $vmh_settings = \Drupal::config('varbase_media_header.settings')->get(VARBASE_MEDIA_HEADER_SETTINGS);
    $entity_info = \Drupal::entityManager()->getDefinitions();

    $module_path = \Drupal::service('module_handler')->getModule('varbase_media_header')->getPath();

    foreach ($entity_info as $entity_type_key => $entity_type) {

      // Entity Type Assets Path.
      $entity_type_assets_path = $module_path . '/' . VARBASE_MEDIA_HEADER_ASSETS . '/' . $entity_type_key;

      $bundles = \Drupal::entityManager()->getBundleInfo($entity_type_key);
      foreach ($bundles as $bundle_key => $bundle) {
        if (!empty($vmh_settings[$entity_type_key])
         && isset($vmh_settings[$entity_type_key][$bundle_key])
         && $vmh_settings[$entity_type_key][$bundle_key]) {

          $config_name = "field.field." . $entity_type_key . "." . $bundle_key . ".field_page_header_style";
          if (!(\Drupal::configFactory()->get($config_name) == NULL)) {
            $config_factory = \Drupal::configFactory()->getEditable($config_name);
            $config_entity_type_key_token = "TOKEN_" . strtoupper($entity_type_key);
            $config_template_file_name = "field.field." . $entity_type_key . "." . $config_entity_type_key_token . ".field_page_header_style.yml";
            $config_path = $entity_type_assets_path . '/' . $config_template_file_name;

            if (file_exists($config_path)) {
              $config_content = file_get_contents($config_path);
              $config_content = str_replace($config_entity_type_key_token, $bundle_key, $config_content);
              $config_data = (array) Yaml::parse($config_content);
              $config_factory->setData($config_data)->save(TRUE);
            }
          }

          $config_name = "field.field." . $entity_type_key . "." . $bundle_key . ".field_media";
          if (!(\Drupal::configFactory()->get($config_name) == NULL)) {
            $config_factory = \Drupal::configFactory()->getEditable($config_name);
            $config_entity_type_key_token = "TOKEN_" . strtoupper($entity_type_key);
            $config_template_file_name = "field.field." . $entity_type_key . "." . $config_entity_type_key_token . ".field_media.yml";
            $config_path = $entity_type_assets_path . '/' . $config_template_file_name;

            if (file_exists($config_path)) {
              $config_content = file_get_contents($config_path);
              $config_content = str_replace($config_entity_type_key_token, $bundle_key, $config_content);
              $config_data = (array) Yaml::parse($config_content);
              $config_factory->setData($config_data)->save(TRUE);
            }
          }

          $config_name = "core.entity_form_display." . $entity_type_key . "." . $bundle_key . ".default";
          if (!(\Drupal::configFactory()->get($config_name) == NULL)) {
            $config_factory = \Drupal::configFactory()->getEditable($config_name);
            $config_entity_type_key_token = "TOKEN_" . strtoupper($entity_type_key);
            $config_template_file_name = "core.entity_form_display." . $entity_type_key . "." . $config_entity_type_key_token . ".default.yml";
            $config_path = $entity_type_assets_path . '/' . $config_template_file_name;

            if (file_exists($config_path)) {
              $config_content = file_get_contents($config_path);
              $config_content = str_replace($config_entity_type_key_token, $bundle_key, $config_content);
              $part_config_data = (array) Yaml::parse($config_content);

              $site_config_data = $config_factory->get();
              if (isset($site_config_data['content'])) {
                $site_config_data['content'] = array_merge($site_config_data['content'], $part_config_data);
              }

              $config_factory->setData($site_config_data)->save(TRUE);
            }
          }

          $config_name = "core.entity_view_display." . $entity_type_key . "." . $bundle_key . ".default";
          if (!(\Drupal::configFactory()->get($config_name) == NULL)) {
            $config_factory = \Drupal::configFactory()->getEditable($config_name);
            $config_entity_type_key_token = "TOKEN_" . strtoupper($entity_type_key);
            $config_template_file_name = "core.entity_view_display." . $entity_type_key . "." . $config_entity_type_key_token . ".default.yml";
            $config_path = $entity_type_assets_path . '/' . $config_template_file_name;

            if (file_exists($config_path)) {
              $config_content = file_get_contents($config_path);
              $config_content = str_replace($config_entity_type_key_token, $bundle_key, $config_content);
              $part_config_data = (array) Yaml::parse($config_content);

              $site_config_data = $config_factory->get();
              if (isset($site_config_data['hidden'])) {
                $site_config_data['hidden'] = array_merge($site_config_data['hidden'], $part_config_data);
              }

              $config_factory->setData($site_config_data)->save(TRUE);
            }
          }
        }
      }
    }

    // Flush all caches.
    drupal_flush_all_caches();
  }

}

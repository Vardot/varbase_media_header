<?php

namespace Drupal\varbase_media_header\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\Yaml\Yaml;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * VarbaseMediaHeaderSettingsForm Class.
 */
class VarbaseMediaHeaderSettingsForm extends ConfigFormBase {

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Contains the configuration object factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new Varbase Media Header Block.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(array $configuration, string $plugin_id, array $plugin_definition, ModuleHandlerInterface $module_handler, ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->moduleHandler = $module_handler;
    $this->configFactory = $config_factory;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('module_handler'),
      $container->get('config.factory'),
      $container->get('entity_type.manager')
    );
  }

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

    $entity_info = $this->entityTypeManager->getStorage('node_type')->loadMultiple();
    $vmh_settings = $config->get('varbase_media_header_settings');

    // Planed for allowed entity types to have node, taxonomy.
    // more other entity types if needed.
    $allowed_entity_types = ['node'];

    $form['varbase_media_header_settings'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Select entity types which are going to use the varbase media header'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
      '#tree' => TRUE,
      '#description' => $this->t('Enable varbase media header for these entity types and bundles.'),
    ];

    foreach ($entity_info as $entity_type_key => $entity_type) {
      $bundle_options = [];

      // Skip not allowed entity types.
      if (in_array($entity_type_key, $allowed_entity_types)) {
        $bundles = $this->entityTypeManager->getBundleInfo($entity_type_key);
        foreach ($bundles as $bundle_key => $bundle) {
          $bundle_options[$bundle_key] = $bundle['label'];
        }

        $form['varbase_media_header_settings'][$entity_type_key] = [
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
      ->set('varbase_media_header_settings', $form_state->getValue('varbase_media_header_settings'))
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
    $vmh_settings = $this->config('varbase_media_header.settings')->get('varbase_media_header_settings');
    $entity_info = $this->entityTypeManager->getDefinitions();

    $module_path = $this->moduleHandler->getModule('varbase_media_header')->getPath();

    foreach ($entity_info as $entity_type_key => $entity_type) {

      // Entity Type Assets Path.
      $entity_type_assets_path = $module_path . '/src/assets/config_templates/' . $entity_type_key;

      $bundles = $this->entityTypeManager->getBundleInfo($entity_type_key);
      foreach ($bundles as $bundle_key => $bundle) {
        if (!empty($vmh_settings[$entity_type_key])
         && isset($vmh_settings[$entity_type_key][$bundle_key])
         && $vmh_settings[$entity_type_key][$bundle_key]) {

          $config_name = "field.field." . $entity_type_key . "." . $bundle_key . ".field_page_header_style";
          if (!($this->configFactory->get($config_name) == NULL)) {
            $config_factory = $this->configFactory->getEditable($config_name);
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
          if (!($this->configFactory->get($config_name) == NULL)) {
            $config_factory = $this->configFactory->getEditable($config_name);
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
          if (!($this->configFactory->get($config_name) == NULL)) {
            $config_factory = $this->configFactory->getEditable($config_name);
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
          if (!($this->configFactory->get($config_name) == NULL)) {
            $config_factory = $this->configFactory->getEditable($config_name);
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

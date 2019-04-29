<?php

namespace Drupal\varbase_media_header\Plugin\Block;

use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Cache\Cache;
use Drupal\media\Entity\Media;
use Drupal\Core\Language\LanguageInterface;

/**
 * Provides a Varbase Media Header block.
 *
 * Responsive media as a background and page title and breadcrumbs.
 *
 * @Block(
 *   id = "varbase_media_header_block",
 *   admin_label = @Translation("Varbase Media Header"),
 *   category = @Translation("Varbase Media Header"),
 *   context = {
 *     "node" = @ContextDefinition(
 *       "entity:node",
 *       label = @Translation("Current Node"),
 *       required = FALSE,
 *     )
 *   }
 * )
 */
class VarbaseMediaHeaderBlock extends BlockBase implements BlockPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();

    $node = \Drupal::routeMatch()->getParameter('node');
    if ($node instanceof NodeInterface) {
      if (isset($node)) {

        $node = Node::load($node->id());

        if (isset($config['vmh_node'][$node->bundle()])
          && $config['vmh_node'][$node->bundle()] != '_none_') {

          if ($node->hasField('field_page_header_style')
            && !$node->get('field_page_header_style')->isEmpty()
            && $node->get('field_page_header_style')->value != 'standard') {

            // Page title.
            $vmh_page_title = \Drupal::service('title_resolver')->getTitle(\Drupal::request(), \Drupal::routeMatch()->getRouteObject());
            $vmh_page_title = is_array($vmh_page_title) ? $vmh_page_title['#markup'] : $vmh_page_title;

            // Page Breadcrumb block.
            $block_manager = \Drupal::service('plugin.manager.block');
            $block_config = [];
            $plugin_block = $block_manager->createInstance('system_breadcrumb_block', $block_config);
            $access_result = $plugin_block->access(\Drupal::currentUser());
            if (!(is_object($access_result)
               && $access_result->isForbidden()
               || is_bool($access_result)
               && !$access_result)) {

              $vmh_page_breadcrumbs = $plugin_block->build();
            }

            $media_field_name = $config['vmh_node'][$node->bundle()];

            $langcode = \Drupal::languageManager()->getCurrentLanguage(LanguageInterface::TYPE_CONTENT)->getId();

            // Background media.
            $vmh_background_media = NULL;
            if ($node->hasField($media_field_name)) {
              if ($node->hasTranslation($langcode)) {
                if (!$node->getTranslation($langcode)->get($media_field_name)->isEmpty()) {
                  $node_field_media = $node->getTranslation($langcode)->get($media_field_name)->getValue();
                }
              }
              else {
                $node_field_media = $node->get($media_field_name)->getValue();
              }

              if (!empty($node_field_media)) {
                $node_field_media_entity = Media::load($node_field_media[0]['target_id']);
                $node_field_media_build = \Drupal::entityTypeManager()->getViewBuilder('media')->view($node_field_media_entity, $config['vmh_media_view_mode']);
                $vmh_background_media = \Drupal::service('renderer')->render($node_field_media_build);
              }
            }

            return [
              'varbase_media_header_content' => [
                '#title' => $this->t('Varbase Media Header'),
                '#theme' => 'varbase_media_header_block',
                '#cache' => [
                  'tags' => $this->getCacheTags(),
                  'contexts' => $this->getCacheContexts(),
                  'max-age' => $this->getCacheMaxAge(),
                ],
                '#vmh_page_title' => $vmh_page_title,
                '#vmh_page_breadcrumbs' => $vmh_page_breadcrumbs,
                '#vmh_background_media' => $vmh_background_media,
              ],
            ];
          }
        }
      }
    }

    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $config = $this->getConfiguration();

    $vmh_settings = \Drupal::config('varbase_media_header.settings')->get(VARBASE_MEDIA_HEADER_SETTINGS);

    $entity_info = \Drupal::entityManager()->getDefinitions();

    foreach ($entity_info as $entity_type_key => $entity_type) {

      if (!empty($vmh_settings[$entity_type_key])
        && isset($vmh_settings[$entity_type_key])) {

        $form['vmh_' . $entity_type_key] = [
          '#type' => 'fieldset',
          '#open' => TRUE,
          '#title' => $entity_type->getLabel(),
        ];

        $bundles = \Drupal::entityManager()->getBundleInfo($entity_type_key);
        foreach ($bundles as $bundle_key => $bundle) {
          if (!empty($vmh_settings[$entity_type_key])
            && isset($vmh_settings[$entity_type_key][$bundle_key])
            && $vmh_settings[$entity_type_key][$bundle_key]) {

            $options = ['_none_' => t("-  None  -")];

            $media_fields = \Drupal::service('entity_field.manager')->getFieldDefinitions($entity_type_key, $bundle_key);
            foreach ($media_fields as $field_name => $field_definition) {
              if (!empty($field_definition->getTargetBundle())) {
                if ($field_definition->getType() == 'entity_reference'
                  && $field_definition->getSettings()['target_type'] == 'media') {
                  $options[$field_name] = $field_definition->getLabel();
                }
              }
            }

            $bundle_label = \Drupal::entityTypeManager()
              ->getStorage('node_type')
              ->load($bundle_key)
              ->label();

            $form['vmh_' . $entity_type_key][$bundle_key] = [
              '#type' => 'select',
              '#title' => $bundle_label,
              '#description' => t('Choose a media field.'),
              '#options' => $options,
              '#default_value' => isset($config['vmh_' . $entity_type_key][$bundle_key]) ? $config['vmh_' . $entity_type_key][$bundle_key] : '_none_',
            ];

          }
        }
      }
    }

    $media_view_mode_options = \Drupal::service('entity_display.repository')->getViewModeOptions('media');
    $form['vmh_media_view_mode'] = [
      '#type' => 'select',
      '#title' => t('Media view mode'),
      '#description' => t('Choose the Media view mode to use.'),
      '#options' => $media_view_mode_options,
      '#default_value' => isset($config['vmh_media_view_mode']) ? $config['vmh_media_view_mode'] : '',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();

    // Save configs for each entity type.
    $vmh_settings = \Drupal::config('varbase_media_header.settings')->get(VARBASE_MEDIA_HEADER_SETTINGS);
    $entity_info = \Drupal::entityManager()->getDefinitions();
    foreach ($entity_info as $entity_type_key => $entity_type) {
      if (!empty($vmh_settings[$entity_type_key])
        && isset($vmh_settings[$entity_type_key])) {

        $bundles = \Drupal::entityManager()->getBundleInfo($entity_type_key);
        foreach ($bundles as $bundle_key => $bundle) {
          if (!empty($vmh_settings[$entity_type_key])
            && isset($vmh_settings[$entity_type_key][$bundle_key])
            && $vmh_settings[$entity_type_key][$bundle_key]) {

            $this->configuration['vmh_' . $entity_type_key] = $values['vmh_' . $entity_type_key];
          }
        }
      }
    }

    $this->configuration['vmh_media_view_mode'] = $values['vmh_media_view_mode'];

  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    if ($node = \Drupal::routeMatch()->getParameter('node')) {
      return Cache::mergeTags(parent::getCacheTags(), ['node:' . $node->id()]);
    }
    else {
      return parent::getCacheTags();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(),
      ['url.path',
        'url.query_args',
        'route',
      ]);
  }

}

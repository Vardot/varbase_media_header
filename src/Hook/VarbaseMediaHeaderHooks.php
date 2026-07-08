<?php

declare(strict_types=1);

namespace Drupal\varbase_media_header\Hook;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Theme\ThemeManagerInterface;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\TermInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Hook implementations for the Varbase Media Header module.
 */
class VarbaseMediaHeaderHooks {

  use StringTranslationTrait;

  /**
   * Constructs a VarbaseMediaHeaderHooks object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The configuration factory.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $bundleInfo
   *   The entity type bundle info service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Theme\ThemeManagerInterface $themeManager
   *   The theme manager.
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   The current route match.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler.
   */
  public function __construct(
    protected ConfigFactoryInterface $configFactory,
    protected EntityTypeBundleInfoInterface $bundleInfo,
    protected EntityTypeManagerInterface $entityTypeManager,
    protected ThemeManagerInterface $themeManager,
    protected RouteMatchInterface $routeMatch,
    protected RequestStack $requestStack,
    protected ModuleHandlerInterface $moduleHandler,
  ) {}

  /**
   * Implements hook_form_BASE_FORM_ID_alter() for node forms.
   */
  #[Hook('form_node_form_alter')]
  public function formNodeFormAlter(array &$form, FormStateInterface $form_state, string $form_id): void {
    $vmh_settings = $this->configFactory->get('varbase_media_header.settings')->get('varbase_media_header_settings');
    $bundles = $this->bundleInfo->getBundleInfo('node');

    foreach ($bundles as $bundle_key => $bundle) {
      if (!empty($vmh_settings['node']) && isset($vmh_settings['node'][$bundle_key]) && $vmh_settings['node'][$bundle_key]) {
        if ($form_id == 'node_' . $bundle_key . '_edit_form'
          || $form_id == 'node_' . $bundle_key . '_form') {
          $this->nodeGroupForm($form);
        }
      }
    }
  }

  /**
   * Implements hook_form_BASE_FORM_ID_alter() for taxonomy term forms.
   */
  #[Hook('form_taxonomy_term_form_alter')]
  public function formTaxonomyTermFormAlter(array &$form, FormStateInterface $form_state, string $form_id): void {
    $vmh_settings = $this->configFactory->get('varbase_media_header.settings')->get('varbase_media_header_settings');
    $bundles = $this->bundleInfo->getBundleInfo('taxonomy_term');

    foreach ($bundles as $bundle_key => $bundle) {
      if (!empty($vmh_settings['taxonomy_term']) && isset($vmh_settings['taxonomy_term'][$bundle_key]) && $vmh_settings['taxonomy_term'][$bundle_key]) {
        if ($form_id == 'taxonomy_term_' . $bundle_key . '_edit_form'
          || $form_id == 'taxonomy_term_' . $bundle_key . '_form') {
          $this->taxonomyTermGroupForm($form);
        }
      }
    }
  }

  /**
   * Implements hook_preprocess_HOOK() for block templates.
   */
  #[Hook('preprocess_block')]
  public function preprocessBlock(array &$variables): void {
    $active_theme = $this->configFactory->get('system.theme')->get('default');
    $current_theme = $this->themeManager->getActiveTheme()->getName();
    if (($variables['plugin_id'] == 'page_title_block'
        || $variables['plugin_id'] == 'system_breadcrumb_block')
        && $active_theme == $current_theme) {

      $entity = FALSE;
      $entity_type = FALSE;

      $node = NULL;
      $route_name = $this->routeMatch->getRouteName();
      if ($route_name == 'entity.node.canonical') {
        $node = $this->routeMatch->getParameter('node');
      }
      elseif ($route_name == 'entity.node.latest_version') {
        $latest_version_node = $this->routeMatch->getParameter('node');
        /** @var \Drupal\Core\Entity\RevisionableStorageInterface $storage */
        $storage = $this->entityTypeManager->getStorage('node');
        $last_revision_id = $storage->getLatestRevisionId($latest_version_node->id());
        $node = $storage->loadRevision($last_revision_id);
      }
      elseif ($route_name == 'entity.node.preview'
        && $this->routeMatch->getParameter('view_mode_id') == 'full') {
        $node = $this->routeMatch->getParameter('node_preview');
      }

      if ($node instanceof NodeInterface) {
        $entity = $node;
        $entity_type = $entity->getEntityType()->id();
      }
      else {
        $taxonomy = $this->routeMatch->getParameter('taxonomy_term');
        if ($taxonomy instanceof TermInterface) {
          $entity = $taxonomy;
          $entity_type = $entity->getEntityType()->id();
        }
      }

      if ($entity_type != FALSE) {
        $vmh_settings = $this->configFactory->get('varbase_media_header.settings')->get('varbase_media_header_settings');
        if (!empty($vmh_settings[$entity_type])
          && isset($vmh_settings[$entity_type][$entity->bundle()])
          && $vmh_settings[$entity_type][$entity->bundle()]
          && $entity->hasField('field_page_header_style')
          && !$entity->get('field_page_header_style')->isEmpty()
          && $entity->get('field_page_header_style')->value != 'standard') {

          $vmh_hide_breadcrumbs = $this->configFactory->get('varbase_media_header.settings')->get('hide_breadcrumbs');

          if ($variables['plugin_id'] == 'system_breadcrumb_block'
            && isset($vmh_hide_breadcrumbs)
            && $vmh_hide_breadcrumbs == FALSE) {
            $variables['content'] = '';
          }
          elseif ($variables['plugin_id'] == 'page_title_block') {
            $variables['content'] = '';
          }
        }
        $variables['#cache']['contexts'] = array_merge(
          $variables['#cache']['contexts'] ?? [],
          [
            'route',
            'url',
            'url.path',
            'url.path.parent',
            'url.path.is_front',
            'languages:language_interface',
            'theme',
          ]
        );
      }
    }
  }

  /**
   * Implements hook_theme().
   */
  #[Hook('theme')]
  public function theme(array $existing, string $type, string $theme, string $path): array {
    return [
      'varbase_media_header_block' => [
        'variables' => [
          'title' => 'Varbase Media Header',
          'description' => NULL,
          'vmh_page_title' => NULL,
          'vmh_page_breadcrumbs' => NULL,
          'vmh_background_media' => NULL,
          'vmh_media_type' => NULL,
          'provider' => NULL,
        ],
      ],
      'media_oembed_iframe__remote_video__varbase_media_header' => [
        'template' => 'media-oembed-iframe--remote-video--varbase-media-header',
        'variables' => [
          'provider' => NULL,
          'media' => NULL,
        ],
      ],
    ];
  }

  /**
   * Implements hook_preprocess_HOOK() for the media header oEmbed iframe.
   */
  #[Hook('preprocess_media_oembed_iframe__remote_video__varbase_media_header')]
  public function preprocessMediaOembedIframe(array &$variables): void {
    $query = $this->requestStack->getCurrentRequest()->query;
    $variables['type'] = $query->get('type');
    $variables['provider'] = $query->get('provider');
    $variables['view_mode'] = $query->get('view_mode');
    $variables['base_path'] = base_path();
    $variables['varbase_media_header_path'] = $this->moduleHandler->getModule('varbase_media_header')->getPath();
  }

  /**
   * Groups the Media Header fields on the node add/edit form.
   *
   * @param array $form
   *   The form render array.
   */
  protected function nodeGroupForm(array &$form): void {
    $form['vmh_group'] = [
      '#type' => 'details',
      '#title' => $this->t('Media Header'),
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
   * Groups the Media Header fields on the taxonomy term add/edit form.
   *
   * @param array $form
   *   The form render array.
   */
  protected function taxonomyTermGroupForm(array &$form): void {
    $form['vmh_group'] = [
      '#type' => 'details',
      '#title' => $this->t('Media Header'),
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

}

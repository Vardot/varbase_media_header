<?php

namespace Drupal\varbase_media_header\Plugin\Block;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Controller\TitleResolverInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\node\NodeInterface;
use Drupal\media\MediaInterface;
use Drupal\taxonomy\TermInterface;

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
 *     ),
 *     "taxonomy_term" = @ContextDefinition(
 *       "entity:taxonomy_term",
 *       label = @Translation("Current Term"),
 *       required = FALSE,
 *    ),
 *   }
 * )
 */
class VarbaseMediaHeaderBlock extends BlockBase implements ContainerFactoryPluginInterface {

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
   * The entity type bundle information service.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $bundleInfo;

  /**
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The title resolver.
   *
   * @var \Drupal\Core\Controller\TitleResolverInterface
   */
  protected $titleResolver;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The entity display repository.
   *
   * @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface
   */
  protected $entityDisplayRepository;

  /**
   * Constructs a new Varbase Media Header Block.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $bundle_info
   *   The entity type bundle service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager service.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   * @param \Drupal\Core\Controller\TitleResolverInterface $title_resolver
   *   The title resolver service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The redirect destination service.
   * @param \Drupal\Core\Block\BlockManagerInterface $block_manager
   *   The block manager.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   * @param \Drupal\Core\Entity\EntityDisplayRepositoryInterface $entity_display_repository
   *   The entity display repository.
   */
  public function __construct(array $configuration,
  $plugin_id,
  $plugin_definition,
          ConfigFactoryInterface $config_factory,
          EntityTypeManagerInterface $entity_type_manager,
          EntityTypeBundleInfoInterface $bundle_info,
          LanguageManagerInterface $language_manager,
          RouteMatchInterface $route_match,
          TitleResolverInterface $title_resolver,
          RequestStack $request_stack,
          AccountInterface $current_user,
          BlockManagerInterface $block_manager,
          RendererInterface $renderer,
          EntityFieldManagerInterface $entity_field_manager,
          EntityDisplayRepositoryInterface $entity_display_repository) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
    $this->entityTypeManager = $entity_type_manager;
    $this->bundleInfo = $bundle_info;
    $this->languageManager = $language_manager;
    $this->routeMatch = $route_match;
    $this->titleResolver = $title_resolver;
    $this->requestStack = $request_stack;
    $this->currentUser = $current_user;
    $this->blockManager = $block_manager;
    $this->renderer = $renderer;
    $this->entityFieldManager = $entity_field_manager;
    $this->entityDisplayRepository = $entity_display_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('language_manager'),
      $container->get('current_route_match'),
      $container->get('title_resolver'),
      $container->get('request_stack'),
      $container->get('current_user'),
      $container->get('plugin.manager.block'),
      $container->get('renderer'),
      $container->get('entity_field.manager'),
      $container->get('entity_display.repository')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    $config = $this->getConfiguration();
    $process = FALSE;
    $entity = FALSE;

    $node = new \stdClass();
    if ($this->routeMatch->getRouteName() == 'entity.node.canonical') {
      $node = $this->routeMatch->getParameter('node');
    }
    elseif ($this->routeMatch->getRouteName() == 'entity.node.preview'
      && $this->routeMatch->getParameter('view_mode_id') == 'full') {
      $node = $this->routeMatch->getParameter('node_preview');
    }

    if ($node instanceof NodeInterface && isset($node)) {
      $node_bundle = '';
      if ($this->routeMatch->getRouteName() == 'entity.node.canonical') {
        $node = $this->entityTypeManager->getStorage('node')->load($node->id());
        $node_bundle = $node->bundle();
      }
      elseif ($this->routeMatch->getRouteName() == 'entity.node.preview'
        && $this->routeMatch->getParameter('view_mode_id') == 'full') {
        if ($node->isNew()) {
          $node_bundle = $node->getType();
        }
        else {
          $node_bundle = $node->bundle();
        }
      }

      $entity = $node;
      $process = isset($config['vmh_node'][$node_bundle]) && $config['vmh_node'][$node_bundle] != '_none_';
    }
    else {
      $taxonomy = $this->routeMatch->getParameter('taxonomy_term');
      if ($taxonomy instanceof TermInterface && isset($taxonomy)) {
        $taxonomy = $this->entityTypeManager->getStorage('taxonomy_term')->load($taxonomy->tid->value);
        $entity = $taxonomy;
        $process = isset($config['vmh_taxonomy_term'][$taxonomy->bundle()]) && $config['vmh_taxonomy_term'][$taxonomy->bundle()] != '_none_';
      }
    }

    if ($process != FALSE
      && $entity->hasField('field_page_header_style')
      && !$entity->get('field_page_header_style')->isEmpty()
      && $entity->get('field_page_header_style')->value != 'standard') {

      // Page title.
      $vmh_page_title = $this->titleResolver->getTitle($this->requestStack->getCurrentRequest(), $this->routeMatch->getRouteObject());
      $vmh_page_title = is_array($vmh_page_title) ? $vmh_page_title['#markup'] : $vmh_page_title;

      // Page Breadcrumb block.
      $block_config = [];
      $plugin_block = $this->blockManager->createInstance('system_breadcrumb_block', $block_config);
      $access_result = $plugin_block->access($this->currentUser);
      if (!(is_object($access_result)
          && $access_result->isForbidden()
          || is_bool($access_result)
          && !$access_result)) {

        $vmh_hide_breadcrumbs = $this->configFactory->get('varbase_media_header.settings')->get('hide_breadcrumbs');

        if (isset($vmh_hide_breadcrumbs)
            && $vmh_hide_breadcrumbs == FALSE) {
          $vmh_page_breadcrumbs = $plugin_block->build();
        }
      }

      $media_field_name = $config['vmh_' . $entity->getEntityType()->id()][$entity->bundle()];

      $langcode = $this->languageManager->getCurrentLanguage(LanguageInterface::TYPE_CONTENT)->getId();

      // Background media.
      $vmh_background_media = NULL;
      if ($entity->hasField($media_field_name)) {
        if ($entity->hasTranslation($langcode)) {
          if (!$entity->getTranslation($langcode)->get($media_field_name)->isEmpty()) {
            $entity_field_media = $entity->getTranslation($langcode)->get($media_field_name)->getValue();
          }
        }
        else {
          $entity_field_media = $entity->get($media_field_name)->getValue();
        }

        if (!empty($entity_field_media)) {
          $entity_field_media_entity = $this->entityTypeManager->getStorage('media')->load($entity_field_media[0]['target_id']);

          if ($entity_field_media_entity instanceof MediaInterface) {
            $entity_field_media_build = $this->entityTypeManager->getViewBuilder('media')->view($entity_field_media_entity, $config['vmh_media_view_mode']);
            $vmh_background_media = $this->renderer->render($entity_field_media_build);
            $vmh_media_type = $entity_field_media_entity->bundle();
            if (isset($entity_field_media_entity->field_provider) && !empty($entity_field_media_entity->field_provider)) {
              $provider = $entity_field_media_entity->field_provider->value;
            }
          }
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
          '#vmh_page_title' => (!empty($vmh_page_title) ? $vmh_page_title : NULL),
          '#vmh_page_breadcrumbs' => (!empty($vmh_page_breadcrumbs) ? $vmh_page_breadcrumbs : NULL),
          '#vmh_background_media' => (!empty($vmh_background_media) ? $vmh_background_media : NULL),
          '#vmh_media_type' => (!empty($vmh_media_type) ? $vmh_media_type : NULL),
          '#provider' => (!empty($provider) ? $provider : NULL),
        ],
      ];
    }

    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $config = $this->getConfiguration();

    $vmh_settings = (array) $this->configFactory->get('varbase_media_header.settings')
      ->get('varbase_media_header_settings');

    $entity_info = $this->entityTypeManager->getDefinitions();

    foreach ($entity_info as $entity_type_key => $entity_type) {

      if (!empty($vmh_settings[$entity_type_key])
        && isset($vmh_settings[$entity_type_key])) {

        $form['vmh_' . $entity_type_key] = [
          '#type' => 'fieldset',
          '#open' => TRUE,
          '#title' => $entity_type->getLabel(),
        ];

        $bundles = $this->bundleInfo->getBundleInfo($entity_type_key);
        foreach ($bundles as $bundle_key => $bundle) {
          if (!empty($vmh_settings[$entity_type_key])
            && isset($vmh_settings[$entity_type_key][$bundle_key])
            && $vmh_settings[$entity_type_key][$bundle_key]) {

            $options = ['_none_' => $this->t("-  None  -")];

            $media_fields = $this->entityFieldManager->getFieldDefinitions($entity_type_key, $bundle_key);
            foreach ($media_fields as $field_name => $field_definition) {
              if (!empty($field_definition->getTargetBundle())) {
                if ($field_definition->getType() == 'entity_reference'
                  && $field_definition->getSettings()['target_type'] == 'media') {
                  $options[$field_name] = $field_definition->getLabel();
                }
              }
            }
            if ($entity_type_key == 'node') {
              $bundle_label = $this->entityTypeManager->getStorage('node_type')
                ->load($bundle_key)
                ->label();
            }
            elseif ($entity_type_key == 'taxonomy_term') {
              $bundle_label = $this->entityTypeManager->getStorage('taxonomy_vocabulary')
                ->load($bundle_key)
                ->label();
            }

            $form['vmh_' . $entity_type_key][$bundle_key] = [
              '#type' => 'select',
              '#title' => $bundle_label,
              '#description' => $this->t('Choose a media field.'),
              '#options' => $options,
              '#default_value' => isset($config['vmh_' . $entity_type_key][$bundle_key]) ? $config['vmh_' . $entity_type_key][$bundle_key] : '_none_',
            ];

          }
        }
      }
    }

    $media_view_mode_options = $this->entityDisplayRepository->getViewModeOptions('media');
    $form['vmh_media_view_mode'] = [
      '#type' => 'select',
      '#title' => $this->t('Media view mode'),
      '#description' => $this->t('Choose the Media view mode to use.'),
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
    $vmh_settings = (array) $this->configFactory->get('varbase_media_header.settings')
      ->get('varbase_media_header_settings');

    $entity_info = $this->entityTypeManager->getDefinitions();
    foreach ($entity_info as $entity_type_key => $entity_type) {
      if (!empty($vmh_settings[$entity_type_key])
        && isset($vmh_settings[$entity_type_key])) {

        $bundles = $this->bundleInfo->getBundleInfo($entity_type_key);
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
    $node = new \stdClass();
    if ($this->routeMatch->getRouteName() == 'entity.node.canonical') {
      $node = $this->routeMatch->getParameter('node');
    }
    elseif ($this->routeMatch->getRouteName() == 'entity.node.preview'
      && $this->routeMatch->getParameter('view_mode_id') == 'full') {
      $node = $this->routeMatch->getParameter('node_preview');
    }

    if ($node instanceof NodeInterface) {
      if (isset($node)) {
        return Cache::mergeTags(parent::getCacheTags(), ['node:' . $node->id()]);
      }
    }
    elseif (is_numeric($node)) {
      return Cache::mergeTags(parent::getCacheTags(), ['node:' . (int) $node]);
    }
    else {
      $taxonomy = $this->routeMatch->getParameter('taxonomy_term');
      if ($taxonomy instanceof TermInterface) {
        if (isset($taxonomy)) {
          return Cache::mergeTags(parent::getCacheTags(), ['taxonomy_term:' . $taxonomy->tid->value]);
        }
      }
      elseif (is_numeric($taxonomy)) {
        return Cache::mergeTags(parent::getCacheTags(), ['taxonomy_term:' . (int) $taxonomy]);
      }
    }

    return parent::getCacheTags();
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

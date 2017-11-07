<?php

namespace Drupal\block_discovery\Plugin\Block;

use Drupal\Component\Annotation\Plugin\Discovery\AnnotationBridgeDecorator;
use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Plugin\Discovery\AnnotatedClassDiscovery;
use Drupal\Core\Plugin\Discovery\ContainerDerivativeDiscoveryDecorator;
use Drupal\Core\Plugin\Discovery\YamlDiscoveryDecorator;

/**
 * Provides a plugin manager for discoverable blocks.
 */
class DiscoverableBlockManager extends DefaultPluginManager implements DiscoverableBlockManagerInterface {

  /**
   * The theme handler.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * DiscoverableBlockPluginManager constructor.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   The theme handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler, ThemeHandlerInterface $theme_handler) {
    parent::__construct('Plugin/Block', $namespaces, $module_handler, DiscoverableBlockInterface::class, DiscoverableBlock::class);
    $this->themeHandler = $theme_handler;
    $this->setCacheBackend($cache_backend, 'block_discovery');
    $this->alterInfo('block_discovery');
  }

  /**
   * {@inheritdoc}
   */
  protected function providerExists($provider) {
    return $this->moduleHandler->moduleExists($provider) || $this->themeHandler->themeExists($provider);
  }

  /**
   * {@inheritdoc}
   */
  protected function getDiscovery() {
    if (!$this->discovery) {
      $discovery = new AnnotatedClassDiscovery($this->subdir, $this->namespaces, $this->pluginDefinitionAnnotationName, $this->additionalAnnotationNamespaces);
      $discovery = new YamlDiscoveryDecorator($discovery, 'blocks', $this->moduleHandler->getModuleDirectories() + $this->themeHandler->getThemeDirectories());
      $discovery = new AnnotationBridgeDecorator($discovery, $this->pluginDefinitionAnnotationName);
      $discovery = new ContainerDerivativeDiscoveryDecorator($discovery);
      $this->discovery = $discovery;
    }
    return $this->discovery;
  }

  /**
   * {@inheritdoc}
   */
  public function processDefinition(&$definition, $plugin_id) {
    parent::processDefinition($definition, $plugin_id);

    if (!$definition instanceof DiscoverableBlockDefinition) {
      throw new InvalidPluginDefinitionException($plugin_id, sprintf('The "%s" discoverable block definition must extend %s', $plugin_id, DiscoverableBlockDefinition::class));
    }

    // Add the module or theme path to the 'path'.
    $provider = $definition->getProvider();
    if ($this->moduleHandler->moduleExists($provider)) {
      $base_path = $this->moduleHandler->getModule($provider)->getPath();
    }
    elseif ($this->themeHandler->themeExists($provider)) {
      $base_path = $this->themeHandler->getTheme($provider)->getPath();
    }
    else {
      $base_path = '';
    }

    $path = $definition->getPath();
    $path = !empty($path) ? $base_path . '/' . $path : $base_path;
    $definition->setPath($path);

    // Add a dependency on the provider of the library.
    if ($library = $definition->getLibrary()) {
      $config_dependencies = $definition->getConfigDependencies();
      list($library_provider) = explode('/', $library, 2);
      if ($this->moduleHandler->moduleExists($library_provider)) {
        $config_dependencies['module'][] = $library_provider;
      }
      elseif ($this->themeHandler->themeExists($library_provider)) {
        $config_dependencies['theme'][] = $library_provider;
      }
      $definition->setConfigDependencies($config_dependencies);
    }

    // If 'template' is set, then we'll derive 'template_path' and 'theme_hook'.
    $template = $definition->getTemplate();
    if (!empty($template)) {
      $template_parts = explode('/', $template);

      $template = array_pop($template_parts);
      $template_path = $path;
      if (count($template_parts) > 0) {
        $template_path .= '/' . implode('/', $template_parts);
      }
      $definition->setTemplate($template);
      $definition->setThemeHook(strtr($template, '-', '_'));
      $definition->setTemplatePath($template_path);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getThemeImplementations() {
    $hooks = [];
    $hooks['block_discovery'] = [
      'render element' => 'content',
    ];
    /** @var \Drupal\block_discovery\Plugin\Block\DiscoverableBlockDefinition[] $definitions */
    $definitions = $this->getDefinitions();
    foreach ($definitions as $definition) {
      if ($template = $definition->getTemplate()) {
        $hooks[$definition->getThemeHook()] = [
          'render element' => 'content',
          'base hook' => 'block_discovery',
          'template' => $template,
          'path' => $definition->getTemplatePath(),
          'variables' => ['site_url' => NULL],
        ];
      }
    }
    return $hooks;
  }

  /**
   * {@inheritdoc}
   */
  public function getCategories() {
    // Fetch all categories from definitions and remove duplicates.
    $categories = array_unique(array_values(array_map(function (DiscoverableBlockDefinition $definition) {
      return $definition->getCategory();
    }, $this->getDefinitions())));
    natcasesort($categories);
    return $categories;
  }

  /**
   * {@inheritdoc}
   *
   * @return \Drupal\block_discovery\Plugin\Block\DiscoverableBlockDefinition[]
   *   A sorted array of discoverable block definitions.
   */
  public function getSortedDefinitions(array $definitions = NULL, $label_key = 'label') {
    // Sort the plugins first by category, then by label.
    $definitions = isset($definitions) ? $definitions : $this->getDefinitions();
    // Suppress errors because PHPUnit will indirectly modify the contents,
    // triggering https://bugs.php.net/bug.php?id=50688.
    @uasort($definitions, function (DiscoverableBlockDefinition $a, DiscoverableBlockDefinition $b) {
      if ($a->getCategory() != $b->getCategory()) {
        return strnatcasecmp($a->getCategory(), $b->getCategory());
      }
      return strnatcasecmp($a->getLabel(), $b->getLabel());
    });
    return $definitions;
  }

  /**
   * {@inheritdoc}
   *
   * @return \Drupal\block_discovery\Plugin\Block\DiscoverableBlockDefinition[][]
   *   A grouped list of discoverable block definitions.
   */
  public function getGroupedDefinitions(array $definitions = NULL, $label_key = 'label') {
    $definitions = $this->getSortedDefinitions(isset($definitions) ? $definitions : $this->getDefinitions(), $label_key);
    $grouped_definitions = [];
    foreach ($definitions as $id => $definition) {
      $grouped_definitions[(string) $definition->getCategory()][$id] = $definition;
    }
    return $grouped_definitions;
  }

  /**
   * {@inheritdoc}
   */
  public function getDiscoverableBlockOptions() {
    $discoverable_block_options = [];
    foreach ($this->getGroupedDefinitions() as $category => $discoverable_block_definitions) {
      foreach ($discoverable_block_definitions as $name => $discoverable_block_definition) {
        $discoverable_block_options[$category][$name] = $discoverable_block_definition->getLabel();
      }
    }
    return $discoverable_block_options;
  }

}

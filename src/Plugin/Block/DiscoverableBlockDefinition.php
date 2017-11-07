<?php

namespace Drupal\block_discovery\Plugin\Block;

use Drupal\Component\Plugin\Definition\DerivablePluginDefinitionInterface;
use Drupal\Component\Plugin\Definition\PluginDefinitionInterface;
use Drupal\Component\Plugin\Definition\PluginDefinition;
use Drupal\Core\Plugin\Definition\DependentPluginDefinitionInterface;
use Drupal\Core\Plugin\Definition\DependentPluginDefinitionTrait;

/**
 * Provides an implementation of a discoverable block definition & its metadata.
 */
class DiscoverableBlockDefinition extends PluginDefinition implements PluginDefinitionInterface, DerivablePluginDefinitionInterface, DependentPluginDefinitionInterface {

  use DependentPluginDefinitionTrait;

  /**
   * The name of the deriver of this discoverable block definition, if any.
   *
   * @var string|null
   */
  protected $deriver;

  /**
   * The human-readable name.
   *
   * @var string
   */
  protected $label;

  /**
   * An optional description for advanced discoverable blocks.
   *
   * @var string
   */
  protected $description;

  /**
   * The human-readable category.
   *
   * @var string
   */
  protected $category;

  /**
   * The template file to render this discoverable block.
   *
   * @var string|null
   */
  protected $template;

  /**
   * The path to the template.
   *
   * @var string
   */
  protected $templatePath;

  /**
   * The theme hook used to render this discoverable block.
   *
   * @var string|null
   */
  protected $themeHook;

  /**
   * Path (relative to the module or theme) to resources like icon or template.
   *
   * @var string
   */
  protected $path;

  /**
   * The asset library.
   *
   * @var string|null
   */
  protected $library;

  /**
   * The default region.
   *
   * @var string
   */
  protected $region;

  /**
   * Any additional properties and values.
   *
   * @var array
   */
  protected $additional = [];

  /**
   * DiscoverableBlockDefinition constructor.
   *
   * @param array $definition
   *   An array of values from the annotation.
   */
  public function __construct(array $definition) {
    foreach ($definition as $property => $value) {
      $this->set($property, $value);
    }
  }

  /**
   * Gets any arbitrary property.
   *
   * @param string $property
   *   The property to retrieve.
   *
   * @return mixed
   *   The value for that property, or NULL if the property does not exist.
   */
  public function get($property) {
    if (property_exists($this, $property)) {
      $value = isset($this->{$property}) ? $this->{$property} : NULL;
    }
    else {
      $value = isset($this->additional[$property]) ? $this->additional[$property] : NULL;
    }
    return $value;
  }

  /**
   * Sets a value to an arbitrary property.
   *
   * @param string $property
   *   The property to use for the value.
   * @param mixed $value
   *   The value to set.
   *
   * @return $this
   */
  public function set($property, $value) {
    if (property_exists($this, $property)) {
      $this->{$property} = $value;
    }
    else {
      $this->additional[$property] = $value;
    }
    return $this;
  }

  /**
   * Gets the human-readable name of the discoverable block definition.
   *
   * @return string|\Drupal\Core\StringTranslation\TranslatableMarkup
   *   The human-readable name of the discoverable block definition.
   */
  public function getLabel() {
    return $this->label;
  }

  /**
   * Sets the human-readable name of the discoverable block definition.
   *
   * @param string|\Drupal\Core\StringTranslation\TranslatableMarkup $label
   *   The human-readable name of the discoverable block definition.
   *
   * @return $this
   */
  public function setLabel($label) {
    $this->label = $label;
    return $this;
  }

  /**
   * Gets the description of the discoverable block definition.
   *
   * @return string|\Drupal\Core\StringTranslation\TranslatableMarkup
   *   The description of the discoverable block definition.
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * Sets the description of the discoverable block definition.
   *
   * @param string|\Drupal\Core\StringTranslation\TranslatableMarkup $description
   *   The description of the discoverable block definition.
   *
   * @return $this
   */
  public function setDescription($description) {
    $this->description = $description;
    return $this;
  }

  /**
   * Gets the human-readable category of the discoverable block definition.
   *
   * @return string|\Drupal\Core\StringTranslation\TranslatableMarkup
   *   The human-readable category of the discoverable block definition.
   */
  public function getCategory() {
    return $this->category;
  }

  /**
   * Sets the human-readable category of the discoverable block definition.
   *
   * @param string|\Drupal\Core\StringTranslation\TranslatableMarkup $category
   *   The human-readable category of the discoverable block definition.
   *
   * @return $this
   */
  public function setCategory($category) {
    $this->category = $category;
    return $this;
  }

  /**
   * Gets the template name.
   *
   * @return string|null
   *   The template name, if it exists.
   */
  public function getTemplate() {
    return $this->template;
  }

  /**
   * Sets the template name.
   *
   * @param string|null $template
   *   The template name.
   *
   * @return $this
   */
  public function setTemplate($template) {
    $this->template = $template;
    return $this;
  }

  /**
   * Gets the template path.
   *
   * @return string
   *   The template path.
   */
  public function getTemplatePath() {
    return $this->templatePath;
  }

  /**
   * Sets the template path.
   *
   * @param string $template_path
   *   The template path.
   *
   * @return $this
   */
  public function setTemplatePath($template_path) {
    $this->templatePath = $template_path;
    return $this;
  }

  /**
   * Gets the theme hook.
   *
   * @return string|null
   *   The theme hook, if it exists.
   */
  public function getThemeHook() {
    return $this->themeHook;
  }

  /**
   * Sets the theme hook.
   *
   * @param string $theme_hook
   *   The theme hook.
   *
   * @return $this
   */
  public function setThemeHook($theme_hook) {
    $this->themeHook = $theme_hook;
    return $this;
  }

  /**
   * Gets the base path for this discoverable block definition.
   *
   * @return string
   *   The base path.
   */
  public function getPath() {
    return $this->path;
  }

  /**
   * Sets the base path for this discoverable block definition.
   *
   * @param string $path
   *   The base path.
   *
   * @return $this
   */
  public function setPath($path) {
    $this->path = $path;
    return $this;
  }

  /**
   * Gets the asset library for this discoverable block definition.
   *
   * @return string|null
   *   The asset library, if it exists.
   */
  public function getLibrary() {
    return $this->library;
  }

  /**
   * Sets the asset library for this discoverable block definition.
   *
   * @param string|null $library
   *   The asset library.
   *
   * @return $this
   */
  public function setLibrary($library) {
    $this->library = $library;
    return $this;
  }

  /**
   * Gets the region.
   *
   * @return string
   *   The machine-readable name of the default region.
   */
  public function getRegion() {
    return $this->region;
  }

  /**
   * Sets the default region.
   *
   * @param string $region
   *   The machine-readable name of the default region.
   *
   * @return $this
   */
  public function setDefaultRegion($region) {
    $this->region = $region;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getDeriver() {
    return $this->deriver;
  }

  /**
   * {@inheritdoc}
   */
  public function setDeriver($deriver) {
    $this->deriver = $deriver;
    return $this;
  }

}

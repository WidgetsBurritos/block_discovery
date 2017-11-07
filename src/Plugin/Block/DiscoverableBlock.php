<?php

namespace Drupal\block_discovery\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a DiscoverabeBlock block.
 *
 * @Block(
 *   id = "discoverable_block",
 *   admin_label = @Translation("Discoverable block"),
 *   category = @Translation("Discoverable block"),
 * )
 */
class DiscoverableBlock extends BlockBase implements DiscoverableBlockInterface {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->setConfiguration($configuration);
  }

  /**
   * The page title: a string (plain title) or a render array (formatted title).
   *
   * @var string|array
   */
  protected $title = '';

  /**
   * {@inheritdoc}
   */
  public function setTitle($title) {
    $this->title = $title;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return ['label_display' => FALSE];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();
    // TODO: Inject service via constructor:
    $block_discovery_manager = \Drupal::service('block_discovery.manager');

    // Retrieve existing configuration for this block.
    $definitions = $block_discovery_manager->getSortedDefinitions();
    if (isset($definitions[$config['discoverable_block']])) {
      $block = $definitions[$config['discoverable_block']];

      return [
        '#theme' => $block->getThemeHook(),
        '#configuration' => $config,
        '#plugin_id' => $config['id'],
        '#base_plugin_id' => $this->getProvider(),
        '#derivative_plugin_id' => $config['id'],
        'content' => [],
      ];
    }

    return [
      '#markup' => $this->t('Invalid discoverable block.'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $config = $this->getConfiguration();
    // TODO: Inject service via constructor:
    $block_discovery_manager = \Drupal::service('block_discovery.manager');

    // Retrieve existing configuration for this block.
    $options = $block_discovery_manager->getDiscoverableBlockOptions();

    $form['discoverable_block'] = [
      '#type' => 'select',
      '#title' => $this->t('Discoverable Block'),
      '#options' => $options,
      '#default_value' => isset($config['discoverable_block']) ? $config['discoverable_block'] : '',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    // Save our custom settings when the form is submitted.
    $this->setConfigurationValue('discoverable_block', $form_state->getValue('discoverable_block'));
  }

  /**
   * {@inheritdoc}
   */
  public function blockValidate($form, FormStateInterface $form_state) {
    $block = $form_state->getValue('discoverable_block');

    if (empty($block)) {
      $form_state->setErrorByName('discoverable_block', t('Discoverable block is required'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getProvider() {
    return $this->pluginDefinition['provider'];
  }

  /**
   * {@inheritdoc}
   */
  public function get() {
    return new DiscoverableBlockDefinition($this->configuration);
  }

}

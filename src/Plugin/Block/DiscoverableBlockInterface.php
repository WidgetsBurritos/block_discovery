<?php

namespace Drupal\block_discovery\Plugin\Block;

/**
 * Interface for discoverable block plugins.
 */
interface DiscoverableBlockInterface {

  /**
   * Returns the provider.
   *
   * @return string
   *   The provider.
   */
  public function getProvider();

  // TODO: Add methods here.
}

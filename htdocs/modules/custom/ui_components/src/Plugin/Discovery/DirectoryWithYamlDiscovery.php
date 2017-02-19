<?php

/**
 * @file
 * Contains \Drupal\ui_components\Plugin\Discovery\DirectoryWithYamlDiscovery.
 */

namespace Drupal\ui_components\Plugin\Discovery;

use Drupal\ui_components\Component\Discovery\DirectoryWithYamlDiscovery as ComponentDirectoryWithYamlDiscovery;
use Drupal\Core\Plugin\Discovery\YamlDiscovery;

/**
 * Discovers directories each with one YAML file in a set of directories.
 */
class DirectoryWithYamlDiscovery extends YamlDiscovery {

  /**
   * Constructs a DirectoryWithYamlDiscovery object.
   *
   * @param array $directories
   *   An array of directories to scan, keyed by the provider. The value can
   *   either be a string or an array of strings. The string values should be
   *   the path of a directory to scan.
   * @param string $subdirectory
   *   The subdirectory to scan in each of the passed $directories.
   */
  public function __construct(array $directories, $subdirectory) {
    // Intentionally does not call parent constructor as this class uses a
    // different YAML discovery.
    $this->discovery = new ComponentDirectoryWithYamlDiscovery($directories, $subdirectory);
  }

}

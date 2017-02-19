<?php

/**
 * @file
 * Contains \Drupal\ui_components\Component\Discovery\DirectoryWithYamlDiscovery.
 */

namespace Drupal\ui_components\Component\Discovery;

use Drupal\Component\Discovery\YamlDirectoryDiscovery;

/**
 * Discovers directories each with one YAML file in a set of directories.
 */
class DirectoryWithYamlDiscovery extends YamlDirectoryDiscovery {

  /**
   * The subdirectory to scan.
   *
   * @var string
   */
  protected $subdirectory;

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
    parent::__construct($directories, 'directory_with_yaml:' . $subdirectory);
    $this->subdirectory = $subdirectory;
  }

  /**
   * {@inheritdoc}
   */
  protected function getIdentifier($file, array $data) {
    return basename($data[static::FILE_KEY], '.yml');
  }

  /**
   * Returns an array of providers keyed by file path.
   *
   * @return array
   *   An array of providers keyed by file path.
   */
  protected function findFiles() {
    $file_list = [];
    foreach ($this->directories as $provider => $directories) {
      $directories = (array) $directories;
      foreach ($directories as $directory) {
        // Check if there is a subdirectory with the specified name.
        if (is_dir($directory) && is_dir($directory . '/' . $this->subdirectory)) {
          // Now iterate over all subdirectories below the specifically named
          // subdirectory, and check if a .yml file exists with the same name.
          // For example:
          // - Assuming $this->subdirectory === 'fancy'
          // - Then this checks for 'fancy/foo/foo.yml', 'fancy/bar/bar.yml'.
          $iterator = new \FilesystemIterator($directory . '/' . $this->subdirectory);
          /** @var \SplFileInfo $file_info */
          foreach ($iterator as $file_info) {
            if ($file_info->isDir()) {
              $yml_file_in_directory = $file_info->getPath() . '/' . $file_info->getBasename() . '/' . $file_info->getBasename() . '.yml';
              if (is_file($yml_file_in_directory)) {
                $file_list[$yml_file_in_directory] = $provider;
              }
            }
          }
        }
      }
    }
    return $file_list;
  }

}

<?php
/**
 * @file
 * UiComponentsFileSystemLoader
 */

namespace Drupal\ui_components\Template\Loader;

use Drupal\Core\Template\Loader\FilesystemLoader;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;

/**
 * Class UiComponentsFileSystemLoader
 */
class UiComponentsFileSystemLoader extends FilesystemLoader {

  /**
   * @inheritdoc
   */
  public function __construct($paths = array(), ModuleHandlerInterface $module_handler, ThemeHandlerInterface $theme_handler) {
    parent::__construct($paths, $module_handler, $theme_handler);

    // Add paths for each component's namespace.
    // Components that could be used by non-Drupal applications may use
    // namespaces.
    $modules = array_keys($module_handler->getModuleList());
    foreach (\Drupal::service('theme_component_discovery')->getComponents() as $id => $resolved_component) {
      foreach ($resolved_component['_provider tree'] as $extension_name => $component) {
        // 1. Module components.
        if (in_array($extension_name, $modules)) {
          $path = $module_handler->invoke($extension_name, 'components_path');
          if (!$path) {
            $path = drupal_get_path('module', $extension_name);
          }
        }
        // 2. Theme components.
        elseif (drupal_get_path('theme', $extension_name)) {
          $path = drupal_get_path('theme', $extension_name);
        }
        if ($path) {
          $this->addPath($path . '/components/' . $id, $id);
        }
      }
    }

  }

}

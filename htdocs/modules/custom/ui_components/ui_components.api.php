<?php

/**
 * @file
 * Hooks specific to the UI Components module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Alter the array of module directories.
 *
 * Allows a module to declare components in an external directory.
 * For example, components from an external pattern library could be deployed
 * in the directory DRUPAL ROOT . '/ui/components'.
 */
function hook_components_directory_alter(&$module_directories) {
  $module_directories['ui_components'] = DRUPAL_ROOT . '/ui';
}

/**
 * Get the path to the external components.
 *
 * For example, components from an external pattern library could be deployed
 * at the path 'ui/components'.
 */
function hook_components_path() {
  return 'ui';
}

/**
 * Allows to alter/add ui component into registry.
 *
 * For example, allowing to add alias of a UI component.
 */
function hook_ui_component_registry_alter(&$theme_registry) {
  // Provide component variation for feedback message.
  $theme_registry['ui_disruption_message'] = $theme_registry['ui_feedback_msg'];
}

/**
 * @} End of "addtogroup hooks".
 */

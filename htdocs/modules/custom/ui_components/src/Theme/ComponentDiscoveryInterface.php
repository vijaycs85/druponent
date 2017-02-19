<?php

namespace Drupal\ui_components\Theme;

/**
 * ComponentDiscoveryInterface.
 */
interface ComponentDiscoveryInterface {

  /**
   * Get base components.
   *
   * Components where "extends: false" (or equivalent: no "extends" key at all),
   * without extensions applied.
   *
   * @return array
   *   Base components.
   */
  public function getBaseComponents();

  /**
   * Get all components, with extensions applied.
   *
   * @return array
   *   Components.
   */
  public function getComponents();

  /**
   * Get asset library for component.
   *
   * @param int $component_id
   *   Component id.
   *
   * @return string
   *   Asset library.
   */
  public function getAssetLibraryForComponent($component_id);


  /**
   * Get pattern library path.
   *
   * @return string
   *   Path
   */
  public function getPatternLibraryPath();

}

<?php

namespace Drupal\ui_components\Theme;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\ui_components\Plugin\Discovery\DirectoryWithYamlDiscovery;
use Drupal\Core\Theme\ActiveTheme;

/**
 * Discovers components as defined by modules, themes and a pattern library.
 *
 * 1. Components.
 *
 * - are uniquely identified by their name/ID across all themes and modules
 * - themes can extend components: add CSS/JS, add more variables, change the
 *   Twig template
 * - can be defined by both modules and themes (but theme-defined components
 *   cannot be used by modules)
 *
 * Implementation details:
 * - The component definition as specified by the module or base theme providing
 *   the original component is called the "base component".
 * - Components with associated CSS/JS automatically have asset libraries
 *   generated, one per component definition. Dependencies are added
 *   automatically, so you end up with for example a "label" component in Classy
 *   which has a "classy/label" asset library that depends on the "system/label
 * - Module-defined components with associated CSS automatically get the SMACSS
 *   category 'component'.
 * - Theme-defined components with associated CSS automatically get the SMACSS
 *   category 'theme'.
 * - The 'extends' YAML key defaults to FALSE in modules and defaults to TRUE in
 *   themes. (Because themes usually extend module-defined components.)
 *
 *
 * 2. Directory structure and theme-based extending
 *
 * The component name/ID is seen in its directory name, and the corresponding
 * YAML and Twig template. In other words, this is the layout:
 *
 * <extension> (module, theme or root of pattern library)
 *  |- components
 *     |- <component name>
 *        |- <component name>.yml
 *        |- <component name>.twig
 *
 * Concrete example:
 *
 * core/modules/system
 *  |- components
 *     |- label
 *        |- label.yml
 *        |- label.html.twig
 * cores/themes/classy
 *  |- components
 *     |- label
 *        |- label.yml
 *        |- label.html.twig
 * themes/fancy
 *  |- components
 *     |- label
 *        |- label.yml
 *        |- label.html.twig
 * ui/
 *  |- components
 *     |- label
 *        |- label.yml
 *        |- label.html.twig
 *
 * In this example, the custom 'fancy' theme builds on the 'classy' base theme.
 *
 * The component extension (inheritance) tree:
 *  1. module (e.g. 'mymodule')
 *  2. ancestor base theme (e.g. 'stable')
 *  3. parent base theme (e.g. 'classy')
 *  4. theme (e.g. 'fancy')
 *  5. pattern library @todo check inheritance
 *
 * Both the 'classy' and 'fancy' themes:
 * - extend the 'label' component defined by Drupal core's 'system'
 * - add additional CSS in the YAML file
 * - add an additional variable in the YAML file
 * - add more classes in the Twig file
 *
 * The end result is a single 'label' component:
 * - with three asset libraries: 'fancy/label', which depends on 'classy/label',
 *   which depends on 'system/label'
 * - with the variables defined in the system module component definition, plus
 *   the one in 'classy' appended, plus the one in 'fancy' appended
 */
class ComponentDiscovery implements ComponentDiscoveryInterface {

  /**
   * The app root.
   *
   * @var string
   */
  protected $root;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructor.
   *
   * @param string $root
   *   The app root.
   * @param ModuleHandlerInterface $module_handler
   *   Module handler.
   * @param ThemeHandlerInterface $theme_handler
   *   Theme handler.
   */
  public function __construct($root, ModuleHandlerInterface $module_handler, ThemeHandlerInterface $theme_handler) {
    $this->root = $root;
    $this->moduleHandler = $module_handler;
    $this->themeHandler = \Drupal::service('theme_handler');
    $this->themeManager = \Drupal::service('theme.manager');
    $this->config = \Drupal::config('ui_components.settings');
  }

  /**
   * Get active theme.
   *
   * Note that the active theme can change mid-request! This is how the pattern.
   * Library is able to work: it temporarily sets the active theme to 'stark'.
   *
   * @return string
   *   Active theme
   */
  protected function getActiveTheme() {
    return $this->themeManager->getActiveTheme();
  }

  /**
   * {@inheritdoc}
   */
  public function getBaseComponents() {
    $base_components = array_filter($this->getModuleComponents(), 'static::isModuleBaseComponent');
    foreach ($this->getThemeComponents() as $theme => $theme_components) {
      $theme_base_components = array_filter($theme_components, 'static::isThemeBaseComponent');

      // Extension-level validation.
      $intersection = array_intersect_key($base_components, $theme_base_components);
      if (empty($intersection)) {
        $base_components += $theme_base_components;
      }
      else {
        throw new \LogicException('Theme ' . $theme . ' is redefining components ' . implode(', ', array_keys($intersection)) . '.');
      }
    }
    return $base_components;
  }

  /**
   * {@inheritdoc}
   */
  public function getComponents() {
    $components = $this->getModuleComponents();
    foreach ($this->getThemeComponents() as $theme => $theme_components) {
      foreach ($theme_components as $id => $theme_component) {
        // Defining new component.
        if ($this->isThemeBaseComponent($theme_component)) {
          if (isset($components[$id])) {
            throw new \LogicException('Theme ' . $theme . ' is redefining component ' . $id . '.');
          }
          $components[$id] = $theme_component;
        }
        // Extending existing component.
        else {
          $component = &$components[$id];

          // Adding more variables is allowed.
          $component['variables'] = array_merge($component['variables'], $theme_component['variables']);

          // Adding more assets is allowed.
          if (count($theme_component['_asset_libraries'])) {
            // Update the theme component's asset library to depend on the
            // asset.
            if (count($component['_asset_libraries'])) {
              $library = &$theme_component['_asset_libraries'][$theme_component['provider'] . '/' . $id];
              $library['dependencies'] = array_merge($library['dependencies'], array_keys($component['_asset_libraries']));
            }
            $component['_asset_libraries'] = array_merge($component['_asset_libraries'], $theme_component['_asset_libraries']);
          }

          // Anything else is disallowed.
          $disallowed_keys = ['label', 'documentation'];
          foreach ($disallowed_keys as $disallowed_key) {
            if (isset($theme_component[$disallowed_key])) {
              throw new \LogicException('Theme ' . $theme . ' is trying to override the key ' . $disallowed_key . ' for the component ' . $id . ', this is not allowed.');
            }
          }

          // Update provider, and track the tree of providers extending
          // component.
          $component['_provider tree'][$theme_component['provider']] = $theme_component;
        }
      }
    }
    return $components;
  }

  /**
   * {@inheritdoc}
   */
  public function getAssetLibraryForComponent($component_id) {
    $components = $this->getComponents();
    if (isset($components[$component_id])) {
      $component = $components[$component_id];
      if (isset($component['_asset_libraries']) && is_array($component['_asset_libraries'])) {
        $asset_libraries = array_keys($component['_asset_libraries']);
        return end($asset_libraries);
      }
    }
    return '';
  }

  /**
   * Get module components.
   *
   * Components keyed by module. Horizontal extension.
   *
   * @array
   *   Components
   */
  protected function getModuleComponents() {
    return $this->normalizeComponents($this->getModulesDiscovery()->getDefinitions(), 'module');
  }

  /**
   * Get theme components.
   *
   * Components keyed by theme. Vertical extension.
   *
   * @return array
   *   Components
   */
  protected function getThemeComponents() {
    $components_by_theme = [];
    foreach ($this->getThemesDiscoveries() as $theme => $discovery) {
      $components_by_theme[$theme] = $this->normalizeComponents($discovery->getDefinitions(), 'theme');
    }
    return $components_by_theme;
  }

  /**
   * Sets defaults and generates asset libraries.
   *
   * @param array $components
   *   Components.
   * @param string $provider_type
   *   Provider type.
   *
   * @return array
   *   Components
   */
  protected function normalizeComponents(array $components, $provider_type) {
    foreach ($components as $id => &$component) {
      $this->validateComponent($component);

      // Set defaults.
      if (!isset($component['variables'])) {
        $component['variables'] = [];
      }

      $component['_theme_id'] = isset($component['prefix']) ? $component['prefix'] . "_" . $id : $id;

      // Generate initial provider tree.
      if (!isset($component['_provider tree'])) {
        $component['_provider tree'] = [$component['provider'] => $component];
      }

      // Generate asset library.
      $component['_asset_libraries'] = [];
      if (isset($component['assets']) && (isset($component['assets']['css']) || isset($component['assets']['js']) || isset($component['assets']['dependencies']))) {
        $library_name = $component['provider'] . '/' . $id;

        $library = [
          'version' => 'VERSION',
        ];
        if (isset($component['assets']['css'])) {
          $smacss_category = ($provider_type === 'module') ? 'component' : 'theme';
          $library['css'][$smacss_category] = $component['assets']['css'];
        }
        $library['js'] = isset($component['assets']['js']) ? $component['assets']['js'] : [];
        $library['dependencies'] = isset($component['assets']['dependencies']) ? $component['assets']['dependencies'] : [];

        $component['_asset_libraries'][$library_name] = $library;
      }
    }
    return $components;
  }

  /**
   * Validate component.
   *
   * @param array $component_definition
   *   Component definition.
   */
  protected function validateComponent(array $component_definition) {
    // @todo throw exceptions for anything that is invalid in this component's
    //   definition — in other words: YAML validation, config schema-style.
    //  Also verify that any additional variables added by component
    //  extensions have default values specified, otherwise they can break
    //  existing code.
  }

  /**
   * Check if component is a module base component.
   *
   * In modules, 'extends' defaults to false.
   *
   * @param array $component_definition
   *   Component definition.
   *
   * @return bool
   *   TRUE if component is a module base component
   */
  protected static function isModuleBaseComponent(array $component_definition) {
    return !isset($component_definition['extends']) || $component_definition['extends'] === FALSE;
  }

  /**
   * Check if component is a theme base component.
   *
   * In modules, 'extends' defaults to true.
   *
   * @param array $component_definition
   *   Component definition.
   *
   * @return bool
   *   TRUE if component is a theme base component
   */
  protected static function isThemeBaseComponent(array $component_definition) {
    return isset($component_definition['extends']) && $component_definition['extends'] === FALSE;
  }

  /**
   * Get component directories from installed modules.
   *
   * @return array
   *   Directories.
   */
  protected function getModulesDiscovery() {
    $module_directories = $this->moduleHandler->getModuleDirectories();
    $this->moduleHandler->alter('components_directory', $module_directories);
    return new DirectoryWithYamlDiscovery($module_directories, 'components');
  }


  /**
   * {@inheritdoc}
   */
  public function getPatternLibraryPath() {
    return $this->config->get('pattern_library_path');
  }

  /**
   * List of themes, from least to most specific.
   *
   * @return array
   *   Themes
   */
  protected function getOrderedThemes() {
    return array_merge(array_reverse($this->getActiveTheme()->getBaseThemes()), [$this->getActiveTheme()->getName() => $this->getActiveTheme()]);
  }

  /**
   * Get component directories from a theme.
   */
  protected function getThemeDiscovery(ActiveTheme $theme) {
    return new DirectoryWithYamlDiscovery([$theme->getName() => $this->root . '/' . $theme->getPath()], 'components');
  }

  /**
   * Get component directories from all themes.
   *
   * @return array
   *   Directories.
   */
  protected function getThemesDiscoveries() {
    return array_map([$this, 'getThemeDiscovery'], $this->getOrderedThemes());
  }

}

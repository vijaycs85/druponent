<?php
/**
 * @file
 * Drupal\ui_components\Render\Renderer.php.
 */

namespace Drupal\ui_components\Render;

use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Render\Element;
use Drupal\Core\Render\Markup;
use Drupal\Core\Render\RendererInterface;
use Drupal\Component\Render\MarkupInterface;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableMetadata;

/**
 * {@inheritdoc}
 */
class Renderer extends \Drupal\Core\Render\Renderer implements RendererInterface {

  /**
   * {@inheritdoc}
   */
  protected function doRender(&$elements, $is_root_call = FALSE) {
    if (empty($elements)) {
      return '';
    }

    if (!isset($elements['#access']) && isset($elements['#access_callback'])) {
      if (is_string($elements['#access_callback']) && strpos($elements['#access_callback'], '::') === FALSE) {
        $elements['#access_callback'] = $this->controllerResolver->getControllerFromDefinition($elements['#access_callback']);
      }
      $elements['#access'] = call_user_func($elements['#access_callback'], $elements);
    }

    // Early-return nothing if user does not have access.
    if (isset($elements['#access'])) {
      // If #access is an AccessResultInterface object, we must apply it's
      // cacheability metadata to the render array.
      if ($elements['#access'] instanceof AccessResultInterface) {
        $this->addCacheableDependency($elements, $elements['#access']);
        if (!$elements['#access']->isAllowed()) {
          return '';
        }
      }
      elseif ($elements['#access'] === FALSE) {
        return '';
      }
    }

    // Do not print elements twice.
    if (!empty($elements['#printed'])) {
      return '';
    }

    $context = $this->getCurrentRenderContext();
    if (!isset($context)) {
      throw new \LogicException("Render context is empty, because render() was called outside of a renderRoot() or renderPlain() call. Use renderPlain()/renderRoot() or #lazy_builder/#pre_render instead.");
    }
    $context->push(new BubbleableMetadata());

    // Set the bubbleable rendering metadata that has configurable defaults, if:
    // - this is the root call, to ensure that the final render array definitely
    //   has these configurable defaults, even when no subtree is render cached.
    // - this is a render cacheable subtree, to ensure that the cached data has
    //   the configurable defaults (which may affect the ID and invalidation).
    if ($is_root_call || isset($elements['#cache']['keys'])) {
      $required_cache_contexts = $this->rendererConfig['required_cache_contexts'];
      if (isset($elements['#cache']['contexts'])) {
        $elements['#cache']['contexts'] = Cache::mergeContexts($elements['#cache']['contexts'], $required_cache_contexts);
      }
      else {
        $elements['#cache']['contexts'] = $required_cache_contexts;
      }
    }

    // Try to fetch the prerendered element from cache, replace any placeholders
    // and return the final markup.
    if (isset($elements['#cache']['keys'])) {
      $cached_element = $this->renderCache->get($elements);
      if ($cached_element !== FALSE) {
        $elements = $cached_element;
        // Only when we're in a root (non-recursive) Renderer::render() call,
        // placeholders must be processed, to prevent breaking the render cache
        // in case of nested elements with #cache set.
        if ($is_root_call) {
          $this->replacePlaceholders($elements);
        }
        // Mark the element markup as safe if is it a string.
        if (is_string($elements['#markup'])) {
          $elements['#markup'] = Markup::create($elements['#markup']);
        }
        // The render cache item contains all the bubbleable rendering metadata
        // for the subtree.
        $context->update($elements);
        // Render cache hit, so rendering is finished, all necessary info
        // collected!
        $context->bubble();
        return $elements['#markup'];
      }
    }
    // Two-tier caching: track pre-bubbling elements' #cache, #lazy_builder and
    // #create_placeholder for later comparison.
    // @see \Drupal\Core\Render\RenderCacheInterface::get()
    // @see \Drupal\Core\Render\RenderCacheInterface::set()
    $pre_bubbling_elements = array_intersect_key($elements, [
      '#cache' => TRUE,
      '#lazy_builder' => TRUE,
      '#create_placeholder' => TRUE,
    ]);

    // @todo Remove the #type fallback once no @RenderElements exist anymore
    // @todo Remove the #theme fallback once hook_theme() no longer exists
    if (isset($elements['#component'])) {
      $component_id = $elements['#component'];
      $bc_component_name = str_replace('-', '_', $component_id);
      $type_or_theme = count($this->elementInfo->getInfo($bc_component_name)) > 1 ? '#type' : '#theme';
      $elements[$type_or_theme] = $bc_component_name;

      // @todo Reinsert call to validateComponent().

      // Attach this component's asset library.
      $asset_library = \Drupal::service('theme_component_discovery')->getAssetLibraryForComponent($component_id);
      if ($asset_library) {
        $elements['#attached']['library'][] = $asset_library;
      }
    }

    // If the default values for this element have not been loaded yet, populate
    // them.
    if (isset($elements['#type']) && empty($elements['#defaults_loaded'])) {
      $elements += $this->elementInfo->getInfo($elements['#type']);
    }

    // First validate the usage of #lazy_builder; both of the next if-statements
    // use it if available.
    if (isset($elements['#lazy_builder'])) {
      // @todo Convert to assertions once https://www.drupal.org/node/2408013
      //   lands.
      if (!is_array($elements['#lazy_builder'])) {
        throw new \DomainException('The #lazy_builder property must have an array as a value.');
      }
      if (count($elements['#lazy_builder']) !== 2) {
        throw new \DomainException('The #lazy_builder property must have an array as a value, containing two values: the callback, and the arguments for the callback.');
      }
      if (count($elements['#lazy_builder'][1]) !== count(array_filter($elements['#lazy_builder'][1], function($v) {
        return is_null($v) || is_scalar($v);
      }))) {
        throw new \DomainException("A #lazy_builder callback's context may only contain scalar values or NULL.");
      }
      $children = Element::children($elements);
      if ($children) {
        throw new \DomainException(sprintf('When a #lazy_builder callback is specified, no children can exist; all children must be generated by the #lazy_builder callback. You specified the following children: %s.', implode(', ', $children)));
      }
      $supported_keys = [
        '#lazy_builder',
        '#cache',
        '#create_placeholder',
        // These keys are not actually supported, but they are added
        // automatically by the Renderer, so we don't crash on them;
        // them being missing when their #lazy_builder callback is
        // invoked won't surprise the developer.
        '#weight',
        '#printed',
      ];
      $unsupported_keys = array_diff(array_keys($elements), $supported_keys);
      if (count($unsupported_keys)) {
        throw new \DomainException(sprintf('When a #lazy_builder callback is specified, no properties can exist; all properties must be generated by the #lazy_builder callback. You specified the following properties: %s.', implode(', ', $unsupported_keys)));
      }
    }
    // Determine whether to do auto-placeholdering.
    if ($this->placeholderGenerator->canCreatePlaceholder($elements) && $this->placeholderGenerator->shouldAutomaticallyPlaceholder($elements)) {
      $elements['#create_placeholder'] = TRUE;
    }
    // If instructed to create a placeholder, and a #lazy_builder callback is
    // present (without such a callback, it would be impossible to replace the
    // placeholder), replace the current element with a placeholder.
    if (isset($elements['#create_placeholder']) && $elements['#create_placeholder'] === TRUE) {
      if (!isset($elements['#lazy_builder'])) {
        throw new \LogicException('When #create_placeholder is set, a #lazy_builder callback must be present as well.');
      }
      $elements = $this->placeholderGenerator->createPlaceholder($elements);
    }
    // Build the element if it is still empty.
    if (isset($elements['#lazy_builder'])) {
      $callable = $elements['#lazy_builder'][0];
      $args = $elements['#lazy_builder'][1];
      if (is_string($callable) && strpos($callable, '::') === FALSE) {
        $callable = $this->controllerResolver->getControllerFromDefinition($callable);
      }
      $new_elements = call_user_func_array($callable, $args);
      // Retain the original cacheability metadata, plus cache keys.
      CacheableMetadata::createFromRenderArray($elements)
        ->merge(CacheableMetadata::createFromRenderArray($new_elements))
        ->applyTo($new_elements);
      if (isset($elements['#cache']['keys'])) {
        $new_elements['#cache']['keys'] = $elements['#cache']['keys'];
      }
      $elements = $new_elements;
      $elements['#lazy_builder_built'] = TRUE;
    }

    // All render elements support #markup and #plain_text.
    if (!empty($elements['#markup']) || !empty($elements['#plain_text'])) {
      $elements = $this->ensureMarkupIsSafe($elements);
    }

    // Make any final changes to the element before it is rendered. This means
    // that the $element or the children can be altered or corrected before the
    // element is rendered into the final text.
    if (isset($elements['#pre_render'])) {
      foreach ($elements['#pre_render'] as $callable) {
        if (is_string($callable) && strpos($callable, '::') === FALSE) {
          $callable = $this->controllerResolver->getControllerFromDefinition($callable);
        }
        $elements = call_user_func($callable, $elements);
      }
    }

    // Defaults for bubbleable rendering metadata.
    $elements['#cache']['tags'] = isset($elements['#cache']['tags']) ? $elements['#cache']['tags'] : array();
    $elements['#cache']['max-age'] = isset($elements['#cache']['max-age']) ? $elements['#cache']['max-age'] : Cache::PERMANENT;
    $elements['#attached'] = isset($elements['#attached']) ? $elements['#attached'] : array();

    // Allow #pre_render to abort rendering.
    if (!empty($elements['#printed'])) {
      // The #printed element contains all the bubbleable rendering metadata for
      // the subtree.
      $context->update($elements);
      // #printed, so rendering is finished, all necessary info collected!
      $context->bubble();
      return '';
    }

    // Add any JavaScript state information associated with the element.
    if (!empty($elements['#states'])) {
      drupal_process_states($elements);
    }

    // Get the children of the element, sorted by weight.
    $children = Element::children($elements, TRUE);

    // Initialize this element's #children, unless a #pre_render callback
    // already preset #children.
    if (!isset($elements['#children'])) {
      $elements['#children'] = '';
    }

    // Assume that if #theme is set it represents an implemented hook.
    $theme_is_implemented = isset($elements['#theme']);
    // Check the elements for insecure HTML and pass through sanitization.
    if (isset($elements)) {
      $markup_keys = array(
        '#description',
        '#field_prefix',
        '#field_suffix',
      );
      foreach ($markup_keys as $key) {
        if (!empty($elements[$key]) && is_scalar($elements[$key])) {
          $elements[$key] = $this->xssFilterAdminIfUnsafe($elements[$key]);
        }
      }
    }

    // Call the element's #theme function if it is set. Then any children of the
    // element have to be rendered there. If the internal #render_children
    // property is set, do not call the #theme function to prevent infinite
    // recursion.
    if ($theme_is_implemented && !isset($elements['#render_children'])) {
      $elements['#children'] = $this->theme->render($elements['#theme'], $elements);

      // If ThemeManagerInterface::render() returns FALSE this means that the
      // hook in #theme was not found in the registry and so we need to update
      // our flag accordingly. This is common for theme suggestions.
      $theme_is_implemented = ($elements['#children'] !== FALSE);
    }

    // If #theme is not implemented or #render_children is set and the element
    // has an empty #children attribute, render the children now. This is the
    // same process as Renderer::render() but is inlined for speed.
    if ((!$theme_is_implemented || isset($elements['#render_children'])) && empty($elements['#children'])) {
      foreach ($children as $key) {
        $elements['#children'] .= $this->doRender($elements[$key]);
      }
      $elements['#children'] = Markup::create($elements['#children']);
    }

    // If #theme is not implemented and the element has raw #markup as a
    // fallback, prepend the content in #markup to #children. In this case
    // #children will contain whatever is provided by #pre_render prepended to
    // what is rendered recursively above. If #theme is implemented then it is
    // the responsibility of that theme implementation to render #markup if
    // required. Eventually #theme_wrappers will expect both #markup and
    // #children to be a single string as #children.
    if (!$theme_is_implemented && isset($elements['#markup'])) {
      $elements['#children'] = Markup::create($elements['#markup'] . $elements['#children']);
    }

    // Let the theme functions in #theme_wrappers add markup around the rendered
    // children.
    // #states and #attached have to be processed before #theme_wrappers,
    // because the #type 'page' render array from drupal_prepare_page() would
    // render the $page and wrap it into the html.html.twig template without the
    // attached assets otherwise.
    // If the internal #render_children property is set, do not call the
    // #theme_wrappers function(s) to prevent infinite recursion.
    if (isset($elements['#theme_wrappers']) && !isset($elements['#render_children'])) {
      foreach ($elements['#theme_wrappers'] as $key => $value) {
        // If the value of a #theme_wrappers item is an array then the theme
        // hook is found in the key of the item and the value contains attribute
        // overrides. Attribute overrides replace key/value pairs in $elements
        // for only this ThemeManagerInterface::render() call. This allows
        // #theme hooks and #theme_wrappers hooks to share variable names
        // without conflict or ambiguity.
        $wrapper_elements = $elements;
        if (is_string($key)) {
          $wrapper_hook = $key;
          foreach ($value as $attribute => $override) {
            $wrapper_elements[$attribute] = $override;
          }
        }
        else {
          $wrapper_hook = $value;
        }

        $elements['#children'] = $this->theme->render($wrapper_hook, $wrapper_elements);
      }
    }

    // Filter the outputted content and make any last changes before the content
    // is sent to the browser. The changes are made on $content which allows the
    // outputted text to be filtered.
    if (isset($elements['#post_render'])) {
      foreach ($elements['#post_render'] as $callable) {
        if (is_string($callable) && strpos($callable, '::') === FALSE) {
          $callable = $this->controllerResolver->getControllerFromDefinition($callable);
        }
        $elements['#children'] = call_user_func($callable, $elements['#children'], $elements);
      }
    }

    // We store the resulting output in $elements['#markup'], to be consistent
    // with how render cached output gets stored. This ensures that placeholder
    // replacement logic gets the same data to work with, no matter if #cache is
    // disabled, #cache is enabled, there is a cache hit or miss.
    $prefix = isset($elements['#prefix']) ? $this->xssFilterAdminIfUnsafe($elements['#prefix']) : '';
    $suffix = isset($elements['#suffix']) ? $this->xssFilterAdminIfUnsafe($elements['#suffix']) : '';

    $elements['#markup'] = Markup::create($prefix . $elements['#children'] . $suffix);

    // We've rendered this element (and its subtree!), now update the context.
    $context->update($elements);

    // Cache the processed element if both $pre_bubbling_elements and $elements
    // have the metadata necessary to generate a cache ID.
    if (isset($pre_bubbling_elements['#cache']['keys']) && isset($elements['#cache']['keys'])) {
      if ($pre_bubbling_elements['#cache']['keys'] !== $elements['#cache']['keys']) {
        throw new \LogicException('Cache keys may not be changed after initial setup. Use the contexts property instead to bubble additional metadata.');
      }
      $this->renderCache->set($elements, $pre_bubbling_elements);
      // Update the render context; the render cache implementation may update
      // the element, and it may have different bubbleable metadata now.
      // @see \Drupal\Core\Render\PlaceholderingRenderCache::set()
      $context->pop();
      $context->push(new BubbleableMetadata());
      $context->update($elements);
    }

    // Only when we're in a root (non-recursive) Renderer::render() call,
    // placeholders must be processed, to prevent breaking the render cache in
    // case of nested elements with #cache set.
    //
    // By running them here, we ensure that:
    // - they run when #cache is disabled,
    // - they run when #cache is enabled and there is a cache miss.
    // Only the case of a cache hit when #cache is enabled, is not handled here,
    // that is handled earlier in Renderer::render().
    if ($is_root_call) {
      $this->replacePlaceholders($elements);
      // @todo remove as part of https://www.drupal.org/node/2511330.
      if ($context->count() !== 1) {
        throw new \LogicException('A stray drupal_render() invocation with $is_root_call = TRUE is causing bubbling of attached assets to break.');
      }
    }

    // Rendering is finished, all necessary info collected!
    $context->bubble();

    $elements['#printed'] = TRUE;
    return $elements['#markup'];
  }

  /**
   * Validate component.
   *
   * @todo Stop this throwing errors all the time.
   *
   * @param array $elements
   *   Elements.
   */
  protected function validateComponent(array $elements) {
    $component_id = $elements['#component'];
    $components = \Drupal::service('theme_component_discovery')->getComponents();
    $component_variables = $components[$component_id]['variables'];

    $debug = \Drupal::service('twig')->isDebug();

    // Validate specified variables. No more mysterious keys in render arrays!
    // @todo Only do this when twig.config.debug === true?
    if ($debug) {
      $system_properties = [
        '#cache',
        '#type',
        '#theme',
        '#component',
        '#attached',
        '#defaults_loaded',
        '#printed',
      ];
      $component_properties = array_map(function ($v) {
        return '#' . $v;
      }, array_keys($component_variables));
      $allowed_properties = array_merge($system_properties, $component_properties);
      $disallowed_properties = array_diff(Element::properties($elements), $allowed_properties);
      if (!empty($disallowed_properties)) {
        throw new \LogicException('The following properties are not allowed on the ' . $elements['#component'] . ' component: ' . implode(', ', $disallowed_properties) . '.');
      }
    }

    // Validate variable values.
    // @todo Only do this when twig.config.debug === true?
    if ($debug) {
      foreach ($component_variables as $variable_name => $variable_definition) {
        // Every variable must be defined, except if it is one added by a theme;
        // in that case, we automatically inherit the default value.
        if (!isset($elements['#' . $variable_name])) {
          $base_component = reset($components[$component_id]['_provider tree']);
          if (isset($base_component['variables'][$variable_name])) {
            throw new \LogicException('Variable ' . $variable_name . ' is not specified.');
          }
          else {
            // Extensions that add new variables are required to have defaults.
            $elements['#' . $variable_name] = $components[$component_id]['variables'][$variable_name]['default'];
          }
        }

        $expected_type = $variable_definition['type'];
        $expected_type_is_array = strpos($expected_type, '[]') !== FALSE;
        if ($expected_type_is_array) {
          $expected_type_array_value = substr($expected_type, 0, -2);
        }

        if ($expected_type === 'component' || $expected_type === 'components') {
          $subtree = $elements['#' . $variable_name];
          // @todo Until we are able to migrate away from the old theme/render
          //   system, be very forgiving: accept any render array or Markup.
          if (is_array($subtree) || $subtree instanceof MarkupInterface) {
            continue;
          }
        }

        $type = gettype($elements['#' . $variable_name]);
        if ($expected_type_is_array) {
          if ($type !== 'array') {
            throw new \LogicException('Expected ' . $variable_name . ' to be of type ' . $expected_type . ', ' . $type . ' given.');
          }
          else {
            foreach ($elements['#' . $variable_name] as $index => $array_value) {
              $type_array_value = gettype($array_value);
              if ($type_array_value !== $expected_type_array_value) {
                throw new \LogicException('Expected ' . $variable_name . ' to be of type ' . $expected_type . ', but element ' . $index . ' in array is of type ' . $type . '.');
              }
            }
          }
        }
        else {
          if ($expected_type !== $type) {
            throw new \LogicException('Expected ' . $variable_name . ' to be of type ' . $expected_type . ', ' . $type . ' given.');
          }
        }
      }
    }
  }

}

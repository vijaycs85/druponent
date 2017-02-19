<?php
/**
 * @file
 * UiComponentsServiceProvider.
 */

namespace Drupal\ui_components;

use Drupal\Core\DependencyInjection\ServiceProviderInterface;
use Drupal\Core\DependencyInjection\ContainerBuilder;

/**
 * Class UiComponentsServiceProvider.
 */
class UiComponentsServiceProvider implements ServiceProviderInterface {
  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container) {
    // Override class for renderer service.
    $service = $container->getDefinition('renderer');
    $service->setClass(__NAMESPACE__ . '\Render\Renderer');

    // Override class for twig.loader.filesystem service.
    $twigLoader = $container->getDefinition('twig.loader.filesystem');
    $twigLoader->setClass(__NAMESPACE__ . '\Template\Loader\UiComponentsFileSystemLoader');

  }

}

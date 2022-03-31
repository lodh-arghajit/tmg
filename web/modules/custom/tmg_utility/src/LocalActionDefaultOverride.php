<?php

namespace Drupal\tmg_utility;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Menu\LocalActionDefault;
use Drupal\Core\Routing\RouteMatch;

/**
 * Provides a default implementation for local action plugins.
 */
class LocalActionDefaultOverride extends LocalActionDefault {

  /**
   * {@inheritdoc}
   */
  public function getTitle(Request $request = NULL) {
    // Subclasses may pull in the request or specific attributes as parameters.
    // The title from YAML file discovery may be a TranslatableMarkup object.
    $route = RouteMatch::createFromRequest( \Drupal::request());
    $variables = $this->getRouteParameters($route);
    $title = (string) $this->pluginDefinition['title'];
    if (!empty($variables['taxonomy_vocabulary'])) {
      $sub_title = $variables['taxonomy_vocabulary'];
      $title = "Add $sub_title";
    }
    return $title;
  }
}

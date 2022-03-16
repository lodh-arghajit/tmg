<?php

namespace Drupal\tmg_utility\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Subscriber for prplp routes.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  public function alterRoutes(RouteCollection $collection) {
    // Override controller for password reset submit action.
    if ($route = $collection->get('user.reset')) {
      $route->setDefault('_controller', '\Drupal\tmg_utility\Controller\PrlpController::prlpResetPassLogin');
    }
  }

}

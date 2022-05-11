<?php


namespace Drupal\tmg_utility\EventSubscriber;

use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\NestedArray;
use Drupal\node\Entity\Node;
use Drupal\views\ResultRow;
use Drupal\views_remote_data\Events\RemoteDataLoadEntitiesEvent;
use Drupal\views_remote_data\Events\RemoteDataQueryEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Test subscriber for populating values in test views.
 */
final class ViewsRemoteDataSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      RemoteDataQueryEvent::class => 'onQuery',
    ];
  }



  /**
   * Subscribes to populate the view results.
   *
   * @param \Drupal\views_remote_data\Events\RemoteDataQueryEvent $event
   *   The event.
   */
  public function onQuery(RemoteDataQueryEvent $event): void {
    $supported_bases = [
      'views_remote_data_tmw_api_partner',
    ];
    $base_tables = array_keys($event->getView()->getBaseTables());
    if (count(array_intersect($supported_bases, $base_tables)) > 0) {
      // Ensure cache tags can be bubbled.
      $event->addCacheTags(['views_remote_data_tmw_api_partner']);
      $module_handler = \Drupal::service('module_handler');
      $module_path = $module_handler->getModule('tmg_utility')->getPath();

      $fixture = Json::decode((string) file_get_contents($module_path . '/fixtures/simple.json'));


      foreach ($fixture['services'] as $item) {
        $event->addResult(new ResultRow($item));
      }
    }
  }
}

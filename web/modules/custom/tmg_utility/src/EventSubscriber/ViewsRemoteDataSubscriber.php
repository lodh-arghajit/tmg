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
    $flag = FALSE;
    foreach ($event->getView()->exposed_raw_input as $exposed_raw_input) {
      if ($exposed_raw_input) {
        $flag = TRUE;
      }
    }
    if (count(array_intersect($supported_bases, $base_tables)) > 0 && $flag) {
      // Ensure cache tags can be bubbled.
      $event->addCacheTags(['views_remote_data_tmw_api_partner']);
      $module_handler = \Drupal::service('module_handler');
      $module_path = $module_handler->getModule('tmg_utility')->getPath();
      $fixture = json_decode((string) file_get_contents($module_path . '/fixtures/simple.json'), TRUE);

      $term_condition = "";
      if ($fixture["terms & conditions"]) {
        $term_condition = implode(" ", $fixture["terms & conditions"]);
      }
      foreach ($fixture['services'] as $item) {
        $item["term_condition"] = $term_condition;
        $event->addResult(new ResultRow($item));
      }
    }
  }
}

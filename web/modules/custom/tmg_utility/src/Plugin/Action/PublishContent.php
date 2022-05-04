<?php

namespace Drupal\tmg_utility\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\views_bulk_operations\Action\ViewsBulkOperationsActionBase;

/**
 * Approve a user.
 *
 * @Action(
 *   id = "tm_admin_publish_content",
 *   label = @Translation("TM publish content"),
 *   type = "node",
 * )
 */
class PublishContent extends ViewsBulkOperationsActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute($node = NULL) {
    // Skip unblocking user if they are already unblocked.
    if ($node !== FALSE) {
      $node->setPublished();

      $node->save();

    }
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    /** @var \Drupal\user\UserInterface $object */

    return $account->hasPermission('TMW admin');

  }

}
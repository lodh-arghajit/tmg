<?php

namespace Drupal\tmg_utility\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\views_bulk_operations\Action\ViewsBulkOperationsActionBase;

/**
 * Reject a user.
 *
 * @Action(
 *   id = "tmw_user_unblock_action",
 *   label = @Translation("TMW Unblock user"),
 *   type = "user"
 * )
 */
class UnblockUser extends ViewsBulkOperationsActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute($account = NULL) {
    // Skip blocking user if they are already blocked.
    if ($account !== FALSE) {

      // For efficiency manually save the original account before applying any
      // changes.
      $account->activate();
      $account->save();
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

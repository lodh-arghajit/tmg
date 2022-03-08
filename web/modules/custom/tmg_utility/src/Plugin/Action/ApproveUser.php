<?php

namespace Drupal\tmg_utility\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\views_bulk_operations\Action\ViewsBulkOperationsActionBase;

/**
 * Approve a user.
 *
 * @Action(
 *   id = "user_approve_user_action",
 *   label = @Translation("Approve user"),
 *   type = "user"
 * )
 */
class ApproveUser extends ViewsBulkOperationsActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute($account = NULL) {
    // Skip unblocking user if they are already unblocked.
    if ($account !== FALSE) {
      $account->set('field_approved_by_admin', '1');
      $account->save();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    /** @var \Drupal\user\UserInterface $object */
    $access = $object->status->access('edit', $account, TRUE)
      ->andIf($object->access('update', $account, TRUE));

    return $return_as_object ? $access : $access->isAllowed();
  }

}

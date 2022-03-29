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
 *   type = "user",
 * )
 */
class ApproveUser extends ViewsBulkOperationsActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute($account = NULL) {
    // Skip unblocking user if they are already unblocked.
    if ($account !== FALSE) {
      $account->set('field_admin_approved', '1');
      $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
      $account->set('field_approved_by', ['target_id' => $user->id()]);
      $request_time = \Drupal::time()->getCurrentTime();
      $account->set('field_approved_rejected_', $request_time);
      $account->save();
      _user_mail_notify('status_activated', $account);
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

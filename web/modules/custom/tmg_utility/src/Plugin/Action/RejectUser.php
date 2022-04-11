<?php

namespace Drupal\tmg_utility\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\views_bulk_operations\Action\ViewsBulkOperationsActionBase;

/**
 * Reject a user.
 *
 * @Action(
 *   id = "user_reject_action",
 *   label = @Translation("Reject user"),
 *   type = "user"
 * )
 */
class RejectUser extends ViewsBulkOperationsActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute($account = NULL) {
    // Skip blocking user if they are already blocked.
    if ($account !== FALSE) {

      // For efficiency manually save the original account before applying any
      // changes.
      $account->set('field_admin_approved', '2');
      $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
      $account->set('field_approved_by', ['target_id' => $user->id()]);
      $request_time = \Drupal::time()->getCurrentTime();
      $account->set('field_approved_rejected_', $request_time);
      $account->block();
      $account->save();
      _user_mail_notify('status_canceled', $account);
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

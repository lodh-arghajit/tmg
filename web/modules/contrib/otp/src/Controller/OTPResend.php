<?php

namespace Drupal\otp\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class OTPResend.
 *
 * Provides resend otp feature.
 *
 * @package Drupal\otp\Controller
 *
 * @ingroup otp
 */
class OTPResend extends ControllerBase {

  /**
   * The entity type manager.
   *
   * @var Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs an Controller object.
   *
   * @param Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entityTypeManager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('entity_type.manager'));
  }

  /**
   * Function to send otp.
   */
  public function sendOtp($uuid) {
    $user = $this->entityTypeManager->getStorage('user')->loadByProperties(['uuid' => $uuid]);
    $otp_send = _otp_generate_otp($user, TRUE);
    if (is_array($otp_send) && $otp_send['email_send']) {
      $this->messenger()->addMessage($this->t('A new verification code has been sent to %email', ['%email' => $user->mail]));
    }
    elseif (!$otp_send) {
      $this->messenger()->addError($this->t("You've reached the maximum number of attempts. Please contact our Customer Support Team at 888-557-6788 for assistance with creating your account."));
    }
    else {
      $this->messenger()->addError($this->t('Some error occurred in sending OTP'));
    }
    return new RedirectResponse('user/register/otp?u=' . $uuid);
  }

}

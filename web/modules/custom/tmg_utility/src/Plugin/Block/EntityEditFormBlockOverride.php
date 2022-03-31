<?php

namespace Drupal\tmg_utility\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\EntityOwnerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\entityform_block\Plugin\Block\EntityEditFormBlock;

/**
 * Provides a block for creating a new content entity.
 *
 * @Block(
 *   id = "entityform_block_override",
 *   admin_label = @Translation("Entity form override"),
 *   category = @Translation("Forms")
 * )
 */
class EntityEditFormBlockOverride extends EntityEditFormBlock {



  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array(
      'entity_type' => '',
      'bundle' => '',
      'entity_id' => '',
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    $entity = $this->entityTypeManager
      ->getStorage($this->configuration['entity_type'])
      ->load($this->configuration['entity_id']);
    return $this->entityTypeManager
      ->getAccessControlHandler($this->configuration['entity_type'])
      ->access($entity, 'update', $account, TRUE);
  }

    /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $form['entity_id'] = array(
      '#title' => t('Entity Id'),
      '#type' => 'textfield',
      '#required' => TRUE,
      '#default_value' => $this->configuration['entity_id'],
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $selected_entity_type_bundle = $form_state->getValue('entity_type_bundle');
    $entity_id = $form_state->getValue('entity_id');
    $values = explode('.', $selected_entity_type_bundle);
    $this->configuration['entity_type'] = $values[0];
    $this->configuration['bundle'] = $values[1];
    $this->configuration['entity_id'] = $entity_id;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $values = array();
    // Specify selected bundle if the entity has bundles.
    if ($this->entityTypeManager->getDefinition($this->configuration['entity_type'])->hasKey('bundle')) {
      $bundle_key = $this->entityTypeManager->getDefinition($this->configuration['entity_type'])->getKey('bundle');
      $values = array($bundle_key => $this->configuration['bundle']);
    }

    $entity = $this->entityTypeManager
      ->getStorage($this->configuration['entity_type'])
      ->load($this->configuration['entity_id']);



    $form = $this->entityTypeManager
      ->getFormObject($this->configuration['entity_type'], 'default')
      ->setEntity($entity);
    return \Drupal::formBuilder()->getForm($form);
  }
}

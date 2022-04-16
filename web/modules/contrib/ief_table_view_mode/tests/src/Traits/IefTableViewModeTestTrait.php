<?php

namespace Drupal\Tests\ief_table_view_mode\Traits;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Entity\Entity\EntityViewMode;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\ief_table_view_mode\Form\EntityInlineTableViewModeForm;
use Drupal\Tests\field\Traits\EntityReferenceTestTrait;

/**
 * Provides common functionality for the IEF table view mode test classes.
 */
trait IefTableViewModeTestTrait {

  use EntityReferenceTestTrait;

  /**
   * Create an entity reference field and configure it with the widget.
   *
   * @param string $entity_type_id
   *   The entity type id to which the field will be created.
   * @param string $bundle
   *   The bundle name to which the field will be attached.
   * @param string $field_name
   *   The field name of the new field.
   * @param string|null $target_type
   *   The entity type to reference.
   * @param array $target_bundles
   *   The bundles to reference.
   * @param string $form_mode
   *   The form mode to configure the widget.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function configureFieldAndWidget($entity_type_id, $bundle, $field_name, $target_type = NULL, array $target_bundles = [], $form_mode = 'default') {
    if (is_null($target_type)) {
      $target_type = $entity_type_id;
    }

    if (empty($target_bundles)) {
      $target_bundles[$bundle] = $bundle;
    }

    $this->createEntityReferenceField(
      $entity_type_id,
      $bundle,
      $field_name,
      'Reference field',
      $target_type,
      'default',
      ['target_bundles' => $target_bundles],
      FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED
    );

    $this->assert('pass', new FormattableMarkup('The field %field_name is created.', ['%field_name' => $field_name]));

    $this->configureWidget($entity_type_id, $bundle, $field_name, $form_mode);
  }

  /**
   * Configure a field with the widget of this module.
   *
   * @param string $entity_type_id
   *   The entity type id to configure.
   * @param string $bundle
   *   The bundle name to configure.
   * @param string $field_name
   *   The field name to configure.
   * @param string $form_mode
   *   The form mode to configure the widget.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function configureWidget($entity_type_id, $bundle, $field_name, $form_mode = 'default') {
    /** @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface $display_repository */
    $display_repository = \Drupal::service('entity_display.repository');
    $form_display = $display_repository->getFormDisplay($entity_type_id, $bundle);
    $component = $form_display->getComponent($field_name);
    $component['type'] = 'inline_entity_form_complex_table_view_mode';
    $display_repository->getFormDisplay($entity_type_id, $bundle)
      ->setComponent($field_name, $component)
      ->save();

    $this->assert('pass', new FormattableMarkup('The widget "Inline entity form - Complex - Table View Mode" is attached to the field %field_name.', ['%field_name' => $field_name]));
  }

  /**
   * Configured the view mode ief_table.
   *
   * @param string $entity_type_id
   *   The entity type id to configure.
   * @param string $bundle
   *   The bundle to configure.
   * @param array $components
   *   An array of component names to configure in the view mode.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function configureViewModeIef($entity_type_id, $bundle, array $components) {
    $ief_view_mode = $entity_type_id . '.' . EntityInlineTableViewModeForm::IEF_TABLE_VIEW_MODE_NAME;
    // Create the view mode ief_table if not exists.
    if (!EntityViewMode::load($ief_view_mode)) {
      $view_mode = EntityViewMode::create([
        'id' => $ief_view_mode,
        'label' => 'Inline Entity Form Table',
        'targetEntityType' => $entity_type_id,
      ]);
      $view_mode->save();
    }

    /** @var \Drupal\Core\Entity\Display\EntityDisplayInterface $display */
    if (!($display = EntityViewDisplay::load("{$entity_type_id}.{$bundle}." . EntityInlineTableViewModeForm::IEF_TABLE_VIEW_MODE_NAME))) {
      $display = EntityViewDisplay::create([
        'targetEntityType' => $entity_type_id,
        'bundle' => $bundle,
        'mode' => EntityInlineTableViewModeForm::IEF_TABLE_VIEW_MODE_NAME,
        'status' => TRUE,
      ]);
      $display->save();
    }

    $this->assertNotNull($display, 'Configure the view display ief table.');

    foreach ($display->getComponents() as $component_name => $component) {
      $display->removeComponent($component_name);
    }

    foreach ($components as $component_name) {
      $display->setComponent($component_name);
    }

    $display->save();

    $this->assert('pass', new FormattableMarkup('The view mode ief_table is configured with the follow components: (%components).', ['%components' => implode(', ', $components)]));
  }

}

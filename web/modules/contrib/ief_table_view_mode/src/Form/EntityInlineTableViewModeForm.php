<?php

namespace Drupal\ief_table_view_mode\Form;

use Drupal\inline_entity_form\Form\EntityInlineForm;
use Drupal\Core\Entity\Entity\EntityViewDisplay;

/**
 * Entity inline table view mode form handler.
 */
class EntityInlineTableViewModeForm extends EntityInlineForm {

  const IEF_TABLE_VIEW_MODE_NAME = 'ief_table';

  /**
   * {@inheritdoc}
   */
  public function getTableFields($bundles) {
    $fields = parent::getTableFields($bundles);
    $entity_type = $this->entityType->id();
    foreach ($bundles as $bundle) {
      $display = EntityViewDisplay::load($entity_type . '.' . $bundle . '.' . self::IEF_TABLE_VIEW_MODE_NAME);
      if (!$display || !$display->status()) {
        continue;
      }

      $old_fields = $fields;
      $fields = [];

      $field_definitions = $this->entityFieldManager->getFieldDefinitions($entity_type, $bundle);
      // Checking fields instances.
      foreach ($field_definitions as $field_name => $field_definition) {
        if (!$field_definition->isDisplayConfigurable('view')) {
          continue;
        }
        $display_options = $display->getComponent($field_name);
        if (empty($display_options)) {
          continue;
        }
        $fields[$field_name] = [
          'type' => 'field',
          'label' => $field_definition->getLabel(),
          'display_options' => $display_options,
          'weight' => $display_options['weight'],
        ];
      }

      // Default settings maybe has not registered any extra field.
      foreach ($old_fields as $old_field_name => $old_field) {
        if (isset($fields[$old_field_name])) {
          continue;
        }
        $display_options = $display->getComponent($old_field_name);
        if (empty($display_options)) {
          continue;
        }
        $fields[$old_field_name] = $old_field;
        $fields[$old_field_name]['weight'] = $display_options['weight'];
      }

      $extra_fields = $this->entityFieldManager->getExtraFields($entity_type, $bundle);
      $extra_fields = isset($extra_fields['display']) ? $extra_fields['display'] : [];

      foreach ($extra_fields as $extra_field_name => $extra_field) {
        $display_options = $display->getComponent($extra_field_name);
        if (empty($display_options)) {
          continue;
        }
        $fields[$extra_field_name] = [
          'type' => 'callback',
          'label' => $extra_field['label']->render(),
          'callback' => 'ief_table_view_mode_table_field_extra_field_callback',
          'callback_arguments' => [$extra_field_name],
          'weight' => $display_options['weight'],
        ];
      }
    }

    return $fields;
  }

}

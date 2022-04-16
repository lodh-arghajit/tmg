<?php

namespace Drupal\link_target\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\link\Plugin\Field\FieldWidget\LinkWidget;

/**
 * Plugin implementation of the 'link_target_field_widget' widget.
 *
 * @FieldWidget(
 *   id = "link_target_field_widget",
 *   label = @Translation("Link with target"),
 *   field_types = {
 *     "link"
 *   }
 * )
 */
class LinkTargetFieldWidget extends LinkWidget {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'available_targets' => [],
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);

    $element['available_targets'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t("Available Targets"),
      '#description' => $this->t('The enabled targets that are displayed in the field widget target selection dropdown. If none are selected, all will be available.'),
      '#default_value' => $this->getSetting('available_targets'),
      '#options' => $this->getTargets(),
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();
    $targets_conf = $this->getSelectedOptions();
    if ($targets_conf) {
      // Clear out any zeros saved for unchecked values.
      foreach ($targets_conf as $key => $val) {
        if (!$val) {
          unset($targets_conf[$key]);
        }
      }
      $summary[] = !empty($targets_conf) ? $this->t('Available targets:') . ' ' . implode(', ', $targets_conf) : $this->t('No target options were selected.');
    }
    else {
      $summary[] = $this->t('All targets will be available.');
    }
    return $summary;
  }

  /**
   * Helper function to provide an array of target options.
   *
   * @return array
   *   The options to use as targets
   */
  public static function getTargets() {
    return [
      '_self' => t('Current window (_self)'),
      '_blank' => t('New window (_blank)'),
      'parent' => t('Parent window (_parent)'),
      'top' => t('Topmost window (_top)'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    $item = $this->getLinkItem($items, $delta);
    $options = $item->get('options')->getValue();
    $targets_available = $this->getSelectedOptions(TRUE);

    $default_value = !empty($options['attributes']['target']) ? $options['attributes']['target'] : '';
    $element['options']['attributes']['target'] = [
      '#type' => 'select',
      '#title' => $this->t('Select a target'),
      '#options' => ['' => $this->t('- None -')] + $targets_available,
      '#default_value' => $default_value,
      '#description' => $this->t('Select a link behavior. <em>_self</em> will open the link in the current window. <em>_blank</em> will open the link in a new window or tab. <em>_parent</em> and <em>_top</em> will generally open in the same window or tab, but in some cases will open in a different window.'),
    ];
    return $element;
  }

  /**
   * Getting link items.
   *
   * @param \Drupal\Core\Field\FieldItemListInterface $items
   *   Returning of field items.
   * @param string $delta
   *   Returning field delta with item.
   *
   * @return \Drupal\link\LinkItemInterface
   *   Returning link items inteface.
   */
  private function getLinkItem(FieldItemListInterface $items, $delta) {
    return $items[$delta];
  }

  /**
   * Retrieve settings and convert to an array that includes on those selected.
   *
   * return array
   *   The options to include.
   */
  public function getSelectedOptions($default_all = FALSE) {
    $defaults = $this->getTargets();

    // If a subset of targets have been specified, use them.
    $targets_conf = $this->getSetting('available_targets');
    if ($targets_conf) {
      $targets_available = [];
      foreach ($targets_conf as $key) {
        if (isset($defaults[$key])) {
          $targets_available[$key] = $defaults[$key];
        }
      }
    }
    if (empty($targets_available) && $default_all) {
      $targets_available = $defaults;
    }
    return $targets_available;
  }

}

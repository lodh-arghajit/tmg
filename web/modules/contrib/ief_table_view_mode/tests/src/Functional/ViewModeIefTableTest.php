<?php

namespace Drupal\Tests\ief_table_view_mode\Functional;

use Drupal\Core\Entity\Entity\EntityViewMode;
use Drupal\ief_table_view_mode\Form\EntityInlineTableViewModeForm;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\ief_table_view_mode\Traits\IefTableViewModeTestTrait;

/**
 * Test the view mode ief_table.
 *
 * @group ief_table_view_mode
 */
class ViewModeIefTableTest extends BrowserTestBase {

  use IefTableViewModeTestTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'field',
    'node',
    'ief_table_view_mode',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'classy';

  /**
   * Set a content type.
   */
  protected function setUp() {
    parent::setUp();

    $this->drupalCreateContentType(['type' => 'article']);
  }

  /**
   * Check the creation of the view mode ief_table automatically.
   */
  public function testViewMode() {
    $view_mode = EntityViewMode::load('node.' . EntityInlineTableViewModeForm::IEF_TABLE_VIEW_MODE_NAME);

    $this->assertNull($view_mode, 'The view mode Inline Entity Form Table not exists');

    $this->configureFieldAndWidget('node', 'article', 'field_reference');

    $view_mode = EntityViewMode::load('node.' . EntityInlineTableViewModeForm::IEF_TABLE_VIEW_MODE_NAME);

    $this->assertNotNull($view_mode, 'The view mode Inline Entity Form Table now exists');
  }

}

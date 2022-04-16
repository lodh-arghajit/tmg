<?php

namespace Drupal\Tests\ief_table_view_mode\FunctionalJavascript;

use Drupal\Tests\ief_table_view_mode\Traits\IefTableViewModeTestTrait;
use Drupal\Tests\inline_entity_form\FunctionalJavascript\InlineEntityFormTestBase;

/**
 * IEF complex table view mode field widget tests.
 *
 * @group ief_table_view_mode
 */
class ComplexTableViewModeWidgetTest extends InlineEntityFormTestBase {

  use IefTableViewModeTestTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'field',
    'node',
    'inline_entity_form_test',
    'ief_table_view_mode',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->user = $this->createUser([
      'create ief_reference_type content',
      'create ief_test_nested1 content',
      'create ief_test_nested2 content',
      'create ief_test_nested3 content',
      'edit any ief_reference_type content',
      'delete any ief_reference_type content',
      'create ief_test_complex content',
      'edit any ief_test_complex content',
      'delete any ief_test_complex content',
      'edit any ief_test_nested1 content',
      'edit any ief_test_nested2 content',
      'edit any ief_test_nested3 content',
      'view own unpublished content',
      'administer content types',
    ]);
    $this->drupalLogin($this->user);
  }

  /**
   * Test the widget behavior before and after configure the view mode ief.
   */
  public function testWidgetBehaviorBeforeAndAfterConfigureTheViewModeIef() {
    $first_title_field_xpath = $this->getXpathForNthInputByLabelText('Title', 1);
    $inner_title_field_xpath = $this->getXpathForNthInputByLabelText('Title', 2);
    $first_name_field_xpath = $this->getXpathForNthInputByLabelText('First name', 1);
    $last_name_field_xpath = $this->getXpathForNthInputByLabelText('Last name', 1);

    $assert_session = $this->assertSession();
    $page = $this->getSession()->getPage();

    $this->drupalGet('node/add/ief_test_complex');
    $assert_session->elementExists('xpath', $inner_title_field_xpath)->setValue('Some reference');
    $assert_session->elementExists('xpath', $first_name_field_xpath)->setValue('John');
    $assert_session->elementExists('xpath', $last_name_field_xpath)->setValue('Doe');
    $page->pressButton('Create node');
    $this->assertNotEmpty($assert_session->waitForElement('css', '.ief-row-entity'));

    // Tests if correct fields appear in the table by default.
    $assert_session->elementTextContains('css', '.ief-row-entity .inline-entity-form-node-label', 'Some reference');
    $assert_session->elementTextContains('css', '.ief-row-entity .inline-entity-form-node-status', 'Published');
    $assert_session->elementNotExists('css', '.ief-row-entity .inline-entity-form-node-first_name');

    // Make sure unrelated AJAX submit doesn't save the referenced entity.
    // @todo restore this test.
    // @see https://www.drupal.org/project/inline_entity_form/issues/3088453
    $assert_session->elementExists('xpath', $first_title_field_xpath)->setValue('Some title');
    $page->pressButton('Save');
    $assert_session->pageTextContains('IEF test complex Some title has been created.');

    // Change the widget to ief_table_view_mode.
    $this->configureWidget('node', 'ief_test_complex', 'multi');
    // Configure the view mode and show only the first_name field.
    $this->configureViewModeIef('node', 'ief_reference_type', ['first_name']);

    $node = $this->drupalGetNodeByTitle('Some title');
    $this->drupalGet('node/' . $node->id() . '/edit');

    $assert_session->elementNotExists('css', '.ief-row-entity .inline-entity-form-node-label');
    $assert_session->elementNotExists('css', '.ief-row-entity .inline-entity-form-node-status');
    $assert_session->elementTextContains('css', '.ief-row-entity .inline-entity-form-node-first_name', 'John');

    // Now show label, first name and last name fields.
    $this->configureViewModeIef('node', 'ief_reference_type', [
      'label',
      'first_name',
      'last_name',
    ]);

    $this->drupalGet('node/' . $node->id() . '/edit');
    $assert_session->elementTextContains('css', '.ief-row-entity .inline-entity-form-node-first_name', 'John');
    $assert_session->elementTextContains('css', '.ief-row-entity .inline-entity-form-node-last_name', 'Doe');
    $assert_session->elementTextContains('css', '.ief-row-entity .inline-entity-form-node-label', 'Some reference');

  }

}

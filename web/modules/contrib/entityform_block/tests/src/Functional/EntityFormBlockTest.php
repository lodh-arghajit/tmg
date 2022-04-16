<?php

namespace Drupal\Tests\entityform_block\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests the entity form blocks.
 *
 * @group entityform_block
 */
class EntityFormBlockTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'classy';

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array(
    'node',
    'block',
    'entityform_block',
    'taxonomy',
    'comment',
    'contact'
  );

  /**
   * Tests the entity form blocks.
   */
  public function testEntityFormBlock() {
    // Create article content type.
    $this->drupalCreateContentType(array('type' => 'article', 'name' => 'Article'));

    $admin_user = $this->drupalCreateUser(array(
      'administer blocks',
      'administer nodes',
      'administer site configuration',
      'create article content',
      'administer taxonomy',
      // Needed for create user form.
      // @todo Support register.
      'administer users',
    ));
    $this->drupalLogin($admin_user);

    // Add a content block with an entity form.
    $this->drupalGet('admin/structure/block/add/entityform_block/classy', ['query' => ['region' => 'content']]);

    // Assert that comments and personal form bundles are not displayed.
    $this->assertNoOption('edit-settings-entity-type-bundle', 'comment.comment');
    $this->assertNoOption('edit-settings-entity-type-bundle', 'contact_message.personal');

    $edit = array(
      'settings[entity_type_bundle]' => 'node.article',
    );
    $this->drupalPostForm(NULL, $edit, t('Save block'));

    $this->drupalGet('<front>');

    // Make sure the entity form is available.
    $this->assertSession()->pageTextContains('Entity form');
    $this->assertSession()->fieldExists('title[0][value]');
    $this->assertSession()->fieldExists('body[0][value]');
    $this->assertSession()->fieldExists('revision_log[0][value]');

    // Add a vocabulary.
    $edit = array(
      'name' => 'Vocabulary Tags',
      'vid' => 'vocabulary_tags',
    );
    $this->drupalPostForm('admin/structure/taxonomy/add', $edit, t('Save'), [], 'taxonomy-vocabulary-form');
    $this->assertSession()->pageTextContains('Created new vocabulary Vocabulary Tags.');

    // Add a form block for creating tags.
    $this->drupalGet('admin/structure/block/add/entityform_block/classy', ['query' => ['region' => 'content']]);
    $edit = array(
      'settings[entity_type_bundle]' => 'taxonomy_term.vocabulary_tags',
    );
    $this->drupalPostForm(NULL, $edit, t('Save block'));

    $this->drupalGet('<front>');

    // Make sure the vocabulary form is available.
    $this->assertSession()->fieldExists('name[0][value]');
    $this->assertSession()->fieldExists('description[0][value]');

    // Add a form block for users.
    $this->drupalGet('admin/structure/block/add/entityform_block/classy', ['query' => ['region' => 'content']]);
    $edit = array(
      'settings[entity_type_bundle]' => 'user.user',
    );
    $this->drupalPostForm(NULL, $edit, t('Save block'));

    $this->drupalGet('<front>');

    // Make sure the user form is available.
    $this->assertSession()->fieldExists('mail');
    $this->assertSession()->fieldExists('name');
    $this->assertSession()->fieldExists('pass[pass1]');
  }

}

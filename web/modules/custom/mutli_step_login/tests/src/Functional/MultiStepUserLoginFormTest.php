<?php

namespace Drupal\Tests\multi_step_login\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Ensure the multi step login functionality works as expected.
 *
 * @group user
 */
class MultiStepUserLoginFormTest extends BrowserTestBase {

  /**
   * Modules to install.
   *
   * @var array
   */
  public static $modules = ['multi_step'];

  /**
   * {@inheritdoc}
   */
  protected $path = 'multi-step-form';

  /**
   * {@inheritdoc}
   */
  public function testUserWillSeeOnlyUserNameOrEmailField() {
    $this->drupalGet($this->path);
    $this->assertResponse(200);

    $this->assertSession()->pageTextContains(t('Email and Username'));
    $this->assertSession()->buttonExists(t('Next'));

    // Ensure password field is not visible.
    $this->assertSession()->pageTextNotContains(t('Enter the password that accompanies your username'));
    $this->assertSession()->buttonNotExists(t('Log in'));
  }

  /**
   * {@inheritdoc}
   */
  public function testUserWillSeePasswordFieldOnlyIfUserNameIsValid() {
    $this->drupalGet($this->path);
    $user = $this->drupalCreateUser([]);
    $edit = ['combo' => $user->getAccountName()];
    $this->drupalPostForm(NULL, $edit, t('Next'));
    $this->assertUrl($this->path, [], 'Redirected to the correct URL');

    $this->assertSession()->pageTextContains(t('Enter the password that accompanies your username'));
    $this->assertSession()->buttonExists(t('Log in'));
  }

  /**
   * {@inheritdoc}
   */
  public function testUserWillRedirectToRegistrationPageIfUsernameOrEmailDoesNotExists() {
    $this->drupalGet($this->path);
    $edit = ['combo' => 'UserNameNotExists'];
    $this->drupalPostForm(NULL, $edit, t('Next'));
    $this->assertSession()->addressEquals('user/register');

    $this->assertSession()->pageTextNotContains(t('Enter the password that accompanies your username'));
    $this->assertSession()->buttonNotExists(t('Log in'));
  }

  /**
   * {@inheritdoc}
   */
  public function testUserCanLoginUsingMultiStepForm() {
    $destination = 'the-url-does-not-exists';
    $this->drupalGet($this->path, ['query' => ['destination' => $destination]]);
    $user = $this->drupalCreateUser([]);
    $form = ['combo' => $user->getAccountName()];
    $this->drupalPostForm(NULL, $form, t('Next'));
    $form = [];
    $form['pass'] = $user->passRaw;
    $this->drupalPostForm(NULL, $form, t('Log in'));
    $this->assertUrl($destination, [], 'Redirected to the correct URL');
  }

}

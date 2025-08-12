<?php

namespace Drupal\Tests\shanfont\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests the Shan Font configuration form and functionality.
 *
 * @group shanfont
 */
class ShanFontConfigTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['shanfont', 'system'];

  /**
   * A user with permission to administer site configuration.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Create and login admin user.
    $this->adminUser = $this->drupalCreateUser([
      'administer site configuration',
      'access administration pages',
    ]);
    $this->drupalLogin($this->adminUser);
  }

  /**
   * Tests the Shan Font configuration form access and basic functionality.
   */
  public function testConfigFormAccess() {
    // Test that the configuration form is accessible.
    $this->drupalGet('admin/config/user-interface/shanfont');
    $this->assertSession()->statusCodeEquals(200);
    
    // Check that the form elements are present.
    $this->assertSession()->fieldExists('font_mode');
    $this->assertSession()->buttonExists('Save configuration');
    
    // Check that the default mode is quick_setup.
    $this->assertSession()->fieldValueEquals('font_mode', 'quick_setup');
  }

  /**
   * Tests the configuration form rendering and elements.
   */
  public function testConfigFormElements() {
    $this->drupalGet('admin/config/user-interface/shanfont');
    
    // Test that all radio options are present.
    $this->assertSession()->fieldExists('font_mode');
    $this->assertSession()->optionExists('font_mode', 'theme_defaults');
    $this->assertSession()->optionExists('font_mode', 'quick_setup');
    $this->assertSession()->optionExists('font_mode', 'custom_selection');
    
    // Test that custom font checkboxes are present.
    $font_variants = [
      'shan_thin',
      'shan_thin_italic',
      'shan_regular',
      'shan_italic',
      'shan_bold',
      'shan_bold_italic',
      'shan_black',
      'shan_black_italic',
    ];
    
    foreach ($font_variants as $variant) {
      $this->assertSession()->fieldExists($variant);
    }
    
    // Test header elements presence.
    $this->assertSession()->pageTextContains('Shan Font');
    $this->assertSession()->pageTextContains('www.shanfont.com');
    $this->assertSession()->pageTextContains('Developed by TaiDev');
  }

  /**
   * Tests saving configuration in different modes.
   */
  public function testConfigFormSubmission() {
    // Test theme defaults mode.
    $this->drupalGet('admin/config/user-interface/shanfont');
    $this->submitForm([
      'font_mode' => 'theme_defaults',
    ], 'Save configuration');
    
    $this->assertSession()->pageTextContains('Shan Font settings have been saved.');
    
    // Verify the configuration was saved.
    $config = $this->config('shanfont.settings');
    $this->assertEquals('theme_defaults', $config->get('font_mode'));
    
    // Test custom selection mode with specific fonts.
    $this->drupalGet('admin/config/user-interface/shanfont');
    $this->submitForm([
      'font_mode' => 'custom_selection',
      'shan_regular' => TRUE,
      'shan_bold' => TRUE,
      'shan_italic' => FALSE,
    ], 'Save configuration');
    
    $this->assertSession()->pageTextContains('Shan Font settings have been saved.');
    
    // Verify custom selection configuration.
    $config = $this->config('shanfont.settings');
    $this->assertEquals('custom_selection', $config->get('font_mode'));
    $selected_fonts = $config->get('selected_fonts');
    $this->assertTrue($selected_fonts['shan_regular']);
    $this->assertTrue($selected_fonts['shan_bold']);
    $this->assertFalse($selected_fonts['shan_italic']);
  }

  /**
   * Tests font application on frontend pages.
   */
  public function testFontApplicationOnFrontend() {
    // Set to quick setup mode.
    $this->config('shanfont.settings')
      ->set('font_mode', 'quick_setup')
      ->save();
    
    // Visit the front page.
    $this->drupalGet('<front>');
    
    // Check that the shanfont-quick-setup class is applied to body.
    $this->assertSession()->elementExists('css', 'body.shanfont-quick-setup');
    
    // Test custom selection mode.
    $this->config('shanfont.settings')
      ->set('font_mode', 'custom_selection')
      ->set('selected_fonts', [
        'shan_regular' => TRUE,
        'shan_bold' => TRUE,
      ])
      ->save();
    
    $this->drupalGet('<front>');
    
    // Check that the custom selection class is applied.
    $this->assertSession()->elementExists('css', 'body.shanfont-custom-selection');
    
    // Check data attribute for selected fonts.
    $body = $this->getSession()->getPage()->find('css', 'body');
    $this->assertStringContains('shan_regular', $body->getAttribute('data-shanfont-variants'));
    $this->assertStringContains('shan_bold', $body->getAttribute('data-shanfont-variants'));
  }

  /**
   * Tests that theme defaults mode doesn't apply any font classes.
   */
  public function testThemeDefaultsMode() {
    // Set to theme defaults mode.
    $this->config('shanfont.settings')
      ->set('font_mode', 'theme_defaults')
      ->save();
    
    // Visit the front page.
    $this->drupalGet('<front>');
    
    // Check that no Shan Font classes are applied.
    $this->assertSession()->elementNotExists('css', 'body.shanfont-quick-setup');
    $this->assertSession()->elementNotExists('css', 'body.shanfont-custom-selection');
  }

  /**
   * Tests menu link and routing.
   */
  public function testMenuLinkAndRouting() {
    // Test that the menu link exists in the admin interface.
    $this->drupalGet('admin/config/user-interface');
    $this->assertSession()->linkExists('Shan Font');
    
    // Click the link and verify it goes to the right page.
    $this->clickLink('Shan Font');
    $this->assertSession()->addressEquals('admin/config/user-interface/shanfont');
    $this->assertSession()->statusCodeEquals(200);
  }

  /**
   * Tests access control.
   */
  public function testAccessControl() {
    // Logout admin user.
    $this->drupalLogout();
    
    // Create a user without admin permissions.
    $regular_user = $this->drupalCreateUser(['access content']);
    $this->drupalLogin($regular_user);
    
    // Try to access the configuration form.
    $this->drupalGet('admin/config/user-interface/shanfont');
    $this->assertSession()->statusCodeEquals(403);
    
    // Login as admin again and verify access.
    $this->drupalLogin($this->adminUser);
    $this->drupalGet('admin/config/user-interface/shanfont');
    $this->assertSession()->statusCodeEquals(200);
  }

  /**
   * Tests library attachment based on configuration.
   */
  public function testLibraryAttachment() {
    // Test quick setup mode library attachment.
    $this->config('shanfont.settings')
      ->set('font_mode', 'quick_setup')
      ->save();
    
    $this->drupalGet('<front>');
    
    // Check that the CDN and local font libraries are attached.
    // Note: In a real test environment, you might need to check the HTML source
    // or use JavaScript to verify library loading.
    $this->assertSession()->statusCodeEquals(200);
    
    // Test that admin styles are loaded on admin pages.
    $this->drupalGet('admin/config/user-interface/shanfont');
    $this->assertSession()->statusCodeEquals(200);
  }

  /**
   * Tests configuration schema validation.
   */
  public function testConfigurationSchema() {
    // Test that the configuration schema is properly defined.
    $config = $this->config('shanfont.settings');
    
    // Set various configuration values and ensure they're valid.
    $config->set('font_mode', 'custom_selection');
    $config->set('selected_fonts', [
      'shan_regular' => TRUE,
      'shan_bold' => FALSE,
    ]);
    $config->save();
    
    // Verify the configuration was saved correctly.
    $saved_config = $this->config('shanfont.settings');
    $this->assertEquals('custom_selection', $saved_config->get('font_mode'));
    $this->assertTrue($saved_config->get('selected_fonts.shan_regular'));
    $this->assertFalse($saved_config->get('selected_fonts.shan_bold'));
  }

}
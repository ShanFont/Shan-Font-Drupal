<?php

namespace Drupal\Tests\shanfont;

use Drupal\Tests\BrowserTestBase;

/**
 * Base class for Shan Font tests.
 *
 * Provides common functionality for Shan Font module tests.
 */
abstract class ShanFontTestBase extends BrowserTestBase {

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
   * A regular user without administrative permissions.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $regularUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Create users.
    $this->adminUser = $this->drupalCreateUser([
      'administer site configuration',
      'access administration pages',
    ]);

    $this->regularUser = $this->drupalCreateUser([
      'access content',
    ]);
  }

  /**
   * Helper method to set Shan Font configuration.
   *
   * @param string $mode
   *   The font mode to set.
   * @param array $selected_fonts
   *   Array of selected fonts for custom mode.
   */
  protected function setShanFontConfig($mode, array $selected_fonts = []) {
    $config = $this->config('shanfont.settings');
    $config->set('font_mode', $mode);
    
    if (!empty($selected_fonts)) {
      $config->set('selected_fonts', $selected_fonts);
    }
    
    $config->save();
  }

  /**
   * Helper method to get default font selections.
   *
   * @return array
   *   Default font selection array.
   */
  protected function getDefaultFontSelection() {
    return [
      'shan_thin' => FALSE,
      'shan_thin_italic' => FALSE,
      'shan_regular' => TRUE,
      'shan_italic' => FALSE,
      'shan_bold' => TRUE,
      'shan_bold_italic' => FALSE,
      'shan_black' => FALSE,
      'shan_black_italic' => FALSE,
    ];
  }

  /**
   * Helper method to assert font mode application on frontend.
   *
   * @param string $expected_mode
   *   The expected font mode.
   */
  protected function assertFontModeApplied($expected_mode) {
    $this->drupalGet('<front>');
    
    switch ($expected_mode) {
      case 'quick_setup':
        $this->assertSession()->elementExists('css', 'body.shanfont-quick-setup');
        $this->assertSession()->elementNotExists('css', 'body.shanfont-custom-selection');
        break;
        
      case 'custom_selection':
        $this->assertSession()->elementExists('css', 'body.shanfont-custom-selection');
        $this->assertSession()->elementNotExists('css', 'body.shanfont-quick-setup');
        break;
        
      case 'theme_defaults':
      default:
        $this->assertSession()->elementNotExists('css', 'body.shanfont-quick-setup');
        $this->assertSession()->elementNotExists('css', 'body.shanfont-custom-selection');
        break;
    }
  }

  /**
   * Helper method to assert configuration form elements.
   */
  protected function assertConfigFormElements() {
    $this->assertSession()->fieldExists('font_mode');
    $this->assertSession()->optionExists('font_mode', 'theme_defaults');
    $this->assertSession()->optionExists('font_mode', 'quick_setup');
    $this->assertSession()->optionExists('font_mode', 'custom_selection');
    
    // Check custom font checkboxes.
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
    
    $this->assertSession()->buttonExists('Save configuration');
  }

  /**
   * Helper method to create test font files (mock).
   *
   * @return array
   *   Array of created test file paths.
   */
  protected function createTestFontFiles() {
    $module_path = $this->container->get('extension.list.module')->getPath('shanfont');
    $fonts_dir = $module_path . '/fonts';
    
    if (!is_dir($fonts_dir)) {
      mkdir($fonts_dir, 0755, TRUE);
    }
    
    $font_files = [
      'Shan-Thin.woff2',
      'Shan-ThinItalic.woff2',
      'Shan-Regular.woff2',
      'Shan-Italic.woff2',
      'Shan-Bold.woff2',
      'Shan-BoldItalic.woff2',
      'Shan-Black.woff2',
      'Shan-BlackItalic.woff2',
    ];
    
    $created_files = [];
    foreach ($font_files as $filename) {
      $filepath = $fonts_dir . '/' . $filename;
      // Create empty files for testing.
      file_put_contents($filepath, 'mock font data');
      $created_files[] = $filepath;
    }
    
    return $created_files;
  }

  /**
   * Helper method to clean up test font files.
   *
   * @param array $files
   *   Array of file paths to clean up.
   */
  protected function cleanupTestFontFiles(array $files) {
    foreach ($files as $file) {
      if (file_exists($file)) {
        unlink($file);
      }
    }
  }

  /**
   * Helper method to assert admin styles are loaded.
   */
  protected function assertAdminStylesLoaded() {
    // Check that admin-specific CSS is loaded on admin pages.
    $this->drupalGet('admin/config/user-interface/shanfont');
    
    // Look for admin-specific CSS classes or elements.
    $this->assertSession()->elementExists('css', '.shanfont-header');
  }

  /**
   * Helper method to test font loading strategy.
   *
   * @param string $mode
   *   The font mode to test.
   * @param array $selected_fonts
   *   Selected fonts for custom mode.
   *
   * @return array
   *   The font loading strategy.
   */
  protected function testFontLoadingStrategy($mode, array $selected_fonts = []) {
    $this->setShanFontConfig($mode, $selected_fonts);
    
    /** @var \Drupal\shanfont\Service\ShanFontManager $font_manager */
    $font_manager = $this->container->get('shanfont.font_manager');
    
    return $font_manager->getFontLoadingStrategy();
  }

}
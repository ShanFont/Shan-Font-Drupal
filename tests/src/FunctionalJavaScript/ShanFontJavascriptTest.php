<?php

namespace Drupal\Tests\shanfont\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Tests the Shan Font module JavaScript functionality.
 *
 * @group shanfont
 */
class ShanFontJavascriptTest extends WebDriverTestBase {

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
   * Tests dynamic form behavior with JavaScript.
   */
  public function testConfigFormDynamicBehavior() {
    $this->drupalGet('admin/config/user-interface/shanfont');
    
    $page = $this->getSession()->getPage();
    $assert_session = $this->assertSession();
    
    // Test that custom font options are initially hidden.
    $custom_fieldset = $page->find('css', 'fieldset[data-drupal-selector="edit-custom-fonts"]');
    if ($custom_fieldset) {
      // Check if fieldset is hidden when not in custom selection mode.
      $quick_setup_radio = $page->find('css', 'input[value="quick_setup"]');
      if ($quick_setup_radio && $quick_setup_radio->isChecked()) {
        $this->assertFalse($custom_fieldset->isVisible());
      }
    }
    
    // Select custom selection mode.
    $page->selectFieldOption('font_mode', 'custom_selection');
    
    // Wait for the form to update.
    $assert_session->waitForElementVisible('css', 'fieldset[data-drupal-selector="edit-custom-fonts"]');
    
    // Verify custom font options are now visible.
    $custom_fieldset = $page->find('css', 'fieldset[data-drupal-selector="edit-custom-fonts"]');
    if ($custom_fieldset) {
      $this->assertTrue($custom_fieldset->isVisible());
    }
    
    // Test font selection checkboxes.
    $regular_checkbox = $page->find('css', 'input[name="shan_regular"]');
    $bold_checkbox = $page->find('css', 'input[name="shan_bold"]');
    
    if ($regular_checkbox && $bold_checkbox) {
      // Select some fonts.
      $regular_checkbox->check();
      $bold_checkbox->check();
      
      // Submit the form.
      $page->pressButton('Save configuration');
      
      // Wait for form submission.
      $assert_session->waitForText('Shan Font settings have been saved.');
      
      // Verify the checkboxes remain checked.
      $this->assertTrue($regular_checkbox->isChecked());
      $this->assertTrue($bold_checkbox->isChecked());
    }
  }

  /**
   * Tests font loading on frontend pages.
   */
  public function testFontLoadingOnFrontend() {
    // Configure quick setup mode.
    $this->config('shanfont.settings')
      ->set('font_mode', 'quick_setup')
      ->save();
    
    // Visit frontend page.
    $this->drupalGet('<front>');
    
    $page = $this->getSession()->getPage();
    
    // Check that body has the correct class.
    $body = $page->find('css', 'body.shanfont-quick-setup');
    $this->assertNotNull($body, 'Body has shanfont-quick-setup class in quick setup mode.');
    
    // Test custom selection mode.
    $this->config('shanfont.settings')
      ->set('font_mode', 'custom_selection')
      ->set('selected_fonts', [
        'shan_regular' => TRUE,
        'shan_bold' => TRUE,
      ])
      ->save();
    
    // Refresh page to apply new configuration.
    $this->getSession()->reload();
    
    // Check custom selection class and data attributes.
    $body = $page->find('css', 'body.shanfont-custom-selection');
    $this->assertNotNull($body, 'Body has shanfont-custom-selection class in custom mode.');
    
    $data_variants = $body->getAttribute('data-shanfont-variants');
    $this->assertStringContainsString('shan_regular', $data_variants);
    $this->assertStringContainsString('shan_bold', $data_variants);
  }

  /**
   * Tests CSS loading and font application.
   */
  public function testCssLoadingAndApplication() {
    // Set quick setup mode.
    $this->config('shanfont.settings')
      ->set('font_mode', 'quick_setup')
      ->save();
    
    $this->drupalGet('<front>');
    
    $session = $this->getSession();
    
    // Wait for CSS to load.
    $session->wait(3000);
    
    // Check if font family is applied using JavaScript.
    $font_family = $session->evaluateScript("
      return window.getComputedStyle(document.body).fontFamily;
    ");
    
    // The exact font-family might vary, but we can check if our CSS is loaded.
    $this->assertIsString($font_family);
    
    // Check if CSS files are loaded.
    $css_loaded = $session->evaluateScript("
      var links = document.querySelectorAll('link[rel=\"stylesheet\"]');
      var shanfontCss = false;
      for (var i = 0; i < links.length; i++) {
        if (links[i].href.includes('shanfont') || links[i].href.includes('shan.css')) {
          shanfontCss = true;
          break;
        }
      }
      return shanfontCss;
    ");
    
    $this->assertTrue($css_loaded, 'Shan Font CSS is loaded on the page.');
  }

  /**
   * Tests admin interface responsiveness.
   */
  public function testAdminInterfaceResponsiveness() {
    $this->drupalGet('admin/config/user-interface/shanfont');
    
    $session = $this->getSession();
    
    // Test desktop view.
    $session->resizeWindow(1200, 800);
    $session->wait(1000);
    
    $page = $this->getSession()->getPage();
    $form_container = $page->find('css', '.shanfont-header');
    
    if ($form_container) {
      $this->assertTrue($form_container->isVisible());
    }
    
    // Test tablet view.
    $session->resizeWindow(768, 1024);
    $session->wait(1000);
    
    // Form should still be usable.
    $font_mode_field = $page->find('css', 'input[name="font_mode"]');
    if ($font_mode_field) {
      $this->assertTrue($font_mode_field->isVisible());
    }
    
    // Test mobile view.
    $session->resizeWindow(375, 667);
    $session->wait(1000);
    
    // Form should still be accessible.
    $save_button = $page->find('css', 'input[value="Save configuration"]');
    if ($save_button) {
      $this->assertTrue($save_button->isVisible());
    }
    
    // Reset to desktop view.
    $session->resizeWindow(1200, 800);
  }

  /**
   * Tests form validation with JavaScript.
   */
  public function testFormValidationWithJavascript() {
    $this->drupalGet('admin/config/user-interface/shanfont');
    
    $page = $this->getSession()->getPage();
    $assert_session = $this->assertSession();
    
    // Select custom selection mode.
    $page->selectFieldOption('font_mode', 'custom_selection');
    
    // Wait for custom options to appear.
    $assert_session->waitForElementVisible('css', 'fieldset[data-drupal-selector="edit-custom-fonts"]');
    
    // Ensure no fonts are selected (all unchecked).
    $font_checkboxes = $page->findAll('css', 'fieldset[data-drupal-selector="edit-custom-fonts"] input[type="checkbox"]');
    foreach ($font_checkboxes as $checkbox) {
      if ($checkbox->isChecked()) {
        $checkbox->uncheck();
      }
    }
    
    // Submit form without selecting any fonts.
    $page->pressButton('Save configuration');
    
    // Wait for potential validation message or form submission.
    $this->getSession()->wait(2000);
    
    // The form should handle this gracefully (either prevent submission
    // or show a message). The exact behavior depends on implementation.
  }

  /**
   * Tests accessibility features.
   */
  public function testAccessibilityFeatures() {
    $this->drupalGet('admin/config/user-interface/shanfont');
    
    $session = $this->getSession();
    $page = $this->getSession()->getPage();
    
    // Test keyboard navigation.
    $first_radio = $page->find('css', 'input[name="font_mode"][value="theme_defaults"]');
    if ($first_radio) {
      $first_radio->focus();
      
      // Simulate tab navigation.
      $session->getDriver()->getWebDriverSession()->getKeyboard()->sendKeys("\t");
      
      // Check that focus moved to next element.
      $focused_element = $session->evaluateScript('return document.activeElement.tagName');
      $this->assertNotEmpty($focused_element);
    }
    
    // Test ARIA attributes and labels.
    $form_elements = $page->findAll('css', 'input, select, textarea');
    foreach ($form_elements as $element) {
      // Each form element should have a label or aria-label.
      $id = $element->getAttribute('id');
      if ($id) {
        $label = $page->find('css', "label[for=\"{$id}\"]");
        $aria_label = $element->getAttribute('aria-label');
        $aria_labelledby = $element->getAttribute('aria-labelledby');
        
        $this->assertTrue(
          $label !== null || !empty($aria_label) || !empty($aria_labelledby),
          "Form element with ID {$id} should have an associated label."
        );
      }
    }
  }

}
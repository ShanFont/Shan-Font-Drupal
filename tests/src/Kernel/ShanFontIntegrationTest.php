<?php

namespace Drupal\Tests\shanfont\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\shanfont\Service\ShanFontManager;

/**
 * Tests the Shan Font module integration and services.
 *
 * @group shanfont
 */
class ShanFontIntegrationTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['system', 'shanfont'];

  /**
   * The Shan Font manager service.
   *
   * @var \Drupal\shanfont\Service\ShanFontManager
   */
  protected $shanFontManager;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installConfig(['shanfont']);
    $this->shanFontManager = $this->container->get('shanfont.font_manager');
  }

  /**
   * Tests that the service is properly registered and accessible.
   */
  public function testServiceRegistration() {
    $this->assertInstanceOf(ShanFontManager::class, $this->shanFontManager);
  }

  /**
   * Tests default configuration installation.
   */
  public function testDefaultConfiguration() {
    $config = $this->config('shanfont.settings');
    
    // Test that default configuration is installed.
    $this->assertEquals('quick_setup', $config->get('font_mode'));
    
    // Test that default selected fonts are set.
    $selected_fonts = $config->get('selected_fonts');
    $this->assertTrue($selected_fonts['shan_regular']);
    $this->assertTrue($selected_fonts['shan_bold']);
    $this->assertFalse($selected_fonts['shan_italic']);
  }

  /**
   * Tests configuration validation through the service.
   */
  public function testConfigurationValidation() {
    // Test valid configuration.
    $valid_config = [
      'font_mode' => 'quick_setup',
    ];
    $errors = $this->shanFontManager->validateConfig($valid_config);
    $this->assertEmpty($errors);

    // Test invalid configuration.
    $invalid_config = [
      'font_mode' => 'nonexistent_mode',
    ];
    $errors = $this->shanFontManager->validateConfig($invalid_config);
    $this->assertNotEmpty($errors);
  }

  /**
   * Tests font loading strategy calculation.
   */
  public function testFontLoadingStrategy() {
    // Test quick setup strategy.
    $this->config('shanfont.settings')
      ->set('font_mode', 'quick_setup')
      ->save();

    $strategy = $this->shanFontManager->getFontLoadingStrategy();
    $this->assertEquals('quick_setup', $strategy['mode']);
    $this->assertTrue($strategy['use_cdn']);
    $this->assertTrue($strategy['use_local']);

    // Test custom selection strategy.
    $this->config('shanfont.settings')
      ->set('font_mode', 'custom_selection')
      ->set('selected_fonts', [
        'shan_regular' => TRUE,
        'shan_bold' => FALSE,
      ])
      ->save();

    $strategy = $this->shanFontManager->getFontLoadingStrategy();
    $this->assertEquals('custom_selection', $strategy['mode']);
    $this->assertFalse($strategy['use_cdn']);
    $this->assertTrue($strategy['use_local']);
    $this->assertEquals(['shan_regular'], $strategy['selected_fonts']);
  }

  /**
   * Tests configuration changes and cache invalidation.
   */
  public function testConfigurationChanges() {
    $config = $this->configFactory->getEditable('shanfont.settings');
    
    // Change to custom selection mode.
    $config->set('font_mode', 'custom_selection');
    $config->set('selected_fonts', [
      'shan_regular' => TRUE,
      'shan_bold' => TRUE,
      'shan_italic' => FALSE,
    ]);
    $config->save();

    // Verify changes are reflected in the service.
    $this->assertEquals('custom_selection', $this->shanFontManager->getFontMode());
    
    $selected_fonts = $this->shanFontManager->getSelectedFonts();
    $this->assertTrue($selected_fonts['shan_regular']);
    $this->assertTrue($selected_fonts['shan_bold']);
    $this->assertFalse($selected_fonts['shan_italic']);
  }

  /**
   * Tests font variant metadata.
   */
  public function testFontVariantMetadata() {
    $variants = $this->shanFontManager->getFontVariants();
    
    // Test that all expected variants exist with proper metadata.
    $expected_variants = [
      'shan_thin' => ['weight' => 100, 'style' => 'normal'],
      'shan_thin_italic' => ['weight' => 100, 'style' => 'italic'],
      'shan_regular' => ['weight' => 400, 'style' => 'normal'],
      'shan_italic' => ['weight' => 400, 'style' => 'italic'],
      'shan_bold' => ['weight' => 700, 'style' => 'normal'],
      'shan_bold_italic' => ['weight' => 700, 'style' => 'italic'],
      'shan_black' => ['weight' => 900, 'style' => 'normal'],
      'shan_black_italic' => ['weight' => 900, 'style' => 'italic'],
    ];

    foreach ($expected_variants as $variant_key => $expected_props) {
      $this->assertArrayHasKey($variant_key, $variants);
      $this->assertEquals($expected_props['weight'], $variants[$variant_key]['weight']);
      $this->assertEquals($expected_props['style'], $variants[$variant_key]['style']);
      $this->assertArrayHasKey('file', $variants[$variant_key]);
      $this->assertArrayHasKey('label', $variants[$variant_key]);
    }
  }

  /**
   * Tests hook implementations through module functionality.
   */
  public function testHookImplementations() {
    // Test that the module implements the expected hooks.
    $module_handler = $this->container->get('module_handler');
    
    $this->assertTrue($module_handler->implementsHook('shanfont', 'help'));
    $this->assertTrue($module_handler->implementsHook('shanfont', 'page_attachments'));
    $this->assertTrue($module_handler->implementsHook('shanfont', 'preprocess_html'));
  }

  /**
   * Tests library definitions.
   */
  public function testLibraryDefinitions() {
    /** @var \Drupal\Core\Asset\LibraryDiscoveryInterface $library_discovery */
    $library_discovery = $this->container->get('library.discovery');
    
    // Test that our libraries are properly defined.
    $shan_fonts_library = $library_discovery->getLibraryByName('shanfont', 'shan-fonts');
    $this->assertNotEmpty($shan_fonts_library);
    $this->assertArrayHasKey('css', $shan_fonts_library);
    
    $shan_cdn_library = $library_discovery->getLibraryByName('shanfont', 'shan-cdn');
    $this->assertNotEmpty($shan_cdn_library);
    $this->assertArrayHasKey('css', $shan_cdn_library);
    
    $admin_styles_library = $library_discovery->getLibraryByName('shanfont', 'admin-styles');
    $this->assertNotEmpty($admin_styles_library);
    $this->assertArrayHasKey('css', $admin_styles_library);
  }

  /**
   * Tests configuration schema compliance.
   */
  public function testConfigurationSchema() {
    /** @var \Drupal\Core\Config\TypedConfigManagerInterface $typed_config */
    $typed_config = $this->container->get('config.typed');
    
    // Test that our configuration schema is properly defined.
    $definition = $typed_config->getDefinition('shanfont.settings');
    $this->assertNotEmpty($definition);
    
    // Test configuration validation against schema.
    $config_data = $this->config('shanfont.settings')->get();
    $typed_data = $typed_config->create($definition, $config_data);
    
    // Validate the configuration.
    $violations = $typed_data->validate();
    $this->assertEquals(0, $violations->count(), 'Configuration validates against schema.');
  }

  /**
   * Tests CSS generation functionality.
   */
  public function testCssGeneration() {
    // Test that CSS generation works with valid font selection.
    $selected_fonts = ['shan_regular', 'shan_bold'];
    
    // Generate CSS (note: this test assumes font files exist or are mocked).
    $css = $this->shanFontManager->generateFontCSS($selected_fonts);
    
    // Verify CSS structure without depending on actual font files.
    $this->assertIsString($css);
    
    // Test empty font selection.
    $empty_css = $this->shanFontManager->generateFontCSS([]);
    $this->assertIsString($empty_css);
  }

  /**
   * Tests event subscriber registration.
   */
  public function testEventSubscriberRegistration() {
    /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher */
    $event_dispatcher = $this->container->get('event_dispatcher');
    
    // Test that our cache invalidator is registered as an event subscriber.
    $listeners = $event_dispatcher->getListeners('config.save');
    $this->assertNotEmpty($listeners);
    
    // Check that our subscriber is in the list.
    $subscriber_found = FALSE;
    foreach ($listeners as $listener) {
      if (is_array($listener) && 
          isset($listener[0]) && 
          get_class($listener[0]) === 'Drupal\shanfont\EventSubscriber\ShanFontCacheInvalidator') {
        $subscriber_found = TRUE;
        break;
      }
    }
    $this->assertTrue($subscriber_found, 'ShanFontCacheInvalidator event subscriber is registered.');
  }

  /**
   * Tests service dependencies.
   */
  public function testServiceDependencies() {
    // Test that the service has all required dependencies.
    $this->assertInstanceOf(ShanFontManager::class, $this->shanFontManager);
    
    // Test that the service can access configuration.
    $font_mode = $this->shanFontManager->getFontMode();
    $this->assertIsString($font_mode);
    
    // Test that the service can access font variants.
    $variants = $this->shanFontManager->getFontVariants();
    $this->assertIsArray($variants);
    $this->assertNotEmpty($variants);
  }

  /**
   * Tests configuration export and import.
   */
  public function testConfigurationExportImport() {
    // Set custom configuration.
    $config = $this->configFactory->getEditable('shanfont.settings');
    $config->set('font_mode', 'custom_selection');
    $config->set('selected_fonts', [
      'shan_regular' => TRUE,
      'shan_bold' => TRUE,
      'shan_italic' => FALSE,
    ]);
    $config->save();
    
    // Export configuration.
    $exported_config = $config->getRawData();
    
    // Clear and reimport.
    $config->delete();
    $this->assertNull($this->config('shanfont.settings')->get('font_mode'));
    
    // Reinstall with exported data.
    $config = $this->configFactory->getEditable('shanfont.settings');
    foreach ($exported_config as $key => $value) {
      $config->set($key, $value);
    }
    $config->save();
    
    // Verify imported configuration.
    $this->assertEquals('custom_selection', $this->config('shanfont.settings')->get('font_mode'));
    $selected_fonts = $this->config('shanfont.settings')->get('selected_fonts');
    $this->assertTrue($selected_fonts['shan_regular']);
    $this->assertTrue($selected_fonts['shan_bold']);
    $this->assertFalse($selected_fonts['shan_italic']);
  }

}
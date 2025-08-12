<?php

namespace Drupal\Tests\shanfont\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\shanfont\Service\ShanFontManager;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\Extension;
use Drupal\Core\File\FileSystemInterface;

/**
 * Tests the ShanFontManager service.
 *
 * @group shanfont
 * @coversDefaultClass \Drupal\shanfont\Service\ShanFontManager
 */
class ShanFontManagerTest extends UnitTestCase {

  /**
   * The mocked config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $configFactory;

  /**
   * The mocked module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $moduleHandler;

  /**
   * The mocked file system.
   *
   * @var \Drupal\Core\File\FileSystemInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $fileSystem;

  /**
   * The mocked config object.
   *
   * @var \Drupal\Core\Config\ImmutableConfig|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $config;

  /**
   * The ShanFontManager instance.
   *
   * @var \Drupal\shanfont\Service\ShanFontManager
   */
  protected $shanFontManager;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Mock dependencies.
    $this->configFactory = $this->createMock(ConfigFactoryInterface::class);
    $this->moduleHandler = $this->createMock(ModuleHandlerInterface::class);
    $this->fileSystem = $this->createMock(FileSystemInterface::class);
    $this->config = $this->createMock(ImmutableConfig::class);

    // Setup config factory to return our mocked config.
    $this->configFactory
      ->method('get')
      ->with('shanfont.settings')
      ->willReturn($this->config);

    // Create the service instance.
    $this->shanFontManager = new ShanFontManager(
      $this->configFactory,
      $this->moduleHandler,
      $this->fileSystem
    );
  }

  /**
   * Tests getFontMode method.
   *
   * @covers ::getFontMode
   */
  public function testGetFontMode() {
    // Test with configured mode.
    $this->config
      ->method('get')
      ->with('font_mode')
      ->willReturn('custom_selection');

    $result = $this->shanFontManager->getFontMode();
    $this->assertEquals('custom_selection', $result);
  }

  /**
   * Tests getFontMode method with default fallback.
   *
   * @covers ::getFontMode
   */
  public function testGetFontModeDefault() {
    // Test with no configured mode (returns default).
    $this->config
      ->method('get')
      ->with('font_mode')
      ->willReturn(NULL);

    $result = $this->shanFontManager->getFontMode();
    $this->assertEquals('quick_setup', $result);
  }

  /**
   * Tests getSelectedFonts method.
   *
   * @covers ::getSelectedFonts
   */
  public function testGetSelectedFonts() {
    $expected_fonts = [
      'shan_regular' => TRUE,
      'shan_bold' => TRUE,
      'shan_italic' => FALSE,
    ];

    $this->config
      ->method('get')
      ->with('selected_fonts')
      ->willReturn($expected_fonts);

    $result = $this->shanFontManager->getSelectedFonts();
    $this->assertEquals($expected_fonts, $result);
  }

  /**
   * Tests getSelectedFonts method with default fallback.
   *
   * @covers ::getSelectedFonts
   */
  public function testGetSelectedFontsDefault() {
    $this->config
      ->method('get')
      ->with('selected_fonts')
      ->willReturn(NULL);

    $result = $this->shanFontManager->getSelectedFonts();
    $this->assertEquals([], $result);
  }

  /**
   * Tests getFontVariants method.
   *
   * @covers ::getFontVariants
   */
  public function testGetFontVariants() {
    $variants = $this->shanFontManager->getFontVariants();
    
    // Check that all expected variants are present.
    $expected_variants = [
      'shan_thin',
      'shan_thin_italic',
      'shan_regular',
      'shan_italic',
      'shan_bold',
      'shan_bold_italic',
      'shan_black',
      'shan_black_italic',
    ];

    foreach ($expected_variants as $variant) {
      $this->assertArrayHasKey($variant, $variants);
      $this->assertArrayHasKey('file', $variants[$variant]);
      $this->assertArrayHasKey('weight', $variants[$variant]);
      $this->assertArrayHasKey('style', $variants[$variant]);
      $this->assertArrayHasKey('label', $variants[$variant]);
    }

    // Test specific variant properties.
    $this->assertEquals('Shan-Regular.woff2', $variants['shan_regular']['file']);
    $this->assertEquals(400, $variants['shan_regular']['weight']);
    $this->assertEquals('normal', $variants['shan_regular']['style']);
    
    $this->assertEquals('Shan-Bold.woff2', $variants['shan_bold']['file']);
    $this->assertEquals(700, $variants['shan_bold']['weight']);
    $this->assertEquals('normal', $variants['shan_bold']['style']);
  }

  /**
   * Tests getFontLoadingStrategy method for different modes.
   *
   * @covers ::getFontLoadingStrategy
   * @dataProvider fontModeProvider
   */
  public function testGetFontLoadingStrategy($mode, $expected_strategy) {
    $this->config
      ->method('get')
      ->willReturnMap([
        ['font_mode', $mode],
        ['selected_fonts', ['shan_regular' => TRUE, 'shan_bold' => FALSE]],
      ]);

    $strategy = $this->shanFontManager->getFontLoadingStrategy();
    
    $this->assertEquals($expected_strategy['mode'], $strategy['mode']);
    $this->assertEquals($expected_strategy['use_cdn'], $strategy['use_cdn']);
    $this->assertEquals($expected_strategy['use_local'], $strategy['use_local']);
  }

  /**
   * Data provider for font mode testing.
   */
  public function fontModeProvider() {
    return [
      'quick_setup' => [
        'quick_setup',
        [
          'mode' => 'quick_setup',
          'use_cdn' => TRUE,
          'use_local' => TRUE,
        ],
      ],
      'custom_selection' => [
        'custom_selection',
        [
          'mode' => 'custom_selection',
          'use_cdn' => FALSE,
          'use_local' => TRUE,
        ],
      ],
      'theme_defaults' => [
        'theme_defaults',
        [
          'mode' => 'theme_defaults',
          'use_cdn' => FALSE,
          'use_local' => FALSE,
        ],
      ],
    ];
  }

  /**
   * Tests generateFontCSS method.
   *
   * @covers ::generateFontCSS
   */
  public function testGenerateFontCSS() {
    // Mock module handler to return a module path.
    $extension = $this->createMock(Extension::class);
    $extension->method('getPath')->willReturn('modules/custom/shanfont');
    
    $this->moduleHandler
      ->method('getModule')
      ->with('shanfont')
      ->willReturn($extension);

    // Mock file_exists to return true for our test.
    $this->mockGlobalFunction('file_exists', TRUE);

    $selected_fonts = ['shan_regular'];
    $css = $this->shanFontManager->generateFontCSS($selected_fonts);

    // Check that CSS contains expected elements.
    $this->assertStringContainsString('@font-face', $css);
    $this->assertStringContainsString("font-family: 'Shan'", $css);
    $this->assertStringContainsString('font-weight: 400', $css);
    $this->assertStringContainsString('font-style: normal', $css);
    $this->assertStringContainsString('font-display: swap', $css);
    $this->assertStringContainsString('Shan-Regular.woff2', $css);
  }

  /**
   * Tests validateConfig method with valid configuration.
   *
   * @covers ::validateConfig
   */
  public function testValidateConfigValid() {
    $config = [
      'font_mode' => 'custom_selection',
      'selected_fonts' => [
        'shan_regular' => TRUE,
        'shan_bold' => FALSE,
      ],
    ];

    $errors = $this->shanFontManager->validateConfig($config);
    $this->assertEmpty($errors);
  }

  /**
   * Tests validateConfig method with invalid font mode.
   *
   * @covers ::validateConfig
   */
  public function testValidateConfigInvalidMode() {
    $config = [
      'font_mode' => 'invalid_mode',
    ];

    $errors = $this->shanFontManager->validateConfig($config);
    $this->assertNotEmpty($errors);
    $this->assertContains('Invalid font mode specified.', $errors);
  }

  /**
   * Tests validateConfig method with custom selection but no fonts selected.
   *
   * @covers ::validateConfig
   */
  public function testValidateConfigCustomSelectionNoFonts() {
    $config = [
      'font_mode' => 'custom_selection',
      'selected_fonts' => [
        'shan_regular' => FALSE,
        'shan_bold' => FALSE,
      ],
    ];

    $errors = $this->shanFontManager->validateConfig($config);
    $this->assertNotEmpty($errors);
    $this->assertContains('At least one font must be selected in custom selection mode.', $errors);
  }

  /**
   * Tests validateConfig method with invalid font variant.
   *
   * @covers ::validateConfig
   */
  public function testValidateConfigInvalidFontVariant() {
    $config = [
      'font_mode' => 'custom_selection',
      'selected_fonts' => [
        'invalid_font' => TRUE,
      ],
    ];

    $errors = $this->shanFontManager->validateConfig($config);
    $this->assertNotEmpty($errors);
    $this->assertContains('Invalid font variant: invalid_font', $errors);
  }

  /**
   * Helper method to mock global functions.
   */
  private function mockGlobalFunction($function_name, $return_value) {
    if (!function_exists($function_name . '_mock')) {
      eval("function {$function_name}_mock() { return " . var_export($return_value, TRUE) . "; }");
    }
  }

}
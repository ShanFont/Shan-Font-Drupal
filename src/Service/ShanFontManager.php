<?php

namespace Drupal\shanfont\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\File\FileSystemInterface;

/**
 * Service for managing Shan Font functionality.
 */
class ShanFontManager {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * Available font variants.
   *
   * @var array
   */
  protected $fontVariants = [
    'shan_thin' => [
      'file' => 'Shan-Thin.woff2',
      'weight' => 100,
      'style' => 'normal',
      'label' => 'Shan Thin',
    ],
    'shan_thin_italic' => [
      'file' => 'Shan-ThinItalic.woff2',
      'weight' => 100,
      'style' => 'italic',
      'label' => 'Shan Thin Italic',
    ],
    'shan_regular' => [
      'file' => 'Shan-Regular.woff2',
      'weight' => 400,
      'style' => 'normal',
      'label' => 'Shan Regular',
    ],
    'shan_italic' => [
      'file' => 'Shan-Italic.woff2',
      'weight' => 400,
      'style' => 'italic',
      'label' => 'Shan Italic',
    ],
    'shan_bold' => [
      'file' => 'Shan-Bold.woff2',
      'weight' => 700,
      'style' => 'normal',
      'label' => 'Shan Bold',
    ],
    'shan_bold_italic' => [
      'file' => 'Shan-BoldItalic.woff2',
      'weight' => 700,
      'style' => 'italic',
      'label' => 'Shan Bold Italic',
    ],
    'shan_black' => [
      'file' => 'Shan-Black.woff2',
      'weight' => 900,
      'style' => 'normal',
      'label' => 'Shan Black',
    ],
    'shan_black_italic' => [
      'file' => 'Shan-BlackItalic.woff2',
      'weight' => 900,
      'style' => 'italic',
      'label' => 'Shan Black Italic',
    ],
  ];

  /**
   * Constructs a ShanFontManager object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ModuleHandlerInterface $module_handler, FileSystemInterface $file_system) {
    $this->configFactory = $config_factory;
    $this->moduleHandler = $module_handler;
    $this->fileSystem = $file_system;
  }

  /**
   * Gets the current font mode.
   *
   * @return string
   *   The current font mode.
   */
  public function getFontMode() {
    return $this->configFactory->get('shanfont.settings')->get('font_mode') ?: 'quick_setup';
  }

  /**
   * Gets the selected fonts for custom mode.
   *
   * @return array
   *   Array of selected fonts.
   */
  public function getSelectedFonts() {
    return $this->configFactory->get('shanfont.settings')->get('selected_fonts') ?: [];
  }

  /**
   * Gets all available font variants.
   *
   * @return array
   *   Array of font variants with metadata.
   */
  public function getFontVariants() {
    return $this->fontVariants;
  }

  /**
   * Checks if a font file exists locally.
   *
   * @param string $font_key
   *   The font variant key.
   *
   * @return bool
   *   TRUE if the font file exists, FALSE otherwise.
   */
  public function fontFileExists($font_key) {
    if (!isset($this->fontVariants[$font_key])) {
      return FALSE;
    }

    $module_path = $this->moduleHandler->getModule('shanfont')->getPath();
    $font_path = $module_path . '/fonts/' . $this->fontVariants[$font_key]['file'];
    
    return file_exists($font_path);
  }

  /**
   * Gets the path to a font file.
   *
   * @param string $font_key
   *   The font variant key.
   *
   * @return string|null
   *   The font file path or NULL if not found.
   */
  public function getFontFilePath($font_key) {
    if (!$this->fontFileExists($font_key)) {
      return NULL;
    }

    $module_path = $this->moduleHandler->getModule('shanfont')->getPath();
    return '/' . $module_path . '/fonts/' . $this->fontVariants[$font_key]['file'];
  }

  /**
   * Generates CSS for selected fonts.
   *
   * @param array $selected_fonts
   *   Array of selected font keys.
   *
   * @return string
   *   Generated CSS string.
   */
  public function generateFontCSS(array $selected_fonts = []) {
    $css = '';
    $fonts_to_load = $selected_fonts ?: array_keys($this->fontVariants);

    foreach ($fonts_to_load as $font_key) {
      if (isset($this->fontVariants[$font_key]) && $this->fontFileExists($font_key)) {
        $variant = $this->fontVariants[$font_key];
        $font_path = $this->getFontFilePath($font_key);
        
        $css .= "@font-face {\n";
        $css .= "  font-family: 'Shan';\n";
        $css .= "  src: url('{$font_path}') format('woff2');\n";
        $css .= "  font-weight: {$variant['weight']};\n";
        $css .= "  font-style: {$variant['style']};\n";
        $css .= "  font-display: swap;\n";
        $css .= "}\n\n";
      }
    }

    return $css;
  }

  /**
   * Checks if CDN is available.
   *
   * @return bool
   *   TRUE if CDN is accessible, FALSE otherwise.
   */
  public function isCdnAvailable() {
    $cdn_url = 'https://cdn.jsdelivr.net/gh/ShanFont/ShanFont@main/shan.css';
    
    // Simple check - in production, you might want to cache this result
    $headers = @get_headers($cdn_url, 1);
    return $headers && strpos($headers[0], '200') !== FALSE;
  }

  /**
   * Gets font loading strategy based on configuration.
   *
   * @return array
   *   Array containing loading strategy information.
   */
  public function getFontLoadingStrategy() {
    $mode = $this->getFontMode();
    $strategy = [
      'mode' => $mode,
      'use_cdn' => FALSE,
      'use_local' => FALSE,
      'selected_fonts' => [],
    ];

    switch ($mode) {
      case 'quick_setup':
        $strategy['use_cdn'] = TRUE;
        $strategy['use_local'] = TRUE; // Fallback
        break;

      case 'custom_selection':
        $strategy['use_local'] = TRUE;
        $strategy['selected_fonts'] = array_keys(array_filter($this->getSelectedFonts()));
        break;

      case 'theme_defaults':
      default:
        // No fonts loaded
        break;
    }

    return $strategy;
  }

  /**
   * Validates font configuration.
   *
   * @param array $config
   *   Configuration array to validate.
   *
   * @return array
   *   Array of validation errors, empty if valid.
   */
  public function validateConfig(array $config) {
    $errors = [];

    // Validate font mode
    $valid_modes = ['theme_defaults', 'quick_setup', 'custom_selection'];
    if (!isset($config['font_mode']) || !in_array($config['font_mode'], $valid_modes)) {
      $errors[] = 'Invalid font mode specified.';
    }

    // Validate selected fonts if in custom mode
    if (isset($config['font_mode']) && $config['font_mode'] === 'custom_selection') {
      if (isset($config['selected_fonts'])) {
        $selected_count = count(array_filter($config['selected_fonts']));
        if ($selected_count === 0) {
          $errors[] = 'At least one font must be selected in custom selection mode.';
        }

        // Check if selected fonts exist
        foreach ($config['selected_fonts'] as $font_key => $selected) {
          if ($selected && !isset($this->fontVariants[$font_key])) {
            $errors[] = "Invalid font variant: {$font_key}";
          }
        }
      }
    }

    return $errors;
  }

}
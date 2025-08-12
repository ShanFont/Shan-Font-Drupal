# Shan Font Drupal Module

![Shan Font in Drupal](https://shanfont.com/wp-content/uploads/2025/07/shan-font-in-drupal-1248x702.jpg)

A comprehensive Drupal module for easy integration of Shan fonts into your website with multiple configuration options, CDN support, and local fallback capabilities.

**Official Project Page:** https://www.drupal.org/project/shanfont

**Developed by TaiDev**

## Description

The Shan Font module provides seamless integration of authentic Shan typography into your Drupal website. Designed specifically for Shan-speaking communities and cultural organizations, this module offers three distinct font modes with performance optimization, accessibility features, and security best practices.

## Key Features

- **Multiple Integration Modes**: Theme Defaults, Quick Setup, and Custom Selection
- **CDN with Local Fallback**: Automatically loads from CDN first, falls back to local fonts
- **Performance Optimized**: Uses `font-display: swap` for optimal loading performance
- **Responsive Design**: Mobile-friendly implementation across all devices
- **Accessibility Compliant**: Full keyboard navigation and screen reader support
- **Security Focused**: Follows Drupal security best practices and coding standards
- **8 Font Variants**: Complete Shan font family with different weights and styles
- **API Integration**: Programmatic configuration and theme integration support

## Installation

### Automatic Installation (Recommended)

The easiest way to install Shan Font is using Composer and Drupal.org:

1. **Via Composer** (Recommended):
   ```bash
   composer require drupal/shanfont
   drush en shanfont
   ```

2. **Via Drupal Admin Interface**:
   - Go to **Extend** in your Drupal admin
   - Click **Install new module**
   - Enter project name: `shanfont` or visit: https://www.drupal.org/project/shanfont
   - Click **Install** and then **Enable**

3. **Configure the module**:
   - Navigate to **Configuration > User Interface > Shan Font** (`/admin/config/user-interface/shanfont`)
   - Choose your preferred font mode and save configuration

### Alternative Installation Methods

#### From Drupal.org
1. Visit the official project page: https://www.drupal.org/project/shanfont
2. Download the latest stable release
3. Extract to your `modules/contrib/shanfont` directory
4. Enable the module: `drush en shanfont` or through the admin interface
5. Configure at `/admin/config/user-interface/shanfont`

#### Manual Installation
1. Download from https://www.drupal.org/project/shanfont
2. Extract and upload to `modules/custom/shanfont` directory
3. Ensure proper file permissions are set
4. Enable through **Extend** page or use `drush en shanfont`
5. Place font files in the appropriate directories (see File Structure below)

## System Requirements

- **Drupal Core**: 9.0+ or 10.0+
- **PHP**: 8.0+ (8.1+ recommended)
- **Browser**: Modern browser with WOFF2 support (95%+ coverage)
- **Server**: Web server with proper file permissions for font assets

## File Structure

```
shanfont/
├── assets/
│   └── ShanFont.webp
├── config/
│   ├── install/
│   │   └── shanfont.settings.yml
│   └── schema/
│       └── shanfont.schema.yml
├── css/
│   ├── shanfont.css
│   └── shanfont-admin.css
├── fonts/
│   ├── Shan-Thin.woff2
│   ├── Shan-ThinItalic.woff2
│   ├── Shan-Regular.woff2
│   ├── Shan-Italic.woff2
│   ├── Shan-Bold.woff2
│   ├── Shan-BoldItalic.woff2
│   ├── Shan-Black.woff2
│   └── Shan-BlackItalic.woff2
├── src/
│   └── Form/
│       └── ShanFontConfigForm.php
├── tests/
│   └── src/
│       └── Functional/
│           └── ShanFontConfigTest.php
├── shanfont.info.yml
├── shanfont.libraries.yml
├── shanfont.links.menu.yml
├── shanfont.routing.yml
├── shanfont.module
├── shanfont.install
└── README.md
```

## Configuration

Navigate to **Configuration > User Interface > Shan Font** (`/admin/config/user-interface/shanfont`) to configure the module.

### Font Modes

#### 1. Theme Defaults
- Resets to use your theme's default fonts
- Removes any custom Shan font styling
- Useful for temporarily disabling Shan fonts
- Maintains existing design while providing easy re-enable option

#### 2. Quick Setup (Default)
- Automatically applies Shan fonts to the entire website
- Loads from CDN: `https://cdn.jsdelivr.net/gh/ShanFont/ShanFont@main/shan.css`
- Falls back to local fonts if CDN fails
- Zero configuration required
- Recommended for most users

#### 3. Custom Selection
- Choose specific font variants to load
- Optimized loading of only selected fonts
- Granular control over font application
- Reduces bandwidth usage and improves performance
- Advanced users can select from 8 font variants

### Font Variants Available

The module includes 8 professional Shan font variants:

| Variant | CSS Class | Weight | Style | File |
|---------|-----------|--------|-------|------|
| Thin | `.shanfont-thin` | 100 | Normal | Shan-Thin.woff2 |
| Thin Italic | `.shanfont-thin-italic` | 100 | Italic | Shan-ThinItalic.woff2 |
| Regular | `.shanfont-regular` | 400 | Normal | Shan-Regular.woff2 |
| Italic | `.shanfont-italic` | 400 | Italic | Shan-Italic.woff2 |
| Bold | `.shanfont-bold` | 700 | Normal | Shan-Bold.woff2 |
| Bold Italic | `.shanfont-bold-italic` | 700 | Italic | Shan-BoldItalic.woff2 |
| Black | `.shanfont-black` | 900 | Normal | Shan-Black.woff2 |
| Black Italic | `.shanfont-black-italic` | 900 | Italic | Shan-BlackItalic.woff2 |

## CSS Utility Classes

The module provides comprehensive CSS utility classes for manual font application:

### Primary Classes
```css
.shanfont               /* Apply Shan font family */
.shanfont-thin          /* Shan Thin (100 weight) */
.shanfont-regular       /* Shan Regular (400 weight) */
.shanfont-bold          /* Shan Bold (700 weight) */
.shanfont-black         /* Shan Black (900 weight) */
```

### Style Variants
```css
.shanfont-italic        /* Italic style */
.shanfont-thin-italic   /* Thin + Italic */
.shanfont-bold-italic   /* Bold + Italic */
.shanfont-black-italic  /* Black + Italic */
```

### Utility Classes
```css
.no-shanfont           /* Remove Shan font */
.shanfont-responsive   /* Responsive font sizing */
```

## Developer API

### Programmatic Configuration

```php
// Get current configuration
$config = \Drupal::config('shanfont.settings');
$mode = $config->get('font_mode');
$selected_fonts = $config->get('selected_fonts');
$use_cdn = $config->get('use_cdn');

// Update configuration programmatically
$config = \Drupal::configFactory()->getEditable('shanfont.settings');
$config->set('font_mode', 'custom_selection');
$config->set('selected_fonts', [
  'shan_regular' => TRUE,
  'shan_bold' => TRUE,
  'shan_italic' => TRUE,
]);
$config->set('use_cdn', TRUE);
$config->save();

// Clear cache to apply changes
drupal_flush_all_caches();
```

### Theme Integration

Add Shan fonts to your custom theme:

```php
// In your theme's .theme file
function MYTHEME_preprocess_page(&$variables) {
  $shanfont_config = \Drupal::config('shanfont.settings');
  
  if ($shanfont_config->get('font_mode') !== 'theme_defaults') {
    $variables['#attached']['library'][] = 'shanfont/shan-fonts';
  }
}

// Add specific font variants conditionally
function MYTHEME_preprocess_node(&$variables) {
  if ($variables['node']->getType() === 'article') {
    $variables['#attached']['library'][] = 'shanfont/shan-fonts-extended';
  }
}
```

### Custom Library Usage

Create custom font combinations in your theme:

```yaml
# In your theme's libraries.yml
custom-shan-fonts:
  css:
    theme:
      css/custom-shan.css: {}
  dependencies:
    - shanfont/shan-fonts
```

## Performance Optimization

### Font Loading Performance
- **Font Display Swap**: Ensures text remains visible during font load
- **CDN Loading**: Reduces server bandwidth and improves global performance
- **Local Fallback**: Automatic failover ensures reliability
- **Selective Loading**: Custom selection mode loads only needed variants
- **WOFF2 Format**: Modern, compressed format for 30% smaller file sizes

### Drupal Performance Integration
- **CSS Aggregation**: Compatible with Drupal's CSS aggregation
- **Cache Integration**: Proper cache tags and contexts
- **BigPipe Compatible**: Works with Drupal's BigPipe module
- **Lazy Loading**: Fonts load efficiently with page content

## Security Features

- **Input Validation**: All form inputs are properly sanitized using Drupal's Form API
- **XSS Protection**: Output is properly escaped through Drupal's render system
- **CSRF Protection**: Forms include proper token validation
- **Access Control**: Configuration requires "administer site configuration" permission
- **Safe External URLs**: CDN URLs are validated and sanitized
- **File Security**: Local font files are served with proper headers

## Accessibility Compliance

- **WCAG 2.1 AA Compliant**: Meets accessibility standards
- **Screen Reader Support**: Proper semantic markup and ARIA labels
- **Keyboard Navigation**: Full keyboard accessibility for admin interface
- **High Contrast**: Maintains readability in high contrast mode
- **Font Size Scaling**: Respects user font size preferences

## Browser Support

- **Modern Browsers**: Chrome 36+, Firefox 39+, Safari 12+, Edge 79+
- **WOFF2 Support**: 95%+ browser coverage globally
- **Graceful Degradation**: Automatic fallback to system fonts
- **Progressive Enhancement**: Enhanced experience for supported browsers

## Frequently Asked Questions

**Q: Will this module slow down my website?**  
A: No! The module is performance-optimized with CDN loading, font-display: swap, and selective font loading to minimize impact on page speed.

**Q: Can I use this with any Drupal theme?**  
A: Yes! The module works with any properly coded Drupal theme. The "Theme Defaults" mode ensures maximum compatibility.

**Q: Do I need to upload font files manually?**  
A: No, all necessary Shan font files are included with the module installation.

**Q: Is this compatible with Drupal 10?**  
A: Yes, the module is fully compatible with Drupal 9 and Drupal 10.

**Q: Can I customize which elements use Shan fonts?**  
A: Absolutely! Use the "Custom Selection" mode and CSS utility classes for granular control over font application.

## Troubleshooting

### Fonts Not Loading
1. **Check Module Status**: Ensure module is enabled at `/admin/modules`
2. **Verify Configuration**: Check settings at `/admin/config/user-interface/shanfont`
3. **Clear Cache**: Run `drush cr` or clear cache through admin interface
4. **File Permissions**: Ensure font files are readable by web server
5. **Browser Console**: Check for JavaScript or network errors

### Configuration Issues
1. **Permission Check**: Verify user has "administer site configuration" permission
2. **Database Connectivity**: Ensure database connection is stable
3. **Configuration Storage**: Check if configuration is being saved properly
4. **Module Dependencies**: Verify all required dependencies are met

### CDN Problems
1. **Network Connectivity**: Test CDN URL accessibility
2. **Firewall Settings**: Check if firewall blocks CDN requests
3. **Local Fallback**: Disable CDN to use local fonts only
4. **SSL Issues**: Verify HTTPS compatibility if using SSL

### Performance Issues
1. **Font Selection**: Use Custom Selection mode to load fewer fonts
2. **CSS Aggregation**: Enable CSS aggregation in performance settings
3. **Caching**: Enable page caching and CSS/JS aggregation
4. **CDN Usage**: Consider using a full-site CDN for better performance

## Development & Contributing

### Coding Standards
- Follow [Drupal Coding Standards](https://www.drupal.org/docs/develop/standards)
- Use PHP_CodeSniffer with Drupal standards
- Write comprehensive PHPUnit tests
- Document all public APIs

### Testing
```bash
# Install development dependencies
composer install --dev

# Run coding standards check
vendor/bin/phpcs --standard=Drupal,DrupalPractice shanfont/

# Fix coding standards issues
vendor/bin/phpcbf --standard=Drupal,DrupalPractice shanfont/

# Run PHPUnit tests
vendor/bin/phpunit modules/contrib/shanfont/tests/

# Run functional tests
vendor/bin/phpunit --group shanfont
```

### Contributing Guidelines
1. **Fork Repository**: Create a fork of the project repository
2. **Feature Branch**: Create a feature branch for your changes
3. **Follow Standards**: Adhere to Drupal coding and documentation standards
4. **Write Tests**: Include comprehensive tests for new features
5. **Update Documentation**: Keep documentation current with changes
6. **Submit Patch**: Create a patch file or pull request with detailed description

## Changelog

### Version 1.0.0 (Initial Release)
**Core Features**
- Multiple font mode support (Theme Defaults, Quick Setup, Custom Selection)
- CDN integration with automatic local fallback
- 8 complete Shan font variants with proper CSS classes
- Performance optimization with font-display: swap

**User Experience**
- Intuitive admin interface following Drupal design patterns
- Responsive configuration form
- Comprehensive help documentation
- Live preview functionality (planned for 1.1.0)

**Technical Excellence**
- Full Drupal 9/10 compatibility
- Complete coding standards compliance
- Security best practices implementation
- Accessibility features (WCAG 2.1 AA)
- Comprehensive test coverage

**Performance & Security**
- Optimized font loading strategies
- Input validation and XSS protection
- Proper cache integration
- BigPipe compatibility

## Support & Community

- **Official Project Page**: https://shanfont.com
- **Issue Queue**: https://github.com/ShanFont/Shan-Font-Drupal
- **Documentation**: https://shanfont.com/use-drupal

## License

This module is licensed under the GNU General Public License v2.0 or later, consistent with Drupal core licensing.

**Developed by TaiDev** | **© 2025 All Rights Reserved**

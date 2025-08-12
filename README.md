# Shan Font Drupal Module

# Shan Font Drupal Module

A comprehensive Drupal module for easy integration of Shan fonts into your website with multiple configuration options and fallback support.

**Developed by TaiDev**

## Features

- **Multiple Integration Modes**: Theme Defaults, Quick Setup, and Custom Selection
- **CDN with Local Fallback**: Automatically loads from CDN first, falls back to local fonts
- **Performance Optimized**: Uses `font-display: swap` for optimal loading
- **Responsive Design**: Mobile-friendly implementation
- **Accessibility**: Full keyboard navigation and screen reader support
- **Security**: Follows Drupal security best practices

## Installation

1. Download and extract the module to your `modules/custom/shanfont` directory
2. Place your font files in the `fonts/` directory:
   - `Shan-Thin.woff2`
   - `Shan-ThinItalic.woff2`
   - `Shan-Regular.woff2`
   - `Shan-Italic.woff2`
   - `Shan-Bold.woff2`
   - `Shan-BoldItalic.woff2`
   - `Shan-Black.woff2`
   - `Shan-BlackItalic.woff2`
3. Place the logo file `ShanFont.webp` in the `assets/` directory
4. Enable the module: `drush en shanfont`
5. Configure at `/admin/config/user-interface/shanfont`

## Directory Structure

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
├── shanfont.info.yml
├── shanfont.libraries.yml
├── shanfont.links.menu.yml
├── shanfont.routing.yml
├── shanfont.module
├── shanfont.install
└── README.md
```

## Configuration Modes

### Theme Defaults
Resets to use your theme's default fonts without any Shan font application.

### Quick Setup (Default)
- Automatically applies Shan fonts to the entire website
- Loads from CDN: `https://cdn.jsdelivr.net/gh/ShanFont/ShanFont@main/shan.css`
- Falls back to local fonts if CDN fails
- Zero configuration required

### Custom Selection
- Choose specific font variants to load
- Optimized loading of only selected fonts
- Granular control over font application
- Reduces bandwidth usage

## CSS Classes

The module provides utility classes for manual font application:

- `.shanfont` - Apply Shan font family
- `.shanfont-thin` - Shan Thin (100 weight)
- `.shanfont-regular` - Shan Regular (400 weight)
- `.shanfont-bold` - Shan Bold (700 weight)
- `.shanfont-black` - Shan Black (900 weight)

## API Usage

### Programmatic Configuration

```php
// Get current configuration
$config = \Drupal::config('shanfont.settings');
$mode = $config->get('font_mode');
$selected_fonts = $config->get('selected_fonts');

// Update configuration
$config = \Drupal::configFactory()->getEditable('shanfont.settings');
$config->set('font_mode', 'custom_selection');
$config->set('selected_fonts', [
  'shan_regular' => TRUE,
  'shan_bold' => TRUE,
  // ... other fonts
]);
$config->save();
```

### Theme Integration

Add Shan fonts to your theme's template:

```php
// In your theme's .theme file
function MYTHEME_preprocess_page(&$variables) {
  if (\Drupal::config('shanfont.settings')->get('font_mode') !== 'theme_defaults') {
    $variables['#attached']['library'][] = 'shanfont/shan-fonts';
  }
}
```

## Performance Considerations

- Fonts use `font-display: swap` for optimal loading
- CDN loading reduces server bandwidth
- Local fallback ensures reliability
- Custom selection mode loads only needed variants
- CSS is optimized and minification-ready

## Browser Support

- All modern browsers supporting WOFF2
- Fallback fonts for older browsers
- Progressive enhancement approach

## Security

- Input validation on all form submissions
- XSS protection through Drupal's render system
- Safe external URL handling for CDN
- Proper file path handling for local fonts

## Troubleshooting

### Fonts Not Loading
1. Check if font files exist in the `fonts/` directory
2. Verify file permissions (readable by web server)
3. Check browser developer tools for loading errors
4. Ensure CDN URL is accessible

### Configuration Not Saving
1. Clear Drupal cache: `drush cr`
2. Check user permissions for "administer site configuration"
3. Verify database connectivity

### Performance Issues
1. Use Custom Selection mode to load fewer fonts
2. Enable CSS aggregation and compression
3. Consider CDN for better global performance

## Development

### Contributing
1. Follow Drupal coding standards
2. Write comprehensive tests
3. Update documentation
4. Test across supported Drupal versions

### Testing
```bash
# Run coding standards check
phpcs --standard=Drupal shanfont/

# Run PHPUnit tests
phpunit modules/custom/shanfont/tests/
```

## License

This module is licensed under the GNU General Public License v2.0 or later.

## Support

- Official website: https://shanfont.com
- Module issues: Create issue in project repository
- Drupal community: https://drupal.org/project/shanfont

## Changelog

### 1.0.0
- Initial release
- Multiple font mode support
- CDN with local fallback
- Responsive design
- Accessibility features
- Performance optimization

## License
This module is licensed under the GPLv2 or later.
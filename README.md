# ASN Network Information Web Template

A lightweight, customizable web template designed for displaying ASN (Autonomous System Number) network information. Built specifically for shared hosting environments like cPanel, this template requires minimal dependencies and works with basic PHP setups.

## Features

- **Lightweight & Fast**: Minimal dependencies, vanilla JavaScript, and optimized for shared hosting
- **PeeringDB Integration**: Automatic data fetching from PeeringDB API
- **Theme Support**: Light/dark mode with system preference detection
- **Responsive Design**: Mobile-friendly layout that works across all devices
- **Easy Configuration**: Simple PHP configuration file
- **Shared Hosting Ready**: Works out of the box on cPanel and similar environments
- **No Build Process**: Direct deployment without compilation steps

## Requirements

- PHP 7.4+ (compatible with most shared hosting providers)
- Basic web server (Apache/Nginx)
- HTTPS for secure API calls (optional but recommended)

## Quick Start

1. Download the latest release
2. Upload files to your web hosting directory
3. Edit `config.php` with your network details
4. Visit your website!

## Configuration

Edit `config.php` to customize your template:

```php
// Network Configuration
define('ASN', '47272');
define('PEERINGDB_NET_ID', '34220');
define('NETWORK_NAME', 'HYEHOST');

// Theme Configuration
define('PRIMARY_COLOR', '#ff5733');
define('LOGO_PATH', '');
define('LOGO_HEIGHT', '40');
define('DEFAULT_THEME', 'light');

// Display Configuration
define('SORT_BY_SPEED', true);
define('SORT_ALPHABETICALLY', true);
define('SHOW_PEERING_POLICY', true);
```

## Installation on Shared Hosting

1. **cPanel Installation**:
   - Log in to your cPanel account
   - Navigate to File Manager
   - Upload the template files to your desired directory
   - Set file permissions to 644 for files and 755 for directories

2. **Direct FTP Upload**:
   - Connect to your hosting via FTP
   - Upload all files to your web directory
   - Ensure proper file permissions

## Optional Features

- **Markdown Support**: If your hosting supports Composer, you can install Parsedown for enhanced policy formatting
- **Cookie Consent**: Built-in cookie notice that's easy to configure
- **Custom Network Tools**: Add links to BGP tools, Looking Glass, etc.

## Performance

- No database required
- Minimal server requirements
- Cached API responses
- Optimized assets
- Fast page load times

## Security

- Input sanitization
- No external dependencies by default
- Configurable cookie consent
- Safe for shared hosting environments

## Demo

Visit [47272.net/asn-web-template](https://47272.net/asn-web-template) to see a live demo.

## Support

- Create an issue on GitHub
- Compatible with most shared hosting providers
- Tested on popular cPanel hosts

## License

MIT License - feel free to use for your network!

---

Made with ❤️ for the network community

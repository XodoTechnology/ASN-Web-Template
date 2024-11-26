# ASN Network Information Web Template

A clean, responsive web template for displaying ASN (Autonomous System Number) network information using PeeringDB data.

## Demo

Check out the live demo at [https://47272.net/asn-web-template](https://47272.net/asn-web-template)

## Features

- Clean, responsive design
- Dark/Light theme toggle
- Automatic PeeringDB data fetching
- Customizable appearance
- Mobile-friendly interface
- File-based caching system
- Markdown support for peering policy

## Requirements

- PHP 7.4 or higher
- cURL extension enabled
- Write permissions for cache directory

## Installation

1. Clone the repository:
```bash
git clone https://github.com/Lostepic/ASN-Web-Template.git
```

2. Configure your web server to serve the directory

3. Copy `config.example.php` to `config.php` and edit the settings

4. Ensure the cache directory is writable:
```bash
chmod 755 cache
```

## Configuration

Edit `config.php` to customize your installation:

```php
// Network Configuration
define('ASN', '47272');                    // Your ASN number
define('PEERINGDB_NET_ID', '34220');       // Your PeeringDB Network ID
define('NETWORK_NAME', 'HYEHOST');         // Your network name

// Network Tools URLs (optional)
define('BGPTOOLS_URL', '');                // BGP.tools URL
define('BGPHE_URL', '');                   // BGP Hurricane Electric URL
define('LOOKING_GLASS_URL', '');           // Your Looking Glass URL

// Theme Configuration
define('PRIMARY_COLOR', '#ff5733');        // Primary color for UI elements
define('LOGO_PATH', '');                   // Path to your logo (empty for text)
define('LOGO_HEIGHT', '40');               // Logo height in pixels
define('DEFAULT_THEME', 'light');          // Default theme ('light' or 'dark')

// Display Configuration
define('SORT_BY_SPEED', true);             // Sort IX list by speed
define('SORT_ALPHABETICALLY', true);       // Sort IX list alphabetically
define('SHOW_PEERING_POLICY', true);       // Show peering policy tab
define('PEERING_POLICY_FILE', 'policy.md'); // Path to policy file

// Footer Configuration
define('SHOW_GITHUB_LINK', true);          // Show GitHub link in footer
define('SHOW_CREDITS', true);              // Show credits in footer
define('COPYRIGHT_TEXT', 'Your Company');   // Copyright text
define('GITHUB_REPO_URL', '');             // Your GitHub repo URL

// Cookie Consent Configuration
define('SHOW_COOKIE_CONSENT', true);       // Show cookie consent banner
define('COOKIE_CONSENT_TEXT', 'This website uses cookies to enhance your experience.');
define('COOKIE_POLICY_URL', '');           // URL to your cookie policy (optional)
```

## Customizing Peering Policy

Create a `policy.md` file in the root directory with your peering policy in Markdown format.

## Cache

PeeringDB data is cached for 6 hours by default. To clear the cache, delete files in the `cache` directory.

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Credits

Created by [AaranCloud](https://github.com/Lostepic) for [HYEHOST](https://hyehost.org).

# BGP Session Viewer

A modern, responsive web interface for viewing BGP session status and network information. Originally created by [AaranCloud](https://github.com/Lostepic) and modified by [Xodo Technology, LLC](https://www.xodo.net).

## Features

- Real-time BGP session status monitoring
- IPv4 and IPv6 protocol support
- PeeringDB integration
- Dark/Light theme support
- Responsive design
- Email address obfuscation
- Session filtering and sorting

## Prerequisites

1. Web server with PHP 7.4+ support
2. [BIRD](https://bird.network.cz/) routing daemon
3. [Birdwatcher](https://github.com/alice-lg/birdwatcher) API service
4. SSL certificate (recommended for production)

## Installation

1. Clone this repository to your web server:
   ```bash
   git clone https://github.com/yourusername/bgp-session-viewer.git
   cd bgp-session-viewer
   ```

2. Configure your web server (Apache example):
   ```apache
   <VirtualHost *:80>
       ServerName network.example.com
       DocumentRoot /path/to/bgp-session-viewer
       
       <Directory /path/to/bgp-session-viewer>
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```

3. Install and configure Birdwatcher on your BIRD routers:
   ```bash
   # Install Birdwatcher
   git clone https://github.com/alice-lg/birdwatcher.git
   cd birdwatcher
   make
   
   # Configure Birdwatcher
   cp etc/birdwatcher.conf.example /etc/birdwatcher.conf
   
   # Edit the configuration
   vim /etc/birdwatcher.conf
   
   # Start the service
   ./birdwatcher -config /etc/birdwatcher.conf
   ```

## Configuration

1. Copy the example config:
   ```bash
   cp config.example.php config.php
   ```

2. Edit `config.php` with your settings:
   ```php
   // Network Information
   define('NETWORK_NAME', 'Your Network Name');
   define('ASN', '65000');
   define('COPYRIGHT_TEXT', 'Your Company Name');
   
   // Contact Information
   define('PEER_EMAIL', 'peering@yourdomain.com');
   define('ABUSE_EMAIL', 'abuse@yourdomain.com');
   
   // Birdwatcher Router Configuration
   $BIRDWATCHER_ROUTERS = [
       'router1' => [
           'name' => 'Router Location',
           'ipv4' => [
               'url' => 'http://rtr.location.example.com:6969',
               'daemon' => 'bird'
           ],
           'ipv6' => [
               'url' => 'http://rtr.location.example.com:6970',
               'daemon' => 'bird6'
           ]
       ]
   ];
   
   // Hidden BGP Sessions (optional)
   $HIDDEN_BGP_SESSIONS = [
       'BHS_',      // Hide all BHS sessions
       'RC_',     // Hide all Route Collector sessions
   ];
   ```

3. Set up the cache directory:
   ```bash
   mkdir cache
   chmod 777 cache
   ```

## Usage

1. Access your installation through a web browser:
   ```
   https://network.example.com
   ```

2. The interface will show:
   - BGP session status
   - Protocol information
   - PeeringDB details
   - Network statistics

## Session Filtering

- Sessions can be hidden using prefixes in `$HIDDEN_BGP_SESSIONS`
- IPv4/IPv6 protocols are automatically filtered based on their suffix (_v4/_v6)
- Sessions are sorted alphabetically by default

## Customization

1. Theme settings in `config.php`:
   ```php
   define('PRIMARY_COLOR', '#20f9ff');
   define('LOGO_PATH', 'path/to/your/logo.png');
   define('LOGO_DARK_PATH', 'path/to/your/logo.png');
   define('DEFAULT_THEME', 'dark');
   ```

2. Custom styling can be added to `style.css`

## Credits

- Original creator: [AaranCloud](https://github.com/Lostepic)
- Modified by: [Xodo Technology, LLC](https://www.xodo.net)

Please keep the credits section in the footer as a courtesy to the original developers.

## License

This project is licensed under the MIT License - see the LICENSE file for details.

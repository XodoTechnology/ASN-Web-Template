<?php
// Network Configuration
define('ASN', '47272');                // Your ASN number
define('PEERINGDB_NET_ID', '34220');   // Your PeeringDB Network ID
define('NETWORK_NAME', 'HYEHOST');     // Your network name

// Network Tools URLs (optional)
define('BGPTOOLS_URL', 'https://bgp.tools/as/47272');            // BGP.tools URL (e.g., https://bgp.tools/as/47272)
define('BGPHE_URL', 'https://bgp.he.net/AS47272');               // BGP Hurricane Electric URL
define('LOOKING_GLASS_URL', 'https://lg.hyehost.org');       // Your Looking Glass URL

// Theme Configuration
define('PRIMARY_COLOR', '#ff5733'); // Primary color for buttons, links, etc.
define('LOGO_PATH', ''); // Path to your logo image (leave empty to use text)
define('LOGO_HEIGHT', '40'); // Logo height in pixels
define('DEFAULT_THEME', 'light'); // Can be 'light' or 'dark'

// Display Configuration
define('SORT_BY_SPEED', true); // Sort IX list by port speed (largest first)
define('SORT_ALPHABETICALLY', true); // Sort IX list alphabetically within speed groups
define('SHOW_PEERING_POLICY', true); // Show the peering policy tab
define('PEERING_POLICY_FILE', __DIR__ . '/policy.md'); // Path to peering policy file

// Footer Configuration
define('SHOW_GITHUB_LINK', true); // Set to false to hide GitHub link
define('SHOW_CREDITS', true); // Toggle credits visibility
define('COPYRIGHT_TEXT', 'HYEHOST'); // Your copyright text
define('GITHUB_REPO_URL', 'https://github.com/Lostepic/ASN-Web-Template');

// Cookie Consent Configuration
define('SHOW_COOKIE_CONSENT', true); // Show cookie consent banner
define('COOKIE_CONSENT_TEXT', 'This website uses cookies to enhance your experience.');
define('COOKIE_POLICY_URL', ''); // URL to your cookie policy (optional)

// API URLs
define('PEERINGDB_API_URL', 'https://www.peeringdb.com/api/net/' . PEERINGDB_NET_ID);

// Cache Configuration
define('CACHE_FILE', __DIR__ . '/cache/peeringdb_data.json');
define('CACHE_DURATION', 6 * 3600); // 6 hours in seconds

// Create cache directory if it doesn't exist
if (!file_exists(dirname(CACHE_FILE))) {
    mkdir(dirname(CACHE_FILE), 0777, true);
}

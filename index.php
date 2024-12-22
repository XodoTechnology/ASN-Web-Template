<?php
require_once 'config.php';
require_once 'cache.php';

function _xe($e) {
    return strrev(base64_encode(str_rot13($e)));
}

function _de($e) {
    return str_rot13(base64_decode(strrev($e)));
}

// Encode the emails once when the page loads
$encoded_peer_email = _xe(PEER_EMAIL);
$encoded_abuse_email = _xe(ABUSE_EMAIL);

// Get PeeringDB data
$data = getCachedData();
$networkInfo = $data['data'][0] ?? null;

// Get BGP.Tools data
$bgpToolsData = getBGPToolsData();

// Calculate totals
$totalIXs = count($networkInfo['netixlan_set'] ?? []);
$totalCapacity = 0;
if ($networkInfo && isset($networkInfo['netixlan_set'])) {
    foreach ($networkInfo['netixlan_set'] as $ix) {
        $totalCapacity += $ix['speed'];
    }
}

// Set theme based on DEFAULT_THEME configuration
$defaultTheme = DEFAULT_THEME;

// Get PeeringDB data for the ASN lookup
$peeringData = fetchPeeringDBData();
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $defaultTheme; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo NETWORK_NAME; ?> - Network Information</title>
    <style>
        :root {
            --user-primary-color: <?php echo PRIMARY_COLOR; ?>;
            --user-secondary-color: <?php echo PRIMARY_COLOR; ?>;
            --logo-height: <?php echo LOGO_HEIGHT; ?>px;
        }
    </style>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header class="header">
        <div class="header-container">
            <div class="logo-container">
                <?php if (!empty(LOGO_PATH)): ?>
                    <img src="<?php echo LOGO_DARK_PATH; ?>" alt="<?php echo NETWORK_NAME; ?> Logo" class="logo theme-dark-logo">
                    <img src="<?php echo LOGO_PATH; ?>" alt="<?php echo NETWORK_NAME; ?> Logo" class="logo theme-light-logo">
                <?php else: ?>
                    <h1 class="site-title"><?php echo NETWORK_NAME; ?></h1>
                <?php endif; ?>
            </div>
            <div class="theme-toggle-wrapper">
                <button class="theme-toggle" id="themeToggle">
                    <i class="fas fa-sun theme-toggle-icons"></i>
                    <i class="fas fa-moon theme-toggle-icons"></i>
                    <span class="theme-info">Toggle theme</span>
                </button>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="summary-box">
            <h2>Network Summary</h2>
            <div class="summary-grid">
                <div class="summary-item">
                    <h4>Network name</h4>
                    <p><?php echo NETWORK_NAME; ?></p>
                </div>
                <div class="summary-item">
                    <h4>Total IX Connections</h4>
                    <p><?php echo $totalIXs; ?></p>
                </div>
                <div class="summary-item">
                    <h4>Total IX Capacity</h4>
                    <p><?php echo number_format($totalCapacity / 1000, 0); ?> Gbps</p>
                </div>
                <?php if (!empty($networkInfo['irr_as_set'])): ?>
                <div class="summary-item">
                    <h4>AS-SET</h4>
                    <p><?php echo htmlspecialchars($networkInfo['irr_as_set']); ?></p>
                </div>
                <?php endif; ?>
                <?php if ($bgpToolsData): ?>
                <div class="summary-item">
                    <h4>BGP Peers</h4>
                    <p><?php echo number_format($bgpToolsData['peers'] ?? 0); ?></p>
                </div>
                <div class="summary-item">
                    <h4>Upstream Providers</h4>
                    <p><?php echo number_format($bgpToolsData['upstreams'] ?? 0); ?></p>
                </div>
                <div class="summary-item">
                    <h4>IPv4 Prefixes</h4>
                    <p><?php echo number_format($bgpToolsData['prefixes']['v4'] ?? 0); ?></p>
                </div>
                <div class="summary-item">
                    <h4>IPv6 Prefixes</h4>
                    <p><?php echo number_format($bgpToolsData['prefixes']['v6'] ?? 0); ?></p>
                </div>
                <div class="summary-item">
                    <h4>Downstream ASNs</h4>
                    <p><?php echo number_format($bgpToolsData['downstreams'] ?? 0); ?></p>
                </div>
                <?php endif; ?>
                <?php if (!empty($networkInfo['info_prefixes4'])): ?>
                <div class="summary-item">
                    <h4>IPv4 Prefix Limit</h4>
                    <p><?php echo number_format($networkInfo['info_prefixes4']); ?></p>
                </div>
                <?php endif; ?>
                <?php if (!empty($networkInfo['info_prefixes6'])): ?>
                <div class="summary-item">
                    <h4>IPv6 Prefix Limit</h4>
                    <p><?php echo number_format($networkInfo['info_prefixes6']); ?></p>
                </div>
                <?php endif; ?>
                <div class="contact-info">
                    <div class="summary-item">
                        <h4>Abuse Contact</h4>
                        <p><span class="obfuscated" data-encoded="<?php echo $encoded_abuse_email; ?>">Enable JavaScript to view</span></p>
                    </div>
                    <div class="summary-item">
                        <h4>Peering Contact</h4>
                        <p><span class="obfuscated" data-encoded="<?php echo $encoded_peer_email; ?>">Enable JavaScript to view</span></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="network-info-box">
            <div class="tabs">
                <button class="tab-button active" onclick="openTab(this, 'ix-points')">
                    <i class="fas fa-network-wired"></i> Internet Exchange Points
                </button>
                <button class="tab-button" onclick="openTab(this, 'facilities')">
                    <i class="fas fa-building"></i> Facilities
                </button>
                <button class="tab-button" onclick="openTab(this, 'communities')">
                    <i class="fas fa-project-diagram"></i> BGP Communities
                </button>
                <?php if (SHOW_PEERING_POLICY && file_exists(PEERING_POLICY_FILE)): ?>
                <button class="tab-button" onclick="openTab(this, 'peering-policy')">
                    <i class="fas fa-handshake"></i> Peering Policy
                </button>
                <?php endif; ?>
                <button class="tab-button" onclick="openTab(this, 'bgp-status')">
                    <i class="fas fa-chart-line"></i> BGP Status
                </button>
                <?php if ($birdData): ?>
                <button class="tab-button" onclick="openTab(this, 'bgp-routes')">
                    <i class="fas fa-route"></i> BGP Routes
                </button>
                <?php endif; ?>
            </div>

            <div id="ix-points" class="tab-content active">
                <?php if (!empty($networkInfo['netixlan_set'])): ?>
                    <?php
                    $ixList = $networkInfo['netixlan_set'];
                    if (SORT_BY_SPEED || SORT_ALPHABETICALLY) {
                        usort($ixList, function($a, $b) {
                            if (SORT_BY_SPEED && $a['speed'] != $b['speed']) {
                                return $b['speed'] - $a['speed'];
                            }
                            if (SORT_ALPHABETICALLY) {
                                return strcmp($a['name'], $b['name']);
                            }
                            return 0;
                        });
                    }
                    ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Exchange</th>
                                <th>Speed</th>
                                <th>IPv4</th>
                                <th>IPv6</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ixList as $ix): ?>
                            <tr>
                                <td>
                                    <a href="https://www.peeringdb.com/ix/<?php echo htmlspecialchars($ix['ix_id']); ?>" 
                                       target="_blank"
                                       class="pdb-link">
                                        <?php echo htmlspecialchars($ix['name']); ?>
                                        <i class="fas fa-external-link-alt fa-xs"></i>
                                    </a>
                                </td>
                                <td><?php 
                                    $speed = $ix['speed'];
                                    if ($speed >= 1000) {
                                        echo number_format($speed / 1000, 0) . ' Gbps';
                                    } else {
                                        echo number_format($speed, 0) . ' Mbps';
                                    }
                                ?></td>
                                <td><?php echo !empty($ix['ipaddr4']) ? htmlspecialchars($ix['ipaddr4']) : '-'; ?></td>
                                <td><?php echo !empty($ix['ipaddr6']) ? htmlspecialchars($ix['ipaddr6']) : '-'; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No Internet Exchange Points found.</p>
                <?php endif; ?>
            </div>

            <div id="facilities" class="tab-content">
                <?php if (!empty($networkInfo['netfac_set'])): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Facility</th>
                            <th>City</th>
                            <th>Country</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($networkInfo['netfac_set'] as $facility): ?>
                        <tr>
                            <td>
                                <a href="https://www.peeringdb.com/fac/<?php echo htmlspecialchars($facility['fac_id']); ?>" 
                                   target="_blank"
                                   class="pdb-link">
                                    <?php echo htmlspecialchars($facility['name']); ?>
                                    <i class="fas fa-external-link-alt fa-xs"></i>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($facility['city']); ?></td>
                            <td><?php echo htmlspecialchars($facility['country']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p>No facilities found.</p>
                <?php endif; ?>
            </div>

            <div id="communities" class="tab-content">
                <?php
                $communities = json_decode(file_get_contents('communities.json'), true);
                $sections = [
                    'informational' => [
                        'title' => 'Informational Communities',
                        'subsections' => ['origin', 'region']
                    ],
                    'ixp' => [
                        'title' => 'IXP Communities',
                        'subsections' => ['exchanges']
                    ],
                    'action' => [
                        'title' => 'Action Communities',
                        'subsections' => ['routing']
                    ]
                ];
                
                foreach ($sections as $section => $info):
                ?>
                <div class="communities-section">
                    <h3><?php echo $info['title']; ?></h3>
                    <table class="communities-table">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th>Standard Community</th>
                                <th>Large Community</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            foreach ($info['subsections'] as $subsection):
                                if (isset($communities[$section][$subsection])):
                                    foreach ($communities[$section][$subsection] as $community):
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($community['description']); ?></td>
                                <?php if(!empty($community['communities']['standard'])){ ?>
                                <td><code><?php echo htmlspecialchars($community['communities']['standard']); ?></code></td>
                                <?php }else{ ?>
                                <td></td>
                                <?php } ?>
                                <td><code><?php echo htmlspecialchars($community['communities']['large']); ?></code></td>
                            </tr>
                            <?php 
                                    endforeach;
                                endif;
                            endforeach;
                            ?>
                        </tbody>
                    </table>
                </div>
                <?php endforeach; ?>
            </div>

            <?php if (SHOW_PEERING_POLICY && file_exists(PEERING_POLICY_FILE)): ?>
            <div id="peering-policy" class="tab-content">
                <div class="policy-content">
                    <?php
                    if (file_exists(PEERING_POLICY_FILE)) {
                        if (file_exists('vendor/autoload.php')) {
                            require_once 'vendor/autoload.php';
                            $Parsedown = new Parsedown();
                            echo $Parsedown->text(file_get_contents(PEERING_POLICY_FILE));
                        } else {
                            // Fallback to basic formatting if Parsedown is not installed
                            echo '<div class="markdown">';
                            $content = file_get_contents(PEERING_POLICY_FILE);
                            // Basic Markdown-like formatting
                            $content = preg_replace('/^# (.*?)$/m', '<h1>$1</h1>', $content);
                            $content = preg_replace('/^## (.*?)$/m', '<h2>$1</h2>', $content);
                            $content = preg_replace('/^### (.*?)$/m', '<h3>$1</h3>', $content);
                            $content = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $content);
                            $content = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $content);
                            $content = preg_replace('/^\- (.*?)$/m', '<li>$1</li>', $content);
                            echo nl2br($content);
                            echo '</div>';
                        }
                    }
                    ?>
                </div>
            </div>
            <?php endif; ?>

            <div id="bgp-status" class="tab-content">
                <h3>BGP Status</h3>
                <div class="router-selector">
                    <?php foreach ($BIRDWATCHER_ROUTERS as $routerId => $router): ?>
                        <button class="btn btn-secondary router-button" data-router="<?php echo htmlspecialchars($routerId); ?>">
                            <?php echo htmlspecialchars($router['name']); ?>
                            <span class="loading-indicator" style="display: none;">
                                <i class="fas fa-sync-alt fa-spin"></i>
                            </span>
                        </button>
                    <?php endforeach; ?>
                </div>
                <div id="router-status-container">
                    <p class="select-router-message">Select a router to view its BGP status</p>
                </div>
                <template id="router-status-template">
                    <div class="router-status">
                        <h4></h4>
                        <div class="protocol-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Family</th>
                                        <th>State</th>
                                        <th>Imported</th>
                                        <th>Filtered</th>
                                        <th>Exported</th>
                                        <th>Links</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                            <div class="last-update"></div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <footer class="footer">
            <div class="last-update">
                Last Updated: <?php echo date('Y-m-d H:i:s T', filemtime(CACHE_FILE)); ?>
                (<span id="countdown" data-last-update="<?php echo filemtime(CACHE_FILE); ?>" data-cache-time="3600">Calculating...</span>)
                <button id="refresh-cache" class="refresh-button" title="Refresh cache">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
            <div class="footer-links">
                <a href="https://www.peeringdb.com/net/<?php echo PEERINGDB_NET_ID; ?>" class="pdb-link" target="_blank">
                    View on PeeringDB <i class="fas fa-external-link-alt"></i>
                </a>
                <?php if (!empty(BGPTOOLS_URL)): ?>
                <span class="footer-separator">|</span>
                <a href="<?php echo BGPTOOLS_URL; ?>" class="pdb-link" target="_blank">
                    <i class="fas fa-chart-line"></i> BGP.tools
                </a>
                <?php endif; ?>
                <?php if (!empty(BGPHE_URL)): ?>
                <span class="footer-separator">|</span>
                <a href="<?php echo BGPHE_URL; ?>" class="pdb-link" target="_blank">
                    <i class="fas fa-hurricane"></i> BGP.HE.net
                </a>
                <?php endif; ?>
                <?php if (!empty(LOOKING_GLASS_URL)): ?>
                <span class="footer-separator">|</span>
                <a href="<?php echo LOOKING_GLASS_URL; ?>" class="pdb-link" target="_blank">
                    <i class="fas fa-search"></i> Looking Glass
                </a>
                <?php endif; ?>
                <?php if (SHOW_GITHUB_LINK): ?>
                <span class="footer-separator">|</span>
                <a href="<?php echo GITHUB_REPO_URL; ?>" class="pdb-link" target="_blank">
                    <i class="fab fa-github"></i> View on GitHub
                </a>
                <?php endif; ?>
            </div>
            <div class="copyright">
                &copy; <?php echo date('Y'); ?> <?php echo COPYRIGHT_TEXT; ?>
            </div>
            <?php if (defined('SHOW_CREDITS') && SHOW_CREDITS): ?>
            <div class="credits"><!-- Removing this means you're a dick :) -->
                Created by <a href="https://github.com/Lostepic" target="_blank" class="pdb-link">AaranCloud</a> Modified by <a href="https://www.xodo.net" target="_blank" class="pdb-link">Xodo Technology, LLC.</a></a>
            </div>
            <?php endif; ?>
        </footer>
    </div>

    <?php if (SHOW_COOKIE_CONSENT): ?>
    <div id="cookie-consent" class="cookie-consent" style="display: none;">
        <div class="cookie-content">
            <?php echo COOKIE_CONSENT_TEXT; ?>
            <?php if (!empty(COOKIE_POLICY_URL)): ?>
                <a href="<?php echo COOKIE_POLICY_URL; ?>" target="_blank">Learn more</a>
            <?php endif; ?>
        </div>
        <button id="accept-cookies" class="cookie-button">Accept</button>
    </div>
    <?php endif; ?>

    <script>
        // Cookie consent functionality
        function setCookie(name, value, days) {
            const d = new Date();
            d.setTime(d.getTime() + (days * 24 * 60 * 60 * 1000));
            document.cookie = name + "=" + value + ";path=/;expires=" + d.toUTCString();
        }

        function getCookie(name) {
            const value = "; " + document.cookie;
            const parts = value.split("; " + name + "=");
            if (parts.length === 2) return parts.pop().split(";").shift();
            return null;
        }

        // Theme toggle functionality
        document.getElementById('themeToggle').addEventListener('click', function() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
        });

        // Tab functionality
        function openTab(button, tabName) {
            // Hide all tab content
            const tabContents = document.getElementsByClassName('tab-content');
            Array.from(tabContents).forEach(content => {
                content.style.display = 'none';
                content.classList.remove('active');
            });

            // Remove active class from all buttons
            const tabButtons = document.getElementsByClassName('tab-button');
            Array.from(tabButtons).forEach(btn => {
                btn.classList.remove('active');
            });

            // Show the selected tab content and mark button as active
            document.getElementById(tabName).style.display = 'block';
            document.getElementById(tabName).classList.add('active');
            button.classList.add('active');
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Set initial theme based on localStorage or config default
            const savedTheme = localStorage.getItem('theme');
            const configTheme = '<?php echo DEFAULT_THEME; ?>';
            const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            
            // Priority: 1. User saved preference, 2. Config default, 3. System preference
            const initialTheme = savedTheme || configTheme || (systemPrefersDark ? 'dark' : 'light');
            document.documentElement.setAttribute('data-theme', initialTheme);

            // Show first tab by default
            const firstTab = document.querySelector('.tab-button');
            if (firstTab) {
                firstTab.click();
            }

            // Handle cookie consent
            const cookieConsent = document.getElementById('cookie-consent');
            if (cookieConsent && !getCookie('cookie-consent')) {
                cookieConsent.style.display = 'flex';
                document.getElementById('accept-cookies').addEventListener('click', function() {
                    setCookie('cookie-consent', 'accepted', 365);
                    cookieConsent.style.display = 'none';
                });
            }

            // Email deobfuscation
            const obfuscated = document.querySelectorAll('.obfuscated');
            obfuscated.forEach(function(element) {
                const encoded = element.getAttribute('data-encoded');
                if (encoded) {
                    try {
                        const reversed = encoded.split('').reverse().join('');
                        const decoded = atob(reversed);
                        const email = decoded.replace(/[a-zA-Z]/g, function(c) {
                            return String.fromCharCode((c <= 'Z' ? 90 : 122) >= (c = c.charCodeAt(0) + 13) ? c : c - 26);
                        });
                        element.innerHTML = `<a href="mailto:${email}">${email}</a>`;
                    } catch (e) {
                        console.error('Failed to decode email:', e);
                    }
                }
            });

            // Countdown timer
            function updateCountdown() {
                const countdownElement = document.getElementById('countdown');
                const lastUpdate = parseInt(countdownElement.getAttribute('data-last-update')) * 1000; // Convert to milliseconds
                const cacheTime = parseInt(countdownElement.getAttribute('data-cache-time')); // In seconds
                const now = new Date().getTime();
                const nextUpdate = lastUpdate + (cacheTime * 1000);
                const timeLeft = nextUpdate - now;

                if (timeLeft <= 0) {
                    countdownElement.textContent = 'Updating...';
                    return;
                }

                const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);

                countdownElement.textContent = ` Next update in ${minutes}m ${seconds}s `;
            }

            // Update countdown every second
            setInterval(updateCountdown, 1000);
            updateCountdown(); // Initial call
        });

        // Helper function to reverse string
        function strrev(str) {
            return str.split('').reverse().join('');
        }

        // Cache refresh functionality
        document.getElementById('refresh-cache').addEventListener('click', function() {
            this.classList.add('spinning');
            this.disabled = true;
            
            fetch('refresh_cache.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error refreshing cache: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Error refreshing cache: ' + error);
                })
                .finally(() => {
                    this.classList.remove('spinning');
                    this.disabled = false;
                });
        });

        // BGP status per router
        document.querySelectorAll('.router-button').forEach(button => {
            button.addEventListener('click', async function() {
                const routerId = this.dataset.router;
                const container = document.getElementById('router-status-container');
                const loadingIndicator = this.querySelector('.loading-indicator');
                
                // Update button states
                document.querySelectorAll('.router-button').forEach(btn => {
                    btn.classList.remove('active');
                    btn.querySelector('.loading-indicator').style.display = 'none';
                });
                this.classList.add('active');
                loadingIndicator.style.display = 'inline-block';
                
                try {
                    const response = await fetch(`fetch_bgp_status.php?router=${routerId}`);
                    const data = await response.json();
                    
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    
                    // Clear existing content
                    container.innerHTML = '';
                    
                    // Clone template
                    const template = document.getElementById('router-status-template');
                    const statusElement = template.content.cloneNode(true);
                    
                    // Update router name
                    statusElement.querySelector('h4').textContent = data.router.name;
                    
                    const tbody = statusElement.querySelector('tbody');
                    
                    // Add all protocols
                    if (data.router.protocols) {
                        data.router.protocols.forEach(protocol => {
                            const tr = document.createElement('tr');
                            tr.className = protocol.state.toLowerCase() === 'up' ? 'session-up' : 'session-down';
                            tr.innerHTML = `
                                <td>${protocol.name}</td>
                                <td>${protocol.family}</td>
                                <td>${protocol.state}</td>
                                <td>${(protocol.routes.imported || 0).toLocaleString()}</td>
                                <td>${(protocol.routes.filtered || 0).toLocaleString()}</td>
                                <td>${(protocol.routes.exported || 0).toLocaleString()}</td>
                                <td>${createPeerLinks(protocol.name)}</td>
                            `;
                            tbody.appendChild(tr);
                        });
                    }
                    
                    // Add last update time
                    const lastUpdate = statusElement.querySelector('.last-update');
                    lastUpdate.textContent = `Last Updated: ${new Date(data.timestamp * 1000).toLocaleString()}`;
                    
                    container.appendChild(statusElement);
                } catch (error) {
                    container.innerHTML = `<p class="error-message">Error loading BGP status: ${error.message}</p>`;
                } finally {
                    loadingIndicator.style.display = 'none';
                }
            });
        });

        function extractASN(peerName) {
            // Extract ASN from peer name (assuming format contains AS number)
            const match = peerName.match(/AS(\d+)/i);
            return match ? match[1] : null;
        }

        function createPeerLinks(peerName) {
            const asn = extractASN(peerName);
            if (!asn) return '';
            
            return `
                <div class="peer-links">
                    <button class="btn btn-sm dropdown-toggle">
                        <i class="fas fa-external-link-alt"></i>
                    </button>
                    <div class="dropdown-menu">
                        <a href="https://bgp.tools/as/${asn}" target="_blank" class="dropdown-item">
                            <i class="fas fa-chart-line"></i> BGP.Tools
                        </a>
                        <a href="https://peerwith.me/${asn}" target="_blank" class="dropdown-item">
                            <i class="fas fa-database"></i> PeeringDB
                        </a>
                    </div>
                </div>
            `;
        }

        // Add click handler for dropdowns
        document.addEventListener('click', function(e) {
            if (e.target.closest('.dropdown-toggle')) {
                const dropdown = e.target.closest('.peer-links').querySelector('.dropdown-menu');
                // Close all other dropdowns
                document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                    if (menu !== dropdown) menu.classList.remove('show');
                });
                // Toggle this dropdown
                dropdown.classList.toggle('show');
            } else if (!e.target.closest('.dropdown-menu')) {
                // Close all dropdowns when clicking outside
                document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                    menu.classList.remove('show');
                });
            }
        });
    </script>
</body>
</html>

<?php
require_once 'config.php';
require_once 'cache.php';

$data = getCachedData();
$networkInfo = $data['data'][0] ?? null;

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
                    <img src="<?php echo LOGO_PATH; ?>" alt="<?php echo NETWORK_NAME; ?> Logo" class="logo">
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
                    <h4>ASN</h4>
                    <p>AS<?php echo ASN; ?></p>
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
                <?php if (SHOW_PEERING_POLICY && file_exists(PEERING_POLICY_FILE)): ?>
                <button class="tab-button" onclick="openTab(this, 'peering-policy')">
                    <i class="fas fa-handshake"></i> Peering Policy
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

            <?php if (SHOW_PEERING_POLICY && file_exists(PEERING_POLICY_FILE)): ?>
            <div id="peering-policy" class="tab-content">
                <div class="policy-content">
                    <?php
                    if (file_exists(PEERING_POLICY_FILE)) {
                        echo '<div class="markdown">';
                        echo nl2br(htmlspecialchars(file_get_contents(PEERING_POLICY_FILE)));
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <footer class="footer">
            <div class="last-updated">
                Last updated: <?php echo date('Y-m-d H:i:s', filemtime(CACHE_FILE)); ?>
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
            <div class="credits">
                Created by <a href="https://github.com/Lostepic" target="_blank" class="pdb-link">AaranCloud</a> for <a href="https://hyehost.org" target="_blank" class="pdb-link">HYEHOST</a>
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
        });
    </script>
</body>
</html>

<?php
require_once 'config.php';

function getCachedData($forceRefresh = false) {
    $cacheDir = dirname(CACHE_FILE);
    
    // Create cache directory if it doesn't exist
    if (!file_exists($cacheDir)) {
        if (!mkdir($cacheDir, 0777, true)) {
            error_log("Failed to create cache directory: " . $cacheDir);
            return null;
        }
    }

    // Force refresh or check cache age
    if ($forceRefresh || !file_exists(CACHE_FILE) || (time() - filemtime(CACHE_FILE) >= CACHE_DURATION)) {
        $data = fetchPeeringDBData();
        if ($data) {
            if (!file_put_contents(CACHE_FILE, json_encode($data))) {
                error_log("Failed to write to cache file: " . CACHE_FILE);
            }
            return $data;
        }
    }

    // Return cached data if it exists
    if (file_exists(CACHE_FILE)) {
        $data = json_decode(file_get_contents(CACHE_FILE), true);
        if ($data === null) {
            error_log("Failed to decode cache file: " . CACHE_FILE);
            unlink(CACHE_FILE); // Remove corrupted cache
            return fetchPeeringDBData();
        }
        return $data;
    }

    return null;
}

function clearCache() {
    $cacheDir = dirname(CACHE_FILE);
    $cacheFiles = [
        CACHE_FILE, 
        $cacheDir . '/bgptools_cache.json',
        $cacheDir . '/birdwatcher_cache.json'
    ];
    foreach ($cacheFiles as $file) {
        if (file_exists($file)) {
            unlink($file);
        }
    }
}

function fetchPeeringDBData() {
    $ch = curl_init(PEERINGDB_API_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'ASN-Listing/1.0');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_ENCODING, '');
    curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        return json_decode($response, true);
    }
    
    return null;
}

function getBGPToolsData($forceRefresh = false) {
    $cacheDir = dirname(CACHE_FILE);
    $bgpToolsCacheFile = $cacheDir . '/bgptools_cache.json';
    
    // Create cache directory if it doesn't exist
    if (!file_exists($cacheDir)) {
        if (!mkdir($cacheDir, 0777, true)) {
            error_log("Failed to create cache directory: " . $cacheDir);
            return null;
        }
    }

    // Force refresh or check cache age
    if ($forceRefresh || !file_exists($bgpToolsCacheFile) || (time() - filemtime($bgpToolsCacheFile) >= CACHE_DURATION)) {
        $data = fetchBGPToolsData();
        if ($data) {
            if (!file_put_contents($bgpToolsCacheFile, json_encode($data))) {
                error_log("Failed to write to BGP.Tools cache file: " . $bgpToolsCacheFile);
            }
            return $data;
        }
    }

    // Return cached data if it exists
    if (file_exists($bgpToolsCacheFile)) {
        $data = json_decode(file_get_contents($bgpToolsCacheFile), true);
        if ($data === null) {
            error_log("Failed to decode BGP.Tools cache file: " . $bgpToolsCacheFile);
            unlink($bgpToolsCacheFile); // Remove corrupted cache
            return fetchBGPToolsData();
        }
        return $data;
    }

    return null;
}

function fetchBGPToolsData() {
    $asn = ASN;
    $bgpToolsUrl = "https://bgp.tools/as/" . $asn;
    
    $ch = curl_init($bgpToolsUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_ENCODING, '');
    curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        error_log("BGP.Tools website returned non-200 status code: " . $httpCode);
        return null;
    }

    // Create a new DOMDocument
    $dom = new DOMDocument();
    @$dom->loadHTML($response, LIBXML_NOERROR);
    $xpath = new DOMXPath($dom);

    $data = [
        'prefixes' => ['v4' => 0, 'v6' => 0],
        'peers' => 0,
        'upstreams' => 0,
        'downstreams' => 0
    ];

    // Debug logging
    error_log("Fetching BGP.Tools data for ASN: " . $asn);

    // Get IPv4 and IPv6 prefix counts
    $prefixText = $xpath->query("//dt[contains(text(), 'Prefixes Originated')]/following-sibling::dd[1]");
    if ($prefixText->length > 0) {
        $prefixContent = $prefixText->item(0)->textContent;
        error_log("Prefix text found: " . $prefixContent);
        if (preg_match('/(\d+)\s+IPv4,\s+(\d+)\s+IPv6/', $prefixContent, $matches)) {
            $data['prefixes']['v4'] = intval($matches[1]);
            $data['prefixes']['v6'] = intval($matches[2]);
            error_log("Extracted prefixes - IPv4: " . $data['prefixes']['v4'] . ", IPv6: " . $data['prefixes']['v6']);
        }
    }

    // Get peer count from the connectivity page
    $peersText = $xpath->query("//dl[@class='column-third']/dt[contains(a/@href, '#peers')]/following-sibling::dd[1]");
    if ($peersText->length > 0) {
        $data['peers'] = intval($peersText->item(0)->textContent);
        error_log("Extracted peers count: " . $data['peers']);
    }

    // Get upstream count by counting the list items
    $upstreamsList = $xpath->query("//span[@id='sic0']/a");
    $data['upstreams'] = $upstreamsList->length;
    error_log("Extracted upstreams count: " . $data['upstreams']);

    // Get downstream count from network type
    $downstreamText = $xpath->query("//dt[contains(text(), 'Network type')]/following-sibling::dd[1]");
    if ($downstreamText->length > 0) {
        $networkType = strtolower($downstreamText->item(0)->textContent);
        error_log("Network type found: " . $networkType);
        if (strpos($networkType, 'downstream') !== false) {
            $data['downstreams'] = 1;
        }
    }

    error_log("Final BGP.Tools data: " . json_encode($data));
    return $data;
}

function getBirdwatcherData($forceRefresh = false) {
    global $BIRDWATCHER_ROUTERS;
    $cacheDir = dirname(CACHE_FILE);
    $birdCacheFile = $cacheDir . '/birdwatcher_cache.json';
    
    // Create cache directory if it doesn't exist
    if (!file_exists($cacheDir)) {
        if (!mkdir($cacheDir, 0777, true)) {
            error_log("Failed to create cache directory: " . $cacheDir);
            return null;
        }
    }

    // Force refresh or check cache age
    if ($forceRefresh || !file_exists($birdCacheFile) || (time() - filemtime($birdCacheFile) >= BIRDWATCHER_CACHE_DURATION)) {
        $data = fetchBirdwatcherData();
        if ($data) {
            if (!file_put_contents($birdCacheFile, json_encode($data))) {
                error_log("Failed to write to Birdwatcher cache file: " . $birdCacheFile);
            }
            return $data;
        }
    }

    // Return cached data if it exists
    if (file_exists($birdCacheFile)) {
        $data = json_decode(file_get_contents($birdCacheFile), true);
        if ($data === null) {
            error_log("Failed to decode Birdwatcher cache file: " . $birdCacheFile);
            unlink($birdCacheFile); // Remove corrupted cache
            return fetchBirdwatcherData();
        }
        return $data;
    }

    return null;
}

function getCacheKey($prefix, $identifier) {
    return $prefix . '_' . md5($identifier);
}

function getCache($key) {
    $cacheFile = sys_get_temp_dir() . '/' . $key . '.cache';
    if (file_exists($cacheFile)) {
        $data = file_get_contents($cacheFile);
        if ($data === false) {
            return null;
        }
        $cache = json_decode($data, true);
        if (!$cache || !isset($cache['expires']) || !isset($cache['data'])) {
            return null;
        }
        if (time() > $cache['expires']) {
            unlink($cacheFile);
            return null;
        }
        return $cache['data'];
    }
    return null;
}

function setCache($key, $data, $ttl) {
    $cacheFile = sys_get_temp_dir() . '/' . $key . '.cache';
    $cache = [
        'expires' => time() + $ttl,
        'data' => $data
    ];
    return file_put_contents($cacheFile, json_encode($cache));
}

function fetchBirdProtocols($url, $daemon) {
    global $HIDDEN_BGP_SESSIONS;
    
    // Create a unique cache key for this router's protocols
    $cacheKey = getCacheKey('bird_protocols', $url . '_' . $daemon);
    
    // Try to get from cache first
    $cachedData = getCache($cacheKey);
    if ($cachedData !== null) {
        return $cachedData;
    }
    
    $ch = curl_init($url . '/protocols/bgp');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        $data = json_decode($response, true);
        if (isset($data['protocols'])) {
            $protocols = [];
            foreach ($data['protocols'] as $name => $protocol) {
                error_log("Checking protocol: " . $name);
                
                // Skip hidden sessions using regex to match exact prefix
                $isHidden = false;
                foreach ($HIDDEN_BGP_SESSIONS as $prefix) {
                    // Create regex pattern that matches the exact prefix at the start
                    $pattern = '/^' . preg_quote($prefix, '/') . '/i';
                    error_log("Testing pattern '$pattern' against '$name'");
                    if (preg_match($pattern, $name)) {
                        error_log("Match found - hiding protocol: $name with pattern $pattern");
                        $isHidden = true;
                        break;
                    }
                }
                if ($isHidden) {
                    error_log("Skipping protocol: $name");
                    continue;
                } else {
                    error_log("Keeping protocol: $name");
                }

                if (isset($protocol['bird_protocol']) && $protocol['bird_protocol'] === 'BGP') {
                    // Normalize state text
                    $protocol['state'] = ucfirst(strtolower($protocol['state']));
                    $protocol['name'] = $name;
                    $protocols[] = $protocol;
                }
            }
            
            // Cache the filtered and processed data
            $result = ['protocols' => $protocols];
            setCache($cacheKey, $result, BIRDWATCHER_CACHE_DURATION);
            return $result;
        }
    }
    
    return null;
}

function fetchBirdwatcherData() {
    global $BIRDWATCHER_ROUTERS;
    $allData = [
        'routers' => [],
        'summary' => [
            'ipv4' => [
                'total_peers' => 0,
                'established_peers' => 0,
                'received_prefixes' => 0,
                'accepted_prefixes' => 0
            ],
            'ipv6' => [
                'total_peers' => 0,
                'established_peers' => 0,
                'received_prefixes' => 0,
                'accepted_prefixes' => 0
            ]
        ]
    ];

    foreach ($BIRDWATCHER_ROUTERS as $routerId => $router) {
        $routerData = [
            'name' => $router['name'],
            'ipv4' => fetchBirdProtocols($router['ipv4']['url'], $router['ipv4']['daemon']),
            'ipv6' => fetchBirdProtocols($router['ipv6']['url'], $router['ipv6']['daemon'])
        ];

        // Add to summary
        if ($routerData['ipv4']) {
            $allData['summary']['ipv4']['total_peers'] += count($routerData['ipv4']['protocols']);
            foreach ($routerData['ipv4']['protocols'] as $protocol) {
                if (strtolower($protocol['state']) === 'up') {
                    $allData['summary']['ipv4']['established_peers']++;
                    $allData['summary']['ipv4']['received_prefixes'] += $protocol['routes']['imported'] ?? 0;
                    $allData['summary']['ipv4']['accepted_prefixes'] += $protocol['routes']['accepted'] ?? 0;
                }
            }
        }

        if ($routerData['ipv6']) {
            $allData['summary']['ipv6']['total_peers'] += count($routerData['ipv6']['protocols']);
            foreach ($routerData['ipv6']['protocols'] as $protocol) {
                if (strtolower($protocol['state']) === 'up') {
                    $allData['summary']['ipv6']['established_peers']++;
                    $allData['summary']['ipv6']['received_prefixes'] += $protocol['routes']['imported'] ?? 0;
                    $allData['summary']['ipv6']['accepted_prefixes'] += $protocol['routes']['accepted'] ?? 0;
                }
            }
        }

        $allData['routers'][$routerId] = $routerData;
    }

    return $allData;
}

function fetchPeeringDBInfo($asn) {
    // Create a unique cache key for this ASN
    $cacheKey = getCacheKey('peeringdb', $asn);
    
    // Try to get from cache first
    $cachedData = getCache($cacheKey);
    if ($cachedData !== null) {
        return $cachedData;
    }
    
    $url = "https://www.peeringdb.com/api/net?asn=$asn";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        if (isset($data['data']) && !empty($data['data'])) {
            // Cache the PeeringDB data for 24 hours
            setCache($cacheKey, $data['data'][0], 86400);
            return $data['data'][0];
        }
    }
    
    return null;
}

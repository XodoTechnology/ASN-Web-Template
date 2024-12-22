<?php
require_once 'cache.php';
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_GET['router']) || !isset($BIRDWATCHER_ROUTERS[$_GET['router']])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid router specified']);
    exit;
}

$routerId = $_GET['router'];
$router = $BIRDWATCHER_ROUTERS[$routerId];

// Fetch data for both IPv4 and IPv6
$routerData = [
    'name' => $router['name'],
    'protocols' => []
];

function fetchProtocolsWithFamily($url, $daemon, $family) {
    $data = fetchBirdProtocols($url, $daemon);
    if ($data && isset($data['protocols'])) {
        $filteredProtocols = [];
        foreach ($data['protocols'] as $protocol) {
            // For IPv4 daemon, skip protocols ending with _v6
            if ($family === 'IPv4' && str_ends_with($protocol['name'], '_v6')) {
                continue;
            }
            // For IPv6 daemon, skip protocols ending with _v4
            if ($family === 'IPv6' && str_ends_with($protocol['name'], '_v4')) {
                continue;
            }
            $protocol['family'] = $family;
            $filteredProtocols[] = $protocol;
        }
        return $filteredProtocols;
    }
    return null;
}

// Get IPv4 protocols
if (isset($router['ipv4'])) {
    $ipv4Protocols = fetchProtocolsWithFamily($router['ipv4']['url'], $router['ipv4']['daemon'], 'IPv4');
    if ($ipv4Protocols) {
        $routerData['protocols'] = array_merge($routerData['protocols'], $ipv4Protocols);
    }
}

// Get IPv6 protocols
if (isset($router['ipv6'])) {
    $ipv6Protocols = fetchProtocolsWithFamily($router['ipv6']['url'], $router['ipv6']['daemon'], 'IPv6');
    if ($ipv6Protocols) {
        $routerData['protocols'] = array_merge($routerData['protocols'], $ipv6Protocols);
    }
}

// Sort protocols alphabetically by name
if (!empty($routerData['protocols'])) {
    usort($routerData['protocols'], function($a, $b) {
        return strcmp($a['name'], $b['name']);
    });
}

// If no protocols were found at all, return an error
if (empty($routerData['protocols'])) {
    http_response_code(503);
    echo json_encode(['error' => 'Unable to fetch BGP data from either IPv4 or IPv6']);
    exit;
}

echo json_encode([
    'router' => $routerData,
    'timestamp' => time()
]);

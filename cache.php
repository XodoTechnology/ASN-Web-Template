<?php
require_once 'config.php';

function getCachedData() {
    if (!file_exists(dirname(CACHE_FILE))) {
        mkdir(dirname(CACHE_FILE), 0777, true);
    }

    if (file_exists(CACHE_FILE)) {
        $cacheAge = time() - filemtime(CACHE_FILE);
        if ($cacheAge < CACHE_DURATION) {
            return json_decode(file_get_contents(CACHE_FILE), true);
        }
    }

    $data = fetchPeeringDBData();
    if ($data) {
        file_put_contents(CACHE_FILE, json_encode($data));
        return $data;
    }

    // If fetch fails and cache exists, return cached data regardless of age
    if (file_exists(CACHE_FILE)) {
        return json_decode(file_get_contents(CACHE_FILE), true);
    }

    return null;
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

<?php

/**
 * Visitor tracking system
 * Tracks visitors by IP, country, and region
 */

require_once __DIR__ . '/../config/config.php';

function getVisitorIP() {
    $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
    
    foreach ($ipKeys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function getGeoLocation($ip) {
    // Skip tracking for localhost/private IPs
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
        return ['country' => 'Local', 'region' => 'Local', 'city' => 'Local'];
    }
    
    // Use ip-api.com free service (no API key required)
    $url = "http://ip-api.com/json/{$ip}?fields=status,country,regionName,city";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 2);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
    $response = @curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200 && $response) {
        $data = json_decode($response, true);
        if ($data && $data['status'] === 'success') {
            return [
                'country' => $data['country'] ?? 'Unknown',
                'region' => $data['regionName'] ?? 'Unknown',
                'city' => $data['city'] ?? 'Unknown'
            ];
        }
    }
    
    // Fallback if API fails
    return ['country' => 'Unknown', 'region' => 'Unknown', 'city' => 'Unknown'];
}

function trackVisitor() {
    try {
        $ip = getVisitorIP();
        $geo = getGeoLocation($ip);
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $pageUrl = $_SERVER['REQUEST_URI'] ?? '';
        $referrer = $_SERVER['HTTP_REFERER'] ?? '';
        
        // Update or insert visitor record
        // Check if visitor exists in last 5 minutes (online)
        $checkQuery = query(
            'SELECT id FROM visitors WHERE ip_address = ? AND last_activity > DATE_SUB(NOW(), INTERVAL 5 MINUTE) LIMIT 1',
            [$ip]
        );
        
        if ($checkQuery && $checkQuery->num_rows > 0) {
            // Update existing visitor's last activity
            query(
                'UPDATE visitors SET last_activity = NOW(), page_url = ?, referrer = ? WHERE ip_address = ? AND last_activity > DATE_SUB(NOW(), INTERVAL 5 MINUTE)',
                [$pageUrl, $referrer, $ip]
            );
        } else {
            // Insert new visitor
            query(
                'INSERT INTO visitors (ip_address, country, region, city, user_agent, page_url, referrer) VALUES (?, ?, ?, ?, ?, ?, ?)',
                [$ip, $geo['country'], $geo['region'], $geo['city'], $userAgent, $pageUrl, $referrer]
            );
        }
    } catch (Exception $e) {
        // Silently fail - don't break the site if tracking fails
        error_log('Visitor tracking error: ' . $e->getMessage());
    }
}

// Track visitor if not in admin area
if (!defined('ADMIN_AREA')) {
    trackVisitor();
}


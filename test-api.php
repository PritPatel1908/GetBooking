<?php

/**
 * Quick API Testing Script
 * 
 * Usage: php test-api.php
 * 
 * Make sure your Laravel server is running: php artisan serve
 */

$baseUrl = 'http://localhost:8000/api';

// Colors for terminal output
$green = "\033[32m";
$red = "\033[31m";
$yellow = "\033[33m";
$blue = "\033[34m";
$reset = "\033[0m";

echo "\n{$blue}=== API Testing Script ==={$reset}\n\n";

// Test 1: Public API - Get Grounds
echo "{$yellow}Test 1: Get Grounds (Public API){$reset}\n";
$ch = curl_init($baseUrl . '/grounds');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json'
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    echo "{$green}✓ SUCCESS{$reset} - Status: $httpCode\n";
    $data = json_decode($response, true);
    if (isset($data['data'])) {
        echo "   Found " . count($data['data']) . " grounds\n";
    }
} else {
    echo "{$red}✗ FAILED{$reset} - Status: $httpCode\n";
    echo "   Response: " . substr($response, 0, 100) . "\n";
}
echo "\n";

// Test 2: Login (You need to provide valid credentials)
echo "{$yellow}Test 2: Login API{$reset}\n";
echo "Enter your email: ";
$email = trim(fgets(STDIN));
echo "Enter your password: ";
$password = trim(fgets(STDIN));

$ch = curl_init($baseUrl . '/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'email' => $email,
    'password' => $password
]));
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$data = json_decode($response, true);
curl_close($ch);

if ($httpCode === 200 && isset($data['token'])) {
    echo "{$green}✓ SUCCESS{$reset} - Status: $httpCode\n";
    $token = $data['token'];
    echo "   Token received: " . substr($token, 0, 30) . "...\n";
    echo "   User: " . ($data['user']['name'] ?? 'N/A') . "\n";
    
    // Test 3: Get User with token
    echo "\n{$yellow}Test 3: Get User (Protected API with Token){$reset}\n";
    $ch = curl_init($baseUrl . '/user');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Authorization: Bearer ' . $token
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $userData = json_decode($response, true);
    curl_close($ch);
    
    if ($httpCode === 200) {
        echo "{$green}✓ SUCCESS{$reset} - Status: $httpCode\n";
        echo "   User ID: " . ($userData['id'] ?? 'N/A') . "\n";
        echo "   User Name: " . ($userData['name'] ?? 'N/A') . "\n";
        echo "   User Email: " . ($userData['email'] ?? 'N/A') . "\n";
    } else {
        echo "{$red}✗ FAILED{$reset} - Status: $httpCode\n";
        echo "   Response: " . substr($response, 0, 100) . "\n";
    }
    
    // Test 4: Logout
    echo "\n{$yellow}Test 4: Logout API{$reset}\n";
    $ch = curl_init($baseUrl . '/logout');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Authorization: Bearer ' . $token
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $logoutData = json_decode($response, true);
    curl_close($ch);
    
    if ($httpCode === 200 && isset($logoutData['success']) && $logoutData['success']) {
        echo "{$green}✓ SUCCESS{$reset} - Status: $httpCode\n";
        echo "   Message: " . ($logoutData['message'] ?? 'N/A') . "\n";
    } else {
        echo "{$red}✗ FAILED{$reset} - Status: $httpCode\n";
        echo "   Response: " . substr($response, 0, 100) . "\n";
    }
    
} else {
    echo "{$red}✗ FAILED{$reset} - Status: $httpCode\n";
    echo "   Message: " . ($data['message'] ?? 'Unknown error') . "\n";
    if (isset($data['errors'])) {
        echo "   Errors: " . json_encode($data['errors']) . "\n";
    }
}

// Test 5: Get Cities (Public)
echo "\n{$yellow}Test 5: Get Cities (Public API){$reset}\n";
$ch = curl_init($baseUrl . '/cities');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json'
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    echo "{$green}✓ SUCCESS{$reset} - Status: $httpCode\n";
    $data = json_decode($response, true);
    if (isset($data['data'])) {
        echo "   Found " . count($data['data']) . " cities\n";
    }
} else {
    echo "{$red}✗ FAILED{$reset} - Status: $httpCode\n";
}

echo "\n{$blue}=== Testing Complete ==={$reset}\n\n";


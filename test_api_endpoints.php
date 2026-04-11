<?php
$token = '21|IRhcE85nPn82NGLyEu66gTUHuxc9kHi4vpTLtVMG';

// Test Wishlist endpoint
echo "Testing Wishlist Endpoint...\n";
$ch = curl_init('https://www.tofofstore.com/api/wishlist?page=1');
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token, 'Accept: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "Wishlist Status: " . $httpcode . "\n";
if ($httpcode !== 200) {
    echo "Wishlist Response: " . substr($response, 0, 300) . "\n";
}
curl_close($ch);

echo "\n";

// Test Discounts endpoint
echo "Testing Discounts Endpoint...\n";
$ch = curl_init('https://www.tofofstore.com/api/profile/discounts?page=1');
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token, 'Accept: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "Discounts Status: " . $httpcode . "\n";
if ($httpcode !== 200) {
    echo "Discounts Response: " . substr($response, 0, 300) . "\n";
}
curl_close($ch);

<?php
$host = '127.0.0.1';
$user = 'root';
$pass = '';
$db = 'cos';

echo "Attempting direct mysqli connection to $host...\n";
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error . "\n");
}

echo "Connected successfully to $db.\n";
$sql = "ALTER TABLE wishlists MODIFY id bigint(20) unsigned NOT NULL AUTO_INCREMENT";

if ($conn->query($sql) === TRUE) {
    echo "Table wishlists updated successfully.\n";
} else {
    echo "Error updating table: " . $conn->error . "\n";
}

$conn->close();

<?php
$conn = mysqli_connect('127.0.0.1', 'root', '', 'cos');
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
echo "Connected successfully to 127.0.0.1\n";

$result = mysqli_query($conn, "SHOW CREATE TABLE addresses");
if ($result) {
    $row = mysqli_fetch_row($result);
    echo "Create address table:\n" . $row[1] . "\n";
} else {
    echo "Error showing create table: " . mysqli_error($conn) . "\n";
}

mysqli_close($conn);

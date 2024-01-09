<?php
session_start();

$serverName = "LAPTOP-FIIPHBAB";
$uid = "";  // Replace with your SQL Server username
$pwd = "";  // Replace with your SQL Server password
$DB = "Domc_TEST";

try {
    $conn = new PDO("sqlsrv:server=$serverName;Database=$DB", $uid, $pwd);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

if (!$conn) {
    die(print_r(sqlsrv_errors(), true));
}
?>

<?php
/**
 * Database Connection Configuration
 * HRnexa - Human Resource Management System
 */

// Include main configuration
require_once __DIR__ . '/config.php';

// Database Configuration from Environment Variables
$db_host = getenv('DB_HOST') ?: 'localhost';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASS') ?: '';
$db_name = getenv('DB_NAME') ?: 'hrnexa';

// Create connection using MySQLi
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4
$conn->set_charset("utf8mb4");

// Set timezone
date_default_timezone_set('Asia/Dhaka');
$conn->query("SET time_zone = '" . date('P') . "'");

// Error reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);




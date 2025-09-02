<?php
// Debug script to check system status
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Tourism System Debug Information</h2>";

// Check PHP version
echo "<h3>PHP Information</h3>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Server: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";

// Check file existence
echo "<h3>File Check</h3>";
$files_to_check = [
    '../config/database.php',
    '../classes/Booking.php',
    '../classes/Customer.php',
    '../classes/Tour.php',
    'auth.php'
];

foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        echo "✅ $file - EXISTS<br>";
    } else {
        echo "❌ $file - MISSING<br>";
    }
}

// Check database connection
echo "<h3>Database Connection Test</h3>";
try {
    include_once '../config/database.php';
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        echo "✅ Database connection successful<br>";
        
        // Check tables
        $tables = ['bookings', 'customers', 'tours', 'packages', 'locations', 'admins'];
        foreach ($tables as $table) {
            try {
                $stmt = $db->query("SELECT COUNT(*) FROM $table");
                $count = $stmt->fetchColumn();
                echo "✅ Table '$table' exists with $count records<br>";
            } catch (Exception $e) {
                echo "❌ Table '$table' error: " . $e->getMessage() . "<br>";
            }
        }
    } else {
        echo "❌ Database connection failed<br>";
    }
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
}

// Check session
echo "<h3>Session Information</h3>";
session_start();
if (isset($_SESSION['admin_id'])) {
    echo "✅ Admin logged in: " . $_SESSION['admin_name'] . " (ID: " . $_SESSION['admin_id'] . ")<br>";
} else {
    echo "❌ No admin session found<br>";
}

// Check permissions
echo "<h3>File Permissions</h3>";
$current_dir = dirname(__FILE__);
if (is_writable($current_dir)) {
    echo "✅ Directory is writable<br>";
} else {
    echo "❌ Directory is not writable<br>";
}

echo "<h3>Error Log Check</h3>";
$error_log = ini_get('error_log');
if ($error_log && file_exists($error_log)) {
    echo "Error log location: $error_log<br>";
    $recent_errors = tail($error_log, 10);
    if ($recent_errors) {
        echo "<pre>Recent errors:\n" . htmlspecialchars($recent_errors) . "</pre>";
    }
} else {
    echo "Error log not found or not configured<br>";
}

function tail($filename, $lines = 10) {
    if (!file_exists($filename)) return false;
    $file = file($filename);
    return implode('', array_slice($file, -$lines));
}
?>

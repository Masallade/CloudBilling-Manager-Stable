<?php
/**
 * Database Fix Script - Creates Missing Aauth Tables
 * Access this file directly: http://192.168.100.83/demo.cloudbillingmanager.com/fix_database.php
 * 
 * WARNING: This script will create missing database tables. Make sure you have a database backup!
 */

$hostname = 'localhost';
$username = 'root';
$password = '';
$database = 'cloudbilling_v2';

echo "<h2>Database Fix Script - Creating Missing Aauth Tables</h2>";
echo "<p style='color: orange;'><strong>⚠ WARNING:</strong> This script will modify your database. Make sure you have a backup!</p>";

$conn = @mysqli_connect($hostname, $username, $password);

if (!$conn) {
    die("<p style='color: red;'>✗ MySQL connection failed: " . mysqli_connect_error() . "</p>");
}

if (!@mysqli_select_db($conn, $database)) {
    die("<p style='color: red;'>✗ Database '{$database}' does not exist or is not accessible</p>");
}

echo "<p style='color: green;'>✓ Connected to database '{$database}'</p>";

// Read and execute SQL file
$sql_file = 'create_aauth_tables.sql';
if (!file_exists($sql_file)) {
    die("<p style='color: red;'>✗ SQL file '{$sql_file}' not found!</p>");
}

$sql = file_get_contents($sql_file);

// Split SQL into individual statements
$statements = array_filter(
    array_map('trim', explode(';', $sql)),
    function($stmt) {
        return !empty($stmt) && !preg_match('/^--/', $stmt);
    }
);

echo "<h3>Executing SQL Statements...</h3>";

$success_count = 0;
$error_count = 0;

foreach ($statements as $statement) {
    // Skip comments and empty statements
    $statement = trim($statement);
    if (empty($statement) || preg_match('/^--/', $statement)) {
        continue;
    }
    
    if (mysqli_query($conn, $statement)) {
        $success_count++;
        // Extract table name from CREATE TABLE statement for better feedback
        if (preg_match('/CREATE TABLE.*?`?(\w+)`?/i', $statement, $matches)) {
            $table_name = $matches[1];
            echo "<p style='color: green;'>✓ Created/verified table: <strong>{$table_name}</strong></p>";
        }
    } else {
        $error_count++;
        $error = mysqli_error($conn);
        // Don't show error for "table already exists"
        if (strpos($error, 'already exists') === false) {
            echo "<p style='color: red;'>✗ Error: {$error}</p>";
            echo "<pre style='background: #f0f0f0; padding: 10px;'>" . htmlspecialchars(substr($statement, 0, 200)) . "...</pre>";
        }
    }
}

// Verify tables were created
echo "<hr><h3>Verifying Tables...</h3>";
$required_tables = [
    'aauth_groups',
    'aauth_user_to_group',
    'aauth_perms',
    'aauth_perm_to_group',
    'aauth_perm_to_user',
    'aauth_user_variables',
    'aauth_group_to_group',
    'geopos_login_attempts',
    'geopos_pms'
];

$all_exist = true;
foreach ($required_tables as $table) {
    $result = mysqli_query($conn, "SHOW TABLES LIKE '{$table}'");
    if ($result && mysqli_num_rows($result) > 0) {
        echo "<p style='color: green;'>✓ Table '{$table}' exists</p>";
    } else {
        echo "<p style='color: red;'>✗ Table '{$table}' is still missing</p>";
        $all_exist = false;
    }
}

mysqli_close($conn);

echo "<hr>";
if ($all_exist) {
    echo "<h3 style='color: green;'>✓ SUCCESS! All required tables have been created.</h3>";
    echo "<p>You can now try accessing your application: <a href='index.php'>http://192.168.100.83/demo.cloudbillingmanager.com/</a></p>";
} else {
    echo "<h3 style='color: orange;'>⚠ Some tables may still be missing. Please check the errors above.</h3>";
}

echo "<p><strong>Summary:</strong> {$success_count} statements executed successfully, {$error_count} errors encountered.</p>";




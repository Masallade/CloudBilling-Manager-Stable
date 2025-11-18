<?php
/**
 * Simple diagnostic script to test database connection and basic setup
 * Access this file directly: http://192.168.100.83/demo.cloudbillingmanager.com/test_connection.php
 */

// Test PHP version
echo "<h2>PHP Version: " . PHP_VERSION . "</h2>";

// Test if we can connect to database
$hostname = 'localhost';
$username = 'root';
$password = '';
$database = 'cloudbilling_v2';

echo "<h3>Testing Database Connection...</h3>";
$conn = @mysqli_connect($hostname, $username, $password);

if ($conn) {
    echo "<p style='color: green;'>✓ MySQL connection successful</p>";
    
    // Check if database exists
    $db_selected = @mysqli_select_db($conn, $database);
    if ($db_selected) {
        echo "<p style='color: green;'>✓ Database '{$database}' exists and is accessible</p>";
        
        // Check for required tables
        $required_tables = [
            'geopos_users', 
            'geopos_system', 
            'aauth_groups',
            'aauth_user_to_group',
            'aauth_perms',
            'aauth_perm_to_group',
            'aauth_perm_to_user',
            'aauth_user_variables',
            'geopos_login_attempts',
            'geopos_pms'
        ];
        
        $missing_tables = [];
        foreach ($required_tables as $table) {
            $result = mysqli_query($conn, "SHOW TABLES LIKE '{$table}'");
            if ($result && mysqli_num_rows($result) > 0) {
                echo "<p style='color: green;'>✓ Table '{$table}' exists</p>";
            } else {
                echo "<p style='color: red;'>✗ Table '{$table}' is MISSING</p>";
                $missing_tables[] = $table;
            }
        }
        
        if (!empty($missing_tables)) {
            echo "<hr>";
            echo "<h3 style='color: red;'>⚠ CRITICAL: Missing Required Tables!</h3>";
            echo "<p>The following Aauth authentication tables are missing. This will cause the application to fail:</p>";
            echo "<ul>";
            foreach ($missing_tables as $table) {
                echo "<li><strong>{$table}</strong></li>";
            }
            echo "</ul>";
            echo "<p><strong>Solution:</strong> You need to run the database installation/migration scripts to create these tables.</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Database '{$database}' does not exist or is not accessible</p>";
        echo "<p>Error: " . mysqli_error($conn) . "</p>";
    }
    mysqli_close($conn);
} else {
    echo "<p style='color: red;'>✗ MySQL connection failed</p>";
    echo "<p>Error: " . mysqli_connect_error() . "</p>";
}

// Test if CodeIgniter paths are correct
echo "<h3>Testing File Paths...</h3>";
$paths = [
    'index.php' => 'index.php',
    'system/core' => 'system/core',
    'application/config' => 'application/config',
    'application/controllers/User.php' => 'application/controllers/User.php',
];

foreach ($paths as $name => $path) {
    if (file_exists($path)) {
        echo "<p style='color: green;'>✓ {$name} exists</p>";
    } else {
        echo "<p style='color: red;'>✗ {$name} is missing</p>";
    }
}

echo "<hr>";
echo "<p><strong>If all checks pass, the issue might be:</strong></p>";
echo "<ul>";
echo "<li>PHP errors being suppressed</li>";
echo "<li>Output buffering issues</li>";
echo "<li>Missing required PHP extensions</li>";
echo "<li>CodeIgniter bootstrap errors</li>";
echo "</ul>";


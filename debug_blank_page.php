<?php
/**
 * Debug script to find the exact error causing the blank page
 * Access: http://192.168.100.83/demo.cloudbillingmanager.com/debug_blank_page.php
 */

// Enable all error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<h2>Debugging Blank Page Issue</h2>";
echo "<p>PHP Version: " . PHP_VERSION . "</p>";

// Test 1: Basic PHP
echo "<h3>Test 1: Basic PHP</h3>";
echo "<p style='color: green;'>✓ PHP is working</p>";

// Test 2: Check if we can load CodeIgniter
echo "<h3>Test 2: Loading CodeIgniter Bootstrap</h3>";

// Set environment
define('ENVIRONMENT', 'development');

// Set paths
$system_path = 'system';
$application_folder = 'application';

// Set current directory
if (defined('STDIN')) {
    chdir(dirname(__FILE__));
}

// Resolve system path
if (($_temp = realpath($system_path)) !== FALSE) {
    $system_path = $_temp.DIRECTORY_SEPARATOR;
} else {
    $system_path = strtr(
        rtrim($system_path, '/\\'),
        '/\\',
        DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR
    ).DIRECTORY_SEPARATOR;
}

// Define constants
define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));
define('BASEPATH', $system_path);
define('FCPATH', dirname(__FILE__).DIRECTORY_SEPARATOR);
define('SYSDIR', basename(BASEPATH));

// Application path
if (is_dir($application_folder)) {
    if (($_temp = realpath($application_folder)) !== FALSE) {
        $application_folder = $_temp;
    }
}
define('APPPATH', $application_folder.DIRECTORY_SEPARATOR);

echo "<p style='color: green;'>✓ Paths defined</p>";
echo "<p>BASEPATH: " . BASEPATH . "</p>";
echo "<p>APPPATH: " . APPPATH . "</p>";

// Test 3: Try to load CodeIgniter core
echo "<h3>Test 3: Loading CodeIgniter Core</h3>";
try {
    if (file_exists(BASEPATH.'core/CodeIgniter.php')) {
        echo "<p style='color: green;'>✓ CodeIgniter.php exists</p>";
        
        // Try to require it with output buffering to catch errors
        ob_start();
        $error_occurred = false;
        $error_message = '';
        
        set_error_handler(function($errno, $errstr, $errfile, $errline) use (&$error_occurred, &$error_message) {
            $error_occurred = true;
            $error_message = "Error $errno: $errstr in $errfile on line $errline";
            return false;
        });
        
        // Try to include just the first part to see where it fails
        echo "<p>Attempting to load CodeIgniter...</p>";
        
        // Instead of loading full CI, let's test the User controller directly
        require_once BASEPATH.'core/Common.php';
        echo "<p style='color: green;'>✓ Common.php loaded</p>";
        
        // Test database connection
        echo "<h3>Test 4: Database Connection Test</h3>";
        $hostname = 'localhost';
        $username = 'root';
        $password = '';
        $database = 'cloudbilling_v2';
        
        $conn = @mysqli_connect($hostname, $username, $password);
        if ($conn) {
            echo "<p style='color: green;'>✓ MySQL connection successful</p>";
            if (@mysqli_select_db($conn, $database)) {
                echo "<p style='color: green;'>✓ Database selected</p>";
                
                // Check what happens when Aauth tries to load
                echo "<h3>Test 5: Testing Aauth Library Requirements</h3>";
                
                // Check if required tables exist
                $tables_to_check = [
                    'geopos_users' => 'Users table',
                    'geopos_system' => 'System config table',
                ];
                
                foreach ($tables_to_check as $table => $desc) {
                    $result = mysqli_query($conn, "SHOW TABLES LIKE '{$table}'");
                    if ($result && mysqli_num_rows($result) > 0) {
                        echo "<p style='color: green;'>✓ {$desc} ({$table}) exists</p>";
                    } else {
                        echo "<p style='color: red;'>✗ {$desc} ({$table}) MISSING</p>";
                    }
                }
                
                mysqli_close($conn);
            }
        }
        
        restore_error_handler();
        $output = ob_get_clean();
        if ($output) {
            echo "<pre>" . htmlspecialchars($output) . "</pre>";
        }
        
        if ($error_occurred) {
            echo "<p style='color: red;'>✗ Error occurred: {$error_message}</p>";
        }
        
    } else {
        echo "<p style='color: red;'>✗ CodeIgniter.php not found at: " . BASEPATH.'core/CodeIgniter.php' . "</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Exception: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} catch (Error $e) {
    echo "<p style='color: red;'>✗ Fatal Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<h3>Next Steps:</h3>";
echo "<p>If all tests pass, the issue might be in:</p>";
echo "<ul>";
echo "<li>The User controller constructor</li>";
echo "<li>Aauth library initialization</li>";
echo "<li>Missing view files</li>";
echo "<li>Session configuration issues</li>";
echo "</ul>";




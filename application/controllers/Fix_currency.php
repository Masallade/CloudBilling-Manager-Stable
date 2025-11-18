<?php
/**
 * Utility Controller to Fix Currency to Uppercase
 * This will update the database and clear caches
 */
defined('BASEPATH') or exit('No direct script access allowed');

class Fix_currency extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Fix currency in database to uppercase
     * Access: /fix_currency/update_database
     */
    public function update_database()
    {
        // Update currency in database to uppercase
        $query = $this->db->query("SELECT currency FROM geopos_system WHERE id=1 LIMIT 1");
        $row = $query->row_array();
        
        if (isset($row['currency'])) {
            $current_currency = $row['currency'];
            $uppercase_currency = strtoupper($current_currency);
            
            if ($current_currency !== $uppercase_currency) {
                $this->db->set('currency', $uppercase_currency);
                $this->db->where('id', 1);
                $result = $this->db->update('geopos_system');
                
                if ($result) {
                    echo "✓ Database updated: '$current_currency' → '$uppercase_currency'<br>";
                } else {
                    echo "✗ Failed to update database<br>";
                }
            } else {
                echo "✓ Currency already uppercase: '$current_currency'<br>";
            }
        } else {
            echo "✗ Currency not found in database<br>";
        }
    }

    /**
     * Clear all dashboard cache files
     * Access: /fix_currency/clear_cache
     */
    public function clear_cache()
    {
        $cacheDir = APPPATH . 'cache' . DIRECTORY_SEPARATOR;
        $deleted = 0;
        
        if (is_dir($cacheDir)) {
            $files = glob($cacheDir . 'dash_*.json');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                    $deleted++;
                }
            }
            echo "✓ Deleted $deleted cache file(s)<br>";
        } else {
            echo "✗ Cache directory not found<br>";
        }
    }

    /**
     * Run all fixes
     * Access: /fix_currency/run_all
     */
    public function run_all()
    {
        echo "<h2>Fixing Currency to Uppercase</h2>";
        echo "<hr>";
        
        echo "<h3>1. Updating Database:</h3>";
        $this->update_database();
        
        echo "<h3>2. Clearing Cache:</h3>";
        $this->clear_cache();
        
        echo "<hr>";
        echo "<h3>✓ All fixes completed!</h3>";
        echo "<p>Please refresh your dashboard page.</p>";
    }
}



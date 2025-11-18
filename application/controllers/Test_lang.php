<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Test_lang extends CI_Controller
{
    public function index()
    {
        // Load language file
        $this->lang->load('english', 'english');
        
        echo "<h2>Testing Language Placeholder Replacement</h2>";
        echo "<p>Tax type from database: <strong>" . getTaxName() . "</strong></p>";
        echo "<p>Tax rate from database: <strong>" . getTaxSettings()['tax_rate'] . "%</strong></p>";
        echo "<hr>";
        
        // Test various language lines
        $test_keys = array(
            'Tax',
            'Total Tax',
            'Tax Amount',
            'Total Tax Amount',
            'Product Tax',
            'Tax Rates',
            'Previous Balance'
        );
        
        echo "<table border='1' cellpadding='5' style='border-collapse:collapse;'>";
        echo "<tr><th>Language Key</th><th>Output</th><th>Contains Placeholder?</th></tr>";
        
        foreach ($test_keys as $key) {
            $value = $this->lang->line($key);
            $has_placeholder = (strpos($value, '{') !== false) ? '<span style="color:red;">YES - NOT REPLACED!</span>' : '<span style="color:green;">No - OK</span>';
            echo "<tr><td>" . $key . "</td><td>" . $value . "</td><td>" . $has_placeholder . "</td></tr>";
        }
        
        echo "</table>";
        
        // Check if MY_Lang is being used
        echo "<hr>";
        echo "<p>Language class: <strong>" . get_class($this->lang) . "</strong></p>";
        
        // Debug: show raw language array value
        echo "<p>Raw value of \$this->lang->line('Tax'): <code>" . $this->lang->line('Tax') . "</code></p>";
    }
}


<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Extended Language Class
 * Automatically replaces {tax}, {Tax}, and {TAX} placeholders with dynamic tax type from settings
 */
class MY_Lang extends CI_Lang {

    /**
     * Load a language file with automatic tax placeholder replacement
     *
     * @param   mixed   $langfile   Language file name
     * @param   string  $idiom      Language name (english, etc.)
     * @param   bool    $return     Whether to return the loaded array of translations
     * @param   bool    $add_suffix Whether to add suffix to $langfile
     * @param   string  $alt_path   Alternative path to look for the language file
     * @return  void|string[]   Array containing translations, if $return is set to TRUE
     */
    public function load($langfile, $idiom = '', $return = FALSE, $add_suffix = TRUE, $alt_path = '')
    {
        if (is_array($langfile))
        {
            foreach ($langfile as $value)
            {
                $this->load($value, $idiom, $return, $add_suffix, $alt_path);
            }

            return;
        }

        $langfile = str_replace('.php', '', $langfile);

        if ($add_suffix === TRUE)
        {
            $langfile = preg_replace('/_lang$/', '', $langfile).'_lang';
        }

        $langfile .= '.php';

        if (empty($idiom) OR ! preg_match('/^[a-z_-]+$/i', $idiom))
        {
            $config =& get_config();
            $idiom = empty($config['language']) ? 'english' : $config['language'];
        }

        if ($return === FALSE && isset($this->is_loaded[$langfile]) && $this->is_loaded[$langfile] === $idiom)
        {
            return;
        }

        // Load the base file, so any others found can override it
        $basepath = APPPATH.'language/'.$idiom.'/'.$langfile;
        $found = FALSE;

        if (file_exists($basepath))
        {
            include($basepath);
            $found = TRUE;
        }

        // Do we have an alternative path to look in?
        if ($alt_path !== '')
        {
            $alt_path .= 'language/'.$idiom.'/'.$langfile;
            if (file_exists($alt_path))
            {
                include($alt_path);
                $found = TRUE;
            }
        }
        else
        {
            foreach (get_instance()->load->get_package_paths(TRUE) as $package_path)
            {
                $package_path .= 'language/'.$idiom.'/'.$langfile;
                if ($basepath !== $package_path && file_exists($package_path))
                {
                    include($package_path);
                    $found = TRUE;
                    break;
                }
            }
        }

        if ($found !== TRUE)
        {
            show_error('Unable to load the requested language file: language/'.$idiom.'/'.$langfile);
        }

        if ( ! isset($lang) OR ! is_array($lang))
        {
            log_message('error', 'Language file contains no data: language/'.$idiom.'/'.$langfile);

            if ($return === TRUE)
            {
                return array();
            }
            return;
        }

        if ($return === TRUE)
        {
            return $lang;
        }

        $this->is_loaded[$langfile] = $idiom;
        $this->language = array_merge($this->language, $lang);

        log_message('info', 'Language file loaded: language/'.$idiom.'/'.$langfile);
        return TRUE;
    }

    /**
     * Fetch a single line of text from the language array
     * With automatic tax placeholder replacement
     *
     * @param   string  $line   Language line key
     * @return  string  Translation
     */
    public function line($line = '', $log_errors = TRUE)
    {
        // Return empty string if line is empty
        if ($line === '') {
            return '';
        }
        
        // Check if language array is loaded
        if (!isset($this->language) || !is_array($this->language)) {
            if ($log_errors) {
                log_message('error', 'Language array not initialized. Attempting to load default language.');
            }
            // Try to load default language
            $config =& get_config();
            $idiom = empty($config['language']) ? 'english' : $config['language'];
            $this->load($idiom, $idiom);
        }
        
        $value = (!isset($this->language[$line])) ? FALSE : $this->language[$line];

        // If language line not found, use fallback
        if ($value === FALSE || $value === '')
        {
            // Only log if it's a critical/common key
            if ($log_errors) {
                $common_keys = array('Name', 'Email', 'Phone', 'Address', 'Save', 'Update', 'Delete', 'Edit', 'View', 'Close', 'Tax');
                if (in_array($line, $common_keys)) {
                    log_message('debug', 'Could not find the language line "'.$line.'"');
                }
            }
            
            // Return the key itself as fallback (humanized)
            $value = ucwords(str_replace('_', ' ', $line));
        }

        // Always process tax placeholders if they exist (even in fallback)
        if ($value !== FALSE && $value !== '')
        {
            if (strpos($value, '{tax}') !== FALSE || strpos($value, '{Tax}') !== FALSE || strpos($value, '{TAX}') !== FALSE)
            {
                $original_value = $value;
                $value = $this->replace_tax_placeholder($value);
                
                // Debug log
                log_message('debug', 'MY_Lang: Replaced placeholder in "'.$line.'": "'.$original_value.'" => "'.$value.'"');
            }
        }

        return $value;
    }

    /**
     * Replace tax placeholders with actual tax type from settings
     *
     * @param   string  $text   Text containing placeholders
     * @return  string  Text with placeholders replaced
     */
    private function replace_tax_placeholder($text)
    {
        // Get tax name from settings using helper function
        $tax_name = $this->get_tax_name();

        // Replace placeholders with different cases
        $text = str_replace('{tax}', strtolower($tax_name), $text);
        $text = str_replace('{TAX}', strtoupper($tax_name), $text);
        $text = str_replace('{Tax}', ucfirst(strtolower($tax_name)), $text);

        return $text;
    }

    /**
     * Get tax type name from settings
     * Uses the getTaxName() helper function if available, otherwise queries directly
     *
     * @return string Tax type name
     */
    private function get_tax_name()
    {
        $CI =& get_instance();
        
        // Ensure database is loaded
        if (!isset($CI->db))
        {
            $CI->load->database();
        }
        
        // Try to use the helper function if it's loaded
        if (function_exists('getTaxName'))
        {
            $tax_name = getTaxName();
            if (!empty($tax_name)) {
                return strtoupper($tax_name); // Always return in CAPITAL letters
            }
        }
        
        // Load the siteconfig helper if not already loaded
        if (!function_exists('getTaxName'))
        {
            $helper_path = APPPATH . 'helpers/siteconfig_helper.php';
            if (file_exists($helper_path))
            {
                require_once($helper_path);
                if (function_exists('getTaxName'))
                {
                    $tax_name = getTaxName();
                    if (!empty($tax_name)) {
                        return strtoupper($tax_name); // Always return in CAPITAL letters
                    }
                }
            }
        }

        // Fallback: query database directly (single row, no id needed)
        try {
            $query = $CI->db->query("SELECT tax_type FROM geopos_system LIMIT 1");
            
            if ($query && $query->num_rows() > 0) {
                $row = $query->row_array();
                if (isset($row['tax_type']) && !empty($row['tax_type'])) {
                    return strtoupper($row['tax_type']); // Always return in CAPITAL letters
                }
            }
            
            // Try alternative column name (for backward compatibility)
            try {
                $query2 = $CI->db->query("SELECT tax FROM geopos_system LIMIT 1");
                if ($query2 && $query2->num_rows() > 0) {
                    $row2 = $query2->row_array();
                    if (isset($row2['tax']) && !empty($row2['tax'])) {
                        return strtoupper($row2['tax']); // Always return in CAPITAL letters
                    }
                }
            } catch (Exception $e2) {
                // Ignore
            }
            
            return 'VAT';
            
        } catch (Exception $e) {
            return 'VAT';
        }
    }
}


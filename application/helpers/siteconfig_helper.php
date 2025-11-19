<?php
/**
 * Geo POS -  Accounting,  Invoicing  and CRM Application
 * Copyright (c) Rajesh Dukiya. All Rights Reserved
 * ***********************************************************************
 *
 *  Email: support@ultimatekode.com
 *  Website: https://www.ultimatekode.com
 *
 *  ************************************************************************
 *  * This software is furnished under a license and may be used and copied
 *  * only  in  accordance  with  the  terms  of such  license and with the
 *  * inclusion of the above copyright notice.
 *  * If you Purchased from Codecanyon, Please read the full License from
 *  * here- http://codecanyon.net/licenses/standard/
 * ***********************************************************************
 */


if (!defined('BASEPATH')) exit('No direct script access allowed');


function dateformat($input)
{
    $ci =& get_instance();
    $date = new DateTime($input);
    $date = $date->format($ci->config->item('dformat'));
    return $date;
}

function assets_url($input = '')
{
    return base_url($input);
}

function dateformat_time($input)
{
    $ci =& get_instance();
    $date = new DateTime($input);
    $date = $date->format($ci->config->item('dformat') . ' H:i:s');
    return $date;
}

function datefordatabase($input)
{
    $date = new DateTime($input);
    $date = $date->format('Y-m-d H:i:s');
    return $date;
}

function timefordatabase($input)
{

    $time = new DateTime($input);
    $time = $time->format('H:i:s');
    return $time;
}

function user_role($id = 5)
{
    $ci =& get_instance();
    switch ($id) {
        case 5:
            return $ci->lang->line('Business Owner');
            break;
        case 4:
            return $ci->lang->line('Business Manager');
            break;
        case 3:
            return $ci->lang->line('Sales Manager');
            break;
        case 2:
            return $ci->lang->line('Sales Person');
            break;
        case 1:
            return $ci->lang->line('Inventory Manager');
            break;
        case -1:
            return $ci->lang->line('Project Manager');
            break;
    }
}

function amountFormat($number)
{
    $ci =& get_instance();
    $query = $ci->db->query("SELECT currency FROM geopos_system  LIMIT 1");
    $row = $query->row_array();
    $currency = strtoupper($row['currency']);
    //get data from database
    $query2 = $ci->db->query("SELECT * FROM univarsal_api WHERE id=4 LIMIT 1");
    $row = $query2->row_array();
    //Format money as per country
    if ($row['method'] == 'l') {
        return $currency . ' ' . @number_format($number, $row['url'], $row['key1'], $row['key2']);
    } else {
        return @number_format($number, $row['url'], $row['key1'], $row['key2']) . ' ' . $currency;
    }

}

function prefix($number)
{
    $ci =& get_instance();
    $query2 = $ci->db->query("SELECT * FROM univarsal_api WHERE id=51 LIMIT 1");
    $row = $query2->row_array();
    //Format money as per country
    switch ($number) {
        case 1:
            return $row['name'];
            break;
        case 2:
            return $row['key1'];
            break;
        case 3:
            return $row['key2'];
            break;
        case 4:
            return $row['url'];
            break;
        case 5:
            return $row['method'];
            break;
        case 6:
            return $row['other'];
            break;
        case 7:
            $query2 = $ci->db->query("SELECT other FROM univarsal_api WHERE id=52 LIMIT 1");
            $row = $query2->row_array();
            return $row['other'];
            break;
    }
}

function user_premission($input1, $input2)
{
    if (hash_equals($input1, $input2)) {
        return true;
    } else {
        return false;
    }
}


function amountFormat_s($number)
{
    $ci =& get_instance();
    $ci->load->database();
    //get data from database
    $query2 = $ci->db->query("SELECT * FROM univarsal_api WHERE id=4 LIMIT 1");
    $row = $query2->row_array();
    //Format money as per country

    return @number_format($number, $row['url'], $row['key1'], $row['key2']);

}

function amountFormat_general($number=0)
{
    $ci =& get_instance();
    $ci->load->database();
    //get data from database
    $query2 = $ci->db->query("SELECT * FROM univarsal_api WHERE id=4 LIMIT 1");
    $row = $query2->row_array();
    //Format money as per country - ensure proper float conversion and decimal limit
    $number = @number_format((float)$number, (int)$row['url'], $row['key1'], '');
    return $number;
}

/**
 * Format decimal number with specified or default decimal places
 * @param mixed $number The number to format
 * @param int|null $decimals Optional: Number of decimal places (null = use settings)
 * @return string Formatted number
 */
function formatDecimal($number, $decimals = null)
{
    $ci =& get_instance();
    $ci->load->database();
    
    // Get decimal settings from database
    $query = $ci->db->query("SELECT * FROM univarsal_api WHERE id=4 LIMIT 1");
    $row = $query->row_array();
    
    // Use specified decimals or get from settings
    $decimal_places = ($decimals !== null) ? (int)$decimals : (int)$row['url'];
    $dec_point = $row['key1'];
    
    // Format with specified decimal places, ensuring float conversion
    $formatted = number_format((float)$number, $decimal_places, $dec_point, '');
    
    return $formatted;
}

/**
 * Get tax settings from system configuration
 * @return array Array with 'type' (tax name) and 'rate' (percentage as decimal)
 */
function getTaxSettings()
{
    $ci =& get_instance();
    $ci->load->database();
    
    try {
        // Get tax settings from geopos_system table (single row, no id needed)
        $query = $ci->db->query("SELECT tax_type, tax_rate FROM geopos_system LIMIT 1");
        
        if (!$query) {
            // If query fails (columns don't exist), return defaults
            return array(
                'type' => 'VAT',
                'rate' => 0.20,
                'rate_percent' => 20
            );
        }
        
        $row = $query->row_array();
        
        // Return tax settings with defaults if not set (tax type always in uppercase)
        return array(
            'type' => isset($row['tax_type']) && $row['tax_type'] ? strtoupper($row['tax_type']) : 'VAT',
            'rate' => isset($row['tax_rate']) && $row['tax_rate'] ? (float)$row['tax_rate'] / 100 : 0.20,
            'rate_percent' => isset($row['tax_rate']) && $row['tax_rate'] ? (float)$row['tax_rate'] : 20
        );
    } catch (Exception $e) {
        // If database error (columns don't exist yet), return defaults
        return array(
            'type' => 'VAT',
            'rate' => 0.20,
            'rate_percent' => 20
        );
    }
}

/**
 * Get tax type name from settings (replaces hardcoded VAT/GST)
 * @return string Tax type name (e.g., VAT, GST, Sales Tax)
 */
function getTaxName()
{
    $ci =& get_instance();
    
    // Ensure database is loaded
    if (!isset($ci->db)) {
        $ci->load->database();
    }
    
    try {
        // Get tax_type from geopos_system table (single row, no id needed)
        $query = $ci->db->query("SELECT tax_type FROM geopos_system LIMIT 1");
        if ($query && $query->num_rows() > 0) {
            $row = $query->row_array();
            if (isset($row['tax_type']) && !empty($row['tax_type'])) {
                return strtoupper($row['tax_type']); // Always return in CAPITAL letters
            }
        }
        
        // Fallback to tax column (for backward compatibility)
        $query2 = $ci->db->query("SELECT tax FROM geopos_system LIMIT 1");
        if ($query2 && $query2->num_rows() > 0) {
            $row2 = $query2->row_array();
            if (isset($row2['tax']) && !empty($row2['tax'])) {
                return strtoupper($row2['tax']); // Always return in CAPITAL letters
            }
        }
        
        // Try getTaxSettings() as last resort
        $settings = getTaxSettings();
        if (isset($settings['type']) && !empty($settings['type'])) {
            return strtoupper($settings['type']); // Always return in CAPITAL letters
        }
        
        // Default fallback
        return 'VAT';
        
    } catch (Exception $e) {
        // Fallback to VAT if error
        return 'VAT';
    }
}

/**
 * Calculate tax amount based on configured tax rate
 * @param float $amount The amount to calculate tax on
 * @param string $product_vattype Product VAT type (T1 = taxable, others = non-taxable)
 * @return float Tax amount
 */
function calculateTax($amount, $product_vattype = 'T1')
{
    if ($product_vattype != 'T1') {
        return 0;
    }
    
    $tax_settings = getTaxSettings();
    return $amount * $tax_settings['rate'];
}

/**
 * Get language line with dynamic tax name replacement
 * Replaces {tax} placeholder with actual tax type from settings
 * @param string $line Language line key
 * @return string Translated text with dynamic tax name
 */
function lang_tax($line)
{
    $ci =& get_instance();
    $ci->load->helper('language');
    
    // Get the language line
    $text = $ci->lang->line($line);
    
    // If language line not found, return the key
    if (!$text) {
        return $line;
    }
    
    // Get dynamic tax name
    $tax_name = getTaxName();
    
    // Replace {tax} placeholder with actual tax type
    $text = str_replace('{tax}', strtolower($tax_name), $text);
    $text = str_replace('{TAX}', strtoupper($tax_name), $text);
    $text = str_replace('{Tax}', ucfirst(strtolower($tax_name)), $text);
    
    return $text;
}

function numberClean($number)
{
    $ci =& get_instance();
    $ci->load->database();
    $query2 = $ci->db->query("SELECT * FROM univarsal_api WHERE id=4 LIMIT 1");
    $row = $query2->row_array();
    $number = str_replace($row['key2'], "", $number);
    $number = str_replace($row['key1'], ".", $number);
    return (float)$number;
}


function amountExchange($number, $id = 0, $loc = 0)
{
    $ci =& get_instance();
    $ci->load->database();
    
    // COMMENTED OUT: Multi-currency support via geopos_currencies or locations
    // Now using ONLY geopos_system.currency
    // if ($loc > 0 && $id == 0) {
    //     $query = $ci->db->query("SELECT cur FROM geopos_locations WHERE id='$loc' LIMIT 1");
    //     $row = $query->row_array();
    //     $id = $row['cur'];
    // }
    // if ($id > 0) {
    //     $query = $ci->db->query("SELECT * FROM geopos_currencies WHERE id='$id' LIMIT 1");
    //     $row = $query->row_array();
    //     $currency = $row['symbol'];
    //     $rate = $row['rate'];
    //     $thosand = $row['thous'];
    //     $dec_point = $row['dpoint'];
    //     $decimal_after = $row['decim'];
    //     $totalamount = $rate * $number;
    //     //get data from database
    //     //Format money as per country
    //     if ($row['cpos'] == 0) {
    //         return $currency . ' ' . @number_format($totalamount, $decimal_after, $dec_point, $thosand);
    //     } else {
    //         return @number_format($totalamount, $decimal_after, $dec_point, $thosand) . ' ' . $currency;
    //     }
    // } else {

    // ALWAYS USE: geopos_system.currency
    $query = $ci->db->query("SELECT currency FROM geopos_system LIMIT 1");
    $row = $query->row_array();
    $currency = strtoupper($row['currency']);

    //get data from database
    $query2 = $ci->db->query("SELECT * FROM univarsal_api WHERE id=4 LIMIT 1");
    $row = $query2->row_array();
    //Format money as per country - ensure proper float conversion
    if ($row['method'] == 'l') {
        return $currency . ' ' . @number_format((float)$number, (int)$row['url'], $row['key1'], $row['key2']);
    } else {
        return @number_format((float)$number, (int)$row['url'], $row['key1'], $row['key2']) . ' ' . $currency;
    }
    // }

}

function amountExchange_s($number, $id = 0, $loc = 0)
{
    $ci =& get_instance();
    $ci->load->database();
    
    // COMMENTED OUT: Multi-currency support
    // Now using ONLY geopos_system.currency
    // if ($loc > 0 && $id == 0) {
    //     $query = $ci->db->query("SELECT cur FROM geopos_locations WHERE id='$loc' LIMIT 1");
    //     $row = $query->row_array();
    //     $id = $row['cur'];
    // }
    // if ($id > 0) {
    //     $query = $ci->db->query("SELECT * FROM geopos_currencies WHERE id='$id' LIMIT 1");
    //     $row = $query->row_array();
    //     $rate = $row['rate'];
    //     $dec_point = $row['dpoint'];
    //     $totalamount = $rate * $number;
    //     $decimal_after = $row['decim'];
    //     $totalamount = number_format($totalamount, $decimal_after, $dec_point, '');
    //     return $totalamount;
    // } else;
    
    // ALWAYS USE: geopos_system formatting settings
    $query = $ci->db->query("SELECT currency FROM geopos_system LIMIT 1");
    $row = $query->row_array();
    $currency = strtoupper($row['currency']); // Ensure uppercase (though not used in return)
    
    //get data from database
    $query2 = $ci->db->query("SELECT * FROM univarsal_api WHERE id=4 LIMIT 1");
    $row = $query2->row_array();
    $number = number_format((float)$number, (int)$row['url'], $row['key1'], '');
    return $number;
    // }

}

function edit_amountExchange_s($number, $id = 0, $loc = 0)
{
    $ci =& get_instance();
    $ci->load->database();
    
    // COMMENTED OUT: Multi-currency support
    // Now using ONLY geopos_system.currency
    // if ($loc > 0) {
    //     $query = $ci->db->query("SELECT cur FROM geopos_locations WHERE id='$loc' LIMIT 1");
    //     $row = $query->row_array();
    //     $id = $row['cur'];
    // }
    // if ($id > 0) {
    //     $query = $ci->db->query("SELECT * FROM geopos_currencies WHERE id='$id' LIMIT 1");
    //     $row = $query->row_array();
    //     $rate = $row['rate'];
    //     $decimal_after = $row['decim'];
    //     $dec_point = $row['dpoint'];
    //     $number = str_replace($decimal_after, "", $number);
    //     $number = str_replace($dec_point, ".", $number);
    //     $totalamount = $rate * (float)$number;
    //     $totalamount = number_format($totalamount, $decimal_after, $dec_point, '');
    //     return $totalamount;
    // } else {
    
    // ALWAYS USE: geopos_system formatting settings
    $query = $ci->db->query("SELECT currency FROM geopos_system LIMIT 1");
    $row = $query->row_array();
    $currency = strtoupper($row['currency']); // Ensure uppercase (though not used in return)
    
    //get data from database
    $query2 = $ci->db->query("SELECT * FROM univarsal_api WHERE id=4 LIMIT 1");
    $row = $query2->row_array();
    $number = number_format($number, $row['url'], $row['key1'], '');
    return $number;
    // }

}

function rev_amountExchange_s($number, $id = 0, $loc = 0)
{
    $ci =& get_instance();
    $ci->load->database();
    $query2 = $ci->db->query("SELECT other FROM univarsal_api WHERE id=5 LIMIT 1");
    $row = $query2->row_array();
    $revers = $row['other'];

    // COMMENTED OUT: Multi-currency support via locations or currencies
    // Now using ONLY geopos_system formatting settings
    // if ($loc) {
    //     $query = $ci->db->query("SELECT cur FROM geopos_locations WHERE id='$loc' LIMIT 1");
    //     $row = $query->row_array();
    //     $lcid = $row['cur'];
    //     if ($lcid > 0) {
    //         $query = $ci->db->query("SELECT * FROM geopos_currencies WHERE id='$lcid' LIMIT 1");
    //         $row = $query->row_array();
    //         if($row['id']){
    //             $rate = $row['rate'];
    //             $number = str_replace($row['thous'], "", $number);
    //             $number = str_replace($row['dpoint'], ".", $number);
    //             $number = (float)$number / $rate;
    //         }
    //         else {
    //             $query2 = $ci->db->query("SELECT * FROM univarsal_api WHERE id=4 LIMIT 1");
    //             $row = $query2->row_array();
    //             $number = str_replace($row['key2'], "", $number);
    //             $number = str_replace($row['key1'], ".", $number);
    //         }
    //     } elseif ($id) {
    //         $query = $ci->db->query("SELECT * FROM geopos_currencies WHERE id='$id' LIMIT 1");
    //         $row = $query->row_array();
    //         $rate = $row['rate'];
    //         $number = str_replace($row['thous'], "", $number);
    //         $number = str_replace($row['dpoint'], ".", $number);
    //         $number = (float)$number / $rate;
    //     }
    //     else {
    //         $query2 = $ci->db->query("SELECT * FROM univarsal_api WHERE id=4 LIMIT 1");
    //         $row = $query2->row_array();
    //         $number = str_replace($row['key2'], "", $number);
    //         $number = str_replace($row['key1'], ".", $number);
    //     }
    // } elseif ($id) {
    //     $query = $ci->db->query("SELECT * FROM geopos_currencies WHERE id='$id' LIMIT 1");
    //     $row = $query->row_array();
    //     $rate = $row['rate'];
    //     $number = str_replace($row['thous'], "", $number);
    //     $number = str_replace($row['dpoint'], ".", $number);
    //     if (!$revers) {
    //         $number = (float)$number / $rate;
    //     }
    // } else {
    
    // ALWAYS USE: geopos_system formatting settings
    $query2 = $ci->db->query("SELECT * FROM univarsal_api WHERE id=4 LIMIT 1");
    $row = $query2->row_array();
    $number = str_replace($row['key2'], "", $number);
    $number = str_replace($row['key1'], ".", $number);
    // }

    return (float)$number;
}

function rev_amountExchange($number, $id = 0)
{
    $ci =& get_instance();
    $query = $ci->db->query("SELECT other FROM univarsal_api WHERE id='5' LIMIT 1");
    $row = $query->row_array();
    $reverse = $row['other'];
    
    // COMMENTED OUT: Multi-currency exchange rate conversion
    // Now using ONLY geopos_system.currency (no conversion needed)
    // if ($reverse && $id > 0) {
    //     $query = $ci->db->query("SELECT rate FROM geopos_currencies WHERE id='$id' LIMIT 1");
    //     $row = $query->row_array();
    //     $rate = $row['rate'];
    //     $totalamount = $number / $rate;
    //     return $totalamount;
    // } else {
    
    // ALWAYS USE: Return number as-is (no currency conversion)
    return $number;
    // }
}

function array_compare()
{
    $criteriaNames = func_get_args();
    $compare = function ($first, $second) use ($criteriaNames) {
        while (!empty($criteriaNames)) {
            $criterion = array_shift($criteriaNames);
            $sortOrder = 1;
            if (is_array($criterion)) {
                $sortOrder = $criterion[1] == SORT_DESC ? -1 : 1;
                $criterion = $criterion[0];
            }
            if ($first[$criterion] < $second[$criterion]) {
                return -1 * $sortOrder;
            } else if ($first[$criterion] > $second[$criterion]) {
                return 1 * $sortOrder;
            }
        }
        return 0;
    };

    return $compare;
}

function locations()
{
    $ci =& get_instance();
    $ci->load->database();
    $query2 = $ci->db->query("SELECT * FROM geopos_locations");
    return $query2->result_array();
}

function location($number = 0)
{
    $ci =& get_instance();
    $ci->load->database();
    if ($number > 0) {
        $query2 = $ci->db->query("SELECT * FROM geopos_locations WHERE id=$number");
        $location = $query2->row_array();
        // Get logo and taxid from geopos_system table instead of location
        $query_system = $ci->db->query("SELECT logo, taxid FROM geopos_system LIMIT 1");
        $system = $query_system->row_array();
        if ($system) {
            if (isset($system['logo'])) {
                $location['logo'] = $system['logo'];
            }
            if (isset($system['taxid'])) {
                $location['taxid'] = $system['taxid'];
            }
        }
        return $location;
    } else {
        $query2 = $ci->db->query("SELECT cname,address,city,region,country,postbox,phone,email,taxid,logo,foundation FROM geopos_system  LIMIT 1");
        return $query2->row_array();
    }
}

function active($input1)
{
    $t_file = APPPATH . 'config' . DIRECTORY_SEPARATOR . 'lic.php';
    
    // BYPASS: Always set license to "1" (valid) regardless of input
    $input1 = '1';
    
    // Check if file exists, if not create it
    if (!file_exists($t_file)) {
        // Try to create the file
        $dir = dirname($t_file);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($t_file, '1');
    }
    
    if (is_writeable($t_file)) {
        file_put_contents($t_file, '1'); // Always write "1" (valid license)
        $lc = file_get_contents($t_file);
        // Always return success
        echo json_encode(array('status' => 'Success', 'message' => 'License updated successfully!'));
    } else {
        // Try to make it writable
        @chmod($t_file, 0666);
        if (is_writeable($t_file)) {
            file_put_contents($t_file, '1'); // Always write "1" (valid license)
            // Always return success
            echo json_encode(array('status' => 'Success', 'message' => 'License updated successfully!'));
        } else {
            echo json_encode(array('status' => 'WError', 'message' => 'Server write permissions denied! Please check file permissions for: ' . $t_file));
        }
    } 

}

function currency($loc = 0, $id = 0)
{
    $ci =& get_instance();
    $ci->load->database();
    
    // COMMENTED OUT: Multi-currency support
    // Now using ONLY geopos_system.currency
    // if ($loc > 0 && $id == 0) {
    //     $query = $ci->db->query("SELECT cur FROM geopos_locations WHERE id='$loc' LIMIT 1");
    //     $row = $query->row_array();
    //     $id = $row['cur'];
    // }
    // if ($id > 0) {
    //     $query = $ci->db->query("SELECT * FROM geopos_currencies WHERE id='$id' LIMIT 1");
    //     $row = $query->row_array();
    //     $currency = $row['symbol'];
    // } else {
    
    // ALWAYS USE: geopos_system.currency
    $query = $ci->db->query("SELECT currency FROM geopos_system LIMIT 1");
    $row = $query->row_array();
    $currency = strtoupper($row['currency']);
    // }
    return $currency;
}

function plugins_checker()
{
    $path = FCPATH . 'application/plugins';
    $plugins = array_diff(scandir($path), array('.', '..'));
    foreach ($plugins as $row) {
        $url = file_get_contents($path . '/' . $row);
        $plug = json_decode($url, true);
        echo '    <li><a class="dropdown-item"
                                                           href="' . base_url() . $plug['path'] . '"><i
                                                                    class="ft-chevron-right"></i> ' . $plug['name'] . '
                                                        </a></li>';
    }
}

function custom_plugins_checker($name='sms')
{
    $path = FCPATH . 'application'.DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.$name;
      if(file_exists($path)) {


          $plugins = array_diff(scandir($path), array('.', '..'));
          foreach ($plugins as $row) {
              $url = file_get_contents($path . '/' . $row);
              $plug = json_decode($url, true);
              echo '    <li><a class="dropdown-item"
                                                           href="' . base_url() . $plug['path'] . '"><i
                                                                    class="ft-chevron-right"></i> ' . $plug['name'] . '
                                                        </a></li>';
          }
      }
}

function datatable_lang()
{
    $ci =& get_instance();
    $result='';
   $lang= $ci->config->item('mylang');
   $dfile=FCPATH . 'application/language/'.$lang.'/datatable.php';
   if(file_exists($dfile)) $result=include_once($dfile);
    echo $result;
}

function accounting($loc = 0)
{
    $ci =& get_instance();
    $ci->load->database();
    
    // COMMENTED OUT: Location-specific currency formatting
    // Now using ONLY geopos_system formatting settings
    // if ($loc > 0) {
    //     $query = $ci->db->query("SELECT cur FROM geopos_locations WHERE id='$loc' LIMIT 1");
    //     $row = $query->row_array();
    //     $id = $row['cur'];
    //     if ($id > 0) {
    //         $query = $ci->db->query("SELECT * FROM geopos_currencies WHERE id='$id' LIMIT 1");
    //         $row = $query->row_array();
    //         $thosand = $row['thous'];
    //         $dec_point = $row['dpoint'];
    //         $decimal_after = $row['decim'];
    //     }
    // } else {
    
    // ALWAYS USE: geopos_system formatting settings
    $query2 = $ci->db->query("SELECT * FROM univarsal_api WHERE id=4 LIMIT 1");
    $row = $query2->row_array();
    $thosand = $row['key2'];
    $dec_point = $row['key1'];
    $decimal_after = $row['url'];
    // }

    echo " <script type='text/javascript'>accounting.settings = {number: {precision :$decimal_after,thousand: '$thosand',decimal : '$dec_point'}};
var two_fixed=$decimal_after; </script>";

}

/**
 * Safe language line retrieval with fallback
 * This function ensures language lines always return a value, even if missing
 * Automatically processes tax placeholders
 * 
 * @param string $key Language line key
 * @param string $fallback Fallback text if language line is not found
 * @return string Translated text or fallback (with tax placeholders replaced)
 */
if (!function_exists('safe_lang_line')) {
    function safe_lang_line($key, $fallback = '')
    {
        $ci =& get_instance();
        
        // Ensure language is loaded
        if (!isset($ci->lang)) {
            $ci->load->library('lang');
        }
        
        // Get language line (MY_Lang will handle tax placeholders automatically)
        $text = $ci->lang->line($key, FALSE); // FALSE = don't log errors
        
        // If language line not found or empty, use fallback
        if ($text === FALSE || empty($text)) {
            $text = !empty($fallback) ? $fallback : ucwords(str_replace('_', ' ', $key));
        }
        
        // Process tax placeholders if they exist (in case MY_Lang didn't catch them)
        if (strpos($text, '{tax}') !== FALSE || strpos($text, '{Tax}') !== FALSE || strpos($text, '{TAX}') !== FALSE) {
            $tax_name = getTaxName();
            $text = str_replace('{tax}', strtolower($tax_name), $text);
            $text = str_replace('{TAX}', strtoupper($tax_name), $text);
            $text = str_replace('{Tax}', ucfirst(strtolower($tax_name)), $text);
        }
        
        return $text;
    }
}

if (!function_exists('dd')) {
    function dd($var)
    {
        print_r($var);
        exit;
    }
}
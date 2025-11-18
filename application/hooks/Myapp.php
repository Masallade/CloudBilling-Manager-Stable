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

defined('BASEPATH') OR exit('No direct script access allowed');

class Myapp extends CI_Controller
{
    public function appset()
    {
        $ci =& get_instance();
        $ci->load->database();
        
        try {
            $query = $ci->db->query("SELECT * FROM geopos_system WHERE id=1 LIMIT 1");
            $row = $query->row_array();
            
            // Ensure we have a valid row
            if (!$row || empty($row)) {
                // Use default language if database query fails
                $lang = 'english';
            } else {
                $lang = isset($row["lang"]) && !empty($row["lang"]) ? $row["lang"] : 'english';
            }
            
            // Load language files with error handling
            try {
                $ci->lang->load($lang, $lang);
            } catch (Exception $e) {
                // If main language file fails, try english
                if ($lang !== 'english') {
                    $ci->lang->load('english', 'english');
                }
            }
            
            try {
                $ci->lang->load('part', $lang);
            } catch (Exception $e) {
                // Part language file is optional, continue if it fails
            }
            
            // Set config items only if row exists
            if ($row && !empty($row)) {
                $ci->config->set_item('mylang', $lang);
                $ci->config->set_item('ctitle', isset($row["cname"]) ? $row["cname"] : '');
                $ci->config->set_item('address', isset($row["address"]) ? $row["address"] : '');
                $ci->config->set_item('city', isset($row["city"]) ? $row["city"] : '');
                $ci->config->set_item('region', isset($row["region"]) ? $row["region"] : '');
                $ci->config->set_item('country', isset($row["country"]) ? $row["country"] : '');
                $ci->config->set_item('phone', isset($row["phone"]) ? $row["phone"] : '');
                $ci->config->set_item('email', isset($row["email"]) ? $row["email"] : '');
                $ci->config->set_item('tax', isset($row["tax"]) ? $row["tax"] : '');
                $ci->config->set_item('taxno', isset($row["taxid"]) ? $row["taxid"] : '');
                $ci->config->set_item('format_curr', isset($row["currency_format"]) ? $row["currency_format"] : '');
                $ci->config->set_item('prefix', isset($row["prefix"]) ? $row["prefix"] : '');
                $ci->config->set_item('tzone', isset($row["zone"]) ? $row["zone"] : 'UTC');
                $ci->config->set_item('logo', isset($row["logo"]) ? $row["logo"] : '');


                // Set date format
                $dformat = isset($row['dformat']) ? (int)$row['dformat'] : 1;
                switch ($dformat) {
                    case 1:
                        $ci->config->set_item('date', date("d-m-Y"));
                        $ci->config->set_item('dformat', "d-m-Y");
                        $ci->config->set_item('dformat2', "dd-mm-yyyy");
                        break;
                    case 2:
                        $ci->config->set_item('date', date("Y-m-d"));
                        $ci->config->set_item('dformat', "Y-m-d");
                        $ci->config->set_item('dformat2', "yyyy-mm-dd");
                        break;
                    case 3:
                        $ci->config->set_item('date', date("m-d-Y"));
                        $ci->config->set_item('dformat', "m-d-Y");
                        $ci->config->set_item('dformat2', "mm-dd-yyyy");
                        break;
                    default:
                        $ci->config->set_item('date', date("d-m-Y"));
                        $ci->config->set_item('dformat', "d-m-Y");
                        $ci->config->set_item('dformat2', "dd-mm-yyyy");
                        break;
                }
                
                // Set timezone
                $timezone = isset($row["zone"]) && !empty($row["zone"]) ? $row["zone"] : 'UTC';
                date_default_timezone_set($timezone);
            } else {
                // Set defaults if no database row
                $ci->config->set_item('mylang', 'english');
                $ci->config->set_item('dformat', "d-m-Y");
                $ci->config->set_item('dformat2', "dd-mm-yyyy");
                date_default_timezone_set('UTC');
            }
        } catch (Exception $e) {
            // Fallback to defaults if database error
            $ci->lang->load('english', 'english');
            $ci->config->set_item('mylang', 'english');
            $ci->config->set_item('dformat', "d-m-Y");
            $ci->config->set_item('dformat2', "dd-mm-yyyy");
            date_default_timezone_set('UTC');
            log_message('error', 'Myapp::appset() error: ' . $e->getMessage());
        }





    }

}

?>
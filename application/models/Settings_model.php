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

defined('BASEPATH') or exit('No direct script access allowed');

class Settings_model extends CI_Model
{
    public function company_details($id)
    {
        // Get the single record from geopos_system (table should only contain 1 row)
        $this->db->select('*');
        $this->db->from('geopos_system');
        $this->db->limit(1);
        $query = $this->db->get();
        return $query->row_array();
    }

    public function update_company($id, $name, $phone, $email, $tax, $mobile, $mobile2, $address, $city, $region, $country, $postbox, $taxid, $data_share, $sortcode)
    {
        $data = array(
            'cname' => $name,
            'phone' => $phone,
            'email' => $email,
            'tax' => $tax,
            'mobile' => $mobile,
            'mobile2' => $mobile2,
            'address' => $address,
            'city' => $city,
            'region' => $region,
            'country' => $country,
            'postbox' => $postbox,
            'taxid' => $taxid,
            'sortcode' => $sortcode
        );
        
        // Check if any record exists in table (should only be 1 row)
        $this->db->select('id');
        $this->db->from('geopos_system');
        $query = $this->db->get();
        $exists = $query->num_rows() > 0;
        
        if ($exists) {
            // Get existing record id and update
            $existing_record = $query->row_array();
            $existing_id = $existing_record['id'];
            $this->db->set($data);
            $this->db->where('id', $existing_id);
            if ($this->db->update('geopos_system')) {
                echo json_encode(array('status' => 'Success', 'message' =>
                $this->lang->line('UPDATED')));
                if ($data_share != BDATA) {
                    $config_file_path = APPPATH . "config/constants.php";
                    $config_file = file_get_contents($config_file_path);
                    $config_file = str_replace("('BDATA', '" . BDATA . "')", "('BDATA', '$data_share')", $config_file);
                    file_put_contents($config_file_path, $config_file);
                }
            } else {
                echo json_encode(array('status' => 'Error', 'message' =>
                $this->lang->line('ERROR')));
            }
        } else {
            // Insert new record
            $data['id'] = $id;
            if ($this->db->insert('geopos_system', $data)) {
                echo json_encode(array('status' => 'Success', 'message' =>
                $this->lang->line('ADDED')));
                if ($data_share != BDATA) {
                    $config_file_path = APPPATH . "config/constants.php";
                    $config_file = file_get_contents($config_file_path);
                    $config_file = str_replace("('BDATA', '" . BDATA . "')", "('BDATA', '$data_share')", $config_file);
                    file_put_contents($config_file_path, $config_file);
                }
            } else {
                echo json_encode(array('status' => 'Error', 'message' =>
                $this->lang->line('ERROR')));
            }
        }
    }

    public function update_billing($id, $invoiceprefix, $taxid, $taxstatus, $lang)
    {
        $data = array(
            'taxid' => $taxid,
            'tax' => $taxstatus,
            'prefix' => $invoiceprefix,
            'lang' => $lang
        );
        
        // Check if any record exists in table (should only be 1 row)
        $this->db->select('id');
        $this->db->from('geopos_system');
        $query = $this->db->get();
        $exists = $query->num_rows() > 0;
        
        if ($exists) {
            // Get existing record id and update
            $existing_record = $query->row_array();
            $existing_id = $existing_record['id'];
            $this->db->set($data);
            $this->db->where('id', $existing_id);
            if ($this->db->update('geopos_system')) {
                echo json_encode(array('status' => 'Success', 'message' =>
                $this->lang->line('UPDATED')));
            } else {
                echo json_encode(array('status' => 'Error', 'message' =>
                $this->lang->line('ERROR')));
            }
        } else {
            // Insert new record
            $data['id'] = $id;
            if ($this->db->insert('geopos_system', $data)) {
                echo json_encode(array('status' => 'Success', 'message' =>
                $this->lang->line('ADDED')));
            } else {
                echo json_encode(array('status' => 'Error', 'message' =>
                $this->lang->line('ERROR')));
            }
        }
    }

    public function update_language($id, $lang)
    {
        $data = array(
            'lang' => $lang
        );
        
        // Check if any record exists in table (should only be 1 row)
        $this->db->select('id');
        $this->db->from('geopos_system');
        $query = $this->db->get();
        $exists = $query->num_rows() > 0;
        
        if ($exists) {
            // Get existing record id and update
            $existing_record = $query->row_array();
            $existing_id = $existing_record['id'];
            $this->db->set($data);
            $this->db->where('id', $existing_id);
            if ($this->db->update('geopos_system')) {
                return array('status' => 'Success', 'message' => $this->lang->line('UPDATED'));
            } else {
                return array('status' => 'Error', 'message' => $this->lang->line('ERROR'));
            }
        } else {
            // Insert new record
            $data['id'] = $id;
            if ($this->db->insert('geopos_system', $data)) {
                return array('status' => 'Success', 'message' => $this->lang->line('ADDED'));
            } else {
                return array('status' => 'Error', 'message' => $this->lang->line('ERROR'));
            }
        }
    }

    public function prefix()
    {
        $this->db->select('*');
        $this->db->from('univarsal_api');
        $this->db->where('id', 51);
        $query = $this->db->get();
        $result = $query->row_array();
        $this->db->select('other');
        $this->db->from('univarsal_api');
        $this->db->where('id', 52);
        $query = $this->db->get();
        $result['pos'] = $query->row_array()['other'];
        return $result;
    }

    public function update_prefix($invoiceprefix, $q_prefix, $p_prefix, $r_prefix, $s_prefix, $t_prefix, $o_prefix, $pos_prefix)
    {
        $data = array(
            'name' => $q_prefix,
            'key1' => $p_prefix,
            'key2' => $r_prefix,
            'url' => $s_prefix,
            'method' => $t_prefix,
            'other' => $o_prefix
        );
        $this->db->set($data);
        $this->db->where('id', 51);
        $this->db->update('univarsal_api');
        $data = array(
            'other' => $pos_prefix
        );
        $this->db->set($data);
        $this->db->where('id', 52);
        $this->db->update('univarsal_api');
        
        // Handle geopos_system update/insert
        $system_data = array('prefix' => $invoiceprefix);
        $id = 1;
        
        // Check if any record exists in table (should only be 1 row)
        $this->db->select('id');
        $this->db->from('geopos_system');
        $query = $this->db->get();
        $exists = $query->num_rows() > 0;
        
        if ($exists) {
            // Get existing record id and update
            $existing_record = $query->row_array();
            $existing_id = $existing_record['id'];
            $this->db->set($system_data);
            $this->db->where('id', $existing_id);
            if ($this->db->update('geopos_system')) {
                echo json_encode(array('status' => 'Success', 'message' =>
                $this->lang->line('UPDATED')));
            } else {
                echo json_encode(array('status' => 'Error', 'message' =>
                $this->lang->line('ERROR')));
            }
        } else {
            // Insert new record
            $system_data['id'] = $id;
            if ($this->db->insert('geopos_system', $system_data)) {
                echo json_encode(array('status' => 'Success', 'message' =>
                $this->lang->line('ADDED')));
            } else {
                echo json_encode(array('status' => 'Error', 'message' =>
                $this->lang->line('ERROR')));
            }
        }
    }


    public function update_dtformat($id, $tzone, $dateformat)
    {
        $data = array(
            'dformat' => $dateformat,
            'zone' => $tzone
        );
        
        // Check if any record exists in table (should only be 1 row)
        $this->db->select('id');
        $this->db->from('geopos_system');
        $query = $this->db->get();
        $exists = $query->num_rows() > 0;
        
        if ($exists) {
            // Get existing record id and update
            $existing_record = $query->row_array();
            $existing_id = $existing_record['id'];
            $this->db->set($data);
            $this->db->where('id', $existing_id);
            if ($this->db->update('geopos_system')) {
                echo json_encode(array('status' => 'Success', 'message' =>
                $this->lang->line('UPDATED')));
            } else {
                echo json_encode(array('status' => 'Error', 'message' =>
                $this->lang->line('ERROR')));
            }
        } else {
            // Insert new record
            $data['id'] = $id;
            if ($this->db->insert('geopos_system', $data)) {
                echo json_encode(array('status' => 'Success', 'message' =>
                $this->lang->line('ADDED')));
            } else {
                echo json_encode(array('status' => 'Error', 'message' =>
                $this->lang->line('ERROR')));
            }
        }
    }

    public function companylogo($id, $pic)
    {
        // Check if any record exists in table (should only be 1 row)
        $this->db->select('id, logo');
        $this->db->from('geopos_system');
        $query = $this->db->get();
        $result = $query->row_array();
        $exists = $query->num_rows() > 0;
        
        $data = array(
            'logo' => $pic
        );
        
        if ($exists) {
            // Get existing record id and update
            $existing_id = $result['id'];
            $this->db->set($data);
            $this->db->where('id', $existing_id);
            if ($this->db->update('geopos_system')) {
                if (!empty($result['logo'])) {
                    unlink(FCPATH . 'userfiles/company/' . $result['logo']);
                    unlink(FCPATH . 'userfiles/company/thumbnail/' . $result['logo']);
                }
            }
        } else {
            // Insert new record
            $data['id'] = $id;
            $this->db->insert('geopos_system', $data);
        }
    }

    //email

    public function email_smtp()
    {
        $this->db->select('*');
        $this->db->from('geopos_smtp');
        $query = $this->db->get();
        return $query->row_array();
    }

    public function update_smtp($host, $port, $auth, $auth_type, $username, $password, $sender)
    {
        $data = array(
            'host' => $host,
            'port' => $port,
            'auth' => $auth,
            'auth_type' => $auth_type,
            'username' => $username,
            'password' => $password,
            'sender' => $sender,
        );
        $this->db->set($data);
        $this->db->where('id', 1);
        if ($this->db->update('geopos_smtp')) {
            echo json_encode(array('status' => 'Success', 'message' =>
            $this->lang->line('UPDATED')));
        } else {
            echo json_encode(array('status' => 'Error', 'message' =>
            $this->lang->line('ERROR')));
        }
    }

    private function validate_p($var1, $var2)
    {
        $var2 .= '&app=' . base_url();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, SERVICE);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "var1=" . urlencode($var1) . "&var2=" . urlencode($var2));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        $output = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);
        
        // Handle curl errors
        if ($output === false || !empty($curl_error)) {
            // If curl fails, return empty string to allow local activation
            // You can change this behavior if needed
            return '';
        }
        
        // Handle HTTP errors
        if ($http_code != 200) {
            return '';
        }
        
        return trim($output);
    }

    public function update_atformat($var1, $var2)
    {
        // BYPASS: Skip all validation and always activate
        $output = '1'; // Always set to "1" (valid license)
        
        $this->load->driver('cache');
        $this->cache->file->save('cache_validation', $output);
        active($output);
    }

    public function get_terms($id)
    {
        $this->db->select('*');
        $this->db->from('geopos_terms');
        $this->db->where('id', $id);
        $query = $this->db->get();
        return $query->row_array();
    }

    public function billingterms()
    {
        $this->db->select('id,title,type');
        $this->db->from('geopos_terms');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function slabs()
    {
        $this->db->select('*');
        $this->db->from('geopos_config');
        $this->db->where('type', 2);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function add_slab($tname, $trate, $ttype, $ttype2)
    {
        $data = array(
            'type' => 2,
            'val1' => $tname,
            'val2' => $trate,
            'val3' => $ttype,
            'val4' => $ttype2
        );
        if ($this->db->insert('geopos_config', $data)) {
            echo json_encode(array('status' => 'Success', 'message' =>
            $this->lang->line('ADDED')));
        } else {
            echo json_encode(array('status' => 'Error', 'message' =>
            $this->lang->line('ERROR')));
        }
    }

    public function add_term($title, $type, $term)
    {
        $data = array(
            'title' => $title,
            'type' => $type,
            'terms' => $term
        );
        if ($this->db->insert('geopos_terms', $data)) {
            echo json_encode(array('status' => 'Success', 'message' =>
            $this->lang->line('ADDED')));
        } else {
            echo json_encode(array('status' => 'Error', 'message' =>
            $this->lang->line('ERROR')));
        }
    }

    public function edit_term($id, $title, $type, $term)
    {
        $data = array(
            'title' => $title,
            'type' => $type,
            'terms' => $term
        );
        $this->db->set($data);
        $this->db->where('id', $id);
        if ($this->db->update('geopos_terms', $data)) {
            echo json_encode(array('status' => 'Success', 'message' =>
            $this->lang->line('UPDATED')));
        } else {
            echo json_encode(array('status' => 'Error', 'message' =>
            $this->lang->line('ERROR')));
        }
    }


    public function edit_terms()
    {
        $this->db->select('id,title');
        $this->db->from('geopos_terms');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function auto_post()
    {
        // Get the single record from geopos_system (table should only contain 1 row)
        $this->db->select('auto_post');
        $this->db->from('geopos_system');
        $this->db->limit(1);
        $query = $this->db->get();
        $result = $query->row_array();
        $auto_post = !empty($result) && $result['auto_post'] == 1;
        return $auto_post;
    }
    public function show_profit_per()
    {
        // Get the single record from geopos_system (table should only contain 1 row)
        $this->db->select('show_profit_per');
        $this->db->from('geopos_system');
        $this->db->limit(1);
        $query = $this->db->get();
        $result = $query->row_array();
        $show_profit_per = !empty($result) && $result['show_profit_per'] == 1;
        return $show_profit_per;
    }
    public function auto_pricing()
    {
        // Get the single record from geopos_system (table should only contain 1 row)
        $this->db->select('auto_pricing');
        $this->db->from('geopos_system');
        $this->db->limit(1);
        $query = $this->db->get();
        $result = $query->row_array();
        $auto_pricing = !empty($result) && $result['auto_pricing'] == 1;
        return $auto_pricing;
    }


    public function theme($tdirection, $menu)
    {
        if ($tdirection != LTR) {
            $config_file_path = APPPATH . "config/constants.php";
            $config_file = file_get_contents($config_file_path);
            $config_file = str_replace(LTR, $tdirection, $config_file);
            file_put_contents($config_file_path, $config_file);
        }
        if ($menu != MENU) {
            $config_file_path = APPPATH . "config/constants.php";
            $config_file = file_get_contents($config_file_path);
            $config_file = str_replace("('MENU', '" . MENU . "')", "('MENU', '$menu')", $config_file);
            file_put_contents($config_file_path, $config_file);
        }
        echo json_encode(array('status' => 'Success', 'message' =>
        $this->lang->line('UPDATED')));
    }

    public function currency()
    {
        // Get the single record from geopos_system (table should only contain 1 row)
        $this->db->select('*');
        $this->db->from('geopos_system');
        $this->db->limit(1);
        $query = $this->db->get();
        $system_data = $query->row_array();
        
        // Get univarsal_api record by name='currency' for currency formatting settings
        $this->db->select('*');
        $this->db->from('univarsal_api');
        $this->db->where('name', 'currency');
        $this->db->limit(1);
        $query = $this->db->get();
        $api_data = $query->row_array();
        
        // Merge both arrays
        if (empty($system_data)) {
            $system_data = array();
        }
        if (empty($api_data)) {
            $api_data = array();
        }
        
        $result = array_merge($system_data, $api_data);
        
        // If no result at all, return defaults to prevent errors
        if (empty($result)) {
            return array(
                'currency' => '',
                'auto_post' => 0,
                'auto_pricing' => 0,
                'show_profit_per' => 0,
                'tax_type' => 'VAT',
                'tax_rate' => 20,
                'key1' => '.',
                'key2' => ',',
                'url' => '2',
                'method' => 'l',
                'other' => '',
                'active' => 0
            );
        }
        
        return $result;
    }

    public function update_currency($id, $currency, $thous_sep, $deci_sep, $decimal, $method, $roundoff = 'Off', $r_precision = 0, $auto_post = 0, $show_profit_per = 0, $auto_pricing = 0, $tax_type = 'VAT', $tax_rate = 20)
    {
        // Data for updating the 'geopos_system' table
        $system_data = array(
            'currency' => $currency,
            'auto_post' => $auto_post,
            'show_profit_per' => $show_profit_per, // New field
            'auto_pricing' => $auto_pricing, // New field
            'tax_type' => $tax_type, // Tax type (VAT, GST, etc.)
            'tax_rate' => $tax_rate // Tax rate percentage
        );

        // Check if any record exists in table (should only be 1 row)
        $this->db->select('id');
        $this->db->from('geopos_system');
        $query = $this->db->get();
        $exists = $query->num_rows() > 0;
        
        if ($exists) {
            // Get existing record id and update
            $existing_record = $query->row_array();
            $existing_id = $existing_record['id'];
            $this->db->set($system_data);
            $this->db->where('id', $existing_id);
            $update_system = $this->db->update('geopos_system');
        } else {
            // Insert new record
            $system_data['id'] = $id;
            $update_system = $this->db->insert('geopos_system', $system_data);
        }

        // Data for updating the 'univarsal_api' table
        $api_data = array(
            'key1' => $deci_sep,
            'key2' => $thous_sep,
            'url' => $decimal,
            'method' => $method,
            'other' => $roundoff,
            'active' => $r_precision,
        );

        // Check if record exists in univarsal_api by name
        $this->db->select('id');
        $this->db->from('univarsal_api');
        $this->db->where('name', 'currency');
        $query = $this->db->get();
        $exists = $query->num_rows() > 0;
        
        if ($exists) {
            // Update existing record
            $this->db->set($api_data);
            $this->db->where('name', 'currency');
            $update_api = $this->db->update('univarsal_api');
        } else {
            // Insert new record
            $api_data['name'] = 'currency';
            $update_api = $this->db->insert('univarsal_api', $api_data);
        }

        // Return status based on both update operations
        if ($update_system && $update_api) {
            return array('status' => 'Success', 'message' => 'Settings updated successfully!');
        } else {
            return array('status' => 'Error', 'message' => 'Error occurred while updating settings.');
        }
    }




    public function delete_terms($id)
    {
        $this->db->select('id');
        $this->db->from('geopos_terms');

        $query = $this->db->get();
        if ($query->num_rows() > 1) {
            return $this->db->delete('geopos_terms', array('id' => $id));
        } else {
            return false;
        }
    }

    public function delete_slab($id)
    {
        return $this->db->delete('geopos_config', array('id' => $id, 'type' => 2));
    }

    public function update_tax($id, $taxid, $taxstatus, $tdirection)
    {
        $data = array(
            'taxid' => $taxid,
            'tax' => $taxstatus
        );
        
        // Check if any record exists in table (should only be 1 row)
        $this->db->select('id');
        $this->db->from('geopos_system');
        $query = $this->db->get();
        $exists = $query->num_rows() > 0;
        
        if ($exists) {
            // Get existing record id and update
            $existing_record = $query->row_array();
            $existing_id = $existing_record['id'];
            $this->db->set($data);
            $this->db->where('id', $existing_id);
            if ($this->db->update('geopos_system')) {
                echo json_encode(array('status' => 'Success', 'message' =>
                $this->lang->line('UPDATED')));
            } else {
                echo json_encode(array('status' => 'Error', 'message' =>
                $this->lang->line('ERROR')));
            }
        } else {
            // Insert new record
            $data['id'] = $id;
            if ($this->db->insert('geopos_system', $data)) {
                echo json_encode(array('status' => 'Success', 'message' =>
                $this->lang->line('ADDED')));
            } else {
                echo json_encode(array('status' => 'Error', 'message' =>
                $this->lang->line('ERROR')));
            }
        }

        if ($tdirection != LTR) {
            $config_file_path = APPPATH . "config/constants.php";
            $config_file = file_get_contents($config_file_path);
            $config_file = str_replace(GST_INCL, $tdirection, $config_file);
            file_put_contents($config_file_path, $config_file);
        }
    }

    public function automail()
    {
        $this->db->select('*');
        $this->db->from('univarsal_api');
        $this->db->where('id', 56);
        $query = $this->db->get();
        return $query->row_array();
    }

    public function update_automail($email, $sms)
    {
        $data = array(
            'key1' => $email,
            'key2' => $sms
        );
        $this->db->set($data);
        $this->db->where('id', 56);
        if ($this->db->update('univarsal_api')) {
            echo json_encode(array('status' => 'Success', 'message' =>
            $this->lang->line('UPDATED')));
        } else {
            echo json_encode(array('status' => 'Error', 'message' =>
            $this->lang->line('ERROR')));
        }
    }

    public function logs()
    {
        $this->db->select('*');
        $this->db->from('geopos_log');
        $this->db->order_by('id', 'DESC');
        // $this->db->limit(450, 'DESC');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function posstyle($posvs)
    {
        if ($posvs != POSV) {
            $config_file_path = APPPATH . "config/constants.php";
            $config_file = file_get_contents($config_file_path);
            $config_file = str_replace("('POSV', '" . POSV . "')", "('POSV', '$posvs')", $config_file);
            file_put_contents($config_file_path, $config_file);
        }
        echo json_encode(array('status' => 'Success', 'message' =>
        $this->lang->line('UPDATED')));
    }

    public function zerostock($os)
    {
        $data = array(
            'key1' => $os
        );
        $this->db->set($data);
        $this->db->where('id', 63);
        $this->db->update('univarsal_api');
    }

    public function billing_settings($stock, $serial, $expired)
    {
        $this->zerostock($stock);
        $data = array(
            'key1' => $serial,
            'key2' => $expired

        );
        $this->db->set($data);
        $this->db->where('id', 67);

        if ($this->db->update('univarsal_api')) {
            echo json_encode(array('status' => 'Success', 'message' =>
            $this->lang->line('UPDATED')));
        } else {
            echo json_encode(array('status' => 'Error', 'message' =>
            $this->lang->line('ERROR')));
        }
    }

    public function custom_fields($id = 0)
    {

        if ($id) {
            $this->db->select('*');
            $this->db->from('geopos_custom_fields');
            $this->db->where('id', $id);
            $query = $this->db->get();
            return $query->row_array();
        } else {


            $this->db->select('*');
            $this->db->from('geopos_custom_fields');

            $query = $this->db->get();
            return $query->result_array();
        }
    }

    public function custom_field_add($f_name, $f_type, $f_module, $f_view, $f_required, $f_placeholder, $f_description)
    {
        $data = array(
            'f_module' => $f_module,
            'f_type' => $f_type,
            'name' => $f_name,
            'placeholder' => $f_placeholder,
            'value_data' => $f_description,
            'f_view' => $f_view,
            'other' => $f_required

        );

        if ($this->db->insert('geopos_custom_fields', $data)) {
            echo json_encode(array('status' => 'Success', 'message' =>
            $this->lang->line('ADDED') . "  <a href='add_custom_field' class='btn btn-indigo btn-lg'><span class='icon-plus-circle' aria-hidden='true'></span>  </a>   <a href='custom_fields' class='btn btn-info btn-lg'><span class='icon-list' aria-hidden='true'></span>  </a>"));
        } else {
            echo json_encode(array('status' => 'Error', 'message' =>
            $this->lang->line('ERROR')));
        }
    }

    public function custom_field_edit($id, $f_name, $f_view, $f_required, $f_placeholder, $f_description)
    {
        $data = array(

            'name' => $f_name,
            'placeholder' => $f_placeholder,
            'value_data' => $f_description,
            'f_view' => $f_view,
            'other' => $f_required

        );
        $this->db->set($data);
        $this->db->where('id', $id);
        if ($this->db->update('geopos_custom_fields')) {
            echo json_encode(array('status' => 'Success', 'message' =>
            $this->lang->line('UPDATED')));
        } else {
            echo json_encode(array('status' => 'Error', 'message' =>
            $this->lang->line('ERROR')));
        }
    }

    public function printinvoice($posvs)
    {
        if ($posvs != INVV) {
            $config_file_path = APPPATH . "config/constants.php";
            $config_file = file_get_contents($config_file_path);

            $config_file = str_replace("('INVV', '" . INVV . "')", "('INVV', '$posvs')", $config_file);
            file_put_contents($config_file_path, $config_file);
        }
        echo json_encode(array('status' => 'Success', 'message' =>
        $this->lang->line('UPDATED')));
    }

    public function debug($debug)
    {

        if ($debug != ENVIRONMENT) {
            $config_file_path = FCPATH . "index.php";
            $config_file = file_get_contents($config_file_path);
            $str1 =  "'" . ENVIRONMENT . "')";
            $str2 = "'$debug')";
            $config_file = str_replace($str1, $str2, $config_file);
            file_put_contents($config_file_path, $config_file);
        }

        echo json_encode(array('status' => 'Success', 'message' =>
        $this->lang->line('UPDATED')));
    }
}

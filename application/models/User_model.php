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
class User_model extends CI_Model
{
    public function isUserLoggedIn($user_id)
    {
        // Query the user_sessions table to check if the user is logged in (is_logged_in = 1)
        $this->db->select('is_logged_in');
        $this->db->where('user_id', $user_id);
        $query = $this->db->get('user_sessions');

        if ($query->num_rows() > 0) {

              $this->db->select('last_activity, ip_address, email');
            $this->db->from('geopos_users');
            $this->db->where('email',$user_id);
            $row = $this->db->get()->row();


             $current_time = time();
             $last_active = strtotime(isset($row->last_activity) ? $row->last_activity : ''); 

                $inactive_duration = $current_time - $last_active;
                if ($inactive_duration > 60) {
                         $this->User_model->markUserAsLoggedOut($user_id);
                          $this->db->where('ip_address', $row->ip_address);
                          $this->db->delete('ci_sessions');

                  
                }



        return true;


        } else {
            return false;
        }

        
    }

    public function markUserAsLoggedIn($user_id)
    {
        // Check if the user is already logged in on another device
        if ($this->isUserLoggedIn($user_id)) {


            return false;
        }

        // Generate a unique session ID for the user (you may use a secure session ID generator)
        $session_id = uniqid();

        // Create a new session record with is_logged_in set to 1
        $data = array(
            'user_id' => $user_id,
            'session_id' => $session_id,
            'is_logged_in' => 1,
            'login_time' => time()
        );

        // Insert the session record into the user_sessions table
        $this->db->insert('user_sessions', $data);

        // Return the session ID (for session management or identification)
        return $session_id;
    }

    public function get_due_invoices()
    {
        $this->db->select('invoiceduedate AS date, COUNT(tid) AS invoice_count, SUM(total) AS total_due');
        $this->db->from('geopos_invoices');
        $this->db->where_in('inv_type', ['INVOICE', 'DAYPASS']);
        $this->db->where('status', 'due');
        $this->db->group_by('invoiceduedate');
        $query = $this->db->get();

        return $query->result_array(); // Return the result as an array
    }

    public function markUserAsLoggedOut($user_id)
    {
        // Delete the session record associated with the user
        $this->db->where('user_id', $user_id);
        $this->db->delete('user_sessions');

        // You can add error handling here if needed
    }
}

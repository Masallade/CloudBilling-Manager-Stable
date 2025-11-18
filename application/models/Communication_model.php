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


class Communication_model extends CI_Model
{

    public function __construct()
    {
        $this->load->model('settings_model', 'settings');
        // parent::__construct();
    }

    public function send_email($mailto, $mailtotitle, $subject, $message, $attachmenttrue = false, $attachment = '')
    {
        $this->load->library('ultimatemailer');
        $company = $this->settings->company_details(1);
        // Get SMTP configuration
        $this->db->select('host, port, auth, auth_type, username, password, sender');
        $this->db->from('geopos_smtp');
        $query = $this->db->get();
    
        if ($query->num_rows() > 0) {
            $smtpresult = $query->row_array();
    
            // Extract SMTP configuration values
            $host = $smtpresult['host'];
            $port = $smtpresult['port'];
            $auth = $smtpresult['auth'];
            $auth_type = $smtpresult['auth_type'];
            $username = $smtpresult['username'];
            $password = $smtpresult['password'];
            $mailfrom = $smtpresult['sender'];
            // $mailfromtilte = $this->config->item('ctitle');
            $mailfromtilte = $company['cname'];
    
            // Log the SMTP configuration being used (without password for security)
            log_message('error', 'Using SMTP config from database: Host=' . $host . ', Port=' . $port . ', Auth=' . $auth . ', AuthType=' . $auth_type . ', Username=' . $username . ', From=' . $mailfrom);

            // Attempt to send email
            $result = $this->ultimatemailer->load(
                $host, $port, $auth, $auth_type, $username, $password,
                $mailfrom, $mailfromtilte, $mailto, $mailtotitle, $subject,
                $message, $attachmenttrue, $attachment
            );
    
            if ($result) {
                log_message('error', 'Email sent successfully to: ' . $mailto);
                return array("status" => "Success", "message" => "Email Sent Successfully!");
            } else {
                log_message('error', 'Email sending failed for: ' . $mailto);
                return array("status" => "Error", "message" => "Email sending failed. Check logs for details.");
            }
        } else {
            return array("status" => "Error", "message" => "SMTP configuration not found.");
        }
    }
    
    
    public function send_corn_email($mailto, $mailtotitle, $subject, $message, $attachmenttrue = false, $attachment = '')
    {
        $this->load->library('ultimatemailer');
        $this->db->select('host,port,auth,auth_type,username,password,sender');
        $this->db->from('geopos_smtp');
        $query = $this->db->get();
        $smtpresult = $query->row_array();
        $host = $smtpresult['host'];
        $port = $smtpresult['port'];
        $auth = $smtpresult['auth'];
        $auth_type = $smtpresult['auth_type'];
        $username = $smtpresult['username'];;
        $password = $smtpresult['password'];
        $mailfrom = $smtpresult['sender'];
        $mailfromtilte = $this->config->item('ctitle');
        return $this->ultimatemailer->corn_mail($host, $port, $auth, $auth_type, $username, $password, $mailfrom, $mailfromtilte, $mailto, $mailtotitle, $subject, $message, $attachmenttrue, $attachment);

    }

    public function group_email($recipients, $subject, $message, $attachmenttrue, $attachment,$m=true)
    {
        $this->load->library('ultimatemailer');
        $this->db->select('host,port,auth,auth_type,username,password,sender');
        $this->db->from('geopos_smtp');
        $query = $this->db->get();
        $smtpresult = $query->row_array();
        $host = $smtpresult['host'];
        $port = $smtpresult['port'];
        $auth = $smtpresult['auth'];
        $auth_type = $smtpresult['auth_type'];
        $username = $smtpresult['username'];;
        $password = $smtpresult['password'];
        $mailfrom = $smtpresult['sender'];
        $mailfromtilte = $this->config->item('ctitle');
        return $this->ultimatemailer->group_load($host, $port, $auth, $auth_type, $username, $password, $mailfrom, $mailfromtilte, $recipients, $subject, $message, $attachmenttrue, $attachment,$m);

    }
}
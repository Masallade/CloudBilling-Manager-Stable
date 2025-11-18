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

use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\Printer;

require_once APPPATH . "libraries/tcpdf/PDFMerger.php";

use PDFMerger\PDFMerger;

class Invoices extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library("Aauth");

        if (!$this->aauth->is_loggedin()) {
            redirect('/user/', 'refresh');
        }
        if (!$this->aauth->permission_new(null, 'salesAccess')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }

        if ($this->aauth->get_user()->roleid == 2) {
            $this->limited = $this->aauth->get_user()->id;
        } else {
            $this->limited = '';
        }
        $this->load->model('Stockreturn_model', 'stockreturn');
        $this->load->model('invoices_model', 'invocies');
        $this->load->model('plugins_model', 'plugins');
        $this->load->model('settings_model', 'settings');
        $this->load->model('customers_model', 'customers');
        $this->load->library("Custom");
        $this->load->library('XLSXWriter');
        $this->load->helper('download');
        $this->li_a = 'sales';
        $this->load->library('dpdf');
    }

    // Helper function to safely get language line with fallback
    private function safe_lang_line($key, $fallback = '')
    {
        $text = $this->lang->line($key);
        if ($text === FALSE || empty($text)) {
            return $fallback ? $fallback : $key;
        }
        return $text;
    }

    // Helper function to format status text with proper fallback
    private function format_status_text($status)
    {
        if (empty($status)) {
            return '';
        }
        // Handle status with underscores (e.g., "payment_due" -> "Payment Due")
        $status_key = ucwords(str_replace('_', ' ', $status));
        $status_text = $this->lang->line($status_key);
        if ($status_text === FALSE || empty($status_text)) {
            // Fallback to original status if translation not found
            $status_text = ucwords(str_replace('_', ' ', $status));
        }
        return $status_text;
    }

    // Helper function to format dates consistently
    private function format_invoice_date($date_value)
    {
        $formatted_date = '';
        if (isset($date_value) && !empty($date_value)) {
            $date_value = trim($date_value);
            // Skip invalid MySQL dates
            if ($date_value && $date_value != '0000-00-00' && $date_value != '0000-00-00 00:00:00') {
                // Parse the date (format: YYYY-MM-DD or YYYY-MM-DD HH:MM:SS)
                $date_parts = explode(' ', $date_value);
                $date_only = $date_parts[0]; // Get just the date part

                // Try to format using dateformat function
                try {
                    $formatted = dateformat($date_only);
                    if (!empty($formatted)) {
                        $formatted_date = $formatted;
                    } else {
                        // If dateformat returns empty, format manually
                        $timestamp = strtotime($date_only);
                        if ($timestamp !== false && $timestamp > 0) {
                            $date_format = $this->config->item('dformat');
                            if (!$date_format) {
                                $date_format = 'd-m-Y'; // Default format
                            }
                            $formatted_date = date($date_format, $timestamp);
                        }
                    }
                } catch (Exception $e) {
                    // If dateformat throws exception, format manually
                    $timestamp = strtotime($date_only);
                    if ($timestamp !== false && $timestamp > 0) {
                        $date_format = $this->config->item('dformat');
                        if (!$date_format) {
                            $date_format = 'd-m-Y';
                        }
                        $formatted_date = date($date_format, $timestamp);
                    } else {
                        // Show raw date as last resort
                        $formatted_date = $date_only;
                    }
                }
            }
        }
        return $formatted_date;
    }

    // Helper function to get vattype from products table
    private function get_product_vattype($pid)
    {
        $this->db->select('code_type');
        $this->db->from('geopos_products');
        $this->db->where('pid', $pid);
        $query = $this->db->get();
        $result = $query->row_array();
        return $result['code_type'] ?? 'T0';
    }

    public function quotattion_invoice($printing = true, $id = -1)
    {
        $company = $this->settings->company_details(1);

        $productCounter = 0;
        $productsPerPage = 18;

        $hide_balance = 0;
        $tid = $id;
        if ($tid == -1) {
            $tid = $this->input->get('id');
            $hide_balance = ($this->input->get('hide_balance') == 1);
        }
        $data['id'] = $tid;
        $data['invoice'] = $this->invocies->invoice_details($tid, $this->limited);
        $data['products'] = $this->invocies->invoice_products($tid);
        $data['employee'] = $this->invocies->employee($data['invoice']['eid']);
        if ($data['invoice']['i_class'] == 1) {
            $pref = prefix(7);
        } else {
            $pref = $this->config->item('prefix');
        }
        ob_end_clean();
        ob_start();

        $sql = 'SELECT sum(total)-sum(pamnt) as "cust_balance" FROM geopos_invoices WHERE status="due" and csd="' . $data["invoice"]["csd"] . '" or status="partial" and csd="' . $data["invoice"]["csd"] . '"';
        $query = $this->db->query($sql);
        $response = $query->result_array();
        $cust_balance_inv = $response[0]['cust_balance'];

        // $cust_balance_inv= $response[0]['cust_balance'];
        $sql2 = "SELECT content FROM `geopos_notes` ORDER BY `geopos_notes`.`id`  DESC LIMIT 1";
        $query2 = $this->db->query($sql2);
        $response2 = $query2->result_array();
        $content2 = $response2[0]['content'];

        $sql2 = 'SELECT data FROM `geopos_custom_data` where rid="' . $data["invoice"]["csd"] . '"';
        $query2 = $this->db->query($sql2);
        $response2 = $query2->result_array();
        $custom_fields = $response2[0]['data'];

        $d = $this->input->get('d');
        if ($d == '1' || $d == 1) {
            $sql = 'Update geopos_invoices set print_status=1 where id= ' . $tid . ' ';
            $this->db->query($sql);
        }



        ini_set('memory_limit', '64M');

        $data_tr = "";

        $left_logo = base_url("userfiles/company/" . $company['logo']);
        $data['left_logo'] = $left_logo;
        $right_logo = base_url("assets/images/halal_logo.jpg");
        $data['right_logo'] = $right_logo;
        ob_end_clean();
        if ($data['invoice']['inv_type'] == 'INVOICE') {
            $header = '<html><head><style>@page { margin: 350px 50px 200px; } header{ position: fixed;  top: -340px;}
            table{width:100%;}.pagenum:after {content: counter(page)}</style> </head><body><header>';
            $header .= '<table style="width:100%;">
            <tr style="border:1px solid #1367A4;">';
            // $header .= '<td style="width:100%;"><img src="' . $left_logo . '" height="165px" width="270px"></td>';
            $header .= '<td style="width:100%;" height="165px">
                            <img src="' . $left_logo . '" height="145px" width="270px"><br>
                            <div style="font-size:18px; font-weight:bold; text-align:center;">
                                QUOTATION
                            </div>
                        </td>
            <td style="width:40%;"><img src="' . $right_logo . '" height="60px" width="60px"></td>
            <td style="width:100%;"><br><b>' . $company['cname'] . '</b><br>' . $company['address'] . ' ' . $company['city'] . ' '
                . $company['region'] . ', ' . $company['postbox'] . ', ' . $company['country'] . ' <br> Phone:&nbsp;&nbsp;&nbsp;' . $company['phone'] . ' <br> Mobile:&nbsp;&nbsp;' . $company['mobile'] . ' <br>
                <table style="border: 1px solid #1367A4;
                border-collapse: collapse;margin-left:25px;margin-top:10px; border-radius: 6px;width:190px;">
                <tr style="">
                            <th  style="border: 1px solid #1367A4; width:80px;
                            border-collapse: collapse;font-size: 13px;text-align: center" >' . $this->lang->line('Date') . '
                                </th>
                                <th  style="border: 1px solid #1367A4;
                                border-collapse: collapse;font-size: 13px;text-align: center">' . $this->lang->line('Quote') . '
                                </th>
                                <th  style="border: 1px solid #1367A4;
                                border-collapse: collapse;font-size: 13px;text-align: center">' . $this->lang->line('A/C') . '
                                </th>
                </tr>
                <tr >
                    <td style="width:30%;font-size: 13px;border: 1px solid #1367A4;border-collapse: collapse;text-align: center">
                    ' . date("d-m-Y", strtotime($data["invoice"]["invoicedate"])) . '
                    </td>
                    <td style="font-size: 13px;border: 1px solid #1367A4;border-collapse: collapse;text-align: center">' . $data['invoice']['tid'] . '</td>
                    <td style="font-size: 13px;border: 1px solid #1367A4;border-collapse: collapse;text-align: center">' . $data["invoice"]["name"] . '</td>
                </tr>
                </table>
            </td>
            </tr></table>';
        } else {
            $header = '<html><head><style>@page { margin: 350px 50px 200px; }header{ position: fixed;  top: -320px;}table{width:100%;}.pagenum:after {content: counter(page)}</style> </head><body><header><span style="float:right;"  class="pagenum">Page:</span>';
            $header .= ' <div style="width:100%; "><div style="width:23%; float:left; text-align:center;">  <br>
                </div></div><div><p style="text-align:center;height:84px;">Note: This is not a valid Invoice  </p>
                    Date: ' . date("d-m-Y", strtotime($data["invoice"]["invoicedate"])) . '
                </div><div style="clear:both"></div>';
        }

        $header .= '<div style="width:40%; float:left; margin-top:5px;">'; // START container
        // Top box: Company name
        $header .= '<div style="border:1px solid #1367A4; height:70px; padding: 4px 5px; border-radius: 6px; margin-bottom:5px;">';
        if ($data['invoice']['company']) {
            $header .= $data['invoice']['company'] . '<br>';
        }


        if ($data['invoice']['inv_type'] == 'INVOICE') {
            $header .= $data['invoice']['cust_address'] . ' ' . $data['invoice']['cust_city'] . '<br>' . $data['invoice']['cust_postcode'];
            $header .= '</div>';
            // Bottom box: Address + Terms
            $header .= '<div style="border:1px solid #1367A4; height:25px; padding: 4px 5px; border-radius: 6px; font-size: 13px;">';
            if ($data['invoice']['pamt_terms']) {
                $header .= '<b><span style="background-color:yellow">TERMS:</span></b> ' . $custom_fields;
            }
        } else {
            $header .= $data['invoice']['cust_address'] . ' ' . $data['invoice']['cust_city'] . '<br>' . $data['invoice']['cust_postcode'];
            $header .= '</div>';
            // Bottom box: Address + Terms
            $header .= '<div style="border:1px solid #1367A4; height:25px; padding: 4px 5px; border-radius: 6px; font-size: 13px;">';
            if ($data['invoice']['pamt_terms']) {
                $header .= '<b><span style="background-color:yellow">TERMS:</span></b> ' . $custom_fields;
            }
        }
        $header .= '</div></div><div style="float:right;margin-left:-150px;margin-top:-30px;width:50%;" ><div style=" font-size: 25px;">';

        if ($data['invoice']['inv_type'] == 'INVOICE') {
            $header .= '<br> ';
            $header .= '</div><table style="height:5%;  font-size: 14px; border:1px solid #1367A4;border-radius: 6px;"> 
        <tr ><td ><b>' . $content2 . '</b></td>
        </tr>';
            $header .= '</table>';
        } else {
            $header .= '<span style="font-size: 32px; transform: translate(-78px, -38px)" >Day Pass</span>';
            if ($data['invoice']['driver_name'] != '' && $data['invoice']['vehicle_no'] != '') {
                $header .= '</div><table style="height:5%;  font-size: 14px; border:1px solid #1367A4;border-radius: 6px;"> 
            
            <tr ><td ><b>Driver Name: </b></td></tr>
            <tr ><td >' . $data['invoice']['driver_name'] . ' ' . $data['invoice']['vehicle_no'] . '</td>
            </tr>';
                $header .= '</table>';
            }
        }
        $header .= '</div></div></header>';

        $header .= '<table style="width:100%; font-size: 14px;  margin-top:-43px;"><tr><td style="width: 100%;">
        <h3 style="margin-top:10px;  text-align:center; color:black; font-size:18px;">';
        $header .= '<span style="color:red; font-weight:bold;">NOTE:</span> ';
        $header .= '<span style="font-weight:bold;">THIS IS NOT A VALID INVOICE, IT\'S JUST A QUOTATION</span>';
        $header .= '</h3></td></tr></table>';

        $header .= '<div style="clear:both"></div> <main><div style="width:100%; margin-top:-17px; "><table width="100%" class="myProducts" style="border:1px solid #1367A4;border-radius: 6px;"><thead><tr style="font-size: 14px; background:#ccc; padding:4px;"><th style="width: 12%; text-align:left; ">' . $this->lang->line('Quantity') . '</th><th style="border-left: 1px solid #1367A4;width: 45%; text-align:left;">' . $this->lang->line('Details') . '</th><th style="width: 13%; text-align:left; border-left: 1px solid #1367A4;">' . $this->lang->line('Unit Price') . '</th><th style="width: 20%; text-align:left; border-left: 1px solid #1367A4;">' . $this->lang->line('Net Amount') . '</th><th style="width: 10%;  text-align:left;border-left: 1px solid #1367A4;">' . getTaxName() . '</th></tr></thead>';
        $header .= '<tbody style="font-size:15px;">';

        $ns = 0;
        foreach ($data['products'] as $row) {
            if ($row['qty'] > 0) {
                $productCounter++;
                $ns++;

                if ($productCounter % $productsPerPage === 0) {
                    if ($productCounter > 0) {
                        // Close the previous table if it's not the first page
                        $header .= '</tbody>';
                        $header .= '</table>';
                    }

                    // Start a new page
                    $header .= '<div style="page-break-after: always;"></div>';
                    $header .= '<table style="border:1px solid #1367A4;border-radius: 6px;">';
                    $header .= '<thead>';
                    $header .= '<tr style="font-size: 14px; background: #ccc; padding: 4px;">';
                    $header .= '<th style="width: 12%; text-align: left;">' . $this->lang->line('Quantity') . '</th>';
                    $header .= '<th style="border-left: 1px solid #1367A4; width: 45%; text-align: left;">' . $this->lang->line('Details') . '</th>';
                    $header .= '<th style="width: 13%; text-align: left; border-left: 1px solid #1367A4;">' . $this->lang->line('Unit Price') . '</th>';
                    $header .= '<th style="width: 20%; text-align: left; border-left: 1px solid #1367A4;">' . $this->lang->line('Net Amount') . '</th>';
                    $header .= '<th style="width: 10%; text-align: left; border-left: 1px solid #1367A4;">' . getTaxName() . '</th>';
                    $header .= '</tr>';
                    $header .= '</thead>';
                    $header .= '<tbody>';
                }
                if (empty($row['product_des']) || $row['product_des'] == "") {
                } else {
                    if ($row['serial'] == '1') {
                        $header .= '<tr style="background:#FBF36D; style="font-size:18px;"">';
                    } else {
                        $header .= '<tr ' . (($ns % 2 == 0) ? 'style=" background: #f1f4fb;"' : '') . '>';
                    }
                    $header .= '<td style=" border-bottom:1px solid white;">' . (int)$row["qty"] .  '</td style=" border-bottom:1px solid white;">
                    <td style=" border-bottom:1px solid white;">' . $row["product_des"] . '</td>
                    <td style=" border-bottom:1px solid white;">' . rev_amountExchange_s($row['price']) . '</td>
                     <td style=" border-bottom:1px solid white;">' . rev_amountExchange_s($row['subtotal']) . '</td>
                     <td style=" border-bottom:1px solid white;">' . rev_amountExchange_s($row['totaltax']) . ' </td></tr>';
                }
            }
        }
        $header .= '</tbody></table></div></main>';
        // Use database values instead of calculating totals
        if ($data['invoice']['inv_type'] == 'INVOICE') {

            $header .= '<footer style="position:absolute;bottom:-195px;">';



            // if ($data['invoice']['inv_type'] == 'INVOICE') {
            $header .= '<div style="border:1px solid #1367A4;width:59%;height:120px; padding: 8px 0px 5px 4px; border-radius: 6px;float:left;"><span style="color:rgb(48, 37, 133)">
                <b>*MAXIMUM CHANGE ACCEPTED=&#163;40</b></span>';
            $header .= '<div style="font-size: 12px; margin-top: 10px; margin-bottom: 6px;">';
            $header .= '<span style="display: block; padding-bottom: 5px;">*All claims must be notified at the time of delivery.</span>';
            $header .= '<span style="display: block; padding-bottom: 5px;">*Any claims made after delivery will not be considered.</span>';
            $header .= '<span style="display: block; padding-bottom: 5px;">*Frozen and chilled products cannot be returned once delivered.</span>';
            $header .= '<span style="display: block; padding-bottom: 5px;">*Goods will remain the property of ' . $company['cname'] . '.</span>';
            $header .= '<span style="display: block; padding-bottom: 5px;">*Until paid in full by the customer.</span>';
            $header .= '</div>';

            $header .= '<div style="width:100%">
                <div style="clear:both"></div></div><div style="clear:both"></div></div>';
            // }

            // <div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left">Customer:</div>
            // <div style="border:1px solid black;width:8%;height:18px;float:left;margin-top:10px;margin-left:-178px;"></div>
            // <div style="border:1px solid black;width:8%;height:18px;float:left;margin-top:10px;margin-left:-58px;"></div>
            // <div style="border:1px solid black;width:8%;height:18px;float:left;margin-top:10px;margin-left:-118px;"></div><
            // div style="float:left;margin-left:-178px;margin-top:-5px;font-size: 12px;"><b>Cash &nbsp;&nbsp; Card Payment &nbsp;&nbsp;BACS</b></div>
            // <div style="clear:both"></div><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left;">Staff Name:</div>
            // <div style="font-size: 17px;padding-left:10px;border:1px solid black;width:35%;height:20px;float:left;padding-top:3px;padding-bottom:3px;margin-left:-187px;margin-top:3px">&#163;</div>

            $header .= '<div style="float:right;margin-left:320px;width:280px" ><table class="maintable2" style="width:100%;border-radius: 6px; border:1px solid #1367A4;font-size: 14px;">';

            if ($data['invoice']['inv_type'] == 'INVOICE') {
                if (!$hide_balance) {
                }
            }

            $header .= '<tr><td style="width: 60%;  background: #f1f4fb;padding:  4px 0px 4px 7px;">&nbsp;Total NET Amount</td><td> ' . amountExchange($data["invoice"]["subtotal"]) . ' </td></tr>
            <tr ><td style="width: 60%;  background: #f1f4fb;padding: 4px 0px 4px 7px;">&nbsp;Total ' . getTaxName() . ' Amount</td><td> ' . amountExchange($data["invoice"]["tax"]) . '  </td></tr>
            <tr><td style="width: 60%;  background: #f1f4fb;padding: 4px 0px 4px 7px;">&nbsp;Quote Total</td><td> ' . amountExchange($data["invoice"]["total"]) . '   </td></tr>';
            $header .= '</table>';
            if ($data['invoice']['inv_type'] == 'INVOICE') {
                // Bank Transfer Info Box (inline layout)
                $header .= '<div style="width:100%; margin-top:10px; font-size:12px;">';
                $header .= '<table style="width:100%;border:1px solid #1367A4;  border-radius:6px;"><tr>';
                $header .= '<td style="width:40%;"><b>For Bank Transfer:</b></td>';
                $header .= '<td style="width:60%;"><b>' . $company['cname'] . '<br>S/C: ' . $company['sortcode'] . '&nbsp;&nbsp;&nbsp; AC: ' . $company['taxid'] . '</b></td>';
                $header .= '</tr></table>';
                $header .= '</div></div>';
            }
            // $header .= '<span href="mailto:' . $company['email'] . '"><img src="' . base_url("assets/images/mail.jpg") . '" height="32px" width="38px" style="margin-top:10px;">  </span>' . $company['email'] . '<br>
            $header .= '
            <!--<span href="https://www.cloudbillingmanager.com/"><img src="' . base_url("assets/images/web.jpg") . '" height="32px" width="38px" style="margin-top:10px;"> </span>www.cloudbillingmanager.com -->
            </div><div style="clear:both">  
            <span href="mailto:' . $company['email'] . '"><img src="' . base_url("assets/images/mail.jpg") . '" height="32px" width="38px" style="margin-top:10px;">  </span>' . $company['email'] . '
            <span style="text-align: right; href="https://www.5starfoodsltd.com/"><img src="' . base_url("assets/images/web.jpg") . '" height="32px" width="38px" style="margin-top:10px;">http://www.5starfoodsltd.com/</span>
            &nbsp;&nbsp;&nbsp;&nbsp;<span>Thank you for your business!</span>';
            // </div>';
            // $header .= '</table>
            // <span href="mailto:' . $company['email'] . '"><img src="' . base_url("assets/images/mail.jpg") . '" height="32px" width="38px" style="margin-top:10px;">  </span>' . $company['email'] . '<br>
            // <!--<span href="https://www.cloudbillingmanager.com/"><img src="' . base_url("assets/images/web.jpg") . '" height="32px" width="38px" style="margin-top:10px;"> </span>www.cloudbillingmanager.com -->
            // </div><div style="clear:both">
            // <span style="text-align: left; margin-left:70px;">Thank you for your business!</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            // &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;    
            // <span style="text-align: right; margin-left:270px;  href="https://www.5starfoodsltd.com/"><img src="' . base_url("assets/images/web.jpg") . '" height="32px" width="38px" style="margin-top:10px;">www.cloudbillingmanager.com </span>
            // </div>';

            if ($data['invoice']['inv_type'] == 'INVOICE') {
            }
            $header .= ' </footer></body></html>';
        } else {
            $header .= '<footer style="position:absolute;bottom:-195px;">';

            $header .= '<div style="border:1px solid #1367A4;width:50%;height:140px; padding: 8px 0px 5px 4px; border-radius: 6px;float:left;margin-top:5px;display:none"><span style="color:rgb(48, 37, 133)"><b>*MAXIMUM CHANGE ACCEPTED=&#163;25</b></span><div style=" font-size: 10px;">All goods remain the property of ' . $company['cname'] . ' 
            until all monies are paid in full<br>Any complaints should be made within 48 hours of purchasing.</div><br><div style="width:100%"><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left">Print Name:</div><div style="border:1px solid black;width:10%;height:18px;float:left;margin-left:10px;;margin-left:-78px;"></div><div style="float:left;margin-left:-158px;margin-top:3px;font-size: 12px;"><b>Cash Tendered</b></div><div style="clear:both"></div><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left;">Signature:</div><div style="font-size: 17px;padding-left:10px;border:1px solid black;width:35%;height:20px;float:left;padding-top:3px;padding-bottom:3px;margin-left:-157px;margin-top:-8px">&#163;</div><div style="clear:both"></div></div><div style="clear:both"></div></div>';

            $header .= '<div style="float:right;margin-left:320px;width:280px" ><table class="maintable2" style="width:100%; border:1px solid #1367A4;font-size: 14px;">';

            $header .= '<tr><td style="width: 60%;  background: #f1f4fb;padding:  4px 0px 4px 7px;">&nbsp;Total NET Amount</td>
            <td> ' . amountExchange($data["invoice"]["subtotal"]) . ' </td></tr><tr ><td style="width: 60%;  background: #f1f4fb;padding: 4px 0px 4px 7px;">&nbsp;Total ' . $this->lang->line('Tax') . ' Amount</td>
            <td> ' . amountExchange($data["invoice"]["tax"]) . '  </td>
            </tr><tr><td style="width: 60%;  background: #f1f4fb;padding: 4px 0px 4px 7px;">&nbsp;Invoice Total</td><td> ' . amountExchange($data["invoice"]["total"]) . '   </td></tr>';

            $header .= '</table></div><div style="clear:both"></div>';
            $header .= '<p style="font-size:8px;display:none"> ' . $company['cname'] . ' is the trading name of ' . $company['cname'] . ', Company No. 11671004. &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Bank Account Details: Sort Code: ' . $company['sortcode'] . ' A/C No: ' . $company['taxid'] . '</p>';

            $header .= ' </footer></body></html>';
        }
        $file_name = $tid;
        $this->dpdf->loadHtml($header);
        $this->dpdf->render();
        $output =  $this->dpdf->output();
        $file_location = 'userfiles/invoices/quote_' . $file_name . '.pdf';
        file_put_contents($file_location, $output);
        if ($printing) {
            $this->dpdf->stream("" . $file_name . ".pdf", array("Attachment" => 0));
        }
    }
    public function counter_invoice($printing = true, $id = -1)
    {
        $company = $this->settings->company_details(1);

        $productCounter = 0;
        $productsPerPage = 18;

        $hide_balance = 0;
        $tid = $id;
        if ($tid == -1) {
            $tid = $this->input->get('id');
            $hide_balance = ($this->input->get('hide_balance') == 1);
        }
        $data['id'] = $tid;
        $data['invoice'] = $this->invocies->invoice_details($tid, $this->limited);
        $data['products'] = $this->invocies->invoice_products($tid);
        $data['employee'] = $this->invocies->employee($data['invoice']['eid']);
        if ($data['invoice']['i_class'] == 1) {
            $pref = prefix(7);
        } else {
            $pref = $this->config->item('prefix');
        }
        ob_end_clean();
        ob_start();

        $sql = 'SELECT sum(total)-sum(pamnt) as "cust_balance" FROM geopos_invoices WHERE status="due" and csd="' . $data["invoice"]["csd"] . '" or status="partial" and csd="' . $data["invoice"]["csd"] . '"';
        $query = $this->db->query($sql);
        $response = $query->result_array();
        $cust_balance_inv = $response[0]['cust_balance'];

        // $cust_balance_inv= $response[0]['cust_balance'];
        $sql2 = "SELECT content FROM `geopos_notes` ORDER BY `geopos_notes`.`id`  DESC LIMIT 1";
        $query2 = $this->db->query($sql2);
        $response2 = $query2->result_array();
        $content2 = $response2[0]['content'];

        $sql2 = 'SELECT data FROM `geopos_custom_data` where rid="' . $data["invoice"]["csd"] . '"';
        $query2 = $this->db->query($sql2);
        $response2 = $query2->result_array();
        $custom_fields = $response2[0]['data'];

        if ($d == '1' || $d == 1) {
            $sql = 'Update geopos_invoices set print_status=1 where id= ' . $tid . ' ';
            $this->db->query($sql);
        }



        ini_set('memory_limit', '64M');

        $data_tr = "";

        $left_logo = base_url("userfiles/company/" . $company['logo']);
        $data['left_logo'] = $left_logo;
        $right_logo = base_url("assets/images/halal_logo.jpg");
        $data['right_logo'] = $right_logo;
        ob_end_clean();
        if ($data['invoice']['inv_type'] == 'INVOICE') {
            $header = '<html><head><style>@page { margin: 350px 50px 200px; } header{ position: fixed;  top: -340px;}
            table{width:100%;}.pagenum:after {content: counter(page)}</style> </head><body><header>';
            $header .= '<table style="width:100%;">
            <tr style="border:1px solid #1367A4;">';
            // $header .= '<td style="width:100%;"><img src="' . $left_logo . '" height="165px" width="270px"></td>';
            $header .= '<td style="width:100%;" height="165px">
                            <img src="' . $left_logo . '" height="145px" width="270px"><br>
                            <div style="font-size:18px; font-weight:bold; text-align:center;">
                                COUNTER INVOICE
                            </div>
                        </td>
            <td style="width:40%;"><img src="' . $right_logo . '" height="60px" width="60px"></td>
            <td style="width:100%;"><br><b>' . $company['cname'] . '</b><br>' . $company['address'] . ' ' . $company['city'] . ' '
                . $company['region'] . ', ' . $company['postbox'] . ', ' . $company['country'] . ' <br> Phone:&nbsp;&nbsp;&nbsp;' . $company['phone'] . ' <br> Mobile:&nbsp;&nbsp;' . $company['mobile'] . ' <br>
                <table style="border: 1px solid #1367A4;
                border-collapse: collapse;margin-left:-45px;margin-top:10px; border-radius: 6px;width:320px;">
                <tr style="">
                            <th  style="border: 1px solid #1367A4; width:80px;
                            border-collapse: collapse;font-size: 13px;text-align: center" >Date
                                </th>
                                <th  style="border: 1px solid #1367A4;
                                border-collapse: collapse;font-size: 13px;text-align: center">Invoice
                                </th>
                                <th  style="border: 1px solid #1367A4;
                                border-collapse: collapse;font-size: 13px;text-align: center">A/C
                                </th>
                                <th  style="border: 1px solid #1367A4;
                                border-collapse: collapse;font-size: 13px; text-align: center">EC NO 
                                </th>
                                <th  style="border: 1px solid #1367A4;
                                border-collapse: collapse;font-size: 13px; text-align: center">' . getTaxName() . ' Reg
                                </th>
                </tr>
                <tr >
                    <td style="width:30%;font-size: 13px;border: 1px solid #1367A4;border-collapse: collapse;text-align: center">
                    ' . date("d-m-Y", strtotime($data["invoice"]["invoicedate"])) . '
                    </td>
                    <td style="font-size: 13px;border: 1px solid #1367A4;border-collapse: collapse;text-align: center">' . $data['invoice']['tid'] . '</td>
                    <td style="font-size: 13px;border: 1px solid #1367A4;border-collapse: collapse;text-align: center">' . $data["invoice"]["name"] . '</td>
                    <td style="font-size: 13px;border: 1px solid #1367A4;border-collapse: collapse;text-align: center">882 /2004</td>
                    <td style="font-size: 13px;border: 1px solid #1367A4;border-collapse: collapse;text-align: center">299367533</td>
                </tr>
                </table>
            </td>
            </tr></table>';
        } else {
            $header = '<html><head><style>@page { margin: 350px 50px 200px; }header{ position: fixed;  top: -320px;}table{width:100%;}.pagenum:after {content: counter(page)}</style> </head><body><header><span style="float:right;"  class="pagenum">Page:</span>';
            $header .= ' <div style="width:100%; "><div style="width:23%; float:left; text-align:center;">  <br>
                </div></div><div><p style="text-align:center;height:84px;">Note: This is not a valid Invoice  </p>
                    Date: ' . date("d-m-Y", strtotime($data["invoice"]["invoicedate"])) . '
                </div><div style="clear:both"></div>';
        }

        $header .= '<div style="width:40%; float:left; margin-top:5px;">'; // START container
        // Top box: Company name
        $header .= '<div style="border:1px solid #1367A4; height:70px; padding: 4px 5px; border-radius: 6px; margin-bottom:5px;">';
        if ($data['invoice']['company']) {
            $header .= $data['invoice']['company'] . '<br>';
        }


        if ($data['invoice']['inv_type'] == 'INVOICE') {
            $header .= $data['invoice']['cust_address'] . ' ' . $data['invoice']['cust_city'] . '<br>' . $data['invoice']['cust_postcode'];
            $header .= '</div>';
            // Bottom box: Address + Terms
            $header .= '<div style="border:1px solid #1367A4; height:25px; padding: 4px 5px; border-radius: 6px; font-size: 13px;">';
            if ($data['invoice']['pamt_terms']) {
                $header .= '<b><span style="background-color:yellow">TERMS:</span></b> ' . $custom_fields;
            }
        } else {
            $header .= $data['invoice']['cust_address'] . ' ' . $data['invoice']['cust_city'] . '<br>' . $data['invoice']['cust_postcode'];
            $header .= '</div>';
            // Bottom box: Address + Terms
            $header .= '<div style="border:1px solid #1367A4; height:25px; padding: 4px 5px; border-radius: 6px; font-size: 13px;">';
            if ($data['invoice']['pamt_terms']) {
                $header .= '<b><span style="background-color:yellow">TERMS:</span></b> ' . $custom_fields;
            }
        }
        $header .= '</div></div><div style="float:right;margin-left:-150px;margin-top:-30px;width:50%;" ><div style=" font-size: 25px;">';

        if ($data['invoice']['inv_type'] == 'INVOICE') {
            $header .= '<br> ';
            $header .= '</div><table style="height:5%;  font-size: 14px; border:1px solid #1367A4;border-radius: 6px;"> 
        <tr ><td ><b>' . $content2 . '</b></td>
        </tr>';
            $header .= '</table>';
        } else {
            $header .= '<span style="font-size: 32px; transform: translate(-78px, -38px)" >Day Pass</span>';
            if ($data['invoice']['driver_name'] != '' && $data['invoice']['vehicle_no'] != '') {
                $header .= '</div><table style="height:5%;  font-size: 14px; border:1px solid #1367A4;border-radius: 6px;"> 
            
            <tr ><td ><b>Driver Name: </b></td></tr>
            <tr ><td >' . $data['invoice']['driver_name'] . ' ' . $data['invoice']['vehicle_no'] . '</td>
            </tr>';
                $header .= '</table>';
            }
        }
        $header .= '</div></div></header>';
        $header .= '<div style="clear:both"></div> <main><div style="width:100%; margin-top:-17px; "><table width="100%" class="myProducts" style="border:1px solid #1367A4;border-radius: 6px;"><thead><tr style="font-size: 14px; background:#ccc; padding:4px;"><th style="width: 12%; text-align:left; ">' . $this->lang->line('Quantity') . '</th><th style="border-left: 1px solid #1367A4;width: 45%; text-align:left;">' . $this->lang->line('Details') . '</th><th style="width: 13%; text-align:left; border-left: 1px solid #1367A4;">' . $this->lang->line('Unit Price') . '</th><th style="width: 20%; text-align:left; border-left: 1px solid #1367A4;">' . $this->lang->line('Net Amount') . '</th><th style="width: 10%;  text-align:left;border-left: 1px solid #1367A4;">' . getTaxName() . '</th></tr></thead>';
        $header .= '<tbody style="font-size:15px;">';

        $ns = 0;
        foreach ($data['products'] as $row) {
            if ($row['qty'] > 0) {
                $productCounter++;
                $ns++;

                if ($productCounter % $productsPerPage === 0) {
                    if ($productCounter > 0) {
                        // Close the previous table if it's not the first page
                        $header .= '</tbody>';
                        $header .= '</table>';
                    }

                    // Start a new page
                    $header .= '<div style="page-break-after: always;"></div>';
                    $header .= '<table style="border:1px solid #1367A4;border-radius: 6px;">';
                    $header .= '<thead>';
                    $header .= '<tr style="font-size: 14px; background: #ccc; padding: 4px;">';
                    $header .= '<th style="width: 12%; text-align: left;">' . $this->lang->line('Quantity') . '</th>';
                    $header .= '<th style="border-left: 1px solid #1367A4; width: 45%; text-align: left;">' . $this->lang->line('Details') . '</th>';
                    $header .= '<th style="width: 13%; text-align: left; border-left: 1px solid #1367A4;">' . $this->lang->line('Unit Price') . '</th>';
                    $header .= '<th style="width: 20%; text-align: left; border-left: 1px solid #1367A4;">' . $this->lang->line('Net Amount') . '</th>';
                    $header .= '<th style="width: 10%; text-align: left; border-left: 1px solid #1367A4;">' . getTaxName() . '</th>';
                    $header .= '</tr>';
                    $header .= '</thead>';
                    $header .= '<tbody>';
                }
                if (empty($row['product_des']) || $row['product_des'] == "") {
                } else {
                    if ($row['serial'] == '1') {
                        $header .= '<tr style="background:#FBF36D; style="font-size:18px;"">';
                    } else {
                        $header .= '<tr ' . (($ns % 2 == 0) ? 'style=" background: #f1f4fb;"' : '') . '>';
                    }
                    $header .= '<td style=" border-bottom:1px solid white;">' . (int)$row["qty"] .  '</td style=" border-bottom:1px solid white;">
                    <td style=" border-bottom:1px solid white;">' . $row["product_des"] . '</td>
                    <td style=" border-bottom:1px solid white;">' . rev_amountExchange_s($row['price'], $data['invoice']['multi'], $data['invoice']['loc']) . '</td>
                     <td style=" border-bottom:1px solid white;">' . rev_amountExchange_s($row['subtotal'], $data['invoice']['multi'], $data['invoice']['loc']) . '</td>
                     <td style=" border-bottom:1px solid white;">' . rev_amountExchange_s($row['totaltax'], $data['invoice']['multi'], $data['invoice']['loc']) . ' </td></tr>';
                }
            }
        }
        $header .= '</tbody></table></div></main>';
        // Use database values instead of calculating totals

        if ($data['invoice']['inv_type'] == 'INVOICE') {

            $header .= '<footer style="position:absolute;bottom:-195px;">';
            // if ($data['invoice']['inv_type'] == 'INVOICE') {
            //     // Bank Transfer Info Box (inline layout)
            //     $header .= '<table style="width:100%; margin-bottom:5px;"><tr>';
            //     $header .= '<td style="width:60%; border:1px solid #1367A4; border-radius:6px; padding:5px;">';
            //     $header .= '<b>For Bank Transfer:&nbsp;&nbsp; ' . $company['cname'] . '<br>';
            //     $header .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            //     S/C: ' . $company['sortcode'] . '&nbsp;&nbsp;&nbsp; AC: ' . $company['taxid'] . '</b></td>';
            //     $header .= '<td style="width:40%;"><b>&nbsp;&nbsp;&nbsp;&nbsp;Customer Signature:</b>_______________</td>';
            //     $header .= '</tr></table>';
            // }

            if ($data['invoice']['inv_type'] == 'INVOICE') {
                $header .= '<div style="border:1px solid #1367A4;width:59%;height:170px; padding: 8px 0px 5px 4px; border-radius: 6px;float:left;"><span style="color:rgb(48, 37, 133)"><b>*MAXIMUM CHANGE ACCEPTED=&#163;40</b></span><div style=" font-size: 14px; margin-bottom: 6px;">
            *All claims must be notified at the time of delivery.<br>
            *Any claims made after delivery will not be considered.<br>
            *Frozen and chilled products cannot be returned once delivered.<br>
            *Goods will remain the property of ' . $company['cname'] . ' until paid in full by the customer.
            </div><div style="width:100%"><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left">Customer:</div><div style="border:1px solid black;width:8%;height:18px;float:left;margin-top:10px;margin-left:-178px;"></div><div style="border:1px solid black;width:8%;height:18px;float:left;margin-top:10px;margin-left:-58px;"></div><div style="border:1px solid black;width:8%;height:18px;float:left;margin-top:10px;margin-left:-118px;"></div><div style="float:left;margin-left:-178px;margin-top:-5px;font-size: 12px;"><b>Cash &nbsp;&nbsp; Card Payment &nbsp;&nbsp;BACS</b></div><div style="clear:both"></div><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left;">Staff Name:</div><div style="font-size: 17px;padding-left:10px;border:1px solid black;width:35%;height:20px;float:left;padding-top:3px;padding-bottom:3px;margin-left:-187px;margin-top:3px">&#163;</div><div style="clear:both"></div></div><div style="clear:both"></div></div>';
            }

            $header .= '<div style="float:right;margin-left:320px;width:280px" ><table class="maintable2" style="width:100%;border-radius: 6px; border:1px solid #1367A4;font-size: 14px;">';

            if ($data['invoice']['inv_type'] == 'INVOICE') {
                if (!$hide_balance) {
                }
            }

            $header .= '<tr><td style="width: 60%;  background: #f1f4fb;padding:  4px 0px 4px 7px;">&nbsp;Total NET Amount</td><td> ' . amountExchange($data["invoice"]["subtotal"], $data['invoice']['multi'], $data['invoice']['loc']) . ' </td></tr>
            <tr ><td style="width: 60%;  background: #f1f4fb;padding: 4px 0px 4px 7px;">&nbsp;Total ' . $this->lang->line('Tax') . ' Amount</td><td> ' . amountExchange($data["invoice"]["tax"], $data['invoice']['multi'], $data['invoice']['loc']) . '  </td></tr>
            <tr><td style="width: 60%;  background: #f1f4fb;padding: 4px 0px 4px 7px;">&nbsp;Invoice Total</td><td> ' . amountExchange($data["invoice"]["total"], $data['invoice']['multi'], $data['invoice']['loc']) . '   </td></tr>';


            if ($data['invoice']['inv_type'] == 'INVOICE' || $data['invoice']['inv_type'] == 'DAYPASS') {
                if (!$hide_balance) {
                    $header .= '<tr ><td style="width: 50%;  background: #f1f4fb;padding: 4px 0px 4px 7px;">&nbsp;Previous Balance</td><td>';
                    $invbalance = 0;
                    if ($cust_balance_inv >= $data['invoice']['total']) {
                        $invbalance =  $cust_balance_inv - $data['invoice']['total'];
                        $header .=  amountExchange($invbalance, $data['invoice']['multi'], $data['invoice']['loc']);
                        // $header .=  substr(amountExchange($invbalance, $data['invoice']['multi'], $data['invoice']['loc']), 3, 100);
                    } else {
                        $header .=  amountExchange($cust_balance_inv, $data['invoice']['multi'], $data['invoice']['loc']);
                        // $header .=  substr(amountExchange($cust_balance_inv, $data['invoice']['multi'], $data['invoice']['loc']), 3, 100);
                    }
                    $header .= '</td></tr>';
                    $header .= ' <tr><td style="width: 60%; background: #f1f4fb;padding:  4px 0px 4px 7px;">&nbsp;Balance Now Due</td><td>' . amountExchange($cust_balance_inv, $data['invoice']['multi'], $data['invoice']['loc']) . ' </td></tr> ';
                }
            }
            $header .= '</table>';
            if ($data['invoice']['inv_type'] == 'INVOICE') {
                // Bank Transfer Info Box (inline layout)
                $header .= '<div style="width:100%; margin-top:10px; font-size:12px;">';
                $header .= '<table style="width:100%;border:1px solid #1367A4;  border-radius:6px;"><tr>';
                $header .= '<td style="width:40%;"><b>For Bank Transfer:</b></td>';
                $header .= '<td style="width:60%;"><b>' . $company['cname'] . '<br>S/C: ' . $company['sortcode'] . '&nbsp;&nbsp;&nbsp; AC: ' . $company['taxid'] . '</b></td>';
                $header .= '</tr></table>';
                $header .= '</div></div>';
            }
            // $header .= '<span href="mailto:' . $company['email'] . '"><img src="' . base_url("assets/images/mail.jpg") . '" height="32px" width="38px" style="margin-top:10px;">  </span>' . $company['email'] . '<br>
            $header .= '
            <!--<span href="https://www.cloudbillingmanager.com/"><img src="' . base_url("assets/images/web.jpg") . '" height="32px" width="38px" style="margin-top:10px;"> </span>www.cloudbillingmanager.com -->
            </div><div style="clear:both">  
            <span href="mailto:' . $company['email'] . '"><img src="' . base_url("assets/images/mail.jpg") . '" height="32px" width="38px" style="margin-top:10px;">  </span>' . $company['email'] . '
            <span style="text-align: right; href="https://www.5starfoodsltd.com/"><img src="' . base_url("assets/images/web.jpg") . '" height="32px" width="38px" style="margin-top:10px;">http://www.5starfoodsltd.com/</span>
            &nbsp;&nbsp;&nbsp;&nbsp;<span>Thank you for your business!</span>';
            // </div>';
            // $header .= '</table>
            // <span href="mailto:' . $company['email'] . '"><img src="' . base_url("assets/images/mail.jpg") . '" height="32px" width="38px" style="margin-top:10px;">  </span>' . $company['email'] . '<br>
            // <!--<span href="https://www.cloudbillingmanager.com/"><img src="' . base_url("assets/images/web.jpg") . '" height="32px" width="38px" style="margin-top:10px;"> </span>www.cloudbillingmanager.com -->
            // </div><div style="clear:both">
            // <span style="text-align: left; margin-left:70px;">Thank you for your business!</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            // &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;    
            // <span style="text-align: right; margin-left:270px;  href="https://www.5starfoodsltd.com/"><img src="' . base_url("assets/images/web.jpg") . '" height="32px" width="38px" style="margin-top:10px;">www.cloudbillingmanager.com </span>
            // </div>';

            if ($data['invoice']['inv_type'] == 'INVOICE') {
            }
            $header .= ' </footer></body></html>';
        } else {
            $header .= '<footer style="position:absolute;bottom:-195px;">';

            $header .= '<div style="border:1px solid #1367A4;width:50%;height:140px; padding: 8px 0px 5px 4px; border-radius: 6px;float:left;margin-top:5px;display:none"><span style="color:rgb(48, 37, 133)"><b>*MAXIMUM CHANGE ACCEPTED=&#163;25</b></span><div style=" font-size: 10px;">All goods remain the property of ' . $company['cname'] . ' 
            until all monies are paid in full<br>Any complaints should be made within 48 hours of purchasing.</div><br><div style="width:100%"><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left">Print Name:</div><div style="border:1px solid black;width:10%;height:18px;float:left;margin-left:10px;;margin-left:-78px;"></div><div style="float:left;margin-left:-158px;margin-top:3px;font-size: 12px;"><b>Cash Tendered</b></div><div style="clear:both"></div><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left;">Signature:</div><div style="font-size: 17px;padding-left:10px;border:1px solid black;width:35%;height:20px;float:left;padding-top:3px;padding-bottom:3px;margin-left:-157px;margin-top:-8px">&#163;</div><div style="clear:both"></div></div><div style="clear:both"></div></div>';

            $header .= '<div style="float:right;margin-left:320px;width:280px" ><table class="maintable2" style="width:100%; border:1px solid #1367A4;font-size: 14px;">';

            $header .= '<tr><td style="width: 60%;  background: #f1f4fb;padding:  4px 0px 4px 7px;">&nbsp;Total NET Amount</td>
            <td> ' . amountExchange($data["invoice"]["subtotal"]) . ' </td></tr><tr ><td style="width: 60%;  background: #f1f4fb;padding: 4px 0px 4px 7px;">&nbsp;Total ' . $this->lang->line('Tax') . ' Amount</td>
            <td> ' . amountExchange($data["invoice"]["tax"]) . '  </td>
            </tr><tr><td style="width: 60%;  background: #f1f4fb;padding: 4px 0px 4px 7px;">&nbsp;Invoice Total</td><td> ' . amountExchange($data["invoice"]["total"]) . '   </td></tr>';

            $header .= '</table></div><div style="clear:both"></div>';
            $header .= '<p style="font-size:8px;display:none"> ' . $company['cname'] . ' is the trading name of ' . $company['cname'] . ', Company No. 11671004. &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Bank Account Details: Sort Code: ' . $company['sortcode'] . ' A/C No: ' . $company['taxid'] . '</p>';

            $header .= ' </footer></body></html>';
        }
        $file_name = $tid;
        $this->dpdf->loadHtml($header);
        $this->dpdf->render();
        $output =  $this->dpdf->output();
        $file_location = 'userfiles/invoices/counter_' . $file_name . '.pdf';
        file_put_contents($file_location, $output);
        if ($printing) {
            $this->dpdf->stream("" . $file_name . ".pdf", array("Attachment" => 0));
        }
    }


    public function previewInvoice()
    {

        $company = $this->settings->company_details(1);

        $tid = $this->input->get('id');
        $data['id'] = $tid;
        $data['invoice'] = $this->invocies->invoice_details($tid, $this->limited);
        if ($data['invoice']['id']) $data['products'] = $this->invocies->invoice_products($tid);
        if ($data['invoice']['id']) $data['employee'] = $this->invocies->employee($data['invoice']['eid']);
        if ($data['invoice']['i_class'] == 1) {
            $pref = prefix(7);
        } else {
            $pref = $this->config->item('prefix');
        }

        ob_end_clean();
        ob_start();

        $sql = 'SELECT sum(total)-sum(pamnt) as "cust_balance" FROM geopos_invoices WHERE status="due" and csd="' . $data["invoice"]["csd"] . '" or status="partial" and csd="' . $data["invoice"]["csd"] . '"';
        $query = $this->db->query($sql);
        $response = $query->result_array();
        $cust_balance_inv = $response[0]['cust_balance'];
        $sql2 = "SELECT content FROM `geopos_notes` ORDER BY `geopos_notes`.`id`  DESC LIMIT 1";
        $query2 = $this->db->query($sql2);
        $response2 = $query2->result_array();
        $content2 = $response2[0]['content'];

        if ($d == '1' || $d == 1) {
            $sql = 'Update geopos_invoices set print_status=1 where id= ' . $tid . ' ';
            $this->db->query($sql);
        }

        $sql = "select name, address,lane,city,postcode,phone,mobile, sc, ac,vat,company_logo,halal_logo,email,website,invoice_note, invoice, daypass  from geopos_company";

        $query = $this->db->query($sql);
        $response = $query->result_array();
        $companyname = $response[0]['name'];
        $address = $response[0]['address'];
        $lane = $response[0]['lane'];
        $city = $response[0]['city'];
        $postcode = $response[0]['postcode'];
        $telephone = $response[0]['phone'];
        $mobile = $response[0]['mobile'];
        $sc = $response[0]['sc'];
        $ac = $response[0]['ac'];
        $vat = $response[0]['vat'];
        $company_logo = $response[0]['company_logo'];
        $halal_logo = $response[0]['halal_logo'];
        $email = $response[0]['email'];
        $website = $response[0]['website'];
        $invoice_note21 = $response[0]['invoice_note'];
        $invoice_template_id = $response[0]['invoice'];
        $daypass_template_id = $response[0]['daypass'];


        $sql = "select company_logo, halal_logo, header_invoice,header_daypass,company_address_box,company_address_box_end,
         invoice_note,bank_transfer_details,invoice_detail_header,footer_left,footer_right from geopos_invoices_template where id=" . $invoice_template_id;

        $query = $this->db->query($sql);
        $response = $query->result_array();
        $header = $response[0]['header_invoice'];
        $header_daypass = $response[0]['header_daypass'];
        $company_address_box = $response[0]['company_address_box'];
        $company_address_box_end = $response[0]['company_address_box_end'];
        $invoice_note = $response[0]['invoice_note'];
        $bank_transfer_details = $response[0]['bank_transfer_details'];
        $invoice_detail_header = $response[0]['invoice_detail_header'];
        $footer_left = $response[0]['footer_left'];
        $footer_right = $response[0]['footer_right'];
        $invoicedate = date("d-m-Y", strtotime($data["invoice"]["invoicedate"]));
        $invoicename = $data["invoice"]["name"];
        $invoiceid = $data['invoice']['tid'];



        $addrr = $city . ' , ' . $postcode;

        ini_set('memory_limit', '64M');

        $data_tr = "";
        $data['invoice']['inv_type'] = 'INVOICE';
        ob_end_clean();
        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            $html_string = htmlspecialchars_decode($header);
            $html_string = str_replace("halal_logo", $halal_logo, $html_string);
            $html_string = str_replace("invoicedate", $invoicedate, $html_string);
            $html_string = str_replace("invoiceid", $invoiceid, $html_string);
            $html_string = str_replace("invoicename", $invoicename, $html_string);
            $html_string = str_replace("company_logo", $company_logo, $html_string);
            $html_string = str_replace("companyname", $companyname, $html_string);
            $html_string = str_replace("address", $address, $html_string);
            $html_string = str_replace("lane", $lane, $html_string);
            $html_string = str_replace("city, postcode", $addrr, $html_string);
            $html_string = str_replace("telephone", $telephone, $html_string);
            $html_string = str_replace("mobile", $mobile, $html_string);
            $html_string = str_replace("vat", $vat, $html_string);
            $header = $html_string;
        } else {
            $header = $header_daypass;
        }

        $header .= $company_address_box;
        if ($data['invoice']['company']) {
            $header .= $data['invoice']['company'] . '<br>';
        }

        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            $header .= 'Customer Address <br/>Customer City <br/>Customer Postcode';
        } else {
            $header .= 'Customer Address <br/>Customer City <br/>Customer Postcode';
        }


        $header .= $company_address_box_end;



        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {

            $invoice_note2 = htmlspecialchars_decode($invoice_note);
            $invoice_note3 = str_replace("content2", $invoice_note21, $invoice_note2);
            $header .=  $invoice_note3;
            $this->aauth->applog("[Invoice Viewed ] - $tid", $this->aauth->get_user()->username);
        } else {
            $header .= '&nbsp;';

            $header .= ' Day Pass ';
            $this->aauth->applog("[Day Pass Viewed ] - $tid", $this->aauth->get_user()->username);
        }

        $html_string = htmlspecialchars_decode($bank_transfer_details);
        $html_string = str_replace("companyname", $companyname, $html_string);
        $html_string = str_replace("sc", $sc, $html_string);
        $html_string = str_replace("ac", $ac, $html_string);

        $header .= $html_string;
        $header .= $invoice_detail_header;

        $ns = 0;
        $sub_t = 0;

        $header .= '</tbody></table></div></main>';


        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {

            $header .= '<footer style="position:absolute;bottom:-195px">';
            if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {

                $html_string = htmlspecialchars_decode($footer_left);
                $html_string = str_replace("companyname", $companyname, $html_string);

                $header .= $footer_left;
            }

            $sub_ts = 0;
            foreach ($data['products'] as $row) {
                // Removed calculation - using database value instead
            }

            $header .= '<div style="float:right;margin-left:320px;width:280px" ><table class="maintable2" style="width:100%;border-radius: 6px; border:1px solid #1367A4;font-size: 14px;">';

            if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
                $header .= '<tr ><td style="width: 50%;  background: lightgrey;padding: 4px 0px 4px 7px;">&nbsp;Previous Balance</td><td>';
                $invbalance = 0;
                if ($cust_balance_inv >= $data['invoice']['total']) {
                    $invbalance =  $cust_balance_inv - $data['invoice']['total'];
                    $header .=  '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;0.00';
                } else {
                    $header .=  '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;0.00';
                }
                $header .= '</td></tr>';
            }


            $header .= '<tr><td style="width: 60%;  background: lightgrey;padding:  4px 0px 4px 7px;">&nbsp;Total NET Amount</td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 0.00  </td></tr><tr ><td style="width: 60%;  background: lightgrey;padding: 4px 0px 4px 7px;">&nbsp;Total ' . $this->lang->line('Tax') . ' Amount</td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 0.00 </td></tr><tr><td style="width: 60%;  background: #f1f4fb;padding: 4px 0px 4px 7px;">&nbsp;Invoice Total</td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 0.00  </td></tr>';
            if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {

                $header .= ' <tr><td style="width: 60%; background: lightgrey;padding:  4px 0px 4px 7px;">&nbsp;Balance Now Due</td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;0.00 </td></tr></table>';
            }

            $webimage = base_url("assets/images/web.jpg");
            $mailimage = base_url("assets/images/mail.jpg");
            $html_string = htmlspecialchars_decode($footer_right);
            $html_string = str_replace("mailimage", $mailimage, $html_string);
            $html_string = str_replace("webimage", $webimage, $html_string);
            $html_string = str_replace("email", $email, $html_string);
            $html_string = str_replace("website", $website, $html_string);
            $header .= $html_string;
            if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            }
            $header .= ' </footer></body></html>';
        } else {


            $header .= '<footer style="position:absolute;bottom:-195px">';
            $header .= '<div style="border:1px solid #1367A4;width:50%;height:140px; padding: 8px 0px 5px 4px; border-radius: 6px;float:left;margin-top:5px;display:none"><span style="color:rgb(48, 37, 133)"><b>*MAXIMUM CHANGE ACCEPTED=&#163;25</b></span><div style=" font-size: 10px;">All goods remain the property of ' . $company['cname'] . ' 
        until all monies are paid in full<br>Any complaints should be made within 48 hours of purchasing.</div><br><div style="width:100%"><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left">Print Name:</div><div style="border:1px solid black;width:10%;height:18px;float:left;margin-left:10px;;margin-left:-78px;"></div><div style="float:left;margin-left:-158px;margin-top:3px;font-size: 12px;"><b>Cash Tendered</b></div><div style="clear:both"></div><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left;">Signature:</div><div style="font-size: 17px;padding-left:10px;border:1px solid black;width:35%;height:20px;float:left;padding-top:3px;padding-bottom:3px;margin-left:-157px;margin-top:-8px">&#163;</div><div style="clear:both"></div></div><div style="clear:both"></div></div>';
            $sub_ts = 0;
            foreach ($data['products'] as $row) {
                // Removed calculation - using database value instead
            }

            $header .= '<div style="float:right;margin-left:320px;width:280px" ><table class="maintable2" style="width:100%; border:1px solid #1367A4;font-size: 14px;">';


            $inv_total_amt = $data["invoice"]["total"] + $data["invoice"]["tax"];
            $sub_ts = formatDecimal($data["invoice"]["subtotal"]);
            $header .= '<tr><td style="width: 60%;  background: lightgrey;padding:  4px 0px 4px 7px;">&nbsp;Total NET Amount</td><td> ' . $sub_ts . ' </td></tr><tr ><td style="width: 60%;  background: lightgrey;padding: 4px 0px 4px 7px;">&nbsp;Total ' . $this->lang->line('Tax') . ' Amount</td><td> ' . formatDecimal($data["invoice"]["tax"]) . '  </td></tr><tr><td style="width: 60%;  background: lightgrey;padding: 4px 0px 4px 7px;">&nbsp;Invoice Total</td><td> ' . formatDecimal($inv_total_amt) . '   </td></tr>';

            $header .= '</table></div><div style="clear:both"></div>';


            $header .= ' </footer></body></html>';
        }

        $file_name = $this->input->get('id');
        $this->dpdf->loadHtml($header);
        $this->dpdf->render();
        $output =  $this->dpdf->output();
        $file_location = 'userfiles/invoices/' . $file_name . '.pdf';
        file_put_contents($file_location, $output);
        $this->dpdf->stream("" . $file_name . ".pdf", array("Attachment" => 0, "I" => true));
    }


    public function create_credit_note()
    {

        $data['emp'] = $this->plugins->universal_api(69);
        if ($data['emp']['key1']) {
            $this->load->model('employee_model', 'employee');
            $data['employee'] = $this->employee->list_employee();
        }

        $this->load->library("Common");
        $data['custom_fields_c'] = $this->custom->add_fields(1);

        $this->load->model('customers_model', 'customers');
        $this->load->model('plugins_model', 'plugins');

        $data['exchange'] = $this->plugins->universal_api(5);
        $data['customergrouplist'] = $this->customers->group_list();
        $data['lastinvoice'] = $this->stockreturn->lastcredit();
        $data['warehouse'] = $this->invocies->warehouses();
        $data['terms'] = $this->invocies->billingterms();
        // COMMENTED OUT: Multi-currency support
        // $data['currency'] = $this->invocies->currencies();
        // NEW: Get currency from geopos_system
        $query = $this->db->query("SELECT currency FROM geopos_system WHERE id=1 LIMIT 1");
        $row = $query->row_array();
        $currency = strtoupper($row['currency']);
        // Return as array format for compatibility with views that expect array
        $data['currency'] = array(array('symbol' => $currency, 'code' => $currency, 'id' => 0));

        $this->load->library("Common");
        $data['taxlist'] = $this->common->taxlist($this->config->item('tax'));
        $head['title'] = "New Invoice";
        $head['usernm'] = $this->aauth->get_user()->username;
        $data['taxdetails'] = $this->common->taxdetail();
        $data['custom_fields'] = $this->custom->add_fields(2);

        $this->load->view('fixed/header', $head);
        $this->load->view('stockreturn/newCrdeitnote', $data);
        $this->load->view('fixed/footer');
    }

    //create invoice
    public function create()
    {
        // Debugbar test
        if (ENVIRONMENT === 'development') {
            $this->load->helper('debugbar');
            debug('Invoice create method started');
        }

        $company = $this->settings->company_details(1);
        $data['emp'] = $this->plugins->universal_api(69);
        if ($data['emp']['key1']) {
            $this->load->model('employee_model', 'employee');
            $data['employee'] = $this->employee->list_employee();
        }

        $this->load->library("Common");
        $data['custom_fields_c'] = $this->custom->add_fields(1);

        $this->load->model('customers_model', 'customers');
        $this->load->model('plugins_model', 'plugins');

        $data['exchange'] = $this->plugins->universal_api(5);
        $data['customergrouplist'] = $this->customers->group_list();
        $data['lastinvoice'] = $this->invocies->lastinvoice();
        $data['warehouse'] = $this->invocies->warehouses();
        $data['terms'] = $this->invocies->billingterms();
        // COMMENTED OUT: Multi-currency support
        // $data['currency'] = $this->invocies->currencies();
        // NEW: Get currency from geopos_system
        $query = $this->db->query("SELECT currency FROM geopos_system WHERE id=1 LIMIT 1");
        $row = $query->row_array();
        $currency = strtoupper($row['currency']);
        // Return as array format for compatibility with views that expect array
        $data['currency'] = array(array('symbol' => $currency, 'code' => $currency, 'id' => 0));
        $this->load->library("Common");
        $data['taxlist'] = $this->common->taxlist($this->config->item('tax'));
        $head['title'] = "New Invoice";
        $head['usernm'] = $this->aauth->get_user()->username;
        $data['taxdetails'] = $this->common->taxdetail();
        $data['custom_fields'] = $this->custom->add_fields(2);
        $data['show_profit_per'] = $this->settings->show_profit_per();
        $data['auto_post'] = $this->settings->auto_post();
        $data['invoice_format'] = $company['invoice_format'];
        // var_dump($data['invoice_format']); die();
        // dd($data);
        $this->load->view('fixed/header', $head);
        $this->load->view('invoices/newinvoice', $data);
        $this->load->view('fixed/footer');
    }

    //edit invoice
    public function edit()
    {
        $company = $this->settings->company_details(1);
        $data['auto_post'] = $this->settings->auto_post();
        $tid = intval($this->input->get('id'));
        $data['id'] = $tid;
        $data['title'] = "Edit Invoice $tid";
        $this->load->model('customers_model', 'customers');
        $data['customergrouplist'] = $this->customers->group_list();
        $data['terms'] = $this->invocies->billingterms();
        // COMMENTED OUT: Multi-currency support
        // $data['currency'] = $this->invocies->currencies();
        // NEW: Get currency from geopos_system
        $query = $this->db->query("SELECT currency FROM geopos_system WHERE id=1 LIMIT 1");
        $row = $query->row_array();
        $currency = strtoupper($row['currency']);
        // Return as array format for compatibility with views that expect array
        $data['currency'] = array(array('symbol' => $currency, 'code' => $currency, 'id' => 0));
        $data['invoice'] = $this->invocies->invoice_details($tid, $this->limited);
        if ($data['invoice']['id']) $data['products'] = $this->invocies->items_with_product($tid);

        //echo '<pre>'; print_r($data['products']); exit;

        $head['title'] = "Edit Invoice #$tid";
        $head['usernm'] = $this->aauth->get_user()->username;
        $data['warehouse'] = $this->invocies->warehouses();
        $this->load->model('plugins_model', 'plugins');
        $data['exchange'] = $this->plugins->universal_api(5);
        $this->load->library("Common");
        //$data['taxlist'] = $this->common->taxlist_edit($data['invoice']['taxstatus']);
        $data['taxlist'] = $this->common->taxlist($this->config->item('tax'));

        $this->load->library("Common");
        $data['custom_fields_c'] = $this->custom->add_fields(1);
        $data['custom_fields'] = $this->custom->add_fields(2);
        $data['invoice_format'] = $company['invoice_format'];
        $this->aauth->applog("[" . ($data['invoice']['inv_type'] == "DAYPASS" ? "Day Pass" : 'Invoice') . " Previewed - #" . $data['invoice']['tid'] . "] - Total: " . $data['invoice']['total'] . ", Tax: " . $data['invoice']['tax'] . ", Products: " . count($data['products']), $this->aauth->get_user()->username);




        $this->load->view('fixed/header', $head);
        if ($data['invoice']['id']) $this->load->view('invoices/edit', $data);
        $this->load->view('fixed/footer');
    }



    public function edit_bfr_posting()
    {
        $data['auto_post'] = $this->settings->auto_post();
        $tid = intval($this->input->get('id'));
        $data['id'] = $tid;
        $data['title'] = "Edit Invoice $tid";
        $this->load->model('customers_model', 'customers');
        $data['customergrouplist'] = $this->customers->group_list();
        $data['terms'] = $this->invocies->billingterms();
        // COMMENTED OUT: Multi-currency support
        // $data['currency'] = $this->invocies->currencies();
        // NEW: Get currency from geopos_system
        $query = $this->db->query("SELECT currency FROM geopos_system WHERE id=1 LIMIT 1");
        $row = $query->row_array();
        $currency = strtoupper($row['currency']);
        // Return as array format for compatibility with views that expect array
        $data['currency'] = array(array('symbol' => $currency, 'code' => $currency, 'id' => 0));
        $data['invoice'] = $this->invocies->invoice_details_bfr_post($tid, $this->limited);
        if ($data['invoice']['id']) $data['products'] = $this->invocies->items_with_product_bfr_post($tid);

        //echo '<pre>'; print_r($data['products']); exit;

        $head['title'] = "Edit Invoice #$tid";
        $head['usernm'] = $this->aauth->get_user()->username;
        $data['warehouse'] = $this->invocies->warehouses();
        $this->load->model('plugins_model', 'plugins');
        $data['exchange'] = $this->plugins->universal_api(5);
        $this->load->library("Common");
        //$data['taxlist'] = $this->common->taxlist_edit($data['invoice']['taxstatus']);
        $data['taxlist'] = $this->common->taxlist($this->config->item('tax'));

        $this->load->library("Common");
        $data['custom_fields_c'] = $this->custom->add_fields(1);
        $data['custom_fields'] = $this->custom->add_fields(2);

        $this->load->view('fixed/header', $head);
        if ($data['invoice']['id']) $this->load->view('invoices/edit', $data);
        $this->load->view('fixed/footer');
    }





    public function edit_invoices()
    {
        $tid = intval($this->input->get('id'));
        $data['id'] = $tid;
        $data['title'] = "Credit Invoice $tid";
        $this->load->model('customers_model', 'customers');
        $data['customergrouplist'] = $this->customers->group_list();
        $data['terms'] = $this->invocies->billingterms();
        // COMMENTED OUT: Multi-currency support
        // $data['currency'] = $this->invocies->currencies();
        // NEW: Get currency from geopos_system
        $query = $this->db->query("SELECT currency FROM geopos_system WHERE id=1 LIMIT 1");
        $row = $query->row_array();
        $currency = strtoupper($row['currency']);
        // Return as array format for compatibility with views that expect array
        $data['currency'] = array(array('symbol' => $currency, 'code' => $currency, 'id' => 0));
        $data['invoice'] = $this->invocies->invoice_details_for_credit($tid, $this->limited);
        if ($data['invoice']['id']) $data['products'] = $this->invocies->items_with_product_for_credit($tid);
        $head['title'] = "Credit Invoice #$tid";
        $head['usernm'] = $this->aauth->get_user()->username;
        $data['warehouse'] = $this->invocies->warehouses();
        $this->load->model('plugins_model', 'plugins');
        $data['exchange'] = $this->plugins->universal_api(5);
        $this->load->library("Common");
        $data['taxlist'] = $this->common->taxlist($this->config->item('tax'));
        $this->load->library("Common");
        $data['custom_fields_c'] = $this->custom->add_fields(1);
        $data['custom_fields'] = $this->custom->add_fields(2);
        $this->load->view('fixed/header', $head);
        if ($data['invoice']['id']) $this->load->view('invoices/stock_return', $data);
        $this->load->view('fixed/footer');
    }





    //invoices list
    public function index($filter = "")
    {
        // Fetch 'param' from the query string
        $param = $this->input->get('param');
        $due_date = $this->input->get('due_date');
        $company = $this->settings->company_details(1);
        $head['title'] = "Manage Invoices";
        $head['usernm'] = $this->aauth->get_user()->username;

        // Pass the 'param' to the view
        $data['param'] = $param;
        $data['due_date'] = $due_date;
        $data['invoice_format'] = $company['invoice_format'];

        $this->load->view('fixed/header', $head);
        $this->load->view('invoices/invoices', $data);
        $this->load->view('fixed/footer');
    }


    public function unposted_invoices($filter = "")
    {
        $head['title'] = "Manage Invoices";
        $head['usernm'] = $this->aauth->get_user()->username;

        $this->load->view('fixed/header', $head);
        $this->load->view('invoices/unpostedinvoices');
        $this->load->view('fixed/footer');
    }

    public function bulk_delete($filter = "")
    {
        $head['title'] = "Manage Invoices";
        $head['usernm'] = $this->aauth->get_user()->username;
        $head['bulk_delete'] = 1;
        $this->load->view('fixed/header', $head);
        $this->load->view('invoices/bulkdaypass_deletion');
        $this->load->view('fixed/footer');
    }
    public function print_invoices($filter = "")
    {

        $head['title'] = "Print Invoices";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $this->load->view('invoices/print-invoices');
        $this->load->view('fixed/footer');
    }


    //invoices Sale Report
    public function extended()
    {
        $head['title'] = "Sales By Product";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->db->select('geopos_product_cat.id, geopos_product_cat.title');
        $this->db->from('geopos_product_cat');

        $query = $this->db->get();
        $head['categories'] = $query->result_array();

        $this->load->view('fixed/header', $head);
        $this->load->view('invoices/invoicesale');
        $this->load->view('fixed/footer');
    }



    public function post_invoices()
    {
        $new_ids = array();
        $i = 0;
        $ids = $this->input->get('ids');
        // dd($ids);
        $ids = array_values(array_unique($ids));

        foreach ($ids as $value) {
            // Get the highest invoice ID and customer ID
            $this->db->select_max('id');
            $this->db->select('csd'); // Select customer ID (csd) along with max ID
            $query = $this->db->get('geopos_invoices');
            $result = $query->row_array();
            $highestId = $result['id'] + 1;
            $_cust_id = $result['csd'];


            $this->db->where('id', $value);
            $query1 = $this->db->get('geopos_invoices_bfr_post');
            $data1 = $query1->result_array();
            $inv_numb = 0;
            foreach ($data1 as $invoice) {

                // Fetch Total Balance for the customer
                $this->db->select('SUM(total)-SUM(pamnt) as total_balance');
                $this->db->from('geopos_invoices');
                $this->db->where('status !=', 'paid'); // Exclude records where the amount is 0
                $this->db->where('csd', $invoice['csd']); // Filter by customer ID
                $query = $this->db->get();
                $method_advance = $query->result();
                $total_balance = $method_advance[0]->total_balance;
                // var_dump($total_balance, $invoice['csd']);
                // die();

                $newData = array();
                $newData['id'] = $highestId;
                $newData['tid'] = $invoice['tid'];
                $newData['inv_type'] = $invoice['inv_type'];
                $newData['invoicedate'] = $invoice['invoicedate'];
                $newData['invoiceduedate'] = $invoice['invoiceduedate'];
                $newData['weight_qty'] = $invoice['weight_qty'];
                // $newData['weight_unit'] = $invoice['weight_unit'];
                $newData['subtotal'] = $invoice['subtotal'];
                $newData['shipping'] = $invoice['shipping'];
                $newData['ship_tax'] = $invoice['ship_tax'];
                $newData['ship_tax_type'] = $invoice['ship_tax_type'];
                $newData['discount'] = $invoice['discount'];
                $newData['discount_rate'] = $invoice['discount_rate'];
                $newData['tax'] = $invoice['tax'];
                $newData['total'] = $invoice['total'];
                $newData['pmethod'] = $invoice['pmethod'];
                $newData['notes'] = $invoice['notes'];
                $newData['status'] = $invoice['status'];
                $newData['csd'] = $invoice['csd'];
                $newData['eid'] = $invoice['eid'];
                $newData['pamnt'] = $invoice['pamnt'];
                $newData['items'] = $invoice['items'];
                $newData['taxstatus'] = $invoice['taxstatus'];
                $newData['discstatus'] = $invoice['discstatus'];
                $newData['format_discount'] = $invoice['format_discount'];
                $newData['refer'] = $invoice['refer'];
                $newData['term'] = $invoice['term'];
                $newData['multi'] = $invoice['multi'];
                $newData['i_class'] = $invoice['i_class'];
                $newData['loc'] = $invoice['loc'];
                $newData['r_time'] = $invoice['r_time'];
                $newData['cust_name'] = $invoice['cust_name'];
                $newData['cust_address'] = $invoice['cust_address'];
                $newData['cust_city'] = $invoice['cust_city'];
                $newData['cust_postcode'] = $invoice['cust_postcode'];
                $newData['created'] = $invoice['created'];
                $newData['created_by'] = $invoice['created_by'];
                $newData['updated_on'] = $invoice['updated_on'];
                $newData['print_status'] = 0;
                $newData['is_posted'] = $invoice['is_posted'];
                $newData['driver_name'] = $invoice['driver_name'];
                $newData['vehicle_no'] = $invoice['vehicle_no'];
                $newData['pamt_terms'] = $invoice['pamt_terms'];
                $this->db->insert('geopos_invoices', $newData);
                $insert_id = $this->db->insert_id();
                $inv_numb = $invoice['tid'];
                $new_ids[] = $insert_id;



                $this->aauth->applog('[Invoice Posted - #' . $invoice["tid"] . '] - Customer: ' . $invoice['cust_address'] . ' - Total: ' . $invoice["total"] . ', Tax: ' . $invoice["tax"],           $this->aauth->get_user()->username);
                $this->db->where('tid', $value);
                $query2 = $this->db->get('geopos_invoice_items_bfr_post');


                $data2 = $query2->result_array();

                foreach ($data2 as $invoice_items) {

                    $inv_items['tid'] = $insert_id;
                    $inv_items['pid'] = $invoice_items['pid'];
                    $inv_items['cat_id'] = $invoice_items['cat_id'];
                    $inv_items['cid'] = $invoice_items['cid'];
                    $inv_items['product'] = $invoice_items['product'];
                    $inv_items['code'] = $invoice_items['code'];
                    $inv_items['qty'] = $invoice_items['qty'];
                    $inv_items['price'] = $invoice_items['price'];
                    $inv_items['weight_qty'] = $invoice_items['weight_qty'];
                    // $inv_items['weight_unit'] = $invoice_items['weight_unit'];
                    $inv_items['tax'] = $invoice_items['tax'];
                    $inv_items['discount'] = $invoice_items['discount'];
                    $inv_items['subtotal'] = $invoice_items['subtotal'];
                    $inv_items['totaltax'] = $invoice_items['totaltax'];
                    $inv_items['totaldiscount'] = $invoice_items['totaldiscount'];
                    $inv_items['product_des'] = $invoice_items['product_des'];
                    $inv_items['i_class'] = $invoice_items['i_class'];
                    $inv_items['unit'] = $invoice_items['unit'];
                    $inv_items['serial'] = $invoice_items['serial'];
                    $inv_items['added_on'] = $invoice_items['added_on'];

                    $this->db->insert('geopos_invoice_items', $inv_items);
                }
                // $this->printinvoice(false, $insert_id);

                $this->db->set('is_posted', 1, FALSE);
                $this->db->where('tid', $value);
                $this->db->update('geopos_invoice_items_bfr_post');

                $this->db->where('tid', $value);
                $query3 = $this->db->get('geopos_transactions_bfr_post');
                $data3 = $query3->result_array();
                foreach ($data3 as $invoice_trans) {


                    // Fetch payment methods for the customer
                    $this->db->select('method');
                    $this->db->from('advance_payment');
                    $this->db->where('amount !=', 0); // Exclude records where the amount is 0
                    $this->db->where('payerid', $_cust_id); // Filter by customer ID
                    $query = $this->db->get();
                    // Check if payment methods exist, otherwise use the method from $invoice_trans
                    $method_advance = $query->result();
                    // Check if the retrieved method is 'Advance Payment'
                    if (!empty($method_advance[0]->method)) {
                        $method = 'Advance Payment';
                        $paymt_method = $method_advance[0]->method;
                    } else {
                        $method = $invoice_trans['method'];
                        $paymt_method = $invoice_trans['paymt_method'];
                    }
                    // Prepare transaction data
                    $invoice_trans_data = [
                        'acid' => $invoice_trans['acid'],
                        'account' => $invoice_trans['account'],
                        'type' => $invoice_trans['type'],
                        'cat' => $invoice_trans['cat'],
                        'credit' => $invoice_trans['credit'],
                        'debit' => $invoice_trans['debit'],
                        'balance' => $invoice_trans['balance'],
                        'payer' => $invoice_trans['payer'],
                        'payerid' => $invoice_trans['payerid'],
                        'method' => $method,
                        'paymt_method' => $paymt_method,
                        'paymt_date' => $invoice_trans['paymt_date'],
                        'date' => $invoice['invoicedate'],
                        'tid' => $insert_id, // Ensure $insert_id is defined earlier in your code
                        'eid' => $invoice_trans['eid'],
                        'note' => $invoice_trans['note'],
                        'ext' => $invoice_trans['ext'],
                        'loc' => $invoice_trans['loc'],
                        'os' => $invoice_trans['os'],
                        'inv_id' => $inv_numb, // Ensure $inv_numb is defined earlier in your code
                        'created' => $invoice_trans['created'],
                        'created_by' => $this->aauth->get_user()->id,
                        'received_payment' => $invoice_trans['debit']
                    ];

                    // Insert the transaction data into the database
                    $this->db->insert('geopos_transactions', $invoice_trans_data);

                    $this->db->select('csd,total');
                    $this->db->from('geopos_invoices');
                    $this->db->where('id', $insert_id);
                    $query = $this->db->get();
                    $inv_data = $query->row_array();
                    $total = $inv_data['total'];
                    $csd = $inv_data['csd'];

                    $this->db->select('amount');
                    $this->db->from('advance_payment');
                    $this->db->where('payerid', $csd);
                    $query = $this->db->get();
                    $customer_balance_form_query = $query->row_array();
                    $c_b = floatval($customer_balance_form_query['amount']);

                    // var_dump($c_b); die();


                    if ($total_balance < 0) { // Only process if there’s an overpayment
                        $pamnt_total = abs($total_balance); // Convert to positive value
                        $pamnt_paid = $pamnt_total - $invoice["total"]; // Remaining after invoice payment

                        if ($pamnt_total > 0) {
                            if ($pamnt_total < $invoice["total"]) {
                                // Partial payment
                                $this->db->set('pamnt', "pamnt+$pamnt_total", FALSE);
                                $this->db->set('status', 'partial');
                                $remaining_payment = $pamnt_total;
                            } else {
                                // Full payment
                                $this->db->set('pamnt', "$invoice[total]", FALSE);
                                $this->db->set('status', 'paid');
                                $remaining_payment = $pamnt_paid; // Remaining after paying this invoice
                            }

                            $this->db->where('id', $insert_id);
                            $this->db->update('geopos_invoices');

                            // Update received payment in transactions
                            $this->db->set('received_payment', $invoice["total"], FALSE);
                            $this->db->where('tid', $insert_id);
                            $this->db->update('geopos_transactions');

                            // Process remaining credit invoices
                            if ($remaining_payment > 0) {
                                $this->db->select('id, pamnt, total');
                                $this->db->from('geopos_invoices');
                                $this->db->where('csd', $invoice['csd']);
                                $this->db->where('inv_type', 'CREDIT');
                                $this->db->where_in('status', ['due', 'partial']);
                                $this->db->order_by('id', 'ASC'); // Process older invoices first
                                $query = $this->db->get();

                                foreach ($query->result() as $credit_invoice) {
                                    if ($remaining_payment <= 0) {
                                        break;
                                    }

                                    $invoice_balance = abs($credit_invoice->total) - abs($credit_invoice->pamnt);

                                    if ($remaining_payment >= $invoice_balance) {
                                        // Fully pay this credit invoice
                                        $this->db->set('pamnt', -abs($credit_invoice->total), FALSE); // Ensure negative
                                        $this->db->set('status', 'paid');
                                        $remaining_payment -= $invoice_balance;
                                    } else {
                                        // Partially pay this credit invoice
                                        $this->db->set('pamnt', "pamnt-$invoice[total]", FALSE);
                                        $this->db->set('status', 'partial');
                                        $remaining_payment = 0;
                                    }
                                    $this->db->where_in('status', ['due', 'partial']);
                                    $this->db->where('inv_type', 'CREDIT');
                                    $this->db->where('id', $credit_invoice->id);
                                    $this->db->update('geopos_invoices');
                                }
                            }
                        }
                    }



                    if ($total > $c_b && $c_b > 0) {
                        $total_1 = $total - $c_b;
                        $pamnt_rec = $total - $total_1;
                        $this->db->set('pamnt', "pamnt+$c_b", FALSE);
                        $this->db->set('status', 'partial');
                        $this->db->where('id', $insert_id);
                        $this->db->update('geopos_invoices');

                        $this->db->set('received_payment', "$pamnt_rec", FALSE);
                        $this->db->where('tid', $insert_id);
                        $this->db->update('geopos_transactions');

                        $status = 'Partial';
                        $this->aauth->applog("[Invoice Partial Paid During Post from Advance Balance - Invoice ID: " . $insert_id . "] - Customer ID: " . $csd . ", Status: " . $status . ", Amount: " . $c_b, $this->aauth->get_user()->username);
                        $this->db->set('amount', 0.00);
                        $this->db->where('payerid', $csd);
                        $this->db->update('advance_payment');
                        $query = $this->db->last_query();

                        $this->db->set('status', 'paid');
                        $this->db->where('csd', $csd);
                        $this->db->where('notes =', 'Advance Payment');
                        $this->db->where('r_time', 'mig');
                        $this->db->update('geopos_invoices');
                    } else {
                        if ($total <= $c_b && $c_b > 0) {
                            $this->db->set('pamnt', "pamnt+$total", FALSE);
                            $this->db->set('status', 'paid');
                            $this->db->where('id', $insert_id);
                            $this->db->update('geopos_invoices');

                            $this->db->set('received_payment', "$total", FALSE);
                            $this->db->where('tid', $insert_id);
                            $this->db->update('geopos_transactions');

                            $status = 'Paid';
                            $this->aauth->applog("[Invoice Paid During Post from Advance Balance - Invoice ID: " . $insert_id . "] - Customer ID: " . $csd . ", Status: " . $status . ", Amount: " . $c_b, $this->aauth->get_user()->username);
                            $this->db->set('amount', "amount-$total", FALSE);
                            $this->db->where('payerid', $csd);
                            $this->db->update('advance_payment');

                            $this->db->select('geopos_invoices.*');
                            $this->db->from('geopos_invoices');
                            $this->db->where_in('status', ['partial', 'due']);
                            $this->db->where('r_time', 'mig');
                            $this->db->where('status !=', 'paid');
                            $this->db->where('notes =', 'Advance Payment');
                            $this->db->where('csd',  $csd);
                            $query = $this->db->get();
                            $rows = $query->num_rows();
                            $total_new = $total;
                            if ($rows > 0) {
                                $result = $query->result();
                                foreach ($result as $prd) {
                                    if ($total_new > 0) {
                                        if ($prd->subtotal > $total_new) {
                                            $this->db->set('subtotal', "subtotal-$total_new", FALSE);
                                            $this->db->set('total', "total+$total_new", FALSE);
                                            $this->db->set('status', 'partial');
                                            $this->db->where('id', $prd->id);
                                            $this->db->update('geopos_invoices');
                                            $total_new -= $prd->subtotal;
                                        } else {
                                            $this->db->set('status', 'paid');
                                            $this->db->where('id', $prd->id);
                                            $this->db->update('geopos_invoices');
                                            $total_new -= $prd->subtotal;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }


                $this->db->set('is_posted', 1, FALSE);
                $this->db->where('id', $value);
                $this->db->update('geopos_invoices_bfr_post');
            }

            $i++;
        }

        // Return JSON response with new invoice IDs
        echo json_encode(array('success' => true, 'new_ids' => $new_ids));
    }

    public function creditnotes()
    {
        if (!$this->aauth->permission_new(null, 'salesCreditNote')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $head['title'] = "Manage Credit Notes";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $this->load->view('invoices/creditnotes_client');
        $this->load->view('fixed/footer');
    }

    public function ajax_list_credits()
    {
        $no = $this->input->post('start');
        $type = $this->input->get('t');
        $start_date = $this->input->post('start_date');
        $end_date = $this->input->post('end_date');

        $list = $this->stockreturn->get_creditnote_datatables($type, $start_date, $end_date);
        $data = [];

        foreach ($list as $invoices) {
            $no++;
            $row = [];
            $invoice_id = htmlspecialchars($invoices->id);
            $invoice_tid = htmlspecialchars($invoices->tid);
            $invoice_name = htmlspecialchars($invoices->name);
            $invoice_date = $this->format_invoice_date($invoices->invoicedate);
            $invoice_total = amountExchange($invoices->total, 0, $this->aauth->get_user()->loc);
            $invoice_status = htmlspecialchars($invoices->status);

            $row[] = $no;
            $row[] = '<a href="' . base_url("stockreturn/credit_note?id=$invoice_id") . '">' . $invoice_tid . '</a>';
            $row[] = $invoice_name;
            $row[] = $invoice_date;
            $row[] = $invoice_total;
            // $row[] = '<span class="badge st-' . $invoice_status . '">' . $this->lang->line(ucwords($invoice_status)) . '</span>';
            $row[] = '<a href="' . base_url("stockreturn/credit_note?id=$invoice_id") . '" class="btn btn-success btn-sm"><i class="fa fa-eye"></i> ' . $this->lang->line('View') . '</a> &nbsp; <a href="' . base_url("stockreturn/print_creditnote_invoice?id=$invoice_id") . '&d=1" class="btn btn-info btn-sm"  title="Download"><span class="fa fa-download"></span></a>';
            $data[] = $row;
        }

        $output = [
            "draw" => $this->input->post('draw'),
            "recordsTotal" => $this->stockreturn->count_all_credit_notes($type),
            "recordsFiltered" => $this->stockreturn->count_filtered_credit_notes($type, $start_date, $end_date),
            "data" => $data,
        ];

        // Ensure no output before JSON and set proper content type
        if (ob_get_level()) {
            ob_clean();
        }
        $this->output->set_content_type('application/json');
        echo json_encode($output);
    }

    public function print_customer_invoices($filter = "")
    {
        $head['title'] = "Print Invoices";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $this->load->view('invoices/customer_invoices');
        $this->load->view('fixed/footer');
    }


    public function print_customer_invoices_merged()
    {
        // Get IDs and print type from POST
        $tids = isset($_POST['hidden_fields']) ? explode(",", $_POST['hidden_fields']) : [];
        // Normalize, trim and deduplicate IDs to avoid duplicate prints
        if (!empty($tids)) {
            $tids = array_values(array_unique(array_filter(array_map('trim', $tids), function ($v) {
                return $v !== '';
            })));
        }
        $print_type = $this->input->post('print_type') ?: 'sale';

        // Validate input
        if (empty($tids) || !in_array($print_type, ['sale'])) {
            echo "Invalid request: missing invoice IDs or invalid print type.";
            return;
        }

        $dirPath = 'userfiles/invoices/';

        // Get all files in invoices directory
        $files = array_diff(scandir($dirPath), ['.', '..']);

        $prefix = $print_type . '_';

        // Load your PDFMerger library (make sure it's loaded in your controller)
        $pdf = new PDFMerger();

        $foundAny = false;
        $added = []; // track absolute paths already added

        if ($files) {
            foreach ($tids as $tid) {
                // We want to find files matching prefix + tid
                $expectedName = $prefix . $tid;
                foreach ($files as $file) {
                    // Check file extension and prefix match
                    if (pathinfo($file, PATHINFO_EXTENSION) === 'pdf' && substr($file, 0, -4) === $expectedName) {
                        $absolutePath = $dirPath . $file;
                        if (!isset($added[$absolutePath])) {
                            // add two copies of each invoice
                            $pdf->addPDF($absolutePath, 'all');
                            // $pdf->addPDF($absolutePath, 'all');
                            $added[$absolutePath] = true;
                        }
                        $foundAny = true;
                        break;  // break inner loop once found to avoid duplicates
                    }
                }
            }

            if (!$foundAny) {
                echo "No matching files found to merge.";
                return;
            }

            $new_file = md5(time() . rand(1, 10)) . '.pdf';
            $mergedPath = APPPATH . '../' . $dirPath . $new_file;

            $pdf->merge('file', $mergedPath);

            // Mark selected unposted invoices as printed in a single query
            $ids = array_map('intval', $tids);
            if (!empty($ids)) {
                $this->db->where_in('id', $ids);
                $this->db->set('print_status', 1);
                $this->db->update('geopos_invoices');
            }

            // Output headers for inline display of PDF
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="' . $new_file . '"');
            header('Content-Transfer-Encoding: binary');
            header('Accept-Ranges: bytes');

            // Read and output the merged PDF
            @readfile($mergedPath);

            // Optional: delete merged file after serving
            // unlink($mergedPath);

        } else {
            echo "No invoice files found in directory.";
        }
    }



    public function sales_by_products()
    {
        $head['title'] = "Sales By Product";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $this->load->view('invoices/invoiceproducts');
        $this->load->view('fixed/footer');
    }

    //invoices list
    public function receipt()
    {
        $head['title'] = "Manage Receipt";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $data['list'] = $this->invocies->get_rec_datatables();
        $this->load->view('invoices/invoices_rec', $data);
        $this->load->view('fixed/footer');
    }

    public function invoices_payment()
    {
        if (!$this->aauth->permission_new(null, 'salesCustomerInvoices')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $custid = $this->input->get('id');
        $data['details'] = $this->customers->details($custid);
        $data['money'] = $this->customers->money_details($custid);
        $head['usernm'] = $this->aauth->get_user()->username;
        $head['title'] = 'View Customer Invoices';
        $this->load->view('fixed/header', $head);
        $this->load->view('invoices/cinvoices', $data);
        $this->load->view('fixed/footer');
    }



    public function action_bk_bfr_post()
    {

        $currency = $this->input->post('mcurrency');
        $customer_id = $this->input->post('customer_id');

        if ($customer_id == 0 || $customer_id == '' || empty($customer_id)) {
            echo json_encode(array('status' => 'Error', 'message' =>
            'Please select client for Invoice Generation'));
            exit;
        }

        // Get Customer Balance
        $prev_balance = $this->aauth->get_customer($customer_id);
        $pbalance = $prev_balance->balance;
        $cname = $prev_balance->name;
        $this->db->select_max('tid');
        $this->db->from('geopos_invoices');
        $query = $this->db->get();
        $pr = $query->row_array();
        $cat_id = $pr['tid'];
        //$invocieno = $this->input->post('invocieno');
        //$invocieno = $invocieno + $cat_id ;
        $invocieno =  $cat_id + 1;
        $invoicedate = $this->input->post('invoicedate');
        $invocieduedate = $invoicedate; //$this->input->post('invocieduedate');
        // $invocieduedate = date('d-m-Y', strtotime($invoicedate . ' + 1 days')); //$this->input->post('invocieduedate');
        // $invocieduedate = date('d-m-Y', strtotime($invoicedate . ' + 30 days')); //$this->input->post('invocieduedate');
        $invoicetype = $this->input->post('invoiceType');
        //  if($invoicetype=="DAYPASS"){
        //     $invocieno = 0;
        // }
        $notes = $this->input->post('notes', true);
        $tax = $this->input->post('tax_handle');
        $ship_taxtype = $this->input->post('ship_taxtype');
        $disc_val = numberClean($this->input->post('disc_val'));
        $subtotal = rev_amountExchange_s($this->input->post('subtotal'), $currency, $this->aauth->get_user()->loc);
        $product_id = $this->input->post('pid');
        foreach ($this->input->post('product_qty') as $key => $value) {
            if ($value == '0' || $value == '' &&  $product_id[$key] != "") {
                echo json_encode(array('status' => 'Error', 'message' => 'Quantity can not be zero or empty.'));
                exit;
            }
        }
        foreach ($this->input->post('product_price') as $key => $value) {
            $this->db->select('*');
            $this->db->from('geopos_products');
            $this->db->where('pid', $product_id[$key]);
            $query = $this->db->get();
            $pr = $query->row_array();
            $productprice = $pr['product_price'];
            $product_name = $pr['product_name'];
            if ($value < $productprice) {
                echo json_encode(array('status' => 'Error', 'message' => 'Price for Product ' . $product_name . ' can not be less then ' . $productprice));
                exit;
            }
        }
        // No the farmish of Cheema sb to cater the previous balance at the time of invoice creation

        /**if($subtotal == '0'){
			 echo json_encode(array('status' => 'Error', 'message' =>'Please choose product from product list. Go to Item manager section if you have not added the products.'));
            exit;
		}*/

        $cbalance = $pbalance + $subtotal;
        $shipping = rev_amountExchange_s($this->input->post('shipping'), $currency, $this->aauth->get_user()->loc);
        $shipping_tax = rev_amountExchange_s($this->input->post('ship_tax'), $currency, $this->aauth->get_user()->loc);
        if ($ship_taxtype == 'incl') $shipping = $shipping - $shipping_tax;
        $refer = $this->input->post('refer', true);
        $total = rev_amountExchange_s($this->input->post('total'), $currency, $this->aauth->get_user()->loc);
        $project = $this->input->post('prjid');
        $total_tax = 0;
        $total_discount = rev_amountExchange_s($this->input->post('after_disc'), $currency, $this->aauth->get_user()->loc);
        $discountFormat = $this->input->post('discountFormat');
        $pterms = $this->input->post('pterms', true);
        $i = 0;
        if ($discountFormat == '0') {
            $discstatus = 0;
        } else {
            $discstatus = 1;
        }
        if ($customer_id == 0) {
            echo json_encode(array('status' => 'Error', 'message' =>
            'Please add a new client'));
            exit;
        }

        $this->load->model('plugins_model', 'plugins');
        $empl_e = $this->plugins->universal_api(69);
        if ($empl_e['key1']) {
            $emp = $this->input->post('employee');
        } else {
            $emp = $this->aauth->get_user()->id;
        }

        $transok = true;
        $st_c = 0;
        $this->load->library("Common");
        $this->db->trans_start();
        //Invoice Data
        $bill_date = datefordatabase($invoicedate);
        $bill_due_date = datefordatabase($invocieduedate);
        $cust_name = $this->input->post('customer_name');


        $addrr = $this->input->post('customer_address1');

        $address_bk = preg_split("/\r\n|\n|\r/", $addrr);
        //echo '<pre>'; print_r($address_bk); exit; 

        if (isset($address_bk[0])) {
            $cust_address = trim($address_bk[0]);
        } else {
            $cust_address = "";
        }
        if (isset($address_bk[1])) {
            $cust_city = $address_bk[1];
        } else {
            $cust_city = "";
        }
        if (isset($address_bk[2])) {
            $cust_postcode = $address_bk[2];
        } else {
            $cust_postcode = "";
        }

        /*
if($invoicetype=="DAYPASS"){
$pid = $this->input->post('pid');
$ptotal_tax = $this->input->post('taxa');
 foreach ($pid as $key => $value) {
			if( empty($value) || ($value == NULL) || ($value == "") ){
				}else{
                $total_tax += numberClean($ptotal_tax[$key]);
                }
 }
 //$total=$total-$total_tax;
$total=$total;
 $subtotal=$total;
 $total_tax=0;
 $cbalance = $pbalance+$subtotal;
} */



        $data = array('created_by' => $this->aauth->get_user()->id, 'tid' => $invocieno, 'inv_type' => $invoicetype, 'invoicedate' => $bill_date, 'invoiceduedate' => $bill_due_date, 'subtotal' => $subtotal, 'shipping' => $shipping, 'ship_tax' => $shipping_tax, 'ship_tax_type' => $ship_taxtype, 'discount_rate' => $disc_val, 'total' => $total, 'notes' => $notes, 'csd' => $customer_id, 'eid' => $emp, 'taxstatus' => $tax, 'discstatus' => $discstatus, 'format_discount' => $discountFormat, 'refer' => $refer, 'term' => $pterms, 'multi' => $currency, 'loc' => $this->aauth->get_user()->loc, 'cust_name' => $cust_name, 'cust_address' => $cust_address, 'cust_postcode' => $cust_postcode, 'cust_city' => $cust_city);
        $invocieno2 = $invocieno;
        if ($this->db->insert('geopos_invoices', $data)) {

            $invocieno = $this->db->insert_id();

            //products

            $paydate = date('Y-m-d');

            $data_trans = array(
                'acid' => '1',
                'account' => 'Sales Account',
                'type' => 'Income',
                'cat' => 'Sales',
                'credit' =>  $total,
                'payer' => $cname,
                'payerid' => $customer_id,
                'method' => '',
                'balance' => $cbalance,
                'date' => $bill_date,
                'eid' => $this->aauth->get_user()->id,
                'tid' => $invocieno,
                'inv_id' => $invocieno2,
                'note' => 'Invoice # ' . $invocieno2,
                'loc' => $this->aauth->get_user()->loc
            );
            $this->db->insert('geopos_transactions', $data_trans);


            $data = [
                'balance' => $cbalance,
            ];

            $this->db->where('id', $customer_id);
            $this->db->update('geopos_customers', $data);

            $pid = $this->input->post('pid');
            $productlist = array();
            $prodindex = 0;
            $itc = 0;
            $product_id = $this->input->post('pid');
            $product_name1 = $this->input->post('product_name', true);
            $product_qty = $this->input->post('product_qty');
            $product_price = $this->input->post('product_price');
            $product_tax = $this->input->post('product_tax');
            $product_discount = $this->input->post('product_discount');
            $product_subtotal = $this->input->post('product_subtotal');
            $ptotal_tax = $this->input->post('taxa');
            $ptotal_disc = $this->input->post('disca');
            $product_des = $this->input->post('product_description', true);
            $product_unit = $this->input->post('unit');
            $product_hsn = $this->input->post('hsn', true);
            $product_alert = $this->input->post('alert');
            $product_vattype = $this->input->post('vattype');

            $product_serial = array();

            $boxes = count($pid);

            $p = array_key_exists('SI', $_POST) ? $_POST['SI'] : array();

            for ($v = 0; $v < $boxes; $v++) {

                if (array_key_exists($v, $p)) {
                    $product_serial[$v] = '1';
                } else {
                    $product_serial[$v] = '0';
                }
            }


            foreach ($pid as $key => $value) {
                if (empty($value) || ($value == NULL) || ($value == "")) {
                } else {

                    $total_discount += numberClean(@$ptotal_disc[$key]);
                    $total_tax += calculateTax(rev_amountExchange_s($product_price[$key], $currency, $this->aauth->get_user()->loc) * numberClean($product_qty[$key]), $product_vattype[$key]);

                    $this->db->select('*');
                    $this->db->from('geopos_products');
                    $this->db->where('pid', $product_id[$key]);
                    $query = $this->db->get();
                    $pr = $query->row_array();

                    $cat_id = $pr['pcat'];




                    $data = array(
                        'tid' => $invocieno,
                        'pid' => $product_id[$key],
                        'cat_id' => $cat_id,
                        'cid' => $customer_id,
                        'product' => $product_name1[$key],
                        'code' => $product_hsn[$key],
                        'qty' => numberClean($product_qty[$key]),
                        'price' => rev_amountExchange_s($product_price[$key], $currency, $this->aauth->get_user()->loc),
                        'tax' => calculateTax(rev_amountExchange_s($product_price[$key], $currency, $this->aauth->get_user()->loc) * numberClean($product_qty[$key]), $product_vattype[$key]),
                        'discount' => numberClean($product_discount[$key]),
                        'subtotal' => rev_amountExchange_s($product_subtotal[$key], $currency, $this->aauth->get_user()->loc),
                        'totaltax' => calculateTax(rev_amountExchange_s($product_price[$key], $currency, $this->aauth->get_user()->loc) * numberClean($product_qty[$key]), $product_vattype[$key]),
                        'totaldiscount' => rev_amountExchange_s($ptotal_disc[$key], $currency, $this->aauth->get_user()->loc),
                        'product_des' => $product_des[$key],
                        'unit' => $product_unit[$key],
                        'serial' => $product_serial[$key]
                    );

                    $productlist[$prodindex] = $data;
                    $i++;
                    $prodindex++;
                    $amt = numberClean($product_qty[$key]);
                    if ($product_id[$key] > 0) {
                        $this->db->set('qty', "qty-$amt", FALSE);
                        $this->db->where('pid', $product_id[$key]);
                        $this->db->update('geopos_products');
                        if ((numberClean($product_alert[$key]) - $amt) < 0 and $st_c == 0 and $this->common->zero_stock()) {
                            echo json_encode(array('status' => 'Error', 'message' => 'Product - <strong>' . $product_name1[$key] . "</strong> - Low quantity. Available stock is  " . $product_alert[$key]));
                            $transok = false;
                            $st_c = 1;
                        }
                    }
                    $itc += $amt;
                }
            }

            if ($prodindex > 0) {
                $this->db->insert_batch('geopos_invoice_items', $productlist);
                $this->db->set(array('discount' => rev_amountExchange_s(amountFormat_general($total_discount), $currency, $this->aauth->get_user()->loc), 'tax' => rev_amountExchange_s(amountFormat_general($total_tax), $currency, $this->aauth->get_user()->loc), 'items' => $itc));
                $this->db->where('id', $invocieno);
                $this->db->update('geopos_invoices');
            } else {
                echo json_encode(array('status' => 'Error', 'message' =>
                "Please choose product from product list. Go to Item manager section if you have not added the products."));
                $transok = false;
            }
            if ($transok) {
                $validtoken = hash_hmac('ripemd160', $invocieno, $this->config->item('encryption_key'));
                $link = base_url('billing/view?id=' . $invocieno . '&token=' . $validtoken);


                echo json_encode(array('status' => 'Success', 'pid' => $invocieno,  'message' =>
                $this->lang->line('Invoice Success') . " <a href='view?id=$invocieno' class='btn btn-primary btn-lg'><span class='fa fa-eye' aria-hidden='true'></span> " . $this->lang->line('View') . "  </a> &nbsp; &nbsp;<a href='printinvoice?id=$invocieno' class='btn btn-blue btn-lg' target='_blank'><span class='fa fa-print' aria-hidden='true'></span> " . $this->lang->line('Print') . "  </a> &nbsp; &nbsp; <a href='$link' class='btn btn-purple btn-lg'><span class='fa fa-globe' aria-hidden='true'></span> " . $this->lang->line('Public View') . " </a> &nbsp; &nbsp; <a href='create' class='btn btn-warning btn-lg'><span class='fa fa-plus-circle' aria-hidden='true'></span></a>"));
            }
        } else {
            echo json_encode(array('status' => 'Error', 'message' =>
            "Invalid Entry!"));
            $transok = false;
        }
        if ($transok) {
            if ($this->aauth->permission_new(null, 'salesManageInvoices') and $project > 0) {
                $data = array('pid' => $project, 'meta_key' => 11, 'meta_data' => $invocieno, 'value' => '0');
                $this->db->insert('geopos_project_meta', $data);
            }
            $this->db->trans_complete();

            if ($invoicetype == "DAYPASS") {
                $this->aauth->applog("[Day Pass Created - #$invocieno2] - Customer: $cust_name - Total: $total, Tax: $total_tax, Products: " . count($productlist), $this->aauth->get_user()->username);
            } else {
                $this->aauth->applog("[Invoice Created - #$invocieno2] - Customer: $cust_name - Total: $total, Tax: $total_tax, Products: " . count($productlist), $this->aauth->get_user()->username);
            }
        } else {
            $this->db->trans_rollback();
        }
        if ($transok) {
            $this->db->from('univarsal_api');
            $this->db->where('univarsal_api.id', 56);
            $query = $this->db->get();
            $auto = $query->row_array();
            if ($auto['key1'] == 1) {
                $this->db->select('name,email');
                $this->db->from('geopos_customers');
                $this->db->where('id', $customer_id);
                $query = $this->db->get();
                $customer = $query->row_array();
                $this->load->model('communication_model');
                $invoice_mail = $this->send_invoice_auto($invocieno, $invocieno2, $bill_date, $total, $currency);
                $attachmenttrue = false;
                $attachment = '';
                $this->communication_model->send_corn_email($customer['email'], $customer['name'], $invoice_mail['subject'], $invoice_mail['message'], $attachmenttrue, $attachment);
            }
            if ($auto['key2'] == 1) {
                $this->db->select('name,phone');
                $this->db->from('geopos_customers');
                $this->db->where('id', $customer_id);
                $query = $this->db->get();
                $customer = $query->row_array();
                $this->load->model('plugins_model', 'plugins');

                $invoice_sms = $this->send_sms_auto($invocieno, $invocieno2, $bill_date, $total, $currency);
                $mobile = $customer['phone'];
                $text_message = $invoice_sms['message'];
                $this->load->model('sms_model', 'sms');
                $this->sms->send_sms($mobile, $text_message, false);
            }

            //profit calculation
            $t_profit = 0;
            $this->db->select('geopos_invoice_items.pid, geopos_invoice_items.price, geopos_invoice_items.qty, geopos_products.fproduct_price');
            $this->db->from('geopos_invoice_items');
            $this->db->join('geopos_products', 'geopos_products.pid = geopos_invoice_items.pid', 'left');
            $this->db->where('geopos_invoice_items.tid', $invocieno);
            $query = $this->db->get();
            $pids = $query->result_array();
            foreach ($pids as $profit) {
                $t_cost = $profit['fproduct_price'] * $profit['qty'];
                $s_cost = $profit['price'] * $profit['qty'];
                $t_profit += $s_cost - $t_cost;
            }
            $data = array('type' => 9, 'rid' => $invocieno, 'col1' => rev_amountExchange_s($t_profit, $currency, $this->aauth->get_user()->loc), 'd_date' => $bill_date);

            $this->db->insert('geopos_metadata', $data);

            $this->custom->save_fields_data($invocieno, 2);


            redirect('invoices', 'refresh');
        }
    }





    public function action()
    {
        // dd($this->input->post());
        $auto_post = $this->settings->auto_post();
        // dd($auto_post);
        $invoice_table = 'geopos_invoices_bfr_post';
        $invoice_items_table = 'geopos_invoice_items_bfr_post';
        $transactions_table = 'geopos_transactions_bfr_post';

        if ($auto_post) {
            $invoice_table = 'geopos_invoices';
            $invoice_items_table = 'geopos_invoice_items';
            $transactions_table = 'geopos_transactions';
        }

        $weight_qty = $this->input->post('weight_qty'); // Array of weight quantities
        // $weight_unit = 'KG';
        $product_qty = $this->input->post('product_qty'); // Array of product quantities

        $currency = $this->input->post('mcurrency');
        $customer_id = $this->input->post('customer_id');
        // Fetch Total Balance for the customer
        $this->db->select('SUM(total)-SUM(pamnt) as total_balance');
        $this->db->from('geopos_invoices');
        $this->db->where('status !=', 'paid'); // Exclude records where the amount is 0
        $this->db->where('csd', $customer_id); // Filter by customer ID
        $query = $this->db->get();
        $method_advance = $query->result();
        $total_balance = $method_advance[0]->total_balance;
        // var_dump($total_balance, $customer_id);
        // die();
        $driver_name = $this->input->post('driver_name');
        $vehicle_no = $this->input->post('vehicle_no');

        $pamt_terms = $this->input->post('pamt_terms');
        if (empty($pamt_terms)) {
            $pamt_terms = 0;
        }
        $limit_values = $this->customers->get_limit_values($customer_id);
        $invoice_count = $this->customers->get_invoice_count($customer_id);
        $customer_balance = $this->customers->get_customer_balance($customer_id);
        // Check if limit values are set and perform limit checks
        if (!empty($limit_values)) {
            foreach ($limit_values as $id => $value) {
                // Check if type is 'invoice' or 'balance'
                if ($value['type'] === 'invoices') {
                    if ($value['amount'] <= $invoice_count) {
                        echo json_encode(array('status' => 'Error', 'message' =>
                        'The Invoice limit for the client has been reached'));
                        exit;
                    }
                } elseif ($value['type'] === 'balance') {
                    if ($value['amount'] <= $customer_balance) {
                        echo json_encode(array('status' => 'Error', 'message' =>
                        'The Invoice Balance limit for the client has been reached'));
                        exit;
                    }
                }
            }
        }
        if ($customer_id == 0 || $customer_id == '' || empty($customer_id)) {
            echo json_encode(array('status' => 'Error', 'message' =>
            'Please select client for Invoice Generation'));
            exit;
        }

        // Get Customer Balance
        $prev_balance = $this->aauth->get_customer($customer_id);
        $pbalance = $prev_balance->balance;
        $cname = $prev_balance->name;
        $this->db->select_max('tid');
        $this->db->from($invoice_table);
        $query = $this->db->get();
        $pr = $query->row_array();
        $cat_id = $pr['tid'];
        $invocieno =  $cat_id + 1;
        $invoicedate = $this->input->post('invoicedate');
        $invocieduedate = $invoicedate; //$this->input->post('invocieduedate');
        // $invocieduedate = date('d-m-Y', strtotime($invoicedate . ' + 1 days')); //$this->input->post('invocieduedate');
        // $invocieduedate = date('d-m-Y', strtotime($invoicedate . ' + 30 days')); //$this->input->post('invocieduedate');
        $invoicetype = $this->input->post('invoiceType');
        $notes = $this->input->post('notes', true);
        $tax = $this->input->post('tax_handle');
        $ship_taxtype = $this->input->post('ship_taxtype');
        $disc_val = numberClean($this->input->post('disc_val'));
        $subtotal = rev_amountExchange_s($this->input->post('subtotal'), $currency, $this->aauth->get_user()->loc);
        $product_id = $this->input->post('pid');
        foreach ($this->input->post('product_qty') as $key => $value) {
            if ($value == '0' || $value == '' &&  $product_id[$key] != "") {
                echo json_encode(array('status' => 'Error', 'message' => 'Quantity can not be zero or empty.'));
                exit;
            }
        }
        foreach ($this->input->post('product_price') as $key => $value) {
            $this->db->select('*');
            $this->db->from('geopos_products');
            $this->db->where('pid', $product_id[$key]);
            $query = $this->db->get();
            $pr = $query->row_array();
            $productprice = $pr['product_price'];
            $product_name = $pr['product_name'];
        }
        $cbalance = $pbalance + $subtotal;
        $shipping = rev_amountExchange_s($this->input->post('shipping'), $currency, $this->aauth->get_user()->loc);
        $shipping_tax = rev_amountExchange_s($this->input->post('ship_tax'), $currency, $this->aauth->get_user()->loc);
        if ($ship_taxtype == 'incl') $shipping = $shipping - $shipping_tax;
        $refer = $this->input->post('refer', true);
        $total = rev_amountExchange_s($this->input->post('total'), $currency, $this->aauth->get_user()->loc);
        $project = $this->input->post('prjid');
        $total_tax = 0;
        $total_discount = rev_amountExchange_s($this->input->post('after_disc'), $currency, $this->aauth->get_user()->loc);
        $discountFormat = $this->input->post('discountFormat');

        $pterms = $this->input->post('pterms', true);
        // Set default term to 1 if no payment terms are selected
        if (empty($pterms)) {
            $pterms = 1;
        }
        $i = 0;
        if ($discountFormat == '0') {
            $discstatus = 0;
        } else {
            $discstatus = 1;
        }
        if ($customer_id == 0) {
            echo json_encode(array('status' => 'Error', 'message' =>
            'Please add a new client'));
            exit;
        }

        $this->load->model('plugins_model', 'plugins');
        $empl_e = $this->plugins->universal_api(69);
        if ($empl_e['key1']) {
            $emp = $this->input->post('employee');
        } else {
            $emp = $this->aauth->get_user()->id;
        }

        $transok = true;
        $st_c = 0;
        $this->load->library("Common");
        $this->db->trans_start();
        //Invoice Data
        $bill_date = datefordatabase($invoicedate);
        $bill_due_date = datefordatabase($invocieduedate);
        $cust_name = $this->input->post('customer_name');


        $addrr = $this->input->post('customer_address1');

        $address_bk = preg_split("/\r\n|\n|\r/", $addrr);
        //echo '<pre>'; print_r($address_bk); exit; 

        if (isset($address_bk[0])) {
            $cust_address = trim($address_bk[0]);
        } else {
            $cust_address = "";
        }
        if (isset($address_bk[1])) {
            $cust_city = $address_bk[1];
        } else {
            $cust_city = "";
        }
        if (isset($address_bk[2])) {
            $cust_postcode = $address_bk[2];
        } else {
            $cust_postcode = "";
        }
        $total_tax = 0;
        //  $cbalance = $pbalance+$subtotal;
        // }
        // Retrieve inputs from POST request


        // Initialize the total weight variable
        $total_weight = 0;

        // Loop through the weight quantities and product quantities to calculate the total weight
        if (!empty($weight_qty) && !empty($product_qty)) {
            foreach ($weight_qty as $key => $weight) {
                // Multiply weight by product quantity and add to the total weight
                $total_weight += $weight * (int)$product_qty[$key];
            }
        }
        // var_dump($total_weight); die();

        $data = array('created_by' => $this->aauth->get_user()->id, 'tid' => $invocieno, 'inv_type' => $invoicetype, 'invoicedate' => $bill_date, 'invoiceduedate' => $bill_due_date, 'weight_qty' => $total_weight, 'subtotal' => $subtotal, 'shipping' => $shipping, 'ship_tax' => $shipping_tax, 'ship_tax_type' => $ship_taxtype, 'discount_rate' => $disc_val, 'total' => $total, 'notes' => $notes, 'csd' => $customer_id, 'eid' => $emp, 'taxstatus' => $tax, 'discstatus' => $discstatus, 'format_discount' => $discountFormat, 'refer' => $refer, 'term' => $pterms, 'multi' => $currency, 'loc' => $this->aauth->get_user()->loc, 'cust_name' => $cust_name, 'cust_address' => $cust_address, 'cust_postcode' => $cust_postcode, 'cust_city' => $cust_city, 'driver_name' => $driver_name, 'vehicle_no' => $vehicle_no, 'pamt_terms' => $pamt_terms);
        $invocieno2 = $invocieno;
        if ($this->db->insert($invoice_table, $data)) {

            $invocieno = $this->db->insert_id();

            //products

            $paydate = date('Y-m-d');
            $note_set = '';
            if ($invoicetype == 'INVOICE') {
                $note_set = 'Invoice # ' . $invocieno2;
            } else if ($invoicetype == 'DAYPASS') {
                $note_set = 'Daypass # ' . $invocieno2;
            }
            // var_dump($note_set); die();
            $data_trans = array(
                'acid' => '1',
                'account' => 'Sales Account',
                'type' => 'Income',
                'cat' => 'Sales',
                'credit' =>  $total,
                'payer' => $cname,
                'payerid' => $customer_id,
                'method' => '',
                'balance' => $cbalance,
                'date' => $bill_date,
                'eid' => $this->aauth->get_user()->id,
                'tid' => $invocieno,
                'inv_id' => $invocieno2,
                'note' => $note_set,
                'loc' => $this->aauth->get_user()->loc
            );
            $this->db->insert($transactions_table, $data_trans);


            $data = [
                'balance' => $cbalance,
            ];

            $this->db->where('id', $customer_id);
            $this->db->update('geopos_customers', $data);

            $pid = $this->input->post('pid');
            $productlist = array();
            $prodindex = 0;
            $itc = 0;
            $product_id = $this->input->post('pid');
            $product_name1 = $this->input->post('product_name', true);
            $product_qty = $this->input->post('product_qty');
            $product_price = $this->input->post('product_price');
            $product_tax = $this->input->post('product_tax');
            $product_discount = $this->input->post('product_discount');
            $product_subtotal = $this->input->post('product_subtotal');
            $ptotal_tax = $this->input->post('taxa');
            $ptotal_disc = $this->input->post('disca');
            $product_des = $this->input->post('product_description', true);
            $product_unit = $this->input->post('unit');
            $product_hsn = $this->input->post('hsn', true);
            $product_alert = $this->input->post('alert');
            $product_vattype = $this->input->post('vattype');

            $product_serial = array();

            $boxes = count($pid);

            $p = array_key_exists('SI', $_POST) ? $_POST['SI'] : array();

            for ($v = 0; $v < $boxes; $v++) {

                if (array_key_exists($v, $p)) {
                    $product_serial[$v] = '1';
                } else {
                    $product_serial[$v] = '0';
                }
            }


            foreach ($pid as $key => $value) {
                if (empty($value) || ($value == NULL) || ($value == "")) {
                } else {

                    $total_discount += numberClean(@$ptotal_disc[$key]);
                    $total_tax += rev_amountExchange_s($ptotal_tax[$key], $currency, $this->aauth->get_user()->loc);

                    $this->db->select('*');
                    $this->db->from('geopos_products');
                    $this->db->where('pid', $product_id[$key]);
                    $query = $this->db->get();
                    $pr = $query->row_array();

                    $cat_id = $pr['pcat'];

                    $purchase = $pr['product_price'];

                    $data = array(
                        'tid' => $invocieno,
                        'pid' => $product_id[$key],
                        'cat_id' => $cat_id,
                        'cid' => $customer_id,
                        'product' => $product_name1[$key],
                        'code' => $product_hsn[$key],
                        'qty' => numberClean($product_qty[$key]),
                        'price' => rev_amountExchange_s($product_price[$key], $currency, $this->aauth->get_user()->loc),
                        'weight_qty' => $weight_qty[$key],
                        // 'weight_unit' => $weight_unit[$key],
                        'purchase' => numberClean($purchase),
                        'tax' => rev_amountExchange_s($product_tax[$key], $currency, $this->aauth->get_user()->loc),
                        'discount' => numberClean($product_discount[$key]),
                        'subtotal' => rev_amountExchange_s($product_subtotal[$key], $currency, $this->aauth->get_user()->loc),
                        'totaltax' => rev_amountExchange_s($ptotal_tax[$key], $currency, $this->aauth->get_user()->loc),
                        'totaldiscount' => rev_amountExchange_s($ptotal_disc[$key], $currency, $this->aauth->get_user()->loc),
                        'product_des' => $product_des[$key],
                        'unit' => $product_unit[$key],
                        'serial' => $product_serial[$key]
                    );

                    $productlist[$prodindex] = $data;
                    $i++;
                    $prodindex++;
                    $amt = numberClean($product_qty[$key]);
                    if ($product_id[$key] > 0) {
                        $this->db->set('qty', "qty-$amt", FALSE);
                        $this->db->where('pid', $product_id[$key]);
                        $this->db->update('geopos_products');
                        if ((numberClean($product_alert[$key]) - $amt) < 0 and $st_c == 0 and $this->common->zero_stock()) {
                            echo json_encode(array('status' => 'Error', 'message' => 'Product - <strong>' . $product_name1[$key] . "</strong> - Low quantity. Available stock is  " . $product_alert[$key]));
                            $transok = false;
                            $st_c = 1;
                        }
                    }
                    $itc += $amt;
                }
            }
            // var_dump($productlist); die();

            if ($prodindex > 0) {
                $this->db->insert_batch($invoice_items_table, $productlist);
                $this->db->set(array('discount' => rev_amountExchange_s(amountFormat_general($total_discount), $currency, $this->aauth->get_user()->loc), 'tax' => rev_amountExchange_s(amountFormat_general($total_tax), $currency, $this->aauth->get_user()->loc), 'items' => $itc));
                $this->db->where('id', $invocieno);
                $this->db->update($invoice_table);
            } else {
                echo json_encode(array('status' => 'Error', 'message' =>
                "Please choose product from product list. Go to Item manager section if you have not added the products."));
                $transok = false;
            }
            if ($transok) {
                $validtoken = hash_hmac('ripemd160', $invocieno, $this->config->item('encryption_key'));
                $link = base_url('billing/view?id=' . $invocieno . '&token=' . $validtoken);


                echo json_encode(array('status' => 'Success', 'pid' => $invocieno,  'message' =>
                $this->lang->line('Invoice Success') . " <a href='view?id=$invocieno' class='btn btn-primary btn-lg'><span class='fa fa-eye' aria-hidden='true'></span> " . $this->lang->line('View') . "  </a> &nbsp; &nbsp;<a href='printinvoice_bfr_posting?id=$invocieno' class='btn btn-blue btn-lg' target='_blank'><span class='fa fa-print' aria-hidden='true'></span> " . $this->lang->line('Print') . "  </a> &nbsp; &nbsp; <a href='$link' class='btn btn-purple btn-lg'><span class='fa fa-globe' aria-hidden='true'></span> " . $this->lang->line('Public View') . " </a> &nbsp; &nbsp; <a href='create' class='btn btn-warning btn-lg'><span class='fa fa-plus-circle' aria-hidden='true'></span></a>"));
            }

            // var_dump($total_balance ,$invocieno, $customer_id); die();


            if ($auto_post && $total_balance < 0) { // Only process if there’s an overpayment
                $pamnt_total = abs($total_balance); // Convert to positive value
                $pamnt_paid = $pamnt_total - abs($total); // Remaining after invoice payment

                if ($pamnt_total > 0) {
                    if ($pamnt_total < $total) {
                        // Partial payment
                        $this->db->set('pamnt', "pamnt+$pamnt_total", FALSE);
                        $this->db->set('status', 'partial');
                        $remaining_payment = $pamnt_total;
                    } else {
                        // Full payment
                        $this->db->set('pamnt', "pamnt+$total", FALSE);
                        $this->db->set('status', 'paid');
                        $remaining_payment = $pamnt_paid; // Remaining after paying this invoice
                    }

                    $this->db->where('id', $invocieno);
                    $this->db->update('geopos_invoices');

                    // Update received payment in transactions
                    $this->db->set('received_payment', $total, FALSE);
                    $this->db->where('tid', $invocieno);
                    $this->db->update('geopos_transactions');

                    // Process remaining credit invoices
                    if ($remaining_payment > 0) {
                        $this->db->select('id, pamnt, total');
                        $this->db->from('geopos_invoices');
                        $this->db->where('csd', $customer_id);
                        $this->db->where('inv_type', 'CREDIT');
                        $this->db->where_in('status', ['due', 'partial']);
                        $this->db->order_by('id', 'ASC'); // Process older invoices first
                        $query = $this->db->get();

                        foreach ($query->result() as $credit_invoice) {
                            if ($remaining_payment <= 0) {
                                break;
                            }

                            $invoice_balance = abs($credit_invoice->total) - abs($credit_invoice->pamnt);

                            if ($remaining_payment >= $invoice_balance) {
                                // Fully pay this credit invoice
                                $this->db->set('pamnt', -abs($credit_invoice->total), FALSE); // Ensure negative
                                $this->db->set('status', 'paid');
                                $remaining_payment -= $invoice_balance;
                            } else {
                                // Partially pay this credit invoice
                                $this->db->set('pamnt', "pamnt-$total", FALSE);
                                $this->db->set('status', 'partial');
                                $remaining_payment = 0;
                            }

                            $this->db->where('id', $credit_invoice->id);
                            $this->db->update('geopos_invoices');
                        }
                    }
                }
            }




            $this->db->select('csd,total');
            $this->db->from('geopos_invoices');
            $this->db->where('id', $invocieno);
            $query = $this->db->get();
            $inv_data = $query->row_array();
            $total = floatval($inv_data['total']);
            $csd = $inv_data['csd'];

            $this->db->select('amount');
            $this->db->from('advance_payment');
            $this->db->where('payerid', $csd);
            $query = $this->db->get();
            $customer_balance_form_query = $query->row_array();
            $c_b = floatval($customer_balance_form_query['amount']);

            $c_b = floatval($customer_balance_form_query['amount']  ?? 0);

            if ($c_b > 0) {
                if ($total > $c_b && $c_b > 0) {

                    $this->db->set('pamnt', "pamnt+$c_b", FALSE);
                    $this->db->set('status', 'partial');
                    $this->db->where('id', $invocieno);
                    $this->db->update('geopos_invoices');

                    $status = 'Partial';
                    $this->aauth->applog("[Invoice Partial Paid During Post from Advance Balance - Invoice ID: " . $invocieno . "] - Customer ID: " . $csd . ", - Customer: " . $cust_name . ", Status: " . $status . ", Amount: " . $c_b, $this->aauth->get_user()->username);
                    $this->db->set('amount', 0.00);
                    $this->db->where('payerid', $csd);
                    $this->db->update('advance_payment');
                    $query = $this->db->last_query();

                    $this->db->set('status', 'paid');
                    $this->db->where('csd', $csd);
                    $this->db->where('notes =', 'Advance Payment');
                    $this->db->where('r_time', 'mig');
                    $this->db->update('geopos_invoices');
                }
                if ($total == $c_b) {

                    $this->db->set('pamnt', "pamnt+$c_b", FALSE);
                    // $this->db->set('pamnt', $total, FALSE);
                    $this->db->set('status', 'paid');
                    $this->db->where('id', $invocieno);
                    $this->db->update('geopos_invoices');

                    $status = 'Paid';
                    $this->aauth->applog("[Invoice Comlpete Paid from Advance Balance - Invoice: " . $invocieno . "] - Customer: " . $cust_name . ", Status: " . $status . ", Amount: " . $c_b, $this->aauth->get_user()->username);
                    $this->db->set('amount', 0.00);
                    $this->db->where('payerid', $csd);
                    $this->db->update('advance_payment');
                    $this->db->set('status', 'paid');
                    $this->db->where('csd', $csd);
                    $this->db->where('notes =', 'Advance Payment');
                    $this->db->where('r_time', 'mig');
                    $this->db->update('geopos_invoices');
                } else {
                    if ($total < $c_b && $c_b > 0) {
                        $this->db->set('pamnt', "pamnt+$total", FALSE);
                        $this->db->set('status', 'paid');
                        $this->db->where('id', $invocieno);
                        $this->db->update('geopos_invoices');
                        $status = 'Paid';
                        $this->aauth->applog("[Invoice Paid During Post from Advance Balance - Invoice ID: " . $invocieno . "] - Customer ID: " . $csd . ",- Customer: " . $cust_name . ", Status: " . $status . ", Amount: " . $c_b, $this->aauth->get_user()->username);
                        $this->db->set('amount', "amount-$total", FALSE);
                        $this->db->where('payerid', $csd);
                        $this->db->update('advance_payment');

                        $this->db->select('geopos_invoices.*');
                        $this->db->from('geopos_invoices');
                        $this->db->where_in('status', ['partial', 'due']);
                        $this->db->where('r_time', 'mig');
                        $this->db->where('status !=', 'paid');
                        $this->db->where('notes =', 'Advance Payment');
                        $this->db->where('csd',  $csd);
                        $query = $this->db->get();
                        $rows = $query->num_rows();
                        $total_new = $total;
                        if ($rows > 0) {
                            $result = $query->result();
                            foreach ($result as $prd) {
                                if ($total_new > 0) {
                                    if ($prd->subtotal > $total_new) {
                                        $this->db->set('subtotal', "subtotal-$total_new", FALSE);
                                        $this->db->set('total', "total+$total_new", FALSE);
                                        $this->db->set('status', 'partial');
                                        $this->db->where('id', $prd->id);
                                        $this->db->update('geopos_invoices');
                                        $total_new -= $prd->subtotal;
                                    } else {
                                        $this->db->set('status', 'paid');
                                        $this->db->where('id', $prd->id);
                                        $this->db->update('geopos_invoices');
                                        $total_new -= $prd->subtotal;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } else {
            echo json_encode(array('status' => 'Error', 'message' =>
            "Invalid Entry!"));
            // var_dump($this->db->last_query());
            $transok = false;
        }
        if ($transok) {
            if ($this->aauth->permission_new(null, 'salesManageInvoices') and $project > 0) {
                $data = array('pid' => $project, 'meta_key' => 11, 'meta_data' => $invocieno, 'value' => '0');
                $this->db->insert('geopos_project_meta', $data);
            }
            $this->db->trans_complete();
        } else {
            $this->db->trans_rollback();
        }
        if ($transok) {
            $this->db->from('univarsal_api');
            $this->db->where('univarsal_api.id', 56);
            $query = $this->db->get();
            $auto = $query->row_array();
            if ($auto['key1'] == 1) {
                $this->db->select('name,email');
                $this->db->from('geopos_customers');
                $this->db->where('id', $customer_id);
                $query = $this->db->get();
                $customer = $query->row_array();
                $this->load->model('communication_model');
                $invoice_mail = $this->send_invoice_auto($invocieno, $invocieno2, $bill_date, $total, $currency);
                $attachmenttrue = false;
                $attachment = '';
                $this->communication_model->send_corn_email($customer['email'], $customer['name'], $invoice_mail['subject'], $invoice_mail['message'], $attachmenttrue, $attachment);
            }
            if ($auto['key2'] == 1) {
                $this->db->select('name,phone');
                $this->db->from('geopos_customers');
                $this->db->where('id', $customer_id);
                $query = $this->db->get();
                $customer = $query->row_array();
                $this->load->model('plugins_model', 'plugins');

                $invoice_sms = $this->send_sms_auto($invocieno, $invocieno2, $bill_date, $total, $currency);
                $mobile = $customer['phone'];
                $text_message = $invoice_sms['message'];
                $this->load->model('sms_model', 'sms');
                $this->sms->send_sms($mobile, $text_message, false);
            }

            //profit calculation
            $t_profit = 0;
            $this->db->select($invoice_items_table . '.pid, ' . $invoice_items_table . '.price, ' . $invoice_items_table . '.qty, geopos_products.fproduct_price');
            $this->db->from($invoice_items_table);
            $this->db->join('geopos_products', 'geopos_products.pid = ' . $invoice_items_table . '.pid', 'left');
            $this->db->where($invoice_items_table . '.tid', $invocieno);
            $query = $this->db->get();
            $pids = $query->result_array();
            foreach ($pids as $profit) {
                $t_cost = $profit['fproduct_price'] * $profit['qty'];
                $s_cost = $profit['price'] * $profit['qty'];
                $t_profit += $s_cost - $t_cost;
            }
            $data = array('type' => 9, 'rid' => $invocieno, 'col1' => rev_amountExchange_s($t_profit, $currency, $this->aauth->get_user()->loc), 'd_date' => $bill_date);

            $this->db->insert('geopos_metadata', $data);

            $this->custom->save_fields_data($invocieno, 2);

            // redirect('invoices/unposted_invoices', 'refresh');
            if ($auto_post) {
                redirect('invoices/', 'refresh');
            } else {
                redirect('invoices/unposted_invoices', 'refresh');
            }
        }
    }


    public function print_merged_unposted()
    {
        // Get IDs and print type from POST
        $tids = isset($_POST['hidden_fields']) ? explode(",", $_POST['hidden_fields']) : [];
        // Normalize, trim and deduplicate IDs to avoid duplicate prints
        if (!empty($tids)) {
            $tids = array_values(array_unique(array_filter(array_map('trim', $tids), function ($v) {
                return $v !== '';
            })));
        }
        $print_type = $this->input->post('print_type');

        // Validate input
        if (empty($tids) || !in_array($print_type, ['sale', 'quote', 'counter', 'loadingslip'])) {
            echo "Invalid request: missing invoice IDs or invalid print type.";
            return;
        }

        $dirPath = 'userfiles/invoices_bfr_postr/';

        // Get all files in invoices_bfr_postr directory
        $files = array_diff(scandir($dirPath), ['.', '..']);

        $prefix = $print_type . '_';

        // Load your PDFMerger library (make sure it's loaded in your controller)
        $pdf = new PDFMerger();

        $foundAny = false;
        $added = []; // track absolute paths already added

        if ($files) {
            foreach ($tids as $tid) {
                // We want to find files matching prefix + tid
                $expectedName = $prefix . $tid;
                foreach ($files as $file) {
                    // Check file extension and prefix match
                    if (pathinfo($file, PATHINFO_EXTENSION) === 'pdf' && substr($file, 0, -4) === $expectedName) {
                        $absolutePath = $dirPath . $file;
                        if (!isset($added[$absolutePath])) {
                            // add two copies of each invoice
                            $pdf->addPDF($absolutePath, 'all');
                            // $pdf->addPDF($absolutePath, 'all');
                            $added[$absolutePath] = true;
                        }
                        $foundAny = true;
                        break;  // break inner loop once found to avoid duplicates
                    }
                }
            }

            if (!$foundAny) {
                echo "No matching files found to merge.";
                return;
            }

            $new_file = md5(time() . rand(1, 10)) . '.pdf';
            $mergedPath = APPPATH . '../' . $dirPath . $new_file;

            $pdf->merge('file', $mergedPath);

            // Mark selected invoices as printed in a single query
            $ids = array_map('intval', $tids);
            if (!empty($ids)) {
                $this->db->where_in('id', $ids);
                $this->db->set('print_status', 1);
                $this->db->update('geopos_invoices_bfr_post');
            }

            // Output headers for inline display of PDF
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="' . $new_file . '"');
            header('Content-Transfer-Encoding: binary');
            header('Accept-Ranges: bytes');

            // Read and output the merged PDF
            @readfile($mergedPath);

            // Optional: delete merged file after serving
            // unlink($mergedPath);

        } else {
            echo "No invoice files found in directory.";
        }
    }

    public function print_merged()
    {
        // Get IDs and print type from POST
        $tids = isset($_POST['hidden_fields']) ? explode(",", $_POST['hidden_fields']) : [];
        // Normalize, trim and deduplicate IDs to avoid duplicate prints
        if (!empty($tids)) {
            $tids = array_values(array_unique(array_filter(array_map('trim', $tids), function ($v) {
                return $v !== '';
            })));
        }
        $print_type = $this->input->post('print_type') ?: 'sale';

        // Validate input
        if (empty($tids) || !in_array($print_type, ['sale'])) {
            echo "Invalid request: missing invoice IDs or invalid print type.";
            return;
        }

        $dirPath = 'userfiles/invoices/';

        // Get all files in invoices directory
        $files = array_diff(scandir($dirPath), ['.', '..']);

        $prefix = $print_type . '_';

        // Load your PDFMerger library (make sure it's loaded in your controller)
        $pdf = new PDFMerger();

        $foundAny = false;
        $added = []; // track absolute paths already added

        if ($files) {
            foreach ($tids as $tid) {
                // We want to find files matching prefix + tid
                $expectedName = $prefix . $tid;
                foreach ($files as $file) {
                    // Check file extension and prefix match
                    if (pathinfo($file, PATHINFO_EXTENSION) === 'pdf' && substr($file, 0, -4) === $expectedName) {
                        $absolutePath = $dirPath . $file;
                        if (!isset($added[$absolutePath])) {
                            // add two copies of each invoice
                            $pdf->addPDF($absolutePath, 'all');
                            // $pdf->addPDF($absolutePath, 'all');
                            $added[$absolutePath] = true;
                        }
                        $foundAny = true;
                        break;  // break inner loop once found to avoid duplicates
                    }
                }
            }

            if (!$foundAny) {
                echo "No matching files found to merge.";
                return;
            }

            $new_file = md5(time() . rand(1, 10)) . '.pdf';
            $mergedPath = APPPATH . '../' . $dirPath . $new_file;

            $pdf->merge('file', $mergedPath);

            // Mark selected unposted invoices as printed in a single query
            $ids = array_map('intval', $tids);
            if (!empty($ids)) {
                $this->db->where_in('id', $ids);
                $this->db->set('print_status', 1);
                $this->db->update('geopos_invoices');
            }

            // Output headers for inline display of PDF
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="' . $new_file . '"');
            header('Content-Transfer-Encoding: binary');
            header('Accept-Ranges: bytes');

            // Read and output the merged PDF
            @readfile($mergedPath);

            // Optional: delete merged file after serving
            // unlink($mergedPath);

        } else {
            echo "No invoice files found in directory.";
        }
    }

    //     public function ajax_list_unposted(){

    //         $this->db->select('title, content, date');
    //        $this->db->from('mytable');
    // $query = $this->db->get(); 

    //     }

    public function ajax_list_daypass()
    {
        $list = $this->invocies->get_datatables_daypass($this->limited);
        //var_dump($list); exit; 
        $data = array();
        $no = $this->input->post('start');
        $no = $this->input->post('start');
        foreach ($list as $invoices) {
            $no++;
            $row = array();
            $row[] = $no;
            if ($invoices->print_status == 1) {

                $printed = 'Yes';
            } else {
                $printed = ' ';
            }

            $row[] = $invoices->tid;
            $row[] =  $invoices->name;

            $row[] =  $invoices->company;
            $row[] =  $invoices->inv_type;
            $row[] = $this->format_invoice_date($invoices->invoicedate);
            $row[] = amountExchange($invoices->total, 0, $this->aauth->get_user()->loc);
            $row[] = '<span class="st-' . $invoices->status . '">' . $this->format_status_text($invoices->status) . '</span>';
            $row[] = $printed;
            $row[] = '<input type="checkbox"  class="checkbox"  name="receipt" data-id="' . $invoices->id . '" value="' . $invoices->id . '">';
            $row[] = '<a href="' . base_url("invoices/printinvoice?id=$invoices->id") . '&d=1" class="btn btn-info btn-sm"  title="Download"><span class="fa fa-download"></span></a> <a href="#" data-object-id="' . $invoices->id . '" class="btn btn-danger btn-sm delete-object"><span class="fa fa-trash"></span></a>';
            $data[] = $row;
        }
        $output = array(
            "draw" => $this->input->post('draw'),
            "recordsTotal" => $this->invocies->count_all($this->limited),
            "recordsFiltered" => $this->invocies->count_filtered($this->limited),
            "data" => $data,
        );
        //output to json format
        if (ob_get_level()) {
            ob_clean();
        }
        $this->output->set_content_type('application/json');
        echo json_encode($output);
    }

    public function delete_post_invoices()
    {

        $id = $this->input->get('ids');
        $this->db->where('id', $id);
        $this->db->delete('geopos_invoices_bfr_post');

        $this->db->where('tid', $id);
        $query = $this->db->delete('geopos_invoice_items_bfr_post');


        $this->db->where('tid', $id);
        $query = $this->db->delete('geopos_transactions_bfr_post');

        echo "success";
    }

    public function delete_invoices()
    {
        if (!$this->aauth->permission_new(null, 'salesDeleteInvoice')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }

        $id = $this->input->get('ids');

        $this->db->where('inv_id', $id);
        $query1 = $this->db->get('geopos_stock_r');
        $data1 = $query1->result_array();
        if ($data1) {
            foreach ($data1 as $invoice_ret) {
                $transaction_id = $invoice_ret['trans_id'];
                $this->db->where('tid', $invoice_ret['id']);
                $this->db->delete('geopos_stock_r_items');
            }
        }
        $this->db->where('id', $transaction_id);
        $query = $this->db->delete('geopos_transactions');

        $this->db->where('inv_id', $id);
        $this->db->delete('geopos_stock_r');




        $this->db->where('id', $id);
        $this->db->delete('geopos_invoices');

        $this->db->where('tid', $id);
        $query = $this->db->delete('geopos_invoice_items');


        $this->db->where('tid', $id);
        $query = $this->db->delete('geopos_transactions');

        echo "success";
    }



    public function duplicate_invoices()
    {
        $auto_post = $this->settings->auto_post();
        $invoice_table = 'geopos_invoices_bfr_post';
        $invoice_items_table = 'geopos_invoice_items_bfr_post';
        $transactions_table = 'geopos_transactions_bfr_post';
        if ($auto_post) {
            $invoice_table = 'geopos_invoices';
            $invoice_items_table = 'geopos_invoice_items';
            $transactions_table = 'geopos_transactions';
        }

        $ids = $this->input->get('ids');
        $this->db->where('id', $ids);
        $query1 = $this->db->get('geopos_invoices');
        $data1 = $query1->result_array();
        $this->db->trans_start();

        $customer_id = 0;

        $lastinvoice = $this->invocies->lastinvoice() + 1;

        foreach ($data1 as $invoice) {

            $newData = array();

            $newData['tid'] = $lastinvoice;
            $newData['inv_type'] = $invoice['inv_type'];
            $newData['invoicedate'] = datefordatabase(date('Y-m-d H:i:s'));
            $newData['invoiceduedate'] = datefordatabase(date('d-m-Y', strtotime(date() . ' + 30 days')));
            $newData['subtotal'] = $invoice['subtotal'];
            $newData['shipping'] = $invoice['shipping'];
            $newData['ship_tax'] = $invoice['ship_tax'];
            $newData['ship_tax_type'] = $invoice['ship_tax_type'];
            $newData['discount'] = $invoice['discount'];
            $newData['discount_rate'] = $invoice['discount_rate'];
            $newData['tax'] = $invoice['tax'];
            $newData['total'] = $invoice['total'];
            $newData['pmethod'] = $invoice['pmethod'];
            $newData['notes'] = $invoice['notes'];
            $newData['status'] = due;
            $newData['csd'] = $invoice['csd'];
            $customer_id = $invoice['csd'];
            $newData['eid'] = $this->aauth->get_user()->id;
            $newData['pamnt'] = $invoice['pamnt'];
            $newData['items'] = $invoice['items'];
            $newData['taxstatus'] = $invoice['taxstatus'];
            $newData['discstatus'] = $invoice['discstatus'];
            $newData['format_discount'] = $invoice['format_discount'];
            $newData['refer'] = $invoice['refer'];
            $newData['term'] = $invoice['term'];
            $newData['multi'] = $invoice['multi'];
            $newData['i_class'] = $invoice['i_class'];
            $newData['loc'] = $invoice['loc'];
            $newData['r_time'] = $invoice['r_time'];
            $newData['cust_name'] = $invoice['cust_name'];
            $newData['cust_address'] = $invoice['cust_address'];
            $newData['cust_city'] = $invoice['cust_city'];
            $newData['cust_postcode'] = $invoice['cust_postcode'];
            $newData['created_by'] = $this->aauth->get_user()->id;
            $newData['print_status'] = 0;
            $newData['driver_name'] = $invoice['driver_name'];
            $newData['vehicle_no'] = $invoice['vehicle_no'];
            $newData['pamt_terms'] = $invoice['pamt_terms'];

            $this->db->insert($invoice_table, $newData);
            $insert_id = $this->db->insert_id();

            $this->aauth->applog('[Invoice Duplicated - #' . $invoice["tid"] . '] - Customer: ' . $invoice['cust_name'] . ' - Total: ' . $invoice["total"] . ', Tax: ' . $invoice["tax"], $this->aauth->get_user()->username);
        }
        $this->db->where('tid', $ids);
        $query2 = $this->db->get('geopos_invoice_items');

        $data2 = $query2->result_array();
        foreach ($data2 as $invoice_items) {
            $inv_items['tid'] = $insert_id;
            $inv_items['pid'] = $invoice_items['pid'];
            $inv_items['cat_id'] = $invoice_items['cat_id'];
            $inv_items['cid'] = $invoice_items['cid'];
            $inv_items['product'] = $invoice_items['product'];
            $inv_items['code'] = $invoice_items['code'];
            $inv_items['qty'] = $invoice_items['qty'];
            $inv_items['price'] = $invoice_items['price'];
            $inv_items['tax'] = $invoice_items['tax'];
            $inv_items['discount'] = $invoice_items['discount'];
            $inv_items['subtotal'] = $invoice_items['subtotal'];
            $inv_items['totaltax'] = $invoice_items['totaltax'];
            $inv_items['totaldiscount'] = $invoice_items['totaldiscount'];
            $inv_items['product_des'] = $invoice_items['product_des'];
            $inv_items['i_class'] = $invoice_items['i_class'];
            $inv_items['unit'] = $invoice_items['unit'];
            $inv_items['serial'] = $invoice_items['serial'];
            $inv_items['added_on'] = datefordatabase(date());
            $this->db->insert($invoice_items_table, $inv_items);
        }

        $this->db->where('tid', $ids);
        $this->db->where('payerid', $customer_id);
        $query3 = $this->db->get('geopos_transactions');
        $data3 = $query3->result_array();
        foreach ($data3 as $invoice_trans) {
            $invoice_trans_data['acid'] = $invoice_trans['acid'];
            $invoice_trans_data['account'] = $invoice_trans['account'];
            $invoice_trans_data['type'] = $invoice_trans['type'];
            $invoice_trans_data['cat'] = $invoice_trans['cat'];
            $invoice_trans_data['credit'] = $invoice_trans['credit'];
            $invoice_trans_data['debit'] = $invoice_trans['debit'];
            $invoice_trans_data['balance'] = $invoice_trans['balance'];
            $invoice_trans_data['payer'] = $invoice_trans['payer'];
            $invoice_trans_data['payerid'] = $invoice_trans['payerid'];
            $invoice_trans_data['method'] = $invoice_trans['method'];
            $invoice_trans_data['paymt_method'] = $invoice_trans['paymt_method'];
            $invoice_trans_data['paymt_date'] = $invoice_trans['paymt_date'];
            $invoice_trans_data['date'] = datefordatabase(date('Y-m-d H:i:s'));
            $invoice_trans_data['tid'] = $insert_id;
            $invoice_trans_data['eid'] = $this->aauth->get_user()->id;
            $invoice_trans_data['note'] = "Invoice # " . $lastinvoice;
            $invoice_trans_data['ext'] = $invoice_trans['ext'];
            $invoice_trans_data['loc'] = $invoice_trans['loc'];
            $invoice_trans_data['os'] = $invoice_trans['os'];
            $invoice_trans_data['inv_id'] = $lastinvoice;
            $invoice_trans_data['payerid'] = $invoice_trans['payerid'];
            $invoice_trans_data['method'] = $invoice_trans['method'];
            $invoice_trans_data['paymt_method'] = $invoice_trans['paymt_method'];
            $invoice_trans_data['created'] = datefordatabase(date('Y-m-d H:i:s'));
            $invoice_trans_data['created_by'] = $this->aauth->get_user()->id;
            $this->db->insert($transactions_table, $invoice_trans_data);
        }
        $this->db->trans_complete();
        return "success";
    }

    public function ajax_list_customers()
    {
        $list = $this->invocies->get_datatables($this->limited);


        $data = array();
        $no = $this->input->post('start');
        $no = $this->input->post('start');
        foreach ($list as $invoices) {
            if ($invoices->status != "due") {
                continue;
            }
            if ($invoices->total > 0) {
                $no++;
                $row = array();
                $row[] = $no;

                $row[] =  $invoices->tid;
                // $row[] = $invoices->tid;
                $row[] =  $invoices->name;

                $row[] =  $invoices->company;
                $row[] =  $invoices->inv_type;
                $row[] = $this->format_invoice_date($invoices->invoicedate);
                // $row[] = $invoices->total;
                $row[] = amountExchange($invoices->item_price, 0, $this->aauth->get_user()->loc);
                // Fix status display with proper language lookup and fallback
                $row[] = '<span class="st-' . $invoices->status . '">' . $this->format_status_text($invoices->status) . '</span>';
                $row[] = '<a href="' . base_url("invoices/edit_invoices?id=$invoices->id") . '" target="_blank" class="btn btn-info btn-sm" title="Stock Return"><i class="fa fa-exchange" aria-hidden="true"></i></a>';
                $data[] = $row;
            }
        }
        $output = array(
            "draw" => $this->input->post('draw'),
            "recordsTotal" => $this->invocies->count_all($this->limited),
            "recordsFiltered" => $this->invocies->count_filtered($this->limited),
            "data" => $data,
        );
        //output to json format
        if (ob_get_level()) {
            ob_clean();
        }
        $this->output->set_content_type('application/json');
        echo json_encode($output);
    }

    public function sendInvoiceEmail()
    {
        $email = $this->input->post('email');
        $name = $this->input->post('name');
        $subject = $this->input->post('subject');
        $message = $this->input->post('message');
        $attachment = $this->input->post('attachment');
        $attachmenttrue = !empty($attachment) ? true : false;
        if ($this->communication_model->send_corn_email($email, $name, $subject, $message, $attachmenttrue, $attachment)) {
            echo json_encode(['status' => 'success', 'message' => 'Email sent successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to send email.']);
        }
    }

    public function ajax_list_bfr_post()
    {
        $expression = $this->input->post('expression');
        $expression2 = $this->input->post('expression2');
        $value = $this->input->post('value');
        $value2 = $this->input->post('value2');
        $joinQuery = $this->input->post('joinQuery');
        $_invoices = $this->input->post('invoices');
        $driver = $this->input->post('driver');
        $company = $this->settings->company_details(1);

        // Ensure no output before JSON and set proper content type
        if (ob_get_level()) {
            ob_clean();
        }
        $this->output->set_content_type('application/json');

        $list = $this->invocies->get_datatables_bfr_post($this->limited, $expression, $expression2, $value, $value2, $joinQuery, $_invoices, $driver);
        $data = array();
        $no = $this->input->post('start');
        foreach ($list as $invoices) {
            $no++;
            $row = array();
            $row[] = $no;
            if ($invoices->print_status == 1) {

                $printed = 'Yes';
            } else {
                $printed = ' ';
            }
            $row[] = $invoices->tid;
            // $row[] =  $invoices->name ;
            $row[] =  $this->aauth->permission_new(null, 'salesEditInvoice') ? '<span data-id="' . $no . '" data-link="' . base_url("invoices/edit_bfr_posting?id=$invoices->id") . '&d=1" >' . $invoices->name . '</span>' : '<span>' . $invoices->name . '</span>';

            $row[] =  $invoices->company;
            $row[] =  $invoices->inv_type;

            // Format Invoice Date using helper method
            $row[] = $this->format_invoice_date($invoices->invoicedate);
            $row[] = amountExchange($invoices->tax, 0, $this->aauth->get_user()->loc);
            $row[] = amountExchange($invoices->total, 0, $this->aauth->get_user()->loc);
            $row[] = '<span class="st-' . $invoices->status . '">' . $this->format_status_text($invoices->status) . '</span>';
            $row[] = $printed;
            $row[] = '<input type="checkbox"  class="checkbox"  name="receipt" data-id="' . $invoices->id . '" value="' . $invoices->id . '">';
            // Build action buttons based on permissions
            $action_buttons = '';

            // Print Invoices permission
            if ($this->aauth->permission_new(null, 'salesPrintInvoices')) {
                $action_buttons .= '<a href="' . base_url("invoices/printinvoice_bfr_posting" . (($company['invoice_format'] == 1) ? '' : $company['invoice_format']) . "?id=$invoices->id") . '&d=1&no_print=1" target="_blank" class="btn btn-primary btn-sm" title="Sale Invoice"><i class="fa fa-file-pdf-o" aria-hidden="true"></i></a> ';
                $action_buttons .= '<a href="' . base_url("invoices/counter_invoice_bfr_posting?id=$invoices->id") . '&d=1&no_print=1" target="_blank" class="btn btn-info btn-sm" title="Counter Collection"><i class="fa fa-building" aria-hidden="true"></i></a> ';
                $action_buttons .= '<a href="' . base_url("invoices/quotattion_invoice_bfr_posting?id=$invoices->id") . '&d=1&no_print=1" target="_blank" class="btn btn-warning btn-sm" title="Quotation INVOICE"><i class="fa fa-file-text" aria-hidden="true"></i></a> ';
                $action_buttons .= '<a href="' . base_url("invoices/loadingslip?id=$invoices->id") . '&d=1&no_print=1" target="_blank" class="btn btn-secondary btn-sm" title="Loading Slip"><i class="fa fa-book" aria-hidden="true"></i></a> ';
            }

            // Post Invoice permission
            if ($this->aauth->permission_new(null, 'salesSelectPostInvoice')) {
                $action_buttons .= '<a href="#" data-object-id="' . $invoices->id . '" class="btn btn-success btn-sm post_invoices" title="Post Invoice"><i class="fa fa-database" aria-hidden="true"></i></a> ';
            }

            // Delete Invoice permission
            if ($this->aauth->permission_new(null, 'salesDeleteInvoice')) {
                $action_buttons .= '<a href="#" data-object-id="' . $invoices->id . '" class="btn btn-danger btn-sm delete-invoice-bfr-post" title="Delete Invoice"><i class="fa fa-trash" aria-hidden="true"></i></a>';
            }

            $row[] = $action_buttons;
            $data[] = $row;
        }


        $output = array(
            "draw" => $this->input->post('draw'),
            "recordsTotal" => $this->invocies->count_all_bfr_post($this->limited),
            "recordsFiltered" => $this->invocies->count_filtered_bfr_post($this->limited),
            "data" => $data,
        );
        //output to json format
        if (ob_get_level()) {
            ob_clean();
        }
        $this->output->set_content_type('application/json');
        echo json_encode($output);
    }

    public function ajax_list()
    {
        $param = $this->input->post('param');
        $due_date = $this->input->post('due_date');
        $auto_post = $this->settings->auto_post();
        $data = array();
        $no = $this->input->post('start');
        $expression = $this->input->post('expression');
        $expression2 = $this->input->post('expression2');
        $value = $this->input->post('value');
        $value2 = $this->input->post('value2');
        $joinQuery = $this->input->post('joinQuery');
        $_invoices = $this->input->post('invoices');
        $driver = $this->input->post('driver');
        $company = $this->settings->company_details(1);
        // DataTables pagination params with sane defaults
        $start = (int)$this->input->post('start');
        $lengthInput = (int)$this->input->post('length');
        $DEFAULT_LENGTH = 25;
        $MAX_LENGTH = 100;
        $length = $lengthInput > 0 ? $lengthInput : $DEFAULT_LENGTH;
        $length = $length > $MAX_LENGTH ? $MAX_LENGTH : $length;

        // Fetch list
        $list = $this->invocies->get_datatables($this->limited, $expression, $expression2, $value, $value2, $joinQuery, $_invoices, $driver, $param, $due_date);
        foreach ($list as $invoices) {
            if ($invoices->total > 0) {
                $no++;
                $row = array();
                $row[] = $no;
                $invoiceFormat = ($company['invoice_format'] == 1) ? '' : $company['invoice_format'];
                $shareLink = base_url("billing/invoice" . $invoiceFormat . "?id=$invoices->id") .
                    '&token=' . hash_hmac('ripemd160', 's' . $invoices->id, $this->config->item('encryption_key'));

                $companyEmail = $company['email'];

                $tid = ($auto_post && $this->aauth->permission_new(null, 'salesEditInvoice'))
                    ? '<a href="' . base_url("invoices/edit?id=$invoices->id") . '" target="_blank">' . $invoices->tid . '</a>'
                    : $invoices->tid;

                $invoiceLink = base_url("invoices/view_invoice?id={$invoices->id}&d=1");

                $row[] = $tid;
                $row[] = '<span data-id="' . $no . '" data-link="' . $invoiceLink . '">' . $invoices->name . '</span>';
                $row[] = $invoices->company;
                $row[] = $invoices->inv_type;
                $row[] = $this->format_invoice_date($invoices->invoicedate);
                $row[] = amountExchange($invoices->tax, 0, $this->aauth->get_user()->loc);
                $row[] = amountExchange((float)$invoices->total - (float)$invoices->tax, 0, $this->aauth->get_user()->loc);
                $row[] = amountExchange($invoices->total, 0, $this->aauth->get_user()->loc);
                // Fix status display with proper language lookup and fallback
                $row[] = '<span class="st-' . $invoices->status . '">' . $this->format_status_text($invoices->status) . '</span>';
                $row[] = $invoices->print_status == 1 ? 'Yes' : ' ';

                // Checkbox for receipt selection
                $row[] = '<input type="checkbox" class="checkbox" name="receipt" data-id="' . $invoices->id . '" value="' . $invoices->id . '">';

                // Action buttons: Download, Duplicate, Share, Delete
                $row[] = '
                    ' . ($this->aauth->permission_new(null, 'salesPrintInvoices') ? '<a href="' . base_url("invoices/printinvoice" . $invoiceFormat . "?id=$invoices->id") . '&d=1&no_print=1" target="_blank" class="btn btn-primary btn-sm" title="Sale Invoice">
                        <i class="fa fa-file-pdf-o" aria-hidden="true"></i>
                    </a>
                    <a href="' . base_url("invoices/counter_invoice?id=$invoices->id") . '&d=1&no_print=1" target="_blank" class="btn btn-info btn-sm" title="Counter Collection">
                        <i class="fa fa-building" aria-hidden="true"></i>
                    </a>
                    <a href="' . base_url("invoices/quotattion_invoice?id=$invoices->id") . '&d=1&no_print=1" target="_blank" class="btn btn-warning btn-sm" title="Quotation INVOICE">
                        <i class="fa fa-file-text" aria-hidden="true"></i>
                    </a>' : '') .
                    ($this->aauth->permission_new(null, 'salesDuplicateInvoice') ? '<a href="#" data-object-id="' . $invoices->id . '" class="btn btn-success btn-sm duplicate_inv">
                        <i class="fa fa-copy" aria-hidden="true" title="Duplicate Invoice"></i>
                    </a>' : '') .
                    ($this->aauth->permission_new(null, 'salesShareInvoice') ? '<a href="#" class="btn btn-secondary btn-sm share-btn"
                        data-id="' . $invoices->id . '" 
                        data-link="' . $shareLink . '" 
                        data-email="' . $companyEmail . '" 
                        data-toggle="modal" 
                        data-target="#shareModal">
                        <i class="fa fa-share-alt" aria-hidden="true" title="Share Invoice"></i>
                    </a>' : '') .
                    ($this->aauth->permission_new(null, 'salesDeleteInvoice') ? '<a href="#" data-object-id="' . $invoices->id . '" class="btn btn-danger btn-sm delete-object">
                        <i class="fa fa-trash" aria-hidden="true" title="Delete Invoice"></i>
                    </a>' : '') .
                    '';

                $data[] = $row;
            }
        }


        // Provide correct DT response using filtered count with same params
        $recordsTotal = $this->invocies->count_all($this->limited);
        $recordsFiltered = $this->invocies->count_filtered_with_params($this->limited, $expression, $expression2, $value, $value2, $joinQuery, $_invoices, $driver, $param, $due_date);
        $output = array(
            "draw" => intval($this->input->post('draw')),
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $recordsFiltered,
            "data" => $data
        );

        // Ensure no output before JSON and set proper content type
        if (ob_get_level()) {
            ob_clean();
        }
        $this->output->set_content_type('application/json');
        echo json_encode($output);
    }

    public function print_list()
    {
        $list = $this->invocies->get_datatables_for_merged($this->limited);
        $data = array();
        $no = $this->input->post('start');
        $items = 0;
        $i = 0;
        foreach ($list as $invoices) {
            if ($invoices->total > 0) {
                $no++;
                $items++;
                $i++;
                $row = array();
                $row[] =  $invoices->tid;
                $row[] =  '<span data-id="' . $invoices->tid . '" data-link="' . base_url("invoices/view_invoice?id=$invoices->id") . '&d=1" >' . $invoices->name . '</span>';
                $row[] =  $invoices->name;
                $row[] =  $invoices->company;
                $row[] =  $invoices->inv_type;
                $row[] = $this->format_invoice_date($invoices->invoicedate);
                $row[] = amountExchange($invoices->total, $invoices->multi, $invoices->loc);
                // Fix status display with proper language lookup and fallback
                $row[] = '<span class="st-' . $invoices->status . '">' . $this->format_status_text($invoices->status) . '</span>';
                $row[] = '<input type="checkbox"  class="checkbox"  name="receipt" data-id="' . $invoices->id . '" value="' . $invoices->id . '">';
                $data[] = $row;
            }
        }
        $output = array(
            "draw" => $this->input->post('draw'),
            "recordsTotal" => $items,
            "recordsFiltered" => $items,
            "data" => $data,
        );
        echo json_encode($output);
    }

    public function view_invoice()
    {

        $this->load->model('accounts_model');
        $data['acclist'] = $this->accounts_model->accountslist();
        $tid = intval($this->input->get('id'));

        $data['id'] = $tid;
        $data['invoice'] = $this->invocies->purchase_details($tid);
        $data['products'] = $this->invocies->purchase_products($tid);
        $data['activity'] = $this->invocies->purchase_transactions($tid);
        $data['attach'] = $this->invocies->attach($tid);
        $data['employee'] = $this->invocies->employee($data['invoice']['eid']);
        $head['usernm'] = $this->aauth->get_user()->username;
        $head['title'] = "Customer Invoice " . $data['invoice']['iid'];
        $data['company'] = $this->settings->company_details(1);
        if (($data['invoice']['i_class'] != 2 && $this->aauth->permission_new(null, 'salesManageInvoices')) or ($data['invoice']['i_class'] == 2 && $this->aauth->permission_new(null, 'salesManageInvoices'))) {
            $this->load->view('fixed/header', $head);
            if ($data['invoice']['tid']) $this->load->view('invoices/customer_invoice_view', $data);
            $this->load->view('fixed/footer');
        }
    }


    public function daybook()
    {
        $head['title'] = "Day Book";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $this->load->view('invoices/daybook');
        $this->load->view('fixed/footer');
    }
    public function vat_collect()
    {
        $head['title'] = "Vat Collection";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $this->load->view('invoices/vat_collect');
        $this->load->view('fixed/footer');
    }
    public function payments_recieved_report()
    {
        $company = $this->settings->company_details(1);
        ini_set('memory_limit', '-1');
        set_time_limit(0);
        $data = $this->invocies->invoices_export();

        $data_tr = "";
        $start_date = $_POST['hidden_start_date'];
        $end_date = $_POST['hidden_end_date'];

        foreach ($data as $key => $row) {
            $total_sub += $row["debit"];
            if ($row["paymt_method"] == "Bank Transfer") {

                $total_bank_payment += $row["debit"];
            }
            if ($row["paymt_method"] == "Cash") {

                $total_cash_payment += $row["debit"];
            }
            if ($row["paymt_method"] == "Card Payment") {

                $total_card_payment += $row["debit"];
            }
        }


        ob_end_clean();
        $no = 1;
        foreach ($data as $key => $row) {
            $data_tr .= '
        <tr>
        <td>' . $no++ . '</td>';
            $data_tr .= '
        <td>' . $row["date"] . '</td>
        <td>' . $row["paymt_method"] . '</td>
        <td>' . $row["payer"] . '</td>
        <td>' . amountExchange_s($row["debit"]) . '</td>
        ';

            $data_tr .= '</tr>';
        }



        $date = date("d/m/y");
        $time = date("h:i:s");
        $html = "
            <html>
            <head>
                <style>
                    body{font-family: 'Times New Roman', Times, serif;}
                    @page { margin: 110px 50px; }
                    #header{ position: fixed;  top: -70px;}
                    .left_header{float:left;}
                    .center_header{margin-left:70px;width:450px}
                    .right_header{position: fixed;  top: -70px;left:630px;}
                    .pagenum:after {content: counter(page)}
                    table{width:100%;}
                </style>
            </head>
            <body>
                <div id='header'>
                <div class='left_header'>
                    <div><b>Date: $date</b></div>
                    <div><b>Time: $time</b></div>
                </div>
                <div style='font-size:18px;text-align:center;' class='center_header'>
                    <div>
                    <u><b>" . $company['cname'] . " </b>  </u>
                    </div>
                    <div >
                        <b><u> Payments Recieved (Summary)</u></b> 
                    </div>
                </div >
                <div class='right_header'>
                    <b><span class='pagenum'>Page:</span></b>
                </div>
                </div>
                <div style='font-size:13px;'>
                    <div  style='float:left'>
                        <div><b>Date From:</b> $start_date</div>
                        <div><b>Date To:</b> $end_date</div>
                    </div>
                    <div style='float:right; margin-right:100px'>
                        <div><b>Customer From:</b></div>
                        <div><b>Customer To:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ZZZZZZZZ</b></div>
                    </div>
                    <div style='clear:both'>
                    <br>
                    <div  style='float:left'>
                        <div><b>Transaction From:</b> 1</div>
                        <div><b>Transaction To:</b> 99,999,999</div>
                    </div>
                    <div style='float:right; margin-right:100px'>
                        <div><b>Total Cash Payment: $total_cash_payment</b></div>
                        <div><b>Total Bank Payment:$total_bank_payment</b></div>
                        <div><b>Total Card Payment:$total_card_payment</b></div>
                    </div>
                </div>

                <div style='clear:both'>
                </div>
            <br>
            <table>
            <thead  style='font-size:13px;'>
                <tr>
                    <td>
                    <u> No</u>
                    </td>
                    <td>
                    <u> Date</u>
                    </td>
                    <td>
                    <u> Method</u>
                    </td>
                    <td>
                    <u> AC/ID</u>
                    </td>
                    <td>
                    <u> Amount</u>
                    </td>
                </tr>
            </thead>
            <tbody style='font-size:13px;'>
            $data_tr
            </tbody>
            <tr>
            <td></td>
            <td></td>
            <td></td>
            <td style=''> Total Payment Recieved:</td>

            <td style='border-top:2px solid black;border-bottom:2px solid black'> " . amountExchange_s($total_sub) . "</td>
            </tr>
            </tr>
            </table>
            </body>
            </html>
            ";
        if ($this->input->post('excel')) {
            $excelData = [['Date', 'Method', 'AC/ID', 'Amount']];
            foreach ($data as $row) {
                array_push(
                    $excelData,
                    [$row["date"], $row["paymt_method"], $row["payer"], amountExchange_s($row["debit"])]
                );
            }
            // Add total row
            array_push($excelData, ['', '', 'Total Payment Recieved:', amountExchange_s($total_sub)]);
            $this->exportTableToExcel($excelData);
        } else {
            $this->dpdf->loadHtml($html);
            $this->dpdf->render();
            $this->dpdf->stream("" . "Payment Recieved Report" . ".pdf", array("Attachment" => 0));
        }
    }


    function exportTableToExcel($tableData)
    {
        // Load the download helper
        $this->load->helper('download');

        $writer = new XLSXWriter();
        foreach ($tableData as $row) {
            $writer->writeSheetRow('Sheet1', $row);
        }
        $file_name = 'Payment_Received_' . time() . '.xlsx';
        $file_path = 'userfiles/documents/' . $file_name;
        $writer->writeToFile($file_path);

        $data = file_get_contents($file_path);
        force_download($file_name, $data);
    }


    public function payments_report()
    {

        $head['title'] = "Payment Recieved Report";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $this->load->view('invoices/payments_recieved');
        $this->load->view('fixed/footer');
    }

    public function daybook_export()
    {
        $company = $this->settings->company_details(1);
        ini_set('memory_limit', '-1');
        set_time_limit(0);
        $data = $this->invocies->daybook_export();

        $data_tr = "";
        $start_date = $_POST['hidden_start_date'];
        $end_date = $_POST['hidden_end_date'];

        $netsubtotal = 0;
        $subtotal = 0;
        ob_end_clean();
        $no = 1;
        foreach ($data as $key => $row) {
            $subtotal = $row["subtotal"] - $row["tax"];
            $netsubtotal += $subtotal;
            $data_tr .= '
        <tr>
        <td>' . $no++ . '</td>';
            if ($row["tid"] == 0) {
                $data_tr .= '<td>1</td>';
            } else {
                $data_tr .= '<td>' . round($row["items"]) . '</td>';
            }
            $data_tr .= '<td>SI</td>
        <td>' . $row["invoicedate"] . '</td>
        <td>' . $row["name"] . '</td>
        <td>' . $row["tid"] . '</td>';


            if ($row["tid"] == 0) {
                $data_tr .= '<td>Opening Balance</td><td>0.00</td><td>' . amountExchange_s(($row["subtotal"] - $row["tax"])) . '</td>';
            } else {
                $data_tr .= '<td>' . amountExchange_s($subtotal) . '</td><td>' . amountExchange_s($row["tax"]) . '</td><td>' . amountExchange_s($row["subtotal"]) . '</td>';
            }
            $data_tr .= '</tr>';
        }

        $total_qty = 0;
        $total_sub = 0;
        $total_tax = 0;
        foreach ($data as $key => $row) {

            if ($row["tid"] == 0) {
                $total_sub += ($row["subtotal"] - $row["tax"]);
            } else {
                $total_sub += $row["subtotal"];
                $total_tax += $row["tax"];
            }
        }


        $date = date("d/m/y");
        $time = date("h:i:s");
        $html = "
<html>
<head>
    <style>
        body{font-family: 'Times New Roman', Times, serif;}
        @page { margin: 110px 50px; }
        #header{ position: fixed;  top: -70px;}
        .left_header{float:left;}
        .center_header{margin-left:70px;width:450px}
        .right_header{position: fixed;  top: -70px;left:630px;}
        .pagenum:after {content: counter(page)}
        table{width:100%;}
    </style>
</head>
<body>
    <div id='header'>
       <div class='left_header'>
          <div><b>Date: $date</b></div>
          <div><b>Time: $time</b></div>
      </div>
      <div style='font-size:17px;text-align:center;' class='center_header'>
          <div>
           <u><b>" . $company['cname'] . " </b>  </u>
        </div>
        <div >
            <b><u> Day Books: Customer Invoices (Summary)</u></b> 
        </div>
      </div >
      <div class='right_header'>
        <b><span class='pagenum'>Page:</span></b>
      </div>
    </div>
    <div style='font-size:13px;'>
        <div  style='float:left'>
            <div><b>Date From:</b> $start_date</div>
            <div><b>Date To:</b> $end_date</div>
        </div>
        <div style='float:right; margin-right:100px'>
            <div><b>Customer From:</b></div>
            <div><b>Customer To:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ZZZZZZZZ</b></div>
        </div>
        <div style='clear:both'>
        <br>
         <div  style='float:left'>
            <div><b>Transaction From:</b> 1</div>
            <div><b>Transaction To:</b> 99,999,999</div>
        </div>
    </div>

    <div style='clear:both'>
    </div>
<br>
<table>
  <thead  style='font-size:13px;'>
    <tr>
        <td>
           <u> Tran&nbsp;No</u>
        </td>
        <td>
           <u> Items</u>
        </td>
        <td>
           <u> Tp</u>
        </td>
        <td>
           <u> Date</u>
        </td>
        <td>
           <u> A/C&nbsp;Ref</u>
        </td>
        <td>
           <u> Inv&nbsp;Ref</u>
        </td>
        <td>
           <u> Net&nbsp;Amount</u>
        </td>
        <td>
           <u> Vat&nbsp;Amount</u>
        </td>
        <td>
           <u> Gross&nbsp;Amount</u>
        </td>
    </tr>
</thead>
<tbody style='font-size:13px;'>
$data_tr
</tbody>
<tr>
<td></td>
<td></td>
<td></td>
<td></td>
<td></td>
<td style=''> Total:</td>
<td style='border-top:2px solid black;border-bottom:2px solid black'> " . amountExchange_s($netsubtotal) . "</td>
<td style='border-top:2px solid black;border-bottom:2px solid black'> " . amountExchange_s($total_tax) . "</td>
<td style='border-top:2px solid black;border-bottom:2px solid black'> " . amountExchange_s($total_sub) . "</td>
</tr>
</table>
</body>
</html>
";
        $this->dpdf->loadHtml($html);
        $this->dpdf->render();
        $this->dpdf->stream("" . "Sale Report" . ".pdf", array("Attachment" => 0));
    }

    public function bk_ajax_sale_list()
    {
        $list = $this->invocies->bk_get_datatables($this->limited);
        $data = array();

        $no = $this->input->post('start');
        foreach ($list as $invoices) {
            $no++;
            $row = array();
            $row[] =  $invoices->tid;
            $row[] =  $invoices->name;
            $row[] =  $invoices->company;
            $row[] = $this->format_invoice_date($invoices->invoicedate);
            $row[] = $this->format_invoice_date($invoices->invoiceduedate);
            $row[] = amountExchange($invoices->total, 0, $this->aauth->get_user()->loc);
            $row[] = '<input type="checkbox" class="checkbox"  name="receipt" data-id="' . $invoices->id . '" value="' . $invoices->id . '">';
            $data[] = $row;
        }
        $output = array(
            "draw" => $this->input->post('draw'),
            "recordsTotal" => $this->invocies->count_all($this->limited),
            "recordsFiltered" => $this->invocies->bk_count_filtered($this->limited),
            "data" => $data,
        );
        echo json_encode($output);
    }
    public function vat_collect_export()
    {
        $company = $this->settings->company_details(1);
        ini_set('memory_limit', '-1');
        set_time_limit(0);
        $data = $this->invocies->daybook_export();

        $data_tr = "";
        $start_date = $_POST['hidden_start_date'];
        $end_date = $_POST['hidden_end_date'];

        $netsubtotal = 0;
        $subtotal = 0;
        ob_end_clean();
        $no = 1;
        foreach ($data as $key => $row) {
            $subtotal = $row["subtotal"] - $row["tax"];
            $netsubtotal += $subtotal;
            $data_tr .= '
        <tr>
        <td>' . $no++ . '</td>';
            if ($row["tid"] == 0) {
                $data_tr .= '<td>1</td>';
            } else {
                $data_tr .= '<td>' . round($row["items"]) . '</td>';
            }
            $data_tr .= '<td>SI</td>
        <td>' . $row["invoicedate"] . '</td>
        <td>' . $row["name"] . '</td>
        <td>' . $row["tid"] . '</td>
        <td>' . amountExchange_s($row['tax']) . '</td>';
            // <td>' . $row["tax"] . '</td>';


            // if ($row["tid"] == 0) {
            //     $data_tr .= '<td>Opening Balance</td><td>0.00</td><td>' . ($row["subtotal"] - $row["tax"]) . '</td>';
            // } else {
            // $data_tr .= '<td>' . $row["tax"] . '</td>';
            // }
            $data_tr .= '</tr>';
        }

        $total_qty = 0;
        $total_sub = 0;
        $total_tax = 0;
        foreach ($data as $key => $row) {

            if ($row["tid"] == 0) {
                $total_sub += ($row["subtotal"] - $row["tax"]);
            } else {
                $total_sub += $row["subtotal"];
                $total_tax += $row["tax"];
            }
        }


        $date = date("d/m/y");
        $time = date("h:i:s");
        $html = "
<html>
<head>
    <style>
        body{font-family: 'Times New Roman', Times, serif;}
        @page { margin: 110px 50px; }
        #header{ position: fixed;  top: -70px;}
        .left_header{float:left;}
        .center_header{margin-left:70px;width:450px}
        .right_header{position: fixed;  top: -70px;left:630px;}
        .pagenum:after {content: counter(page)}
        table{width:100%;}
    </style>
</head>
<body>
    <div id='header'>
       <div class='left_header'>
          <div><b>Date: $date</b></div>
          <div><b>Time: $time</b></div>
      </div>
      <div style='font-size:17px;text-align:center;' class='center_header'>
          <div>
           <u><b>" . $company['cname'] . " </b>  </u>
        </div>
        <div >
            <b><u> Vat Collection: Customer Invoices (Summary)</u></b> 
        </div>
      </div >
      <div class='right_header'>
        <b><span class='pagenum'>Page:</span></b>
      </div>
    </div>
    <div style='font-size:13px;'>
        <div  style='float:left'>
            <div><b>Date From:</b> $start_date</div>
            <div><b>Date To:</b> $end_date</div>
        </div>
        <div style='float:right; margin-right:100px'>
            <div><b>Customer From:</b></div>
            <div><b>Customer To:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ZZZZZZZZ</b></div>
        </div>
        <div style='clear:both'>
        <br>
         <div  style='float:left'>
            <div><b>Transaction From:</b> 1</div>
            <div><b>Transaction To:</b> 99,999,999</div>
        </div>
    </div>

    <div style='clear:both'>
    </div>
<br>
<table>
  <thead  style='font-size:13px;'>
    <tr>
        <td>
           <u> Tran&nbsp;No</u>
        </td>
        <td>
           <u> Items</u>
        </td>
        <td>
           <u> Tp</u>
        </td>
        <td>
           <u> Date</u>
        </td>
        <td>
           <u> A/C&nbsp;Ref</u>
        </td>
        <td>
           <u> Inv&nbsp;Ref</u>
        </td>
        <td>
           <u> Vat&nbsp;Amount</u>
        </td>
    </tr>
</thead>
<tbody style='font-size:13px;'>
$data_tr
</tbody>
<tr>
<td></td>
<td></td>
<td></td>
<td></td>
<td></td>
<td style=''> Totals:</td>
<td style='border-top:2px solid black;border-bottom:2px solid black'>" . amountExchange_s($total_tax) . " </td>
</tr>
</table>
</body>
</html>
";
        $this->dpdf->loadHtml($html);
        $this->dpdf->render();
        $this->dpdf->stream("" . "Sale Report" . ".pdf", array("Attachment" => 0));
    }


    function ProductList($invoice_id)
    {
        $list = $this->invocies->get_invoice_datatables($invoice_id);
        $data = '';
        $no = 0;
        $data = '<table class="table table-striped table-bordered zero-configuration "> <thead> <th> Sr# </th> <th> Product Code </th><th> Product Description </th>  <th> Qty </th> <th> Sub Total </th> <th> Total VAT</th>  </thead>';
        foreach ($list as $invoices) {

            $data .= '<tr>';
            $no++;
            $row = array();
            $data .= '<td>' . $no . '</td>';


            $data .= '<td>' . $invoices->code . '</td>';
            $data .= '<td>' . $invoices->product_des . '</td>';
            $data .= '<td>' . $invoices->qty . '</td>';
            $data .= '<td>' . amountExchange($invoices->subtotal, 0, $this->aauth->get_user()->loc) . '</td>';
            $data .= '<td>' . amountExchange($invoices->totaltax, 0, $this->aauth->get_user()->loc) . '</td>';

            $data .= '</tr>';
        }
        $data .= '</table>';
        //output to json format
        echo $data;
    }



    public function ajax_bulk_invoices()
    {
        $list = $this->invocies->get_datatables_daypass($this->limited, $this->input->post('type'), $this->input->post('status'));
        $data = array();
        $no = $this->input->post('start');
        $no = $this->input->post('start');
        foreach ($list as $invoices) {
            $no++;
            $row = array();
            $row[] = $no;
            if ($invoices->print_status == 1) {

                $printed = 'Yes';
            } else {
                $printed = ' ';
            }

            $row[] = $invoices->tid;
            $row[] =  $invoices->name;

            $row[] =  $invoices->company;
            $row[] =  $invoices->inv_type;
            $row[] = $this->format_invoice_date($invoices->invoicedate);
            $row[] = amountExchange($invoices->total, 0, $this->aauth->get_user()->loc);
            $row[] = '<span class="st-' . $invoices->status . '">' . $this->format_status_text($invoices->status) . '</span>';
            $row[] = $printed;
            $row[] = '<input type="checkbox"  class="checkbox"  name="receipt" data-id="' . $invoices->id . '" value="' . $invoices->id . '">';
            $row[] = '<a href="' . base_url("invoices/printinvoice?id=$invoices->id") . '&d=1" class="btn btn-info btn-sm"  title="Download"><span class="fa fa-download"></span></a> <a href="#" data-object-id="' . $invoices->id . '" class="btn btn-danger btn-sm delete-object"><span class="fa fa-trash"></span></a>';
            $data[] = $row;
        }
        $output = array(
            "draw" => $this->input->post('draw'),
            "recordsTotal" => $this->invocies->count_all($this->limited),
            "recordsFiltered" => $this->invocies->count_filtered($this->limited),
            "data" => $data,
        );
        //output to json format
        echo json_encode($output);
    }

    public function bulk_delete_daypass()
    {
        if ($this->input->post()) {
            $ids =  $this->input->post('ids');
            foreach ($ids as $id) {
                // $this->db->where('tid', 0);
                $this->db->where('id', $id);
                // $this->db->where('inv_type', 'DAYPASS');
                $this->db->delete('geopos_invoices');

                $query = $this->db->get('geopos_invoices');
                // var_dump($query);exit(0);
                foreach ($query->result() as $row) {
                    $this->db->insert('geopos_invoices_history', $row);
                }

                // $this->db->where('tid', 0);
                $this->db->where('id', $id);
                // $this->db->where('inv_type', 'DAYPASS');
                $this->db->delete('geopos_invoices');

                $this->db->where('tid', $id);
                $query = $this->db->get('geopos_invoice_items');
                foreach ($query->result() as $row) {
                    $this->db->insert('geopos_invoice_items_history', $row);
                }

                $this->db->where('tid', $id);
                $this->db->delete('geopos_invoice_items');

                $this->db->where('tid', $id);
                $this->db->delete('geopos_transactions');
            }
            $ids_str = implode(", ", $ids);
            $this->aauth->applog("[Day Pass Bulk Deletion] - (ID: $ids_str)", $this->aauth->get_user()->username);
        }
    }


    public function ajax_list_rec()
    {
        $id =  $this->input->post('id');
        $list = $this->invocies->get_rec_datatables($id);
        $data = array();
        $no = $this->input->post('start');

        $k = 0;
        foreach ($list as $invoices) {
            if ($invoices->total > 0 && $invoices->total - $invoices->pamnt > 0) {
                $remaining = ($invoices->total - $invoices->pamnt);
                $no++;
                $row = array();
                $row[] = '<tr><td>' . $no . '</td>';
                //$row[] = '<td>'. $invoices->tid.'</td>';
                $row[] = '<td><a target="_blank" href="' . base_url("invoices/printinvoice?id=" . $invoices->id) . '">' . $invoices->tid . '</a></td>';
                $row[] = '<td>' . $invoices->name . '</td>';
                $row[] = '<td>' . $invoices->inv_type . '</td>';
                $row[] = '<td>' . $this->format_invoice_date($invoices->invoicedate) . '</td>';
                $row[] = '<td>' . $this->format_invoice_date($invoices->invoiceduedate) . '</td>';

                $row[] = '<td>' . amountExchange($invoices->total, 0, $this->aauth->get_user()->loc) . '</td>';


                $row[] = '<td>' . amountExchange($remaining, 0, $this->aauth->get_user()->loc) . '</td>';
                $row[] = '<td><input  type="number" step="any" min="0"  max="' . $remaining . '"  autocomplete="off" name="receipt_amount" onkeypress="return isNumber(event)" onkeyup=" rowsTotal(' . $k . '), billsUpyog(), updatePID(' . $invoices->id . ',' . $k . ')" id="receipt_amount' . $k . '"> </td>';
                //    $row[] = '<td><select name="method[]" id="method">
                // <option value="Bank Transfer">Bank Transfer</option>
                // <option value="cash">Cash</option></select></td>';
                // $row[] = '<td><input  type="date" step="any" min="0"  max="'.$remaining.'"  autocomplete="off" name="end_date[]" onkeypress="return isNumber(event)" onkeyup=" rowsTotal('.$k.'), billsUpyog(), updatePID('.$invoices->id.','.$k.')" id="receipt_amount'.$k.'"> </td>';
                $row[] = '<td><input type="checkbox" class="checkbox" onchange="doalert(this,' . $k . '),rowsTotal(' . $k . '), billsUpyog()" name="receipt" data-id="' . $invoices->tid . '" value="' . $remaining . '"  ></td> </tr>
			 
                                <input type="hidden" class="ttInput" name="product_subtotal[]" id="total-' . $k . '" value="0">
                                <input type="hidden" class="pdIn" name="pid[]" id="pid-' . $k . '" value="' . $invoices->id . '">
                                <input type="hidden" name="customers[]"  value="' . $invoices->csd . '">
                                <input type="hidden" name="hsn[]" id="hsn-' . $k . '" value="' . $invoices->name . '">
                                <input type="hidden" name="serial[]" id="serial-' . $k . '" value="">';
                //$row[] = '<a href="' . base_url("invoices/view?id=$invoices->id") . '" class="btn btn-success btn-sm" title="View"><i class="fa fa-eye"></i></a>&nbsp;<a href="' . base_url("invoices/printinvoice?id=$invoices->id") . '&d=1" class="btn btn-info btn-sm"  title="Download"><span class="fa fa-download"></span></a> <a href="#" data-object-id="' . $invoices->id . '" class="btn btn-danger btn-sm delete-object"><span class="fa fa-trash"></span></a>';
                $data[] = $row;
                $k++;
            }
        }
        if ($data == null) {
            echo 'No Record Found';
        } else {
            //output to json format
            print_r($data);
        }
    }


    public function view()
    {
        $this->load->model('accounts_model');
        $data['acclist'] = $this->accounts_model->accountslist((int)$this->aauth->get_user()->loc);
        $tid = $this->input->get('id');
        $data['invoice'] = $this->invocies->invoice_details($tid, $this->limited);
        $data['attach'] = $this->invocies->attach($tid);
        $data['c_custom_fields'] = $this->custom->view_fields_data($data['invoice']['cid'], 1);
        $head['usernm'] = $this->aauth->get_user()->username;
        $head['title'] = "Invoice " . $data['invoice']['tid'];
        $this->load->view('fixed/header', $head);
        $data['products'] = $this->invocies->invoice_products($tid);
        if ($data['invoice']['id']) $data['activity'] = $this->invocies->invoice_transactions($tid);
        $data['employee'] = $this->invocies->employee($data['invoice']['eid']);
        $data['custom_fields'] = $this->custom->view_fields_data($tid, 2);
        if ($data['invoice']['id']) {
            $data['invoice']['id'] = $tid;
            $this->load->view('invoices/view', $data);
        }
        $this->load->view('fixed/footer');
    }

    public function loadingslip($printing = true, $id = -1)
    {
        $company = $this->settings->company_details(1);

        $tid = $this->input->get('id');

        $no_print = $this->input->get('no_print');
        if ($no_print) {
            $no_print = $no_print;
        }
        $data['id'] = $tid;
        $data['invoice'] = $this->invocies->invoice_details_bfr_post($tid, $this->limited);
        if ($data['invoice']['id']) $data['products'] = $this->invocies->invoice_products_bfr_post($tid);
        // var_dump($data['products']);  die();
        // dd($data['products']);
        if ($data['invoice']['id']) $data['employee'] = $this->invocies->employee_bfr_post($data['invoice']['eid']);
        if ($data['invoice']['i_class'] == 1) {
            $pref = prefix(7);
        } else {
            $pref = $this->config->item('prefix');
        }
        ob_end_clean();
        ob_start();

        $sql = 'SELECT sum(total)-sum(pamnt) as "cust_balance" FROM geopos_invoices WHERE status="due" and csd="' . $data["invoice"]["csd"] . '" or status="partial" and csd="' . $data["invoice"]["csd"] . '"';
        $query = $this->db->query($sql);
        $response = $query->result_array();
        $cust_balance_inv = $response[0]['cust_balance'];

        // $cust_balance_inv= $response[0]['cust_balance'];
        $sql2 = "SELECT content FROM `geopos_notes` ORDER BY `geopos_notes`.`id`  DESC LIMIT 1";
        $query2 = $this->db->query($sql2);
        $response2 = $query2->result_array();
        $content2 = $response2[0]['content'];

        $sql2 = 'SELECT data FROM `geopos_custom_data` where rid="' . $data["invoice"]["csd"] . '"';
        $query2 = $this->db->query($sql2);
        $response2 = $query2->result_array();
        $custom_fields = $response2[0]['data'];
        // Calculate total products and page count
        $total_products = count($data['products']);
        $productsPerPage = 10;  // Define how many products should appear per page

        if ($total_products > 0) {
            $page_count = ceil($total_products / $productsPerPage);
        } else {
            $page_count = 1;  // Default to 1 page if no products are available
        }
        // var_dump($page_count);
        // die();
        if ($no_print == 1) {
            $sql = 'Update geopos_invoices_bfr_post set print_status=1 where id= ' . $tid . ' ';
            $this->db->query($sql);
        }




        ini_set('memory_limit', '64M');

        $data_tr = "";

        $left_logo = base_url("userfiles/company/" . $company['logo']);
        $data['left_logo'] = $left_logo;
        $right_logo = base_url("assets/images/halal_logo.jpg");
        $data['right_logo'] = $right_logo;
        ob_end_clean();
        if ($data['invoice']['inv_type'] == 'INVOICE') {
            $header = '<html><head><style>@page { margin: 350px 50px 200px; } header{ position: fixed;  top: -340px;}
            table{width:100%;}.pagenum:after {content: counter(page)}</style> </head><body><header>';
            $header .= '<table style="width:100%;">
            <tr style="border:1px solid #1367A4;">
            <td style="width:100%; text-align: center;">
                <h2>' . $company['cname'] . '</h2><br>
                <span style="font-size: 18px; font-weight: bold; color: #1367A4;">LOADING SLIP</span>
            </td>
            </tr></table>';
        } else {
            $header = '<html><head><style>@page { margin: 350px 50px 200px; }header{ position: fixed;  top: -320px;}table{width:100%;}.pagenum:after {content: counter(page)}</style> </head><body><header><span style="float:right;"  class="pagenum">Page:</span>';
            $header .= ' <div style="width:100%; "><div style="width:23%; float:left; text-align:center;">  <br> Invoice#: ' . $data['invoice']['tid'] . '
                </div></div><div><p style="text-align:center;height:84px;">Note: This is not a valid Invoice  </p>
                    Date: ' . date("d-m-Y", strtotime($data["invoice"]["invoicedate"])) . '
                </div><div style="clear:both"></div>';
        }

        $header .= '<div style="width:40%; float:left; margin-top:5px;">'; // START container
        // Top box: Company name
        $header .= '<div style="border:1px solid #1367A4; height:70px; padding: 4px 5px; border-radius: 6px; margin-bottom:5px;">';
        if ($data['invoice']['company']) {
            $header .= $data['invoice']['company'] . '<br>';
        }


        if ($data['invoice']['inv_type'] == 'INVOICE') {
            $header .= $data['invoice']['cust_address'] . ' ' . $data['invoice']['cust_city'] . '<br>' . $data['invoice']['cust_postcode'];
            $header .= '</div>';
            // Bottom box: Address + Terms
            $header .= '<div style="border:1px solid #1367A4; height:25px; padding: 4px 5px; border-radius: 6px; font-size: 13px;">';
            if ($data['invoice']['pamt_terms']) {
                $header .= '<b><span style="background-color:yellow">TERMS:</span></b> ' . $custom_fields;
            }
        } else {
            $header .= $data['invoice']['cust_address'] . ' ' . $data['invoice']['cust_city'] . '<br>' . $data['invoice']['cust_postcode'];
            if ($data['invoice']['pamt_terms']) {
                $header .= '<b><span style="background-color:yellow">TERMS:</span></b> ' . $custom_fields;
            }
        }
        $header .= '</div></div>';
        $header .= '<div style="width: 40%; height:100px; display: flex; justify-content: flex-end; float:right;">';
        $header .= '<table class="maintable2" style="width: 100%; border-radius: 6px; border: 1px solid #1367A4; font-size: 14px;">
                        <tr>
                            <td style="border-bottom: 1px solid #1367A4; border-right: 1px solid #1367A4; width: 60%; background: #f1f4fb; padding: 4px 7px;">&nbsp;Sales Order No</td>
                            <td style="border-bottom: 1px solid #1367A4;">' . $data['invoice']['tid'] . '</td>
                        </tr>
                        <tr>
                            <td style="border-bottom: 1px solid #1367A4; border-right: 1px solid #1367A4; width: 60%; background: #f1f4fb; padding: 4px 7px;">&nbsp;Sales Order Date</td>
                            <td style="border-bottom: 1px solid #1367A4;">' . $data["invoice"]["invoicedate"] . '</td>
                        </tr>
                        <tr>
                            <td style="border-bottom: 1px solid #1367A4; border-right: 1px solid #1367A4; width: 60%; background: #f1f4fb; padding: 4px 7px;">&nbsp;Page</td>
                            <td style="border-bottom: 1px solid #1367A4;">' . $page_count . '</td>
                        </tr>
                        <tr>
                            <td style="border-right: 1px solid #1367A4; width: 60%; background: #f1f4fb; padding: 4px 7px;">&nbsp;Collection time </td>
                            <td></td>
                        </tr>
                    </table>';
        $header .= '</div>'; // Closing the Right Box
        $header .= '</div></header>';
        // $header .= '<div style="clear:both"></div> <main>';
        // $header .= '<div style="width:100%; margin-top:-17px; ">';
        // $header .= '<table width="100%" class="myProducts">
        //                 <thead>
        //                     <tr style="font-size: 14px; background:#ccc; padding:4px;">
        //                         <th style="width: 4%; text-align:left;">
        //                             <input type="checkbox" onclick="toggleCheckboxes(this)" style="margin: 0; vertical-align: middle;">
        //                         </th>
        //                         <th style="width: 12%; text-align:left;">Quantity</th>
        //                         <th style="border-left: 1px solid #1367A4; width: 43%; text-align:left;">Details</th>
        //                     </tr>
        //                 </thead>';
        // $header .= '<tbody style="font-size:15px;">';

        // $ns = 0;
        // $sub_t = 0;

        // foreach ($data['products'] as $row) {
        //     $ns++;
        //     $sub_t += $row['price'] * $row['qty'];
        //     $up = amountExchange($row['price'], $invoice['multi'], $invoice['loc']);
        //     $vat = amountExchange($row['totaltax'], $invoice['multi'], $invoice['loc']);
        //     $netamount = amountExchange($row['subtotal'] - $row['totaltax'], $invoice['multi'], $invoice['loc']);

        //     if (!empty($row['product_des'])) {
        //         // Highlight row if serial equals 1
        //         $rowStyle = ($row['serial'] == '1') ? 'background:#FBF36D; font-size:18px;' : 'border-bottom:1px solid white;';

        //         $header .= '<tr style="' . $rowStyle . '">';
        //         $header .= '<td style="border: 1px solid #1367A4; text-align: center;">
        //                 <input type="checkbox" class="row-checkbox" style="margin: 0; vertical-align: middle;">
        //             </td>';
        //         $header .= '<td style="border-bottom:1px solid white;">' . (int)$row["qty"] . ' ' . $row["unit"] . '</td>';
        //         $header .= '<td style="border-bottom:1px solid white;">' . $row["product_des"] . '</td>';
        //         $header .= '</tr>';
        //     }
        // }

        // $header .= '</tbody></table></div></main>';
        $header .= '<div style="clear:both"></div> <main>';
        $header .= '<div style="width:100%; margin-top:-17px;">';
        $header .= '<table width="100%" class="myProducts" style="border-collapse: collapse; font-size:15px;">';
        $header .= '<thead>
                <tr style="font-size: 14px; background:#ccc;">
                    <th style="width: 4%; text-align:left; border: 1px solid #1367A4;">
                        <input type="checkbox" onclick="toggleCheckboxes(this)" style="margin: 0; vertical-align: middle;">
                    </th>
                    <th style="width: 12%; text-align:center; border: 1px solid #1367A4;">Quantity</th>
                    <th style="width: 43%; text-align:left; border: 1px solid #1367A4;">Details</th>
                </tr>
            </thead>';
        $header .= '<tbody>';

        $ns = 0;
        $sub_t = 0;

        foreach ($data['products'] as $row) {
            $ns++;
            // Removed calculation - using database value instead
            // Using rev_amountExchange_s() directly in output - no need for variables
            $netamount = amountExchange($row['subtotal'] - $row['totaltax'], $invoice['multi'], $invoice['loc']);

            if (!empty($row['product_des'])) {
                $rowStyle = ($row['serial'] == '1') ? 'background:#FBF36D; font-size:18px;' : '';

                $header .= '<tr style="' . $rowStyle . '">';
                $header .= '<td style="border: 1px solid #1367A4; text-align: center;">
                        <input type="checkbox" class="row-checkbox" style="margin: 0; vertical-align: middle;">
                    </td>';
                $header .= '<td style="text-align:center; border: 1px solid #1367A4;">' . (int)$row["qty"] . ' ' . $row["unit"] . '</td>';
                $header .= '<td style="border: 1px solid #1367A4;">' . $row["product_des"] . '</td>';
                $header .= '</tr>';
            }
        }

        $header .= '</tbody></table></div></main>';

        $header .= '<footer style="position: absolute; bottom: -195px; width: 100%;">';

        // Divider Line
        $header .= '<hr style="color: #1367A4;">';

        // Left Section of the Footer
        $header .= '<div style="border: 1px solid #1367A4; width: 59%; height: 100px; padding: 8px 4px 5px 4px; border-radius: 6px; float: left;">
                        Note:<br>
                        <div style="width: 100%;"></div>
                        <div style="clear: both;"></div>
                        </div>';

        // Right Section of the Footer
        $header .= '<div style="float: right; height: 100px; margin-left: 320px; width: 280px;">
                        <table class="maintable2" style="width: 100%; border-radius: 6px; border: 1px solid #1367A4; font-size: 14px;">
                            <tr>
                                <td style="border-bottom: 1px solid #1367A4; border-right: 1px solid #1367A4; width: 60%; background: #f1f4fb; padding: 4px 7px;">&nbsp;No. of Pallets</td>
                                <td style="border-bottom: 1px solid #1367A4;"></td>
                            </tr>
                            <tr>
                                <td style="border-bottom: 1px solid #1367A4; border-right: 1px solid #1367A4; width: 60%; background: #f1f4fb; padding: 4px 7px;">&nbsp;Completed By</td>
                                <td style="border-bottom: 1px solid #1367A4;"></td>
                            </tr>
                            <tr>
                                <td style="border-bottom: 1px solid #1367A4; border-right: 1px solid #1367A4; width: 60%; background: #f1f4fb; padding: 4px 7px;">&nbsp;Checked By</td>
                                <td style="border-bottom: 1px solid #1367A4;"></td>
                            </tr>
                            <tr>
                                <td style="border-right: 1px solid #1367A4; width: 60%; background: #f1f4fb; padding: 4px 7px;">&nbsp;DEL / COLL</td>
                                <td></td>
                            </tr>
                        </table>
                        </div>
                        <div style="clear: both;">
                        </div>';



        $header .= '</footer></body></html>';
        $file_name = $tid;
        $this->dpdf->loadHtml($header);
        $this->dpdf->render();
        $output =  $this->dpdf->output();
        $file_location = 'userfiles/invoices_bfr_postr/loadingslip_' . $file_name . '.pdf';
        file_put_contents($file_location, $output);
        if ($printing) {
            $this->dpdf->stream("" . $file_name . ".pdf", array("Attachment" => 0));
        }
    }
    public function loadingslip_posted($printing = true, $id = -1)
    {
        $company = $this->settings->company_details(1);

        $productCounter = 0;
        $productsPerPage = 18;

        $hide_balance = 0;
        $tid = $id;
        if ($tid == -1) {
            $tid = $this->input->get('id');
            $hide_balance = ($this->input->get('hide_balance') == 1);
        }
        $data['id'] = $tid;
        // $data['invoice'] = $this->invocies->invoice_details($tid, $this->limited);
        // $data['products'] = $this->invocies->invoice_products($tid);
        // $data['employee'] = $this->invocies->employee($data['invoice']['eid']);
        $data['invoice'] = $this->invocies->invoice_details_bfr_post($tid, $this->limited);
        if ($data['invoice']['id']) $data['products'] = $this->invocies->invoice_products_bfr_post($tid);
        // var_dump($data['products']);  die();
        // dd($data['products']);
        if ($data['invoice']['id']) $data['employee'] = $this->invocies->employee_bfr_post($data['invoice']['eid']);
        if ($data['invoice']['i_class'] == 1) {
            $pref = prefix(7);
        } else {
            $pref = $this->config->item('prefix');
        }
        ob_end_clean();
        ob_start();

        $sql = 'SELECT sum(total)-sum(pamnt) as "cust_balance" FROM geopos_invoices WHERE status="due" and csd="' . $data["invoice"]["csd"] . '" or status="partial" and csd="' . $data["invoice"]["csd"] . '"';
        $query = $this->db->query($sql);
        $response = $query->result_array();
        $cust_balance_inv = $response[0]['cust_balance'];

        // $cust_balance_inv= $response[0]['cust_balance'];
        $sql2 = "SELECT content FROM `geopos_notes` ORDER BY `geopos_notes`.`id`  DESC LIMIT 1";
        $query2 = $this->db->query($sql2);
        $response2 = $query2->result_array();
        $content2 = $response2[0]['content'];

        $sql2 = 'SELECT data FROM `geopos_custom_data` where rid="' . $data["invoice"]["csd"] . '"';
        $query2 = $this->db->query($sql2);
        $response2 = $query2->result_array();
        $custom_fields = $response2[0]['data'];

        $total_products = count($data['products']);
        $page_count = ceil($total_products / $productsPerPage);

        if ($d == '1' || $d == 1) {
            $sql = 'Update geopos_invoices set print_status=1 where id= ' . $tid . ' ';
            $this->db->query($sql);
        }



        ini_set('memory_limit', '64M');

        $data_tr = "";

        $left_logo = base_url("userfiles/company/" . $company['logo']);
        $data['left_logo'] = $left_logo;
        $right_logo = base_url("assets/images/halal_logo.jpg");
        $data['right_logo'] = $right_logo;
        ob_end_clean();
        if ($data['invoice']['inv_type'] == 'INVOICE') {
            $header = '<html><head><style>@page { margin: 350px 50px 200px; } header{ position: fixed;  top: -340px;}
            table{width:100%;}.pagenum:after {content: counter(page)}</style> </head><body><header>';
            $header .= '<table style="width:100%;">
            <tr style="border:1px solid #1367A4;">
            <td style="width:100%; text-align: center;">
                <h2>' . $company['cname'] . '</h2><br>
                <span style="font-size: 18px; font-weight: bold; color: #1367A4;">LOADING SLIP</span>
            </td>
            </tr></table>';
        } else {
            $header = '<html><head><style>@page { margin: 350px 50px 200px; }header{ position: fixed;  top: -320px;}table{width:100%;}.pagenum:after {content: counter(page)}</style> </head><body><header><span style="float:right;"  class="pagenum">Page:</span>';
            $header .= ' <div style="width:100%; "><div style="width:23%; float:left; text-align:center;">  <br> Invoice#: ' . $data['invoice']['tid'] . '
                </div></div><div><p style="text-align:center;height:84px;">Note: This is not a valid Invoice  </p>
                    Date: ' . date("d-m-Y", strtotime($data["invoice"]["invoicedate"])) . '
                </div><div style="clear:both"></div>';
        }

        $header .= '<div style="border:1px solid #1367A4;width:40%; height:100px; padding: 8px 10px 8px; border-radius: 6px;float:left;margin-top:35px;">';

        if ($data['invoice']['company']) {
            $header .= $data['invoice']['company'] . '<br>';
        }

        if ($data['invoice']['inv_type'] == 'INVOICE') {
            $header .= $data['invoice']['cust_address'] . '<br/>' . $data['invoice']['cust_city'] . '<br/>' .  $data['invoice']['cust_postcode'];
            if ($data['invoice']['pamt_terms']) {
                $header .= '<br><b><span style="background-color:yellow">TERMS:</span></b> ' . $custom_fields;
            }
        } else {
            $header .= $data['invoice']['cust_address'] . '<br/>' . $data['invoice']['cust_city'] . '<br/>' .  $data['invoice']['cust_postcode'];
            $header .= '</div>';
            // Bottom box: Address + Terms
            $header .= '<div style="border:1px solid #1367A4; height:25px; padding: 4px 5px; border-radius: 6px; font-size: 13px;">';
            if ($data['invoice']['pamt_terms']) {
                $header .= '<br><b><span style="background-color:yellow">TERMS:</span></b> ' . $custom_fields;
            }
        }

        $header .= '</div>';
        $header .= '<div style="width: 40%; height:100px; display: flex; justify-content: flex-end; float:right;">';
        $header .= '<table class="maintable2" style="width: 100%; border-radius: 6px; border: 1px solid #1367A4; font-size: 14px;">
                        <tr>
                            <td style="border-bottom: 1px solid #1367A4; border-right: 1px solid #1367A4; width: 60%; background: #f1f4fb; padding: 4px 7px;">&nbsp;Sales Order No</td>
                            <td style="border-bottom: 1px solid #1367A4;">' . $data['invoice']['tid'] . '</td>
                        </tr>
                        <tr>
                            <td style="border-bottom: 1px solid #1367A4; border-right: 1px solid #1367A4; width: 60%; background: #f1f4fb; padding: 4px 7px;">&nbsp;Sales Order Date</td>
                            <td style="border-bottom: 1px solid #1367A4;">' . $data["invoice"]["invoicedate"] . '</td>
                        </tr>
                        <tr>
                            <td style="border-bottom: 1px solid #1367A4; border-right: 1px solid #1367A4; width: 60%; background: #f1f4fb; padding: 4px 7px;">&nbsp;Page</td>
                            <td style="border-bottom: 1px solid #1367A4;">' . $page_count . '</td>
                        </tr>
                        <tr>
                            <td style="border-right: 1px solid #1367A4; width: 60%; background: #f1f4fb; padding: 4px 7px;">&nbsp;Collection time </td>
                            <td></td>
                        </tr>
                    </table>';
        $header .= '</div>'; // Closing the Right Box
        $header .= '</div></header>';
        $header .= '<div style="clear:both"></div> <main><div style="width:100%; margin-top:-17px; ">';
        $header .= '<table width="100%" class="myProducts">
                        <thead>
                            <tr style="font-size: 14px; background:#ccc; padding:4px;">
                                <th style="width: 4%; text-align:left;">
                                    <input type="checkbox" onclick="toggleCheckboxes(this)" style="margin: 0; vertical-align: middle;">
                                </th>
                                <th style="width: 12%; text-align:left;">Quantity</th>
                                <th style="border-left: 1px solid #1367A4; width: 43%; text-align:left;">Details</th>
                            </tr>
                        </thead>';
        $header .= '<tbody style="font-size:15px;">';

        $ns = 0;
        $sub_t = 0;

        foreach ($data['products'] as $row) {
            $ns++;
            // Removed calculation - using database value instead
            // Using rev_amountExchange_s() directly in output - no need for variables
            $netamount = amountExchange($row['subtotal'] - $row['totaltax'], $invoice['multi'], $invoice['loc']);

            if (!empty($row['product_des'])) {
                // Highlight row if serial equals 1
                $rowStyle = ($row['serial'] == '1') ? 'background:#FBF36D; font-size:18px;' : 'border-bottom:1px solid white;';

                $header .= '<tr style="' . $rowStyle . '">';
                $header .= '<td style="border: 1px solid #1367A4; text-align: center;">
                        <input type="checkbox" class="row-checkbox" style="margin: 0; vertical-align: middle;">
                    </td>';
                $header .= '<td style="border-bottom:1px solid white;">' . (int)$row["qty"] . ' ' . $row["unit"] . '</td>';
                $header .= '<td style="border-bottom:1px solid white;">' . $row["product_des"] . '</td>';
                $header .= '</tr>';
            }
        }

        $header .= '</tbody></table></div></main>';

        $header .= '<footer style="position: absolute; bottom: -195px; width: 100%;">';

        // Divider Line
        $header .= '<hr style="color: #1367A4;">';

        // Left Section of the Footer
        $header .= '<div style="border: 1px solid #1367A4; width: 59%; height: 100px; padding: 8px 4px 5px 4px; border-radius: 6px; float: left;">
                        <br>
                        <div style="width: 100%;"></div>
                        <div style="clear: both;"></div>
                        </div>';

        // Right Section of the Footer
        $header .= '<div style="float: right; height: 100px; margin-left: 320px; width: 280px;">
                        <table class="maintable2" style="width: 100%; border-radius: 6px; border: 1px solid #1367A4; font-size: 14px;">
                            <tr>
                                <td style="border-bottom: 1px solid #1367A4; border-right: 1px solid #1367A4; width: 60%; background: #f1f4fb; padding: 4px 7px;">&nbsp;No. of Pallets</td>
                                <td style="border-bottom: 1px solid #1367A4;"></td>
                            </tr>
                            <tr>
                                <td style="border-bottom: 1px solid #1367A4; border-right: 1px solid #1367A4; width: 60%; background: #f1f4fb; padding: 4px 7px;">&nbsp;Completed By</td>
                                <td style="border-bottom: 1px solid #1367A4;"></td>
                            </tr>
                            <tr>
                                <td style="border-bottom: 1px solid #1367A4; border-right: 1px solid #1367A4; width: 60%; background: #f1f4fb; padding: 4px 7px;">&nbsp;Checked By</td>
                                <td style="border-bottom: 1px solid #1367A4;"></td>
                            </tr>
                            <tr>
                                <td style="border-right: 1px solid #1367A4; width: 60%; background: #f1f4fb; padding: 4px 7px;">&nbsp;DEL / COLL</td>
                                <td></td>
                            </tr>
                        </table>
                        </div>
                        <div style="clear: both;">
                        </div>';



        $header .= '</footer></body></html>';
        $file_name = $tid;
        $this->dpdf->loadHtml($header);
        $this->dpdf->render();
        $output =  $this->dpdf->output();
        $file_location = 'userfiles/invoices/' . $file_name . '.pdf';
        file_put_contents($file_location, $output);
        if ($printing) {
            $this->dpdf->stream("" . $file_name . ".pdf", array("Attachment" => 0));
        }
    }

    public function quotattion_invoice_bfr_posting($printing = true, $id = -1)
    {
        $company = $this->settings->company_details(1);

        $productCounter = 0;
        $productsPerPage = 18;

        $hide_balance = 0;
        $tid = $id;
        if ($tid == -1) {
            $tid = $this->input->get('id');
            $hide_balance = ($this->input->get('hide_balance') == 1);
        }
        $data['id'] = $tid;
        // $data['invoice'] = $this->invocies->invoice_details($tid, $this->limited);
        // $data['products'] = $this->invocies->invoice_products($tid);
        // $data['employee'] = $this->invocies->employee($data['invoice']['eid']);
        $data['invoice'] = $this->invocies->invoice_details_bfr_post($tid, $this->limited);
        if ($data['invoice']['id']) $data['products'] = $this->invocies->invoice_products_bfr_post($tid);
        // var_dump($data['products']);  die();
        // dd($data['products']);
        if ($data['invoice']['id']) $data['employee'] = $this->invocies->employee_bfr_post($data['invoice']['eid']);
        if ($data['invoice']['i_class'] == 1) {
            $pref = prefix(7);
        } else {
            $pref = $this->config->item('prefix');
        }
        ob_end_clean();
        ob_start();



        $sql = 'SELECT sum(total)-sum(pamnt) as "cust_balance" FROM geopos_invoices WHERE status="due" and csd="' . $data["invoice"]["csd"] . '" or status="partial" and csd="' . $data["invoice"]["csd"] . '"';
        $query = $this->db->query($sql);
        $response = $query->result_array();
        $cust_balance_inv = $response[0]['cust_balance'];

        // $cust_balance_inv= $response[0]['cust_balance'];
        $sql2 = "SELECT content FROM `geopos_notes` ORDER BY `geopos_notes`.`id`  DESC LIMIT 1";
        $query2 = $this->db->query($sql2);
        $response2 = $query2->result_array();
        $content2 = $response2[0]['content'];

        $sql2 = 'SELECT data FROM `geopos_custom_data` where rid="' . $data["invoice"]["csd"] . '"';
        $query2 = $this->db->query($sql2);
        $response2 = $query2->result_array();
        $custom_fields = $response2[0]['data'];

        if ($d == '1' || $d == 1) {
            $sql = 'Update geopos_invoices set print_status=1 where id= ' . $tid . ' ';
            $this->db->query($sql);
        }



        ini_set('memory_limit', '64M');

        $data_tr = "";

        $left_logo = base_url("userfiles/company/" . $company['logo']);
        $data['left_logo'] = $left_logo;
        $right_logo = base_url("assets/images/halal_logo.jpg");
        $data['right_logo'] = $right_logo;
        ob_end_clean();
        if ($data['invoice']['inv_type'] == 'INVOICE') {
            $header = '<html><head><style>@page { margin: 350px 50px 200px; } header{ position: fixed;  top: -340px;}
            table{width:100%;}.pagenum:after {content: counter(page)}</style> </head><body><header>';
            $header .= '<table style="width:100%;">
            <tr style="border:1px solid #1367A4;">';
            // $header .= '<td style="width:100%;"><img src="' . $left_logo . '" height="165px" width="270px"></td>';
            $header .= '<td style="width:100%;" height="165px">
                            <img src="' . $left_logo . '" height="145px" width="270px"><br>
                            <div style="font-size:18px; font-weight:bold; text-align:center;">
                                QUOTATION
                            </div>
                        </td>
            <td style="width:40%;"><img src="' . $right_logo . '" height="60px" width="60px"></td>
            <td style="width:100%;"><br><b>' . $company['cname'] . '</b><br>' . $company['address'] . ' ' . $company['city'] . ' '
                . $company['region'] . ', ' . $company['postbox'] . ', ' . $company['country'] . ' <br> Phone:&nbsp;&nbsp;&nbsp;' . $company['phone'] . ' <br> Mobile:&nbsp;&nbsp;' . $company['mobile'] . ' <br>
                <table style="border: 1px solid #1367A4;
                border-collapse: collapse;margin-left:25px;margin-top:10px; border-radius: 6px;width:190px;">
                <tr style="">
                            <th  style="border: 1px solid #1367A4; width:80px;
                            border-collapse: collapse;font-size: 13px;text-align: center" >Date
                                </th>
                                <th  style="border: 1px solid #1367A4;
                                border-collapse: collapse;font-size: 13px;text-align: center">Quote
                                </th>
                                <th  style="border: 1px solid #1367A4;
                                border-collapse: collapse;font-size: 13px;text-align: center">A/C
                                </th>
                </tr>
                <tr >
                    <td style="width:30%;font-size: 13px;border: 1px solid #1367A4;border-collapse: collapse;text-align: center">
                    ' . date("d-m-Y", strtotime($data["invoice"]["invoicedate"])) . '
                    </td>
                    <td style="font-size: 13px;border: 1px solid #1367A4;border-collapse: collapse;text-align: center">' . $data['invoice']['tid'] . '</td>
                    <td style="font-size: 13px;border: 1px solid #1367A4;border-collapse: collapse;text-align: center">' . $data["invoice"]["name"] . '</td>
                </tr>
                </table>
            </td>
            </tr></table>';
        } else {
            $header = '<html><head><style>@page { margin: 350px 50px 200px; }header{ position: fixed;  top: -320px;}table{width:100%;}.pagenum:after {content: counter(page)}</style> </head><body><header><span style="float:right;"  class="pagenum">Page:</span>';
            $header .= ' <div style="width:100%; "><div style="width:23%; float:left; text-align:center;">  <br>
                </div></div><div><p style="text-align:center;height:84px;">Note: This is not a valid Invoice  </p>
                    Date: ' . date("d-m-Y", strtotime($data["invoice"]["invoicedate"])) . '
                </div><div style="clear:both"></div>';
        }

        $header .= '<div style="width:40%; float:left; margin-top:5px;">'; // START container
        // Top box: Company name
        $header .= '<div style="border:1px solid #1367A4; height:70px; padding: 4px 5px; border-radius: 6px; margin-bottom:5px;">';
        if ($data['invoice']['company']) {
            $header .= $data['invoice']['company'] . '<br>';
        }


        if ($data['invoice']['inv_type'] == 'INVOICE') {
            $header .= $data['invoice']['cust_address'] . ' ' . $data['invoice']['cust_city'] . '<br>' . $data['invoice']['cust_postcode'];
            $header .= '</div>';
            // Bottom box: Address + Terms
            $header .= '<div style="border:1px solid #1367A4; height:25px; padding: 4px 5px; border-radius: 6px; font-size: 13px;">';
            if ($data['invoice']['pamt_terms']) {
                $header .= '<b><span style="background-color:yellow">TERMS:</span></b> ' . $custom_fields;
            }
        } else {
            $header .= $data['invoice']['cust_address'] . ' ' . $data['invoice']['cust_city'] . '<br>' . $data['invoice']['cust_postcode'];
            $header .= '</div>';
            // Bottom box: Address + Terms
            $header .= '<div style="border:1px solid #1367A4; height:25px; padding: 4px 5px; border-radius: 6px; font-size: 13px;">';
            if ($data['invoice']['pamt_terms']) {
                $header .= '<b><span style="background-color:yellow">TERMS:</span></b> ' . $custom_fields;
            }
        }
        $header .= '</div></div><div style="float:right;margin-left:-150px;margin-top:-30px;width:50%;" ><div style=" font-size: 25px;">';

        if ($data['invoice']['inv_type'] == 'INVOICE') {
            $header .= '<br> ';
            $header .= '</div><table style="height:5%;  font-size: 14px; border:1px solid #1367A4;border-radius: 6px;"> 
        <tr ><td ><b>' . $content2 . '</b></td>
        </tr>';
            $header .= '</table>';
        } else {
            $header .= '<span style="font-size: 32px; transform: translate(-78px, -38px)" >Day Pass</span>';
            if ($data['invoice']['driver_name'] != '' && $data['invoice']['vehicle_no'] != '') {
                $header .= '</div><table style="height:5%;  font-size: 14px; border:1px solid #1367A4;border-radius: 6px;"> 
            
            <tr ><td ><b>Driver Name: </b></td></tr>
            <tr ><td >' . $data['invoice']['driver_name'] . ' ' . $data['invoice']['vehicle_no'] . '</td>
            </tr>';
                $header .= '</table>';
            }
        }
        $header .= '</div></div></header>';

        $header .= '<table style="width:100%; font-size: 14px;  margin-top:-43px;"><tr><td style="width: 100%;">
        <h3 style="margin-top:10px;  text-align:center; color:black; font-size:18px;">';
        $header .= '<span style="color:red; font-weight:bold;">NOTE:</span> ';
        $header .= '<span style="font-weight:bold;">THIS IS NOT A VALID INVOICE, IT\'S JUST A QUOTATION</span>';
        $header .= '</h3></td></tr></table>';

        $header .= '<div style="clear:both"></div> <main><div style="width:100%; margin-top:-17px; "><table width="100%" class="myProducts" style="border:1px solid #1367A4;border-radius: 6px;"><thead><tr style="font-size: 14px; background:#ccc; padding:4px;"><th style="width: 12%; text-align:left; ">' . $this->lang->line('Quantity') . '</th><th style="border-left: 1px solid #1367A4;width: 45%; text-align:left;">' . $this->lang->line('Details') . '</th><th style="width: 13%; text-align:left; border-left: 1px solid #1367A4;">' . $this->lang->line('Unit Price') . '</th><th style="width: 20%; text-align:left; border-left: 1px solid #1367A4;">' . $this->lang->line('Net Amount') . '</th><th style="width: 10%;  text-align:left;border-left: 1px solid #1367A4;">' . getTaxName() . '</th></tr></thead>';
        $header .= '<tbody style="font-size:15px;">';

        $ns = 0;
        foreach ($data['products'] as $row) {
            if ($row['qty'] > 0) {
                $productCounter++;
                $ns++;

                if ($productCounter % $productsPerPage === 0) {
                    if ($productCounter > 0) {
                        // Close the previous table if it's not the first page
                        $header .= '</tbody>';
                        $header .= '</table>';
                    }

                    // Start a new page
                    $header .= '<div style="page-break-after: always;"></div>';
                    $header .= '<table style="border:1px solid #1367A4;border-radius: 6px;">';
                    $header .= '<thead>';
                    $header .= '<tr style="font-size: 14px; background: #ccc; padding: 4px;">';
                    $header .= '<th style="width: 12%; text-align: left;">Quantity</th>';
                    $header .= '<th style="border-left: 1px solid #1367A4; width: 45%; text-align: left;">Details</th>';
                    $header .= '<th style="width: 13%; text-align: left; border-left: 1px solid #1367A4;">Unit Price</th>';
                    $header .= '<th style="width: 20%; text-align: left; border-left: 1px solid #1367A4;">Net Amount</th>';
                    $header .= '<th style="width: 10%; text-align: left; border-left: 1px solid #1367A4;">' . getTaxName() . '</th>';
                    $header .= '</tr>';
                    $header .= '</thead>';
                    $header .= '<tbody>';
                }
                // Removed calculation - using database value instead
                // Using rev_amountExchange_s() directly in output - no need for variables
                // $vat = amountExchange($row['totaltax']);
                // $netamount = amountExchange($row['subtotal'] - $row['totaltax']);
                if (empty($row['product_des']) || $row['product_des'] == "") {
                } else {
                    if ($row['serial'] == '1') {
                        $header .= '<tr style="background:#FBF36D; style="font-size:18px;"">';
                    } else {
                        $header .= '<tr ' . (($ns % 2 == 0) ? 'style=" background: #f1f4fb;"' : '') . '>';
                    }
                    $header .= '<td style=" border-bottom:1px solid white;">' . (int)$row["qty"] .  '</td style=" border-bottom:1px solid white;">
                    <td style=" border-bottom:1px solid white;">' . $row["product_des"] . '</td>
                    <td style=" border-bottom:1px solid white;">' . rev_amountExchange_s($row['price']) . '</td>
                     <td style=" border-bottom:1px solid white;">' . rev_amountExchange_s($row['subtotal']) . '</td>
                     <td style=" border-bottom:1px solid white;">' . rev_amountExchange_s($row['totaltax']) . ' </td></tr>';
                }
            }
        }
        $header .= '</tbody></table></div></main>';
        // Use database values instead of calculating totals

        if ($data['invoice']['inv_type'] == 'INVOICE') {

            $header .= '<footer style="position:absolute;bottom:-195px;">';


            if ($data['invoice']['inv_type'] == 'INVOICE') {
                $header .= '<div style="border:1px solid #1367A4;width:59%;height:120px; padding: 8px 0px 5px 4px; border-radius: 6px;float:left;"><span style="color:rgb(48, 37, 133)">
                <b>*MAXIMUM CHANGE ACCEPTED=&#163;40</b></span>';
                $header .= '<div style="font-size: 12px; margin-top: 10px; margin-bottom: 6px;">';
                $header .= '<span style="display: block; padding-bottom: 5px;">*All claims must be notified at the time of delivery.</span>';
                $header .= '<span style="display: block; padding-bottom: 5px;">*Any claims made after delivery will not be considered.</span>';
                $header .= '<span style="display: block; padding-bottom: 5px;">*Frozen and chilled products cannot be returned once delivered.</span>';
                $header .= '<span style="display: block; padding-bottom: 5px;">*Goods will remain the property of ' . $company['cname'] . '.</span>';
                $header .= '<span style="display: block; padding-bottom: 5px;">*Until paid in full by the customer.</span>';
                $header .= '</div>';

                $header .= '<div style="width:100%">
                <div style="clear:both"></div></div><div style="clear:both"></div></div>';
            }
            $header .= '<div style="float:right;margin-left:320px;width:280px" ><table class="maintable2" style="width:100%;border-radius: 6px; border:1px solid #1367A4;font-size: 14px;">';

            if ($data['invoice']['inv_type'] == 'INVOICE') {
                if (!$hide_balance) {
                }
            }

            $header .= '<tr><td style="width: 60%;  background: #f1f4fb;padding:  4px 0px 4px 7px;">&nbsp;Total NET Amount</td><td> ' . amountExchange($data["invoice"]["subtotal"]) . ' </td></tr>
            <tr ><td style="width: 60%;  background: #f1f4fb;padding: 4px 0px 4px 7px;">&nbsp;Total ' . getTaxName() . ' Amount</td><td> ' . amountExchange($data["invoice"]["tax"]) . '  </td></tr>
            <tr><td style="width: 60%;  background: #f1f4fb;padding: 4px 0px 4px 7px;">&nbsp;Quote Total</td><td> ' . amountExchange($data["invoice"]["total"]) . '   </td></tr>';
            $header .= '</table>';
            if ($data['invoice']['inv_type'] == 'INVOICE') {
                // Bank Transfer Info Box (inline layout)
                $header .= '<div style="width:100%; margin-top:10px; font-size:12px;">';
                $header .= '<table style="width:100%;border:1px solid #1367A4;  border-radius:6px;"><tr>';
                $header .= '<td style="width:40%;"><b>For Bank Transfer:</b></td>';
                $header .= '<td style="width:60%;"><b>' . $company['cname'] . '<br>S/C: ' . $company['sortcode'] . '&nbsp;&nbsp;&nbsp; AC: ' . $company['taxid'] . '</b></td>';
                $header .= '</tr></table>';
                $header .= '</div></div>';
            }
            // $header .= '<span href="mailto:' . $company['email'] . '"><img src="' . base_url("assets/images/mail.jpg") . '" height="32px" width="38px" style="margin-top:10px;">  </span>' . $company['email'] . '<br>
            $header .= '
            <!--<span href="https://www.cloudbillingmanager.com/"><img src="' . base_url("assets/images/web.jpg") . '" height="32px" width="38px" style="margin-top:10px;"> </span>www.cloudbillingmanager.com -->
            </div><div style="clear:both">  
            <span href="mailto:' . $company['email'] . '"><img src="' . base_url("assets/images/mail.jpg") . '" height="32px" width="38px" style="margin-top:10px;">  </span>' . $company['email'] . '
            <span style="text-align: right; href="https://www.5starfoodsltd.com/"><img src="' . base_url("assets/images/web.jpg") . '" height="32px" width="38px" style="margin-top:10px;">http://www.5starfoodsltd.com/</span>
            &nbsp;&nbsp;&nbsp;&nbsp;<span>Thank you for your business!</span>';

            if ($data['invoice']['inv_type'] == 'INVOICE') {
            }
            $header .= ' </footer></body></html>';
        } else {
            $header .= '<footer style="position:absolute;bottom:-195px;">';

            $header .= '<div style="border:1px solid #1367A4;width:50%;height:140px; padding: 8px 0px 5px 4px; border-radius: 6px;float:left;margin-top:5px;display:none"><span style="color:rgb(48, 37, 133)"><b>*MAXIMUM CHANGE ACCEPTED=&#163;25</b></span><div style=" font-size: 10px;">All goods remain the property of ' . $company['cname'] . ' 
            until all monies are paid in full<br>Any complaints should be made within 48 hours of purchasing.</div><br><div style="width:100%"><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left">Print Name:</div><div style="border:1px solid black;width:10%;height:18px;float:left;margin-left:10px;;margin-left:-78px;"></div><div style="float:left;margin-left:-158px;margin-top:3px;font-size: 12px;"><b>Cash Tendered</b></div><div style="clear:both"></div><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left;">Signature:</div><div style="font-size: 17px;padding-left:10px;border:1px solid black;width:35%;height:20px;float:left;padding-top:3px;padding-bottom:3px;margin-left:-157px;margin-top:-8px">&#163;</div><div style="clear:both"></div></div><div style="clear:both"></div></div>';

            $header .= '<div style="float:right;margin-left:320px;width:280px" ><table class="maintable2" style="width:100%; border:1px solid #1367A4;font-size: 14px;">';

            $header .= '<tr><td style="width: 60%;  background: #f1f4fb;padding:  4px 0px 4px 7px;">&nbsp;' . $lang('Total NET Amount') . '</td>
            <td> ' . amountExchange($data["invoice"]["subtotal"]) . ' </td></tr><tr ><td style="width: 60%;  background: #f1f4fb;padding: 4px 0px 4px 7px;">&nbsp;Total ' . $lang('Tax') . ' Amount</td>
            <td> ' . amountExchange($data["invoice"]["tax"]) . '  </td>
            </tr><tr><td style="width: 60%;  background: #f1f4fb;padding: 4px 0px 4px 7px;">&nbsp;' . $lang('Invoice Total') . '</td><td> ' . amountExchange($data["invoice"]["total"]) . '   </td></tr>';

            $header .= '</table></div><div style="clear:both"></div>';
            $header .= '<p style="font-size:8px;display:none"> ' . $company['cname'] . ' is the trading name of ' . $company['cname'] . ', Company No. 11671004. &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Bank Account Details: Sort Code: ' . $company['sortcode'] . ' A/C No: ' . $company['taxid'] . '</p>';

            $header .= ' </footer></body></html>';
        }
        $file_name = $tid;
        $this->dpdf->loadHtml($header);
        $this->dpdf->render();
        $output =  $this->dpdf->output();
        $file_location = 'userfiles/invoices_bfr_postr/quote_' . $file_name . '.pdf';
        file_put_contents($file_location, $output);
        if ($printing) {
            $this->dpdf->stream("" . $file_name . ".pdf", array("Attachment" => 0));
        }
    }
    public function counter_invoice_bfr_posting($printing = true, $id = -1)
    {
        // Explicitly load language file for PDF generation
        $system_lang = $this->db->query("SELECT lang FROM geopos_system WHERE id=1 LIMIT 1")->row_array();
        $lang_to_load = $system_lang['lang'] ?? 'english';
        $this->lang->load($lang_to_load, $lang_to_load);

        // Get dynamic tax name for placeholder replacement
        $dynamic_tax_name = getTaxName();

        // Helper function to get and replace language lines
        $that = $this; // Store controller reference for closure
        $lang = function ($key) use ($that, $dynamic_tax_name) {
            $text = $that->lang->line($key);
            if ($text === FALSE || empty($text)) {
                return $key; // Return key if translation not found
            }
            // Replace tax placeholders
            return str_replace(
                array('{tax}', '{Tax}', '{TAX}'),
                array(strtolower($dynamic_tax_name), ucfirst(strtolower($dynamic_tax_name)), strtoupper($dynamic_tax_name)),
                $text
            );
        };

        $company = $this->settings->company_details(1);

        $tid = $this->input->get('id');

        $no_print = $this->input->get('no_print');
        if ($no_print) {
            $no_print = $no_print;
        }
        $data['id'] = $tid;
        $data['invoice'] = $this->invocies->invoice_details_bfr_post($tid, $this->limited);
        if ($data['invoice']['id']) $data['products'] = $this->invocies->invoice_products_bfr_post($tid);
        // dd($data['products']);
        if ($data['invoice']['id']) $data['employee'] = $this->invocies->employee_bfr_post($data['invoice']['eid']);
        if ($data['invoice']['i_class'] == 1) {
            $pref = prefix(7);
        } else {
            $pref = $this->config->item('prefix');
        }
        ob_end_clean();
        ob_start();


        $sql = 'SELECT sum(total)-sum(pamnt) as "cust_balance" FROM geopos_invoices WHERE status="due" and csd="' . $data["invoice"]["csd"] . '" or status="partial" and csd="' . $data["invoice"]["csd"] . '"';
        $query = $this->db->query($sql);
        $response = $query->result_array();
        $cust_balance_inv = $response[0]['cust_balance'];

        // $cust_balance_inv= $response[0]['cust_balance'];
        $sql2 = "SELECT content FROM `geopos_notes` ORDER BY `geopos_notes`.`id`  DESC LIMIT 1";
        $query2 = $this->db->query($sql2);
        $response2 = $query2->result_array();
        $content2 = $response2[0]['content'];

        $sql2 = 'SELECT data FROM `geopos_custom_data` where rid="' . $data["invoice"]["csd"] . '"';
        $query2 = $this->db->query($sql2);
        $response2 = $query2->result_array();
        $custom_fields = $response2[0]['data'];
        if ($no_print == 1) {
            $sql = 'Update geopos_invoices_bfr_post set print_status=1 where id= ' . $tid . ' ';
            $this->db->query($sql);
        }




        ini_set('memory_limit', '64M');

        $data_tr = "";

        // $left_logo = base_url("assets/images/dixy_logo_.jpeg");
        // $data['left_logo'] = $left_logo;
        // $right_logo = base_url("assets/images/halal_logo.jpg");
        // $data['right_logo'] = $right_logo;
        $left_logo = base_url("userfiles/company/" . $company['logo']);
        $data['left_logo'] = $left_logo;
        $right_logo = base_url("assets/images/halal_logo.jpg");
        $data['right_logo'] = $right_logo;
        ob_end_clean();
        if ($data['invoice']['inv_type'] == 'INVOICE') {
            $header = '<html><head><style>@page { margin: 350px 50px 200px; }header{ position: fixed;  top: -320px;}
            table{width:100%;}.pagenum:after {content: counter(page)}</style> </head><body><header>';
            $header .= '<table style="width:100%;">
            <tr style="border:1px solid #1367A4;">';
            // <td style="width:100%;"><img src="' . $left_logo . '" height="165px" width="270px"></td>
            $header .= '<td style="width:100%;" height="165px">
                            <img src="' . $left_logo . '" height="145px" width="270px"><br>
                            <div style="font-size:18px; font-weight:bold; text-align:center;">
                                COUNTER INVOICE
                            </div>
                        </td>
            <td style="width:40%;"><img src="' . $right_logo . '" height="60px" width="60px"></td>
            <td style="width:100%;"><br><b>' . $company['cname'] . '</b><br>' . $company['address'] . ' ' . $company['city'] . ' ' . $company['region'] . ', ' . $company['postbox'] . ', ' . $company['country'] . ' <br> Phone:&nbsp;&nbsp;&nbsp;' . $company['phone'] . ' <br> Mobile:&nbsp;&nbsp;' . $company['mobile'] . ' <br>
                <table style="border: 1px solid #1367A4;
                border-collapse: collapse;margin-left:-45px;margin-top:10px; border-radius: 6px;width:320px;">
                <tr style="">
                            <th  style="border: 1px solid #1367A4; width:80px;
                            border-collapse: collapse;font-size: 13px;text-align: center" >Date
                                </th>
                                <th  style="border: 1px solid #1367A4;
                                border-collapse: collapse;font-size: 13px;text-align: center">Invoice
                                </th>
                                <th  style="border: 1px solid #1367A4;
                                border-collapse: collapse;font-size: 13px;text-align: center">A/C
                                </th>
                                <th  style="border: 1px solid #1367A4;
                                border-collapse: collapse;font-size: 13px; text-align: center">EC NO 
                                </th>
                                <th  style="border: 1px solid #1367A4;
                                border-collapse: collapse;font-size: 13px; text-align: center"> ' . $this->lang->line('VAT') . ' Reg
                                </th>
                </tr>
                <tr >
                    <td style="width:30%;font-size: 13px;border: 1px solid #1367A4;border-collapse: collapse;text-align: center">
                    ' . date("d-m-Y", strtotime($data["invoice"]["invoicedate"])) . '
                    </td>
                    <td style="font-size: 13px;border: 1px solid #1367A4;border-collapse: collapse;text-align: center">' . $data['invoice']['tid'] . '</td>
                    <td style="font-size: 13px;border: 1px solid #1367A4;border-collapse: collapse;text-align: center">' . $data["invoice"]["name"] . '</td>
                    <td style="font-size: 13px;border: 1px solid #1367A4;border-collapse: collapse;text-align: center">882 /2004</td>
                    <td style="font-size: 13px;border: 1px solid #1367A4;border-collapse: collapse;text-align: center">299367533</td>
                </tr>
                </table>
            </td>
            </tr></table>';
        } else {
            $header = '<html><head><style>@page { margin: 350px 50px 200px; }header{ position: fixed;  top: -320px;}table{width:100%;}.pagenum:after {content: counter(page)}</style> </head><body><header><span style="float:right;"  class="pagenum">Page:</span>';
            $header .= ' <div style="width:100%; "><div style="width:23%; float:left; text-align:center;">  <br> Invoice#: ' . $data['invoice']['tid'] . '
      </div></div><div><p style="text-align:center;height:84px;">Note: This is not a valid Invoice  </p></div><div style="clear:both"></div>';
        }

        $header .= '<div style="width:40%; float:left; margin-top:5px;">'; // START container
        // Top box: Company name
        $header .= '<div style="border:1px solid #1367A4; height:70px; padding: 4px 5px; border-radius: 6px; margin-bottom:5px;">';
        if ($data['invoice']['company']) {
            $header .= $data['invoice']['company'] . '<br>';
        }


        if ($data['invoice']['inv_type'] == 'INVOICE') {
            $header .= $data['invoice']['cust_address'] . ' ' . $data['invoice']['cust_city'] . '<br>' . $data['invoice']['cust_postcode'];
            $header .= '</div>';
            // Bottom box: Address + Terms
            $header .= '<div style="border:1px solid #1367A4; height:25px; padding: 4px 5px; border-radius: 6px; font-size: 13px;">';
            if ($data['invoice']['pamt_terms']) {
                $header .= '<b><span style="background-color:yellow">TERMS:</span></b> ' . $custom_fields;
            }
        } else {
            $header .= $data['invoice']['cust_address'] . ' ' . $data['invoice']['cust_city'] . '<br/>' . $data['invoice']['cust_postcode'];
            $header .= '</div>';
            // Bottom box: Address + Terms
            $header .= '<div style="border:1px solid #1367A4; height:25px; padding: 4px 5px; border-radius: 6px; font-size: 13px;">';
            if ($data['invoice']['pamt_terms']) {
                $header .= '<br><b> <span style="background-color:yellow">TERMS:</span> </b>' . $custom_fields . '';
            }
        }
        // if ($data['invoice']['postbox']){
        //     $header.= $data['invoice']['cust_address'];
        // } 
        // if ($data['invoice']['email']){
        //     $header.= '<br> ' . $this->lang->line('Email') . ': ' . $data['invoice']['email'];
        // }

        $header .= '</div></div><div style="float:right;margin-left:-150px;margin-top:-30px;width:50%;" ><div style=" font-size: 25px;">';



        if ($data['invoice']['inv_type'] == 'INVOICE') {
            $header .= '<br> ';
            $header .= '</div><table style="height:5%;  font-size: 14px; border:1px solid #1367A4;border-radius: 6px;"> 
        <tr ><td ><b>' . $content2 . '</b></td>
        </tr>';
            $header .= '</table>';
        } else {
            $header .= '<span style="font-size: 32px; transform: translate(-82px, -28px)" >Day Pass</span>';
            if ($data['invoice']['driver_name'] != '' && $data['invoice']['vehicle_no'] != '') {
                $header .= '</div><table style="height:5%;  font-size: 14px; border:1px solid #1367A4;border-radius: 6px;"> 
        
        <tr ><td ><b>Driver Name: </b></td></tr>
        <tr ><td >' . $data['invoice']['driver_name'] . ' ' . $data['invoice']['vehicle_no'] . '</td>
        </tr>';
                $header .= '</table>';
            }
        }



        $header .= '</div></div></header>';
        // if ($data['invoice']['inv_type'] == 'INVOICE') {
        //     $header .= '

        //     <table style="width:100%; font-size: 14px;  margin-top:-15px;"><tr><td style="width: 100%;">&nbsp;<b> 
        //     &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        //     For Bank Transfer:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $company['cname'] . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;S/C&nbsp;&nbsp;' . $company['sortcode'] . '           &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;AC&nbsp;&nbsp;' . $company['taxid'] . '  
        //     </b></td></tr></table>

        //     ';
        // }
        $header .= '<div style="clear:both"></div> <main><div style="width:100%; margin-top:5px; ">
        <table width="100%" class="myProducts" style="border:1px solid #1367A4;border-radius: 6px;">
        <thead><tr style="font-size: 14px; background:#ccc; padding:4px;">
        <th style="width: 12%; text-align:left; ">' . $lang('Quantity') . '</th>
        <th style="border-left: 1px solid #1367A4;width: 45%; text-align:left;">' . $lang('Details') . '</th>
        <th style="width: 13%; text-align:left; border-left: 1px solid #1367A4;">' . $lang('Unit_Rate') . '</th>
        <th style="width: 20%; text-align:left; border-left: 1px solid #1367A4;">' . $lang('Net Amount') . '</th>
        <th style="width: 10%;  text-align:left;border-left: 1px solid #1367A4;">' . $lang('Tax') . '</th></tr></thead>';
        $header .= '<tbody style="font-size:15px;">';

        $ns = 0;
        foreach ($data['products'] as $row) {
            if ($row['qty'] > 0) {
                $ns++;

                // Removed calculation - using database value instead
                // dd($row['qty']);
                $up =     amountExchange($row['price']);
                $vat = amountExchange($row['totaltax']);
                $netamount = amountExchange($row['subtotal'] - $row['totaltax']);
                if (empty($row['product_des']) || $row['product_des'] == "") {
                } else {
                    if ($row['serial'] == '1') {
                        $header .= '<tr style="background:#FBF36D; style="font-size:18px;"">';
                    } else {
                        $header .= '<tr style=" border-bottom:1px solid white;">';
                    }
                    $header .= '<td style=" border-bottom:1px solid white;">' . (int)$row["qty"] . $row["unit"] . '</td style=" border-bottom:1px solid white;">
                    <td style=" border-bottom:1px solid white;">' . $row["product_des"] . '</td><td style=" border-bottom:1px solid white;">' . rev_amountExchange_s($row['price']) . '</td> <td style=" border-bottom:1px solid white;">' . rev_amountExchange_s($row['subtotal']) . '</td><td style=" border-bottom:1px solid white;">' . rev_amountExchange_s($row['totaltax']) . ' </td></tr>';
                }
            }
        }
        $header .= '</tbody></table></div></main>';


        if ($data['invoice']['inv_type'] == 'INVOICE') {

            $header .= '<footer style="position:absolute;bottom:-195px">';
            if ($data['invoice']['inv_type'] == 'INVOICE') {
                $header .= '<div style="border:1px solid #1367A4;width:59%;height:170px; padding: 8px 0px 5px 4px; border-radius: 6px;float:left;"><span style="color:rgb(48, 37, 133)"><b>*MAXIMUM CHANGE ACCEPTED=&#163;40</b></span><div style=" font-size: 14px; margin-bottom: 6px;">
            *All claims must be notified at the time of delivery.<br>
            *Any claims made after delivery will not be considered.<br>
            *Frozen and chilled products cannot be returned once delivered.<br>
            *Goods will remain the property of ' . $company['cname'] . ' until paid in full by the customer.
            </div><div style="width:100%"><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left">Customer:</div><div style="border:1px solid black;width:8%;height:18px;float:left;margin-top:10px;margin-left:-178px;"></div><div style="border:1px solid black;width:8%;height:18px;float:left;margin-top:10px;margin-left:-58px;"></div><div style="border:1px solid black;width:8%;height:18px;float:left;margin-top:10px;margin-left:-118px;"></div><div style="float:left;margin-left:-178px;margin-top:-5px;font-size: 12px;"><b>Cash &nbsp;&nbsp; Card Payment &nbsp;&nbsp;BACS</b></div><div style="clear:both"></div><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left;">
            Staff Name:</div><div style="font-size: 17px;padding-left:10px;border:1px solid black;width:35%;height:20px;float:left;padding-top:3px;padding-bottom:3px;margin-left:-187px;margin-top:3px">&#163;</div><div style="clear:both"></div></div><div style="clear:both"></div></div>';
            }

            $sub_ts = 0;
            foreach ($data['products'] as $row) {
                // Removed calculation - using database value instead
            }

            $header .= '<div style="float:right;margin-left:320px;width:280px" ><table class="maintable2" style="width:100%;border-radius: 6px; border:1px solid #1367A4;font-size: 14px;">';

            if ($data['invoice']['inv_type'] == 'INVOICE') {
                // $header.='<tr ><td style="width: 50%;  background: lightgrey;padding: 4px 0px 4px 7px;">&nbsp;Previous Balance</td><td>' ;
                $invbalance = 0;
                if ($cust_balance_inv >= $data['invoice']['total']) {
                    $invbalance =  $cust_balance_inv - $data['invoice']['total'];
                    $header .=  substr(amountExchange($invbalance, $data['invoice']['multi'], $data['invoice']['loc']), 3, 100);
                } else {
                    $header .=  substr(amountExchange($cust_balance_inv, $data['invoice']['multi'], $data['invoice']['loc']), 3, 100);
                }
                $header .= '</td></tr>';
            }


            // if($data['invoice']['inv_type'] == 'DAYPASS') {
            //     $header.=' <tr><td style="width: 60%; background: lightgrey;padding:  4px 0px 4px 7px;">&nbsp;Driver</td><td>'. $data['invoice']['driver_name'] .'  '. $data['invoice']['vehicle_no'].' </td></tr> '; 
            // }
            $header .= '<tr><td style="width: 60%;  background: lightgrey;padding:  4px 0px 4px 7px;">&nbsp;Total NET Amount</td><td> ' . amountExchange($data["invoice"]["subtotal"]) . ' </td></tr>
            <tr ><td style="width: 60%;  background: lightgrey;padding: 4px 0px 4px 7px;">&nbsp;Total ' . $this->lang->line('VAT') . ' Amount</td><td> ' . amountExchange($data["invoice"]["tax"]) . '  </td></tr>
            <tr><td style="width: 60%;  background: lightgrey;padding: 4px 0px 4px 7px;">&nbsp;Invoice Total</td><td> ' . amountExchange($data["invoice"]["total"]) . '   </td></tr>';
            if ($data['invoice']['inv_type'] == 'INVOICE') {

                // $header.=' <tr><td style="width: 60%; background: lightgrey;padding:  4px 0px 4px 7px;">&nbsp;Balance Now Due</td><td>'.$cust_balance_inv.' </td></tr> ';  
            }

            $header .= '</table>';
            if ($data['invoice']['inv_type'] == 'INVOICE') {
                // Bank Transfer Info Box (inline layout)
                $header .= '<div style="width:100%; margin-top:10px; font-size:12px;">';
                $header .= '<table style="width:100%;border:1px solid #1367A4;  border-radius:6px;"><tr>';
                $header .= '<td style="width:40%;"><b>For Bank Transfer:</b></td>';
                $header .= '<td style="width:60%;"><b>' . $company['cname'] . '<br>S/C: ' . $company['sortcode'] . '&nbsp;&nbsp;&nbsp; AC: ' . $company['taxid'] . '</b></td>';
                $header .= '</tr></table>';
                $header .= '</div></div>';
            }
            // $header .= '<span href="mailto:' . $company['email'] . '"><img src="' . base_url("assets/images/mail.jpg") . '" height="32px" width="38px" style="margin-top:10px;">  </span>' . $company['email'] . '<br>
            $header .= '
            <!--<span href="https://www.cloudbillingmanager.com/"><img src="' . base_url("assets/images/web.jpg") . '" height="32px" width="38px" style="margin-top:10px;"> </span>www.cloudbillingmanager.com -->
            </div><div style="clear:both">  
            <span href="mailto:' . $company['email'] . '"><img src="' . base_url("assets/images/mail.jpg") . '" height="32px" width="38px" style="margin-top:10px;">  </span>' . $company['email'] . '
            <span style="text-align: right; href="https://www.5starfoodsltd.com/"><img src="' . base_url("assets/images/web.jpg") . '" height="32px" width="38px" style="margin-top:10px;">http://www.5starfoodsltd.com/</span>
            &nbsp;&nbsp;&nbsp;&nbsp;<span>Thank you for your business!</span>';

            if ($data['invoice']['inv_type'] == 'INVOICE') {
            }
            $header .= ' </footer></body></html>';
        } else {


            $header .= '<footer style="position:absolute;bottom:-195px">';
            //if ($data['invoice']['inv_type'] == 'INVOICE' ){
            $header .= '<div style="border:1px solid #1367A4;width:50%;height:140px; padding: 8px 0px 5px 4px; border-radius: 6px;float:left;margin-top:5px;display:none"><span style="color:rgb(48, 37, 133)"><b>*MAXIMUM CHANGE ACCEPTED=&#163;25</b></span><div style=" font-size: 10px;">All goods remain the property of ' . $company['cname'] . '
            until all monies are paid in full<br>Any complaints should be made within 48 hours of purchasing.</div><br><div style="width:100%"><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left">Print Name:</div><div style="border:1px solid black;width:10%;height:18px;float:left;margin-left:10px;;margin-left:-78px;"></div><div style="float:left;margin-left:-158px;margin-top:3px;font-size: 12px;"><b>Cash Tendered</b></div><div style="clear:both"></div><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left;">Signature:</div><div style="font-size: 17px;padding-left:10px;border:1px solid black;width:35%;height:20px;float:left;padding-top:3px;padding-bottom:3px;margin-left:-157px;margin-top:-8px">&#163;</div><div style="clear:both"></div></div><div style="clear:both"></div></div>';
            //}

            $sub_ts = 0;
            foreach ($data['products'] as $row) {
                // Removed calculation - using database value instead
            }

            $header .= '<div style="float:right;margin-left:320px;width:280px" ><table class="maintable2" style="width:100%; border:1px solid #1367A4;font-size: 14px;">';


            // $inv_total_amt=$data["invoice"]["total"]+$data["invoice"]["tax"];

            $inv_total_amt = $data["invoice"]["total"];
            $sub_ts = formatDecimal($data["invoice"]["subtotal"]);

            $header .= '<tr><td style="width: 60%;  background: lightgrey;padding:  4px 0px 4px 7px;">&nbsp;Total NET Amount</td><td> ' . amountExchange($data["invoice"]["subtotal"]) . ' </td></tr>
            <tr ><td style="width: 60%;  background: lightgrey;padding: 4px 0px 4px 7px;">&nbsp;Total ' . $this->lang->line('VAT') . ' Amount</td><td> ' . amountExchange($data["invoice"]["tax"]) . '  </td></tr>
            <tr><td style="width: 60%;  background: lightgrey;padding: 4px 0px 4px 7px;">&nbsp;Invoice Total</td><td> ' . amountExchange($inv_total_amt) . '   </td></tr>';

            $header .= '</table></div><div style="clear:both"></div>';

            //if ($data['invoice']['inv_type'] == 'INVOICE' ){ 
            $header .= '<p style="font-size:8px;display:none"> ' . $company['cname'] . ' is the trading name of ' . $company['cname'] . ', Company No. 11671004. &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Bank Account Details: Sort Code: ' . $company['sortcode'] . ' A/C No: ' . $company['taxid'] . '</p>';
            //  } 
            $header .= ' </footer></body></html>';
        }

        // $file_name = preg_replace('/[^A-Za-z0-9]+/', '-', 'Invoice__' . $data['invoice']['name'] . '_' . $data['invoice']['tid']);
        $file_name = $this->input->get('id');
        $this->dpdf->loadHtml($header);
        $this->dpdf->render();
        $output =  $this->dpdf->output();
        $file_location = 'userfiles/invoices_bfr_postr/counter_' . $file_name . '.pdf';
        file_put_contents($file_location, $output);
        $this->dpdf->stream("" . $file_name . ".pdf", array("Attachment" => 0));
    }

    public function printinvoice_bfr_posting()
    {
        // Explicitly load language file for PDF generation
        $system_lang = $this->db->query("SELECT lang FROM geopos_system WHERE id=1 LIMIT 1")->row_array();
        $lang_to_load = $system_lang['lang'] ?? 'english';
        $this->lang->load($lang_to_load, $lang_to_load);

        // Get dynamic tax name for placeholder replacement
        $dynamic_tax_name = getTaxName();

        // Helper function to get and replace language lines
        $that = $this; // Store controller reference for closure
        $lang = function ($key) use ($that, $dynamic_tax_name) {
            $text = $that->lang->line($key);
            if ($text === FALSE || empty($text)) {
                return $key; // Return key if translation not found
            }
            // Replace tax placeholders
            return str_replace(
                array('{tax}', '{Tax}', '{TAX}'),
                array(strtolower($dynamic_tax_name), ucfirst(strtolower($dynamic_tax_name)), strtoupper($dynamic_tax_name)),
                $text
            );
        };

        // DEBUG: Test language replacement
        $test_debug = $this->input->get('debug');
        if ($test_debug == '1') {
            echo "<h3>Language Replacement Debug</h3>";
            echo "<p>Language loaded: <strong>" . $lang_to_load . "</strong></p>";
            echo "<p>Language class: <strong>" . get_class($this->lang) . "</strong></p>";
            echo "<p>Tax from getTaxName(): <strong>" . $dynamic_tax_name . "</strong></p>";
            echo "<p>lang('Tax'): <strong>" . $lang('Tax') . "</strong></p>";
            echo "<p>lang('Quantity'): <strong>" . $lang('Quantity') . "</strong></p>";
            echo "<p>lang('Details'): <strong>" . $lang('Details') . "</strong></p>";
            echo "<p>lang('Total Tax'): <strong>" . $lang('Total Tax') . "</strong></p>";
            die();
        }

        $company = $this->settings->company_details(1);

        $tid = $this->input->get('id');

        $no_print = $this->input->get('no_print');
        if ($no_print) {
            $no_print = $no_print;
        }
        $data['id'] = $tid;
        $data['invoice'] = $this->invocies->invoice_details_bfr_post($tid, $this->limited);
        if ($data['invoice']['id']) $data['products'] = $this->invocies->invoice_products_bfr_post($tid);
        // dd($data['products']);
        if ($data['invoice']['id']) $data['employee'] = $this->invocies->employee_bfr_post($data['invoice']['eid']);
        if ($data['invoice']['i_class'] == 1) {
            $pref = prefix(7);
        } else {
            $pref = $this->config->item('prefix');
        }
        ob_end_clean();
        ob_start();

        $sql = 'SELECT sum(total)-sum(pamnt) as "cust_balance" FROM geopos_invoices WHERE status="due" and csd="' . $data["invoice"]["csd"] . '" or status="partial" and csd="' . $data["invoice"]["csd"] . '"';
        $query = $this->db->query($sql);
        $response = $query->result_array();
        $cust_balance_inv = $response[0]['cust_balance'];

        // $cust_balance_inv= $response[0]['cust_balance'];
        $sql2 = "SELECT content FROM `geopos_notes` ORDER BY `geopos_notes`.`id`  DESC LIMIT 1";
        $query2 = $this->db->query($sql2);
        $response2 = $query2->result_array();
        $content2 = $response2[0]['content'];

        $sql2 = 'SELECT data FROM `geopos_custom_data` where rid="' . $data["invoice"]["csd"] . '"';
        $query2 = $this->db->query($sql2);
        $response2 = $query2->result_array();
        $custom_fields = $response2[0]['data'];
        if ($no_print == 1) {
            $sql = 'Update geopos_invoices_bfr_post set print_status=1 where id= ' . $tid . ' ';
            $this->db->query($sql);
        }




        ini_set('memory_limit', '64M');

        $data_tr = "";

        $left_logo = base_url("userfiles/company/" . $company['logo']);
        $data['left_logo'] = $left_logo;
        $right_logo = base_url("assets/images/halal_logo.jpg");
        $data['right_logo'] = $right_logo;
        ob_end_clean();
        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            $header = '<html><head><style>@page { margin: 350px 50px 250px; }header{ position: fixed;  top: -320px;}footer{ position: fixed; bottom: -220px; height: 240px; }table{width:100%;}.pagenum:after {content: counter(page)}</style> </head><body><header>';
            $header .= ' <div style="width:100%; "><div style="width:23%; float:left; text-align:center;">  <img src="' . $left_logo . '" height="150px" width="180px"> </div><div style="width:48%; margin-left:20px; float:left; text-align:center;"> <div style="font-size: 30px;"><b>' . $company['cname'] . '</b></div><div style="font-size: 14px;"><div>' . $company['address'] . '</div><div>' . $company['region'] . ', ' . $company['city'] . ' ' . $company['postbox'] . ' ' . $company['country'] . '</div><div>Phone: ' . $company['phone'] . '&nbsp;&nbsp;&nbsp;&nbsp; Mobile:&nbsp;&nbsp;' . $company['mobile'] . '</div><div>Email: ' . $company['email'] . '</div></div> </div><span style="float:right;"  class="pagenum">Page:</span><div style="width:25%; float:left; text-align:center;">  <img src="' . $right_logo . '" height="100px" width="100px"> 
      </div></div><div style="clear:both"></div>';
        } else {
            $header = '<html><head><style>@page { margin: 350px 50px 250px; }header{ position: fixed;  top: -320px;}footer{ position: fixed; bottom: -220px; height: 240px; }table{width:100%;}.pagenum:after {content: counter(page)}</style> </head><body><header><span style="float:right;"  class="pagenum">Page:</span>';
            $header .= ' <div style="width:100%; "><div style="width:23%; float:left; text-align:center;">
      </div></div><div><p style="text-align:center;height:100px;">Note: This is not a valid Invoice  </p></div><div style="clear:both"></div>';
        }

        $header .= '<div style="border:1px solid #1367A4;width:40%;height:100px; padding: 8px 10px 8px; border-radius: 6px;float:left;margin-top:5px;">';
        if ($data['invoice']['company']) {
            $header .= $data['invoice']['company'] . '<br>';
        }

        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            $header .= $data['invoice']['cust_address'] . '<br/>' . $data['invoice']['cust_city'] . '<br/>' . $data['invoice']['cust_postcode'] . "";
            if ($data['invoice']['pamt_terms']) {
                $header .= '<br><b><span style="background-color:yellow">TERMS:</span></b> ' . $custom_fields;
            }
        } else {
            $header .= ' <br> ' . $data['invoice']['cust_address'] . '<br/>' . $data['invoice']['cust_city'] . '<br/>' . $data['invoice']['cust_postcode'] . "";
        }

        $header .= '</div><div style="float:right;margin-left:-150px;margin-top:-30px;width:50%" ><div style=" font-size: 18px; margin-top: 12px">';



        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            $header .= '<b>&nbsp;Invoice</b>';
        } else {
            $header .= '&nbsp;';
        }
        $header .= '</div><table style="width:100%; font-size: 14px; border:1px solid #1367A4;"><tr><td style="width: 50%; border-bottom:1px solid #1367A4;background: lightgrey;padding: 8px 0px 8px 7px;">&nbsp;Invoice No.</td>
       <td  style="border-bottom:1px solid #1367A4;">';

        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            $header .= $data['invoice']['tid'];
        } else {
            $header .= ' Day Pass ';
        }

        $header .= ' </td></tr><tr><td style="width: 50%; border-bottom:1px solid #1367A4;background: lightgrey;padding:  8px 0px 8px 7px;">&nbsp;Invoice Date</td><td style="border-bottom:1px solid #1367A4;"> ' . date("d-m-Y", strtotime($data["invoice"]["invoicedate"])) . ' </td></tr>';


        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            $header .= '  <tr><td style="width: 50%; background: lightgrey;padding: 8px 0px 8px 7px;">&nbsp;Account No.</td><td> ' . $data["invoice"]["name"] . '</td></tr>';
        } else {
        }
        $header .= '</table><div>';




        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            $header .= ' ';
            $header .= '
           
            <table style="width:100%; font-size: 14px; border:1px solid #1367A4; margin-top:5px;"><tr><td style="width: 100%;padding: 8px 0px 8px 7px;">&nbsp;<b> Chilled Products 0-4&deg;C <span style="border:1px solid black;">&nbsp;&nbsp;&nbsp;</span> Frozen Products -18&deg;C <span style="border:1px solid black;">&nbsp;&nbsp;&nbsp;</span></b></td></tr></table>
            
            ';
        }
        $header .= '</div></div></header>';


        $header .= '<div style="clear:both"></div><main><div style="width:100%;  ">
        <table width="100%" class="myProducts" style="border:1px solid #1367A4;">
        <thead><tr style="font-size: 14px; background:#ccc; padding:4px;">
        <th style="width: 12%; text-align:left; ">' . $lang('Quantity') . '</th>
        <th style="border-left: 1px solid #1367A4;width: 45%; text-align:left;">' . $lang('Details') . '</th>
        <th style="width: 13%; text-align:left; border-left: 1px solid #1367A4;">' . $lang('Unit_Rate') . '</th>
        <th style="width: 20%; text-align:left; border-left: 1px solid #1367A4;">' . $lang('Net Amount') . '</th>
        <th style="width: 10%;  text-align:left;border-left: 1px solid #1367A4;">' . $lang('Tax') . '</th>
        </tr></thead>';
        $header .= '<tbody style="font-size:15px;">';

        $ns = 0;
        foreach ($data['products'] as $row) {
            $ns++;
            if ($row['serial'] == '1') {
                $header .= '<tr style="background:#FBF36D; style="font-size:18px;"">';
            } else {
                $header .= '<tr style=" border-bottom:1px solid white;">';
            }
            $header .= '<td style=" border-bottom:1px solid white;">' . (int)$row["qty"] . $row["unit"] . '</td style=" border-bottom:1px solid white;">
                <td style=" border-bottom:1px solid white;">' . $row["product_des"] . '</td>
                <td style=" border-bottom:1px solid white;">' . rev_amountExchange_s($row['price']) . '</td>    
                <td style=" border-bottom:1px solid white;">' . rev_amountExchange_s($row['subtotal']) . '</td>
                <td style=" border-bottom:1px solid white;">' . rev_amountExchange_s($row['totaltax']) . ' </td>
                </tr>';
        }
        $header .= '</tbody></table></div></main>';


        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {

            $header .= '<footer>';
            if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
                $header .= '<div style="border:1px solid #1367A4;width:50%;height:140px; padding: 8px 0px 5px 4px; border-radius: 6px;float:left;margin-top:5px"><span style="color:rgb(48, 37, 133)"><b>*MAXIMUM CHANGE ACCEPTED=&#163;25</b></span><div style=" font-size: 10px;">All goods remain the property of ' . $company['cname'] . ' until all monies are paid in full<br>Any complaints should be made within 48 hours of purchasing.</div><br><div style="width:100%"><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left">Print Name:</div><div style="border:1px solid black;width:10%;height:18px;float:left;margin-left:10px;;margin-left:-78px;"></div><div style="float:left;margin-left:-158px;margin-top:3px;font-size: 12px;"><b>Cash Tendered</b></div><div style="clear:both"></div><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left;">Signature:</div><div style="font-size: 17px;padding-left:10px;border:1px solid black;width:35%;height:20px;float:left;padding-top:3px;padding-bottom:3px;margin-left:-157px;margin-top:-8px">&#163;</div><div style="clear:both"></div></div><div style="clear:both"></div></div>';
            }

            $sub_ts = 0;
            foreach ($data['products'] as $row) {
                // Removed calculation - using database value instead
            }

            $header .= '<div style="float:right;margin-left:320px;width:280px" ><table class="maintable2" style="width:100%; border:1px solid #1367A4;font-size: 14px;">';

            if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
                $header .= '<tr ><td style="width: 50%;  background: lightgrey;padding: 4px 0px 4px 7px;">&nbsp;' . $lang('Previous Balance') . '</td><td>';
                $invbalance = 0;
                if ($cust_balance_inv >= $data['invoice']['total']) {
                    $invbalance =  $cust_balance_inv - $data['invoice']['total'];
                    $header .=  amountExchange($invbalance, $data['invoice']['multi'], $data['invoice']['loc']);
                } else {
                    $header .=  amountExchange($cust_balance_inv, $data['invoice']['multi'], $data['invoice']['loc']);
                }
                $header .= '</td></tr>';
            }

            $header .= '<tr><td style="width: 60%;  background: lightgrey;padding:  4px 0px 4px 7px;">&nbsp;' . $lang('Net Amount') . '     </td>
            <td> ' . amountExchange($data['invoice']['subtotal']) . ' </td></tr>
            <tr ><td style="width: 60%;  background: lightgrey;padding: 4px 0px 4px 7px;">&nbsp;' . $lang('Total Tax Amount') . ' </td>
            <td> ' . amountExchange($data["invoice"]["tax"]) . '  </td></tr>
            <tr><td style="width: 60%;  background: lightgrey;padding: 4px 0px 4px 7px;">&nbsp;' . $lang('Grand Total') . '</td>
            <td> ' . amountExchange($data["invoice"]["total"]) . '   </td></tr>';
            if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {

                $header .= ' <tr><td style="width: 60%; background: lightgrey;padding:  4px 0px 4px 7px;">&nbsp;' . $lang('Balance Now Due') . '</td>
                <td>' . amountExchange($cust_balance_inv) . ' </td></tr> ';
            }
            $header .= '</table><span style="font-size:12px;"><b>' . $company['cname'] . '.</b></span></div><div style="clear:both"></div>';

            if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
                $header .= '<p style="font-size:12px;margin-top:10px;"> ' . $company['cname'] . ' is the trading name of ' . $company['cname'] . '
                <br>Bank Account Details: &nbsp;&nbsp;<b>Sort Code: ' . $company['sortcode'] . '</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>A/C N0.&nbsp; ' . $company['taxid'] . '</b></p>';
            }
            $header .= ' </footer></body></html>';
        } else {


            $header .= '<footer>';
            //if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE' ){
            $header .= '<div style="border:1px solid #1367A4;width:50%;height:140px; padding: 8px 0px 5px 4px; border-radius: 6px;float:left;margin-top:5px;display:none"><span style="color:rgb(48, 37, 133)"><b>*MAXIMUM CHANGE ACCEPTED=&#163;25</b></span><div style=" font-size: 10px;">All goods remain the property of ' . $company['cname'] . ' until all monies are paid in full<br>Any complaints should be made within 48 hours of purchasing.</div><br><div style="width:100%"><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left">Print Name:</div><div style="border:1px solid black;width:10%;height:18px;float:left;margin-left:10px;;margin-left:-78px;"></div><div style="float:left;margin-left:-158px;margin-top:3px;font-size: 12px;"><b>Cash Tendered</b></div><div style="clear:both"></div><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left;">Signature:</div><div style="font-size: 17px;padding-left:10px;border:1px solid black;width:35%;height:20px;float:left;padding-top:3px;padding-bottom:3px;margin-left:-157px;margin-top:-8px">&#163;</div><div style="clear:both"></div></div><div style="clear:both"></div></div>';
            //}

            $header .= '<div style="float:right;margin-left:320px;width:280px" ><table class="maintable2" style="width:100%; border:1px solid #1367A4;font-size: 14px;">';

            $header .= '<tr><td style="width: 60%;  background: lightgrey;padding:  4px 0px 4px 7px;">&nbsp;' . $lang('Net Amount') . '     </td>
            <td> ' . amountExchange($data['invoice']['subtotal']) . ' </td></tr>
            <tr ><td style="width: 60%;  background: lightgrey;padding: 4px 0px 4px 7px;">&nbsp;' . $lang('Total Tax Amount') . ' </td>
            <td> ' . amountExchange($data["invoice"]["tax"]) . '  </td></tr>
            <tr><td style="width: 60%;  background: lightgrey;padding: 4px 0px 4px 7px;">&nbsp;' . $lang('Grand Total') . '</td>
            <td> ' . amountExchange($data["invoice"]["total"]) . '   </td></tr>';

            $header .= '</table></div><div style="clear:both"></div>';

            //if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE' ){ 
            $header .= '<p style="font-size:8px;display:none"> ' . $company['cname'] . ' is the trading name of ' . $company['cname'] . ', Company No. 11671004&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Bank Account Details: Sort Code: 04-06-05 A/C No: 21721837</p>';
            //  } 
            $header .= ' </footer></body></html>';
        }

        // $file_name = preg_replace('/[^A-Za-z0-9]+/', '-', 'Invoice__' . $data['invoice']['name'] . '_' . $data['invoice']['tid']);
        $file_name = $this->input->get('id');
        $this->dpdf->loadHtml($header);
        $this->dpdf->render();
        $output =  $this->dpdf->output();
        $file_location = 'userfiles/invoices/' . $file_name . '.pdf';
        file_put_contents($file_location, $output);
        $this->dpdf->stream("" . $file_name . ".pdf", array("Attachment" => 0));
    }
    public function printinvoice_bfr_posting2()
    {
        // Explicitly load language file for PDF generation
        $system_lang = $this->db->query("SELECT lang FROM geopos_system WHERE id=1 LIMIT 1")->row_array();
        $lang_to_load = $system_lang['lang'] ?? 'english';
        $this->lang->load($lang_to_load, $lang_to_load);
        $dynamic_tax_name = getTaxName();
        $that = $this;
        $lang = function ($key) use ($that, $dynamic_tax_name) {
            $text = $that->lang->line($key);
            if ($text === FALSE || empty($text)) return $key;
            return str_replace(array('{tax}', '{Tax}', '{TAX}'), array(strtolower($dynamic_tax_name), ucfirst(strtolower($dynamic_tax_name)), strtoupper($dynamic_tax_name)), $text);
        };

        $company = $this->settings->company_details(1);

        $tid = $this->input->get('id');

        $no_print = $this->input->get('no_print');
        if ($no_print) {
            $no_print = $no_print;
        }
        $data['id'] = $tid;
        $data['invoice'] = $this->invocies->invoice_details_bfr_post($tid, $this->limited);
        if ($data['invoice']['id']) $data['products'] = $this->invocies->invoice_products_bfr_post($tid);
        // dd($data['products']);
        if ($data['invoice']['id']) $data['employee'] = $this->invocies->employee_bfr_post($data['invoice']['eid']);
        if ($data['invoice']['i_class'] == 1) {
            $pref = prefix(7);
        } else {
            $pref = $this->config->item('prefix');
        }
        ob_end_clean();
        ob_start();

        $sql = 'SELECT sum(total)-sum(pamnt) as "cust_balance" FROM geopos_invoices WHERE status="due" and csd="' . $data["invoice"]["csd"] . '" or status="partial" and csd="' . $data["invoice"]["csd"] . '"';
        $query = $this->db->query($sql);
        $response = $query->result_array();
        $cust_balance_inv = $response[0]['cust_balance'];

        // $cust_balance_inv= $response[0]['cust_balance'];
        $sql2 = "SELECT content FROM `geopos_notes` ORDER BY `geopos_notes`.`id`  DESC LIMIT 1";
        $query2 = $this->db->query($sql2);
        $response2 = $query2->result_array();
        $content2 = $response2[0]['content'];

        $sql2 = 'SELECT data FROM `geopos_custom_data` where rid="' . $data["invoice"]["csd"] . '"';
        $query2 = $this->db->query($sql2);
        $response2 = $query2->result_array();
        $custom_fields = $response2[0]['data'];
        if ($no_print == 1) {
            $sql = 'Update geopos_invoices_bfr_post set print_status=1 where id= ' . $tid . ' ';
            $this->db->query($sql);
        }




        ini_set('memory_limit', '64M');

        $data_tr = "";
        $left_logo = base_url("userfiles/company/" . $company['logo']);
        $data['left_logo'] = $left_logo;
        $right_logo = base_url("assets/images/halal_logo.jpg");
        $data['right_logo'] = $right_logo;
        ob_end_clean();
        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            $header = '<html><head><style>@page { margin: 350px 50px 200px; }header{ position: fixed;  top: -320px;}
            table{width:100%;}.pagenum:after {content: counter(page)}</style> </head><body><header>';
            $header .= '<table style="width:100%;">
            <tr style="border:1px solid #1367A4;">';
            $header .= '<td style="width:100%;"><img src="' . $left_logo . '" height="165px" width="270px"></td>
            <td style="width:40%;"><img src="' . $right_logo . '" height="60px" width="60px"></td>
            <td style="width:100%;"><br><b>' . $company['cname'] . '</b><br>' . $company['address'] . ' ' . $company['city'] . ' ' . $company['region'] . ', ' . $company['postbox'] . ', ' . $company['country'] . ' <br> Phone:&nbsp;&nbsp;&nbsp;' . $company['phone'] . ' <br> Mobile:&nbsp;&nbsp;' . $company['mobile'] . ' <br>
                <table style="border: 1px solid #1367A4;
                border-collapse: collapse;margin-left:-45px;margin-top:10px; border-radius: 6px;width:320px;">
                <tr style="">
                            <th  style="border: 1px solid #1367A4; width:80px;
                            border-collapse: collapse;font-size: 13px;text-align: center" >Date
                                </th>
                                <th  style="border: 1px solid #1367A4;
                                border-collapse: collapse;font-size: 13px;text-align: center">Invoice
                                </th>
                                <th  style="border: 1px solid #1367A4;
                                border-collapse: collapse;font-size: 13px;text-align: center">A/C
                                </th>
                                <th  style="border: 1px solid #1367A4;
                                border-collapse: collapse;font-size: 13px; text-align: center">EC NO 
                                </th>
                                <th  style="border: 1px solid #1367A4;
                                border-collapse: collapse;font-size: 13px; text-align: center">VAT Reg
                                </th>
                </tr>
                <tr >
                    <td style="width:30%;font-size: 13px;border: 1px solid #1367A4;border-collapse: collapse;text-align: center">
                    ' . date("d-m-Y", strtotime($data["invoice"]["invoicedate"])) . '
                    </td>
                    <td style="font-size: 13px;border: 1px solid #1367A4;border-collapse: collapse;text-align: center">' . $data['invoice']['tid'] . '</td>
                    <td style="font-size: 13px;border: 1px solid #1367A4;border-collapse: collapse;text-align: center">' . $data["invoice"]["name"] . '</td>
                    <td style="font-size: 13px;border: 1px solid #1367A4;border-collapse: collapse;text-align: center">882 /2004</td>
                    <td style="font-size: 13px;border: 1px solid #1367A4;border-collapse: collapse;text-align: center">299367533</td>
                </tr>
                </table>
            </td>
            </tr></table>';
        } else {
            $header = '<html><head><style>@page { margin: 350px 50px 200px; }header{ position: fixed;  top: -320px;}table{width:100%;}.pagenum:after {content: counter(page)}</style> </head><body><header><span style="float:right;"  class="pagenum">Page:</span>';
            $header .= ' <div style="width:100%; "><div style="width:23%; float:left; text-align:center;">  <br> Invoice#: ' . $data['invoice']['tid'] . '
      </div></div><div><p style="text-align:center;height:84px;">Note: This is not a valid Invoice  </p></div><div style="clear:both"></div>';
        }

        $header .= '<div style="border:1px solid #1367A4;width:40%;height:85px; font-size:14px; padding: 8px 10px 8px 8px; border-radius: 6px;float:left;margin-top:5px;">';
        if ($data['invoice']['company']) {
            $header .= $data['invoice']['company'] . '<br>';
        }

        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            $header .= $data['invoice']['cust_address'] . ' ' . $data['invoice']['cust_city'] . '<br/>' . $data['invoice']['cust_postcode'];
            if ($data['invoice']['pamt_terms']) {
                $header .= '<br><b> <span style="background-color:yellow">TERMS:</span></b> ' . $custom_fields . '';
            }
        } else {
            $header .= $data['invoice']['cust_address'] . ' ' . $data['invoice']['cust_city'] . '<br/>' . $data['invoice']['cust_postcode'];
            if ($data['invoice']['pamt_terms']) {
                $header .= '<br><b> <span style="background-color:yellow">TERMS:</span> </b>' . $custom_fields . '';
            }
        }

        $header .= '</div><div style="float:right;margin-left:-150px;margin-top:-30px;width:50%;" ><div style=" font-size: 25px;">';
        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            $header .= '<br> ';
            $header .= '</div><table style="height:5%;  font-size: 14px; border:1px solid #1367A4;border-radius: 6px;"> 
        <tr ><td ><b>' . $content2 . '</b></td>
        </tr>';
            $header .= '</table>';
        } else {
            $header .= '<span style="font-size: 32px; transform: translate(-82px, -28px)" >Day Pass</span>';
            if ($data['invoice']['driver_name'] != '' && $data['invoice']['vehicle_no'] != '') {
                $header .= '</div><table style="height:5%;  font-size: 14px; border:1px solid #1367A4;border-radius: 6px;"> 
        
        <tr ><td ><b>Driver Name: </b></td></tr>
        <tr ><td >' . $data['invoice']['driver_name'] . ' ' . $data['invoice']['vehicle_no'] . '</td>
        </tr>';
                $header .= '</table>';
            }
        }

        $header .= '</div></div></header>';
        if ($data['invoice']['inv_type'] == 'INVOICE') {
            $header .= '
           
            <table style="width:100%; font-size: 14px;  margin-top:-15px;"><tr><td style="width: 100%;">&nbsp;<b> 
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            For Bank Transfer:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $company['cname'] . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;S/C&nbsp;&nbsp;' . $company['sortcode'] . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;AC&nbsp;&nbsp;' . $company['taxid'] . '  
            </b></td></tr></table>
            
            ';
        }
        $header .= '<div style="clear:both"></div> <main><div style="width:100%; margin-top:5px; ">
        <table width="100%" class="myProducts" style="border:1px solid #1367A4;border-radius: 6px;">
        <thead><tr style="font-size: 14px; background:#ccc; padding:4px;">
        <th style="width: 12%; text-align:left; ">' . $this->lang->line('Quantity') . '</th>
        <th style="border-left: 1px solid #1367A4;width: 45%; text-align:left;">' . $this->lang->line('Details') . '</th>
        <th style="width: 13%; text-align:left; border-left: 1px solid #1367A4;">' . $this->lang->line('Unit_Rate') . '</th>
        <th style="width: 20%; text-align:left; border-left: 1px solid #1367A4;">' . $this->lang->line('Net Amount') . '</th>
        <th style="width: 10%;  text-align:left;border-left: 1px solid #1367A4;">' . $this->lang->line('Tax') . '</th></tr></thead>';
        $header .= '<tbody style="font-size:15px;">';

        $ns = 0;
        foreach ($data['products'] as $row) {
            $ns++;
            if ($row['serial'] == '1') {
                $header .= '<tr style="background:#FBF36D; style="font-size:18px;"">';
            } else {
                $header .= '<tr style=" border-bottom:1px solid white;">';
            }
            $header .= '<td style=" border-bottom:1px solid white;">' . (int)$row["qty"] . $row["unit"] . '
                    </td style=" border-bottom:1px solid white;">
                    <td style=" border-bottom:1px solid white;">' . $row["product_des"] . '</td>
                    <td style=" border-bottom:1px solid white;">' . rev_amountExchange_s($row['price']) . '</td> 
                    <td style=" border-bottom:1px solid white;">' . rev_amountExchange_s($row['subtotal']) . '</td>
                    <td style=" border-bottom:1px solid white;">' . rev_amountExchange_s($row['totaltax']) . ' </td></tr>';
        }
        $header .= '</tbody></table></div></main>';


        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {

            $header .= '<footer style="position:absolute;bottom:-195px">';
            if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
                $header .= '<div style="border:1px solid #1367A4;width:59%;height:170px; padding: 8px 0px 5px 4px; border-radius: 6px;float:left;"><span style="color:rgb(48, 37, 133)"><b>*MAXIMUM CHANGE ACCEPTED=&#163;60</b></span><div style=" font-size: 10px;">
                    *All claims must be notified at the time of delivery,<br>
                    any claims made after delivery will not be considered.<br>
                    Goods will remain the property of ' . $company['cname'] . ' <br>until paid in full by the customer.<br>
                    Frozen and chilled products cannot be returned once delivered.<br>
                    </div><br><div style="width:100%"><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left">
                    Customer:</div><div style="border:1px solid black;width:8%;height:18px;float:left;margin-top:10px;margin-left:-178px;"></div>
                    <div style="border:1px solid black;width:8%;height:18px;float:left;margin-top:10px;margin-left:-58px;"></div>
                    <div style="border:1px solid black;width:8%;height:18px;float:left;margin-top:10px;margin-left:-118px;"></div>
                    <div style="float:left;margin-left:-178px;margin-top:-5px;font-size: 12px;"><b>Cash &nbsp;&nbsp; Card Payment &nbsp;&nbsp;BACS</b>
                    </div><div style="clear:both"></div><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left;
                    font-size: 12px;">Driver:<b>' . $data['invoice']['driver_name'] . '  ' . $data['invoice']['vehicle_no'] . '</b></div>
                    <div style="font-size: 17px;padding-left:10px;border:1px solid black;width:35%;height:20px;float:left;padding-top:3px;
                    padding-bottom:3px;margin-left:-187px;margin-top:3px">&#163;</div><div style="clear:both"></div></div>
                    <div style="clear:both"></div></div>';
            }

            $sub_ts = 0;
            foreach ($data['products'] as $row) {
                // Removed calculation - using database value instead
            }

            $header .= '<div style="float:right;margin-left:320px;width:280px" ><table class="maintable2" style="width:100%;border-radius: 6px; border:1px solid #1367A4;font-size: 14px;">';

            if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
                // $header.='<tr ><td style="width: 50%;  background: lightgrey;padding: 4px 0px 4px 7px;">&nbsp;Previous Balance</td><td>' ;
                $invbalance = 0;
                if ($cust_balance_inv >= $data['invoice']['total']) {
                    $invbalance =  $cust_balance_inv - $data['invoice']['total'];
                    $header .=  amountExchange($invbalance, $data['invoice']['multi'], $data['invoice']['loc']);
                } else {
                    $header .=  amountExchange($cust_balance_inv, $data['invoice']['multi'], $data['invoice']['loc']);
                }
                $header .= '</td></tr>';
            }


            // if($data['invoice']['inv_type'] == 'DAYPASS') {
            //     $header.=' <tr><td style="width: 60%; background: lightgrey;padding:  4px 0px 4px 7px;">&nbsp;Driver</td><td>'. $data['invoice']['driver_name'] .'  '. $data['invoice']['vehicle_no'].' </td></tr> '; 
            // }
            $header .= '<tr><td style="width: 60%;  background: lightgrey;padding:  4px 0px 4px 7px;">&nbsp;' . $this->lang->line('Net Amount') . '     </td>
            <td> ' . amountExchange($data['invoice']['subtotal']) . ' </td></tr>
            <tr ><td style="width: 60%;  background: lightgrey;padding: 4px 0px 4px 7px;">&nbsp;' . $this->lang->line('Total Tax Amount') . ' </td>
            <td> ' . amountExchange($data["invoice"]["tax"]) . '  </td></tr>
            <tr><td style="width: 60%;  background: lightgrey;padding: 4px 0px 4px 7px;">&nbsp;' . $this->lang->line('Grand Total') . '</td>
            <td> ' . amountExchange($data["invoice"]["total"]) . '   </td></tr>';
            if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {

                // $header.=' <tr><td style="width: 60%; background: lightgrey;padding:  4px 0px 4px 7px;">&nbsp;Balance Now Due</td><td>'.$cust_balance_inv.' </td></tr> ';  
            }

            $header .= '</table>
            <span href="mailto:' . $company['email'] . '"><img src="' . base_url("assets/images/mail.jpg") . '" height="32px" width="38px" style="margin-top:10px;">  </span>' . $company['email'] . '<br>
            <!--<span href="https://www.cloudbillingmanager.com/"><img src="' . base_url("assets/images/web.jpg") . '" height="32px" width="38px" style="margin-top:10px;"> </span>www.cloudbillingmanager.com -->
            </div><div style="clear:both">
            <span style="text-align: center;margin-left:70px;">Thank you for your business!</span>
            </div>';

            if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            }
            $header .= ' </footer></body></html>';
        } else {


            $header .= '<footer style="position:absolute;bottom:-195px">';
            //if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE' ){
            $header .= '<div style="border:1px solid #1367A4;width:50%;height:140px; padding: 8px 0px 5px 4px; border-radius: 6px;float:left;margin-top:5px;display:none"><span style="color:rgb(48, 37, 133)"><b>*MAXIMUM CHANGE ACCEPTED=&#163;25</b></span><div style=" font-size: 10px;">All goods remain the property of ' . $company['cname'] . '
            until all monies are paid in full<br>Any complaints should be made within 48 hours of purchasing.</div><br><div style="width:100%"><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left">Print Name:</div><div style="border:1px solid black;width:10%;height:18px;float:left;margin-left:10px;;margin-left:-78px;"></div><div style="float:left;margin-left:-158px;margin-top:3px;font-size: 12px;"><b>Cash Tendered</b></div><div style="clear:both"></div><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left;">Signature:</div><div style="font-size: 17px;padding-left:10px;border:1px solid black;width:35%;height:20px;float:left;padding-top:3px;padding-bottom:3px;margin-left:-157px;margin-top:-8px">&#163;</div><div style="clear:both"></div></div><div style="clear:both"></div></div>';
            //}

            $sub_ts = 0;
            foreach ($data['products'] as $row) {
                // Removed calculation - using database value instead
            }

            $header .= '<div style="float:right;margin-left:320px;width:280px" ><table class="maintable2" style="width:100%; border:1px solid #1367A4;font-size: 14px;">';


            // $inv_total_amt=$data["invoice"]["total"]+$data["invoice"]["tax"];

            $inv_total_amt = $data["invoice"]["total"];
            $sub_ts = formatDecimal($data["invoice"]["subtotal"]);

            $header .= '<tr><td style="width: 60%;  background: lightgrey;padding:  4px 0px 4px 7px;">&nbsp;' . $this->lang->line('Net Amount') . '     </td>
            <td> ' . amountExchange($data['invoice']['subtotal']) . ' </td></tr>
            <tr ><td style="width: 60%;  background: lightgrey;padding: 4px 0px 4px 7px;">&nbsp;' . $this->lang->line('Total Tax Amount') . ' </td>
            <td> ' . amountExchange($data["invoice"]["tax"]) . '  </td></tr>
            <tr><td style="width: 60%;  background: lightgrey;padding: 4px 0px 4px 7px;">&nbsp;' . $this->lang->line('Grand Total') . '</td>
            <td> ' . amountExchange($data["invoice"]["total"]) . '   </td></tr>';

            $header .= '</table></div><div style="clear:both"></div>';

            //if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE' ){ 
            $header .= '<p style="font-size:8px;display:none"> ' . $company['cname'] . ' is the trading name of ' . $company['cname'] . ', Company No. 11671004. &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Bank Account Details: Sort Code: ' . $company['sortcode'] . ' A/C No: ' . $company['taxid'] . '</p>';
            //  } 
            $header .= ' </footer></body></html>';
        }

        // $file_name = preg_replace('/[^A-Za-z0-9]+/', '-', 'Invoice__' . $data['invoice']['name'] . '_' . $data['invoice']['tid']);
        $file_name = $this->input->get('id');
        $this->dpdf->loadHtml($header);
        $this->dpdf->render();
        $output =  $this->dpdf->output();
        $file_location = 'userfiles/invoices_bfr_postr/sale_' . $file_name . '.pdf';
        file_put_contents($file_location, $output);
        $this->dpdf->stream("" . $file_name . ".pdf", array("Attachment" => 0));
    }
    public function printinvoice_bfr_posting3()
    {
        // Explicitly load language file for PDF generation
        $system_lang = $this->db->query("SELECT lang FROM geopos_system WHERE id=1 LIMIT 1")->row_array();
        $lang_to_load = $system_lang['lang'] ?? 'english';
        $this->lang->load($lang_to_load, $lang_to_load);
        $dynamic_tax_name = getTaxName();
        $that = $this;
        $lang = function ($key) use ($that, $dynamic_tax_name) {
            $text = $that->lang->line($key);
            if ($text === FALSE || empty($text)) return $key;
            return str_replace(array('{tax}', '{Tax}', '{TAX}'), array(strtolower($dynamic_tax_name), ucfirst(strtolower($dynamic_tax_name)), strtoupper($dynamic_tax_name)), $text);
        };
        $company = $this->settings->company_details(1);

        $tid = $this->input->get('id');

        $no_print = $this->input->get('no_print');
        if ($no_print) {
            $no_print = $no_print;
        }
        $data['id'] = $tid;
        $data['invoice'] = $this->invocies->invoice_details_bfr_post($tid, $this->limited);
        if ($data['invoice']['id']) $data['products'] = $this->invocies->invoice_products_bfr_post($tid);
        // dd($data['products']);
        if ($data['invoice']['id']) $data['employee'] = $this->invocies->employee_bfr_post($data['invoice']['eid']);
        if ($data['invoice']['i_class'] == 1) {
            $pref = prefix(7);
        } else {
            $pref = $this->config->item('prefix');
        }
        ob_end_clean();
        ob_start();

        $sql = 'SELECT sum(total)-sum(pamnt) as "cust_balance" FROM geopos_invoices WHERE status="due" and csd="' . $data["invoice"]["csd"] . '" or status="partial" and csd="' . $data["invoice"]["csd"] . '"';
        $query = $this->db->query($sql);
        $response = $query->result_array();
        $cust_balance_inv = $response[0]['cust_balance'];

        // $cust_balance_inv= $response[0]['cust_balance'];
        $sql2 = "SELECT content FROM `geopos_notes` ORDER BY `geopos_notes`.`id`  DESC LIMIT 1";
        $query2 = $this->db->query($sql2);
        $response2 = $query2->result_array();
        $content2 = $response2[0]['content'];

        $sql2 = 'SELECT data FROM `geopos_custom_data` where rid="' . $data["invoice"]["csd"] . '"';
        $query2 = $this->db->query($sql2);
        $response2 = $query2->result_array();
        $custom_fields = $response2[0]['data'];
        if ($no_print == 1) {
            $sql = 'Update geopos_invoices_bfr_post set print_status=1 where id= ' . $tid . ' ';
            $this->db->query($sql);
        }




        ini_set('memory_limit', '64M');

        $data_tr = "";

        $left_logo = base_url("userfiles/company/" . $company['logo']);
        $data['left_logo'] = $left_logo;
        $right_logo = base_url("assets/images/halal_logo.jpg");
        $data['right_logo'] = $right_logo;
        ob_end_clean();
        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            $header = '<html><head><style> * {font-family: sans-serif !important;} @page { margin: 350px 50px 200px;}header{ position: fixed;  top: -320px;}table{width:100%;}.pagenum:after {content: counter(page)}</style> </head><body><header>';
            $header .= ' <div style="width:100%;"><div style="width:25%; float:left; text-align:center; margin-bottom:4px">  <img src="' . $left_logo . '" height="140px" width="155px"> </div><div style="width:75%; margin-left:20px; float:left; text-align:center; color:#f8faff; background-color: #25468d; border-bottom-left-radius:10em;  padding: 18px 0;"> <div style="font-size: 20px;"><b>' . $company['cname'] . '</b></div><div style="font-size: 14px;"><div>' . $company['address'] . '</div><div>' . $company['region'] . ', ' . $company['city'] . ' ' . $company['postbox'] . ' ' . $company['country'] . '</div><div>Phone: ' . $company['phone'] . '&nbsp;&nbsp;&nbsp;&nbsp;Mobile:&nbsp;&nbsp;' . $company['mobile'] . '</div><div>Email: ' . $company['email'] . '</div></div> </div> 
      </div><div style="clear:both;"></div>';
        } else {
            $header = '<html><head><style>@page { margin: 350px 50px 200px; }header{ position: fixed;  top: -320px;}table{width:100%;}.pagenum:after {content: counter(page)}</style> </head><body><header><span style="float:right;"  class="pagenum">Page:</span>';
            $header .= ' <div style="width:100%; "><div style="width:23%; float:left; text-align:center;">
      </div></div><div><p style="text-align:center;height:100px;">Note: This is not a valid Invoice  </p></div><div style="clear:both"></div>';
        }

        $header .= '<div style="width:40%;height:100px; border-radius: 0px;float:left;margin-top:55px;">';
        $header .= '<b>&nbsp;Customer\'s Info</b>';
        $header .= '<div style="border:1px solid #25468D; padding: 8px 10px 8px;">';
        if ($data['invoice']['company']) {
            $header .= $data['invoice']['company'] . '<br>';
        }

        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            $header .= $data['invoice']['cust_address'] . '<br/>' . $data['invoice']['cust_city'] . '<br/>' . $data['invoice']['cust_postcode'] . "";
            if ($data['invoice']['pamt_terms']) {
                $header .= '<br><b><span style="background-color:yellow">TERMS:</span></b> ' . $custom_fields;
            }
        } else {
            $header .= ' <br> ' . $data['invoice']['cust_address'] . '<br/>' . $data['invoice']['cust_city'] . '<br/>' . $data['invoice']['cust_postcode'] . "";
        }

        $header .= '</div></div><div style="float:right;margin-left:-150px;margin-top:0px;width:50%" ><div style=" font-size: 18px">';



        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            $header .= '<b>&nbsp;Invoice</b>';
        } else {
            $header .= '&nbsp;';
        }
        $header .= '</div><table style="width:100%; font-size: 14px; border:1px solid #25468D;"><tr><td style="width: 50%; border-bottom:1px solid #25468D;background: #f1f4fb;padding: 4px;">&nbsp;Invoice No.</td>
       <td  style="border-bottom:1px solid #25468D;">';

        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            $header .= $data['invoice']['tid'];
        } else {
            $header .= ' Day Pass ';
        }

        $header .= ' </td></tr><tr><td style="width: 50%; border-bottom:1px solid #25468D;background: #f1f4fb;padding:  4px;">&nbsp;Invoice Date</td><td style="border-bottom:1px solid #25468D;"> ' . date("d-m-Y", strtotime($data["invoice"]["invoicedate"])) . ' </td></tr>';


        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            $header .= '  <tr><td style="width: 50%; background: #f1f4fb;padding: 4px;">&nbsp;Account No.</td><td> ' . $data["invoice"]["name"] . '</td></tr>';
        } else {
        }
        $header .= '</table><div>';
        $header .= '</div></div></header>';


        $header .= '<div style="clear:both"></div><main><div style="width:100%;  ">
        <table width="100%" class="myProducts" style="border:1px solid #25468D;">
        <thead><tr style="font-size: 14px; background:#f1f4fb; padding:4px;">
        <th style="width: 12%; text-align:left; ">' . $this->lang->line('Quantity') . '</th>
        <th style="border-left: 1px solid #25468D;width: 45%; text-align:left;">' . $this->lang->line('Details') . '</th>
        <th style="width: 13%; text-align:left; border-left: 1px solid #25468D;">' . $this->lang->line('Unit_Rate') . '</th>
        <th style="width: 20%; text-align:left; border-left: 1px solid #25468D;">' . $this->lang->line('Net Amount') . '</th>
        <th style="width: 10%;  text-align:left;border-left: 1px solid #25468D;">' . $this->lang->line('Tax') . '</th></tr></thead>';
        $header .= '<tbody style="font-size:15px;">';

        $ns = 0;
        foreach ($data['products'] as $row) {
            $ns++;
            if ($row['serial'] == '1') {
                $header .= '<tr style="background:#FBF36D; style="font-size:18px;"">';
            } else {
                $header .= '<tr style=" border-bottom:1px solid white;">';
            }
            $header .= '<td style=" border-bottom:1px solid white;">' . (int)$row["qty"] . $row["unit"] . '</td style=" border-bottom:1px solid white;">
                <td style=" border-bottom:1px solid white;">' . $row["product_des"] . '</td>
                <td style=" border-bottom:1px solid white;">' . rev_amountExchange_s($row['price']) . '</td> 
                <td style=" border-bottom:1px solid white;">' . rev_amountExchange_s($row['subtotal']) . '</td>
                <td style=" border-bottom:1px solid white;">' . rev_amountExchange_s($row['totaltax']) . ' </td></tr>';
        }
        $header .= '</tbody></table></div></main>';


        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {

            $header .= '<footer style="position:absolute;bottom:-195px; border-bottom:20px solid #F36E38;">';
            if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
                $header .= '<div style="border:1px solid #25468D;width:50%;height:140px; padding: 8px; border-radius: 0px; float:left;margin-top:5px"><span style="color:#25468d"><b>*MAXIMUM CHANGE ACCEPTED=&#163;25</b></span><div style=" font-size: 10px;">All goods remain the property of ' . $company['cname'] . ' until all monies are paid in full<br>Any complaints should be made within 48 hours of purchasing.</div><br><div style="width:100%"><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;  font-size: 10px; float:left">Print Name:</div><div style="border:1px solid black;width:10%;height:18px;float:left;margin-left:10px;;margin-left:-78px;"></div><div style="float:left;margin-left:-158px;margin-top:3px;font-size: 10px;"><b>Cash Tendered</b></div><div style="clear:both"></div><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;  font-size: 10px; float:left;">Signature:</div><div style="padding-left:10px;border:1px solid black;width:35%;height:20px;float:left;padding-top:3px;padding-bottom:3px;margin-left:-157px;  font-size: 10px; margin-top:-8px">&#163;</div><div style="clear:both"></div></div><div style="clear:both"></div></div>';
            }

            $sub_ts = 0;
            foreach ($data['products'] as $row) {
                // Removed calculation - using database value instead
            }

            $header .= '<div style="float:right;margin-left:320px;width:280px" ><table class="maintable2" style="width:100%; border:1px solid #25468D;font-size: 14px;">';

            if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
                $header .= '<tr ><td style="width: 50%;  background: #f1f4fb;padding: 3px 8px; font-size:12px">&nbsp;' . $this->lang->line('Previous Balance') . '</td>
                <td style="font-size: 12px">';
                $invbalance = 0;
                if ($cust_balance_inv >= $data['invoice']['total']) {
                    $invbalance =  $cust_balance_inv - $data['invoice']['total'];
                    // $header .=  substr(amountExchange($invbalance, $data['invoice']['multi'], $data['invoice']['loc']), 3, 100);
                    $header .=  amountExchange($invbalance, $data['invoice']['multi'], $data['invoice']['loc']);
                } else {
                    $header .=  amountExchange($cust_balance_inv, $data['invoice']['multi'], $data['invoice']['loc']);
                    // $header .=  substr(amountExchange($cust_balance_inv, $data['invoice']['multi'], $data['invoice']['loc']), 3, 100);
                }
                $header .= '</td></tr>';
            }

            $header .= '<tr><td style="width: 60%;  background: #f1f4fb;padding:  3px 8px; font-size:12px">&nbsp;' . $this->lang->line('Net Amount') . '</td>
            <td style="font-size: 12px"> ' . amountExchange($data["invoice"]["subtotal"]) . ' </td></tr>
            <tr ><td style="width: 60%;  background: #f1f4fb;padding: 3px 8px; font-size:12px">' . $this->lang->line('Total Tax Amount') . '</td>
            <td style="font-size: 12px"> ' . amountExchange($data["invoice"]["tax"]) . '  </td></tr>
            <tr><td style="width: 60%;  background: #f1f4fb;padding: 3px 8px; font-size:12px">' . $this->lang->line('Grand Total') . '</td>
            <td style="font-size: 12px"> ' . amountExchange($data["invoice"]["total"]) . '   </td></tr>';
            if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {

                $header .= ' <tr><td style="width: 60%; background: #f1f4fb;padding:  3px 8px; font-size:12px">&nbsp;' . $this->lang->line('Balance Now Due') . '</td>
                <td style="font-size: 12px">' . amountExchange($cust_balance_inv) . ' </td></tr> ';
                $header .= ' <tr><td style="width: 60%; background: #f1f4fb;padding:  3px 8px; font-size:12px">&nbsp;Driver Name</td><td style="font-size: 12px">' . $data['invoice']['driver_name'] . ' </td></tr> ';
                $header .= ' <tr><td style="width: 60%; background: #f1f4fb;padding:  3px 8px; font-size:12px">&nbsp;Vehicle No.</td><td style="font-size: 12px">' . $data['invoice']['vehicle_no'] . ' </td></tr> ';
            }
            $header .= '</table></div><div style="clear:both"></div>';

            if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
                $header .= '<p style="font-size:12px">Powered By <a style="text-decoration: none; font-weight: 600; color: #25468d" href="https://cloudbillingmanager.com/" target="_blank">Cloud Billing Manger</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Bank Account Details: &nbsp;&nbsp;<b> ' . $company['taxid'] . '</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Sort Code: &nbsp; <b>' . $company['sortcode'] . '</b></p>
    
        ';
            }
            $header .= ' </footer></body></html>';
        } else {


            $header .= '<footer style="position:absolute;bottom:-195px">';
            //if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE' ){
            $header .= '<div style="border:1px solid #25468D;width:50%;height:140px; padding: 8px 0px 5px 4px; border-radius: 6px;float:left;margin-top:5px;display:none"><span style="color:rgb(48, 37, 133)"><b>*MAXIMUM CHANGE ACCEPTED=&#163;25</b></span><div style=" font-size: 10px;">All goods remain the property of ' . $company['cname'] . ' until all monies are paid in full<br>Any complaints should be made within 48 hours of purchasing.</div><br><div style="width:100%"><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left">Print Name:</div><div style="border:1px solid black;width:10%;height:18px;float:left;margin-left:10px;;margin-left:-78px;"></div><div style="float:left;margin-left:-158px;margin-top:3px;font-size: 12px;"><b>Cash Tendered</b></div><div style="clear:both"></div><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left;">Signature:</div><div style="font-size: 17px;padding-left:10px;border:1px solid black;width:35%;height:20px;float:left;padding-top:3px;padding-bottom:3px;margin-left:-157px;margin-top:-8px">&#163;</div><div style="clear:both"></div></div><div style="clear:both"></div></div>';
            //}

            $sub_ts = 0;
            foreach ($data['products'] as $row) {
                // Removed calculation - using database value instead
            }

            $header .= '<div style="float:right;margin-left:320px;width:280px" ><table class="maintable2" style="width:100%; border:1px solid #25468D;font-size: 14px;">';


            $inv_total_amt = $data["invoice"]["total"] + $data["invoice"]["tax"];
            $sub_ts = formatDecimal($data["invoice"]["subtotal"]);

            $header .= '<tr><td style="width: 60%;  background: #f1f4fb;padding:  3px 8px; font-size:12px">&nbsp;' . $this->lang->line('Net Amount') . '</td>
            <td style="font-size: 12px"> ' . amountExchange($data["invoice"]["subtotal"]) . ' </td></tr>
            <tr ><td style="width: 60%;  background: #f1f4fb;padding: 3px 8px; font-size:12px">' . $this->lang->line('Total Tax Amount') . '</td>
            <td style="font-size: 12px"> ' . amountExchange($data["invoice"]["tax"]) . '  </td></tr>
            <tr><td style="width: 60%;  background: #f1f4fb;padding: 3px 8px; font-size:12px">' . $this->lang->line('Grand Total') . '</td>
            <td style="font-size: 12px"> ' . amountExchange($data["invoice"]["total"]) . '   </td></tr>';

            $header .= '</table></div><div style="clear:both"></div>';

            //if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE' ){ 
            $header .= '<p style="font-size:8px;display:none">Powered By <a href="https://cloudbillingmanager.com/" target="_blank">Cloud Billing Manger</a>, Company No. 11671004&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Bank Account Details: Sort Code: 04-06-05 A/C No: 21721837</p>';
            //  } 
            $header .= ' </footer></body></html>';
        }

        // $file_name = preg_replace('/[^A-Za-z0-9]+/', '-', 'Invoice__' . $data['invoice']['name'] . '_' . $data['invoice']['tid']);
        $file_name = $this->input->get('id');
        $this->dpdf->loadHtml($header);
        $this->dpdf->render();
        $output =  $this->dpdf->output();
        $file_location = 'userfiles/invoices/' . $file_name . '.pdf';
        file_put_contents($file_location, $output);
        $this->dpdf->stream("" . $file_name . ".pdf", array("Attachment" => 0));
    }
    public function printinvoice_bfr_posting4()
    {
        // Explicitly load language file for PDF generation
        $system_lang = $this->db->query("SELECT lang FROM geopos_system WHERE id=1 LIMIT 1")->row_array();
        $lang_to_load = $system_lang['lang'] ?? 'english';
        $this->lang->load($lang_to_load, $lang_to_load);
        $dynamic_tax_name = getTaxName();
        $that = $this;
        $lang = function ($key) use ($that, $dynamic_tax_name) {
            $text = $that->lang->line($key);
            if ($text === FALSE || empty($text)) return $key;
            return str_replace(array('{tax}', '{Tax}', '{TAX}'), array(strtolower($dynamic_tax_name), ucfirst(strtolower($dynamic_tax_name)), strtoupper($dynamic_tax_name)), $text);
        };
        $company = $this->settings->company_details(1);

        $tid = $this->input->get('id');

        $no_print = $this->input->get('no_print');
        if ($no_print) {
            $no_print = $no_print;
        }
        $data['id'] = $tid;
        $data['invoice'] = $this->invocies->invoice_details_bfr_post($tid, $this->limited);
        if ($data['invoice']['id']) $data['products'] = $this->invocies->invoice_products_bfr_post($tid);
        // dd($data['products']);
        if ($data['invoice']['id']) $data['employee'] = $this->invocies->employee_bfr_post($data['invoice']['eid']);
        if ($data['invoice']['i_class'] == 1) {
            $pref = prefix(7);
        } else {
            $pref = $this->config->item('prefix');
        }
        ob_end_clean();
        ob_start();

        $sql = 'SELECT sum(total)-sum(pamnt) as "cust_balance" FROM geopos_invoices WHERE status="due" and csd="' . $data["invoice"]["csd"] . '" or status="partial" and csd="' . $data["invoice"]["csd"] . '"';
        $query = $this->db->query($sql);
        $response = $query->result_array();
        $cust_balance_inv = $response[0]['cust_balance'];

        // $cust_balance_inv= $response[0]['cust_balance'];
        $sql2 = "SELECT content FROM `geopos_notes` ORDER BY `geopos_notes`.`id`  DESC LIMIT 1";
        $query2 = $this->db->query($sql2);
        $response2 = $query2->result_array();
        $content2 = $response2[0]['content'];

        $sql2 = 'SELECT data FROM `geopos_custom_data` where rid="' . $data["invoice"]["csd"] . '"';
        $query2 = $this->db->query($sql2);
        $response2 = $query2->result_array();
        $custom_fields = $response2[0]['data'];
        if ($no_print == 1) {
            $sql = 'Update geopos_invoices_bfr_post set print_status=1 where id= ' . $tid . ' ';
            $this->db->query($sql);
        }




        ini_set('memory_limit', '64M');

        $data_tr = "";

        $left_logo = base_url("userfiles/company/" . $company['logo']);
        $data['left_logo'] = $left_logo;
        $right_logo = base_url("assets/images/halal_logo.jpg");
        $data['right_logo'] = $right_logo;
        ob_end_clean();
        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            $header = '<html><head><style> * {font-family: sans-serif;} @page { margin: 350px 50px 200px;}header{ position: fixed;  top: -320px;}table{width:100%;}.pagenum:after {content: counter(page)}</style> </head><body><header>';
            $header .= ' <div style="width:100%;"><div style="width:25%; float:left; text-align:center; margin-bottom:4px">  <img src="' . $left_logo . '" height="140px" width="155px"></div><div style="width:75%; float:left; text-align:center; margin-bottom:4px; margin-top:24px; font-size: 28px; font-weight:600;">' . $company['cname'] . '
            <div style="font-size:13px; font-weight:400; text-align:center; margin-top: 8px;"><table>
            <tr>
                <td><b>Invoice No: </b>' . $data['invoice']['tid'] . '</td>
                <td><b>Invoice Date: </b>' . date("d-m-Y", strtotime($data["invoice"]["invoicedate"])) . '</td>
                <td><b>Account No: </b>' . $data["invoice"]["name"] . '</td>
            </tr>
        </table></div></div>
            
            <div style="clear:both;"></div>
            <div style="width:378px; top: 0px; right:-180px;  color:#f8faff; background-color: #f36e38; position: absolute !important; transform: rotate(45deg);  "> <div style=" line-height:24px; width: 100%; text-transform: capitalize; font-size: 18px; text-align:center; padding-bottom: 8px;">' . $data['invoice']['status'] . '</div></div> </div> 
      </div><div style="clear:both;"></div>';
        } else {
            $header = '<html><head><style>@page { margin: 350px 50px 200px; }header{ position: fixed;  top: -320px;}table{width:100%;}.pagenum:after {content: counter(page)}</style> </head><body><header><span style="float:right;"  class="pagenum">Page:</span>';
            $header .= ' <div style="width:100%; "><div style="width:23%; float:left; text-align:center;">
      </div></div><div><p style="text-align:center;height:100px;">Note: This is not a valid Invoice  </p></div><div style="clear:both"></div>';
        }

        $header .= '<div style="width:40%;height:100px; border-radius: 0px;float:left;margin-top:55px;">';
        $header .= '<div style="font-size: 14px;"><div>' . $company['address'] . '</div><div>' . $company['region'] . ', ' . $company['city'] . ' ' . $company['postbox'] . ' ' . $company['country'] . '</div><div>Phone: ' . $company['phone'] . '</div><div>Mobile:&nbsp;&nbsp;' . $company['mobile'] . '</div><div>Email: ' . $company['email'] . '</div>';

        $header .= '</div></div><div style="float:right;margin-left:-150px;margin-top:0px;width:50%" ><div style=" font-size: 12px">';

        $header .= '<b style=" font-size: 14px">&nbsp;Customer\'s Info</b>';
        $header .= '<div style="border-top:1px solid #25468D; padding: 0 4px;">';
        if ($data['invoice']['company']) {
            $header .= '<b>Name: </b> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $data['invoice']['company'] . '<br>';
        }

        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            $header .= '<b>Address: </b>&nbsp;' . $data['invoice']['cust_address'] . ' ' . $data['invoice']['cust_city'] . ' - ' . $data['invoice']['cust_postcode'] . "";
            if ($data['invoice']['pamt_terms']) {
                $header .= '<br><b><span style="background-color:yellow">TERMS:</span></b> ' . $custom_fields;
            }
        } else {
            $header .= ' <br> ' . $data['invoice']['cust_address'] . '<br/>' . $data['invoice']['cust_city'] . ' ' . $data['invoice']['cust_postcode'] . "";
        }

        $header .= '</div></div></div></header>';


        $header .= '<div style="clear:both"></div><main><div style="width:100%;  ">
        <table width="100%" class="myProducts"><thead><tr style="font-size: 14px; background:#f1f4fb; padding:4px;">
        <th style="width: 12%; text-align:left; ">' . $this->lang->line('Quantity') . '</th>
        <th style="border-left: 1px solid #25468D;width: 45%; text-align:left;">' . $this->lang->line('Details') . '</th>
        <th style="width: 13%; text-align:left; border-left: 1px solid #25468D;">' . $this->lang->line('Unit_Rate') . '</th>
        <th style="width: 20%; text-align:left; border-left: 1px solid #25468D;">' . $this->lang->line('Net Amount') . '</th>
        <th style="width: 10%;  text-align:left;border-left: 1px solid #25468D;">' . $this->lang->line('Tax') . '</th></tr></thead>';
        $header .= '<tbody style="font-size:15px;">';

        $ns = 0;
        foreach ($data['products'] as $row) {
            $ns++;
            if ($row['serial'] == '1') {
                $header .= '<tr style="background:#FBF36D; style="font-size:18px;"">';
            } else {
                $header .= '<tr style=" border-bottom:1px solid white;">';
            }
            $header .= '<td style=" border-bottom:1px solid white;">' . (int)$row["qty"] . $row["unit"] . '</td style=" border-bottom:1px solid white;">
                <td style=" border-bottom:1px solid white;">' . $row["product_des"] . '</td>
                <td style=" border-bottom:1px solid white;">' . rev_amountExchange_s($row['price']) . '</td> 
                <td style=" border-bottom:1px solid white;">' . rev_amountExchange_s($row['subtotal']) . '</td>
                <td style=" border-bottom:1px solid white;">' . rev_amountExchange_s($row['totaltax']) . ' </td></tr>';
        }
        $header .= '</tbody></table></div></main>';


        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {

            $header .= '<footer style="position:absolute;bottom:-195px; border-bottom:20px solid #25468D;">';
            if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
                $header .= '<div style="#25468D;width:50%;height:140px; padding: 8px; border-radius: 0px; float:left;margin-top:5px"><span style="color:#25468d"><b>*MAXIMUM CHANGE ACCEPTED=&#163;25</b></span><div style=" font-size: 10px;">All goods remain the property of ' . $company['cname'] . ' until all monies are paid in full<br>Any complaints should be made within 48 hours of purchasing.</div><br><div style="width:100%"><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;  font-size: 10px; float:left">Print Name:</div><div style="border:1px solid black;width:10%;height:18px;float:left;margin-left:10px;;margin-left:-78px;"></div><div style="float:left;margin-left:-158px;margin-top:3px;font-size: 10px;"><b>Cash Tendered</b></div><div style="clear:both"></div><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;  font-size: 10px; float:left;">Signature:</div><div style="padding-left:10px;border:1px solid black;width:35%;height:20px;float:left;padding-top:3px;padding-bottom:3px;margin-left:-157px;  font-size: 10px; margin-top:-8px">&#163;</div><div style="clear:both"></div></div><div style="clear:both"></div></div>';
            }

            $header .= '<div style="float:right;margin-left:320px;width:280px" ><table class="maintable2" style="width:100%; #25468D;font-size: 14px;">';

            if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
                $header .= '<tr ><td style="width: 50%;  background: #f1f4fb;padding: 3px 8px; font-size:12px">&nbsp;' . $this->lang->line('Previous Balance') . '</td>
                <td style="font-size: 12px">';
                $invbalance = 0;
                if ($cust_balance_inv >= $data['invoice']['total']) {
                    $invbalance =  $cust_balance_inv - $data['invoice']['total'];
                    $header .=  amountExchange($invbalance, $data['invoice']['multi'], $data['invoice']['loc']);
                    // $header .=  substr(amountExchange($invbalance, $data['invoice']['multi'], $data['invoice']['loc']), 3, 100);
                } else {
                    // $header .=  substr(amountExchange($cust_balance_inv, $data['invoice']['multi'], $data['invoice']['loc']), 3, 100);
                    $header .=  amountExchange($cust_balance_inv, $data['invoice']['multi'], $data['invoice']['loc']);
                }
                $header .= '</td></tr>';
            }

            $header .= '<tr><td style="width: 60%;  background: #f1f4fb;padding:  3px 8px; font-size:12px">&nbsp;' . $this->lang->line('Net Amount') . '</td>
            <td style="font-size: 12px"> ' . amountExchange($data['invoice']['subtotal']) . ' </td></tr>
            <tr ><td style="width: 60%;  background: #f1f4fb;padding: 3px 8px; font-size:12px">' . $this->lang->line('Total Tax Amount') . '</td>
            <td style="font-size: 12px"> ' . amountExchange($data["invoice"]["tax"]) . '  </td></tr>
            <tr><td style="width: 60%;  background: #f1f4fb;padding: 3px 8px; font-size:12px">' . $this->lang->line('Grand Total') . '</td>
            <td style="font-size: 12px"> ' . amountExchange($data["invoice"]["total"]) . '   </td></tr>';
            if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {

                $header .= ' <tr><td style="width: 60%; background: #f1f4fb;padding:  3px 8px; font-size:12px">' . $this->lang->line('Balance Now Due') . '</td><td style="font-size: 12px">' . amountExchange($cust_balance_inv) . ' </td></tr> ';
                $header .= ' <tr><td style="width: 60%; background: #f1f4fb;padding:  3px 8px; font-size:12px">' . $this->lang->line('DriverName') . '</td><td style="font-size: 12px">' . $data['invoice']['driver_name'] . ' </td></tr> ';
                $header .= ' <tr><td style="width: 60%; background: #f1f4fb;padding:  3px 8px; font-size:12px">' . $this->lang->line('VehicleNo') . '</td><td style="font-size: 12px">' . $data['invoice']['vehicle_no'] . ' </td></tr> ';
            }
            $header .= '</table></div><div style="clear:both"></div>';

            if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
                $header .= '<p style="font-size:12px">Powered By <a style="text-decoration: none; font-weight: 600; color: #25468d" href="https://cloudbillingmanager.com/" target="_blank">Cloud Billing Manger</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Bank Account Details: &nbsp;&nbsp;<b> ' . $company['taxid'] . '</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Sort Code: &nbsp; <b>' . $company['sortcode'] . '</b></p>
    
        ';
            }
            $header .= ' </footer></body></html>';
        } else {


            $header .= '<footer style="position:absolute;bottom:-195px">';
            //if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE' ){
            $header .= '<div style="#25468D;width:50%;height:140px; padding: 8px 0px 5px 4px; float:left;margin-top:5px;display:none"><span style="color:rgb(48, 37, 133)"><b>*MAXIMUM CHANGE ACCEPTED=&#163;25</b></span><div style=" font-size: 10px;">All goods remain the property of ' . $company['cname'] . ' until all monies are paid in full<br>Any complaints should be made within 48 hours of purchasing.</div><br><div style="width:100%"><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left">Print Name:</div><div style="border:1px solid black;width:10%;height:18px;float:left;margin-left:10px;;margin-left:-78px;"></div><div style="float:left;margin-left:-158px;margin-top:3px;font-size: 12px;"><b>Cash Tendered</b></div><div style="clear:both"></div><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left;">Signature:</div><div style="font-size: 17px;padding-left:10px;border:1px solid black;width:35%;height:20px;float:left;padding-top:3px;padding-bottom:3px;margin-left:-157px;margin-top:-8px">&#163;</div><div style="clear:both"></div></div><div style="clear:both"></div></div>';
            //}

            $sub_ts = 0;
            foreach ($data['products'] as $row) {
                // Removed calculation - using database value instead
            }

            $header .= '<div style="float:right;margin-left:320px;width:280px" ><table class="maintable2" style="width:100%; border:1px solid #25468D;font-size: 14px;">';



            $header .= '<tr><td style="width: 60%;  background: #f1f4fb;padding:  4px 0px 4px 7px;">&nbsp;' . $this->lang->line('Net Amount') . '</td><td> ' . amountExchange($data['invoice']['subtotal']) . ' </td></tr>
            <tr ><td style="width: 60%;  background: #f1f4fb;padding: 4px 0px 4px 7px;">&nbsp;' . $this->lang->line('Total Tax Amount') . '</td>
            <td> ' . amountExchange($data["invoice"]["tax"]) . '  </td></tr>
            <tr><td style="width: 60%;  background: #f1f4fb;padding: 4px 0px 4px 7px;">&nbsp;' . $this->lang->line('Grand Total') . '</td><td> ' . amountExchange($data["invoice"]["total"]) . '   </td></tr>';

            $header .= '</table></div><div style="clear:both"></div>';

            //if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE' ){ 
            $header .= '<p style="font-size:8px;display:none">Powered By <a href="https://cloudbillingmanager.com/" target="_blank">Cloud Billing Manger</a>, Company No. 11671004&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Bank Account Details: Sort Code: 04-06-05 A/C No: 21721837</p>';
            //  } 
            $header .= ' </footer></body></html>';
        }

        // $file_name = preg_replace('/[^A-Za-z0-9]+/', '-', 'Invoice__' . $data['invoice']['name'] . '_' . $data['invoice']['tid']);
        $file_name = $this->input->get('id');
        $this->dpdf->loadHtml($header);
        $this->dpdf->render();
        $output =  $this->dpdf->output();
        $file_location = 'userfiles/invoices/' . $file_name . '.pdf';
        file_put_contents($file_location, $output);
        $this->dpdf->stream("" . $file_name . ".pdf", array("Attachment" => 0));
    }
    public function printinvoice_bfr_posting5($printing = true, $id = -1)
    {
        // Explicitly load language file for PDF generation
        $system_lang = $this->db->query("SELECT lang FROM geopos_system WHERE id=1 LIMIT 1")->row_array();
        $lang_to_load = $system_lang['lang'] ?? 'english';
        $this->lang->load($lang_to_load, $lang_to_load);
        $dynamic_tax_name = getTaxName();
        $that = $this;
        $lang = function ($key) use ($that, $dynamic_tax_name) {
            $text = $that->lang->line($key);
            if ($text === FALSE || empty($text)) return $key;
            return str_replace(array('{tax}', '{Tax}', '{TAX}'), array(strtolower($dynamic_tax_name), ucfirst(strtolower($dynamic_tax_name)), strtoupper($dynamic_tax_name)), $text);
        };
        $company = $this->settings->company_details(1);

        $tid = $this->input->get('id');

        $no_print = $this->input->get('no_print');
        if ($no_print) {
            $no_print = $no_print;
        }
        $data['id'] = $tid;
        $data['invoice'] = $this->invocies->invoice_details_bfr_post($tid, $this->limited);
        if ($data['invoice']['id']) $data['products'] = $this->invocies->invoice_products_bfr_post($tid);
        // dd($data['products']);
        if ($data['invoice']['id']) $data['employee'] = $this->invocies->employee_bfr_post($data['invoice']['eid']);
        if ($data['invoice']['i_class'] == 1) {
            $pref = prefix(7);
        } else {
            $pref = $this->config->item('prefix');
        }
        ob_end_clean();
        ob_start();

        $sql = 'SELECT sum(total)-sum(pamnt) as "cust_balance" FROM geopos_invoices WHERE status="due" and csd="' . $data["invoice"]["csd"] . '" or status="partial" and csd="' . $data["invoice"]["csd"] . '"';
        $query = $this->db->query($sql);
        $response = $query->result_array();
        $cust_balance_inv = $response[0]['cust_balance'];

        // $cust_balance_inv= $response[0]['cust_balance'];
        $sql2 = "SELECT content FROM `geopos_notes` ORDER BY `geopos_notes`.`id`  DESC LIMIT 1";
        $query2 = $this->db->query($sql2);
        $response2 = $query2->result_array();
        $content2 = $response2[0]['content'];

        $sql2 = 'SELECT data FROM `geopos_custom_data` where rid="' . $data["invoice"]["csd"] . '"';
        $query2 = $this->db->query($sql2);
        $response2 = $query2->result_array();
        $custom_fields = $response2[0]['data'];
        if ($no_print == 1) {
            $sql = 'Update geopos_invoices_bfr_post set print_status=1 where id= ' . $tid . ' ';
            $this->db->query($sql);
        }




        ini_set('memory_limit', '64M');

        $data_tr = "";

        $left_logo = base_url("userfiles/company/" . $company['logo']);
        $data['left_logo'] = $left_logo;
        $right_logo = base_url("assets/images/halal_logo.jpg");
        $data['right_logo'] = $right_logo;
        // $left_logo = base_url("assets/images/dixy_logo_.jpeg");
        //     $data['left_logo'] = $left_logo;
        //     $right_logo = base_url("assets/images/halal_logo.jpg");
        //     $data['right_logo'] = $right_logo;
        ob_end_clean();
        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            $header = '<html><head><style>@page { margin: 350px 50px 240px; } header{ position: fixed;  top: -320px;}
            table{width:100%;}.pagenum:after {content: counter(page)}</style> </head><body><header>';
            $header = '<html><head><style>
                        @page { margin: 350px 50px 200px; }
                        header { position: fixed; top: -330px; }
                        table { width: 100%; }
                        .pagenum:after { content: counter(page); }
                    </style></head><body><header>';

            $header .= '<table style="width: 100%;">
                        <tr style="border: 1px solid #1367A4;">
                            <td style="width: 100%;">
                                <br><b>' . $company['cname'] . '</b><br>' . $company['address'] . '<br>' . $company['city'] . ' ' . $company['region'] . ', ' . $company['postbox'] . ',' . $company['country'] . '<br> Phone:&nbsp;&nbsp;&nbsp;&nbsp;' . $company['phone'] . '<br>Mobile:&nbsp;&nbsp;&nbsp;' . $company['mobile'] . '
                            </td>
                            <td style="width: 40%;"><img src="' . $right_logo . '" height="60px" width="60px"></td>
                            <td style="width: 100%;"><img src="' . $left_logo . '" height="140px" width="250px"></td>
                        </tr>
                    </table>';

            $header .= '<div style="width: 100%; display: flex; justify-content: space-between;
            align-items: flex-start; margin-top: 8px;">';
            $header .= '<div style="border: 1px solid #1367A4; padding: 0 10px; border-radius: 6px;
            font-size:12px;">';
            $header .= '<span style="width: 100%; display: block;">
                <b>' . $content2 . '</b>
            </span>';
            $header .= '</div>';  // Closing the inner div
            $header .= '</div>';  // Closing the outer div
        } else {
            $header = '<html><head><style>@page { margin: 350px 50px 200px; }header{ position: fixed;  top: -320px;}table{width:100%;}.pagenum:after {content: counter(page)}</style> </head><body>
            <header><span style="float:right;"  class="pagenum">Page:</span>';
            $header .= ' <div style="width:100%; "><div style="width:23%; float:left; text-align:center;">  <br> Invoice#: ' . $data['invoice']['tid'] . '
                </div></div><div><p style="text-align:center;height:84px;">Note: This is not a valid Invoice  </p>
                    Date: ' . date("d-m-Y", strtotime($data["invoice"]["invoicedate"])) . '
                </div><div style="clear:both"></div>';
        }

        $header .= '<div style="width: 100%; display: flex; justify-content: space-between;
        align-items: flex-start; margin-top: auto; font-size:12px;">';
        // Left Box
        $header .= '<div style="border: 1px solid #1367A4; height: auto; width: 25%; padding: 10px;
        border-radius: 6px;" >';
        if ($data['invoice']['company']) {
            $header .= $data['invoice']['company'] . '<br>';
        }
        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            $header .= $data['invoice']['cust_address'] . '<br/>' . $data['invoice']['cust_city'] . '<br>' . $data['invoice']['cust_postcode'];
            if ($data['invoice']['pamt_terms']) {
                $header .= '<br><b><span style="background-color: yellow;">TERMS:</span></b> ' . $custom_fields;
            }
        } else {
            $header .= $data['invoice']['cust_address'] . '<br/>' . $data['invoice']['cust_city']  . '<br>' . $data['invoice']['cust_postcode'];
            if ($data['invoice']['pamt_terms']) {
                $header .= '<br><b> <span style="background-color:yellow">TERMS:</span> </b>' . $custom_fields . '';
            }
        }
        $header .= '</div>';

        // Center Box (for Bank Transfer Details)
        $header .= '<div style="width: 34%; display: flex; justify-content: flex-end; margin-left: 233px;">';
        if ($data['invoice']['inv_type'] == 'INVOICE') {
            $header .= '<div style="padding: 10px; text-align: center;">';
            $header .= '<b>For Bank Transfer:</b><br>';
            $header .= $company['cname'] . '<br>';
            $header .= 'S/C ' . $company['sortcode'] . '<br>';
            $header .= 'AC ' . $company['taxid'];
            $header .= '</div>';
        }
        $header .= '</div>';
        // Right Box (for Invoice Details)
        $header .= '<div style="width: 30%; display: flex; justify-content: flex-end; margin-left: 498px;">';
        $header .= '<table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <th style="border: 1px solid #1367A4; padding: 5px; text-align: left;">Date:</th>
                        <td style="border: 1px solid #1367A4; padding: 5px; text-align: right;">' . date("d-m-Y", strtotime($data["invoice"]["invoicedate"])) . '</td>
                    </tr>
                    <tr>
                        <th style="border: 1px solid #1367A4; padding: 5px; text-align: left;">Invoice:</th>
                        <td style="border: 1px solid #1367A4; padding: 5px; text-align: right;">' . $data['invoice']['tid'] . '</td>
                    </tr>
                    <tr>
                        <th style="border: 1px solid #1367A4; padding: 5px; text-align: left;">A/C:</th>
                        <td style="border: 1px solid #1367A4; padding: 5px; text-align: right;">' . $data["invoice"]["name"] . '</td>
                    </tr>
                    <tr>
                        <th style="border: 1px solid #1367A4; padding: 5px; text-align: left;">EC NO:</th>
                        <td style="border: 1px solid #1367A4; padding: 5px; text-align: right;">882 /2004</td>
                    </tr>
                    <tr>
                        <th style="border: 1px solid #1367A4; padding: 5px; text-align: left;">' . $this->lang->line('VAT') . ' Reg:</th>
                        <td style="border: 1px solid #1367A4; padding: 5px; text-align: right;">299367533</td>
                    </tr>
                </table>';
        $header .= '</div>'; // Closing the Right Box
        $header .= '</div>';
        $header .= '</header>';  // Closing the header and HTML structure
        $header .= '<div style="clear:both"></div><main><div style="width:100%;  "><table width="100%" class="myProducts" style="border:1px solid #1367A4;"><thead><tr style="font-size: 14px; background:#ccc; padding:4px;"><th style="width: 12%; text-align:left; ">' . $lang('Quantity') . '</th><th style="border-left: 1px solid #1367A4;width: 45%; text-align:left;">' . $lang('Details') . '</th><th style="width: 13%; text-align:left; border-left: 1px solid #1367A4;">' . $lang('Unit_Rate') . '</th><th style="width: 20%; text-align:left; border-left: 1px solid #1367A4;">' . $lang('Net Amount') . '</th><th style="width: 10%;  text-align:left;border-left: 1px solid #1367A4;">' . $lang('Tax') . '</th></tr></thead>';
        $header .= '<tbody style="font-size:15px;">';

        $ns = 0;
        foreach ($data['products'] as $row) {
            $ns++;
            // Removed calculation - using database value instead
            // Using rev_amountExchange_s() directly in output - no need for variables
            $netamount = amountExchange($row['subtotal'] - $row['totaltax'], $invoice['multi'], $invoice['loc']);
            if (empty($row['product_des']) || $row['product_des'] == "") {
            } else {
                if ($row['serial'] == '1') {
                    $header .= '<tr style="background:#FBF36D; style="font-size:18px;"">';
                } else {
                    $header .= '<tr style=" border-bottom:1px solid white;">';
                }
                $header .= '<td style=" border-bottom:1px solid white;">' . (int)$row["qty"] . $row["unit"] . '</td style=" border-bottom:1px solid white;"><td style=" border-bottom:1px solid white;">' . $row["product_des"] . '</td><td style=" border-bottom:1px solid white;">' . rev_amountExchange_s($row['price']) . '</td> <td style=" border-bottom:1px solid white;">' . rev_amountExchange_s($row['subtotal']) . '</td><td style=" border-bottom:1px solid white;">' . rev_amountExchange_s($row['totaltax']) . ' </td></tr>';
            }
        }
        $header .= '</tbody></table></div></main>';
        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {

            $header .= '<footer style="position:fixed; bottom:10px;">';
            if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
                $header .= '<div style="width: 70%; float: left;">';

                // Initialize subtotal
                $sub_ts = 0;
                foreach ($data['products'] as $row) {
                    // Removed calculation - using database value instead
                }

                // Define a reusable cell style

                // Start the table with proper borders and collapse styles
                $header .= '<table style="border:1px solid #1367A4; border-collapse: collapse; font-size:12px;">';

                // Add rows with proper border styling and alignment
                $header .= '<tr>
                        <td style="border:1px solid #1367A4; width:20%; padding: 5px;">Total NET Amount</td>
                        <td style="border:1px solid #1367A4; width:20%; padding: 5px;">' . amountExchange($data["invoice"]["subtotal"]) . '</td>
                        <td style="border:1px solid #1367A4; border-bottom:none; padding: 5px;"></td>
                        <td style="border:1px solid #1367A4; width:15%; padding: 5px;">Cash</td>
                        <td style="border:1px solid #1367A4; padding: 5px;"></td>
                        <td style="border:1px solid #1367A4; width:15%; padding: 5px;">Sign/Print</td>
                    </tr>';

                $header .= '<tr>
                        <td style="border:1px solid #1367A4; width:20%; padding: 5px;">Total ' . $this->lang->line('VAT') . ' Amount</td>
                        <td style="border:1px solid #1367A4; width:20%; padding: 5px;">' . amountExchange($data["invoice"]["tax"]) . '</td>
                        <td style="text-align:center; font-weight:bold; padding: 5px;">Total Amount</td>
                        <td style="border:1px solid #1367A4; width:15%; padding: 5px;">Card/CHQ</td>
                        <td style="border:1px solid #1367A4; padding: 5px;"></td>
                        <td style="border:1px solid #1367A4; width:15%; padding: 5px;"></td>
                    </tr>';

                // Add Previous Balance row if needed
                if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
                    if (!$hide_balance) {
                        // Balance calculation logic
                    }
                }
                if (!$hide_balance) {
                    $invbalance = 0;
                    if ($cust_balance_inv >= $data['invoice']['total']) {
                        $invbalance = $cust_balance_inv - $data['invoice']['total'];
                        $previous_balance = substr(amountExchange($invbalance, $data['invoice']['multi'], $data['invoice']['loc']), 3, 100);
                    } else {
                        $previous_balance = substr(amountExchange($cust_balance_inv, $data['invoice']['multi'], $data['invoice']['loc']), 3, 100);
                    }
                    $balance_now_due = $cust_balance_inv;

                    $header .= '<tr>
                            <td style="border:1px solid #1367A4; width:20%; padding: 5px;">Previous Balance</td>
                            <td style="border:1px solid #1367A4; width:20%; padding: 5px;">' . amountExchange($previous_balance) . '</td>
                            <td style="text-align:center; font-weight:bold; padding: 5px;">' . amountExchange($data["invoice"]["total"]) . '</td>
                            <td style="border:1px solid #1367A4; padding: 5px; width:15%;">BACS</td>
                            <td style="border:1px solid #1367A4; padding: 5px;"></td>
                            <td style="border:1px solid #1367A4; padding: 5px; width:15%;"></td>
                        </tr>';
                    $header .= '<tr>
                            <td style="border:1px solid #1367A4; width:20%; padding: 5px;">Balance Now Due</td>
                            <td style="border:1px solid #1367A4; width:20%; padding: 5px;">' . amountExchange($balance_now_due) . '</td>
                            <td style="border-left:1px solid #1367A4; padding: 5px;"></td>
                            <td style="border:1px solid #1367A4; padding: 5px; width:15%;">Total</td>
                            <td style="border:1px solid #1367A4; padding: 5px;"></td>
                            <td style="border:1px solid #1367A4; padding: 5px; width:15%;"></td>
                        </tr>';
                } else {
                    $header .= '<tr>
                            <td style="padding: 15px; width:20%;"></td>
                            <td style="padding: 15px; width:20%;"></td>
                            <td style="border-left:1px solid #1367A4; text-align:center; font-weight:bold; padding: 5px;">' . amountExchange($invbalance) . '</td>
                            <td style="border:1px solid #1367A4; padding: 5px; width:15%;">BACS</td>
                            <td style="border:1px solid #1367A4; padding: 5px;"></td>
                            <td style="border:1px solid #1367A4; padding: 5px; width:15%;"></td>
                        </tr>';
                    $header .= '<tr>
                            <td style="padding: 15px; width:20%;"></td>
                            <td style="padding: 15px; width:20%;"></td>
                            <td style="border:1px solid #1367A4; border-top:none; padding: 5px;"></td>
                            <td style="border:1px solid #1367A4; padding: 5px; width:15%;">Total</td>
                            <td style="border:1px solid #1367A4; padding: 5px;"></td>
                            <td style="border:1px solid #1367A4; padding: 5px; width:15%;"></td>
                        </tr>';
                }

                $header .= '</table>';


                $header .= '
                <span style="color:rgb(48, 37, 133);font-size: 12px;text-align:center;"><b>*MAXIMUM CHANGE ACCEPTED=&#163;60</b> </span>
                <div style=" font-size: 12px;">
                *All claims must be notified at the time of delivery,
                any claims made after delivery will not be considered. Goods will remain
                the property of ' . $company['cname'] . ' until paid in full by the customer.Frozen and  products cannot be returned once delivered.
                </div>';
                $header .= '<div style="clear: both;"></div>';
                $header .= '</div>'; // End of main div


                // Add the remaining content
            }



            $sub_ts = 0;
            foreach ($data['products'] as $row) {
                // Removed calculation - using database value instead
            }

            $header .= '<div style="float:right; margin-left:530px; font-size: 14px;">
            <table class="maintable2" style="border: 1px solid #1367A4; border-collapse: collapse; width: 100%;">';

            // Add "Office Use Only" header row
            $header .= '<tr>
                        <th style="border: 1px solid #1367A4; text-align: center; padding: 8px;" colspan="2">&nbsp;Office Use Only</th>
                    </tr>';

            // Add row for "Updated"
            $header .= '<tr>
                        <td style="width: 50%; border: 1px solid #1367A4; padding: 4px 0px 4px 7px;">Updated</td>
                        <td style="width: 50%; border: 1px solid #1367A4; padding: 4px 0px 4px 7px;"></td>
                    </tr>';

            // Add row for "Payment Allocated"
            $header .= '<tr>
                        <td style="width: 50%; border: 1px solid #1367A4; padding: 0px 0px 0px 7px;">Payment Allocated</td>
                        <td style="width: 50%; border: 1px solid #1367A4; padding: 0px 0px 0px 7px;"></td>
                    </tr>';

            // Add an empty row
            $header .= '<tr>
                        <td style="padding: 4px 0px 4px 7px; border: 1px solid #1367A4; border-right: none;">&nbsp;</td>
                        <td></td>
                    </tr>';

            // Additional logic based on invoice type and company
            if ($data['invoice']['inv_type'] == 'INVOICE' || $data['invoice']['inv_type'] == 'DAYPASS' && $data['invoice']['company'] != 'COUNTER SALE') {
                // If there's additional balance logic, it would go here
            }

            $header .= '</table>

            <span style="font-size:12px; text-align: center; href="mailto:' . $company['email'] . '">
            <img src="' . base_url("assets/images/mail.jpg") . '" height="30px" width="35px" style="margin-top:8px;">  </span>' . $company['email'] . '
            <br><span style="font-size:12px; text-align: center; margin-left:10px;">Thank you for your business!</span>
            </div>
            <div style="clear: both;"></div>';
            if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            }
            $header .= ' </footer></body></html>';
        } else {


            $header .= '<footer style="position:fixed;bottom:-195px;">';
            //if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE' ){
            $header .= '<div style="border:1px solid #1367A4;width:50%;height:140px; padding: 8px 0px 5px 4px; border-radius: 6px;float:left;margin-top:5px;display:none">
            <span style="color:rgb(48, 37, 133) font-size: 12px;"><b>*MAXIMUM CHANGE ACCEPTED=&#163;25</b></span><div style=" font-size: 12px;">All goods remain the property of ' . $company['cname'] . ' 
            until all monies are paid in full<br>Any complaints should be made within 48 hours of purchasing.</div><br><div style="width:100%"><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left">Print Name:</div><div style="border:1px solid black;width:10%;height:18px;float:left;margin-left:10px;;margin-left:-78px;"></div><div style="float:left;margin-left:-158px;margin-top:3px;font-size: 12px;"><b>Cash Tendered</b></div><div style="clear:both"></div><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left;">Signature:</div><div style="font-size: 17px;padding-left:10px;border:1px solid black;width:35%;height:20px;float:left;padding-top:3px;padding-bottom:3px;margin-left:-157px;margin-top:-8px">&#163;</div><div style="clear:both"></div></div><div style="clear:both"></div></div>';
            //}

            $sub_ts = 0;
            foreach ($data['products'] as $row) {
                // Removed calculation - using database value instead
            }

            $header .= '<div style="float:right;margin-left:320px;width:280px" ><table class="maintable2" style="width:100%; border:1px solid #1367A4;font-size: 14px;">';


            //  $inv_total_amt=$data["invoice"]["total"]+$data["invoice"]["tax"];

            $inv_total_amt = $data["invoice"]["total"];
            $sub_ts = formatDecimal($data["invoice"]["subtotal"]);

            $header .= '<tr><td style="width: 60%;  padding:  4px 0px 4px 7px;">&nbsp;Total NET Amount</td><td> ' . $sub_ts . ' </td></tr>
            <tr ><td style="width: 60%;  background: #f1f4fb;padding: 4px 0px 4px 7px;">&nbsp;Total ' . $this->lang->line('VAT') . ' Amount</td><td> ' . $data["invoice"]["tax"] . '  </td></tr><tr><td style="width: 60%;  background: #f1f4fb;padding: 4px 0px 4px 7px;">&nbsp;
             Invoice Total</td><td> ' . $inv_total_amt . '   </td></tr>';
            if (!$hide_balance) {
                $header .= '<tr ><td style="width: 50%;  background: #f1f4fb;padding: 4px 0px 4px 7px;">&nbsp;Previous Balance</td><td>';
                $invbalance = 0;
                if ($cust_balance_inv >= $data['invoice']['total']) {
                    $invbalance =  $cust_balance_inv - $data['invoice']['total'];
                    $header .=  substr(amountExchange($invbalance, $data['invoice']['multi'], $data['invoice']['loc']), 3, 100);
                } else {
                    $header .=  substr(amountExchange($cust_balance_inv, $data['invoice']['multi'], $data['invoice']['loc']), 3, 100);
                }
                $header .= '</td></tr>';
                $header .= ' <tr><td style="width: 60%; background: #f1f4fb;padding:  4px 0px 4px 7px;">
                &nbsp;Balance Now Due</td><td>' . $cust_balance_inv . ' </td></tr> ';
            }

            $header .= '</table></div><div style="clear:both"></div>';

            //if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE' ){ 
            $header .= '<p style="font-size:8px;display:none"> ' . $company['cname'] . ' 
            is the trading name of ' . $company['cname'] . ', Company No. 11671004. &nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp; Bank Account Details: Sort Code: ' . $company['sortcode'] . ' A/C No: '
                . $company['taxid'] . '</p>';
            //  } 
            $header .= ' </footer></body></html>';
        }

        // $file_name = preg_replace('/[^A-Za-z0-9]+/', '-', 'Invoice__' . $data['invoice']['name'] . '_' . $data['invoice']['tid']);
        // $tid = $id;
        // if($tid == -1) {
        //     $tid = $this->input->get('id');
        // }
        $file_name = $tid;
        $this->dpdf->loadHtml($header);
        $this->dpdf->render();
        $output =  $this->dpdf->output();
        $file_location = 'userfiles/invoices/' . $file_name . '.pdf';
        file_put_contents($file_location, $output);
        if ($printing) {
            $this->dpdf->stream("" . $file_name . ".pdf", array("Attachment" => 0));
        }
    }
    public function printinvoice_bfr_posting6($printing = true, $id = -1)
    {
        // Explicitly load language file for PDF generation
        $system_lang = $this->db->query("SELECT lang FROM geopos_system WHERE id=1 LIMIT 1")->row_array();
        $lang_to_load = $system_lang['lang'] ?? 'english';
        $this->lang->load($lang_to_load, $lang_to_load);
        $dynamic_tax_name = getTaxName();
        $that = $this;
        $lang = function ($key) use ($that, $dynamic_tax_name) {
            $text = $that->lang->line($key);
            if ($text === FALSE || empty($text)) return $key;
            return str_replace(array('{tax}', '{Tax}', '{TAX}'), array(strtolower($dynamic_tax_name), ucfirst(strtolower($dynamic_tax_name)), strtoupper($dynamic_tax_name)), $text);
        };
        $company = $this->settings->company_details(1);

        $tid = $this->input->get('id');

        $no_print = $this->input->get('no_print');
        if ($no_print) {
            $no_print = $no_print;
        }
        $data['id'] = $tid;
        $data['invoice'] = $this->invocies->invoice_details_bfr_post($tid, $this->limited);
        if ($data['invoice']['id']) $data['products'] = $this->invocies->invoice_products_bfr_post($tid);
        // dd($data['products']);
        if ($data['invoice']['id']) $data['employee'] = $this->invocies->employee_bfr_post($data['invoice']['eid']);
        if ($data['invoice']['i_class'] == 1) {
            $pref = prefix(7);
        } else {
            $pref = $this->config->item('prefix');
        }
        ob_end_clean();
        ob_start();

        $sql = 'SELECT sum(total)-sum(pamnt) as "cust_balance" FROM geopos_invoices WHERE status="due" and csd="' . $data["invoice"]["csd"] . '" or status="partial" and csd="' . $data["invoice"]["csd"] . '"';
        $query = $this->db->query($sql);
        $response = $query->result_array();
        $cust_balance_inv = $response[0]['cust_balance'];

        // $cust_balance_inv= $response[0]['cust_balance'];
        $sql2 = "SELECT content FROM `geopos_notes` ORDER BY `geopos_notes`.`id`  DESC LIMIT 1";
        $query2 = $this->db->query($sql2);
        $response2 = $query2->result_array();
        $content2 = $response2[0]['content'];

        $sql2 = 'SELECT data FROM `geopos_custom_data` where rid="' . $data["invoice"]["csd"] . '"';
        $query2 = $this->db->query($sql2);
        $response2 = $query2->result_array();
        $custom_fields = $response2[0]['data'];
        if ($no_print == 1) {
            $sql = 'Update geopos_invoices_bfr_post set print_status=1 where id= ' . $tid . ' ';
            $this->db->query($sql);
        }




        ini_set('memory_limit', '64M');

        $data_tr = "";

        $left_logo = base_url("userfiles/company/" . $company['logo']);
        $data['left_logo'] = $left_logo;
        $right_logo = base_url("assets/images/halal_logo.jpg");
        $data['right_logo'] = $right_logo;
        // $left_logo = base_url("assets/images/dixy_logo_.jpeg");
        //     $data['left_logo'] = $left_logo;
        //     $right_logo = base_url("assets/images/halal_logo.jpg");
        //     $data['right_logo'] = $right_logo;
        ob_end_clean();
        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            $header = '<html><head><style>@page { margin: 350px 50px 240px; } header{ position: fixed;  top: -320px;}
            table{width:100%;}.pagenum:after {content: counter(page)}</style> </head><body><header>';
            $header = '<html><head><style>
                        @page { margin: 350px 50px 200px; }
                        header { position: fixed; top: -340px; }
                        table { width: 100%; }
                        .pagenum:after { content: counter(page); }
                    </style></head><body><header>';

            $header .= '<table style="width: 100%;">
                        <tr style="border: 1px solid #1367A4;">
                        <td style="width: 100%;">
                            <b>' . $company['cname'] . '</b><br>' . $company['address'] . '<br>' . $company['city'] . ' ' . $company['region'] . ', ' . $company['postbox'] . '<br>' . $company['country'] . '<br> Phone:&nbsp;&nbsp;&nbsp;&nbsp;' . $company['phone'] . '<br>Mobile:&nbsp;&nbsp;&nbsp;' . $company['mobile'] . '
                        </td>
                        <td style="width: 40%;"><img src="' . $right_logo . '" height="60px" width="60px"></td>
                        <td style="width: 100%;"><img src="' . $left_logo . '" height="165px" width="270px"></td>
                        </tr>
                    </table>';
        } else {
            $header = '<html><head><style>@page { margin: 350px 50px 200px; }header{ position: fixed;  top: -320px;}table{width:100%;}.pagenum:after {content: counter(page)}</style> </head><body>
            <header><span style="float:right;"  class="pagenum">Page:</span>';
            $header .= ' <div style="width:100%; "><div style="width:23%; float:left; text-align:center;">  <br> Invoice#: ' . $data['invoice']['tid'] . '
                </div></div><div><p style="text-align:center;height:84px;">Note: This is not a valid Invoice  </p>
                    Date: ' . date("d-m-Y", strtotime($data["invoice"]["invoicedate"])) . '
                </div><div style="clear:both"></div>';
        }
        // Customer Info
        $header .= '<div style="width:40%;height:100px; border-radius: 0px;float:left;">';
        $header .= '<b>&nbsp;Customer\'s Info</b>';
        $header .= '<div style="border:1px solid #25468D; padding: 8px 10px 8px;">';
        if ($data['invoice']['company']) {
            $header .= $data['invoice']['company'] . '<br>';
        }

        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            $header .= $data['invoice']['cust_address'] . ' ' . $data['invoice']['cust_city'] . '<br/>' . $data['invoice']['cust_postcode'] . "";
            if ($data['invoice']['pamt_terms']) {
                $header .= '<br><b><span style="background-color:yellow">TERMS:</span></b> ' . $custom_fields;
            }
            // if ($data['invoice']['country']) {
            //      $header.= '<br>' . $data['invoice']['country'];
            // }
        } else {
            $header .= ' <br> ' . $data['invoice']['cust_address'] . '<br/>' . $data['invoice']['cust_city'] . '<br/>' . $data['invoice']['cust_postcode'] . "";
        }
        // if ($data['invoice']['postbox']){
        //     $header.=' <br> ' . $data['invoice']['cust_address'];
        // } 
        // if ($data['invoice']['email']){
        //     $header.= '<br> ' . $this->lang->line('Email') . ': ' . $data['invoice']['email'];
        // }

        $header .= '</div></div><div style="float:right;margin-left:-150px;margin-top:0px;width:50%" ><div style=" font-size: 18px">';



        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            $header .= '<b>&nbsp;Invoice</b>';
        } else {
            $header .= '&nbsp;';
        }
        $header .= '</div><table style="width:100%; font-size: 14px; border:1px solid #25468D;"><tr><td style="width: 50%; border-bottom:1px solid #25468D;background: #f1f4fb;padding: 4px;">&nbsp;Invoice No.</td>
       <td  style="border-bottom:1px solid #25468D;">';

        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            $header .= $data['invoice']['tid'];
        } else {
            $header .= ' Day Pass ';
        }

        $header .= ' </td></tr><tr><td style="width: 50%; border-bottom:1px solid #25468D;background: #f1f4fb;padding:  4px;">&nbsp;Invoice Date</td><td style="border-bottom:1px solid #25468D;"> ' . date("d-m-Y", strtotime($data["invoice"]["invoicedate"])) . ' </td></tr>';


        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            $header .= '  <tr><td style="width: 50%; background: #f1f4fb;padding: 4px;">&nbsp;Account No.</td><td> ' . $data["invoice"]["name"] . '</td></tr>';
        } else {
        }
        $header .= '</table><div>';





        // if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
        //     $header .= ' &nbsp;VAT Reg No: 375 6160 81 ';
        // }
        $header .= '</div></div></header>';
        if ($data['invoice']['inv_type'] == 'INVOICE') {
            $header .= '
            
                <table style="width:100%; font-size: 14px;  margin-top:-43px;"><tr><td style="width: 100%;">&nbsp;<b> 
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                For Bank Transfer:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $company['cname'] . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;S/C&nbsp;&nbsp;' . $company['sortcode'] . ' 
                          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;AC&nbsp;&nbsp;' . $company['taxid'] . '  
                </b></td></tr></table>
                
                ';
        }
        $header .= '<div style="clear:both"></div><main><div style="width:100%;  ">
        <table width="100%" class="myProducts" style="border:1px solid #1367A4;">
        <thead><tr style="font-size: 14px; background:#ccc; padding:4px;">
        <th style="width: 12%; text-align:left; ">Quantity</th>
        <th style="border-left: 1px solid #1367A4;width: 45%; text-align:left;">Details</th>
        <th style="width: 13%; text-align:left; border-left: 1px solid #1367A4;">Unit Price</th>
        <th style="width: 20%; text-align:left; border-left: 1px solid #1367A4;">Net Amount</th>
        <th style="width: 10%;  text-align:left;border-left: 1px solid #1367A4;">' . $this->lang->line('Tax') . '</th></tr></thead>';
        $header .= '<tbody style="font-size:15px;">';

        $ns = 0;
        foreach ($data['products'] as $row) {
            $ns++;
            // Removed calculation - using database value instead
            // Using rev_amountExchange_s() directly in output - no need for variables
            $netamount = amountExchange($row['subtotal'] - $row['totaltax'], $invoice['multi'], $invoice['loc']);
            if (empty($row['product_des']) || $row['product_des'] == "") {
            } else {
                if ($row['serial'] == '1') {
                    $header .= '<tr style="background:#FBF36D; style="font-size:18px;"">';
                } else {
                    $header .= '<tr style=" border-bottom:1px solid white;">';
                }
                $header .= '<td style=" border-bottom:1px solid white;">' . (int)$row["qty"] . $row["unit"] . '</td style=" border-bottom:1px solid white;"><td style=" border-bottom:1px solid white;">' . $row["product_des"] . '</td><td style=" border-bottom:1px solid white;">' . rev_amountExchange_s($row['price']) . '</td> <td style=" border-bottom:1px solid white;">' . rev_amountExchange_s($row['subtotal']) . '</td><td style=" border-bottom:1px solid white;">' . rev_amountExchange_s($row['totaltax']) . ' </td></tr>';
            }
        }
        $header .= '</tbody></table></div></main>';


        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {

            $header .= '<footer style="position:absolute;bottom:-195px;">';
            if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
                $header .= '<div style="border:1px solid #25468D;width:50%;height:140px; padding: 8px; border-radius: 0px; float:left;margin-top:5px"><span style="color:#25468d"><b>*MAXIMUM CHANGE ACCEPTED=&#163;25</b></span><div style=" font-size: 10px;">All goods remain the property of ' . $company['cname'] . ' until all monies are paid in full<br>Any complaints should be made within 48 hours of purchasing.</div><br><div style="width:100%"><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;  font-size: 10px; float:left">Print Name:</div><div style="border:1px solid black;width:10%;height:18px;float:left;margin-left:10px;;margin-left:-78px;"></div><div style="float:left;margin-left:-158px;margin-top:3px;font-size: 10px;"><b>Cash Tendered</b></div><div style="clear:both"></div><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;  font-size: 10px; float:left;">Signature:</div><div style="padding-left:10px;border:1px solid black;width:35%;height:20px;float:left;padding-top:3px;padding-bottom:3px;margin-left:-157px;  font-size: 10px; margin-top:-8px">&#163;</div><div style="clear:both"></div></div><div style="clear:both"></div></div>';
            }

            $sub_ts = 0;
            foreach ($data['products'] as $row) {
                // Removed calculation - using database value instead
            }

            $header .= '<div style="float:right;margin-left:320px;width:280px" ><table class="maintable2" style="width:100%; border:1px solid #25468D;font-size: 14px;">';

            if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
                $header .= '<tr ><td style="width: 50%;  background: #f1f4fb;padding: 3px 8px; font-size:12px">&nbsp;Previous Balance</td><td style="font-size: 12px">';
                $invbalance = 0;
                if ($cust_balance_inv >= $data['invoice']['total']) {
                    $invbalance =  $cust_balance_inv - $data['invoice']['total'];
                    $header .=  amountExchange($invbalance, $data['invoice']['multi'], $data['invoice']['loc']);
                    // $header .=  substr(amountExchange($invbalance, $data['invoice']['multi'], $data['invoice']['loc']), 3, 100);
                } else {
                    $header .=  amountExchange($cust_balance_inv, $data['invoice']['multi'], $data['invoice']['loc']);
                    // $header .=  substr(amountExchange($cust_balance_inv, $data['invoice']['multi'], $data['invoice']['loc']), 3, 100);
                }
                $header .= '</td></tr>';
            }

            $header .= '<tr><td style="width: 60%;  background: #f1f4fb;padding:  3px 8px; font-size:12px">&nbsp;Total NET Amount</td><td style="font-size: 12px"> ' . amountExchange($data["invoice"]["subtotal"]) . ' </td></tr>
            <tr ><td style="width: 60%;  background: #f1f4fb;padding: 3px 8px; font-size:12px">&nbsp;Total VAT Amount</td><td style="font-size: 12px"> ' . amountExchange($data["invoice"]["tax"]) . '  </td></tr>
            <tr><td style="width: 60%;  background: #f1f4fb;padding: 3px 8px; font-size:12px">&nbsp;Invoice Total</td><td style="font-size: 12px"> ' . amountExchange($data["invoice"]["total"]) . '   </td></tr>';
            if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {

                $header .= ' <tr><td style="width: 60%; background: #f1f4fb;padding:  3px 8px; font-size:12px">&nbsp;Balance Now Due</td><td style="font-size: 12px">' . amountExchange($cust_balance_inv) . ' </td></tr> ';
                $header .= ' <tr><td style="width: 60%; background: #f1f4fb;padding:  3px 8px; font-size:12px">&nbsp;Driver Name</td><td style="font-size: 12px">' . $data['invoice']['driver_name'] . ' </td></tr> ';
                $header .= ' <tr><td style="width: 60%; background: #f1f4fb;padding:  3px 8px; font-size:12px">&nbsp;Vehicle No.</td><td style="font-size: 12px">' . $data['invoice']['vehicle_no'] . ' </td></tr> ';
            }
            $header .= '</table></div><div style="clear:both"></div>';

            if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
                $header .= '<p style="font-size:12px">Powered By <a style="text-decoration: none; font-weight: 600; color: #25468d" href="https://cloudbillingmanager.com/" target="_blank">Cloud Billing Manger</a>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;Bank Account Details: &nbsp;&nbsp;<b> ' . $company['taxid'] . '</b>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Sort Code: &nbsp; <b>' . $company['sortcode'] . '</b></p>
    
            ';
            }
            $header .= ' </footer></body></html>';
        } else {


            $header .= '<footer style="position:absolute;bottom:-195px">';
            //if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE' ){
            $header .= '<div style="border:1px solid #25468D;width:50%;height:140px; padding: 8px 0px 5px 4px; border-radius: 6px;float:left;margin-top:5px;display:none"><span style="color:rgb(48, 37, 133)"><b>*MAXIMUM CHANGE ACCEPTED=&#163;25</b></span><div style=" font-size: 10px;">All goods remain the property of ' . $company['cname'] . ' until all monies are paid in full<br>Any complaints should be made within 48 hours of purchasing.</div><br><div style="width:100%"><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left">Print Name:</div><div style="border:1px solid black;width:10%;height:18px;float:left;margin-left:10px;;margin-left:-78px;"></div><div style="float:left;margin-left:-158px;margin-top:3px;font-size: 12px;"><b>Cash Tendered</b></div><div style="clear:both"></div><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left;">Signature:</div><div style="font-size: 17px;padding-left:10px;border:1px solid black;width:35%;height:20px;float:left;padding-top:3px;padding-bottom:3px;margin-left:-157px;margin-top:-8px">&#163;</div><div style="clear:both"></div></div><div style="clear:both"></div></div>';
            //}

            $sub_ts = 0;
            foreach ($data['products'] as $row) {
                // Removed calculation - using database value instead
            }

            $header .= '<div style="float:right;margin-left:320px;width:280px" ><table class="maintable2" style="width:100%; border:1px solid #25468D;font-size: 14px;">';


            $inv_total_amt = $data["invoice"]["total"] + $data["invoice"]["tax"];
            $sub_ts = formatDecimal($data["invoice"]["subtotal"]);

            $header .= '<tr><td style="width: 60%;  background: #f1f4fb;padding:  4px 0px 4px 7px;">&nbsp;Total NET Amount</td><td> ' . $sub_ts . ' </td></tr><tr ><td style="width: 60%;  background: #f1f4fb;padding: 4px 0px 4px 7px;">&nbsp;Total ' . $this->lang->line('Tax') . ' Amount</td><td> ' . $data["invoice"]["tax"] . '  </td></tr><tr><td style="width: 60%;  background: #f1f4fb;padding: 4px 0px 4px 7px;">&nbsp;Invoice Total</td><td> ' . $inv_total_amt . '   </td></tr>';

            $header .= '</table></div><div style="clear:both"></div>';

            //if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE' ){ 
            $header .= '<p style="font-size:8px;display:none">Powered By <a href="https://cloudbillingmanager.com/" target="_blank">Cloud Billing Manger</a>, Company No. 11671004&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Bank Account Details: Sort Code: 04-06-05 A/C No: 21721837</p>';
            //  } 
            $header .= ' </footer></body></html>';
        }

        // $file_name = preg_replace('/[^A-Za-z0-9]+/', '-', 'Invoice__' . $data['invoice']['name'] . '_' . $data['invoice']['tid']);
        // $tid = $id;
        // if($tid == -1) {
        //     $tid = $this->input->get('id');
        // }
        $file_name = $tid;
        $this->dpdf->loadHtml($header);
        $this->dpdf->render();
        $output =  $this->dpdf->output();
        $file_location = 'userfiles/invoices/' . $file_name . '.pdf';
        file_put_contents($file_location, $output);
        if ($printing) {
            $this->dpdf->stream("" . $file_name . ".pdf", array("Attachment" => 0));
        }
    }
    public function printinvoice_bfr_posting7()
    {

        $company = $this->settings->company_details(1);

        $tid = $this->input->get('id');

        $no_print = $this->input->get('no_print');
        if ($no_print) {
            $no_print = $no_print;
        }
        $data['id'] = $tid;
        $data['invoice'] = $this->invocies->invoice_details_bfr_post($tid, $this->limited);
        if ($data['invoice']['id']) $data['products'] = $this->invocies->invoice_products_bfr_post($tid);
        // dd($data['products']);
        if ($data['invoice']['id']) $data['employee'] = $this->invocies->employee_bfr_post($data['invoice']['eid']);
        if ($data['invoice']['i_class'] == 1) {
            $pref = prefix(7);
        } else {
            $pref = $this->config->item('prefix');
        }
        ob_end_clean();
        ob_start();

        $sql = 'SELECT sum(total)-sum(pamnt) as "cust_balance" FROM geopos_invoices WHERE status="due" and csd="' . $data["invoice"]["csd"] . '" or status="partial" and csd="' . $data["invoice"]["csd"] . '"';
        $query = $this->db->query($sql);
        $response = $query->result_array();
        $cust_balance_inv = $response[0]['cust_balance'];

        // $cust_balance_inv= $response[0]['cust_balance'];
        $sql2 = "SELECT content FROM `geopos_notes` ORDER BY `geopos_notes`.`id`  DESC LIMIT 1";
        $query2 = $this->db->query($sql2);
        $response2 = $query2->result_array();
        $content2 = $response2[0]['content'];

        $sql2 = 'SELECT data FROM `geopos_custom_data` where rid="' . $data["invoice"]["csd"] . '"';
        $query2 = $this->db->query($sql2);
        $response2 = $query2->result_array();
        $custom_fields = $response2[0]['data'];
        if ($no_print == 1) {
            $sql = 'Update geopos_invoices_bfr_post set print_status=1 where id= ' . $tid . ' ';
            $this->db->query($sql);
        }




        ini_set('memory_limit', '64M');

        $data_tr = "";


        $left_logo = base_url("userfiles/company/" . $company['logo']);
        $data['left_logo'] = $left_logo;
        $right_logo = base_url("assets/images/halal_logo.jpg");
        $data['right_logo'] = $right_logo;
        // $left_logo = base_url("assets/images/dixy_logo_.jpeg");
        //     $data['left_logo'] = $left_logo;
        //     $right_logo = base_url("assets/images/halal_logo.jpg");
        //     $data['right_logo'] = $right_logo;
        ob_end_clean();
        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            $header = '<html><head><style>@page { margin: 350px 50px 240px; } header{ position: fixed;  top: -320px;}
                table{width:100%;}.pagenum:after {content: counter(page)}</style> </head><body><header>';
            $header = '<html><head><style>
                            @page { margin: 350px 50px 200px; }
                            header { position: fixed; top: -330px; }
                            table { width: 100%; }
                            .pagenum:after { content: counter(page); }
                        </style></head><body><header>';

            $header .= '<table style="width: 100%; border-collapse: collapse;">
                        <tr style="border: 1px solid #1367A4;">
                            <td style="width: 100%; padding: 10px; vertical-align: top;">
                                <b>' . $company['cname'] . '</b><br>'
                . $company['address'] . '<br>'
                . $company['city'] . ' ' . $company['region'] . ', ' . $company['postbox'] . '<br>' . $company['country'] . '<br>
                                Phone: ' . $company['phone'] . '<br>
                                Mobile: ' . $company['mobile'] . '
                            </td>
                            <td style="width: 20%; text-align: center; vertical-align: middle;">
                                <img src="' . $right_logo . '" height="60px" width="60px">
                            </td>
                            <td style="width: 100%; padding: 10px; text-align: center; vertical-align: middle;">
                                <div style="
                                    border: 1px solid #1367A4;
                                    height: auto;
                                    width: 100%;
                                    text-align: center;
                                    padding: 10px;
                                    border-radius: 6px;
                                    margin: 0 auto;">
                                    <span style="font-size: 16px; font-weight: bold; color: #1367A4; display: block; margin-bottom: 8px;">
                                        IMPORTANT CUSTOMER NOTICE!
                                    </span>
                                    <span style="font-size: 12px; color: #333; display: block; margin-bottom: 5px;">
                                        ANY DELIVERY ORDER PLACED FOR BELOW £250 IN VALUE WILL INCUR A DELIVERY CHARGE.
                                    </span>
                                    <span style="font-size: 12px; color: #333; display: block;">
                                        KINDLY PLACE ORDERS ACCORDINGLY!
                                    </span>
                                </div>
                            </td>
                        </tr>
                    </table>';


            $header .= '<div style="width: 100%; display: flex; justify-content: space-between;
                align-items: flex-start; margin-top: 8px;">';
            $header .= '<div style="border: 1px solid #1367A4; height:30px; padding: 0 10px; border-radius: 6px;
                font-size:12px;">';
            // $header .= '<span style="width: 100%; display: block;">
            //     <b>' . $content2 . '</b>
            // </span>';
            $header .= '</div>';  // Closing the inner div
            $header .= '</div>';  // Closing the outer div
        } else {
            $header = '<html><head><style>@page { margin: 350px 50px 200px; }header{ position: fixed;  top: -320px;}table{width:100%;}.pagenum:after {content: counter(page)}</style> </head><body>
                <header><span style="float:right;"  class="pagenum">Page:</span>';
            $header .= ' <div style="width:100%; "><div style="width:23%; float:left; text-align:center;">  <br> Invoice#: ' . $data['invoice']['tid'] . '
                    </div></div><div><p style="text-align:center;height:84px;">Note: This is not a valid Invoice  </p>
                        Date: ' . date("d-m-Y", strtotime($data["invoice"]["invoicedate"])) . '
                    </div><div style="clear:both"></div>';
        }

        $header .= '<div style="width: 100%; display: flex; justify-content: space-between;
            align-items: flex-start; margin-top: auto; font-size:12px;">';
        // Left Box
        $header .= '<div style="border: 1px solid #1367A4; height: auto; width: 25%; padding: 10px;
            border-radius: 6px;" >';
        if ($data['invoice']['company']) {
            $header .= $data['invoice']['company'] . '<br>';
        }
        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            $header .= $data['invoice']['cust_address'] . ' ' . $data['invoice']['cust_city'] . '<br>' . $data['invoice']['cust_postcode'];
            if ($data['invoice']['pamt_terms']) {
                $header .= '<br><b><span style="background-color: yellow;">TERMS:</span></b> ' . $custom_fields;
            }
        } else {
            $header .= $data['invoice']['cust_address'] . ' ' . $data['invoice']['cust_city'] . '<br>' . $data['invoice']['cust_postcode'];
            if ($data['invoice']['pamt_terms']) {
                $header .= '<br><b> <span style="background-color:yellow">TERMS:</span> </b>' . $custom_fields . '';
            }
        }
        $header .= '</div>';

        // Center Box (for Bank Transfer Details)
        $header .= '<div style="width: 34%; display: flex; justify-content: flex-end; margin-left: 233px;">';
        if ($data['invoice']['inv_type'] == 'INVOICE') {
            $header .= '<div style="padding: 10px; text-align: center;">';
            $header .= '<b>For Bank Transfer:</b><br>';
            $header .= $company['cname'] . '<br>';
            $header .= 'S/C ' . $company['sortcode'] . '<br>';
            $header .= 'AC ' . $company['taxid'];
            $header .= '</div>';
        }
        $header .= '</div>';
        // Right Box (for Invoice Details)
        $header .= '<div style="width: 30%; display: flex; justify-content: flex-end; margin-left: 498px;">';
        $header .= '<table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <th style="border: 1px solid #1367A4; padding: 5px; text-align: left;">Date:</th>
                            <td style="border: 1px solid #1367A4; padding: 5px; text-align: right;">' . date("d-m-Y", strtotime($data["invoice"]["invoicedate"])) . '</td>
                        </tr>
                        <tr>
                            <th style="border: 1px solid #1367A4; padding: 5px; text-align: left;">Invoice:</th>
                            <td style="border: 1px solid #1367A4; padding: 5px; text-align: right;">' . $data['invoice']['tid'] . '</td>
                        </tr>
                        <tr>
                            <th style="border: 1px solid #1367A4; padding: 5px; text-align: left;">A/C:</th>
                            <td style="border: 1px solid #1367A4; padding: 5px; text-align: right;">' . $data["invoice"]["name"] . '</td>
                        </tr>
                        <tr>
                            <th style="border: 1px solid #1367A4; padding: 5px; text-align: left;">EC NO:</th>
                            <td style="border: 1px solid #1367A4; padding: 5px; text-align: right;">882 /2004</td>
                        </tr>
                        <tr>
                            <th style="border: 1px solid #1367A4; padding: 5px; text-align: left;">VAT Reg:</th>
                            <td style="border: 1px solid #1367A4; padding: 5px; text-align: right;">299367533</td>
                        </tr>
                    </table>';
        $header .= '</div>'; // Closing the Right Box
        $header .= '</div>';
        $header .= '</header>';  // Closing the header and HTML structure
        $header .= '<div style="clear:both"></div><main><div style="width:100%;">';
        $header .= '<table width="100%" class="myProducts" style="border:1px solid #1367A4;">
                            <thead>
                                <tr style="font-size: 14px; background:#ccc; padding:4px;">
                                    <th style="width: 4%; text-align:left;">
                                        <input type="checkbox" onclick="toggleCheckboxes(this)" style="margin: 0; vertical-align: middle;">
                                    </th>
                                    <th style="width: 12%; text-align:left;">Quantity</th>
                                    <th style="border-left: 1px solid #1367A4; width: 43%; text-align:left;">Details</th>
                                    <th style="width: 13%; text-align:left; border-left: 1px solid #1367A4;">Unit Price</th>
                                    <th style="width: 18%; text-align:left; border-left: 1px solid #1367A4;">Net Amount</th>
                                    <th style="width: 10%; text-align:left; border-left: 1px solid #1367A4;">VAT</th>
                                </tr>
                            </thead>';
        $header .= '<tbody style="font-size:15px;">';

        $ns = 0;
        $sub_t = 0;

        foreach ($data['products'] as $row) {
            $ns++;
            // Removed calculation - using database value instead
            // Using rev_amountExchange_s() directly in output - no need for variables
            $netamount = amountExchange($row['subtotal'] - $row['totaltax'], $invoice['multi'], $invoice['loc']);

            if (!empty($row['product_des'])) {
                // Highlight row if serial equals 1
                $rowStyle = ($row['serial'] == '1') ? 'background:#FBF36D; font-size:18px;' : 'border-bottom:1px solid white;';

                $header .= '<tr style="' . $rowStyle . '">';
                $header .= '<td style="border: 1px solid #1367A4; text-align: center;">
                            <input type="checkbox" class="row-checkbox" style="margin: 0; vertical-align: middle;">
                        </td>';
                $header .= '<td style="border-bottom:1px solid white;">' . (int)$row["qty"] . ' ' . $row["unit"] . '</td>';
                $header .= '<td style="border-bottom:1px solid white;">' . $row["product_des"] . '</td>';
                $header .= '<td style="border-bottom:1px solid white;">' . rev_amountExchange_s($row['price']) . '</td>';
                $header .= '<td style="border-bottom:1px solid white;">' . rev_amountExchange_s($row['subtotal']) . '</td>';
                $header .= '<td style="border-bottom:1px solid white;">' . rev_amountExchange_s($row['totaltax']) . '</td>';
                $header .= '</tr>';
            }
        }

        $header .= '</tbody></table></div></main>';

        // Use database values instead of calculating totals

        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {

            $header .= '<footer style="position:fixed; bottom:10px;">';
            if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
                $header .= '<div style="width: 70%; float: left;">';

                // Initialize subtotal
                $sub_ts = 0;
                foreach ($data['products'] as $row) {
                    // Removed calculation - using database value instead
                }

                // Define a reusable cell style

                // Start the table with proper borders and collapse styles
                $header .= '<table style="border:1px solid #1367A4; border-collapse: collapse; font-size:12px;">';

                // Add rows with proper border styling and alignment
                $header .= '<tr>
                            <td style="border:1px solid #1367A4; width:20%; padding: 5px;">Total NET Amount</td>
                            <td style="border:1px solid #1367A4; width:20%; padding: 5px;">' . amountExchange($data["invoice"]["subtotal"]) . '</td>
                            <td style="border:1px solid #1367A4; border-bottom:none; padding: 5px;"></td>
                            <td style="border:1px solid #1367A4; width:15%; padding: 5px;">Cash</td>
                            <td style="border:1px solid #1367A4; padding: 5px;"></td>
                            <td style="border:1px solid #1367A4; width:15%; padding: 5px;">Sign/Print</td>
                        </tr>';

                $header .= '<tr>
                            <td style="border:1px solid #1367A4; width:20%; padding: 5px;">Total VAT Amount</td>
                            <td style="border:1px solid #1367A4; width:20%; padding: 5px;">' . amountExchange($data["invoice"]["tax"]) . '</td>
                            <td style="text-align:center; font-weight:bold; padding: 5px;">Total Amount</td>
                            <td style="border:1px solid #1367A4; width:15%; padding: 5px;">Card/CHQ</td>
                            <td style="border:1px solid #1367A4; padding: 5px;"></td>
                            <td style="border:1px solid #1367A4; width:15%; padding: 5px;"></td>
                        </tr>';

                // Add Previous Balance row if needed
                if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
                    if (!$hide_balance) {
                        // Balance calculation logic
                    }
                }
                if (!$hide_balance) {
                    $invbalance = 0;
                    if ($cust_balance_inv >= $data['invoice']['total']) {
                        $invbalance = $cust_balance_inv - $data['invoice']['total'];
                        $previous_balance = substr(amountExchange($invbalance, $data['invoice']['multi'], $data['invoice']['loc']), 3, 100);
                    } else {
                        $previous_balance = substr(amountExchange($cust_balance_inv, $data['invoice']['multi'], $data['invoice']['loc']), 3, 100);
                    }
                    $balance_now_due = $cust_balance_inv;

                    $header .= '<tr>
                                <td style="border:1px solid #1367A4; width:20%; padding: 5px;">Previous Balance</td>
                                <td style="border:1px solid #1367A4; width:20%; padding: 5px;">' . amountExchange($previous_balance) . '</td>
                                <td style="text-align:center; font-weight:bold; padding: 5px;">' . amountExchange($data["invoice"]["total"]) . '</td>
                                <td style="border:1px solid #1367A4; padding: 5px; width:15%;">BACS</td>
                                <td style="border:1px solid #1367A4; padding: 5px;"></td>
                                <td style="border:1px solid #1367A4; padding: 5px; width:15%;"></td>
                            </tr>';
                    $header .= '<tr>
                                <td style="border:1px solid #1367A4; width:20%; padding: 5px;">Balance Now Due</td>
                                <td style="border:1px solid #1367A4; width:20%; padding: 5px;">' . amountExchange($balance_now_due) . '</td>
                                <td style="border-left:1px solid #1367A4; padding: 5px;"></td>
                                <td style="border:1px solid #1367A4; padding: 5px; width:15%;">Total</td>
                                <td style="border:1px solid #1367A4; padding: 5px;"></td>
                                <td style="border:1px solid #1367A4; padding: 5px; width:15%;"></td>
                            </tr>';
                } else {
                    $header .= '<tr>
                                <td style="padding: 15px; width:20%;"></td>
                                <td style="padding: 15px; width:20%;"></td>
                                <td style="border-left:1px solid #1367A4; text-align:center; font-weight:bold; padding: 5px;">' . amountExchange($invbalance) . '</td>
                                <td style="border:1px solid #1367A4; padding: 5px; width:15%;">BACS</td>
                                <td style="border:1px solid #1367A4; padding: 5px;"></td>
                                <td style="border:1px solid #1367A4; padding: 5px; width:15%;"></td>
                            </tr>';
                    $header .= '<tr>
                                <td style="padding: 15px; width:20%;"></td>
                                <td style="padding: 15px; width:20%;"></td>
                                <td style="border:1px solid #1367A4; border-top:none; padding: 5px;"></td>
                                <td style="border:1px solid #1367A4; padding: 5px; width:15%;">Total</td>
                                <td style="border:1px solid #1367A4; padding: 5px;"></td>
                                <td style="border:1px solid #1367A4; padding: 5px; width:15%;"></td>
                            </tr>';
                }

                $header .= '</table>';


                $header .= '<div style=" font-size: 10px; text-align:center;">
                    All goods remain the property of Exel Foods Ltd, unless full payment is received. Please note drivers will not accept more than
                    £40 in coins(£1&£2 coins only).All Shortages/damages must be clearly shown on both copies of the invoice.
                    All payments will be allocated to the oldest outstanding balances first.
                    </div>';
                // $header .= '
                // <span style="color:rgb(48, 37, 133);font-size: 12px;text-align:center;"><b>*MAXIMUM CHANGE ACCEPTED=&#163;60</b> </span>
                // <div style=" font-size: 12px;">
                // *All claims must be notified at the time of delivery,
                // any claims made after delivery will not be considered. Goods will remain
                // the property of ' . $company['cname'] . ' until paid in full by the customer.Frozen and  products cannot be returned once delivered.
                // </div>';
                $header .= '<div style="clear: both;"></div>';
                $header .= '</div>'; // End of main div


                // Add the remaining content
            }



            $sub_ts = 0;
            foreach ($data['products'] as $row) {
                // Removed calculation - using database value instead
            }

            $header .= '<div style="float:right; margin-left:530px; font-size: 14px;">
                <table class="maintable2" style="border: 1px solid #1367A4; border-collapse: collapse; width: 100%;">';

            // Add "Office Use Only" header row
            $header .= '<tr>
                            <th style="border: 1px solid #1367A4; text-align: center; padding: 8px;" colspan="2">&nbsp;Office Use Only</th>
                        </tr>';

            // Add row for "Updated"
            $header .= '<tr>
                            <td style="width: 50%; border: 1px solid #1367A4; padding: 4px 0px 4px 7px;">Updated</td>
                            <td style="width: 50%; border: 1px solid #1367A4; padding: 4px 0px 4px 7px;"></td>
                        </tr>';

            // Add row for "Payment Allocated"
            $header .= '<tr>
                            <td style="width: 50%; border: 1px solid #1367A4; padding: 0px 0px 0px 7px;">Payment Allocated</td>
                            <td style="width: 50%; border: 1px solid #1367A4; padding: 0px 0px 0px 7px;"></td>
                        </tr>';

            // Add an empty row
            $header .= '<tr>
                            <td style="padding: 4px 0px 4px 7px; border: 1px solid #1367A4; border-right: none;">&nbsp;</td>
                            <td></td>
                        </tr>';

            // Additional logic based on invoice type and company
            if ($data['invoice']['inv_type'] == 'INVOICE' || $data['invoice']['inv_type'] == 'DAYPASS' && $data['invoice']['company'] != 'COUNTER SALE') {
                // If there's additional balance logic, it would go here
            }

            $header .= '</table>
    
                <span style="font-size:12px; text-align: center; href="mailto:' . $company['email'] . '">
                <img src="' . base_url("assets/images/mail.jpg") . '" height="30px" width="35px" style="margin-top:8px;">  </span>' . $company['email'] . '
                <br><span style="font-size:12px; text-align: center; margin-left:10px;">Thank you for your business!</span>
                </div>
                <div style="clear: both;"></div>';
            if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            }
            $header .= ' </footer></body></html>';
        } else {


            $header .= '<footer style="position:fixed;bottom:-195px;">';
            //if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE' ){
            $header .= '<div style="border:1px solid #1367A4;width:50%;height:140px; padding: 8px 0px 5px 4px; border-radius: 6px;float:left;margin-top:5px;display:none">
                <span style="color:rgb(48, 37, 133) font-size: 12px;"><b>*MAXIMUM CHANGE ACCEPTED=&#163;25</b></span><div style=" font-size: 12px;">All goods remain the property of ' . $company['cname'] . ' 
                until all monies are paid in full<br>Any complaints should be made within 48 hours of purchasing.</div><br><div style="width:100%"><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left">Print Name:</div><div style="border:1px solid black;width:10%;height:18px;float:left;margin-left:10px;;margin-left:-78px;"></div><div style="float:left;margin-left:-158px;margin-top:3px;font-size: 12px;"><b>Cash Tendered</b></div><div style="clear:both"></div><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left;">Signature:</div><div style="font-size: 17px;padding-left:10px;border:1px solid black;width:35%;height:20px;float:left;padding-top:3px;padding-bottom:3px;margin-left:-157px;margin-top:-8px">&#163;</div><div style="clear:both"></div></div><div style="clear:both"></div></div>';
            //}

            $sub_ts = 0;
            foreach ($data['products'] as $row) {
                // Removed calculation - using database value instead
            }

            $header .= '<div style="float:right;margin-left:320px;width:280px" ><table class="maintable2" style="width:100%; border:1px solid #1367A4;font-size: 14px;">';


            //  $inv_total_amt=$data["invoice"]["total"]+$data["invoice"]["tax"];

            $inv_total_amt = $data["invoice"]["total"];
            $sub_ts = formatDecimal($data["invoice"]["subtotal"]);

            $header .= '<tr><td style="width: 60%;  padding:  4px 0px 4px 7px;">&nbsp;Total NET Amount</td><td> ' . $sub_ts . ' </td></tr>
                <tr ><td style="width: 60%;  background: #f1f4fb;padding: 4px 0px 4px 7px;">&nbsp;Total ' . $this->lang->line('Tax') . ' Amount</td><td> ' . $data["invoice"]["tax"]
                . '  </td></tr><tr><td style="width: 60%;  background: #f1f4fb;padding: 4px 0px 4px 7px;">&nbsp;
                 Invoice Total</td><td> ' . $inv_total_amt . '   </td></tr>';
            if (!$hide_balance) {
                $header .= '<tr ><td style="width: 50%;  background: #f1f4fb;padding: 4px 0px 4px 7px;">&nbsp;Previous Balance</td><td>';
                $invbalance = 0;
                if ($cust_balance_inv >= $data['invoice']['total']) {
                    $invbalance =  $cust_balance_inv - $data['invoice']['total'];
                    $header .=  substr(amountExchange($invbalance, $data['invoice']['multi'], $data['invoice']['loc']), 3, 100);
                } else {
                    $header .=  substr(amountExchange($cust_balance_inv, $data['invoice']['multi'], $data['invoice']['loc']), 3, 100);
                }
                $header .= '</td></tr>';
                $header .= ' <tr><td style="width: 60%; background: #f1f4fb;padding:  4px 0px 4px 7px;">
                    &nbsp;Balance Now Due</td><td>' . $cust_balance_inv . ' </td></tr> ';
            }

            $header .= '</table></div><div style="clear:both"></div>';

            //if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE' ){ 
            $header .= '<p style="font-size:8px;display:none"> ' . $company['cname'] . ' 
                is the trading name of ' . $company['cname'] . ', Company No. 11671004. &nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp; Bank Account Details: Sort Code: ' . $company['sortcode'] . ' A/C No: '
                . $company['taxid'] . '</p>';
            //  } 
            $header .= ' </footer></body></html>';
        }

        // $file_name = preg_replace('/[^A-Za-z0-9]+/', '-', 'Invoice__' . $data['invoice']['name'] . '_' . $data['invoice']['tid']);
        $file_name = $this->input->get('id');
        $this->dpdf->loadHtml($header);
        $this->dpdf->render();
        $output =  $this->dpdf->output();
        $file_location = 'userfiles/invoices_bfr_postr/' . $file_name . '.pdf';
        file_put_contents($file_location, $output);
        $this->dpdf->stream("" . $file_name . ".pdf", array("Attachment" => 0));
    }
    public function printinvoice_bfr_posting8($printing = true, $id = -1)
    {
        // Explicitly load language file for PDF generation
        $system_lang = $this->db->query("SELECT lang FROM geopos_system WHERE id=1 LIMIT 1")->row_array();
        $lang_to_load = $system_lang['lang'] ?? 'english';
        $this->lang->load($lang_to_load, $lang_to_load);
        $dynamic_tax_name = getTaxName();
        $that = $this;
        $lang = function ($key) use ($that, $dynamic_tax_name) {
            $text = $that->lang->line($key);
            if ($text === FALSE || empty($text)) return $key;
            return str_replace(array('{tax}', '{Tax}', '{TAX}'), array(strtolower($dynamic_tax_name), ucfirst(strtolower($dynamic_tax_name)), strtoupper($dynamic_tax_name)), $text);
        };
        $company = $this->settings->company_details(1);
        $no_print = $this->input->get('no_print');
        if ($no_print) {
            $no_print = $no_print;
        }
        // var_dump('no_print', $no_print);
        // die();
        $tid = $id;
        if ($tid == -1) {
            $tid = $this->input->get('id');
        }

        $data['id'] = $tid;
        $data['invoice'] = $this->invocies->invoice_details_bfr_post($tid, $this->limited);
        if ($data['invoice']['id']) $data['products'] = $this->invocies->invoice_products_bfr_post($tid);
        // dd($data['products']);
        if ($data['invoice']['id']) $data['employee'] = $this->invocies->employee_bfr_post($data['invoice']['eid']);

        ob_end_clean();
        ob_start();

        // mark printed
        if ($no_print == 1) {
            $sql = 'Update geopos_invoices_bfr_post set print_status=1 where id= ' . $tid . ' ';
            $this->db->query($sql);
        }

        $left_logo = base_url('userfiles/company/' . $company['logo']);
        $invoice = $data['invoice'];

        // Prepare customer billing information
        $bill_to_lines = '';
        // Company name in bold (if provided)
        if (!empty($invoice['company'])) {
            $bill_to_lines .= '<strong>' . htmlspecialchars($invoice['company']) . '</strong><br>';
        }
        // Address block (line 1, city, postcode)
        $address_line_1 = trim((string)($invoice['cust_address'] ?? ''));
        $address_line_2 = trim((string)($invoice['cust_city'] ?? ''));
        $postcode_value = trim((string)($invoice['cust_postcode'] ?? ''));
        if ($address_line_1 !== '') {
            $bill_to_lines .= htmlspecialchars($address_line_1);
            if ($address_line_2 !== '') {
                $bill_to_lines .= ', ' . htmlspecialchars($address_line_2);
            }
            if ($postcode_value !== '') {
                $bill_to_lines .= '<br>' . htmlspecialchars($postcode_value);
            }
        }

        // Sales Tax and NTN lines: always show; use real values if available, else use placeholders
        $sales_tax_value = '';
        foreach (['sales_tax', 'sales_tax_no', 'gst', 'vat_no', 'strn', 'stn'] as $key) {
            if (!empty($invoice[$key])) {
                $sales_tax_value = (string)$invoice[$key];
                break;
            }
        }
        $ntn_value = '';
        foreach (['ntn', 'ntn_no', 'taxid'] as $key) {
            if (!empty($invoice[$key])) {
                $ntn_value = (string)$invoice[$key];
                break;
            }
        }
        if ($sales_tax_value === '') {
            $sales_tax_value = '32-77-8761-291-58';
        }
        if ($ntn_value === '') {
            $ntn_value = '7222516-7';
        }

        $bill_to_lines .= '<br><span class="tax-info">Sales Tax # ' . htmlspecialchars($sales_tax_value) . '</span>';
        $bill_to_lines .= '<br><span class="tax-info">NTN # ' . htmlspecialchars($ntn_value) . '</span>';

        $reference_no = isset($invoice['refer']) ? $invoice['refer'] : '';

        // Use database values instead of calculating totals
        $subtotal = $data['invoice']['subtotal'];
        $total_tax = $data['invoice']['tax'];
        $grand_total = $data['invoice']['total'];

        // Start HTML structure with CSS
        $html = '<html><head><style>
            * { font-family: "Open Sans", sans-serif; }
            body { font-size: 8pt; color: #333333; }
            /* Page margins - A4 Portrait: Top=0.7in, Bottom=0.7in, Left=0.55in, Right=0.4in */
            @page { margin: 0.7in 0.4in 0.7in 0.55in; }
            table { width: 100%; border-collapse: collapse; }
            /* Footer - Page Number on Right, Font Size 6pt, Color #aaaaaa */
            footer.doc-footer { position: fixed; bottom: -0.7in; left: 0; right: 0; height: 0.4in; text-align: right; padding: 0 20px; color: #aaaaaa; font-size: 6pt; background: #ffffff; }
            footer.doc-footer .page-count:after { content: "Page " counter(page) " of " counter(pages); }
            /* Header content styling */
            .page-header { margin-bottom: 10px; }
            .doc-meta { margin-top: 10px; margin-bottom: 20px; }
            .doc-meta .title-row { width:100%; margin: 6px 0 2px; }
            .doc-meta .title-cell { width:20%; text-align:center; font-weight:200; }
            .muted { color:#666; font-size: 12px; }
            /* Customer details - Font Size 9pt, Color #333333 */
            .customer-details { font-size: 9pt; color: #333333; line-height:1.6; }
            .tax-info { font-size: 9pt; color: #333333; }
            .title { font-size: 11pt; letter-spacing: 1px; color: #333333; }
            .box { border:1px solid #ccc; border-radius:4px; padding:8px; }
            /* Items table - Border #d8d8d7 */
            .items { border: 1px solid #d8d8d7; margin-top: 0; }
            .items th, .items td { border: 1px solid #d8d8d7; padding: 6px; font-size: 8pt; color: #333333; }
            /* Table Header - Background #F5F6F7, Font Size 8pt, Color #333333 */
            .items thead th { background: #F5F6F7; color: #333333; font-size: 8pt; font-weight: 600; }
            /* Item Rows - Background #ffffff, Font Size 8pt, Color #333333 */
            .items tbody td { background: #ffffff; font-size: 8pt; color: #333333; }
            /* Compact meta table on the right to reduce label/value gap */
            .meta-table { width: auto; margin-left: auto; border-collapse: collapse; table-layout: auto; }
            .meta-table td { padding: 2px 6px; font-size: 9pt; color: #333333; }
            .meta-table td.value { text-align: left; white-space: nowrap; }
            /* Organization Name - Font Size 9pt, Color #333333 */
            .org-name { font-weight: 700; font-size: 9pt; color: #333333; }
            .org-details { font-size: 8pt; color: #333333; }
            /* Customer Name Label - Font Size 9pt, Color #333333 */
            .customer-label { font-weight: 700; font-size: 9pt; color: #333333; margin-bottom: 6px; }
            /* Totals section styling */
            .totals-box { 
                padding: 10px; 
                margin-top: 20px; 
                width: 300px; 
                float: right; 
                font-size: 8pt;
                color: #333333;
            }
            .total-row { 
                display: table; 
                width: 100%; 
                margin-bottom: 5px; 
            }
            .total-label { 
                display: table-cell; 
                font-weight: bold; 
                width: 60%; 
            }
            .total-value { 
                display: table-cell; 
                text-align: right; 
                font-weight: bold; 
            }
            .grand-total { 
                border-top: 1px solid #333; 
                padding-top: 5px; 
                margin-top: 5px; 
            }
            /* Page break utilities */
            .page-break { page-break-after: always; }
        </style></head><body>';

        // Store header HTML in a variable for reuse
        $header_html = '<div class="page-header">
            <table style="width:100%; margin-bottom:5px;"><tr>
                <td style="width:45%; vertical-align:top;">
                    <img src="' . $left_logo . '" height="80" style="vertical-align:top;">
                </td>
                <td style="width:55%; text-align:right; vertical-align:top;">
                    <div class="org-name">' . htmlspecialchars($company['cname']) . '</div>
                    <div class="org-details">
                    ' . htmlspecialchars($company['address']) . '<br>
                    ' . htmlspecialchars($company['city']) . ', ' . htmlspecialchars($company['country']) . '<br>
                    Mob: ' . htmlspecialchars($company['mobile']) . ' | Tel: ' . htmlspecialchars($company['phone']) . '<br>
                    Email: ' . htmlspecialchars($company['email']) . '<br>
                    Web: ' . htmlspecialchars($company['website'] ?? 'www.aaico.pk') . '<br>
                    <strong>NTN: ' . htmlspecialchars($ntn_value) . '</strong>
                    </div>
                </td>
            </tr></table>
            <div class="doc-meta">
                <table class="title-row">
                    <tr>
                        <td style="width:40%;"><hr style="margin:0; border:none; border-top:2px solid #dcdcdc;"></td>
                        <td class="title-cell"><span class="title">SALES TAX INVOICE</span></td>
                        <td style="width:40%;"><hr style="margin:0; border:none; border-top:2px solid #dcdcdc;"></td>
                    </tr>
                </table>
                <table style="margin-top:10px; width:100%; table-layout:fixed;">
                    <tr>
                        <td style="width:58%; vertical-align:top; padding-right:12px;">
                            <div style="padding:8px;">
                                <div class="customer-label">Principal</div>
                                <div class="customer-details">' . $bill_to_lines . '</div>
                            </div>
                        </td>
                        <td style="width:42%; vertical-align:top;">
                            <table class="meta-table">
                                <tr>
                                    <td style="font-weight:600;">Invoice No:</td>
                                    <td class="value">' . htmlspecialchars($invoice['tid']) . '</td>
                                </tr>
                                <tr>
                                    <td style="font-weight:600;">Invoice Date</td>
                                    <td class="value" style="white-space:nowrap;">' . date('d M Y', strtotime($invoice['invoicedate'])) . '</td>
                                </tr>
                                <tr>
                                    <td style="font-weight:600;">Due Date</td>
                                    <td class="value" style="white-space:nowrap;">' . date('d M Y', strtotime($invoice['invoicedate'] . ' +30 days')) . '</td>
                                </tr>
                                <tr>
                                    <td style="font-weight:600;">P.O. #</td>
                                    <td class="value" style="white-space:nowrap;">' . htmlspecialchars($reference_no) . '</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>
        </div>';

        // Add header for first page
        $html .= $header_html;

        // Start items table
        $html .= '<table class="items">
                <thead>
                    <tr>
                        <th style="width:6%; text-align:center;">' . $lang('#') . '</th>
                        <th style="text-align:left;">' . $lang('Item & Description') . '</th>
                        <th style="width:8%; text-align:center;">' . $lang('Quantity') . '</th>
                        <th style="width:12%; text-align:right;">' . $lang('Rate') . '</th>
                        <th style="width:13%; text-align:right;">' . $lang('Net Amount') . '</th>
                    </tr>
                </thead>
                <tbody>';

        $rownum = 0;
        $items_on_page = 0;

        foreach ($data['products'] as $row) {
            if ($row['qty'] > 0) {
                $rownum++;
                $items_on_page++;

                $html .= '<tr>'
                    . '<td style="text-align:center;">' . $rownum . '</td>'
                    . '<td style="text-align:left;">' . $row["product_des"] . '</td>'
                    . '<td style="text-align:center;">' . (int)$row["qty"] . $row["unit"] . '</td>'
                    . '<td style="text-align:right;">' . rev_amountExchange_s($row['price']) . '</td>'
                    . '<td style="text-align:right;">' . rev_amountExchange_s($row['subtotal']) . '</td>'
                    . '</tr>';

                // Force a page break after 15 rows
                if ($items_on_page == 15) {
                    $html .= '</tbody></table>';
                    $html .= '<div class="page-break"></div>';

                    // Add header for next page
                    $html .= $header_html;

                    // Restart items table
                    $html .= '<table class="items">
                        <thead>
                            <tr>
                                <th style="width:6%; text-align:center;">' . $lang('#') . '</th>
                                <th style="text-align:left;">' . $lang('Item & Description') . '</th>
                                <th style="width:8%; text-align:center;">' . $lang('Quantity') . '</th>
                                <th style="width:12%; text-align:right;">' . $lang('Rate') . '</th>
                                <th style="width:13%; text-align:right;">' . $lang('Net Amount') . '</th>
                            </tr>
                        </thead>
                        <tbody>';

                    $items_on_page = 0; // Reset counter for next page
                }
            }
        }

        if ($rownum === 0) {
            $html .= '<tr><td colspan="5" style="text-align:center; color:#777;">No items found</td></tr>';
        }

        $html .= '</tbody></table>';

        // ==================== TOTALS AND FOOTER SECTIONS ====================
        $html .= '<div style="margin-top:20px; font-size:8pt; color:#333333; clear:both;">Thanks for your business.</div>';

        $html .= '<div class="totals-box">
            <div class="total-row">
                <div class="total-label">' . $lang('Sub Total') . '</div>
                <div class="total-value">' . amountExchange($data['invoice']['subtotal']) . ' </div>
            </div>
            <div class="total-row">
                <div class="total-label">' . $lang('Total Taxable Amount') . '</div>
                <div class="total-value">' . amountExchange($data['invoice']['subtotal']) . ' </div>
            </div>';

        if ($data["invoice"]["tax"] > 0) {
            $html .= '<div class="total-row">
                <div class="total-label"' . $lang('Total Tax Amount') . '</div>
                <div class="total-value">' . amountExchange($data["invoice"]["tax"]) . '</div>
            </div>';
        }

        $html .= '<div class="total-row grand-total">
                <div class="total-label">' . $lang('Grand Total') . '</div>
                <div class="total-value">' . amountExchange($data["invoice"]["total"]) . '</div>
            </div>
            <div class="total-row">
                <div class="total-label">Payment Made</div>
                <div class="total-value">(-) ' . amountExchange($data["invoice"]["pamnt"]) . '</div>
            </div>
            <div class="total-row grand-total">
                <div class="total-label">Balance Due</div>
                <div class="total-value">' . amountExchange($data["invoice"]["total"] - $data["invoice"]["pamnt"]) . '</div>
            </div>
        </div>';

        // Footer with page numbers
        $html .= '<footer class="doc-footer"><span class="page-count"></span></footer>';

        $html .= '</body></html>';

        // Generate PDF
        $file_name = $tid;
        $this->dpdf->loadHtml($html);
        $this->dpdf->render();
        $output = $this->dpdf->output();
        $file_location = 'userfiles/invoices/sales_tax_invoice_' . $file_name . '.pdf';
        file_put_contents($file_location, $output);

        if ($printing) {
            $this->dpdf->stream("sales_tax_invoice_" . $file_name . ".pdf", array("Attachment" => 0));
        }
    }

    public function printinvoice_bfr_posting9()
    {

        // Explicitly load language file for PDF generation
        $system_lang = $this->db->query("SELECT lang FROM geopos_system WHERE id=1 LIMIT 1")->row_array();
        $lang_to_load = $system_lang['lang'] ?? 'english';
        $this->lang->load($lang_to_load, $lang_to_load);

        // Get dynamic tax name for placeholder replacement
        $dynamic_tax_name = getTaxName();

        // Helper function to get and replace language lines
        $that = $this; // Store controller reference for closure
        $lang = function ($key) use ($that, $dynamic_tax_name) {
            $text = $that->lang->line($key);
            if ($text === FALSE || empty($text)) {
                return $key; // Return key if translation not found
            }
            // Replace tax placeholders
            return str_replace(
                array('{tax}', '{Tax}', '{TAX}'),
                array(strtolower($dynamic_tax_name), ucfirst(strtolower($dynamic_tax_name)), strtoupper($dynamic_tax_name)),
                $text
            );
        };

        $company = $this->settings->company_details(1);

        $tid = $this->input->get('id');

        $no_print = $this->input->get('no_print');
        if ($no_print) {
            $no_print = $no_print;
        }
        $data['id'] = $tid;
        $data['invoice'] = $this->invocies->invoice_details_bfr_post($tid, $this->limited);
        if ($data['invoice']['id'])
            $data['products'] = $this->invocies->invoice_products_bfr_post($tid);
        // dd($data['products']);
        if ($data['invoice']['id'])
            $data['employee'] = $this->invocies->employee_bfr_post($data['invoice']['eid']);
        if ($data['invoice']['i_class'] == 1) {
            $pref = prefix(7);
        } else {
            $pref = $this->config->item('prefix');
        }
        //print_r($data['invoice']['csd']);die();
        //var_dump($data['products']); exit; 
        ob_end_clean();
        ob_start();

        $sql = 'SELECT sum(total)-sum(pamnt) as "cust_balance" FROM geopos_invoices WHERE status="due" and csd="' . $data["invoice"]["csd"] . '" or status="partial" and csd="' . $data["invoice"]["csd"] . '"';
        $query = $this->db->query($sql);
        $response = $query->result_array();
        $cust_balance_inv = $response[0]['cust_balance'];

        // $cust_balance_inv= $response[0]['cust_balance'];
        $sql2 = "SELECT content FROM `geopos_notes` ORDER BY `geopos_notes`.`id`  DESC LIMIT 1";
        $query2 = $this->db->query($sql2);
        $response2 = $query2->result_array();
        $content2 = $response2[0]['content'];

        $sql2 = 'SELECT data FROM `geopos_custom_data` where rid="' . $data["invoice"]["csd"] . '"';
        $query2 = $this->db->query($sql2);
        $response2 = $query2->result_array();
        $custom_fields = $response2[0]['data'];
        if ($no_print == 1) {
            $sql = 'Update geopos_invoices_bfr_post set print_status=1 where id= ' . $tid . ' ';
            $this->db->query($sql);
        }

        ini_set('memory_limit', '64M');

        $data_tr = "";

        $left_logo = base_url("userfiles/company/" . $company['logo']);
        $data['left_logo'] = $left_logo;
        $right_logo = base_url("assets/images/halal_logo.jpg");
        $data['right_logo'] = $right_logo;
        ob_end_clean();

        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            $header = '<html><head><style>
            @page { 
                margin: 350px 50px 200px; 
            }
            header{ 
                position: fixed;  
                top: -320px;
            }
            footer {
                position: fixed;
                bottom: -150px;
                width: 100%;
            }
            table{
                width:100%;
            }
            .pagenum:after {
                content: counter(page);
            }
            .page-break {
                page-break-before: always;
            }
            .items-table {
                width: 100%;
                border: 1px solid #1367A4;
                border-radius: 6px;
                border-collapse: collapse;
            }
            .items-table thead th {
                font-size: 12px;
                background: #ccc;
                padding: 4px;
            }
            .items-table tbody tr {
                border-bottom: 1px solid white;
            }
            </style></head><body><header>';

            $header .= '<table style="width:100%; border-collapse: collapse;">
                        <tr>
                       <!-- LEFT SIDE: Logo, Company Box, Customer Box -->
                        <td style="width:55%; vertical-align:top;">
                            <!-- Company Logo -->
                            <div style="text-align:center;">
                                <img src="' . $left_logo . '" height="165px" width="230px" style="display:block;">
                            </div>

                            <!-- SALES INVOICE Heading -->
                            <div style="font-size:22px; font-weight:bold; color:#1367A4; text-align:center; margin-bottom:4px;">
                                SALES INVOICE
                            </div>

                            <!-- Box Wrapper to control width -->
                            <div style="width:99%; text-align:left;">

                            
                <!-- Company Info Box -->
                <div style="border:1px solid #1367A4; font-size:13px; padding:0px 4px; border-radius:6px; margin-bottom:15px; text-align:center ; margin-top:-2px">
                    <b>' . $company['cname'] . '</b><br>
                    ' . $company['address'] . ' ' . $company['city'] . ' ' . $company['region'] . ', ' . $company['postbox'] . ', ' . $company['country'] . ' 
                    <br>
                    <table style="width:100%; font-size:13px; margin-top:2px; border-collapse:collapse;">
                    <tr>
                    <td style="text-align:left; white-space:nowrap;"><b>Phone:</b></td>
                    <td style="text-align:left;">' . $company['phone'] . '</td>
                </tr>
                <tr>
                    <td style="text-align:left; white-space:nowrap;"><b>Mobile:</b></td>
                    <td style="text-align:left;">' . $company['mobile'] . '</td>
                </tr>

                    </table>
                </div>





            <!-- Customer "Bill To" Box -->
            <div style="border:1px solid #1367A4; font-size:13px; padding:1px 4px; border-radius:6px; background-color:#f9f9f9; margin-top:-12px">
                <table style="width:100%; font-size:13px; border-collapse:collapse;">
                    <tr>
                        <td style="font-weight:bold; width:16%; vertical-align:top;">Bill To:</td>
                        <td style="text-align:left; font-size:11px">' . ($data['invoice']['company'] ? $data['invoice']['company'] : '&nbsp;') . '</td>
                    </tr>
                    <tr>
                        <td style="font-weight:bold; width:16%; vertical-align:top;">Address:</td>
                        <td style="text-align:left;font-size:11px ; word-wrap:break-word;">' . $data['invoice']['cust_address'] . ' ' . $data['invoice']['cust_city'] . ' ' . $data['invoice']['cust_postcode'] . '</td>
                    </tr>';


            $header .= '</table></div>
                                            </div>
                                        </td>';
            // Right side - Invoice and Bank Details with Special Notes
            $header .= '
                                    <td style="width:55%; vertical-align:top; padding:5px;">
                                        <!-- Top Section: Logo + Invoice Details -->
                                        <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 4px;">
                                        <!-- Right Logo (Floated to extreme right) -->
                                            <div style="position: absolute; top: 0; right: 0;">
                                                <img src="' . $right_logo . '" height="45px" width="45px" style="margin: 0;">
                                            </div>
                                            <!-- Left: Invoice Details Table -->
                                            <div style="width:85%;">
                                                <table style="width: 100%; border-collapse: collapse;">
                                                    <tr>
                                                        <th style="border: 1px solid #1367A4; padding: 3px 6px; text-align: center; font-size:11px; font-weight:bold;">Date:</th>
                                                        <td style="border: 1px solid #1367A4; padding: 3px 6px; text-align: center; font-size:11px;">' . date("d-m-Y", strtotime($data["invoice"]["invoicedate"])) . '</td>
                                                    </tr>
                                                    <tr>
                                                        <th style="border: 1px solid #1367A4; padding: 3px 6px; text-align: center; font-size:11px; font-weight:bold;">A/C:</th>
                                                        <td style="border: 1px solid #1367A4; padding: 3px 6px; text-align: center; font-size:11px;">' . $data["invoice"]["name"] . '</td>
                                                    </tr>
                                                    <tr>
                                                        <th style="border: 1px solid #1367A4; padding: 3px 6px; text-align: center; font-size:11px; font-weight:bold;">Invoice#</th>
                                                        <td style="border: 1px solid #1367A4; padding: 3px 6px; text-align: center; font-size:11px;">' . $data['invoice']['tid'] . '</td>
                                                    </tr>
                                                    <tr>
                                                        <th style="border: 1px solid #1367A4; padding: 3px 6px; text-align: center; font-size:11px; font-weight:bold;">Company#</th>
                                                        <td style="border: 1px solid #1367A4; padding: 3px 6px; text-align: center; font-size:11px;">12070999</td>
                                                    </tr>
                                                    <tr>
                                                        <th style="border: 1px solid #1367A4; padding: 3px 6px; text-align: center; font-size:11px; font-weight:bold;">Vat Reg</th>
                                                        <td style="border: 1px solid #1367A4; padding: 3px 6px; text-align: center; font-size:11px;">497981705</td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>

                                        <!-- Bank Details Table -->
                                        <table style="width: 100%; border-collapse: collapse; margin-bottom:4px;">
                                            <tr>
                                                <th style="border: 1px solid #1367A4; padding: 3px 6px; text-align: center; font-size:11px; font-weight:bold;">Bank Name</th>
                                                <td style="border: 1px solid #1367A4; padding: 3px 6px; text-align: center; font-size:11px;">Lyods</td>
                                            </tr>
                                            <tr>
                                                <th style="border: 1px solid #1367A4; padding: 3px 6px; text-align: center; font-size:11px; font-weight:bold;">Account Name</th>
                                                <td style="border: 1px solid #1367A4; padding: 3px 6px; text-align: center; font-size:11px;">' . $company['cname'] . '</td>
                                            </tr>
                                            <tr>
                                                <th style="border: 1px solid #1367A4; padding: 3px 6px; text-align: center; font-size:11px; font-weight:bold;">Account Number</th>
                                                <td style="border: 1px solid #1367A4; padding: 3px 6px; text-align: center; font-size:11px;">' . $company['taxid'] . '</td>
                                            </tr>
                                            <tr>
                                                <th style="border: 1px solid #1367A4; padding: 3px 6px; text-align: center; font-size:11px; font-weight:bold;">Sort Code</th>
                                                <td style="border: 1px solid #1367A4; padding: 3px 6px; text-align: center; font-size:11px;">' . $company['sortcode'] . '</td>
                                            </tr>
                                        </table>

                                        <!-- Special Notes -->
                                        <div style="border:1px solid #1367A4; font-size:13px; padding:5px 8px; border-radius:4px; background-color:#f0f8ff;">
                                            <strong>Special Note:</strong><br>
                                            <span style="font-size:11px;">KINGSLEY GB DRINKS 330ML CAN ON PROMOTION</span><br>
                                            <span style="color:#ff6600; font-size:11px;">COLA &nbsp;&nbsp; CHERRYADE &nbsp;&nbsp; LEMONADE &nbsp;&nbsp; ORANGEAD</span><br>
                                            <strong style="font-size:11px;">ALL ARE EXCLUSIVELY 5.49+ VAT</strong>
                                        </div>
                                        <!-- Payment Terms Box -->
                                        <div style="border:1px solid #1367A4; font-size:13px; padding:10px; border-radius:6px; background-color:#f9f9f9; margin-top:5px;">
                                        <!-- Add TERMS row if applicable -->
                                            <table style="width:100%; font-size:13px;">
                                            <tr>
                                                <td style="font-weight:bold; color:#cc0000;">Payment Terms:</td>
                                                <td style="text-align:right;"><span style="background-color:yellow;">' . $custom_fields . '</span></td>
                                            </tr></table>
                                            </div>
                                    </td>';

            $header .= '</tr></table></header>';
        } else {
            $header = '<html><head><style>
            @page { 
                margin: 350px 50px 200px; 
            }
            header{ 
                position: fixed;  
                top: -320px;
            }
            footer {
                position: fixed;
                bottom: -150px;
                width: 100%;
            }
            table{
                width:100%;
            }
            .pagenum:after {
                content: counter(page);
            }
            .page-break {
                page-break-before: always;
            }
            .items-table {
                width: 100%;
                border: 1px solid #1367A4;
                border-radius: 6px;
                border-collapse: collapse;
            }
            .items-table thead th {
                font-size: 14px;
                background: #ccc;
                padding: 4px;
            }
            .items-table tbody tr {
                border-bottom: 1px solid white;
            }
            </style></head><body><header><span style="float:right;" class="pagenum">Page:</span>';

            // Main header table
            $header .= '<table style="width:100%; border-collapse: collapse;">
                    <tr>
                        <!-- Left side - Cash Format Title and Special Notes -->
                        <td style="width:50%; vertical-align:top; padding:5px;">
                            <!-- Cash Format Header -->
                            <div style="border:2px solid #000; font-size:20px; font-weight:bold; padding:10px; text-align:center; background-color:#f0f0f0; margin-top:40px; margin-bottom:80px;">
                                CASH FORMAT
                            </div>
                            
                            <!-- Special Notes Box -->
                             <div style="border:1px solid #1367A4; font-size:16px; padding:8px; border-radius:4px; background-color:#f0f8ff;">
                                <strong>Special Note:</strong><br>
                                <span style="font-size:11px;">KINGSLEY GB DRINKS 330ML CAN ON PROMOTION</span><br>
                                <span style="color:#ff6600; font-size:11px;">COLA &nbsp;&nbsp; CHERRYADE &nbsp;&nbsp; LEMONADE &nbsp;&nbsp; ORANGEAD</span><br>
                                <strong style="font-size:11px;">ALL ARE EXCLUSIVELY 5.49+ VAT</strong>
                            </div>
                        </td>
                        
                        <!-- Right side - Invoice Details -->
                        <td style="width:50%; vertical-align:top; padding:5px;">
                            <!-- Invoice Details Table -->
                            <table style="width:80%; border-collapse:collapse; border:1px solid #000; margin-top:24px;  margin-bottom:28px;">
                                <tr>
                                    <th style="border:1px solid #000; padding:4px; text-align:center; background-color:#e6f2ff; font-size:16px;">Date</th>
                                    <td style="border:1px solid #000; padding:4px; text-align:center; font-size:16px;">' . date("d/m/Y", strtotime($data["invoice"]["invoicedate"])) . '</td>
                                </tr>
                                <tr>
                                    <th style="border:1px solid #000; padding:4px; text-align:center; background-color:#e6f2ff; font-size:16px;">A/C</th>
                                    <td style="border:1px solid #000; padding:4px; text-align:center; font-size:16px;">XYZ</td>
                                </tr>
                                <tr>
                                    <th style="border:1px solid #000; padding:4px; text-align:center; background-color:#e6f2ff; font-size:16px;">CSH#</th>
                                    <td style="border:1px solid #000; padding:4px; text-align:center; font-size:16px;">#' . $data['invoice']['tid'] . '</td>
                                </tr>
                            </table>
                                <!-- Customer "Bill To" Box -->
                                <div style="border:1px solid #1367A4; font-size:13px; padding:0px 10px; margin-top:24px; margin-bottom:8px; background-color:#f9f9f9;">
                                    <table style="width:100%; font-size:13px;">
                                        <tr>
                                            <td style="font-weight:bold; width:80px;">Bill To:</td>
                                            <td style="text-align:right;">' . ($data['invoice']['company'] ? $data['invoice']['company'] : '&nbsp;sajawal') . '</td>
                                        </tr>
                                        <tr>
                                            <td style="font-weight:bold;">Address:</td>
                                            <td style="text-align:right;">' . $data['invoice']['cust_address'] . ' ' . $data['invoice']['cust_city'] . ' ' . $data['invoice']['cust_postcode'] . '</td>
                                        </tr>
                                    </table>
                                </div>
                            
                           <!-- Payment Terms Box -->
                                        <div style="border:1px solid #1367A4; font-size:13px; padding:10px; margin-bottom:8px; background-color:#f9f9f9;">
                                            <table style="width:100%; font-size:13px;">
                                                <tr>
                                                    <td style="font-weight:bold; color:#cc0000;">Payment Terms:</td>
                                                    <td style="text-align:right;"><span style="background-color:yellow;">' . $custom_fields . '</span></td>
                                                </tr>
                                            </table>
                                        </div>
             
                            
                            <!-- Invalid Pattern Notice -->
                            <div style="border:2px solid #000; font-size:12px; font-weight:bold; padding:6px; text-align:center; background-color:#ffcccc;">
                                INVALID PATTERN NOT FOR OFFICIAL USE
                            </div>
                        </td>
                    </tr>
                </table></header>';
        }

        // Modified product section with pagination
        $header .= '<main>';

        // Group products into pages of 15 items each
        $items_per_page = 20;
        $filtered_products = [];

        // Filter products with qty > 0
        foreach ($data['products'] as $row) {
            if ($row['qty'] > 0 && (!empty($row['product_des']) && $row['product_des'] != "")) {
                $filtered_products[] = $row;
            }
        }

        $total_items = count($filtered_products);
        $total_pages = ceil($total_items / $items_per_page);

        for ($page = 0; $page < $total_pages; $page++) {
            $start_index = $page * $items_per_page;
            $end_index = min($start_index + $items_per_page, $total_items);

            // Add page break for pages after the first one
            if ($page > 0) {
                $header .= '<div class="page-break"></div>';
            }
            $header .= '<div style="width:100%; margin-top:10px;">
            <table class="items-table" style="width:100%; border-collapse:collapse;">
                <thead style="background:#f0f0f0;">
                    <tr>
                        <th style="width: 12%; text-align:center; padding:6px;">' . $lang('Quantity') . '</th>
                        <th style="border-left: 1px solid #1367A4; width: 45%; padding:6px; text-align:left;">' . $lang('Details') . '</th>
                        <th style="width: 13%; text-align:center; border-left: 1px solid #1367A4; padding:6px;">' . $lang('Unit Price') . '</th>
                        <th style="width: 20%; text-align:center; border-left: 1px solid #1367A4; padding:6px;">' . $lang('Net Amount') . '</th>
                        <th style="width: 10%; text-align:center; border-left: 1px solid #1367A4; padding:6px;">' . $lang('Tax') . '</th>
                    </tr>
                </thead>
                <tbody style="font-size:15px;">
        ';

            for ($i = $start_index; $i < $end_index; $i++) {
                $row = $filtered_products[$i];

                $row_style = ($row['serial'] == '1') ? 'background:#FBF36D;' : '';

                $header .= '<tr style="' . $row_style . ' border-bottom:1px solid white;">
                <td style="border-bottom:1px solid white; text-align:center;">' . (int)$row["qty"] . $row["unit"] . '</td>
                <td style="border-bottom:1px solid white; text-align:left;">' . $row["product_des"] . '</td>
                <td style="border-bottom:1px solid white; text-align:center;">' . rev_amountExchange_s($row['price']) . '</td>
                <td style="border-bottom:1px solid white; text-align:center;">' . rev_amountExchange_s($row['subtotal']) . '</td>
                <td style="border-bottom:1px solid white; text-align:center;">' . rev_amountExchange_s($row['totaltax']) . '</td>
            </tr>';
            }

            // Fill remaining rows with empty cells if this is the last page and has fewer than 15 items
            $current_page_items = $end_index - $start_index;
            if ($page == $total_pages - 1 && $current_page_items < $items_per_page) {
                $empty_rows = $items_per_page - $current_page_items;
                for ($j = 0; $j < $empty_rows; $j++) {
                    $header .= '<tr style="height: 25px;">
                        <td style="border-bottom:1px solid white;">&nbsp;</td>
                        <td style="border-bottom:1px solid white;">&nbsp;</td>
                        <td style="border-bottom:1px solid white;">&nbsp;</td>
                        <td style="border-bottom:1px solid white;">&nbsp;</td>
                        <td style="border-bottom:1px solid white;">&nbsp;</td>
                        </tr>';
                }
            }

            $header .= '</tbody></table></div>';

            // Add page number indicator
            if ($total_pages > 1) {
                $header .= '<div style="text-align: right; margin-top: 10px; font-size: 12px;">
                    Page ' . ($page + 1) . ' of ' . $total_pages . '
                    </div>';
            }
        }

        $header .= '</main>';

        // Footer Section
        $footer = '<footer>';
        if ($data['invoice']['inv_type'] == 'INVOICE') {
            $footer .= '
                        <table style="width:100%; border-collapse:collapse; margin-top:-170px;">
                            <tr>
                                <!-- Left Side: Notes + Customer -->
                                <td style="width:65%; vertical-align:top; padding:5px;">
                                    <div style="border:1px solid #1367A4; font-size:14px; padding:8px; border-radius:6px;">
                                        <strong style="color:rgb(48, 37, 133)">NOTE: *THE HIGHEST CHANGE ALLOWED IS.=£30</strong><br>
                                        Notification of any claims must be informed at the time of delivery.<br>
                                        Claims submitted after delivery won\'t be taken into account.<br>
                                        Frozen and chilled products cannot be returned once delivered.<br>
                                        ' . $company['cname'] . ' will retain ownership of the goods until the consumer has made full payment.<br><br>

                                    <table style="width:100%; border-collapse:collapse;">
                                            <tr>
                                                <!-- Left column: Customer & Driver (stacked) -->
                                                <td style="width:53%; vertical-align:top;">
                                                    <div style="border:1px solid #000; height:30px; border-radius:4px; padding-left:4px;"><strong>Customer:</strong></div>
                                                    <div style="border:1px solid #000; height:30px; border-radius:4px; margin-top:4px; padding-left:4px;"><strong>Driver:</strong></div>
                                                </td>
                                                <td></td>
                                                <!-- Right column: Payments & Amount -->
                                                <td style="width:45%; vertical-align:top;">
                                                    <!-- Payment methods (Cash / Card / BACS) -->
                                                    <table style="width:100%; border-collapse:collapse;">
                                                        <tr>
                                                            <td style="text-align:center;"><strong>Cash</strong></td>
                                                            <td style="text-align:center;"><strong>Card Payment</strong></td>
                                                            <td style="text-align:center;"><strong>BACS</strong></td>
                                                        </tr>
                                                        <tr>
                                                            <td><div style="border:1px solid #000; height:20px; border-radius:4px;"></div></td>
                                                            <td><div style="border:1px solid #000; height:20px; border-radius:4px;"></div></td>
                                                            <td><div style="border:1px solid #000; height:20px; border-radius:4px;"></div></td>
                                                        </tr>
                                                    </table>

                                                    <!-- Amount Box -->
                                                    <div style="border:1px solid #000; height:20px; border-radius:4px; margin-top:3px; padding-left:4px;">£</div>
                                                </td>
                                            </tr>
                                        </table>

                                    </div>
                                </td>

                                <!-- Right Side: Totals + Contact Info -->
                                <td style="width:35%; vertical-align:top; padding:5px;">
                                    <div style="border:1px solid #1367A4; font-size:13px; padding:4px; border-radius:6px; background-color:#f1f7fc;">
                                        <table style="width:100%; font-size:13px;">
                                            <tr><td>' . $lang('NET Amount') . '</td><td style="text-align:right;">' . amountExchange($data['invoice']['subtotal']) . '</td></tr>
                                            <tr><td>' . $lang('Total Tax Amount') . '</td><td style="text-align:right;">' . amountExchange($data['invoice']['tax']) . '</td></tr>
                                            <tr><td><strong>' . $lang('Grand Total') . '</strong></td><td style="text-align:right;"><strong>' . amountExchange($data['invoice']['total']) . '</strong></td></tr>';
            if (!$hide_balance) {
                $footer .= '<tr>
                                                                                <td>' . $lang('Previous Balance') . '</td>';
                $invbalance = 0;
                if ($cust_balance_inv >= $data['invoice']['total']) {
                    $invbalance =  $cust_balance_inv - $data['invoice']['total'];
                    $footer .= '<td style="text-align:right;">' . amountExchange($invbalance, $data['invoice']['multi'], $data['invoice']['loc']) . '</td>';
                } else {
                    $footer .= '<td style="text-align:right;">' . amountExchange($cust_balance_inv, $data['invoice']['multi'], $data['invoice']['loc']) . '</td>';
                }
                $footer .= '</tr>
                                                    <tr><td><strong>' . $lang('Balance Now Due') . '</strong></td><td style="text-align:right;"><strong>' . amountExchange($cust_balance_inv) . '</strong></td></tr>';
            }
            $footer .= '</table>
                                    </div>
                                    <!--Email & Website-->
                                    <div style="margin-top:10px; font-size:12px;">
                                        <img src="' . base_url("assets/images/mail.jpg") . '" width="20" height="20" style="vertical-align:middle;">&nbsp;' . $company['email'] . '<br>
                                        <img src="' . base_url("assets/images/web.jpg") . '" width="20" height="20" style="vertical-align:middle;">&nbsp;http://www.digifoodsdistribution.com/
                                    </div>
                                    <!-- Thank You / Bank Note -->
                                    <div style="margin-top:10px; font-size:12px;">
                                        <table style="width:100%; border:1px solid #1367A4; border-radius:6px;">
                                            <tr><td style="padding:5px;">We appreciate your business and trust..!</td></tr>
                                        </table>
                                    </div> <!-- end thank you and bank note -->
                                </td>
                            </tr>
                        </table>';
        } else {
            // Cash Format Footer
            $footer .= '
                        <table style="width:100%; border-collapse:collapse; margin-top:-170px;">
                            <tr>
                                <!-- Left Side: Notes + Customer -->
                                <td style="width:65%; vertical-align:top; padding:5px;">
                                    <div style="border:1px solid #1367A4; font-size:14px; padding:8px; border-radius:6px;">
                                        <strong style="color:rgb(48, 37, 133)">NOTE: *THE HIGHEST CHANGE ALLOWED IS.=£30</strong><br>
                                        Notification of any claims must be informed at the time of delivery.<br>
                                        Claims submitted after delivery won\'t be taken into account.<br>
                                        Frozen and chilled products cannot be returned once delivered.<br>
                                        ' . $company['cname'] . ' will retain ownership of the goods until the consumer has made full payment.<br><br>

                                    <table style="width:100%; border-collapse:collapse;">
                                            <tr>
                                                <!-- Left column: Customer & Driver (stacked) -->
                                                <td style="width:53%; vertical-align:top;">
                                                    <div style="border:1px solid #000; height:30px; border-radius:4px; padding-left:4px;"><strong>Customer:</strong></div>
                                                    <div style="border:1px solid #000; height:30px; border-radius:4px; margin-top:4px; padding-left:4px;"><strong>Driver:</strong></div>
                                                </td>
                                                <td></td>
                                                <!-- Right column: Payments & Amount -->
                                                <td style="width:45%; vertical-align:top;">
                                                    <!-- Payment methods (Cash / Card / BACS) -->
                                                    <table style="width:100%; border-collapse:collapse;">
                                                        <tr>
                                                            <td style="text-align:center;"><strong>Cash</strong></td>
                                                            <td style="text-align:center;"><strong>Card Payment</strong></td>
                                                            <td style="text-align:center;"><strong>BACS</strong></td>
                                                        </tr>
                                                        <tr>
                                                            <td><div style="border:1px solid #000; height:20px; border-radius:4px;"></div></td>
                                                            <td><div style="border:1px solid #000; height:20px; border-radius:4px;"></div></td>
                                                            <td><div style="border:1px solid #000; height:20px; border-radius:4px;"></div></td>
                                                        </tr>
                                                    </table>

                                                    <!-- Amount Box -->
                                                    <div style="border:1px solid #000; height:20px; border-radius:4px; margin-top:3px; padding-left:4px;">£</div>
                                                </td>
                                            </tr>
                                        </table>

                                    </div>
                                </td>

                                <!-- Right Side: Totals + Contact Info -->
                                <td style="width:35%; vertical-align:top; padding:5px;">
                                    <div style="border:1px solid #1367A4; font-size:13px; padding:4px; border-radius:6px; background-color:#f1f7fc;">
                                        <table style="width:100%; font-size:13px;">
                                            <tr><td>' . $lang('NET Amount') . '</td><td style="text-align:right;">' . amountExchange($data['invoice']['subtotal']) . '</td></tr>
                                            <tr><td>' . $lang('Total Tax Amount') . '</td><td style="text-align:right;">' . amountExchange($data['invoice']['tax']) . '</td></tr>
                                            <tr><td><strong>' . $lang('Grand Total') . '</strong></td><td style="text-align:right;"><strong>' . amountExchange($data['invoice']['total']) . '</strong></td></tr>';
            if (!$hide_balance) {
                $footer .= '<tr>
                                                                                <td>' . $lang('Previous Balance') . '</td>';
                $invbalance = 0;
                if ($cust_balance_inv >= $data['invoice']['total']) {
                    $invbalance =  $cust_balance_inv - $data['invoice']['total'];
                    $footer .= '<td style="text-align:right;">' . amountExchange($invbalance, $data['invoice']['multi'], $data['invoice']['loc']) . '</td>';
                } else {
                    $footer .= '<td style="text-align:right;">' . amountExchange($cust_balance_inv, $data['invoice']['multi'], $data['invoice']['loc']) . '</td>';
                }
                $footer .= '</tr>
                                                    <tr><td><strong>' . $lang('Balance Now Due') . '</strong></td><td style="text-align:right;"><strong>' . amountExchange($cust_balance_inv) . '</strong></td></tr>';
            }
            $footer .= '</table>
                                    </div>
                                    <!--Email & Website-->
                                    <div style="margin-top:5px; font-size:12px;">
                                        <img src="' . base_url("assets/images/mail.jpg") . '" width="20" height="20" style="vertical-align:middle;">&nbsp;' . $company['email'] . '<br>
                                        <img src="' . base_url("assets/images/web.jpg") . '" width="20" height="20" style="vertical-align:middle;">&nbsp;http://www.digifoodsdistribution.com/
                                    </div>
                                    <!-- Thank You / Bank Note -->
                                    <div style="margin-top:5px; font-size:12px;">
                                        <table style="width:100%; border:1px solid #1367A4; border-radius:6px;">
                                            <tr><td style="padding:2px; text-align:center;">ITS AN AUTO GENERATED SYSTEM TESTING INVOICE WHICH IS NOT VALID FOR OFFICIAL USE</td></tr>
                                        </table>
                                    </div> <!-- end thank you and bank note -->
                                </td>
                            </tr>
                        </table>';
        }
        $footer .= '</footer>';

        // Complete the HTML structure
        $header .= $footer;
        $header .= '</body></html>';

        // Generate and stream PDF
        $file_name = $this->input->get('id');
        $this->dpdf->loadHtml($header);
        $this->dpdf->render();
        $output = $this->dpdf->output();
        $file_location = 'userfiles/invoices_bfr_postr/sale_' . $file_name . '.pdf';
        file_put_contents($file_location, $output);
        $this->dpdf->stream("" . $file_name . ".pdf", array("Attachment" => 0));
    }
    // printunposted


    public function printinvoice()
    {
        // Explicitly load language file for PDF generation
        $system_lang = $this->db->query("SELECT lang FROM geopos_system WHERE id=1 LIMIT 1")->row_array();
        $lang_to_load = $system_lang['lang'] ?? 'english';
        $this->lang->load($lang_to_load, $lang_to_load);

        // Get dynamic tax name for placeholder replacement
        $dynamic_tax_name = getTaxName();

        // Helper function to get and replace language lines
        $that = $this; // Store controller reference for closure
        $lang = function ($key) use ($that, $dynamic_tax_name) {
            $text = $that->lang->line($key);
            if ($text === FALSE || empty($text)) {
                return $key; // Return key if translation not found
            }
            // Replace tax placeholders
            return str_replace(
                array('{tax}', '{Tax}', '{TAX}'),
                array(strtolower($dynamic_tax_name), ucfirst(strtolower($dynamic_tax_name)), strtoupper($dynamic_tax_name)),
                $text
            );
        };

        $company = $this->settings->company_details(1);

        $tid = $this->input->get('id');
        // If called in status-only mode from AJAX (d=1), just set print_status and return JSON
        $d = $this->input->get('d');
        if ($d == '1' || $d == 1) {
            $sql = 'Update geopos_invoices set print_status=1 where id= ' . (int)$tid . ' ';
            $this->db->query($sql);
            // header('Content-Type: application/json');
            // echo json_encode(['status' => 'ok', 'id' => (int)$tid]);
            // return;
        }
        $data['id'] = $tid;
        $data['invoice'] = $this->invocies->invoice_details($tid, $this->limited);
        $data['products'] = $this->invocies->invoice_products($tid);
        $data['employee'] = $this->invocies->employee($data['invoice']['eid']);
        if ($data['invoice']['i_class'] == 1) {
            $pref = prefix(7);
        } else {
            $pref = $this->config->item('prefix');
        }
        ob_end_clean();
        ob_start();

        $sql = 'SELECT sum(total)-sum(pamnt) as "cust_balance" FROM geopos_invoices WHERE status="due" and csd="' . $data["invoice"]["csd"] . '" or status="partial" and csd="' . $data["invoice"]["csd"] . '"';
        $query = $this->db->query($sql);
        $response = $query->result_array();
        $cust_balance_inv = $response[0]['cust_balance'];

        // $cust_balance_inv= $response[0]['cust_balance'];
        $sql2 = "SELECT content FROM `geopos_notes` ORDER BY `geopos_notes`.`id`  DESC LIMIT 1";
        $query2 = $this->db->query($sql2);
        $response2 = $query2->result_array();
        $content2 = $response2[0]['content'];

        // Status update handled above for d=1



        ini_set('memory_limit', '64M');

        $data_tr = "";

        $left_logo = base_url("userfiles/company/" . $company['logo']);
        $data['left_logo'] = $left_logo;
        $right_logo = base_url("assets/images/halal_logo.jpg");
        $data['right_logo'] = $right_logo;
        ob_end_clean();
        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            $header = '<html><head><style>@page { margin: 350px 50px 250px; }header{ position: fixed;  top: -320px;}footer{ position: fixed; bottom: -220px; height: 240px; }table{width:100%;}.pagenum:after {content: counter(page)}</style> </head><body><header>';
            $header .= ' <div style="width:100%; "><div style="width:23%; float:left; text-align:center;">  <img src="' . $left_logo . '" height="150px" width="180px"> </div><div style="width:48%; margin-left:20px; float:left; text-align:center;"> <div style="font-size: 30px;"><b>' . $company['cname'] . '</b></div><div style="font-size: 14px;"><div>' . $company['address'] . '</div><div>' . $company['region'] . ', ' . $company['city'] . ' ' . $company['postbox'] . ' ' . $company['country'] . '</div><div>Phone: ' . $company['phone'] . '&nbsp;&nbsp;&nbsp;&nbsp; Mobile:&nbsp;&nbsp;' . $company['mobile'] . '</div><div>Email: ' . $company['email'] . '</div></div> </div><span style="float:right;"  class="pagenum">Page:</span><div style="width:25%; float:left; text-align:center;">  <img src="' . $right_logo . '" height="100px" width="100px"> 
                        </div></div><div style="clear:both"></div>';
        } else {
            $header = '<html><head><style>@page { margin: 350px 50px 250px; }header{ position: fixed;  top: -320px;}footer{ position: fixed; bottom: -220px; height: 240px; }table{width:100%;}.pagenum:after {content: counter(page)}</style> </head><body><header><span style="float:right;"  class="pagenum">Page:</span>';
            $header .= ' <div style="width:100%; "><div style="width:23%; float:left; text-align:center;">
                        </div></div><div><p style="text-align:center;height:100px;">Note: This is not a valid Invoice  </p>';

            $header .= '&nbsp;Invoice Date:&nbsp;&nbsp;&nbsp;' . date("d-m-Y", strtotime($data["invoice"]["invoicedate"])) . '</div><div style="clear:both"></div>';
        }

        $header .= '<div style="border:1px solid #1367A4;width:40%;height:100px; padding: 8px 10px 8px; border-radius: 6px;float:left;margin-top:5px;">';
        if ($data['invoice']['company']) {
            $header .= $data['invoice']['company'] . '<br>';
        }

        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            $header .= $data['invoice']['cust_address'] . '<br/>' . $data['invoice']['cust_city'] . '<br/>' . $data['invoice']['cust_postcode'] . "";
            if ($data['invoice']['pamt_terms']) {
                $header .= '<br><b> <span style="background-color:yellow">TERMS:</span> </b>' . $custom_fields . '';
            }
            // if ($data['invoice']['country']) {
            //      $header.= '<br>' . $data['invoice']['country'];
            // }
        } else {
            $header .= ' <br> ' . $data['invoice']['cust_address'] . '<br/>' . $data['invoice']['cust_city'] . '<br/>' . $data['invoice']['cust_postcode'] . "";
        }
        // if ($data['invoice']['postbox']){
        //     $header.=' <br> ' . $data['invoice']['cust_address'];
        // } 
        // if ($data['invoice']['email']){
        //     $header.= '<br> ' . $this->lang->line('Email') . ': ' . $data['invoice']['email'];
        // }

        $header .= '</div><div style="float:right;margin-left:-150px;margin-top:-30px;width:50%" ><div style=" font-size: 18px; margin-top: 12px">';



        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            $header .= '<b>&nbsp;Invoice</b>';
            $header .= '</div><table style="width:100%; font-size: 14px; border:1px solid #1367A4;">';
        } else {
            $header .= '&nbsp;';
        }

        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            $header .= '<tr><td style="width: 50%; border-bottom:1px solid #1367A4;background: lightgrey;padding: 8px 0px 8px 7px;">&nbsp;Invoice No.</td>
            <td  style="border-bottom:1px solid #1367A4;">';
            $header .= $data['invoice']['tid'];
            $header .= ' </td></tr><tr><td style="width: 50%; border-bottom:1px solid #1367A4;background: lightgrey;padding:  8px 0px 8px 7px;">&nbsp;Invoice Date</td><td style="border-bottom:1px solid #1367A4;"> ' . date("d-m-Y", strtotime($data["invoice"]["invoicedate"])) . ' </td></tr>';
        } else {
            $header .= ' Day Pass';
        }



        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            $header .= '  <tr><td style="width: 50%; background: lightgrey;padding: 8px 0px 8px 7px;">&nbsp;Account No.</td><td> ' . $data["invoice"]["name"] . '</td></tr>';
            $header .= '</table><div>';
        }




        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            $header .= ' ';
            $header .= '
           
            <table style="width:100%; font-size: 14px; border:1px solid #1367A4; margin-top:5px;"><tr><td style="width: 100%;padding: 8px 0px 8px 7px;">&nbsp;<b> Chilled Products 0-4&deg;C <span style="border:1px solid black;">&nbsp;&nbsp;&nbsp;</span> Frozen Products -18&deg;C <span style="border:1px solid black;">&nbsp;&nbsp;&nbsp;</span></b></td></tr></table>
            
            ';
        }

        // if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
        //     $header .= ' &nbsp;VAT Reg No: 375 6160 81 ';
        // }
        $header .= '</div></div></header>';


        $header .= '<div style="clear:both"></div><main><div style="width:100%;  ">
        <table width="100%" class="myProducts" style="border:1px solid #1367A4;"><thead>
        <tr style="font-size: 14px; background:#ccc; padding:4px;">
        <th style="width: 12%; text-align:left; ">' . $lang('Quantity') . '</th>
        <th style="border-left: 1px solid #1367A4;width: 45%; text-align:left;">' . $lang('Details') . '</th>
        <th style="width: 13%; text-align:left; border-left: 1px solid #1367A4;">' . $lang('Unit_Rate') . '</th>
        <th style="width: 20%; text-align:left; border-left: 1px solid #1367A4;">' . $lang('Net Amount') . '</th>
        <th style="width: 10%;  text-align:left;border-left: 1px solid #1367A4;">' . $lang('Tax') . '</th></tr></thead>';
        $header .= '<tbody style="font-size:15px;">';

        $ns = 0;
        foreach ($data['products'] as $row) {
            $ns++;
            if (empty($row['product_des']) || $row['product_des'] == "") {
            } else {
                if ($row['serial'] == '1') {
                    $header .= '<tr style="background:#FBF36D; style="font-size:18px;"">';
                } else {
                    $header .= '<tr style=" border-bottom:1px solid white;">';
                }
                $header .= '<td style=" border-bottom:1px solid white;">' . (int)$row["qty"] . '</td style=" border-bottom:1px solid white;">
                <td style=" border-bottom:1px solid white;">' . $row["product_des"] . '</td>
                <td style=" border-bottom:1px solid white;">' . rev_amountExchange_s($row['price'], $invoice['multi'], $invoice['loc']) . '</td>
                 <td style=" border-bottom:1px solid white;">' . rev_amountExchange_s($row['subtotal'], $invoice['multi'], $invoice['loc']) . '</td>
                 <td style=" border-bottom:1px solid white;">' . rev_amountExchange_s($row['totaltax'], $invoice['multi'], $invoice['loc']) . ' </td></tr>';
            }
        }
        $header .= '</tbody></table></div></main>';
        // Use database values instead of calculating totals

        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {

            $header .= '<footer>';
            if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
                $header .= '<div style="border:1px solid #1367A4;width:50%;height:140px; padding: 8px 0px 5px 4px; border-radius: 6px;float:left;margin-top:5px"><span style="color:rgb(48, 37, 133)"><b>*MAXIMUM CHANGE ACCEPTED=&#163;25</b></span><div style=" font-size: 10px;">All goods remain the property of ' . $company['cname'] . ' until all monies are paid in full<br>Any complaints should be made within 48 hours of purchasing.</div><br><div style="width:100%"><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left">Print Name:</div><div style="border:1px solid black;width:10%;height:18px;float:left;margin-left:10px;;margin-left:-78px;"></div><div style="float:left;margin-left:-158px;margin-top:3px;font-size: 12px;"><b>Cash Tendered</b></div><div style="clear:both"></div><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left;">Signature:</div><div style="font-size: 17px;padding-left:10px;border:1px solid black;width:35%;height:20px;float:left;padding-top:3px;padding-bottom:3px;margin-left:-157px;margin-top:-8px">&#163;</div><div style="clear:both"></div></div><div style="clear:both"></div></div>';
            }

            // $sub_ts = 0;
            // foreach ($data['products'] as $row) {
            //     $sub_ts += $row['price'] * $row['qty'];
            // }

            $header .= '<div style="float:right;margin-left:320px;width:280px" ><table class="maintable2" style="width:100%; border:1px solid #1367A4;font-size: 14px;">';

            if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
                $header .= '<tr ><td style="width: 50%;  background: lightgrey;padding: 4px 0px 4px 7px;">&nbsp;Previous Balance</td><td>';
                $invbalance = 0;
                if ($cust_balance_inv >= $data['invoice']['total']) {
                    $invbalance =  $cust_balance_inv - $data['invoice']['total'];
                    // $header .=  substr(amountExchange($invbalance, $data['invoice']['multi'], $data['invoice']['loc']), 3, 100);
                    $header .=  amountExchange($invbalance, $data['invoice']['multi'], $data['invoice']['loc']);
                } else {
                    $header .=  amountExchange($cust_balance_inv, $data['invoice']['multi'], $data['invoice']['loc']);
                    // $header .=  substr(amountExchange($cust_balance_inv, $data['invoice']['multi'], $data['invoice']['loc']), 3, 100);
                }
                $header .= '</td></tr>';
            }

            $header .= '<tr><td style="width: 60%;  background: lightgrey;padding:  4px 0px 4px 7px;">&nbsp;Total NET Amount</td><td> ' . amountExchange($data["invoice"]["subtotal"]) . ' </td></tr>
            <tr ><td style="width: 60%;  background: lightgrey;padding: 4px 0px 4px 7px;">&nbsp;Total ' . $this->lang->line('Tax') . ' Amount</td><td> ' . amountExchange($data["invoice"]["tax"]) . '  </td></tr>
            <tr><td style="width: 60%;  background: lightgrey;padding: 4px 0px 4px 7px;">&nbsp;Invoice Total</td><td> ' . amountExchange($data["invoice"]["total"]) . '   </td></tr>';
            if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {

                $header .= ' <tr><td style="width: 60%; background: lightgrey;padding:  4px 0px 4px 7px;">&nbsp;' . $lang('Balance Now Due') . '</td><td>' . amountExchange($cust_balance_inv) . ' </td></tr> ';
            }
            $header .= '</table><span style="font-size:12px;"><b>' . $company['cname'] . '.</b></span></div><div style="clear:both"></div>';

            if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
                $header .= '<p style="font-size:12px;margin-top:10px;"> ' . $company['cname'] . ' is the trading name of ' . $company['cname'] . '
                <br>Bank Account Details: &nbsp;&nbsp;<b>Sort Code: ' . $company['sortcode'] . '</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>A/C N0.&nbsp; ' . $company['taxid'] . '</b></p>';
            }

            $header .= ' </footer></body></html>';
        } else {


            $header .= '<footer>';
            //if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE' ){
            $header .= '<div style="border:1px solid #1367A4;width:50%;height:140px; padding: 8px 0px 5px 4px; border-radius: 6px;float:left;margin-top:5px;display:none"><span style="color:rgb(48, 37, 133)"><b>*MAXIMUM CHANGE ACCEPTED=&#163;25</b></span><div style=" font-size: 10px;">All goods remain the property of ' . $company['cname'] . ' until all monies are paid in full<br>Any complaints should be made within 48 hours of purchasing.</div><br><div style="width:100%"><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left">Print Name:</div><div style="border:1px solid black;width:10%;height:18px;float:left;margin-left:10px;;margin-left:-78px;"></div><div style="float:left;margin-left:-158px;margin-top:3px;font-size: 12px;"><b>Cash Tendered</b></div><div style="clear:both"></div><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left;">Signature:</div><div style="font-size: 17px;padding-left:10px;border:1px solid black;width:35%;height:20px;float:left;padding-top:3px;padding-bottom:3px;margin-left:-157px;margin-top:-8px">&#163;</div><div style="clear:both"></div></div><div style="clear:both"></div></div>';
            //}

            // $sub_ts = 0;
            // foreach ($data['products'] as $row) {
            //     $sub_ts += $row['price'] * $row['qty'];
            // }

            $header .= '<div style="float:right;margin-left:320px;width:280px" ><table class="maintable2" style="width:100%; border:1px solid #1367A4;font-size: 14px;">';


            // $inv_total_amt = $data["invoice"]["total"] + $data["invoice"]["tax"];
            // $sub_ts = number_format($sub_ts, 2, '.', '');

            $header .= '<tr><td style="width: 60%;  background: lightgrey;padding:  4px 0px 4px 7px;">&nbsp;' . $lang('Total NET Amount') . '</td>
            <td> ' . amountExchange($data["invoice"]["subtotal"]) . ' </td></tr><tr >
            <td style="width: 60%;  background: lightgrey;padding: 4px 0px 4px 7px;">&nbsp;Total ' . $lang('Tax') . ' Amount</td><td> ' . amountExchange($data["invoice"]["tax"]) . '  </td>
            </tr><tr><td style="width: 60%;  background: lightgrey;padding: 4px 0px 4px 7px;">&nbsp;' . $lang('Invoice Total') . '</td><td> ' . amountExchange($data["invoice"]["total"]) . '   </td></tr>';

            $header .= '</table></div><div style="clear:both"></div>';

            //if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE' ){ 
            $header .= '<p style="font-size:8px;display:none"> ' . $company['cname'] . ' is the trading name of ' . $company['cname'] . ', Company No. 11671004&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Bank Account Details: Sort Code: 04-06-05 A/C No: 21721837</p>';
            //  } 
            $header .= ' </footer></body></html>';
        }

        // $file_name = preg_replace('/[^A-Za-z0-9]+/', '-', 'Invoice__' . $data['invoice']['name'] . '_' . $data['invoice']['tid']);
        $file_name = $this->input->get('id');
        $this->dpdf->loadHtml($header);
        $this->dpdf->render();
        $output =  $this->dpdf->output();
        // $file_location = 'userfiles/invoices/' . $file_name . '.pdf';
        $file_location = 'userfiles/invoices/sale_' . $file_name . '.pdf';
        file_put_contents($file_location, $output);
        $this->dpdf->stream("" . $file_name . ".pdf", array("Attachment" => 0));
    }
    public function printinvoice2($printing = true, $id = -1)
    {
        // Explicitly load language file for PDF generation
        $system_lang = $this->db->query("SELECT lang FROM geopos_system WHERE id=1 LIMIT 1")->row_array();
        $lang_to_load = $system_lang['lang'] ?? 'english';
        $this->lang->load($lang_to_load, $lang_to_load);
        $dynamic_tax_name = getTaxName();
        $that = $this;
        $lang = function ($key) use ($that, $dynamic_tax_name) {
            $text = $that->lang->line($key);
            if ($text === FALSE || empty($text)) return $key;
            return str_replace(array('{tax}', '{Tax}', '{TAX}'), array(strtolower($dynamic_tax_name), ucfirst(strtolower($dynamic_tax_name)), strtoupper($dynamic_tax_name)), $text);
        };
        $company = $this->settings->company_details(1);

        $productCounter = 0;
        $productsPerPage = 18;

        $hide_balance = 0;
        $tid = $id;
        if ($tid == -1) {
            $tid = $this->input->get('id');
            $hide_balance = ($this->input->get('hide_balance') == 1);
        }
        // If called in status-only mode from AJAX (d=1), just set print_status and return JSON
        $d = $this->input->get('d');
        if ($d == '1' || $d == 1) {
            $sql = 'Update geopos_invoices set print_status=1 where id= ' . (int)$tid . ' ';
            $this->db->query($sql);
        }
        $data['id'] = $tid;
        $data['invoice'] = $this->invocies->invoice_details($tid, $this->limited);
        $data['products'] = $this->invocies->invoice_products($tid);
        $data['employee'] = $this->invocies->employee($data['invoice']['eid']);
        if ($data['invoice']['i_class'] == 1) {
            $pref = prefix(7);
        } else {
            $pref = $this->config->item('prefix');
        }
        ob_end_clean();
        ob_start();

        $sql = 'SELECT sum(total)-sum(pamnt) as "cust_balance" FROM geopos_invoices WHERE status="due" and csd="' . $data["invoice"]["csd"] . '" or status="partial" and csd="' . $data["invoice"]["csd"] . '"';
        $query = $this->db->query($sql);
        $response = $query->result_array();
        $cust_balance_inv = $response[0]['cust_balance'];

        // $cust_balance_inv= $response[0]['cust_balance'];
        $sql2 = "SELECT content FROM `geopos_notes` ORDER BY `geopos_notes`.`id`  DESC LIMIT 1";
        $query2 = $this->db->query($sql2);
        $response2 = $query2->result_array();
        $content2 = $response2[0]['content'];

        $sql2 = 'SELECT data FROM `geopos_custom_data` where rid="' . $data["invoice"]["csd"] . '"';
        $query2 = $this->db->query($sql2);
        $response2 = $query2->result_array();
        $custom_fields = $response2[0]['data'];

        // Status update handled above for d=1



        ini_set('memory_limit', '64M');

        $data_tr = "";

        $left_logo = base_url("userfiles/company/" . $company['logo']);
        $data['left_logo'] = $left_logo;
        $right_logo = base_url("assets/images/halal_logo.jpg");
        $data['right_logo'] = $right_logo;
        ob_end_clean();
        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            $header = '<html><head><style>@page { margin: 350px 50px 200px; } header{ position: fixed;  top: -340px;}
            table{width:100%;}.pagenum:after {content: counter(page)}</style> </head><body><header>';
            $header .= '<table style="width:100%;">
            <tr style="border:1px solid #1367A4;">';
            // $header .= '<td style="width:100%;"><img src="' . $left_logo . '" height="165px" width="270px"></td>';
            $header .= '<td style="width:100%;"><img src="' . $left_logo . '" height="165px" width="270px"></td>
            <td style="width:40%;"><img src="' . $right_logo . '" height="60px" width="60px"></td>
            <td style="width:100%;"><br><b>' . $company['cname'] . '</b><br>' . $company['address'] . ' ' . $company['city'] . ' '
                . $company['region'] . ', ' . $company['postbox'] . ', ' . $company['country'] . ' <br> Phone:&nbsp;&nbsp;&nbsp;' . $company['phone'] . ' <br> Mobile:&nbsp;&nbsp;' . $company['mobile'] . ' <br>
                <table style="border: 1px solid #1367A4;
                border-collapse: collapse;margin-left:-45px;margin-top:10px; border-radius: 6px;width:320px;">
                <tr style="">
                            <th  style="border: 1px solid #1367A4; width:80px;
                            border-collapse: collapse;font-size: 13px;text-align: center" >Date
                                </th>
                                <th  style="border: 1px solid #1367A4;
                                border-collapse: collapse;font-size: 13px;text-align: center">Invoice
                                </th>
                                <th  style="border: 1px solid #1367A4;
                                border-collapse: collapse;font-size: 13px;text-align: center">A/C
                                </th>
                                <th  style="border: 1px solid #1367A4;
                                border-collapse: collapse;font-size: 13px; text-align: center">EC NO 
                                </th>
                                <th  style="border: 1px solid #1367A4;
                                border-collapse: collapse;font-size: 13px; text-align: center">VAT Reg
                                </th>
                </tr>
                <tr >
                    <td style="width:30%;font-size: 13px;border: 1px solid #1367A4;border-collapse: collapse;text-align: center">
                    ' . date("d-m-Y", strtotime($data["invoice"]["invoicedate"])) . '
                    </td>
                    <td style="font-size: 13px;border: 1px solid #1367A4;border-collapse: collapse;text-align: center">' . $data['invoice']['tid'] . '</td>
                    <td style="font-size: 13px;border: 1px solid #1367A4;border-collapse: collapse;text-align: center">' . $data["invoice"]["name"] . '</td>
                    <td style="font-size: 13px;border: 1px solid #1367A4;border-collapse: collapse;text-align: center">882 /2004</td>
                    <td style="font-size: 13px;border: 1px solid #1367A4;border-collapse: collapse;text-align: center">299367533</td>
                </tr>
                </table>
            </td>
            </tr></table>';
        } else {
            $header = '<html><head><style>@page { margin: 350px 50px 200px; }header{ position: fixed;  top: -320px;}table{width:100%;}.pagenum:after {content: counter(page)}</style> </head><body><header><span style="float:right;"  class="pagenum">Page:</span>';
            $header .= ' <div style="width:100%; "><div style="width:23%; float:left; text-align:center;">  <br>
                </div></div><div><p style="text-align:center;height:84px;">Note: This is not a valid Invoice  </p>
                    Date: ' . date("d-m-Y", strtotime($data["invoice"]["invoicedate"])) . '
                </div><div style="clear:both"></div>';
            // $header .= ' <div style="width:100%; "><div style="width:23%; float:left; text-align:center;">  <br> Invoice#: ' . $data['invoice']['tid'] . '
            //     </div></div><div><p style="text-align:center;height:84px;">Note: This is not a valid Invoice  </p>
            //         Date: ' . date("d-m-Y", strtotime($data["invoice"]["invoicedate"])) . '
            //     </div><div style="clear:both"></div>';
        }

        $header .= '<div style="border:1px solid #1367A4;width:40%;height:84px; padding: 8px 10px 8px; border-radius: 6px;float:left;margin-top:5px;">';
        if ($data['invoice']['company']) {
            $header .= $data['invoice']['company'] . '<br>';
        }

        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            $header .= $data['invoice']['cust_address'] . ' ' . $data['invoice']['cust_city'] . '<br>' . $data['invoice']['cust_postcode'];
            if ($data['invoice']['pamt_terms']) {
                $header .= '<br><b> <span style="background-color:yellow">TERMS:</span></b> ' . $custom_fields . '';
            }
            // if ($data['invoice']['country']) {
            //      $header.= '<br>' . $data['invoice']['country'];
            // }
        } else {
            $header .= $data['invoice']['cust_address'] . ' ' . $data['invoice']['cust_city'] . '<br>' . $data['invoice']['cust_postcode'];
            if ($data['invoice']['pamt_terms']) {
                $header .= '<br><b> <span style="background-color:yellow">TERMS:</span> </b>' . $custom_fields . '';
            }
        }
        // if ($data['invoice']['postbox']){
        //     $header.= $data['invoice']['cust_address'];
        // } 
        // if ($data['invoice']['email']){
        //     $header.= '<br> ' . $this->lang->line('Email') . ': ' . $data['invoice']['email'];
        // }

        $header .= '</div><div style="float:right;margin-left:-150px;margin-top:-30px;width:50%;" ><div style=" font-size: 25px;">';



        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            $header .= '<br> ';
            $header .= '</div><table style="height:5%;  font-size: 14px; border:1px solid #1367A4;border-radius: 6px;"> 
        <tr ><td ><b>' . $content2 . '</b></td>
        </tr>';
            $header .= '</table>';
        } else {
            $header .= '<span style="font-size: 32px; transform: translate(-78px, -38px)" >Day Pass</span>';
            if ($data['invoice']['driver_name'] != '' && $data['invoice']['vehicle_no'] != '') {
                $header .= '</div><table style="height:5%;  font-size: 14px; border:1px solid #1367A4;border-radius: 6px;"> 
            
            <tr ><td ><b>Driver Name: </b></td></tr>
            <tr ><td >' . $data['invoice']['driver_name'] . ' ' . $data['invoice']['vehicle_no'] . '</td>
            </tr>';
                $header .= '</table>';
            }
        }



        $header .= '</div></div></header>';

        if ($data['invoice']['inv_type'] == 'INVOICE') {
            $header .= '
            
                <table style="width:100%; font-size: 14px;  margin-top:-43px;"><tr><td style="width: 100%;">&nbsp;<b> 
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                For Bank Transfer:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $company['cname'] . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;S/C&nbsp;&nbsp;' . $company['sortcode'] . '           &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;AC&nbsp;&nbsp;' . $company['taxid'] . '  
                </b></td></tr></table>
                
                ';
        }
        $header .= '<div style="clear:both"></div> <main><div style="width:100%; margin-top:-17px; "><table width="100%" class="myProducts" style="border:1px solid #1367A4;border-radius: 6px;"><thead><tr style="font-size: 14px; background:#ccc; padding:4px;"><th style="width: 12%; text-align:left; ">' . $this->lang->line('Quantity') . '</th><th style="border-left: 1px solid #1367A4;width: 45%; text-align:left;">' . $this->lang->line('Details') . '</th><th style="width: 13%; text-align:left; border-left: 1px solid #1367A4;">' . $this->lang->line('Unit Price') . '</th><th style="width: 20%; text-align:left; border-left: 1px solid #1367A4;">' . $this->lang->line('Net Amount') . '</th><th style="width: 10%;  text-align:left;border-left: 1px solid #1367A4;">' . getTaxName() . '</th></tr></thead>';
        $header .= '<tbody style="font-size:15px;">';

        $ns = 0;
        foreach ($data['products'] as $row) {
            if ($row['qty'] > 0) {
                $productCounter++;
                $ns++;

                if ($productCounter % $productsPerPage === 0) {
                    if ($productCounter > 0) {
                        // Close the previous table if it's not the first page
                        $header .= '</tbody>';
                        $header .= '</table>';
                    }

                    // Start a new page
                    $header .= '<div style="page-break-after: always;"></div>';
                    $header .= '<table style="border:1px solid #1367A4;border-radius: 6px;">';
                    $header .= '<thead>';
                    $header .= '<tr style="font-size: 14px; background: #ccc; padding: 4px;">';
                    $header .= '<th style="width: 12%; text-align: left;">Quantity</th>';
                    $header .= '<th style="border-left: 1px solid #1367A4; width: 45%; text-align: left;">Details</th>';
                    $header .= '<th style="width: 13%; text-align: left; border-left: 1px solid #1367A4;">Unit Price</th>';
                    $header .= '<th style="width: 20%; text-align: left; border-left: 1px solid #1367A4;">Net Amount</th>';
                    $header .= '<th style="width: 10%; text-align: left; border-left: 1px solid #1367A4;">VAT</th>';
                    $header .= '</tr>';
                    $header .= '</thead>';
                    $header .= '<tbody>';
                }
                // Removed calculation - using database value instead
                // Using rev_amountExchange_s() directly in output - no need for variables
                // $vat = amountExchange($row['totaltax']);
                // $netamount = amountExchange($row['subtotal'] - $row['totaltax']);
                if (empty($row['product_des']) || $row['product_des'] == "") {
                } else {
                    if ($row['serial'] == '1') {
                        $header .= '<tr style="background:#FBF36D; style="font-size:18px;"">';
                    } else {
                        $header .= '<tr ' . (($ns % 2 == 0) ? 'style=" background: #f1f4fb;"' : '') . '>';
                    }
                    $header .= '<td style=" border-bottom:1px solid white;">' . (int)$row["qty"] .  '</td style=" border-bottom:1px solid white;">
                    <td style=" border-bottom:1px solid white;">' . $row["product_des"] . '</td>
                    <td style=" border-bottom:1px solid white;">' . rev_amountExchange_s($row['price']) . '</td>
                     <td style=" border-bottom:1px solid white;">' . rev_amountExchange_s($row['subtotal']) . '</td>
                     <td style=" border-bottom:1px solid white;">' . rev_amountExchange_s($row['totaltax']) . ' </td></tr>';
                }
            }
        }
        $header .= '</tbody></table></div></main>';
        // Use database values instead of calculating totals

        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {

            $header .= '<footer style="position:absolute;bottom:-195px;">';
            if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
                $header .= '<div style="border:1px solid #1367A4;width:59%;height:170px; padding: 8px 0px 5px 4px; border-radius: 6px;float:left;"><span style="color:rgb(48, 37, 133)"><b>*MAXIMUM CHANGE ACCEPTED=&#163;60</b></span><div style=" font-size: 10px;">
            *All claims must be notified at the time of delivery,<br>
            any claims made after delivery will not be considered.<br>
            Goods will remain the property of ' . $company['cname'] . ' <br>until paid in full by the customer.<br>
            Frozen and chilled products cannot be returned once delivered.<br>
            </div><br><div style="width:100%"><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left">Customer:</div><div style="border:1px solid black;width:8%;height:18px;float:left;margin-top:10px;margin-left:-178px;"></div><div style="border:1px solid black;width:8%;height:18px;float:left;margin-top:10px;margin-left:-58px;"></div><div style="border:1px solid black;width:8%;height:18px;float:left;margin-top:10px;margin-left:-118px;"></div><div style="float:left;margin-left:-178px;margin-top:-5px;font-size: 12px;"><b>Cash &nbsp;&nbsp; Card Payment &nbsp;&nbsp;BACS</b></div><div style="clear:both"></div><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left;">Driver: <b>' . $data['invoice']['driver_name'] . '  ' . $data['invoice']['vehicle_no'] . '</b></div><div style="font-size: 17px;padding-left:10px;border:1px solid black;width:35%;height:20px;float:left;padding-top:3px;padding-bottom:3px;margin-left:-187px;margin-top:3px">&#163;</div><div style="clear:both"></div></div><div style="clear:both"></div></div>';
            }

            $header .= '<div style="float:right;margin-left:320px;width:280px" ><table class="maintable2" style="width:100%;border-radius: 6px; border:1px solid #1367A4;font-size: 14px;">';

            if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
                if (!$hide_balance) {
                    //    $header.='<tr ><td style="width: 50%;  background: #f1f4fb;padding: 4px 0px 4px 7px;">&nbsp;Previous Balance</td><td>' ;
                    //     $invbalance = 0 ;
                    //    if($cust_balance_inv >= $data['invoice']['total']){ 
                    //        $invbalance =  $cust_balance_inv -$data['invoice']['total']; 
                    //        $header.=  substr( amountExchange($invbalance, $data['invoice']['multi'], $data['invoice']['loc']),3,100);  
                    //        }else{ 
                    //             $header.=  substr(amountExchange($cust_balance_inv, $data['invoice']['multi'], $data['invoice']['loc']),3,100);    } 
                    //             $header.='</td></tr>'; 
                }
            }

            $header .= '<tr><td style="width: 60%;  background: #f1f4fb;padding:  4px 0px 4px 7px;">&nbsp;Total NET Amount</td><td> ' . amountExchange($data["invoice"]["subtotal"]) . ' </td></tr>
            <tr ><td style="width: 60%;  background: #f1f4fb;padding: 4px 0px 4px 7px;">&nbsp;Total ' . getTaxName() . ' Amount</td><td> ' . amountExchange($data["invoice"]["tax"]) . '  </td></tr>
            <tr><td style="width: 60%;  background: #f1f4fb;padding: 4px 0px 4px 7px;">&nbsp;Invoice Total</td><td> ' . amountExchange($data["invoice"]["total"]) . '   </td></tr>';


            if ($data['invoice']['inv_type'] == 'INVOICE' || $data['invoice']['inv_type'] == 'DAYPASS' && $data['invoice']['company'] != 'COUNTER SALE') {
                if (!$hide_balance) {
                    $header .= '<tr ><td style="width: 50%;  background: #f1f4fb;padding: 4px 0px 4px 7px;">&nbsp;Previous Balance</td><td>';
                    $invbalance = 0;
                    if ($cust_balance_inv >= $data['invoice']['total']) {
                        $invbalance =  $cust_balance_inv - $data['invoice']['total'];
                        $header .=  amountExchange($invbalance, $data['invoice']['multi'], $data['invoice']['loc']);
                        // $header .=  substr(amountExchange($invbalance, $data['invoice']['multi'], $data['invoice']['loc']), 3, 100);
                    } else {
                        $header .=  amountExchange($cust_balance_inv, $data['invoice']['multi'], $data['invoice']['loc']);
                        // $header .=  substr(amountExchange($cust_balance_inv, $data['invoice']['multi'], $data['invoice']['loc']), 3, 100);
                    }
                    $header .= '</td></tr>';
                    $header .= ' <tr><td style="width: 60%; background: #f1f4fb;padding:  4px 0px 4px 7px;">&nbsp;Balance Now Due</td><td>' . amountExchange($cust_balance_inv) . ' </td></tr> ';
                }
            }
            $header .= '</table>
            <span href="mailto:' . $company['email'] . '"><img src="' . base_url("assets/images/mail.jpg") . '" height="32px" width="38px" style="margin-top:10px;">  </span>' . $company['email'] . '<br>
            <!--<span href="https://www.cloudbillingmanager.com/"><img src="' . base_url("assets/images/web.jpg") . '" height="32px" width="38px" style="margin-top:10px;"> </span>www.cloudbillingmanager.com -->
            </div><div style="clear:both">
            <span style="text-align: center;margin-left:70px;">Thank you for your business!</span>
            </div>';

            if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            }
            $header .= ' </footer></body></html>';
        } else {
            $header .= '<footer style="position:absolute;bottom:-195px;">';
            //if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE' ){
            $header .= '<div style="border:1px solid #1367A4;width:50%;height:140px; padding: 8px 0px 5px 4px; border-radius: 6px;float:left;margin-top:5px;display:none"><span style="color:rgb(48, 37, 133)"><b>*MAXIMUM CHANGE ACCEPTED=&#163;25</b></span><div style=" font-size: 10px;">All goods remain the property of ' . $company['cname'] . ' 
            until all monies are paid in full<br>Any complaints should be made within 48 hours of purchasing.</div><br><div style="width:100%"><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left">Print Name:</div><div style="border:1px solid black;width:10%;height:18px;float:left;margin-left:10px;;margin-left:-78px;"></div><div style="float:left;margin-left:-158px;margin-top:3px;font-size: 12px;"><b>Cash Tendered</b></div><div style="clear:both"></div><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left;">Signature:</div><div style="font-size: 17px;padding-left:10px;border:1px solid black;width:35%;height:20px;float:left;padding-top:3px;padding-bottom:3px;margin-left:-157px;margin-top:-8px">&#163;</div><div style="clear:both"></div></div><div style="clear:both"></div></div>';
            //}

            // $sub_ts = 0;
            // foreach ($data['products'] as $row) {
            //     $sub_ts += $row['price'] * $row['qty'];
            // }

            $header .= '<div style="float:right;margin-left:320px;width:280px" ><table class="maintable2" style="width:100%; border:1px solid #1367A4;font-size: 14px;">';


            //  $inv_total_amt=$data["invoice"]["total"]+$data["invoice"]["tax"];

            // $inv_total_amt = $data["invoice"]["total"];
            // $sub_ts = number_format($sub_ts, 2, '.', '');

            $header .= '<tr><td style="width: 60%;  background: #f1f4fb;padding:  4px 0px 4px 7px;">&nbsp;Total NET Amount</td>
            <td> ' . amountExchange($data["invoice"]["subtotal"]) . ' </td></tr><tr ><td style="width: 60%;  background: #f1f4fb;padding: 4px 0px 4px 7px;">&nbsp;Total ' . $this->lang->line('Tax') . ' Amount</td>
            <td> ' . amountExchange($data["invoice"]["tax"]) . '  </td>
            </tr><tr><td style="width: 60%;  background: #f1f4fb;padding: 4px 0px 4px 7px;">&nbsp;Invoice Total</td><td> ' . amountExchange($data["invoice"]["total"]) . '   </td></tr>';
            // if (!$hide_balance) {
            //     $header .= '<tr ><td style="width: 50%;  background: #f1f4fb;padding: 4px 0px 4px 7px;">&nbsp;Previous Balance</td><td>';
            //     $invbalance = 0;
            //     if ($cust_balance_inv >= $data['invoice']['total']) {
            //         $invbalance =  $cust_balance_inv - $data['invoice']['total'];
            //         $header .=  substr(amountExchange($invbalance, $data['invoice']['multi'], $data['invoice']['loc']), 3, 100);
            //     } else {
            //         $header .=  substr(amountExchange($cust_balance_inv, $data['invoice']['multi'], $data['invoice']['loc']), 3, 100);
            //     }
            //     $header .= '</td></tr>';
            //     $header .= ' <tr><td style="width: 60%; background: #f1f4fb;padding:  4px 0px 4px 7px;">&nbsp;Balance Now Due</td><td>' . $cust_balance_inv . ' </td></tr> ';
            // }

            $header .= '</table></div><div style="clear:both"></div>';

            //if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE' ){ 
            $header .= '<p style="font-size:8px;display:none"> ' . $company['cname'] . ' is the trading name of ' . $company['cname'] . ', Company No. 11671004. &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Bank Account Details: Sort Code: ' . $company['sortcode'] . ' A/C No: ' . $company['taxid'] . '</p>';
            //  } 
            $header .= ' </footer></body></html>';
        }

        // $file_name = preg_replace('/[^A-Za-z0-9]+/', '-', 'Invoice__' . $data['invoice']['name'] . '_' . $data['invoice']['tid']);
        // $tid = $id;
        // if($tid == -1) {
        //     $tid = $this->input->get('id');
        // }
        $file_name = $tid;
        $this->dpdf->loadHtml($header);
        $this->dpdf->render();
        $output =  $this->dpdf->output();
        $file_location = 'userfiles/invoices/sale_' . $file_name . '.pdf';
        file_put_contents($file_location, $output);
        if ($printing) {
            $this->dpdf->stream("" . $file_name . ".pdf", array("Attachment" => 0));
        }
    }
    public function printinvoice3()
    {
        // Explicitly load language file for PDF generation
        $system_lang = $this->db->query("SELECT lang FROM geopos_system WHERE id=1 LIMIT 1")->row_array();
        $lang_to_load = $system_lang['lang'] ?? 'english';
        $this->lang->load($lang_to_load, $lang_to_load);
        $dynamic_tax_name = getTaxName();
        $that = $this;
        $lang = function ($key) use ($that, $dynamic_tax_name) {
            $text = $that->lang->line($key);
            if ($text === FALSE || empty($text)) return $key;
            return str_replace(array('{tax}', '{Tax}', '{TAX}'), array(strtolower($dynamic_tax_name), ucfirst(strtolower($dynamic_tax_name)), strtoupper($dynamic_tax_name)), $text);
        };
        $company = $this->settings->company_details(1);

        $tid = $this->input->get('id');
        // If called in status-only mode from AJAX (d=1), just set print_status and return JSON
        $d = $this->input->get('d');
        if ($d == '1' || $d == 1) {
            $sql = 'Update geopos_invoices set print_status=1 where id= ' . (int)$tid . ' ';
            $this->db->query($sql);
        }
        $data['id'] = $tid;
        $data['invoice'] = $this->invocies->invoice_details($tid, $this->limited);
        $data['products'] = $this->invocies->invoice_products($tid);
        $sql2 = 'SELECT data FROM `geopos_custom_data` where rid="' . $data["invoice"]["csd"] . '"';
        $query2 = $this->db->query($sql2);
        $response2 = $query2->result_array();
        $custom_fields = $response2[0]['data'];
        $data['employee'] = $this->invocies->employee($data['invoice']['eid']);
        if ($data['invoice']['i_class'] == 1) {
            $pref = prefix(7);
        } else {
            $pref = $this->config->item('prefix');
        }
        ob_end_clean();
        ob_start();

        $sql = 'SELECT sum(total)-sum(pamnt) as "cust_balance" FROM geopos_invoices WHERE status="due" and csd="' . $data["invoice"]["csd"] . '" or status="partial" and csd="' . $data["invoice"]["csd"] . '"';
        $query = $this->db->query($sql);
        $response = $query->result_array();
        $cust_balance_inv = $response[0]['cust_balance'];

        // $cust_balance_inv= $response[0]['cust_balance'];
        $sql2 = "SELECT content FROM `geopos_notes` ORDER BY `geopos_notes`.`id`  DESC LIMIT 1";
        $query2 = $this->db->query($sql2);
        $response2 = $query2->result_array();
        $content2 = $response2[0]['content'];

        // Status update handled above for d=1



        ini_set('memory_limit', '64M');

        $data_tr = "";

        $left_logo = base_url("userfiles/company/" . $company['logo']);
        $data['left_logo'] = $left_logo;
        $right_logo = base_url("assets/images/halal_logo.jpg");
        $data['right_logo'] = $right_logo;
        ob_end_clean();
        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            $header = '<html><head><style> * {font-family: sans-serif !important;} @page { margin: 350px 50px 200px;}header{ position: fixed;  top: -320px;}table{width:100%;}.pagenum:after {content: counter(page)}</style> </head><body><header>';
            $header .= ' <div style="width:100%;"><div style="width:25%; float:left; text-align:center; margin-bottom:4px">  <img src="' . $left_logo . '" height="140px" width="155px"> </div><div style="width:75%; margin-left:20px; float:left; text-align:center; color:#f8faff; background-color: #25468d; border-bottom-left-radius:10em;  padding: 18px 0;"> <div style="font-size: 20px;"><b>' . $company['cname'] . '</b></div><div style="font-size: 14px;"><div>' . $company['address'] . '</div><div>' . $company['region'] . ', ' . $company['city'] . ' ' . $company['postbox'] . ' ' . $company['country'] . '</div><div>Phone: ' . $company['phone'] . '&nbsp;&nbsp;&nbsp;&nbsp;Mobile:&nbsp;&nbsp;' . $company['mobile'] . '</div><div>Email: ' . $company['email'] . '</div></div> </div> 
      </div><div style="clear:both;"></div>';
        } else {
            $header = '<html><head><style>@page { margin: 350px 50px 200px; }header{ position: fixed;  top: -320px;}table{width:100%;}.pagenum:after {content: counter(page)}</style> </head><body><header><span style="float:right;"  class="pagenum">Page:</span>';
            $header .= ' <div style="width:100%; "><div style="width:23%; float:left; text-align:center;">
                        </div></div><div><p style="text-align:center;height:100px;">Note: This is not a valid Invoice  </p>';
            $header .= '&nbsp;Invoice Date:&nbsp;&nbsp;&nbsp;' . date("d-m-Y", strtotime($data["invoice"]["invoicedate"])) . '</div><div style="clear:both"></div>';
        }

        $header .= '<div style="width:40%;height:100px; border-radius: 0px;float:left;margin-top:55px;">';
        $header .= '<b>&nbsp;Customer\'s Info</b>';
        $header .= '<div style="border:1px solid #25468D; padding: 8px 10px 8px;">';
        if ($data['invoice']['company']) {
            $header .= $data['invoice']['company'] . '<br>';
        }

        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            $header .= $data['invoice']['cust_address'] . '<br/>' . $data['invoice']['cust_city'] . '<br/>' . $data['invoice']['cust_postcode'] . "";
            if ($data['invoice']['pamt_terms']) {
                $header .= '<br><b><span style="background-color:yellow">TERMS:</span></b> ' . $custom_fields;
            }
            // if ($data['invoice']['country']) {
            //      $header.= '<br>' . $data['invoice']['country'];
            // }
        } else {
            $header .= ' <br> ' . $data['invoice']['cust_address'] . '<br/>' . $data['invoice']['cust_city'] . '<br/>' . $data['invoice']['cust_postcode'] . "";
        }
        // if ($data['invoice']['postbox']){
        //     $header.=' <br> ' . $data['invoice']['cust_address'];
        // } 
        // if ($data['invoice']['email']){
        //     $header.= '<br> ' . $this->lang->line('Email') . ': ' . $data['invoice']['email'];
        // }

        $header .= '</div></div><div style="float:right;margin-left:-150px;margin-top:0px;width:50%" ><div style=" font-size: 18px">';



        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            $header .= '<b>&nbsp;Invoice</b>';
        } else {
            $header .= '&nbsp;';
        }
        $header .= '</div><table style="width:100%; font-size: 14px; border:1px solid #25468D;"><tr><td style="width: 50%; border-bottom:1px solid #25468D;background: #f1f4fb;padding: 4px;">&nbsp;Invoice No.</td>
       <td  style="border-bottom:1px solid #25468D;">';

        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            $header .= $data['invoice']['tid'];
        } else {
            $header .= ' Day Pass ';
        }

        $header .= ' </td></tr><tr><td style="width: 50%; border-bottom:1px solid #25468D;background: #f1f4fb;padding:  4px;">&nbsp;Invoice Date</td><td style="border-bottom:1px solid #25468D;"> ' . date("d-m-Y", strtotime($data["invoice"]["invoicedate"])) . ' </td></tr>';


        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            $header .= '  <tr><td style="width: 50%; background: #f1f4fb;padding: 4px;">&nbsp;Account No.</td><td> ' . $data["invoice"]["name"] . '</td></tr>';
        } else {
        }
        $header .= '</table><div>';




        // if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
        //     $header .= ' &nbsp;VAT Reg No: 375 6160 81 ';
        // }
        $header .= '</div></div></header>';


        $header .= '<div style="clear:both"></div><main><div style="width:100%;  "><table width="100%" class="myProducts" style="border:1px solid #25468D;"><thead><tr style="font-size: 14px; background:#f1f4fb; padding:4px;"><th style="width: 12%; text-align:left; ">' . $lang('Quantity') . '</th><th style="border-left: 1px solid #25468D;width: 45%; text-align:left;">' . $lang('Details') . '</th><th style="width: 13%; text-align:left; border-left: 1px solid #25468D;">' . $lang('Unit_Rate') . '</th><th style="width: 20%; text-align:left; border-left: 1px solid #25468D;">' . $lang('Net Amount') . '</th><th style="width: 10%;  text-align:left;border-left: 1px solid #25468D;">' . $lang('Tax') . '</th></tr></thead>';
        $header .= '<tbody style="font-size:15px;">';

        $ns = 0;
        foreach ($data['products'] as $row) {
            $ns++;
            // Removed calculation - using database value instead
            // Using rev_amountExchange_s() directly in output - no need for variables
            $netamount = amountExchange($row['subtotal']);
            // $vat = amountExchange($row['totaltax'], $invoice['multi'], $invoice['loc']);
            // $netamount = amountExchange($row['subtotal'] - $row['totaltax'], $invoice['multi'], $invoice['loc']);
            if (empty($row['product_des']) || $row['product_des'] == "") {
            } else {
                if ($row['serial'] == '1') {
                    $header .= '<tr style="background:#FBF36D; style="font-size:18px;"">';
                } else {
                    $header .= '<tr style=" border-bottom:1px solid white;">';
                }
                $header .= '<td style=" border-bottom:1px solid white;">' . (int)$row["qty"] . '</td style=" border-bottom:1px solid white;">
                <td style=" border-bottom:1px solid white;">' . $row["product_des"] . '</td>
                <td style=" border-bottom:1px solid white;">' . rev_amountExchange_s($row['price']) . '</td> 
                <td style=" border-bottom:1px solid white;">' . rev_amountExchange_s($row['subtotal']) . '</td>
                <td style=" border-bottom:1px solid white;">' . rev_amountExchange_s($row['totaltax']) . ' </td></tr>';
            }
        }
        $header .= '</tbody></table></div></main>';

        // Use database values instead of calculating totals
        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {

            $header .= '<footer style="position:absolute;bottom:-195px; border-bottom:20px solid #F36E38;">';
            if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
                $header .= '<div style="border:1px solid #25468D;width:50%;height:140px; padding: 8px; border-radius: 0px; float:left;margin-top:5px"><span style="color:#25468d"><b>*MAXIMUM CHANGE ACCEPTED=&#163;25</b></span><div style=" font-size: 10px;">All goods remain the property of ' . $company['cname'] . ' until all monies are paid in full<br>Any complaints should be made within 48 hours of purchasing.</div><br><div style="width:100%"><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;  font-size: 10px; float:left">Print Name:</div><div style="border:1px solid black;width:10%;height:18px;float:left;margin-left:10px;;margin-left:-78px;"></div><div style="float:left;margin-left:-158px;margin-top:3px;font-size: 10px;"><b>Cash Tendered</b></div><div style="clear:both"></div><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;  font-size: 10px; float:left;">Signature:</div><div style="padding-left:10px;border:1px solid black;width:35%;height:20px;float:left;padding-top:3px;padding-bottom:3px;margin-left:-157px;  font-size: 10px; margin-top:-8px">&#163;</div><div style="clear:both"></div></div><div style="clear:both"></div></div>';
            }

            // $sub_ts = 0;
            // foreach ($data['products'] as $row) {
            //     $sub_ts += $row['price'] * $row['qty'];
            // }

            $header .= '<div style="float:right;margin-left:320px;width:280px" ><table class="maintable2" style="width:100%; border:1px solid #25468D;font-size: 14px;">';

            if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
                $header .= '<tr ><td style="width: 50%;  background: #f1f4fb;padding: 3px 8px; font-size:12px">&nbsp;Previous Balance</td><td style="font-size: 12px">';
                $invbalance = 0;
                if ($cust_balance_inv >= $data['invoice']['total']) {
                    $invbalance =  $cust_balance_inv - $data['invoice']['total'];
                    // $header .=  substr(amountExchange($invbalance, $data['invoice']['multi'], $data['invoice']['loc']), 3, 100);
                    $header .=  amountExchange($invbalance, $data['invoice']['multi'], $data['invoice']['loc']);
                } else {
                    $header .=  amountExchange($cust_balance_inv, $data['invoice']['multi'], $data['invoice']['loc']);
                    // $header .=  substr(amountExchange($cust_balance_inv, $data['invoice']['multi'], $data['invoice']['loc']), 3, 100);
                }
                $header .= '</td></tr>';
            }

            $header .= '<tr><td style="width: 60%;  background: #f1f4fb;padding:  3px 8px; font-size:12px">&nbsp;Total NET Amount</td><td style="font-size: 12px"> ' . amountExchange($data["invoice"]["subtotal"]) . ' </td></tr>
            <tr ><td style="width: 60%;  background: #f1f4fb;padding: 3px 8px; font-size:12px">&nbsp;Total VAT Amount</td><td style="font-size: 12px"> ' . amountExchange($data["invoice"]["tax"]) . '  </td></tr>
            <tr><td style="width: 60%;  background: #f1f4fb;padding: 3px 8px; font-size:12px">&nbsp;Invoice Total</td><td style="font-size: 12px"> ' . amountExchange($data["invoice"]["total"]) . '   </td></tr>';
            if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {

                $header .= ' <tr><td style="width: 60%; background: #f1f4fb;padding:  3px 8px; font-size:12px">&nbsp;Balance Now Due</td><td style="font-size: 12px">' . amountExchange($cust_balance_inv) . ' </td></tr> ';
                $header .= ' <tr><td style="width: 60%; background: #f1f4fb;padding:  3px 8px; font-size:12px">&nbsp;Driver Name</td><td style="font-size: 12px">' . $data['invoice']['driver_name'] . ' </td></tr> ';
                $header .= ' <tr><td style="width: 60%; background: #f1f4fb;padding:  3px 8px; font-size:12px">&nbsp;Vehicle No.</td><td style="font-size: 12px">' . $data['invoice']['vehicle_no'] . ' </td></tr> ';
            }
            $header .= '</table></div><div style="clear:both"></div>';

            if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
                $header .= '<p style="font-size:12px">Powered By <a style="text-decoration: none; font-weight: 600; color: #25468d" href="https://cloudbillingmanager.com/" target="_blank">Cloud Billing Manger</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Bank Account Details: &nbsp;&nbsp;<b> ' . $company['taxid'] . '</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Sort Code: &nbsp; <b>' . $company['sortcode'] . '</b></p>
    
        ';
            }
            $header .= ' </footer></body></html>';
        } else {


            $header .= '<footer style="position:absolute;bottom:-195px">';
            //if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE' ){
            $header .= '<div style="border:1px solid #25468D;width:50%;height:140px; padding: 8px 0px 5px 4px; border-radius: 6px;float:left;margin-top:5px;display:none"><span style="color:rgb(48, 37, 133)"><b>*MAXIMUM CHANGE ACCEPTED=&#163;25</b></span><div style=" font-size: 10px;">All goods remain the property of ' . $company['cname'] . ' until all monies are paid in full<br>Any complaints should be made within 48 hours of purchasing.</div><br><div style="width:100%"><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left">Print Name:</div><div style="border:1px solid black;width:10%;height:18px;float:left;margin-left:10px;;margin-left:-78px;"></div><div style="float:left;margin-left:-158px;margin-top:3px;font-size: 12px;"><b>Cash Tendered</b></div><div style="clear:both"></div><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left;">Signature:</div><div style="font-size: 17px;padding-left:10px;border:1px solid black;width:35%;height:20px;float:left;padding-top:3px;padding-bottom:3px;margin-left:-157px;margin-top:-8px">&#163;</div><div style="clear:both"></div></div><div style="clear:both"></div></div>';
            //}

            // $sub_ts = 0;
            // foreach ($data['products'] as $row) {
            //     $sub_ts += $row['price'] * $row['qty'];
            // }

            $header .= '<div style="float:right;margin-left:320px;width:280px" ><table class="maintable2" style="width:100%; border:1px solid #25468D;font-size: 14px;">';


            // $inv_total_amt = $data["invoice"]["total"] + $data["invoice"]["tax"];
            // $sub_ts = number_format($sub_ts, 2, '.', '');

            $header .= '<tr><td style="width: 60%;  background: #f1f4fb;padding:  4px 0px 4px 7px;">&nbsp;Total NET Amount</td>
            <td> ' . amountExchange($data["invoice"]["subtotal"]) . ' </td></tr><tr ><td style="width: 60%;  background: #f1f4fb;padding: 4px 0px 4px 7px;">&nbsp;Total ' . $this->lang->line('Tax') . ' Amount</td>
            <td> ' . amountExchange($data["invoice"]["tax"]) . '  </td></tr><tr><td style="width: 60%;  background: #f1f4fb;padding: 4px 0px 4px 7px;">
            &nbsp;Invoice Total</td><td> ' . amountExchange($data["invoice"]["total"]) . '   </td></tr>';

            $header .= '</table></div><div style="clear:both"></div>';

            //if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE' ){ 
            $header .= '<p style="font-size:8px;display:none">Powered By <a href="https://cloudbillingmanager.com/" target="_blank">Cloud Billing Manger</a>, Company No. 11671004&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Bank Account Details: Sort Code: 04-06-05 A/C No: 21721837</p>';
            //  } 
            $header .= ' </footer></body></html>';
        }

        // $file_name = preg_replace('/[^A-Za-z0-9]+/', '-', 'Invoice__' . $data['invoice']['name'] . '_' . $data['invoice']['tid']);
        $file_name = $this->input->get('id');
        $this->dpdf->loadHtml($header);
        $this->dpdf->render();
        $output =  $this->dpdf->output();
        $file_location = 'userfiles/invoices/' . $file_name . '.pdf';
        file_put_contents($file_location, $output);
        $this->dpdf->stream("" . $file_name . ".pdf", array("Attachment" => 0));
    }
    public function printinvoice4()
    {
        // Explicitly load language file for PDF generation
        $system_lang = $this->db->query("SELECT lang FROM geopos_system WHERE id=1 LIMIT 1")->row_array();
        $lang_to_load = $system_lang['lang'] ?? 'english';
        $this->lang->load($lang_to_load, $lang_to_load);
        $dynamic_tax_name = getTaxName();
        $that = $this;
        $lang = function ($key) use ($that, $dynamic_tax_name) {
            $text = $that->lang->line($key);
            if ($text === FALSE || empty($text)) return $key;
            return str_replace(array('{tax}', '{Tax}', '{TAX}'), array(strtolower($dynamic_tax_name), ucfirst(strtolower($dynamic_tax_name)), strtoupper($dynamic_tax_name)), $text);
        };
        $company = $this->settings->company_details(1);

        $tid = $this->input->get('id');
        // If called in status-only mode from AJAX (d=1), just set print_status and return JSON
        $d = $this->input->get('d');
        if ($d == '1' || $d == 1) {
            $sql = 'Update geopos_invoices set print_status=1 where id= ' . (int)$tid . ' ';
            $this->db->query($sql);
        }
        $data['id'] = $tid;
        $data['invoice'] = $this->invocies->invoice_details($tid, $this->limited);
        $data['products'] = $this->invocies->invoice_products($tid);
        $sql2 = 'SELECT data FROM `geopos_custom_data` where rid="' . $data["invoice"]["csd"] . '"';
        $query2 = $this->db->query($sql2);
        $response2 = $query2->result_array();
        $custom_fields = $response2[0]['data'];
        $data['employee'] = $this->invocies->employee($data['invoice']['eid']);
        if ($data['invoice']['i_class'] == 1) {
            $pref = prefix(7);
        } else {
            $pref = $this->config->item('prefix');
        }
        ob_end_clean();
        ob_start();

        $sql = 'SELECT sum(total)-sum(pamnt) as "cust_balance" FROM geopos_invoices WHERE status="due" and csd="' . $data["invoice"]["csd"] . '" or status="partial" and csd="' . $data["invoice"]["csd"] . '"';
        $query = $this->db->query($sql);
        $response = $query->result_array();
        $cust_balance_inv = $response[0]['cust_balance'];

        // $cust_balance_inv= $response[0]['cust_balance'];
        $sql2 = "SELECT content FROM `geopos_notes` ORDER BY `geopos_notes`.`id`  DESC LIMIT 1";
        $query2 = $this->db->query($sql2);
        $response2 = $query2->result_array();
        $content2 = $response2[0]['content'];

        // Status update handled above for d=1



        ini_set('memory_limit', '64M');

        $data_tr = "";

        $left_logo = base_url("userfiles/company/" . $company['logo']);
        $data['left_logo'] = $left_logo;
        $right_logo = base_url("assets/images/halal_logo.jpg");
        $data['right_logo'] = $right_logo;
        ob_end_clean();
        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            $header = '<html><head><style> * {font-family: sans-serif;} @page { margin: 350px 50px 200px;}header{ position: fixed;  top: -320px;}table{width:100%;}.pagenum:after {content: counter(page)}</style> </head><body><header>';
            $header .= ' <div style="width:100%;"><div style="width:25%; float:left; text-align:center; margin-bottom:4px">  <img src="' . $left_logo . '" height="140px" width="155px"></div><div style="width:75%; float:left; text-align:center; margin-bottom:4px; margin-top:24px; font-size: 28px; font-weight:600;">' . $company['cname'] . '
            <div style="font-size:13px; font-weight:400; text-align:center; margin-top: 8px;"><table>
            <tr>
                <td><b>Invoice No: </b>' . $data['invoice']['tid'] . '</td>
                <td><b>Invoice Date: </b>' . date("d-m-Y", strtotime($data["invoice"]["invoicedate"])) . '</td>
                <td><b>Account No: </b>' . $data["invoice"]["name"] . '</td>
            </tr>
        </table></div></div>
            
            <div style="clear:both;"></div>
            <div style="width:378px; top: 0px; right:-180px;  color:#f8faff; background-color: #f36e38; position: absolute !important; transform: rotate(45deg);  "> <div style=" line-height:24px; width: 100%; text-transform: capitalize; font-size: 18px; text-align:center; padding-bottom: 8px;">' . $data['invoice']['status'] . '</div></div> </div> 
      </div><div style="clear:both;"></div>';
        } else {
            $header = '<html><head><style>@page { margin: 350px 50px 200px; }header{ position: fixed;  top: -320px;}table{width:100%;}.pagenum:after {content: counter(page)}</style> </head><body><header><span style="float:right;"  class="pagenum">Page:</span>';
            $header .= ' <div style="width:100%; "><div style="width:23%; float:left; text-align:center;">
      </div></div><div><p style="text-align:center;height:100px;">Note: This is not a valid Invoice  </p></div><div style="clear:both"></div>';
        }

        $header .= '<div style="width:40%;height:100px; border-radius: 0px;float:left;margin-top:55px;">';
        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            $header .= '<div style="font-size: 14px;"><div>' . $company['address'] . '</div><div>' . $company['region'] . ', ' . $company['city'] . ' ' . $company['postbox'] . ' ' . $company['country'] . '</div><div>Phone: ' . $company['phone'] . '</div><div>Mobile:&nbsp;&nbsp;' . $company['mobile'] . '</div><div>Email: ' . $company['email'] . '</div>';
        }
        $header .= '</div></div><div style="float:right;margin-left:-150px;margin-top:0px;width:50%" ><div style=" font-size: 12px">';

        $header .= '<b style=" font-size: 14px">&nbsp;Customer\'s Info</b>';
        $header .= '<div style="border-top:1px solid #25468D; padding: 0 4px;">';
        if ($data['invoice']['company']) {
            $header .= '<b>Name: </b> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $data['invoice']['company'] . '<br>';
        }

        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            $header .= '<b>Address: </b>&nbsp;' . $data['invoice']['cust_address'] . ' ' . $data['invoice']['cust_city'] . ' - ' . $data['invoice']['cust_postcode'] . "";
            if ($data['invoice']['pamt_terms']) {
                $header .= '<br><b> <span style="background-color:yellow">TERMS:</span> </b>' . $custom_fields . '';
            }
        } else {
            $header .= ' <br> ' . $data['invoice']['cust_address'] . '<br/>' . $data['invoice']['cust_city'] . ' ' . $data['invoice']['cust_postcode'] . "";
        }
        $header .= '</div></div></div></header>';


        $header .= '<div style="clear:both"></div><main><div style="width:100%;  "><table width="100%" class="myProducts"><thead><tr style="font-size: 14px; background:#f1f4fb; padding:4px;"><th style="width: 12%; text-align:left; ">' . $lang('Quantity') . '</th><th style="border-left: 1px solid #25468D;width: 45%; text-align:left;">' . $lang('Details') . '</th><th style="width: 13%; text-align:left; border-left: 1px solid #25468D;">' . $lang('Unit_Rate') . '</th><th style="width: 20%; text-align:left; border-left: 1px solid #25468D;">' . $lang('Net Amount') . '</th><th style="width: 10%;  text-align:left;border-left: 1px solid #25468D;">' . $lang('Tax') . '</th></tr></thead>';
        $header .= '<tbody style="font-size:15px;">';

        $ns = 0;
        foreach ($data['products'] as $row) {
            $ns++;
            // Removed calculation - using database value instead
            // Using rev_amountExchange_s() directly in output - no need for variables
            $netamount = amountExchange($row['subtotal']);
            // $vat = amountExchange($row['totaltax'], $invoice['multi'], $invoice['loc']);
            // $netamount = amountExchange($row['subtotal'] - $row['totaltax'], $invoice['multi'], $invoice['loc']);
            if (empty($row['product_des']) || $row['product_des'] == "") {
            } else {
                if ($row['serial'] == '1') {
                    $header .= '<tr style="background:#FBF36D; style="font-size:18px;"">';
                } else {
                    $header .= '<tr style=" border-bottom:1px solid white;">';
                }
                $header .= '<td style=" border-bottom:1px solid white;">' . (int)$row["qty"] . '</td style=" border-bottom:1px solid white;">
                <td style=" border-bottom:1px solid white;">' . $row["product_des"] . '</td>
                <td style=" border-bottom:1px solid white;">' . rev_amountExchange_s($row['price']) . '</td> 
                <td style=" border-bottom:1px solid white;">' . rev_amountExchange_s($row['subtotal']) . '</td>
                <td style=" border-bottom:1px solid white;">' . rev_amountExchange_s($row['totaltax']) . ' </td></tr>';
            }
        }
        $header .= '</tbody></table></div></main>';
        // Use database values instead of calculating totals

        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {

            $header .= '<footer style="position:absolute;bottom:-195px; border-bottom:20px solid #25468D;">';
            if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
                $header .= '<div style="#25468D;width:50%;height:140px; padding: 8px; border-radius: 0px; float:left;margin-top:5px"><span style="color:#25468d"><b>*MAXIMUM CHANGE ACCEPTED=&#163;25</b></span><div style=" font-size: 10px;">All goods remain the property of ' . $company['cname'] . ' until all monies are paid in full<br>Any complaints should be made within 48 hours of purchasing.</div><br><div style="width:100%"><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;  font-size: 10px; float:left">Print Name:</div><div style="border:1px solid black;width:10%;height:18px;float:left;margin-left:10px;;margin-left:-78px;"></div><div style="float:left;margin-left:-158px;margin-top:3px;font-size: 10px;"><b>Cash Tendered</b></div><div style="clear:both"></div><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;  font-size: 10px; float:left;">Signature:</div><div style="padding-left:10px;border:1px solid black;width:35%;height:20px;float:left;padding-top:3px;padding-bottom:3px;margin-left:-157px;  font-size: 10px; margin-top:-8px">&#163;</div><div style="clear:both"></div></div><div style="clear:both"></div></div>';
            }

            // $sub_ts = 0;
            // foreach ($data['products'] as $row) {
            //     $sub_ts += $row['price'] * $row['qty'];
            // }

            $header .= '<div style="float:right;margin-left:320px;width:280px" ><table class="maintable2" style="width:100%; #25468D;font-size: 14px;">';

            if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
                $header .= '<tr ><td style="width: 50%;  background: #f1f4fb;padding: 3px 8px; font-size:12px">&nbsp;Previous Balance</td><td style="font-size: 12px">';
                $invbalance = 0;
                if ($cust_balance_inv >= $data['invoice']['total']) {
                    $invbalance =  $cust_balance_inv - $data['invoice']['total'];
                    $header .=  amountExchange($invbalance, $data['invoice']['multi'], $data['invoice']['loc']);
                    // $header .=  substr(amountExchange($invbalance, $data['invoice']['multi'], $data['invoice']['loc']), 3, 100);
                } else {
                    // $header .=  substr(amountExchange($cust_balance_inv, $data['invoice']['multi'], $data['invoice']['loc']), 3, 100);
                    $header .=  amountExchange($cust_balance_inv, $data['invoice']['multi'], $data['invoice']['loc']);
                }
                $header .= '</td></tr>';
            }

            $header .= '<tr><td style="width: 60%;  background: #f1f4fb;padding:  3px 8px; font-size:12px">&nbsp;Total NET Amount</td><td style="font-size: 12px"> ' . amountExchange($data["invoice"]["subtotal"]) . ' </td></tr>
            <tr ><td style="width: 60%;  background: #f1f4fb;padding: 3px 8px; font-size:12px">&nbsp;Total VAT Amount</td><td style="font-size: 12px"> ' . amountExchange($data["invoice"]["tax"]) . '  </td></tr>
            <tr><td style="width: 60%;  background: #f1f4fb;padding: 3px 8px; font-size:12px">&nbsp;Invoice Total</td><td style="font-size: 12px"> ' . amountExchange($data["invoice"]["total"]) . '   </td></tr>';
            if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {

                $header .= ' <tr><td style="width: 60%; background: #f1f4fb;padding:  3px 8px; font-size:12px">&nbsp;Balance Now Due</td><td style="font-size: 12px">' . amountExchange($cust_balance_inv) . ' </td></tr> ';
                $header .= ' <tr><td style="width: 60%; background: #f1f4fb;padding:  3px 8px; font-size:12px">&nbsp;Driver Name</td><td style="font-size: 12px">' . $data['invoice']['driver_name'] . ' </td></tr> ';
                $header .= ' <tr><td style="width: 60%; background: #f1f4fb;padding:  3px 8px; font-size:12px">&nbsp;Vehicle No.</td><td style="font-size: 12px">' . $data['invoice']['vehicle_no'] . ' </td></tr> ';
            }
            $header .= '</table></div><div style="clear:both"></div>';

            if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
                $header .= '<p style="font-size:12px">Powered By <a style="text-decoration: none; font-weight: 600; color: #25468d" href="https://cloudbillingmanager.com/" target="_blank">Cloud Billing Manger</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Bank Account Details: &nbsp;&nbsp;<b> ' . $company['taxid'] . '</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Sort Code: &nbsp; <b>' . $company['sortcode'] . '</b></p>
    
        ';
            }
            $header .= ' </footer></body></html>';
        } else {


            $header .= '<footer style="position:absolute;bottom:-195px">';
            //if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE' ){
            $header .= '<div style="#25468D;width:50%;height:140px; padding: 8px 0px 5px 4px; float:left;margin-top:5px;display:none"><span style="color:rgb(48, 37, 133)"><b>*MAXIMUM CHANGE ACCEPTED=&#163;25</b></span><div style=" font-size: 10px;">All goods remain the property of ' . $company['cname'] . ' until all monies are paid in full<br>Any complaints should be made within 48 hours of purchasing.</div><br><div style="width:100%"><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left">Print Name:</div><div style="border:1px solid black;width:10%;height:18px;float:left;margin-left:10px;;margin-left:-78px;"></div><div style="float:left;margin-left:-158px;margin-top:3px;font-size: 12px;"><b>Cash Tendered</b></div><div style="clear:both"></div><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left;">Signature:</div><div style="font-size: 17px;padding-left:10px;border:1px solid black;width:35%;height:20px;float:left;padding-top:3px;padding-bottom:3px;margin-left:-157px;margin-top:-8px">&#163;</div><div style="clear:both"></div></div><div style="clear:both"></div></div>';
            //}

            // $sub_ts = 0;
            // foreach ($data['products'] as $row) {
            //     $sub_ts += $row['price'] * $row['qty'];
            // }

            $header .= '<div style="float:right;margin-left:320px;width:280px" ><table class="maintable2" style="width:100%; border:1px solid #25468D;font-size: 14px;">';


            // $inv_total_amt = $data["invoice"]["total"] + $data["invoice"]["tax"];
            // $sub_ts = number_format($sub_ts, 2, '.', '');

            $header .= '<tr><td style="width: 60%;  background: #f1f4fb;padding:  4px 0px 4px 7px;">&nbsp;Total NET Amount</td>
            <td> ' . amountExchange($data["invoice"]["subtotal"]) . ' </td></tr><tr ><td style="width: 60%;  background: #f1f4fb;padding: 4px 0px 4px 7px;">&nbsp;Total ' . $this->lang->line('Tax') . ' Amount</td>
            <td> ' . amountExchange($data["invoice"]["tax"]) . '  </td></tr><tr><td style="width: 60%;  background: #f1f4fb;padding: 4px 0px 4px 7px;">&nbsp;Invoice Total</td>
            <td> ' . amountExchange($data["invoice"]["total"]) . '   </td></tr>';

            $header .= '</table></div><div style="clear:both"></div>';

            //if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE' ){ 
            $header .= '<p style="font-size:8px;display:none">Powered By <a href="https://cloudbillingmanager.com/" target="_blank">Cloud Billing Manger</a>, Company No. 11671004&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Bank Account Details: Sort Code: 04-06-05 A/C No: 21721837</p>';
            //  } 
            $header .= ' </footer></body></html>';
        }

        // $file_name = preg_replace('/[^A-Za-z0-9]+/', '-', 'Invoice__' . $data['invoice']['name'] . '_' . $data['invoice']['tid']);
        $file_name = $this->input->get('id');
        $this->dpdf->loadHtml($header);
        $this->dpdf->render();
        $output =  $this->dpdf->output();
        $file_location = 'userfiles/invoices/' . $file_name . '.pdf';
        file_put_contents($file_location, $output);
        $this->dpdf->stream("" . $file_name . ".pdf", array("Attachment" => 0));
    }
    public function printinvoice5($printing = true, $id = -1)
    {
        // Explicitly load language file for PDF generation
        $system_lang = $this->db->query("SELECT lang FROM geopos_system WHERE id=1 LIMIT 1")->row_array();
        $lang_to_load = $system_lang['lang'] ?? 'english';
        $this->lang->load($lang_to_load, $lang_to_load);
        $dynamic_tax_name = getTaxName();
        $that = $this;
        $lang = function ($key) use ($that, $dynamic_tax_name) {
            $text = $that->lang->line($key);
            if ($text === FALSE || empty($text)) return $key;
            return str_replace(array('{tax}', '{Tax}', '{TAX}'), array(strtolower($dynamic_tax_name), ucfirst(strtolower($dynamic_tax_name)), strtoupper($dynamic_tax_name)), $text);
        };
        $company = $this->settings->company_details(1);

        $productCounter = 0;
        $productsPerPage = 18;

        $hide_balance = 0;
        $tid = $id;
        if ($tid == -1) {
            $tid = $this->input->get('id');
            $hide_balance = ($this->input->get('hide_balance') == 1);
        }
        $data['id'] = $tid;
        $data['invoice'] = $this->invocies->invoice_details($tid, $this->limited);
        $data['products'] = $this->invocies->invoice_products($tid);
        $data['employee'] = $this->invocies->employee($data['invoice']['eid']);
        if ($data['invoice']['i_class'] == 1) {
            $pref = prefix(7);
        } else {
            $pref = $this->config->item('prefix');
        }
        ob_end_clean();
        ob_start();

        $sql = 'SELECT sum(total)-sum(pamnt) as "cust_balance" FROM geopos_invoices WHERE status="due" and csd="' . $data["invoice"]["csd"] . '" or status="partial" and csd="' . $data["invoice"]["csd"] . '"';
        $query = $this->db->query($sql);
        $response = $query->result_array();
        $cust_balance_inv = $response[0]['cust_balance'];

        // $cust_balance_inv= $response[0]['cust_balance'];
        $sql2 = "SELECT content FROM `geopos_notes` ORDER BY `geopos_notes`.`id`  DESC LIMIT 1";
        $query2 = $this->db->query($sql2);
        $response2 = $query2->result_array();
        $content2 = $response2[0]['content'];

        $sql2 = 'SELECT data FROM `geopos_custom_data` where rid="' . $data["invoice"]["csd"] . '"';
        $query2 = $this->db->query($sql2);
        $response2 = $query2->result_array();
        $custom_fields = $response2[0]['data'];

        $sql = 'Update geopos_invoices set print_status=1 where id= ' . $tid . ' ';
        $this->db->query($sql);



        ini_set('memory_limit', '64M');

        $data_tr = "";

        $left_logo = base_url("userfiles/company/" . $company['logo']);
        $data['left_logo'] = $left_logo;
        $right_logo = base_url("assets/images/halal_logo.jpg");
        $data['right_logo'] = $right_logo;
        // $left_logo = base_url("assets/images/dixy_logo_.jpeg");
        //     $data['left_logo'] = $left_logo;
        //     $right_logo = base_url("assets/images/halal_logo.jpg");
        //     $data['right_logo'] = $right_logo;
        ob_end_clean();
        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            $header = '<html><head><style>@page { margin: 350px 50px 240px; } header{ position: fixed;  top: -320px;}
            table{width:100%;}.pagenum:after {content: counter(page)}</style> </head><body><header>';
            $header = '<html><head><style>
                        @page { margin: 350px 50px 200px; }
                        header { position: fixed; top: -330px; }
                        table { width: 100%; }
                        .pagenum:after { content: counter(page); }
                    </style></head><body><header>';

            $header .= '<table style="width: 100%;">
                        <tr style="border: 1px solid #1367A4;">
                            <td style="width: 100%;">
                                <br><b>' . $company['cname'] . '</b><br>' . $company['address'] . '<br>' . $company['city'] . ' ' . $company['region'] . ', ' . $company['postbox'] . '<br>' . $company['country'] . '<br> Phone:&nbsp;&nbsp;&nbsp;&nbsp;' . $company['phone'] . '<br>Mobile:&nbsp;&nbsp;&nbsp;' . $company['mobile'] . '
                            </td>
                            <td style="width: 40%;"><img src="' . $right_logo . '" height="60px" width="60px"></td>
                            <td style="width: 100%;"><img src="' . $left_logo . '" height="140px" width="250px"></td>
                        </tr>
                    </table>';

            $header .= '<div style="width: 100%; display: flex; justify-content: space-between;
            align-items: flex-start; margin-top: 8px;">';
            $header .= '<div style="border: 1px solid #1367A4; padding: 0 10px; border-radius: 6px;
            font-size:12px;">';
            $header .= '<span style="width: 100%; display: block;">
                <b>' . $content2 . '</b>
            </span>';
            $header .= '</div>';  // Closing the inner div
            $header .= '</div>';  // Closing the outer div
        } else {
            $header = '<html><head><style>@page { margin: 350px 50px 200px; }header{ position: fixed;  top: -320px;}table{width:100%;}.pagenum:after {content: counter(page)}</style> </head><body>
            <header><span style="float:right;"  class="pagenum">Page:</span>';
            // $header .= ' <div style="width:100%; "><div style="width:23%; float:left; text-align:center;">  <br> Invoice#: ' . $data['invoice']['tid'] . '
            //     </div>';
            $header .= ' </div><div><p style="text-align:center;height:84px;">Note: This is not a valid Invoice  </p>
                    Date: ' . date("d-m-Y", strtotime($data["invoice"]["invoicedate"])) . '
                </div><div style="clear:both"></div>';
        }

        $header .= '<div style="width: 100%; display: flex; justify-content: space-between;
        align-items: flex-start; margin-top: auto; font-size:12px;">';
        // Left Box
        $header .= '<div style="border: 1px solid #1367A4; height: auto; width: 25%; padding: 10px;
        border-radius: 6px;" >';
        if ($data['invoice']['company']) {
            $header .= $data['invoice']['company'] . '<br>';
        }
        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            $header .= $data['invoice']['cust_address'] . ' ' . $data['invoice']['cust_city'] . '<br>' . $data['invoice']['cust_postcode'];
            if ($data['invoice']['pamt_terms']) {
                $header .= '<br><b><span style="background-color: yellow;">TERMS:</span></b> ' . $custom_fields;
            }
        } else {
            $header .= $data['invoice']['cust_address'] . ' ' . $data['invoice']['cust_city'] . '<br>' . $data['invoice']['cust_postcode'];
            if ($data['invoice']['pamt_terms']) {
                $header .= '<br><b> <span style="background-color:yellow">TERMS:</span> </b>' . $custom_fields . '';
            }
        }
        $header .= '</div>';

        // Center Box (for Bank Transfer Details)
        $header .= '<div style="width: 34%; display: flex; justify-content: flex-end; margin-left: 233px;">';
        if ($data['invoice']['inv_type'] == 'INVOICE') {
            $header .= '<div style="padding: 10px; text-align: center;">';
            $header .= '<b>For Bank Transfer:</b><br>';
            $header .= $company['cname'] . '<br>';
            $header .= 'S/C ' . $company['sortcode'] . '<br>';
            $header .= 'AC ' . $company['taxid'];
            $header .= '</div>';
        }
        $header .= '</div>';
        // Right Box (for Invoice Details)
        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            $header .= '<div style="width: 30%; display: flex; justify-content: flex-end; margin-left: 498px;">';
            $header .= '<table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <th style="border: 1px solid #1367A4; padding: 5px; text-align: left;">Date:</th>
                        <td style="border: 1px solid #1367A4; padding: 5px; text-align: right;">' . date("d-m-Y", strtotime($data["invoice"]["invoicedate"])) . '</td>
                    </tr>
                    <tr>
                        <th style="border: 1px solid #1367A4; padding: 5px; text-align: left;">Invoice:</th>
                        <td style="border: 1px solid #1367A4; padding: 5px; text-align: right;">' . $data['invoice']['tid'] . '</td>
                    </tr>
                    <tr>
                        <th style="border: 1px solid #1367A4; padding: 5px; text-align: left;">A/C:</th>
                        <td style="border: 1px solid #1367A4; padding: 5px; text-align: right;">' . $data["invoice"]["name"] . '</td>
                    </tr>
                    <tr>
                        <th style="border: 1px solid #1367A4; padding: 5px; text-align: left;">EC NO:</th>
                        <td style="border: 1px solid #1367A4; padding: 5px; text-align: right;">882 /2004</td>
                    </tr>
                    <tr>
                        <th style="border: 1px solid #1367A4; padding: 5px; text-align: left;">' . $this->lang->line('VAT') . ' Reg:</th>
                        <td style="border: 1px solid #1367A4; padding: 5px; text-align: right;">299367533</td>
                    </tr>
                </table>';
            $header .= '</div>'; // Closing the Right Box
        }
        $header .= '</div>';
        $header .= '</header>';  // Closing the header and HTML structure
        $header .= '<div style="clear:both"></div><main><div style="width:100%;  "><table width="100%" class="myProducts" style="border:1px solid #1367A4;"><thead><tr style="font-size: 14px; background:#ccc; padding:4px;"><th style="width: 12%; text-align:left; ">' . $lang('Quantity') . '</th><th style="border-left: 1px solid #1367A4;width: 45%; text-align:left;">' . $lang('Details') . '</th><th style="width: 13%; text-align:left; border-left: 1px solid #1367A4;">' . $lang('Unit_Rate') . '</th><th style="width: 20%; text-align:left; border-left: 1px solid #1367A4;">' . $lang('Net Amount') . '</th><th style="width: 10%;  text-align:left;border-left: 1px solid #1367A4;">' . $lang('Tax') . '</th></tr></thead>';
        $header .= '<tbody style="font-size:15px;">';

        $ns = 0;
        foreach ($data['products'] as $row) {
            $ns++;
            // Removed calculation - using database value instead
            $up =     amountExchange($row['price'], $data['invoice']['multi'], $data['invoice']['loc']);
            $product_vattype = $this->get_product_vattype($row['pid']);
            $vat = amountExchange($row['totaltax'], $data['invoice']['multi'], $data['invoice']['loc']);
            $netamount = amountExchange($row['subtotal'], $data['invoice']['multi'], $data['invoice']['loc']);
            // $vat = amountExchange($row['totaltax'], $invoice['multi'], $invoice['loc']);
            // $netamount = amountExchange($row['subtotal'] - $row['totaltax'], $invoice['multi'], $invoice['loc']);
            if (empty($row['product_des']) || $row['product_des'] == "") {
            } else {
                if ($row['serial'] == '1') {
                    $header .= '<tr style="background:#FBF36D; style="font-size:18px;"">';
                } else {
                    $header .= '<tr style=" border-bottom:1px solid white;">';
                }
                $header .= '<td style=" border-bottom:1px solid white;">' . (int)$row["qty"] . $row["unit"] . '</td style=" border-bottom:1px solid white;"><td style=" border-bottom:1px solid white;">' . $row["product_des"] . '</td><td style=" border-bottom:1px solid white;">' . rev_amountExchange_s($row['price']) . '</td> <td style=" border-bottom:1px solid white;">' . rev_amountExchange_s($row['subtotal']) . '</td><td style=" border-bottom:1px solid white;">' . rev_amountExchange_s($row['totaltax']) . ' </td></tr>';
            }
        }
        $header .= '</tbody></table></div></main>';
        // Use database values instead of calculating totals
        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            $header .= '<footer style="position:fixed; bottom:10px;">';
            if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
                $header .= '<div style="width: 70%; float: left;">';

                // Initialize subtotal
                // $sub_ts = 0;
                // foreach ($data['products'] as $row) {
                //     $sub_ts += $row['price'] * $row['qty'];
                // }

                // Define a reusable cell style

                // Start the table with proper borders and collapse styles
                $header .= '<table style="border:1px solid #1367A4; border-collapse: collapse; font-size:12px;">';

                // Add rows with proper border styling and alignment
                $header .= '<tr>
                        <td style="border:1px solid #1367A4; width:20%; padding: 5px;">Total NET Amount</td>
                        <td style="border:1px solid #1367A4; width:20%; padding: 5px;">' . amountExchange($data["invoice"]["subtotal"], $data['invoice']['multi'], $data['invoice']['loc']) . '</td>
                        <td style="border:1px solid #1367A4; border-bottom:none; padding: 5px;"></td>
                        <td style="border:1px solid #1367A4; width:15%; padding: 5px;">Cash</td>
                        <td style="border:1px solid #1367A4; padding: 5px;"></td>
                        <td style="border:1px solid #1367A4; width:15%; padding: 5px;">Sign/Print</td>
                    </tr>';

                $header .= '<tr>
                        <td style="border:1px solid #1367A4; width:20%; padding: 5px;">Total ' . $this->lang->line('VAT') . ' Amount</td>
                        <td style="border:1px solid #1367A4; width:20%; padding: 5px;">' . amountExchange($data["invoice"]["tax"], $data['invoice']['multi'], $data['invoice']['loc']) . '</td>
                        <td style="text-align:center; font-weight:bold; padding: 5px;">Total Amount</td>
                        <td style="border:1px solid #1367A4; width:15%; padding: 5px;">Card/CHQ</td>
                        <td style="border:1px solid #1367A4; padding: 5px;"></td>
                        <td style="border:1px solid #1367A4; width:15%; padding: 5px;"></td>
                    </tr>';

                // Add Previous Balance row if needed
                if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
                    if (!$hide_balance) {
                        // Balance calculation logic
                    }
                }
                if (!$hide_balance) {
                    $invbalance = 0;
                    if ($cust_balance_inv >= $data['invoice']['total']) {
                        $invbalance = $cust_balance_inv - $data['invoice']['total'];
                        $previous_balance = $invbalance;
                    } else {
                        $previous_balance = $cust_balance_inv;
                    }
                    $balance_now_due = $cust_balance_inv;

                    $header .= '<tr>
                            <td style="border:1px solid #1367A4; width:20%; padding: 5px;">Previous Balance</td>
                            <td style="border:1px solid #1367A4; width:20%; padding: 5px;">' . amountExchange($previous_balance, $data['invoice']['multi'], $data['invoice']['loc']) . '</td>
                            <td style="text-align:center; font-weight:bold; padding: 5px;">' . amountExchange($data["invoice"]["total"], $data['invoice']['multi'], $data['invoice']['loc']) . '</td>
                            <td style="border:1px solid #1367A4; padding: 5px; width:15%;">BACS</td>
                            <td style="border:1px solid #1367A4; padding: 5px;"></td>
                            <td style="border:1px solid #1367A4; padding: 5px; width:15%;"></td>
                        </tr>';
                    $header .= '<tr>
                            <td style="border:1px solid #1367A4; width:20%; padding: 5px;">Balance Now Due</td>
                            <td style="border:1px solid #1367A4; width:20%; padding: 5px;">' . amountExchange($balance_now_due, $data['invoice']['multi'], $data['invoice']['loc']) . '</td>
                            <td style="border-left:1px solid #1367A4; padding: 5px;"></td>
                            <td style="border:1px solid #1367A4; padding: 5px; width:15%;">Total</td>
                            <td style="border:1px solid #1367A4; padding: 5px;"></td>
                            <td style="border:1px solid #1367A4; padding: 5px; width:15%;"></td>
                        </tr>';
                } else {
                    $header .= '<tr>
                            <td style="padding: 15px; width:20%;"></td>
                            <td style="padding: 15px; width:20%;"></td>
                            <td style="border-left:1px solid #1367A4; text-align:center; font-weight:bold; padding: 5px;">' . amountExchange($invbalance, $data['invoice']['multi'], $data['invoice']['loc']) . '</td>
                            <td style="border:1px solid #1367A4; padding: 5px; width:15%;">BACS</td>
                            <td style="border:1px solid #1367A4; padding: 5px;"></td>
                            <td style="border:1px solid #1367A4; padding: 5px; width:15%;"></td>
                        </tr>';
                    $header .= '<tr>
                            <td style="padding: 15px; width:20%;"></td>
                            <td style="padding: 15px; width:20%;"></td>
                            <td style="border:1px solid #1367A4; border-top:none; padding: 5px;"></td>
                            <td style="border:1px solid #1367A4; padding: 5px; width:15%;">Total</td>
                            <td style="border:1px solid #1367A4; padding: 5px;"></td>
                            <td style="border:1px solid #1367A4; padding: 5px; width:15%;"></td>
                        </tr>';
                }

                $header .= '</table>';


                $header .= '
                <span style="color:rgb(48, 37, 133);font-size: 12px;text-align:center;"><b>*MAXIMUM CHANGE ACCEPTED=&#163;60</b> </span>
                <div style=" font-size: 12px;">
                *All claims must be notified at the time of delivery,
                any claims made after delivery will not be considered. Goods will remain
                the property of ' . $company['cname'] . ' until paid in full by the customer.Frozen and  products cannot be returned once delivered.
                </div>';
                $header .= '<div style="clear: both;"></div>';
                $header .= '</div>'; // End of main div


                // Add the remaining content
            }



            $sub_ts = 0;
            foreach ($data['products'] as $row) {
                // Removed calculation - using database value instead
            }

            $header .= '<div style="float:right; margin-left:530px; font-size: 14px;">
            <table class="maintable2" style="border: 1px solid #1367A4; border-collapse: collapse; width: 100%;">';

            // Add "Office Use Only" header row
            $header .= '<tr>
                        <th style="border: 1px solid #1367A4; text-align: center; padding: 8px;" colspan="2">&nbsp;Office Use Only</th>
                    </tr>';

            // Add row for "Updated"
            $header .= '<tr>
                        <td style="width: 50%; border: 1px solid #1367A4; padding: 4px 0px 4px 7px;">Updated</td>
                        <td style="width: 50%; border: 1px solid #1367A4; padding: 4px 0px 4px 7px;"></td>
                    </tr>';

            // Add row for "Payment Allocated"
            $header .= '<tr>
                        <td style="width: 50%; border: 1px solid #1367A4; padding: 0px 0px 0px 7px;">Payment Allocated</td>
                        <td style="width: 50%; border: 1px solid #1367A4; padding: 0px 0px 0px 7px;"></td>
                    </tr>';

            // Add an empty row
            $header .= '<tr>
                        <td style="padding: 4px 0px 4px 7px; border: 1px solid #1367A4; border-right: none;">&nbsp;</td>
                        <td></td>
                    </tr>';

            // Additional logic based on invoice type and company
            if ($data['invoice']['inv_type'] == 'INVOICE' || $data['invoice']['inv_type'] == 'DAYPASS' && $data['invoice']['company'] != 'COUNTER SALE') {
                // If there's additional balance logic, it would go here
            }

            $header .= '</table>

            <span style="font-size:12px; text-align: center; href="mailto:' . $company['email'] . '">
            <img src="' . base_url("assets/images/mail.jpg") . '" height="30px" width="35px" style="margin-top:8px;">  </span>' . $company['email'] . '
            <br><span style="font-size:12px; text-align: center; margin-left:10px;">Thank you for your business!</span>
            </div>
            <div style="clear: both;"></div>';
            if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            }
            $header .= ' </footer></body></html>';
        } else {
            $header .= '<footer style="position:fixed; bottom:-70px;">';
            $header .= '<div style="float:right; margin-left:530px; font-size: 14px;">
            <table class="maintable2" style="border: 1px solid #1367A4; border-collapse: collapse; width: 100%;">';
            // $sub_ts = 0;
            // foreach ($data['products'] as $row) {
            //     $sub_ts += $row['price'] * $row['qty'];
            // }

            // Add "Office Use Only" header row
            $header .= '<tr>
                            <td style="width: 50%; border: 1px solid #1367A4; padding: 4px 0px 4px 7px;">Total NET Amount</td>
                            <td style="width: 50%; border: 1px solid #1367A4; padding: 4px 0px 4px 7px;">' . amountExchange($data["invoice"]["subtotal"], $data['invoice']['multi'], $data['invoice']['loc']) . '</td>
                        </tr>';

            // Add row for "Updated"
            $header .= '<tr>
                        <td style="width: 50%; border: 1px solid #1367A4; padding: 4px 0px 4px 7px;">Total ' . $this->lang->line('VAT') . ' Amount</td>
                        <td style="width: 50%; border: 1px solid #1367A4; padding: 4px 0px 4px 7px;">' . amountExchange($data["invoice"]["tax"], $data['invoice']['multi'], $data['invoice']['loc']) . '</td>
                    </tr>';

            // Add row for "Payment Allocated"
            $header .= '<tr>
                        <td style="width: 50%; border: 1px solid #1367A4; padding: 0px 0px 0px 7px;">Total Invoice Balance</td>
                        <td style="width: 50%; border: 1px solid #1367A4; padding: 0px 0px 0px 7px;">' . amountExchange($data["invoice"]["total"], $data['invoice']['multi'], $data['invoice']['loc']) . '</td>
                    </tr>';

            // Additional logic based on invoice type and company
            if ($data['invoice']['inv_type'] == 'INVOICE' || $data['invoice']['inv_type'] == 'DAYPASS' && $data['invoice']['company'] != 'COUNTER SALE') {
                // If there's additional balance logic, it would go here
            }

            $header .= '</table>';
            $header .= ' </footer></body></html>';
        }

        // $file_name = preg_replace('/[^A-Za-z0-9]+/', '-', 'Invoice__' . $data['invoice']['name'] . '_' . $data['invoice']['tid']);
        // $tid = $id;
        // if($tid == -1) {
        //     $tid = $this->input->get('id');
        // }
        $file_name = $tid;
        $this->dpdf->loadHtml($header);
        $this->dpdf->render();
        $output =  $this->dpdf->output();
        $file_location = 'userfiles/invoices/' . $file_name . '.pdf';
        file_put_contents($file_location, $output);
        if ($printing) {
            $this->dpdf->stream("" . $file_name . ".pdf", array("Attachment" => 0));
        }
    }
    public function printinvoice6($printing = true, $id = -1)
    {
        // Explicitly load language file for PDF generation
        $system_lang = $this->db->query("SELECT lang FROM geopos_system WHERE id=1 LIMIT 1")->row_array();
        $lang_to_load = $system_lang['lang'] ?? 'english';
        $this->lang->load($lang_to_load, $lang_to_load);
        $dynamic_tax_name = getTaxName();
        $that = $this;
        $lang = function ($key) use ($that, $dynamic_tax_name) {
            $text = $that->lang->line($key);
            if ($text === FALSE || empty($text)) return $key;
            return str_replace(array('{tax}', '{Tax}', '{TAX}'), array(strtolower($dynamic_tax_name), ucfirst(strtolower($dynamic_tax_name)), strtoupper($dynamic_tax_name)), $text);
        };
        $company = $this->settings->company_details(1);

        $productCounter = 0;
        $productsPerPage = 18;

        $hide_balance = 0;
        $tid = $id;
        if ($tid == -1) {
            $tid = $this->input->get('id');
            $hide_balance = ($this->input->get('hide_balance') == 1);
        }
        $data['id'] = $tid;
        $data['invoice'] = $this->invocies->invoice_details($tid, $this->limited);
        $data['products'] = $this->invocies->invoice_products($tid);
        $data['employee'] = $this->invocies->employee($data['invoice']['eid']);
        if ($data['invoice']['i_class'] == 1) {
            $pref = prefix(7);
        } else {
            $pref = $this->config->item('prefix');
        }
        ob_end_clean();
        ob_start();

        $sql = 'SELECT sum(total)-sum(pamnt) as "cust_balance" FROM geopos_invoices WHERE status="due" and csd="' . $data["invoice"]["csd"] . '" or status="partial" and csd="' . $data["invoice"]["csd"] . '"';
        $query = $this->db->query($sql);
        $response = $query->result_array();
        $cust_balance_inv = $response[0]['cust_balance'];

        // $cust_balance_inv= $response[0]['cust_balance'];
        $sql2 = "SELECT content FROM `geopos_notes` ORDER BY `geopos_notes`.`id`  DESC LIMIT 1";
        $query2 = $this->db->query($sql2);
        $response2 = $query2->result_array();
        $content2 = $response2[0]['content'];

        $sql2 = 'SELECT data FROM `geopos_custom_data` where rid="' . $data["invoice"]["csd"] . '"';
        $query2 = $this->db->query($sql2);
        $response2 = $query2->result_array();
        $custom_fields = $response2[0]['data'];

        $sql = 'Update geopos_invoices set print_status=1 where id= ' . $tid . ' ';
        $this->db->query($sql);



        ini_set('memory_limit', '64M');

        $data_tr = "";

        $left_logo = base_url("userfiles/company/" . $company['logo']);
        $data['left_logo'] = $left_logo;
        $right_logo = base_url("assets/images/halal_logo.jpg");
        $data['right_logo'] = $right_logo;
        // $left_logo = base_url("assets/images/dixy_logo_.jpeg");
        //     $data['left_logo'] = $left_logo;
        //     $right_logo = base_url("assets/images/halal_logo.jpg");
        //     $data['right_logo'] = $right_logo;
        ob_end_clean();
        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            $header = '<html><head><style>@page { margin: 350px 50px 240px; } header{ position: fixed;  top: -320px;}
            table{width:100%;}.pagenum:after {content: counter(page)}</style> </head><body><header>';
            $header = '<html><head><style>
                        @page { margin: 350px 50px 200px; }
                        header { position: fixed; top: -340px; }
                        table { width: 100%; }
                        .pagenum:after { content: counter(page); }
                    </style></head><body><header>';

            $header .= '<table style="width: 100%;">
                        <tr style="border: 1px solid #1367A4;">
                        <td style="width: 100%;">
                            <b>' . $company['cname'] . '</b><br>' . $company['address'] . '<br>' . $company['city'] . ' ' . $company['region'] . ', ' . $company['postbox'] . '<br>' . $company['country'] . '<br> Phone:&nbsp;&nbsp;&nbsp;&nbsp;' . $company['phone'] . '<br>Mobile:&nbsp;&nbsp;&nbsp;' . $company['mobile'] . '
                        </td>
                        <td style="width: 40%;"><img src="' . $right_logo . '" height="60px" width="60px"></td>
                        <td style="width: 100%;"><img src="' . $left_logo . '" height="165px" width="270px"></td>
                        </tr>
                    </table>';
        } else {
            $header = '<html><head><style>@page { margin: 350px 50px 200px; }header{ position: fixed;  top: -320px;}table{width:100%;}.pagenum:after {content: counter(page)}</style> </head><body>
            <header><span style="float:right;"  class="pagenum">Page:</span>';
            $header .= ' <div style="width:100%; "><div style="width:23%; float:left; text-align:center;">  <br>
                </div></div><div><p style="text-align:center;height:84px;">Note: This is not a valid Invoice  </p>
                    Date: ' . date("d-m-Y", strtotime($data["invoice"]["invoicedate"])) . '
                </div><div style="clear:both"></div>';
            // $header .= ' <div style="width:100%; "><div style="width:23%; float:left; text-align:center;">  <br> Invoice#: ' . $data['invoice']['tid'] . '
            //     </div></div><div><p style="text-align:center;height:84px;">Note: This is not a valid Invoice  </p>
            //         Date: ' . date("d-m-Y", strtotime($data["invoice"]["invoicedate"])) . '
            //     </div><div style="clear:both"></div>';
        }
        // Customer Info
        $header .= '<div style="width:40%;height:100px; border-radius: 0px;float:left;">';
        $header .= '<b>&nbsp;Customer\'s Info</b>';
        $header .= '<div style="border:1px solid #25468D; padding: 8px 10px 8px;">';
        if ($data['invoice']['company']) {
            $header .= $data['invoice']['company'] . '<br>';
        }

        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            $header .= $data['invoice']['cust_address'] . ' ' . $data['invoice']['cust_city'] . '<br/>' . $data['invoice']['cust_postcode'] . "";
            if ($data['invoice']['pamt_terms']) {
                $header .= '<br><b> <span style="background-color:yellow">TERMS:</span> </b>' . $custom_fields . '';
            }
            // if ($data['invoice']['country']) {
            //      $header.= '<br>' . $data['invoice']['country'];
            // }
        } else {
            $header .= ' <br> ' . $data['invoice']['cust_address'] . ' ' . $data['invoice']['cust_city'] . '<br/>' . $data['invoice']['cust_postcode'] . "";
            if ($data['invoice']['pamt_terms']) {
                $header .= '<br><b> <span style="background-color:yellow">TERMS:</span> </b>' . $custom_fields . '';
            }
        }
        // if ($data['invoice']['postbox']){
        //     $header.=' <br> ' . $data['invoice']['cust_address'];
        // } 
        // if ($data['invoice']['email']){
        //     $header.= '<br> ' . $this->lang->line('Email') . ': ' . $data['invoice']['email'];
        // }

        $header .= '</div></div><div style="float:right;margin-left:-150px;margin-top:0px;width:50%" ><div style=" font-size: 18px">';



        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            $header .= '<b>&nbsp;Invoice</b>';
            $header .= '</div><table style="width:100%; font-size: 14px; border:1px solid #25468D;"><tr><td style="width: 50%; border-bottom:1px solid #25468D;background: #f1f4fb;padding: 4px;">&nbsp;Invoice No.</td>
           <td  style="border-bottom:1px solid #25468D;">';
            $header .= $data['invoice']['tid'];
            $header .= ' </td></tr><tr><td style="width: 50%; border-bottom:1px solid #25468D;background: #f1f4fb;padding:  4px;">&nbsp;Invoice Date</td><td style="border-bottom:1px solid #25468D;"> ' . date("d-m-Y", strtotime($data["invoice"]["invoicedate"])) . ' </td></tr>';
            $header .= '  <tr><td style="width: 50%; background: #f1f4fb;padding: 4px;">&nbsp;Account No.</td><td> ' . $data["invoice"]["name"] . '</td></tr>';
            $header .= '</table><div>';
        } else {
            $header .= '&nbsp;';
        }






        // if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
        //     $header .= ' &nbsp;VAT Reg No: 375 6160 81 ';
        // }
        $header .= '</div></div></header>';
        if ($data['invoice']['inv_type'] == 'INVOICE') {
            $header .= '
            
                <table style="width:100%; font-size: 14px;  margin-top:-43px;"><tr><td style="width: 100%;">&nbsp;<b> 
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                For Bank Transfer:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $company['cname'] . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;S/C&nbsp;&nbsp;' . $company['sortcode'] . ' 
                          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;AC&nbsp;&nbsp;' . $company['taxid'] . '  
                </b></td></tr></table>
                
                ';
        }
        $header .= '<div style="clear:both"></div><main><div style="width:100%;  ">
        <table width="100%" class="myProducts" style="border:1px solid #1367A4;">
        <thead><tr style="font-size: 14px; background:#ccc; padding:4px;">
        <th style="width: 12%; text-align:left; ">Quantity</th>
        <th style="border-left: 1px solid #1367A4;width: 45%; text-align:left;">Details</th>
        <th style="width: 13%; text-align:left; border-left: 1px solid #1367A4;">Unit Price</th>
        <th style="width: 20%; text-align:left; border-left: 1px solid #1367A4;">Net Amount</th>
        <th style="width: 10%;  text-align:left;border-left: 1px solid #1367A4;">' . $this->lang->line('Tax') . '</th></tr></thead>';
        $header .= '<tbody style="font-size:15px;">';

        $ns = 0;
        foreach ($data['products'] as $row) {
            $ns++;
            // Removed calculation - using database value instead
            // Using rev_amountExchange_s() directly in output - no need for variables
            $netamount = amountExchange($row['subtotal']);
            // $vat = amountExchange($row['totaltax'], $invoice['multi'], $invoice['loc']);
            // $netamount = amountExchange($row['subtotal'] - $row['totaltax'], $invoice['multi'], $invoice['loc']);
            if (empty($row['product_des']) || $row['product_des'] == "") {
            } else {
                if ($row['serial'] == '1') {
                    $header .= '<tr style="background:#FBF36D; style="font-size:18px;"">';
                } else {
                    $header .= '<tr style=" border-bottom:1px solid white;">';
                }
                $header .= '<td style=" border-bottom:1px solid white;">' . (int)$row["qty"] . '</td style=" border-bottom:1px solid white;">
                <td style=" border-bottom:1px solid white;">' . $row["product_des"] . '</td><td style=" border-bottom:1px solid white;">' . rev_amountExchange_s($row['price']) . '</td> 
                <td style=" border-bottom:1px solid white;">' . rev_amountExchange_s($row['subtotal']) . '</td><td style=" border-bottom:1px solid white;">' . rev_amountExchange_s($row['totaltax']) . ' </td></tr>';
            }
        }
        $header .= '</tbody></table></div></main>';
        // Use database values instead of calculating totals

        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {

            $header .= '<footer style="position:absolute;bottom:-195px;">';
            if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
                $header .= '<div style="border:1px solid #25468D;width:50%;height:140px; padding: 8px; border-radius: 0px; float:left;margin-top:5px"><span style="color:#25468d"><b>*MAXIMUM CHANGE ACCEPTED=&#163;25</b></span><div style=" font-size: 10px;">All goods remain the property of ' . $company['cname'] . ' until all monies are paid in full<br>Any complaints should be made within 48 hours of purchasing.</div><br><div style="width:100%"><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;  font-size: 10px; float:left">Print Name:</div><div style="border:1px solid black;width:10%;height:18px;float:left;margin-left:10px;;margin-left:-78px;"></div><div style="float:left;margin-left:-158px;margin-top:3px;font-size: 10px;"><b>Cash Tendered</b></div><div style="clear:both"></div><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;  font-size: 10px; float:left;">Signature:</div><div style="padding-left:10px;border:1px solid black;width:35%;height:20px;float:left;padding-top:3px;padding-bottom:3px;margin-left:-157px;  font-size: 10px; margin-top:-8px">&#163;</div><div style="clear:both"></div></div><div style="clear:both"></div></div>';
            }

            // $sub_ts = 0;
            // foreach ($data['products'] as $row) {
            //     $sub_ts += $row['price'] * $row['qty'];
            // }

            $header .= '<div style="float:right;margin-left:320px;width:280px" ><table class="maintable2" style="width:100%; border:1px solid #25468D;font-size: 14px;">';

            if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
                $header .= '<tr ><td style="width: 50%;  background: #f1f4fb;padding: 3px 8px; font-size:12px">&nbsp;Previous Balance</td><td style="font-size: 12px">';
                $invbalance = 0;
                if ($cust_balance_inv >= $data['invoice']['total']) {
                    $invbalance =  $cust_balance_inv - $data['invoice']['total'];
                    $header .=  amountExchange($invbalance, $data['invoice']['multi'], $data['invoice']['loc']);
                    // $header .=  substr(amountExchange($invbalance, $data['invoice']['multi'], $data['invoice']['loc']), 3, 100);
                } else {
                    $header .=  amountExchange($cust_balance_inv, $data['invoice']['multi'], $data['invoice']['loc']);
                    // $header .=  substr(amountExchange($cust_balance_inv, $data['invoice']['multi'], $data['invoice']['loc']), 3, 100);
                }
                $header .= '</td></tr>';
            }

            $header .= '<tr><td style="width: 60%;  background: #f1f4fb;padding:  3px 8px; font-size:12px">&nbsp;Total NET Amount</td><td style="font-size: 12px"> ' . amountExchange($data["invoice"]["subtotal"]) . ' </td></tr>
            <tr ><td style="width: 60%;  background: #f1f4fb;padding: 3px 8px; font-size:12px">&nbsp;Total VAT Amount</td><td style="font-size: 12px"> ' . amountExchange($data["invoice"]["tax"]) . '  </td></tr>
            <tr><td style="width: 60%;  background: #f1f4fb;padding: 3px 8px; font-size:12px">&nbsp;Invoice Total</td><td style="font-size: 12px"> ' . amountExchange($data["invoice"]["total"]) . '   </td></tr>';
            if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {

                $header .= ' <tr><td style="width: 60%; background: #f1f4fb;padding:  3px 8px; font-size:12px">&nbsp;Balance Now Due</td><td style="font-size: 12px">' . amountExchange($cust_balance_inv) . ' </td></tr> ';
                $header .= ' <tr><td style="width: 60%; background: #f1f4fb;padding:  3px 8px; font-size:12px">&nbsp;Driver Name</td><td style="font-size: 12px">' . $data['invoice']['driver_name'] . ' </td></tr> ';
                $header .= ' <tr><td style="width: 60%; background: #f1f4fb;padding:  3px 8px; font-size:12px">&nbsp;Vehicle No.</td><td style="font-size: 12px">' . $data['invoice']['vehicle_no'] . ' </td></tr> ';
            }
            $header .= '</table></div><div style="clear:both"></div>';

            if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
                $header .= '<p style="font-size:12px">Powered By <a style="text-decoration: none; font-weight: 600; color: #25468d" href="https://cloudbillingmanager.com/" target="_blank">Cloud Billing Manger</a>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;Bank Account Details: &nbsp;&nbsp;<b> ' . $company['taxid'] . '</b>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Sort Code: &nbsp; <b>' . $company['sortcode'] . '</b></p>
    
            ';
            }
            $header .= ' </footer></body></html>';
        } else {


            $header .= '<footer style="position:absolute;bottom:-195px">';
            //if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE' ){
            $header .= '<div style="border:1px solid #25468D;width:50%;height:140px; padding: 8px 0px 5px 4px; border-radius: 6px;float:left;margin-top:5px;display:none"><span style="color:rgb(48, 37, 133)"><b>*MAXIMUM CHANGE ACCEPTED=&#163;25</b></span><div style=" font-size: 10px;">All goods remain the property of ' . $company['cname'] . ' until all monies are paid in full<br>Any complaints should be made within 48 hours of purchasing.</div><br><div style="width:100%"><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left">Print Name:</div><div style="border:1px solid black;width:10%;height:18px;float:left;margin-left:10px;;margin-left:-78px;"></div><div style="float:left;margin-left:-158px;margin-top:3px;font-size: 12px;"><b>Cash Tendered</b></div><div style="clear:both"></div><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left;">Signature:</div><div style="font-size: 17px;padding-left:10px;border:1px solid black;width:35%;height:20px;float:left;padding-top:3px;padding-bottom:3px;margin-left:-157px;margin-top:-8px">&#163;</div><div style="clear:both"></div></div><div style="clear:both"></div></div>';
            //}

            // $sub_ts = 0;
            // foreach ($data['products'] as $row) {
            //     $sub_ts += $row['price'] * $row['qty'];
            // }

            $header .= '<div style="float:right;margin-left:320px;width:280px" ><table class="maintable2" style="width:100%; border:1px solid #25468D;font-size: 14px;">';


            // $inv_total_amt = $data["invoice"]["total"] + $data["invoice"]["tax"];
            // $sub_ts = number_format($sub_ts, 2, '.', '');

            $header .= '<tr><td style="width: 60%;  background: #f1f4fb;padding:  4px 0px 4px 7px;">&nbsp;Total NET Amount</td>
            <td> ' . amountExchange($data["invoice"]["subtotal"]) . ' </td></tr><tr ><td style="width: 60%;  background: #f1f4fb;padding: 4px 0px 4px 7px;">&nbsp;Total ' . $this->lang->line('Tax') . ' Amount</td>
            <td> ' . amountExchange($data["invoice"]["tax"]) . '  </td></tr><tr><td style="width: 60%;  background: #f1f4fb;padding: 4px 0px 4px 7px;">&nbsp;Invoice Total</td>
            <td> ' . amountExchange($data["invoice"]["total"]) . '   </td></tr>';

            $header .= '</table></div><div style="clear:both"></div>';

            //if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE' ){ 
            $header .= '<p style="font-size:8px;display:none">Powered By <a href="https://cloudbillingmanager.com/" target="_blank">Cloud Billing Manger</a>, Company No. 11671004&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Bank Account Details: Sort Code: 04-06-05 A/C No: 21721837</p>';
            //  } 
            $header .= ' </footer></body></html>';
        }

        // $file_name = preg_replace('/[^A-Za-z0-9]+/', '-', 'Invoice__' . $data['invoice']['name'] . '_' . $data['invoice']['tid']);
        // $tid = $id;
        // if($tid == -1) {
        //     $tid = $this->input->get('id');
        // }
        $file_name = $tid;
        $this->dpdf->loadHtml($header);
        $this->dpdf->render();
        $output =  $this->dpdf->output();
        $file_location = 'userfiles/invoices/' . $file_name . '.pdf';
        file_put_contents($file_location, $output);
        if ($printing) {
            $this->dpdf->stream("" . $file_name . ".pdf", array("Attachment" => 0));
        }
    }
    public function printinvoice7($printing = true, $id = -1)
    {
        // Explicitly load language file for PDF generation
        $system_lang = $this->db->query("SELECT lang FROM geopos_system WHERE id=1 LIMIT 1")->row_array();
        $lang_to_load = $system_lang['lang'] ?? 'english';
        $this->lang->load($lang_to_load, $lang_to_load);
        $dynamic_tax_name = getTaxName();
        $that = $this;
        $lang = function ($key) use ($that, $dynamic_tax_name) {
            $text = $that->lang->line($key);
            if ($text === FALSE || empty($text)) return $key;
            return str_replace(array('{tax}', '{Tax}', '{TAX}'), array(strtolower($dynamic_tax_name), ucfirst(strtolower($dynamic_tax_name)), strtoupper($dynamic_tax_name)), $text);
        };
        $company = $this->settings->company_details(1);

        $productCounter = 0;
        $productsPerPage = 18;

        $hide_balance = 0;
        $tid = $id;
        if ($tid == -1) {
            $tid = $this->input->get('id');
            $hide_balance = ($this->input->get('hide_balance') == 1);
        }
        $data['id'] = $tid;
        $data['invoice'] = $this->invocies->invoice_details($tid, $this->limited);
        $data['products'] = $this->invocies->invoice_products($tid);
        $data['employee'] = $this->invocies->employee($data['invoice']['eid']);
        if ($data['invoice']['i_class'] == 1) {
            $pref = prefix(7);
        } else {
            $pref = $this->config->item('prefix');
        }
        ob_end_clean();
        ob_start();

        $sql = 'SELECT sum(total)-sum(pamnt) as "cust_balance" FROM geopos_invoices WHERE status="due" and csd="' . $data["invoice"]["csd"] . '" or status="partial" and csd="' . $data["invoice"]["csd"] . '"';
        $query = $this->db->query($sql);
        $response = $query->result_array();
        $cust_balance_inv = $response[0]['cust_balance'];

        // $cust_balance_inv= $response[0]['cust_balance'];
        $sql2 = "SELECT content FROM `geopos_notes` ORDER BY `geopos_notes`.`id`  DESC LIMIT 1";
        $query2 = $this->db->query($sql2);
        $response2 = $query2->result_array();
        $content2 = $response2[0]['content'];

        $sql2 = 'SELECT data FROM `geopos_custom_data` where rid="' . $data["invoice"]["csd"] . '"';
        $query2 = $this->db->query($sql2);
        $response2 = $query2->result_array();
        $custom_fields = $response2[0]['data'];

        $sql = 'Update geopos_invoices set print_status=1 where id= ' . $tid . ' ';
        $this->db->query($sql);



        ini_set('memory_limit', '64M');

        $data_tr = "";

        $left_logo = base_url("userfiles/company/" . $company['logo']);
        $data['left_logo'] = $left_logo;
        $right_logo = base_url("assets/images/halal_logo.jpg");
        $data['right_logo'] = $right_logo;
        // $left_logo = base_url("assets/images/dixy_logo_.jpeg");
        //     $data['left_logo'] = $left_logo;
        //     $right_logo = base_url("assets/images/halal_logo.jpg");
        //     $data['right_logo'] = $right_logo;
        ob_end_clean();
        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            $header = '<html><head><style>@page { margin: 350px 50px 240px; } header{ position: fixed;  top: -320px;}
            table{width:100%;}.pagenum:after {content: counter(page)}</style> </head><body><header>';
            $header = '<html><head><style>
                        @page { margin: 350px 50px 200px; }
                        header { position: fixed; top: -330px; }
                        table { width: 100%; }
                        .pagenum:after { content: counter(page); }
                    </style></head><body><header>';

            $header .= '<table style="width: 100%; border-collapse: collapse;">
                    <tr style="border: 1px solid #1367A4;">
                        <td style="width: 100%; padding: 10px; vertical-align: top;">
                            <b>' . $company['cname'] . '</b><br>'
                . $company['address'] . '<br>'
                . $company['city'] . ' ' . $company['region'] . ', ' . $company['postbox'] . '<br>' . $company['country'] . '<br>
                            Phone: ' . $company['phone'] . '<br>
                            Mobile: ' . $company['mobile'] . '
                        </td>
                        <td style="width: 20%; text-align: center; vertical-align: middle;">
                            <img src="' . $right_logo . '" height="60px" width="60px">
                        </td>
                        <td style="width: 100%; padding: 10px; text-align: center; vertical-align: middle;">
                            <div style="
                                border: 1px solid #1367A4;
                                height: auto;
                                width: 100%;
                                text-align: center;
                                padding: 10px;
                                border-radius: 6px;
                                margin: 0 auto;">
                                <span style="font-size: 16px; font-weight: bold; color: #1367A4; display: block; margin-bottom: 8px;">
                                    IMPORTANT CUSTOMER NOTICE!
                                </span>
                                <span style="font-size: 12px; color: #333; display: block; margin-bottom: 5px;">
                                    ANY DELIVERY ORDER PLACED FOR BELOW £250 IN VALUE WILL INCUR A DELIVERY CHARGE.
                                </span>
                                <span style="font-size: 12px; color: #333; display: block;">
                                    KINDLY PLACE ORDERS ACCORDINGLY!
                                </span>
                            </div>
                        </td>
                    </tr>
                </table>';


            $header .= '<div style="width: 100%; display: flex; justify-content: space-between;
            align-items: flex-start; margin-top: 8px;">';
            $header .= '<div style="border: 1px solid #1367A4; height:30px; padding: 0 10px; border-radius: 6px;
            font-size:12px;">';
            // $header .= '<span style="width: 100%; display: block;">
            //     <b>' . $content2 . '</b>
            // </span>';
            $header .= '</div>';  // Closing the inner div
            $header .= '</div>';  // Closing the outer div
        } else {
            $header = '<html><head><style>@page { margin: 350px 50px 200px; }header{ position: fixed;  top: -320px;}table{width:100%;}.pagenum:after {content: counter(page)}</style> </head><body>
            <header><span style="float:right;"  class="pagenum">Page:</span>';
            $header .= ' <div style="width:100%; "><div style="width:23%; float:left; text-align:center;">  <br> Invoice#: ' . $data['invoice']['tid'] . '
                </div></div><div><p style="text-align:center;height:84px;">Note: This is not a valid Invoice  </p>
                    Date: ' . date("d-m-Y", strtotime($data["invoice"]["invoicedate"])) . '
                </div><div style="clear:both"></div>';
        }

        $header .= '<div style="width: 100%; display: flex; justify-content: space-between;
        align-items: flex-start; margin-top: auto; font-size:12px;">';
        // Left Box
        $header .= '<div style="border: 1px solid #1367A4; height: auto; width: 25%; padding: 10px;
        border-radius: 6px;" >';
        if ($data['invoice']['company']) {
            $header .= $data['invoice']['company'] . '<br>';
        }
        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            $header .= $data['invoice']['cust_address'] . ' ' . $data['invoice']['cust_city'] . '<br>' . $data['invoice']['cust_postcode'];
            if ($data['invoice']['pamt_terms']) {
                $header .= '<br><b><span style="background-color: yellow;">TERMS:</span></b> ' . $custom_fields;
            }
        } else {
            $header .= $data['invoice']['cust_address'] . ' ' . $data['invoice']['cust_city'] . '<br>' . $data['invoice']['cust_postcode'];
            if ($data['invoice']['pamt_terms']) {
                $header .= '<br><b> <span style="background-color:yellow">TERMS:</span> </b>' . $custom_fields . '';
            }
        }
        $header .= '</div>';

        // Center Box (for Bank Transfer Details)
        $header .= '<div style="width: 34%; display: flex; justify-content: flex-end; margin-left: 233px;">';
        if ($data['invoice']['inv_type'] == 'INVOICE') {
            $header .= '<div style="padding: 10px; text-align: center;">';
            $header .= '<b>For Bank Transfer:</b><br>';
            $header .= $company['cname'] . '<br>';
            $header .= 'S/C ' . $company['sortcode'] . '<br>';
            $header .= 'AC ' . $company['taxid'];
            $header .= '</div>';
        }
        $header .= '</div>';
        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            // Right Box (for Invoice Details)
            $header .= '<div style="width: 30%; display: flex; justify-content: flex-end; margin-left: 498px;">';
            $header .= '<table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <th style="border: 1px solid #1367A4; padding: 5px; text-align: left;">Date:</th>
                        <td style="border: 1px solid #1367A4; padding: 5px; text-align: right;">' . date("d-m-Y", strtotime($data["invoice"]["invoicedate"])) . '</td>
                    </tr>
                    <tr>
                        <th style="border: 1px solid #1367A4; padding: 5px; text-align: left;">Invoice:</th>
                        <td style="border: 1px solid #1367A4; padding: 5px; text-align: right;">' . $data['invoice']['tid'] . '</td>
                    </tr>
                    <tr>
                        <th style="border: 1px solid #1367A4; padding: 5px; text-align: left;">A/C:</th>
                        <td style="border: 1px solid #1367A4; padding: 5px; text-align: right;">' . $data["invoice"]["name"] . '</td>
                    </tr>
                    <tr>
                        <th style="border: 1px solid #1367A4; padding: 5px; text-align: left;">EC NO:</th>
                        <td style="border: 1px solid #1367A4; padding: 5px; text-align: right;">882 /2004</td>
                    </tr>
                    <tr>
                        <th style="border: 1px solid #1367A4; padding: 5px; text-align: left;">VAT Reg:</th>
                        <td style="border: 1px solid #1367A4; padding: 5px; text-align: right;">299367533</td>
                    </tr>
                </table>';
            $header .= '</div>'; // Closing the Right Box
        }
        $header .= '</div>';
        $header .= '</header>';  // Closing the header and HTML structure
        $header .= '<div style="clear:both"></div><main><div style="width:100%;">';
        $header .= '<table width="100%" class="myProducts" style="border:1px solid #1367A4;">
                        <thead>
                            <tr style="font-size: 14px; background:#ccc; padding:4px;">
                                <th style="width: 4%; text-align:left;">
                                    <input type="checkbox" onclick="toggleCheckboxes(this)" style="margin: 0; vertical-align: middle;">
                                </th>
                                <th style="width: 12%; text-align:left;">Quantity</th>
                                <th style="border-left: 1px solid #1367A4; width: 43%; text-align:left;">Details</th>
                                <th style="width: 13%; text-align:left; border-left: 1px solid #1367A4;">Unit Price</th>
                                <th style="width: 18%; text-align:left; border-left: 1px solid #1367A4;">Net Amount</th>
                                <th style="width: 10%; text-align:left; border-left: 1px solid #1367A4;">VAT</th>
                            </tr>
                        </thead>';
        $header .= '<tbody style="font-size:15px;">';

        $ns = 0;
        $sub_t = 0;

        foreach ($data['products'] as $row) {
            $ns++;
            // Removed calculation - using database value instead
            // Using rev_amountExchange_s() directly in output - no need for variables
            $netamount = amountExchange($row['subtotal']);
            // $vat = amountExchange($row['totaltax'], $invoice['multi'], $invoice['loc']);
            // $netamount = amountExchange($row['subtotal'] - $row['totaltax'], $invoice['multi'], $invoice['loc']);

            if (!empty($row['product_des'])) {
                // Highlight row if serial equals 1
                $rowStyle = ($row['serial'] == '1') ? 'background:#FBF36D; font-size:18px;' : 'border-bottom:1px solid white;';

                $header .= '<tr style="' . $rowStyle . '">';
                $header .= '<td style="border: 1px solid #1367A4; text-align: center;">
                        <input type="checkbox" class="row-checkbox" style="margin: 0; vertical-align: middle;">
                    </td>';
                $header .= '<td style="border-bottom:1px solid white;">' . (int)$row["qty"] . '</td>';
                $header .= '<td style="border-bottom:1px solid white;">' . $row["product_des"] . '</td>';
                $header .= '<td style="border-bottom:1px solid white;">' . rev_amountExchange_s($row['price']) . '</td>';
                $header .= '<td style="border-bottom:1px solid white;">' . rev_amountExchange_s($row['subtotal']) . '</td>';
                $header .= '<td style="border-bottom:1px solid white;">' . rev_amountExchange_s($row['totaltax']) . '</td>';
                $header .= '</tr>';
            }
        }

        $header .= '</tbody></table></div></main>';
        // Use database values instead of calculating totals
        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {

            $header .= '<footer style="position:fixed; bottom:10px;">';
            if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
                $header .= '<div style="width: 70%; float: left;">';

                // Initialize subtotal
                $sub_ts = 0;
                foreach ($data['products'] as $row) {
                    // Removed calculation - using database value instead
                }

                // Define a reusable cell style

                // Start the table with proper borders and collapse styles
                $header .= '<table style="border:1px solid #1367A4; border-collapse: collapse; font-size:12px;">';

                // Add rows with proper border styling and alignment
                $header .= '<tr>
                        <td style="border:1px solid #1367A4; width:20%; padding: 5px;">Total NET Amount</td>
                        <td style="border:1px solid #1367A4; width:20%; padding: 5px;">' . amountExchange($data["invoice"]["subtotal"]) . '</td>
                        <td style="border:1px solid #1367A4; border-bottom:none; padding: 5px;"></td>
                        <td style="border:1px solid #1367A4; width:15%; padding: 5px;">Cash</td>
                        <td style="border:1px solid #1367A4; padding: 5px;"></td>
                        <td style="border:1px solid #1367A4; width:15%; padding: 5px;">Sign/Print</td>
                    </tr>';

                $header .= '<tr>
                        <td style="border:1px solid #1367A4; width:20%; padding: 5px;">Total VAT Amount</td>
                        <td style="border:1px solid #1367A4; width:20%; padding: 5px;">' . amountExchange($data["invoice"]["tax"]) . '</td>
                        <td style="text-align:center; font-weight:bold; padding: 5px;">Total Amount</td>
                        <td style="border:1px solid #1367A4; width:15%; padding: 5px;">Card/CHQ</td>
                        <td style="border:1px solid #1367A4; padding: 5px;"></td>
                        <td style="border:1px solid #1367A4; width:15%; padding: 5px;"></td>
                    </tr>';

                // Add Previous Balance row if needed
                if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
                    if (!$hide_balance) {
                        // Balance calculation logic
                    }
                }
                if (!$hide_balance) {
                    $invbalance = 0;
                    if ($cust_balance_inv >= $data['invoice']['total']) {
                        $invbalance = $cust_balance_inv - $data['invoice']['total'];
                        $previous_balance = substr(amountExchange($invbalance, $data['invoice']['multi'], $data['invoice']['loc']), 3, 100);
                    } else {
                        $previous_balance = substr(amountExchange($cust_balance_inv, $data['invoice']['multi'], $data['invoice']['loc']), 3, 100);
                    }
                    $balance_now_due = $cust_balance_inv;

                    $header .= '<tr>
                            <td style="border:1px solid #1367A4; width:20%; padding: 5px;">Previous Balance</td>
                            <td style="border:1px solid #1367A4; width:20%; padding: 5px;">' . amountExchange($previous_balance) . '</td>
                            <td style="text-align:center; font-weight:bold; padding: 5px;">' . amountExchange($data["invoice"]["total"]) . '</td>
                            <td style="border:1px solid #1367A4; padding: 5px; width:15%;">BACS</td>
                            <td style="border:1px solid #1367A4; padding: 5px;"></td>
                            <td style="border:1px solid #1367A4; padding: 5px; width:15%;"></td>
                        </tr>';
                    $header .= '<tr>
                            <td style="border:1px solid #1367A4; width:20%; padding: 5px;">Balance Now Due</td>
                            <td style="border:1px solid #1367A4; width:20%; padding: 5px;">' . amountExchange($balance_now_due) . '</td>
                            <td style="border-left:1px solid #1367A4; padding: 5px;"></td>
                            <td style="border:1px solid #1367A4; padding: 5px; width:15%;">Total</td>
                            <td style="border:1px solid #1367A4; padding: 5px;"></td>
                            <td style="border:1px solid #1367A4; padding: 5px; width:15%;"></td>
                        </tr>';
                } else {
                    $header .= '<tr>
                            <td style="padding: 15px; width:20%;"></td>
                            <td style="padding: 15px; width:20%;"></td>
                            <td style="border-left:1px solid #1367A4; text-align:center; font-weight:bold; padding: 5px;">' . amountExchange($invbalance) . '</td>
                            <td style="border:1px solid #1367A4; padding: 5px; width:15%;">BACS</td>
                            <td style="border:1px solid #1367A4; padding: 5px;"></td>
                            <td style="border:1px solid #1367A4; padding: 5px; width:15%;"></td>
                        </tr>';
                    $header .= '<tr>
                            <td style="padding: 15px; width:20%;"></td>
                            <td style="padding: 15px; width:20%;"></td>
                            <td style="border:1px solid #1367A4; border-top:none; padding: 5px;"></td>
                            <td style="border:1px solid #1367A4; padding: 5px; width:15%;">Total</td>
                            <td style="border:1px solid #1367A4; padding: 5px;"></td>
                            <td style="border:1px solid #1367A4; padding: 5px; width:15%;"></td>
                        </tr>';
                }

                $header .= '</table>';


                $header .= '<div style=" font-size: 10px; text-align:center;">
                All goods remain the property of Exel Foods Ltd, unless full payment is received. Please note drivers will not accept more than
                £40 in coins(£1&£2 coins only).All Shortages/damages must be clearly shown on both copies of the invoice.
                All payments will be allocated to the oldest outstanding balances first.
                </div>';
                // $header .= '
                // <span style="color:rgb(48, 37, 133);font-size: 12px;text-align:center;"><b>*MAXIMUM CHANGE ACCEPTED=&#163;60</b> </span>
                // <div style=" font-size: 12px;">
                // *All claims must be notified at the time of delivery,
                // any claims made after delivery will not be considered. Goods will remain
                // the property of ' . $company['cname'] . ' until paid in full by the customer.Frozen and  products cannot be returned once delivered.
                // </div>';
                $header .= '<div style="clear: both;"></div>';
                $header .= '</div>'; // End of main div


                // Add the remaining content
            }



            // $sub_ts = 0;
            // foreach ($data['products'] as $row) {
            //     $sub_ts += $row['price'] * $row['qty'];
            // }

            $header .= '<div style="float:right; margin-left:530px; font-size: 14px;">
            <table class="maintable2" style="border: 1px solid #1367A4; border-collapse: collapse; width: 100%;">';

            // Add "Office Use Only" header row
            $header .= '<tr>
                        <th style="border: 1px solid #1367A4; text-align: center; padding: 8px;" colspan="2">&nbsp;Office Use Only</th>
                    </tr>';

            // Add row for "Updated"
            $header .= '<tr>
                        <td style="width: 50%; border: 1px solid #1367A4; padding: 4px 0px 4px 7px;">Updated</td>
                        <td style="width: 50%; border: 1px solid #1367A4; padding: 4px 0px 4px 7px;"></td>
                    </tr>';

            // Add row for "Payment Allocated"
            $header .= '<tr>
                        <td style="width: 50%; border: 1px solid #1367A4; padding: 0px 0px 0px 7px;">Payment Allocated</td>
                        <td style="width: 50%; border: 1px solid #1367A4; padding: 0px 0px 0px 7px;"></td>
                    </tr>';

            // Add an empty row
            $header .= '<tr>
                        <td style="padding: 4px 0px 4px 7px; border: 1px solid #1367A4; border-right: none;">&nbsp;</td>
                        <td></td>
                    </tr>';

            // Additional logic based on invoice type and company
            if ($data['invoice']['inv_type'] == 'INVOICE' || $data['invoice']['inv_type'] == 'DAYPASS' && $data['invoice']['company'] != 'COUNTER SALE') {
                // If there's additional balance logic, it would go here
            }

            $header .= '</table>

            <span style="font-size:12px; text-align: center; href="mailto:' . $company['email'] . '">
            <img src="' . base_url("assets/images/mail.jpg") . '" height="30px" width="35px" style="margin-top:8px;">  </span>' . $company['email'] . '
            <br><span style="font-size:12px; text-align: center; margin-left:10px;">Thank you for your business!</span>
            </div>
            <div style="clear: both;"></div>';
            if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            }
            $header .= ' </footer></body></html>';
        } else {


            $header .= '<footer style="position:fixed;bottom:-100px;">';
            //if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE' ){
            $header .= '<div style="border:1px solid #1367A4;width:50%;height:140px; padding: 8px 0px 5px 4px; border-radius: 6px;float:left;margin-top:5px;display:none">
            <span style="color:rgb(48, 37, 133) font-size: 12px;"><b>*MAXIMUM CHANGE ACCEPTED=&#163;25</b></span><div style=" font-size: 12px;">All goods remain the property of ' . $company['cname'] . ' 
            until all monies are paid in full<br>Any complaints should be made within 48 hours of purchasing.</div><br><div style="width:100%"><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left">Print Name:</div><div style="border:1px solid black;width:10%;height:18px;float:left;margin-left:10px;;margin-left:-78px;"></div><div style="float:left;margin-left:-158px;margin-top:3px;font-size: 12px;"><b>Cash Tendered</b></div><div style="clear:both"></div><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left;">Signature:</div><div style="font-size: 17px;padding-left:10px;border:1px solid black;width:35%;height:20px;float:left;padding-top:3px;padding-bottom:3px;margin-left:-157px;margin-top:-8px">&#163;</div><div style="clear:both"></div></div><div style="clear:both"></div></div>';
            //}

            // $sub_ts = 0;
            // foreach ($data['products'] as $row) {
            //     $sub_ts += $row['price'] * $row['qty'];
            // }

            $header .= '<div style="float:right;margin-left:320px;width:280px" ><table class="maintable2" style="width:100%; border:1px solid #1367A4;font-size: 14px;">';


            //  $inv_total_amt=$data["invoice"]["total"]+$data["invoice"]["tax"];

            $inv_total_amt = $data["invoice"]["total"];
            $sub_ts = formatDecimal($data["invoice"]["subtotal"]);

            $header .= '<tr><td style="width: 60%;  background: #f1f4fb;padding: 4px 0px 4px 7px;">&nbsp;Total NET Amount</td><td> ' . amountExchange($data["invoice"]["subtotal"]) . ' </td></tr>
            <tr ><td style="width: 60%;  background: #f1f4fb;padding: 4px 0px 4px 7px;">&nbsp;Total ' . $this->lang->line('Tax') . ' Amount</td><td> ' . amountExchange($data["invoice"]["tax"])
                . '  </td></tr><tr><td style="width: 60%;  background: #f1f4fb;padding: 4px 0px 4px 7px;">&nbsp;
             Invoice Total</td><td> ' . amountExchange($data["invoice"]["total"]) . '   </td></tr>';

            $header .= '</table></div><div style="clear:both"></div>';

            //if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE' ){ 
            $header .= '<p style="font-size:8px;display:none"> ' . $company['cname'] . ' 
            is the trading name of ' . $company['cname'] . ', Company No. 11671004. &nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp; Bank Account Details: Sort Code: ' . $company['sortcode'] . ' A/C No: '
                . $company['taxid'] . '</p>';
            //  } 
            $header .= ' </footer></body></html>';
        }

        // $file_name = preg_replace('/[^A-Za-z0-9]+/', '-', 'Invoice__' . $data['invoice']['name'] . '_' . $data['invoice']['tid']);
        // $tid = $id;
        // if($tid == -1) {
        //     $tid = $this->input->get('id');
        // }
        $file_name = $tid;
        $this->dpdf->loadHtml($header);
        $this->dpdf->render();
        $output =  $this->dpdf->output();
        $file_location = 'userfiles/invoices/' . $file_name . '.pdf';
        file_put_contents($file_location, $output);
        if ($printing) {
            $this->dpdf->stream("" . $file_name . ".pdf", array("Attachment" => 0));
        }
    }
    public function printinvoice8($printing = true, $id = -1)
    {
        // Explicitly load language file for PDF generation
        $system_lang = $this->db->query("SELECT lang FROM geopos_system WHERE id=1 LIMIT 1")->row_array();
        $lang_to_load = $system_lang['lang'] ?? 'english';
        $this->lang->load($lang_to_load, $lang_to_load);
        $dynamic_tax_name = getTaxName();
        $that = $this;
        $lang = function ($key) use ($that, $dynamic_tax_name) {
            $text = $that->lang->line($key);
            if ($text === FALSE || empty($text)) return $key;
            return str_replace(array('{tax}', '{Tax}', '{TAX}'), array(strtolower($dynamic_tax_name), ucfirst(strtolower($dynamic_tax_name)), strtoupper($dynamic_tax_name)), $text);
        };
        $company = $this->settings->company_details(1);

        $productCounter = 0;
        $productsPerPage = 18;

        $hide_balance = 0;
        $tid = $id;
        if ($tid == -1) {
            $tid = $this->input->get('id');
            $hide_balance = ($this->input->get('hide_balance') == 1);
        }
        $data['id'] = $tid;
        $data['invoice'] = $this->invocies->invoice_details($tid, $this->limited);
        $data['products'] = $this->invocies->invoice_products($tid);
        $data['employee'] = $this->invocies->employee($data['invoice']['eid']);
        if ($data['invoice']['i_class'] == 1) {
            $pref = prefix(7);
        } else {
            $pref = $this->config->item('prefix');
        }
        ob_end_clean();
        ob_start();

        // mark printed
        if ($no_print == 1) {
            $sql = 'Update geopos_invoices_bfr_post set print_status=1 where id= ' . $tid . ' ';
            $this->db->query($sql);
        }

        $left_logo = base_url('userfiles/company/' . $company['logo']);
        $invoice = $data['invoice'];

        // Prepare customer billing information
        $bill_to_lines = '';
        // Company name in bold (if provided)
        if (!empty($invoice['company'])) {
            $bill_to_lines .= '<strong>' . htmlspecialchars($invoice['company']) . '</strong><br>';
        }
        // Address block (line 1, city, postcode)
        $address_line_1 = trim((string)($invoice['cust_address'] ?? ''));
        $address_line_2 = trim((string)($invoice['cust_city'] ?? ''));
        $postcode_value = trim((string)($invoice['cust_postcode'] ?? ''));
        if ($address_line_1 !== '') {
            $bill_to_lines .= htmlspecialchars($address_line_1);
            if ($address_line_2 !== '') {
                $bill_to_lines .= ', ' . htmlspecialchars($address_line_2);
            }
            if ($postcode_value !== '') {
                $bill_to_lines .= '<br>' . htmlspecialchars($postcode_value);
            }
        }

        // Sales Tax and NTN lines: always show; use real values if available, else use placeholders
        $sales_tax_value = '';
        foreach (['sales_tax', 'sales_tax_no', 'gst', 'vat_no', 'strn', 'stn'] as $key) {
            if (!empty($invoice[$key])) {
                $sales_tax_value = (string)$invoice[$key];
                break;
            }
        }
        $ntn_value = '';
        foreach (['ntn', 'ntn_no', 'taxid'] as $key) {
            if (!empty($invoice[$key])) {
                $ntn_value = (string)$invoice[$key];
                break;
            }
        }
        if ($sales_tax_value === '') {
            $sales_tax_value = '32-77-8761-291-58';
        }
        if ($ntn_value === '') {
            $ntn_value = '7222516-7';
        }

        $bill_to_lines .= '<br><span class="tax-info">Sales Tax # ' . htmlspecialchars($sales_tax_value) . '</span>';
        $bill_to_lines .= '<br><span class="tax-info">NTN # ' . htmlspecialchars($ntn_value) . '</span>';

        $reference_no = isset($invoice['refer']) ? $invoice['refer'] : '';

        // Use database values instead of calculating totals
        $subtotal = $data['invoice']['subtotal'];
        $total_tax = $data['invoice']['tax'];
        $grand_total = $data['invoice']['total'];

        // Start HTML structure with CSS
        $html = '<html><head><style>
            * { font-family: "Open Sans", sans-serif; }
            body { font-size: 8pt; color: #333333; }
            /* Page margins - A4 Portrait: Top=0.7in, Bottom=0.7in, Left=0.55in, Right=0.4in */
            @page { margin: 0.7in 0.4in 0.7in 0.55in; }
            table { width: 100%; border-collapse: collapse; }
            /* Footer - Page Number on Right, Font Size 6pt, Color #aaaaaa */
            footer.doc-footer { position: fixed; bottom: -0.7in; left: 0; right: 0; height: 0.4in; text-align: right; padding: 0 20px; color: #aaaaaa; font-size: 6pt; background: #ffffff; }
            footer.doc-footer .page-count:after { content: "Page " counter(page) " of " counter(pages); }
            /* Header content styling */
            .page-header { margin-bottom: 10px; }
            .doc-meta { margin-top: 10px; margin-bottom: 20px; }
            .doc-meta .title-row { width:100%; margin: 6px 0 2px; }
            .doc-meta .title-cell { width:20%; text-align:center; font-weight:200; }
            .muted { color:#666; font-size: 12px; }
            /* Customer details - Font Size 9pt, Color #333333 */
            .customer-details { font-size: 9pt; color: #333333; line-height:1.6; }
            .tax-info { font-size: 9pt; color: #333333; }
            .title { font-size: 11pt; letter-spacing: 1px; color: #333333; }
            .box { border:1px solid #ccc; border-radius:4px; padding:8px; }
            /* Items table - Border #d8d8d7 */
            .items { border: 1px solid #d8d8d7; margin-top: 0; }
            .items th, .items td { border: 1px solid #d8d8d7; padding: 6px; font-size: 8pt; color: #333333; }
            /* Table Header - Background #F5F6F7, Font Size 8pt, Color #333333 */
            .items thead th { background: #F5F6F7; color: #333333; font-size: 8pt; font-weight: 600; }
            /* Item Rows - Background #ffffff, Font Size 8pt, Color #333333 */
            .items tbody td { background: #ffffff; font-size: 8pt; color: #333333; }
            /* Compact meta table on the right to reduce label/value gap */
            .meta-table { width: auto; margin-left: auto; border-collapse: collapse; table-layout: auto; }
            .meta-table td { padding: 2px 6px; font-size: 9pt; color: #333333; }
            .meta-table td.value { text-align: left; white-space: nowrap; }
            /* Organization Name - Font Size 9pt, Color #333333 */
            .org-name { font-weight: 700; font-size: 9pt; color: #333333; }
            .org-details { font-size: 8pt; color: #333333; }
            /* Customer Name Label - Font Size 9pt, Color #333333 */
            .customer-label { font-weight: 700; font-size: 9pt; color: #333333; margin-bottom: 6px; }
            /* Totals section styling */
            .totals-box { 
                padding: 10px; 
                margin-top: 20px; 
                width: 300px; 
                float: right; 
                font-size: 8pt;
                color: #333333;
            }
            .total-row { 
                display: table; 
                width: 100%; 
                margin-bottom: 5px; 
            }
            .total-label { 
                display: table-cell; 
                font-weight: bold; 
                width: 60%; 
            }
            .total-value { 
                display: table-cell; 
                text-align: right; 
                font-weight: bold; 
            }
            .grand-total { 
                border-top: 1px solid #333; 
                padding-top: 5px; 
                margin-top: 5px; 
            }
            /* Page break utilities */
            .page-break { page-break-after: always; }
        </style></head><body>';

        // Store header HTML in a variable for reuse
        $header_html = '<div class="page-header">
            <table style="width:100%; margin-bottom:5px;"><tr>
                <td style="width:45%; vertical-align:top;">
                    <img src="' . $left_logo . '" height="80" style="vertical-align:top;">
                </td>
                <td style="width:55%; text-align:right; vertical-align:top;">
                    <div class="org-name">' . htmlspecialchars($company['cname']) . '</div>
                    <div class="org-details">
                    ' . htmlspecialchars($company['address']) . '<br>
                    ' . htmlspecialchars($company['city']) . ', ' . htmlspecialchars($company['country']) . '<br>
                    Mob: ' . htmlspecialchars($company['mobile']) . ' | Tel: ' . htmlspecialchars($company['phone']) . '<br>
                    Email: ' . htmlspecialchars($company['email']) . '<br>
                    Web: ' . htmlspecialchars($company['website'] ?? 'www.aaico.pk') . '<br>
                    <strong>NTN: ' . htmlspecialchars($ntn_value) . '</strong>
                    </div>
                </td>
            </tr></table>
            <div class="doc-meta">
                <table class="title-row">
                    <tr>
                        <td style="width:40%;"><hr style="margin:0; border:none; border-top:2px solid #dcdcdc;"></td>
                        <td class="title-cell"><span class="title">SALES TAX INVOICE</span></td>
                        <td style="width:40%;"><hr style="margin:0; border:none; border-top:2px solid #dcdcdc;"></td>
                    </tr>
                </table>
                <table style="margin-top:10px; width:100%; table-layout:fixed;">
                    <tr>
                        <td style="width:58%; vertical-align:top; padding-right:12px;">
                            <div style="padding:8px;">
                                <div class="customer-label">Principal</div>
                                <div class="customer-details">' . $bill_to_lines . '</div>
                            </div>
                        </td>
                        <td style="width:42%; vertical-align:top;">
                            <table class="meta-table">
                                <tr>
                                    <td style="font-weight:600;">Invoice No:</td>
                                    <td class="value">' . htmlspecialchars($invoice['tid']) . '</td>
                                </tr>
                                <tr>
                                    <td style="font-weight:600;">Invoice Date</td>
                                    <td class="value" style="white-space:nowrap;">' . date('d M Y', strtotime($invoice['invoicedate'])) . '</td>
                                </tr>
                                <tr>
                                    <td style="font-weight:600;">Due Date</td>
                                    <td class="value" style="white-space:nowrap;">' . date('d M Y', strtotime($invoice['invoicedate'] . ' +30 days')) . '</td>
                                </tr>
                                <tr>
                                    <td style="font-weight:600;">P.O. #</td>
                                    <td class="value" style="white-space:nowrap;">' . htmlspecialchars($reference_no) . '</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>
        </div>';

        // Add header for first page
        $html .= $header_html;

        // Start items table
        $html .= '<table class="items">
                <thead>
                    <tr>
                        <th style="width:6%; text-align:center;">' . $lang('#') . '</th>
                        <th style="text-align:left;">' . $lang('Item & Description') . '</th>
                        <th style="width:8%; text-align:center;">' . $lang('Quantity') . '</th>
                        <th style="width:12%; text-align:right;">' . $lang('Rate') . '</th>
                        <th style="width:13%; text-align:right;">' . $lang('Net Amount') . '</th>
                    </tr>
                </thead>
                <tbody>';

        $rownum = 0;
        $items_on_page = 0;

        foreach ($data['products'] as $row) {
            if ($row['qty'] > 0) {
                $rownum++;
                $items_on_page++;

                $item_name = !empty($row['product_des']) ? $row['product_des'] : ($row['product'] ?? '');
                $unit = isset($row['unit']) ? ' ' . $row['unit'] : '';
                $rate = formatDecimal($row['price']);
                $amount = formatDecimal($row['qty'] * $row['price']);

                $html .= '<tr>'
                    . '<td style="text-align:center;">' . $rownum . '</td>'
                    . '<td style="text-align:left;">' . $row["product_des"] . '</td>'
                    . '<td style="text-align:center;">' . (int)$row["qty"] . $row["unit"] . '</td>'
                    . '<td style="text-align:right;">' . rev_amountExchange_s($row['price']) . '</td>'
                    . '<td style="text-align:right;">' . rev_amountExchange_s($row['subtotal']) . '</td>'
                    . '</tr>';

                // Force a page break after 15 rows
                if ($items_on_page == 15) {
                    $html .= '</tbody></table>';
                    $html .= '<div class="page-break"></div>';

                    // Add header for next page
                    $html .= $header_html;

                    // Restart items table
                    $html .= '<table class="items">
                        <thead>
                            <tr>
                                <th style="width:6%; text-align:center;">' . $lang('#') . '</th>
                                <th style="text-align:left;">' . $lang('Item & Description') . '</th>
                                <th style="width:8%; text-align:center;">' . $lang('Quantity') . '</th>
                                <th style="width:12%; text-align:right;">' . $lang('Rate') . '</th>
                                <th style="width:13%; text-align:right;">' . $lang('Net Amount') . '</th>
                            </tr>
                        </thead>
                        <tbody>';

                    $items_on_page = 0; // Reset counter for next page
                }
            }
        }

        if ($rownum === 0) {
            $html .= '<tr><td colspan="5" style="text-align:center; color:#777;">No items found</td></tr>';
        }

        $html .= '</tbody></table>';

        // ==================== TOTALS AND FOOTER SECTIONS ====================
        $html .= '<div style="margin-top:20px; font-size:8pt; color:#333333; clear:both;">Thanks for your business.</div>';

        $html .= '<div class="totals-box">
            <div class="total-row">
                <div class="total-label">' . $lang('Sub Total') . '</div>
                <div class="total-value">' . amountExchange($data['invoice']['subtotal']) . ' </div>
            </div>
            <div class="total-row">
                <div class="total-label">' . $lang('Total Taxable Amount') . '</div>
                <div class="total-value">'.amountExchange($data['invoice']['subtotal']) . ' </div>
            </div>';

        if ($data['invoice']['tax'] > 0) {
            $html .= '<div class="total-row">
                <div class="total-label">' . $lang('Total Tax Amount') . '</div>
                <div class="total-value">'.amountExchange($data['invoice']['tax']) . '</div>
            </div>';
        }

        $html .= '<div class="total-row grand-total">
                    <div class="total-label">' . $lang('Grand Total') . '</div>
                    <div class="total-value">' . amountExchange($data["invoice"]["total"]) . '</div>
                </div>
                <div class="total-row">
                    <div class="total-label">Payment Made</div>
                    <div class="total-value">(-) ' . amountExchange($data["invoice"]["pamnt"]) . '</div>
                </div>
                <div class="total-row grand-total">
                    <div class="total-label">Balance Due</div>
                    <div class="total-value">' . amountExchange($data["invoice"]["total"] - $data["invoice"]["pamnt"]) . '</div>
                </div>
            </div>';

        // Footer with page numbers
        $html .= '<footer class="doc-footer"><span class="page-count"></span></footer>';

        $html .= '</body></html>';

        // Generate PDF
        $file_name = $tid;
        $this->dpdf->loadHtml($html);
        $this->dpdf->render();
        $output = $this->dpdf->output();
        $file_location = 'userfiles/invoices/sales_tax_invoice_' . $file_name . '.pdf';
        file_put_contents($file_location, $output);

        if ($printing) {
            $this->dpdf->stream("sales_tax_invoice_" . $file_name . ".pdf", array("Attachment" => 0));
        }
    }
    public function printinvoice9($printing = true, $id = -1)
    {

        // Explicitly load language file for PDF generation
        $system_lang = $this->db->query("SELECT lang FROM geopos_system WHERE id=1 LIMIT 1")->row_array();
        $lang_to_load = $system_lang['lang'] ?? 'english';
        $this->lang->load($lang_to_load, $lang_to_load);

        // Get dynamic tax name for placeholder replacement
        $dynamic_tax_name = getTaxName();

        // Helper function to get and replace language lines
        $that = $this; // Store controller reference for closure
        $lang = function ($key) use ($that, $dynamic_tax_name) {
            $text = $that->lang->line($key);
            if ($text === FALSE || empty($text)) {
                return $key; // Return key if translation not found
            }
            // Replace tax placeholders
            return str_replace(
                array('{tax}', '{Tax}', '{TAX}'),
                array(strtolower($dynamic_tax_name), ucfirst(strtolower($dynamic_tax_name)), strtoupper($dynamic_tax_name)),
                $text
            );
        };

        $company = $this->settings->company_details(1);

        $tid = $this->input->get('id');

        $no_print = $this->input->get('no_print');
        if ($no_print) {
            $no_print = $no_print;
        }
        $data['id'] = $tid;
        $data['invoice'] = $this->invocies->invoice_details($tid, $this->limited);
        $data['products'] = $this->invocies->invoice_products($tid);
        $data['employee'] = $this->invocies->employee($data['invoice']['eid']);
        if ($data['invoice']['i_class'] == 1) {
            $pref = prefix(7);
        } else {
            $pref = $this->config->item('prefix');
        }
        //print_r($data['invoice']['csd']);die();
        //var_dump($data['products']); exit; 
        ob_end_clean();
        ob_start();

        $sql = 'SELECT sum(total)-sum(pamnt) as "cust_balance" FROM geopos_invoices WHERE status="due" and csd="' . $data["invoice"]["csd"] . '" or status="partial" and csd="' . $data["invoice"]["csd"] . '"';
        $query = $this->db->query($sql);
        $response = $query->result_array();
        $cust_balance_inv = $response[0]['cust_balance'];

        // $cust_balance_inv= $response[0]['cust_balance'];
        $sql2 = "SELECT content FROM `geopos_notes` ORDER BY `geopos_notes`.`id`  DESC LIMIT 1";
        $query2 = $this->db->query($sql2);
        $response2 = $query2->result_array();
        $content2 = $response2[0]['content'];

        $sql2 = 'SELECT data FROM `geopos_custom_data` where rid="' . $data["invoice"]["csd"] . '"';
        $query2 = $this->db->query($sql2);
        $response2 = $query2->result_array();
        $custom_fields = $response2[0]['data'];
        if ($no_print == 1) {
            $sql = 'Update geopos_invoices set print_status=1 where id= ' . $tid . ' ';
            $this->db->query($sql);
        }

        ini_set('memory_limit', '64M');

        $data_tr = "";

        $left_logo = base_url("userfiles/company/" . $company['logo']);
        $data['left_logo'] = $left_logo;
        $right_logo = base_url("assets/images/halal_logo.jpg");
        $data['right_logo'] = $right_logo;
        ob_end_clean();

        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            $header = '<html><head><style>
            @page { 
                margin: 350px 50px 200px; 
            }
            header{ 
                position: fixed;  
                top: -320px;
            }
            footer {
                position: fixed;
                bottom: -150px;
                width: 100%;
            }
            table{
                width:100%;
            }
            .pagenum:after {
                content: counter(page);
            }
            .page-break {
                page-break-before: always;
            }
            .items-table {
                width: 100%;
                border: 1px solid #1367A4;
                border-radius: 6px;
                border-collapse: collapse;
            }
            .items-table thead th {
                font-size: 12px;
                background: #ccc;
                padding: 4px;
            }
            .items-table tbody tr {
                border-bottom: 1px solid white;
            }
            </style></head><body><header>';

            $header .= '<table style="width:100%; border-collapse: collapse;">
                        <tr>
                       <!-- LEFT SIDE: Logo, Company Box, Customer Box -->
                        <td style="width:55%; vertical-align:top;">
                            <!-- Company Logo -->
                            <div style="text-align:center;">
                                <img src="' . $left_logo . '" height="165px" width="230px" style="display:block;">
                            </div>

                            <!-- SALES INVOICE Heading -->
                            <div style="font-size:22px; font-weight:bold; color:#1367A4; text-align:center; margin-bottom:4px;">
                                SALES INVOICE
                            </div>

                            <!-- Box Wrapper to control width -->
                            <div style="width:99%; text-align:left;">

                            
                <!-- Company Info Box -->
                <div style="border:1px solid #1367A4; font-size:13px; padding:0px 4px; border-radius:6px; margin-bottom:15px; text-align:center ; margin-top:-2px">
                    <b>' . $company['cname'] . '</b><br>
                    ' . $company['address'] . ' ' . $company['city'] . ' ' . $company['region'] . ', ' . $company['postbox'] . ', ' . $company['country'] . ' 
                    <br>
                    <table style="width:100%; font-size:13px; margin-top:2px; border-collapse:collapse;">
                    <tr>
                    <td style="text-align:left; white-space:nowrap;"><b>Phone:</b></td>
                    <td style="text-align:left;">' . $company['phone'] . '</td>
                </tr>
                <tr>
                    <td style="text-align:left; white-space:nowrap;"><b>Mobile:</b></td>
                    <td style="text-align:left;">' . $company['mobile'] . '</td>
                </tr>

                    </table>
                </div>





            <!-- Customer "Bill To" Box -->
            <div style="border:1px solid #1367A4; font-size:13px; padding:1px 4px; border-radius:6px; background-color:#f9f9f9; margin-top:-12px">
                <table style="width:100%; font-size:13px; border-collapse:collapse;">
                    <tr>
                        <td style="font-weight:bold; width:16%; vertical-align:top;">Bill To:</td>
                        <td style="text-align:left; font-size:11px">' . ($data['invoice']['company'] ? $data['invoice']['company'] : '&nbsp;') . '</td>
                    </tr>
                    <tr>
                        <td style="font-weight:bold; width:16%; vertical-align:top;">Address:</td>
                        <td style="text-align:left;font-size:11px ; word-wrap:break-word;">' . $data['invoice']['cust_address'] . ' ' . $data['invoice']['cust_city'] . ' ' . $data['invoice']['cust_postcode'] . '</td>
                    </tr>';


            $header .= '</table></div>
                                            </div>
                                        </td>';
            // Right side - Invoice and Bank Details with Special Notes
            $header .= '
                                    <td style="width:55%; vertical-align:top; padding:5px;">
                                        <!-- Top Section: Logo + Invoice Details -->
                                        <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 4px;">
                                        <!-- Right Logo (Floated to extreme right) -->
                                            <div style="position: absolute; top: 0; right: 0;">
                                                <img src="' . $right_logo . '" height="45px" width="45px" style="margin: 0;">
                                            </div>
                                            <!-- Left: Invoice Details Table -->
                                            <div style="width:85%;">
                                                <table style="width: 100%; border-collapse: collapse;">
                                                    <tr>
                                                        <th style="border: 1px solid #1367A4; padding: 3px 6px; text-align: center; font-size:11px; font-weight:bold;">Date:</th>
                                                        <td style="border: 1px solid #1367A4; padding: 3px 6px; text-align: center; font-size:11px;">' . date("d-m-Y", strtotime($data["invoice"]["invoicedate"])) . '</td>
                                                    </tr>
                                                    <tr>
                                                        <th style="border: 1px solid #1367A4; padding: 3px 6px; text-align: center; font-size:11px; font-weight:bold;">A/C:</th>
                                                        <td style="border: 1px solid #1367A4; padding: 3px 6px; text-align: center; font-size:11px;">' . $data["invoice"]["name"] . '</td>
                                                    </tr>
                                                    <tr>
                                                        <th style="border: 1px solid #1367A4; padding: 3px 6px; text-align: center; font-size:11px; font-weight:bold;">Invoice#</th>
                                                        <td style="border: 1px solid #1367A4; padding: 3px 6px; text-align: center; font-size:11px;">' . $data['invoice']['tid'] . '</td>
                                                    </tr>
                                                    <tr>
                                                        <th style="border: 1px solid #1367A4; padding: 3px 6px; text-align: center; font-size:11px; font-weight:bold;">Company#</th>
                                                        <td style="border: 1px solid #1367A4; padding: 3px 6px; text-align: center; font-size:11px;">12070999</td>
                                                    </tr>
                                                    <tr>
                                                        <th style="border: 1px solid #1367A4; padding: 3px 6px; text-align: center; font-size:11px; font-weight:bold;">Vat Reg</th>
                                                        <td style="border: 1px solid #1367A4; padding: 3px 6px; text-align: center; font-size:11px;">497981705</td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>

                                        <!-- Bank Details Table -->
                                        <table style="width: 100%; border-collapse: collapse; margin-bottom:4px;">
                                            <tr>
                                                <th style="border: 1px solid #1367A4; padding: 3px 6px; text-align: center; font-size:11px; font-weight:bold;">Bank Name</th>
                                                <td style="border: 1px solid #1367A4; padding: 3px 6px; text-align: center; font-size:11px;">Lyods</td>
                                            </tr>
                                            <tr>
                                                <th style="border: 1px solid #1367A4; padding: 3px 6px; text-align: center; font-size:11px; font-weight:bold;">Account Name</th>
                                                <td style="border: 1px solid #1367A4; padding: 3px 6px; text-align: center; font-size:11px;">' . $company['cname'] . '</td>
                                            </tr>
                                            <tr>
                                                <th style="border: 1px solid #1367A4; padding: 3px 6px; text-align: center; font-size:11px; font-weight:bold;">Account Number</th>
                                                <td style="border: 1px solid #1367A4; padding: 3px 6px; text-align: center; font-size:11px;">' . $company['taxid'] . '</td>
                                            </tr>
                                            <tr>
                                                <th style="border: 1px solid #1367A4; padding: 3px 6px; text-align: center; font-size:11px; font-weight:bold;">Sort Code</th>
                                                <td style="border: 1px solid #1367A4; padding: 3px 6px; text-align: center; font-size:11px;">' . $company['sortcode'] . '</td>
                                            </tr>
                                        </table>

                                        <!-- Special Notes -->
                                        <div style="border:1px solid #1367A4; font-size:13px; padding:5px 8px; border-radius:4px; background-color:#f0f8ff;">
                                            <strong>Special Note:</strong><br>
                                            <span style="font-size:11px;">KINGSLEY GB DRINKS 330ML CAN ON PROMOTION</span><br>
                                            <span style="color:#ff6600; font-size:11px;">COLA &nbsp;&nbsp; CHERRYADE &nbsp;&nbsp; LEMONADE &nbsp;&nbsp; ORANGEAD</span><br>
                                            <strong style="font-size:11px;">ALL ARE EXCLUSIVELY 5.49+ VAT</strong>
                                        </div>
                                        <!-- Payment Terms Box -->
                                        <div style="border:1px solid #1367A4; font-size:13px; padding:10px; border-radius:6px; background-color:#f9f9f9; margin-top:5px;">
                                        <!-- Add TERMS row if applicable -->
                                            <table style="width:100%; font-size:13px;">
                                            <tr>
                                                <td style="font-weight:bold; color:#cc0000;">Payment Terms:</td>
                                                <td style="text-align:right;"><span style="background-color:yellow;">' . $custom_fields . '</span></td>
                                            </tr></table>
                                            </div>
                                    </td>';

            $header .= '</tr></table></header>';
        } else {
            $header = '<html><head><style>
            @page { 
                margin: 350px 50px 200px; 
            }
            header{ 
                position: fixed;  
                top: -320px;
            }
            footer {
                position: fixed;
                bottom: -150px;
                width: 100%;
            }
            table{
                width:100%;
            }
            .pagenum:after {
                content: counter(page);
            }
            .page-break {
                page-break-before: always;
            }
            .items-table {
                width: 100%;
                border: 1px solid #1367A4;
                border-radius: 6px;
                border-collapse: collapse;
            }
            .items-table thead th {
                font-size: 14px;
                background: #ccc;
                padding: 4px;
            }
            .items-table tbody tr {
                border-bottom: 1px solid white;
            }
            </style></head><body><header><span style="float:right;" class="pagenum">Page:</span>';

            // Main header table
            $header .= '<table style="width:100%; border-collapse: collapse;">
                    <tr>
                        <!-- Left side - Cash Format Title and Special Notes -->
                        <td style="width:50%; vertical-align:top; padding:5px;">
                            <!-- Cash Format Header -->
                            <div style="border:2px solid #000; font-size:20px; font-weight:bold; padding:10px; text-align:center; background-color:#f0f0f0; margin-top:40px; margin-bottom:80px;">
                                CASH FORMAT
                            </div>
                            
                            <!-- Special Notes Box -->
                             <div style="border:1px solid #1367A4; font-size:16px; padding:8px; border-radius:4px; background-color:#f0f8ff;">
                                <strong>Special Note:</strong><br>
                                <span style="font-size:11px;">KINGSLEY GB DRINKS 330ML CAN ON PROMOTION</span><br>
                                <span style="color:#ff6600; font-size:11px;">COLA &nbsp;&nbsp; CHERRYADE &nbsp;&nbsp; LEMONADE &nbsp;&nbsp; ORANGEAD</span><br>
                                <strong style="font-size:11px;">ALL ARE EXCLUSIVELY 5.49+ VAT</strong>
                            </div>
                        </td>
                        
                        <!-- Right side - Invoice Details -->
                        <td style="width:50%; vertical-align:top; padding:5px;">
                            <!-- Invoice Details Table -->
                            <table style="width:80%; border-collapse:collapse; border:1px solid #000; margin-top:24px;  margin-bottom:28px;">
                                <tr>
                                    <th style="border:1px solid #000; padding:4px; text-align:center; background-color:#e6f2ff; font-size:16px;">Date</th>
                                    <td style="border:1px solid #000; padding:4px; text-align:center; font-size:16px;">' . date("d/m/Y", strtotime($data["invoice"]["invoicedate"])) . '</td>
                                </tr>
                                <tr>
                                    <th style="border:1px solid #000; padding:4px; text-align:center; background-color:#e6f2ff; font-size:16px;">A/C</th>
                                    <td style="border:1px solid #000; padding:4px; text-align:center; font-size:16px;">XYZ</td>
                                </tr>
                                <tr>
                                    <th style="border:1px solid #000; padding:4px; text-align:center; background-color:#e6f2ff; font-size:16px;">CSH#</th>
                                    <td style="border:1px solid #000; padding:4px; text-align:center; font-size:16px;">#' . $data['invoice']['tid'] . '</td>
                                </tr>
                            </table>
                                <!-- Customer "Bill To" Box -->
                                <div style="border:1px solid #1367A4; font-size:13px; padding:0px 10px; margin-top:24px; margin-bottom:8px; background-color:#f9f9f9;">
                                    <table style="width:100%; font-size:13px;">
                                        <tr>
                                            <td style="font-weight:bold; width:80px;">Bill To:</td>
                                            <td style="text-align:right;">' . ($data['invoice']['company'] ? $data['invoice']['company'] : '&nbsp;sajawal') . '</td>
                                        </tr>
                                        <tr>
                                            <td style="font-weight:bold;">Address:</td>
                                            <td style="text-align:right;">' . $data['invoice']['cust_address'] . ' ' . $data['invoice']['cust_city'] . ' ' . $data['invoice']['cust_postcode'] . '</td>
                                        </tr>
                                    </table>
                                </div>
                            
                           <!-- Payment Terms Box -->
                                        <div style="border:1px solid #1367A4; font-size:13px; padding:10px; margin-bottom:8px; background-color:#f9f9f9;">
                                            <table style="width:100%; font-size:13px;">
                                                <tr>
                                                    <td style="font-weight:bold; color:#cc0000;">Payment Terms:</td>
                                                    <td style="text-align:right;"><span style="background-color:yellow;">' . $custom_fields . '</span></td>
                                                </tr>
                                            </table>
                                        </div>
             
                            
                            <!-- Invalid Pattern Notice -->
                            <div style="border:2px solid #000; font-size:12px; font-weight:bold; padding:6px; text-align:center; background-color:#ffcccc;">
                                INVALID PATTERN NOT FOR OFFICIAL USE
                            </div>
                        </td>
                    </tr>
                </table></header>';
        }

        // Modified product section with pagination
        $header .= '<main>';

        // Group products into pages of 15 items each
        $items_per_page = 20;
        $filtered_products = [];

        // Filter products with qty > 0
        foreach ($data['products'] as $row) {
            if ($row['qty'] > 0 && (!empty($row['product_des']) && $row['product_des'] != "")) {
                $filtered_products[] = $row;
            }
        }

        $total_items = count($filtered_products);
        $total_pages = ceil($total_items / $items_per_page);

        for ($page = 0; $page < $total_pages; $page++) {
            $start_index = $page * $items_per_page;
            $end_index = min($start_index + $items_per_page, $total_items);

            // Add page break for pages after the first one
            if ($page > 0) {
                $header .= '<div class="page-break"></div>';
            }
            $header .= '<div style="width:100%; margin-top:10px;">
            <table class="items-table" style="width:100%; border-collapse:collapse;">
                <thead style="background:#f0f0f0;">
                    <tr>
                        <th style="width: 12%; text-align:center; padding:6px;">' . $lang('Quantity') . '</th>
                        <th style="border-left: 1px solid #1367A4; width: 45%; padding:6px; text-align:left;">' . $lang('Details') . '</th>
                        <th style="width: 13%; text-align:center; border-left: 1px solid #1367A4; padding:6px;">' . $lang('Unit Price') . '</th>
                        <th style="width: 20%; text-align:center; border-left: 1px solid #1367A4; padding:6px;">' . $lang('Net Amount') . '</th>
                        <th style="width: 10%; text-align:center; border-left: 1px solid #1367A4; padding:6px;">' . $lang('Tax') . '</th>
                    </tr>
                </thead>
                <tbody style="font-size:15px;">
        ';

            for ($i = $start_index; $i < $end_index; $i++) {
                $row = $filtered_products[$i];

                $row_style = ($row['serial'] == '1') ? 'background:#FBF36D;' : '';

                $header .= '<tr style="' . $row_style . ' border-bottom:1px solid white;">
                <td style="border-bottom:1px solid white; text-align:center;">' . (int)$row["qty"] . $row["unit"] . '</td>
                <td style="border-bottom:1px solid white; text-align:left;">' . $row["product_des"] . '</td>
                <td style="border-bottom:1px solid white; text-align:center;">' . amountExchange_s($row['price']) . '</td>
                <td style="border-bottom:1px solid white; text-align:center;">' . amountExchange_s($row['subtotal']) . '</td>
                <td style="border-bottom:1px solid white; text-align:center;">' . amountExchange_s($row['totaltax']) . '</td>
            </tr>';
            }

            // Fill remaining rows with empty cells if this is the last page and has fewer than 15 items
            $current_page_items = $end_index - $start_index;
            if ($page == $total_pages - 1 && $current_page_items < $items_per_page) {
                $empty_rows = $items_per_page - $current_page_items;
                for ($j = 0; $j < $empty_rows; $j++) {
                    $header .= '<tr style="height: 25px;">
                        <td style="border-bottom:1px solid white;">&nbsp;</td>
                        <td style="border-bottom:1px solid white;">&nbsp;</td>
                        <td style="border-bottom:1px solid white;">&nbsp;</td>
                        <td style="border-bottom:1px solid white;">&nbsp;</td>
                        <td style="border-bottom:1px solid white;">&nbsp;</td>
                        </tr>';
                }
            }

            $header .= '</tbody></table></div>';

            // Add page number indicator
            if ($total_pages > 1) {
                $header .= '<div style="text-align: right; margin-top: 10px; font-size: 12px;">
                    Page ' . ($page + 1) . ' of ' . $total_pages . '
                    </div>';
            }
        }

        $header .= '</main>';

        // Footer Section
        $footer = '<footer>';
        if ($data['invoice']['inv_type'] == 'INVOICE') {
            $footer .= '
                        <table style="width:100%; border-collapse:collapse; margin-top:-170px;">
                            <tr>
                                <!-- Left Side: Notes + Customer -->
                                <td style="width:65%; vertical-align:top; padding:5px;">
                                    <div style="border:1px solid #1367A4; font-size:14px; padding:8px; border-radius:6px;">
                                        <strong style="color:rgb(48, 37, 133)">NOTE: *THE HIGHEST CHANGE ALLOWED IS.=£30</strong><br>
                                        Notification of any claims must be informed at the time of delivery.<br>
                                        Claims submitted after delivery won\'t be taken into account.<br>
                                        Frozen and chilled products cannot be returned once delivered.<br>
                                        ' . $company['cname'] . ' will retain ownership of the goods until the consumer has made full payment.<br><br>

                                    <table style="width:100%; border-collapse:collapse;">
                                            <tr>
                                                <!-- Left column: Customer & Driver (stacked) -->
                                                <td style="width:53%; vertical-align:top;">
                                                    <div style="border:1px solid #000; height:30px; border-radius:4px; padding-left:4px;"><strong>Customer:</strong></div>
                                                    <div style="border:1px solid #000; height:30px; border-radius:4px; margin-top:4px; padding-left:4px;"><strong>Driver:</strong></div>
                                                </td>
                                                <td></td>
                                                <!-- Right column: Payments & Amount -->
                                                <td style="width:45%; vertical-align:top;">
                                                    <!-- Payment methods (Cash / Card / BACS) -->
                                                    <table style="width:100%; border-collapse:collapse;">
                                                        <tr>
                                                            <td style="text-align:center;"><strong>Cash</strong></td>
                                                            <td style="text-align:center;"><strong>Card Payment</strong></td>
                                                            <td style="text-align:center;"><strong>BACS</strong></td>
                                                        </tr>
                                                        <tr>
                                                            <td><div style="border:1px solid #000; height:20px; border-radius:4px;"></div></td>
                                                            <td><div style="border:1px solid #000; height:20px; border-radius:4px;"></div></td>
                                                            <td><div style="border:1px solid #000; height:20px; border-radius:4px;"></div></td>
                                                        </tr>
                                                    </table>

                                                    <!-- Amount Box -->
                                                    <div style="border:1px solid #000; height:20px; border-radius:4px; margin-top:3px; padding-left:4px;">£</div>
                                                </td>
                                            </tr>
                                        </table>

                                    </div>
                                </td>

                                <!-- Right Side: Totals + Contact Info -->
                                <td style="width:35%; vertical-align:top; padding:5px;">
                                    <div style="border:1px solid #1367A4; font-size:13px; padding:4px; border-radius:6px; background-color:#f1f7fc;">
                                        <table style="width:100%; font-size:13px;">
                                            <tr><td>' . $lang('NET Amount') . '</td><td style="text-align:right;">' . amountExchange($data['invoice']['subtotal']) . '</td></tr>
                                            <tr><td>' . $lang('Total Tax Amount') . '</td><td style="text-align:right;">' . amountExchange($data['invoice']['tax']) . '</td></tr>
                                            <tr><td><strong>' . $lang('Grand Total') . '</strong></td><td style="text-align:right;"><strong>' . amountExchange($data['invoice']['total']) . '</strong></td></tr>';
            if (!$hide_balance) {
                $footer .= '<tr>
                                                                                <td>' . $lang('Previous Balance') . '</td>';
                $invbalance = 0;
                if ($cust_balance_inv >= $data['invoice']['total']) {
                    $invbalance =  $cust_balance_inv - $data['invoice']['total'];
                    $footer .= '<td style="text-align:right;">' . amountExchange($invbalance, $data['invoice']['multi'], $data['invoice']['loc']) . '</td>';
                } else {
                    $footer .= '<td style="text-align:right;">' . amountExchange($cust_balance_inv, $data['invoice']['multi'], $data['invoice']['loc']) . '</td>';
                }
                $footer .= '</tr>
                                                    <tr><td><strong>' . $lang('Balance Now Due') . '</strong></td><td style="text-align:right;"><strong>' . amountExchange($cust_balance_inv) . '</strong></td></tr>';
            }
            $footer .= '</table>
                                    </div>
                                    <!--Email & Website-->
                                    <div style="margin-top:10px; font-size:12px;">
                                        <img src="' . base_url("assets/images/mail.jpg") . '" width="20" height="20" style="vertical-align:middle;">&nbsp;' . $company['email'] . '<br>
                                        <img src="' . base_url("assets/images/web.jpg") . '" width="20" height="20" style="vertical-align:middle;">&nbsp;http://www.digifoodsdistribution.com/
                                    </div>
                                    <!-- Thank You / Bank Note -->
                                    <div style="margin-top:10px; font-size:12px;">
                                        <table style="width:100%; border:1px solid #1367A4; border-radius:6px;">
                                            <tr><td style="padding:5px;">We appreciate your business and trust..!</td></tr>
                                        </table>
                                    </div> <!-- end thank you and bank note -->
                                </td>
                            </tr>
                        </table>';
        } else {
            // Cash Format Footer
            $footer .= '
                        <table style="width:100%; border-collapse:collapse; margin-top:-170px;">
                            <tr>
                                <!-- Left Side: Notes + Customer -->
                                <td style="width:65%; vertical-align:top; padding:5px;">
                                    <div style="border:1px solid #1367A4; font-size:14px; padding:8px; border-radius:6px;">
                                        <strong style="color:rgb(48, 37, 133)">NOTE: *THE HIGHEST CHANGE ALLOWED IS.=£30</strong><br>
                                        Notification of any claims must be informed at the time of delivery.<br>
                                        Claims submitted after delivery won\'t be taken into account.<br>
                                        Frozen and chilled products cannot be returned once delivered.<br>
                                        ' . $company['cname'] . ' will retain ownership of the goods until the consumer has made full payment.<br><br>

                                    <table style="width:100%; border-collapse:collapse;">
                                            <tr>
                                                <!-- Left column: Customer & Driver (stacked) -->
                                                <td style="width:53%; vertical-align:top;">
                                                    <div style="border:1px solid #000; height:30px; border-radius:4px; padding-left:4px;"><strong>Customer:</strong></div>
                                                    <div style="border:1px solid #000; height:30px; border-radius:4px; margin-top:4px; padding-left:4px;"><strong>Driver:</strong></div>
                                                </td>
                                                <td></td>
                                                <!-- Right column: Payments & Amount -->
                                                <td style="width:45%; vertical-align:top;">
                                                    <!-- Payment methods (Cash / Card / BACS) -->
                                                    <table style="width:100%; border-collapse:collapse;">
                                                        <tr>
                                                            <td style="text-align:center;"><strong>Cash</strong></td>
                                                            <td style="text-align:center;"><strong>Card Payment</strong></td>
                                                            <td style="text-align:center;"><strong>BACS</strong></td>
                                                        </tr>
                                                        <tr>
                                                            <td><div style="border:1px solid #000; height:20px; border-radius:4px;"></div></td>
                                                            <td><div style="border:1px solid #000; height:20px; border-radius:4px;"></div></td>
                                                            <td><div style="border:1px solid #000; height:20px; border-radius:4px;"></div></td>
                                                        </tr>
                                                    </table>

                                                    <!-- Amount Box -->
                                                    <div style="border:1px solid #000; height:20px; border-radius:4px; margin-top:3px; padding-left:4px;">£</div>
                                                </td>
                                            </tr>
                                        </table>

                                    </div>
                                </td>

                                <!-- Right Side: Totals + Contact Info -->
                                <td style="width:35%; vertical-align:top; padding:5px;">
                                    <div style="border:1px solid #1367A4; font-size:13px; padding:4px; border-radius:6px; background-color:#f1f7fc;">
                                        <table style="width:100%; font-size:13px;">
                                            <tr><td>' . $lang('NET Amount') . '</td><td style="text-align:right;">' . amountExchange($data['invoice']['subtotal']) . '</td></tr>
                                            <tr><td>' . $lang('Total Tax Amount') . '</td><td style="text-align:right;">' . amountExchange($data['invoice']['tax']) . '</td></tr>
                                            <tr><td><strong>' . $lang('Grand Total') . '</strong></td><td style="text-align:right;"><strong>' . amountExchange($data['invoice']['total']) . '</strong></td></tr>';
            if (!$hide_balance) {
                $footer .= '<tr>
                                                                                <td>' . $lang('Previous Balance') . '</td>';
                $invbalance = 0;
                if ($cust_balance_inv >= $data['invoice']['total']) {
                    $invbalance =  $cust_balance_inv - $data['invoice']['total'];
                    $footer .= '<td style="text-align:right;">' . amountExchange($invbalance, $data['invoice']['multi'], $data['invoice']['loc']) . '</td>';
                } else {
                    $footer .= '<td style="text-align:right;">' . amountExchange($cust_balance_inv, $data['invoice']['multi'], $data['invoice']['loc']) . '</td>';
                }
                $footer .= '</tr>
                                                    <tr><td><strong>' . $lang('Balance Now Due') . '</strong></td><td style="text-align:right;"><strong>' . amountExchange($cust_balance_inv) . '</strong></td></tr>';
            }
            $footer .= '</table>
                                    </div>
                                    <!--Email & Website-->
                                    <div style="margin-top:5px; font-size:12px;">
                                        <img src="' . base_url("assets/images/mail.jpg") . '" width="20" height="20" style="vertical-align:middle;">&nbsp;' . $company['email'] . '<br>
                                        <img src="' . base_url("assets/images/web.jpg") . '" width="20" height="20" style="vertical-align:middle;">&nbsp;http://www.digifoodsdistribution.com/
                                    </div>
                                    <!-- Thank You / Bank Note -->
                                    <div style="margin-top:5px; font-size:12px;">
                                        <table style="width:100%; border:1px solid #1367A4; border-radius:6px;">
                                            <tr><td style="padding:2px; text-align:center;">ITS AN AUTO GENERATED SYSTEM TESTING INVOICE WHICH IS NOT VALID FOR OFFICIAL USE</td></tr>
                                        </table>
                                    </div> <!-- end thank you and bank note -->
                                </td>
                            </tr>
                        </table>';
        }
        $footer .= '</footer>';

        // Complete the HTML structure
        $header .= $footer;
        $header .= '</body></html>';

        // Generate and stream PDF
        $file_name = $this->input->get('id');
        $this->dpdf->loadHtml($header);
        $this->dpdf->render();
        $output = $this->dpdf->output();
        $file_location = 'userfiles/invoices/sale_' . $file_name . '.pdf';
        file_put_contents($file_location, $output);
        $this->dpdf->stream("" . $file_name . ".pdf", array("Attachment" => 0));
    }

    public function delete_i()
    {
        if ($this->aauth->permission_new(null, 'salesDeleteInvoice')) {
            $id = $this->input->post('deleteid');



            $sql = "select created,tid,total,tax from geopos_invoices where id=$id";
            $query = $this->db->query($sql);
            $response = $query->result_array();
            $inv_created = $response[0]['created'];

            $inv_created = new DateTime($inv_created); //from database

            $inv_created_check = new DateTime('2021-11-04 22:03:03');
            //print_r( $inv_created_check->format('Y-m-d'));die();

            if ($inv_created->format("Y-m-d") < $inv_created_check->format("Y-m-d")) {
                echo json_encode(array('status' => 'Error', 'message' => "Can not delete this invoice"));
                exit;
            }

            if ($this->invocies->invoice_delete($id, $this->limited)) {
                $this->aauth->applog("[" . ($invoice['inv_type'] == "DAYPASS" ? "Day Pass" : 'Invoice') . " Deleted - #" . $response[0]['tid'] . "] - Total: " . $response[0]['total'] . ", Tax: " . $response[0]['tax'], $this->aauth->get_user()->username);
                echo json_encode(array('status' => 'Success', 'message' =>
                $this->lang->line('DELETED')));
            } else {
                echo json_encode(array('status' => 'Error', 'message' =>
                $this->lang->line('ERROR')));
            }
        } else {
            echo json_encode(array('status' => 'Error', 'message' =>
            $this->lang->line('ERROR')));
        }
    }



    public function editaction()
    {
        // dd($this->input->post());

        $auto_post = $this->settings->auto_post();
        $invoice_table = 'geopos_invoices_bfr_post';
        $invoice_items_table = 'geopos_invoice_items_bfr_post';
        $transactions_table = 'geopos_transactions_bfr_post';
        $products_table = 'geopos_products';
        if ($auto_post) {
            // redirect('invoices', 'refresh');
            $invoice_table = 'geopos_invoices';
            $invoice_items_table = 'geopos_invoice_items';
            $transactions_table = 'geopos_transactions';
        }
        // var_dump($this->input->post('product_qty')); die();
        // var_dump('hello'); die();
        $customer_id = $this->input->post('customer_id');

        $driver_name = $this->input->post('driver_name');
        $vehicle_no = $this->input->post('vehicle_no');
        $pamt_terms = $this->input->post('pamt_terms');
        $invocieno = $this->input->post('invocieno');
        $inv_type = $this->input->post('invoiceType');
        $iid = $this->input->post('iid');
        // var_dump($iid);exit();
        $sql = "select status,inv_type,tid from $invoice_table where id=$iid";
        $query = $this->db->query($sql);
        $response = $query->result_array();
        $inv_status_check = $response[0]['status'];
        // var_dump($inv_status_check);
        // die();
        $weight_qty = $this->input->post('weight_qty'); // Array of weight quantities
        // $weight_unit = $this->input->post('weight_unit'); // Array of weight quantities
        // $weight_unit = 'KG';
        $product_qty = $this->input->post('product_qty'); // Array of product quantities
        $inv_tid = $response[0]['tid'];
        $previous_invoice_type = $response[0]['inv_type'];
        if ($inv_status_check != "due") {
            echo json_encode(array('status' => 'Error', 'message' => 'This Invoice is paid.Can not Update.'));
            exit;
        }
        // if($inv_type!=$previous_invoice_type){
        // echo json_encode(array('status' => 'Error', 'message' =>'Invoice type can not be changed.'));
        //             exit;
        // }
        $cust_name = $this->input->post('customer_name');
        $cust_address = trim($this->input->post('customer_address1'));
        //var_dump($inv_type); exit; 
        $invoicedate = $this->input->post('invoicedate');
        //$invocieduedate = $this->input->post('invocieduedate');
        $invocieduedate = $invoicedate; //$this->input->post('invocieduedate');
        // $invocieduedate = date('d-m-Y', strtotime($invoicedate . ' + 1 days')); //$this->input->post('invocieduedate');
        // $invocieduedate = date('d-m-Y', strtotime($invoicedate . ' + 30 days'));
        $notes = $this->input->post('notes', true);
        $tax = $this->input->post('tax_handle');
        $ptotal_tax = $this->input->post('taxa');
        $ship_taxtype = $this->input->post('ship_taxtype');
        $total_tax = 0;
        $discountFormat = $this->input->post('discountFormat');
        $pterms = $this->input->post('pterms');
        // Set default term to 1 if no payment terms are selected
        if (empty($pterms)) {
            $pterms = 1;
        }
        $currency = $this->input->post('mcurrency');
        $subtotal = rev_amountExchange_s($this->input->post('subtotal'), $currency, $this->aauth->get_user()->loc);
        $sql = "select subtotal,inv_type from $invoice_table where id=$iid";
        $query = $this->db->query($sql);
        $response = $query->result_array();
        $previous_invoice_total = $response[0]['subtotal'];
        $previous_invoice_type = $response[0]['inv_type'];
        //	Get Customer Balance
        $prev_balance = $this->aauth->get_customer($customer_id);
        $pbalance = $prev_balance->balance;
        $product_id = $this->input->post('pid');

        foreach ($this->input->post('product_qty') as $key => $value) {
            if ($value == '0' || $value == '' &&  $product_id[$key] != "") {
                echo json_encode(array('status' => 'Error', 'message' => 'Quantity can not be zero or empty.'));
                exit;
            }
        }

        foreach ($this->input->post('product_price') as $key => $value) {
            $this->db->select('*');
            $this->db->from('geopos_products');
            $this->db->where('pid', $product_id[$key]);
            $query = $this->db->get();
            $pr = $query->row_array();
            $productprice = $pr['product_price'];
            $product_name = $pr['product_name'];
            if ($value < $productprice) {
                echo json_encode(array('status' => 'Error', 'message' => 'Price for Product ' . $product_name . ' can not be less then ' . $productprice));
                exit;
            }
        }



        $this->db->select('*');
        $this->db->from($transactions_table);
        $this->db->where('inv_id',  $inv_tid);
        $this->db->where('tid', $iid);
        $query = $this->db->get();
        //   var_dump($this->db->last_query());exit();
        // print_r($query->num_rows());die();
        if ($query->num_rows() < 1) {
            echo json_encode(array('status' => 'Error', 'message' => 'Can Not Edit This Invoice.'));
            exit;
        }
        $total = rev_amountExchange_s($this->input->post('total'), $currency, $this->aauth->get_user()->loc);
        if ($previous_invoice_type == "DAYPASS" && $inv_type == "INVOICE") {

            $pid = $this->input->post('pid');
            $ptotal_tax = $this->input->post('taxa');
            foreach ($pid as $key => $value) {
                if (empty($value) || ($value == NULL) || ($value == "")) {
                } else {
                    $total_tax += rev_amountExchange_s($ptotal_tax[$key], $currency, $this->aauth->get_user()->loc);
                }
            }
            //  $total=$total+$total_tax;
            $subtotal = $total;
            $total_tax = 0;
            $pbalance = $pbalance - $previous_invoice_total;
            $cbalance = $pbalance + $subtotal;


            $this->db->select_max('tid');
            $this->db->from($invoice_table);
            $query = $this->db->get();
            $pr = $query->row_array();
            $cat_id = $pr['tid'];
            $invocieno =  $cat_id + 1;
            $data = [
                'tid' => $invocieno,
            ];
            $this->db->where('id', $iid);
            $this->db->update($invoice_table, $data);
            $data = [
                'note' => 'Invoice # ' . $invocieno,
                'inv_id' => $invocieno,
                'credit' => $total,
                'balance' => $cbalance,
                'payer' => $cust_name,
                'payerid' => $customer_id,
                'date' => datefordatabase($invoicedate)
            ];
            $this->db->where('inv_id',  $inv_tid);
            $this->db->where('tid', $iid);
            $this->db->update($transactions_table, $data);
            $this->aauth->applog("[Daypass to Invoice Update] - $invocieno - Customer: $cust_name ", $this->aauth->get_user()->username);
        } else if ($previous_invoice_type == "INVOICE" && $inv_type == "DAYPASS") {
            $pid = $this->input->post('pid');
            $ptotal_tax = $this->input->post('taxa');
            // foreach ($pid as $key => $value) {
            //     if (empty($value) || ($value == NULL) || ($value == "")) {
            //     } else {
            //         $total_tax += numberClean($ptotal_tax[$key]);
            //     }
            // }
            //   $total=$total-$total_tax;
            $subtotal = $total;
            //  $total_tax=0;
            $pbalance = $pbalance - $previous_invoice_total;
            $cbalance = $pbalance + $subtotal;

            // $data = [
            //         'tid' => 0,
            //         ];
            //         $this->db->where('id', $iid);
            //         $this->db->update('geopos_invoices_bfr_post', $data);
            $data = [
                'credit' => $total,
                'balance' => $cbalance,
                'payer' => $cust_name,
                'payerid' => $customer_id,
                'date' => datefordatabase($invoicedate)
            ];
            $this->db->where('inv_id',  $inv_tid);
            $this->db->where('tid', $iid);
            $this->db->update($transactions_table, $data);
            $this->aauth->applog("[Invoice to Daypass Update] -$inv_tid - Customer: $cust_name  ", $this->aauth->get_user()->username);
        } else if ($previous_invoice_type == "DAYPASS" && $inv_type == "DAYPASS") {
            $pid = $this->input->post('pid');
            $ptotal_tax = $this->input->post('taxa');
            // foreach ($pid as $key => $value) {
            //     if (empty($value) || ($value == NULL) || ($value == "")) {
            //     } else {
            //         $total_tax += numberClean($ptotal_tax[$key]);
            //     }
            // }
            //  $total=$total-$total_tax;
            $subtotal = $total;
            //  $total_tax=0;
            $pbalance = $pbalance - $previous_invoice_total;
            $cbalance = $pbalance + $subtotal;
            //    $data = [
            //         'tid' => 0,
            //         ];
            //         $this->db->where('id', $iid);
            //         $this->db->update('geopos_invoices_bfr_post', $data);
            $data = [
                //  'note' => 'Invoice # 0',
                'inv_type' => 'DAYPASS',
                'credit' => $total,
                'balance' => $cbalance,
                'payer' => $cust_name,
                'payerid' => $customer_id,
                'date' => datefordatabase($invoicedate)
            ];
            $this->db->where('inv_id',  $inv_tid);
            $this->db->where('tid', $iid);
            $this->db->update($transactions_table, $data);
            $this->aauth->applog("[Daypass Update] -$inv_tid - Customer: $cust_name  ", $this->aauth->get_user()->username);
        } else if ($previous_invoice_type == "INVOICE" && $inv_type == "INVOICE") {
            $cbalance = $pbalance - $previous_invoice_total;
            $cbalance = $cbalance + $subtotal;
            $data = [
                'credit' => $total,
                'balance' => $cbalance,
                'payer' => $cust_name,
                'payerid' => $customer_id,
                'date' => datefordatabase($invoicedate)
            ];
            $this->db->where('inv_id',  $inv_tid);
            $this->db->where('tid', $iid);
            $this->db->update($transactions_table, $data);
            $this->aauth->applog("[Invoice Update] -$inv_tid - Customer: $cust_name  ", $this->aauth->get_user()->username);
        }
        $shipping = rev_amountExchange_s($this->input->post('shipping'), $currency, $this->aauth->get_user()->loc);
        $shipping_tax = rev_amountExchange_s($this->input->post('ship_tax'), $currency, $this->aauth->get_user()->loc);
        if ($ship_taxtype == 'incl') $shipping = $shipping - $shipping_tax;
        $refer = $this->input->post('refer', true);
        //$total = rev_amountExchange_s($this->input->post('total'), $currency, $this->aauth->get_user()->loc);
        $disc_val = numberClean($this->input->post('disc_val'));
        $total_discount = rev_amountExchange_s($this->input->post('after_disc'), $currency, $this->aauth->get_user()->loc);
        $i = 0;
        if ($this->limited) {
            $employee = $this->invocies->invoice_details($iid, $this->limited);
            if ($this->aauth->get_user()->id != $employee['eid']) exit();
        }
        if ($discountFormat == '0') {
            $discstatus = 0;
        } else {
            $discstatus = 1;
        }

        if ($customer_id == 0) {
            echo json_encode(array('status' => 'Error', 'message' =>
            $this->lang->line('Please add a new client')));
            exit;
        }

        $this->db->trans_start();
        $transok = true;
        $st_c = 0;
        $this->load->library("Common");

        $bill_date = datefordatabase($invoicedate);
        //$bill_due_date = datefordatabase($invocieduedate);

        $addrr = $this->input->post('customer_address1');

        $address_bk = preg_split("/\r\n|\n|\r/", $addrr);
        //echo '<pre>'; print_r($address_bk); exit; 
        if (isset($address_bk[0])) {
            $cust_address = trim($address_bk[0]);
        } else {
            $cust_address = "";
        }
        if (isset($address_bk[1])) {
            $cust_city = $address_bk[1];
        } else {
            $cust_city = "";
        }
        if (isset($address_bk[2])) {
            $cust_postcode = $address_bk[2];
        } else {
            $cust_postcode = "";
        }
        // Initialize the total weight variable
        $total_weight = 0;

        // Loop through the weight quantities and product quantities to calculate the total weight
        if (!empty($weight_qty) && !empty($product_qty)) {
            foreach ($weight_qty as $key => $weight) {
                // Multiply weight by product quantity and add to the total weight
                $total_weight += $weight * (int)$product_qty[$key];
            }
        }
        // dd($total_weight);
        $data = array('updated_on' => date("Y-m-d h:i:s"), 'created_by' => $this->aauth->get_user()->id, 'invoicedate' => $bill_date, 'inv_type' => $inv_type, 'weight_qty' => $total_weight, 'subtotal' => $subtotal, 'shipping' => $shipping, 'ship_tax' => $shipping_tax, 'ship_tax_type' => $ship_taxtype, 'discount_rate' => $disc_val, 'discount' => $total_discount, 'tax' => $total_tax, 'total' => $total, 'notes' => $notes, 'csd' => $customer_id, 'items' => 0, 'taxstatus' => $tax, 'discstatus' => $discstatus, 'format_discount' => $discountFormat, 'refer' => $refer, 'term' => $pterms, 'multi' => $currency, 'cust_name' => $cust_name, 'cust_address' => $cust_address, 'cust_city' => $cust_city, 'cust_postcode' => $cust_postcode, 'driver_name' => $driver_name, 'vehicle_no' => $vehicle_no, 'pamt_terms' => $pamt_terms);
        $this->db->set($data);
        $this->db->where('id', $iid);
        if ($this->db->update($invoice_table, $data)) {
            //Product Data
            $pid = $this->input->post('pid');
            $productlist = array();
            $prodindex = 0;
            $itc = 0;
            $this->db->delete($invoice_items_table, array('tid' => $iid));
            $product_id = $this->input->post('pid');
            $product_name1 = $this->input->post('product_name', true);
            $product_qty = $this->input->post('product_qty');
            $old_product_qty = $this->input->post('old_product_qty');
            $product_price = $this->input->post('product_price');
            $product_tax = $this->input->post('product_tax');
            $product_discount = $this->input->post('product_discount');
            $product_subtotal = $this->input->post('product_subtotal');
            $ptotal_tax = $this->input->post('taxa');
            $ptotal_disc = $this->input->post('disca');
            $product_des = $this->input->post('product_description', true);
            $product_unit = $this->input->post('unit');
            $product_hsn = $this->input->post('hsn');
            //$product_serial = $this->input->post('serial');                                    
            $product_serial = array();

            $boxes = count($pid);

            $p = array_key_exists('SI', $_POST) ? $_POST['SI'] : array();

            for ($v = 0; $v < $boxes; $v++) {

                if (array_key_exists($v, $p)) {
                    $product_serial[$v] = '1';
                } else {
                    $product_serial[$v] = '0';
                }
            }

            $product_alert = $this->input->post('alert');
            $product_vattype = $this->input->post('vattype');
            //var_dump($pid);exit();
            foreach ($pid as $key => $value) {
                if (empty($value) || ($value == NULL) || ($value == "")) {
                } else {

                    $total_discount += numberClean(@$ptotal_disc[$key]);
                    $total_tax += rev_amountExchange_s($ptotal_tax[$key], $currency, $this->aauth->get_user()->loc);
                    $this->db->select('*');
                    $this->db->from('geopos_products');
                    $this->db->where('pid', $product_id[$key]);
                    $query = $this->db->get();
                    $pr = $query->row_array();

                    $cat_id = $pr['pcat'];
                    $data = array(
                        'tid' => $iid,
                        'pid' => $product_id[$key],
                        'cid' => $customer_id,
                        'cat_id' => $cat_id,
                        'product' => $product_name1[$key],
                        'code' => $product_hsn[$key],
                        'qty' => numberClean($product_qty[$key]),
                        'price' => rev_amountExchange_s($product_price[$key], $currency, $this->aauth->get_user()->loc),
                        'weight_qty' => $weight_qty[$key],
                        // 'weight_unit' => $weight_unit[$key],
                        'tax' => rev_amountExchange_s($product_tax[$key], $currency, $this->aauth->get_user()->loc),
                        'discount' => numberClean($product_discount[$key]),
                        'subtotal' => rev_amountExchange_s($product_subtotal[$key], $currency, $this->aauth->get_user()->loc),
                        'totaltax' => rev_amountExchange_s($ptotal_tax[$key], $currency, $this->aauth->get_user()->loc),
                        'totaldiscount' => rev_amountExchange_s($ptotal_disc[$key], $currency, $this->aauth->get_user()->loc),
                        'product_des' => $product_des[$key],
                        'unit' => $product_unit[$key],
                        'serial' => $product_serial[$key]
                    );
                    $productlist[$prodindex] = $data;
                    $i++;
                    $prodindex++;

                    $amt = numberClean(@$product_qty[$key]) - numberClean(@$old_product_qty[$key]);
                    if ($product_id[$key] > 0 and $amt) {
                        $this->db->set('qty', "qty-$amt", FALSE);
                        $this->db->where('pid', $product_id[$key]);
                        $this->db->update('geopos_products');

                        if (isset($product_alert[$key]) and (numberClean($product_alert[$key]) - $amt) < 0 and $st_c == 0 and $this->common->zero_stock()) {
                            echo json_encode(array('status' => 'Error', 'message' => 'Product - <strong>' . $product_name1[$key] . "</strong> - Low quantity. Available stock is  " . $product_alert[$key]));
                            $transok = false;
                            $st_c = 1;
                        }
                    }
                    $itc += $amt;
                }
            }
            //echo '<pre>'; print_r($productlist);exit();
            if ($prodindex > 0) {
                $this->db->insert_batch($invoice_items_table, $productlist);
                if (count($product_serial) > 0) {
                    $this->db->set('status', 1);
                    $this->db->where_in('serial', $product_serial);
                    $this->db->update('geopos_product_serials');
                }

                $this->db->set(array('discount' => rev_amountExchange_s(amountFormat_general($total_discount), $currency, $this->aauth->get_user()->loc), 'tax' => rev_amountExchange_s(amountFormat_general($total_tax), $currency, $this->aauth->get_user()->loc), 'items' => $itc));
                $this->db->where('id', $iid);
                $this->db->update($invoice_table);
                if ($transok)    echo json_encode(array('status' => 'Success', 'pid' => $iid, 'message' => $this->lang->line('Invoice has  been updated') . " <a href='view?id=$iid' class='btn btn-info btn-lg'><span class='fa fa-eye' aria-hidden='true'></span> " . $this->lang->line('View') . " </a> "));
            } else {
                echo json_encode(array('status' => 'Error', 'message' =>
                $this->lang->line('ERROR')));
                $transok = false;
            }

            if ($this->input->post('restock')) {
                foreach ($this->input->post('restock') as $key => $value) {
                    $myArray = explode('-', $value);
                    $prid = $myArray[0];
                    $dqty = numberClean($myArray[1]);
                    if ($prid > 0) {
                        $this->db->set('qty', "qty+$dqty", FALSE);
                        $this->db->where('pid', $prid);
                        $this->db->update('geopos_products');
                    }
                }
            }

            $data = [
                'balance' => $cbalance,
                'credit' => $total,
                'payer' => $cust_name,
                'payerid' => $customer_id,
                'date' => datefordatabase($invoicedate)
            ];

            $this->db->where('inv_id',  $inv_tid);
            $this->db->where('tid', $iid);
            $this->db->update($transactions_table, $data);
            $data = [
                'balance' => $cbalance,
            ];
            $this->db->where('id', $customer_id);
            $this->db->update('geopos_customers', $data);
        } else {
            if ($transok)   echo json_encode(array('status' => 'Error', 'message' =>
            "Please add at least one product in invoice"));
            $transok = false;
        }


        if ($transok) {
            $this->db->trans_complete();
        } else {
            $this->db->trans_rollback();
        }

        //profit calculation
        $t_profit = 0;
        // $this->db->select('geopos_invoice_items_bfr_post.pid, geopos_invoice_items_bfr_post.price, geopos_invoice_items_bfr_post.qty, geopos_products.fproduct_price');
        // $this->db->from('geopos_invoice_items_bfr_post');
        // $this->db->join('geopos_products', 'geopos_products.pid = geopos_invoice_items_bfr_post.pid', 'left');
        // $this->db->where('geopos_invoice_items_bfr_post.tid', $iid);
        $this->db->select("$invoice_items_table.pid, $invoice_items_table.price, $invoice_items_table.qty, $products_table.fproduct_price");
        $this->db->from($invoice_items_table);
        $this->db->join($products_table, "$products_table.pid = $invoice_items_table.pid", 'left');
        $this->db->where("$invoice_items_table.tid", $iid);
        $query = $this->db->get();
        $pids = $query->result_array();
        foreach ($pids as $profit) {
            $t_cost = $profit['fproduct_price'] * $profit['qty'];
            $s_cost = $profit['price'] * $profit['qty'];

            $t_profit += $s_cost - $t_cost;
        }
        $this->db->trans_start();
        $this->db->set('col1', $t_profit);
        $this->db->where('type', 9);
        $this->db->where('rid', $iid);
        $this->db->update('geopos_metadata');
        $this->db->trans_complete();
    }


    public function editaction4()
    {
        $customer_id = $this->input->post('customer_id');
        $invocieno = $this->input->post('invocieno');
        $inv_type = $this->input->post('invoiceType');
        $iid = $this->input->post('iid');
        // var_dump($iid); die();
        $sql = "select status,inv_type,tid from geopos_invoices where id=$iid";
        $query = $this->db->query($sql);
        $response = $query->result_array();
        $inv_status_check = $response[0]['status'];
        $inv_tid = $response[0]['tid'];
        $previous_invoice_type = $response[0]['inv_type'];
        if ($inv_status_check != "due") {
            echo json_encode(array('status' => 'Error', 'message' => 'This Invoice is paid.Can not Credit.'));
            exit;
        }
        $cust_name = $this->input->post('customer_name');
        $cust_address = trim($this->input->post('customer_address1'));
        $invoicedate = $this->input->post('invoicedate');
        $invocieduedate = $invoicedate; //$this->input->post('invocieduedate');
        // $invocieduedate = date('d-m-Y', strtotime($invoicedate . ' + 1 days')); //$this->input->post('invocieduedate');
        // $invocieduedate = date('d-m-Y', strtotime($invoicedate . ' + 30 days'));
        $notes = $this->input->post('notes', true);
        $tax = $this->input->post('tax_handle');
        $ship_taxtype = $this->input->post('ship_taxtype');
        $total_tax = 0;
        $discountFormat = $this->input->post('discountFormat');
        $pterms = $this->input->post('pterms');
        $currency = $this->input->post('mcurrency');
        $subtotal = rev_amountExchange_s($this->input->post('subtotal'), $currency, $this->aauth->get_user()->loc);
        $sql = "select subtotal,inv_type from geopos_invoices where id=$iid";
        $query = $this->db->query($sql);
        $response = $query->result_array();
        $previous_invoice_total = $response[0]['subtotal'];
        $previous_invoice_type = $response[0]['inv_type'];
        $prev_balance = $this->aauth->get_customer($customer_id);
        $pbalance = $prev_balance->balance;
        $product_id = $this->input->post('pid');
        // foreach( $this->input->post('product_qty') as $key => $value){
        // if( $value=='0' || $value=='' &&  $product_id[$key]!="" ){
        //      echo json_encode(array('status' => 'Error', 'message' =>'Quantity can not be zero or empty.'));
        // exit;
        // }
        // }

        foreach ($this->input->post('product_price') as $key => $value) {
            $this->db->select('*');
            $this->db->from('geopos_products');
            $this->db->where('pid', $product_id[$key]);
            $query = $this->db->get();
            $pr = $query->row_array();
            $productprice = $pr['product_price'];
            $product_name = $pr['product_name'];
            // if( $value<$productprice){
            //     echo json_encode(array('status' => 'Error', 'message' =>'Price for Product '.$product_name. ' can not be less then '.$productprice));
            //     exit;
            // }
        }
        $total = rev_amountExchange_s($this->input->post('total'), $currency, $this->aauth->get_user()->loc);

        $cbalance = $pbalance - $previous_invoice_total;
        $cbalance = $cbalance + $subtotal;



        $shipping = rev_amountExchange_s($this->input->post('shipping'), $currency, $this->aauth->get_user()->loc);
        $shipping_tax = rev_amountExchange_s($this->input->post('ship_tax'), $currency, $this->aauth->get_user()->loc);
        if ($ship_taxtype == 'incl') $shipping = $shipping - $shipping_tax;
        $refer = $this->input->post('refer', true);
        //$total = rev_amountExchange_s($this->input->post('total'), $currency, $this->aauth->get_user()->loc);
        $disc_val = numberClean($this->input->post('disc_val'));
        $total_discount = rev_amountExchange_s($this->input->post('after_disc'), $currency, $this->aauth->get_user()->loc);
        $i = 0;
        if ($this->limited) {
            $employee = $this->invocies->invoice_details($iid, $this->limited);
            if ($this->aauth->get_user()->id != $employee['eid']) exit();
        }
        if ($discountFormat == '0') {
            $discstatus = 0;
        } else {
            $discstatus = 1;
        }

        if ($customer_id == 0) {
            echo json_encode(array('status' => 'Error', 'message' =>
            $this->lang->line('Please add a new client')));
            exit;
        }

        $this->db->trans_start();
        $transok = true;
        $st_c = 0;
        $this->load->library("Common");

        $bill_date = datefordatabase($invoicedate);
        //$bill_due_date = datefordatabase($invocieduedate);

        $addrr = $this->input->post('customer_address1');

        $address_bk = preg_split("/\r\n|\n|\r/", $addrr);
        //echo '<pre>'; print_r($address_bk); exit; 
        if (isset($address_bk[0])) {
            $cust_address = trim($address_bk[0]);
        } else {
            $cust_address = "";
        }
        if (isset($address_bk[1])) {
            $cust_city = $address_bk[1];
        } else {
            $cust_city = "";
        }
        if (isset($address_bk[2])) {
            $cust_postcode = $address_bk[2];
        } else {
            $cust_postcode = "";
        }
        $last_trans_inserted_id = 0;

        $data_return = array('invoiceduedate' => date("Y-m-d h:i:s"), 'inv_id' => $iid, 'trans_id' => $last_trans_inserted_id, 'eid' => $this->aauth->get_user()->id, 'invoicedate' => $bill_date, 'tid' => $invocieno, 'subtotal' => $subtotal, 'shipping' => $shipping, 'ship_tax' => $shipping_tax, 'ship_tax_type' => $ship_taxtype, 'discount' => $total_discount, 'tax' => $total_tax, 'total' => $total, 'notes' => $notes, 'pmethod' => '', 'csd' => $customer_id, 'status' => 'accepted', 'discstatus' => $discstatus, 'taxstatus' => $tax, 'format_discount' => $discountFormat, 'refer' => $refer, 'term' => 1, 'pamnt' => 0.00, 'items' => '', 'loc' => 0, 'i_class' => 1, 'multi' => 0);
        $this->db->insert('geopos_stock_r', $data_return);

        if ($this->db->affected_rows() > 0) {
            $last_inserted_id = $this->db->insert_id();

            $data_trans = array(
                'acid' => '1',
                'account' => 'Sales Account',
                'type' => 'Expense',
                'cat' => 'Sales',
                'debit' =>  $total,
                'payer' => $cust_name,
                'payerid' => $customer_id,
                'method' => 'Stock Return',
                'balance' => $cbalance,
                'date' => datefordatabase($invoicedate),
                'eid' => $this->aauth->get_user()->id,
                'tid' => $iid,
                'inv_id' => $invocieno,
                'note' => 'Stock Return Invoice # ' . $invocieno,
                'loc' => $this->aauth->get_user()->loc
            );
            $this->db->insert('geopos_transactions', $data_trans);
            $last_trans_inserted_id = $this->db->insert_id();
            $this->aauth->applog("[Invoice Credited] -$invocieno - Customer: $cust_name  ", $this->aauth->get_user()->username);

            $pid = $this->input->post('pid');
            $productlist = array();
            $prodindex = 0;
            $itc = 0;

            // $this->db->delete('geopos_invoice_items', array('tid' => $iid));
            // var_dump($this->db->last_query());exit;
            $product_id = $this->input->post('pid');
            $product_name1 = $this->input->post('product_name', true);
            $product_qty = $this->input->post('product_qty');
            $old_product_qty = $this->input->post('old_product_qty');
            $product_price = $this->input->post('product_price');
            $product_tax = $this->input->post('product_tax');
            $product_discount = $this->input->post('product_discount');
            $product_subtotal = $this->input->post('product_subtotal');
            $ptotal_tax = $this->input->post('taxa');
            $ptotal_disc = $this->input->post('disca');
            $product_des = $this->input->post('product_description', true);
            $product_unit = $this->input->post('unit');
            $product_hsn = $this->input->post('hsn');
            //$product_serial = $this->input->post('serial');                                    
            $product_serial = array();

            $boxes = count($pid);

            $p = array_key_exists('SI', $_POST) ? $_POST['SI'] : array();

            for ($v = 0; $v < $boxes; $v++) {

                if (array_key_exists($v, $p)) {
                    $product_serial[$v] = '1';
                } else {
                    $product_serial[$v] = '0';
                }
            }

            $product_alert = $this->input->post('alert');
            $product_vattype = $this->input->post('vattype');
            $total_tax_return = 0;
            $total_inv_return = 0;
            foreach ($pid as $key => $value) {
                if (empty($value) || ($value == NULL) || ($value == "")) {
                } else {

                    $total_discount += numberClean(@$ptotal_disc[$key]);
                    $total_tax += calculateTax(rev_amountExchange_s($product_price[$key], $currency, $this->aauth->get_user()->loc) * numberClean($product_qty[$key]), $product_vattype[$key]);
                    $this->db->select('*');
                    $this->db->from('geopos_products');
                    $this->db->where('pid', $product_id[$key]);
                    $query = $this->db->get();
                    $pr = $query->row_array();

                    $cat_id = $pr['pcat'];
                    $data = array(
                        'tid' => $iid,
                        'pid' => $product_id[$key],
                        'cid' => $customer_id,
                        'cat_id' => $cat_id,
                        'product' => $product_name1[$key],
                        'code' => $product_hsn[$key],
                        'qty' => numberClean($product_qty[$key]),
                        'price' => rev_amountExchange_s($product_price[$key], $currency, $this->aauth->get_user()->loc),
                        'tax' => calculateTax(rev_amountExchange_s($product_price[$key], $currency, $this->aauth->get_user()->loc) * numberClean($product_qty[$key]), $product_vattype[$key]),
                        'discount' => numberClean($product_discount[$key]),
                        'subtotal' => rev_amountExchange_s($product_subtotal[$key], $currency, $this->aauth->get_user()->loc),
                        'totaltax' => calculateTax(rev_amountExchange_s($product_price[$key], $currency, $this->aauth->get_user()->loc) * numberClean($product_qty[$key]), $product_vattype[$key]),
                        'totaldiscount' => rev_amountExchange_s($ptotal_disc[$key], $currency, $this->aauth->get_user()->loc),
                        'product_des' => $product_des[$key],
                        'unit' => $product_unit[$key],
                        'serial' => $product_serial[$key]
                    );
                    // var_dump("QTy:", $product_qty[$key], "OLD", $old_product_qty[$key]); die();
                    if ($product_qty[$key] <> $old_product_qty[$key]) {
                        $qty = $old_product_qty[$key] - $product_qty[$key];
                        // $qty= ($product_qty[$key] == '0' || $product_qty[$key] == 0) ? 0 : ($old_product_qty[$key]-$product_qty[$key]);
                        $s_price = $qty * $product_price[$key];
                        $total_tax = $qty * $product_tax[$key];
                        $subtotal = $s_price; //+ $total_tax;
                        $total_tax_return += $total_tax;
                        $total_inv_return += $subtotal;
                        $data_stockreturn = array(
                            'tid' => $last_inserted_id,
                            'pid' => $product_id[$key],
                            // 'cid' =>$customer_id,
                            // 'cat_id' =>$cat_id,
                            'product' => $product_name1[$key],
                            'code' => $product_hsn[$key],
                            'qty' => numberClean($qty),
                            'price' => rev_amountExchange_s($product_price[$key], $currency, $this->aauth->get_user()->loc),
                            'tax' => calculateTax(rev_amountExchange_s($product_price[$key], $currency, $this->aauth->get_user()->loc) * numberClean($product_qty[$key]), $product_vattype[$key]),
                            'discount' => numberClean($product_discount[$key]),
                            'subtotal' => rev_amountExchange_s($subtotal, $currency, $this->aauth->get_user()->loc),
                            'totaltax' => rev_amountExchange_s($total_tax, $currency, $this->aauth->get_user()->loc),
                            'totaldiscount' => rev_amountExchange_s($ptotal_disc[$key], $currency, $this->aauth->get_user()->loc),
                            'product_des' => $product_des[$key],
                            'unit' => $product_unit[$key],
                            'added_on' => datefordatabase(date('Y-m-d H:i:s'))
                        );
                        $productlist2[$prodindex] = $data_stockreturn;
                    }

                    $productlist[$prodindex] = $data;
                    $i++;
                    $prodindex++;

                    // $amt = numberClean(@$product_qty[$key]) - numberClean(@$old_product_qty[$key]);
                    // $amt_ = numberClean(@$old_product_qty[$key]) - numberClean(@$product_qty[$key]);
                    $product_qty_clean = numberClean(@$product_qty[$key]);
                    $old_product_qty_clean = numberClean(@$old_product_qty[$key]);

                    if ($product_qty_clean === $old_product_qty_clean) {
                        $amt = $amt_ = $product_qty_clean;
                    } else {
                        $amt = $product_qty_clean - $old_product_qty_clean;
                        $amt_ = $old_product_qty_clean - $product_qty_clean;
                    }
                    if ($product_id[$key] > 0 and $amt) {
                        $this->db->set('qty', "qty-$amt", FALSE);
                        $this->db->where('pid', $product_id[$key]);
                        $this->db->update('geopos_products');
                        // var_dump($amt, $customer_id, $iid, $product_id[$key]); die();

                        // Update invoice qty count
                        $this->db->set('qty', "qty-$amt_", FALSE);
                        $this->db->where('cid', $customer_id);
                        $this->db->where('tid', $iid);
                        $this->db->where('pid', $product_id[$key]); // Ensure valid array index
                        $this->db->update('geopos_invoice_items');
                        // Debugging the last query to see if it is being generated as expected
                        // var_dump($this->db->last_query()); // Correct method to fetch the last query
                        // die(); // Stop execution to check the output

                        if (isset($product_alert[$key]) and (numberClean($product_alert[$key]) - $amt) < 0 and $st_c == 0 and $this->common->zero_stock()) {
                            echo json_encode(array('status' => 'Error', 'message' => 'Product - <strong>' . $product_name1[$key] . "</strong> - Low quantity. Available stock is  " . $product_alert[$key]));
                            $transok = false;
                            $st_c = 1;
                        }
                    }
                    $itc += $amt;
                }
            }
            // exit;

            if ($prodindex > 0) {
                // $this->db->insert_batch('geopos_invoice_items', $productlist);
                // var_dump($this->db->last_query());
                if (isset($productlist2)) {
                    $this->db->insert_batch('geopos_stock_r_items', $productlist2);
                    // var_dump($this->db->last_query());

                    // exit;
                    $data_return_update = array('subtotal' => $total_inv_return - $total_tax_return, 'tax' => $total_tax_return, 'total' => $total_inv_return + $total_tax_return, 'inv_id' => $iid, 'trans_id' => $last_trans_inserted_id);
                    $this->db->set($data_return_update);
                    $this->db->where('id', $last_inserted_id);
                    $this->db->update('geopos_stock_r');



                    $this->db->set('tid', $iid);
                    $this->db->where('id', $last_trans_inserted_id);
                    $this->db->where('payerid', $customer_id);
                    $this->db->update('geopos_transactions');

                    $data_invoice_return_update = ($total_inv_return + $total_tax_return);

                    $this->db->set('pamnt', "pamnt+$data_invoice_return_update", FALSE);
                    $this->db->where('id', $iid);
                    $this->db->update('geopos_invoices');
                }
                if (count($product_serial) > 0) {
                    $this->db->set('status', 1);
                    $this->db->where_in('serial', $product_serial);
                    $this->db->update('geopos_product_serials');
                }
                // $this->db->set(array('discount' => rev_amountExchange_s(amountFormat_general($total_discount), $currency, $this->aauth->get_user()->loc), 'tax' => rev_amountExchange_s(amountFormat_general($total_tax), $currency, $this->aauth->get_user()->loc), 'items' => $itc));
                // $this->db->where('id', $iid);
                // $this->db->update('geopos_invoices');
                if ($transok)    echo json_encode(array('status' => 'Success', 'url' => base_url() . '/stockreturn/customer', 'message' => "Stock Credited Successfully!"));
                //  if($transok)    echo json_encode(array('status' => 'Success', 'pid' => $iid,'message' => $this->lang->line('Invoice has  been credited') . " <a href='' class='btn btn-info btn-lg'><span class='fa fa-eye' aria-hidden='true'></span> " . $this->lang->line('View') . " </a> "));
            } else {
                echo json_encode(array('status' => 'Error', 'message' =>
                $this->lang->line('ERROR')));
                $transok = false;
            }

            if ($this->input->post('restock')) {
                foreach ($this->input->post('restock') as $key => $value) {
                    $myArray = explode('-', $value);
                    $prid = $myArray[0];
                    $dqty = numberClean($myArray[1]);
                    if ($prid > 0) {
                        $this->db->set('qty', "qty+$dqty", FALSE);
                        $this->db->where('pid', $prid);
                        $this->db->update('geopos_products');
                    }
                }
            }

            $data = [
                // 'balance' => $cbalance,
                'debit' => $total_inv_return + $total_tax_return
            ];

            $this->db->where('id', $last_trans_inserted_id);
            $this->db->where('payerid', $customer_id);
            // $this->db->where('tid', $iid);
            $this->db->update('geopos_transactions', $data);
            $data = [
                'balance' => $cbalance,
            ];
            $this->db->where('id', $customer_id);
            $this->db->update('geopos_customers', $data);
        } else {
            if ($transok)   echo json_encode(array('status' => 'Error', 'message' =>
            "Can not edit this invoice"));
            $transok = false;
        }


        if ($transok) {
            $this->db->trans_complete();
        } else {
            $this->db->trans_rollback();
        }

        //profit calculation
        $t_profit = 0;
        $this->db->select('geopos_invoice_items.pid, geopos_invoice_items.price, geopos_invoice_items.qty, geopos_products.fproduct_price');
        $this->db->from('geopos_invoice_items');
        $this->db->join('geopos_products', 'geopos_products.pid = geopos_invoice_items.pid', 'left');
        $this->db->where('geopos_invoice_items.tid', $iid);
        $query = $this->db->get();
        $pids = $query->result_array();
        foreach ($pids as $profit) {
            $t_cost = $profit['fproduct_price'] * $profit['qty'];
            $s_cost = $profit['price'] * $profit['qty'];

            $t_profit += $s_cost - $t_cost;
        }
        $this->db->trans_start();
        $this->db->set('col1', $t_profit);
        $this->db->where('type', 9);
        $this->db->where('rid', $iid);
        $this->db->update('geopos_metadata');
        $this->db->trans_complete();
    }
    // public function editaction4()
    // {
    //     $customer_id = $this->input->post('customer_id');
    //     $invocieno = $this->input->post('invocieno');
    //     $inv_type = $this->input->post('invoiceType');
    //     $iid = $this->input->post('iid');
    //     $sql = "select status,inv_type,tid from geopos_invoices where id=$iid";
    //     $query = $this->db->query($sql);
    //     $response = $query->result_array();
    //     $inv_status_check = $response[0]['status'];
    //     $inv_tid = $response[0]['tid'];
    //     $previous_invoice_type = $response[0]['inv_type'];
    //     if ($inv_status_check != "due") {
    //         echo json_encode(array('status' => 'Error', 'message' => 'This Invoice is paid.Can not Credit.'));
    //         exit;
    //     }
    //     $cust_name = $this->input->post('customer_name');
    //     $cust_address = trim($this->input->post('customer_address1'));
    //     $invoicedate = $this->input->post('invoicedate');
    //     $invocieduedate = date('d-m-Y', strtotime($invoicedate . ' + 30 days'));
    //     $notes = $this->input->post('notes', true);
    //     $tax = $this->input->post('tax_handle');
    //     $ship_taxtype = $this->input->post('ship_taxtype');
    //     $total_tax = 0;
    //     $discountFormat = $this->input->post('discountFormat');
    //     $pterms = $this->input->post('pterms');
    //     $currency = $this->input->post('mcurrency');
    //     $subtotal = rev_amountExchange_s($this->input->post('subtotal'), $currency, $this->aauth->get_user()->loc);
    //     $sql = "select subtotal,inv_type from geopos_invoices where id=$iid";
    //     $query = $this->db->query($sql);
    //     $response = $query->result_array();
    //     $previous_invoice_total = $response[0]['subtotal'];
    //     $previous_invoice_type = $response[0]['inv_type'];
    //     $prev_balance = $this->aauth->get_customer($customer_id);
    //     $pbalance = $prev_balance->balance;
    //     $product_id = $this->input->post('pid');
    //     // foreach( $this->input->post('product_qty') as $key => $value){
    //     // if( $value=='0' || $value=='' &&  $product_id[$key]!="" ){
    //     //      echo json_encode(array('status' => 'Error', 'message' =>'Quantity can not be zero or empty.'));
    //     // exit;
    //     // }
    //     // }

    //     foreach ($this->input->post('product_price') as $key => $value) {
    //         $this->db->select('*');
    //         $this->db->from('geopos_products');
    //         $this->db->where('pid', $product_id[$key]);
    //         $query = $this->db->get();
    //         $pr = $query->row_array();
    //         $productprice = $pr['product_price'];
    //         $product_name = $pr['product_name'];
    //         // if( $value<$productprice){
    //         //     echo json_encode(array('status' => 'Error', 'message' =>'Price for Product '.$product_name. ' can not be less then '.$productprice));
    //         //     exit;
    //         // }
    //     }
    //     $total = rev_amountExchange_s($this->input->post('total'), $currency, $this->aauth->get_user()->loc);
    //     // if($previous_invoice_type=="DAYPASS" && $inv_type=="INVOICE"){

    //     //    $pid = $this->input->post('pid');
    //     //     $ptotal_tax = $this->input->post('taxa');
    //     //     foreach ($pid as $key => $value) {
    //     //         if( empty($value) || ($value == NULL) || ($value == "") ){
    //     //            }else{
    //     //                 $total_tax += numberClean($ptotal_tax[$key]);
    //     //             }
    //     //     }
    //     //  $subtotal=$total;
    //     //  $total_tax=0;
    //     //  $pbalance=$pbalance-$previous_invoice_total;
    //     //  $cbalance = $pbalance+$subtotal;
    //     //        $this->db->select_max('tid');
    //     //         $this->db->from('geopos_invoices');
    //     //         $query = $this->db->get();
    //     //         $pr = $query->row_array();
    //     //         $cat_id = $pr['tid'];
    //     //         $invocieno =  $cat_id+1;
    //     //     $data = [
    //     //             'tid' => $invocieno,
    //     //             ];
    //     //             $this->db->where('id', $iid);
    //     //             $this->db->update('geopos_invoices', $data);
    //     //                  $data_trans = array(
    //     // 				'acid' => '1',
    //     // 				'account' => 'Sales Account',
    //     // 				'type' => 'Expense',
    //     // 				'cat' => 'Sales',
    //     // 				'debit' =>  $total,
    //     // 				'payer' => $cname,
    //     // 				'payerid' => $customer_id,
    //     // 				'method' => 'Stock Return',
    //     //                 'balance' => $cbalance, 
    //     // 				'date' => datefordatabase(date()),
    //     // 				'eid' => $this->aauth->get_user()->id,
    //     // 				'tid' => $invocieno,
    //     //                 'inv_id' => $invocieno,
    //     // 				'note' => 'Stock Return Invoice # '. $invocieno,
    //     // 				'loc' => $this->aauth->get_user()->loc
    //     // 			);
    //     // 			 $this->db->insert('geopos_transactions', $data_trans);
    //     //             $last_trans_inserted_id = $this->db->insert_id();
    //     //             $this->aauth->applog("[Daypass to Invoice Credited] -$invocieno   ", $this->aauth->get_user()->username);
    //     // }
    //     // else if($previous_invoice_type=="INVOICE" && $inv_type=="DAYPASS"){
    //     //     $pid = $this->input->post('pid');
    //     //     $ptotal_tax = $this->input->post('taxa');
    //     //     foreach ($pid as $key => $value) {
    //     //         if( empty($value) || ($value == NULL) || ($value == "") ){
    //     //            }else{
    //     //                 $total_tax += numberClean($ptotal_tax[$key]);
    //     //             }
    //     //     }
    //     //  $total=$total-$total_tax;
    //     //  $subtotal=$total;
    //     //  $total_tax=0;
    //     //  $pbalance=$pbalance-$previous_invoice_total;
    //     //  $cbalance = $pbalance+$subtotal;

    //     //     $data = [
    //     //             'tid' => 0,
    //     //             ];
    //     //             $this->db->where('id', $iid);
    //     //             $this->db->update('geopos_invoices', $data);
    //     //                  $data_trans = array(
    //     // 				'acid' => '1',
    //     // 				'account' => 'Sales Account',
    //     // 				'type' => 'Expense',
    //     // 				'cat' => 'Sales',
    //     // 				'debit' =>  $total,
    //     // 				'payer' => $cname,
    //     // 				'payerid' => $customer_id,
    //     // 				'method' => 'Stock Return',
    //     //                  'balance' => $cbalance, 
    //     // 				'date' => datefordatabase(date()),
    //     // 				'eid' => $this->aauth->get_user()->id,
    //     // 				'tid' => $invocieno,
    //     //                 'inv_id' => $invocieno,
    //     // 				'note' => 'Stock Return Invoice # '. $invocieno,
    //     // 				'loc' => $this->aauth->get_user()->loc
    //     // 			);
    //     // 			 $this->db->insert('geopos_transactions', $data_trans);
    //     //             $last_trans_inserted_id = $this->db->insert_id();

    //     //            $this->aauth->applog("[Invoice to Daypass Credited] -$invocieno   ", $this->aauth->get_user()->username);
    //     // }
    //     // else if($previous_invoice_type=="DAYPASS" && $inv_type=="DAYPASS"){
    //     // $pid = $this->input->post('pid');
    //     // $ptotal_tax = $this->input->post('taxa');
    //     //  foreach ($pid as $key => $value) {
    //     //             if( empty($value) || ($value == NULL) || ($value == "") ){
    //     //                 }else{
    //     //                 $total_tax += numberClean($ptotal_tax[$key]);
    //     //                 }
    //     //  }
    //     //  $total=$total-$total_tax;
    //     //  $subtotal=$total;
    //     //  $total_tax=0;
    //     //  $pbalance=$pbalance-$previous_invoice_total;
    //     //  $cbalance = $pbalance+$subtotal;
    //     //        $data = [
    //     //             'tid' => 0,
    //     //             ];
    //     //             $this->db->where('id', $iid);
    //     //             $this->db->update('geopos_invoices', $data);
    //     //      $data_trans = array(
    //     // 				'acid' => '1',
    //     // 				'account' => 'Sales Account',
    //     // 				'type' => 'Expense',
    //     // 				'cat' => 'Sales',
    //     // 				'debit' =>  $total,
    //     // 				'payer' => $cname,
    //     // 				'payerid' => $customer_id,
    //     // 				'method' => 'Stock Return',
    //     //                  'balance' => $cbalance, 
    //     // 				'date' => datefordatabase(date()),
    //     // 				'eid' => $this->aauth->get_user()->id,
    //     // 				'tid' => $invocieno,
    //     //                 'inv_id' => $invocieno,
    //     // 				'note' => 'Stock Return Invoice # '. $invocieno,
    //     // 				'loc' => $this->aauth->get_user()->loc
    //     // 			);
    //     // 			$this->db->insert('geopos_transactions', $data_trans);
    //     //             $last_trans_inserted_id = $this->db->insert_id();

    //     //             $this->aauth->applog("[Daypass Invoice Credited] -$invocieno   ", $this->aauth->get_user()->username);

    //     // } else if($previous_invoice_type=="INVOICE" && $inv_type=="INVOICE"){
    //     //     $cbalance = $pbalance - $previous_invoice_total;
    //     //     $cbalance = $cbalance+$subtotal;
    //     //     $data_trans = array(
    //     // 				'acid' => '1',
    //     // 				'account' => 'Sales Account',
    //     // 				'type' => 'Expense',
    //     // 				'cat' => 'Sales',
    //     // 				'debit' =>  $total,
    //     // 				'payer' => $cname,
    //     // 				'payerid' => $customer_id,
    //     // 				'method' => 'Stock Return',
    //     //                  'balance' => $cbalance, 
    //     // 				'date' => datefordatabase(date()),
    //     // 				'eid' => $this->aauth->get_user()->id,
    //     // 				'tid' => $invocieno,
    //     //                 'inv_id' => $invocieno,
    //     // 				'note' => 'Stock Return Invoice # '. $invocieno,
    //     // 				'loc' => $this->aauth->get_user()->loc
    //     // 			);
    //     // 			 $this->db->insert('geopos_transactions', $data_trans);
    //     //               $last_trans_inserted_id = $this->db->insert_id();
    //     //            $this->aauth->applog("[Invoice Credited] -$invocieno   ", $this->aauth->get_user()->username);

    //     // }
    //     $cbalance = $pbalance - $previous_invoice_total;
    //     $cbalance = $cbalance + $subtotal;



    //     $shipping = rev_amountExchange_s($this->input->post('shipping'), $currency, $this->aauth->get_user()->loc);
    //     $shipping_tax = rev_amountExchange_s($this->input->post('ship_tax'), $currency, $this->aauth->get_user()->loc);
    //     if ($ship_taxtype == 'incl') $shipping = $shipping - $shipping_tax;
    //     $refer = $this->input->post('refer', true);
    //     //$total = rev_amountExchange_s($this->input->post('total'), $currency, $this->aauth->get_user()->loc);
    //     $disc_val = numberClean($this->input->post('disc_val'));
    //     $total_discount = rev_amountExchange_s($this->input->post('after_disc'), $currency, $this->aauth->get_user()->loc);
    //     $i = 0;
    //     if ($this->limited) {
    //         $employee = $this->invocies->invoice_details($iid, $this->limited);
    //         if ($this->aauth->get_user()->id != $employee['eid']) exit();
    //     }
    //     if ($discountFormat == '0') {
    //         $discstatus = 0;
    //     } else {
    //         $discstatus = 1;
    //     }

    //     if ($customer_id == 0) {
    //         echo json_encode(array('status' => 'Error', 'message' =>
    //         $this->lang->line('Please add a new client')));
    //         exit;
    //     }

    //     $this->db->trans_start();
    //     $transok = true;
    //     $st_c = 0;
    //     $this->load->library("Common");

    //     $bill_date = datefordatabase($invoicedate);
    //     //$bill_due_date = datefordatabase($invocieduedate);

    //     $addrr = $this->input->post('customer_address1');

    //     $address_bk = preg_split("/\r\n|\n|\r/", $addrr);
    //     //echo '<pre>'; print_r($address_bk); exit; 
    //     if (isset($address_bk[0])) {
    //         $cust_address = trim($address_bk[0]);
    //     } else {
    //         $cust_address = "";
    //     }
    //     if (isset($address_bk[1])) {
    //         $cust_city = $address_bk[1];
    //     } else {
    //         $cust_city = "";
    //     }
    //     if (isset($address_bk[2])) {
    //         $cust_postcode = $address_bk[2];
    //     } else {
    //         $cust_postcode = "";
    //     }


    //     //  $data_ret_invoices = array('updated_on'=>date("Y-m-d h:i:s"),'created_by'=>$this->aauth->get_user()->id,'invoicedate' => $bill_date, 'invoiceduedate'=>date("Y-m-d h:i:s"),'inv_type' => 'INVOICE', 'subtotal' => -$subtotal, 'shipping' => "-".$shipping, 'ship_tax' => "-".$shipping_tax, 'ship_tax_type' => $ship_taxtype, 'discount_rate' => $disc_val, 'discount' => $total_discount, 'tax' => "-".$total_tax, 'total' => -$total, 'notes' => 'Stock Return', 'csd' => $customer_id, 'items' => 0, 'taxstatus' => $tax,'i_class '=> 0,'loc'=>0,'eid'=>$this->aauth->get_user()->id,'discstatus' => $discstatus, 'format_discount' => $discountFormat, 'refer' => $refer, 'term' => $pterms, 'multi' => $currency,'cust_name'=>$cust_name , 'cust_address'=>$cust_address,'cust_city'=>$cust_city,'cust_postcode'=>$cust_postcode);
    //     //  $data_ins=$this->db->insert('geopos_invoices', $data_ret_invoices);
    //     // $last_ret_inserted_id = $this->db->insert_id();

    //     $last_trans_inserted_id = 0;

    //     $data_return = array('invoiceduedate' => date("Y-m-d h:i:s"), 'inv_id' => $iid, 'trans_id' => $last_trans_inserted_id, 'eid' => $this->aauth->get_user()->id, 'invoicedate' => $bill_date, 'tid' => $invocieno, 'subtotal' => $subtotal, 'shipping' => $shipping, 'ship_tax' => $shipping_tax, 'ship_tax_type' => $ship_taxtype, 'discount' => $total_discount, 'tax' => $total_tax, 'total' => $total, 'notes' => $notes, 'pmethod' => '', 'csd' => $customer_id, 'status' => 'accepted', 'discstatus' => $discstatus, 'taxstatus' => $tax, 'format_discount' => $discountFormat, 'refer' => $refer, 'term' => 1, 'pamnt' => 0.00, 'items' => '', 'loc' => 0, 'i_class' => 1, 'multi' => 0);
    //     $this->db->insert('geopos_stock_r', $data_return);

    //     if ($this->db->affected_rows() > 0) {
    //         $last_inserted_id = $this->db->insert_id();

    //         $data_trans = array(
    //             'acid' => '1',
    //             'account' => 'Sales Account',
    //             'type' => 'Expense',
    //             'cat' => 'Sales',
    //             'debit' =>  $total,
    //             'payer' => $cust_name,
    //             'payerid' => $customer_id,
    //             'method' => 'Stock Return',
    //             'balance' => $cbalance,
    //             'date' => datefordatabase($invoicedate),
    //             'eid' => $this->aauth->get_user()->id,
    //             'tid' => $iid,
    //             'inv_id' => $invocieno,
    //             'note' => 'Stock Return Invoice # ' . $invocieno,
    //             'loc' => $this->aauth->get_user()->loc
    //         );
    //         $this->db->insert('geopos_transactions', $data_trans);
    //         $last_trans_inserted_id = $this->db->insert_id();
    //         $this->aauth->applog("[Invoice Credited] -$invocieno - Customer: $cust_name  ", $this->aauth->get_user()->username);

    //         $pid = $this->input->post('pid');
    //         $productlist = array();
    //         $prodindex = 0;
    //         $itc = 0;

    //         // $this->db->delete('geopos_invoice_items', array('tid' => $iid));
    //         // var_dump($this->db->last_query());exit;
    //         $product_id = $this->input->post('pid');
    //         $product_name1 = $this->input->post('product_name', true);
    //         $product_qty = $this->input->post('product_qty');
    //         $old_product_qty = $this->input->post('old_product_qty');
    //         $product_price = $this->input->post('product_price');
    //         $product_tax = $this->input->post('product_tax');
    //         $product_discount = $this->input->post('product_discount');
    //         $product_subtotal = $this->input->post('product_subtotal');
    //         $ptotal_tax = $this->input->post('taxa');
    //         $ptotal_disc = $this->input->post('disca');
    //         $product_des = $this->input->post('product_description', true);
    //         $product_unit = $this->input->post('unit');
    //         $product_hsn = $this->input->post('hsn');
    //         //$product_serial = $this->input->post('serial');                                    
    //         $product_serial = array();

    //         $boxes = count($pid);

    //         $p = array_key_exists('SI', $_POST) ? $_POST['SI'] : array();

    //         for ($v = 0; $v < $boxes; $v++) {

    //             if (array_key_exists($v, $p)) {
    //                 $product_serial[$v] = '1';
    //             } else {
    //                 $product_serial[$v] = '0';
    //             }
    //         }

    //         $product_alert = $this->input->post('alert');
    //         $total_tax_return = 0;
    //         $total_inv_return = 0;
    //         foreach ($pid as $key => $value) {
    //             if (empty($value) || ($value == NULL) || ($value == "")) {
    //             } else {

    //                 $total_discount += numberClean(@$ptotal_disc[$key]);
    //                 $total_tax += numberClean($ptotal_tax[$key]);
    //                 $this->db->select('*');
    //                 $this->db->from('geopos_products');
    //                 $this->db->where('pid', $product_id[$key]);
    //                 $query = $this->db->get();
    //                 $pr = $query->row_array();

    //                 $cat_id = $pr['pcat'];
    //                 $data = array(
    //                     'tid' => $iid,
    //                     'pid' => $product_id[$key],
    //                     'cid' => $customer_id,
    //                     'cat_id' => $cat_id,
    //                     'product' => $product_name1[$key],
    //                     'code' => $product_hsn[$key],
    //                     'qty' => numberClean($product_qty[$key]),
    //                     'price' => rev_amountExchange_s($product_price[$key], $currency, $this->aauth->get_user()->loc),
    //                     'tax' => numberClean($product_tax[$key]),
    //                     'discount' => numberClean($product_discount[$key]),
    //                     'subtotal' => rev_amountExchange_s($product_subtotal[$key], $currency, $this->aauth->get_user()->loc),
    //                     'totaltax' => rev_amountExchange_s($ptotal_tax[$key], $currency, $this->aauth->get_user()->loc),
    //                     'totaldiscount' => rev_amountExchange_s($ptotal_disc[$key], $currency, $this->aauth->get_user()->loc),
    //                     'product_des' => $product_des[$key],
    //                     'unit' => $product_unit[$key],
    //                     'serial' => $product_serial[$key]
    //                 );
    //                 // var_dump("QTy:", $product_qty[$key], "OLD", $old_product_qty[$key]);
    //                 if ($product_qty[$key] <> $old_product_qty[$key]) {
    //                     $qty = $old_product_qty[$key] - $product_qty[$key];
    //                     // $qty= ($product_qty[$key] == '0' || $product_qty[$key] == 0) ? 0 : ($old_product_qty[$key]-$product_qty[$key]);
    //                     $s_price = $qty * $product_price[$key];
    //                     $total_tax = $qty * $product_tax[$key];
    //                     $subtotal = $s_price; //+ $total_tax;
    //                     $total_tax_return += $total_tax;
    //                     $total_inv_return += $subtotal;
    //                     $data_stockreturn = array(
    //                         'tid' => $last_inserted_id,
    //                         'pid' => $product_id[$key],
    //                         // 'cid' =>$customer_id,
    //                         // 'cat_id' =>$cat_id,
    //                         'product' => $product_name1[$key],
    //                         'code' => $product_hsn[$key],
    //                         'qty' => numberClean($qty),
    //                         'price' => rev_amountExchange_s($product_price[$key], $currency, $this->aauth->get_user()->loc),
    //                         'tax' => ($product_vattype[$key] == 'T1') ? (rev_amountExchange_s($product_price[$key], $currency, $this->aauth->get_user()->loc) * numberClean($product_qty[$key])) * 0.20 : 0,
    //                         'discount' => numberClean($product_discount[$key]),
    //                         'subtotal' => rev_amountExchange_s($subtotal, $currency, $this->aauth->get_user()->loc),
    //                         'totaltax' => rev_amountExchange_s($total_tax, $currency, $this->aauth->get_user()->loc),
    //                         'totaldiscount' => rev_amountExchange_s($ptotal_disc[$key], $currency, $this->aauth->get_user()->loc),
    //                         'product_des' => $product_des[$key],
    //                         'unit' => $product_unit[$key],
    //                         'added_on' => datefordatabase(date('Y-m-d H:i:s'))
    //                     );
    //                     $productlist2[$prodindex] = $data_stockreturn;
    //                 }

    //                 $productlist[$prodindex] = $data;
    //                 $i++;
    //                 $prodindex++;

    //                 $amt = numberClean(@$product_qty[$key]) - numberClean(@$old_product_qty[$key]);
    //                 if ($product_id[$key] > 0 and $amt) {
    //                     $this->db->set('qty', "qty-$amt", FALSE);
    //                     $this->db->where('pid', $product_id[$key]);
    //                     $this->db->update('geopos_products');

    //                     if (isset($product_alert[$key]) and (numberClean($product_alert[$key]) - $amt) < 0 and $st_c == 0 and $this->common->zero_stock()) {
    //                         echo json_encode(array('status' => 'Error', 'message' => 'Product - <strong>' . $product_name1[$key] . "</strong> - Low quantity. Available stock is  " . $product_alert[$key]));
    //                         $transok = false;
    //                         $st_c = 1;
    //                     }
    //                 }
    //                 $itc += $amt;
    //             }
    //         }
    //         // exit;

    //         if ($prodindex > 0) {
    //             // $this->db->insert_batch('geopos_invoice_items', $productlist);
    //             // var_dump($this->db->last_query());
    //             if (isset($productlist2)) {
    //                 $this->db->insert_batch('geopos_stock_r_items', $productlist2);
    //                 // var_dump($this->db->last_query());

    //                 // exit;
    //                 $data_return_update = array('subtotal' => $total_inv_return - $total_tax_return, 'tax' => $total_tax_return, 'total' => $total_inv_return + $total_tax_return, 'inv_id' => $iid, 'trans_id' => $last_trans_inserted_id);
    //                 $this->db->set($data_return_update);
    //                 $this->db->where('id', $last_inserted_id);
    //                 $this->db->update('geopos_stock_r');



    //                 $this->db->set('tid', $iid);
    //                 $this->db->where('id', $last_trans_inserted_id);
    //                 $this->db->where('payerid', $customer_id);
    //                 $this->db->update('geopos_transactions');

    //                 $data_invoice_return_update = ($total_inv_return + $total_tax_return);

    //                 $this->db->set('pamnt', "pamnt+$data_invoice_return_update", FALSE);
    //                 $this->db->where('id', $iid);
    //                 $this->db->update('geopos_invoices');

    //                 //$inv_status_check
    //                 //   if($inv_status_check=="due"){
    //                 //        $this->db->set('pamnt', "pamnt+$total", FALSE);
    //                 //        $this->db->where('id', $iid);
    //                 //       $this->db->update('geopos_invoices');
    //                 //   }
    //                 //   else{
    //                 //        $this->db->set('pamnt', "pamnt-$total", FALSE);
    //                 //         $this->db->where('id', $iid);
    //                 //         $this->db->update('geopos_invoices');

    //                 //   }
    //                 //   $this->db->set($data_invoice_return_update);


    //                 // var_dump($this->db->last_query());exit();


    //             }
    //             if (count($product_serial) > 0) {
    //                 $this->db->set('status', 1);
    //                 $this->db->where_in('serial', $product_serial);
    //                 $this->db->update('geopos_product_serials');
    //             }
    //             // $this->db->set(array('discount' => rev_amountExchange_s(amountFormat_general($total_discount), $currency, $this->aauth->get_user()->loc), 'tax' => rev_amountExchange_s(amountFormat_general($total_tax), $currency, $this->aauth->get_user()->loc), 'items' => $itc));
    //             // $this->db->where('id', $iid);
    //             // $this->db->update('geopos_invoices');
    //             if ($transok)    echo json_encode(array('status' => 'Success', 'url' => base_url() . '/stockreturn/customer', 'message' => "Stock Credited Successfully!"));
    //             //  if($transok)    echo json_encode(array('status' => 'Success', 'pid' => $iid,'message' => $this->lang->line('Invoice has  been credited') . " <a href='' class='btn btn-info btn-lg'><span class='fa fa-eye' aria-hidden='true'></span> " . $this->lang->line('View') . " </a> "));
    //         } else {
    //             echo json_encode(array('status' => 'Error', 'message' =>
    //             $this->lang->line('ERROR')));
    //             $transok = false;
    //         }

    //         if ($this->input->post('restock')) {
    //             foreach ($this->input->post('restock') as $key => $value) {
    //                 $myArray = explode('-', $value);
    //                 $prid = $myArray[0];
    //                 $dqty = numberClean($myArray[1]);
    //                 if ($prid > 0) {
    //                     $this->db->set('qty', "qty+$dqty", FALSE);
    //                     $this->db->where('pid', $prid);
    //                     $this->db->update('geopos_products');
    //                 }
    //             }
    //         }

    //         $data = [
    //             // 'balance' => $cbalance,
    //             'debit' => $total_inv_return + $total_tax_return
    //         ];

    //         $this->db->where('id', $last_trans_inserted_id);
    //         $this->db->where('payerid', $customer_id);
    //         // $this->db->where('tid', $iid);
    //         $this->db->update('geopos_transactions', $data);
    //         $data = [
    //             'balance' => $cbalance,
    //         ];
    //         $this->db->where('id', $customer_id);
    //         $this->db->update('geopos_customers', $data);
    //     } else {
    //         if ($transok)   echo json_encode(array('status' => 'Error', 'message' =>
    //         "Can not edit this invoice"));
    //         $transok = false;
    //     }


    //     if ($transok) {
    //         $this->db->trans_complete();
    //     } else {
    //         $this->db->trans_rollback();
    //     }

    //     //profit calculation
    //     $t_profit = 0;
    //     $this->db->select('geopos_invoice_items.pid, geopos_invoice_items.price, geopos_invoice_items.qty, geopos_products.fproduct_price');
    //     $this->db->from('geopos_invoice_items');
    //     $this->db->join('geopos_products', 'geopos_products.pid = geopos_invoice_items.pid', 'left');
    //     $this->db->where('geopos_invoice_items.tid', $iid);
    //     $query = $this->db->get();
    //     $pids = $query->result_array();
    //     foreach ($pids as $profit) {
    //         $t_cost = $profit['fproduct_price'] * $profit['qty'];
    //         $s_cost = $profit['price'] * $profit['qty'];

    //         $t_profit += $s_cost - $t_cost;
    //     }
    //     $this->db->trans_start();
    //     $this->db->set('col1', $t_profit);
    //     $this->db->where('type', 9);
    //     $this->db->where('rid', $iid);
    //     $this->db->update('geopos_metadata');
    //     $this->db->trans_complete();
    // }




    public function update_status()
    {
        $tid = $this->input->post('tid');
        $status = $this->input->post('status');
        $this->db->set('status', $status);
        $this->db->where('id', $tid);
        $this->db->update('geopos_invoices');

        echo json_encode(array('status' => 'Success', 'message' =>
        $this->lang->line('UPDATED'), 'pstatus' => $status));
    }


    public function addcustomer()
    {
        $name = $this->input->post('name', true);
        $company = $this->input->post('company', true);
        $phone = $this->input->post('phone', true);
        $email = $this->input->post('email', true);
        $address = $this->input->post('address', true);
        $city = $this->input->post('city', true);
        $region = $this->input->post('region', true);
        $country = $this->input->post('country', true);
        $postbox = $this->input->post('postbox', true);
        $taxid = $this->input->post('taxid', true);
        $customergroup = $this->input->post('customergroup');
        $name_s = $this->input->post('name_s', true);
        $phone_s = $this->input->post('phone_s', true);
        $email_s = $this->input->post('email_s', true);
        $address_s = $this->input->post('address_s', true);
        $city_s = $this->input->post('city_s', true);
        $region_s = $this->input->post('region_s', true);
        $country_s = $this->input->post('country_s', true);
        $postbox_s = $this->input->post('postbox_s', true);

        $this->load->model('customers_model', 'customers');
        $this->customers->add($name, $company, $phone, $email, $address, $city, $region, $country, $postbox, $customergroup, $taxid, $name_s, $phone_s, $email_s, $address_s, $city_s, $region_s, $country_s, $postbox_s);
    }

    public function file_handling()
    {
        if ($this->input->get('op')) {
            $name = $this->input->get('name');
            $invoice = $this->input->get('invoice');
            if ($this->invocies->meta_delete($invoice, 1, $name)) {
                echo json_encode(array('status' => 'Success'));
            }
        } else {
            $id = $this->input->get('id');
            $this->load->library("Uploadhandler_generic", array(
                'accept_file_types' => '/\.(gif|jpe?g|png|docx|docs|txt|pdf|xls)$/i',
                'upload_dir' => FCPATH . 'userfiles/attach/',
                'upload_url' => base_url() . 'userfiles/attach/'
            ));
            $files = (string)$this->uploadhandler_generic->filenaam();
            if ($files != '') {

                $this->invocies->meta_insert($id, 1, $files);
            }
        }
    }

    public function delivery()
    {

        $tid = $this->input->get('id');

        $data['id'] = $tid;
        $data['title'] = "Invoice $tid";
        $data['invoice'] = $this->invocies->invoice_details($tid, $this->limited);
        if ($data['invoice']['id']) $data['products'] = $this->invocies->invoice_products($tid);
        if ($data['invoice']['id']) $data['employee'] = $this->invocies->employee($data['invoice']['eid']);

        ini_set('memory_limit', '64M');

        $html = $this->load->view('invoices/del_note', $data, true);

        //PDF Rendering
        $this->load->library('pdf');

        $pdf = $this->pdf->load();

        $pdf->SetHTMLFooter('<div style="text-align: right;font-family: serif; font-size: 8pt; color: #5C5C5C; font-style: italic;margin-top:-6pt;">{PAGENO}/{nbpg} #' . $tid . '</div>');

        $pdf->WriteHTML($html);

        if ($this->input->get('d')) {

            $pdf->Output('DO_#' . $data['invoice']['tid'] . '.pdf', 'D');
        } else {
            $pdf->Output('DO_#' . $data['invoice']['tid'] . '.pdf', 'I');
        }
    }

    public function proforma()
    {

        $tid = $this->input->get('id');

        $data['id'] = $tid;
        $data['title'] = "Invoice $tid";
        $data['invoice'] = $this->invocies->invoice_details($tid, $this->limited);
        if ($data['invoice']['id']) $data['products'] = $this->invocies->invoice_products($tid);
        if ($data['invoice']['id']) $data['employee'] = $this->invocies->employee($data['invoice']['eid']);
        ini_set('memory_limit', '64M');
        $html = $this->load->view('invoices/proforma', $data, true);
        //PDF Rendering
        $this->load->library('pdf');
        $pdf = $this->pdf->load();
        $pdf->SetHTMLFooter('<div style="text-align: right;font-family: serif; font-size: 8pt; color: #5C5C5C; font-style: italic;margin-top:-6pt;">{PAGENO}/{nbpg} #' . $tid . '</div>');
        $pdf->WriteHTML($html);
        if ($this->input->get('d')) {
            $pdf->Output('Proforma_#' . $data['invoice']['tid'] . '.pdf', 'D');
        } else {
            $pdf->Output('Proforma_#' . $data['invoice']['tid'] . '.pdf', 'I');
        }
    }


    public function send_invoice_auto($invocieno, $invocieno2, $idate, $total, $multi)
    {
        $this->load->library('parser');
        $this->load->model('templates_model', 'templates');
        $template = $this->templates->template_info(6);

        $data = array(
            'Company' => $this->config->item('ctitle'),
            'BillNumber' => $invocieno2
        );
        $subject = $this->parser->parse_string($template['key1'], $data, TRUE);
        $validtoken = hash_hmac('ripemd160', $invocieno, $this->config->item('encryption_key'));
        $link = base_url('billing/view?id=' . $invocieno . '&token=' . $validtoken);


        $data = array(
            'Company' => $this->config->item('ctitle'),
            'BillNumber' => $invocieno2,
            'URL' => "<a href='$link'>$link</a>",
            'CompanyDetails' => '<h6><strong>' . $this->config->item('ctitle') . ',</strong></h6>
<address>' . $this->config->item('address') . '<br>' . $this->config->item('address2') . '</address>
             ' . $this->lang->line('Phone') . ' : ' . $this->config->item('phone') . '<br>  ' . $this->lang->line('Email') . ' : ' . $this->config->item('email'),
            'DueDate' => dateformat($idate),
            'Amount' => amountExchange($total, $multi)
        );
        $message = $this->parser->parse_string($template['other'], $data, TRUE);
        return array('subject' => $subject, 'message' => $message);
    }

    public function send_sms_auto($invocieno, $invocieno2, $idate, $total, $multi)
    {
        $this->load->library('parser');
        $this->load->model('templates_model', 'templates');
        $template = $this->templates->template_info(30);
        $validtoken = hash_hmac('ripemd160', $invocieno, $this->config->item('encryption_key'));
        $link = base_url('billing/view?id=' . $invocieno . '&token=' . $validtoken);
        $this->load->model('plugins_model', 'plugins');
        $sms_service = $this->plugins->universal_api(1);
        if ($sms_service['active']) {
            $this->load->library("Shortenurl");
            $this->shortenurl->setkey($sms_service['key1']);
            $link = $this->shortenurl->shorten($link);
        }
        $data = array(
            'BillNumber' => $invocieno2,
            'URL' => $link,
            'DueDate' => dateformat($idate),
            'Amount' => amountExchange($total, $multi)
        );
        $message = $this->parser->parse_string($template['other'], $data, TRUE);
        return array('message' => $message);
    }





    public function daily_break_down_sheet_export()
    {


        $company = $this->settings->company_details(1);

        ob_end_clean();
        $data = $this->invocies->get_daily_break_down_sheet($_POST['hidden_fields']);
        $driver_name = $_POST['driver_name'];
        $vehicle_no = $_POST['vehicle_number'];
        $data_tr = "";
        foreach ($data as $key => $row) {

            $data_tr .= '
  <tr>
   <td>' . $row["tid"] . '</td>
   <td>' . $row["name"] . '</td>
   <td>' . $row["company"] . '</td>
   <td>' . amountExchange_s($row["total"]) . '</td>
  </tr>
 ';
        }

        $total = 0;
        foreach ($data as $key => $row) {

            $total += $row["total"];
        }


        $date = date("d/m/y");
        $time = date("h:i:s A");
        $html = "
<html>
<head>
    <style>
        @page { margin: 120px 50px; }
        #header{ position: fixed;  top: -90px;}
        .left_header{float:left;}
        .center_header{margin-left:180px;width:300px}
        .right_header{position: fixed;  top: -90px;left:610px;}
        .pagenum:after {content: counter(page)}
        table{width:100%;}
    </style>
</head>
<body>
    <div id='header'>
       <div class='left_header'>
          <div>$date</div>
          <div>$time</div>
      </div>
      <div style='font-size:17px;text-align:center;' class='center_header'>
                    <div>
                        <u><b>" . $company['cname'] . " </b>  </u>
                    </div>
                    <div>
                        <b><u> Daily Breakdown Sheet</u></b> 
                    </div>
                </div>
      <div class='right_header'>
        <span class='pagenum'>Page:</span>
      </div>
    </div>
<div style='width:100%;font-weight: bold;'>Drivers Name: $driver_name<span style='float:right;margin-right:150px;'>Advance:</span></div>
<br>
<div><b>Vehicle Reg No: &nbsp;&nbsp;$vehicle_no</b></div>
<br>
<table>
  <thead>
    <tr>
        <th>
            Number
        </th>
        <th>
            A/C
        </th>
        <th>
            Name
        </th>
        <th>
            Amount
        </th>
        <th>
            Cash
        </th>
    </tr>
</thead>
$data_tr
<tr>
<td></td>
<td></td>
<td></td>
<td style='border-top:2px solid black;border-bottom:2px solid black'>" . amountExchange_s($total) . "</td>
<td></td>

</tr>
</table>
<br>
<br>
<br>
<br>
<table>
    <tr>
        <th>&#163;50:</th>
        <td><td>
    </tr>
     <tr>
        <th>&#163;20:</th>
        <td><td>
    </tr>
    <tr>
        <th>&#163;10:</th>
        <td><td>
    </tr>
    <tr>
        <th>&#163;5:</th>
        <td><td>
    </tr>
    <tr>
        <th>Coins:</th>
        <td><td>
    </tr>
    <tr>
        <th>Diesel:</th>
        <td><td>
    </tr>
    <tr>
        <th>Other Expenses:</th>
        <td><td>
    </tr>
    <tr>
        <th>Total:</th>
        <td><td>
    </tr>
</table>
<br>
<table>
<tr>
    <th style='text-align:center;width:200px' >Signature:</th>
    <th ><div style='margin-left:50px;'>Driver </div> <div style='border: 2px solid black;height:50px; width:150px'></div></th>
    <th ><div style='margin-left:40px;'>MANAGER </div><div style='border: 2px solid black;height:50px; width:150px'></div></th>
</tr>
<br>
<tr>
    <th style='text-align:center;font-size:13px;padding-top:20px'>RETURNED GOODS:</th>

</tr>
</table>

</body>
</html>
";

        $this->dpdf->loadHtml($html);
        $this->dpdf->render();
        $this->dpdf->stream("" . "BreakDownSheet" . ".pdf", array("Attachment" => 0));
    }




    public function exportTableToCSV($tableData)
    {
        $file_name = 'Stock_Adjustment-' . time() . '.csv';
        $file_path = FCPATH . 'userfiles/documents/' . $file_name;

        $file = fopen($file_path, 'w');

        foreach ($tableData as $row) {
            fputcsv($file, $row);
        }

        fclose($file);

        // Force download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $file_name . '"');
        header('Content-Length: ' . filesize($file_path));
        readfile($file_path);
        exit();
    }

    // public function stock_report_export()
    // {
    //     $company = $this->settings->company_details(1);
    //     $data = $this->invocies->stock_report_export($_POST['hidden_fields'], $_POST['hidden_start_category'], $_POST['hidden_end_category']);
    //     $data_tr = "";
    //     $end_cat = $_POST['hidden_end_category'];
    //     $start_cat = $_POST['hidden_start_category'];

    //     if ($start_cat == "" || $end_cat == "") {
    //         $start_cat = 1;
    //         $end_cat = 999;
    //     }
    //     $cat_name = "";

    //     $cat = $_POST['categories_name'];
    //     if ($cat) {
    //         $sql = "select title from geopos_product_cat where id IN  (" . $cat . ") ";
    //         $query = $this->db->query($sql);
    //         $res_array = $query->result_array();
    //         foreach ($res_array as $row) {
    //             $cat_name .= $row['title'] . ",  ";
    //         }
    //     }



    //     $start_date = $_GET['hidden_start_date'];
    //     $end_date = $_GET['hidden_end_date'];
    //     $netsubtotal = 0;
    //     $subtotal = 0;
    //     ob_end_clean();
    //     foreach ($data as $key => $row) {
    //         $subtotal = $row["subtotal"] - $row["totaltax"];
    //         $netsubtotal += $subtotal;
    //         $data_tr .= '
    //     <tr>
    //     <td>' . $row["product"] . '</td>
    //     <td>' . $row["product_des"] . '</td>
    //     <td><b>' . $row["qty"] . '</b></td>
    //     <td>' . $subtotal . '</td>
    //     <td>' . $row["totaltax"] . '</td>
    //     <td>' . $row["subtotal"] . '</td>
    //     </tr>
    //     ';
    //     }

    //     $total_qty = 0;
    //     $total_sub = 0;
    //     $total_tax = 0;
    //     foreach ($data as $key => $row) {
    //         $total_qty += $row["qty"];
    //         $total_sub += $row["subtotal"];
    //         $total_tax += $row["totaltax"];
    //     }

    //     $date = date("d/m/y");
    //     $time = date("h:i:s");
    //     $html = "
    //             <html>
    //             <head>
    //                 <style>
    //                     body{font-family: 'Times New Roman', Times, serif;}
    //                     @page { margin: 110px 50px; }
    //                     #header{ position: fixed;  top: -70px;}
    //                     .left_header{float:left;}
    //                     .center_header{margin-left:130px;width:350px}
    //                     .right_header{position: fixed;  top: -70px;left:630px;}
    //                     .pagenum:after {content: counter(page)}
    //                     table{width:100%;}
    //                 </style>
    //             </head>
    //             <body>
    //                 <div id='header'>
    //                 <div class='left_header'>
    //                     <div><b>Date: $date</b></div>
    //                     <div><b>Time: $time</b></div>
    //                 </div>
    //                 <div style='font-size:17px;text-align:center;' class='center_header'>
    //                     <div>
    //                     <u><b> " . $company['cname'] . " </b>  </u>
    //                     </div>
    //                     <div >
    //                         <b><u> Sales By Product (Summary)</u></b> 
    //                     </div>
    //                 </div >
    //                 <div class='right_header'>
    //                     <b><span class='pagenum'>Page:</span></b>
    //                 </div>
    //                 </div>
    //                 <b>   Product Categories:</b> $cat_name
    //                 <div style='font-size:13px;'>

    //                     <div  style='float:left'>
    //                         <div><b>Stock Code From:$start_cat</b></div>
    //                         <div><b>Stock Code To:$end_cat</b></div>
    //                     </div>
    //                     <div style='float:right; margin-right:300px'>
    //                         <div><b>Invoice Date From:$start_date</b></div>
    //                         <div><b>Invoice Date To:$end_date</b></div>
    //                     </div>
    //                 </div>

    //                 <div style='clear:both'>
    //                 </div>
    //             <br>
    //             <table>
    //             <thead>
    //                 <tr>
    //                     <th>
    //                         Stock Code
    //                     </th>
    //                     <th>
    //                         Description
    //                     </th>
    //                     <th>
    //                         Quantity
    //                     </th>
    //                     <th>
    //                         Net
    //                     </th>
    //                     <th>
    //                         Tax
    //                     </th>
    //                     <th>
    //                         Gross
    //                     </th>
    //                 </tr>
    //             </thead>
    //             $data_tr
    //             <tr>
    //             <td></td>
    //             <td></td>
    //             <td style='border-top:2px solid black;border-bottom:2px solid black'>" . intval($total_qty) . "</td>
    //             <td style='border-top:2px solid black;border-bottom:2px solid black'>" . amountExchange_s($netsubtotal) . "</td>
    //             <td style='border-top:2px solid black;border-bottom:2px solid black'>" . amountExchange_s($total_tax) . "</td>
    //             <td style='border-top:2px solid black;border-bottom:2px solid black'>" . amountExchange_s($total_sub) . "</td>
    //             </tr>
    //             </table>



    //             </body>
    //             </html>
    //             ";
    //     $this->dpdf->loadHtml($html);
    //     $this->dpdf->render();
    //     $this->dpdf->stream("" . "Stock Report" . ".pdf", array("Attachment" => 0));
    // }
    public function stock_report_export()
    {
        $data = $this->invocies->stock_report_export($_POST['hidden_fields'], $_POST['hidden_start_category'], $_POST['hidden_end_category']);
        $data_tr = "";
        $end_cat = $_POST['hidden_end_category'];
        $start_cat = $_POST['hidden_start_category'];

        if ($start_cat == "" || $end_cat == "") {
            $start_cat = 1;
            $end_cat = 999;
        }


        $start_date = $_GET['hidden_start_date'];
        $end_date = $_GET['hidden_end_date'];
        $netsubtotal = 0;
        $subtotal = 0;
        ob_end_clean();
        foreach ($data as $key => $row) {
            $subtotal = $row["subtotal"] - $row["totaltax"];
            $netsubtotal += $subtotal;
            $data_tr .= '
        <tr>
        <td>' . $row["product"] . '</td>
        <td>' . $row["product_des"] . '</td>
        <td><b>' . $row["qty"] . '</b></td>
        <td>' . $subtotal . '</td>
        <td>' . $row["totaltax"] . '</td>
        <td>' . $row["subtotal"] . '</td>
        </tr>
        ';
        }

        $total_qty = 0;
        $total_sub = 0;
        $total_tax = 0;
        foreach ($data as $key => $row) {
            $total_qty += $row["qty"];
            $total_sub += $row["subtotal"];
            $total_tax += $row["totaltax"];
        }


        $date = date("d/m/y");
        $time = date("h:i:s");
        $html = "
            <html>
            <head>
                <style>
                    body{font-family: 'Times New Roman', Times, serif;}
                    @page { margin: 110px 50px; }
                    #header{ position: fixed;  top: -70px;}
                    .left_header{float:left;}
                    .center_header{margin-left:130px;width:350px}
                    .right_header{position: fixed;  top: -70px;left:630px;}
                    .pagenum:after {content: counter(page)}
                    table{width:100%;}
                </style>
            </head>
            <body>
                <div id='header'>
                <div class='left_header'>
                    <div><b>Date: $date</b></div>
                    <div><b>Time: $time</b></div>
                </div>
                <div style='font-size:18px;text-align:center;' class='center_header'>
                    <div>
                    <u><b> BFC DISTRIBUTION LTD </b>  </u>
                    </div>
                    <div >
                        <b><u> Sales By Product (Summary)</u></b> 
                    </div>
                </div >
                <div class='right_header'>
                    <b><span class='pagenum'>Page:</span></b>
                </div>
                </div>
                <div style='font-size:13px;'>
                    <div  style='float:left'>
                        <div><b>Stock Code From:$start_cat</b></div>
                        <div><b>Stock Code To:$end_cat</b></div>
                    </div>
                    <div style='float:right; margin-right:300px'>
                        <div><b>Invoice Date From:$start_date</b></div>
                        <div><b>Invoice Date To:$end_date</b></div>
                    </div>
                </div>

                <div style='clear:both'>
                </div>
            <br>
            <table>
            <thead>
                <tr>
                    <th>
                        Stock Code
                    </th>
                    <th>
                        Description
                    </th>
                    <th>
                        Quantity
                    </th>
                    <th>
                        Net
                    </th>
                    <th>
                        Tax
                    </th>
                    <th>
                        Gross
                    </th>
                </tr>
            </thead>
            $data_tr
            <tr>
            <td></td>
            <td></td>
            <td style='border-top:2px solid black;border-bottom:2px solid black'> $total_qty</td>
            <td style='border-top:2px solid black;border-bottom:2px solid black'> $netsubtotal</td>
            <td style='border-top:2px solid black;border-bottom:2px solid black'> $total_tax</td>
            <td style='border-top:2px solid black;border-bottom:2px solid black'> $total_sub</td>
            </tr>
            </table>



            </body>
            </html>
            ";
        $this->dpdf->loadHtml($html);
        $this->dpdf->render();
        $this->dpdf->stream("" . "Stock Report" . ".pdf", array("Attachment" => 0));
    }
    public function view_payslip()
    {
        $id = $this->input->get('id');
        $inv = $this->input->get('inv');
        $data['invoice'] = $this->invocies->invoice_details($inv, $this->limited);
        if (!$data['invoice']['id']) exit('Limited Permissions!');

        $this->load->model('transactions_model', 'transactions');
        $head['title'] = "View Transaction";
        $head['usernm'] = $this->aauth->get_user()->username;

        $data['trans'] = $this->transactions->view($id);

        if ($data['trans']['payerid'] > 0) {
            $data['cdata'] = $this->transactions->cview($data['trans']['payerid'], $data['trans']['ext']);
        } else {
            $data['cdata'] = array('address' => 'Not Registered', 'city' => '', 'phone' => '', 'email' => '');
        }
        ini_set('memory_limit', '64M');

        $html = $this->load->view('transactions/view-print-customer', $data, true);

        //PDF Rendering
        $this->load->library('pdf');

        $pdf = $this->pdf->load_en();

        $pdf->SetHTMLFooter('<table width="100%" style="vertical-align: bottom; font-family: serif; font-size: 8pt; color: #5C5C5C; font-style: italic;"><tr><td width="33%"></td><td width="33%" align="center" style="font-weight: bold; font-style: italic;">{PAGENO}/{nbpg}</td><td width="33%" style="text-align: right; ">#' . $id . '</td></tr></table>');

        $pdf->WriteHTML($html);

        if ($this->input->get('d')) {

            $pdf->Output('Trans_#' . $id . '.pdf', 'D');
        } else {
            $pdf->Output('Trans_#' . $id . '.pdf', 'I');
        }
    }

    public function invoice_customers_ajax()
    {
        $list = $this->customers->get_all_customers();
        echo json_encode($list);
    }




    public function geofence()
    {
        $head['title'] = "Geofence";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $this->load->view('invoices/geofence');
        $this->load->view('fixed/footer');
    }



    public function export_invoice_geofence()
    {
        $company = $this->settings->company_details(1);
        $fence_data = json_decode($_POST['fence_data']);
        //print_r($fence_data[0]);die();
        ob_end_clean();

        $data_tr = "";
        foreach ($fence_data as $key => $row) {
            $data_tr .= '
  <tr>
   <td>' . $row->name . '</td>
   <td>' . $row->code . '</td>
   <td>' . $row->fence . '</td>
  </tr>
 ';
        }

        $total = 0;



        $date = date("d/m/y");
        $time = date("h:i:s");
        $html = "
<html>
<head>
    <style>
        @page { margin: 120px 50px; }
        #header{ position: fixed;  top: -90px;}
        .left_header{float:left;}
        .center_header{margin-left:180px;width:300px}
        .right_header{position: fixed;  top: -90px;left:610px;}
        .pagenum:after {content: counter(page)}
        table{width:100%;}
    </style>
</head>
<body>
    <div id='header'>
       <div class='left_header'>
          <div>$date</div>
          <div>$time</div>
      </div>
      <div class='center_header'  style='font-size:18px;text-align:center;'>
          <div>
           <u><b> " . $company['cname'] . " </b>  </u>
        </div>
        <div>
            <b> Delivery Details Zone Wise </b> 
        </div>
      </div >
      <div class='right_header'>
        <span class='pagenum'>Page:</span>
      </div>
    </div>


<br>
<table>
  <thead>
    <tr>
        <th>
            Customer Name
        </th>
        <th>
            Customer Code
        </th>
        <th>
            Zone
        </th>
    </tr>
</thead>
$data_tr

</table>


</body>
</html>
";

        $this->dpdf->loadHtml($html);
        $this->dpdf->render();
        $this->dpdf->stream("" . "Zone Report" . ".pdf", array("Attachment" => 0));
    }

    public function ajax_list_customers_invoices()
    {

        // Let the model read filters (start_date, end_date), ordering, search and paging from POST
        $list = $this->invocies->get_datatables_for_print();

        $data = array();
        $no = 0;
        foreach ($list as $invoice) {
            $no++;
            $row = array();
            // 0: No
            $row[] = $no;
            // 1: Inv # (tid)
            $row[] = $invoice->tid;
            // 2: A/C (use company as account label fallback)
            $row[] = $invoice->company;
            // 3: Name
            $row[] = $invoice->name;
            // 4: Type
            $row[] = $invoice->inv_type;
            // 5: Date
            $row[] = $this->format_invoice_date($invoice->invoicedate);
            // 6: Amount
            $row[] = amountExchange($invoice->item_price, $invoice->multi, $invoice->loc);
            // 7: Status
            $row[] = '<span class="st-' . $invoice->status . '">' . $this->format_status_text($invoice->status) . '</span>';
            // 8: Action (selection checkbox expected by the UI)
            $row[] = '<a href="' . base_url("invoices/edit_invoices?id=$invoice->id") . '" target="_blank" class="btn btn-info btn-sm"  title="Stock Return"><span class="fa fa-exchange"></span>';

            $data[] = $row;
        }

        // Build DataTables response (draw, recordsTotal, recordsFiltered, data)
        $draw = isset($_POST['draw']) ? (int)$_POST['draw'] : 0;
        $recordsFiltered = $this->invocies->count_filtered_for_print();
        $recordsTotal = $this->invocies->count_all_for_print();

        echo json_encode(array(
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data
        ));
    }
}

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
require_once APPPATH . 'third_party/vendor/autoload.php';


use Omnipay\Omnipay;
use PayPal\Api\Amount;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Api\PaymentExecution;

class Billing extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->config->set_item('csrf_protection', FALSE);
        $this->load->model('invoices_model', 'invocies');
        $this->load->model('billing_model', 'billing');
        $this->load->library("Aauth");
        $this->load->library("Custom");
        $this->load->model('employee_model', 'employee');
        $this->load->library("dpdf");
    }

    public function view()
    {

        if (!$this->input->get()) {
            exit();
        }

        $tid = $this->input->get('id');
        $token = $this->input->get('token');

        $validtoken = hash_hmac('ripemd160', $tid, $this->config->item('encryption_key'));

        if (hash_equals($token, $validtoken)) {

            $this->load->model('accounts_model');


            $data['id'] = $tid;
            $data['token'] = $token;

            $data['invoice'] = $this->invocies->invoice_details($tid, '', false);
            $data['acclist'] = $this->accounts_model->accountslist(false . $data['invoice']['loc']);
            $data['online_pay'] = $this->billing->online_pay_settings();
            $data['products'] = $this->invocies->invoice_products($tid);
            $data['activity'] = $this->invocies->invoice_transactions($tid);
            $data['attach'] = $this->invocies->attach($tid);
            if (CUSTOM) $data['c_custom_fields'] = $this->custom->view_fields_data($data['invoice']['cid'], 1, 1);
            $data['gateway'] = $this->billing->gateway_list('Yes');


            $data['employee'] = $this->invocies->employee($data['invoice']['eid']);

            $head['usernm'] = '';
            $head['title'] = "Invoice " . $data['invoice']['tid'];
            $this->load->view('billing/header', $head);
            $this->load->view('billing/view', $data);
            $this->load->view('billing/footer');
        }
    }


    public function quoteview()
    {
        if (!$this->input->get()) {
            exit();
        }
        $tid = intval($this->input->get('id'));
        $token = $this->input->get('token');
        $validtoken = hash_hmac('ripemd160', 'q' . $tid, $this->config->item('encryption_key'));
        if (hash_equals($token, $validtoken)) {
            $this->load->model('quote_model', 'quote');
            $this->load->model('accounts_model');
            $data['acclist'] = $this->accounts_model->accountslist();
            $tid = intval($this->input->get('id'));
            $data['id'] = $tid;
            $data['token'] = $token;
            $data['invoice'] = $this->quote->quote_details($tid);
            $data['attach'] = $this->quote->attach($tid);
            $data['products'] = $this->quote->quote_products($tid);
            $data['employee'] = $this->quote->employee($data['invoice']['eid']);
            $head['title'] = "Quote " . $data['invoice']['tid'];
            $head['usernm'] = '';
            $this->load->view('billing/header', $head);
            $this->load->view('billing/quoteview', $data);
            $this->load->view('billing/footer');
        }
    }


    // public function invoice($printing = true, $id = -1)
    // {
    //     $company = $this->settings->company_details(1);

    //     $productCounter = 0;
    //     $productsPerPage = 18;

    //     $hide_balance = 0;
    //     $tid = $id;
    //     if ($tid == -1) {
    //         $tid = $this->input->get('id');
    //         $hide_balance = ($this->input->get('hide_balance') == 1);
    //     }
    //     $data['id'] = $tid;
    //     $data['invoice'] = $this->invocies->invoice_details($tid, $this->limited);
    //     if ($data['invoice']['id']) $data['products'] = $this->invocies->invoice_products($tid);
    //     if ($data['invoice']['id']) $data['employee'] = $this->invocies->employee($data['invoice']['eid']);
    //     if ($data['invoice']['i_class'] == 1) {
    //         $pref = prefix(7);
    //     } else {
    //         $pref = $this->config->item('prefix');
    //     }
    //     //print_r($data['invoice']['csd']);die();
    //     //var_dump($data['products']); exit; 
    //     ob_end_clean();
    //     ob_start();

    //     $sql = 'SELECT sum(total)-sum(pamnt) as "cust_balance" FROM geopos_invoices WHERE status="due" and csd="' . $data["invoice"]["csd"] . '" or status="partial" and csd="' . $data["invoice"]["csd"] . '"';
    //     $query = $this->db->query($sql);
    //     $response = $query->result_array();
    //     $cust_balance_inv = $response[0]['cust_balance'];

    //     // $cust_balance_inv= $response[0]['cust_balance'];
    //     $sql2 = "SELECT content FROM `geopos_notes` ORDER BY `geopos_notes`.`id`  DESC LIMIT 1";
    //     $query2 = $this->db->query($sql2);
    //     $response2 = $query2->result_array();
    //     $content2 = $response2[0]['content'];

    //     $sql2 = 'SELECT data FROM `geopos_custom_data` where rid="' . $data["invoice"]["csd"] . '"';
    //     $query2 = $this->db->query($sql2);
    //     $response2 = $query2->result_array();
    //     $custom_fields = $response2[0]['data'];

    //     $sql = 'Update geopos_invoices set print_status=1 where id= ' . $tid . ' ';
    //     $this->db->query($sql);



    //     ini_set('memory_limit', '64M');

    //     $data_tr = "";

    //     $left_logo = base_url("userfiles/company/" . $company['logo']);
    //     $data['left_logo'] = $left_logo;
    //     $right_logo = base_url("assets/images/halal_logo.jpg");
    //     $data['right_logo'] = $right_logo;
    //     ob_end_clean();
    //     if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
    //         $header = '<html><head><style>@page { margin: 350px 50px 200px; } header{ position: fixed;  top: -340px;}
    //     table{width:100%;}.pagenum:after {content: counter(page)}</style> </head><body><header>';
    //         $header .= '<table style="width:100%;">
    //     <tr style="border:1px solid #1367A4;">
    //     <td style="width:100%;"><img src="' . $left_logo . '" height="165px" width="270px"></td>
    //     <td style="width:40%;"><img src="' . $right_logo . '" height="60px" width="60px"></td>
    //     <td style="width:100%;"><br><b>' . $company['cname'] . '</b><br>' . $company['address'] . '<br>' . $company['city'] . ' ' . $company['region'] . ', ' . $company['postbox'] . '<br>' . $company['country'] . ' <br> Phone:&nbsp;&nbsp;&nbsp;&nbsp;' . $company['phone'] . ' <br> 
    //         <table style="border: 1px solid #1367A4;
    //         border-collapse: collapse;margin-left:-45px;margin-top:10px; border-radius: 6px;width:320px;">
    //         <tr style="">
    //              <th  style="border: 1px solid #1367A4; width:80px;
    //              border-collapse: collapse;font-size: 13px;text-align: center" >Date
    //                 </th>
    //                 <th  style="border: 1px solid #1367A4;
    //                 border-collapse: collapse;font-size: 13px;text-align: center">Invoice
    //                 </th>
    //                 <th  style="border: 1px solid #1367A4;
    //                 border-collapse: collapse;font-size: 13px;text-align: center">A/C
    //                 </th>
    //                  <th  style="border: 1px solid #1367A4;
    //                 border-collapse: collapse;font-size: 13px; text-align: center">EC NO 
    //                 </th>
    //                 <th  style="border: 1px solid #1367A4;
    //                 border-collapse: collapse;font-size: 13px; text-align: center">VAT Reg
    //                 </th>
    //                 </tr>
    //                 <tr >
    //                     <td style="width:30%;font-size: 13px;border: 1px solid #1367A4;border-collapse: collapse;text-align: center">
    //                     ' . date("d-m-Y", strtotime($data["invoice"]["invoicedate"])) . '
    //                     </td>
    //                     <td style="font-size: 13px;border: 1px solid #1367A4;border-collapse: collapse;text-align: center">' . $data['invoice']['tid'] . '</td>
    //                     <td style="font-size: 13px;border: 1px solid #1367A4;border-collapse: collapse;text-align: center">' . $data["invoice"]["name"] . '</td>
    //                     <td style="font-size: 13px;border: 1px solid #1367A4;border-collapse: collapse;text-align: center">882 /2004</td>
    //                     <td style="font-size: 13px;border: 1px solid #1367A4;border-collapse: collapse;text-align: center">299367533</td>
    //                 </tr>
    //                 </table>
    //             </td>
    //             </tr></table>';
    //     } else {
    //         $header = '<html><head><style>@page { margin: 350px 50px 200px; }header{ position: fixed;  top: -320px;}table{width:100%;}.pagenum:after {content: counter(page)}</style> </head><body><header><span style="float:right;"  class="pagenum">Page:</span>';
    //         $header .= ' <div style="width:100%; "><div style="width:23%; float:left; text-align:center;">  <br> Invoice#: ' . $data['invoice']['tid'] . '
    //             </div></div><div><p style="text-align:center;height:84px;">Note: This is not a valid Invoice  </p>
    //                 Date: ' . date("d-m-Y", strtotime($data["invoice"]["invoicedate"])) . '
    //             </div><div style="clear:both"></div>';
    //     }

    //     $header .= '<div style="border:1px solid #1367A4;width:40%;height:84px; padding: 8px 10px 8px; border-radius: 6px;float:left;margin-top:5px;">';
    //     if ($data['invoice']['company']) {
    //         $header .= $data['invoice']['company'] . '<br>';
    //     }

    //     if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
    //         $header .= $data['invoice']['cust_address'] . '<br/>' . $data['invoice']['cust_city'] . $data['invoice']['cust_postcode'];
    //         if ($data['invoice']['pamt_terms']) {
    //             $header .= '<br><b> <span style="background-color:yellow">TERMS:</span></b> ' . $custom_fields . '';
    //         }
    //         // if ($data['invoice']['country']) {
    //         //      $header.= '<br>' . $data['invoice']['country'];
    //         // }
    //     } else {
    //         $header .= $data['invoice']['cust_address'] . '<br/>' . $data['invoice']['cust_city'] . $data['invoice']['cust_postcode'];
    //         if ($data['invoice']['pamt_terms']) {
    //             $header .= '<br><b> <span style="background-color:yellow">TERMS:</span> </b>' . $custom_fields . '';
    //         }
    //     }
    //     // if ($data['invoice']['postbox']){
    //     //     $header.= $data['invoice']['cust_address'];
    //     // } 
    //     // if ($data['invoice']['email']){
    //     //     $header.= '<br> ' . $this->lang->line('Email') . ': ' . $data['invoice']['email'];
    //     // }

    //     $header .= '</div><div style="float:right;margin-left:-150px;margin-top:-30px;width:50%;" ><div style=" font-size: 25px;">';



    //     if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
    //         $header .= '<br> ';
    //         $header .= '</div><table style="height:5%;  font-size: 14px; border:1px solid #1367A4;border-radius: 6px;"> 
    //             <tr ><td ><b>' . $content2 . '</b></td>
    //             </tr>';
    //         $header .= '</table>';
    //     } else {
    //         $header .= '<span style="font-size: 32px; transform: translate(-78px, -38px)" >Day Pass</span>';
    //         if ($data['invoice']['driver_name'] != '' && $data['invoice']['vehicle_no'] != '') {
    //             $header .= '</div><table style="height:5%;  font-size: 14px; border:1px solid #1367A4;border-radius: 6px;"> 

    //                 <tr ><td ><b>Driver Name: </b></td></tr>
    //                 <tr ><td >' . $data['invoice']['driver_name'] . ' ' . $data['invoice']['vehicle_no'] . '</td>
    //                 </tr>';
    //             $header .= '</table>';
    //         }
    //     }



    //     $header .= '</div></div></header>';

    //     if ($data['invoice']['inv_type'] == 'INVOICE') {
    //         $header .= '

    //                     <table style="width:100%; font-size: 14px;  margin-top:-43px;"><tr><td style="width: 100%;">&nbsp;<b> 
    //                     &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    //                     For Bank Transfer:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $company['cname'] . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;S/C&nbsp;&nbsp;' . $company['sortcode'] . '           &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;AC&nbsp;&nbsp;' . $company['taxid'] . '  
    //                     </b></td></tr></table>

    //                     ';
    //     }
    //     $header .= '<div style="clear:both"></div> <main><div style="width:100%; margin-top:-17px; "><table width="100%" class="myProducts" style="border:1px solid #1367A4;border-radius: 6px;"><thead><tr style="font-size: 14px; background:#ccc; padding:4px;"><th style="width: 12%; text-align:left; ">Quantity</th><th style="border-left: 1px solid #1367A4;width: 45%; text-align:left;">Details</th><th style="width: 13%; text-align:left; border-left: 1px solid #1367A4;">Unit Price</th><th style="width: 20%; text-align:left; border-left: 1px solid #1367A4;">Net Amount</th><th style="width: 10%;  text-align:left;border-left: 1px solid #1367A4;">VAT</th></tr></thead>';
    //     $header .= '<tbody style="font-size:15px;">';

    //     $ns = 0;
    //     $sub_t = 0;
    //     foreach ($data['products'] as $row) {
    //         if ($row['qty'] > 0) {
    //             $productCounter++;
    //             $ns++;

    //             if ($productCounter % $productsPerPage === 0) {
    //                 if ($productCounter > 0) {
    //                     // Close the previous table if it's not the first page
    //                     $header .= '</tbody>';
    //                     $header .= '</table>';
    //                 }

    //                 // Start a new page
    //                 $header .= '<div style="page-break-after: always;"></div>';
    //                 $header .= '<table style="border:1px solid #1367A4;border-radius: 6px;">';
    //                 $header .= '<thead>';
    //                 $header .= '<tr style="font-size: 14px; background: #ccc; padding: 4px;">';
    //                 $header .= '<th style="width: 12%; text-align: left;">Quantity</th>';
    //                 $header .= '<th style="border-left: 1px solid #1367A4; width: 45%; text-align: left;">Details</th>';
    //                 $header .= '<th style="width: 13%; text-align: left; border-left: 1px solid #1367A4;">Unit Price</th>';
    //                 $header .= '<th style="width: 20%; text-align: left; border-left: 1px solid #1367A4;">Net Amount</th>';
    //                 $header .= '<th style="width: 10%; text-align: left; border-left: 1px solid #1367A4;">VAT</th>';
    //                 $header .= '</tr>';
    //                 $header .= '</thead>';
    //                 $header .= '<tbody>';
    //             }
    //             $sub_t += $row['price'] * $row['qty'];
    //             $up =     amountExchange($row['price']);
    //             $vat = amountExchange($row['totaltax']);
    //             $netamount = amountExchange($row['subtotal'] - $row['totaltax']);
    //             if (empty($row['product_des']) || $row['product_des'] == "") {
    //             } else {
    //                 if ($row['serial'] == '1') {
    //                     $header .= '<tr style="background:#FBF36D; style="font-size:18px;"">';
    //                 } else {
    //                     $header .= '<tr ' . (($ns % 2 == 0) ? 'style=" background: #f1f4fb;"' : '') . '>';
    //                 }
    //                 $header .= '<td style=" border-bottom:1px solid white;">' . $row["qty"] .  '</td style=" border-bottom:1px solid white;"><td style=" border-bottom:1px solid white;">' . $row["product_des"] . '</td><td style=" border-bottom:1px solid white;">' . substr($up, 3, 100) . '</td> <td style=" border-bottom:1px solid white;">' . substr($netamount, 3, 100) . '</td><td style=" border-bottom:1px solid white;">' . substr($vat, 3, 100) . ' </td></tr>';
    //             }
    //         }
    //     }
    //     $header .= '</tbody></table></div></main>';


    //     if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {

    //         $header .= '<footer style="position:absolute;bottom:-195px;">';
    //         if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
    //             $header .= '<div style="border:1px solid #1367A4;width:59%;height:170px; padding: 8px 0px 5px 4px; border-radius: 6px;float:left;"><span style="color:rgb(48, 37, 133)"><b>*MAXIMUM CHANGE ACCEPTED=&#163;60</b></span><div style=" font-size: 10px;">
    //         *All claims must be notified at the time of delivery,<br>
    //         any claims made after delivery will not be considered.<br>
    //         Goods will remain the property of ' . $company['cname'] . ' <br>until paid in full by the customer.<br>
    //         Frozen and chilled products cannot be returned once delivered.<br>
    //         </div><br><div style="width:100%"><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left">Customer:</div><div style="border:1px solid black;width:8%;height:18px;float:left;margin-top:10px;margin-left:-178px;"></div><div style="border:1px solid black;width:8%;height:18px;float:left;margin-top:10px;margin-left:-58px;"></div><div style="border:1px solid black;width:8%;height:18px;float:left;margin-top:10px;margin-left:-118px;"></div><div style="float:left;margin-left:-178px;margin-top:-5px;font-size: 12px;"><b>Cash &nbsp;&nbsp; Card Payment &nbsp;&nbsp;BACS</b></div><div style="clear:both"></div><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left;">Driver: <b>' . $data['invoice']['driver_name'] . '  ' . $data['invoice']['vehicle_no'] . '</b></div><div style="font-size: 17px;padding-left:10px;border:1px solid black;width:35%;height:20px;float:left;padding-top:3px;padding-bottom:3px;margin-left:-187px;margin-top:3px">&#163;</div><div style="clear:both"></div></div><div style="clear:both"></div></div>';
    //         }

    //         $sub_ts = 0;
    //         foreach ($data['products'] as $row) {
    //             $sub_ts += $row['price'] * $row['qty'];
    //         }

    //         $header .= '<div style="float:right;margin-left:320px;width:280px" ><table class="maintable2" style="width:100%;border-radius: 6px; border:1px solid #1367A4;font-size: 14px;">';

    //         if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
    //             if (!$hide_balance) {
    //                 //    $header.='<tr ><td style="width: 50%;  background: #f1f4fb;padding: 4px 0px 4px 7px;">&nbsp;Previous Balance</td><td>' ;
    //                 //     $invbalance = 0 ;
    //                 //    if($cust_balance_inv >= $data['invoice']['total']){ 
    //                 //        $invbalance =  $cust_balance_inv -$data['invoice']['total']; 
    //                 //        $header.=  substr( amountExchange($invbalance, $data['invoice']['multi'], $data['invoice']['loc']),3,100);  
    //                 //        }else{ 
    //                 //             $header.=  substr(amountExchange($cust_balance_inv, $data['invoice']['multi'], $data['invoice']['loc']),3,100);    } 
    //                 //             $header.='</td></tr>'; 
    //             }
    //         }

    //         $header .= '<tr><td style="width: 60%;  background: #f1f4fb;padding:  4px 0px 4px 7px;">&nbsp;Total NET Amount</td><td> ' . $sub_ts . ' </td></tr><tr ><td style="width: 60%;  background: #f1f4fb;padding: 4px 0px 4px 7px;">&nbsp;Total VAT Amount</td><td> ' . $data["invoice"]["tax"] . '  </td></tr><tr><td style="width: 60%;  background: #f1f4fb;padding: 4px 0px 4px 7px;">&nbsp;Invoice Total</td><td> ' . $data["invoice"]["total"] . '   </td></tr>';


    //         if ($data['invoice']['inv_type'] == 'INVOICE' || $data['invoice']['inv_type'] == 'DAYPASS' && $data['invoice']['company'] != 'COUNTER SALE') {
    //             if (!$hide_balance) {
    //                 $header .= '<tr ><td style="width: 50%;  background: #f1f4fb;padding: 4px 0px 4px 7px;">&nbsp;Previous Balance</td><td>';
    //                 $invbalance = 0;
    //                 if ($cust_balance_inv >= $data['invoice']['total']) {
    //                     $invbalance =  $cust_balance_inv - $data['invoice']['total'];
    //                     $header .=  substr(amountExchange($invbalance, $data['invoice']['multi'], $data['invoice']['loc']), 3, 100);
    //                 } else {
    //                     $header .=  substr(amountExchange($cust_balance_inv, $data['invoice']['multi'], $data['invoice']['loc']), 3, 100);
    //                 }
    //                 $header .= '</td></tr>';
    //                 $header .= ' <tr><td style="width: 60%; background: #f1f4fb;padding:  4px 0px 4px 7px;">&nbsp;Balance Now Due</td><td>' . $cust_balance_inv . ' </td></tr> ';
    //             }
    //         }
    //         $header .= '</table>
    //         <span href="mailto:' . $company['email'] . '"><img src="' . base_url("assets/images/mail.jpg") . '" height="32px" width="38px" style="margin-top:10px;">  </span>' . $company['email'] . '<br>
    //         <!--<span href="https://www.cloudbillingmanager.com/"><img src="' . base_url("assets/images/web.jpg") . '" height="32px" width="38px" style="margin-top:10px;"> </span>www.cloudbillingmanager.com -->
    //         </div><div style="clear:both">
    //         <span style="text-align: center;margin-left:70px;">Thank you for your business!</span>
    //                 </div>';

    //         if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
    //         }
    //         $header .= ' </footer></body></html>';
    //     } else {


    //         $header .= '<footer style="position:absolute;bottom:-195px;">';
    //         //if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE' ){
    //         $header .= '<div style="border:1px solid #1367A4;width:50%;height:140px; padding: 8px 0px 5px 4px; border-radius: 6px;float:left;margin-top:5px;display:none"><span style="color:rgb(48, 37, 133)"><b>*MAXIMUM CHANGE ACCEPTED=&#163;25</b></span><div style=" font-size: 10px;">All goods remain the property of ' . $company['cname'] . ' 
    //         until all monies are paid in full<br>Any complaints should be made within 48 hours of purchasing.</div><br><div style="width:100%"><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left">Print Name:</div><div style="border:1px solid black;width:10%;height:18px;float:left;margin-left:10px;;margin-left:-78px;"></div><div style="float:left;margin-left:-158px;margin-top:3px;font-size: 12px;"><b>Cash Tendered</b></div><div style="clear:both"></div><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left;">Signature:</div><div style="font-size: 17px;padding-left:10px;border:1px solid black;width:35%;height:20px;float:left;padding-top:3px;padding-bottom:3px;margin-left:-157px;margin-top:-8px">&#163;</div><div style="clear:both"></div></div><div style="clear:both"></div></div>';
    //         //}

    //         $sub_ts = 0;
    //         foreach ($data['products'] as $row) {
    //             $sub_ts += $row['price'] * $row['qty'];
    //         }

    //         $header .= '<div style="float:right;margin-left:320px;width:280px" ><table class="maintable2" style="width:100%; border:1px solid #1367A4;font-size: 14px;">';


    //         //  $inv_total_amt=$data["invoice"]["total"]+$data["invoice"]["tax"];

    //         $inv_total_amt = $data["invoice"]["total"];
    //         $sub_ts = number_format($sub_ts, 2, '.', '');

    //         $header .= '<tr><td style="width: 60%;  background: #f1f4fb;padding:  4px 0px 4px 7px;">&nbsp;Total NET Amount</td><td> ' . $sub_ts . ' </td></tr><tr ><td style="width: 60%;  background: #f1f4fb;padding: 4px 0px 4px 7px;">&nbsp;Total VAT Amount</td><td> ' . $data["invoice"]["tax"] . '  </td></tr><tr><td style="width: 60%;  background: #f1f4fb;padding: 4px 0px 4px 7px;">&nbsp;Invoice Total</td><td> ' . $inv_total_amt . '   </td></tr>';
    //         if (!$hide_balance) {
    //             $header .= '<tr ><td style="width: 50%;  background: #f1f4fb;padding: 4px 0px 4px 7px;">&nbsp;Previous Balance</td><td>';
    //             $invbalance = 0;
    //             if ($cust_balance_inv >= $data['invoice']['total']) {
    //                 $invbalance =  $cust_balance_inv - $data['invoice']['total'];
    //                 $header .=  substr(amountExchange($invbalance, $data['invoice']['multi'], $data['invoice']['loc']), 3, 100);
    //             } else {
    //                 $header .=  substr(amountExchange($cust_balance_inv, $data['invoice']['multi'], $data['invoice']['loc']), 3, 100);
    //             }
    //             $header .= '</td></tr>';
    //             $header .= ' <tr><td style="width: 60%; background: #f1f4fb;padding:  4px 0px 4px 7px;">&nbsp;Balance Now Due</td><td>' . $cust_balance_inv . ' </td></tr> ';
    //         }

    //         $header .= '</table></div><div style="clear:both"></div>';

    //         //if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE' ){ 
    //         $header .= '<p style="font-size:8px;display:none"> ' . $company['cname'] . ' is the trading name of ' . $company['cname'] . ', Company No. 11671004. &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Bank Account Details: Sort Code: ' . $company['sortcode'] . ' A/C No: ' . $company['taxid'] . '</p>';
    //         //  } 
    //         $header .= ' </footer></body></html>';
    //     }
    //     if (!$this->input->get()) {
    //         exit();
    //     }
    //     $tid = intval($this->input->get('id'));
    //     $token = $this->input->get('token');
    //     $validtoken = hash_hmac('ripemd160', 's' . $tid, $this->config->item('encryption_key'));

    //     if (hash_equals($token, $validtoken)) {

    //         $this->load->model('invoices_model', 'invocies');
    //         $this->load->model('accounts_model');
    //         $data['acclist'] = $this->accounts_model->accountslist();
    //         $data['attach'] = $this->invocies->attach($tid);
    //         $tid = intval($this->input->get('id'));
    //         $data['id'] = $tid;
    //         $data['token'] = $token;
    //         $data['invoice'] = $this->invocies->purchase_details($tid);
    //         // $data['online_pay'] = $this->purchase->online_pay_settings();
    //         $data['products'] = $this->invocies->purchase_products($tid);
    //         $data['activity'] = $this->invocies->purchase_transactions($tid);

    //         $head['title'] = "Order " . $data['invoice']['tid'];
    //         $data['employee'] = $this->invocies->employee($data['invoice']['eid']);
    //         $head['usernm'] = '';

    //         $id = $this->aauth->get_user()->id;
    //         $data['employeee'] = $this->employee->employee_details($id);
    //         $data['eid'] = intval($id);

    //         // $this->load->view('billing/header', $head);
    //         // $this->load->view('billing/invoice', $data);
    //         // $this->load->view('billing/footer');
    //         // }
    //         // $file_name = preg_replace('/[^A-Za-z0-9]+/', '-', 'Invoice__' . $data['invoice']['name'] . '_' . $data['invoice']['tid']);
    //         // $tid = $id;
    //         // if($tid == -1) {
    //         //     $tid = $this->input->get('id');
    //         // }
    //         $file_name = $tid;
    //         $this->dpdf->loadHtml($header);
    //         $this->dpdf->render();
    //         $output =  $this->dpdf->output();
    //         $file_location = 'userfiles/invoices/' . $file_name . '.pdf';
    //         file_put_contents($file_location, $output);
    //         if ($printing) {
    //             $this->dpdf->stream("" . $file_name . ".pdf", array("Attachment" => 0));
    //         }
    //     }
    // }

    //  for invoice format


    public function invoice($printing = true, $id = -1)
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

        $sql = 'Update geopos_invoices set print_status=1 where id= ' . $tid . ' ';
        $this->db->query($sql);



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
        $header .= '<div style="clear:both"></div> <main><div style="width:100%; margin-top:-17px; "><table width="100%" class="myProducts" style="border:1px solid #1367A4;border-radius: 6px;"><thead><tr style="font-size: 14px; background:#ccc; padding:4px;"><th style="width: 12%; text-align:left; ">Quantity</th><th style="border-left: 1px solid #1367A4;width: 45%; text-align:left;">Details</th><th style="width: 13%; text-align:left; border-left: 1px solid #1367A4;">Unit Price</th><th style="width: 20%; text-align:left; border-left: 1px solid #1367A4;">Net Amount</th><th style="width: 10%;  text-align:left;border-left: 1px solid #1367A4;">' . getTaxName() . '</th></tr></thead>';
        $header .= '<tbody style="font-size:15px;">';

        $ns = 0;
        $sub_t = 0;
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
                $sub_t += $row['price'] * $row['qty'];
                $up =     amountExchange($row['price']);
                $vat = amountExchange($row['tax'] * $row['qty']);
                $netamount = amountExchange($row['price'] * $row['qty']);
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
                    <td style=" border-bottom:1px solid white;">' . substr($up, 3, 100) . '</td>
                     <td style=" border-bottom:1px solid white;">' . substr($netamount, 3, 100) . '</td>
                     <td style=" border-bottom:1px solid white;">' . substr($vat, 3, 100) . ' </td></tr>';
                }
            }
        }
        $header .= '</tbody></table></div></main>';
        $sub_ts = 0;
        $vats = 0;
        foreach ($data['products'] as $row) {
            $sub_ts += $row['price'] * $row['qty'];
            $vats += $row['tax'] * $row['qty'];
        }
        $total = $sub_ts + $vats;

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

            $header .= '<tr><td style="width: 60%;  background: #f1f4fb;padding:  4px 0px 4px 7px;">&nbsp;Total NET Amount</td><td> ' . amountExchange($sub_ts) . ' </td></tr>
            <tr ><td style="width: 60%;  background: #f1f4fb;padding: 4px 0px 4px 7px;">&nbsp;Total VAT Amount</td><td> ' . amountExchange($vats) . '  </td></tr>
            <tr><td style="width: 60%;  background: #f1f4fb;padding: 4px 0px 4px 7px;">&nbsp;Invoice Total</td><td> ' . amountExchange($total) . '   </td></tr>';


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
            <td> ' . amountExchange($sub_ts) . ' </td></tr><tr ><td style="width: 60%;  background: #f1f4fb;padding: 4px 0px 4px 7px;">&nbsp;Total VAT Amount</td>
            <td> ' . amountExchange($vats) . '  </td>
            </tr><tr><td style="width: 60%;  background: #f1f4fb;padding: 4px 0px 4px 7px;">&nbsp;Invoice Total</td><td> ' . amountExchange($total) . '   </td></tr>';
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
        $file_location = 'userfiles/invoices/' . $file_name . '.pdf';
        file_put_contents($file_location, $output);
        if ($printing) {
            $this->dpdf->stream("" . $file_name . ".pdf", array("Attachment" => 0));
        }
    }
    public function invoice2()
    {
        $company = $this->settings->company_details(1);

        $tid = $this->input->get('id');
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

        $sql = 'Update geopos_invoices set print_status=1 where id= ' . $tid . ' ';
        $this->db->query($sql);



        ini_set('memory_limit', '64M');

        $data_tr = "";

        $left_logo = base_url("userfiles/company/" . $company['logo']);
        $data['left_logo'] = $left_logo;
        $right_logo = base_url("assets/images/halal_logo.jpg");
        $data['right_logo'] = $right_logo;
        ob_end_clean();
        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            $header = '<html><head><style>@page { margin: 350px 50px 200px; }header{ position: fixed;  top: -320px;}table{width:100%;}.pagenum:after {content: counter(page)}</style> </head><body><header>';
            $header .= ' <div style="width:100%; "><div style="width:23%; float:left; text-align:center;">  <img src="' . $left_logo . '" height="150px" width="180px"> </div><div style="width:48%; margin-left:20px; float:left; text-align:center;"> <div style="font-size: 30px;"><b>' . $company['cname'] . '</b></div><div style="font-size: 14px;"><div>' . $company['address'] . '</div><div>' . $company['region'] . ', ' . $company['city'] . ' ' . $company['postbox'] . ' ' . $company['country'] . '</div><div>Phone: ' . $company['phone'] . '&nbsp;&nbsp;&nbsp;&nbsp; Mobile:&nbsp;&nbsp;' . $company['mobile'] . '</div><div>Email: ' . $company['email'] . '</div></div> </div><span style="float:right;"  class="pagenum">Page:</span><div style="width:25%; float:left; text-align:center;">  <img src="' . $right_logo . '" height="100px" width="100px"> 
                        </div></div><div style="clear:both"></div>';
        } else {
            $header = '<html><head><style>@page { margin: 350px 50px 200px; }header{ position: fixed;  top: -320px;}table{width:100%;}.pagenum:after {content: counter(page)}</style> </head><body><header><span style="float:right;"  class="pagenum">Page:</span>';
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
        <th style="width: 12%; text-align:left; ">Quantity</th>
        <th style="border-left: 1px solid #1367A4;width: 45%; text-align:left;">Details</th>
        <th style="width: 13%; text-align:left; border-left: 1px solid #1367A4;">Unit Price</th>
        <th style="width: 20%; text-align:left; border-left: 1px solid #1367A4;">Net Amount</th>
        <th style="width: 10%;  text-align:left;border-left: 1px solid #1367A4;">' . getTaxName() . '</th></tr></thead>';
        $header .= '<tbody style="font-size:15px;">';

        $ns = 0;
        $sub_t = 0;
        foreach ($data['products'] as $row) {
            $ns++;
            $sub_t += $row['price'] * $row['qty'];
            $up =     amountExchange($row['price'], $invoice['multi'], $invoice['loc']);
            $vat = amountExchange($row['tax'] * $row['qty']);
            $netamount = amountExchange($row['price'] * $row['qty']);
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
                <td style=" border-bottom:1px solid white;">' . substr($up, 3, 100) . '</td>
                 <td style=" border-bottom:1px solid white;">' . substr($netamount, 3, 100) . '</td>
                 <td style=" border-bottom:1px solid white;">' . substr($vat, 3, 100) . ' </td></tr>';
            }
        }
        $header .= '</tbody></table></div></main>';
        $sub_ts = 0;
        $vats = 0;
        foreach ($data['products'] as $row) {
            $sub_ts += $row['price'] * $row['qty'];
            $vats += $row['tax'] * $row['qty'];
        }
        $total = $sub_ts + $vats;

        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {

            $header .= '<footer style="position:absolute;bottom:-195px">';
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

            $header .= '<tr><td style="width: 60%;  background: lightgrey;padding:  4px 0px 4px 7px;">&nbsp;Total NET Amount</td><td> ' . amountExchange($sub_ts) . ' </td></tr>
            <tr ><td style="width: 60%;  background: lightgrey;padding: 4px 0px 4px 7px;">&nbsp;Total VAT Amount</td><td> ' . amountExchange($vats) . '  </td></tr>
            <tr><td style="width: 60%;  background: lightgrey;padding: 4px 0px 4px 7px;">&nbsp;Invoice Total</td><td> ' . amountExchange($total) . '   </td></tr>';
            if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {

                $header .= ' <tr><td style="width: 60%; background: lightgrey;padding:  4px 0px 4px 7px;">&nbsp;Balance Now Due</td><td>' . amountExchange($cust_balance_inv) . ' </td></tr> ';
            }
            $header .= '</table><span style="font-size:12px;"><b>' . $company['cname'] . '.</b></span></div><div style="clear:both"></div>';

            if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
                // $header .= '<p style="font-size:12px"> ' . $company['cname'] . ' is the trading name of ' . $company['cname'] . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Bank Account Details: &nbsp;&nbsp;<b>Sort Code ' . $company['sortcode'] . '</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>A/C N0.&nbsp; ' . $company['taxid'] . '</b></p>
                // $header .= '<p style="font-size:12px">' . $company['cname'] . ' is the trading name of ' . $company['cname'] . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Bank Account Details: &nbsp;&nbsp;<b> ' . $company['taxid'] . '</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Sort Code: &nbsp; <b>' . $company['sortcode'] . '</b></p>
                $header .= '
                    <p style="font-size:12px">
                       Bank Account Details: Sort Code: <b>' . $company['sortcode'] . '</b> A/C No:<b> ' . $company['taxid'] . '
                       </p>
                ';

                // ';
            }
            $header .= ' </footer></body></html>';
        } else {


            $header .= '<footer style="position:absolute;bottom:-195px">';
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

            $header .= '<tr><td style="width: 60%;  background: lightgrey;padding:  4px 0px 4px 7px;">&nbsp;Total NET Amount</td>
            <td> ' . amountExchange($sub_ts) . ' </td></tr><tr >
            <td style="width: 60%;  background: lightgrey;padding: 4px 0px 4px 7px;">&nbsp;Total VAT Amount</td><td> ' . amountExchange($vats) . '  </td>
            </tr><tr><td style="width: 60%;  background: lightgrey;padding: 4px 0px 4px 7px;">&nbsp;Invoice Total</td><td> ' . amountExchange($total) . '   </td></tr>';

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
    public function invoice3()
    {
        $company = $this->settings->company_details(1);

        $tid = $this->input->get('id');
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

        $sql = 'Update geopos_invoices set print_status=1 where id= ' . $tid . ' ';
        $this->db->query($sql);



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
            $header .= $data['invoice']['cust_address'] . ' ' . $data['invoice']['cust_city'] . '<br/>' . $data['invoice']['cust_postcode'] . "";
            if ($data['invoice']['pamt_terms']) {
                $header .= '<br><b> <span style="background-color:yellow">TERMS:</span> </b>' . $custom_fields . '';
            }
            // if ($data['invoice']['country']) {
            //      $header.= '<br>' . $data['invoice']['country'];
            // }
        } else {
            $header .= ' <br> ' . $data['invoice']['cust_address'] . ' ' . $data['invoice']['cust_city'] . '<br/>' . $data['invoice']['cust_postcode'] . "";
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
        $header .= '</div>';

        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            $header .= '<table style="width:100%; font-size: 14px; border:1px solid #25468D;">
           <tr><td style="width: 50%; border-bottom:1px solid #25468D;background: #f1f4fb;padding: 4px;">&nbsp;Invoice No.</td>
          <td  style="border-bottom:1px solid #25468D;">';
            $header .= '<tr><td style="width: 50%; border-bottom:1px solid #1367A4;background: lightgrey;padding: 8px 0px 8px 7px;">&nbsp;Invoice No.</td>
        <td  style="border-bottom:1px solid #1367A4;">';
            $header .= $data['invoice']['tid'];
            $header .= ' </td></tr><tr><td style="width: 50%; border-bottom:1px solid #1367A4;background: lightgrey;padding:  8px 0px 8px 7px;">&nbsp;Invoice Date</td><td style="border-bottom:1px solid #1367A4;"> ' . date("d-m-Y", strtotime($data["invoice"]["invoicedate"])) . ' </td></tr>';
        }

        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            $header .= $data['invoice']['tid'];
        } else {
            $header .= ' Day Pass ';
        }

        // $header .= ' </td></tr><tr><td style="width: 50%; border-bottom:1px solid #25468D;background: #f1f4fb;padding:  4px;">&nbsp;Invoice Date</td><td style="border-bottom:1px solid #25468D;"> ' . date("d-m-Y", strtotime($data["invoice"]["invoicedate"])) . ' </td></tr>';


        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
            $header .= '  <tr><td style="width: 50%; background: #f1f4fb;padding: 4px;">&nbsp;Account No.</td><td> ' . $data["invoice"]["name"] . '</td></tr>';
        } else {
        }
        $header .= '</table><div>';





        // if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
        //     $header .= ' &nbsp;VAT Reg No: 375 6160 81 ';
        // }
        $header .= '</div></div></header>';


        $header .= '<div style="clear:both"></div><main><div style="width:100%;  "><table width="100%" class="myProducts" style="border:1px solid #25468D;"><thead><tr style="font-size: 14px; background:#f1f4fb; padding:4px;"><th style="width: 12%; text-align:left; ">Quantity</th><th style="border-left: 1px solid #25468D;width: 45%; text-align:left;">Details</th><th style="width: 13%; text-align:left; border-left: 1px solid #25468D;">Unit Price</th><th style="width: 20%; text-align:left; border-left: 1px solid #25468D;">Net Amount</th><th style="width: 10%;  text-align:left;border-left: 1px solid #25468D;">' . getTaxName() . '</th></tr></thead>';
        $header .= '<tbody style="font-size:15px;">';

        $ns = 0;
        $sub_t = 0;
        foreach ($data['products'] as $row) {
            $ns++;
            $sub_t += $row['price'] * $row['qty'];
            $up =     amountExchange($row['price'], $invoice['multi'], $invoice['loc']);
            $vat = amountExchange($row['tax'] * $row['qty']);
            $netamount = amountExchange($row['price'] * $row['qty']);
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
                <td style=" border-bottom:1px solid white;">' . substr($up, 3, 100) . '</td> 
                <td style=" border-bottom:1px solid white;">' . substr($netamount, 3, 100) . '</td>
                <td style=" border-bottom:1px solid white;">' . substr($vat, 3, 100) . ' </td></tr>';
            }
        }
        $header .= '</tbody></table></div></main>';

        $sub_ts = 0;
        $vats = 0;
        foreach ($data['products'] as $row) {
            $sub_ts += $row['price'] * $row['qty'];
            $vats += $row['tax'] * $row['qty'];
        }
        $total = $sub_ts + $vats;
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

            $header .= '<tr><td style="width: 60%;  background: #f1f4fb;padding:  3px 8px; font-size:12px">&nbsp;Total NET Amount</td><td style="font-size: 12px"> ' . amountExchange($sub_ts) . ' </td></tr>
            <tr ><td style="width: 60%;  background: #f1f4fb;padding: 3px 8px; font-size:12px">&nbsp;Total VAT Amount</td><td style="font-size: 12px"> ' . amountExchange($vats) . '  </td></tr>
            <tr><td style="width: 60%;  background: #f1f4fb;padding: 3px 8px; font-size:12px">&nbsp;Invoice Total</td><td style="font-size: 12px"> ' . amountExchange($total) . '   </td></tr>';
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
            <td> ' . amountExchange($sub_ts) . ' </td></tr><tr ><td style="width: 60%;  background: #f1f4fb;padding: 4px 0px 4px 7px;">&nbsp;Total VAT Amount</td>
            <td> ' . amountExchange($vats) . '  </td></tr><tr><td style="width: 60%;  background: #f1f4fb;padding: 4px 0px 4px 7px;">
            &nbsp;Invoice Total</td><td> ' . amountExchange($total) . '   </td></tr>';

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
    public function invoice4()
    {
        $company = $this->settings->company_details(1);

        $tid = $this->input->get('id');
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

        $sql = 'Update geopos_invoices set print_status=1 where id= ' . $tid . ' ';
        $this->db->query($sql);



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


        $header .= '<div style="clear:both"></div><main><div style="width:100%;  "><table width="100%" class="myProducts"><thead><tr style="font-size: 14px; background:#f1f4fb; padding:4px;"><th style="width: 12%; text-align:left; ">Quantity</th><th style="border-left: 1px solid #25468D;width: 45%; text-align:left;">Details</th><th style="width: 13%; text-align:left; border-left: 1px solid #25468D;">Unit Price</th><th style="width: 20%; text-align:left; border-left: 1px solid #25468D;">Net Amount</th><th style="width: 10%;  text-align:left;border-left: 1px solid #25468D;">VAT</th></tr></thead>';
        $header .= '<tbody style="font-size:15px;">';

        $ns = 0;
        $sub_t = 0;
        foreach ($data['products'] as $row) {
            $ns++;
            $sub_t += $row['price'] * $row['qty'];
            $up =     amountExchange($row['price'], $invoice['multi'], $invoice['loc']);
            $vat = amountExchange($row['tax'] * $row['qty']);
            $netamount = amountExchange($row['price'] * $row['qty']);
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
                <td style=" border-bottom:1px solid white;">' . substr($up, 3, 100) . '</td> 
                <td style=" border-bottom:1px solid white;">' . substr($netamount, 3, 100) . '</td>
                <td style=" border-bottom:1px solid white;">' . substr($vat, 3, 100) . ' </td></tr>';
            }
        }
        $header .= '</tbody></table></div></main>';
        $sub_ts = 0;
        $vats = 0;
        foreach ($data['products'] as $row) {
            $sub_ts += $row['price'] * $row['qty'];
            $vats += $row['tax'] * $row['qty'];
        }
        $total = $sub_ts + $vats;

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

            $header .= '<tr><td style="width: 60%;  background: #f1f4fb;padding:  3px 8px; font-size:12px">&nbsp;Total NET Amount</td><td style="font-size: 12px"> ' . amountExchange($sub_ts) . ' </td></tr>
            <tr ><td style="width: 60%;  background: #f1f4fb;padding: 3px 8px; font-size:12px">&nbsp;Total VAT Amount</td><td style="font-size: 12px"> ' . amountExchange($vats) . '  </td></tr>
            <tr><td style="width: 60%;  background: #f1f4fb;padding: 3px 8px; font-size:12px">&nbsp;Invoice Total</td><td style="font-size: 12px"> ' . amountExchange($total) . '   </td></tr>';
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
            <td> ' . amountExchange($sub_ts) . ' </td></tr><tr ><td style="width: 60%;  background: #f1f4fb;padding: 4px 0px 4px 7px;">&nbsp;Total VAT Amount</td>
            <td> ' . amountExchange($vats) . '  </td></tr><tr><td style="width: 60%;  background: #f1f4fb;padding: 4px 0px 4px 7px;">&nbsp;Invoice Total</td>
            <td> ' . amountExchange($total) . '   </td></tr>';

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
    public function invoice5($printing = true, $id = -1)
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
                        <th style="border: 1px solid #1367A4; padding: 5px; text-align: left;">VAT Reg:</th>
                        <td style="border: 1px solid #1367A4; padding: 5px; text-align: right;">299367533</td>
                    </tr>
                </table>';
            $header .= '</div>'; // Closing the Right Box
        }
        $header .= '</div>';
        $header .= '</header>';  // Closing the header and HTML structure
        $header .= '<div style="clear:both"></div><main><div style="width:100%;  "><table width="100%" class="myProducts" style="border:1px solid #1367A4;"><thead><tr style="font-size: 14px; background:#ccc; padding:4px;"><th style="width: 12%; text-align:left; ">Quantity</th><th style="border-left: 1px solid #1367A4;width: 45%; text-align:left;">Details</th><th style="width: 13%; text-align:left; border-left: 1px solid #1367A4;">Unit Price</th><th style="width: 20%; text-align:left; border-left: 1px solid #1367A4;">Net Amount</th><th style="width: 10%;  text-align:left;border-left: 1px solid #1367A4;">VAT</th></tr></thead>';
        $header .= '<tbody style="font-size:15px;">';

        $ns = 0;
        $sub_t = 0;
        foreach ($data['products'] as $row) {
            $ns++;
            $sub_t += $row['price'] * $row['qty'];
            $up =     amountExchange($row['price'], $invoice['multi'], $invoice['loc']);
            $vat = amountExchange($row['tax'] * $row['qty']);
            $netamount = amountExchange($row['price'] * $row['qty']);
            // $vat = amountExchange($row['totaltax'], $invoice['multi'], $invoice['loc']);
            // $netamount = amountExchange($row['subtotal'] - $row['totaltax'], $invoice['multi'], $invoice['loc']);
            if (empty($row['product_des']) || $row['product_des'] == "") {
            } else {
                if ($row['serial'] == '1') {
                    $header .= '<tr style="background:#FBF36D; style="font-size:18px;"">';
                } else {
                    $header .= '<tr style=" border-bottom:1px solid white;">';
                }
                $header .= '<td style=" border-bottom:1px solid white;">' . (int)$row["qty"] . $row["unit"] . '</td style=" border-bottom:1px solid white;"><td style=" border-bottom:1px solid white;">' . $row["product_des"] . '</td><td style=" border-bottom:1px solid white;">' . substr($up, 3, 100) . '</td> <td style=" border-bottom:1px solid white;">' . substr($netamount, 3, 100) . '</td><td style=" border-bottom:1px solid white;">' . substr($vat, 3, 100) . ' </td></tr>';
            }
        }
        $header .= '</tbody></table></div></main>';
        $sub_ts = 0;
        $vats = 0;
        foreach ($data['products'] as $row) {
            $sub_ts += $row['price'] * $row['qty'];
            $vats += $row['tax'] * $row['qty'];
        }
        $total = $sub_ts + $vats;
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
                        <td style="border:1px solid #1367A4; width:20%; padding: 5px;">' . amountExchange($sub_ts) . '</td>
                        <td style="border:1px solid #1367A4; border-bottom:none; padding: 5px;"></td>
                        <td style="border:1px solid #1367A4; width:15%; padding: 5px;">Cash</td>
                        <td style="border:1px solid #1367A4; padding: 5px;"></td>
                        <td style="border:1px solid #1367A4; width:15%; padding: 5px;">Sign/Print</td>
                    </tr>';

                $header .= '<tr>
                        <td style="border:1px solid #1367A4; width:20%; padding: 5px;">Total VAT Amount</td>
                        <td style="border:1px solid #1367A4; width:20%; padding: 5px;">' . amountExchange($vats) . '</td>
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
                            <td style="text-align:center; font-weight:bold; padding: 5px;">' . amountExchange($total) . '</td>
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
                $sub_ts += $row['price'] * $row['qty'];
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
                            <td style="width: 50%; border: 1px solid #1367A4; padding: 4px 0px 4px 7px;">' . amountExchange($sub_ts) . '</td>
                        </tr>';

            // Add row for "Updated"
            $header .= '<tr>
                        <td style="width: 50%; border: 1px solid #1367A4; padding: 4px 0px 4px 7px;">Total VAT Amount</td>
                        <td style="width: 50%; border: 1px solid #1367A4; padding: 4px 0px 4px 7px;">' . amountExchange($vats) . '</td>
                    </tr>';

            // Add row for "Payment Allocated"
            $header .= '<tr>
                        <td style="width: 50%; border: 1px solid #1367A4; padding: 0px 0px 0px 7px;">Total Invoice Balance</td>
                        <td style="width: 50%; border: 1px solid #1367A4; padding: 0px 0px 0px 7px;">' . amountExchange($total) . '</td>
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
    public function invoice6($printing = true, $id = -1)
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
        <th style="width: 10%;  text-align:left;border-left: 1px solid #1367A4;">' . getTaxName() . '</th></tr></thead>';
        $header .= '<tbody style="font-size:15px;">';

        $ns = 0;
        $sub_t = 0;
        foreach ($data['products'] as $row) {
            $ns++;
            $sub_t += $row['price'] * $row['qty'];
            $up =     amountExchange($row['price'], $invoice['multi'], $invoice['loc']);
            $vat = amountExchange($row['tax'] * $row['qty']);
            $netamount = amountExchange($row['price'] * $row['qty']);
            // $vat = amountExchange($row['totaltax'], $invoice['multi'], $invoice['loc']);
            // $netamount = amountExchange($row['subtotal'] - $row['totaltax'], $invoice['multi'], $invoice['loc']);
            if (empty($row['product_des']) || $row['product_des'] == "") {
            } else {
                if ($row['serial'] == '1') {
                    $header .= '<tr style="background:#FBF36D; style="font-size:18px;"">';
                } else {
                    $header .= '<tr style=" border-bottom:1px solid white;">';
                }
                $header .= '<td style=" border-bottom:1px solid white;">' . (int)$row["qty"]. '</td style=" border-bottom:1px solid white;">
                <td style=" border-bottom:1px solid white;">' . $row["product_des"] . '</td><td style=" border-bottom:1px solid white;">' . substr($up, 3, 100) . '</td> 
                <td style=" border-bottom:1px solid white;">' . substr($netamount, 3, 100) . '</td><td style=" border-bottom:1px solid white;">' . substr($vat, 3, 100) . ' </td></tr>';
            }
        }
        $header .= '</tbody></table></div></main>';
        $sub_ts = 0;
        $vats = 0;
        foreach ($data['products'] as $row) {
            $sub_ts += $row['price'] * $row['qty'];
            $vats += $row['tax'] * $row['qty'];
        }
        $total = $sub_ts + $vats;

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

            $header .= '<tr><td style="width: 60%;  background: #f1f4fb;padding:  3px 8px; font-size:12px">&nbsp;Total NET Amount</td><td style="font-size: 12px"> ' . amountExchange($sub_ts) . ' </td></tr>
            <tr ><td style="width: 60%;  background: #f1f4fb;padding: 3px 8px; font-size:12px">&nbsp;Total VAT Amount</td><td style="font-size: 12px"> ' . amountExchange($vats) . '  </td></tr>
            <tr><td style="width: 60%;  background: #f1f4fb;padding: 3px 8px; font-size:12px">&nbsp;Invoice Total</td><td style="font-size: 12px"> ' . amountExchange($total) . '   </td></tr>';
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
            <td> ' . amountExchange($sub_ts) . ' </td></tr><tr ><td style="width: 60%;  background: #f1f4fb;padding: 4px 0px 4px 7px;">&nbsp;Total VAT Amount</td>
            <td> ' . amountExchange($vats) . '  </td></tr><tr><td style="width: 60%;  background: #f1f4fb;padding: 4px 0px 4px 7px;">&nbsp;Invoice Total</td>
            <td> ' . amountExchange($total) . '   </td></tr>';

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
    public function invoice7($printing = true, $id = -1)
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
            $sub_t += $row['price'] * $row['qty'];
            $up = amountExchange($row['price'], $invoice['multi'], $invoice['loc']);
            $vat = amountExchange($row['tax'] * $row['qty']);
            $netamount = amountExchange($row['price'] * $row['qty']);
            // $vat = amountExchange($row['totaltax'], $invoice['multi'], $invoice['loc']);
            // $netamount = amountExchange($row['subtotal'] - $row['totaltax'], $invoice['multi'], $invoice['loc']);

            if (!empty($row['product_des'])) {
                // Highlight row if serial equals 1
                $rowStyle = ($row['serial'] == '1') ? 'background:#FBF36D; font-size:18px;' : 'border-bottom:1px solid white;';

                $header .= '<tr style="' . $rowStyle . '">';
                $header .= '<td style="border: 1px solid #1367A4; text-align: center;">
                        <input type="checkbox" class="row-checkbox" style="margin: 0; vertical-align: middle;">
                    </td>';
                $header .= '<td style="border-bottom:1px solid white;">' . (int)$row["qty"].'</td>';
                $header .= '<td style="border-bottom:1px solid white;">' . $row["product_des"] . '</td>';
                $header .= '<td style="border-bottom:1px solid white;">' . substr($up, 3, 100) . '</td>';
                $header .= '<td style="border-bottom:1px solid white;">' . substr($netamount, 3, 100) . '</td>';
                $header .= '<td style="border-bottom:1px solid white;">' . substr($vat, 3, 100) . '</td>';
                $header .= '</tr>';
            }
        }

        $header .= '</tbody></table></div></main>';
        $sub_ts = 0;
        $vats = 0;
        foreach ($data['products'] as $row) {
            $sub_ts += $row['price'] * $row['qty'];
            $vats += $row['tax'] * $row['qty'];
        }
        $total = $sub_ts + $vats;
        if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {

            $header .= '<footer style="position:fixed; bottom:10px;">';
            if ($data['invoice']['inv_type'] == 'INVOICE' && $data['invoice']['company'] != 'COUNTER SALE') {
                $header .= '<div style="width: 70%; float: left;">';

                // Initialize subtotal
                $sub_ts = 0;
                foreach ($data['products'] as $row) {
                    $sub_ts += $row['price'] * $row['qty'];
                }

                // Define a reusable cell style

                // Start the table with proper borders and collapse styles
                $header .= '<table style="border:1px solid #1367A4; border-collapse: collapse; font-size:12px;">';

                // Add rows with proper border styling and alignment
                $header .= '<tr>
                        <td style="border:1px solid #1367A4; width:20%; padding: 5px;">Total NET Amount</td>
                        <td style="border:1px solid #1367A4; width:20%; padding: 5px;">' . amountExchange($sub_ts) . '</td>
                        <td style="border:1px solid #1367A4; border-bottom:none; padding: 5px;"></td>
                        <td style="border:1px solid #1367A4; width:15%; padding: 5px;">Cash</td>
                        <td style="border:1px solid #1367A4; padding: 5px;"></td>
                        <td style="border:1px solid #1367A4; width:15%; padding: 5px;">Sign/Print</td>
                    </tr>';

                $header .= '<tr>
                        <td style="border:1px solid #1367A4; width:20%; padding: 5px;">Total VAT Amount</td>
                        <td style="border:1px solid #1367A4; width:20%; padding: 5px;">' . amountExchange($vats) . '</td>
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
                            <td style="text-align:center; font-weight:bold; padding: 5px;">' . amountExchange($total) . '</td>
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
            $sub_ts = formatDecimal($sub_ts);

            $header .= '<tr><td style="width: 60%;  background: #f1f4fb;padding: 4px 0px 4px 7px;">&nbsp;Total NET Amount</td><td> ' . amountExchange($sub_ts) . ' </td></tr>
            <tr ><td style="width: 60%;  background: #f1f4fb;padding: 4px 0px 4px 7px;">&nbsp;Total VAT Amount</td><td> ' . amountExchange($vats)
                . '  </td></tr><tr><td style="width: 60%;  background: #f1f4fb;padding: 4px 0px 4px 7px;">&nbsp;
             Invoice Total</td><td> ' . amountExchange($total) . '   </td></tr>';

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


    //        public function invoice()
    // {
    //     if (!$this->input->get()) {
    //         exit();
    //     }
    //     $tid = intval($this->input->get('id'));
    //     $token = $this->input->get('token');
    //     $validtoken = hash_hmac('ripemd160', 's' . $tid, $this->config->item('encryption_key'));

    //     if (hash_equals($token, $validtoken)) {

    //         $this->load->model('invoices_model', 'invocies');
    //         $this->load->model('accounts_model');
    //         $data['acclist'] = $this->accounts_model->accountslist();
    //         $data['attach'] = $this->invocies->attach($tid);
    //         $tid = intval($this->input->get('id'));
    //         $data['id'] = $tid;
    //         $data['token'] = $token;
    //         $data['invoice'] = $this->invocies->purchase_details($tid);
    //         // $data['online_pay'] = $this->purchase->online_pay_settings();
    //         $data['products'] = $this->invocies->purchase_products($tid);
    //         $data['activity'] = $this->invocies->purchase_transactions($tid);

    //         $head['title'] = "Order " . $data['invoice']['tid'];
    //         $data['employee'] = $this->invocies->employee($data['invoice']['eid']);
    //         $head['usernm'] = '';

    //         $id = $this->aauth->get_user()->id;
    //         $data['employeee'] = $this->employee->employee_details($id);
    //         $data['eid'] = intval($id);

    //         $this->load->view('billing/header', $head);
    //         $this->load->view('billing/invoice', $data);
    //         $this->load->view('billing/footer');
    //     }
    // }





    public function purchase()
    {
        if (!$this->input->get()) {
            exit();
        }
        $tid = intval($this->input->get('id'));
        $token = $this->input->get('token');
        $validtoken = hash_hmac('ripemd160', 'p' . $tid, $this->config->item('encryption_key'));
        if (hash_equals($token, $validtoken)) {
            $this->load->model('purchase_model', 'purchase');
            $this->load->model('accounts_model');
            $data['acclist'] = $this->accounts_model->accountslist();
            $data['attach'] = $this->purchase->attach($tid);
            $tid = intval($this->input->get('id'));
            $data['id'] = $tid;
            $data['token'] = $token;
            $data['invoice'] = $this->purchase->purchase_details($tid);
            // $data['online_pay'] = $this->purchase->online_pay_settings();
            $data['products'] = $this->purchase->purchase_products($tid);
            $data['activity'] = $this->purchase->purchase_transactions($tid);
            $head['title'] = "Purchase " . $data['invoice']['tid'];
            $data['employee'] = $this->purchase->employee($data['invoice']['eid']);
            $head['usernm'] = '';
            $this->load->view('billing/header', $head);
            $this->load->view('billing/purchase', $data);
            $this->load->view('billing/footer');
        }
    }

    public function stockreturn()
    {
        if (!$this->input->get()) {
            exit();
        }
        $data['company'] = $this->settings->company_details(1);
        $data['logo'] = base_url("userfiles/company/" . $data['company']['logo']);
        $tid = intval($this->input->get('id'));
        $token = $this->input->get('token');
        $validtoken = hash_hmac('ripemd160', 's' . $tid, $this->config->item('encryption_key'));
        if (hash_equals($token, $validtoken)) {
            $this->load->model('stockreturn_model', 'stockreturn');
            $this->load->model('accounts_model');
            $data['acclist'] = $this->accounts_model->accountslist();
            $data['attach'] = $this->stockreturn->attach($tid);
            $tid = intval($this->input->get('id'));
            $data['id'] = $tid;
            $data['token'] = $token;
            $data['invoice'] = $this->stockreturn->purchase_details($tid);
            // dd($data['invoice']);
            // $data['online_pay'] = $this->purchase->online_pay_settings();
            $data['products'] = $this->stockreturn->purchase_products($tid);
            $data['activity'] = $this->stockreturn->purchase_transactions($tid);
            $head['title'] = "Order " . $data['invoice']['tid'];
            $data['employee'] = $this->stockreturn->employee($data['invoice']['eid']);
            $head['usernm'] = '';
            $this->load->view('billing/header', $head);
            $this->load->view('billing/stockreturn', $data);
            $this->load->view('billing/footer');
        }
    }




    public function creditnote()
    {
        if (!$this->input->get()) {
            exit();
        }
        $tid = intval($this->input->get('id'));
        $token = $this->input->get('token');
        $validtoken = hash_hmac('ripemd160', 's' . $tid, $this->config->item('encryption_key'));
        if (hash_equals($token, $validtoken)) {
            $this->load->model('stockreturn_model', 'stockreturn');
            $this->load->model('accounts_model');
            $data['acclist'] = $this->accounts_model->accountslist();
            $data['attach'] = $this->stockreturn->attach($tid);
            $tid = intval($this->input->get('id'));
            $data['id'] = $tid;
            $data['token'] = $token;
            // $data['invoice'] = $this->stockreturn->purchase_details($tid);
            // // $data['online_pay'] = $this->purchase->online_pay_settings();
            // $data['products'] = $this->stockreturn->purchase_products($tid);
            $data['invoice'] = $this->stockreturn->creditnote_details($tid);
            $data['products'] = $this->stockreturn->creditnote_products($tid);
            $data['activity'] = $this->stockreturn->purchase_transactions($tid);
            $head['title'] = "Order " . $data['invoice']['tid'];
            $data['employee'] = $this->stockreturn->employee($data['invoice']['eid']);
            $head['usernm'] = '';
            $this->load->view('billing/header', $head);
            $this->load->view('billing/creditnote', $data);
            $this->load->view('billing/footer');
        }
    }













    public function gateway()
    {
        if (!$this->input->get()) {
            exit();
        }
        $tid = intval($this->input->post('tid'));
        $token = $this->input->post('token');
        $amount = $this->input->post('p_amount');
        $pay_gateway = $this->input->post('pay_gateway');
        $validtoken = hash_hmac('ripemd160', $tid, $this->config->item('encryption_key'));
        if (hash_equals($token, $validtoken)) {
            switch ($pay_gateway) {
                case 1:
                    $this->card();
                    break;
            }
        }
    }


    public function printinvoice()
    {
        if (!$this->input->get()) {
            exit();
        }
        $tid = intval($this->input->get('id'));
        $token = $this->input->get('token');
        $validtoken = hash_hmac('ripemd160', $tid, $this->config->item('encryption_key'));
        if (hash_equals($token, $validtoken)) {
            $data['id'] = $tid;
            $data['invoice'] = $this->invocies->invoice_details($tid);
            $data['title'] = "Invoice " . $data['invoice']['tid'];
            $data['products'] = $this->invocies->invoice_products($tid);
            $data['employee'] = $this->invocies->employee($data['invoice']['eid']);
            if (CUSTOM) {
                $data['c_custom_fields'] = $this->custom->view_fields_data($data['invoice']['cid'], 1, 1);
                $data['i_custom_fields'] = $this->custom->view_fields_data($tid, 2, 1);
            }

            $left_logo = base_url("assets/images/prime_food_logo.jpg");
            $data['left_logo'] = $left_logo;
            $right_logo = base_url("assets/images/halal_logo.jpg");
            $data['right_logo'] = $right_logo;

            $data['round_off'] = $this->custom->api_config(4);
            if ($data['invoice']['i_class'] == 1) {
                $pref = prefix(7);
            } elseif ($data['invoice']['i_class'] > 1) {
                $pref = prefix(3);
            } else {
                $pref = $this->config->item('prefix');
            }
            $data['general'] = array('title' => $this->lang->line('Invoice'), 'person' => $this->lang->line('Customer'), 'prefix' => $pref, 't_type' => 0);
            ini_set('memory_limit', '64M');
            if ($data['invoice']['taxstatus'] == 'cgst' || $data['invoice']['taxstatus'] == 'igst') {
                $html = $this->load->view('print_files/invoice-a4-gst_v' . INVV, $data, true);
            } else {
                $html = $this->load->view('print_files/ptest', $data, true);
                //    $html=str_replace("strong","span",$html);
                //     $html=str_replace("<h","<span",$html);
            }
            //PDF Rendering
            $this->load->library('pdf');
            if (INVV == 1) {
                $header = $this->load->view('print_files/invoice-header_v1', $data, true);
                //  $header=str_replace("<h","<span",$header);
                $pdf = $this->pdf->load_split(array('margin_top' => 5));
                $pdf->SetHTMLHeader($header);
            }
            if (INVV == 2) {
                $pdf = $this->pdf->load_split(array('margin_top' => 5));
            }
            $pdf->SetHTMLFooter('<div style="text-align: right;font-family: serif; font-size: 8pt; color: #5C5C5C; font-style: italic;margin-top:-6pt;">{PAGENO}/{nbpg} #' . $data['invoice']['tid'] . '</div>');
            $pdf->WriteHTML($html);
            if ($this->input->get('d')) {
                $pdf->Output('Invoice_#' . $data['invoice']['tid'] . '.pdf', 'D');
            } else {
                $pdf->Output('Invoice_#' . $data['invoice']['tid'] . '.pdf', 'I');
            }
        }
    }


    public function printquote()
    {
        if (!$this->input->get()) {
            exit();
        }
        $tid = intval($this->input->get('id'));
        $token = $this->input->get('token');

        $validtoken = hash_hmac('ripemd160', 'q' . $tid, $this->config->item('encryption_key'));

        if (hash_equals($token, $validtoken)) {
            $this->load->model('quote_model', 'quote');
            $data['id'] = $tid;
            $data['title'] = "Quote $tid";
            $data['invoice'] = $this->quote->quote_details($tid);
            $data['products'] = $this->quote->quote_products($tid);
            $data['employee'] = $this->quote->employee($data['invoice']['eid']);
            $data['round_off'] = $this->custom->api_config(4);
            $data['general'] = array('title' => $this->lang->line('Quote'), 'person' => $this->lang->line('Customer'), 'prefix' => prefix(1), 't_type' => 1);

            ini_set('memory_limit', '64M');

            if ($data['invoice']['taxstatus'] == 'cgst' || $data['invoice']['taxstatus'] == 'igst') {
                $html = $this->load->view('print_files/invoice-a4-gst_v' . INVV, $data, true);
            } else {
                $html = $this->load->view('print_files/invoice-a4_v' . INVV, $data, true);
            }

            //PDF Rendering
            $this->load->library('pdf');
            if (INVV == 1) {
                $header = $this->load->view('print_files/invoice-header_v' . INVV, $data, true);
                $pdf = $this->pdf->load_split(array('margin_top' => 40));
                $pdf->SetHTMLHeader($header);
            }
            if (INVV == 2) {
                $pdf = $this->pdf->load_split(array('margin_top' => 5));
            }
            $pdf->SetHTMLFooter('<div style="text-align: right;font-family: serif; font-size: 8pt; color: #5C5C5C; font-style: italic;margin-top:-6pt;">{PAGENO}/{nbpg} #' . $data['invoice']['tid'] . '</div>');

            $pdf->WriteHTML($html);

            if ($this->input->get('d')) {

                $pdf->Output('Quote_#' . $tid . '.pdf', 'D');
            } else {
                $pdf->Output('Quote_#' . $tid . '.pdf', 'I');
            }
        }
    }


    public function printorder()
    {
        if (!$this->input->get()) {
            exit();
        }
        $tid = intval($this->input->get('id'));
        $token = $this->input->get('token');

        $validtoken = hash_hmac('ripemd160', 'p' . $tid, $this->config->item('encryption_key'));

        if (hash_equals($token, $validtoken)) {
            $this->load->model('purchase_model', 'purchase');

            $data['id'] = $tid;
            $data['title'] = "Invoice $tid";
            $data['invoice'] = $this->purchase->purchase_details($tid);
            $data['products'] = $this->purchase->purchase_products($tid);
            $data['employee'] = $this->purchase->employee($data['invoice']['eid']);
            $data['round_off'] = $this->custom->api_config(4);
            $data['general'] = array('title' => $this->lang->line('Purchase Order'), 'person' => $this->lang->line('Supplier'), 'prefix' => prefix(2), 't_type' => 0);
            ini_set('memory_limit', '64M');
            if ($data['invoice']['taxstatus'] == 'cgst' || $data['invoice']['taxstatus'] == 'igst') {
                $html = $this->load->view('print_files/invoice-a4-gst_v' . INVV, $data, true);
            } else {
                $html = $this->load->view('print_files/invoice-a4_v' . INVV, $data, true);
            }

            //PDF Rendering
            $this->load->library('pdf');
            if (INVV == 1) {
                $header = $this->load->view('print_files/invoice-header_v' . INVV, $data, true);
                $pdf = $this->pdf->load_split(array('margin_top' => 40));
                $pdf->SetHTMLHeader($header);
            }
            if (INVV == 2) {
                $pdf = $this->pdf->load_split(array('margin_top' => 5));
            }
            $pdf->SetHTMLFooter('<div style="text-align: right;font-family: serif; font-size: 8pt; color: #5C5C5C; font-style: italic;margin-top:-6pt;">{PAGENO}/{nbpg} #' . $data['invoice']['tid'] . '</div>');

            $pdf->WriteHTML($html);

            if ($this->input->get('d')) {

                $pdf->Output('Purchase_#' . $tid . '.pdf', 'D');
            } else {
                $pdf->Output('Purchase_#' . $tid . '.pdf', 'I');
            }
        }
    }





    public function printscreditnote()
    {
        if (!$this->input->get()) {
            exit();
        }
        $tid = intval($this->input->get('id'));
        $token = $this->input->get('token');
        $validtoken = hash_hmac('ripemd160', 's' . $tid, $this->config->item('encryption_key'));
        if (hash_equals($token, $validtoken)) {
            $this->load->model('stockreturn_model', 'stockreturn');
            $data['id'] = $tid;
            $data['title'] = "Invoice $tid";
            // $data['invoice'] = $this->stockreturn->purchase_details($tid);
            // $data['products'] = $this->stockreturn->purchase_products($tid);


            $data['invoice'] = $this->stockreturn->creditnote_details($tid);
            $data['products'] = $this->stockreturn->creditnote_products($tid);

            $data['employee'] = $this->stockreturn->employee($data['invoice']['eid']);
            $data['round_off'] = $this->custom->api_config(4);
            $ty = $this->input->get('ty');

            if ($ty < 2) {
                if ($data['invoice']['i_class'] == 1) {
                    $data['general'] = array('title' => $this->lang->line('Stock Return'), 'person' => $this->lang->line('Customer'), 'prefix' => prefix(4), 't_type' => 0);
                } else {
                    $data['general'] = array('title' => $this->lang->line('Stock Return'), 'person' => $this->lang->line('Supplier'), 'prefix' => prefix(4), 't_type' => 0);
                }
            } else {
                $data['general'] = array('title' => $this->lang->line('Credit Note'), 'person' => $this->lang->line('Customer'), 'prefix' => prefix(4), 't_type' => 0);
            }



            $sql = 'SELECT sum(total)-sum(pamnt) as "cust_balance" FROM geopos_invoices WHERE status="due" and csd="' . $data["invoice"]["csd"] . '" or status="partial" and csd="' . $data["invoice"]["csd"] . '"';
            $query = $this->db->query($sql);
            $response = $query->result_array();
            $cust_balance_inv = $response[0]['cust_balance'];

            ob_end_clean();
            ob_start();

            ini_set('memory_limit', '64M');

            $data_tr = "";

            $left_logo = base_url("assets/images/dixy_logo_.jpeg");
            $data['left_logo'] = $left_logo;
            $right_logo = base_url("assets/images/halal_logo.jpg");
            $data['right_logo'] = $right_logo;
            ob_end_clean();

            $header = '<html><head><style>@page { margin: 350px 50px 200px; }header{ position: fixed;  top: -320px;}
table{width:100%;}.pagenum:after {content: counter(page)}</style> </head><body><header>';
            $header .= '<table style="width:100%;">
<tr style="border:1px solid #1367A4;">
  <td style="width:100%;"><img src="' . $left_logo . '" height="165px" width="270px"></td>
  <td style="width:40%;"><img src="' . $right_logo . '" height="60px" width="60px"></td>
  <td style="width:100%;"><b>5 Star Food Distribution Ltd.</b><br>Uplands B, Unit 2B-1 <br> Blackhorse Lane <br>London, E17 5QJ <br> Phone:&nbsp;&nbsp;&nbsp;&nbsp;0203 417 7077 <br> Mobile:&nbsp;&nbsp; 0789 413 4060 
    <table style="border: 1px solid #1367A4;
    border-collapse: collapse;margin-left:-45px;margin-top:10px; border-radius: 6px;width:320px;">
    <tr >
                 <th  style="border: 1px solid #1367A4;
                 border-collapse: collapse;font-size: 13px;text-align: center;width:80px;" >Date
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
         ' . $data['invoice']['invoicedate'] . '
        </td>
        <td style="font-size: 13px;border: 1px solid #1367A4;border-collapse: collapse;text-align: center">' . $data['invoice']['tid'] . '</td>
        <td style="font-size: 13px;border: 1px solid #1367A4;border-collapse: collapse;text-align: center">Stock Return</td>
         <td style="font-size: 13px;border: 1px solid #1367A4;border-collapse: collapse;text-align: center">882 /2004</td>
        <td style="font-size: 13px;border: 1px solid #1367A4;border-collapse: collapse;text-align: center">299367533</td>
    </tr>
    </table>
  </td>
</tr></table>';
            $header .= '<div style="border:1px solid #1367A4;width:40%;height:100px; padding: 8px 10px 8px; border-radius: 6px;float:left;margin-top:5px;">';

            $header .= $data['invoice']['company'] . '<br>';


            $header .= $data['invoice']['city'] . ' <br/>' . $data['invoice']['postbox'] . ' <br/>' . $data['invoice']['address'];
            $header .= '</div><div style="float:right;margin-left:-150px;margin-top:-30px;width:50%;" ><div style=" font-size: 25px;">';


            $header .= '<br> ';
            $header .= '</div><table style="height:5%;  font-size: 14px; border:1px solid #1367A4;border-radius: 6px;"> 
<tr ><td ><b>' . $data['invoice']['notes'] . '</b></td>
</tr>';
            $header .= '</table>';

            $header .= '</div></div></header>';
            $header .= '
           
            <table style="width:100%; font-size: 14px;  margin-top:-15px;"><tr><td style="width: 100%;">&nbsp;<b> 
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            For Bank Transfer:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;5 Star Food Distribution Ltd.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;S/C&nbsp;&nbsp;20-98-98           &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;AC&nbsp;&nbsp;33821986  
            </b></td></tr></table>
            
            ';
            $header .= '<div style="clear:both"></div> <main><div style="width:100%; margin-top:5px; "><table width="100%" class="myProducts" style="border:1px solid #1367A4;border-radius: 6px;"><thead><tr style="font-size: 14px; background:#ccc; padding:4px;"><th style="width: 12%; text-align:left; ">Quantity</th><th style="border-left: 1px solid #1367A4;width: 45%; text-align:left;">Details</th><th style="width: 13%; text-align:left; border-left: 1px solid #1367A4;">Unit Price</th><th style="width: 20%; text-align:left; border-left: 1px solid #1367A4;">Net Amount</th><th style="width: 20%; text-align:left; border-left: 1px solid #1367A4;">Net VAT</th></tr></thead>';
            $header .= '<tbody style="font-size:15px;">';

            $ns = 0;
            $sub_t = 0;

            foreach ($data['products'] as $row) {
                $ns++;

                $sub_t += $row['price'] * $row['qty'];
                $up =     amountExchange($row['price']);
                $vat = amountExchange($row['tax'] * $row['qty']);
                $netamount = amountExchange($row['subtotal'] - $row['totaltax']);
                $header .= '<tr><td style=" border-bottom:1px solid white;">' . $row["qty"] . $row["unit"] . '</td style=" border-bottom:1px solid white;"><td style=" border-bottom:1px solid white;">' . $row["product_des"] . '</td><td style=" border-bottom:1px solid white;">' . substr($up, 3, 100) . '</td> <td style=" border-bottom:1px solid white;">' . substr($netamount, 3, 100) . '</td><td style=" border-bottom:1px solid white;">' . substr($vat, 3, 100) . ' </td></tr>';

                if (empty($row['product_des']) || $row['product_des'] == "") {
                    //   $header.='<td style=" border-bottom:1px solid white;">' . $row["qty"] . $row["unit"] . '</td style=" border-bottom:1px solid white;"><td style=" border-bottom:1px solid white;">' . $row["product"] . '</td><td style=" border-bottom:1px solid white;">' . substr($up,3,100). '</td> <td style=" border-bottom:1px solid white;">' . substr($netamount,3,100) . '</td></tr>';


                } else {
                    if (isset($row['serial'])) {
                        if ($row['serial'] == '1') {
                            $header .= '<tr style="background:#FBF36D; style="font-size:18px;"">';
                        } else {
                            $header .= '<tr style=" border-bottom:1px solid white;">';
                        }
                    }
                }
            }
            $header .= '</tbody></table></div></main>';
            $header .= '<footer style="position:absolute;bottom:-195px">';

            $header .= '<div style="border:1px solid #1367A4;width:59%;height:170px; padding: 8px 0px 5px 4px; border-radius: 6px;float:left;"><span style="color:rgb(48, 37, 133)"><b>*MAXIMUM CHANGE ACCEPTED=&#163;60</b></span><div style=" font-size: 10px;">
*All claims must be notified at the time of delivery,<br>
any claims made after delivery will not be considered.<br>
Goods will remain the property of 5 Star Foods<br>
Distribution Ltd until paid in full by the customer.<br>
Frozen and chilled products cannot be returned once delivered.<br>
</div><br><div style="width:100%"><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left">Customer:</div><div style="border:1px solid black;width:8%;height:18px;float:left;margin-top:10px;margin-left:-178px;"></div><div style="border:1px solid black;width:8%;height:18px;float:left;margin-top:10px;margin-left:-58px;"></div><div style="border:1px solid black;width:8%;height:18px;float:left;margin-top:10px;margin-left:-118px;"></div><div style="float:left;margin-left:-178px;margin-top:-5px;font-size: 12px;"><b>Cash &nbsp;&nbsp; Card Payment &nbsp;&nbsp;BACS</b></div><div style="clear:both"></div><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left;">Driver:</div><div style="font-size: 17px;padding-left:10px;border:1px solid black;width:35%;height:20px;float:left;padding-top:3px;padding-bottom:3px;margin-left:-187px;margin-top:3px">&#163;</div><div style="clear:both"></div></div><div style="clear:both"></div></div>';


            $sub_ts = 0;
            foreach ($data['products'] as $row) {
                $sub_ts += $row['price'] * $row['qty'];
            }

            $header .= '<div style="float:right;margin-left:320px;width:280px" ><table class="maintable2" style="width:100%;border-radius: 6px; border:1px solid #1367A4;font-size: 14px;">';
        }

        $header .= '<tr><td style="width: 60%;  background: lightgrey;padding:  4px 0px 4px 7px;">&nbsp;Total NET Amount</td><td> ' . $sub_ts . ' </td><tr><td style="width: 60%;  background: lightgrey;padding: 4px 0px 4px 7px;">&nbsp;VAT Total</td><td> ' . $data["invoice"]["tax"] . '</td></tr></tr><tr><td style="width: 60%;  background: lightgrey;padding: 4px 0px 4px 7px;">&nbsp;Invoice Total</td><td> ' . $data["invoice"]["total"] . '   </td></tr>';


        $header .= ' <tr><td style="width: 60%; background: lightgrey;padding:  4px 0px 4px 7px;">&nbsp;Balance Now Due</td><td>' . $cust_balance_inv . ' </td></tr> ';

        $header .= '</table>
 <span href="mailto:fivestarfooduk@gmail.com"><img src="' . base_url("assets/images/mail.jpg") . '" height="32px" width="38px" style="margin-top:10px;">  </span>fivestarfooduk@gmail.com<br>
 <span href="https://www.5starfoodsltd.com/"><img src="' . base_url("assets/images/web.jpg") . '" height="32px" width="38px" style="margin-top:10px;"> </span>www.5starfoodsltd.com 
 </div><div style="clear:both">
 <span style="text-align: center;margin-left:70px;">Thank you for your business!</span>
 </div>';

        $header .= ' 
    
    ';

        $header .= ' </footer></body></html>';


        $file_name = $this->input->get('id');
        $this->dpdf->loadHtml($header);
        $this->dpdf->render();
        $output =  $this->dpdf->output();
        $file_location = 'userfiles/creditnotes/' . $file_name . '.pdf';
        file_put_contents($file_location, $output);
        $this->dpdf->stream("" . $file_name . ".pdf", array("Attachment" => 0));
    }











































    public function printstockreturn()
    {
        $company = $this->settings->company_details(1);
        if (!$this->input->get()) {
            exit();
        }
        $tid = intval($this->input->get('id'));
        $token = $this->input->get('token');
        $validtoken = hash_hmac('ripemd160', 's' . $tid, $this->config->item('encryption_key'));
        if (hash_equals($token, $validtoken)) {
            $this->load->model('stockreturn_model', 'stockreturn');
            $data['id'] = $tid;
            $data['title'] = "Invoice $tid";
            $data['invoice'] = $this->stockreturn->purchase_details($tid);
            $data['products'] = $this->stockreturn->purchase_products($tid);
            $data['employee'] = $this->stockreturn->employee($data['invoice']['eid']);
            $data['round_off'] = $this->custom->api_config(4);
            $ty = $this->input->get('ty');

            if ($ty < 2) {
                if ($data['invoice']['i_class'] == 1) {
                    $data['general'] = array('title' => $this->lang->line('Stock Return'), 'person' => $this->lang->line('Customer'), 'prefix' => prefix(4), 't_type' => 0);
                } else {
                    $data['general'] = array('title' => $this->lang->line('Stock Return'), 'person' => $this->lang->line('Supplier'), 'prefix' => prefix(4), 't_type' => 0);
                }
            } else {
                $data['general'] = array('title' => $this->lang->line('Credit Note'), 'person' => $this->lang->line('Customer'), 'prefix' => prefix(4), 't_type' => 0);
            }



            $sql = 'SELECT sum(total)-sum(pamnt) as "cust_balance" FROM geopos_invoices WHERE status="due" and csd="' . $data["invoice"]["csd"] . '" or status="partial" and csd="' . $data["invoice"]["csd"] . '"';
            $query = $this->db->query($sql);
            $response = $query->result_array();
            $cust_balance_inv = $response[0]['cust_balance'];

            ob_end_clean();
            ob_start();

            ini_set('memory_limit', '64M');

            $data_tr = "";

            $left_logo = base_url("userfiles/company/" . $company['logo']);
            $data['left_logo'] = $left_logo;
            $right_logo = base_url("assets/images/halal_logo.jpg");
            $data['right_logo'] = $right_logo;
            // $left_logo = base_url("assets/images/dixy_logo_.jpeg");
            // $data['left_logo'] = $left_logo;
            // $right_logo = base_url("assets/images/halal_logo.jpg");
            // $data['right_logo'] = $right_logo;
            ob_end_clean();

            $header = '<html><head><style>@page { margin: 350px 50px 200px; }header{ position: fixed;  top: -320px;}
            table{width:100%;}.pagenum:after {content: counter(page)}</style> </head><body><header>';
            $header .= '<table style="width:100%;">
            <tr style="border:1px solid #1367A4;">
                <td style="width:100%;"><img src="' . $left_logo . '" height="165px" width="270px"></td>
                <td style="width:40%;"><img src="' . $right_logo . '" height="60px" width="60px"></td>
                <td style="width:100%;"><br><b>' . $company['cname'] . '</b><br>' . $company['address'] . ' ' . $company['city'] . ' '
                . $company['region'] . ', ' . $company['postbox'] . ', ' . $company['country'] . ' <br> Phone:&nbsp;&nbsp;&nbsp;' . $company['phone'] . ' <br> Mobile:&nbsp;&nbsp;' . $company['mobile'] . ' <br>
            <table style="border: 1px solid #1367A4;
            border-collapse: collapse;margin-left:-45px;margin-top:10px; border-radius: 6px;width:320px;">
            <tr >
                 <th  style="border: 1px solid #1367A4;
                 border-collapse: collapse;font-size: 13px;text-align: center;width:80px;" >Date
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
                ' . $data['invoice']['invoicedate'] . '
                </td>
                <td style="font-size: 13px;border: 1px solid #1367A4;border-collapse: collapse;text-align: center">' . $data['invoice']['tid'] . '</td>
                <td style="font-size: 13px;border: 1px solid #1367A4;border-collapse: collapse;text-align: center">Stock Return</td>
                <td style="font-size: 13px;border: 1px solid #1367A4;border-collapse: collapse;text-align: center">882 /2004</td>
                <td style="font-size: 13px;border: 1px solid #1367A4;border-collapse: collapse;text-align: center">299367533</td>
            </tr>
            </table>
        </td>
        </tr></table>';
            $header .= '<div style="border:1px solid #1367A4;width:40%;height:100px; padding: 8px 10px 8px; border-radius: 6px;float:left;margin-top:5px;">';

            $header .= $data['invoice']['company'] . '<br>';


            $header .= $data['invoice']['city'] . ' <br/>' . $data['invoice']['postbox'] . ' <br/>' . $data['invoice']['address'];
            $header .= '</div><div style="float:right;margin-left:-150px;margin-top:-30px;width:50%;" ><div style=" font-size: 25px;">';


            $header .= '<br> ';
            $header .= '</div><table style="height:5%;  font-size: 14px; border:1px solid #1367A4;border-radius: 6px;"> 
                        <tr ><td ><b>' . $data['invoice']['notes'] . '</b></td>
                        </tr>';
            $header .= '</table>';

            $header .= '</div></div></header>';
            $header .= '
           
            <table style="width:100%; font-size: 14px;  margin-top:-15px;"><tr><td style="width: 100%;">&nbsp;<b> 
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            For Bank Transfer:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;5 Star Food Distribution Ltd.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;S/C&nbsp;&nbsp;20-98-98        
               &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;AC&nbsp;&nbsp;33821986  
            </b></td></tr></table>
            
            ';
            $header .= '<div style="clear:both"></div> <main><div style="width:100%; margin-top:5px; ">
            <table width="100%" class="myProducts" style="border:1px solid #1367A4;border-radius: 6px;"><thead>
            <tr style="font-size: 14px; background:#ccc; padding:4px;"><th style="width: 12%; text-align:left; ">Quantity</th>
            <th style="border-left: 1px solid #1367A4;width: 20%; text-align:left;">Product Code</th>
            <th style="border-left: 1px solid #1367A4;width: 35%; text-align:left;">Details</th>
            <th style="width: 13%; text-align:left; border-left: 1px solid #1367A4;">Unit Price</th>
            <th style="width: 15%; text-align:left; border-left: 1px solid #1367A4;">Net Amount</th>
            <th style="width: 15%; text-align:left; border-left: 1px solid #1367A4;">Net VAT</th></tr></thead>';
            $header .= '<tbody style="font-size:15px;">';

            $ns = 0;
            $sub_t = 0;

            foreach ($data['products'] as $row) {
                $ns++;

                $sub_t += $row['price'] * $row['qty'];
                $up =     amountExchange($row['price']);
                $vat = amountExchange($row['tax'] * $row['qty']);
                $netamount = amountExchange($row['subtotal'] - $row['totaltax']);
                $header .= '<tr><td style=" border-bottom:1px solid white;">' . $row["qty"] . '</td style=" border-bottom:1px solid white;">
                <td style=" border-bottom:1px solid white;">' . $row["code"] . '</td>
                <td style=" border-bottom:1px solid white;">' . $row["product_des"] . '</td>
                <td style=" border-bottom:1px solid white;">' . substr($up, 3, 100) . '</td> 
                <td style=" border-bottom:1px solid white;">' . substr($netamount, 3, 100) . '</td><td style=" border-bottom:1px solid white;">' . substr($vat, 3, 100) . ' </td></tr>';

                if (empty($row['product_des']) || $row['product_des'] == "") {
                    //   $header.='<td style=" border-bottom:1px solid white;">' . $row["qty"] . $row["unit"] . '</td style=" border-bottom:1px solid white;"><td style=" border-bottom:1px solid white;">' . $row["product"] . '</td><td style=" border-bottom:1px solid white;">' . substr($up,3,100). '</td> <td style=" border-bottom:1px solid white;">' . substr($netamount,3,100) . '</td></tr>';


                } else {
                    if (isset($row['serial'])) {
                        if ($row['serial'] == '1') {
                            $header .= '<tr style="background:#FBF36D; style="font-size:18px;"">';
                        } else {
                            $header .= '<tr style=" border-bottom:1px solid white;">';
                        }
                    }
                }
            }
            $header .= '</tbody></table></div></main>';
            $header .= '<footer style="position:absolute;bottom:-195px">';

            $header .= '<div style="border:1px solid #1367A4;width:59%;height:170px; padding: 8px 0px 5px 4px; border-radius: 6px;float:left;"><span style="color:rgb(48, 37, 133)"><b>*MAXIMUM CHANGE ACCEPTED=&#163;60</b></span><div style=" font-size: 10px;">
*All claims must be notified at the time of delivery,<br>
any claims made after delivery will not be considered.<br>
Goods will remain the property of 5 Star Foods<br>
Distribution Ltd until paid in full by the customer.<br>
Frozen and chilled products cannot be returned once delivered.<br>
</div><br><div style="width:100%"><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left">Customer:</div><div style="border:1px solid black;width:8%;height:18px;float:left;margin-top:10px;margin-left:-178px;"></div><div style="border:1px solid black;width:8%;height:18px;float:left;margin-top:10px;margin-left:-58px;"></div><div style="border:1px solid black;width:8%;height:18px;float:left;margin-top:10px;margin-left:-118px;"></div><div style="float:left;margin-left:-178px;margin-top:-5px;font-size: 12px;"><b>Cash &nbsp;&nbsp; Card Payment &nbsp;&nbsp;BACS</b></div><div style="clear:both"></div><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left;">Driver:</div><div style="font-size: 17px;padding-left:10px;border:1px solid black;width:35%;height:20px;float:left;padding-top:3px;padding-bottom:3px;margin-left:-187px;margin-top:3px">&#163;</div><div style="clear:both"></div></div><div style="clear:both"></div></div>';


            $sub_ts = 0;
            $total_vat = 0;
            foreach ($data['products'] as $row) {
                $sub_ts += $row['price'] * $row['qty'];
                $total_vat += $row['tax'] * $row['qty'];
            }

            $header .= '<div style="float:right;margin-left:320px;width:280px" ><table class="maintable2" style="width:100%;border-radius: 6px; border:1px solid #1367A4;font-size: 14px;">';
        }

        $header .= '<tr><td style="width: 60%;  background: lightgrey;padding:  4px 0px 4px 7px;">&nbsp;Total NET Amount</td><td> ' . amountExchange($sub_ts) . ' </td>
        <tr><td style="width: 60%;  background: lightgrey;padding: 4px 0px 4px 7px;">&nbsp;VAT Total</td><td> ' . amountExchange($total_vat) . '</td></tr>
        </tr><tr><td style="width: 60%;  background: lightgrey;padding: 4px 0px 4px 7px;">&nbsp;Invoice Total</td><td> ' . amountExchange($data["invoice"]["total"]) . '   </td></tr>';


        $header .= ' <tr><td style="width: 60%; background: lightgrey;padding:  4px 0px 4px 7px;">&nbsp;Balance Now Due</td><td>' . amountExchange($cust_balance_inv) . ' </td></tr> ';

        $header .= '</table>
 <span href="mailto:fivestarfooduk@gmail.com"><img src="' . base_url("assets/images/mail.jpg") . '" height="32px" width="38px" style="margin-top:10px;">  </span>fivestarfooduk@gmail.com<br>
 <span href="https://www.5starfoodsltd.com/"><img src="' . base_url("assets/images/web.jpg") . '" height="32px" width="38px" style="margin-top:10px;"> </span>www.5starfoodsltd.com 
 </div><div style="clear:both">
 <span style="text-align: center;margin-left:70px;">Thank you for your business!</span>
 </div>';

        $header .= ' 
    
    ';

        $header .= ' </footer></body></html>';


        $file_name = $this->input->get('id');
        $this->dpdf->loadHtml($header);
        $this->dpdf->render();
        $output =  $this->dpdf->output();
        $file_location = 'userfiles/returninvoices/' . $file_name . '.pdf';
        file_put_contents($file_location, $output);
        $this->dpdf->stream("" . $file_name . ".pdf", array("Attachment" => 0));
    }



    public function card()
    {
        if (!$this->input->get()) {
            exit();
        }
        $data['redirect_u'] = '';
        if (isset($_COOKIE['pos_set'])) {
            $data['redirect_u'] = $_COOKIE['pos_set'];
            setcookie("pos_set", null, -1, '/');
        }
        $online_pay = $this->billing->online_pay_settings();
        if ($online_pay['enable'] == 0) {
            exit();
        }
        $data['tid'] = $this->input->get('id');
        $data['token'] = $this->input->get('token');
        $data['itype'] = $this->input->get('itype');
        $data['gid'] = $this->input->get('gid');
        if ($data['itype'] == 'inv') {
            $validtoken = hash_hmac('ripemd160', $data['tid'], $this->config->item('encryption_key'));
            if (hash_equals($data['token'], $validtoken)) {
                $data['invoice'] = $this->invocies->invoice_details($data['tid'], '', false);
                $data['company'] = location($data['invoice']['loc']);
            } else {
                exit();
            }
        }
        switch ($data['gid']) {
            case 1:
                $fname = 'stripe';
                break;
            case 2:
                $fname = 'authorize';
                break;
            case 3:
                $fname = 'pinpay';
                break;
            case 4:
                $fname = 'paypal';
                break;
            case 5:
                $fname = 'securepay';
                break;
            case 6:
                $fname = 'checkout';
                break;
            case 7:
                $fname = 'payumoney';
                break;
            case 8:
                $fname = 'razor';
                break;
            default:
                $fname = 'stripe';
                break;
        }
        $online_pay = $this->billing->online_pay_settings();
        $data['gateway'] = $this->billing->gateway($data['gid']);
        if ($online_pay['enable'] == 1) {
            $this->load->view('billing/header');
            $this->load->view('gateways/card_' . $fname, $data);
            $this->load->view('billing/footer');
        } else {
            echo '<h3>' . $this->lang->line('Online Payment Service') . '</h3>';
        }
    }

    public function process_card()
    {
        if (!$this->input->post()) {
            exit();
        }
        $tid = $this->input->post('id', true);
        $itype = $this->input->post('itype', true);
        $gateway = $this->input->post('gateway', true);

        $amount = number_format($this->input->post('amount', true), 2, '.', '');

        if ($itype == 'inv') {
            $customer = $this->invocies->invoice_details($tid, null, false);
            if (!$customer['tid']) {
                exit();
            }
        }
        $hash = $this->input->post('token', true);

        $cardNumber = $this->input->post('cardNumber', true);
        $cardExpiry = $this->input->post('cardExpiry', true);
        $cardCVC = $this->input->post('cardCVC', true);
        $nmonth = substr($cardExpiry, 0, 2);
        $nyear = '20' . substr($cardExpiry, 5, 2);
        $note = 'Card Payment for #' . $customer['tid'];
        $pmethod = 'Card';
        $amount_o = $amount;
        // COMMENTED OUT: Multi-currency support
        // if ($customer['multi'] > 0) {
        //     $multi_currency = $this->invocies->currency_d($customer['multi']);
        //     $gateway_data['currency'] = $multi_currency['code'];
        //     $note .= ' (Currency Conversion Applied)';
        // }
        // if ($customer['loc'] > 0) {
        //     $multi_currency = $this->invocies->currency_d($customer['multi'], $customer['loc']);
        //     $gateway_data['currency'] = $multi_currency['code'];
        //     $note .= ' (Currency Conversion Applied)';
        // }
        
        // NEW: Always use geopos_system.currency
        $query = $this->db->query("SELECT currency FROM geopos_system WHERE id=1 LIMIT 1");
        $row = $query->row_array();
        $gateway_data['currency'] = $row['currency'];
        $validtoken = hash_hmac('ripemd160', $tid, $this->config->item('encryption_key'));
        $gateway_data = $this->billing->gateway($gateway);
        $surcharge = ($amount * $gateway_data['surcharge']) / 100;
        $amount_t = $amount + $surcharge;
        $amount = number_format($amount_t, 2, '.', '');
        if (hash_equals($hash, $validtoken)) {
            switch ($gateway) {
                case 1:

                    $response = $this->stripe($this->input->post('paymentMethodId', true), number_format($amount, 0, '', ''), $gateway_data, $tid, $customer, '', $this->input->post('paymentIntentId', true));
                    break;
                case 2:
                    $response = $this->authorizenet($cardNumber, $nmonth, $nyear, $cardCVC, $amount, $tid, $gateway_data, $customer);
                    break;
                case 3:
                    $response = $this->pinpay($cardNumber, $nmonth, $nyear, $cardCVC, $amount, $tid, $gateway_data, $customer);
                    break;
                case 4:
                    $response = $this->paypal($cardNumber, $nmonth, $nyear, $cardCVC, $amount, $tid, $gateway_data, $customer);
                    break;
                case 5:
                    $response = $this->securepay($cardNumber, $nmonth, $nyear, $cardCVC, $amount, $tid, $gateway_data);
                    break;
                case 6:
                    $response = $this->twocheckout($this->input->post('auth_token', true), $amount, $tid, $gateway_data, $customer);
                    break;
            }
            // Process response

            if ($gateway > 1) {
                if ($response->isSuccessful()) {

                    $amount_o = rev_amountExchange_s($amount_o, $customer['multi'], $customer['loc']);
                    if ($this->billing->paynow($tid, $amount_o, $note, $pmethod, $customer['loc'])) {
                        header('Content-Type: application/json');
                        echo json_encode(array('status' => 'Success', 'message' =>
                        $this->lang->line('Thank you for the payment') . " <a href='" . base_url('billing/view?id=' . $tid . '&token=' . $hash) . "' class='btn btn-info btn-lg'><span class='icon-file-text2' aria-hidden='true'></span> " . $this->lang->line('View') . "</a>"));
                    }
                } elseif ($response->isRedirect()) {
                    // Redirect to offsite payment gateway
                    $response->redirect();
                } else {
                    // Payment failed
                    echo json_encode(array('status' => 'Error', 'message' =>
                    $this->lang->line('Payment failed')));
                }
            } elseif ($gateway == 1 and @$response['status'] == 'succeeded') {
                $amount_o = rev_amountExchange_s(($amount - $surcharge) / 100, $customer['multi'], $customer['loc']);
                if ($this->billing->paynow($tid, $amount_o, $note, $pmethod, $customer['loc'])) {
                    header('Content-Type: application/json');
                    echo json_encode(array('status' => 'Success', 'clientSecret' => $response['clientSecret'], 'message' =>
                    $this->lang->line('Thank you for the payment') . " <a href='" . base_url('billing/view?id=' . $tid . '&token=' . $hash) . "' class='btn btn-info btn-lg'><span class='icon-file-text2' aria-hidden='true'></span> " . $this->lang->line('View') . "</a>"));
                }
            } elseif ($gateway == 1 and @$response['status'] == 'error') {
                header('Content-Type: application/json');
                echo json_encode(array('error' => $response['message']));
            }
        }
    }


    private function stripe($token, $amount, $gateway_data, $tid, $customer, $currency = '', $token_id = '')
    {
        require_once APPPATH . 'third_party/stripe-php/vendor/autoload.php';
        \Stripe\Stripe::setApiKey($gateway_data['key1']);
        try {
            if ($token) {
                // Create new PaymentIntent with a PaymentMethod ID from the client.
                $intent = \Stripe\PaymentIntent::create([
                    "amount" => $amount,
                    "currency" => $gateway_data['currency'],
                    "payment_method" => $token,
                    "confirmation_method" => "manual",
                    "confirm" => true,
                    // If a mobile client passes `useStripeSdk`, set `use_stripe_sdk=true`
                    // to take advantage of new authentication features in mobile SDKs
                    "use_stripe_sdk" => true,

                ]);
                switch ($intent->status) {
                    case "succeeded":

                        return array('status' => 'succeeded', 'paid_amount' => $intent->amount, 'clientSecret' => $intent->client_secret);
                        break;
                }
                // After create, if the PaymentIntent's status is succeeded, fulfill the order.
            } else if ($token_id) {
                // Confirm the PaymentIntent to finalize payment after handling a required action
                // on the client.

                $intent = \Stripe\PaymentIntent::retrieve($token_id);
                $intent->confirm();
                // After confirm, if the PaymentIntent's status is succeeded, fulfill the order.
                switch ($intent->status) {
                    case "succeeded":

                        return array('status' => 'succeeded', 'paid_amount' => $intent->amount, 'clientSecret' => $intent->client_secret);
                        break;
                }
            }

            $output = $this->generateResponse($intent);

            echo json_encode($output);
        } catch (Stripe\Exception\CardException $e) {
            return array('status' => 'error', 'paid_amount' => 0, 'message' => $e->getMessage());
        }
    }


    private function authorizenet($cardNumber, $nmonth, $nyear, $cardCVC, $amount, $tid, $gateway_data, $customer)
    {
        $gateway = Omnipay::create('AuthorizeNet_AIM');
        $gateway->setApiLoginId($gateway_data['key2']);
        $gateway->setTransactionKey($gateway_data['key1']);
        $gateway->setDeveloperMode($gateway_data['dev_mode']);
        $meta = array(
            'Name' => $customer['name'],
            'email' => $customer['email']
        );
        try {
            return $gateway->purchase(
                array(
                    'card' => array(
                        'number' => $cardNumber,
                        'expiryMonth' => $nmonth,
                        'expiryYear' => $nyear,
                        'cvv' => $cardCVC
                    ),
                    'amount' => $amount,
                    'currency' => $gateway_data['currency'],
                    'description' => 'Paid for ' . $customer['name'] . ' INV#' . $tid,
                    'metadata' => $meta

                )
            )->send();
        } catch (Exception $e) {
            return 0;
        }
    }


    private function pinpay($cardNumber, $nmonth, $nyear, $cardCVC, $amount, $tid, $gateway_data, $customer)
    {
        $gateway = \Omnipay\Omnipay::create('Pin');

        // Initialise the gateway
        $gateway->initialize(array(
            'secretKey' => $gateway_data['key1'],
            'testMode' => $gateway_data['dev_mode'], // Or false when you are ready for live transactions
        ));

        // Create a credit card object
        // This card can be used for testing.
        // See https://pin.net.au/docs/api/test-cards for a list of card
        // numbers that can be used for testing.
        $card = new \Omnipay\Common\CreditCard(array(
            'firstName' => $customer['name'],
            'lastName' => 'Customer',
            'number' => $cardNumber,
            'expiryMonth' => $nmonth,
            'expiryYear' => $nyear,
            'cvv' => $cardCVC,
            'email' => $customer['email'],
            'billingAddress1' => $customer['address'],
            'billingCountry' => $customer['country'],
            'billingCity' => $customer['city'],
            'billingPostcode' => $customer['postbox'],
            'billingState' => $customer['region'],
        ));

        // Do a purchase transaction on the gateway
        $transaction = $gateway->purchase(array(
            'description' => 'Payment for INV#' . $tid,
            'amount' => $amount,
            'currency' => $gateway_data['currency'],
            'clientIp' => $_SERVER['REMOTE_ADDR'],
            'card' => $card,
        ));
        return $transaction->send();
    }


    private function securepay($cardNumber, $nmonth, $nyear, $cardCVC, $amount, $tid, $gateway_data)
    {


        $gateway = \Omnipay\Omnipay::create('SecurePay_SecureXML');
        $gateway->setMerchantId($gateway_data['key1']);
        $gateway->setTransactionPassword($gateway_data['key2']);
        $gateway->setTestMode($gateway_data['dev_mode']);
        // Create a credit card object
        $card = new \Omnipay\Common\CreditCard(
            [
                'number' => $cardNumber,
                'expiryMonth' => $nmonth,
                'expiryYear' => $nyear,
                'cvv' => $cardCVC,
            ]
        );
        // Perform a purchase test
        $transaction = $gateway->purchase(
            [
                'amount' => $amount,
                'currency' => $gateway_data['currency'],
                'transactionId' => 'invoice_' . $tid,
                'card' => $card,
            ]
        );

        return $transaction->send();
    }

    private function twocheckout($auth_token, $amount, $tid, $gateway_data, $customer)
    {


        $gateway = Omnipay::create('TwoCheckoutPlus_Token');
        $gateway->setAccountNumber($gateway_data['extra']);
        $gateway->setTestMode($gateway_data['dev_mode']);
        $gateway->setPrivateKey($gateway_data['key2']);

        $formData = array(
            'firstName' => $customer['name'],
            'email' => $customer['email'],
            'billingAddress1' => $customer['address'],
            'billingCountry' => $customer['country'],
            'billingCity' => $customer['city'],
            'billingPostcode' => $customer['postbox'],
            'billingState' => $customer['region'],
            "phoneNumber" => $customer['phone'],
        );


        $purchase_request_data = array(
            'card' => $formData,
            'token' => $auth_token,
            'transactionId' => $tid,
            'currency' => $gateway_data['currency'],
            'total' => $amount,
            'amount' => $amount,
        );
        return $gateway->purchase($purchase_request_data)->send();
    }


    private function paypal($cardNumber, $nmonth, $nyear, $cardCVC, $amount, $tid, $gateway_data, $customer)
    {

        $gateway = Omnipay::create('PayPal_Rest');
        // Initialise the gateway
        $gateway->initialize(array(
            'clientId' => $gateway_data['key1'],
            'secret' => $gateway_data['key2'],
            'testMode' => $gateway_data['dev_mode'], // Or false when you are ready for live transactions
        ));

        $card = new \Omnipay\Common\CreditCard(array(
            'firstName' => $customer['name'],
            'lastName' => 'Customer',
            'number' => $cardNumber,
            'expiryMonth' => $nmonth,
            'expiryYear' => $nyear,
            'cvv' => $cardCVC,
            'billingAddress1' => $customer['address'],
            'billingCountry' => $customer['country'],
            'billingCity' => $customer['city'],
            'billingPostcode' => $customer['postbox'],
            'billingState' => $customer['region']
        ));

        try {
            $transaction = $gateway->purchase(array(
                'amount' => $amount,
                'currency' => $gateway_data['currency'],
                'description' => 'Payment for #inv ' . $tid,
                'card' => $card,
            ));
            return $transaction->send();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function bank()
    {
        $online_pay = $this->billing->online_pay_settings();
        if ($online_pay['bank'] == 1) {
            $data['accounts'] = $this->billing->bank_accounts('Yes');
            $this->load->view('billing/header');
            $this->load->view('payment/public_bank_view', $data);
            $this->load->view('billing/footer');
        }
    }


    public function recharge()
    {

        if (!$this->input->get()) {
            exit();
        }
        $online_pay = $this->billing->online_pay_settings();
        if ($online_pay['enable'] == 0) {
            exit();
        }
        $data['id'] = base64_decode($this->input->get('id', true));


        $data['amount'] = $this->input->get('amount', true);
        $data['gid'] = $this->input->get('gid', true);
        $data['token'] = $this->input->get('token', true);

        switch ($data['gid']) {
            case 1:
                $fname = 'stripe';
                break;
            case 2:
                $fname = 'authorize';
                break;
            case 3:
                $fname = 'pinpay';
                break;
            case 4:
                $fname = 'paypal';
                break;
            case 5:
                $fname = 'securepay';
                break;
            case 6:
                $fname = 'checkout';
                break;
            case 7:
                $fname = 'payumoney';
                break;
            case 8:
                $fname = 'razor';
                break;
            default:
                $fname = 'stripe';
                break;
        }
        $online_pay = $this->billing->online_pay_settings();
        $data['gateway'] = $this->billing->gateway($data['gid']);
        if ($online_pay['enable'] == 1) {
            $this->load->view('billing/header');
            $this->load->view('gateways/recharge/card_' . $fname, $data);
            $this->load->view('billing/footer');
        } else {
            echo '<h3>' . $this->lang->line('Online Payment Service') . '</h3>';
        }
    }

    public function process_recharge()
    {
        if (!$this->input->post()) {
            exit();
        }

        $tid = $this->input->post('id', true);
        $amount = number_format($this->input->post('amount', true), 2, '.', '');
        $gateway = $this->input->post('gateway', true);
        $cardNumber = $this->input->post('cardNumber', true);
        $cardExpiry = $this->input->post('cardExpiry', true);
        $cardCVC = $this->input->post('cardCVC', true);

        $nmonth = substr($cardExpiry, 0, 2);
        $nyear = '20' . substr($cardExpiry, 5, 2);


        $pmethod = 'Card';

        $amount_o = $amount;

        $gateway_data = $this->billing->gateway($gateway);
        $surcharge = ($amount * $gateway_data['surcharge']) / 100;
        $amount_t = $amount + $surcharge;
        $this->load->model('customers_model', 'customers');
        $customer = $this->customers->details($tid, false);
        $note = 'Recharge Card Payment for Customer' . $customer['email'];


        $amount = number_format($amount_t, 2, '.', '');


        switch ($gateway) {

            case 1:
                //       $response = $this->stripe($this->input->post('stripeToken', true), $amount, $gateway_data, $tid, $customer);

                $response = $this->stripe($this->input->post('paymentMethodId', true), number_format($amount, 0, '', ''), $gateway_data, $tid, $customer, '', $this->input->post('paymentIntentId', true));

                break;
            case 2:
                $response = $this->authorizenet($cardNumber, $nmonth, $nyear, $cardCVC, $amount, $tid, $gateway_data, $customer);
                break;
            case 3:
                $response = $this->pinpay($cardNumber, $nmonth, $nyear, $cardCVC, $amount, $tid, $gateway_data, $customer);
                break;
            case 4:
                $response = $this->paypal($cardNumber, $nmonth, $nyear, $cardCVC, $amount, $tid, $gateway_data, $customer);
                break;
            case 5:
                $response = $this->securepay($cardNumber, $nmonth, $nyear, $cardCVC, $amount, $tid, $gateway_data);
                break;
            case 6:
                $response = $this->twocheckout($this->input->post('auth_token', true), $amount, $tid, $gateway_data, $customer);
                break;
        }

        // Process response
        if ($gateway > 1) {
            if ($response->isSuccessful()) {

                if ($this->billing->recharge_done($tid, $amount_o)) {
                    header('Content-Type: application/json');
                    echo json_encode(array('status' => 'Success', 'message' =>
                    $this->lang->line('Thank you for the payment') . " <a href='" . base_url('crm/payments/recharge') . "' class='btn btn-info btn-lg'><span class='icon-file-text2' aria-hidden='true'></span> " . $this->lang->line('View') . "</a>"));
                }
            } elseif ($response->isRedirect()) {

                // Redirect to offsite payment gateway
                $response->redirect();
            } else {

                // Payment failed
                echo json_encode(array('status' => 'Error', 'message' =>
                $this->lang->line('Payment failed')));
            }
        } elseif ($gateway == 1 and @$response['status'] == 'succeeded') {
            $amount_o = $amount_o / 100;
            if ($this->billing->recharge_done($tid, $amount_o)) {
                header('Content-Type: application/json');
                echo json_encode(array('status' => 'Success', 'message' =>
                $this->lang->line('Thank you for the payment') . " <a href='" . base_url('crm/payments/recharge') . "' class='btn btn-info btn-lg'><span class='icon-file-text2' aria-hidden='true'></span> " . $this->lang->line('View') . "</a>"));
            }
        } elseif ($gateway == 1 and @$response['status'] == 'error') {
            header('Content-Type: application/json');
            echo json_encode(array('error' => $response['message']));
        }
    }

    public function secureprocess()
    {

        $gid = $this->input->get('g', true);
        //payu
        if ($gid == 7) {
            $status = $this->input->post('status', true);
            $firstname = $this->input->post("firstname", true);
            $amount = $this->input->post("amount", true);
            $txnid = $this->input->post("txnid", true);
            $posted_hash = $this->input->post("hash", true);
            $key = $this->input->post("key", true);
            $productinfo = $this->input->post("productinfo", true);
            $email = $this->input->post("email", true);
            $gateway_data = $this->billing->gateway($gid);
            $salt = $gateway_data['key2'];

            // Salt should be same Post Request

            if ($this->input->post('additionalCharges', true)) {
                $additionalCharges = $this->input->post("additionalCharges", true);
                $retHashSeq = $additionalCharges . '|' . $salt . '|' . $status . '|||||||||||' . $email . '|' . $firstname . '|' . $productinfo . '|' . $amount . '|' . $txnid . '|' . $key;
            } else {
                $retHashSeq = $salt . '|' . $status . '|||||||||||' . $email . '|' . $firstname . '|' . $productinfo . '|' . $amount . '|' . $txnid . '|' . $key;
            }
            $hash = hash("sha512", $retHashSeq);
            if ($hash != $posted_hash) {
                echo "Invalid Transaction. Please try again";
            } elseif ($status == 'success') {

                //tt
                $tid = $this->input->get('inv', true);
                $customer = $this->invocies->invoice_details($tid);
                $note = 'Card Payment for #' . $customer['tid'] . ' T#' . $txnid;
                $pmethod = 'Card';
                $amount_o = $customer['total'] - $customer['pamnt'];
                $surcharge = ($amount_o * $gateway_data['surcharge']) / 100;
                $amount_t = $amount_o + $surcharge;
                $validtoken = hash_hmac('ripemd160', $tid, $this->config->item('encryption_key'));
                if (number_format($amount_t, 2, '.', '') == $amount) {
                    $amount = number_format($amount_o, 2, '.', '');
                    if ($this->billing->paynow($customer['iid'], $amount, $note, $pmethod, $customer['loc'])) {

                        redirect(base_url('billing/view?id=' . $tid . '&token=' . $validtoken));
                    }
                }
            } else {
                $tid = $this->input->get('inv', true);
                $validtoken = hash_hmac('ripemd160', $tid, $this->config->item('encryption_key'));
                echo "Invalid Transaction. Please try again";
                redirect(base_url('billing/view?id=' . $tid . '&token=' . $validtoken));
            }
        } else {
            $data['gateway_data'] = $this->billing->gateway($gid);
            $data['tid'] = $this->input->get('inv', true);
            $this->load->view('gateways/card_razor_verify', $data);
        }
    }


    public function gateway_process()
    {
        //for paypal
        $invoice = $this->input->post('id', true);
        $token = $this->input->post('token', true);

        $gateway_data = $this->billing->gateway(4);
        $paypalConfig = [
            'sandbox' => $gateway_data['dev_mode'],
            'client_id' => $gateway_data['key1'],
            'client_secret' => $gateway_data['key2'],
            'return_url' => base_url('billing/gateway_response'),
            'cancel_url' => base_url('billing/view?id=' . $invoice . '&token=' . $token)
        ];

        $this->load->library("Paypal_gateway", $paypalConfig);

        $apiContext = $this->paypal_gateway->getApiContext();


        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        // Set some example data for the payment.
        $customer = $this->invocies->invoice_details($invoice);
        if (!$customer['tid']) {
            exit();
        }
        $amount = number_format($this->input->post('amount', true), 2, '.', '');
        // COMMENTED OUT: Multi-currency support
        // if ($customer['multi'] > 0) {
        //     $multi_currency = $this->invocies->currency_d($customer['multi']);
        
        // NEW: Always use geopos_system.currency
        $query = $this->db->query("SELECT currency FROM geopos_system WHERE id=1 LIMIT 1");
        $row = $query->row_array();
        $multi_currency = array('code' => $row['currency']);
        // COMMENTED OUT: Multi-currency support
        // if ($customer['multi'] > 0) {
        //     $gateway_data['currency'] = $multi_currency['code'];
        // }
        // if ($customer['loc'] > 0) {
        //     $multi_currency = $this->invocies->currency_d($customer['multi'], $customer['loc']);
        
        // Always set currency from geopos_system
        $gateway_data['currency'] = $multi_currency['code'];
        $validtoken = hash_hmac('ripemd160', $invoice, $this->config->item('encryption_key'));
        $surcharge = ($amount * $gateway_data['surcharge']) / 100;
        $amount_t = $amount + $surcharge;
        $amount = number_format($amount_t, 2, '.', '');

        if (hash_equals($token, $validtoken)) {

            $amountPayable = $amount;
            $invoiceNumber = $invoice;

            $amount = new Amount();
            $amount->setCurrency($gateway_data['currency'])
                ->setTotal($amountPayable);

            $transaction = new Transaction();
            $transaction->setAmount($amount)
                ->setDescription('Some description about the payment being made')
                ->setInvoiceNumber($invoiceNumber);

            $redirectUrls = new RedirectUrls();
            $redirectUrls->setReturnUrl($paypalConfig['return_url'])
                ->setCancelUrl($paypalConfig['cancel_url']);

            $payment = new Payment();
            $payment->setIntent('sale')
                ->setPayer($payer)
                ->setTransactions([$transaction])
                ->setRedirectUrls($redirectUrls);

            try {
                $payment->create($apiContext);
                $this->billing->token($invoice, 1);
            } catch (Exception $e) {
                throw new Exception('Unable to create link for payment');
            }

            header('location:' . $payment->getApprovalLink());
            exit(1);
        }
    }

    public function gateway_response()
    {
        if (empty($this->input->get('paymentId', true)) || empty($this->input->get('PayerID', true))) {
            exit;
        }
        $gateway_data = $this->billing->gateway(4);
        $paypalConfig = [
            'sandbox' => $gateway_data['dev_mode'],
            'client_id' => $gateway_data['key1'],
            'client_secret' => $gateway_data['key2'],
            'return_url' => base_url('billing/gateway_response'),
            'cancel_url' => base_url('billing/view?id=105&token=ee2f511d44dd7f0212d46b92f2d6022754574bb3')
        ];
        $this->load->library("Paypal_gateway", $paypalConfig);
        $apiContext = $this->paypal_gateway->getApiContext();
        $paymentId = $_GET['paymentId'];
        $payment = Payment::get($paymentId, $apiContext);
        $execution = new PaymentExecution();
        $execution->setPayerId($_GET['PayerID']);
        try {
            // Take the payment
            $payment->execute($execution, $apiContext);
            try {
                $payment = Payment::get($paymentId, $apiContext);
                $data = [
                    'transaction_id' => $payment->getId(),
                    'payment_amount' => $payment->transactions[0]->amount->total,
                    'payment_status' => $payment->getState(),
                    'invoice_id' => $payment->transactions[0]->invoice_number
                ];
                $validtoken = hash_hmac('ripemd160', $data['invoice_id'], $this->config->item('encryption_key'));
                $paypalConfig['bill_url'] = base_url('billing/view?id=' . $data['invoice_id'] . '&token=' . $validtoken);
                if ($data['payment_status'] === 'approved') {
                    $customer = $this->invocies->invoice_details($data['invoice_id']);
                    $amount_o = $data['payment_amount'];

                    $amount_o = rev_amountExchange_s($amount_o, $customer['multi'], $customer['loc']);

                    $note = 'Card Payment for #' . $customer['tid'];
                    $pmethod = 'Card';
                    if ($customer['multi'] > 0) {
                        //    $amount =  $amount;
                        $note .= ' (Currency Conversion Applied)';
                    }
                    if ($customer['loc'] > 0) {

                        $note .= ' (Currency Conversion Applied)';
                    }
                    $amount = $amount_o / (($gateway_data['surcharge'] / 100) + 1);
                    $amount_o = number_format($amount, 2, '.', '');
                    $valid = $this->billing->token($customer['iid'], 2);
                    if ($valid['rid'] == $customer['iid']) {
                        $this->billing->paynow($customer['iid'], $amount_o, $note, $pmethod, $customer['loc']);
                        $this->billing->token($customer['iid'], 3);
                    }
                    header('location:' . $paypalConfig['bill_url']);
                    exit(1);
                } else {
                    // Payment failed
                    header('location:' . $paypalConfig['bill_url']);
                    exit(1);
                }
            } catch (Exception $e) {
                // Failed to retrieve payment from PayPal
                $this->billing->token($customer['iid'], 3);
                header('location:' . base_url());
            }
        } catch (Exception $e) {
            // Failed to take payment
            $this->billing->token($customer['iid'], 3);
            header('location:' . base_url());
        }
    }

    public function process_stripe()
    {
        echo 'Payment processing do no hit back button.....';
    }

    public function stripe_api_response()
    {

        $data['gateway'] = $this->billing->gateway(1);
        echo json_encode(['publishableKey' => $data['gateway']['key2']]);
    }

    function generateResponse($intent)
    {
        switch ($intent->status) {
            case "requires_action":
            case "requires_source_action":
                // Card requires authentication
                return [
                    'requiresAction' => true,
                    'paymentIntentId' => $intent->id,
                    'clientSecret' => $intent->client_secret
                ];
            case "requires_payment_method":
            case "requires_source":
                // Card was not properly authenticated, suggest a new payment method
                return [
                    'error' => "Your card was denied, please provide a new payment method"
                ];
            case "succeeded":
                // Payment is complete, authentication not required
                // To cancel the payment after capture you will need to issue a Refund (https://stripe.com/docs/api/refunds)
                return ['clientSecret' => $intent->client_secret];
        }
    }
}

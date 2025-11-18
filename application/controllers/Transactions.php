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

class Transactions extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library("Aauth");
        if (!$this->aauth->is_loggedin()) {
            redirect('/user/', 'refresh');
        }
        if (!$this->aauth->permission_new(null, 'accountsManage')) {

            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $this->load->model('invoices_model');
        $this->load->model('transactions_model', 'transactions');
        $this->load->library("Custom");
        $this->li_a = 'accounts';
    }

    public function index()
    {
        if (!$this->aauth->permission_new(null, 'accountsManage')) {

            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $head['title'] = "Transaction";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $this->load->view('transactions/index');
        $this->load->view('fixed/footer');
    }

    public function submit_vat()
    {
        if (!$this->aauth->permission_new(null, 'accountsManage')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $head['title'] = "Submit VAT";
        $head['usernm'] = $this->aauth->get_user()->username;
        $data['vat_id'] =  $this->input->get('vat_id');
        $this->load->view('fixed/header', $head);
        $this->load->view('transactions/vat_submit', $data);
        $this->load->view('fixed/footer');
    }

    public function vat_translist()
    {

        if (!$this->aauth->permission_new(null, 'accountsVATObligations')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $ttype = $this->input->get('type');
        $list = $this->transactions->get_vat_datatables($ttype);
        $data = array();
        // $no = $_POST['start'];
        $no = $this->input->post('start');
        foreach ($list as $prd) {
            $no++;
            $row = array();
            $pid = $prd->id;
            $row[] = $prd->periodKey;
            $row[] = date($prd->start);
            $row[] = date($prd->end);
            $row[] = date($prd->due);
            $row[] = $prd->status;
            $row[] = $prd->received;

            if ($prd->status == "O") {
                $row[] = '<a href="' . base_url() . 'transactions/submit_vat?vat_id=' . $pid . '" class="btn btn-primary btn-sm">Submit VAT</span> ';
            } else {
                $row[] = "Submitted";
            }


            // $row[] = '<a href="' . base_url() . 'transactions/view?id=' . $pid . '" class="btn btn-primary btn-sm"><span class="fa fa-eye"></span>  ' . $this->lang->line('View') . '</a> <a href="' . base_url() . 'transactions/print_t?id=' . $pid . '" class="btn btn-info btn-sm"  title="Print"><span class="fa fa-print"></span></a>&nbsp; &nbsp;<a  href="#" data-object-id="' . $pid . '" class="btn btn-danger btn-sm delete-object"><span class="fa fa-trash"></span></a>';
            $data[] = $row;
        }
        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->transactions->count_all_vat(),
            "recordsFiltered" => $this->transactions->count_filtered_vat(),
            "data" => $data,
        );
        //output to json format
        echo json_encode($output);
    }


    public function obligations()
    {
        if (!$this->aauth->permission_new(null, 'accountsVATObligations')) {

            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $head['title'] = "Transaction";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $this->load->view('transactions/vat_subimissions');
        $this->load->view('fixed/footer');
    }

    public function add()
    {
        if (!$this->aauth->permission_new(null, 'accountsNewTransaction')) {

            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $data['dual'] = $this->custom->api_config(65);

        $data['cat'] = $this->transactions->categories();
        $data['accounts'] = $this->transactions->acc_list();
        $head['title'] = "Add Transaction";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $this->load->view('transactions/create', $data);
        $this->load->view('fixed/footer');
    }
    public function reverse()
    {
        if (!$this->aauth->permission_new(null, 'accountsReversalTransaction')) {

            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $data['dual'] = $this->custom->api_config(65);

        $data['cat'] = $this->transactions->categories();
        $data['accounts'] = $this->transactions->acc_list();
        $head['title'] = "Add Transaction";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $this->load->view('transactions/reverse', $data);
        $this->load->view('fixed/footer');
    }

    public function advance()
    {
        if (!$this->aauth->permission_new(null, 'accountsAdvancePayment')) {

            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $data['dual'] = $this->custom->api_config(65);

        $data['cat'] = $this->transactions->categories();
        $data['accounts'] = $this->transactions->acc_list();
        $data['lastinvoice'] = $this->invoices_model->lastadvance() + 1;
        $head['title'] = "Advance Payment";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $this->load->view('transactions/advance', $data);
        $this->load->view('fixed/footer');
    }

    public function transfer()
    {
        if (!$this->aauth->permission_new(null, 'accountsNewTransfer')) {

            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }

        $data['cat'] = $this->transactions->categories();
        $data['accounts'] = $this->transactions->acc_list();
        $head['title'] = "New Transfer";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $this->load->view('transactions/transfer', $data);
        $this->load->view('fixed/footer');
    }

    public function payinvoice()
    {

        if (!$this->aauth->permission_new(null, 'accountsManage')) {

            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $amount2 = 0;
        $tid = $this->input->post('tid');
        $amount = rev_amountExchange_s($this->input->post('amount', true), 0, $this->aauth->get_user()->loc);
        $paydate = $this->input->post('paydate', true);
        $note = $this->input->post('shortnote', true);
        $pmethod = $this->input->post('pmethod', true);
        $acid = $this->input->post('account', true);
        $cid = $this->input->post('cid', true);
        $cname = $this->input->post('cname', true);
        $paydate = datefordatabase($paydate);

        $this->db->select('holder');
        $this->db->from('geopos_accounts');
        $this->db->where('id', $acid);
        $query = $this->db->get();
        $account = $query->row_array();

        if ($pmethod == 'Balance') {

            $customer = $this->transactions->check_balance($cid);
            // if (rev_amountExchange_s($customer['balance'], 0, $this->aauth->get_user()->loc) >= $amount) {

            $this->db->set('balance', "balance-$amount", FALSE);
            $this->db->where('id', $cid);
            $this->db->update('geopos_customers');
            //} else {

            //    $amount = rev_amountExchange_s($customer['balance'], 0, $this->aauth->get_user()->loc);
            //    $this->db->set('balance', 0, FALSE);
            //   $this->db->where('id', $cid);
            //   $this->db->update('geopos_customers');
            // }
        }

        $data = array(
            'acid' => $acid,
            'account' => $account['holder'],
            'type' => 'Income',
            'cat' => 'Sales',
            'credit' => $amount,
            'payer' => $cname,
            'payerid' => $cid,
            'method' => $pmethod,
            'date' => $paydate,
            'eid' => $this->aauth->get_user()->id,
            'tid' => $tid,
            'note' => $note,
            'loc' => $this->aauth->get_user()->loc
        );

        $this->db->insert('geopos_transactions', $data);
        $tttid = $this->db->insert_id();

        $this->db->select('total,csd,pamnt');
        $this->db->from('geopos_invoices');
        $this->db->where('id', $tid);
        $query = $this->db->get();
        $invresult = $query->row();

        $totalrm = $invresult->total - $invresult->pamnt;

        if ($totalrm > $amount) {
            $this->db->set('pmethod', $pmethod);
            $this->db->set('pamnt', "pamnt+$amount", FALSE);

            $this->db->set('status', 'partial');
            $this->db->where('id', $tid);
            $this->db->update('geopos_invoices');


            //account update
            $this->db->set('lastbal', "lastbal+$amount", FALSE);
            $this->db->where('id', $acid);
            $this->db->update('geopos_accounts');
            $paid_amount = $invresult->pamnt + $amount;
            $status = 'Partial';
            $totalrm = $totalrm - $amount;
        } else {
            if ($totalrm < $amount) {
                $diff = $totalrm - $amount;
                $diff = abs($diff);
                $amount2 = $amount;
                $amount = $totalrm;
                $this->db->set('balance', "balance+$diff", FALSE);
                $this->db->where('id', $cid);
                $this->db->update('geopos_customers');
                $this->db->set('credit', "credit-$diff", FALSE);
                $this->db->where('id', $tttid);
                $this->db->update('geopos_transactions');
            }
            $this->db->set('pmethod', $pmethod);
            $this->db->set('pamnt', "pamnt+$totalrm", FALSE);
            $this->db->set('status', 'paid');
            $this->db->where('id', $tid);
            $this->db->update('geopos_invoices');
            //account update
            $this->db->set('lastbal', "lastbal+$totalrm", FALSE);
            $this->db->where('id', $acid);
            $this->db->update('geopos_accounts');
            $totalrm = 0;
            $status = 'Paid';
        }
        $amount += $amount2;

        $activitym = "<tr><td>" . '<a href="' . base_url('invoices') . '/view_payslip?id=' . $tttid . '&inv=' . $tid . '" class="btn btn-blue btn-sm"><span class="fa fa-print" aria-hidden="true"></span></a> ' . substr($paydate, 0, 10) . "</td><td>$pmethod</td><td>" . amountExchange_s($amount, 0, $this->aauth->get_user()->loc) . "</td><td>$note</td></tr>";
        $dual = $this->custom->api_config(65);
        if ($dual['key1']) {

            $this->db->select('holder');
            $this->db->from('geopos_accounts');
            $this->db->where('id', $dual['key2']);
            $query = $this->db->get();
            $account = $query->row_array();

            $data['credit'] = 0;
            $data['debit'] = $amount;
            $data['type'] = 'Expense';
            $data['acid'] = $dual['key2'];
            $data['account'] = $account['holder'];
            $data['note'] = 'Debit ' . $data['note'];

            $this->db->insert('geopos_transactions', $data);

            //account update
            $this->db->set('lastbal', "lastbal-$amount", FALSE);
            $this->db->where('id', $dual['key2']);
            $this->db->update('geopos_accounts');
        }
        echo json_encode(array('status' => 'Success', 'message' =>
        $this->lang->line('Transaction has been added'), 'pstatus' => $this->lang->line($status), 'activity' => $activitym, 'amt' => $totalrm, 'ttlpaid' => amountExchange_s($amount, 0, $this->aauth->get_user()->loc)));

        $alert = $this->custom->api_config(66);
        if ($alert['key1'] == 1) {
            $this->load->model('communication_model');
            $subject = $cname . ' ' . $this->lang->line('Transaction has been');
            $body = $subject . '<br> ' . $this->lang->line('Credit') . ' ' . $this->lang->line('Amount') . ' ' . $amount . '<br> ' . $this->lang->line('Debit') . ' ' . $this->lang->line('Amount') . ' 0  <br> ID# ' . $tttid;
            $out = $this->communication_model->send_corn_email($alert['url'], $alert['url'], $subject, $body, false, '');
        }
    }
















    // public function payinvoice_rec()
    // {
    //     if (isset($_POST['total']) && !empty($_POST['total'])) {
    //         $invoice_ids = $_POST['pid'];
    //         var_dump(count($invoice_ids)); die();
    //         $customer_ = $_POST['cst'];
    //         $amount_array = $_POST['product_subtotal'];
    //         $receipt_reference = $_POST['receipt_reference'];
    //         $paymethod1 = $_POST['method'];
    //         $paymtdate1 = $_POST['date'];
    //         //print_r($receipt_references);die();
    //         $customers_array = $_POST['customers'];
    //         $name_array = $_POST['hsn'];
    //         $total = $_POST['total'];
    //         if (count($invoice_ids) > 0) {
    //             function generateUniqueId()
    //             {
    //                 return mt_rand(100000, 999999);
    //             }

    //             // Generate a unique inv_id
    //             $unique_inv_id = generateUniqueId();
    //             for ($i = 0; $i < count($invoice_ids); $i++) {
                    
    //                 if ($amount_array[$i]) {
    //                     $cidind = $customers_array[$i];
    //                     $paymethod = $paymethod1;
    //                     $paymtdate = $paymtdate1;

    //                     $cnameind = $name_array[$i];
    //                     $acid = '1';
    //                     // $paydate = date('Y-m-d');
    //                     if (!empty($receipt_reference)) {
    //                         $note = $receipt_reference;
    //                     } else {
    //                         $note = 'Sale Receipt';
    //                     }
    //                     $pmethod = 'Balance';
    //                     $amount = rev_amountExchange_s($amount_array[$i], 0, $this->aauth->get_user()->loc);

    //                     $tid = $invoice_ids[$i];
    //                     $this->db->select('tid');
    //                     $this->db->from('geopos_invoices');
    //                     $this->db->where('id', $tid);
    //                     $query = $this->db->get();
    //                     $pr = $query->row_array();

    //                     $invoice_tid = $pr['tid'];

    //                     $data = array(
    //                         'acid' => $acid,
    //                         'account' => 'Sales Account',
    //                         'type' => 'Income',
    //                         'cat' => 'Sales',
    //                         'debit' => $amount,
    //                         // 'credit' => $amount_array[$i],
    //                         'payer' => $cnameind,
    //                         'payerid' => $cidind,
    //                         'method' => $pmethod,
    //                         'date' => datefordatabase($paymtdate),
    //                         'eid' => $this->aauth->get_user()->id,
    //                         'tid' => $tid,
    //                         'inv_id' => $invoice_tid,
    //                         'trans_ref' => $unique_inv_id,
    //                         'note' => $note,
    //                         'loc' => $this->aauth->get_user()->loc,
    //                         'paymt_method' => $paymethod,
    //                         'paymt_date' => datefordatabase($paymtdate),
    //                     );
    //                     //var_dump($data); exit; 
    //                     $this->db->insert('geopos_transactions', $data);
    //                     $tttid = $this->db->insert_id();
    //                     $amount2 = 0;
    //                     $tid = $invoice_ids[$i];  // Invoice ID
    //                     $amount = rev_amountExchange_s($amount_array[$i], 0, $this->aauth->get_user()->loc);
    //                     // $receipt_reference=$receipt_references[$i];
    //                     $cid = $customers_array[$i];
    //                     $cname = $name_array[$i];
    //                     $paydate = datefordatabase($paydate);
    //                     $this->db->select('holder');
    //                     $this->db->from('geopos_accounts');
    //                     $this->db->where('id', $acid);
    //                     $query = $this->db->get();
    //                     $account = $query->row_array();
    //                     if ($pmethod == 'Balance') {
    //                         $customer = $this->transactions->check_balance($cid);
    //                         $this->db->set('balance', "balance-$amount", FALSE);
    //                         $this->db->where('id', $cid);
    //                         $this->db->update('geopos_customers');
    //                         $this->db->select('balance');
    //                         $this->db->from('geopos_customers');
    //                         $this->db->where('id', $cid);
    //                         $query = $this->db->get();
    //                         $customer_balance_form_query = $query->row_array();
    //                         $c_b = $customer_balance_form_query['balance'];
    //                         $this->db->set('balance', "$c_b", FALSE);
    //                         $this->db->where('id', $tttid);
    //                         $this->db->update('geopos_transactions');
    //                     }
    //                     $this->db->select('total,csd,pamnt');
    //                     $this->db->from('geopos_invoices');
    //                     $this->db->where('id', $tid);
    //                     $query = $this->db->get();
    //                     $invresult = $query->row();
    //                     $totalrm = $invresult->total - $invresult->pamnt;

    //                     if ($totalrm > $amount) {
    //                         $this->db->set('pmethod', $pmethod);
    //                         $this->db->set('pamnt', "pamnt+$amount", FALSE);

    //                         $this->db->set('status', 'partial');
    //                         $this->db->where('id', $tid);
    //                         $this->db->update('geopos_invoices');


    //                         //calculate os
    //                         $this->db->select('total,csd,pamnt');
    //                         $this->db->from('geopos_invoices');
    //                         $this->db->where('id', $tid);
    //                         $query = $this->db->get();
    //                         $invforos = $query->row();
    //                         $os = $invforos->total - $invforos->pamnt;
    //                         $this->db->set('os', $os, FALSE);
    //                         $this->db->where('id', $tttid);
    //                         $this->db->update('geopos_transactions');

    //                         //account update
    //                         $this->db->set('lastbal', "lastbal+$amount", FALSE);
    //                         $this->db->where('id', $acid);
    //                         $this->db->update('geopos_accounts');
    //                         $paid_amount = $invresult->pamnt + $amount;
    //                         $status = 'Partial';
    //                         $this->aauth->applog("[Customer Receipt - Invoice: $tid] - Customer: " . $customer_ . ", Status: $status, Amount: $amount", $this->aauth->get_user()->username);
    //                         $totalrm = $totalrm - $amount;
    //                     } else {
    //                         if ($totalrm < $amount) {

    //                             $diff = $totalrm - $amount;
    //                             $diff = abs($diff);
    //                             $amount2 = $amount;
    //                             $amount = $totalrm;
    //                             $this->db->set('balance', "balance+$diff", FALSE);
    //                             $this->db->where('id', $cid);
    //                             $this->db->update('geopos_customers');
    //                             // $this->db->set('credit', "credit-$diff", FALSE);
    //                             // $this->db->where('id', $tttid);
    //                             // $this->db->update('geopos_transactions');

    //                             $this->aauth->applog("[Customer Receipt - Invoice: $tid] - Customer: " . $customer_ . ", Status: $status, Amount: $amount", $this->aauth->get_user()->username);
    //                         }
    //                         $this->db->set('pmethod', $pmethod);
    //                         $this->db->set('pamnt', "pamnt+$totalrm", FALSE);
    //                         $this->db->set('status', 'paid');
    //                         $this->db->where('id', $tid);
    //                         $this->db->update('geopos_invoices');
    //                         //account update
    //                         $this->db->set('lastbal', "lastbal+$totalrm", FALSE);
    //                         $this->db->where('id', $acid);
    //                         $this->db->update('geopos_accounts');
    //                         $totalrm = 0;
    //                         $status = 'Paid';
    //                     }
    //                     $amount += $amount2;

    //                     $activitym = "<tr><td>" . '<a href="' . base_url('invoices') . '/view_payslip?id=' . $tttid . '&inv=' . $tid . '" class="btn btn-blue btn-sm"><span class="fa fa-print" aria-hidden="true"></span></a> ' . substr($paydate, 0, 10) . "</td><td>$pmethod</td><td>" . amountExchange_s($amount, 0, $this->aauth->get_user()->loc) . "</td><td>$note</td></tr>";
    //                     $dual = $this->custom->api_config(65);

    //                     //    if ($dual['key1']) {

    //                     //         $this->db->select('holder');
    //                     //         $this->db->from('geopos_accounts');
    //                     //         $this->db->where('id', $dual['key2']);
    //                     //         $query = $this->db->get();
    //                     //         $account = $query->row_array();

    //                     //         $data['credit'] = 0;
    //                     //         $data['debit'] = $amount;
    //                     //         $data['type'] = 'Expense';
    //                     //         $data['acid'] = $dual['key2'];
    //                     //         $data['account'] = $account['holder'];
    //                     //         $data['note'] = 'Debit ' . $data['note'];

    //                     //         $this->db->insert('geopos_transactions', $data);


    //                     //         $this->db->set('lastbal', "lastbal-$amount", FALSE);
    //                     //         $this->db->where('id', $dual['key2']);
    //                     //         $this->db->update('geopos_accounts');
    //                     //     }
    //                     // echo json_encode(array('status' => 'Success', 'message' =>
    //                     //   $this->lang->line('Transaction has been added'), 'pstatus' => $this->lang->line($status), 'activity' => $activitym, 'amt' => $totalrm, 'ttlpaid' => amountExchange_s($amount, 0, $this->aauth->get_user()->loc)));

    //                     $alert = $this->custom->api_config(66);
    //                     if ($alert['key1'] == 1) {
    //                         $this->load->model('communication_model');
    //                         $subject = $cname . ' ' . $this->lang->line('Transaction has been');
    //                         $body = $subject . '<br> ' . $this->lang->line('Credit') . ' ' . $this->lang->line('Amount') . ' ' . $amount . '<br> ' . $this->lang->line('Debit') . ' ' . $this->lang->line('Amount') . ' 0  <br> ID# ' . $tttid;
    //                         $out = $this->communication_model->send_corn_email($alert['url'], $alert['url'], $subject, $body, false, '');
    //                     }
    //                 }
    //             }

    //             $this->session->set_flashdata('success_message', 'Transaction has been added against your invoices');

    //             redirect('invoices/receipt');
    //         }
    //     } else {

    //         $this->session->set_flashdata('err_message', 'Transaction not allowed, please add valid amount against any invoice');


    //         redirect('invoices/receipt');
    //     }
    // }

    public function payinvoice_rec()
    {
        if (isset($_POST['total']) && !empty($_POST['total'])) {
            $invoice_ids = $_POST['pid'];
            $customer_ = $_POST['cst'];
            $amount_array = $_POST['product_subtotal'];
            $receipt_reference = $_POST['receipt_reference'];
            $paymethod1 = $_POST['method'];
            $paymtdate1 = $_POST['date'];
            $customers_array = $_POST['customers'];
            $name_array = $_POST['hsn'];
            $total = $_POST['total'];
    
            if (count($invoice_ids) > 0) {
                function generateUniqueId()
                {
                    return mt_rand(100000, 999999);
                }
    
                // Generate a unique inv_id
                $unique_inv_id = generateUniqueId();
    
                for ($i = 0; $i < count($invoice_ids); $i++) {
                    // var_dump(count($invoice_ids)); die();
                    // Check if the invoice amount is greater than zero, meaning it has been selected for payment
                    if ($amount_array[$i] > 0) {
                        $cidind = $customers_array[$i];
                        $paymethod = $paymethod1;
                        $paymtdate = $paymtdate1;
    
                        $cnameind = $name_array[$i];
                        $acid = '1';
    
                        if (!empty($receipt_reference)) {
                            $note = $receipt_reference;
                        } else {
                            $note = 'Sale Receipt';
                        }
    
                        $pmethod = 'Balance';
                        $amount = rev_amountExchange_s($amount_array[$i], 0, $this->aauth->get_user()->loc);
                        $tid = $invoice_ids[$i];
    
                        // Fetching the invoice transaction ID
                        $this->db->select('tid');
                        $this->db->from('geopos_invoices');
                        $this->db->where('id', $tid);
                        $query = $this->db->get();
                        $pr = $query->row_array();
                        $invoice_tid = $pr['tid'];
    
                        // Data to be inserted
                        $data = array(
                            'acid' => $acid,
                            'account' => 'Sales Account',
                            'type' => 'Income',
                            'cat' => 'Sales',
                            'debit' => $amount,
                            'payer' => $cnameind,
                            'payerid' => $cidind,
                            'method' => $pmethod,
                            'date' => datefordatabase($paymtdate),
                            'eid' => $this->aauth->get_user()->id,
                            'tid' => $tid,
                            'inv_id' => $invoice_tid,
                            'trans_ref' => $unique_inv_id,
                            'note' => $note,
                            'loc' => $this->aauth->get_user()->loc,
                            'paymt_method' => $paymethod,
                            'paymt_date' => datefordatabase($paymtdate),
                            'received_payment' => $amount
                        );
    
                        // Insert transaction data
                        $this->db->insert('geopos_transactions', $data);
                        $tttid = $this->db->insert_id();
    
                        // Additional processing for balances and invoice statuses
                        $this->db->select('total, csd, pamnt');
                        $this->db->from('geopos_invoices');
                        $this->db->where('id', $tid);
                        $query = $this->db->get();
                        $invresult = $query->row();
                        $totalrm = $invresult->total - $invresult->pamnt;
    
                        if ($totalrm > $amount) {
                            // var_dump('partial'); die();
                            $this->db->set('pmethod', $pmethod);
                            $this->db->set('pamnt', "pamnt+$amount", FALSE);
                            $this->db->set('status', 'partial');
                            $this->db->where('id', $tid);
                            $this->db->update('geopos_invoices');
    
                            // Calculate outstanding balance
                            $this->db->select('total, csd, pamnt');
                            $this->db->from('geopos_invoices');
                            $this->db->where('id', $tid);
                            $query = $this->db->get();
                            $invforos = $query->row();
                            $os = $invforos->total - $invforos->pamnt;
                            $this->db->set('os', $os, FALSE);
                            $this->db->where('id', $tttid);
                            $this->db->update('geopos_transactions');
    
                            // Update account balance
                            $this->db->set('lastbal', "lastbal+$amount", FALSE);
                            $this->db->where('id', $acid);
                            $this->db->update('geopos_accounts');
                            $totalrm = $totalrm - $amount;
                        } else {
                            // var_dump('paid'); die();
                            if ($totalrm < $amount) {
                                $diff = $totalrm - $amount;
                                $diff = abs($diff);
                                $amount2 = $amount;
                                $amount = $totalrm;
                                $this->db->set('balance', "balance+$diff", FALSE);
                                $this->db->where('id', $cid);
                                $this->db->update('geopos_customers');
    
                                $this->aauth->applog("[Customer Receipt - Invoice: $tid] - Customer: " . $customer_ . ", Status: Paid, Amount: $amount", $this->aauth->get_user()->username);
                            }
                            $this->db->set('pmethod', $pmethod);
                            $this->db->set('pamnt', "pamnt+$totalrm", FALSE);
                            $this->db->set('status', 'paid');
                            $this->db->where('id', $tid);
                            $this->db->update('geopos_invoices');
    
                            // Update account balance
                            $this->db->set('lastbal', "lastbal+$totalrm", FALSE);
                            $this->db->where('id', $acid);
                            $this->db->update('geopos_accounts');
                            $totalrm = 0;
                        }
    
                        $amount += isset($amount2) ? $amount2 : 0;
    
                        $activitym = "<tr><td>" . '<a href="' . base_url('invoices') . '/view_payslip?id=' . $tttid . '&inv=' . $tid . '" class="btn btn-blue btn-sm"><span class="fa fa-print" aria-hidden="true"></span></a> ' . substr($paymtdate, 0, 10) . "</td><td>$pmethod</td><td>" . amountExchange_s($amount, 0, $this->aauth->get_user()->loc) . "</td><td>$note</td></tr>";
    
                        // Handle notifications and alerts if needed
                        $alert = $this->custom->api_config(66);
                        if ($alert['key1'] == 1) {
                            $this->load->model('communication_model');
                            $subject = $cname . ' ' . $this->lang->line('Transaction has been');
                            $body = $subject . '<br> ' . $this->lang->line('Credit') . ' ' . $this->lang->line('Amount') . ' ' . $amount . '<br> ' . $this->lang->line('Debit') . ' ' . $this->lang->line('Amount') . ' 0  <br> ID# ' . $tttid;
                            $out = $this->communication_model->send_corn_email($alert['url'], $alert['url'], $subject, $body, false, '');
                        }
                    }
                }
    
                $this->session->set_flashdata('success_message', 'Transaction has been added against your invoices');
                redirect('invoices/receipt');
            }
        } else {
            $this->session->set_flashdata('err_message', 'Transaction not allowed, please add valid amount against any invoice');
            redirect('invoices/receipt');
        }
    }
    






























    public function paypurchase()
    {
        // dd($this->input->post());
        if (!$this->aauth->permission_new(null, 'accountsManage')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }

        $tid = $this->input->post('tid', true);
        $amount = $this->input->post('amount', true);
        $paydate = $this->input->post('paydate', true);
        $note = $this->input->post('shortnote', true);
        $pmethod = $this->input->post('pmethod', true);
        $acid = $this->input->post('account', true);
        $cid = $this->input->post('cid', true);
        $cname = $this->input->post('cname', true);
        $paydate = datefordatabase($paydate);
        $this->db->select('holder');
        $this->db->from('geopos_accounts');
        $this->db->where('id', $acid);
        $query = $this->db->get();
        $account = $query->row_array();

        // Fetch the updated invoice reference
        $this->db->select('inv_id, invoice_ref');
        $this->db->from('geopos_purchase');
        $this->db->where('id', $tid); // Use $tid here instead of $row['id']
        $query = $this->db->get();
        $inv_id = $query->row()->inv_id;
        $invoice_ref = $query->row()->invoice_ref;

        $data = array(
            'acid' => $acid,
            'account' => $account['holder'],
            'type' => 'Expense',
            'cat' => 'Purchase',
            'debit' => $amount,
            'payer' => $cname,
            'payerid' => $cid,
            'method' => $pmethod,
            'date' => $paydate,
            'eid' => $this->aauth->get_user()->id,
            'tid' => $tid,
            'note' => $note,
            'ext' => 1,
            'loc' => $this->aauth->get_user()->loc,
            'trans_ref' => $inv_id,
            'inv_id' => $invoice_ref
        );
        $this->db->insert('geopos_transactions', $data);
        $this->db->insert_id();
        $this->db->select('total,csd,pamnt');
        $this->db->from('geopos_purchase');
        $this->db->where('id', $tid);
        $query = $this->db->get();
        $invresult = $query->row();
        $totalrm = $invresult->total - $invresult->pamnt;
        if ($totalrm > $amount) {
            $this->db->set('pmethod', $pmethod);
            $this->db->set('pamnt', "pamnt+$amount", FALSE);
            $this->db->set('status', 'partial');
            $this->db->where('id', $tid);
            $this->db->update('geopos_purchase');
            //account update
            $this->db->set('lastbal', "lastbal-$amount", FALSE);
            $this->db->where('id', $acid);
            $this->db->update('geopos_accounts');
            $paid_amount = $invresult->pamnt + $amount;
            $status = 'Partial';
            $totalrm = $totalrm - $amount;
        } else {
            $this->db->set('pmethod', $pmethod);
            $this->db->set('pamnt', "pamnt+$amount", FALSE);
            $this->db->set('status', 'paid');
            $this->db->where('id', $tid);
            $this->db->update('geopos_purchase');
            //acount update
            $this->db->set('lastbal', "lastbal-$amount", FALSE);
            $this->db->where('id', $acid);
            $this->db->update('geopos_accounts');
            $totalrm = 0;
            $status = 'Paid';
            $paid_amount = $amount;
        }

        $dual = $this->custom->api_config(65);
        if ($dual['key1']) {

            $this->db->select('holder');
            $this->db->from('geopos_accounts');
            $this->db->where('id', $dual['url']);
            $query = $this->db->get();
            $account = $query->row_array();

            $data['debit'] = 0;
            $data['credit'] = $amount;
            $data['type'] = 'Income';
            $data['acid'] = $dual['url'];
            $data['account'] = $account['holder'];
            $data['note'] = 'Credit ' . $data['note'];

            $this->db->insert('geopos_transactions', $data);

            //account update
            $this->db->set('lastbal', "lastbal+$amount", FALSE);
            $this->db->where('id', $dual['url']);
            $this->db->update('geopos_accounts');
        }
        $activitym = "<tr><td>" . substr($paydate, 0, 10) . "</td><td>$pmethod</td><td>$amount</td><td>$note</td></tr>";


        echo json_encode(array('status' => 'Success', 'message' =>
        $this->lang->line('Transaction has been added'), 'pstatus' => $this->lang->line($status), 'activity' => $activitym, 'amt' => $totalrm, 'ttlpaid' => $paid_amount));
    }


    public function cancelinvoice()
    {
        if (!$this->aauth->permission_new(null, 'accountsManage')) {

            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }


        $tid = intval($this->input->post('tid'));


        $this->db->set('pamnt', "0.00", FALSE);
        $this->db->set('total', "0.00", FALSE);
        $this->db->set('items', 0);
        $this->db->set('status', 'canceled');
        $this->db->where('id', $tid);
        $this->db->update('geopos_invoices');
        //reverse
        $this->db->select('credit,debit,acid');
        $this->db->from('geopos_transactions');
        $this->db->where('tid', $tid);
        $query = $this->db->get();
        $revresult = $query->result_array();
        foreach ($revresult as $trans) {
            $amt = $trans['credit'] - $trans['debit'];
            $this->db->set('lastbal', "lastbal-$amt", FALSE);
            $this->db->where('id', $trans['acid']);
            $this->db->update('geopos_accounts');
        }
        $this->db->select('pid,qty');
        $this->db->from('geopos_invoice_items');
        $this->db->where('tid', $tid);
        $query = $this->db->get();
        $prevresult = $query->result_array();
        foreach ($prevresult as $prd) {
            $amt = $prd['qty'];
            $this->db->set('qty', "qty+$amt", FALSE);
            $this->db->where('pid', $prd['pid']);
            $this->db->update('geopos_products');
        }
        $this->db->delete('geopos_transactions', array('tid' => $tid));
        $data = array('type' => 9, 'rid' => $tid);
        $this->db->delete('geopos_metadata', $data);
        echo json_encode(array('status' => 'Success', 'message' =>
        $this->lang->line('Invoice canceled')));
    }


    public function cancelpurchase()
    {
        if (!$this->aauth->permission_new(null, 'accountsManage')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $tid = intval($this->input->post('tid'));
        $this->db->set('pamnt', "0.00", FALSE);
        $this->db->set('status', 'canceled');
        $this->db->where('id', $tid);
        $this->db->update('geopos_purchase');
        //reverse
        $this->db->select('debit,credit,acid');
        $this->db->from('geopos_transactions');
        $this->db->where('tid', $tid);
        $this->db->where('ext', 1);
        $query = $this->db->get();
        $revresult = $query->result_array();
        foreach ($revresult as $trans) {
            $amt = $trans['debit'] - $trans['credit'];
            $this->db->set('lastbal', "lastbal+$amt", FALSE);
            $this->db->where('id', $trans['acid']);
            $this->db->update('geopos_accounts');
        }
        $this->db->select('pid,qty');
        $this->db->from('geopos_purchase_items');
        $this->db->where('tid', $tid);
        $query = $this->db->get();
        $prevresult = $query->result_array();
        foreach ($prevresult as $prd) {
            $amt = $prd['qty'];
            $this->db->set('qty', "qty-$amt", FALSE);
            $this->db->where('pid', $prd['pid']);
            $this->db->update('geopos_products');
        }
        $this->db->delete('geopos_transactions', array('tid' => $tid, 'ext' => 1));
        echo json_encode(array('status' => 'Success', 'message' =>
        $this->lang->line('Purchase canceled!')));
    }

    public function translist()
    {
        if (!$this->aauth->permission_new(null, 'accountsManage')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $ttype = $this->input->get('type');
        $list = $this->transactions->get_datatables($ttype);
        $data = array();
        // $no = $_POST['start'];
        $no = $this->input->post('start');
        foreach ($list as $prd) {
            $no++;
            $row = array();
            $pid = $prd->id;
            $row[] = dateformat($prd->date);
            $row[] = $prd->account;
            $row[] = amountExchange($prd->debit, 0, $this->aauth->get_user()->loc);
            $row[] = amountExchange($prd->credit, 0, $this->aauth->get_user()->loc);
            $row[] = $prd->payer;
            $row[] = $this->lang->line($prd->method);
            $row[] = '<a href="' . base_url() . 'transactions/view?id=' . $pid . '" class="btn btn-primary btn-sm"><span class="fa fa-eye"></span>  ' . $this->lang->line('View') . '</a> <a href="' . base_url() . 'transactions/print_t?id=' . $pid . '" class="btn btn-info btn-sm"  title="Print"><span class="fa fa-print"></span></a>';
            // $row[] = '<a href="' . base_url() . 'transactions/view?id=' . $pid . '" class="btn btn-primary btn-sm"><span class="fa fa-eye"></span>  ' . $this->lang->line('View') . '</a> <a href="' . base_url() . 'transactions/print_t?id=' . $pid . '" class="btn btn-info btn-sm"  title="Print"><span class="fa fa-print"></span></a>&nbsp; &nbsp;<a  href="#" data-object-id="' . $pid . '" class="btn btn-danger btn-sm delete-object"><span class="fa fa-trash"></span></a>';
            $data[] = $row;
        }
        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->transactions->count_all(),
            "recordsFiltered" => $this->transactions->count_filtered(),
            "data" => $data,
        );
        //output to json format
        echo json_encode($output);
    }


    // Category
    public function categories()
    {
        $this->li_a = 'misc_settings';
        if ($this->aauth->get_user()->roleid < 5) {

            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }

        $data['catlist'] = $this->transactions->categories();
        $head['title'] = "Category";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $this->load->view('transactions/cat', $data);
        $this->load->view('fixed/footer');
    }

    public function createcat()
    {
        if ($this->aauth->get_user()->roleid < 5) {

            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }

        $head['title'] = "Category";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $this->load->view('transactions/cat_create');
        $this->load->view('fixed/footer');
    }

    public function editcat()
    {

        if ($this->aauth->get_user()->roleid < 5) {

            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }

        $head['title'] = "Category";
        $head['usernm'] = $this->aauth->get_user()->username;

        $id = $this->input->get('id');

        $data['cat'] = $this->transactions->cat_details($id);

        $this->load->view('fixed/header', $head);
        $this->load->view('transactions/trans-cat-edit', $data);
        $this->load->view('fixed/footer');
    }

    public function save_createcat()
    {

        if ($this->aauth->get_user()->roleid < 5) {

            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }

        $name = $this->input->post('catname');

        if ($this->transactions->addcat($name)) {
            echo json_encode(array('status' => 'Success', 'message' =>
            $this->lang->line('ADDED')));
        } else {
            echo json_encode(array('status' => 'Error', 'message' =>
            $this->lang->line('ERROR')));
        }
    }

    public function editcatsave()
    {
        if ($this->aauth->get_user()->roleid < 5) {

            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }

        $id = $this->input->post('catid');
        $name = $this->input->post('cat_name');

        if ($this->transactions->cat_update($id, $name)) {

            echo json_encode(array('status' => 'Success', 'message' =>
            $this->lang->line('UPDATED')));
        } else {

            echo json_encode(array('status' => 'Error', 'message' =>
            'Error!'));
        }
    }

    public function delete_cat()
    {
        if ($this->aauth->get_user()->roleid < 5) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }

        $id = $this->input->post('deleteid');
        if ($id) {
            $this->db->delete('geopos_trans_cat', array('id' => $id));
            echo json_encode(array('status' => 'Success', 'message' => $this->lang->line('DELETED')));
        } else {
            echo json_encode(array('status' => 'Error', 'message' => 'Error!'));
        }
    }

    public function create_trans()
    {
        if (!$this->aauth->permission_new(null, 'accountsNewTransaction')) {

            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $dual = $this->custom->api_config(65);
        
        $credit = 0;
        $debit = 0;
        $payer_id = $this->input->post('payer_id', true);
        $payer_ty = $this->input->post('ty_p', true);
        $payer_name = $this->input->post('payer_name', true);
        $pay_acc = $this->input->post('pay_acc', true);
        $date = $this->input->post('date', true);
        $amount = numberClean($this->input->post('amount', true));
        $pay_type = $this->input->post('pay_type', true);
        if ($pay_type == 'Income') {
            $credit = $amount;
        } elseif ($pay_type == 'Expense') {
            $debit = $amount;
        }
        $pay_cat = $this->input->post('pay_cat');
        $paymethod = $this->input->post('paymethod');
        $note = $this->input->post('note');
        $date = datefordatabase($date);
        if ($amount > 0) {
            // Generate a transaction reference and default flag for advance payment
            if (!function_exists('generateUniqueTransRefCT')) {
                function generateUniqueTransRefCT() { return mt_rand(100000, 999999); }
            }
            $trans_ref = generateUniqueTransRefCT();
            $is_advance_payment = '';

            if ($this->transactions->addtrans($payer_id, $payer_name, $pay_acc, $date, $debit, $credit, $pay_type, $pay_cat, $paymethod, $note, $this->aauth->get_user()->id, $this->aauth->get_user()->loc, $payer_ty, $is_advance_payment, $trans_ref)) {
                $lid = $this->db->insert_id();

                $this->db->select('balance');
                $this->db->from('geopos_customers');
                $this->db->where('id', $payer_id);
                $query = $this->db->get();
                $customer_balance_form_query = $query->row_array();
                $c_b = $customer_balance_form_query['balance'];
                $c_b = $c_b + $credit;

                $this->db->set('balance', $c_b, FALSE);
                $this->db->where('id', $lid);
                $this->db->update('geopos_transactions');

                $this->db->set('balance', $c_b, FALSE);
                $this->db->where('id', $payer_id);
                $this->db->update('geopos_customers');





                if ($dual['key1']) {
                    $pay_acc = $this->input->post('f_pay_acc', true);
                    $pay_cat = $this->input->post('f_pay_cat');
                    $paymethod = $this->input->post('f_paymethod');
                    $note = $this->input->post('f_note');
                    if ($pay_type == 'Income') {
                        $debit = $amount;
                        $credit = 0;
                        $pay_type_r = 'Expense';
                    } elseif ($pay_type == 'Expense') {
                        $credit = $amount;
                        $debit = 0;
                        $pay_type_r = 'Income';
                    }
                    $this->transactions->addtrans($payer_id, $payer_name, $pay_acc, $date, $debit, $credit, $pay_type_r, $pay_cat, $paymethod, $note, $this->aauth->get_user()->id, $this->aauth->get_user()->loc, $payer_ty, $is_advance_payment, $trans_ref);
                }

                echo json_encode(array('status' => 'Success', 'message' =>
                $this->lang->line('Transaction has been') . "  <a href='" . base_url() . "transactions/add' class='btn btn-blue '><span class='fa fa-plus-circle' aria-hidden='true'></span> " . $this->lang->line('New') . "  </a> <a href='" . base_url() . 'transactions/view?id=' . $lid . "' class='btn btn-primary btn-xs'><span class='fa fa-eye'></span>  " . $this->lang->line('View') . "</a> <a href='" . base_url() . "transactions' class='btn btn-pink '><span class='fa fa-list-alt aria-hidden='true'></span></a>"));
            }
        } else {
            echo json_encode(array('status' => 'Error', 'message' =>
            'Error!'));
        }

        $alert = $this->custom->api_config(66);
        if ($alert['key1'] == 1) {
            $this->load->model('communication_model');
            $subject = $payer_name . ' ' . $this->lang->line('Transaction has been');
            $body = $subject . '<br> ' . $this->lang->line('Credit') . ' ' . $this->lang->line('Amount') . ' ' . $credit . '<br> ' . $this->lang->line('Debit') . ' ' . $this->lang->line('Amount') . ' ' . $debit . '<br> ID# ' . $lid;
            $out = $this->communication_model->send_corn_email($alert['url'], $alert['url'], $subject, $body, false, '');
        }
    }
    public function search_transaction()
    {
        $trans_ref = $this->input->get('term'); // Get the search term from the AJAX request

        // Add condition to fetch data where reversed = 0
        $this->db->like('trans_ref', $trans_ref);
        $this->db->where('reversed', 0); // Only fetch where reversed = 0
        $query = $this->db->get('geopos_transactions'); // Make sure this is your table name

        $results = $query->result_array();

        $output = [];
        if ($results) {
            // Initialize an array to store unique trans_ref with summed debit amounts
            $unique_trans_refs = [];

            // Loop through the results to aggregate debit amounts
            foreach ($results as $result) {
                $trans_ref = $result['trans_ref'];
                $payer = $result['payer'];
                $debit = (float) $result['debit']; // Convert debit to float for accurate summing

                // Ensure payer_id is correctly fetched and handled
                $payer_id = $result['payerid']; // Assuming payer_id is correctly fetched from the database
                $eid = $result['eid']; // Assuming eid is correctly fetched from the database
                $tid = $result['tid']; // Assuming tid is correctly fetched from the database
                $loc = $result['loc'];
                $created_by = $result['created_by'];
                $supplier = $result['supplier'];
                $is_advance_payment = $result['is_advance_payment'];

                // If trans_ref already exists in $unique_trans_refs, add to the existing debit amount
                if (isset($unique_trans_refs[$trans_ref])) {
                    $unique_trans_refs[$trans_ref]['amount'] += $debit;
                } else {
                    // Initialize a new entry for trans_ref
                    $unique_trans_refs[$trans_ref] = [
                        'label' => $trans_ref . ' - ' . $payer,
                        'value' => $trans_ref,
                        'payer' => $payer,
                        'eid' => $eid,
                        'loc' => $loc,
                        'tid' => $tid,
                        'is_advance_payment' => $is_advance_payment,
                        'supplier' => $supplier,
                        'tid' => $tid,
                        'payer_id' => $payer_id, // Include payer_id in the result array
                        'amount' => $debit
                    ];
                }
            }

            // Convert the associative array to indexed array for JSON encoding
            $output = array_values($unique_trans_refs);
        }

        echo json_encode($output); // Output JSON formatted data
    }


    public function revers_trans()
    {
        $invoice_table = 'geopos_invoices';
        $amount = $this->input->post('amount', true); // Amount from form input
        $debit = 0.00; // Assuming debit is 0 for the reverse transaction
        $payer_id = $this->input->post('payer_id', true); // Payer ID from form input
        $this->db->select('company');
        $this->db->from('geopos_customers');
        $this->db->where('id', $payer_id);
        $query_c = $this->db->get();
        $customer = $query_c->row_array();
        $cust_name = $customer['company'];
        $eid = $this->input->post('eid', true); // Employee ID from form input
        $tid = $this->input->post('tid', true); // Transaction ID from form input
        $loc = $this->aauth->get_user()->loc; // Location from form input
        $trans_ref = $this->input->post('trans_ref', true); // Transaction reference from form input
        $payer_name = $this->input->post('payer', true); // Payer name from form input
        $is_advance_payment = $this->input->post('is_advance_payment', true); // Is advance payment from form input
        $supplier = $this->input->post('supplier', true); // Supplier from form input
        $created_by = $this->input->post('created_by', true); // Created by from form input
        $date = date('Y-m-d H:i:s'); // Invoice date from form input

        // Calculate due date 1 month ahead
        $due_date = date('Y-m-d', strtotime($date . ' +1 month'));

        // Fetch maximum tid from your invoice table
        $this->db->select_max('tid');
        $this->db->from($invoice_table);
        $query = $this->db->get();
        $pr = $query->row_array();
        $cat_id = $pr['tid']; // Assuming this is the maximum tid

        // Increment the maximum tid to get the new invoice number
        $invocieno = $cat_id + 1;

        // Fetch customer details based on payer_id (assuming aauth is used for authentication)
        $cust = $this->aauth->get_customer($payer_id);

        // Prepare data array for inserting into your invoice table
        $data = array(
            'created_by' => $this->aauth->get_user()->id,
            'tid' => $invocieno,
            'inv_type' => 'REVERSAL',
            'invoicedate' => $date,
            'invoiceduedate' => $due_date,
            'subtotal' => $amount,
            'shipping' => 0.00,
            'ship_tax' => 0.00,
            'ship_tax_type' => 'incl',
            'discount_rate' => 0.00,
            'total' => $amount,
            'notes' => '',
            'csd' => $payer_id,
            'eid' => $eid,
            'pamnt' => 0.00,
            'items' => 0.00,
            'taxstatus' => '',
            'discstatus' => 1,
            'format_discount' => '%',
            'refer' => '',
            'term' => 1,
            'multi' => '',
            'loc' => $this->aauth->get_user()->loc,
            'cust_name' => $cust_name,
            'cust_address' => $cust->address,
            'cust_postcode' => $cust->postbox,
            'cust_city' => $cust->city,
            'driver_name' => '',
            'vehicle_no' => '',
            'pamt_terms' => ''
        );

        // Insert data into the invoice table
        if ($this->db->insert($invoice_table, $data)) {
            $invocieno2 = $this->db->insert_id(); // Get the inserted invoice ID

            // Prepare data array for inserting into geopos_transactions table
            $data_trans = array(
                'acid' => 1, // Replace with your actual acid value
                'account' => 'Sales Account', // Replace with actual values
                'type' => 'Expense', // Replace with actual values
                'cat' => 'Sales', // Replace with actual values
                'credit' => $amount, // Replace with actual values if applicable
                'debit' => $debit, // Replace with actual values
                'balance' => $amount, // Replace with actual values
                'payer' => $payer_name, // Replace with actual values
                'payerid' => $payer_id, // Replace with actual values
                'method' => '', // Replace with actual values
                'paymt_method' => '', // Replace with actual values
                'paymt_date' => $date, // Replace with actual values
                'date' => $date, // Replace with actual values
                'eid' => $eid, // Replace with actual values
                'tid' => $invocieno2, // Use the new invoice ID as tid
                'inv_id' => $invocieno, // Use the inserted invoice ID as inv_id
                'note' => 'Reversal Transaction', // Note with the invoice number
                'ext' => '', // Replace with actual values if applicable
                'loc' => $loc, // Replace with actual values
                'os' => '', // Replace with actual values if applicable
                'trans_ref' => $trans_ref, // Replace with actual values
                'reversed' => 1, // Indicate that this is a reversed transaction
                'created' => date('Y-m-d H:i:s'), // Timestamp of creation
                'created_by' => $created_by, // Created by user ID
                'updated_on' => date('Y-m-d H:i:s'), // Timestamp of update if applicable
                'is_advance_payment' => $is_advance_payment, // Replace with actual values if applicable
                'supplier' => $supplier // Replace with actual values if applicable
            );

            // Insert data into geopos_transactions table
            $this->db->insert('geopos_transactions', $data_trans);
            $this->db->where('trans_ref', $trans_ref);
            $this->db->update('geopos_transactions', array('reversed' => 1));
            echo json_encode(array('status' => 'Success', 'message' => 'Transaction has been Reversed Successfully!'));
        } else {
            echo json_encode(array('status' => 'Error', 'message' => 'Insert into invoice table failed.'));
        }
    }





    public function save_trans()
    {
        if (!$this->aauth->permission_new(null, 'accountsManage')) {

            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $dual = $this->custom->api_config(65);

        $credit = 0;
        $debit = 0;
        $payer_id = $this->input->post('payer_id', true);
        $this->db->select('company');
        $this->db->from('geopos_customers');
        $this->db->where('id', $payer_id);
        $query_c = $this->db->get();
        $customer = $query_c->row_array();
        $cust_name = $customer['company'];
        $payer_ty = $this->input->post('ty_p', true);
        $payer_name = $this->input->post('payer_name', true);
        $pay_acc = $this->input->post('pay_acc', true);
        $date = $this->input->post('date', true);
        $amount = numberClean($this->input->post('amount', true));
        $invocieno = numberClean($this->input->post('invocieno', true));


        $pay_type = $this->input->post('pay_type', true);
        if ($pay_type == 'Income') {
            $credit = $amount;
        } elseif ($pay_type == 'Expense') {
            $debit = $amount;
        }
        $pay_cat = $this->input->post('pay_cat');
        $paymethod = $this->input->post('paymethod');
        $note = $this->input->post('note');
        if (!$note) {
            $note = "Advance Payment";
        }
        $sql = 'SELECT COALESCE(SUM(total), 0) - COALESCE(SUM(pamnt), 0) AS cust_balance 
        FROM geopos_invoices 
        WHERE (status="due" OR status="partial") 
        AND csd="' . $payer_id . '"';

        $query = $this->db->query($sql);
        $response = $query->row_array();
        $cust_b = isset($response['cust_balance']) ? $response['cust_balance'] : 0;

        if ($cust_b > 0) {
            echo json_encode(array(
                'status' => 'Note',
                'message' => $this->lang->line('Transaction Failed') . "Please settle the first invoice for <b>" . htmlspecialchars($payer_name) . "</b> before making an advance payment."
            ));
        } else {
            $date = datefordatabase($date);
            if ($amount > 0 && isset($payer_id)) {

                $is_advance_payment = 1;

                if ($this->transactions->addtrans2($payer_id, $invocieno, $payer_name, $pay_acc, $date, $debit, $credit, $pay_type, $pay_cat, $paymethod, $note, $this->aauth->get_user()->id, $this->aauth->get_user()->loc, $payer_ty, $is_advance_payment)) {
                    $lid = $this->db->insert_id();

                    $this->db->select('balance');
                    $this->db->from('geopos_customers');
                    $this->db->where('id', $payer_id);
                    $query = $this->db->get();
                    $customer_balance_form_query = $query->row_array();
                    $c_b = floatval($customer_balance_form_query['balance']);
                    $c_b = $c_b + $credit;

                    $this->db->set('balance', $c_b, FALSE);
                    $this->db->where('id', $lid);
                    $this->db->update('geopos_transactions');

                    $this->db->set('balance', $c_b, FALSE);
                    $this->db->where('id', $payer_id);
                    $this->db->update('geopos_customers');

                    $this->db->set('advance_payment', "advance_payment+$amount", FALSE);
                    $this->db->set('advance_payment_used', 0);
                    $this->db->where('id', $payer_id);
                    $this->db->update('geopos_customers');

                    if (!$note) {
                        $note = "Advance Payment";
                    }

                    $this->aauth->applog("[New Advance Payment Transaction Created - $payer_name] - Amount: $amount, Type: $pay_type", $this->aauth->get_user()->username);

                    $data_ret_invoices = array('updated_on' => date("Y-m-d h:i:s"), 'tid' => $invocieno, 'created_by' => $this->aauth->get_user()->id, 'invoicedate' => $date, 'invoiceduedate' => $date, 'inv_type' => 'ADVANCE', 'subtotal' => $amount, 'shipping' => 0, 'ship_tax' => 0, 'ship_tax_type' => 'incl',  'discount_rate' => 0, 'discount' => 0, 'tax' => "-" . 0, 'total' => "-" . $amount, 'notes' => 'Advance Payment', 'csd' => $payer_id, 'items' => 0, 'taxstatus' => 0, 'i_class ' => 0, 'loc' => 0, 'r_time' => 'mig', 'eid' => $this->aauth->get_user()->id, 'discstatus' => 1, 'format_discount' => "%", 'term' => 1, 'cust_name' => $cust_name, 'cust_address' => $payer_name, 'cust_city' => $payer_name, 'cust_postcode' => $payer_name);
                    $data_ins = $this->db->insert('geopos_invoices', $data_ret_invoices);

                    $this->db->insert('geopos_invoices_bfr_post', $data_ret_invoices);


                    $this->db->select('amount');
                    $this->db->from('advance_payment');
                    $this->db->where('payerid', $payer_id);
                    $query = $this->db->get();
                    $rows = $query->num_rows();
                    $customer_balance_form_query = $query->row_array();
                    $c_b = floatval($customer_balance_form_query['amount']);

                    // var_dump($c_b, $rows);


                    if ($rows > 0) {
                        $data = array(
                            'payer' => $payer_name,
                            'date' => $date,
                            'amount' => $amount + $c_b,
                            'method' => $paymethod,
                            'eid' => $this->aauth->get_user()->id,
                            'note' => $note,
                            'tid' => $invocieno
                        );
                        $this->db->where('payerid', $payer_id);
                        $this->db->update('advance_payment', $data);
                    } else {
                        $data = array(
                            'payerid' => $payer_id,
                            'payer' => $payer_name,
                            'date' => $date,
                            'amount' => $amount,
                            'method' => $paymethod,
                            'eid' => $this->aauth->get_user()->id,
                            'note' => $note,
                            'tid' => $invocieno
                        );
                        $this->db->insert('advance_payment', $data);
                    }


                    $this->db->select('geopos_invoices.*');
                    $this->db->from('geopos_invoices');
                    $this->db->where_in('status', ['partial', 'due']);
                    $this->db->where('r_time', 0);
                    $this->db->where('status !=', 'paid');
                    $this->db->where('notes =', ' ');
                    $this->db->where('csd',  $payer_id);
                    $query = $this->db->get();

                    $rows = $query->num_rows();



                    if ($rows > 0) {
                        $result = $query->result();
                        foreach ($result as $prd) {
                            if ($prd->total > 0) {

                                $totalrm = $prd->total -  $prd->pamnt;

                                if ($totalrm > 0) {
                                    $this->db->select('amount');
                                    $this->db->from('advance_payment');
                                    $this->db->where('payerid', $payer_id);
                                    $query = $this->db->get();
                                    $customer_balance_form_query = $query->row_array();
                                    $c_b = floatval($customer_balance_form_query['amount']);
                                    if ($totalrm > $c_b && $c_b > 0) {
                                        $this->db->set('pmethod', $paymethod);
                                        $this->db->set('pamnt', "pamnt+$c_b", FALSE);
                                        $this->db->set('status', 'partial');
                                        $this->db->where('id', $prd->id);
                                        $this->db->update('geopos_invoices');
                                        $status = 'Partial';
                                        $this->aauth->applog("[Invoice Partial Paid from Advance Balance - Invoice: " . $prd->tid . "] - Customer: " . $payer_name . ", Status: " . $status . ", Amount: " . $c_b, $this->aauth->get_user()->username);
                                        $this->db->set('amount', 0.00);
                                        $this->db->where('payerid', $payer_id);
                                        $this->db->update('advance_payment');
                                        $this->db->set('status', 'paid');
                                        $this->db->where('csd', $payer_id);
                                        $this->db->where('notes =', 'Advance Payment');
                                        $this->db->where('r_time', 'mig');
                                        $this->db->update('geopos_invoices');
                                    }

                                    if ($totalrm == $c_b) {


                                        $this->db->set('pmethod', $paymethod);
                                        $this->db->set('pamnt', "pamnt+$totalrm", FALSE);
                                        $this->db->set('status', 'paid');
                                        $this->db->where('id', $prd->id);
                                        $this->db->update('geopos_invoices');
                                        $status = 'Paid';
                                        $this->aauth->applog("[Invoice Comlpete Paid from Advance Balance - Invoice: " . $prd->tid . "] - Customer: " . $payer_name . ", Status: " . $status . ", Amount: " . $c_b, $this->aauth->get_user()->username);
                                        $this->db->set('amount', 0.00);
                                        $this->db->where('payerid', $payer_id);
                                        $this->db->update('advance_payment');
                                        $this->db->set('status', 'paid');
                                        $this->db->where('csd', $payer_id);
                                        $this->db->where('notes =', 'Advance Payment');
                                        $this->db->where('r_time', 'mig');
                                        $this->db->update('geopos_invoices');
                                    } else {

                                        $this->db->select('amount');
                                        $this->db->from('advance_payment');
                                        $this->db->where('payerid', $payer_id);
                                        $query = $this->db->get();
                                        $customer_balance_form_query = $query->row_array();
                                        $c_b = floatval($customer_balance_form_query['amount']);

                                        if ($totalrm < $c_b && $c_b > 0) {

                                            $this->db->set('pmethod', $paymethod);
                                            $this->db->set('pamnt', "pamnt+$totalrm", FALSE);
                                            $this->db->set('status', 'paid');
                                            $this->db->where('id', $prd->id);
                                            $this->db->update('geopos_invoices');
                                            $status = 'Paid';
                                            $this->aauth->applog("[Invoice  Paid from Advance Balance - Invoice: " . $prd->tid . "] - Customer: " . $payer_name . ", Status: " . $status . ", Amount: " . $c_b, $this->aauth->get_user()->username);
                                            $this->db->set('amount', "amount-$totalrm", FALSE);
                                            $this->db->where('payerid', $payer_id);
                                            $this->db->update('advance_payment');

                                            $this->db->select('geopos_invoices.*');
                                            $this->db->from('geopos_invoices');
                                            $this->db->where_in('status', ['partial', 'due']);
                                            $this->db->where('r_time', 'mig');
                                            $this->db->where('status !=', 'paid');
                                            $this->db->where('notes =', 'Advance Payment');
                                            $this->db->where('csd',  $payer_id);
                                            $query_2 = $this->db->get();
                                            $rows2 = $query_2->num_rows();
                                            $total_new = $totalrm;
                                            if ($rows2 > 0) {
                                                $result_arr = $query_2->result();
                                                foreach ($result_arr as $prd) {

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
                                                            var_dump($this->db->last_query());

                                                            $total_new -= $prd->subtotal;
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }


                    echo json_encode(array('status' => 'Success', 'message' =>
                    $this->lang->line('Transaction has been') . "  <a href='" . base_url() . "transactions/advance' class='btn btn-blue '><span class='fa fa-plus-circle' aria-hidden='true'></span> " . $this->lang->line('New') . "  </a> <a href='" . base_url() . 'transactions/view?id=' . $lid . "' class='btn btn-primary btn-xs'><span class='fa fa-eye'></span>  " . $this->lang->line('View') . "</a> <a href='" . base_url() . "transactions' class='btn btn-pink '><span class='fa fa-list-alt aria-hidden='true'></span></a>"));
                }
            } else {
                echo json_encode(array('status' => 'Error', 'message' =>
                'Error!'));
            }
        }
        $alert = $this->custom->api_config(66);
        if ($alert['key1'] == 1) {
            $this->load->model('communication_model');
            $subject = $payer_name . ' ' . $this->lang->line('Transaction has been');
            $body = $subject . '<br> ' . $this->lang->line('Credit') . ' ' . $this->lang->line('Amount') . ' ' . $credit . '<br> ' . $this->lang->line('Debit') . ' ' . $this->lang->line('Amount') . ' ' . $debit . '<br> ID# ' . $lid;
            $out = $this->communication_model->send_corn_email($alert['url'], $alert['url'], $subject, $body, false, '');
        }
    }

    public function new_trans()
    {
        if (!$this->aauth->permission_new(null, 'accountsNewTransaction')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }

        $dual = $this->custom->api_config(65);

        $credit = 0;
        $debit = 0;
        $payer_id = $this->input->post('payer_id', true);
        $payer_ty = $this->input->post('ty_p', true);
        $payer_name = $this->input->post('payer_name', true);
        $pay_acc = $this->input->post('pay_acc', true);
        $date = $this->input->post('date', true);
        $amount = numberClean($this->input->post('amount', true));
        $vat = numberClean($this->input->post('vat', true));
        $subtotal = $amount - $vat;
        $pay_type = $this->input->post('pay_type', true);
        if ($pay_type == 'Income') {
            $credit = $amount;
        } elseif ($pay_type == 'Expense') {
            $debit = $amount;
            $amount = -1 * ($amount);
        }
        $pay_cat = $this->input->post('pay_cat');
        $paymethod = $this->input->post('paymethod');
        $note = $this->input->post('note');
        $date = datefordatabase($date);
        // Function to generate a random 6-digit unique transaction reference
        function generateUniqueTransRef()
        {
            return mt_rand(100000, 999999);
        }

        // Generate a random transaction reference
        $trans_ref = generateUniqueTransRef();
        // if ($amount > 0) {
        if ($this->transactions->addtrans($payer_id, $payer_name, $pay_acc, $date, $debit, $credit, $pay_type, $pay_cat, $paymethod, $note, $this->aauth->get_user()->id, $this->aauth->get_user()->loc, $payer_ty, '', $trans_ref)) {
            $lid = $this->db->insert_id();

            if ($payer_ty == 0) {
                $this->db->select('balance');
                $this->db->from('geopos_customers');
                $this->db->where('id', $payer_id);
                $query = $this->db->get();
                $customer_balance_form_query = $query->row_array();
                $c_b = $customer_balance_form_query['balance'];
                $c_b = $c_b + $credit;

                $this->db->set('balance', $c_b, FALSE);
                $this->db->where('id', $lid);
                $this->db->update('geopos_transactions');

                $this->db->set('balance', $c_b, FALSE);
                $this->db->where('id', $payer_id);
                $this->db->update('geopos_customers');
            }

            $this->aauth->applog("[New Transaction Created - $payer_name] - Amount: $amount, Type: $pay_type", $this->aauth->get_user()->username);

            $data_ret_invoices = array('updated_on' => date("Y-m-d h:i:s"), 'created_by' => $this->aauth->get_user()->id, 'invoicedate' => date('Y-m-d'), 'invoiceduedate' => date("Y-m-d h:i:s"), 'inv_type' => 'INVOICE', 'subtotal' => $subtotal, 'shipping' => 0, 'ship_tax' => 0, 'ship_tax_type' => 'incl', 'discount_rate' => 0, 'discount' => 0, 'tax' => $vat, 'total' => $amount, 'notes' => $note, 'csd' => $payer_id, 'items' => 0, 'taxstatus' => 0, 'i_class ' => 0, 'loc' => 0, 'eid' => $this->aauth->get_user()->id, 'discstatus' => 1, 'format_discount' => "%", 'term' => 1, 'cust_name' => $payer_name, 'cust_address' => $payer_name, 'cust_city' => $payer_name, 'cust_postcode' => $payer_name);

            $data_ins = $this->db->insert('geopos_invoices', $data_ret_invoices);

            if ($dual['key1']) {
                $pay_acc = $this->input->post('f_pay_acc', true);
                $pay_cat = $this->input->post('f_pay_cat');
                $paymethod = $this->input->post('f_paymethod');
                $note = $this->input->post('f_note');
                if ($pay_type == 'Income') {
                    $debit = $amount;
                    $credit = 0;
                    $pay_type_r = 'Expense';
                } elseif ($pay_type == 'Expense') {
                    $credit = $amount;
                    $debit = 0;
                    $pay_type_r = 'Income';
                }
                $this->transactions->addtrans($payer_id, $payer_name, $pay_acc, $date, $debit, $credit, $pay_type_r, $pay_cat, $paymethod, $note, $this->aauth->get_user()->id, $this->aauth->get_user()->loc, $payer_ty, $trans_ref);
            }

            echo json_encode(array('status' => 'Success', 'message' =>
            $this->lang->line('Transaction has been') . "  <a href='" . base_url() . "transactions/add' class='btn btn-blue '><span class='fa fa-plus-circle' aria-hidden='true'></span> " . $this->lang->line('New') . "  </a> <a href='" . base_url() . 'transactions/view?id=' . $lid . "' class='btn btn-primary btn-xs'><span class='fa fa-eye'></span>  " . $this->lang->line('View') . "</a> <a href='" . base_url() . "transactions' class='btn btn-pink '><span class='fa fa-list-alt aria-hidden='true'></span></a>"));
        }
        // } else {
        //     echo json_encode(array('status' => 'Error', 'message' =>
        //         'Error!'));
        // }

        $alert = $this->custom->api_config(66);
        if ($alert['key1'] == 1) {
            $this->load->model('communication_model');
            $subject = $payer_name . ' ' . $this->lang->line('Transaction has been');
            $body = $subject . '<br> ' . $this->lang->line('Credit') . ' ' . $this->lang->line('Amount') . ' ' . $credit . '<br> ' . $this->lang->line('Debit') . ' ' . $this->lang->line('Amount') . ' ' . $debit . '<br> ID# ' . $lid;
            $out = $this->communication_model->send_corn_email($alert['url'], $alert['url'], $subject, $body, false, '');
        }
    }

    public function save_transfer()
    {
        if (!$this->aauth->permission_new(null, 'accountsManage')) {

            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }

        $pay_acc = $this->input->post('pay_acc');
        $pay_acc2 = $this->input->post('pay_acc2');
        $amount = (float)$this->input->post('amount', true);

        if ($amount > 0) {
            if ($this->transactions->addtransfer($pay_acc, $pay_acc2, $amount, $this->aauth->get_user()->id, $this->aauth->get_user()->loc)) {
                echo json_encode(array('status' => 'Success', 'message' =>
                "Transfer has been successfully done! <a href='" . base_url() . "transactions/transfer' class='btn btn-indigo btn-sm'><span class='icon-plus-circle' aria-hidden='true'></span> " . $this->lang->line('New') . "  </a> <a href='" . base_url() . "accounts' class='btn btn-indigo btn-sm'><span class='icon-list-ul' aria-hidden='true'></span></a>"));
            }
        } else {
            echo json_encode(array('status' => 'Error', 'message' =>
            'Error!'));
        }
    }


    public function delete_i()
    {
        if (!$this->aauth->permission_new(null, 'accountsManage')) {

            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }

        $id = $this->input->post('deleteid');
        if ($id) {


            echo json_encode($this->transactions->delt($id));
            $alert = $this->custom->api_config(66);
        } else {
            echo json_encode(array('status' => 'Error', 'message' => 'Error!'));
        }
    }

    public function income()
    {
        if (!$this->aauth->permission_new(null, 'accountsIncome')) {

            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $head['title'] = "Income Transaction";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $this->load->view('transactions/income');
        $this->load->view('fixed/footer');
    }

    public function expense()
    {
        if (!$this->aauth->permission_new(null, 'accountsExpense')) {

            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $head['title'] = "Expense Transaction";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $this->load->view('transactions/expense');
        $this->load->view('fixed/footer');
    }

    public function view()
    {
        if (!$this->aauth->permission_new(null, 'accountsViewTransactions')) {

            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $head['title'] = "View Transaction";
        $head['usernm'] = $this->aauth->get_user()->username;
        $id = $this->input->get('id');
        $data['trans'] = $this->transactions->view($id);

        if ($data['trans']['payerid'] > 0) {
            $data['cdata'] = $this->transactions->cview($data['trans']['payerid'], $data['trans']['ext']);
        } else {
            $data['cdata'] = array('address' => 'Not Registered', 'city' => '', 'phone' => '', 'email' => '');
        }
        $this->load->view('fixed/header', $head);
        if ($data['trans']['id']) $this->load->view('transactions/view', $data);
        $this->load->view('fixed/footer');
    }


    public function print_t()
    {
        if (!$this->aauth->permission_new(null, 'accountsManage')) {

            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $head['title'] = "View Transaction";
        $head['usernm'] = $this->aauth->get_user()->username;
        $id = $this->input->get('id');
        $data['trans'] = $this->transactions->view($id);
        if ($data['trans']['payerid'] > 0) {
            $data['cdata'] = $this->transactions->cview($data['trans']['payerid'], $data['trans']['ext']);
        } else {
            $data['cdata'] = array('address' => 'Not Registered', 'city' => '', 'phone' => '', 'email' => '');
        }


        ini_set('memory_limit', '64M');

        $html = $this->load->view('transactions/view-print', $data, true);

        //PDF Rendering
        $this->load->library('pdf');

        $pdf = $this->pdf->load_en();

        $pdf->SetHTMLFooter('<table width="100%" style="vertical-align: bottom; font-family: serif; font-size: 8pt; color: #5C5C5C; font-style: italic;"><tr><td width="33%"></td><td width="33%" align="center" style="font-weight: bold; font-style: italic;">{PAGENO}/{nbpg}</td><td width="33%" style="text-align: right; ">#' . $id . '</td></tr></table>');

        if ($data['trans']['id']) $pdf->WriteHTML($html);

        if ($this->input->get('d')) {

            $pdf->Output('Trans_#' . $id . '.pdf', 'D');
        } else {
            $pdf->Output('Trans_#' . $id . '.pdf', 'I');
        }
    }
}

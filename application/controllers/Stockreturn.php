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

use function PHPSTORM_META\type;

defined('BASEPATH') or exit('No direct script access allowed');
// use Mike42\Escpos\PrintConnectors\FilePrintConnector;
// use Mike42\Escpos\Printer;
// require_once APPPATH . "libraries/tcpdf/PDFMerger.php";
// use PDFMerger\PDFMerger;


class Stockreturn extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library("Aauth");
        if (!$this->aauth->is_loggedin()) {
            redirect('/user/', 'refresh');
        }
        if (!$this->aauth->permission_new(null, 'stockReturnAccess')) {

            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $this->li_a = 'stockreturn';

        $this->load->model('Stockreturn_model', 'stockreturn');
        $this->load->model('settings_model', 'settings');
        $this->load->library('dpdf');
    }

    //create invoice
    public function create()
    {
        if (!$this->aauth->permission_new(null, 'stockReturnAccess')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $this->load->library("Common");
        $this->load->model('plugins_model', 'plugins');
        $data['exchange'] = $this->plugins->universal_api(5);
        $data['currency'] = $this->stockreturn->currencies();
        $data['taxlist'] = $this->common->taxlist($this->config->item('tax'));
        $this->load->model('customers_model', 'customers');
        $data['customergrouplist'] = $this->customers->group_list();
        $data['lastinvoice'] = $this->stockreturn->lastpurchase();
        $data['terms'] = $this->stockreturn->billingterms();
        $head['title'] = "New Stock return";
        $head['usernm'] = $this->aauth->get_user()->username;
        $data['warehouse'] = $this->stockreturn->warehouses();
        $data['taxdetails'] = $this->common->taxdetail();
        $this->load->view('fixed/header', $head);
        $this->load->view('stockreturn/newinvoice', $data);
        $this->load->view('fixed/footer');
    }



    public function customer_invoices()
    {
        if (!$this->aauth->permission_new(null, 'stockCustomersReturns')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $head['title'] = "Customer Invoices";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $this->load->view('stockreturn/customer_invoices');
        $this->load->view('fixed/footer');
    }



    public function create_client()
    {
        if (!$this->aauth->permission_new(null, 'stockReturnAccess')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $this->load->library("Common");
        $this->load->model('plugins_model', 'plugins');
        $data['exchange'] = $this->plugins->universal_api(5);
        $data['currency'] = $this->stockreturn->currencies();
        $data['taxlist'] = $this->common->taxlist($this->config->item('tax'));
        $this->load->model('customers_model', 'customers');
        $data['customergrouplist'] = $this->customers->group_list();
        $data['lastinvoice'] = $this->stockreturn->lastpurchase();
        $data['terms'] = $this->stockreturn->billingterms();
        $head['title'] = "New Stock return";
        $head['usernm'] = $this->aauth->get_user()->username;
        $data['warehouse'] = $this->stockreturn->warehouses();
        $data['taxdetails'] = $this->common->taxdetail();
        $this->load->view('fixed/header', $head);
        $this->load->view('stockreturn/c_newinvoice', $data);
        $this->load->view('fixed/footer');
    }

    public function create_note()
    {
        if (!$this->aauth->permission_new(null, 'stockReturnAccess')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $this->load->library("Common");
        $data['taxlist'] = $this->common->taxlist($this->config->item('tax'));
        $this->load->model('customers_model', 'customers');
        $this->load->model('plugins_model', 'plugins');
        $data['exchange'] = $this->plugins->universal_api(5);
        $data['currency'] = $this->stockreturn->currencies();
        $data['customergrouplist'] = $this->customers->group_list();
        $data['lastinvoice'] = $this->stockreturn->lastpurchase();
        $data['terms'] = $this->stockreturn->billingterms();
        $head['title'] = "New Credit Note";
        $head['usernm'] = $this->aauth->get_user()->username;
        $data['warehouse'] = $this->stockreturn->warehouses();
        $data['taxdetails'] = $this->common->taxdetail();
        $this->load->view('fixed/header', $head);
        $this->load->view('stockreturn/note_newinvoice', $data);
        $this->load->view('fixed/footer');
    }

    //edit invoice
    public function edit()
    {
        if (!$this->aauth->permission_new(null, 'stockReturnAccess')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $tid = intval($this->input->get('id'));
        $data['id'] = $tid;
        $this->load->model('customers_model', 'customers');
        $data['customergrouplist'] = $this->customers->group_list();
        $data['terms'] = $this->stockreturn->billingterms();
        $data['invoice'] = $this->stockreturn->purchase_details($tid);
        $data['products'] = $this->stockreturn->purchase_products($tid);
        $head['title'] = "Stock return Order " . $data['invoice']['iid'];
        $head['usernm'] = $this->aauth->get_user()->username;
        $data['warehouse'] = $this->stockreturn->warehouses();
        $this->load->library("Common");
        $data['taxlist'] = $this->common->taxlist_edit($data['invoice']['taxstatus']);
        $this->load->view('fixed/header', $head);
        $this->load->view('stockreturn/edit', $data);
        $this->load->view('fixed/footer');
    }

    public function edit_c()
    {
        if (!$this->aauth->permission_new(null, 'stockReturnAccess')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $tid = intval($this->input->get('id'));
        $data['id'] = $tid;
        $this->load->model('customers_model', 'customers');
        $data['customergrouplist'] = $this->customers->group_list();
        $data['terms'] = $this->stockreturn->billingterms();
        $data['invoice'] = $this->stockreturn->purchase_details($tid);
        $data['products'] = $this->stockreturn->purchase_products($tid);;
        $head['title'] = "Stock return Order " . $data['invoice']['iid'];
        $head['usernm'] = $this->aauth->get_user()->username;
        $data['warehouse'] = $this->stockreturn->warehouses();
        $this->load->library("Common");
        $data['taxlist'] = $this->common->taxlist_edit($data['invoice']['taxstatus']);
        $this->load->view('fixed/header', $head);
        $this->load->view('stockreturn/c_edit', $data);
        $this->load->view('fixed/footer');
    }

    public function edit_note()
    {
        if (!$this->aauth->permission_new(null, 'stockReturnAccess')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $tid = intval($this->input->get('id'));
        $data['id'] = $tid;
        $this->load->model('customers_model', 'customers');
        $data['customergrouplist'] = $this->customers->group_list();
        $data['terms'] = $this->stockreturn->billingterms();
        $data['invoice'] = $this->stockreturn->purchase_details($tid);
        $data['products'] = $this->stockreturn->purchase_products($tid);;
        $head['title'] = "Credit Note " . $data['invoice']['iid'];
        $head['usernm'] = $this->aauth->get_user()->username;
        $data['warehouse'] = $this->stockreturn->warehouses();
        $this->load->library("Common");
        $data['taxlist'] = $this->common->taxlist_edit($data['invoice']['taxstatus']);
        $this->load->view('fixed/header', $head);
        $this->load->view('stockreturn/note_edit', $data);
        $this->load->view('fixed/footer');
    }

    //invoices list
    public function index()
    {
        if (!$this->aauth->permission_new(null, 'stockSuppliersReturns')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $head['title'] = "Manage Stock Return Orders";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $this->load->view('stockreturn/invoices');
        $this->load->view('fixed/footer');
    }

    public function customer()
    {
        if (!$this->aauth->permission_new(null, 'stockCustomerViewReturns')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $head['title'] = "Manage Stockreturn Orders";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $this->load->view('stockreturn/invoices_client');
        $this->load->view('fixed/footer');
    }

    public function creditnotes()
    {
        if (!$this->aauth->permission_new(null, 'stockCustomersReturns')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $head['title'] = "Manage Credit Notes";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $this->load->view('stockreturn/creditnotes_client');
        $this->load->view('fixed/footer');
    }
    public function creditnotes_po()
    {
        if (!$this->aauth->permission_new(null, 'stockSuppliersReturns')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $head['title'] = "Manage Credit Notes";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $this->load->view('stockreturn/creditnotes_supplier');
        $this->load->view('fixed/footer');
    }

    public function stock_report()
    {

        $head['title'] = "Stock Report";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $this->load->view('stockreturn/stock_return_report');
        $this->load->view('fixed/footer');
    }


    public function action2()
    {
        // var_dump($this->input->post()); die();
        $currency = $this->input->post('mcurrency');
        $customer_id = $this->input->post('customer_id');
        $person_type = $this->input->post('person_type');

        $customer_inv_to_credit = $this->input->post('customer_inv_to_credit');

        // $this->db->select('id');
        // $this->db->from('geopos_credit_notes');
        // $this->db->where('inv_id', $customer_inv_to_credit);
        // $this->db->where('csd', $customer_id);
        // $query = $this->db->get();
        // $pr_c = $query->row_array();
        // $inv_id = $pr_c['id'];
        // if($inv_id){
        //      echo json_encode(array('status' => 'Error', 'message' =>
        //         "You can not create multiple Credit Notes against an invoice!"));
        //     exit;

        // }



        $inv_blnc_can_credit = $this->input->post('inv_blnc_can_credit');


        $new_u = 'create';
        if ($person_type) {
            $new_u = 'create_client';
            if (!$this->aauth->permission_new(null, 'stockReturnAccess')) {
                exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
            }
        }
        if ($person_type == 2) {
            $new_u = 'create_note';
            if (!$this->aauth->permission_new(null, 'salesCreditNote')) {
                exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
            }
        }
        $invocieno = $this->input->post('invocieno');
        $invoicedate = $this->input->post('invoicedate');
        $invocieduedate = $this->input->post('invocieduedate');
        $notes = $this->input->post('notes', true);
        $tax = $this->input->post('tax_handle');
        $total = rev_amountExchange_s($this->input->post('total'), $currency, $this->aauth->get_user()->loc);

        $ship_taxtype = $this->input->post('ship_taxtype');
        $total_tax = 0;
        $total_discount = 0;
        $discountFormat = $this->input->post('discountFormat');
        $pterms = $this->input->post('pterms');
        $i = 0;
        if ($discountFormat == '0') {
            $discstatus = 0;
        } else {
            $discstatus = 1;
        }
        $subtotal = rev_amountExchange_s($this->input->post('subtotal'), $currency, $this->aauth->get_user()->loc);
        $shipping = rev_amountExchange_s($this->input->post('shipping'), $currency, $this->aauth->get_user()->loc);
        $shipping_tax = rev_amountExchange_s($this->input->post('ship_tax'), $currency, $this->aauth->get_user()->loc);
        if ($ship_taxtype == 'incl') $shipping = $shipping - $shipping_tax;
        $refer = $this->input->post('refer', true);
        $total = rev_amountExchange_s($this->input->post('total'), $currency, $this->aauth->get_user()->loc);
        if ($customer_id == 0) {
            echo json_encode(array('status' => 'Error', 'message' =>
            "Please add a new person or search from a previous added!"));
            exit;
        }

        if ($total > $inv_blnc_can_credit) {
            echo json_encode(array('status' => 'Error', 'message' =>
            "Can not credit this invoice as its amount is more than maximum credit amount"));
            exit;
        }
        $this->db->trans_start();
        //products
        $transok = true;
        //Invoice Data
        $bill_date = datefordatabase($invoicedate);
        $bill_due_date = datefordatabase($invocieduedate);
        if (!$currency) $currency = 0;
        $data = array(
            'tid' => $invocieno,
            'invoicedate' => $bill_date,
            'invoiceduedate' => $bill_due_date,
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'ship_tax' => $shipping_tax,
            'ship_tax_type' => $ship_taxtype,
            'total' => $total,
            'status' => 'due',
            'notes' => $notes,
            'csd' => $customer_id,
            'eid' => $this->aauth->get_user()->id,
            'taxstatus' => $tax,
            'discstatus' => $discstatus,
            'format_discount' => $discountFormat,
            'refer' => $refer,
            'term' => $pterms,
            'loc' => $this->aauth->get_user()->loc,
            'i_class' => 2,
            'multi' => $currency
        );

        $inv_no = $invocieno;
        $this->db->insert('geopos_credit_notes', $data);
        $invocie_credit_no = $this->db->insert_id();
        if ($invocie_credit_no) {

            // var_dump($invocie_credit_no );exit();

            //        $data = array('tid' => $inv_no, 'invoicedate' => $bill_date, 'invoiceduedate' => $bill_due_date, 'subtotal' => 0, 'shipping' => $shipping, 'ship_tax' => $shipping_tax, 'ship_tax_type' =>                                            $ship_taxtype, 'total' => 0,'status'=>'paid', 'notes' => $notes, 'csd' => $customer_id, 'eid' => $this->aauth->get_user()->id, 'taxstatus' => $tax, 'discstatus' => $discstatus, 'format_discount' =>$discountFormat, 'refer' => $refer,'term' => $pterms, 'loc' => $this->aauth->get_user()->loc, 'i_class' => 2,'r_time'=>'mig', 'multi' => $currency);

            //   $this->db->insert('geopos_invoices', $data);

            $this->db->select('total,pamnt');
            $this->db->from('geopos_invoices');
            $this->db->where('id', $customer_inv_to_credit);
            $query = $this->db->get();
            $customer_invoice_balance_form_query = $query->row_array();
            $inv_total_amount = floatval($customer_invoice_balance_form_query['total']);
            $inv_pmnt_amount = floatval($customer_invoice_balance_form_query['pamnt']);
            $this->db->select('*');
            $this->db->from('geopos_customers');
            $this->db->where('id', $customer_id);
            $query_c = $this->db->get();
            $customer = $query_c->row_array();
            $cust_name = $customer['company'];
            $cust_address = $customer['address'];
            $cust_postcode = $customer['postbox'];
            $cust_city = $customer['city'];

            $data_invoice = array(
                'created_by' => $this->aauth->get_user()->id,
                'tid' => $invocieno,
                'inv_type' => 'CREDIT',
                'invoicedate' => $bill_date,
                'invoiceduedate' => $bill_due_date,
                'subtotal' => -$subtotal,
                'shipping' => $shipping,
                'ship_tax' => $shipping_tax,
                'ship_tax_type' => $ship_taxtype,
                'discount_rate' => '',
                'total' => -$total,
                'pamnt' => 0.00,
                'notes' => $notes,
                'csd' => $customer_id,
                'eid' => $this->aauth->get_user()->id,
                'taxstatus' => $tax,
                'discstatus' => $discstatus,
                'format_discount' => $discountFormat,
                'refer' => $refer,
                'term' => $pterms,
                'multi' => $currency,
                'loc' => $this->aauth->get_user()->loc,
                'cust_name' => $cust_name,
                'cust_address' => $cust_address,
                'cust_postcode' => $cust_postcode,
                'cust_city' => $cust_city,
                'driver_name' => '',
                'vehicle_no' => '',
                'pamt_terms' => ''
            );

            $this->db->insert('geopos_invoices', $data_invoice);
            $last_inv_id = $this->db->insert_id();


            // $this->db->set('pamnt', "pamnt+$total", FALSE);
            // $this->db->where('id', $customer_inv_to_credit);
            // $this->db->update('geopos_invoices');


            // $this->db->select('total,pamnt');
            // $this->db->from('geopos_invoices');
            // $this->db->where('id', $customer_inv_to_credit);
            // $query = $this->db->get();
            // $customer_invoice_balance_form_query = $query->row_array();
            // $inv_total_amount = floatval($customer_invoice_balance_form_query['total']);
            // $inv_pmnt_amount = floatval($customer_invoice_balance_form_query['pamnt']);

            // if ($inv_total_amount >  $inv_pmnt_amount &&  $inv_pmnt_amount > 0) {
            //     $this->db->set('status', "partial");
            //     $this->db->where('id', $customer_inv_to_credit);
            //     $this->db->update('geopos_invoices');
            // }

            // if ($inv_total_amount ==  $inv_pmnt_amount) {
            //     $this->db->set('status', "paid");
            //     $this->db->where('id', $customer_inv_to_credit);
            //     $this->db->update('geopos_invoices');
            // }




            $last_inv_id = $invocie_credit_no;

            $this->db->set('credit_amount', "credit_amount-$total", FALSE);
            $this->db->set('credit_used', 0);
            $this->db->where('id', $customer_id);
            $this->db->update('geopos_customers');

            $this->aauth->applog("[New Credit Note Created - $cust_name] - Amount: $total, ", $this->aauth->get_user()->username);

            $this->db->select('paymt_method', 'inv_id');
            $this->db->from('geopos_transactions');
            $this->db->where('payerid', $customer_id);
            $this->db->where('tid', $customer_inv_to_credit);
            $query = $this->db->get();
            $old_inv_form_query = $query->row_array();
            $p_m = $old_inv_form_query['paymt_method'] ?? ''; // Handle missing data gracefully
            $data_trans = array(
                'acid' => '1',
                'account' => 'Sales Account',
                'type' => 'Income',
                'cat' => 'Sales',
                'debit' =>  $total,
                'payer' => $cust_name,
                'payerid' => $customer_id,
                'method' => '',
                'paymt_method' => $p_m,
                'balance' => 0,
                'date' => $bill_date,
                'eid' => $this->aauth->get_user()->id,
                'tid' => $last_inv_id,
                'inv_id' => $inv_no,
                'note' => 'Credit Note # ' . $invocieno,
                'loc' => $this->aauth->get_user()->loc
            );
            $this->db->insert('geopos_transactions', $data_trans);

            $last_trans_id = $this->db->insert_id();

            $data_credit = array(
                'trans_id' => $last_trans_id,
                'inv_id' => $last_inv_id
            );
            $this->db->where('id', $invocie_credit_no);
            $this->db->update('geopos_credit_notes', $data_credit);

            // var_dump($this->db->last_query());
            $query = $this->db->get_where('geopos_invoices', array('id' => $last_inv_id));
            $result = $query->row_array();

            if ($result['amount_credited'] === NULL || $result['amount_credited'] === '0.00') {

                // if ($result['status'] == "paid") {
                //     $status = "partial";
                // } else {

                //     $status = $result['status'];
                // }

                $data = array(
                    'amount_credited' => $total,
                    'status' => "paid"
                );
                $this->db->where('id', $customer_inv_to_credit);
                $this->db->update('geopos_invoices', $data);
                //    var_dump($this->db->last_query());

                // } else {
                // $new_value = $result['amount_credited'] + $total;
                // if ($result['status'] == "paid") {
                //     $status = "partial";
                // } else {

                //     $status = $result['status'];
                // }
                // $data = array(
                //     'amount_credited' => $new_value,
                //     'status' => $status
                // );
                // $this->db->where('id', $customer_inv_to_credit);
                // $this->db->update('geopos_invoices', $data);
                // var_dump($this->db->last_query());


            }



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
                    $total_tax += numberClean($ptotal_tax[$key]);

                    $this->db->select('*');
                    $this->db->from('geopos_products');
                    $this->db->where('pid', $product_id[$key]);
                    $query = $this->db->get();
                    $pr = $query->row_array();

                    $cat_id = $pr['pcat'];

                    $data = array(
                        'tid' => $invocie_credit_no,
                        'pid' => $product_id[$key],
                        'product' => $product_name1[$key],
                        'code' => $product_hsn[$key],
                        'qty' => numberClean($product_qty[$key]),
                        'price' => rev_amountExchange_s($product_price[$key], $currency, $this->aauth->get_user()->loc),
                        'tax' => numberClean($product_tax[$key]),
                        'discount' => numberClean($product_discount[$key]),
                        'subtotal' => rev_amountExchange_s($product_subtotal[$key], $currency, $this->aauth->get_user()->loc),
                        'totaltax' => rev_amountExchange_s($ptotal_tax[$key], $currency, $this->aauth->get_user()->loc),
                        'totaldiscount' => rev_amountExchange_s($ptotal_disc[$key], $currency, $this->aauth->get_user()->loc),
                        'product_des' => $product_des[$key],
                        'unit' => $product_unit[$key]
                    );

                    $productlist[$prodindex] = $data;
                    $i++;
                    $prodindex++;
                    $amt = numberClean($product_qty[$key]);
                    if ($product_id[$key] > 0) {
                        $this->db->set('qty', "qty+$amt", FALSE);
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

                $this->db->insert_batch('geopos_creditnote_items', $productlist);

                $this->db->set(array('discount' => rev_amountExchange_s(amountFormat_general($total_discount), $currency, $this->aauth->get_user()->loc), 'tax' => rev_amountExchange_s(amountFormat_general($total_tax), $currency, $this->aauth->get_user()->loc), 'items' => $itc));
                $this->db->where('id', $invocie_credit_no);
                $this->db->update('geopos_credit_notes');

                $this->db->select('total,pamnt');
                $this->db->from('geopos_invoices');
                $this->db->where('id', $customer_inv_to_credit);
                $query = $this->db->get();
                $customer_invoice_balance_form_query = $query->row_array();
                $inv_total_amount = floatval($customer_invoice_balance_form_query['total']);
                $inv_pmnt_amount = floatval($customer_invoice_balance_form_query['pamnt']);

                if ($inv_total_amount >  $inv_pmnt_amount &&  $inv_pmnt_amount > 0) {
                    $this->db->set('status', "partial");
                    $this->db->where('id', $customer_inv_to_credit);
                    $this->db->update('geopos_invoices');
                }

                if ($inv_total_amount ==  $inv_pmnt_amount) {
                    $this->db->set('status', "paid");
                    $this->db->where('id', $customer_inv_to_credit);
                    $this->db->update('geopos_invoices');
                }
            } else {
                echo json_encode(array('status' => 'Error', 'message' =>
                "Please choose product from product list. Go to Item manager section if you have not added the products."));
                $transok = false;
            }

            echo json_encode(array('status' => 'Success', 'message' =>
            $this->lang->line('ADDED') . " <a href='" . base_url('stockreturn/credit_note?id=' . $invocie_credit_no) . "' class='btn btn-info btn-lg'><span class='fa fa-eye' aria-hidden='true'></span> " . $this->lang->line('View') . " </a>   <a href='" . base_url('stockreturn/print_creditnote_invoice?id=' . $invocie_credit_no) . "' class='btn btn-pink btn-lg'><span class='fa fa-plus-circle' aria-hidden='true'></span>  </a>"));
        } else {
            echo json_encode(array('status' => 'Error', 'message' => $this->lang->line('ERROR')));
            $transok = false;
        }


        if ($transok) {
            $this->db->trans_complete();

            // redirect('stockreturn/creditnotes',refresh);
        } else {
            $this->db->trans_rollback();
        }
    }

    public function action2_po()
    {
        // var_dump($this->input->post()); die();
        $currency = $this->input->post('mcurrency');
        $customer_id = $this->input->post('customer_id');
        $person_type = $this->input->post('person_type');

        $customer_inv_to_credit = $this->input->post('customer_inv_to_credit');

        // $this->db->select('id');
        // $this->db->from('geopos_credit_notes');
        // $this->db->where('inv_id', $customer_inv_to_credit);
        // $this->db->where('csd', $customer_id);
        // $query = $this->db->get();
        // $pr_c = $query->row_array();
        // $inv_id = $pr_c['id'];
        // if($inv_id){
        //      echo json_encode(array('status' => 'Error', 'message' =>
        //         "You can not create multiple Credit Notes against an invoice!"));
        //     exit;

        // }



        $inv_blnc_can_credit = $this->input->post('inv_blnc_can_credit');


        $new_u = 'create';
        if ($person_type) {
            $new_u = 'create_client';
            if (!$this->aauth->permission_new(null, 'stockReturnAccess')) {
                exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
            }
        }
        if ($person_type == 2) {
            $new_u = 'create_note';
            if (!$this->aauth->permission_new(null, 'salesCreditNote')) {
                exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
            }
        }
        $invocieno = $this->input->post('invocieno');
        $invoicedate = $this->input->post('invoicedate');
        $invocieduedate = $this->input->post('invocieduedate');
        $notes = $this->input->post('notes', true);
        $tax = $this->input->post('tax_handle');
        $total = rev_amountExchange_s($this->input->post('total'), $currency, $this->aauth->get_user()->loc);

        $ship_taxtype = $this->input->post('ship_taxtype');
        $total_tax = 0;
        $total_discount = 0;
        $discountFormat = $this->input->post('discountFormat');
        $pterms = $this->input->post('pterms');
        $i = 0;
        if ($discountFormat == '0') {
            $discstatus = 0;
        } else {
            $discstatus = 1;
        }
        $subtotal = rev_amountExchange_s($this->input->post('subtotal'), $currency, $this->aauth->get_user()->loc);
        $shipping = rev_amountExchange_s($this->input->post('shipping'), $currency, $this->aauth->get_user()->loc);
        $shipping_tax = rev_amountExchange_s($this->input->post('ship_tax'), $currency, $this->aauth->get_user()->loc);
        if ($ship_taxtype == 'incl') $shipping = $shipping - $shipping_tax;
        $refer = $this->input->post('refer', true);
        $total = rev_amountExchange_s($this->input->post('total'), $currency, $this->aauth->get_user()->loc);
        if ($customer_id == 0) {
            echo json_encode(array('status' => 'Error', 'message' =>
            "Please add a new person or search from a previous added!"));
            exit;
        }

        if ($total > $inv_blnc_can_credit) {
            echo json_encode(array('status' => 'Error', 'message' =>
            "Can not credit this invoice as its amount is more than maximum credit amount"));
            exit;
        }
        $this->db->trans_start();
        //products
        $transok = true;
        //Invoice Data
        $bill_date = datefordatabase($invoicedate);
        $bill_due_date = datefordatabase($invocieduedate);
        if (!$currency) $currency = 0;

        $data = array(
            'tid' => $customer_inv_to_credit,
            'invoicedate' => $bill_date,
            'invoiceduedate' => $bill_due_date,
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'ship_tax' => $shipping_tax,
            'ship_tax_type' => $ship_taxtype,
            'total' => $total,
            'status' => 'due',
            'notes' => $notes,
            'csd' => $customer_id,
            'eid' => $this->aauth->get_user()->id,
            'taxstatus' => $tax,
            'discstatus' => $discstatus,
            'format_discount' => $discountFormat,
            'refer' => $refer,
            'term' => $pterms,
            'loc' => $this->aauth->get_user()->loc,
            'i_class' => 2,
            'multi' => $currency
        );

        $inv_no = $invocieno;
        $this->db->insert('geopos_credit_notes_po', $data);
        $invocie_credit_no = $this->db->insert_id();
        if ($invocie_credit_no) {

            // var_dump($invocie_credit_no );exit();

            //        $data = array('tid' => $inv_no, 'invoicedate' => $bill_date, 'invoiceduedate' => $bill_due_date, 'subtotal' => 0, 'shipping' => $shipping, 'ship_tax' => $shipping_tax, 'ship_tax_type' =>                                            $ship_taxtype, 'total' => 0,'status'=>'paid', 'notes' => $notes, 'csd' => $customer_id, 'eid' => $this->aauth->get_user()->id, 'taxstatus' => $tax, 'discstatus' => $discstatus, 'format_discount' =>$discountFormat, 'refer' => $refer,'term' => $pterms, 'loc' => $this->aauth->get_user()->loc, 'i_class' => 2,'r_time'=>'mig', 'multi' => $currency);

            //   $this->db->insert('geopos_invoices', $data);

            $this->db->select('total,pamnt,invoice_ref,inv_id');
            $this->db->from('geopos_purchase');
            $this->db->where('id', $customer_inv_to_credit);
            $query = $this->db->get();
            $customer_invoice_balance_form_query = $query->row_array();
            $inv_total_amount = floatval($customer_invoice_balance_form_query['total']);
            $inv_pmnt_amount = floatval($customer_invoice_balance_form_query['pamnt']);
            $invoice_ref = $customer_invoice_balance_form_query['invoice_ref'];
            $unique_invoice_ref = $customer_invoice_balance_form_query['inv_id'];
            $this->db->select_max('tid');
            $query = $this->db->get('geopos_purchase');
            $result = $query->row_array();
            $last_tid = isset($result['tid']) ? intval($result['tid']) : 0;

            // Step 2: Increment the `tid` by 1
            $new_tid = $last_tid + 1;
            $this->db->select('*');
            $this->db->from('geopos_supplier');
            $this->db->where('id', $customer_id);
            $query_c = $this->db->get();
            $customer = $query_c->row_array();
            $cust_name = $customer['name'];
            $cust_address = $customer['address'];
            $cust_postcode = $customer['postbox'];
            $cust_city = $customer['city'];

            $data_invoice = array(
                'status' => 'paid',
                'tid' => $new_tid,
                'invoice_ref' => $invoice_ref,
                'inv_id' => $unique_invoice_ref,
                'invoicedate' => $bill_date,
                'invoiceduedate' => $bill_due_date,
                'subtotal' => $subtotal,
                'shipping' => $shipping,
                'ship_tax' => $shipping_tax,
                'ship_tax_type' => $ship_taxtype,
                'pamnt' => $total,
                'notes' => $notes,
                'csd' => $customer_id,
                'eid' => $this->aauth->get_user()->id,
                'taxstatus' => $tax,
                'discstatus' => $discstatus,
                'format_discount' => $discountFormat,
                'refer' => $refer,
                'term' => $pterms,
                'loc' => $this->aauth->get_user()->loc,
                'multi' => $currency,
                'verified' => 'Unverified'
            );

            $this->db->insert('geopos_purchase', $data_invoice);
            $last_inv_id = $this->db->insert_id();


            // $this->db->set('pamnt', "pamnt+$total", FALSE);
            // $this->db->where('id', $customer_inv_to_credit);
            // $this->db->update('geopos_invoices');


            // $this->db->select('total,pamnt');
            // $this->db->from('geopos_invoices');
            // $this->db->where('id', $customer_inv_to_credit);
            // $query = $this->db->get();
            // $customer_invoice_balance_form_query = $query->row_array();
            // $inv_total_amount = floatval($customer_invoice_balance_form_query['total']);
            // $inv_pmnt_amount = floatval($customer_invoice_balance_form_query['pamnt']);

            // if ($inv_total_amount >  $inv_pmnt_amount &&  $inv_pmnt_amount > 0) {
            //     $this->db->set('status', "partial");
            //     $this->db->where('id', $customer_inv_to_credit);
            //     $this->db->update('geopos_invoices');
            // }

            // if ($inv_total_amount ==  $inv_pmnt_amount) {
            //     $this->db->set('status', "paid");
            //     $this->db->where('id', $customer_inv_to_credit);
            //     $this->db->update('geopos_invoices');
            // }




            $last_inv_id = $invocie_credit_no;

            // $this->db->set('credit_amount', "credit_amount-$total", FALSE);
            // $this->db->set('credit_used', 0);
            // $this->db->where('id', $customer_id);
            // $this->db->update('geopos_customers');

            $this->aauth->applog("[New Credit Note Created - $cust_name] - Amount: $total, ", $this->aauth->get_user()->username);



            $data_trans = array(
                'acid' => '1',
                'account' => 'Purchase Account',
                'type' => 'Income',
                'cat' => 'Purchase',
                'debit' =>  $total,
                'payer' => $cust_name,
                'payerid' => $customer_id,
                'method' => '',
                'balance' => 0,
                'ext' => 1,
                'date' => $bill_date,
                'eid' => $this->aauth->get_user()->id,
                'tid' => $customer_inv_to_credit,
                'inv_id' => $invoice_ref,
                'note' => 'Credit Note # ' . $customer_inv_to_credit,
                'loc' => $this->aauth->get_user()->loc
            );
            $this->db->insert('geopos_transactions', $data_trans);

            $last_trans_id = $this->db->insert_id();

            $data_credit = array(
                'trans_id' => $last_trans_id,
                'inv_id' => $last_inv_id
            );
            $this->db->where('id', $invocie_credit_no);
            $this->db->update('geopos_credit_notes_po', $data_credit);

            // var_dump($this->db->last_query());
            // $query = $this->db->get_where('geopos_purchase', array('id' => $last_inv_id));
            // $result = $query->row_array();

            // if ($result['amount_credited'] === NULL || $result['amount_credited'] === '0.00') {

            // if ($result['status'] == "paid") {
            //     $status = "partial";
            // } else {

            //     $status = $result['status'];
            // }

            // $data = array(
            //     'amount_credited' => $total,
            //     'status' => "paid"
            // );
            // $this->db->where('id', $customer_inv_to_credit);
            // $this->db->update('geopos_purchase', $data);
            //    var_dump($this->db->last_query());

            // } else {
            // $new_value = $result['amount_credited'] + $total;
            // if ($result['status'] == "paid") {
            //     $status = "partial";
            // } else {

            //     $status = $result['status'];
            // }
            // $data = array(
            //     'amount_credited' => $new_value,
            //     'status' => $status
            // );
            // $this->db->where('id', $customer_inv_to_credit);
            // $this->db->update('geopos_invoices', $data);
            // var_dump($this->db->last_query());


            // }



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
                    $total_tax += numberClean($ptotal_tax[$key]);

                    $this->db->select('*');
                    $this->db->from('geopos_products');
                    $this->db->where('pid', $product_id[$key]);
                    $query = $this->db->get();
                    $pr = $query->row_array();

                    $cat_id = $pr['pcat'];

                    $data = array(
                        'tid' => $customer_inv_to_credit,
                        'pid' => $product_id[$key],
                        'product' => $product_name1[$key],
                        'code' => $product_hsn[$key],
                        'qty' => numberClean($product_qty[$key]),
                        'price' => rev_amountExchange_s($product_price[$key], $currency, $this->aauth->get_user()->loc),
                        'tax' => numberClean($product_tax[$key]),
                        'discount' => numberClean($product_discount[$key]),
                        'subtotal' => rev_amountExchange_s($product_subtotal[$key], $currency, $this->aauth->get_user()->loc),
                        'totaltax' => rev_amountExchange_s($ptotal_tax[$key], $currency, $this->aauth->get_user()->loc),
                        'totaldiscount' => rev_amountExchange_s($ptotal_disc[$key], $currency, $this->aauth->get_user()->loc),
                        'product_des' => $product_des[$key],
                        'unit' => $product_unit[$key]
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

                $this->db->insert_batch('geopos_creditnote_po_items', $productlist);

                $this->db->set(array('discount' => rev_amountExchange_s(amountFormat_general($total_discount), $currency, $this->aauth->get_user()->loc), 'tax' => rev_amountExchange_s(amountFormat_general($total_tax), $currency, $this->aauth->get_user()->loc), 'items' => $itc));
                $this->db->where('id', $invocie_credit_no);
                $this->db->update('geopos_credit_notes_po');

                // $this->db->select('total,pamnt');
                // $this->db->from('geopos_invoices');
                // $this->db->where('id', $customer_inv_to_credit);
                // $query = $this->db->get();
                // $customer_invoice_balance_form_query = $query->row_array();
                // $inv_total_amount = floatval($customer_invoice_balance_form_query['total']);
                // $inv_pmnt_amount = floatval($customer_invoice_balance_form_query['pamnt']);

                // if ($inv_total_amount >  $inv_pmnt_amount &&  $inv_pmnt_amount > 0) {
                //     $this->db->set('status', "partial");
                //     $this->db->where('id', $customer_inv_to_credit);
                //     $this->db->update('geopos_invoices');
                // }

                // if ($inv_total_amount ==  $inv_pmnt_amount) {
                //     $this->db->set('status', "paid");
                //     $this->db->where('id', $customer_inv_to_credit);
                //     $this->db->update('geopos_invoices');
                // }
            } else {
                echo json_encode(array('status' => 'Error', 'message' =>
                "Please choose product from product list. Go to Item manager section if you have not added the products."));
                $transok = false;
            }

            echo json_encode(array('status' => 'Success', 'message' =>
            $this->lang->line('ADDED') . " <a href='" . base_url('stockreturn/credit_note_po?id=' . $invocie_credit_no) . "' class='btn btn-info btn-lg'><span class='fa fa-eye' aria-hidden='true'></span> " . $this->lang->line('View') . " </a>   <a href='" . base_url('stockreturn/print_creditnote_invoice_po?id=' . $invocie_credit_no) . "' class='btn btn-pink btn-lg'><span class='fa fa-plus-circle' aria-hidden='true'></span>  </a>"));
        } else {
            echo json_encode(array('status' => 'Error', 'message' => $this->lang->line('ERROR')));
            $transok = false;
        }


        if ($transok) {
            $this->db->trans_complete();

            // redirect('stockreturn/creditnotes',refresh);
        } else {
            $this->db->trans_rollback();
        }
    }









    //action
    public function action()
    {


        $total_s_r_tax = 0;
        $currency = $this->input->post('mcurrency');
        $customer_id = $this->input->post('customer_id');
        $person_type = $this->input->post('person_type');
        $new_u = 'create';
        if ($person_type) {
            $new_u = 'create_client';
            if (!$this->aauth->permission_new(null, 'stockReturnAccess')) {
                exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
            }
        }
        if ($person_type == 2) {
            $new_u = 'create_note';
            if (!$this->aauth->permission_new(null, 'salesCreditNote')) {
                exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
            }
        }
        $invocieno = $this->input->post('invocieno');
        $invoicedate = $this->input->post('invoicedate');
        $invocieduedate = $this->input->post('invocieduedate');
        $notes = $this->input->post('notes', true);
        $tax = $this->input->post('tax_handle');
        $ship_taxtype = $this->input->post('ship_taxtype');
        $total_tax = 0;
        $total_discount = 0;
        $discountFormat = $this->input->post('discountFormat');
        $pterms = $this->input->post('pterms');
        $i = 0;
        if ($discountFormat == '0') {
            $discstatus = 0;
        } else {
            $discstatus = 1;
        }
        $subtotal = rev_amountExchange_s($this->input->post('subtotal'), $currency, $this->aauth->get_user()->loc);
        $shipping = rev_amountExchange_s($this->input->post('shipping'), $currency, $this->aauth->get_user()->loc);
        $shipping_tax = rev_amountExchange_s($this->input->post('ship_tax'), $currency, $this->aauth->get_user()->loc);
        if ($ship_taxtype == 'incl') $shipping = $shipping - $shipping_tax;
        $refer = $this->input->post('refer', true);
        $total = rev_amountExchange_s($this->input->post('total'), $currency, $this->aauth->get_user()->loc);
        if ($customer_id == 0) {
            echo json_encode(array('status' => 'Error', 'message' =>
            "Please add a new person or search from a previous added!"));
            exit;
        }
        $this->db->trans_start();
        //products
        $transok = true;
        //Invoice Data
        $bill_date = datefordatabase($invoicedate);
        $bill_due_date = datefordatabase($invocieduedate);
        if (!$currency) $currency = 0;
        $data = array('tid' => $invocieno, 'invoicedate' => $bill_date, 'invoiceduedate' => $bill_due_date, 'subtotal' => $subtotal, 'shipping' => $shipping, 'ship_tax' => $shipping_tax, 'ship_tax_type' => $ship_taxtype, 'total' => $total, 'notes' => $notes, 'status' => 'accepted', 'csd' => $customer_id, 'eid' => $this->aauth->get_user()->id, 'taxstatus' => $tax, 'discstatus' => $discstatus, 'format_discount' => $discountFormat, 'refer' => $refer, 'term' => $pterms, 'loc' => $this->aauth->get_user()->loc, 'i_class' => $person_type, 'multi' => $currency,);
        $return_products = "";
        if ($this->db->insert('geopos_stock_r', $data)) {
            $invocieno = $this->db->insert_id();
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
            $total_tax = 0;
            $product_des = $this->input->post('product_description', true);
            $product_unit = $this->input->post('unit');
            $product_hsn = $this->input->post('hsn');
            $product_ids = array();
            foreach ($pid as $key => $value) {
                $total_discount += numberClean(@$ptotal_disc[$key]);
                $total_tax += numberClean($ptotal_tax[$key]);
                $data = array(
                    'tid' => $invocieno,
                    'pid' => $product_id[$key],
                    'product' => $product_name1[$key],
                    'code' => $product_hsn[$key],
                    'qty' => numberClean($product_qty[$key]),
                    'price' => rev_amountExchange_s($product_price[$key], $currency, $this->aauth->get_user()->loc),
                    'tax' => numberClean($product_tax[$key]),
                    'discount' => numberClean($product_discount[$key]),
                    'subtotal' => rev_amountExchange_s($product_subtotal[$key], $currency, $this->aauth->get_user()->loc),
                    'totaltax' => rev_amountExchange_s($ptotal_tax[$key], $currency, $this->aauth->get_user()->loc),
                    'totaldiscount' => rev_amountExchange_s($ptotal_disc[$key], $currency, $this->aauth->get_user()->loc),
                    'product_des' => $product_des[$key],
                    'unit' => $product_unit[$key]
                );
                $return_products .= "{ Product: " . $product_name1[$key] . ", Quanity: " . numberClean($product_qty[$key]) . "} ";
                $productlist[$prodindex] = $data;
                $i++;
                $prodindex++;
                $amt = numberClean($product_qty[$key]);
                if ($product_id[$key] > 0) {
                    if ($this->input->post('update_stock') == 'yes') {

                        if ($person_type) {
                            $this->db->set('qty', "qty+$amt", FALSE);
                        } else {
                            $this->db->set('qty', "qty-$amt", FALSE);
                        }

                        $this->db->where('pid', $product_id[$key]);
                        $this->db->update('geopos_products');
                    }
                    $itc += $amt;
                }
                $price = rev_amountExchange_s($product_price[$key], $currency, $this->aauth->get_user()->loc);
                $query = $this->db->query("SELECT id, subtotal FROM `geopos_invoices` WHERE csd=" . $customer_id . " and status='due' ORDER BY `id` DESC ");
                $result = $query->result_array();
                $tax = 0;
                foreach ($result as $row3) {

                    $query =  $this->db->query("SELECT id, qty, tax, price FROM `geopos_invoice_items` WHERE tid=" . $row3['id'] . " AND pid=" . $product_id[$key] . " AND   cid=" . $customer_id . " ");
                    $row = $query->result_array();
                    if ($row) {
                        if ($row['0']['qty'] >= $amt) {
                            if (!in_array($product_id[$key], $product_ids)) {
                                $this->db->set('qty', "qty-$amt", FALSE);

                                if ($row['0']['tax']) {
                                    $tax = $amt * $row['0']['tax'];
                                    $total_s_r_tax += $tax;
                                }

                                $price_now = $row['0']['price'] * $amt;

                                $inv_id = $row['0']['id'];

                                $this->db->set('subtotal', "subtotal-$price_now", FALSE);
                                $this->db->set('totaltax', "totaltax-$tax", FALSE);
                                $this->db->where('pid', $product_id[$key]);
                                $this->db->where('cid', $customer_id);
                                $this->db->where('tid', $row3['id']);
                                $this->db->update('geopos_invoice_items');
                                $updated_status = $this->db->affected_rows();
                                if ($updated_status) {
                                    $quantity = $this->db->get_where('geopos_invoice_items', array('id' => $inv_id))->row()->qty;
                                    if ($quantity == 0) {
                                        $this->db->delete('geopos_invoice_items', array('id' => $inv_id));
                                    }
                                }

                                $inv_total = $price_now + $tax;
                                $this->db->set('subtotal', "subtotal-$price_now", FALSE);
                                $this->db->set('total', "total-$inv_total", FALSE);
                                $this->db->set('tax', "tax-$tax", FALSE);
                                $this->db->where('id', $row3['id']);
                                $this->db->update('geopos_invoices');

                                foreach ($productlist as $tax) {
                                    if (in_array($productlist["pid"], $product_id[$key])) {
                                        $tax["tax"] = $row['0']['tax'];
                                        $tax["totaltax"] = $tax;
                                        $tax["subtotal"] = $price_now;
                                    }
                                }

                                $query =  $this->db->query("SELECT geopos_customers.name FROM `geopos_customers` WHERE id=" . $customer_id);
                                $row = $query->result_array();
                                $cname = $row['0']['name'];
                            }
                            array_push($product_ids, $product_id[$key]);
                        } else {
                            echo json_encode(array('status' => 'Error', 'message' =>
                            "Stock returned quantity should be less than or equal to stock issued quantity"));
                            exit;
                        }
                    }
                }
            }

            if ($prodindex > 0) {
                $this->db->insert_batch('geopos_stock_r_items', $productlist);
                $this->db->set(array('discount' => rev_amountExchange_s(amountFormat_general($total_discount), $currency, $this->aauth->get_user()->loc), 'tax' => rev_amountExchange_s(amountFormat_general($total_s_r_tax), $currency, $this->aauth->get_user()->loc), 'items' => $itc));
                $this->db->where('id', $invocieno);
                $this->db->update('geopos_stock_r');
            } else {
                echo json_encode(array('status' => 'Error', 'message' =>
                "Please choose product from product list. Go to Item manager section if you have not added the products."));
                $transok = false;
            }
            $data_trans = array(
                'acid' => '1',
                'account' => 'Sales Account',
                'type' => 'Expense',
                'cat' => 'Sales',
                'debit' =>  $total,
                'payer' => $cname,
                'payerid' => $customer_id,
                'method' => 'Stock Return',
                // 'balance' => $cbalance, 
                'date' => datefordatabase(date()),
                'eid' => $this->aauth->get_user()->id,
                'tid' => $invocieno,
                'inv_id' => $invocieno,
                'note' => 'Stock Return Invoice # ' . $invocieno,
                'loc' => $this->aauth->get_user()->loc
            );
            $this->db->insert('geopos_transactions', $data_trans);
            echo json_encode(array('status' => 'Success', 'message' =>
            $this->lang->line('ADDED') . " <a href='view?id=$invocieno' class='btn btn-info btn-lg'><span class='fa fa-eye' aria-hidden='true'></span> " . $this->lang->line('View') . " </a>   <a href='" . $new_u . "' class='btn btn-pink btn-lg'><span class='fa fa-plus-circle' aria-hidden='true'></span>  </a>"));
        } else {
            echo json_encode(array('status' => 'Error', 'message' => $this->lang->line('ERROR')));
            $transok = false;
        }


        if ($transok) {
            $this->db->trans_complete();
            $this->aauth->applog("[Stock Returned - Customer: " . $this->input->post('cst') . "] - Order Date: " . dateformat($invoices->invoicedate) . ", Products: [ " . $return_products . "]", $this->aauth->get_user()->username);
        } else {
            $this->db->trans_rollback();
        }
    }


    public function action_2()
    {

        $currency = $this->input->post('mcurrency');
        $customer_id = $this->input->post('customer_id');
        $person_type = $this->input->post('person_type');

        $customer_inv_to_credit = $this->input->post('customer_inv_to_credit');

        $this->db->select('id');
        $this->db->from('geopos_stock_r');
        $this->db->where('inv_id', $customer_inv_to_credit);
        $this->db->where('csd', $customer_id);
        $query = $this->db->get();
        $pr_c = $query->row_array();
        $inv_id = $pr_c['id'];
        // if($inv_id){
        //      echo json_encode(array('status' => 'Error', 'message' =>
        //         "You can not create multiple Credit Notes against an invoice!"));
        //     exit;

        // }



        $inv_blnc_can_credit = $this->input->post('inv_blnc_can_credit');


        $new_u = 'create';
        if ($person_type) {
            $new_u = 'create_client';
            if (!$this->aauth->permission_new(null, 'customerNewCustomer')) {
                exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
            }
        }
        if ($person_type == 2) {
            $new_u = 'create_note';
            if (!$this->aauth->permission_new(null, 'salesCreditNote')) {
                exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
            }
        }
        $invocieno = $this->input->post('invocieno');
        $invoicedate = $this->input->post('invoicedate');
        $invocieduedate = $this->input->post('invocieduedate');
        $notes = $this->input->post('notes', true);
        $tax = $this->input->post('tax_handle');
        $total = rev_amountExchange_s($this->input->post('total'), $currency, $this->aauth->get_user()->loc);

        $ship_taxtype = $this->input->post('ship_taxtype');
        $total_tax = 0;
        $total_discount = 0;
        $discountFormat = $this->input->post('discountFormat');
        $pterms = $this->input->post('pterms');
        $i = 0;
        if ($discountFormat == '0') {
            $discstatus = 0;
        } else {
            $discstatus = 1;
        }
        $subtotal = rev_amountExchange_s($this->input->post('subtotal'), $currency, $this->aauth->get_user()->loc);
        $shipping = rev_amountExchange_s($this->input->post('shipping'), $currency, $this->aauth->get_user()->loc);
        $shipping_tax = rev_amountExchange_s($this->input->post('ship_tax'), $currency, $this->aauth->get_user()->loc);
        if ($ship_taxtype == 'incl') $shipping = $shipping - $shipping_tax;
        $refer = $this->input->post('refer', true);
        $total = rev_amountExchange_s($this->input->post('total'), $currency, $this->aauth->get_user()->loc);
        if ($customer_id == 0) {
            echo json_encode(array('status' => 'Error', 'message' =>
            "Please add a new person or search from a previous added!"));
            exit;
        }

        if ($total > $inv_blnc_can_credit) {
            echo json_encode(array('status' => 'Error', 'message' =>
            "Can not credit this invoice as its amount is more than maximum credit amount"));
            exit;
        }
        $this->db->trans_start();
        //products
        $transok = true;
        //Invoice Data
        $bill_date = datefordatabase($invoicedate);
        $bill_due_date = datefordatabase($invocieduedate);
        if (!$currency) $currency = 0;
        $data = array('tid' => $invocieno, 'invoicedate' => $bill_date, 'invoiceduedate' => $bill_due_date, 'subtotal' => $subtotal, 'shipping' => $shipping, 'ship_tax' => $shipping_tax, 'ship_tax_type' => $ship_taxtype, 'total' =>               $total, 'status' => 'due', 'notes' => $notes, 'csd' => $customer_id, 'eid' => $this->aauth->get_user()->id, 'taxstatus' => $tax, 'discstatus' => $discstatus, 'format_discount' => $discountFormat, 'refer' => $refer, 'term' => $pterms,        'loc' => $this->aauth->get_user()->loc, 'i_class' => 2, 'multi' => $currency);

        $inv_no = $invocieno;
        $this->db->insert('geopos_stock_r', $data);

        $invocie_credit_no = $this->db->insert_id();
        if ($invocie_credit_no) {

            // var_dump($invocie_credit_no );exit();

            $data = array('tid' => $inv_no, 'invoicedate' => $bill_date, 'invoiceduedate' => $bill_due_date, 'subtotal' => -$subtotal, 'shipping' => $shipping, 'ship_tax' => $shipping_tax, 'ship_tax_type' =>                                            $ship_taxtype, 'total' => -$total, 'status' => 'due', 'notes' => $notes, 'csd' => $customer_id, 'eid' => $this->aauth->get_user()->id, 'taxstatus' => $tax, 'discstatus' => $discstatus, 'format_discount' => $discountFormat, 'refer' => $refer, 'term' => $pterms, 'loc' => $this->aauth->get_user()->loc, 'i_class' => 2, 'r_time' => 'mig', 'multi' => $currency);

            $this->db->insert('geopos_invoices', $data);

            $last_inv_id = $this->db->insert_id();

            $this->db->set('credit_amount', "credit_amount+$total", FALSE);
            $this->db->set('credit_used', 0);
            $this->db->where('id', $customer_id);
            $this->db->update('geopos_customers');

            $this->aauth->applog("[New Credit Note Created - $cname] - Amount: $total, ", $this->aauth->get_user()->username);



            $data_trans = array(
                'acid' => '1',
                'account' => 'Sales Account',
                'type' => 'Income',
                'cat' => 'Sales',
                'debit' =>  $total,
                'payer' => $cname,
                'payerid' => $customer_id,
                'method' => '',
                'balance' => 0,
                'date' => $bill_date,
                'eid' => $this->aauth->get_user()->id,
                'tid' => $customer_inv_to_credit,
                'inv_id' => $inv_no,
                'note' => 'Credit Note # ' . $inv_no,
                'loc' => $this->aauth->get_user()->loc
            );
            $this->db->insert('geopos_transactions', $data_trans);

            $last_trans_id = $this->db->insert_id();

            $data_credit = array(
                'trans_id' => $last_trans_id,
                'inv_id' => $customer_inv_to_credit
            );
            $this->db->where('id', $invocie_credit_no);
            $this->db->update('geopos_stock_r', $data_credit);

            // var_dump($this->db->last_query());
            $query = $this->db->get_where('geopos_invoices', array('id' => $customer_inv_to_credit));
            $result = $query->row_array();

            if ($result['amount_credited'] === NULL || $result['amount_credited'] === '0.00') {

                if ($result['status'] == "paid") {
                    $status = "partial";
                } else {

                    $status = $result['status'];
                }

                $data = array(
                    'amount_credited' => $total,
                    'status' => $status
                );
                $this->db->where('id', $customer_inv_to_credit);
                $this->db->update('geopos_invoices', $data);
                //    var_dump($this->db->last_query());

            } else {
                $new_value = $result['amount_credited'] + $total;
                if ($result['status'] == "paid") {
                    $status = "partial";
                } else {

                    $status = $result['status'];
                }
                $data = array(
                    'amount_credited' => $new_value,
                    'status' => $status
                );
                $this->db->where('id', $customer_inv_to_credit);
                $this->db->update('geopos_invoices', $data);
                // var_dump($this->db->last_query());


            }



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
                    $total_tax += numberClean($ptotal_tax[$key]);

                    $this->db->select('*');
                    $this->db->from('geopos_products');
                    $this->db->where('pid', $product_id[$key]);
                    $query = $this->db->get();
                    $pr = $query->row_array();

                    $cat_id = $pr['pcat'];

                    $data = array(
                        'tid' => $invocie_credit_no,
                        'pid' => $product_id[$key],
                        'product' => $product_name1[$key],
                        'code' => $product_hsn[$key],
                        'qty' => numberClean($product_qty[$key]),
                        'price' => rev_amountExchange_s($product_price[$key], $currency, $this->aauth->get_user()->loc),
                        'tax' => numberClean($product_tax[$key]),
                        'discount' => numberClean($product_discount[$key]),
                        'subtotal' => rev_amountExchange_s($product_subtotal[$key], $currency, $this->aauth->get_user()->loc),
                        'totaltax' => rev_amountExchange_s($ptotal_tax[$key], $currency, $this->aauth->get_user()->loc),
                        'totaldiscount' => rev_amountExchange_s($ptotal_disc[$key], $currency, $this->aauth->get_user()->loc),
                        'product_des' => $product_des[$key],
                        'unit' => $product_unit[$key]
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

                $this->db->insert_batch('geopos_stock_r_items', $productlist);

                $this->db->set(array('discount' => rev_amountExchange_s(amountFormat_general($total_discount), $currency, $this->aauth->get_user()->loc), 'tax' => rev_amountExchange_s(amountFormat_general($total_tax), $currency, $this->aauth->get_user()->loc), 'items' => $itc));
                $this->db->where('id', $invocie_credit_no);
                $this->db->update('geopos_stock_r');
            } else {
                echo json_encode(array('status' => 'Error', 'message' =>
                "Please choose product from product list. Go to Item manager section if you have not added the products."));
                $transok = false;
            }

            echo json_encode(array('status' => 'Success', 'message' =>
            $this->lang->line('ADDED') . " <a href='" . base_url('stockreturn/view?id=' . $invocie_credit_no) . "' class='btn btn-info btn-lg'><span class='fa fa-eye' aria-hidden='true'></span> " . $this->lang->line('View') . " </a>   <a href='" . base_url('stockreturn/printinvoice?id=' . $invocie_credit_no) . "' class='btn btn-pink btn-lg'><span class='fa fa-plus-circle' aria-hidden='true'></span>  </a>"));
        } else {
            echo json_encode(array('status' => 'Error', 'message' => $this->lang->line('ERROR')));
            $transok = false;
        }


        if ($transok) {
            $this->db->trans_complete();

            // redirect('stockreturn/creditnotes',refresh);
        } else {
            $this->db->trans_rollback();
        }
    }

    public function ajax_list()
    {
        $no = $this->input->post('start', true) ?? 0;
        $type = $this->input->get('t', true) ?? 0;
        $list = $this->stockreturn->get_datatables($type);
        // var_dump($list); die();
        $data = [];

        foreach ($list as $invoices) {
            $no++;
            $row = [];
            $row[] = $no;
            $row[] = $this->aauth->permission_new(null, 'stockReturnViewStockReturn') ? '<a href="' . base_url("stockreturn/view?id=$invoices->id") . '">' . $invoices->tid . '</a>' : '';
            $row[] = $invoices->name;
            $row[] = dateformat($invoices->invoicedate);
            $row[] = amountExchange($invoices->total, 0, $this->aauth->get_user()->loc);
            $row[] = '<span class="badge st-' . $invoices->status . '">' . $this->lang->line(ucwords($invoices->status)) . '</span>';
            $row[] =
                ($this->aauth->permission_new(null, 'stockReturnViewStockReturn')
                    ? '<a href="' . base_url("stockreturn/view?id=$invoices->id") . '" class="btn btn-success btn-sm">
                    <i class="fa fa-eye"></i> ' . $this->lang->line('View') . '</a> &nbsp;'
                    : '') .

                ($this->aauth->permission_new(null, 'stockReturnPrintStockReturn')
                    ? '<a href="' . base_url("stockreturn/printinvoice?id=$invoices->id&d=1&type=$type") . '" target="_blank" class="btn btn-info btn-sm">
                    <span class="fa fa-download"></span></a>&nbsp;'
                    : '') .

                ($this->aauth->permission_new(null, 'stockReturnDeleteStockReturn')
                    ? '<a href="#" data-object-id="' . $invoices->id . '" class="btn btn-danger btn-sm delete-object">
                    <span class="fa fa-trash"></span></a>'
                    : '');
            $data[] = $row;
        }

        $output = [
            "draw" => $_POST['draw'] ?? 1,
            "recordsTotal" => count($list),  // Total number of records in this request
            "recordsFiltered" => count($list), // Since we're not using count_all/count_filtered
            "data" => $data,
        ];

        echo json_encode($output);
    }

    public function ajax_list_credits_po()
    {
        $no = $this->input->post('start');
        $type = $this->input->get('t');
        // var_dump($type); die();
        $list = $this->stockreturn->get_creditnote_datatables_po($type);
        // var_dump($list);
        // die();
        $data = array();
        foreach ($list as $invoices) {
            $no++;
            $row = array();
            $row[] = $no;
            $row[] = $this->aauth->permission_new(null, 'stockReturnViewStockReturn') ? '<a href="' . base_url("stockreturn/credit_note_po?id=$invoices->id") . '">&nbsp; ' . $invoices->tid . '</a>' : '';
            $row[] = $invoices->name;
            $row[] = dateformat($invoices->invoicedate);
            $row[] = amountExchange($invoices->total, 0, $this->aauth->get_user()->loc);
            // $row[] = '<span class="badge st-' . $invoices->status . '">' . $this->lang->line(ucwords($invoices->status)) . '</span>';
            $row[] = ($this->aauth->permission_new(null, 'stockReturnViewStockReturn')
                ? '<a href="' . base_url("stockreturn/credit_note_po?id=$invoices->id") . '" class="btn btn-success btn-sm"><i class="fa fa-eye"></i> ' . $this->lang->line('View') . '</a>'
                : '') . ($this->aauth->permission_new(null, 'stockReturnPrintStockReturn')
                ? '&nbsp; <a href="' . base_url("stockreturn/print_creditnote_invoice_po?id=$invoices->id") . '&d=1" class="btn btn-info btn-sm"  title="Download"><span class="fa fa-download"></span></a>'
                : '');
            $data[] = $row;
        }
        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->stockreturn->count_all_credit_notes(),
            "recordsFiltered" => $this->stockreturn->count_filtered_credit_notes(),
            "data" => $data,
        );

        echo json_encode($output);
    }



    public function view()
    {
        $company = $this->settings->company_details(1);
        $data['logo'] = base_url("userfiles/company/" . $company['logo']);
        $this->load->model('accounts_model');
        $data['acclist'] = $this->accounts_model->accountslist();
        $tid = intval($this->input->get('id'));
        $data['id'] = $tid;
        $data['invoice'] = $this->stockreturn->purchase_details($tid);
        $data['products'] = $this->stockreturn->purchase_products($tid);
        $data['activity'] = $this->stockreturn->purchase_transactions($tid);
        $data['attach'] = $this->stockreturn->attach($tid);
        $data['employee'] = $this->stockreturn->employee($data['invoice']['eid']);
        $head['usernm'] = $this->aauth->get_user()->username;
        $head['title'] = "Stock return Order " . $data['invoice']['iid'];
        if (($data['invoice']['i_class'] != 2 && $this->aauth->permission_new(null, 'stockReturnManageStockReturn')) or ($data['invoice']['i_class'] == 2 && $this->aauth->permission_new(null, 'stockReturnManageStockReturn'))) {
            $this->load->view('fixed/header', $head);
            if ($data['invoice']['tid']) $this->load->view('stockreturn/view', $data);
            $this->load->view('fixed/footer');
        }
    }

    public function credit_note()
    {
        $data['company'] = $this->settings->company_details(1);
        $data['logo'] = base_url("userfiles/company/" . $data['company']['logo']);
        $this->load->model('accounts_model');
        $this->load->model('stockreturn_model');  // Ensure this model is loaded
        $data['acclist'] = $this->accounts_model->accountslist();
        $tid = intval($this->input->get('id'));
        $data['id'] = $tid;
        $data['invoice'] = $this->stockreturn_model->creditnote_details($tid);
        $data['products'] = $this->stockreturn_model->creditnote_products($tid);
        $data['activity'] = $this->stockreturn_model->purchase_transactions($tid);
        $data['attach'] = $this->stockreturn_model->attach($tid);
        $data['employee'] = $this->stockreturn_model->employee($data['invoice']['eid']);  // Adjust method call here
        $head['usernm'] = $this->aauth->get_user()->username;
        $head['title'] = "Stock return Order " . $data['invoice']['iid'];
        if (($data['invoice']['i_class'] != 2 && $this->aauth->permission_new(null, 'stockReturnManageStockReturn')) or ($data['invoice']['i_class'] == 2 && $this->aauth->permission_new(null, 'stockReturnManageStockReturn'))) {
            $this->load->view('fixed/header', $head);
            if ($data['invoice']['tid']) $this->load->view('stockreturn/creditnote_view', $data);
            $this->load->view('fixed/footer');
        }
    }


    public function credit_note_po()
    {
        $this->load->model('accounts_model');
        $data['acclist'] = $this->accounts_model->accountslist();
        $tid = intval($this->input->get('id'));
        $data['id'] = $tid;
        $data['invoice'] = $this->stockreturn->creditnote_details_po($tid);
        // dd($data['invoice']);
        $data['products'] = $this->stockreturn->creditnote_products_po($tid);
        // dd($data['products']);
        $data['activity'] = $this->stockreturn->purchase_transactions($tid);
        $data['attach'] = $this->stockreturn->attach($tid);
        $data['employee'] = $this->stockreturn->employee($data['invoice']['eid']);
        $head['usernm'] = $this->aauth->get_user()->username;
        $head['title'] = "Stock return Order " . $data['invoice']['iid'];
        dd($data['invoice']['name']);
        $this->load->view('fixed/header', $head);
        $this->load->view('stockreturn/creditnote_po_view', $data);
        $this->load->view('fixed/footer');
    }


    public function stock_return_report()
    {

        $head['title'] = "Day Book";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $this->load->view('stockreturn/s_return_report');
        $this->load->view('fixed/footer');
    }


    public function stockreturn_report()
    {

        $company = $this->settings->company_details(1);
        ini_set('memory_limit', '-1');
        set_time_limit(0);
        $data = $this->stockreturn->stockreturn_report();

        $data_tr = "";
        $start_date = $_POST['hidden_start_date'];
        $end_date = $_POST['hidden_end_date'];

        $netsubtotal = 0;
        $subtotal = 0;
        ob_end_clean();
        $no = 1;
        foreach ($data as $key => $row) {
            // echo"<pre>";
            // print_r($data);die();
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
                $data_tr .= '<td>Opening Balance</td><td>0.00</td><td>' . amountExchange_s($row["subtotal"] - $row["tax"]) . '</td>';
            } else {
                $data_tr .= '<td>' . amountExchange_s($subtotal) . '</td><td>' . amountExchange_s($row["tax"]) . '</td><td>' . amountExchange_s($row["subtotal"]) . '</td>';
            }
            $data_tr .= '</tr>';
            // <td>'.$row["note"].'</td>
        }

        $total_qty = 0;
        $total_sub = 0;
        $total_tax = 0;
        foreach ($data as $key => $row) {
            // $total_qty+=$row["qty"];

            if ($row["tid"] == 0) {
                $total_sub += ($row["subtotal"] - $row["tax"]);
                //$data_tr .='<td>1</td>';
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
            <b><u> Stock Return: Customer Invoices (Summary)</u></b> 
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
           <u> Tax&nbsp;Amount</u>
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













    public function print_creditnote_invoice()
    {
        $tid = $this->input->get('id');
        $ty = $this->input->get('ty');
        $data['id'] = $tid;
        $data['title'] = "Stock Return $tid";
        $data['invoice'] = $this->stockreturn->creditnote_details($tid);
        $data['products'] = $this->stockreturn->creditnote_products($tid);
        $company = $this->settings->company_details(1);

        $data['employee'] = $this->stockreturn->employee($data['invoice']['eid']);
        if (($data['invoice']['i_class'] != 2 && $this->aauth->permission_new(null, 'stockReturnManageStockReturn')) or ($data['invoice']['i_class'] == 2 && $this->aauth->permission_new(null, 'stockReturnManageStockReturn'))) {
            if ($ty < 2) {
                $data['general'] = array('title' => $this->lang->line('Stock Return'), 'person' => $this->lang->line('Supplier'), 'prefix' => prefix(4), 't_type' => 0);
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
            ob_end_clean();

            $header = '<html><head><style>@page { margin: 350px 50px 200px; }header{ position: fixed;  top: -320px;}
        table{width:100%;}.pagenum:after {content: counter(page)}</style> </head><body><header>';
            $header .= '<table style="width:100%;">
        <tr style="border:1px solid #1367A4;">
        <td style="width:100%;"><img src="' . $left_logo . '" height="165px" width="270px"></td>
        <td style="width:40%;"><img src="' . $right_logo . '" height="60px" width="60px"></td>
        <td style="width:100%;"><br><b>' . $company['cname'] . '</b><br>' . $company['address'] . '<br>' . $company['city'] . ' ' . $company['region'] . ', ' . $company['postbox'] . '<br>' . $company['country'] . ' <br> Phone:&nbsp;&nbsp;&nbsp;&nbsp;' . $company['phone'] . ' <br> 
            <table style="border: 1px solid #1367A4;
            border-collapse: collapse;margin-top:10px; border-radius: 6px;">
            <tr >
                        <th  style="border: 1px solid #1367A4;
                        border-collapse: collapse;font-size: 13px;text-align: center" >Date
                            </th>
                            <th  style="border: 1px solid #1367A4;
                            border-collapse: collapse;font-size: 13px;text-align: center">Ivoice
                            </th>
                            <th  style="border: 1px solid #1367A4;
                            border-collapse: collapse;font-size: 13px;text-align: center">A/C
                            </th>
                            <th  style="border: 1px solid #1367A4;
                            border-collapse: collapse;font-size: 13px; text-align: center">VAT
                            </th>
            </tr>
            <tr >
                <td style="width:30%;font-size: 13px;border: 1px solid #1367A4;border-collapse: collapse;text-align: center">
                ' . $data['invoice']['invoicedate'] . '
                </td>
                <td style="font-size: 13px;border: 1px solid #1367A4;border-collapse: collapse;text-align: center">' . $data['invoice']['tid'] . '</td>
                <td style="font-size: 13px;border: 1px solid #1367A4;border-collapse: collapse;text-align: center">Stock Return</td>
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
            For Bank Transfer:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $company['cname'] . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;S/C&nbsp;&nbsp;' . $company['sortcode'] . '           &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;AC&nbsp;&nbsp;' . $company['taxid'] . '  
            </b></td></tr></table>
            
            ';
            $header .= '<div style="clear:both"></div> <main><div style="width:100%; margin-top:5px; "><table width="100%" class="myProducts" style="border:1px solid #1367A4;border-radius: 6px;"><thead><tr style="font-size: 14px; background:#ccc; padding:4px;"><th style="width: 12%; text-align:left; ">Quantity</th><th style="border-left: 1px solid #1367A4;width: 45%; text-align:left;">Details</th><th style="width: 13%; text-align:left; border-left: 1px solid #1367A4;">Unit Price</th><th style="width: 20%; text-align:left; border-left: 1px solid #1367A4;">Net Amount</th><th style="width: 20%; text-align:left; border-left: 1px solid #1367A4;">VAT</th></tr></thead>';
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
                    //    $header.='<td style=" border-bottom:1px solid white;">' . $row["qty"] . $row["unit"] . '</td style=" border-bottom:1px solid white;"><td style=" border-bottom:1px solid white;">' . $row["product"] . '</td><td style=" border-bottom:1px solid white;">' . substr($up,3,100). '</td> <td style=" border-bottom:1px solid white;">' . substr($netamount,3,100) . '</td><td style=" border-bottom:1px solid white;">' . substr($vat,3,100) . ' </td></tr>';



                }
            }
            $header .= '</tbody></table></div></main>';
            $header .= '<footer style="position:absolute;bottom:-195px">';

            $header .= '<div style="border:1px solid #1367A4;width:59%;height:170px; padding: 8px 0px 5px 4px; border-radius: 6px;float:left;"><span style="color:rgb(48, 37, 133)"><b>*MAXIMUM CHANGE ACCEPTED=&#163;60</b></span><div style=" font-size: 10px;">
                *All claims must be notified at the time of delivery,<br>
                any claims made after delivery will not be considered.<br>
                Goods will remain the property of ' . $company['cname'] . ' <br>until paid in full by the customer.<br>
                Frozen and chilled products cannot be returned once delivered.<br>
                </div><br><div style="width:100%"><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left">Customer:</div><div style="border:1px solid black;width:8%;height:18px;float:left;margin-top:10px;margin-left:-178px;"></div><div style="border:1px solid black;width:8%;height:18px;float:left;margin-top:10px;margin-left:-58px;"></div><div style="border:1px solid black;width:8%;height:18px;float:left;margin-top:10px;margin-left:-118px;"></div><div style="float:left;margin-left:-178px;margin-top:-5px;font-size: 12px;"><b>Cash &nbsp;&nbsp; Card Payment &nbsp;&nbsp;BACS</b></div><div style="clear:both"></div><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left;">Driver:</div><div style="font-size: 17px;padding-left:10px;border:1px solid black;width:35%;height:20px;float:left;padding-top:3px;padding-bottom:3px;margin-left:-187px;margin-top:3px">&#163;</div><div style="clear:both"></div></div><div style="clear:both"></div></div>';


            $sub_ts = 0;
            foreach ($data['products'] as $row) {
                $sub_ts += $row['price'] * $row['qty'];
            }

            $header .= '<div style="float:right;margin-left:320px;width:280px" ><table class="maintable2" style="width:100%;border-radius: 6px; border:1px solid #1367A4;font-size: 14px;">';
        }

        $header .= '<tr><td style="width: 60%;  background: lightgrey;padding:  4px 0px 4px 7px;">&nbsp;Total NET Amount</td><td> ' . $sub_ts . ' </td></tr><tr><td style="width: 60%;  background: lightgrey;padding: 4px 0px 4px 7px;">&nbsp;VAT Total</td><td> ' . $data["invoice"]["tax"] . '</td></tr><tr><td style="width: 60%;  background: lightgrey;padding: 4px 0px 4px 7px;">&nbsp;Invoice Total</td><td> ' . $data["invoice"]["total"] . '   </td></tr>';


        $header .= ' <tr><td style="width: 60%; background: lightgrey;padding:  4px 0px 4px 7px;">&nbsp;Balance Now Due</td><td>' . $cust_balance_inv . ' </td></tr> ';

        $header .= '</table>
                <span href="mailto:' . $company['email'] . '"><img src="' . base_url("assets/images/mail.jpg") . '" height="32px" width="38px" style="margin-top:10px;">  </span>' . $company['email'] . '<br>
                <!--<span href="https://www.5starfoodsltd.com/"><img src="' . base_url("assets/images/web.jpg") . '" height="32px" width="38px" style="margin-top:10px;"> </span>www.5starfoodsltd.com -->
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






    public function print_creditnote_invoice_po()
    {

        $tid = $this->input->get('id');
        $ty = $this->input->get('ty');
        $data['id'] = $tid;
        $data['title'] = "Stock Return $tid";
        $data['invoice'] = $this->stockreturn->creditnote_details_po($tid);
        $data['products'] = $this->stockreturn->creditnote_products_po($tid);
        $company = $this->settings->company_details(1);


        $data['employee'] = $this->stockreturn->employee($data['invoice']['eid']);
        if (($data['invoice']['i_class'] != 2 && $this->aauth->permission_new(null, 'stockReturnManageStockReturn')) or ($data['invoice']['i_class'] == 2 && $this->aauth->permission_new(null, 'stockReturnManageStockReturn'))) {
            if ($ty < 2) {
                $data['general'] = array('title' => $this->lang->line('Stock Return'), 'person' => $this->lang->line('Supplier'), 'prefix' => prefix(4), 't_type' => 0);
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

            // $left_logo = base_url("assets/images/dixy_logo_.jpeg");
            // $data['left_logo'] = $left_logo;
            // $right_logo = base_url("assets/images/halal_logo.jpg");
            // $data['right_logo'] = $right_logo;
            $left_logo = base_url("userfiles/company/" . $company['logo']);
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
        <td style="width:100%;"><br><b>' . $company['cname'] . '</b><br>' . $company['address'] . '<br>' . $company['city'] . ' ' . $company['region'] . ', ' . $company['postbox'] . '<br>' . $company['country'] . ' <br> Phone:&nbsp;&nbsp;&nbsp;&nbsp;' . $company['phone'] . ' <br> 
            <table style="border: 1px solid #1367A4;
            border-collapse: collapse;margin-top:10px; border-radius: 6px;">
            <tr >
                        <th  style="border: 1px solid #1367A4;
                        border-collapse: collapse;font-size: 13px;text-align: center" >Date
                            </th>
                            <th  style="border: 1px solid #1367A4;
                            border-collapse: collapse;font-size: 13px;text-align: center">Ivoice
                            </th>
                            <th  style="border: 1px solid #1367A4;
                            border-collapse: collapse;font-size: 13px;text-align: center">A/C
                            </th>
                            <th  style="border: 1px solid #1367A4;
                            border-collapse: collapse;font-size: 13px; text-align: center">VAT
                            </th>
            </tr>
            <tr >
                <td style="width:30%;font-size: 13px;border: 1px solid #1367A4;border-collapse: collapse;text-align: center">
                ' . $data['invoice']['invoicedate'] . '
                </td>
                <td style="font-size: 13px;border: 1px solid #1367A4;border-collapse: collapse;text-align: center">' . $data['invoice']['tid'] . '</td>
                <td style="font-size: 13px;border: 1px solid #1367A4;border-collapse: collapse;text-align: center">Stock Return</td>
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
            For Bank Transfer:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $company['cname'] . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;S/C&nbsp;&nbsp;' . $company['sortcode'] . '           &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;AC&nbsp;&nbsp;' . $company['taxid'] . '  
            </b></td></tr></table>
            
            ';
            $header .= '<div style="clear:both"></div> <main><div style="width:100%; margin-top:5px; "><table width="100%" class="myProducts" style="border:1px solid #1367A4;border-radius: 6px;"><thead><tr style="font-size: 14px; background:#ccc; padding:4px;"><th style="width: 12%; text-align:left; ">Quantity</th><th style="border-left: 1px solid #1367A4;width: 45%; text-align:left;">Details</th><th style="width: 13%; text-align:left; border-left: 1px solid #1367A4;">Unit Price</th><th style="width: 20%; text-align:left; border-left: 1px solid #1367A4;">Net Amount</th><th style="width: 20%; text-align:left; border-left: 1px solid #1367A4;">VAT</th></tr></thead>';
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
                    //    $header.='<td style=" border-bottom:1px solid white;">' . $row["qty"] . $row["unit"] . '</td style=" border-bottom:1px solid white;"><td style=" border-bottom:1px solid white;">' . $row["product"] . '</td><td style=" border-bottom:1px solid white;">' . substr($up,3,100). '</td> <td style=" border-bottom:1px solid white;">' . substr($netamount,3,100) . '</td><td style=" border-bottom:1px solid white;">' . substr($vat,3,100) . ' </td></tr>';



                }
            }
            $header .= '</tbody></table></div></main>';
            $header .= '<footer style="position:absolute;bottom:-195px">';

            $header .= '<div style="border:1px solid #1367A4;width:59%;height:170px; padding: 8px 0px 5px 4px; border-radius: 6px;float:left;"><span style="color:rgb(48, 37, 133)"><b>*MAXIMUM CHANGE ACCEPTED=&#163;60</b></span><div style=" font-size: 10px;">
                *All claims must be notified at the time of delivery,<br>
                any claims made after delivery will not be considered.<br>
                Goods will remain the property of ' . $company['cname'] . ' <br>until paid in full by the customer.<br>
                Frozen and chilled products cannot be returned once delivered.<br>
                </div><br><div style="width:100%"><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left">Customer:</div><div style="border:1px solid black;width:8%;height:18px;float:left;margin-top:10px;margin-left:-178px;"></div><div style="border:1px solid black;width:8%;height:18px;float:left;margin-top:10px;margin-left:-58px;"></div><div style="border:1px solid black;width:8%;height:18px;float:left;margin-top:10px;margin-left:-118px;"></div><div style="float:left;margin-left:-178px;margin-top:-5px;font-size: 12px;"><b>Cash &nbsp;&nbsp; Card Payment &nbsp;&nbsp;BACS</b></div><div style="clear:both"></div><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left;">Driver:</div><div style="font-size: 17px;padding-left:10px;border:1px solid black;width:35%;height:20px;float:left;padding-top:3px;padding-bottom:3px;margin-left:-187px;margin-top:3px">&#163;</div><div style="clear:both"></div></div><div style="clear:both"></div></div>';


            $sub_ts = 0;
            foreach ($data['products'] as $row) {
                $sub_ts += $row['price'] * $row['qty'];
            }

            $header .= '<div style="float:right;margin-left:320px;width:280px" ><table class="maintable2" style="width:100%;border-radius: 6px; border:1px solid #1367A4;font-size: 14px;">';
        }

        $header .= '<tr><td style="width: 60%;  background: lightgrey;padding:  4px 0px 4px 7px;">&nbsp;Total NET Amount</td><td> ' . $sub_ts . ' </td></tr><tr><td style="width: 60%;  background: lightgrey;padding: 4px 0px 4px 7px;">&nbsp;VAT Total</td><td> ' . $data["invoice"]["tax"] . '</td></tr><tr><td style="width: 60%;  background: lightgrey;padding: 4px 0px 4px 7px;">&nbsp;Invoice Total</td><td> ' . $data["invoice"]["total"] . '   </td></tr>';


        $header .= ' <tr><td style="width: 60%; background: lightgrey;padding:  4px 0px 4px 7px;">&nbsp;Balance Now Due</td><td>' . $cust_balance_inv . ' </td></tr> ';

        $header .= '</table>
                <span href="mailto:' . $company['email'] . '"><img src="' . base_url("assets/images/mail.jpg") . '" height="32px" width="38px" style="margin-top:10px;">  </span>' . $company['email'] . '<br>
                <!--<span href="https://www.5starfoodsltd.com/"><img src="' . base_url("assets/images/web.jpg") . '" height="32px" width="38px" style="margin-top:10px;"> </span>www.5starfoodsltd.com -->
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





    public function printinvoice()
    {
        // var_dump($this->input->get('type')); die();
        $company = $this->settings->company_details(1);
        $tid = $this->input->get('id');
        $ty = $this->input->get('ty');
        $type = $this->input->get('type');
        $data['id'] = $tid;
        $data['title'] = "Stock Return $tid";
        $data['invoice'] = $this->stockreturn->purchase_details($tid);
        // dd($data['invoice']);
        $data['products'] = $this->stockreturn->purchase_products($tid);


        $data['employee'] = $this->stockreturn->employee($data['invoice']['eid']);
        if (($data['invoice']['i_class'] != 2 && $this->aauth->permission_new(null, 'stockReturnManageStockReturn')) or ($data['invoice']['i_class'] == 2 && $this->aauth->permission_new(null, 'stockReturnManageStockReturn'))) {
            if ($ty < 2) {
                $data['general'] = array('title' => $this->lang->line('Stock Return'), 'person' => $this->lang->line('Supplier'), 'prefix' => prefix(4), 't_type' => 0);
            } else {
                $data['general'] = array('title' => $this->lang->line('Credit Note'), 'person' => $this->lang->line('Customer'), 'prefix' => prefix(4), 't_type' => 0);
            }

            if ($type == 1) {
                $sql = 'SELECT sum(total)-sum(pamnt) as "cust_balance" FROM geopos_invoices WHERE status="due" and csd="' . $data["invoice"]["csd"] . '" or status="partial" and csd="' . $data["invoice"]["csd"] . '"';
            } else {
                $sql = 'SELECT sum(total)-sum(pamnt) as "cust_balance" FROM geopos_purchase WHERE status="due" and csd="' . $data["invoice"]["cid"] . '" or status="partial" and csd="' . $data["invoice"]["cid"] . '"';
                // var_dump($sql); die();
            }
            $query = $this->db->query($sql);
            $response = $query->result_array();
            // $cust_balance_inv = $response[0]['cust_balance'];
            $cust_balance_inv = isset($response[0]['cust_balance']) ? $response[0]['cust_balance'] : 0.00;


            ob_end_clean();
            ob_start();

            ini_set('memory_limit', '64M');

            $data_tr = "";

            // $left_logo = base_url("assets/images/_");
            // $data['left_logo'] = $left_logo;
            // $right_logo = base_url("assets/images/halal_logo.jpg");
            // $data['right_logo'] = $right_logo;
            $left_logo = base_url("userfiles/company/" . $company['logo']);
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
            <td style="width:100%;"><br><b>' . $company['cname'] . '</b><br>' . $company['address'] . ' ' . $company['city'] . ' ' . $company['region'] . ', ' . $company['postbox'] . ', ' . $company['country'] . ' <br> Phone:&nbsp;&nbsp;&nbsp;' . $company['phone'] . ' <br> Mobile:&nbsp;&nbsp;' . $company['mobile'] . ' <br>
            <table style="border: 1px solid #1367A4;
                border-collapse: collapse;margin-top:10px; border-radius: 6px;">
                <tr >
                 <th  style="border: 1px solid #1367A4;
                 border-collapse: collapse;font-size: 13px;text-align: center" >Date
                    </th>
                    <th  style="border: 1px solid #1367A4;
                    border-collapse: collapse;font-size: 13px;text-align: center">Ivoice
                    </th>
                    <th  style="border: 1px solid #1367A4;
                    border-collapse: collapse;font-size: 13px;text-align: center">A/C
                    </th>
                    <th  style="border: 1px solid #1367A4;
                    border-collapse: collapse;font-size: 13px; text-align: center">VAT
                    </th>
                </tr>
                <tr >
                    <td style="width:30%;font-size: 13px;border: 1px solid #1367A4;border-collapse: collapse;text-align: center">
                    ' . $data['invoice']['invoicedate'] . '
                    </td>
                    <td style="font-size: 13px;border: 1px solid #1367A4;border-collapse: collapse;text-align: center">' . $data['invoice']['tid'] . '</td>
                    <td style="font-size: 13px;border: 1px solid #1367A4;border-collapse: collapse;text-align: center">Stock Return</td>
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
            if (!empty($data['invoice']['notes'])) {
                $header .= '</div><table style="height:5%; font-size: 14px; border:1px solid #1367A4; border-radius: 6px;"> 
                    <tr><td><b>' . $data['invoice']['notes'] . '</b></td></tr>
                </table>';
            } else {
                $header .= '</div><table style="height:5%; font-size: 14px; border:1px solid #1367A4; border-radius: 6px;"> 
                    <tr><td style="text-align: center;"><b>Stock Return</b></td></tr>
                </table>';
            }
            $header .= '</table>';

            $header .= '</div></div></header>';
            $header .= '
           
            <table style="width:100%; font-size: 14px;  margin-top:-15px;"><tr><td style="width: 100%;">&nbsp;<b> 
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            For Bank Transfer:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Cloud Billing Manager.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;S/C&nbsp;&nbsp;20-98-98           &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;AC&nbsp;&nbsp;33821986  
            </b></td></tr></table>
            
            ';
            $header .= '<div style="clear:both"></div> <main><div style="width:100%; margin-top:5px; "><table width="100%" class="myProducts" style="border:1px solid #1367A4;border-radius: 6px;"><thead><tr style="font-size: 14px; background:#ccc; padding:4px;"><th style="width: 12%; text-align:left; ">Quantity</th><th style="border-left: 1px solid #1367A4;width: 45%; text-align:left;">Details</th><th style="width: 13%; text-align:left; border-left: 1px solid #1367A4;">Unit Price</th><th style="width: 20%; text-align:left; border-left: 1px solid #1367A4;">Net Amount</th><th style="width: 20%; text-align:left; border-left: 1px solid #1367A4;">VAT</th></tr></thead>';
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
                <td style=" border-bottom:1px solid white;">' . $row["product_des"] . '</td><td style=" border-bottom:1px solid white;">' . substr($up, 3, 100) . '</td> <td style=" border-bottom:1px solid white;">' . substr($netamount, 3, 100) . '</td><td style=" border-bottom:1px solid white;">' . substr($vat, 3, 100) . ' </td></tr>';

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
                    //    $header.='<td style=" border-bottom:1px solid white;">' . $row["qty"] . $row["unit"] . '</td style=" border-bottom:1px solid white;"><td style=" border-bottom:1px solid white;">' . $row["product"] . '</td><td style=" border-bottom:1px solid white;">' . substr($up,3,100). '</td> <td style=" border-bottom:1px solid white;">' . substr($netamount,3,100) . '</td><td style=" border-bottom:1px solid white;">' . substr($vat,3,100) . ' </td></tr>';



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
                $total_vat += $row['tax'] * $row['qty'];
            }

            $header .= '<div style="float:right;margin-left:320px;width:280px" ><table class="maintable2" style="width:100%;border-radius: 6px; border:1px solid #1367A4;font-size: 14px;">';
        }

        $header .= '<tr><td style="width: 60%;  background: lightgrey;padding:  4px 0px 4px 7px;">&nbsp;Total NET Amount</td><td> ' . amountExchange($sub_ts) . ' </td></tr>
        <tr><td style="width: 60%;  background: lightgrey;padding: 4px 0px 4px 7px;">&nbsp;VAT Total</td><td> ' . amountExchange($total_vat) . '</td></tr>
        <tr><td style="width: 60%;  background: lightgrey;padding: 4px 0px 4px 7px;">&nbsp;Invoice Total</td><td> ' . amountExchange($data["invoice"]["total"]) . '   </td></tr>';


        // $header .= ' <tr><td style="width: 60%; background: lightgrey;padding:  4px 0px 4px 7px;">&nbsp;Balance Now Due</td><td>' . amountExchange($cust_balance_inv) . ' </td></tr> ';

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



    public function delete_i()
    {
        $id = $this->input->post('deleteid');
        if ($this->stockreturn->purchase_delete($id, $this->limited)) {
            echo json_encode(array('status' => 'Success', 'message' =>
            $this->lang->line('DELETED')));
        } else {
            echo json_encode(array('status' => 'Error', 'message' =>
            $this->lang->line('ERROR')));
        }
    }

    public function editaction()
    {
        $customer_id = $this->input->post('customer_id');
        $person_type = $this->input->post('person_type');
        if ($person_type) {
            if (!$this->aauth->permission_new(null, 'stockReturnAccess')) {
                exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
            }
        }
        if ($person_type == 2) {
            if (!$this->aauth->permission_new(null, 'salesCreditNote')) {
                exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
            }
        }
        $invocieno = $this->input->post('iid');
        $invoicedate = $this->input->post('invoicedate');
        $invocieduedate = $this->input->post('invocieduedate');
        $notes = $this->input->post('notes', true);
        $tax = $this->input->post('tax_handle');
        $ship_taxtype = $this->input->post('ship_taxtype');
        $total_tax = 0;
        $total_discount = 0;
        $discountFormat = $this->input->post('discountFormat');
        $pterms = $this->input->post('pterms');
        $i = 0;
        if ($discountFormat == '0') {
            $discstatus = 0;
        } else {
            $discstatus = 1;
        }
        if ($customer_id == 0) {
            echo json_encode(array('status' => 'Error', 'message' =>
            "Please add a new supplier or search from a previous added!"));
            exit;
        }
        $currency = $this->input->post('mcurrency');
        $subtotal = rev_amountExchange_s($this->input->post('subtotal'), $currency, $this->aauth->get_user()->loc);
        $shipping = rev_amountExchange_s($this->input->post('shipping'), $currency, $this->aauth->get_user()->loc);
        $shipping_tax = rev_amountExchange_s($this->input->post('ship_tax'), $currency, $this->aauth->get_user()->loc);
        if ($ship_taxtype == 'incl') $shipping = $shipping - $shipping_tax;
        $refer = $this->input->post('refer', true);
        $total = rev_amountExchange_s($this->input->post('total'), $currency, $this->aauth->get_user()->loc);
        $this->db->trans_start();
        $flag = false;
        $transok = true;
        //Product Data
        $pid = $this->input->post('pid');
        $productlist = array();
        $prodindex = 0;
        $this->db->delete('geopos_stock_r_items', array('tid' => $invocieno));
        $product_id = $this->input->post('pid');
        $product_name1 = $this->input->post('product_name', true);
        $product_qty = $this->input->post('product_qty');
        $old_product_qty = $this->input->post('old_product_qty');
        if ($old_product_qty == '') $old_product_qty = 0;
        $product_price = $this->input->post('product_price');
        $product_tax = $this->input->post('product_tax');
        $product_discount = $this->input->post('product_discount');
        $product_subtotal = $this->input->post('product_subtotal');
        $ptotal_tax = $this->input->post('taxa');
        $ptotal_disc = $this->input->post('disca');
        $product_des = $this->input->post('product_description', true);
        $product_unit = $this->input->post('unit');
        $product_hsn = $this->input->post('hsn');
        foreach ($pid as $key => $value) {
            $total_discount += numberClean(@$ptotal_disc[$key]);
            $total_tax += numberClean($ptotal_tax[$key]);
            $data = array(
                'tid' => $invocieno,
                'pid' => $product_id[$key],
                'product' => $product_name1[$key],
                'code' => $product_hsn[$key],
                'qty' => numberClean($product_qty[$key]),
                'price' => rev_amountExchange_s($product_price[$key], $currency, $this->aauth->get_user()->loc),
                'tax' => numberClean($product_tax[$key]),
                'discount' => numberClean($product_discount[$key]),
                'subtotal' => rev_amountExchange_s($product_subtotal[$key], $currency, $this->aauth->get_user()->loc),
                'totaltax' => rev_amountExchange_s($ptotal_tax[$key], $currency, $this->aauth->get_user()->loc),
                'totaldiscount' => rev_amountExchange_s($ptotal_disc[$key], $currency, $this->aauth->get_user()->loc),
                'product_des' => $product_des[$key],
                'unit' => $product_unit[$key]
            );
            $productlist[$prodindex] = $data;
            $i++;
            $prodindex++;
            if ($this->input->post('update_stock') == 'yes') {
                $amt = numberClean(@$product_qty[$key]) - numberClean(@$old_product_qty[$key]);
                $this->db->set('qty', "qty-$amt", FALSE);
                $this->db->where('pid', $product_id[$key]);
                $this->db->update('geopos_products');
            }
            $flag = true;
        }
        $bill_date = datefordatabase($invoicedate);
        $bill_due_date = datefordatabase($invocieduedate);
        $data = array('invoicedate' => $bill_date, 'invoiceduedate' => $bill_due_date, 'subtotal' => $subtotal, 'shipping' => $shipping, 'ship_tax' => $shipping_tax, 'ship_tax_type' => $ship_taxtype, 'discount' => $total_discount, 'tax' => $total_tax, 'total' => $total, 'notes' => $notes, 'csd' => $customer_id, 'items' => $i, 'taxstatus' => $tax, 'discstatus' => $discstatus, 'format_discount' => $discountFormat, 'refer' => $refer, 'term' => $pterms, 'i_class' => $person_type);
        $this->db->set($data);
        $this->db->where('id', $invocieno);
        if ($flag) {
            if ($this->db->update('geopos_stock_r', $data)) {
                $this->db->insert_batch('geopos_stock_r_items', $productlist);
                echo json_encode(array('status' => 'Success', 'message' =>
                "Updated! <a href='view?id=$invocieno' class='btn btn-info btn-lg'><span class='fa fa-eye' aria-hidden='true'></span> View </a> "));
            } else {
                echo json_encode(array('status' => 'Error', 'message' =>
                "There is a missing field!"));
                $transok = false;
            }
        } else {
            echo json_encode(array('status' => 'Error', 'message' =>
            "Please add atleast one product in order!"));
            $transok = false;
        }

        if ($this->input->post('update_stock') == 'yes') {
            if ($this->input->post('restock')) {
                foreach ($this->input->post('restock') as $key => $value) {
                    $myArray = explode('-', $value);
                    $prid = $myArray[0];
                    $dqty = numberClean($myArray[1]);
                    if ($prid > 0) {
                        $this->db->set('qty', "qty-$dqty", FALSE);
                        $this->db->where('pid', $prid);
                        $this->db->update('geopos_products');
                    }
                }
            }
        }
        if ($transok) {
            $this->db->trans_complete();
        } else {
            $this->db->trans_rollback();
        }
    }

    public function update_status()
    {
        $tid = $this->input->post('tid');
        $status = $this->input->post('status');
        $this->db->select('i_class');
        $this->db->from('geopos_stock_r');
        $this->db->where('id', $tid);
        $query = $this->db->get();
        $stock = $query->row_array();
        if (($stock['i_class'] != 2 && $this->aauth->permission_new(null, 'stockReturnManageStockReturn')) or ($stock['i_class'] == 2 && $this->aauth->permission_new(null, 'stockReturnManageStockReturn'))) {
            $this->db->set('status', $status);
            $this->db->where('id', $tid);
            $this->db->update('geopos_stock_r');
            echo json_encode(array('status' => 'Success', 'message' =>
            'Status updated successfully!', 'pstatus' => $status));
        }
    }

    public function file_handling()
    {
        if ($this->input->get('op')) {
            $name = $this->input->get('name');
            $invoice = $this->input->get('invoice');
            if ($this->stockreturn->meta_delete($invoice, 5, $name)) {
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
                $this->stockreturn->meta_insert($id, 5, $files);
            }
        }
    }

    public function cancelorder()
    {
        $tid = intval($this->input->post('tid'));
        $this->db->select('i_class');
        $this->db->from('geopos_stock_r');
        $this->db->where('id', $tid);
        $query = $this->db->get();
        $stock = $query->row_array();
        if (($stock['i_class'] != 2 && $this->aauth->permission_new(null, 'stockReturnManageStockReturn')) or ($stock['i_class'] == 2 && $this->aauth->permission_new(null, 'stockReturnManageStockReturn'))) {
            $this->db->set('pamnt', "0.00", FALSE);
            $this->db->set('status', 'canceled');
            $this->db->where('id', $tid);
            $this->db->update('geopos_stock_r');
            //reverse
            $this->db->select('credit,acid');
            $this->db->from('geopos_transactions');
            $this->db->where('tid', $tid);
            $this->db->where('ext', 6);
            $query = $this->db->get();
            $revresult = $query->result_array();
            foreach ($revresult as $trans) {
                $amt = $trans['credit'];
                $this->db->set('lastbal', "lastbal-$amt", FALSE);
                $this->db->where('id', $trans['acid']);
                $this->db->update('geopos_accounts');
            }
            $this->db->select('pid,qty');
            $this->db->from('geopos_stock_r_items');
            $this->db->where('tid', $tid);
            $query = $this->db->get();
            $prevresult = $query->result_array();
            foreach ($prevresult as $prd) {
                $amt = $prd['qty'];
                $this->db->set('qty', "qty+$amt", FALSE);
                $this->db->where('pid', $prd['pid']);
                $this->db->update('geopos_products');
            }
            $this->db->delete('geopos_transactions', array('tid' => $tid, 'ext' => 6));
            echo json_encode(array('status' => 'Success', 'message' =>
            $this->lang->line('Return canceled')));
        }
    }


    public function pay()
    {
        $this->load->library("Custom");
        $tid = intval($this->input->post('tid'));
        $this->db->select('i_class');
        $this->db->from('geopos_stock_r');
        $this->db->where('id', $tid);
        $query = $this->db->get();
        $stock = $query->row_array();
        if (($stock['i_class'] != 2 && $this->aauth->permission_new(null, 'stockReturnManageStockReturn')) or ($stock['i_class'] == 2 && $this->aauth->permission_new(null, 'stockReturnManageStockReturn'))) {

            $amount = rev_amountExchange_s($this->input->post('amount', true), 0, $this->aauth->get_user()->loc);
            $paydate = $this->input->post('paydate');
            $note = $this->input->post('shortnote', true);
            $pmethod = $this->input->post('pmethod');
            $acid = $this->input->post('account');
            $cid = $this->input->post('cid');
            $cname = $this->input->post('cname', true);
            $paydate = datefordatabase($paydate);


            if ($stock['i_class'] == 2 or $stock['i_class'] == 1) {
                $this->db->select('holder');
                $this->db->from('geopos_accounts');
                $this->db->where('id', $acid);
                $query = $this->db->get();
                $account = $query->row_array();

                $data = array(
                    'acid' => $acid,
                    'account' => $account['holder'],
                    'type' => 'Expense',
                    'cat' => 'Credit Note',
                    'debit' => $amount,
                    'payer' => $cname,
                    'payerid' => $cid,
                    'method' => $pmethod,
                    'date' => $paydate,
                    'eid' => $this->aauth->get_user()->id,
                    'tid' => $tid,
                    'note' => $note,
                    'ext' => 6
                );
                $this->db->insert('geopos_transactions', $data);
                $this->db->insert_id();
                $this->db->select('total,csd,pamnt');
                $this->db->from('geopos_stock_r');
                $this->db->where('id', $tid);
                $query = $this->db->get();
                $invresult = $query->row();
                $totalrm = $invresult->total - $invresult->pamnt;
                if ($totalrm > $amount) {
                    $this->db->set('pmethod', $pmethod);
                    $this->db->set('pamnt', "pamnt+$amount", FALSE);
                    $this->db->set('status', 'partial');
                    $this->db->where('id', $tid);
                    $this->db->update('geopos_stock_r');
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
                    $this->db->set('status', 'accepted');
                    $this->db->where('id', $tid);
                    $this->db->update('geopos_stock_r');
                    //acount update
                    $this->db->set('lastbal', "lastbal-$amount", FALSE);
                    $this->db->where('id', $acid);
                    $this->db->update('geopos_accounts');
                    $totalrm = 0;
                    $status = 'Accepted';
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
            } else {


                $this->db->select('holder');
                $this->db->from('geopos_accounts');
                $this->db->where('id', $acid);
                $query = $this->db->get();
                $account = $query->row_array();

                $data = array(
                    'acid' => $acid,
                    'account' => $account['holder'],
                    'type' => 'Income',
                    'cat' => 'Purchase',
                    'credit' => $amount,
                    'payer' => $cname,
                    'payerid' => $cid,
                    'method' => $pmethod,
                    'date' => $paydate,
                    'eid' => $this->aauth->get_user()->id,
                    'tid' => $tid,
                    'note' => $note,
                    'ext' => 6
                );
                $this->db->insert('geopos_transactions', $data);
                $this->db->insert_id();
                $this->db->select('total,csd,pamnt');
                $this->db->from('geopos_stock_r');
                $this->db->where('id', $tid);
                $query = $this->db->get();
                $invresult = $query->row();
                $totalrm = $invresult->total - $invresult->pamnt;
                if ($totalrm > $amount) {
                    $this->db->set('pmethod', $pmethod);
                    $this->db->set('pamnt', "pamnt+$amount", FALSE);
                    $this->db->set('status', 'partial');
                    $this->db->where('id', $tid);
                    $this->db->update('geopos_stock_r');
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
                    $this->db->set('status', 'accepted');
                    $this->db->where('id', $tid);
                    $this->db->update('geopos_stock_r');
                    //acount update
                    $this->db->set('lastbal', "lastbal-$amount", FALSE);
                    $this->db->where('id', $acid);
                    $this->db->update('geopos_accounts');
                    $totalrm = 0;
                    $status = 'Accepted';
                    $paid_amount = $amount;
                }
            }


            $activitym = "<tr><td>" . substr($paydate, 0, 10) . "</td><td>$pmethod</td><td>$amount</td><td>$note</td></tr>";
            echo json_encode(array('status' => 'Success', 'message' =>
            $this->lang->line('Transaction has been added'), 'pstatus' => $this->lang->line($status), 'activity' => $activitym, 'amt' => $totalrm, 'ttlpaid' => $paid_amount));
        }
    }
}

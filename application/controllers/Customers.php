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

class Customers extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library("Aauth");
        if (!$this->aauth->is_loggedin()) {
            redirect('/user/', 'refresh');
        }
        if (!$this->aauth->permission_new(null, 'customerAccess')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $this->load->model('customers_model', 'customers');
        $this->load->model('invoices_model', 'invoices');
        $this->load->library("Custom");
        $this->load->library("dpdf");
        $this->load->library('XLSXWriter');
        $this->load->helper('download');

        $this->li_a = 'crm';
        $this->load->model('dashboard_model');
    }

    public function check_limits($custid)
    {
        $is_limit_okay = $this->customers->check_limit_values($custid);

        // Use $is_limit_okay to perform further actions
        if ($is_limit_okay) {
            echo "Limits are okay.";
        } else {
            echo "Limits exceeded.";
        }
    }

    public function index()
    {
        $head['usernm'] = $this->aauth->get_user()->username;
        $data['ttlBalance'] = $this->dashboard_model->CustomerBalance();
        // var_dump($data['limit_values']); die();
        $head['title'] = 'Customers';
        $this->load->view('fixed/header', $head);
        $this->load->view('customers/clist', $data);
        $this->load->view('fixed/footer');
    }
    public function receipt()
    {
        if (!$this->aauth->permission_new(null, 'customerReceipt')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $head['title'] = "Manage Receipt";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $data['list'] = $this->invoices->get_rec_datatables();
        $this->load->view('customers/invoices_rec', $data);
        $this->load->view('fixed/footer');
    }

    public function customer_report()
    {
        $head['usernm'] = $this->aauth->get_user()->username;
        $head['title'] = 'Customers';
        $this->load->view('fixed/header', $head);
        $this->load->view('customers/customer_report');
        $this->load->view('fixed/footer');
    }

    public function customer_ClosingBalance_report()
    {
        $head['usernm'] = $this->aauth->get_user()->username;
        $head['title'] = 'Closing Balance';
        $this->load->view('fixed/header', $head);
        $this->load->view('customers/customer_ClosingBalance_report');
        $this->load->view('fixed/footer');
    }


    public function customer_balance()
    {
        $head['title'] = "Customer Balance Report";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $this->load->view('customers/balance');
        $this->load->view('fixed/footer');
    }

    public function transactions_credit_notes()
    {
        if (!$this->aauth->permission_new(null, 'salesCreditNote')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $custid = $this->input->get('id');
        $data['details'] = $this->customers->details($custid);
        $data['money'] = $this->customers->money_details($custid);
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->db->select('credit_amount');
        $this->db->from('geopos_customers');

        $this->db->where('id', $custid);
        $this->db->where('credit_used', 0);
        $query = $this->db->get();
        $pr_c = $query->row_array();
        $data['cust_amt'] = (float)$pr_c['credit_amount'];

        $head['title'] = 'View Customer Credit Notes';

        $this->load->view('fixed/header', $head);
        $this->load->view('customers/creditNotes', $data);
        $this->load->view('fixed/footer');
    }

    public function transactions_advance_payments()
    {
        if (!$this->aauth->permission_new(null, 'accountsAdvancePayment')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $custid = $this->input->get('id');
        $data['details'] = $this->customers->details($custid);
        $data['money'] = $this->customers->money_details($custid);
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->db->select('amount');
        $this->db->from('advance_payment');

        $this->db->where('payerid', $custid);
        $query = $this->db->get();
        $pr_c = $query->row_array();
        $data['cust_amt'] = $pr_c ? (float)$pr_c['amount'] : 0.0;

        $head['title'] = 'View Customer Advance Payments';

        $this->load->view('fixed/header', $head);
        $this->load->view('customers/Advance_Payments', $data);
        $this->load->view('fixed/footer');
    }

    public function creditnotes_translist()
    {
        if (!$this->aauth->permission_new(null, 'salesCreditNote')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $cid = $this->input->post('cid');
        $list = $this->customers->trans_table($cid);

        $data = array();

        $sql = "select id,invoicedate, total from geopos_credit_notes where csd=$cid  order by id asc";
        //  $sql="select sum(credit) as credit,sum(debit) as debit,id from geopos_transactions where payerid=$cid group by created order by date desc";

        //$sql="select credit,debit,id from geopos_transactions where payerid=$cid order by id asc";
        $query = $this->db->query($sql);
        $response = $query->result_array();
        // Apply global search filter
        $search = $this->input->post('search');
        $term = isset($search['value']) ? trim($search['value']) : '';
        if ($term !== '') {
            $lower = mb_strtolower($term);
            $response = array_values(array_filter($response, function ($row) use ($lower) {
                return (
                    (isset($row['id']) && stripos((string)$row['id'], $lower) !== false) ||
                    (isset($row['invoicedate']) && stripos((string)$row['invoicedate'], $lower) !== false) ||
                    (isset($row['total']) && stripos((string)$row['total'], $lower) !== false)
                );
            }));
        }
        $recordsFiltered = count($response);
        // DataTables pagination
        $start = (int)$this->input->post('start');
        $length = (int)$this->input->post('length');
        $balance_array;
        $balance = 0;
        $no = $this->input->post('start');
        // echo "<pre>";
        // var_dump($list); exit;

        $pageRows = ($length && $length != -1) ? array_slice($response, $start, $length) : $response;

        foreach ($pageRows as $prd) {
            // echo "<pre>";
            //     var_dump($prd); exit;

            $this->db->select('id,tid,invoicedate');
            $this->db->from('geopos_stock_r');
            $this->db->where('tid', $prd['inv_id']);
            $this->db->where('csd', $prd['payerid']);
            $res = $this->db->get()->result_array();
            // var_dump($this->db->last_query());exit();
            $idss = $res[0]['id'];
            $ret_id = $res[0]['tid'];
            $ret_invoicedate = $res[0]['invoicedate'];
            $no++;
            $row = array();
            $pid = $prd["id"];
            $lbl = '<a href="' . base_url() . 'stockreturn/print_creditnote_invoice?id=' . $prd["id"] . '" target="_blank">' . $prd["id"] . '</a>';
            $row[] = $lbl;
            //   if($prd["debit"]<>"0.00"){
            //         $date =  $prd["paymt_date"];

            // }
            // else{
            //     $date =  $prd["date"];
            // }
            $row[] = $prd["invoicedate"];
            $row[] = $prd["total"];

            // Add description column
            $description = 'Credit Note #' . $prd["id"];
            $row[] = $description;

            $data[] = $row;
        }
        $output = array(
            "draw" => $this->input->post('draw'),
            "recordsTotal" => $this->customers->credit_notes_count_all($cid),
            "recordsFiltered" => $recordsFiltered,
            "data" => $data,
        );
        //output to json format
        echo json_encode($output);
    }


    public function advancepaymnets_translist()
    {
        if (!$this->aauth->permission_new(null, 'accountsAdvancePayment')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $cid = $this->input->post('cid');
        $list = $this->customers->trans_table($cid);

        $data = array();

        $sql = "select id,date, debit from geopos_transactions where payerid=$cid and is_advance_payment=1 order by id asc";
        $query = $this->db->query($sql);
        $response = $query->result_array();
        // Apply global search filter
        $search = $this->input->post('search');
        $term = isset($search['value']) ? trim($search['value']) : '';
        if ($term !== '') {
            $lower = mb_strtolower($term);
            $response = array_values(array_filter($response, function ($row) use ($lower) {
                return (
                    (isset($row['id']) && stripos((string)$row['id'], $lower) !== false) ||
                    (isset($row['date']) && stripos((string)$row['date'], $lower) !== false) ||
                    (isset($row['debit']) && stripos((string)$row['debit'], $lower) !== false)
                );
            }));
        }
        $recordsFiltered = count($response);
        // DataTables pagination
        $start = (int)$this->input->post('start');
        $length = (int)$this->input->post('length');
        $balance_array;
        $balance = 0;
        $no = $this->input->post('start');

        $pageRows = ($length && $length != -1) ? array_slice($response, $start, $length) : $response;

        foreach ($pageRows as $prd) {

            $this->db->select('id,tid,invoicedate');
            $this->db->from('geopos_stock_r');
            $this->db->where('tid', $prd['inv_id']);
            $this->db->where('csd', $prd['payerid']);
            $res = $this->db->get()->result_array();
            // var_dump($this->db->last_query());exit();
            $idss = $res[0]['id'];
            $ret_id = $res[0]['tid'];
            $ret_invoicedate = $res[0]['invoicedate'];
            $no++;
            $row = array();
            $pid = $prd["id"];
            $row[] = $prd["id"];
            $row[] = $prd["date"];
            $row[] = $prd["debit"];
            if ($prd["note"] == 'Sale Receipt') {

                $lbl =  $prd["note"] . ' -   ' . $prd["paymt_method"];
            } else {
                if (strpos($prd["note"], 'Stock Return Invoice') !== false && $idss != "") {

                    $lbl =  '<a href="' . base_url() . 'stockreturn/printinvoice?id=' . $idss . '" target="_blank">' . $prd["note"] . '</a>';
                } else if (strpos($prd["note"], 'Invoice #') !== false) {
                    $lbl =  '<a href="' . base_url() . 'invoices/printinvoice?id=' . $prd["tid"] . '" target="_blank">' . $prd["note"] . '</a>';

                    //   }

                } else {
                    $lbl =  $prd["note"];
                }
            }

            $data[] = $row;
        }
        $output = array(
            "draw" => $this->input->post('draw'),
            "recordsTotal" => $this->customers->advance_payments_count_all($cid),
            "recordsFiltered" => $recordsFiltered,
            "data" => $data,
        );
        //output to json format
        echo json_encode($output);
    }


    public function create()
    {
        if (!$this->aauth->permission_new(null, 'customerNewCustomer')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $this->load->library("Common");
        $data['langs'] = $this->common->languages();
        $data['customergrouplist'] = $this->customers->group_list();
        $head['usernm'] = $this->aauth->get_user()->username;
        $data['custom_fields'] = $this->custom->add_fields(1);
        $data['limit_fields_create'] = $this->customers->view_limit_fields();
        $head['title'] = 'Create Customer';
        $this->load->view('fixed/header', $head);
        $this->load->view('customers/create', $data);
        $this->load->view('fixed/footer');
    }
    public function customersList()
    {
        $this->load->library("Common");
        $data['langs'] = $this->common->languages();
        $data['customergrouplist'] = $this->customers->group_list();
        $head['usernm'] = $this->aauth->get_user()->username;
        $data['custom_fields'] = $this->custom->add_fields(1);
        $head['title'] = 'Customers';

        $sql = ' select * from geopos_customers';
        $query = $this->db->query($sql);
        $data['customers'] = $response = $query->result_array();

        foreach ($data['customers'] as $key => $value) {
            $custid = $value["id"];
            $sql = 'SELECT sum(total)-sum(pamnt) as "cust_balance" FROM geopos_invoices WHERE status="due" and csd="' . $custid . '" or status="partial" and csd="' . $custid . '"';
            $query = $this->db->query($sql);
            $response = $query->result_array();
            $cust_balance_inv = $response[0]['cust_balance'];
            $data['customers'][$key]['balance'] = $cust_balance_inv;
        }


        $this->load->view('fixed/header', $head);
        $this->load->view('customers/customers_list', $data);
        $this->load->view('fixed/footer');
    }
    public function view()
    {
        if (!$this->aauth->permission_new(null, 'customerViewCustomer')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $custid = $this->input->get('id');
        $data['details'] = $this->customers->details($custid);
        $sql = 'SELECT sum(total)-sum(pamnt) as "cust_balance" FROM geopos_invoices WHERE csd="' . $custid . '" and status IN("due","partial")';
        $query = $this->db->query($sql);
        $response = $query->result_array();
        $data['details']['balance'] = !empty($response) ? $response[0]['cust_balance'] : 0;
        $data['customergroup'] = $this->customers->group_info($data['details']['gid']);
        $data['money'] = $this->customers->money_details($custid);
        $data['due'] = $this->customers->due_details($custid);
        $head['usernm'] = $this->aauth->get_user()->username;
        $data['activity'] = $this->customers->activity($custid);
        $data['custom_fields'] = $this->custom->view_fields_data($custid, 1);
        $data['is_limit_exceeded'] = $this->customers->check_limit_values($custid);
        // var_dump($data['is_limit_exceeded']); die();
        $head['title'] = 'View Customer';
        $this->load->view('fixed/header', $head);
        if ($data['details']['id']) $this->load->view('customers/view', $data);
        $this->load->view('fixed/footer');
    }

    // AJAX method to load content only
    public function ajax_content()
    {
        $action = $this->input->get('action');
        $custid = $this->input->get('id');

        if (!$custid) {
            echo '<div class="alert alert-danger">Customer ID is required</div>';
            return;
        }

        $data['details'] = $this->customers->details($custid);
        $data['id'] = $custid;
        $data['is_ajax'] = true; // Flag to indicate this is an AJAX request

        switch ($action) {
            case 'invoices':
                if (!$this->aauth->permission_new(null, 'customerActivities')) {
                    echo '<div class="alert alert-danger">Insufficient permissions</div>';
                    return;
                }
                $data['money'] = $this->customers->money_details($custid);
                $this->load->view('customers/invoices', $data);
                break;

            case 'transactions':
                if (!$this->aauth->permission_new(null, 'customerActivities')) {
                    echo '<div class="alert alert-danger">Insufficient permissions</div>';
                    return;
                }
                $data['money'] = $this->customers->money_details($custid);
                $this->load->view('customers/transactions', $data);
                break;

            case 'credit_notes':
                if (!$this->aauth->permission_new(null, 'customerActivities')) {
                    echo '<div class="alert alert-danger">Insufficient permissions</div>';
                    return;
                }
                $this->load->view('customers/creditNotes', $data);
                break;

            case 'advance_payments':
                if (!$this->aauth->permission_new(null, 'customerActivities')) {
                    echo '<div class="alert alert-danger">Insufficient permissions</div>';
                    return;
                }
                $this->load->view('customers/Advance_Payments', $data);
                break;

            case 'statement':
                if (!$this->aauth->permission_new(null, 'customerActivities')) {
                    echo '<div class="alert alert-danger">Insufficient permissions</div>';
                    return;
                }
                $this->load->view('customers/statement', $data);
                break;

            case 'vatreport':
                if (!$this->aauth->permission_new(null, 'customerActivities')) {
                    echo '<div class="alert alert-danger">Insufficient permissions</div>';
                    return;
                }
                $this->load->view('customers/vatreport', $data);
                break;

            default:
                echo '<div class="alert alert-danger">Invalid action</div>';
        }
    }

    // AJAX method for customer view details
    public function ajax_view_details()
    {
        if (!$this->aauth->permission_new(null, 'customerViewCustomer')) {
            echo '<div class="alert alert-danger">Sorry! You have insufficient permissions to access this section</div>';
            return;
        }

        $custid = $this->input->post('id');
        $data['details'] = $this->customers->details($custid);
        $data['customergroup'] = $this->customers->group_info($data['details']['gid']);
        $data['money'] = $this->customers->money_details($custid);
        $data['custom_fields'] = $this->custom->view_fields_data($custid, 1);

        $this->load->view('customers/ajax_view_details', $data);
    }

    // AJAX method for customer invoices
    public function ajax_invoices()
    {
        if (!$this->aauth->permission_new(null, 'customerActivities')) {
            echo '<div class="alert alert-danger">Sorry! You have insufficient permissions to access this section</div>';
            return;
        }

        $custid = $this->input->post('id');
        $data['details'] = $this->customers->details($custid);
        $data['money'] = $this->customers->money_details($custid);

        $this->load->view('customers/ajax_invoices', $data);
    }

    // AJAX method for customer transactions
    public function ajax_transactions()
    {
        if (!$this->aauth->permission_new(null, 'customerActivities')) {
            echo '<div class="alert alert-danger">Sorry! You have insufficient permissions to access this section</div>';
            return;
        }

        $custid = $this->input->post('id');
        $data['details'] = $this->customers->details($custid);
        $data['money'] = $this->customers->money_details($custid);

        $this->load->view('customers/ajax_transactions', $data);
    }

    // AJAX method for customer credit notes
    public function ajax_credit_notes()
    {
        if (!$this->aauth->permission_new(null, 'salesCreditNote')) {
            echo '<div class="alert alert-danger">Sorry! You have insufficient permissions to access this section</div>';
            return;
        }

        $custid = $this->input->post('id');
        $data['details'] = $this->customers->details($custid);
        $data['money'] = $this->customers->money_details($custid);

        $this->db->select('credit_amount');
        $this->db->from('geopos_customers');
        $this->db->where('id', $custid);
        $this->db->where('credit_used', 0);
        $query = $this->db->get();
        $pr_c = $query->row_array();
        $data['cust_amt'] = $pr_c ? (float)$pr_c['credit_amount'] : 0.0;

        $this->load->view('customers/ajax_credit_notes', $data);
    }

    // AJAX method for customer advance payments
    public function ajax_advance_payments()
    {
        if (!$this->aauth->permission_new(null, 'accountsAdvancePayment')) {
            echo '<div class="alert alert-danger">Sorry! You have insufficient permissions to access this section</div>';
            return;
        }

        $custid = $this->input->post('id');
        $data['details'] = $this->customers->details($custid);
        $data['money'] = $this->customers->money_details($custid);

        $this->db->select('amount');
        $this->db->from('advance_payment');
        $this->db->where('payerid', $custid);
        $query = $this->db->get();
        $pr_c = $query->row_array();
        $data['cust_amt'] = $pr_c ? (float)$pr_c['amount'] : 0.0;

        $this->load->view('customers/ajax_advance_payments', $data);
    }

    // AJAX method for customer statement
    public function ajax_statement()
    {
        if (!$this->aauth->permission_new(null, 'customerActivities')) {
            echo '<div class="alert alert-danger">Sorry! You have insufficient permissions to access this section</div>';
            return;
        }

        $custid = $this->input->post('id');
        $data['details'] = $this->customers->details($custid);

        $this->load->view('customers/ajax_statement', $data);
    }

    // AJAX method for customer VAT report
    public function ajax_vatreport()
    {
        if (!$this->aauth->permission_new(null, 'customerActivities')) {
            echo '<div class="alert alert-danger">Sorry! You have insufficient permissions to access this section</div>';
            return;
        }

        $custid = $this->input->post('id');
        $data['details'] = $this->customers->details($custid);

        $this->load->view('customers/ajax_vatreport', $data);
    }


    //  grouyp custmer pricing list start
    public function group_list_price()
    {
        if (!$this->aauth->permission_new(null, 'customerGroupCustomerPrice')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $head['usernm'] = $this->aauth->get_user()->username;
        if ($this->uri->segment(3)) {
            $cust_id = $this->uri->segment(3);
            $sql2 = "SELECT company FROM `geopos_customers` where id=" . $cust_id;
            $query2 = $this->db->query($sql2);
            $response2 = $query2->result_array();
            $head['cust_name'] = $response2[0]['company'];
        }
        $this->db->select('geopos_customers.id, geopos_customers.name');
        $this->db->from('geopos_customers');

        $query = $this->db->get();
        $head['customers'] = $query->result_array();

        $this->db->select('pid, product_name, product_code, product_price, fproduct_price');
        $this->db->from('geopos_products');  // Assuming 'geopos_products' is the table for products
        $query = $this->db->get();
        $head['products'] = $query->result_array();


        $head['title'] = 'Customers';
        $this->load->view('fixed/header', $head);
        $this->load->view('customers/cgplist');
        $this->load->view('fixed/footer');
    }


    public function cgp_list()
    {
        // Get POST data for DataTables
        $start = $this->input->post('start'); // Start index for pagination
        $length = $this->input->post('length'); // Number of records per page
        $search = $this->input->post('search')['value']; // Search term
        $order = $this->input->post('order'); // Sorting order
        $order_column = $order[0]['column']; // Column index for sorting
        $order_dir = $order[0]['dir']; // Sorting direction (asc/desc)

        // Define column mapping for sorting
        $columns = [
            0 => 'c.name', // Customer Name
            // 1 => 'cgp.group_title', // Group Title
            1 => 'cgp.productcode', // Product Code
            2 => 'cgp.description', // Product Description
            3 => 'cgp.costprice', // Purchase Price
            4 => 'cgp.salesprice', // Sale Price
            5 => 'cgp.profitmargin' // Profit Margin
        ];

        // Set default sorting column and direction
        $order_by = $columns[$order_column] ?? 'cgp.id';
        $order_dir = in_array(strtoupper($order_dir), ['ASC', 'DESC']) ? $order_dir : 'ASC';

        // Fetch data from the database
        $this->db->select('cgp.id, c.name AS customer_name, cgp.group_title, cgp.productcode, cgp.description, cgp.costprice, cgp.salesprice, cgp.profitmargin');
        $this->db->from('geopos_customer_pricing cgp');
        $this->db->join('geopos_customers c', 'cgp.custid = c.id', 'left');

        // Apply search filter
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('c.name', $search);
            $this->db->or_like('cgp.group_title', $search);
            $this->db->or_like('cgp.productcode', $search);
            $this->db->group_end();
        }

        // Apply ordering and pagination
        $this->db->order_by($order_by, $order_dir);
        $this->db->limit($length, $start);

        // Get the filtered data
        $query = $this->db->get();
        $data = $query->result_array();

        // Get total records without any filter (for pagination display)
        $this->db->select('COUNT(*) as total_records');
        $this->db->from('geopos_customer_pricing cgp');
        $this->db->join('geopos_customers c', 'cgp.custid = c.id', 'left');
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('c.name', $search);
            $this->db->or_like('cgp.group_title', $search);
            $this->db->or_like('cgp.productcode', $search);
            $this->db->group_end();
        }

        $count_query = $this->db->get();
        $total_records = $count_query->row()->total_records;

        // Group the data by group_title
        $groupedData = [];
        foreach ($data as $row) {
            $groupedData[$row['group_title']][] = $row;
        }

        // Prepare the result for DataTables
        $result = [];
        foreach ($groupedData as $groupTitle => $groupRows) {
            foreach ($groupRows as $row) {
                $result[] = $row;
            }
        }

        // Prepare response for DataTables
        $response = [
            'draw' => intval($this->input->post('draw')), // Draw counter
            'recordsTotal' => $total_records, // Total records without filtering
            'recordsFiltered' => $total_records, // Total records after filtering (same as total in this case)
            'data' => $result // Data for the table
        ];

        // Return JSON response
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }





    public function save_pricing()
    {
        // Set JSON header
        header('Content-Type: application/json');

        // Get POST data
        $customers = $this->input->post('customers'); // Array of customer IDs
        $pricing_data = $this->input->post('pricing_data'); // Array of product pricing data
        $group_title = $this->input->post('group_title'); // Group title

        // CSRF Token
        $csrfName = $this->security->get_csrf_token_name();
        $csrfHash = $this->security->get_csrf_hash();

        // Validate Input Data
        if (empty($customers) || empty($pricing_data) || empty($group_title)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid data provided. Please ensure all fields are filled.',
                $csrfName => $csrfHash
            ]);
            exit;
        }

        // Begin Transaction
        $this->db->trans_start();
        $new_data_added = false; // Flag to track new insertions

        foreach ($customers as $customer_id) {
            // Fetch customer name from `geopos_customers`
            $customer = $this->customers->get_customer_by_id($customer_id);
            if (!$customer) {
                continue; // Skip invalid customers
            }

            foreach ($pricing_data as $product) {
                $product_id = $product['product_id'] ?? null;
                $purchase_price = isset($product['purchase_price']) ? (float) $product['purchase_price'] : 0;
                $sale_price = isset($product['sale_price']) ? (float) $product['sale_price'] : 0;

                if (!$product_id || $sale_price <= 0) {
                    continue; // Skip invalid products
                }

                // Fetch product details from `geopos_products`
                $product_details = $this->customers->get_product_by_id($product_id);
                if (!$product_details) {
                    continue; // Skip if product not found
                }

                // Calculate profit margin
                $profit_margin = ($sale_price > 0) ? (($sale_price - $purchase_price) / $sale_price) * 100 : 0;

                // Prepare data for insert/update
                $pricing_entry = [
                    'custid' => $customer_id,
                    'group_title' => $group_title,
                    'account' => $customer['name'],
                    'productid' => $product_id,
                    'productcode' => $product_details['product_code'],
                    'description' => $product_details['product_des'],
                    'salesprice' => $sale_price,
                    'costprice' => $purchase_price,
                    'profitmargin' => round($profit_margin, 2) // Rounded to 2 decimal places
                ];

                // Check if entry already exists
                $existing = $this->db->get_where('geopos_customer_pricing', [
                    'custid' => $customer_id,
                    'productid' => $product_id
                ])->row_array();

                if ($existing) {
                    $this->db->where('custid', $customer_id)
                        ->where('productid', $product_id)
                        ->update('geopos_customer_pricing', $pricing_entry);

                    $new_data_added = true; // Mark update as valid change
                } else {
                    $this->db->insert('geopos_customer_pricing', $pricing_entry);
                    $new_data_added = true; // Mark new insert
                }
            }
        }

        // Commit or Rollback Transaction
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Database transaction failed. Please try again.',
                $csrfName => $csrfHash
            ]);
            exit;
        }

        // Return Response
        echo json_encode([
            'status' => $new_data_added ? 'success' : 'info',
            'message' => $new_data_added ? 'Pricing data saved successfully!' : 'No new data was added.',
            $csrfName => $csrfHash
        ]);
        exit;
    }





    //  grouyp custmer pricing list end

    public function list_price($cid = null)
    {
        $head['usernm'] = $this->aauth->get_user()->username;

        // Get customer ID from URL or segment
        $cust_id = $cid ?? $this->uri->segment(3);

        // Get customer company name if valid ID
        if (!empty($cust_id) && $cust_id != '0') {
            $query2 = $this->db->get_where('geopos_customers', ['id' => $cust_id]);
            $response2 = $query2->row_array();
            $head['cust_name'] = $response2['company'] ?? 'Unknown';
        } else {
            $head['cust_name'] = ''; // Set to empty if no customer selected
        }

        // Get list of customers
        $this->db->select('id, name, company');
        $head['customers'] = $this->db->get('geopos_customers')->result_array();

        $head['title'] = 'Customers';
        $data['cust_id'] = (!empty($cust_id) && $cust_id != '0') ? $cust_id : null; // Ensure null if '0' or not set

        $this->load->view('fixed/header', $head);
        $this->load->view('customers/cplist', $data); // Pass to view
        $this->load->view('fixed/footer');
    }

    public function loadprice_list()
    {
        // var_dump($this->input->post()); die();
        $no = $this->input->post('start');
        $selected_cid = $this->input->post('cid');
        $custid = $this->input->post('custid');

        // Combine both 'cid' and 'custid'
        $cid = [];

        if (!empty($selected_cid)) {
            $cid = $selected_cid; // Split comma-separated IDs into array
        } else {
            $cid[] = $custid; // Add custid to the array
        }
        $cid = array_filter(array_unique($cid));
        // Use the 'cid' array directly
        $list = $this->customers->get_cprice_datatables($cid);
        // dd($list);
        $recordsFiltered = $this->customers->count_filtered_price($cid);  // Pass the $cid for filtered count

        $data = array();
        foreach ($list as $customers) {
            $no++;
            $row = array();
            $row[] = $no;
            $row[] = $customers->customer_name;
            $row[] = $customers->customer_company;
            $row[] = $customers->productcode;
            $row[] = $customers->description;
            $row[] = amountExchange($customers->old_price);
            $row[] = amountExchange($customers->salesprice);
            $row[] = $customers->added_on;
            $data[] = $row;
        }

        // Prepare the output data
        $output = array(
            "draw" => $this->input->post('draw'),
            "recordsTotal" => $this->customers->count_all(),  // Get the total records without filtering
            "recordsFiltered" => $recordsFiltered,  // Number of records after filtering
            "data" => $data,
        );

        // Output the response as JSON
        echo json_encode($output);
    }


    public function vatreport()
    {
        $data['id'] = $this->input->get('id');
        $this->load->model('transactions_model');
        $data['details'] = $this->customers->details($data['id']);
        $head['title'] = "Account Statement";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $this->load->view('customers/vatreport', $data);
        $this->load->view('fixed/footer');
    }
    public function vatinvoice()
    {
        $cid = $this->input->post('customer');
        $sdate = date("Y-m-d", strtotime($this->input->post('sdate')));
        $edate = date("Y-m-d", strtotime($this->input->post('edate')));
        $sql = 'SELECT * FROM geopos_invoices WHERE csd="' . $cid . '" order by id desc limit 1';
        $query = $this->db->query($sql);
        $response = $query->result_array();
        $tid = $response[0]['id'];
        $data['id'] = $tid;
        $data['invoice'] = $this->invoices->invoice_details($tid, $this->limited);

        if ($data['invoice']['id']) $data['products'] = $this->invoices->invoice_products($tid);
        if ($data['invoice']['id']) $data['employee'] = $this->invoices->employee($data['invoice']['eid']);
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

        // $sql = ' select * from geopos_invoices where csd="' . $data["invoice"]["csd"] . '"  and invoicedate BETWEEN "' . $sdate . '" AND "' . $edate . '"';
        $sql = ' select * from geopos_invoices where csd="' . $data["invoice"]["csd"] . '"';
        $query = $this->db->query($sql);
        $response2 = $query->result_array();

        //var_dump($response2);exit();
        $i = 1;

        foreach ($response2 as $row) {
            $sql_ = ' select tax, total from geopos_credit_notes where csd="' . $data["invoice"]["csd"] . '"  and tid="' . $row["tid"] . '"';
            // $sql_ = ' select tax, total from geopos_credit_notes where csd="' . $data["invoice"]["csd"] . '"  and invoicedate BETWEEN "' . $sdate . '" AND "' . $edate . '" and tid="' . $row["tid"] . '"';
            $query = $this->db->query($sql_);
            $response3 = $query->result_array();
            if (isset($response3[0])) {
                $tax = -abs($response3[0]["tax"]);
            } else {
                $tax = $row["tax"];
            }
            $toal_vat_ += $tax;
            $toal_amt_ += $row["total"];

            $data_tr2 .= '
             <tr>
             <td>' . $i . '</td>
             <td>' . $row["tid"] . '</td>';
            if ($row["tax"] == null || $row["tax"] == "") {
                $data_tr2 .= '
             <td>0.00</td>
             ';
            } else {
                $data_tr2 .= '
             <td>' . $row["inv_type"] . '</td>
             <td>' . amountExchange($tax) . '</td>
             ';
            }
            $data_tr2 .= '<td>  ' . amountExchange($row["total"]) . '</td> <td>  ' . $row["invoicedate"] . '</td>
             
             </tr>';
            $i++;
        }

        $toal_vat = amountExchange($toal_vat_);
        $toal_amt = amountExchange($toal_amt_);
        // var_dump($data_tr2);exit();


        ini_set('memory_limit', '64M');

        $data_tr = "";









        $html = "
                <html>
                <head>
                    <style>
                        @page { margin: 120px 50px; }
                        #header{ position: fixed;  top: -90px;}
                        .left_header{float:left;}
                        .center_header{margin-left:130px;width:350px}
                        .right_header{position: fixed;  top: -90px;left:610px;}
                        .pagenum:after {content: counter(page)}
                        table{width:100%;}
                    </style>
                </head>
                <body>
                    <div id='header'>
                    <div class='left_header'>
                        <div><b>From:</b> $sdate</div>
                        <div><b>To:</b> $edate</div>
                    </div>
                    <div class='center_header'  style='font-size:18px;text-align:center;'>
                        <div>
                        <u><b> Cloud Billing Manager </b>  </u>
                        </div>
                        <div>
                            <b> VAT Report </b> 
                        </div>
                    </div >
                    <div class='right_header'>
                        <span class='pagenum'>Page:</span>
                    </div>
                    </div>
                <div style='width:100%;font-weight: bold;'>Company Name: <span style='font-weight: light;'> " . $data['invoice']['company'] . " </span> </div>
                <br>
                <table>
                <thead>
                    <tr>
                        <th>
                            Sr.No
                        </th>
                        <th>
                            Invoice
                        </th>
                        <th>
                            Invoice Type
                        </th>
                        <th>
                            VAT
                        </th>
                        
                        <th>
                            Amount
                        </th>
                        <th>
                            Date
                        </th>

                    </tr>
                </thead>
                $data_tr2
                <tr>
                <td></td>
                <td></td>
                <td></td>
                <td style='border-top:2px solid black;border-bottom:2px solid black'> $toal_vat</td>
                <td style='border-top:2px solid black;border-bottom:2px solid black'>$toal_amt</td>
                <td></td>

                </tr>
                </table>
                </body>
                </html>
                ";


        $file_name = "Balance-Sheet-" . $this->input->get('id');
        $this->dpdf->loadHtml($html);
        $this->dpdf->render();
        $output =  $this->dpdf->output();
        $file_location = 'userfiles/invoices/' . $file_name . '.pdf';
        file_put_contents($file_location, $output);
        $this->dpdf->stream("" . $file_name . ".pdf", array("Attachment" => 0));
    }



    public function load_list()
    {
        $no = $this->input->post('start');
        $list = $this->customers->get_datatables();
        // var_dump($limit); die();
        $data = array();
        
        if ($this->input->post('due')) {
            foreach ($list as $customers) {

                $no++;
                $row = array();
                $limit = $this->customers->check_limit_values($customers->id);
                $row[] = ' <input type="checkbox" name="cust[]" class="checkbox" value="' . $customers->id . '"> ';
                $row[] = ($this->aauth->permission_new(null, 'customerViewCustomer') ? '<a href="' . base_url('customers/view') . '?id=' . $customers->id . '">' . $customers->name . '</a>' : $customers->name);
                $row[] = amountExchange($customers->total - $customers->pamnt, 0, $this->aauth->get_user()->loc);
                $row[] = $customers->company;


                $sql = 'SELECT sum(total)-sum(pamnt) as "cust_balance" FROM geopos_invoices WHERE status="due" and csd="' . $customers->id . '" or status="partial" and csd="' . $customers->id . '"';
                $query = $this->db->query($sql);
                $response = $query->result_array();
                $cust_balance = !empty($response) ? $response[0]['cust_balance'] : 0;
                $row[] =  amountExchange($cust_balance);
                $row[] = $customers->phone;
                $custom_fields = $this->custom->view_fields_data($customers->id, 1, 1);
                $row[] = $custom_fields[0]["data"];
                $row[] = ($this->aauth->permission_new(null, 'customerViewCustomer') ? '<a href="' . base_url('customers/view') . '?id=' . $customers->id . '" class="btn btn-info btn-sm"><span class="fa fa-eye"></span>  ' . $this->lang->line('View') . '</a>' : '') . ' ' . ($this->aauth->permission_new(null, 'customerEditCustomer') ? '<a href="customers/edit?id=' . $customers->id . '" class="btn btn-primary btn-sm"><span class="fa fa-edit"></span>  ' . $this->lang->line('Edit') . '</a>' : '') . ' ' . ($this->aauth->permission_new(null, 'customerDeleteCustomer') ? '<a href="#" data-object-id="' . $customers->id . '" class="btn btn-danger btn-sm delete-object"><span class="fa fa-trash"></span>' . $this->lang->line('Delete') . '</a>' : '');
                $row[] = $limit;
                $data[] = $row;
            }
        } else {
            foreach ($list as $customers) {
                $no++;
                $row = array();
                $limit = $this->customers->check_limit_values($customers->id);
                $row[] =  ' <input type="checkbox" name="cust[]" class="checkbox" value="' . $customers->id . '"> ';
                $row[] = ($this->aauth->permission_new(null, 'customerViewCustomer') ? '<a href="' . base_url('customers/view') . '?id=' . $customers->id . '">' . $customers->name . '</a>' : $customers->name);
                $row[] = $customers->company;
                $row[] = $customers->address . ', ' . $customers->postbox;

                $sql2 = 'SELECT id, max(invoicedate) as invoicedate  from geopos_invoices where csd= "' . $customers->id . '"';
                $query2 = $this->db->query($sql2);
                $response2 = $query2->row_array();
                $last_invoicedate = $response2 ? $response2['invoicedate'] : null;

                $sql = 'SELECT sum(total)-sum(pamnt) as "cust_balance" FROM geopos_invoices WHERE status="due" and csd="' . $customers->id . '" or status="partial" and csd="' . $customers->id . '"';
                $query = $this->db->query($sql);
                $response = $query->result_array();
                $cust_balance = !empty($response) ? $response[0]['cust_balance'] : 0;



                // $row[] = amountExchange($cust_balance); 
                $row[] = amountExchange($cust_balance);
                $row[] = $customers->phone;
                $row[] = $last_invoicedate;
                //  $row[]=$new_id;
                $custom_fields = $this->custom->view_fields_data($customers->id, 1, 1);
                $row[] = $custom_fields[0]["data"];
                $row[] = ($this->aauth->permission_new(null, 'customerViewCustomer') ? '<a href="' . base_url('customers/view') . '?id=' . $customers->id . '" class="btn btn-info btn-sm"><span class="fa fa-eye"></span>  ' . $this->lang->line('View') . '</a>' : '') . ' ' . ($this->aauth->permission_new(null, 'customerEditCustomer') ? '<a href="customers/edit?id=' . $customers->id . '" class="btn btn-primary btn-sm"><span class="fa fa-edit"></span>  ' . $this->lang->line('Edit') . '</a>' : '') . ' ' . ($this->aauth->permission_new(null, 'customerDeleteCustomer') ? '<a href="#" data-object-id="' . $customers->id . '" class="btn btn-danger btn-sm delete-object"><span class="fa fa-trash"></span> ' . $this->lang->line('Delete') . '</a>' : '');
                $row[] = $limit;
                $data[] = $row;
            }
        }


        $output = array(
            "draw" => $this->input->post('draw'),
            "recordsTotal" => $this->customers->count_all(),
            "recordsFiltered" => $this->customers->count_filtered(),
            "data" => $data,
        );
        //output to json format
        echo json_encode($output);
    }


    //edit section
    public function edit()
    {
        if (!$this->aauth->permission_new(null, 'customerEditCustomer')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $this->load->library("Common");
        $pid = $this->input->get('id');
        $data['customer'] = $this->customers->details($pid);
        $data['customergroup'] = $this->customers->group_info($data['customer']['gid']);
        $data['customergrouplist'] = $this->customers->group_list();
        $head['usernm'] = $this->aauth->get_user()->username;
        $data['custom_fields'] = $this->custom->view_edit_fields($pid, 1);
        $data['limit_fields'] = $this->customers->view_edit_limit_fields($pid);
        // var_dump($data['limit_fields']); die();
        $head['title'] = 'Edit Customer';
        $data['langs'] = $this->common->languages();
        $this->load->view('fixed/header', $head);
        $this->load->view('customers/edit', $data);
        $this->load->view('fixed/footer');
    }


    public function addcustomer()
    {
        // Debugging the post data
        // var_dump($this->input->post()); die();

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
        $language = $this->input->post('language', true);
        $create_login = $this->input->post('c_login', true);
        $password = $this->input->post('password_c', true);
        $docid = $this->input->post('docid', true);
        $custom = $this->input->post('c_field', true);
        $discount = $this->input->post('discount', true);

        // Custom fields data
        $custom_fields_data = [];
        foreach ($this->input->post('limit') as $id => $limit) {
            if ($limit === 'balance') {
                $custom_fields_data[$id] = $limit . ':' . $this->input->post('balance_limit')[$id];
            } elseif ($limit === 'invoices') {
                $custom_fields_data[$id] = $limit . ':' . $this->input->post('invoices_limit')[$id];
            }
        }
        $custom_fields_data_json = json_encode($custom_fields_data);

        // Bank accounts
        $bank_ids = [];
        $bank_numbers = [];
        $bank_refs = [];

        foreach ($this->input->post() as $key => $value) {
            if (preg_match('/bank_id_(\d+)/', $key, $matches)) {
                $bank_ids[] = $value;
            } elseif (preg_match('/bank_number_(\d+)/', $key, $matches)) {
                $bank_numbers[] = $value;
            } elseif (preg_match('/bank_ref_(\d+)/', $key, $matches)) {
                $bank_refs[] = $value;
            }
        }

        // Convert bank accounts to JSON
        $bank_accounts = [
            'ids' => $bank_ids,
            'numbers' => $bank_numbers,
            'refs' => $bank_refs
        ];
        $bank_accounts_json = json_encode($bank_accounts);
        // var_dump($bank_accounts); die();
        // Call the add method of the customers model and pass all the data
        $this->customers->add(
            $name,
            $company,
            $phone,
            $email,
            $address,
            $city,
            $region,
            $country,
            $postbox,
            $customergroup,
            $taxid,
            $name_s,
            $phone_s,
            $email_s,
            $address_s,
            $city_s,
            $region_s,
            $country_s,
            $postbox_s,
            $language,
            $create_login,
            $password,
            $docid,
            $custom,
            $custom_fields_data_json,
            $discount,
            $bank_accounts_json
        );
    }

    function sendSelected()
    {
        if (!$this->aauth->permission_new(null, 'customerEditCustomer')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }

        if ($this->input->post('cust')) {
            $ids = $this->input->post('cust');

            $subject = $this->input->post('subject', true);
            $message = $this->input->post('text');
            $attachmenttrue = false;
            $attachment = '';
            $recipients = $this->customers->recipients($ids);
            $this->load->model('communication_model');
            $this->communication_model->group_email($recipients, $subject, $message, $attachmenttrue, $attachment);
        }
    }

    function sendSmsSelected()
    {
        if (!$this->aauth->permission_new(null, 'customerEditCustomer')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }

        if ($this->input->post('cust')) {
            $ids = $this->input->post('cust');
            $message = $this->input->post('message', true);
            $recipients = $this->customers->recipients($ids);
            $this->config->load('sms');
            $this->load->model('sms_model');
            foreach ($recipients as $row) {

                $this->sms_model->send_sms($row['phone'], $message);
            }
        }
    }

    public function editcustomer()
    {
        // Debugging the post data
        // var_dump($this->input->post()); die();

        if (!$this->aauth->permission_new(null, 'customerEditCustomer')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }

        $id = $this->input->post('id');
        $name = $this->input->post('name', true);
        $company = $this->input->post('company', true);
        $phone = $this->input->post('phone', true);
        $email = $this->input->post('email', true);
        $address = $this->input->post('address', true);
        $city = $this->input->post('city', true);
        $region = $this->input->post('region', true);
        $country = $this->input->post('country', true);
        $postbox = $this->input->post('postbox', true);
        $customergroup = $this->input->post('customergroup', true);
        $taxid = $this->input->post('taxid', true);
        $name_s = $this->input->post('name_s', true);
        $phone_s = $this->input->post('phone_s', true);
        $email_s = $this->input->post('email_s', true);
        $address_s = $this->input->post('address_s', true);
        $city_s = $this->input->post('city_s', true);
        $region_s = $this->input->post('region_s', true);
        $country_s = $this->input->post('country_s', true);
        $postbox_s = $this->input->post('postbox_s', true);
        $docid = $this->input->post('docid', true);
        $custom = $this->input->post('c_field', true);
        $language = $this->input->post('language', true);
        $discount = $this->input->post('discount', true);

        // Custom fields data
        $custom_fields_data = [];
        foreach ($this->input->post('limit') as $field_id => $limit) {
            if ($limit === 'balance') {
                $custom_fields_data[$field_id] = $limit . ':' . $this->input->post('balance_limit')[$field_id];
            } elseif ($limit === 'invoices') {
                $custom_fields_data[$field_id] = $limit . ':' . $this->input->post('invoices_limit')[$field_id];
            }
        }
        $custom_fields_data_json = json_encode($custom_fields_data);

        // Bank accounts data
        $bank_ids = [];
        $bank_numbers = [];
        $bank_refs = [];

        foreach ($this->input->post() as $key => $value) {
            if (preg_match('/bank_id_(\d+)/', $key, $matches)) {
                $bank_ids[] = $value;
            } elseif (preg_match('/bank_number_(\d+)/', $key, $matches)) {
                $bank_numbers[] = $value;
            } elseif (preg_match('/bank_ref_(\d+)/', $key, $matches)) {
                $bank_refs[] = $value;
            }
        }

        // Convert bank accounts to JSON
        $bank_accounts = [
            'ids' => $bank_ids,
            'numbers' => $bank_numbers,
            'refs' => $bank_refs
        ];
        $bank_accounts_json = json_encode($bank_accounts);

        // Call the edit method of the customers model and pass all the data
        if ($id) {
            $this->customers->edit(
                $id,
                $name,
                $company,
                $phone,
                $email,
                $address,
                $city,
                $region,
                $country,
                $postbox,
                $customergroup,
                $taxid,
                $name_s,
                $phone_s,
                $email_s,
                $address_s,
                $city_s,
                $region_s,
                $country_s,
                $postbox_s,
                $docid,
                $custom,
                $language,
                $discount,
                $custom_fields_data_json,
                $bank_accounts_json
            );
        }
    }

    public function changepassword()
    {
        if (!$this->aauth->permission_new(null, 'customerEditCustomer')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        if ($id = $this->input->post()) {
            $id = $this->input->post('id');
            $password = $this->input->post('password', true);
            if ($id) {
                $this->customers->changepassword($id, $password);
            }
        } else {
            $pid = $this->input->get('id');
            $data['customer'] = $this->customers->details($pid);
            $data['customergroup'] = $this->customers->group_info($pid);
            $data['customergrouplist'] = $this->customers->group_list();
            $head['usernm'] = $this->aauth->get_user()->username;
            $head['title'] = 'Edit Customer';
            $this->load->view('fixed/header', $head);
            $this->load->view('customers/edit_password', $data);
            $this->load->view('fixed/footer');
        }
    }


    /**
     * Delete customer and ALL related data
     * This method deletes:
     * - Customer record
     * - All invoices and invoice items
     * - All quotes and quote items
     * - All quotations and quotation items
     * - All transactions
     * - All notes
     * - All documents
     * - Customer pricing
     * - User accounts linked to customer
     * - Custom fields
     */
    private function delete_customer_complete($customer_id)
    {
        $customer_id = (int)$customer_id;
        if ($customer_id <= 0) {
            return false;
        }
        
        // Start transaction for data integrity
        $this->db->trans_start();
        
        try {
            // 1. Delete invoice items and invoices
            $this->db->select('id');
            $this->db->where('csd', $customer_id);
            $invoices = $this->db->get('geopos_invoices')->result();
            foreach ($invoices as $invoice) {
                $this->db->where('tid', $invoice->id);
                $this->db->delete('geopos_invoice_items');
            }
            $this->db->where('csd', $customer_id);
            $this->db->delete('geopos_invoices');
            
            // 2. Delete invoices before posting (if any)
            $this->db->select('id');
            $this->db->where('csd', $customer_id);
            $invoices_bfr = $this->db->get('geopos_invoices_bfr_post')->result();
            foreach ($invoices_bfr as $invoice) {
                $this->db->where('tid', $invoice->id);
                $this->db->delete('geopos_invoice_items');
            }
            $this->db->where('csd', $customer_id);
            $this->db->delete('geopos_invoices_bfr_post');
            
            // 3. Delete quotes and quote items
            $this->db->select('id');
            $this->db->where('csd', $customer_id);
            $quotes = $this->db->get('geopos_quotes')->result();
            foreach ($quotes as $quote) {
                $this->db->where('tid', $quote->id);
                $this->db->delete('geopos_quotes_items');
            }
            $this->db->where('csd', $customer_id);
            $this->db->delete('geopos_quotes');
            
            // 4. Delete quotations and quotation items
            $this->db->select('id');
            $this->db->where('csd', $customer_id);
            $quotations = $this->db->get('geopos_quotations')->result();
            foreach ($quotations as $quotation) {
                $this->db->where('tid', $quotation->id);
                $this->db->delete('geopos_quotation_items');
            }
            $this->db->where('csd', $customer_id);
            $this->db->delete('geopos_quotations');
            
            // 5. Delete all transactions
            $this->db->where('payerid', $customer_id);
            $this->db->delete('geopos_transactions');
            
            // 6. Delete customer pricing
            $this->db->where('custid', $customer_id);
            $this->db->delete('geopos_customer_pricing');
            
            // 7. Delete notes
            $this->db->where('fid', $customer_id);
            $this->db->where('rid', 1);
            $this->db->delete('geopos_notes');
            
            // 8. Delete documents and files
            $this->db->select('filename');
            $this->db->where('fid', $customer_id);
            $this->db->where('rid', 1);
            $documents = $this->db->get('geopos_documents')->result();
            foreach ($documents as $doc) {
                if (!empty($doc->filename)) {
                    @unlink(FCPATH . 'userfiles/documents/' . $doc->filename);
                }
            }
            $this->db->where('fid', $customer_id);
            $this->db->where('rid', 1);
            $this->db->delete('geopos_documents');
            
            // 9. Delete user accounts linked to customer
            $this->db->where('cid', $customer_id);
            $this->db->delete('users');
            
            // 10. Delete custom fields data (explicit deletion to ensure it works)
            // Delete from geopos_custom_data where rid = customer_id and module = 1 (customers)
            $this->db->where('rid', $customer_id);
            $this->db->where('module', 1);
            $this->db->delete('geopos_custom_data');
            
            // Also use the custom library method as backup
            if (isset($this->custom) && method_exists($this->custom, 'del_fields')) {
                $this->custom->del_fields($customer_id, 1);
            }
            
            // 11. Delete customer record (this also handles location restrictions)
            if ($this->aauth->get_user()->loc) {
                $this->db->where('id', $customer_id);
                $this->db->where('loc', $this->aauth->get_user()->loc);
                $this->db->delete('geopos_customers');
            } elseif (!BDATA) {
                $this->db->where('id', $customer_id);
                $this->db->where('loc', 0);
                $this->db->delete('geopos_customers');
            } else {
                $this->db->where('id', $customer_id);
                $this->db->delete('geopos_customers');
            }
            
            // Log the deletion
            if ($this->db->affected_rows() > 0) {
                $this->aauth->applog("[Client Deleted] ID " . $customer_id, $this->aauth->get_user()->username);
            }
            
            // Complete transaction
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                return false;
            }
            
            return true;
            
        } catch (Exception $e) {
            $this->db->trans_rollback();
            log_message('error', 'Customer deletion error: ' . $e->getMessage());
            return false;
        }
    }
    
    public function delete_i()
    {
        // Set JSON content type
        $this->output->set_content_type('application/json');
        
        if (!$this->aauth->permission_new(null, 'customerDeleteCustomer')) {
            echo json_encode(array('status' => 'Error', 'message' => 'Sorry! You have insufficient permissions to access this section'));
            return;
        }
        
        // Check for multiple customer deletion (cust[] array)
        $customers = $this->input->post('cust');
        if (!empty($customers) && is_array($customers)) {
            $deleted_count = 0;
            $error_count = 0;
            $has_invoices = array();
            
            foreach ($customers as $customer_id) {
                // Check if customer has invoices
                $this->db->select('COUNT(*) AS invoice_count');
                $this->db->from('geopos_invoices');
                $this->db->where('csd', $customer_id);
                $invoice_query = $this->db->get();
                $invoice_result = $invoice_query->row_array();
                $invoice_count = isset($invoice_result['invoice_count']) ? (int)$invoice_result['invoice_count'] : 0;
                
                if ($invoice_count > 0) {
                    $has_invoices[] = $customer_id;
                    $error_count++;
                } else {
                    if ($this->delete_customer_complete($customer_id)) {
                        $deleted_count++;
                    } else {
                        $error_count++;
                    }
                }
            }
            
            if (count($has_invoices) > 0) {
                $message = 'Cannot delete ' . count($has_invoices) . ' customer(s): They have invoices. Please delete invoices first.';
                if ($deleted_count > 0) {
                    $message = $deleted_count . ' customer(s) deleted successfully. ' . $message;
                }
                echo json_encode(array('status' => 'Error', 'message' => $message));
            } elseif ($deleted_count > 0) {
                $message = $deleted_count . ' customer(s) and all related data deleted successfully!';
                if ($error_count > 0) {
                    $message .= ' (' . $error_count . ' failed)';
                }
                echo json_encode(array('status' => 'Success', 'message' => $message));
            } else {
                echo json_encode(array('status' => 'Error', 'message' => 'Failed to delete selected customers!'));
            }
            return;
        }
        
        // Check for single customer deletion
        $id = $this->input->post('deleteid');
        if (!empty($id) && $id > 0) {
            // Check if customer has invoices
            $this->db->select('COUNT(*) AS invoice_count');
            $this->db->from('geopos_invoices');
            $this->db->where('csd', $id);
            $invoice_query = $this->db->get();
            $invoice_result = $invoice_query->row_array();
            $invoice_count = isset($invoice_result['invoice_count']) ? (int)$invoice_result['invoice_count'] : 0;
            
            if ($invoice_count > 0) {
                echo json_encode(array('status' => 'Error', 'message' => 'Cannot delete customer: ' . $invoice_count . ' invoice(s) exist. Please delete all invoices first.'));
                return;
            }
            
            if ($this->delete_customer_complete($id)) {
                echo json_encode(array('status' => 'Success', 'message' => 'Customer and all related data deleted successfully!'));
            } else {
                echo json_encode(array('status' => 'Error', 'message' => 'Failed to delete customer!'));
            }
            return;
        }
        
        // No valid input provided
        echo json_encode(array('status' => 'Error', 'message' => 'Invalid request! No customer ID provided.'));
    }

    public function displaypic()
    {
        if (!$this->aauth->permission_new(null, 'customerEditCustomer')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $id = $this->input->get('id');
        $this->load->library("uploadhandler", array(
            'accept_file_types' => '/\.(gif|jpe?g|png)$/i',
            'upload_dir' => FCPATH . 'userfiles/customers/'
        ));
        $img = (string)$this->uploadhandler->filenaam();
        if ($img != '') {
            $this->customers->editpicture($id, $img);
        }
    }

    public function activities()
    {
        if (!$this->aauth->permission_new(null, 'customerActivities')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $head['usernm'] = $this->aauth->get_user()->username;
        $head['title'] = 'Customers Activities';
        $this->load->view('fixed/header', $head);
        $this->load->view('customers/activities', $data);
        $this->load->view('fixed/footer');
    }
    public function reconcile()
    {

        $head['usernm'] = $this->aauth->get_user()->username;
        $data['ttlBalance'] = $this->dashboard_model->CustomerBalance();
        $head['title'] = 'Bank Reconcile';
        $this->load->view('fixed/header', $head);
        $this->load->view('customers/reconcile', $data);
        $this->load->view('fixed/footer');
    }

    public function activitylist_()
    {
        $no = $this->input->post('start');
        $list = $this->customers->get_datatables();
        // var_dump($limit); die();
        $data = array();
        if ($this->input->post('due')) {
            foreach ($list as $customers) {

                $no++;
                $row = array();
                $limit = $this->customers->check_limit_values($customers->id);
                // $row[] = ' <input type="checkbox" name="cust[]" class="checkbox" value="' . $customers->id . '"> ';
                $row[] = $customers->name;
                $row[] = amountExchange($customers->total - $customers->pamnt, 0, $this->aauth->get_user()->loc);
                $row[] = $customers->company;


                $sql = 'SELECT sum(total)-sum(pamnt) as "cust_balance" FROM geopos_invoices WHERE status="due" and csd="' . $customers->id . '" or status="partial" and csd="' . $customers->id . '"';
                $query = $this->db->query($sql);
                $response = $query->result_array();
                $cust_balance = !empty($response) ? $response[0]['cust_balance'] : 0;
                $row[] =  amountExchange($cust_balance);
                // $row[] = $customers->phone;
                $custom_fields = $this->custom->view_fields_data($customers->id, 1, 1);
                $row[] = $custom_fields[0]["data"];
                // $row[] = '<a href="' . base_url('customers/view') . '?id=' . $customers->id . '" class="btn btn-info btn-sm"><span class="fa fa-eye"></span>  ' . $this->lang->line('View') . '</a> ' . (($this->aauth->premission(23)) ? '<a href="customers/edit?id=' . $customers->id . '" class="btn btn-primary btn-sm"><span class="fa fa-pencil"></span>  ' . $this->lang->line('Edit') . '</a>' : '') . (($this->aauth->premission(24)) ? '<a href="#" data-object-id="' . $customers->id . '" class="btn btn-danger btn-sm delete-object"><span class="fa fa-trash"></span></a>' : '');
                $row[] = $limit;
            }
            $data[] = $row;
        } else {
            foreach ($list as $customers) {
                $no++;
                $row = array();
                $limit = $this->customers->check_limit_values($customers->id);
                // $row[] =  ' <input type="checkbox" name="cust[]" class="checkbox" value="' . $customers->id . '"> ';
                $row[] = $customers->name;
                $row[] = $customers->company;
                $row[] = $customers->address . ', ' . $customers->postbox;

                $sql2 = 'SELECT id, max(invoicedate) as invoicedate  from geopos_invoices where csd= "' . $customers->id . '"';
                $query2 = $this->db->query($sql2);
                $response2 = $query2->row_array();
                $last_invoicedate = $response2 ? $response2['invoicedate'] : null;

                $sql = 'SELECT sum(total)-sum(pamnt) as "cust_balance" FROM geopos_invoices WHERE status="due" and csd="' . $customers->id . '" or status="partial" and csd="' . $customers->id . '"';
                $query = $this->db->query($sql);
                $response = $query->result_array();
                $cust_balance = !empty($response) ? $response[0]['cust_balance'] : 0;



                // $row[] = amountExchange($cust_balance); 
                $row[] = $cust_balance;
                // $row[] = $customers->phone;
                $row[] = $last_invoicedate;
                //  $row[]=$new_id;
                $custom_fields = $this->custom->view_fields_data($customers->id, 1, 1);
                $row[] = $custom_fields[0]["data"];
                // $row[] = '<a href="' . base_url('customers/view') . '?id=' . $customers->id . '" class="btn btn-info btn-sm"><span class="fa fa-eye"></span>  ' . $this->lang->line('View') . '</a> ' . (($this->aauth->premission(23)) ? '<a href="customers/edit?id=' . $customers->id . '" class="btn btn-primary btn-sm"><span class="fa fa-pencil"></span>  ' . $this->lang->line('Edit') . '</a>' : '') . (($this->aauth->premission(24)) ? '<a href="#" data-object-id="' . $customers->id . '" class="btn btn-danger btn-sm delete-object"><span class="fa fa-trash"></span></a>' : '');
                $row[] = $limit;
                $data[] = $row;
            }
        }


        $output = array(
            "draw" => $this->input->post('draw'),
            "recordsTotal" => $this->customers->count_all(),
            "recordsFiltered" => $this->customers->count_filtered(),
            "data" => $data,
        );
        //output to json format
        echo json_encode($output);
    }

    public function activitylist()
    {
        $start_date_obj = DateTime::createFromFormat('d-m-Y', $this->input->post('start'));
        $start_date = $start_date_obj->format('Y-m-d');
        $end_date_obj = DateTime::createFromFormat('d-m-Y', $this->input->post('end'));
        $end_date = $end_date_obj->format('Y-m-d');
        $list = $this->customers->activities_table($start_date, $end_date);
        $data = array();

        $limit = -1;
        if ($this->input->post('limit')) {
            $limit = $this->input->post('limit');
        }
        // var_dump($limit);exit;

        $credit = 0;
        $debit = 0;
        $balance = 0;

        // $sql="select sum(credit) as credit,sum(debit) as debit,id from geopos_transactions where group by created order by id asc";
        // $query = $this->db->query($sql);  
        // $response=$query->result_array();
        $no = $this->input->post('start');
        $i = 0;
        foreach ($list as $prd) {
            if ($i == $limit) {
                break;
            }
            try {
                $this->db->select('id');
                $this->db->from('geopos_stock_r');
                $this->db->where('tid', $prd['tid']);
                $res = $this->db->get()->result_array();
                $idss = $res[0]['id'];
                $no++;
                $row = array();
                $pid = $prd["id"];
                $row[] = $prd["id"];
                $row[] = $prd["date"];
                $row[] = $prd["tid"];
                $row[] = $prd["payer"];
                if ($prd["note"] == 'Sale Receipt') {
                    $lbl =  $prd["note"] . ' -   ' . $prd["paymt_method"];
                } else {
                    if (strpos($prd["note"], 'Stock Return Invoice') !== false) {
                        $lbl =  '<a href="' . base_url() . 'stockreturn/printinvoice?id=' . $idss . '" target="_blank">' . $prd["note"] . '</a>';
                    } else if (strpos($prd["note"], 'Invoice #') !== false) {
                        $lbl =  '<a href="' . base_url() . 'invoices/printinvoice?id=' . $prd["tid"] . '" target="_blank">' . $prd["note"] . '</a>';
                    } else {
                        $lbl =  $prd["note"];
                    }
                }
                $row[] = $lbl;

                $row[] = $prd["credit"];
                $row[] = $prd["debit"];

                $credit += $prd["credit"];
                $debit += $prd["debit"];
                $balance += $prd["credit"] - $prd["debit"];

                $data[] = $row;
                $i++;
            } catch (Exception $e) {
            }
        }
        $output = array(
            "draw" => $this->input->post('draw'),
            "recordsTotal" => $this->customers->count_all(),
            // "recordsFiltered" => $this->customers->trans_count_filtered($cid),
            "data" => $data,
            "credit" => round($credit, 2),
            "debit" => round($debit, 2),
            "balance" => round($balance, 2)
        );

        echo json_encode($output);
    }


    public function translist()
    {
        $company = $this->settings->company_details(1);
        // if (!$this->aauth->premission(8)) {
        if (!$this->aauth->permission_new(null, 'customerManageCustomer')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $cid = $this->input->post('cid');
        $list = $this->customers->trans_table($cid);
        $data = array();

        $sql = "select sum(credit) as credit,sum(debit) as debit,id from geopos_transactions where payerid=$cid group by created order by id asc";
        $query = $this->db->query($sql);
        $response = $query->result_array();
        $balance_array;
        $balance = 0;
        foreach ($response as $key => $value) {
            $balance += $value['credit'] - $value['debit'];
            $balance_array[$key]['balance'] = $balance;
            $balance_array[$key]['id'] = $value['id'];
        }
        $no = $this->input->post('start');
        // Apply global search across common fields in-memory (model aggregates by created)
        $search = $this->input->post('search');
        $term = isset($search['value']) ? trim($search['value']) : '';
        if ($term !== '') {
            // Build eid -> username map for searching by username as well as numeric id
            $eidUsernameMap = array();
            $eids = array();
            foreach ($list as $r) {
                if (isset($r['eid']) && $r['eid'] !== '' && $r['eid'] !== null) {
                    $eids[] = (string)$r['eid'];
                }
            }
            if (!empty($eids)) {
                $eids = array_values(array_unique($eids));
                $this->db->select('id, username');
                $this->db->from('geopos_employees');
                $this->db->where_in('id', $eids);
                $eidRows = $this->db->get()->result_array();
                foreach ($eidRows as $erow) {
                    $eidUsernameMap[(string)$erow['id']] = $erow['username'];
                }
            }

            $lower = mb_strtolower($term);
            $list = array_values(array_filter($list, function ($row) use ($lower, $eidUsernameMap) {
                $eidMatches = false;
                if (isset($row['eid'])) {
                    $eidStr = (string)$row['eid'];
                    $eidMatches = stripos($eidStr, $lower) !== false;
                    if (!$eidMatches && isset($eidUsernameMap[$eidStr])) {
                        $eidMatches = stripos(mb_strtolower($eidUsernameMap[$eidStr]), $lower) !== false;
                    }
                }
                return (
                    (isset($row['id']) && stripos((string)$row['id'], $lower) !== false) ||
                    (isset($row['date']) && stripos((string)$row['date'], $lower) !== false) ||
                    (isset($row['tid']) && stripos((string)$row['tid'], $lower) !== false) ||
                    (isset($row['note']) && stripos((string)$row['note'], $lower) !== false) ||
                    (isset($row['paymt_method']) && stripos((string)$row['paymt_method'], $lower) !== false) ||
                    (isset($row['inv_id']) && stripos((string)$row['inv_id'], $lower) !== false) ||
                    (isset($row['trans_ref']) && stripos((string)$row['trans_ref'], $lower) !== false) ||
                    $eidMatches ||
                    (isset($row['debit']) && stripos((string)$row['debit'], $lower) !== false) ||
                    (isset($row['credit']) && stripos((string)$row['credit'], $lower) !== false) ||
                    (isset($row['balance']) && stripos((string)$row['balance'], $lower) !== false)
                );
            }));
        }
        $recordsFiltered = count($list);
        // Apply server-side pagination to $list before building rows (balance already computed in model)
        $startIdx = (int)$this->input->post('start');
        $pageLen = (int)$this->input->post('length');
        $pagedList = ($pageLen && $pageLen != -1) ? array_slice($list, $startIdx, $pageLen) : $list;

        foreach ($pagedList as $prd) {
            $this->db->select('username');
            $this->db->from('geopos_employees');
            $this->db->where('id', $prd['eid']);
            $eidQuery = $this->db->get();
            $eid = $eidQuery->row()->username; // Fetch the 'username' field
            $this->db->select('id,tid,invoicedate');
            $this->db->from('geopos_stock_r');
            $this->db->where('csd', $prd['payerid']);
            $res = $this->db->get()->result_array();
            $no++;
            $row = array();
            $pid = $prd["id"];
            $row[] = $prd["id"];
            $row[] = $prd["date"];// $row[] = $prd["inv_id"];
            if ($prd["trans_ref"] === null) {
                $row[] = $prd["inv_id"];
            } else {
                $row[] = $prd["trans_ref"];
            }
            if ($prd["note"] == 'Sale Receipt') {

                $lbl =  $prd["note"] . ' -   ' . $prd["paymt_method"];
            } else if (strpos($prd["note"], 'Stock Return Invoice') !== false) {
                $idss = ''; // Ensure $idss is initialized
                foreach ($res as $sri) {
                    $idss = $sri['id'];
                    $ret_id = $sri['tid'];
                    $ret_invoicedate = $sri['invoicedate'];
                    $invoice_format = $company['invoice_format'] == 1 ? '' : $company['invoice_format'];
                    $lbl = '<a href="' . base_url() . 'stockreturn/printinvoice?id=' . $idss . '" target="_blank">' . $prd["note"] . '</a>';
                }
            } else if (strpos($prd["note"], 'Invoice #') !== false) {
                $lbl =  '<a href="' . base_url() . 'invoices/printinvoice?id=' . $prd["tid"] . '" target="_blank">' . $prd["note"] . '</a>';
            } else {
                $lbl =  $prd["note"];
            }
            $row[] = $lbl;
            $row[] = $eid;
            $row[] = $prd["credit"];
            $row[] = $prd["debit"];
            
            // Find the balance for this transaction
            $balance = 0;
            foreach ($balance_array as $bal_item) {
                if ($bal_item['id'] == $prd["id"]) {
                    $balance = $bal_item['balance'];
                    break;
                }
            }
            $row[] = formatDecimal($balance); // Add balance column
            
            $data[] = $row;
        }
        $output = array(
            "draw" => $this->input->post('draw'),
            "recordsTotal" => $this->customers->trans_count_all($cid),
            "recordsFiltered" => $recordsFiltered,
            "data" => $data,
        );
        //output to json format
        echo json_encode($output);
    }

    public function inv_list()
    {
        try {
            if (!$this->aauth->permission_new(null, 'customerActivities')) {
                echo json_encode(array('error' => 'Insufficient permissions'));
                return;
            }
            
            $cid = $this->input->post('cid');
            $tid = $this->input->post('tyd');

            if (!$cid) {
                echo json_encode(array('error' => 'Customer ID is required'));
                return;
            }

            $list = $this->customers->inv_datatables($cid, $tid);
            $data = array();
            $no = $this->input->post('start');
            foreach ($list as $invoices) {
                $no++;
                $row = array();
                $row[] = $invoices->tid;
                $row[] = $invoices->invoicedate;
                $row[] = amountExchange($invoices->total, 0, $this->aauth->get_user()->loc);

                $row[] = '<span class="st-' . $invoices->status . '">' . $this->lang->line(ucwords($invoices->status)) . '</span>';

                $row[] = ($this->aauth->permission_new(null, 'customerViewCustomer') ? '<a href="' . base_url("invoices/view?id=$invoices->id") . '" class="btn btn-success btn-xs" title="View Invoice"><i class="fa fa-file-text"></i> </a>' : '') . ' ' . ($this->aauth->permission_new(null, 'customerViewCustomer') ? '<a target="_blank" href="' . base_url("invoices/printinvoice?id=$invoices->id") . '&d=1" class="btn btn-info btn-xs"  title="Download"><span class="fa fa-download"></span></a>' : '') . ' ' . ($this->aauth->permission_new(null, 'customerDeleteCustomer') ? '<a href="#" data-object-id="' . $invoices->id . '" class="btn btn-danger btn-xs delete-object" title="Delete"><span class="fa fa-trash"></span></a>' : '');
                $data[] = $row;
            }
            $output = array(
                "draw" => $this->input->post('draw'),
                "recordsTotal" => $this->customers->inv_count_all($cid, $tid),
                "recordsFiltered" => $this->customers->inv_count_filtered($cid, $tid),
                "data" => $data,
            );
            //output to json format
            echo json_encode($output);
        } catch (Exception $e) {
            echo json_encode(array('error' => 'Server error: ' . $e->getMessage()));
        }
    }

    public function transactions()
    {
        if (!$this->aauth->permission_new(null, 'customerActivities')) {

            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $custid = $this->input->get('id');
        $data['details'] = $this->customers->details($custid);
        $data['money'] = $this->customers->money_details($custid);
        $head['usernm'] = $this->aauth->get_user()->username;
        $head['title'] = 'View Customer Transactions';
        $this->load->view('fixed/header', $head);
        $this->load->view('customers/transactions', $data);
        $this->load->view('fixed/footer');
    }

    public function invoices()
    {
        if (!$this->aauth->permission_new(null, 'customerActivities')) {

            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $custid = $this->input->get('id');
        $data['details'] = $this->customers->details($custid);
        $data['money'] = $this->customers->money_details($custid);
        $head['usernm'] = $this->aauth->get_user()->username;
        $head['title'] = 'View Customer Invoices';
        $this->load->view('fixed/header', $head);
        $this->load->view('customers/invoices', $data);
        $this->load->view('fixed/footer');
    }



    public function quotes()
    {
        if (!$this->aauth->permission_new(null, 'customerActivities')) {

            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $custid = $this->input->get('id');
        $data['details'] = $this->customers->details($custid);
        $data['money'] = $this->customers->money_details($custid);
        $head['usernm'] = $this->aauth->get_user()->username;
        $head['title'] = 'View Customer Quotes';
        $this->load->view('fixed/header', $head);
        $this->load->view('customers/quotes', $data);
        $this->load->view('fixed/footer');
    }

    public function add_balance2()
    {
        //echo "hello";exit;


        $new_balance = $this->input->get('balance');

        $descr = $this->input->get('description');

        $start_date = datefordatabase($this->input->get('end_date'));

        $custid = $this->input->get('cstid');

        $details = $this->customers->details($custid);

        $this->db->set('balance', "balance+$new_balance", FALSE);
        $this->db->where('id', $custid);
        $this->db->update('geopos_customers');

        $data = array(
            'acid' => 1,
            'account' => 'Sales Account',
            'type' => 'Income',
            'cat' => 'Sales',
            'credit' => 0.00,
            'debit' => $new_balance,
            'payer' => $details['name'],
            'payerid' => $custid,
            'method' => '',
            'date' => $start_date,
            'tid' => 0,
            'eid' => 13,
            'note' => $descr,
            'ext' => 0,
            'loc' => 0
        );

        $this->db->insert('geopos_transactions', $data);

        redirect('customers/view?id=' . $custid);
    }

    public function qto_list()
    {
        if (!$this->aauth->permission_new(null, 'customerActivities')) {

            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $cid = $this->input->post('cid');
        $tid = $this->input->post('tyd');
        $list = $this->customers->qto_datatables($cid, $tid);
        $data = array();
        $no = $this->input->post('start');
        foreach ($list as $invoices) {
            $no++;
            $row = array();

            $row[] = $invoices->tid;

            $row[] = $invoices->invoicedate;
            $row[] = amountExchange($invoices->total, 0, $this->aauth->get_user()->loc);
            $row[] = '<span class="st-' . $invoices->status . '">' . $this->lang->line(ucwords($invoices->status)) . '</span>';
            $row[] = ($this->aauth->permission_new(null, 'customerViewCustomer') ? '<a href="' . base_url("quote/view?id=$invoices->id") . '" class="btn btn-success btn-xs" title="View Invoice"><i class="fa fa-file-text"></i> </a>' : '') . ' ' . ($this->aauth->permission_new(null, 'customerViewCustomer') ? '<a href="' . base_url("quote/printquote?id=$invoices->id") . '&d=1" class="btn btn-info btn-xs"  title="Download"><span class="fa fa-download"></span></a>' : '') . ' ' . ($this->aauth->permission_new(null, 'customerDeleteCustomer') ? '<a href="#" data-object-id="' . $invoices->id . '" class="btn btn-danger btn-xs delete-object" title="Delete"><span class="fa fa-trash"></span></a>' : '');
            $data[] = $row;
        }
        $output = array(
            "draw" => $this->input->post('draw'),
            "recordsTotal" => $this->customers->qto_count_all($cid),
            "recordsFiltered" => $this->customers->qto_count_filtered($cid),
            "data" => $data,
        );
        //output to json format
        echo json_encode($output);
    }

    public function balance()
    {
        if (!$this->aauth->permission_new(null, 'customerActivities')) {

            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        if ($this->input->post()) {
            $id = $this->input->post('id');
            $amount = $this->input->post('amount', true);
            if ($this->customers->recharge($id, $amount)) {
                echo json_encode(array('status' => 'Success', 'message' => $this->lang->line('Balance Added')));
            } else {
                echo json_encode(array('status' => 'Error', 'message' => 'Error!'));
            }
        } else {
            $custid = $this->input->get('id');
            $data['details'] = $this->customers->details($custid);
            $data['customergroup'] = $this->customers->group_info($data['details']['gid']);
            $data['money'] = $this->customers->money_details($custid);
            $head['usernm'] = $this->aauth->get_user()->username;
            $data['activity'] = $this->customers->activity($custid);
            $head['title'] = 'View Customer';
            $this->load->view('fixed/header', $head);
            $this->load->view('customers/recharge', $data);
            $this->load->view('fixed/footer');
        }
    }

    public function projects()
    {
        if (!$this->aauth->permission_new(null, 'customerViewCustomer')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $custid = $this->input->get('id');
        $data['details'] = $this->customers->details($custid);
        $data['money'] = $this->customers->money_details($custid);
        $head['usernm'] = $this->aauth->get_user()->username;
        $head['title'] = 'View Customer Invoices';
        $this->load->view('fixed/header', $head);
        $this->load->view('customers/projects', $data);
        $this->load->view('fixed/footer');
    }

    public function prj_list()
    {
        if (!$this->aauth->permission_new(null, 'customerViewCustomer')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $cid = $this->input->post('cid');


        $list = $this->customers->project_datatables($cid);
        $data = array();
        $no = $this->input->post('start');
        foreach ($list as $project) {
            $no++;
            $name = '<a href="' . base_url() . 'projects/explore?id=' . $project->id . '">' . $project->name . '</a>';

            $row = array();
            $row[] = $no;
            $row[] = $name;
            $row[] = dateformat($project->sdate);
            $row[] = $project->customer;
            $row[] = '<span class="project_' . $project->status . '">' . $this->lang->line($project->status) . '</span>';

            $row[] = ($this->aauth->permission_new(null, 'customerViewCustomer') ? '<a href="' . base_url() . 'projects/explore?id=' . $project->id . '" class="btn btn-primary btn-sm rounded" data-id="' . $project->id . '" data-stat="0"> ' . $this->lang->line('View') . ' </a>' : '') . ' ' . ($this->aauth->permission_new(null, 'customerEditCustomer') ? '<a class="btn btn-info btn-sm" href="' . base_url() . 'projects/edit?id=' . $project->id . '" data-object-id="' . $project->id . '"> <i class="fa fa-pencil"></i> </a>' : '') . ' ' . ($this->aauth->permission_new(null, 'customerDeleteCustomer') ? '<a class="btn btn-danger btn-sm delete-object" href="#" data-object-id="' . $project->id . '"> <i class="fa fa-trash"></i> </a>' : '');


            $data[] = $row;
        }
        $output = array(
            "draw" => $this->input->post('draw'),
            "recordsTotal" => $this->customers->project_count_all($cid),
            "recordsFiltered" => $this->customers->project_count_filtered($cid),
            "data" => $data,
        );
        //output to json format
        echo json_encode($output);
    }

    public function notes()
    {
        if (!$this->aauth->permission_new(null, 'customerViewCustomer')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $custid = $this->input->get('id');
        $head['usernm'] = $this->aauth->get_user()->username;
        $data['details'] = $this->customers->details($custid);
        $this->session->set_userdata("cid", $custid);
        $head['title'] = 'Notes';
        $this->load->view('fixed/header', $head);
        $this->load->view('customers/notes', $data);
        $this->load->view('fixed/footer');
    }

    public function notes_load_list()
    {
        if (!$this->aauth->permission_new(null, 'customerManageCustomer')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $cid = $this->input->post('cid');
        $list = $this->customers->notes_datatables($cid);
        $data = array();
        $no = $this->input->post('start');
        foreach ($list as $note) {
            $row = array();
            $no++;
            $row[] = $no;
            $row[] = $note->title;
            $row[] = dateformat($note->cdate);

            $row[] = ($this->aauth->permission_new(null, 'customerViewCustomer') ? '<a href="editnote?id=' . $note->id . '&cid=' . $note->fid . '" class="btn btn-info btn-sm"><span class="fa fa-eye"></span> ' . $this->lang->line('View') . '</a>' : '') . ' ' . ($this->aauth->permission_new(null, 'customerDeleteCustomer') ? '<a class="btn btn-danger btn-sm delete-object" href="#" data-object-id="' . $note->id . '"> <i class="fa fa-trash"></i> </a>' : '');
            $data[] = $row;
        }

        $output = array(
            "draw" => $this->input->post('draw'),
            "recordsTotal" => $this->customers->notes_count_all($cid),
            "recordsFiltered" => $this->customers->notes_count_filtered($cid),
            "data" => $data,
        );
        echo json_encode($output);
    }

    public function editnote()
    {
        if (!$this->aauth->permission_new(null, 'customerManageCustomer')) {

            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        if ($this->input->post()) {
            $id = $this->input->post('id');
            $title = $this->input->post('title', true);
            $content = $this->input->post('content');
            $cid = $this->input->post('cid');
            if ($this->customers->editnote($id, $title, $content, $cid)) {
                echo json_encode(array('status' => 'Success', 'message' => $this->lang->line('UPDATED') . " <a href='notes?id=$cid' class='btn btn-indigo btn-lg'><span class='icon-user' aria-hidden='true'></span>  </a> <a href='editnote?id=$id&cid=$cid' class='btn btn-indigo btn-lg'><span class='icon-eye' aria-hidden='true'></span>  </a>"));
            } else {
                echo json_encode(array('status' => 'Error', 'message' => $this->lang->line('ERROR')));
            }
        } else {
            $id = $this->input->get('id');
            $cid = $this->input->get('cid');
            $data['note'] = $this->customers->note_v($id, $cid);
            $head['usernm'] = $this->aauth->get_user()->username;
            $head['title'] = 'Edit';
            $this->load->view('fixed/header', $head);
            $this->load->view('customers/editnote', $data);
            $this->load->view('fixed/footer');
        }
    }

    public function addnote()
    {
        if (!$this->aauth->permission_new(null, 'customerManageCustomer')) {

            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        if ($this->input->post('title')) {

            $title = $this->input->post('title', true);
            $cid = $this->input->post('id');
            $content = $this->input->post('content');

            if ($this->customers->addnote($title, $content, $cid)) {
                echo json_encode(array('status' => 'Success', 'message' => $this->lang->line('ADDED') . "  <a href='addnote?id=" . $cid . "' class='btn btn-indigo btn-lg'><span class='icon-plus-circle' aria-hidden='true'></span>  </a> <a href='notes?id=" . $cid . "' class='btn btn-grey btn-lg'><span class='icon-eye' aria-hidden='true'></span>  </a>"));
            } else {
                echo json_encode(array('status' => 'Error', 'message' => $this->lang->line('ERROR')));
            }
        } else {
            $data['id'] = $this->input->get('id');
            $head['usernm'] = $this->aauth->get_user()->username;
            $head['title'] = 'Add Note';
            $this->load->view('fixed/header', $head);
            $this->load->view('customers/addnote', $data);
            $this->load->view('fixed/footer');
        }
    }

    public function delete_note()
    {
        $id = $this->input->post('deleteid');
        $cid = $this->session->userdata('cid');
        if ($this->customers->deletenote($id, $cid)) {
            echo json_encode(array('status' => 'Success', 'message' => $this->lang->line('DELETED')));
        } else {
            echo json_encode(array('status' => 'Error', 'message' => $this->lang->line('ERROR')));
        }
    }
    function statement()
    {
        $company = $this->settings->company_details(1);
        $company_name = $company['cname'];
        $company_address = $company['address'];
        $company_city = $company['city'];
        $company_region = $company['region'];
        $company_country = $company['country'];
        $company_postbox = $company['postbox'];
        $company_phone = $company['phone'];
        $company_email = $company['email'];
        
        if ($this->input->post()) {
            ob_end_clean();
            ob_start();
            $this->load->model('reports_model');
            $customer = $this->input->post('customer');
            //$sql="select credit,debit,id from geopos_transactions where payerid=$customer order by date asc";
            $sql = "select sum(credit) as credit,sum(debit) as debit,id,tid from geopos_transactions where payerid=$customer group by credit,debit order by date ASC";
            //  $sql="select sum(credit) as credit,sum(debit) as debit,id,date from geopos_transactions where payerid=$customer group by credit,debit,date";

            $query = $this->db->query($sql);
            $response = $query->result_array();
            //  echo "<pre>";
            // print_r($response);
            // die();
            $balance_array;
            $balance = 0;
            $i = 0;
            foreach ($response as $key => $value) {
                $balance += $value['credit'] - $value['debit'];
                $balance_array[$key]['balance'] = $balance;
                $balance_array[$key]['id'] = $value['id'];
                $i++;
            }

            $sql = 'SELECT sum(total)-sum(pamnt) as "cust_balance" FROM geopos_invoices WHERE status="due" and csd="' . $customer . '" or status="partial" and csd=' . $customer;

            $query = $this->db->query($sql);
            $response = $query->result_array();
            $cust_balance_inv = $response[0]['cust_balance'];


            $trans_type = $this->input->post('trans_type');
            $sdate = datefordatabase($this->input->post('sdate'));
            $edate = datefordatabase($this->input->post('edate'));
            $data['customer'] = $this->customers->details($customer);
            $data['list'] = $this->customers->get_customer_statements($customer, $trans_type, $sdate, $edate);
            // echo "<pre>";
            // var_dump($data['list']);
            // exit;
            $company = $data['customer']['company'];
            $address = $data['customer']['address'];
            $postbox = $data['customer']['postbox'];

            $city = $data['customer']['city'];
            $region = $data['customer']['region'];
            $country = $data['customer']['country'];
            $phone_s = $data['customer']['phone_s'];
            $email_s = $data['customer']['email_s'];
            $name = $data['customer']['name'];
            //   echo "<pre>";
            //   print_r($data['list']);die();
            $data_tr = "";
            $balance = 0;

            foreach ($data['list'] as $key => $row) {
                $balance = $row["balance"];
                //  print_r($row);die();
                //$balance+= $row["credit"]-$row["debit"];
                //  if($row["credit"]>0.1 && $row["debit"]<0.1 || $row["credit"]<0.1 && $row["debit"]>0.1 ){
                if ($row["date"] >= $sdate && $row["date"] <= $edate) {
                    if ($row["tid"] >= 1) {


                        $data_tr .= '
                <tr>
                <td>' . $row["date"] . '</td>
                <td>' . $row["tid"] . '</td>
                <td>  ' . $row["note"] . " " . $row["paymt_method"] . '</td>
                <td>' . $row["credit"] . '</td>
                <td>' . $row["debit"] . '</td>' .
                            //  <td>'.number_format($balance_array[array_search($row["id"], array_column($balance_array, 'id'))]['balance'], 2, '.', '').'</td>
                            '<td>' . formatDecimal($row["balance"]) . '</td>
                
                </tr>';
                    } else {



                        $data_tr .= '
                <tr>
                <td>' . $row["date"] . '</td>
                <td></td>
                <td>' . $row["note"] . " " . $row["paymt_method"] . '</td>
                <td>' . $row["credit"] . '</td>
                <td>' . $row["debit"] . '</td>' .
                            //  <td>'.number_format($balance_array[array_search($row["id"], array_column($balance_array, 'id'))]['balance'], 2, '.', '').'</td>
                            '<td>' . formatDecimal($row["balance"]) . '</td>
                
                </tr>';
                    }
                    //}
                }
            }
            $date = date("d/m/y");
            $time = date("h:i:s");


            $prev_balance = $balance_array[count($balance_array) - 1]['balance'];
            $prev_balance = formatDecimal($prev_balance);
            $startdate = date_create($sdate);
            $startdate = date_format($startdate, "d/m/Y");
            $enddate = date_create($edate);
            $enddate = date_format($enddate, "d/m/Y");
            //var_dump($balance); exit;
            $html = "
                    <html>
                    <head>
                        <style>
                            body{font-family: Tahoma, Verdana, Segoe, sans-serif;font-size:12px;}
                            @page {  }
                            #header{ position: fixed;  top: -280px;}
                            table{width:100%; border-spacing: 5px;}
                            tr td th{padding: 3px;}
                            @page { margin-top: 320px; }.pagenum:after {content: counter(page)}
                        </style>
                        
                    </head>
                    <body>
                        <div id='header'>

                        <div class='pagenum' style='float:right;'>  Page:   </div>         <div class='left_header'>
                            <div>$company_name</div>
                            <div>$company_address</div>
                            <div>$company_city, $company_region, $company_postbox</div>
                            <div>$company_country</div>
                            <div>Tel: $company_phone</div>
                            <div>Email: $company_email</div>
                            </div>
                            
                            <div style='border:1px solid black;width:30%; padding: 8px 10px 8px; border-radius: 6px;float:left;margin-top:30px'>
                            <div>$company</div>
                            <div>$address</div>
                            <br>
                            <div>$city</div>
                            <div>$region, $country</div>
                                <div>$postbox</div>
                            </div>
                            <div style='float:right;margin-left:500px;margin-top:-15px'  >
                                <div>STATEMENT  </div> 
                                <table style='border-collapse: separate;border:1px solid black;  border-radius:6px;
                        -moz-border-radius:6px;'>
                                    <tr >
                                        <td>
                                            Date
                                        </td>
                                        <td >
                                            $date
                                        </td>
                                    </tr>
                                    <tr >
                                        <td >
                                            Account Ref
                                        </td>
                                        <td >
                                            $name
                                        </td>
                                    </tr>
                                    
                                    
                                </table>
                            </div>
                        </div>

                    <div style='margin-top: -38px'>All values are shown in Pound Sterling. Date Range: $startdate - $enddate
                    <table>
                    <thead style='border-collapse: separate;border:1px solid black;  border-radius:6px;
                        -moz-border-radius:6px;'>
                        <tr >
                            <th>
                                Date
                            </th>
                            <th>
                                Ref
                            </th>
                            <th>
                                Details
                            </th>
                            
                            <th>
                                Debit
                            </th>
                            <th>
                                Credit
                            </th>
                            <th>
                                Balance
                            </th>
                        
                        
                        </tr>
                    </thead>
                    $data_tr
                    </table>
                    </div>
                    <div style='position: absolute;bottom: 0px;'>
                    <hr> 
                    <div style='width:160px;float:right;'>
                        <table style='border-collapse: separate;border:1px solid black;  border-radius:6px;
                        -moz-border-radius:6px;'>
                            <tr >
                                <td>Amount Due</td>
                                <td><span style='float:right'>&#163; $prev_balance</span></td>
                            </tr>
                        </table>
                    </div>
                    <table>
                        <tr >
                                        <td >
                                        <b>    Balance </b>
                                        </td>
                                        <td >
                                        <b> &#163;  $prev_balance </b>
                                        </td>
                                    </tr>
                    </table>
                    <div>
                    </body>
                    </html>
                    ";

            if ($this->input->post('excel')) {
                $excelData = array(
                    array('Date', 'Details', 'Debit', 'Credit', 'Balance', 'Customer Balance')
                );
                $array_data = $this->customers->get_customer_statements($customer, $trans_type, $sdate, $edate);



                foreach ($data['list'] as $key => $row) {

                    // foreach($array_data as $row) {
                    array_push(
                        $excelData,
                        array($row["date"], $row["note"], $row["credit"], $row["debit"], formatDecimal($row["balance"]))
                    );
                }

                array_push($excelData, array('', '', '', '', '', $prev_balance));

                $this->exportTableToExcel($excelData);
            } else {
                $this->dpdf->loadHtml($html);
                $this->dpdf->render();
                $this->dpdf->stream("Account Statement.pdf", array("Attachment" => 0));
            }
            // window.open("Stock Report.pdf");
        } else {
            $data['id'] = $this->input->get('id');
            $this->load->model('transactions_model');
            $data['details'] = $this->customers->details($data['id']);
            $head['title'] = "Account Statement";
            $head['usernm'] = $this->aauth->get_user()->username;
            $this->load->view('fixed/header', $head);
            $this->load->view('customers/statement', $data);
            $this->load->view('fixed/footer');
        }
    }

    function exportTableToExcel($tableData)
    {
        // var_dump($tableData);exit();
        $rows = $tableData;
        $writer = new XLSXWriter();

        foreach ($rows as $row)
            $writer->writeSheetRow('Sheet1', $row);
        $file_name = 'Account Statement-' . time() . '.xlsx';
        $file_path = 'userfiles/documents/' . $file_name;

        $writer->writeToFile($file_path);

        $data = file_get_contents(base_url() . $file_path);
        force_download($file_name, $data);
    }






    function customer_report_export()
    {
        $company = $this->settings->company_details(1);
        ob_end_clean();
        ob_start();

        $sdate = $this->input->get('start_date');
        $edate = $this->input->get('end_date');
        $data_tr = '';
        if ($this->input->get('check') != 1) {


            $sql = 'SELECT geopos_customers.name as name,geopos_customers.address as address,geopos_customers.phone as phone, COUNT(geopos_invoices.id ) as total_invoices 
                from geopos_customers
                join geopos_invoices
                on (geopos_customers.id = geopos_invoices.csd) 
                where geopos_invoices.invoicedate between "' . $sdate . '" AND "' . $edate . '"
                Group By (geopos_invoices.csd)
                order by count(geopos_invoices.id) desc
                ';


            $query = $this->db->query($sql);
            $response = $query->result_array();

            foreach ($response as $key => $value) {
                $data_tr .= '
             <tr>
             <td>' . $value["name"] . '</td>
             <td>' . $value["address"] . '</td>
             <td>' . $value["phone"] . '</td>
             <td>' . $value["total_invoices"] . '</td>
             </tr>';
            }
        } else {

            $sql = 'SELECT * from geopos_customers';

            $query = $this->db->query($sql);
            $response = $query->result_array();

            foreach ($response as $key => $value) {
                $c_id = $value['id'];
                $sql = 'SELECT count(geopos_invoices.id) as count from geopos_invoices where geopos_invoices.csd="' . $c_id . '" and geopos_invoices.invoicedate between "' . $sdate . '" AND "' . $edate . '"';
                $query = $this->db->query($sql);
                $inv_response = $query->result_array();
                if ($inv_response[0]['count'] == 0) {
                    $data_tr .= '
             <tr>
             <td>' . $value["name"] . '</td>
             <td>' . $value["address"] . '</td>
             <td>' . $value["phone"] . '</td>
             <td>' . $inv_response[0]["count"] . '</td>
             </tr>';
                }
            }
        }
        $date = date("d/m/y");
        $time = date("h:i:s");

        $html = "
            <html>
            <head>
                <style>
                    body{font-family: Tahoma, Verdana, Segoe, sans-serif;font-size:12px;}
                    @page {  }
                    #header{ position: fixed;  top: -180px;}
                    table{width:100%; border-spacing: 5px;}
                    tr td th{padding: 3px;}
                    @page { margin-top: 220px; }.pagenum:after {content: counter(page)}
                </style>
                
            </head>
            <body>
                <div id='header'>
                <div class='pagenum' style='float:right;'>  Page:   </div>         <div class='left_header'>
                    <div>" . $company['cname'] . "</div>
                    <div>" . $company['address'] . "</div>
                    <div>" . $company['city'] . " " . $company['region'] . ", " . $company['postbox'] . " <br> " . $company['country'] . "</div>
                    
                    
                    <div>Tel: " . $company['phone'] . "</div>
                    <div>Email: " . $company['email'] . "</div>
                    </div>
                </div>

            <div >All values are shown in Pound Sterling.
            <br>
            <table >
            <thead style='border-collapse: separate;border:1px solid black;  border-radius:6px;
                -moz-border-radius:6px;'>
                <tr >
                    <th>
                        Name
                    </th>
                    <th>
                        Address
                    </th>
                    <th>
                        Phone
                    </th>
                    <th>
                        Total Invoices
                    </th>
                </tr>
            </thead>
            $data_tr
            </table>
            </div>

            </body>
            </html>
            ";
        $this->dpdf->loadHtml($html);
        $this->dpdf->render();
        $this->dpdf->stream("Account Statement.pdf", array("Attachment" => 0));
        // window.open("Stock Report.pdf");


    }


    function customer_ClosingBalance_report_export()
    {
        // var_dump($this->input->get('start_date')); die();
        $this->load->library('dpdf');  // Ensure the PDF library is loaded
        $this->load->model('settings'); // Ensure the settings model is loaded

        ob_end_clean();
        ob_start();

        // Retrieve input parameters
        $sdate = $this->input->get('start_date');
        // $edate = $this->input->get('end_date');
        // $start_date = $this->input->get('start_date'); // Expected format: YYYY-MM
        $check = $this->input->get('check');  // 'check' parameter to filter customers with no invoices

        // Base query
        if ($check != 1) {
            // Query for customers with invoices
            $sql = "
                SELECT 
                    c.name AS name,
                    c.company AS company,  -- Assuming you have a company field
                    c.address AS address,
                    SUM(i.total) - SUM(i.pamnt) AS cust_balance
                FROM 
                    geopos_customers c
                JOIN 
                    geopos_invoices i ON c.id = i.csd
                WHERE 
                    i.invoicedate <= '$sdate'
                    AND i.status IN ('due', 'partial')
                GROUP BY 
                    i.csd
                ORDER BY 
                    COUNT(i.id) DESC
            ";
        } else {
            // Query for customers with no invoices in the date range
            $sql = '
            SELECT 
                c.name AS name,
                c.company AS company,  -- Assuming you have a company field
                c.address AS address,
                0 AS cust_balance
            FROM 
                geopos_customers c
            LEFT JOIN 
                geopos_invoices i ON c.id = i.csd 
                AND i.invoicedate BETWEEN "' . $sdate . '" AND "' . $edate . '"
            WHERE 
                i.id IS NULL
            ';
        }

        $query = $this->db->query($sql);
        $response = $query->result_array();
        $no = 1;
        $data_tr = '';
        foreach ($response as $value) {
            $data_tr .= '
        <tr>
        <td>' . $no++ . '</td>';
            $data_tr .= '
            <td>' . htmlspecialchars($value["name"]) . '</td>
            <td>' . htmlspecialchars($value["company"] ?? '') . '</td>
            <td>' . formatDecimal($value["cust_balance"]) . '</td>
        ';

            $data_tr .= '</tr>';
        }

        $date = date("d/m/y");
        $time = date("h:i:s");
        $company = $this->settings->company_details(1); // Retrieve company details

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
                            <b><u> Closing Balance of All Customers </u></b> 
                        </div>
                    </div>
                    <div class='right_header'>
                        <b><span class='pagenum'>Page:</span></b>
                        </div>
                        </div>
                        <div style='font-size:13px;'>Closing Balance of All Customers untill: $sdate.
                        </div>
                        <div style='clear:both'>
                        </div>
                        <br>
                <table>
                 <thead style='font-size:13px; border-collapse: separate;border:1px solid black;  border-radius:6px;
                    -moz-border-radius:6px;'>
                    <tr>
                        <th>
                            No
                        </th>
                        <th>
                            A/C #
                        </th>
                        <th>
                            Name
                        </th>
                        <th>
                            Closing Balance
                        </th>
                    </tr>
                </thead>
                <tbody style='font-size:13px;'>
                $data_tr
                </tbody>
                </table>
                </div>
            </body>
            </html>";

        $this->dpdf->loadHtml($html);
        $this->dpdf->render();
        $this->dpdf->stream("Account Statement.pdf", array("Attachment" => 0));
    }












    public function documents()
    {
        $data['id'] = $this->input->get('id');
        $data['details'] = $this->customers->details($data['id']);
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->session->set_userdata("cid", $data['id']);
        $head['title'] = 'Documents';
        $this->load->view('fixed/header', $head);
        $this->load->view('customers/documents', $data);
        $this->load->view('fixed/footer');
    }

    public function document_load_list()
    {
        $cid = $this->input->post('cid');
        $list = $this->customers->document_datatables($cid);
        $data = array();
        $no = $this->input->post('start');
        foreach ($list as $document) {
            $row = array();
            $no++;
            $row[] = $no;
            $row[] = $document->title;
            $row[] = dateformat($document->cdate);

            $row[] = ($this->aauth->permission_new(null, 'customerViewCustomer') ? '<a href="' . base_url('userfiles/documents/' . $document->filename) . '" class="btn btn-success btn-xs"><i class="fa fa-file-text"></i> ' . $this->lang->line('View') . '</a>' : '') . ' ' . ($this->aauth->permission_new(null, 'customerDeleteCustomer') ? '<a class="btn btn-danger btn-xs delete-object" href="#" data-object-id="' . $document->id . '"> <i class="fa fa-trash"></i> </a>' : '');


            $data[] = $row;
        }

        $output = array(
            "draw" => $this->input->post('draw'),
            "recordsTotal" => $this->customers->document_count_all($cid),
            "recordsFiltered" => $this->customers->document_count_filtered($cid),
            "data" => $data,
        );
        echo json_encode($output);
    }


    public function adddocument()
    {
        $data['id'] = $this->input->get('id');
        $this->load->helper(array('form'));
        $data['response'] = 3;
        $head['usernm'] = $this->aauth->get_user()->username;
        $head['title'] = 'Add Document';

        $this->load->view('fixed/header', $head);

        if ($this->input->post('title')) {
            $title = $this->input->post('title', true);
            $cid = $this->input->post('id');
            $config['upload_path'] = './userfiles/documents';
            $config['allowed_types'] = 'docx|docs|txt|pdf|xls';
            $config['encrypt_name'] = TRUE;
            $config['max_size'] = 3000;
            $this->load->library('upload', $config);

            if (!$this->upload->do_upload('userfile')) {
                $data['response'] = 0;
                $data['responsetext'] = 'File Upload Error';
            } else {
                $data['response'] = 1;
                $data['responsetext'] = 'Document Uploaded Successfully. <a href="documents?id=' . $cid . '"
                                       class="btn btn-indigo btn-md"><i
                                                class="icon-folder"></i>
                                    </a>';
                $filename = $this->upload->data()['file_name'];
                $this->customers->adddocument($title, $filename, $cid);
            }

            $this->load->view('customers/adddocument', $data);
        } else {


            $this->load->view('customers/adddocument', $data);
        }
        $this->load->view('fixed/footer');
    }


    public function delete_document()
    {
        $id = $this->input->post('deleteid');
        $cid = $this->session->userdata('cid');

        if ($this->customers->deletedocument($id, $cid)) {
            echo json_encode(array('status' => 'Success', 'message' => $this->lang->line('DELETED')));
        } else {
            echo json_encode(array('status' => 'Error', 'message' => $this->lang->line('ERROR')));
        }
    }

    public function bulkpayment()
    {
        if (!$this->aauth->permission_new(null, 'customerManageCustomer')) {

            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $data['id'] = $this->input->get('id');
        $data['details'] = $this->customers->details($data['id']);
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->model('accounts_model');
        $data['acclist'] = $this->accounts_model->accountslist((int)$this->aauth->get_user()->loc);
        $this->session->set_userdata("cid", $data['id']);
        $head['title'] = 'Bulk Payment Invoices';
        $this->load->view('fixed/header', $head);
        $this->load->view('customers/bulkpayment', $data);
        $this->load->view('fixed/footer');
    }

    public function bulk_post()
    {
        if (!$this->aauth->permission_new(null, 'customerManageCustomer')) {

            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $csd = $this->input->post('customer', true);
        $sdate = datefordatabase($this->input->post('sdate'));
        $edate = datefordatabase($this->input->post('edate'));
        $trans_type = $this->input->post('trans_type', true);
        $data['details'] = $this->customers->sales_due($sdate, $edate, $csd, $trans_type);

        $due = $data['details']['total'] - $data['details']['pamnt'];
        echo json_encode(array('status' => 'Success', 'message' => $this->lang->line('Calculated') . ' ' . amountExchange($due), 'due' => amountExchange_s($due)));
    }

    public function bulk_post_payment()
    {
        if (!$this->aauth->permission_new(null, 'customerManageCustomer')) {

            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $csd = $this->input->post('customer', true);
        $account = $this->input->post('account', true);
        $pay_method = $this->input->post('pmethod', true);
        $amount = numberClean($this->input->post('amount', true));
        $sdate = datefordatabase($this->input->post('sdate_2'));
        $edate = datefordatabase($this->input->post('edate_2'));
        $trans_type = $this->input->post('trans_type_2', true);
        $note = $this->input->post('note', true);
        $data['details'] = $this->customers->sales_due($sdate, $edate, $csd, $trans_type, false, $amount, $account, $pay_method, $note);
        $due = 0;
        echo json_encode(array('status' => 'Success', 'message' => $this->lang->line('Paid') . ' ' . amountExchange($amount), 'due' => amountExchange_s($due)));
    }




    public function balanceinvoice()
    {
        $cid = $this->input->get('id');
        $company = $this->settings->company_details(1);
        $sql = 'SELECT * FROM geopos_invoices WHERE csd="' . $cid . '" order by id desc limit 1';

        $query = $this->db->query($sql);

        $response = $query->result_array();

        $tid = $response[0]['id'];

        $data['id'] = $tid;
        // print_r($tid);die();
        $data['invoice'] = $this->invoices->invoice_details($tid, $this->limited);
        // print_r($tid);die();
        if ($data['invoice']['id']) $data['products'] = $this->invoices->invoice_products($tid);
        if ($data['invoice']['id']) $data['employee'] = $this->invoices->employee($data['invoice']['eid']);
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
                        </div></div><div><p style="text-align:center;height:100px;">Note: This is not a valid Invoice  </p></div><div style="clear:both"></div>';
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
        } else {
            $header .= ' <br> ' . $data['invoice']['cust_address'] . '<br/>' . $data['invoice']['cust_city'] . '<br/>' . $data['invoice']['cust_postcode'] . "";
        }

        $header .= '</div><div style="float:right;margin-left:-150px;margin-top:-30px;width:50%" ><div style=" font-size: 18px; margin-top: 12px">';
        $header .= '<b>&nbsp;Balance Invoice</b>';

        $header .= '</div><table style="width:100%; font-size: 14px; border:1px solid #1367A4;"><tr><td style="width: 50%; border-bottom:1px solid #1367A4;background: lightgrey;padding: 8px 0px 8px 7px;">&nbsp;Invoice No.</td>
       <td  style="border-bottom:1px solid #1367A4;">';

        $header .= "-";



        $header .= ' </td></tr><tr><td style="width: 50%; border-bottom:1px solid #1367A4;background: lightgrey;padding:  8px 0px 8px 7px;">&nbsp;Invoice Date</td><td style="border-bottom:1px solid #1367A4;"> ' . date("d-m-Y") . ' </td></tr>';


        $header .= '  <tr><td style="width: 50%; background: lightgrey;padding: 8px 0px 8px 7px;">&nbsp;Account No.</td><td> ' . $data["invoice"]["name"] . '</td></tr>';


        $header .= '</table><div>';

        $header .= ' &nbsp;VAT Reg No: 299367533 ';

        $header .= '</div></div></header>';

        $header .= '
           
            <table style="width:100%; font-size: 14px;  margin-top:-15px;"><tr><td style="width: 100%;">&nbsp;<b> 
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            For Bank Transfer:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Cloud Billing Manager.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;S/C&nbsp;&nbsp;                20-98-98           &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;AC&nbsp;&nbsp;33821986  
            </b></td></tr></table>
            
            ';

        $header .= '<div style="clear:both"></div><main><div style="width:100%;  "> <br><br><br><br><br><span style="margin-left:300px;">Balance Invoice</span>';


        $header .= '<table border="1" width="100%" style="border-collapse: collapse; margin-top: 20px;">
            <thead>
                <tr>
                    <th>Customer Name</th>
                    <th>Invoice No.</th>
                    <th>Balance Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>' . htmlspecialchars($invoice['name'] ?? 'N/A') . '</td>
                    <td>' . htmlspecialchars($invoice['tid'] ?? '-') . '</td>
                    <td>' . formatDecimal($cust_balance_inv) . ' GBP</td>
                </tr>
            </tbody>
        </table>';




        $header .= '</div></main>';



        $header .= '<footer style="position:absolute;bottom:-195px">';

        $header .= '<div style="border:1px solid #1367A4;width:50%;height:140px; padding: 8px 0px 5px 4px; border-radius: 6px;float:left;margin-top:5px"><span style="color:rgb(48, 37, 133)"><b>*MAXIMUM CHANGE ACCEPTED=&#163;60</b></span><div style=" font-size: 10px;">All goods remain the property of Cloud Billing Manager until all monies are paid in full<br>Any complaints should be made within 48 hours of purchasing.</div><br><div style="width:100%"><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left">Print Name:</div><div style="border:1px solid black;width:10%;height:18px;float:left;margin-left:10px;;margin-left:-78px;"></div><div style="float:left;margin-left:-158px;margin-top:3px;font-size: 12px;"><b>Cash Tendered</b></div><div style="clear:both"></div><div style="border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left;">Signature:</div><div style="font-size: 17px;padding-left:10px;border:1px solid black;width:35%;height:20px;float:left;padding-top:3px;padding-bottom:3px;margin-left:-157px;margin-top:-8px">&#163;</div><div style="clear:both"></div></div><div style="clear:both"></div></div>';



        $header .= '<div style="float:right;margin-left:320px;width:280px" ><table class="maintable2" style="width:100%; border:1px solid #1367A4;font-size: 14px;">';


        $header .= '<tr ><td style="width: 50%;  background: lightgrey;padding: 4px 0px 4px 7px;">&nbsp;Previous Balance</td><td>';
        $invbalance = 0;
        if ($cust_balance_inv >= $data['invoice']['total']) {
            $invbalance =  $cust_balance_inv;
            $header .=  substr(amountExchange($invbalance, $data['invoice']['multi'], $data['invoice']['loc']), 3, 100);
        } else {
            $header .=  substr(amountExchange($cust_balance_inv, $data['invoice']['multi'], $data['invoice']['loc']), 3, 100);
        }
        $header .= '</td></tr>';


        $header .= '<tr><td style="width: 60%;  background: lightgrey;padding:  4px 0px 4px 7px;">&nbsp;Total NET Amount</td><td>-</td></tr><tr ><td style="width: 60%;  background: lightgrey;padding: 4px 0px 4px 7px;">&nbsp;Total VAT Amount</td><td> -  </td></tr><tr><td style="width: 60%;  background: lightgrey;padding: 4px 0px 4px 7px;">&nbsp;Invoice Total</td><td> -   </td></tr>';


        $header .= ' <tr><td style="width: 60%; background: lightgrey;padding:  4px 0px 4px 7px;">&nbsp;Balance Now Due</td><td>' . $cust_balance_inv . ' </td></tr> ';

        $header .= '</table></div><div style="clear:both"></div>';



        $header .= ' </footer></body></html>';
        $file_name = "Balance-Sheet-" . $this->input->get('id');
        $this->dpdf->loadHtml($header);
        $this->dpdf->render();
        $output =  $this->dpdf->output();
        $file_location = 'userfiles/invoices/' . $file_name . '.pdf';
        file_put_contents($file_location, $output);
        $this->dpdf->stream("" . $file_name . ".pdf", array("Attachment" => 0));
    }
}

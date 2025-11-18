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

class Reports extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('reports_model', 'reports');
        $this->load->model('products_model', 'products');
        $this->load->model('invoices_model', 'invocies');
        $this->load->model('employee_model', 'employee');
        $this->load->library("Aauth");
        if (!$this->aauth->is_loggedin()) {
            redirect('/user/', 'refresh');
        }
        if (!$this->aauth->permission_new(null, 'salesReportsAccess')) {

            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $this->load->library("Custom");
        $this->li_a = 'data';
        $this->load->library('dpdf');
    }

    public function index() {}

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
        $this->load->view('reports/salebyproduct_summary');
        $this->load->view('fixed/footer');
    }

    public function breakdown_sheet()
    {
        $head['title'] = "Breakdown Sheet";
        $head['usernm'] = $this->aauth->get_user()->username;
        // $this->db->select('id,username');
        // $this->db->from('geopos_users');
        // $this->db->where('roleid', 4);
        // $query = $this->db->get();
        // $data['drivers']=$query->result_array();
        $data['drivers'] = $this->employee->list_employee_driver();
        $this->load->view('fixed/header', $head);
        $this->load->view('pos/extended', $data);
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

    public function payments_report()
    {

        $head['title'] = "Payment Recieved Report";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $this->load->view('invoices/payments_recieved');
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

    public function daybook()
    {
        $head['title'] = "Day Book";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $this->load->view('invoices/daybook');
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

    public function geofence()
    {
        $head['title'] = "Geofence";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $this->load->view('invoices/geofence');
        $this->load->view('fixed/footer');
    }

    public function print_customer_invoices($filter = "")
    {
        $head['title'] = "Print Invoices";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $this->load->view('reports/customer_invoices');
        $this->load->view('fixed/footer');
    }

    public function ajax_sale_list()
    {
        $list = $this->invocies->get_sa_datatables($this->limited);
        $list2 = $this->invocies->get_sa_datatables2($this->limited);
        // $list = array_merge($list,$list2);
        $data = array();
        $no = $this->input->post('start');
        $cat_list = explode(",", $this->input->post('start_cat'));
        foreach ($list as $invoices) {
            $sql = "SELECT `cat_id` FROM `geopos_invoice_items` WHERE `tid` = '$invoices->id'";
            $query = $this->db->query($sql);
            $cats = $query->result_array();
            $cat_ids = array_map(function ($item) {
                return $item["cat_id"];
            }, $cats);
            $intersect = array_intersect($cat_list, $cat_ids);

            if (!empty($intersect)) {

                $no++;

                $row = array();
                $row[] = $invoices->tid;
                $row[] = $invoices->name;
                $row[] = $invoices->company;
                $row[] = dateformat($invoices->invoicedate);

                $row[] = $invoices->total;
                $row[] = '<input type="checkbox"  class="checkbox"  name="receipt" data-id="' . $invoices->id . '" value="' . $invoices->id . '">';
                // $row[] = $cat_list;
                // $row[] = $cat_ids;
                $data[] = $row;
            }
        }
        foreach ($list2 as $invoices) {
            $sql = "SELECT `cat_id` FROM `geopos_invoice_items_bfr_post` WHERE `tid` = '$invoices->id'";
            $query = $this->db->query($sql);
            $cats = $query->result_array();
            $cat_ids = array_map(function ($item) {
                return $item["cat_id"];
            }, $cats);
            $intersect = array_intersect($cat_list, $cat_ids);

            if (!empty($intersect)) {

                $no++;

                $row = array();
                $row[] = $invoices->tid;
                $row[] = $invoices->name;
                $row[] = $invoices->company;
                $row[] = dateformat($invoices->invoicedate);

                $row[] = $invoices->total;
                $row[] = '<input type="checkbox"  class="checkbox"  name="unpostedreceipt" data-id="' . $invoices->id . '" value="' . $invoices->id . '">';
                // $row[] = $cat_list;
                // $row[] = $cat_ids;
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
        echo json_encode($output);
    }


    public function print_customer_invoices_merged()
    {


        $tids = explode(",", $_POST['hidden_fields']);
        $dirPath = 'userfiles/invoices/';
        $files = scandir($dirPath);
        $pdf_files = array_diff(scandir($dirPath), array('.', '..'));
        //MERGER FILES
        $pdf = new PDFMerger;
        if ($pdf_files) {
            foreach ($pdf_files as $file) {
                foreach ($tids as $tid) {
                    if (substr($file, 0, -4) == $tid) {
                        $pdf->addPDF('userfiles/invoices/' . $file, 'all');
                        // $pdf->addPDF('userfiles/invoices/' . $file, 'all');
                    }
                }
            }
            $new_file = md5(time() . rand(1, 10)) . '.pdf';
            $pdf->merge('file', APPPATH . '../userfiles/invoices/' . $new_file);
            $file =  APPPATH . '../userfiles/invoices/' . $new_file;
            $filename =  APPPATH . '../userfiles/invoices/' . $new_file;
            // Header content type
            header('Content-type: application/pdf');
            header('Content-Disposition: inline; filename="' . $filename . '"');
            header('Content-Transfer-Encoding: binary');
            header('Accept-Ranges: bytes');
            // Read the file
            @readfile($filename);
            //unlink($file);
        } else {
            $new_file = '';
            echo "Something went Wrong";
        }
    }



    public function proftloss()
    {

        $head['title'] = "Profit & Loss";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $this->load->view('reports/profit_loss');
        $this->load->view('fixed/footer');
    }


    public function export_profitloss()
    {
        ini_set('memory_limit', '-1');
        set_time_limit(0);

        $data = $this->reports->export_profitloss();

        $data_tr = "";
        $start_date = $_POST['hidden_start_date'];
        $end_date = $_POST['hidden_end_date'];
        $netsubtotal = 0;
        $subtotal = 0;
        $inv_g_total = 0;
        $profit_loss_g_total = 0;
        ob_end_clean();
        $no = 1;

        foreach ($data as $key => $row) {
            // echo"<pre>";
            // print_r($data);die();
            $inv_g_total += $row["inv_total"];
            $profit_loss_g_total += $row["inv_profit_loss"];

            $data_tr .= '
        <tr>
        <td>' . $no++ . '</td>';
            //     if($row["tid"]==0){
            //        $data_tr .='<td>1</td>';
            //    }else{
            //        $data_tr .='<td>'.round($row["items"]).'</td>';
            //    }
            $data_tr .= '<td>' . $row["inv_tid"] . '</td>';
            $data_tr .= '<td>' . $row["inv_date"] . '</td>
        <td>' . $row["cust_name"] . '</td>
        <td>' . amountExchange_s($row["inv_total"]) . '</td>
        <td>' . amountExchange_s($row["inv_profit_loss"]) . '</td>';

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
    <u><b> Cloud Billing Manager </b>  </u>
    </div>
    <div >
    <b><u> Profit&Loss: Customer Invoices (Summary)</u></b> 
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
    <u> No</u>
    </td>
    <td>
    <u> Inv&nbsp;No</u>
    </td>
    <td>
    <u> Date</u>
    </td>
    <td>
    <u>Customer Name</u>
    </td>
    <td>
    <u>Invoice Total</u>
    </td>
    <td>
    <u>Profit&Loss</u>
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
    <td style=''> Total:</td>
    <td style='border-top:2px solid black;border-bottom:2px solid black'> " . amountExchange_s($inv_g_total) . "</td>
    <td style='border-top:2px solid black;border-bottom:2px solid black'> " . amountExchange_s($profit_loss_g_total) . "</td>
    </tr>
    </table>
    </body>
    </html>
    ";
        //    die();


        $this->dpdf->loadHtml($html);
        $this->dpdf->render();
        $this->dpdf->stream("" . "Profit&Loss" . ".pdf", array("Attachment" => 0));
    }

















    //Statistics

    public function statistics()
    {
        $data['stat'] = $this->reports->statistics();
        $head['title'] = "Statistics";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $this->load->view('reports/stat', $data);
        $this->load->view('fixed/footer');
    }

    //accounts section

    public function accountstatement()

    {
        $this->load->model('transactions_model');
        $data['accounts'] = $this->transactions_model->acc_list();
        $head['title'] = "Account Statement";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $this->load->view('reports/statement', $data);
        $this->load->view('fixed/footer');
    }

    public function customerstatement()

    {
        $this->load->model('transactions_model');
        $data['accounts'] = $this->transactions_model->acc_list();
        $head['title'] = "Account Statement";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $this->load->view('reports/customer_statement', $data);
        $this->load->view('fixed/footer');
    }

    public function supplierstatement()

    {
        $this->load->model('transactions_model');
        $data['accounts'] = $this->transactions_model->acc_list();
        $head['title'] = "Account Statement";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $this->load->view('reports/supplier_statement', $data);
        $this->load->view('fixed/footer');
    }

    public function viewstatement()

    {
        $this->load->model('accounts_model', 'accounts');
        $pay_acc = $this->input->post('pay_acc');
        $trans_type = $this->input->post('trans_type');
        $sdate = datefordatabase($this->input->post('sdate'));
        $edate = datefordatabase($this->input->post('edate'));
        $ttype = $this->input->post('ttype');
        $account = $this->accounts->details($pay_acc);
        $data['filter'] = array($pay_acc, $trans_type, $sdate, $edate, $ttype, $account['holder']);
        $head['title'] = "Account Statement";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $this->load->view('reports/statement_list', $data);
        $this->load->view('fixed/footer');
    }

    public function customerviewstatement()

    {
        $this->load->model('customers_model', 'customer');
        $cid = $this->input->post('customer');
        $trans_type = $this->input->post('trans_type');
        $sdate = datefordatabase($this->input->post('sdate'));
        $edate = datefordatabase($this->input->post('edate'));
        $ttype = $this->input->post('ttype');
        $customer = $this->customer->details($cid);
        $data['filter'] = array($cid, $trans_type, $sdate, $edate, $ttype, $customer['name']);

        //  print_r( $data['statement']);
        $head['title'] = "Customer Account Statement";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $this->load->view('reports/customerstatement_list', $data);
        $this->load->view('fixed/footer');
    }

    public function supplierviewstatement()

    {
        $this->load->model('supplier_model', 'supplier');
        $cid = $this->input->post('supplier');
        $trans_type = $this->input->post('trans_type');
        $sdate = datefordatabase($this->input->post('sdate'));
        $edate = datefordatabase($this->input->post('edate'));
        $ttype = $this->input->post('ttype');
        $customer = $this->supplier->details($cid);
        $data['filter'] = array($cid, $trans_type, $sdate, $edate, $ttype, $customer['name']);

        //  print_r( $data['statement']);
        $head['title'] = "Supplier Account Statement";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $this->load->view('reports/supplierstatement_list', $data);
        $this->load->view('fixed/footer');
    }


    //

    public function statements()
    {

        $pay_acc = $this->input->post('ac');
        $trans_type = $this->input->post('ty');
        $sdate = datefordatabase($this->input->post('sd'));
        $edate = datefordatabase($this->input->post('ed'));
        $list = $this->reports->get_statements($pay_acc, $trans_type, $sdate, $edate);
        $balance = 0;

        foreach ($list as $row) {
            $balance += $row['credit'] - $row['debit'];
            echo '<tr><td>' . $row['date'] . '</td><td>' . $row['note'] . '</td><td>' . amountExchange($row['debit'], 0, $this->aauth->get_user()->loc) . '</td><td>' . amountExchange($row['credit'], 0, $this->aauth->get_user()->loc) . '</td><td>' . amountExchange($balance, 0, $this->aauth->get_user()->loc) . '</td></tr>';
        }
    }

    public function customerstatements()
    {


        $pay_acc = $this->input->post('ac');
        $trans_type = $this->input->post('ty');
        $sdate = datefordatabase($this->input->post('sd'));
        $edate = datefordatabase($this->input->post('ed'));


        $list = $this->reports->get_customer_statements($pay_acc, $trans_type, $sdate, $edate);
        $balance = 0;

        foreach ($list as $row) {
            $balance += $row['credit'] - $row['debit'];
            echo '<tr><td>' . $row['date'] . '</td><td>' . $row['note'] . '</td><td>' . amountExchange($row['debit'], 0, $this->aauth->get_user()->loc) . '</td><td>' . amountExchange($row['credit'], 0, $this->aauth->get_user()->loc) . '</td><td>' . amountExchange($balance, 0, $this->aauth->get_user()->loc) . '</td></tr>';
        }
    }

    public function supplierstatements()
    {


        $pay_acc = $this->input->post('ac');
        $trans_type = $this->input->post('ty');
        $sdate = datefordatabase($this->input->post('sd'));
        $edate = datefordatabase($this->input->post('ed'));


        $list = $this->reports->get_supplier_statements($pay_acc, $trans_type, $sdate, $edate);
        $balance = 0;

        foreach ($list as $row) {
            $balance += $row['debit'] - $row['credit'];
            echo '<tr><td>' . $row['date'] . '</td><td>' . $row['note'] . '</td><td>' . amountExchange($row['debit'], 0, $this->aauth->get_user()->loc) . '</td><td>' . amountExchange($row['credit'], 0, $this->aauth->get_user()->loc) . '</td><td>' . amountExchange($balance, 0, $this->aauth->get_user()->loc) . '</td></tr>';
        }
    }


    // income section


    public function incomestatement()

    {
        $head['title'] = "Income Statement";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);

        $this->load->model('transactions_model');
        $data['accounts'] = $this->transactions_model->acc_list();
        $data['income'] = $this->reports->incomestatement();


        $this->load->view('reports/incomestatement', $data);


        $this->load->view('fixed/footer');
    }


    public function customincome()
    {

        if ($this->input->post('check')) {
            $acid = $this->input->post('pay_acc');
            $sdate = datefordatabase($this->input->post('sdate'));
            $edate = datefordatabase($this->input->post('edate'));

            $date1 = new DateTime($sdate);
            $date2 = new DateTime($edate);

            $diff = $date2->diff($date1)->format("%a");
            if ($diff < 365) {
                $income = $this->reports->customincomestatement($acid, $sdate, $edate);

                echo json_encode(array('status' => 'Success', 'message' => 'Calculated', 'param1' => '<hr><b>Income between the dates is ' . amountExchange($income['credit'], 0, $this->aauth->get_user()->loc) . '</b>'));
            } else {
                echo json_encode(array('status' => 'Error', 'message' => 'Date range should be within 365 days', 'param1' => ''));
            }
        }
    }

    // expense section


    public function expensestatement()

    {
        $head['title'] = "Expense Statement";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);

        $this->load->model('transactions_model');
        $data['accounts'] = $this->transactions_model->acc_list();
        $data['income'] = $this->reports->expensestatement();


        $this->load->view('reports/expensestatement', $data);


        $this->load->view('fixed/footer');
    }


    public function customexpense()
    {

        if ($this->input->post('check')) {
            $acid = $this->input->post('pay_acc');
            $sdate = datefordatabase($this->input->post('sdate'));
            $edate = datefordatabase($this->input->post('edate'));

            $date1 = new DateTime($sdate);
            $date2 = new DateTime($edate);

            $diff = $date2->diff($date1)->format("%a");
            if ($diff < 365) {
                $income = $this->reports->customexpensestatement($acid, $sdate, $edate);

                echo json_encode(array('status' => 'Success', 'message' => 'Calculated', 'param1' => '<hr><b>Expense between the dates is ' . amountExchange($income['debit'], 0, $this->aauth->get_user()->loc) . '</b>'));
            } else {
                echo json_encode(array('status' => 'Error', 'message' => 'Date range should be within 365 days', 'param1' => ''));
            }
        }
    }


    public function refresh_data()

    {


        $head['title'] = "Refreshing Reports";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $this->load->view('reports/refresh_data');
        $this->load->view('fixed/footer');
    }

    public function refresh_process()

    {

        $this->load->model('cronjob_model');
        if ($this->cronjob_model->reports()) {

            echo json_encode(array('status' => 'Success', 'message' => $this->lang->line('Calculated')));
        }
    }

    public function taxstatement()

    {
        $this->load->model('transactions_model');
        $data['accounts'] = $this->transactions_model->acc_list();
        $head['title'] = "TAX Statement";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->model('locations_model');
        $data['locations'] = $this->locations_model->locations_list();
        $this->load->view('fixed/header', $head);
        $this->load->view('reports/tax_statement', $data);
        $this->load->view('fixed/footer');
    }

    public function taxviewstatement()

    {


        $trans_type = $this->input->post('ty');
        $sdate = datefordatabase($this->input->post('sdate'));
        $edate = datefordatabase($this->input->post('edate'));
        $lid = $this->input->post('lid');
        $data['filter'] = array($sdate, $edate, $trans_type, $lid);


        //  print_r( $data['statement']);
        $head['title'] = "TAX Statement";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $this->load->view('reports/tax_out', $data);
        $this->load->view('fixed/footer');
    }

    public function taxviewstatements_load()
    {


        $trans_type = $this->input->post('ty');
        $sdate = datefordatabase($this->input->post('sd'));
        $edate = datefordatabase($this->input->post('ed'));
        $lid = $this->input->post('loc');

        if ($trans_type == 'Sales') {
            $where = " WHERE (DATE(geopos_invoices.invoicedate) BETWEEN '$sdate' AND '$edate' )";
            if ($lid > 0) $where .= " AND (geopos_invoices.loc=$lid)";
            $query = $this->db->query("SELECT geopos_customers.taxid AS VAT_Number,geopos_invoices.tid AS invoice_number,geopos_invoices.total AS amount,geopos_invoices.tax AS tax,geopos_customers.name AS customer_name,geopos_customers.company AS Company_Name,geopos_invoices.invoicedate AS date FROM geopos_invoices LEFT JOIN geopos_customers ON geopos_invoices.csd=geopos_customers.id" . $where);
        } else {

            $where = " WHERE (DATE(geopos_purchase.invoicedate) BETWEEN '$sdate' AND '$edate') ";
            if ($lid > 0) $where .= " AND (geopos_invoices.loc=$lid)";
            $query = $this->db->query("SELECT geopos_supplier.taxid AS VAT_Number,geopos_purchase.tid AS invoice_number,geopos_purchase.total AS amount,geopos_purchase.tax AS tax,geopos_supplier.name AS customer_name,geopos_supplier.company AS Company_Name,geopos_purchase.invoicedate AS date FROM geopos_purchase LEFT JOIN geopos_supplier ON geopos_purchase.csd=geopos_supplier.id" . $where);
        }


        //echo $where;


        $balance = 0;

        foreach ($query->result_array() as $row) {
            $balance += $row['tax'];
            echo '<tr><td>' . $row['invoice_number'] . '</td><td>' . $row['customer_name'] . '</td><td>' . $row['VAT_Number'] . '</td><td>' . amountExchange($row['amount'], 0, $this->aauth->get_user()->loc) . '</td><td>' . amountExchange($row['tax'], 0, $this->aauth->get_user()->loc) . '</td><td>' . amountExchange($balance, 0, $this->aauth->get_user()->loc) . '</td></tr>';
        }
    }

    // profit section


    public function profitstatement()

    {
        $head['title'] = "Profit Statement";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);

        $this->load->model('locations_model');
        $data['locations'] = $this->locations_model->locations_list2();
        $data['income'] = $this->reports->profitstatement();


        $this->load->view('reports/profitstatement', $data);


        $this->load->view('fixed/footer');
    }


    public function customprofit()
    {

        if ($this->input->post('check')) {
            $lid = $this->input->post('pay_acc');
            $sdate = datefordatabase($this->input->post('sdate'));
            $edate = datefordatabase($this->input->post('edate'));

            $date1 = new DateTime($sdate);
            $date2 = new DateTime($edate);

            if ($this->aauth->get_user()->loc) {
                $lid = $this->aauth->get_user()->loc;
            }

            $diff = $date2->diff($date1)->format("%a");
            if ($diff < 365) {
                $income = $this->reports->customprofitstatement($lid, $sdate, $edate);

                echo json_encode(array('status' => 'Success', 'message' => 'Calculated', 'param1' => '<hr> Profit between the dates is ' . amountExchange($income['col1'], 0, $this->aauth->get_user()->loc) . ' '));
            } else {
                echo json_encode(array('status' => 'Error', 'message' => 'Date range should be within 365 days', 'param1' => ''));
            }
        }
    }

    // profit section


    public function sales()

    {
        $head['title'] = "Sales Statement";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);

        $this->load->model('locations_model');
        $data['locations'] = $this->locations_model->locations_list();
        $data['income'] = $this->reports->salesstatement();


        $this->load->view('reports/sales', $data);


        $this->load->view('fixed/footer');
    }


    public function customsales()
    {

        if ($this->input->post('check')) {
            $lid = $this->input->post('pay_acc');
            $sdate = datefordatabase($this->input->post('sdate'));
            $edate = datefordatabase($this->input->post('edate'));

            $date1 = new DateTime($sdate);
            $date2 = new DateTime($edate);

            if ($this->aauth->get_user()->loc) {
                $lid = $this->aauth->get_user()->loc;
            }

            $diff = $date2->diff($date1)->format("%a");
            if ($diff < 365) {
                $income = $this->reports->customsalesstatement($lid, $sdate, $edate);

                echo json_encode(array('status' => 'Success', 'message' => 'Calculated', 'param1' => '<hr> Sales between the dates is ' . amountExchange($income['total'], 0, $this->aauth->get_user()->loc) . ''));
            } else {
                echo json_encode(array('status' => 'Error', 'message' => 'Date range should be within 365 days', 'param1' => ''));
            }
        }
    }

    // products section
    public function products()

    {
        $head['title'] = "Products Statement";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $this->load->model('locations_model');
        $this->load->model('categories_model');
        $data['locations'] = $this->locations_model->locations_list();
        $data['cat'] = $this->categories_model->category_list();
        $data['income'] = $this->reports->productsstatement();
        $this->load->view('reports/products', $data);
        $this->load->view('fixed/footer');
    }


    public function customproducts()
    {
        if ($this->input->post('check')) {
            $lid = $this->input->post('pay_acc');
            $sdate = datefordatabase($this->input->post('sdate'));
            $edate = datefordatabase($this->input->post('edate'));
            $date1 = new DateTime($sdate);
            $date2 = new DateTime($edate);
            $diff = $date2->diff($date1)->format("%a");
            if ($this->aauth->get_user()->loc) {
                $lid = $this->aauth->get_user()->loc;
            }
            if ($diff < 365) {
                $income = $this->reports->customproductsstatement($lid, $sdate, $edate);
                echo json_encode(array('status' => 'Success', 'message' => 'Calculated', 'param1' => '<hr>Product Sales between the dates is ' . amountExchange($income['subtotal'], 0, $this->aauth->get_user()->loc) . ' <br> Qty between the dates is ' . amountFormat_general($income['qty']) . '.'));
            } else {
                echo json_encode(array('status' => 'Error', 'message' => 'Date range should be within 365 days', 'param1' => ''));
            }
        }
    }

    public function customproducts_cat()
    {
        if ($this->input->post('check')) {
            $lid = $this->input->post('pay_acc');
            $sdate = datefordatabase($this->input->post('sdate'));
            $edate = datefordatabase($this->input->post('edate'));
            $date1 = new DateTime($sdate);
            $date2 = new DateTime($edate);
            $diff = $date2->diff($date1)->format("%a");
            if ($this->aauth->get_user()->loc) {
                $lid = $this->aauth->get_user()->loc;
            }
            if ($diff < 365) {
                $income = $this->reports->customproductsstatement_cat($lid, $sdate, $edate);
                echo json_encode(array('status' => 'Success', 'message' => 'Calculated', 'param1' => '<hr>Product Sales between the dates is ' . amountExchange($income['subtotal'], 0, $this->aauth->get_user()->loc) . ' Qty between the dates is ' . amountFormat_general($income['qty']) . '.'));
            } else {
                echo json_encode(array('status' => 'Error', 'message' => 'Date range should be within 365 days', 'param1' => ''));
            }
        }
    }

    public function fetch_data()
    {
        if ($this->input->get('p')) {

            $data = $this->reports->fetchdata($this->input->get('p'));
            echo json_encode(array('status' => 'Success', 'message' => 'Calculated', 'p1' => $data['p1'], 'p2' => $data['p2'], 'p3' => $data['p3'], 'p4' => $data['p4']));
        }
    }

    public function commission()

    {
        if ($this->input->post('check')) {
            $lid = $this->input->post('pay_acc');
            $sdate = datefordatabase($this->input->post('sdate'));
            $edate = datefordatabase($this->input->post('edate'));

            $date1 = new DateTime($sdate);
            $date2 = new DateTime($edate);

            if ($this->aauth->get_user()->loc) {
                $lid = $this->aauth->get_user()->loc;
            }

            $diff = $date2->diff($date1)->format("%a");
            if ($diff < 365) {
                $commission = $this->reports->customcommission($lid, $sdate, $edate);

                echo json_encode(array('status' => 'Success', 'message' => 'Calculated', 'param1' => 'Commission between the dates is ' . amountExchange($commission, 0, $this->aauth->get_user()->loc)));
            } else {
                echo json_encode(array('status' => 'Error', 'message' => 'Date range should be within 365 days', 'param1' => ''));
            }
        } else {
            $head['title'] = "Commission";
            $head['usernm'] = $this->aauth->get_user()->username;
            $this->load->view('fixed/header', $head);

            $this->load->model('employee_model');
            $data['employee'] = $this->employee_model->list_employee();

            $this->load->view('reports/commission', $data);


            $this->load->view('fixed/footer');
        }
    }
}

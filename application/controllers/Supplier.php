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

class Supplier extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('supplier_model', 'supplier');
        $this->load->model('settings_model', 'settings');
        $this->load->library("Aauth");
        if (!$this->aauth->is_loggedin()) {
            redirect('/user/', 'refresh');
        }
        if (!$this->aauth->permission_new(null, 'suppliersAccess')) {

            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $this->li_a = 'stock';
        $this->load->library('dpdf');
        $this->load->library('XLSXWriter');
        $this->load->helper('download');
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

    public function index()
    {
        if (!$this->aauth->permission_new(null, 'suppliersManageSuppliers')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $head['usernm'] = $this->aauth->get_user()->username;
        $head['title'] = 'Supplier';
        $this->load->view('fixed/header', $head);
        $this->load->view('supplier/clist');
        $this->load->view('fixed/footer');
    }

    public function create()
    {
        if (!$this->aauth->permission_new(null, 'suppliersNewSupplier')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $data['customergrouplist'] = $this->supplier->group_list();
        $head['usernm'] = $this->aauth->get_user()->username;
        $head['title'] = 'Create Supplier';
        $this->load->view('fixed/header', $head);
        $this->load->view('supplier/create', $data);
        $this->load->view('fixed/footer');
    }

    public function view()
    {
        if (!$this->aauth->permission_new(null, 'suppliersManageSuppliers')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $custid = $this->input->get('id');
        $data['details'] = $this->supplier->details($custid);
        $data['customergroup'] = $this->supplier->group_info($data['details']['gid']);
        $data['money'] = $this->supplier->money_details($custid);
        $data['supplier_amount'] = $this->supplier->supplier_ammount($custid);
        $data['expense']  = $data['supplier_amount']['expense'];
        $data['total']  = $data['supplier_amount']['total'];
        $data['pamnt']  = $data['supplier_amount']['pamnt'];
        // dd($data['expense']);
        $head['usernm'] = $this->aauth->get_user()->username;
        $head['title'] = 'View Supplier';
        $this->load->view('fixed/header', $head);
        if ($data['details']['id']) $this->load->view('supplier/view', $data);
        $this->load->view('fixed/footer');
    }
    public function view_()
    {
        $custid = $this->input->get('id');
        $data['details'] = $this->supplier->details($custid);
        $data['customergroup'] = $this->supplier->group_info($data['details']['gid']);
        $data['money'] = $this->supplier->money_details($custid);
        $data['supplier_amount'] = $this->supplier->supplier_ammount($custid);
        $data['expense']  = $data['supplier_amount']['expense'];
        $data['total']  = $data['supplier_amount']['total'];
        $data['pamnt']  = $data['supplier_amount']['pamnt'];
        $head['usernm'] = $this->aauth->get_user()->username;
        $head['title'] = 'View Supplier';
        $this->load->view('fixed/header', $head);
        if ($data['details']['id']) $this->load->view('supplier/view_', $data);
        $this->load->view('fixed/footer');
    }
    function statement()
    {
        $id = $this->input->post('id');
        if ($this->input->post()) {
            ob_end_clean();
            ob_start();
            $company_ = $this->settings->company_details(1);
            $this->load->model('reports_model');
            $_id = $this->input->post('supplier');
            $data['supplier_amount'] = $this->supplier->supplier_ammount($_id);
            $data['expense']  = $data['supplier_amount']['expense'];
            // var_dump($id); die();
            //$sql="select credit,debit,id from geopos_transactions where payerid=$customer order by date asc";
            $sql = "select sum(credit) as credit,sum(debit) as debit,id,tid from geopos_transactions where supplier=$_id group by credit,debit order by date ASC";
            //  $sql="select sum(credit) as credit,sum(debit) as debit,id,date from geopos_transactions where payerid=$customer group by credit,debit,date";

            $query = $this->db->query($sql);
            $response = $query->result_array();
            //  echo "<pre>";
            // print_r($response);
            // die();
            $balance_array = [];
            $balance = 0;
            $i = 0;
            foreach ($response as $key => $value) {
                // if($i == 0) {
                //     $balance+= $value['credit'] == '0.00' ? $value['debit'] : $value['credit'];
                // } else 
                $balance += $value['credit'] - $value['debit'];
                $balance_array[$key]['balance'] = $balance;
                $balance_array[$key]['id'] = $value['id'];
                $i++;
            }
            // exit;
            // foreach($response as $key => $value){
            //     if($value['credit']==0.00 || $value['credit']==null || $value['credit']==""){
            //         if($key==0){
            //             $balance_array[$key]['id']=$value['id'];
            //             $balance_array[$key]['balance']=$value['debit'];
            //         }else{
            //             $balance_array[$key]['id']=$value['id'];
            //             $balance_array[$key]['balance']=$balance_array[$key-1]['balance']-$value['debit'];
            //         }
            //     }
            //     if($value['debit']==0.00 || $value['debit']==null || $value['debit']==""){
            //         if($key==0){
            //             $balance_array[$key]['id']=$value['id'];
            //             $balance_array[$key]['balance']=$value['credit'];
            //         }else{
            //             $balance_array[$key]['id']=$value['id'];
            //             $balance_array[$key]['balance']=$balance_array[$key-1]['balance']+$value['credit'];
            //         }
            //     } 
            // }
            // echo"<pre>";
            // print_r(array_search(14053, array_column($balance_array, 'id')));die();
            // die();
            //

            // $inv_status_check= $response[0]['status'];

            $sql = 'SELECT sum(total)-sum(pamnt) as "cust_balance" FROM geopos_purchase WHERE status="due" and csd="' . $_id . '" or status="partial" and csd=' . $_id;
            $query = $this->db->query($sql);
            // var_dump($query);
            // die();
            $response = $query->result_array();
            $cust_balance_inv = $response[0]['cust_balance'];


            $trans_type = $this->input->post('trans_type');
            $sdate = datefordatabase($this->input->post('sdate'));
            $edate = datefordatabase($this->input->post('edate'));
            // var_dump($sdate, $edate); die();
            $data['customer'] = $this->supplier->details($_id);
            $data['list'] = $this->reports_model->get_supplier_statements($_id, $trans_type, $sdate, $edate);
            // echo "<pre>";
            // var_dump($data['list']);
            // exit;
            $company = $data['customer']['company'];
            $address = $data['customer']['address'];
            $postbox = $data['customer']['postbox'];

            $city = $data['customer']['city'];
            $region = $data['customer']['region'];
            $country = $data['customer']['country'];
            // $phone_s = $data['customer']['phone_s'];
            // $email_s = $data['customer']['email_s'];
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
                // if ($row["date"] >= $sdate && $row["date"] <= $edate) {
                if ($row["tid"] >= 1) {




                    //          if($row["note"]=='Product Invoice' && $row["debit"]<>"0.00"){

                    //         $lbl =  "Receipt";
                    //         }
                    //         else{
                    //             $lbl=$row["note"]." ".$row["paymt_method"];
                    //         }

                    //         if($row["debit"]<>"0.00"){
                    //         $date =  $row["paymt_date"];

                    // }
                    // else{
                    //     $date =  $row["date"];
                    // }
                    //<td>' . $row["date"] . '</td>

                    $data_tr .= '
                <tr>
               
                <td>' . $row["date"] . '</td>
                <td>' . $row["inv_id"] . '</td>
                <td>' . $row["method"] . '</td>
                <td>' . $row["note"] . " " . $row["paymt_method"] . '</td>
                <td>' . $row["credit"] . '</td>
                <td>' . $row["debit"] . '</td>
                
                </tr>';
                } else {

                    //                if($row["note"]=='Product Invoice' && $row["debit"]<>"0.00"){

                    //         $lbl =  "Receipt";
                    //         }
                    //         else{
                    //             $lbl=$row["note"]." ".$row["paymt_method"];
                    //         }

                    //         if($row["debit"]<>"0.00"){
                    //         $date =  $row["paymt_date"];

                    // }
                    // else{
                    //     $date =  $row["date"];
                    // }



                    $data_tr .= '
                <tr>
                <td>' . $row["date"] . '</td>
                <td>' . $row["inv_id"] . '</td>
                <td>' . $row["method"] . '</td>
                <td>' . $row["note"] . " " . $row["paymt_method"] . '</td>
                <td>' . $row["credit"] . '</td>
                <td>' . $row["debit"] . '</td>
                
                </tr>';
                }
                //}
                // }
            }
            $date = date("d/m/y");
            $time = date("h:i:s");
            // $prev_balance = $this->aauth->get_customer($data['customer']['id']);
            // $sql='SELECT sum(total)-sum(pamnt) as "cust_balance" FROM geopos_invoices WHERE status="due" and csd="'.$data['customer']['id'].'" or status="partial" and csd="'.$data['customer']['id'].'"';    
            // $query = $this->db->query($sql);
            // $response=$query->result_array();
            // $prev_balance= $response[0]['cust_balance'];
            $prev_balance = 0; // Default value in case $balance_array is empty
            if (!empty($balance_array)) {
                $last_index = count($balance_array) - 1;
                if (isset($balance_array[$last_index]['balance'])) {
                    $prev_balance = $balance_array[$last_index]['balance'];
                }
            }
            $prev_balance = number_format($prev_balance, 2);

            // $prev_balance = $balance_array[count($balance_array) - 1]['balance'];
            // $prev_balance = number_format($prev_balance, 2);
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
                        <div>" . $company_['cname'] . "</div>
                        <div>" . $company_['address'] . "</div>
                        <div>" . $company_['region'] . ', ' . $company_['city'] . ' ' . $company_['postbox'] . "</div>
                        <div>" . $company_['country'] . "</div>
                        <br>
                        <div>Tel:  " . $company_['phone'] . "</div>
                        <div>Email: " . $company_['email'] . "</div>
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
                                        Supplier Name
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
                            Reference #
                        </th>
                        <th>
                            Payment Method
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
                            <td><span style='float:right'>&#163; " . $data['expense'] . "</span></td>
                        </tr>
                    </table>
                </div>
                <div>
                </body>
                </html>
                ";

            if ($this->input->post('excel')) {
                $excelData = array(
                    array('Date', 'Details', 'Debit', 'Credit')
                );
                // $array_data = $this->reports_model->get_customer_statements($customer, $trans_type, $sdate, $edate);
                $array_data = $this->reports_model->get_supplier_statements($_id, $trans_type, $sdate, $edate);
                // var_dump($array_data);exit();
                foreach ($array_data as $row) {
                    array_push(
                        $excelData,
                        array($row["date"], $row["note"], $row["credit"], $row["debit"])
                    );
                }

                array_push($excelData, array('', '', '', '', ''));

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
            $data['details'] = $this->supplier->details($data['id']);
            $head['title'] = "Account Statement";
            $head['usernm'] = $this->aauth->get_user()->username;
            $this->load->view('fixed/header', $head);
            $this->load->view('supplier/statement', $data);
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
    public function vatreport()
    {
        $company = $this->settings->company_details(1);
        $data_tr = "";
        $total_qty = 0;
        $total_amount = 0;
        $data['id'] = $this->input->get('id');

        ob_end_clean();
        $no = 1;

        $this->db->select('geopos_purchase.invoicedate, geopos_supplier.name as supplier, geopos_purchase.status, geopos_purchase_items.product, geopos_purchase.tax');
        $this->db->from('geopos_purchase');
        $this->db->join('geopos_purchase_items', 'geopos_purchase.id = geopos_purchase_items.tid');
        $this->db->join('geopos_supplier', 'geopos_supplier.id = geopos_purchase.csd');
        $this->db->where('geopos_purchase.csd', $data['id']);

        $query = $this->db->get();
        $result = $query->result_array();

        foreach ($result as $row) {
            $data_tr .= '
                <tr>
                <td><b>' . $row["product"] . '</b></td>
                <td><b>' . $row["status"] . '</b></td>
                <td><b>' . amountExchange_s($row["tax"]) . '</b></td>
                <td><b>' . $row["invoicedate"] . '</b></td>
                </tr>
            ';
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
                        <div>
                            <b><u> Supplier VAT Report</u></b> 
                        </div>
                    </div>
                    <div class='right_header'>
                        <b><span class='pagenum'>Page:</span></b>
                    </div>
                </div>
                <div style='font-size:13px;'>
                    <div style='float:left'>
                        <div><b>Supplier: </b>" . $row['supplier'] . " </div>
                    </div>
                    </div>
                    <div style='clear:both'></div>
                </div>
                <br>
                <table>
                    <thead style='font-size:13px;'>
                        <tr>
                        <td><u>Product</u></td>
                        <td><u>Status</u></td>
                        <td><u>VAT Amount</u></td>
                        <td><u>Invoice Date</u></td>
                        </tr>
                    </thead>
                    <tbody style='font-size:13px;'>
                        $data_tr
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </body>
            </html>
        ";

        $this->dpdf->loadHtml($html);
        $this->dpdf->render();
        $this->dpdf->stream("" . "Stock Report" . ".pdf", array("Attachment" => 0));
    }

    public function getAllSuppliers()
    {
        // Fetch all suppliers from your database (adjust this based on your actual database schema)
        $suppliers = $this->Supplier_model->getAllSuppliers();

        // Send the JSON response to the client
        header('Content-Type: application/json');
        echo json_encode($suppliers);
    }

    public function getSuppliers()
    {
        $start_date = $this->input->post('start_date'); // Get the selected start date from the Ajax request
        $end_date = $this->input->post('end_date'); // Get the selected end date from the Ajax request

        // Fetch the list of suppliers based on the selected date range (adjust this based on your actual database schema)
        $suppliers = $this->Supplier_model->getSuppliersByDateRange($start_date, $end_date);

        // Send the JSON response to the client
        header('Content-Type: application/json');
        echo json_encode($suppliers);
    }

    public function load_list()
    {
        $list = $this->supplier->get_datatables();
        $data = array();
        $no = $this->input->post('start');
        $totalFiltered = $this->supplier->count_filtered();
        
        foreach ($list as $customers) {

            $this->db->select('SUM(total) AS total, SUM(pamnt) AS pamnt, (SUM(total) - SUM(pamnt)) AS expense, COUNT(*) AS po_count');
            $this->db->from('geopos_purchase');
            $this->db->where('csd', $customers->id);
            $query = $this->db->get();
            $result = $query->row_array();


            $expense = $result['expense'];
            $total = $result['total'];
            $pamnt = $result['pamnt'];
            $po_count = isset($result['po_count']) ? (int)$result['po_count'] : 0;

            $no++;

            // Build action buttons
            $actionButtons = '<a href="supplier/view?id=' . $customers->id . '" class="btn btn-info btn-sm"><span class="fa fa-eye"></span> ' . $this->lang->line('View') . '</a> <a href="supplier/edit?id=' . $customers->id . '" class="btn btn-primary btn-sm"><span class="fa fa-pencil"></span> ' . $this->lang->line('Edit') . '</a>';

            // Add delete button for all rows (check purchase orders)
         
                // Show active delete button
                $actionButtons .= ' <a href="#" data-object-id="' . $customers->id . '" class="btn btn-danger btn-sm delete-object"><span class="fa fa-trash"></span></a>';
            

            $row = array();
            $row[] = $no;
            $row[] = '<a href="supplier/view?id=' . $customers->id . '">' . $customers->name . '</a>';
            $row[] = $customers->address . ',' . $customers->city . ',' . $customers->country;
            $row[] = $customers->email;
            $row[] = $customers->phone;

            $row[] = amountExchange($pamnt, 0, $this->aauth->get_user()->loc);
            $row[] = amountExchange($expense, 0, $this->aauth->get_user()->loc);

            $row[] = $actionButtons;

            $data[] = $row;
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->supplier->count_all(),
            "recordsFiltered" => $totalFiltered,
            "data" => $data,
        );
        //output to json format
        echo json_encode($output);
    }

    //edit section
    public function edit()
    {
        if (!$this->aauth->permission_new(null, 'suppliersEditSupplier')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $pid = $this->input->get('id');

        $data['customer'] = $this->supplier->details($pid);
        $data['customergroup'] = $this->supplier->group_info($pid);
        $data['customergrouplist'] = $this->supplier->group_list();
        $head['usernm'] = $this->aauth->get_user()->username;
        $head['title'] = 'Edit Supplier';
        $this->load->view('fixed/header', $head);
        $this->load->view('supplier/edit', $data);
        $this->load->view('fixed/footer');
    }

    public function addsupplier()
    {
        $name = $this->input->post('name', true);
        $company = $this->input->post('company', true);
        $phone = $this->input->post('phone', true);
        $phone1 = $this->input->post('phone1', true);
        $email = $this->input->post('email', true);
        $email2 = $this->input->post('email2', true);
        $email3 = $this->input->post('email3', true);
        $address = $this->input->post('address', true);
        $city = $this->input->post('city', true);
        $region = $this->input->post('region', true);
        $country = $this->input->post('country', true);
        $postbox = $this->input->post('postbox', true);
        $taxid = $this->input->post('taxid', true);

        $this->supplier->add($name, $company, $phone, $phone1, $email, $email2, $email3, $address, $city, $region, $country, $postbox, $taxid);
    }

    public function editsupplier()
    {
        $id = $this->input->post('id', true);
        $name = $this->input->post('name', true);
        $company = $this->input->post('company', true);
        $phone = $this->input->post('phone', true);
        $phone1 = $this->input->post('phone1', true);
        $email = $this->input->post('email', true);
        $email2 = $this->input->post('email2', true);
        $email3 = $this->input->post('email3', true);
        $address = $this->input->post('address', true);
        $city = $this->input->post('city', true);
        $region = $this->input->post('region', true);
        $country = $this->input->post('country', true);
        $postbox = $this->input->post('postbox', true);
        $taxid = $this->input->post('taxid', true);

        if ($id) {
            $result = $this->supplier->edit($id, $name, $company, $phone, $phone1, $email, $email2, $email3, $address, $city, $region, $country, $postbox, $taxid);
            
            // Handle the response properly in controller instead of model
            if ($result) {
                $message = $this->lang->line('UPDATED');
                if (!$message || $message === FALSE) {
                    $message = 'Supplier details updated successfully!';
                }
                echo json_encode(array('status' => 'Success', 'message' => $message));
            } else {
                $message = $this->lang->line('ERROR');
                if (!$message || $message === FALSE) {
                    $message = 'Failed to update supplier details!';
                }
                echo json_encode(array('status' => 'Error', 'message' => $message));
            }
        } else {
            echo json_encode(array('status' => 'Error', 'message' => 'Supplier ID is required'));
        }
    }


    public function delete_i()
    {
        if (!$this->aauth->permission_new(null, 'suppliersDeleteSupplier')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $id = $this->input->post('deleteid');

        // Check if supplier has purchase orders
        $this->db->select('COUNT(*) AS po_count');
        $this->db->from('geopos_purchase');
        $this->db->where('csd', $id);
        $query = $this->db->get();
        $result = $query->row_array();
        $po_count = isset($result['po_count']) ? (int)$result['po_count'] : 0;

        if ($po_count > 0) {
            echo json_encode(array('status' => 'Error', 'message' => 'Cannot delete supplier: ' . $po_count . ' purchase order(s) exist. Please delete all purchase orders first.'));
            return;
        }

        if ($this->supplier->delete($id)) {
            echo json_encode(array('status' => 'Success', 'message' => $this->lang->line('DELETED')));
        } else {
            echo json_encode(array('status' => 'Error', 'message' => $this->lang->line('ERROR')));
        }
    }

    public function displaypic()
    {
        $id = $this->input->get('id');
        $this->load->library("uploadhandler", array(
            'accept_file_types' => '/\.(gif|jpe?g|png)$/i',
            'upload_dir' => FCPATH . 'userfiles/customers/'
        ));
        $img = (string)$this->uploadhandler->filenaam();
        if ($img != '') {
            $this->supplier->editpicture($id, $img);
        }
    }


    public function translist()
    {
        $cid = $this->input->post('cid');
        $list = $this->supplier->trans_table($cid);
        $data = array();
        // $no = $_POST['start'];
        $no = $this->input->post('start');
        foreach ($list as $prd) {
            $no++;
            $row = array();
            $pid = $prd->id;
            $row[] = $prd->date;
            $row[] = $prd->inv_id;
            $row[] = amountExchange($prd->debit, 0, $this->aauth->get_user()->loc);
            $row[] = amountExchange($prd->credit, 0, $this->aauth->get_user()->loc);
            $row[] = $prd->account;
            // $row[] = $prd->payer;
            // $row[] = $this->lang->line($prd->method);
            // $row[] = $prd->note;
            $row[] = '<a href="' . base_url("purchase/view?id=$prd->tid") . '">' . $prd->note . '</a>';
            // $row[] = '<a href="' . base_url() . 'transactions/view?id=' . $pid . '" class="btn btn-primary btn-xs"><span class="fa fa-eye"></span> ' . $this->lang->line('View') . '</a> <a href="#" data-object-id="' . $pid . '" class="btn btn-danger btn-xs delete-object"><span class="fa fa-trash"></span> ' . $this->lang->line('Delete') . '</a>';
            $data[] = $row;
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->supplier->count_all(),
            "recordsFiltered" => $this->supplier->trans_count_filtered($cid),
            "data" => $data,
        );
        //output to json format
        echo json_encode($output);
    }

    public function inv_list()
    {
        $cid = $this->input->post('cid');
        $list = $this->supplier->inv_datatables($cid);
        $data = array();

        $no = $this->input->post('start');

        foreach ($list as $invoices) {
            $no++;
            $row = array();
            $row[] = $no;
            $row[] = $invoices->tid;
            $row[] = $invoices->invoice_ref;

            $row[] = $invoices->invoicedate;
            $row[] = amountExchange($invoices->total, 0, $this->aauth->get_user()->loc);
            $row[] = '<span class="st-' . $invoices->status . '">' . $this->format_status_text($invoices->status) . '</span>';
            $row[] = '<a href="' . base_url("purchase/view?id=$invoices->id") . '" class="btn btn-success btn-xs"><i class="fa fa-eye"></i> ' . $this->lang->line('View') . '</a> &nbsp; <a href="' . base_url("purchase/invoice_export?id=$invoices->id") . '&d=1" target="_blank" class="btn btn-info btn-xs"  title="Download"><span class="fa fa-download"></span></a>&nbsp; &nbsp;<a href="#" data-object-id="' . $invoices->id . '" class="btn btn-danger btn-xs delete-object"><span class="fa fa-trash"></span></a>';
            $data[] = $row;
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->supplier->inv_count_all($cid),
            "recordsFiltered" => $this->supplier->inv_count_filtered($cid),
            "data" => $data,
        );
        //output to json format
        echo json_encode($output);
    }


    public function transactions()
    {
        $custid = $this->input->get('id');
        $data['details'] = $this->supplier->details($custid);
        $data['money'] = $this->supplier->money_details($custid);
        $head['usernm'] = $this->aauth->get_user()->username;
        $head['title'] = 'View Supplier';
        $this->load->view('fixed/header', $head);
        $this->load->view('supplier/transactions', $data);
        $this->load->view('fixed/footer');
    }

    public function invoices()
    {
        $custid = $this->input->get('id');
        $data['details'] = $this->supplier->details($custid);

        $data['money'] = $this->supplier->money_details($custid);
        $head['usernm'] = $this->aauth->get_user()->username;
        $head['title'] = 'View Supplier Invoices';
        $this->load->view('fixed/header', $head);
        $this->load->view('supplier/invoices', $data);
        $this->load->view('fixed/footer');
    }

    public function bulkpayment()
    {
        if (!$this->aauth->permission_new(null, 'suppliersBulkPayment')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $data['id'] = $this->input->get('id');
        $data['details'] = $this->supplier->details($data['id']);
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->model('accounts_model');
        $data['acclist'] = $this->accounts_model->accountslist((int)$this->aauth->get_user()->loc);
        $this->session->set_userdata("cid", $data['id']);
        $head['title'] = 'Bulk Payment Invoices';
        $this->load->view('fixed/header', $head);
        $this->load->view('supplier/bulkpayment', $data);
        $this->load->view('fixed/footer');
    }

    public function bulk_post()
    {
        if (!$this->aauth->permission_new(null, 'suppliersBulkPayment')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $csd = $this->input->post('customer', true);
        $sdate = datefordatabase($this->input->post('sdate'));
        $edate = datefordatabase($this->input->post('edate'));
        $trans_type = $this->input->post('trans_type', true);
        $data['details'] = $this->supplier->sales_due($sdate, $edate, $csd, $trans_type);

        $due = $data['details']['total'] - $data['details']['pamnt'];
        echo json_encode(array('status' => 'Success', 'message' => $this->lang->line('Calculated') . ' ' . amountExchange($due), 'due' => amountExchange_s($due)));
    }

    public function bulk_post_payment()
    {
        if (!$this->aauth->permission_new(null, 'suppliersBulkPayment')) {
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
        $data['details'] = $this->supplier->sales_due($sdate, $edate, $csd, $trans_type, false, $amount, $account, $pay_method, $note);

        $due = 0;
        echo json_encode(array('status' => 'Success', 'message' => $this->lang->line('Paid') . ' ' . amountExchange($amount), 'due' => amountExchange_s($due)));
    }

    // AJAX Methods for Supplier Card Navigation
    public function ajax_view_details()
    {
        if (!$this->aauth->permission_new(null, 'suppliersViewDetails')) {
            echo '<div class="alert alert-danger">Sorry! You have insufficient permissions to access this section</div>';
            return;
        }
        
        $custid = $this->input->post('id');
        $data['details'] = $this->supplier->details($custid);
        $data['customergroup'] = $this->supplier->group_info($data['details']['gid']);
        $data['money'] = $this->supplier->money_details($custid);
        $data['supplier_amount'] = $this->supplier->supplier_ammount($custid);
        $data['expense'] = $data['supplier_amount']['expense'];
        $data['total'] = $data['supplier_amount']['total'];
        $data['pamnt'] = $data['supplier_amount']['pamnt'];
        
        $this->load->view('supplier/ajax_view_details', $data);
    }

    public function ajax_invoices()
    {
        if (!$this->aauth->permission_new(null, 'suppliersViewPO')) {
            echo '<div class="alert alert-danger">Sorry! You have insufficient permissions to access this section</div>';
            return;
        }
        
        $custid = $this->input->post('id');
        $data['details'] = $this->supplier->details($custid);
        $data['money'] = $this->supplier->money_details($custid);
        
        $this->load->view('supplier/ajax_invoices', $data);
    }

    public function ajax_transactions()
    {
        if (!$this->aauth->permission_new(null, 'suppliersViewActivity')) {
            echo '<div class="alert alert-danger">Sorry! You have insufficient permissions to access this section</div>';
            return;
        }
        
        $custid = $this->input->post('id');
        $data['details'] = $this->supplier->details($custid);
        $data['money'] = $this->supplier->money_details($custid);
        
        $this->load->view('supplier/ajax_transactions', $data);
    }

    public function ajax_statement()
    {
        if (!$this->aauth->permission_new(null, 'suppliersAccountStatements')) {
            echo '<div class="alert alert-danger">Sorry! You have insufficient permissions to access this section</div>';
            return;
        }
        
        $data['id'] = $this->input->post('id');
        $data['details'] = $this->supplier->details($data['id']);
        
        $this->load->view('supplier/ajax_statement', $data);
    }
}

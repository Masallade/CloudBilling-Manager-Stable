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


use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Csv;


class Products extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library("Aauth");
        $this->load->library("dpdf");
        $this->load->library('upload');
        $this->load->helper('url');
        $this->load->library('table');
        if (!$this->aauth->is_loggedin()) {
            redirect('/user/', 'refresh');
        }
        if (!$this->aauth->permission_new(null, 'stockAccess')) {

            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $this->load->model('invoices_model', 'invocies');
        $this->load->model('plugins_model', 'plugins');
        $this->load->model('customers_model', 'customers');
        $this->load->model('products_model', 'products');
        $this->load->model('categories_model');
        $this->load->library("Custom");
        $this->load->library("dpdf");
        $this->load->library('XLSXWriter');
        $this->load->helper('download');
        $this->li_a = 'stock';
    }
    function stock_adjustment()
    {
        if (!$this->aauth->permission_new(null, 'stockAdjustment')) {

            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $this->load->helper(array('form'));
        $this->load->model('categories_model');
        $head['title'] = "Stock Adjustment";
        $head['usernm'] = $this->aauth->get_user()->username;
        $data['cat'] = $this->categories_model->category_list();
        $data['warehouse'] = $this->categories_model->warehouse_list();
        $this->load->view('fixed/header', $head);
        $this->load->view('import/stock_adjustment', $data);
        $this->load->view('fixed/footer');
    }
    public function index()
    {
        if (!$this->aauth->permission_new(null, 'stockManageProducts')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $stock_filter = $this->input->get('stock_filter'); // Get the filter from the query string
        $stock_data = $this->categories_model->product_stock(); // Get total sums

        // Convert totals to the right currency format
        $data['salessum'] = amountExchange($stock_data['total_salessum'], $this->aauth->get_user()->loc);
        $data['worthsum'] = amountExchange($stock_data['total_purchasesum'], $this->aauth->get_user()->loc);

        $data['stock_out_filter'] = $stock_filter; // Set filter to '2' for out-of-stock products
        $head['title'] = "Products";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $this->load->view('products/products', $data);
        $this->load->view('fixed/footer');
    }

    public function cat()
    {
        if (!$this->aauth->permission_new(null, 'stockProductCategories')) {

            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $head['title'] = "Product Categories";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $this->load->view('products/cat_productlist');
        $this->load->view('fixed/footer');
    }


    public function add()
    {
        if (!$this->aauth->permission_new(null, 'stockNewProduct')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $data['cat'] = $this->categories_model->category_list();
        $data['units'] = $this->products->units();
        $data['warehouse'] = $this->categories_model->warehouse_list();
        // var_dump($data['warehouse']); die();
        $data['custom_fields'] = $this->custom->add_fields(4);
        $this->load->model('units_model', 'units');
        $data['variables'] = $this->units->variables_list();
        $head['title'] = "Add Product";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $this->load->view('products/product-add', $data);
        $this->load->view('fixed/footer');
    }

    public function products_report()
    {
        $company = $this->settings->company_details(1);
        $data_tr = "";
        $total_quanity = 0;
        $id = $_POST['prod_id'];
        $s_date = $_POST['start_date'];
        $e_date = $_POST['end_date'];
        $start = date("Y-m-d H:i:s", strtotime($_POST['start_date']));
        //   $end=date("Y-m-d H:i:s", strtotime($_POST['end_date']));

        $end = date("Y-m-d H:i:s", strtotime($_POST['end_date'] . ' 23:59:59'));

        $stock_returned = 0;
        $stock_invoiced = 0;
        $stock_added = 0;
        $this->db->select('products_history.product_name, products_history.product_price,products_history.fproduct_price,products_history.qty,products_history.added_by');
        $this->db->from('products_history');
        $this->db->where('products_history.pid', $id);
        $this->db->where('products_history.added_on >=', $start);
        $this->db->where('products_history.added_on <=', $end);
        $query = $this->db->get();
        $pids = $query->result_array();

        ob_end_clean();
        $no = 1;
        foreach ($pids as $key => $row) {
            $stock_added += $row["qty"];
            $data_tr .= '
          <tr>
          <td>' . $no++ . '</td>';
            $data_tr .= '
          <td>' . $row["product_name"] . '</td>
          <td></td>
          <td>' . amountExchange_s($row["product_price"]) . '</td>
          <td>' . amountExchange_s($row["fproduct_price"]) . '</td>
          <td>' . intval($row["qty"]) . '</td>
          <td>Stock In</td>
          <td>' . $row["added_by"] . '</td>
          ';

            $data_tr .= '</tr>';
        }
        $customer_name = "";
        $added_by = "";
        ob_end_clean();
        $this->db->select('geopos_stock_r_items.price, geopos_stock_r_items.qty,geopos_stock_r_items.product_des,geopos_stock_r_items.product,geopos_stock_r_items.pid,geopos_stock_r_items.tid');
        $this->db->from('geopos_stock_r_items');
        $this->db->where('geopos_stock_r_items.pid', $id);
        $this->db->where('geopos_stock_r_items.added_on >=', $start);
        $this->db->where('geopos_stock_r_items.added_on <=', $end);
        $query = $this->db->get();
        $pids2 = $query->result_array();

        if (isset($pids2)) {


            foreach ($pids2 as $key => $row) {

                $sql2 = "SELECT csd,eid FROM `geopos_stock_r` where id=" . $row["tid"];
                $query2 = $this->db->query($sql2);
                $response2 = $query2->result_array();
                if (isset($response2[0]['csd'])) {

                    $customer_id = $response2[0]['csd'];
                    $created_by = $response2[0]['eid'];
                    $sql2 = "SELECT username FROM `geopos_users` where id=" . $created_by;
                    $query2 = $this->db->query($sql2);
                    $response2 = $query2->result_array();
                    $added_by = $response2[0]['username'];
                    $sql2 = "SELECT company FROM `geopos_customers` where id=" . $customer_id;
                    $query2 = $this->db->query($sql2);
                    $response2 = $query2->result_array();
                    $customer_name = $response2[0]['company'];
                }
                $stock_returned += $row["qty"];
                $data_tr .= '
          <tr>
          <td>' . $no++ . '</td>';
                $data_tr .= '
          <td>' . $row["product_des"] . '</td>
          <td>' . $customer_name . '</td>
          <td></td>
          <td>' . amountExchange_s($row["price"]) . '</td>
          <td>' . intval($row["qty"]) . '</td>
          <td>Stock Returned</td>
          <td>' . $added_by . '</td>
          ';

                $data_tr .= '</tr>';
            }
        }



        ob_end_clean();
        $this->db->select('geopos_invoice_items.product_des, geopos_invoice_items.price,geopos_invoice_items.qty,geopos_invoice_items.tid');
        $this->db->from('geopos_invoice_items');
        $this->db->where('geopos_invoice_items.pid', $id);
        $this->db->where('geopos_invoice_items.added_on >=', $start);
        $this->db->where('geopos_invoice_items.added_on <=', $end);
        $query = $this->db->get();
        $pids2 = $query->result_array();
        if (isset($pids2)) {
            foreach ($pids2 as $key => $row) {
                $sql2 = "SELECT cust_name,created_by FROM `geopos_invoices` where id=" . $row["tid"];
                $query2 = $this->db->query($sql2);
                $response2 = $query2->result_array();



                $customer_name = $response2[0]['cust_name'];
                $created_by = $response2[0]['created_by'];

                if ($created_by) {
                    $sql2 = "SELECT username FROM `geopos_users` where id=" . $created_by;
                    $query2 = $this->db->query($sql2);
                    $response2 = $query2->result_array();

                    $added_by = $response2[0]['username'];
                }
                if ($row["qty"] > 0) {

                    $stock_invoiced += $row["qty"];
                    $data_tr .= '
              <tr>
              <td>' . $no++ . '</td>';
                    $data_tr .= '
              <td>' . $row["product_des"] . '</td>
              <td>' . $customer_name . '</td>
                  <td></td>
              <td>' . amountExchange_s($row["price"]) . '</td>
            
              <td>' . intval($row["qty"]) . '</td>
              <td>Stock Out</td>
              <td>' . $added_by . '</td>
              ';

                    $data_tr .= '</tr>';
                }
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
                <u><b> " . $company['cname'] . " </b>  </u>
                </div>
                <div >
                  <b><u> Stock Report</u></b> 
              </div>
            </div >
            <div class='right_header'>
              <b><span class='pagenum'>Page:</span></b>
            </div>
          </div>
          <div style='font-size:13px;'>
              <div  style='float:left'>
                  <div><b>Date From:</b> $s_date</div>
                 
              </div>
              <div style='float:right; margin-right:100px'>
              <div><b>Date To:</b> $e_date</div>
              </div>
              <div style='clear:both'>
             <b> Stock Added:</b>&nbsp;&nbsp;&nbsp;  $stock_added &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b> Stock Returned:</b> &nbsp;&nbsp;&nbsp;  $stock_returned  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  <b> Stock Invoiced:</b>&nbsp;&nbsp;&nbsp; $stock_invoiced
              <br>
             
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
                 <u> Product Name</u>
              </td>
              <td>
                 <u> Customer </u>
              </td>
              <td>
                 <u> Purchase Price</u>
              </td>
              <td>
                 <u> Sale Price</u>
              </td>
              <td>
                 <u> Quantity</u>
              </td>
              <td>
              <u> Stock Status</u>
           </td>
           <td>
              <u> User</u>
           </td>
          </tr>
      </thead>
      <tbody style='font-size:13px;'>
      $data_tr
      </tbody>
  
      </tr>
      </table>
      </body>
      </html>
      ";
        $this->dpdf->loadHtml($html);
        $this->dpdf->render();
        $this->dpdf->stream("" . "Stock Report" . ".pdf", array("Attachment" => 0));
    }

    public function product_list_filtered()
    {
        $catid = $this->input->get('id');
        $sub = $this->input->get('sub');
        $list = $this->products->get_datatables_filtered();
        // dd($list);
        $data = array();
        $no = $this->input->post('start');
        
        // Helper function to safely get language lines
        $safe_lang = function($key, $fallback = '') {
            $text = $this->lang->line($key);
            if ($text === FALSE || empty($text)) {
                return !empty($fallback) ? $fallback : $key;
            }
            return $text;
        };
        
        foreach ($list as $prd) {
            $no++;
            $row = array();
            $row[] = $no;
            $pid = $prd->pid;
            $row[] = '<a href="#" data-object-id="' . htmlspecialchars($pid) . '" class="view-object">' . htmlspecialchars($prd->product_name) . '</a>';
            $row[] = +$prd->qty;
            $row[] = htmlspecialchars($prd->product_code);
            $row[] = htmlspecialchars($prd->c_title);
            $row[] = amountExchange($prd->product_price, 0, $this->aauth->get_user()->loc);
            $row[] = amountExchange($prd->fproduct_price, 0, $this->aauth->get_user()->loc);
            // $row[] = $prd->weight_qty;
            $row[] = '<input type="checkbox" class="checkbox" data-object-id="' . htmlspecialchars($pid) . '" value="' . htmlspecialchars($pid) . '" name="product" data-id="' . htmlspecialchars($pid) . '">';
            
            // Build Settings column with proper button structure
            $view_text = $safe_lang('View', 'View');
            $history_text = $safe_lang('History', 'History');
            $reports_text = $safe_lang('Reports', 'Reports');
            $settings_text = $safe_lang('Settings', 'Settings');
            $edit_text = $safe_lang('Edit', 'Edit');
            $stock_text = $safe_lang('Stock', 'Stock');
            $delete_text = $safe_lang('Delete', 'Delete');
            
            $settings_html = '<div class="btn-group" role="group" style="display: flex; flex-wrap: nowrap; gap: 3px;">';
            
            // View button
            $settings_html .= '<a href="#" data-object-id="' . htmlspecialchars($pid) . '" class="btn btn-success btn-sm view-object" title="' . htmlspecialchars($view_text) . '"><i class="fa fa-eye"></i></a>';
            
            // History button
            $settings_html .= '<a href="#" data-object-id="' . htmlspecialchars($pid) . '" class="btn btn-success btn-sm view-object2" title="' . htmlspecialchars($history_text) . '"><i class="fa fa-history"></i></a>';
            
            // Reports button
            $settings_html .= '<a class="btn btn-pink btn-sm" href="' . base_url('products/report_product?id=' . $pid) . '" target="_blank" title="' . htmlspecialchars($reports_text) . '"><i class="fa fa-pie-chart"></i></a>';
            
            // Dropdown menu for additional actions
            $settings_html .= '<div class="btn-group">';
            $settings_html .= '<button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="' . htmlspecialchars($settings_text) . '"><i class="fa fa-cog"></i></button>';
            $settings_html .= '<div class="dropdown-menu dropdown-menu-right">';
            
            // Edit option
            if ($this->aauth->permission_new(null, 'stockEditProduct')) {
                $settings_html .= '<a href="' . base_url('products/edit?id=' . $pid) . '" class="dropdown-item"><i class="fa fa-edit"></i> ' . htmlspecialchars($edit_text) . '</a>';
                $settings_html .= '<div class="dropdown-divider"></div>';
            }
            
            // Stock Edit option
            if ($this->aauth->permission_new(null, 'stockSettingsProduct')) {
                $settings_html .= '<a href="#" object-id="' . htmlspecialchars($pid) . '" class="dropdown-item edit_price_grid"><i class="fa fa-pencil"></i> ' . htmlspecialchars($stock_text) . ' ' . htmlspecialchars($edit_text) . '</a>';
                $settings_html .= '<div class="dropdown-divider"></div>';
            }
            
            // Delete option
            if ($this->aauth->permission_new(null, 'stockDeleteProduct')) {
                $settings_html .= '<a href="#" data-object-id="' . htmlspecialchars($pid) . '" class="dropdown-item delete-object text-danger"><i class="fa fa-trash"></i> ' . htmlspecialchars($delete_text) . '</a>';
            }
            
            $settings_html .= '</div></div></div>';
            
            $row[] = $settings_html;
            $data[] = $row;
        }
        $output = array(
            "draw" => $this->input->post('draw'),
            "recordsTotal" => $this->products->count_all_filtered($catid, '', $sub),
            "recordsFiltered" => $this->products->count_filtered_filtered($catid, '', $sub),
            "data" => $data,
        );
        //output to json format
        echo json_encode($output);
    }

    public function product_list()
    {
        $catid = $this->input->get('id');
        $sub = $this->input->get('sub');
        if ($catid > 0) {
            $list = $this->products->get_datatables($catid, '', $sub);
        } else {
            $list = $this->products->get_datatables();
        }
        // dd($list);
        $data = array();
        $no = $this->input->post('start');
        
        // Helper function to safely get language lines
        $safe_lang = function($key, $fallback = '') {
            $text = $this->lang->line($key);
            if ($text === FALSE || empty($text)) {
                return !empty($fallback) ? $fallback : $key;
            }
            return $text;
        };
        
        foreach ($list as $prd) {
            $no++;
            $row = array();
            $row[] = $no;
            $pid = $prd->pid;
            $row[] = '<a href="#" data-object-id="' . htmlspecialchars($pid) . '" class="view-object">' . htmlspecialchars($prd->product_name) . '</a>';
            $row[] = $prd->qty;
            $row[] = htmlspecialchars($prd->product_code);
            // $row[] = $prd->code_type;
            $row[] = htmlspecialchars($prd->c_title);
            $row[] = $prd->product_price;
            $row[] = $prd->fproduct_price;
            // $row[] = $prd->weight_qty;
            // $row[] = '<input type="checkbox"  class="checkbox" data-object-id="' . $pid . '" value="' . $pid . '" name="product" data-id="' . $pid . '" value="' . $pid . '">';
            $row[] = '<input type="checkbox" class="checkbox" data-object-id="' . htmlspecialchars($pid) . '" name="product" data-id="' . htmlspecialchars($pid) . '" value="' . htmlspecialchars($pid) . '">';
            
            // Build Settings column with proper button structure
            $view_text = $safe_lang('View', 'View');
            $history_text = $safe_lang('History', 'History');
            $reports_text = $safe_lang('Reports', 'Reports');
            $settings_text = $safe_lang('Settings', 'Settings');
            $edit_text = $safe_lang('Edit', 'Edit');
            $stock_text = $safe_lang('Stock', 'Stock');
            $delete_text = $safe_lang('Delete', 'Delete');
            
            $settings_html = '<div class="btn-group" role="group" style="display: flex; flex-wrap: nowrap; gap: 3px;">';
            
            // View button
            $settings_html .= '<a href="#" data-object-id="' . htmlspecialchars($pid) . '" class="btn btn-success btn-sm view-object" title="' . htmlspecialchars($view_text) . '"><i class="fa fa-eye"></i></a>';
            
            // History button
            $settings_html .= '<a href="#" data-object-id="' . htmlspecialchars($pid) . '" class="btn btn-success btn-sm view-object2" title="' . htmlspecialchars($history_text) . '"><i class="fa fa-history"></i></a>';
            
            // Reports button
            $settings_html .= '<a class="btn btn-pink btn-sm" href="' . base_url('products/report_product?id=' . $pid) . '" target="_blank" title="' . htmlspecialchars($reports_text) . '"><i class="fa fa-pie-chart"></i></a>';
            
            // Dropdown menu for additional actions
            $settings_html .= '<div class="btn-group">';
            $settings_html .= '<button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="' . htmlspecialchars($settings_text) . '"><i class="fa fa-cog"></i></button>';
            $settings_html .= '<div class="dropdown-menu dropdown-menu-right">';
            
            // Edit option
            if ($this->aauth->permission_new(null, 'stockEditProduct')) {
                $settings_html .= '<a href="' . base_url('products/edit?id=' . $pid) . '" class="dropdown-item"><i class="fa fa-edit"></i> ' . htmlspecialchars($edit_text) . '</a>';
                $settings_html .= '<div class="dropdown-divider"></div>';
            }
            
            // Stock Edit option
            if ($this->aauth->permission_new(null, 'stockSettingsProduct')) {
                $settings_html .= '<a href="#" object-id="' . htmlspecialchars($pid) . '" class="dropdown-item edit_price_grid"><i class="fa fa-pencil"></i> ' . htmlspecialchars($stock_text) . ' ' . htmlspecialchars($edit_text) . '</a>';
                $settings_html .= '<div class="dropdown-divider"></div>';
            }
            
            // Delete option
            if ($this->aauth->permission_new(null, 'stockDeleteProduct')) {
                $settings_html .= '<a href="#" data-object-id="' . htmlspecialchars($pid) . '" class="dropdown-item delete-object text-danger"><i class="fa fa-trash"></i> ' . htmlspecialchars($delete_text) . '</a>';
            }
            
            $settings_html .= '</div></div></div>';
            
            $row[] = $settings_html;
            $data[] = $row;
        }

        $output = array(
            "draw" => $this->input->post('draw'),
            "recordsTotal" => $this->products->count_all($catid, '', $sub),
            "recordsFiltered" => $this->products->count_filtered($catid, '', $sub),
            "data" => $data,
        );
        //output to json format
        echo json_encode($output);
    }

    public function product_edit_log()
    {

        $prod_id = $this->input->post('product_id');
        $prod_name = $this->input->post('product_name');
        $prod_price = $this->input->post('product_price');
        $fprod_price = $this->input->post('fproduct_price');
        $prod_qty = $this->input->post('product_qty');
        $prod_des = $this->input->post('product_des');
        // echo "<pre>";
        // echo "prod_des:\n";
        // print_r($prod_des);

        // echo "prod_id:\n";
        // print_r($prod_id);

        // echo "prod_name:\n";
        // print_r($prod_name);

        // echo "prod_price:\n";
        // print_r($prod_price);

        // echo "fprod_price:\n";
        // print_r($fprod_price);

        // echo "prod_qty:\n";
        // print_r($prod_qty);
        // echo "</pre>";

        // die();


        foreach ($prod_id as $key => $val) {

            $dataToSave[$key] = array(
                'pid' => $prod_id[$key],
                'p_name' => $prod_name[$key],
                'p_des' => $prod_des[$key],
                'prod_price' => $prod_price[$key],
                'fprod_price' => $fprod_price[$key],
                'prod_qty' => $prod_qty[$key],
            );
        }
        // dd($dataToSave);


        $count = count($dataToSave);


        for ($x = 0; $x < $count; $x++) {
            $edited_data = $dataToSave[$x];
            // dd($edited_data);
            $this->db->select('geopos_products.pid,geopos_products.product_name,geopos_products.product_des,geopos_products.product_price,geopos_products.fproduct_price,geopos_products.qty');
            $this->db->from('geopos_products');
            $this->db->where('pid', $edited_data['pid']);
            $query = $this->db->get();
            $product = $query->result_array();
            $count2 = count($product);
            for ($j = 0; $j < $count2; $j++) {
                $old_data = $product[$j];

                $this->db->insert('products_history', $old_data);
                $last_id = $this->db->insert_id();
                $this->db->where('id', $last_id);
                $this->db->update('products_history', array('added_by' => $this->aauth->get_user()->username));
            }

            $this->db->where('pid', $edited_data['pid']);
            $this->db->update('geopos_products', array('product_name' => $edited_data['p_name'], 'product_des' => $edited_data['p_des'], 'product_price' => $edited_data['prod_price'], 'fproduct_price' => $edited_data['fprod_price']));
        }

        return redirect(base_url('/products'));
    }
    public function view_history()
    {
        $id = $this->input->post('id');

        $this->db->select('*');
        $this->db->from('products_history');
        $this->db->where('pid', $id);
        $this->db->order_by("id", "DESC");

        $query = $this->db->get();

        $data = $query->result_array();
        $total = count($data);

        $jsonobj = json_encode($data);
        $arr = json_decode($jsonobj, true);
        $template = array(
            'table_open' => '<table class="table table-striped table-bordered">'
        );

        $this->table->set_template($template);
        
        // Use language lines with fallbacks
        $product_name = $this->lang->line('Product Name');
        if ($product_name === FALSE || empty($product_name)) {
            $product_name = 'Product Name';
        }
        
        $purchase_price = $this->lang->line('Purchase Price');
        if ($purchase_price === FALSE || empty($purchase_price)) {
            $purchase_price = 'Purchase Price';
        }
        
        $sale_price = $this->lang->line('Sale Price');
        if ($sale_price === FALSE || empty($sale_price)) {
            $sale_price = 'Sale Price';
        }
        
        $old_qty = $this->lang->line('Old qty');
        if ($old_qty === FALSE || empty($old_qty)) {
            $old_qty = 'Old qty';
        }
        
        $quantity = $this->lang->line('Quantity');
        if ($quantity === FALSE || empty($quantity)) {
            $quantity = 'Quantity';
        }
        
        $date = $this->lang->line('Date');
        if ($date === FALSE || empty($date)) {
            $date = 'Date';
        }
        
        $edited_by = $this->lang->line('Edited By');
        if ($edited_by === FALSE || empty($edited_by)) {
            $edited_by = 'Edited By';
        }
        
        $cell = ['data' => $product_name, 'class' => 'center'];
        $this->table->set_heading($cell, $purchase_price, $sale_price, $old_qty, $quantity, $date, $edited_by);

        foreach ($arr as $key) {
            $cell = ['data' => isset($key['product_name']) ? htmlspecialchars($key['product_name']) : ''];
            $this->table->add_row(
                $cell, 
                isset($key['product_price']) ? amountFormat_general($key['product_price']) : '0.00',
                isset($key['fproduct_price']) ? amountFormat_general($key['fproduct_price']) : '0.00',
                isset($key['old_qty']) ? amountFormat_general($key['old_qty']) : '0.00',
                isset($key['qty']) ? amountFormat_general($key['qty']) : '0.00',
                isset($key['added_on']) ? htmlspecialchars($key['added_on']) : '',
                isset($key['added_by']) ? htmlspecialchars($key['added_by']) : ''
            );
        }

        echo $this->table->generate();
    }

    public function new_edit()
    {
        $pid = $this->input->get('p_id');
        $array = explode(',', $pid);
        $this->db->select('*');
        $this->db->from('geopos_products');
        $this->db->where_in('pid', $array);
        $query = $this->db->get();
        $data['product'] = $query->result_array();

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
        $head['title'] = "Product Edit";
        $head['usernm'] = $this->aauth->get_user()->username;
        $data['taxdetails'] = $this->common->taxdetail();
        $data['custom_fields'] = $this->custom->add_fields(2);
        $this->load->view('fixed/header', $head);
        $this->load->view('products/new_edit', $data);
        $this->load->view('fixed/footer');
    }



    public function editqty($id)
    {
        $this->db->select('*');
        $this->db->from('geopos_products');
        $this->db->where('pid', $id);
        $query = $this->db->get();
        $data = $query->row_array();
        $cat_id = $pr['pcat'];
        echo json_encode(array('saleprice' => $data['fproduct_price'], 'price' => $data['product_price'], 'qty' => $data['qty'], 'name' => $data['product_name']));
    }

    public function updateqty()
    {
        $new_qty = $this->input->post('edit_product_qty_new');
        $p_price = $this->input->post('edit_purchase_price');
        $s_price = $this->input->post('edit_sale_price');
        $old_qty = $this->input->post('edit_product_qty');
        $p_name = $this->input->post('edit_product_name');

        $old_qty1 = (int) $old_qty;
        $new_qty2 = (int) $new_qty;

        // Ensure new stock cannot result in a negative total quantity
        $total_qty = $old_qty1 + $new_qty2;
        if ($total_qty < 0) {
            $this->session->set_flashdata('error', 'Stock quantity cannot be negative.');
            redirect('/products', 'refresh');
            return;
        }

        $dbdata = array(
            "product_price" => $p_price,
            "fproduct_price" => $s_price,
            "qty" => $total_qty,
        );

        $this->load->database();
        $this->db->where('pid', $this->input->post('edit_product_id'));
        $this->db->update('geopos_products', $dbdata);

        $history_data = array(
            "pid" => $this->input->post('edit_product_id'),
            'product_name' => $p_name,
            'product_price' => $p_price,
            'fproduct_price' => $s_price,
            'qty' => $total_qty,
            'old_qty' => $old_qty1,
            'added_by' => $this->aauth->get_user()->username,
        );
        $this->db->insert('products_history', $history_data);
        $this->aauth->applog("[Product Stock Updated] - Old Quantity: $old_qty1, New Quantity: $new_qty2", $this->aauth->get_user()->username);

        if ($this->db->affected_rows() > 0) {
            echo json_encode(['status' => 'success', 'redirect_url' => site_url('products')]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Stock quantity cannot be negative.']);
        }
    }

    public function addproduct()
    {
        $product_name = $this->input->post('product_name', true);
        $catid = $this->input->post('product_cat');
        $warehouse = $this->input->post('product_warehouse');
        $product_code = $this->input->post('product_code');
        $product_price = numberClean($this->input->post('product_price'));
        $factoryprice = numberClean($this->input->post('fproduct_price'));
        $taxrate = numberClean($this->input->post('product_tax', true));
        $disrate = numberClean($this->input->post('product_disc', true));
        $product_qty = numberClean($this->input->post('product_qty', true));
        $product_qty_alert = numberClean($this->input->post('product_qty_alert'));
        $product_desc = $this->input->post('product_desc', true);
        $image = $this->input->post('image');
        $unit = $this->input->post('unit', true);
        $barcode = $this->input->post('barcode');
        $v_type = $this->input->post('v_type');
        $v_stock = $this->input->post('v_stock');
        $v_alert = $this->input->post('v_alert');
        $w_type = $this->input->post('w_type');
        $w_stock = $this->input->post('w_stock');
        $w_alert = $this->input->post('w_alert');
        $wdate = datefordatabase($this->input->post('wdate'));
        $taxable = $this->input->post('taxable');
        $sub_cat = $this->input->post('sub_cat');
        $brand = $this->input->post('brand');
        $p_to_b = $this->input->post('p_to_b');
        $serial = $this->input->post('product_serial');
        $weight_unit = $this->input->post('weight_unit');
        $weight_qty = $this->input->post('weight_qty');

        // Validate warehouse
        if (empty($warehouse) || $warehouse == '' || $warehouse == null) {
            echo json_encode(array('status' => 'Error', 'message' => 'Please select a warehouse first before creating a product.'));
            return;
        }

        // Validate category
        if (empty($catid) || $catid == '' || $catid == null) {
            echo json_encode(array('status' => 'Error', 'message' => 'Please select a product category.'));
            return;
        }

        if ($catid) {
            $this->products->addnew(
                $p_to_b,
                $catid,
                $warehouse,
                $product_name,
                $product_code,
                $product_price,
                $factoryprice,
                $taxrate,
                $disrate,
                $product_qty,
                $product_qty_alert,
                $product_desc,
                $image,
                $unit,
                $barcode,
                $v_type,
                $v_stock,
                $v_alert,
                $wdate,
                $taxable,
                $w_type,
                $w_stock,
                $w_alert,
                $sub_cat,
                $brand,
                $serial,
                $weight_unit,
                $weight_qty
            );
        } else {
            echo json_encode(array('status' => 'Error', 'message' => 'Please select a product category.'));
        }

        $this->aauth->applog("[New Product Added - $product_name (Code: $product_code)] - Price: $product_price, Quantity: $product_qty, Unit: $unit, Taxrate: $taxrate, Discount: $disrate, Weight: $weight_qty, Unit: $weight_unit ", $this->aauth->get_user()->username);
    }

    public function delete_i()
    {
        if (!$this->aauth->permission_new(null, 'stockManageProducts')) {

            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        if ($this->aauth->premission(11)) {
            $id = $this->input->post('deleteid');
            if ($id) {
                $this->db->delete('geopos_products', array('pid' => $id));
                $this->db->delete('geopos_products', array('sub' => $id, 'merge' => 1));
                $this->db->delete('geopos_movers', array('d_type' => 1, 'rid1' => $id));
                $this->db->set('merge', 0);
                $this->db->where('sub', $id);
                $this->db->update('geopos_products');
                echo json_encode(array('status' => 'Success', 'message' => $this->lang->line('DELETED')));
            } else {
                echo json_encode(array('status' => 'Error', 'message' => $this->lang->line('ERROR')));
            }
        } else {
            echo json_encode(array('status' => 'Error', 'message' =>
            $this->lang->line('ERROR')));
        }
    }

    public function edit()
    {
        if (!$this->aauth->permission_new(null, 'stockManageProducts')) {

            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $pid = $this->input->get('id');
        $this->db->select('*');
        $this->db->from('geopos_products');
        $this->db->where('pid', $pid);
        $query = $this->db->get();
        $data['product'] = $query->row_array();
        $product_image = isset($data['product']['image']) ? $data['product']['image'] : '';
        $data['product_image'] = $product_image;
        $data['image_url'] = !empty($product_image) ? base_url('userfiles/product/' . $product_image) : 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
        if ($data['product']['merge'] > 0) {
            $this->db->select('*');
            $this->db->from('geopos_products');
            $this->db->where('merge', 1);
            $this->db->where('sub', $pid);
            $query = $this->db->get();
            $data['product_var'] = $query->result_array();
            $this->db->select('*');
            $this->db->from('geopos_products');
            $this->db->where('merge', 2);
            $this->db->where('sub', $pid);
            $query = $this->db->get();
            $data['product_ware'] = $query->result_array();
        }


        $data['units'] = $this->products->units();
        $data['serial_list'] = $this->products->serials($data['product']['pid']);
        $data['cat_ware'] = $this->categories_model->cat_ware($pid);
        $data['cat_sub'] = $this->categories_model->sub_cat_curr($data['product']['sub_id']);
        $data['cat_sub_list'] = $this->categories_model->sub_cat_list($data['product']['pcat']);
        $data['warehouse'] = $this->categories_model->warehouse_list();
        $data['cat'] = $this->categories_model->category_list();
        $data['custom_fields'] = $this->custom->view_edit_fields($pid, 4);
        $head['title'] = "Edit Product";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->model('units_model', 'units');
        $data['variables'] = $this->units->variables_list();
        $this->load->view('fixed/header', $head);
        $this->load->view('products/product-edit', $data);
        $this->load->view('fixed/footer');
    }

    public function editproduct()
    {
        if (!$this->aauth->permission_new(null, 'stockManageProducts')) {

            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $pid = $this->input->post('pid');
        $product_name = $this->input->post('product_name', true);

        // echo $product_name; exit; 
        $catid = $this->input->post('product_cat');
        $warehouse = $this->input->post('product_warehouse');
        $product_code = $this->input->post('product_code');
        $product_price = numberClean($this->input->post('product_price'));
        $factoryprice = numberClean($this->input->post('fproduct_price'));

        // Validate warehouse
        if (empty($warehouse) || $warehouse == '' || $warehouse == null) {
            echo json_encode(array('status' => 'Error', 'message' => 'Please select a warehouse.'));
            return;
        }

        // Validate category
        if (empty($catid) || $catid == '' || $catid == null) {
            echo json_encode(array('status' => 'Error', 'message' => 'Please select a product category.'));
            return;
        }
        $taxrate = numberClean($this->input->post('product_tax'));
        $disrate = numberClean($this->input->post('product_disc'));
        $product_qty = numberClean($this->input->post('product_qty'));
        $product_qty_alert = numberClean($this->input->post('product_qty_alert'));
        $product_desc = $this->input->post('product_desc', true);
        $image = $this->input->post('image');
        $unit = $this->input->post('unit');
        $barcode = $this->input->post('barcode');
        $code_type = $this->input->post('taxable');
        $sub_cat = $this->input->post('sub_cat');
        $wdate = datefordatabase($this->input->post('wdate'));
        $sql = "insert into products_history (pid, product_name, product_price,fproduct_price, qty )
        values (" . $pid . "," . "'$product_name'" . "," . $product_price . "," . $factoryprice . "," . $product_qty . ")";

        $this->db->query($sql);
        if (!$sub_cat) $sub_cat = 0;
        $brand = $this->input->post('brand');
        $vari = array();
        $vari['v_type'] = $this->input->post('v_type');
        $vari['v_stock'] = $this->input->post('v_stock');
        $vari['v_alert'] = $this->input->post('v_alert');
        $vari['w_type'] = $this->input->post('w_type');
        $vari['w_stock'] = $this->input->post('w_stock');
        $vari['w_alert'] = $this->input->post('w_alert');
        $serial = array();
        $serial['new'] = $this->input->post('product_serial');
        $serial['old'] = $this->input->post('product_serial_e');
        $p_to_b = $this->input->post('p_to_b');
        $weight_unit = $this->input->post('weight_unit');
        $weight_qty = $this->input->post('weight_qty');
        if ($pid) {

            $this->products->edit(
                $p_to_b,
                $pid,
                $catid,
                $warehouse,
                $product_name,
                $product_code,
                $product_price,
                $factoryprice,
                $taxrate,
                $disrate,
                $product_qty,
                $product_qty_alert,
                $product_desc,
                $image,
                $unit,
                $barcode,
                $code_type,
                $sub_cat,
                $brand,
                $vari,
                $serial,
                $weight_unit,
                $weight_qty,
                $wdate
            );
        }

        $this->aauth->applog("[Product Edited - $product_name (Code: $product_code)] - Price: $product_price, Quantity: $product_qty, Unit: $unit, Taxrate: $taxrate, Discount: $disrate", $this->aauth->get_user()->username);
    }


    public function warehouseproduct_list()
    {
        $catid = $this->input->get('id');
        $list = $this->products->get_datatables($catid, true);
        $data = array();
        $no = $this->input->post('start');
        foreach ($list as $prd) {
            $no++;
            $row = array();
            $row[] = $no;
            $pid = $prd->pid;
            $row[] = $prd->product_name;
            $row[] = +$prd->qty;
            $row[] = $prd->product_code;
            $row[] = $prd->c_title;
            $row[] = amountExchange($prd->product_price, 0, $this->aauth->get_user()->loc);
            $row[] = amountExchange($prd->fproduct_price, 0, $this->aauth->get_user()->loc);
            // $row[] = '<a href="#" data-object-id="' . $pid . '" class="btn btn-success btn-sm  view-object"><span class="fa fa-eye"></span> ' . $this->lang->line('View') . '</a> <a href="' . base_url() . 'products/edit?id=' . $pid . '" class="btn btn-primary btn-sm"><span class="fa fa-pencil"></span> ' . $this->lang->line('Edit') . '</a> <a href="#" data-object-id="' . $pid . '" class="btn btn-danger btn-sm  delete-object"><span class="fa fa-trash"></span> ' . $this->lang->line('Delete') . '</a>';
            $data[] = $row;
        }
        $output = array(
            "draw" => $this->input->post('draw'),
            "recordsTotal" => $this->products->count_all($catid, true),
            "recordsFiltered" => $this->products->count_filtered($catid, true),
            "data" => $data,
        );
        echo json_encode($output);
    }

    public function prd_stats()
    {
        $this->products->prd_stats();
    }

    public function stock_transfer_products()
    {
        $wid = $this->input->get('wid');
        $customer = $this->input->post('product');
        $terms = @$customer['term'];
        $result = $this->products->products_list($wid, $terms);
        echo json_encode($result);
    }

    public function sub_cat()
    {
        $wid = $this->input->get('id');
        $result = $this->categories_model->category_list(1, $wid);
        echo json_encode($result);
    }

    public function stock_transfer()
    {
        if (!$this->aauth->permission_new(null, 'stockTransfer')) {

            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        if ($this->input->post()) {
            $products_l = $this->input->post('products_l');
            $from_warehouse = $this->input->post('from_warehouse');
            $to_warehouse = $this->input->post('to_warehouse');
            $qty = $this->input->post('products_qty');
            $this->products->transfer($from_warehouse, $products_l, $to_warehouse, $qty);
        } else {
            $data['cat'] = $this->categories_model->category_list();
            $data['warehouse'] = $this->categories_model->warehouse_list();
            $head['title'] = "Stock Transfer";
            $head['usernm'] = $this->aauth->get_user()->username;
            $this->load->view('fixed/header', $head);
            $this->load->view('products/stock_transfer', $data);
            $this->load->view('fixed/footer');
        }
    }

    public function stock_update()
    {
        $data['cat'] = $this->categories_model->category_list();
        $data['warehouse'] = $this->categories_model->warehouse_list();
        $head['title'] = "Stock Update";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $this->load->view('products/stock_update', $data);
        $this->load->view('fixed/footer');
    }
    public function import()
    {
        $this->load->database(); // Ensure the database is loaded
        header('Content-Type: application/json');

        // Define the upload path
        $path = 'userfiles/product/stock_update/';
        $file = $_FILES['file'] ?? null;

        // Validate uploaded file
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['error_message' => 'No file uploaded or file upload error.']);
            return;
        }

        // Upload the file and get the file path
        $filePath = $this->uploadFile($path, $file);
        if (!$filePath) {
            echo json_encode(['error_message' => 'Failed to upload the file.']);
            return;
        }

        // Get file extension and initialize the appropriate reader
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);

        try {
            if ($extension === 'csv') {
                $reader = new Csv();
            } elseif (in_array($extension, ['xls', 'xlsx'])) {
                $reader = new Xlsx();
            } else {
                echo json_encode(['error_message' => 'Invalid file type.']);
                unlink($filePath); // Remove the uploaded file
                return;
            }

            // Load the spreadsheet
            $spreadsheet = $reader->load($filePath);
            $sheetData = $spreadsheet->getActiveSheet()->toArray();

            $updatedRows = 0;

            // Loop through rows and update the database (skip header row)
            foreach ($sheetData as $key => $row) {
                if ($key === 0) continue; // Skip header row

                $prodCode = $row[0] ?? null;
                $qty = $row[1] ?? null;

                if ($prodCode && $qty) {
                    $this->db->where('prod_code', $prodCode)
                        ->update('stock_product_update', ['qty' => $qty]);

                    if ($this->db->affected_rows() > 0) {
                        $updatedRows++;
                    }
                }
            }

            // Remove the uploaded file after processing
            unlink($filePath);

            // Respond with a success or error message
            if ($updatedRows > 0) {
                echo json_encode(['success_message' => "$updatedRows rows updated successfully."]);
            } else {
                echo json_encode(['error_message' => 'No rows were updated. Please check your data.']);
            }
        } catch (Exception $e) {
            echo json_encode(['error_message' => 'Error processing file: ' . $e->getMessage()]);
        }
    }

    private function uploadFile($path, $file)
    {
        // Create the directory if it doesn't exist
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        // Generate a new unique file name
        $newName = uniqid() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
        $filePath = $path . $newName;

        // Move the uploaded file to the target directory
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            return $filePath;
        }

        return false;
    }


    public function file_handling()
    {
        // Set JSON content type header to ensure proper response
        $this->output->set_content_type('application/json');
        
        // Start output buffering to catch any unwanted output (PHP warnings, errors, etc.)
        ob_start();
        
        try {
            if ($this->input->get('op')) {
                $name = $this->input->get('name');
                $output = ob_get_clean(); // Get and clear buffer
                
                // If there's unwanted output, log it but don't send it
                if (!empty($output) && !json_decode($output)) {
                    log_message('debug', 'Unwanted output in file_handling delete: ' . $output);
                }
                
                if ($this->products->meta_delete($name)) {
                    echo json_encode(array('status' => 'Success'));
                } else {
                    echo json_encode(array('status' => 'Error', 'message' => 'Failed to delete file'));
                }
            } else {
                $id = $this->input->get('id');
                
                // Ensure upload directory exists
                $upload_dir = FCPATH . 'userfiles/product/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                // Clear any unwanted output before loading library
                $output = ob_get_clean();
                if (!empty($output) && !json_decode($output)) {
                    log_message('debug', 'Unwanted output before upload library: ' . $output);
                }
                
                // Load the upload library - it will output JSON directly
                $this->load->library("Uploadhandler_generic", array(
                    'accept_file_types' => '/\.(gif|jpe?g|png)$/i',
                    'upload_dir' => $upload_dir,
                    'upload_url' => base_url() . 'userfiles/product/',
                    'max_file_size' => 10 * 1024 * 1024 // 10 MB
                ));
            }
        } catch (Exception $e) {
            $output = ob_get_clean();
            // Clear any unwanted output
            if (!empty($output) && !json_decode($output)) {
                log_message('error', 'Error in file_handling: ' . $e->getMessage() . ' | Output: ' . $output);
            }
            echo json_encode(array(
                'files' => array(
                    array(
                        'error' => 'Upload failed: ' . $e->getMessage()
                    )
                )
            ));
        } catch (Error $e) {
            $output = ob_get_clean();
            if (!empty($output) && !json_decode($output)) {
                log_message('error', 'Fatal error in file_handling: ' . $e->getMessage() . ' | Output: ' . $output);
            }
            echo json_encode(array(
                'files' => array(
                    array(
                        'error' => 'Upload failed: ' . $e->getMessage()
                    )
                )
            ));
        }
    }

    public function barcode()
    {
        $pid = $this->input->get('id');
        if ($pid) {
            $this->db->select('product_name,barcode,code_type');
            $this->db->from('geopos_products');
            //  $this->db->where('warehouse', $warehouse);
            $this->db->where('pid', $pid);
            $query = $this->db->get();
            $resultz = $query->row_array();
            $data['name'] = $resultz['product_name'];
            $data['code'] = $resultz['barcode'];
            $data['ctype'] = $resultz['code_type'];
            $html = $this->load->view('barcode/view', $data, true);
            ini_set('memory_limit', '64M');

            //PDF Rendering
            $this->load->library('pdf');
            $pdf = $this->pdf->load();
            $pdf->WriteHTML($html);
            $pdf->Output($data['name'] . '_barcode.pdf', 'I');
        }
    }

    public function posbarcode()
    {
        $pid = $this->input->get('id');
        if ($pid) {
            $this->db->select('product_name,barcode,code_type');
            $this->db->from('geopos_products');
            //  $this->db->where('warehouse', $warehouse);
            $this->db->where('pid', $pid);
            $query = $this->db->get();
            $resultz = $query->row_array();
            $data['name'] = $resultz['product_name'];
            $data['code'] = $resultz['barcode'];
            $data['ctype'] = $resultz['code_type'];
            $html = $this->load->view('barcode/posbarcode', $data, true);
            ini_set('memory_limit', '64M');

            //PDF Rendering
            $this->load->library('pdf');
            $pdf = $this->pdf->load_thermal();
            $pdf->WriteHTML($html);
            $pdf->Output($data['name'] . '_barcode.pdf', 'I');
        }
    }

    public function view_over()
    {
        $pid = $this->input->post('id');
        $this->db->select('geopos_products.*,geopos_warehouse.title');
        $this->db->from('geopos_products');
        $this->db->where('geopos_products.pid', $pid);
        $this->db->join('geopos_warehouse', 'geopos_warehouse.id = geopos_products.warehouse');
        if ($this->aauth->get_user()->loc) {
            $this->db->group_start();
            $this->db->where('geopos_warehouse.loc', $this->aauth->get_user()->loc);
            if (BDATA) $this->db->or_where('geopos_warehouse.loc', 0);
            $this->db->group_end();
        } elseif (!BDATA) {
            $this->db->where('geopos_warehouse.loc', 0);
        }

        $query = $this->db->get();
        $data['product'] = $query->row_array();

        $this->db->select('geopos_products.*,geopos_warehouse.title');
        $this->db->from('geopos_products');
        $this->db->join('geopos_warehouse', 'geopos_warehouse.id = geopos_products.warehouse');
        if ($this->aauth->get_user()->loc) {
            $this->db->group_start();
            $this->db->where('geopos_warehouse.loc', $this->aauth->get_user()->loc);
            if (BDATA) $this->db->or_where('geopos_warehouse.loc', 0);
            $this->db->group_end();
        } elseif (!BDATA) {
            $this->db->where('geopos_warehouse.loc', 0);
        }
        $this->db->where('geopos_products.merge', 1);
        $this->db->where('geopos_products.sub', $pid);
        $query = $this->db->get();
        $data['product_variations'] = $query->result_array();

        $this->db->select('geopos_products.*,geopos_warehouse.title');
        $this->db->from('geopos_products');
        $this->db->join('geopos_warehouse', 'geopos_warehouse.id = geopos_products.warehouse');
        if ($this->aauth->get_user()->loc) {
            $this->db->group_start();
            $this->db->where('geopos_warehouse.loc', $this->aauth->get_user()->loc);
            if (BDATA) $this->db->or_where('geopos_warehouse.loc', 0);
            $this->db->group_end();
        } elseif (!BDATA) {
            $this->db->where('geopos_warehouse.loc', 0);
        }
        $this->db->where('geopos_products.sub', $pid);
        $this->db->where('geopos_products.merge', 2);
        $query = $this->db->get();
        $data['product_warehouse'] = $query->result_array();


        $this->load->view('products/view-over', $data);
    }


    public function label()
    {
        $pid = $this->input->get('id');
        if ($pid) {
            $this->db->select('product_name,product_price,product_code,barcode,expiry,code_type');
            $this->db->from('geopos_products');
            //  $this->db->where('warehouse', $warehouse);
            $this->db->where('pid', $pid);
            $query = $this->db->get();
            $resultz = $query->row_array();

            $html = $this->load->view('barcode/label', array('lab' => $resultz), true);
            ini_set('memory_limit', '64M');

            //PDF Rendering
            $this->load->library('pdf');
            $pdf = $this->pdf->load();
            $pdf->WriteHTML($html);
            $pdf->Output($resultz['product_name'] . '_label.pdf', 'I');
        }
    }


    public function poslabel()
    {
        $pid = $this->input->get('id');
        if ($pid) {
            $this->db->select('product_name,product_price,product_code,barcode,expiry,code_type');
            $this->db->from('geopos_products');
            //  $this->db->where('warehouse', $warehouse);
            $this->db->where('pid', $pid);
            $query = $this->db->get();
            $resultz = $query->row_array();
            $html = $this->load->view('barcode/poslabel', array('lab' => $resultz), true);
            ini_set('memory_limit', '64M');
            //PDF Rendering
            $this->load->library('pdf');
            $pdf = $this->pdf->load_thermal();
            $pdf->WriteHTML($html);
            $pdf->Output($resultz['product_name'] . '_label.pdf', 'I');
        }
    }

    public function report_product()
    {
        $pid = intval($this->input->post('id'));
        $company = $this->settings->company_details(1);
        $r_type = intval($this->input->post('r_type'));
        $s_date = datefordatabase($this->input->post('s_date'));
        $e_date = datefordatabase($this->input->post('e_date'));

        if ($pid && $r_type) {


            switch ($r_type) {
                case 1:
                    $query = $this->db->query("SELECT geopos_invoices.tid,geopos_invoice_items.qty,geopos_invoice_items.price,geopos_invoices.invoicedate FROM geopos_invoice_items LEFT JOIN geopos_invoices ON geopos_invoices.id=geopos_invoice_items.tid WHERE geopos_invoice_items.pid='$pid' AND geopos_invoices.status!='canceled' AND (DATE(geopos_invoices.invoicedate) BETWEEN DATE('$s_date') AND DATE('$e_date'))");
                    $result = $query->result_array();
                    break;

                case 2:
                    $query = $this->db->query("SELECT geopos_purchase.tid,geopos_purchase_items.qty,geopos_purchase_items.price,geopos_purchase.invoicedate FROM geopos_purchase_items LEFT JOIN geopos_purchase ON geopos_purchase.id=geopos_purchase_items.tid WHERE geopos_purchase_items.pid='$pid' AND geopos_purchase.status!='canceled' AND (DATE(geopos_purchase.invoicedate) BETWEEN DATE('$s_date') AND DATE('$e_date'))");
                    $result = $query->result_array();
                    break;

                case 3:
                    $query = $this->db->query("SELECT rid2 AS qty, DATE(d_time) AS  invoicedate,note FROM geopos_movers  WHERE geopos_movers.d_type='1' AND rid1='$pid'  AND (DATE(d_time) BETWEEN DATE('$s_date') AND DATE('$e_date'))");
                    $result = $query->result_array();
                    break;
            }

            $this->db->select('*');
            $this->db->from('geopos_products');
            $this->db->where('pid', $pid);
            $query = $this->db->get();
            $product = $query->row_array();

            $cat_ware = $this->categories_model->cat_ware($pid, $this->aauth->get_user()->loc);

            //if(!$cat_ware) exit();
            $html = $this->load->view('products/statementpdf-ltr', array('report' => $result, 'product' => $product, 'cat_ware' => $cat_ware, 'r_type' => $r_type, 'company' => $company), true);
            ini_set('memory_limit', '64M');

            //PDF Rendering
            $this->load->library('pdf');
            $pdf = $this->pdf->load();
            $pdf->WriteHTML($html);
            $pdf->Output($pid . 'report.pdf', 'I');
        } else {
            $pid = intval($this->input->get('id'));
            $this->db->select('*');
            $this->db->from('geopos_products');
            $this->db->where('pid', $pid);
            $query = $this->db->get();
            $product = $query->row_array();
            $head['title'] = "Product Sales";
            $head['usernm'] = $this->aauth->get_user()->username;
            $this->load->view('fixed/header', $head);
            $this->load->view('products/statement', array('id' => $pid, 'product' => $product, 'company' => $company));
            $this->load->view('fixed/footer');
        }
    }

    public function custom_label()
    {
        if (!$this->aauth->permission_new(null, 'stockCustomLabel')) {

            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        if ($this->input->post()) {
            $width = $this->input->post('width');
            $height = $this->input->post('height');
            $padding = $this->input->post('padding');
            $store_name = $this->input->post('store_name');
            $warehouse_name = $this->input->post('warehouse_name');
            $product_price = $this->input->post('product_price');
            $product_code = $this->input->post('product_code');
            $bar_height = $this->input->post('bar_height');
            $total_rows = $this->input->post('total_rows');
            $items_per_rows = $this->input->post('items_per_row');
            $products = array();


            foreach ($this->input->post('products_l') as $row) {
                $this->db->select('geopos_products.product_name,geopos_products.product_price,geopos_products.product_code,geopos_products.barcode,geopos_products.expiry,geopos_products.code_type,geopos_warehouse.title,geopos_warehouse.loc');
                $this->db->from('geopos_products');
                $this->db->join('geopos_warehouse', 'geopos_warehouse.id = geopos_products.warehouse', 'left');

                if ($this->aauth->get_user()->loc) {
                    $this->db->group_start();
                    $this->db->where('geopos_warehouse.loc', $this->aauth->get_user()->loc);

                    if (BDATA) $this->db->or_where('geopos_warehouse.loc', 0);
                    $this->db->group_end();
                } elseif (!BDATA) {
                    $this->db->where('geopos_warehouse.loc', 0);
                }

                //  $this->db->where('warehouse', $warehouse);
                $this->db->where('geopos_products.pid', $row);
                $query = $this->db->get();
                $resultz = $query->row_array();

                $products[] = $resultz;
            }


            $loc = location($resultz['loc']);

            $design = array('store' => $loc['cname'], 'warehouse' => $resultz['title'], 'width' => $width, 'height' => $height, 'padding' => $padding, 'store_name' => $store_name, 'warehouse_name' => $warehouse_name, 'product_price' => $product_price, 'product_code' => $product_code, 'bar_height' => $bar_height, 'total_rows' => $total_rows, 'items_per_row' => $items_per_rows);


            $html = $this->load->view('barcode/custom_label', array('products' => $products, 'style' => $design), true);
            ini_set('memory_limit', '64M');

            //PDF Rendering
            $this->load->library('pdf');
            $pdf = $this->pdf->load_en();
            $pdf->WriteHTML($html);
            $pdf->Output($resultz['product_name'] . '_label.pdf', 'I');
        } else {
            $data['cat'] = $this->categories_model->category_list();
            $data['warehouse'] = $this->categories_model->warehouse_list();
            $head['title'] = "Stock Transfer";
            $head['usernm'] = $this->aauth->get_user()->username;
            $this->load->view('fixed/header', $head);
            $this->load->view('products/custom_label', $data);
            $this->load->view('fixed/footer');
        }
    }

    public function standard_label()
    {
        if (!$this->aauth->permission_new(null, 'stockStandardLabel')) {

            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        if ($this->input->post()) {
            $width = $this->input->post('width');
            $height = $this->input->post('height');
            $padding = $this->input->post('padding');
            $store_name = $this->input->post('store_name');
            $warehouse_name = $this->input->post('warehouse_name');
            $product_price = $this->input->post('product_price');
            $product_code = $this->input->post('product_code');
            $bar_height = $this->input->post('bar_height');
            $total_rows = $this->input->post('total_rows');
            $items_per_rows = $this->input->post('items_per_row');
            $standard_label = $this->input->post('standard_label');
            $products = array();


            foreach ($this->input->post('products_l') as $row) {
                $this->db->select('geopos_products.product_name,geopos_products.product_price,geopos_products.product_code,geopos_products.barcode,geopos_products.expiry,geopos_products.code_type,geopos_warehouse.title,geopos_warehouse.loc');
                $this->db->from('geopos_products');
                $this->db->join('geopos_warehouse', 'geopos_warehouse.id = geopos_products.warehouse', 'left');

                if ($this->aauth->get_user()->loc) {
                    $this->db->group_start();
                    $this->db->where('geopos_warehouse.loc', $this->aauth->get_user()->loc);

                    if (BDATA) $this->db->or_where('geopos_warehouse.loc', 0);
                    $this->db->group_end();
                } elseif (!BDATA) {
                    $this->db->where('geopos_warehouse.loc', 0);
                }

                //  $this->db->where('warehouse', $warehouse);
                $this->db->where('geopos_products.pid', $row);
                $query = $this->db->get();
                $resultz = $query->row_array();

                $products[] = $resultz;
            }


            $loc = location($resultz['loc']);

            $design = array('store' => $loc['cname'], 'warehouse' => $resultz['title'], 'width' => $width, 'height' => $height, 'padding' => $padding, 'store_name' => $store_name, 'warehouse_name' => $warehouse_name, 'product_price' => $product_price, 'product_code' => $product_code, 'bar_height' => $bar_height, 'total_rows' => $total_rows, 'items_per_row' => $items_per_rows);

            switch ($standard_label) {
                case 'eu30019':
                    $html = $this->load->view('standard_label/eu30019', array('products' => $products, 'style' => $design), true);
                    break;
            }


            ini_set('memory_limit', '64M');

            //PDF Rendering
            $this->load->library('pdf');
            $pdf = $this->pdf->load_en();
            $pdf->WriteHTML($html);
            $pdf->Output($resultz['product_name'] . '_label.pdf', 'I');
        } else {
            $data['cat'] = $this->categories_model->category_list();
            $data['warehouse'] = $this->categories_model->warehouse_list();
            $head['title'] = "Stock Transfer";
            $head['usernm'] = $this->aauth->get_user()->username;
            $this->load->view('fixed/header', $head);
            $this->load->view('products/standard_label', $data);
            $this->load->view('fixed/footer');
        }
    }
}

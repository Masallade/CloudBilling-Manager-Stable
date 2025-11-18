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

class Quotations extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library("Aauth");

        if (!$this->aauth->is_loggedin()) {
            redirect('/user/', 'refresh');
        }
        if (!$this->aauth->permission_new(null, 'quotationAccess')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }

        if ($this->aauth->get_user()->roleid == 2) {
            $this->limited = $this->aauth->get_user()->id;
        } else {
            $this->limited = '';
        }
        $this->load->model('quotations_model', 'quotations');
        $this->load->model('plugins_model', 'plugins');
        $this->load->model('settings_model', 'settings');
        $this->load->model('customers_model', 'customers');
        $this->load->model('products_model', 'products');
        $this->load->library("Custom");
        $this->load->library('XLSXWriter');
        $this->load->helper('download');
        $this->li_a = 'quotations';
        $this->load->library('dpdf');
    }

    public function index()
    {
        $data['title'] = 'Manage Quotations';
        $data['param'] = $this->input->get('param');
        $data['due_date'] = $this->input->get('due_date');
        $head['title'] = "Manage Quotations";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $this->load->view('quotations/quotations', $data);
        $this->load->view('fixed/footer');
    }

    public function newquotation()
    {
        if (!$this->aauth->permission_new(null, 'quotationNewQuotation')) {
            exit('<h3>Sorry! You have insufficient permissions to create quotations</h3>');
        }
        
        $data['title'] = 'New Quotation';
        $data['customers'] = $this->customers->customers_list();
        $data['products'] = $this->products->products_list_for_dropdown();
        $data['terms'] = $this->quotations->billingterms();
        $data['warehouses'] = $this->quotations->warehouses();
        $data['currencies'] = $this->quotations->currencies();
        $data['gateways'] = $this->quotations->gateway_list('Yes');
        $data['lastquotation'] = $this->quotations->lastquotation();
        $head['title'] = "New Quotation";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $this->load->view('quotations/newquotation', $data);
        $this->load->view('fixed/footer');
    }

    public function edit()
    {
        if (!$this->aauth->permission_new(null, 'quotationEditQuotation')) {
            exit('<h3>Sorry! You have insufficient permissions to edit quotations</h3>');
        }
        
        $id = $this->input->get('id');
        $data['title'] = 'Edit Quotation';
        $data['quotation'] = $this->quotations->quotation_details($id, $this->limited);
        $data['products'] = $this->quotations->quotation_products($id);
        $data['customers'] = $this->customers->customers_list();
        
        // Get all products for dropdown
        $data['all_products'] = $this->products->products_list_for_dropdown();
        
        $data['terms'] = $this->quotations->billingterms();
        $data['warehouses'] = $this->quotations->warehouses();
        $data['currencies'] = $this->quotations->currencies();
        $data['gateways'] = $this->quotations->gateway_list('Yes');
        $head['title'] = "New Quotation";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $this->load->view('quotations/edit', $data);
        $this->load->view('fixed/footer');
    }

    public function addquotation()
    {
        if (!$this->aauth->permission_new(null, 'quotationNewQuotation')) {
            echo json_encode(array('status' => 'Error', 'message' => 'You have insufficient permissions to create quotations'));
            return;
        }
        
        $customer = $this->input->post('customer');
        $invoicedate = $this->input->post('invoicedate');
        $invoiceduedate = $this->input->post('invoiceduedate');
        $subtotal = $this->input->post('subtotal');
        $shipping = $this->input->post('shipping');
        $ship_tax = $this->input->post('ship_tax');
        $tax = $this->input->post('tax');
        $total = $this->input->post('total');
        $note = $this->input->post('note');
        $term = $this->input->post('term');
        $driver_name = $this->input->post('driver_name');
        $salesperson = $this->input->post('salesperson');

        $this->db->trans_start();

        $lastquotation = $this->quotations->lastquotation();
        $tid = $lastquotation + 1;

        $data = array(
            'tid' => $tid,
            'csd' => $customer,
            'eid' => $this->aauth->get_user()->id,
            'invoicedate' => datefordatabase($invoicedate),
            'invoiceduedate' => datefordatabase($invoiceduedate),
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'ship_tax' => $ship_tax,
            'tax' => $tax,
            'total' => $total,
            'status' => 'pending',
            'note' => $note,
            'term' => $term,
            'loc' => $this->aauth->get_user()->loc,
            'i_class' => 0,
            'r_time' => 'now',
            'salesperson' => $salesperson,
            'driver_name' => $driver_name
        );

        $this->db->insert('geopos_quotations', $data);
        $quotation_id = $this->db->insert_id();

        // Add quotation items
        $product_id = $this->input->post('product_id');
        $product_name = $this->input->post('product_name');
        $product_des = $this->input->post('product_des');
        $product_qty = $this->input->post('product_qty');
        $product_price = $this->input->post('product_price');
        $product_tax = $this->input->post('product_tax');
        $product_discount = $this->input->post('product_discount');
        $product_subtotal = $this->input->post('product_subtotal');
        $product_totaltax = $this->input->post('product_totaltax');
        $product_cat = $this->input->post('product_cat');

        if (is_array($product_id)) {
            for ($i = 0; $i < count($product_id); $i++) {
                if ($product_id[$i] != '') {
                    // Get product name from database
                    $this->db->select('product_name');
                    $this->db->from('geopos_products');
                    $this->db->where('pid', $product_id[$i]);
                    $query = $this->db->get();
                    $product_info = $query->row_array();
                    $product_name = $product_info['product_name'];
                    
                    // Calculate total tax
                    $qty = floatval($product_qty[$i]);
                    $price = floatval($product_price[$i]);
                    $tax_rate = floatval($product_tax[$i]);
                    $discount = floatval($product_discount[$i]);
                    $subtotal = ($qty * $price) - $discount;
                    $totaltax = ($subtotal * $tax_rate) / 100;
                    
                    $data = array(
                        'tid' => $tid,
                        'pid' => $product_id[$i],
                        'product' => $product_name,
                        'product_des' => $product_des[$i],
                        'qty' => $product_qty[$i],
                        'price' => $product_price[$i],
                        'tax' => $product_tax[$i],
                        'discount' => $product_discount[$i],
                        'subtotal' => $product_subtotal[$i],
                        'totaltax' => $totaltax,
                        'cat_id' => $product_cat[$i]
                    );
                    $this->db->insert('geopos_quotation_items', $data);
                }
            }
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            echo json_encode(array('status' => 'Error', 'message' => 'Error adding quotation'));
        } else {
            echo json_encode(array('status' => 'Success', 'message' => 'Quotation added successfully', 'id' => $quotation_id));
        }
    }

    public function updatequotation()
    {
        if (!$this->aauth->permission_new(null, 'quotationEditQuotation')) {
            echo json_encode(array('status' => 'Error', 'message' => 'You have insufficient permissions to edit quotations'));
            return;
        }
        
        $id = $this->input->post('quotation_id');
        // var_dump($id);
        // exit;
        $customer = $this->input->post('customer');
        $invoicedate = $this->input->post('invoicedate');
        $invoiceduedate = $this->input->post('invoiceduedate');
        $subtotal = $this->input->post('subtotal');
        $shipping = $this->input->post('shipping');
        $ship_tax = $this->input->post('ship_tax');
        $tax = $this->input->post('tax');
        $total = $this->input->post('total');
        $note = $this->input->post('note');
        $term = $this->input->post('term');
        $driver_name = $this->input->post('driver_name');
        $salesperson = $this->input->post('salesperson');

        // Validate that we have a quotation ID
        if (empty($id)) {
            echo json_encode(array('status' => 'Error', 'message' => 'Quotation ID is required'));
            return;
        }

        $this->db->trans_start();

        // Get original tid before update for comparison
        $this->db->select('tid');
        $this->db->from('geopos_quotations');
        $this->db->where('id', $id);
        $original_tid = $this->db->get()->row()->tid;
        error_log("UpdateQuotation - Original TID: $original_tid");

        // Update quotation main record - DO NOT update tid, preserve original
        $data = array(
            'csd' => $customer,
            'invoicedate' => datefordatabase($invoicedate),
            'invoiceduedate' => datefordatabase($invoiceduedate),
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'ship_tax' => $ship_tax,
            'tax' => $tax,
            'total' => $total,
            'note' => $note,
            'term' => $term,
            'salesperson' => $salesperson,
            'driver_name' => $driver_name
            // Note: 'tid' is intentionally NOT included to preserve the original value
        );

        // Update quotation main record - explicitly exclude tid to preserve original value
        $this->db->where('id', $id);
        $this->db->update('geopos_quotations', $data);
        
        // Verify that tid was not changed (safety check)
        $this->db->select('tid');
        $this->db->from('geopos_quotations');
        $this->db->where('id', $id);
        $current_tid = $this->db->get()->row()->tid;
        
        // Log for debugging (remove in production)
        error_log("UpdateQuotation - ID: $id, Original TID: $original_tid, Current TID: $current_tid");
        error_log("UpdateQuotation - POST data: " . print_r($_POST, true));
        
        // Verify tid was preserved
        if ($original_tid != $current_tid) {
            error_log("ERROR: TID changed from $original_tid to $current_tid during update!");
        }

        // Handle quotation items - proper add/update/delete logic
        $product_id = $this->input->post('product_id');
        $product_des = $this->input->post('product_des');
        $product_qty = $this->input->post('product_qty');
        $product_price = $this->input->post('product_price');
        $product_tax = $this->input->post('product_tax');
        $product_discount = $this->input->post('product_discount');
        $product_subtotal = $this->input->post('product_subtotal');
        $product_cat = $this->input->post('product_cat');
        $item_id = $this->input->post('item_id'); // Hidden field for existing items

        if (is_array($product_id)) {
            // Get existing items for this quotation
            $this->db->select('id');
            $this->db->from('geopos_quotation_items');
            $this->db->where('tid', $id);
            $existing_items = $this->db->get()->result_array();
            $existing_item_ids = array_column($existing_items, 'id');
            
            $processed_item_ids = array();

            for ($i = 0; $i < count($product_id); $i++) {
                if ($product_id[$i] != '') {
                    // Get product name from database
                    $this->db->select('product_name');
                    $this->db->from('geopos_products');
                    $this->db->where('pid', $product_id[$i]);
                    $query = $this->db->get();
                    $product_info = $query->row_array();
                    $product_name = $product_info['product_name'];
                    
                    // Calculate total tax
                    $qty = floatval($product_qty[$i]);
                    $price = floatval($product_price[$i]);
                    $tax_rate = floatval($product_tax[$i]);
                    $discount = floatval($product_discount[$i]);
                    $subtotal = ($qty * $price) - $discount;
                    $totaltax = ($subtotal * $tax_rate) / 100;
                    
                    $item_data = array(
                        'tid' => $id,
                        'pid' => $product_id[$i],
                        'product' => $product_name,
                        'product_des' => $product_des[$i],
                        'qty' => $product_qty[$i],
                        'price' => $product_price[$i],
                        'tax' => $product_tax[$i],
                        'discount' => $product_discount[$i],
                        'subtotal' => $product_subtotal[$i],
                        'totaltax' => $totaltax,
                        'cat_id' => $product_cat[$i]
                    );

                    // Check if this is an existing item (has item_id) or new item
                    if (isset($item_id[$i]) && !empty($item_id[$i])) {
                        // Update existing item
                        $this->db->where('id', $item_id[$i]);
                        $this->db->where('tid', $id);
                        $this->db->update('geopos_quotation_items', $item_data);
                        $processed_item_ids[] = $item_id[$i];
                    } else {
                        // Insert new item
                        $this->db->insert('geopos_quotation_items', $item_data);
                        $processed_item_ids[] = $this->db->insert_id();
                    }
                }
            }

            // Delete items that are no longer in the form
            $items_to_delete = array_diff($existing_item_ids, $processed_item_ids);
            if (!empty($items_to_delete)) {
                $this->db->where_in('id', $items_to_delete);
                $this->db->where('tid', $id);
                $this->db->delete('geopos_quotation_items');
            }
        } else {
            // If no products are provided, delete all items
            $this->db->where('tid', $id);
            $this->db->delete('geopos_quotation_items');
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            echo json_encode(array('status' => 'Error', 'message' => 'Error updating quotation'));
        } else {
            echo json_encode(array('status' => 'Success', 'message' => 'Quotation updated successfully'));
        }
    }

    public function ajax_list()
    {
        $list = $this->quotations->get_datatables(
            $this->limited,
            $this->input->post('expression'),
            $this->input->post('expression2'),
            $this->input->post('value'),
            $this->input->post('value2'),
            $this->input->post('joinQuery'),
            $this->input->post('invoices'),
            $this->input->post('driver'),
            $this->input->post('param'),
            $this->input->post('due_date')
        );

        $data = array();
        $no = $_POST['start'];
        foreach ($list as $quotation) {
            $no++;
            $row = array();
            $row[] = $no;
            $row[] = $quotation->tid;
            $row[] = $quotation->csd;
            $row[] = $quotation->name;
            $row[] = 'QUOTATION';
            $row[] = dateformat($quotation->invoicedate);
            $row[] = amountExchange($quotation->tax, 0, $this->aauth->get_user()->loc);
            $row[] = amountExchange($quotation->total - $quotation->tax, 0, $this->aauth->get_user()->loc);
            $row[] = amountExchange($quotation->total, 0, $this->aauth->get_user()->loc);
            $row[] = '<span class="badge badge-' . ($quotation->status == 'pending' ? 'warning' : ($quotation->status == 'accepted' ? 'success' : 'danger')) . '">' . ucfirst($quotation->status) . '</span>';
            // Print button with permission check
            $printButton = '';
            if ($this->aauth->permission_new(null, 'quotationPrintQuotation')) {
                $printButton = '<a href="' . site_url('quotations/printquotation?id=' . $quotation->id) . '" class="btn btn-info btn-sm" target="_blank"><i class="fa fa-print"></i></a>';
            }
            $row[] = $printButton;
            $row[] = '<input type="checkbox" name="receipt" value="' . $quotation->id . '">';
            
            // Action buttons with permission checks
            $actionButtons = '<div class="btn-group" style="gap: 5px;">';
            
            if ($this->aauth->permission_new(null, 'quotationEditQuotation')) {
                $actionButtons .= '<a href="' . site_url('quotations/edit?id=' . $quotation->id) . '" class="btn btn-primary btn-sm" style="margin-right: 3px;" target="_blank"><i class="fa fa-edit"></i></a>';
            }
            
            if ($this->aauth->permission_new(null, 'quotationDeleteQuotation')) {
                $actionButtons .= '<a href="#" class="btn btn-danger btn-sm delete-object" data-object-id="' . $quotation->id . '" style="margin-right: 3px;" target="_blank"><i class="fa fa-trash"></i></a>';
            }
            
            if ($this->aauth->permission_new(null, 'quotationConvertQuotation')) {
                $actionButtons .= '<a href="' . site_url('quotations/convert?id=' . $quotation->id) . '" class="btn btn-success btn-sm" title="Convert to Invoice" target="_blank"><i class="fa fa-arrow-circle-right"></i></a>';
            }
            
            $actionButtons .= '</div>';
            $row[] = $actionButtons;
            $data[] = $row;
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->quotations->count_all($this->limited),
            "recordsFiltered" => $this->quotations->count_filtered_with_params(
                $this->limited,
                $this->input->post('expression'),
                $this->input->post('expression2'),
                $this->input->post('value'),
                $this->input->post('value2'),
                $this->input->post('joinQuery'),
                $this->input->post('invoices'),
                $this->input->post('driver'),
                $this->input->post('param'),
                $this->input->post('due_date')
            ),
            "data" => $data,
        );
        echo json_encode($output);
    }

    public function delete()
    {
        if (!$this->aauth->permission_new(null, 'quotationDeleteQuotation')) {
            echo json_encode(array('status' => 'Error', 'message' => 'You have insufficient permissions to delete quotations'));
            return;
        }
        
        $id = $this->input->get('id');
        if ($this->quotations->quotation_delete($id, $this->limited)) {
            echo json_encode(array('status' => 'Success', 'message' => 'Quotation deleted successfully'));
        } else {
            echo json_encode(array('status' => 'Error', 'message' => 'Error deleting quotation'));
        }
    }

    public function printquotation()
    {
        if (!$this->aauth->permission_new(null, 'quotationPrintQuotation')) {
            exit('<h3>Sorry! You have insufficient permissions to print quotations</h3>');
        }
        
        $id = $this->input->get('id');
        $data['quotation'] = $this->quotations->quotation_details($id, $this->limited);
        $data['products'] = $this->quotations->quotation_products($id);
        $data['employee'] = $this->quotations->employee($data['quotation']['eid']);
        $data['company'] = $this->settings->company_details(1);
        $this->load->view('quotations/printquotation', $data);
    }

    public function convert()
    {
        if (!$this->aauth->permission_new(null, 'quotationConvertQuotation')) {
            exit('<h3>Sorry! You have insufficient permissions to convert quotations</h3>');
        }
        
        $id = $this->input->get('id');
        $quotation = $this->quotations->quotation_details($id, $this->limited);
        $products = $this->quotations->quotation_products($id);
        
        // Convert quotation to invoice
        $this->db->trans_start();
        
        // Create invoice
        $invoice_data = array(
            'tid' => $this->quotations->lastquotation() + 1,
            'csd' => $quotation['csd'],
            'eid' => $quotation['eid'],
            'invoicedate' => date('Y-m-d'),
            'invoiceduedate' => $quotation['invoiceduedate'],
            'subtotal' => $quotation['subtotal'],
            'shipping' => $quotation['shipping'],
            'ship_tax' => $quotation['ship_tax'],
            'tax' => $quotation['tax'],
            'total' => $quotation['total'],
            'status' => 'due',
            'note' => $quotation['note'],
            'term' => $quotation['term'],
            'loc' => $quotation['loc'],
            'i_class' => 0,
            'r_time' => 'now',
            'salesperson' => $quotation['salesperson'],
            'driver_name' => $quotation['driver_name']
        );
        
        $this->db->insert('geopos_invoices', $invoice_data);
        $invoice_id = $this->db->insert_id();
        
        // Copy products to invoice items
        foreach ($products as $product) {
            $item_data = array(
                'tid' => $invoice_id,
                'pid' => $product['pid'],
                'product' => $product['product'],
                'product_des' => $product['product_des'],
                'qty' => $product['qty'],
                'price' => $product['price'],
                'tax' => $product['tax'],
                'discount' => $product['discount'],
                'subtotal' => $product['subtotal'],
                'totaltax' => $product['totaltax'],
                'cat_id' => $product['cat_id']
            );
            $this->db->insert('geopos_invoice_items', $item_data);
        }
        
        // Update quotation status
        $this->db->where('id', $id);
        $this->db->update('geopos_quotations', array('status' => 'converted'));
        
        $this->db->trans_complete();
        
        if ($this->db->trans_status() === FALSE) {
            echo json_encode(array('status' => 'Error', 'message' => 'Error converting quotation'));
        } else {
            echo json_encode(array('status' => 'Success', 'message' => 'Quotation converted to invoice successfully', 'invoice_id' => $invoice_id));
        }
    }

    public function search_customers()
    {
        $query = $this->input->get('query');
        
        if (empty($query) || strlen($query) < 1) {
            echo json_encode(array('status' => 'error', 'message' => 'Query too short'));
            return;
        }
        
        // Use caching for search results
        $cache_key = 'search_customers_' . md5($query) . '_' . $this->aauth->get_user()->loc;
        $this->load->driver('cache');
        
        $customers = $this->cache->get($cache_key);
        
        if ($customers === FALSE) {
            $this->db->select('id, name, company, postbox, email');
            $this->db->from('geopos_customers');
            
            // Optimized search - prioritize exact matches
            $this->db->group_start();
            $this->db->like('name', $query, 'after'); // Start with query
            $this->db->or_like('company', $query, 'after');
            $this->db->or_like('postbox', $query, 'after');
            $this->db->group_end();
            
            if ($this->aauth->get_user()->loc) {
                $this->db->where('loc', $this->aauth->get_user()->loc);
            } elseif (!BDATA) {
                $this->db->where('loc', 0);
            }
            
            $this->db->limit(8); // Reduced from 10 to 8
            $this->db->order_by('name', 'ASC');
            
            $query_result = $this->db->get();
            $customers = $query_result->result_array();
            
            // Cache search results for 2 minutes
            $this->cache->save($cache_key, $customers, 120);
        }
        
        echo json_encode(array(
            'status' => 'success',
            'customers' => $customers
        ));
    }

    public function get_customer_details()
    {
        $customer_id = $this->input->get('customer_id');
        
        if (empty($customer_id)) {
            echo json_encode(array('status' => 'error', 'message' => 'Customer ID required'));
            return;
        }
        
        $this->db->select('id, name, company, email, phone, address, city, region, country, postbox');
        $this->db->from('geopos_customers');
        $this->db->where('id', $customer_id);
        
        if ($this->aauth->get_user()->loc) {
            $this->db->where('loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('loc', 0);
        }
        
        $query = $this->db->get();
        
        if ($query->num_rows() > 0) {
            $customer = $query->row_array();
            echo json_encode(array(
                'status' => 'success',
                'customer' => $customer
            ));
        } else {
            echo json_encode(array('status' => 'error', 'message' => 'Customer not found'));
        }
    }

    public function get_all_customers()
    {
        // Use caching to reduce database load
        $cache_key = 'customers_' . $this->aauth->get_user()->loc . '_' . $this->aauth->get_user()->id;
        $this->load->driver('cache');
        
        $customers = $this->cache->get($cache_key);
        
        if ($customers === FALSE) {
            $this->db->select('id, name, company, postbox, email');
            $this->db->from('geopos_customers');
            
            if ($this->aauth->get_user()->loc) {
                $this->db->where('loc', $this->aauth->get_user()->loc);
            } elseif (!BDATA) {
                $this->db->where('loc', 0);
            }
            
            $this->db->order_by('name', 'ASC');
            $this->db->limit(20); // Reduced from 30 to 20
            
            $query = $this->db->get();
            $customers = $query->result_array();
            
            // Cache for 5 minutes
            $this->cache->save($cache_key, $customers, 300);
        }
        
        echo json_encode(array(
            'status' => 'success',
            'customers' => $customers
        ));
    }

    public function get_all_products()
    {
        // Use caching for products
        $cache_key = 'products_' . $this->aauth->get_user()->loc . '_' . $this->aauth->get_user()->id;
        $this->load->driver('cache');
        
        $products = $this->cache->get($cache_key);
        
        if ($products === FALSE) {
            $this->db->select('pid as id, product_name as name, product_code as code, product_price as price, taxrate as tax, product_des as description, code_type');
            $this->db->from('geopos_products');
            
            if ($this->aauth->get_user()->loc) {
                $this->db->where('loc', $this->aauth->get_user()->loc);
            } elseif (!BDATA) {
                $this->db->where('loc', 0);
            }
            
            $this->db->order_by('product_name', 'ASC');
            $this->db->limit(20); // Reduced from 30 to 20
            
            $query = $this->db->get();
            $products = $query->result_array();
            
            // Cache for 5 minutes
            $this->cache->save($cache_key, $products, 300);
        }
        
        echo json_encode(array(
            'status' => 'success',
            'products' => $products
        ));
    }

    public function search_products()
    {
        $query = $this->input->get('query');
        
        if (empty($query) || strlen($query) < 1) {
            echo json_encode(array('status' => 'error', 'message' => 'Query too short'));
            return;
        }
        
        // Use caching for search results
        $cache_key = 'search_products_' . md5($query) . '_' . $this->aauth->get_user()->loc;
        $this->load->driver('cache');
        
        $products = $this->cache->get($cache_key);
        
        if ($products === FALSE) {
            $this->db->select('pid as id, product_name as name, product_code as code, product_price as price, taxrate as tax, product_des as description, code_type');
            $this->db->from('geopos_products');
            
            // Optimized search - prioritize exact matches
            $this->db->group_start();
            $this->db->like('product_name', $query, 'after'); // Start with query
            $this->db->or_like('product_code', $query, 'after');
            $this->db->or_like('product_des', $query, 'after');
            $this->db->group_end();
            
            if ($this->aauth->get_user()->loc) {
                $this->db->where('loc', $this->aauth->get_user()->loc);
            } elseif (!BDATA) {
                $this->db->where('loc', 0);
            }
            
            $this->db->limit(8); // Reduced from 10 to 8
            $this->db->order_by('product_name', 'ASC');
            
            $query_result = $this->db->get();
            $products = $query_result->result_array();
            
            // Cache search results for 2 minutes
            $this->cache->save($cache_key, $products, 120);
        }
        
        echo json_encode(array(
            'status' => 'success',
            'products' => $products
        ));
    }

    public function format_amount()
    {
        // Check if user is logged in
        if (!$this->aauth->is_loggedin()) {
            echo json_encode(array('error' => 'Not logged in'));
            return;
        }
        
        // Check CSRF token
        if (!$this->security->csrf_verify()) {
            echo json_encode(array('error' => 'CSRF token mismatch'));
            return;
        }
        
        // Simple approach - just return the amount with proper formatting
        $amount = $this->input->post('amount');
        if ($amount === null || $amount === '') {
            $amount = 0;
        }
        
        // Convert to float to ensure proper decimal handling
        $amount = floatval($amount);
        
        // Use amountExchange with dynamic precision based on user location setting
        // The precision is determined by the user's location setting, not hardcoded
        $formatted = amountExchange($amount, 0, $this->aauth->get_user()->loc);
        echo json_encode(array('formatted' => $formatted));
    }
}

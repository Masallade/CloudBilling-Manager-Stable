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

class Purchase extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('purchase_model', 'purchase');
        $this->load->library("Aauth");
        if (!$this->aauth->is_loggedin()) {
            redirect('/user/', 'refresh');
        }

        if (!$this->aauth->premission(2) && !$this->aauth->premission(13)) {

            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');

        }
        $this->li_a = 'stock';
        //exit('Under Dev Mode');
$this->load->library('dpdf');

    }

    //create invoice
    public function create()
    {
        $this->load->library("Common");
        $data['taxlist'] = $this->common->taxlist($this->config->item('tax'));
        $this->load->model('plugins_model', 'plugins');
        $data['exchange'] = $this->plugins->universal_api(5);
        $data['currency'] = $this->purchase->currencies();
        $this->load->model('customers_model', 'customers');
        $data['customergrouplist'] = $this->customers->group_list();
        $data['lastinvoice'] = $this->purchase->lastpurchase();
        $data['terms'] = $this->purchase->billingterms();
        $head['title'] = "New Purchase";
        $head['usernm'] = $this->aauth->get_user()->username;
        $data['warehouse'] = $this->purchase->warehouses();
        $data['taxdetails'] = $this->common->taxdetail();
        $this->load->view('fixed/header', $head);
        $this->load->view('purchase/newinvoice', $data);
        $this->load->view('fixed/footer');
    }

    //edit invoice
    public function edit()
    {
        $tid = $this->input->get('id');
        $data['id'] = $tid;
        $data['title'] = "Purchase Order $tid";
        $this->load->model('customers_model', 'customers');
        $data['customergrouplist'] = $this->customers->group_list();
        $data['terms'] = $this->purchase->billingterms();
        $data['invoice'] = $this->purchase->purchase_details($tid);
        $data['products'] = $this->purchase->purchase_products($tid);;
        $head['title'] = "Edit Invoice #$tid";
        $head['usernm'] = $this->aauth->get_user()->username;
        $data['warehouse'] = $this->purchase->warehouses();
        $data['currency'] = $this->purchase->currencies();
        $this->load->model('plugins_model', 'plugins');
        $data['exchange'] = $this->plugins->universal_api(5);
        $this->load->library("Common");
        $data['taxlist'] = $this->common->taxlist_edit($data['invoice']['taxstatus']);
        $this->load->view('fixed/header', $head);
        $this->load->view('purchase/edit', $data);
        $this->load->view('fixed/footer');
    }

    //invoices list
    public function index()
    {
        $head['title'] = "Manage Purchase Orders";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $this->load->view('purchase/invoices');
        $this->load->view('fixed/footer');
    }
        public function verification()
    {
        $head['title'] = "Manage Purchase Orders";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $this->load->view('purchase/verification');
        $this->load->view('fixed/footer');
    }
       public function ajax_list_verification()
    {

        $list = $this->purchase->get_datatables();
        $data = array();

        $no = $this->input->post('start');

        foreach ($list as $invoices) {
            $no++;
            $row = array();
            $row[] = $no;
            $row[] =  $invoices->tid ;
            $row[] = $invoices->name;
            $row[] = dateformat($invoices->invoicedate);
            // $row[] = amountExchange($invoices->total, 0, $this->aauth->get_user()->loc);
           // $row[] = '<span class="st-' . $invoices->status . '">' . $this->lang->line(ucwords($invoices->status)) . '</span>';
           if( $invoices->status=="Verified"){
               $row[] = '<span class="badge badge-primary">' . $invoices->status . '</span>';
           }else{
               $row[] = '<span class="st-' . $invoices->status . '">' . $this->lang->line(ucwords($invoices->status)) . '</span>';
           }
            
                        $row[] = '<a href="' . base_url("purchase/verify?id=$invoices->id") . '" class="btn btn-success btn-xs"><i class="fa fa-eye"></i> View & Verify</a> ';
//&nbsp; <a href="' . base_url("purchase/printinvoice?id=$invoices->id") . '&d=1" class="btn btn-info btn-xs"  title="Download"><span class="fa fa-download"></span></a>&nbsp; &nbsp;<a href="#" data-object-id="' . $invoices->id . '" class="btn btn-danger btn-xs delete-object"><span class="fa fa-trash"></span></a>'
            $data[] = $row;
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->purchase->count_all(),
            "recordsFiltered" => $this->purchase->count_filtered(),
            "data" => $data,
        );
        //output to json format
        echo json_encode($output);

    }

    //action
    // public function action()
    // {
    //     $currency = $this->input->post('mcurrency');
    //     $customer_id = $this->input->post('customer_id');
    //     $invocieno = $this->input->post('invocieno');
    //     $invoicedate = $this->input->post('invoicedate');
    //     $invocieduedate = $this->input->post('invocieduedate');
    //     $notes = $this->input->post('notes', true);
    //     $tax = $this->input->post('tax_handle');
    //     $subtotal = rev_amountExchange_s($this->input->post('subtotal'), $currency, $this->aauth->get_user()->loc);
    //     $shipping = rev_amountExchange_s($this->input->post('shipping'), $currency, $this->aauth->get_user()->loc);
    //     $shipping_tax = rev_amountExchange_s($this->input->post('ship_tax'), $currency, $this->aauth->get_user()->loc);
    //     $ship_taxtype = $this->input->post('ship_taxtype');
    //     if ($ship_taxtype == 'incl') @$shipping = $shipping - $shipping_tax;
    //     $refer = $this->input->post('refer', true);
    //     $total = rev_amountExchange_s($this->input->post('total'), $currency, $this->aauth->get_user()->loc);
    //     $total_tax = 0;
    //     $total_discount = 0;
    //     $discountFormat = $this->input->post('discountFormat');
    //     $pterms = $this->input->post('pterms');
    //     $i = 0;
    //     if ($discountFormat == '0') {
    //         $discstatus = 0;
    //     } else {
    //         $discstatus = 1;
    //     }

    //     if ($customer_id == 0) {
    //         echo json_encode(array('status' => 'Error', 'message' =>
    //             "Please add a new supplier or search from a previous added!"));
    //         exit;
    //     }
    //     $this->db->trans_start();
    //     //products
    //     $transok = true;
    //     //Invoice Data
    //     $bill_date = datefordatabase($invoicedate);
    //     $bill_due_date = datefordatabase($invocieduedate);
    //     $data = array('tid' => $invocieno, 'invoicedate' => $bill_date, 'invoiceduedate' => $bill_due_date, 'subtotal' => $subtotal, 'shipping' => $shipping, 'ship_tax' => $shipping_tax, 'ship_tax_type' => $ship_taxtype, 'total' => $total, 'notes' => $notes, 'csd' => $customer_id, 'eid' => $this->aauth->get_user()->id, 'taxstatus' => $tax, 'discstatus' => $discstatus, 'format_discount' => $discountFormat, 'refer' => $refer, 'term' => $pterms, 'loc' => $this->aauth->get_user()->loc, 'multi' => $currency);


    //     if ($this->db->insert('geopos_purchase', $data)) {
    //         $invocieno = $this->db->insert_id();

    //         $pid = $this->input->post('pid');
    //         $productlist = array();
    //         $prodindex = 0;
    //         $itc = 0;
    //         $flag = false;
    //         $product_id = $this->input->post('pid');
    //         $product_name1 = $this->input->post('product_name', true);
    //         $product_qty = $this->input->post('product_qty');
    //         $product_price = $this->input->post('product_price');
    //         $product_tax = $this->input->post('product_tax');
    //         $product_discount = $this->input->post('product_discount');
    //         $product_subtotal = $this->input->post('product_subtotal');
    //         $ptotal_tax = $this->input->post('taxa');
    //         $ptotal_disc = $this->input->post('disca');
    //         $product_des = $this->input->post('product_description', true);
    //         $product_unit = $this->input->post('unit');
    //         $product_hsn = $this->input->post('hsn');

    //             $gross =0;


    //         foreach ($pid as $key => $value) {


    //             $new_price = rev_amountExchange_s($product_price[$key], $currency, $this->aauth->get_user()->loc);
    //             $new_qty = numberClean($product_qty[$key]);

    //             $tax_rate = numberClean($product_tax[$key]);
    //             $dis_rate = numberClean($product_discount[$key]);

    //             if (isset($product_id[$key])) {


    //                 $this->db->select('product_price,qty');
    //                 $this->db->from('geopos_products');
    //                 $this->db->where('pid', $product_id[$key]);
    //                 $avg_query = $this->db->get();
    //                 $avg_data_ret = $avg_query->row_array();
    //                 $old_price = $avg_data_ret['product_price'];
    //                 $old_qty = $avg_data_ret['qty'];

    //             }
    //             $old_price_qty=$old_price * $old_qty;
    //             $new_price_qty=$new_price * $new_qty;
    //             $up_qty=$old_qty + $new_qty;

    //             $avg_price =($old_price_qty+$new_price_qty) / $up_qty;

    //             $avg_tax = '';
    //             $avg_discount = '';
    //             $avg_subtotal = '';


    //               $unit_sub = ($avg_price * $new_qty);

    //             switch ($tax) {
    //                 case 'yes':
    //                     {

    //                         if ($discountFormat == 'b_p') {
    //                             $avg_discount = ($dis_rate / 100) * $avg_price * $new_qty;
    //                             $unit_sub = $unit_sub - $avg_discount;
    //                         } elseif ($discountFormat == 'bflat') {
    //                             $unit_sub = $unit_sub - $dis_rate;
    //                             $avg_discount= $dis_rate;
    //                         }

    //                         //  var Inpercentage = precentCalc(totalPrice, vatVal);
    //                         $avg_tax = ($tax_rate / 100) * $unit_sub;

    //                         // totalValue = totalPrice + Inpercentage;
    //                         $unit_sub = $unit_sub + $avg_tax;


    //                         if ($discountFormat == '%') {
    //                             $avg_discount = ($dis_rate / 100) * $avg_price * $new_qty;
    //                             $unit_sub = $unit_sub - $avg_discount;
    //                         } elseif ($discountFormat == 'flat') {
    //                             $unit_sub = $unit_sub - $dis_rate;
    //                              $avg_discount= $dis_rate;
    //                         }
    //                     }

    //                     break;
    //                 case 'incl':
    //                      {

    //                         $avg_tax = ($tax_rate / 100) * $unit_sub;
    //                         //$unit_sub = $unit_sub + $avg_tax;

    //                         if ($discountFormat == 'b_p') {
    //                             $avg_discount = ($dis_rate / 100) * ($unit_sub);
    //                             $unit_sub = $unit_sub - $avg_discount;
    //                         } elseif ($discountFormat == 'bflat') {
    //                             $unit_sub = $unit_sub - $dis_rate;
    //                               $avg_discount= $dis_rate;
    //                         }




    //                         if ($discountFormat == '%') {
    //                             $avg_discount = ($dis_rate / 100) * $unit_sub;
    //                             $unit_sub = $unit_sub - $avg_discount;
    //                         } elseif ($discountFormat == 'flat') {
    //                             $unit_sub = $unit_sub - $dis_rate;
    //                              $avg_discount= $dis_rate;
    //                         }
    //                     }
    //                     break;
    //                 case 'no':
    //                     {
    //                         $avg_tax=0;
    //  if ($discountFormat == 'b_p') {
    //                             $avg_discount = ($dis_rate / 100) * $unit_sub;
    //                             $unit_sub = $unit_sub - $avg_discount;
    //                         } elseif ($discountFormat == 'bflat') {
    //                             $unit_sub = $unit_sub - $dis_rate;
    //                               $avg_discount= $dis_rate;
    //                         }




    //                         if ($discountFormat == '%') {
    //                             $avg_discount = ($dis_rate / 100) * $unit_sub;
    //                             $unit_sub = $unit_sub - $avg_discount;
    //                         } elseif ($discountFormat == 'flat') {
    //                             $unit_sub = $unit_sub - $dis_rate;
    //                               $avg_discount= $dis_rate;
    //                         }
    //                     }
    //                     break;
    //             }


    //             $data = array(
    //                 'tid' => $invocieno,
    //                 'pid' => $product_id[$key],
    //                 'product' => $product_name1[$key],
    //                 'code' => $product_hsn[$key],
    //                 'qty' => numberClean($product_qty[$key]),
    //                 'price' =>$avg_price,
    //                 'tax' => numberClean($product_tax[$key]),
    //                 'discount' => numberClean($product_discount[$key]),
    //                 'subtotal' => $unit_sub,
    //                 'totaltax' =>$avg_tax,
    //                 'totaldiscount' =>$avg_discount,
    //                 'product_des' => $product_des[$key],
    //                 'unit' => $product_unit[$key]
    //             );

    //             $total_discount +=$avg_discount;
    //             $total_tax +=$avg_tax;
    //             $gross+=$unit_sub;

    //             $flag = true;
    //             $productlist[$prodindex] = $data;
    //             $i++;
    //             $prodindex++;
    //             $amt = numberClean($product_qty[$key]);

    //             if ($product_id[$key] > 0) {
    //                 if ($this->input->post('update_stock') == 'yes') {

    //                     $this->db->set('qty', "qty+$amt", FALSE);
    //                     $this->db->where('pid', $product_id[$key]);
    //                     $this->db->update('geopos_products');
    //                 }
    //                 $itc += $amt;
    //             }

    //         }
    //         if ($prodindex > 0) {
    //             $this->db->insert_batch('geopos_purchase_items', $productlist);
    //             $this->db->set(array('discount' => rev_amountExchange_s(amountFormat_general($total_discount), $currency, $this->aauth->get_user()->loc), 'tax' => rev_amountExchange_s(amountFormat_general($total_tax), $currency, $this->aauth->get_user()->loc),'total' => $gross, 'items' => $itc));
    //             $this->db->where('id', $invocieno);
    //             $this->db->update('geopos_purchase');

    //         } else {
    //             echo json_encode(array('status' => 'Error', 'message' =>
    //                 "Please choose product from product list. Go to Item manager section if you have not added the products."));
    //             $transok = false;
    //         }


    //         echo json_encode(array('status' => 'Success', 'message' => $this->lang->line('Purchase order success') . "<a href='view?id=$invocieno' class='btn btn-info btn-lg'><span class='fa fa-eye' aria-hidden='true'></span>" . $this->lang->line('View') . " </a>"));
    //     } else {
    //         echo json_encode(array('status' => 'Error', 'message' => $this->lang->line('ERROR')));
    //         $transok = false;
    //     }


    //     if ($transok) {
    //         $this->db->trans_complete();
    //     } else {
    //         $this->db->trans_rollback();
    //     }


    // }


    public function ajax_list()
    {
        $list = $this->purchase->get_datatables();
        $data = array();
        $no = $this->input->post('start');
        foreach ($list as $invoices) {
            $no++;
            $row = array();
            $row[] = $no;

                $row[] = '<a href="' . base_url("purchase/edit?id=$invoices->id") . '">&nbsp; ' . $invoices->tid . '</a>';
            $row[] = $invoices->name;
            $row[] = dateformat($invoices->invoicedate);
            // $row[] = amountExchange($invoices->total, 0, $this->aauth->get_user()->loc);

              if( $invoices->status=="Verified"){
               $row[] = '<span class="badge badge-primary">' . $invoices->status . '</span>';
           }else{
               $row[] = '<span class="st-' . $invoices->status . '">' . $this->lang->line(ucwords($invoices->status)) . '</span>';
           }
            // $row[] = '<span class="st-' . $invoices->status . '">' . $this->lang->line(ucwords($invoices->status)) . '</span>';

                    $row[] = ' <a href="' . base_url("purchase/printinvoice?id=$invoices->id") . '&d=1" class="btn btn-info btn-xs"  title="Download"><span         class="fa fa-download"></span></a>&nbsp; &nbsp;<a href="#" data-object-id="' . $invoices->id . '" class="btn btn-danger btn-xs delete-object"><span class="fa   fa-trash"></span></a>';
         
            $data[] = $row;
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->purchase->count_all(),
            "recordsFiltered" => $this->purchase->count_filtered(),
            "data" => $data,
        );
        //output to json format
        echo json_encode($output);

    }

    public function view()
    {
        $this->load->model('accounts_model');
        $data['acclist'] = $this->accounts_model->accountslist((integer)$this->aauth->get_user()->loc);
        $tid = intval($this->input->get('id'));
        $data['id'] = $tid;
        $head['title'] = "Purchase $tid";
        $data['invoice'] = $this->purchase->purchase_details($tid);
        $data['products'] = $this->purchase->purchase_products($tid);
        $data['activity'] = $this->purchase->purchase_transactions($tid);
        $data['attach'] = $this->purchase->attach($tid);
        $data['employee'] = $this->purchase->employee($data['invoice']['eid']);
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        if ($data['invoice']['tid']) $this->load->view('purchase/view', $data);
        $this->load->view('fixed/footer');

    }


    public function printinvoice()
    {

        $tid = $this->input->get('id');

        $data['id'] = $tid;
        $data['title'] = "Purchase $tid";
        $data['invoice'] = $this->purchase->purchase_details($tid);
        $data['products'] = $this->purchase->purchase_products($tid);
        $data['employee'] = $this->purchase->employee($data['invoice']['eid']);
        $data['invoice']['multi'] = 0;

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

            $pdf->Output('Purchase_#' . $data['invoice']['tid'] . '.pdf', 'D');
        } else {
            $pdf->Output('Purchase_#' . $data['invoice']['tid'] . '.pdf', 'I');
        }


    }
    public function print_purchase_invoice()
    {
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
     
	 //var_dump($data['products']); exit; 
	 
    ob_end_clean();
    ob_start();
ini_set('memory_limit', '64M');

    $data_tr="";

  $left_logo=base_url("assets/images/prime_food_logo.jpg");
  $data['left_logo'] = $left_logo;
  $right_logo=base_url("assets/images/halal_logo.jpg");
   $data['right_logo'] = $right_logo;

   $this->load->library('pdf');

$html = $this->load->view('print_files/ptest', $data, true);

        //     $header = $this->load->view('print_files/invoice-header_v' . INVV, $data, true);
        //     $pdf = $this->pdf->load_split_letter(array('margin_top' => 40));
        //     $pdf->SetHTMLHeader($header);
        // }
        // if (INVV == 2) {
             $pdf = $this->pdf->load_split(array('margin_top' => 10));
        // }
         $pdf->SetHTMLFooter('<div style="font-family: serif; font-size: 12px;width:60px;  color: #5C5C5C; font-style: italic; position:fixed; top: -15px;right: 0px;">{PAGENO}/{nbpg}  </div>');
         $pdf->WriteHTML($html);
         $file_name = preg_replace('/[^A-Za-z0-9]+/', '-', 'Invoice__' . $data['invoice']['name'] . '_' . $data['invoice']['tid']);
        if ($this->input->get('d')) {
             $pdf->Output($file_name . '.pdf', 'D');
         } else {
             $pdf->Output($file_name . '.pdf', 'I');
         }

    }





    public function delete_i()
    {
        $id = $this->input->post('deleteid');

        if ($this->purchase->purchase_delete($id)) {
            echo json_encode(array('status' => 'Success', 'message' =>
                "Purchase Order #$id has been deleted successfully!"));

        } else {

            echo json_encode(array('status' => 'Error', 'message' =>
                "There is an error! Purchase has not deleted."));
        }

    }

    // public function editaction()
    // {
    //     $currency = $this->input->post('mcurrency');
    //     $customer_id = $this->input->post('customer_id');
    //     $invocieno = $this->input->post('iid');
    //     $invoicedate = $this->input->post('invoicedate');
    //     $invocieduedate = $this->input->post('invocieduedate');
    //     $notes = $this->input->post('notes', true);
    //     $tax = $this->input->post('tax_handle');
    //     $refer = $this->input->post('refer', true);
    //     $total = rev_amountExchange_s($this->input->post('total'), $currency, $this->aauth->get_user()->loc);
    //     $total_tax = 0;
    //     $total_discount = 0;
    //     $discountFormat = $this->input->post('discountFormat');
    //     $pterms = $this->input->post('pterms');
    //     $ship_taxtype = $this->input->post('ship_taxtype');
    //     $subtotal = rev_amountExchange_s($this->input->post('subtotal'), $currency, $this->aauth->get_user()->loc);
    //     $shipping = rev_amountExchange_s($this->input->post('shipping'), $currency, $this->aauth->get_user()->loc);
    //     $shipping_tax = rev_amountExchange_s($this->input->post('ship_tax'), $currency, $this->aauth->get_user()->loc);
    //     if ($ship_taxtype == 'incl') $shipping = $shipping - $shipping_tax;

    //     $itc = 0;
    //     if ($discountFormat == '0') {
    //         $discstatus = 0;
    //     } else {
    //         $discstatus = 1;
    //     }

    //     if ($customer_id == 0) {
    //         echo json_encode(array('status' => 'Error', 'message' =>
    //             "Please add a new supplier or search from a previous added!"));
    //         exit();
    //     }

    //     $this->db->trans_start();
    //     $flag = false;
    //     $transok = true;


    //     //Product Data
    //     $pid = $this->input->post('pid');
    //     $productlist = array();

    //     $prodindex = 0;

    //     $this->db->delete('geopos_purchase_items', array('tid' => $invocieno));
    //     $product_id = $this->input->post('pid');
    //     $product_name1 = $this->input->post('product_name', true);
    //     $product_qty = $this->input->post('product_qty');
    //     $old_product_qty = $this->input->post('old_product_qty');
    //     if ($old_product_qty == '') $old_product_qty = 0;
    //     $product_price = $this->input->post('product_price');
    //     $product_tax = $this->input->post('product_tax');
    //     $product_discount = $this->input->post('product_discount');
    //     $product_subtotal = $this->input->post('product_subtotal');
    //     $ptotal_tax = $this->input->post('taxa');
    //     $ptotal_disc = $this->input->post('disca');
    //     $product_des = $this->input->post('product_description', true);
    //     $product_unit = $this->input->post('unit');
    //     $product_hsn = $this->input->post('hsn');

    //     foreach ($pid as $key => $value) {
    //         $total_discount += numberClean(@$ptotal_disc[$key]);
    //         $total_tax += numberClean($ptotal_tax[$key]);
    //         $data = array(
    //             'tid' => $invocieno,
    //             'pid' => $product_id[$key],
    //             'product' => $product_name1[$key],
    //             'code' => $product_hsn[$key],
    //             'qty' => numberClean($product_qty[$key]),
    //             'price' => rev_amountExchange_s($product_price[$key], $currency, $this->aauth->get_user()->loc),
    //             'tax' => numberClean($product_tax[$key]),
    //             'discount' => numberClean($product_discount[$key]),
    //             'subtotal' => rev_amountExchange_s($product_subtotal[$key], $currency, $this->aauth->get_user()->loc),
    //             'totaltax' => rev_amountExchange_s($ptotal_tax[$key], $currency, $this->aauth->get_user()->loc),
    //             'totaldiscount' => rev_amountExchange_s($ptotal_disc[$key], $currency, $this->aauth->get_user()->loc),
    //             'product_des' => $product_des[$key],
    //             'unit' => $product_unit[$key]
    //         );


    //         $productlist[$prodindex] = $data;

    //         $prodindex++;
    //         $amt = numberClean($product_qty[$key]);
    //         $itc += $amt;

    //         if ($this->input->post('update_stock') == 'yes') {
    //             $amt = numberClean(@$product_qty[$key]) - numberClean(@$old_product_qty[$key]);
    //             $this->db->set('qty', "qty+$amt", FALSE);
    //             $this->db->where('pid', $product_id[$key]);
    //             $this->db->update('geopos_products');
    //         }
    //         $flag = true;
    //     }

    //     $bill_date = datefordatabase($invoicedate);
    //     $bill_due_date = datefordatabase($invocieduedate);
    //     $total_discount = rev_amountExchange_s(amountFormat_general($total_discount), $currency, $this->aauth->get_user()->loc);
    //     $total_tax = rev_amountExchange_s(amountFormat_general($total_tax), $currency, $this->aauth->get_user()->loc);

    //     $data = array('invoicedate' => $bill_date, 'invoiceduedate' => $bill_due_date, 'subtotal' => $subtotal, 'shipping' => $shipping, 'ship_tax' => $shipping_tax, 'ship_tax_type' => $ship_taxtype, 'discount' => $total_discount, 'tax' => $total_tax, 'total' => $total, 'notes' => $notes, 'csd' => $customer_id, 'items' => $itc, 'taxstatus' => $tax, 'discstatus' => $discstatus, 'format_discount' => $discountFormat, 'refer' => $refer, 'term' => $pterms, 'multi' => $currency);
    //     $this->db->set($data);
    //     $this->db->where('id', $invocieno);

    //     if ($flag) {

    //         if ($this->db->update('geopos_purchase', $data)) {
    //             $this->db->insert_batch('geopos_purchase_items', $productlist);
    //             echo json_encode(array('status' => 'Success', 'message' =>
    //                 "Purchase order has  been updated successfully! <a href='view?id=$invocieno' class='btn btn-info btn-lg'><span class='fa fa-eye' aria-hidden='true'></span> View </a> "));
    //         } else {
    //             echo json_encode(array('status' => 'Error', 'message' =>
    //                 "There is a missing field!"));
    //             $transok = false;
    //         }


    //     } else {
    //         echo json_encode(array('status' => 'Error', 'message' =>
    //             "Please add atleast one product in order!"));
    //         $transok = false;
    //     }

    //     if ($this->input->post('update_stock') == 'yes') {
    //         if ($this->input->post('restock')) {
    //             foreach ($this->input->post('restock') as $key => $value) {
    //                 $myArray = explode('-', $value);
    //                 $prid = $myArray[0];
    //                 $dqty = numberClean($myArray[1]);
    //                 if ($prid > 0) {

    //                     $this->db->set('qty', "qty-$dqty", FALSE);
    //                     $this->db->where('pid', $prid);
    //                     $this->db->update('geopos_products');
    //                 }
    //             }

    //         }
    //     }


    //     if ($transok) {
    //         $this->db->trans_complete();
    //     } else {
    //         $this->db->trans_rollback();
    //     }
    // }
     public function action()
    {

        $currency = $this->input->post('mcurrency');
        $customer_id = $this->input->post('customer_id');
        $invocieno = $this->input->post('invocieno');
        $invoicedate = $this->input->post('invoicedate');
        $invocieduedate = $this->input->post('invocieduedate');
        $notes = $this->input->post('notes', true);
        $tax = $this->input->post('tax_handle');
        $subtotal = rev_amountExchange_s($this->input->post('subtotal'), $currency, $this->aauth->get_user()->loc);
        $shipping = rev_amountExchange_s($this->input->post('shipping'), $currency, $this->aauth->get_user()->loc);
        $shipping_tax = rev_amountExchange_s($this->input->post('ship_tax'), $currency, $this->aauth->get_user()->loc);
        $ship_taxtype = $this->input->post('ship_taxtype');
        if ($ship_taxtype == 'incl') @$shipping = $shipping - $shipping_tax;
        $refer = $this->input->post('refer', true);
        $total = rev_amountExchange_s($this->input->post('total'), $currency, $this->aauth->get_user()->loc);
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
        $this->db->trans_start();
        //products
        $transok = true;
        //Invoice Data
        $bill_date = datefordatabase($invoicedate);
        $bill_due_date = datefordatabase($invocieduedate);
        $data = array('tid' => $invocieno, 'invoicedate' => $bill_date, 'invoiceduedate' => $bill_due_date, 'subtotal' => $subtotal, 'shipping' => $shipping, 'ship_tax' => $shipping_tax, 'ship_tax_type' => $ship_taxtype, 'total' => $total, 'notes' => $notes, 'csd' => $customer_id, 'eid' => $this->aauth->get_user()->id, 'taxstatus' => $tax, 'discstatus' => $discstatus, 'format_discount' => $discountFormat, 'refer' => $refer, 'term' => $pterms, 'loc' => $this->aauth->get_user()->loc, 'multi' => $currency);


        if ($this->db->insert('geopos_purchase', $data)) {
            $invocieno = $this->db->insert_id();

            $pid_list = $this->input->post('pid');
            $pid=array();
            $product_id=array();
            $productlist = array();
            $prodindex = 0;
            $itc = 0;
            $flag = false;
            $product_id_list = $this->input->post('pid');
            // print_r($pid);die();
            $product_name1_list = $this->input->post('product_name', true);
            $product_name1 = array();
            $product_qty_list = $this->input->post('product_qty');
            $product_qty = array();
            $product_price_list = $this->input->post('product_price');
            $product_price = array();
            $packing_list1 = $this->input->post('packing_type');
            $packing_list=array();
            //$packing = array();
            $product_tax_list = $this->input->post('product_tax');
            $packing_tax=array();
            $product_discount_list = $this->input->post('product_discount');
            $product_discount=array();
            $product_subtotal_list = $this->input->post('product_subtotal');
            $product_subtotal=array();
            $ptotal_tax_list = $this->input->post('taxa');
            $product_tax=array();
            $ptotal_disc_list = $this->input->post('disca');
            $product_disc=array();
            $product_des_list = $this->input->post('product_description', true);
            $product_des=array();
            $product_unit_list = $this->input->post('unit');
            $product_unit=array();
            $product_hsn_list = $this->input->post('hsn');
            $product_hsn=array();
            $gross =0;
            $i=0;
            foreach ($pid_list as $key => $value) {
                if($value!=0){
                    $pid[$i]=$value;
                    $product_id[$i]=$value;
                    $product_name1[$i]=$product_name1_list[$key];
                    $product_qty[$i]=$product_qty_list[$key];
                    $product_price[$i]=$product_price_list[$key];
                    $product_list[$i]=$product_list1[$key];
                    $product_tax[$i]=$product_tax_list[$key];
                    $product_discount[$i]=$product_discount_list[$key];
                    $product_subtotal[$i]=$product_subtotal_list[$key];
                    $product_tax[$i]=$product_tax_list[$key];
                    $product_disc[$i]=$product_disc_list[$key];
                    $product_des[$i]=$product_des_list[$key];
                    $product_unit[$i]=$product_unit_list[$key];
                    $product_hsn[$i]=$product_hsn_list[$key];
                    $packing[$i]=$packing_list1[$key];
                    $i++;
                }
            }

            foreach ($pid as $key => $value) {
                $new_price = rev_amountExchange_s($product_price[$key], $currency, $this->aauth->get_user()->loc);
                $new_qty = numberClean($product_qty[$key]);

                $tax_rate = numberClean($product_tax[$key]);
                $dis_rate = numberClean($product_discount[$key]);

                if (isset($product_id[$key]) ) {
                    $this->db->select('product_price,qty');
                    $this->db->from('geopos_products');
                    $this->db->where('pid', $product_id[$key]);
                    $avg_query = $this->db->get();
                    $avg_data_ret = $avg_query->row_array();
                    $old_price = $avg_data_ret['product_price'];
                    $old_qty = $avg_data_ret['qty'];

                }
                $old_price_qty=$old_price * $old_qty;
                $new_price_qty=$new_price * $new_qty;
                $up_qty=$old_qty + $new_qty;

                $avg_price =($old_price_qty+$new_price_qty) / $up_qty;

                $avg_tax = '';
                $avg_discount = '';
                $avg_subtotal = '';


                $unit_sub = ($avg_price * $new_qty);

                switch ($tax) {
                    case 'yes':
                    {

                        if ($discountFormat == 'b_p') {
                            $avg_discount = ($dis_rate / 100) * $avg_price * $new_qty;
                            $unit_sub = $unit_sub - $avg_discount;
                        } elseif ($discountFormat == 'bflat') {
                            $unit_sub = $unit_sub - $dis_rate;
                            $avg_discount= $dis_rate;
                        }

                            //  var Inpercentage = precentCalc(totalPrice, vatVal);
                        $avg_tax = ($tax_rate / 100) * $unit_sub;

                            // totalValue = totalPrice + Inpercentage;
                        $unit_sub = $unit_sub + $avg_tax;


                        if ($discountFormat == '%') {
                            $avg_discount = ($dis_rate / 100) * $avg_price * $new_qty;
                            $unit_sub = $unit_sub - $avg_discount;
                        } elseif ($discountFormat == 'flat') {
                            $unit_sub = $unit_sub - $dis_rate;
                            $avg_discount= $dis_rate;
                        }
                    }

                    break;
                    case 'incl':
                    {

                        $avg_tax = ($tax_rate / 100) * $unit_sub;
                            //$unit_sub = $unit_sub + $avg_tax;

                        if ($discountFormat == 'b_p') {
                            $avg_discount = ($dis_rate / 100) * ($unit_sub);
                            $unit_sub = $unit_sub - $avg_discount;
                        } elseif ($discountFormat == 'bflat') {
                            $unit_sub = $unit_sub - $dis_rate;
                            $avg_discount= $dis_rate;
                        }




                        if ($discountFormat == '%') {
                            $avg_discount = ($dis_rate / 100) * $unit_sub;
                            $unit_sub = $unit_sub - $avg_discount;
                        } elseif ($discountFormat == 'flat') {
                            $unit_sub = $unit_sub - $dis_rate;
                            $avg_discount= $dis_rate;
                        }
                    }
                    break;
                    case 'no':
                    {
                        $avg_tax=0;
                        if ($discountFormat == 'b_p') {
                            $avg_discount = ($dis_rate / 100) * $unit_sub;
                            $unit_sub = $unit_sub - $avg_discount;
                        } elseif ($discountFormat == 'bflat') {
                            $unit_sub = $unit_sub - $dis_rate;
                            $avg_discount= $dis_rate;
                        }




                        if ($discountFormat == '%') {
                            $avg_discount = ($dis_rate / 100) * $unit_sub;
                            $unit_sub = $unit_sub - $avg_discount;
                        } elseif ($discountFormat == 'flat') {
                            $unit_sub = $unit_sub - $dis_rate;
                            $avg_discount= $dis_rate;
                        }
                    }
                    break;
                }
                $data = array(
                    'tid' => $invocieno,
                    'pid' => $product_id[$key],
                    'product' => $product_name1[$key],
                    'code' => $product_hsn[$key],
                    'qty' => numberClean($product_qty[$key]),
                    'price' =>$avg_price,
                    'tax' => numberClean($product_tax[$key]),
                    'discount' => numberClean($product_discount[$key]),
                    'subtotal' => $unit_sub,
                    'totaltax' =>$avg_tax,
                    'totaldiscount' =>$avg_discount,
                    'product_des' => $product_des[$key],
                    'unit' => $product_unit[$key],
                    'packing' => $packing[$key]
                );
                $total_discount +=$avg_discount;
                $total_tax +=$avg_tax;
                $gross+=$unit_sub;
                $flag = true;
                $productlist[$prodindex] = $data;
                $i++;
                $prodindex++;
                $amt = numberClean($product_qty[$key]);
                if ($product_id[$key] > 0) {
                    if ($this->input->post('update_stock') == 'yes') {

                        $this->db->set('qty', "qty+$amt", FALSE);
                        $this->db->where('pid', $product_id[$key]);
                        $this->db->update('geopos_products');
                    }
                    $itc += $amt;
                }

            }
            if ($prodindex > 0) {
                $this->db->insert_batch('geopos_purchase_items', $productlist);
                // print_r($this->db->last_query());    die();
                $this->db->set(array('discount' => rev_amountExchange_s(amountFormat_general($total_discount), $currency, $this->aauth->get_user()->loc), 'tax' => rev_amountExchange_s(amountFormat_general($total_tax), $currency, $this->aauth->get_user()->loc),'total' => $gross, 'items' => $itc));
                $this->db->where('id', $invocieno);
                $this->db->update('geopos_purchase');
            } else {
                echo json_encode(array('status' => 'Error', 'message' =>
                    "Please choose product from product list. Go to Item manager section if you have not added the products."));
                $transok = false;
            }
            echo json_encode(array('status' => 'Success','pid' => $invocieno,'controller' => 'PKDUR1', 'message' => $this->lang->line('Purchase order success') . "<a href='view?id=$invocieno' class='btn btn-info btn-lg'><span class='fa fa-eye' aria-hidden='true'></span>" . $this->lang->line('View') . " </a>"));
        } else {
            echo json_encode(array('status' => 'Error', 'message' => $this->lang->line('ERROR')));
            $transok = false;
        }
        if ($transok) {
            $this->db->trans_complete();
        } else {
            $this->db->trans_rollback();
        }
    }
        public function verify()
    {

        $tid = $this->input->get('id');
        $data['id'] = $tid;
        $data['title'] = "Purchase Order $tid";
        $this->load->model('customers_model', 'customers');
        $data['customergrouplist'] = $this->customers->group_list();
        $data['terms'] = $this->purchase->billingterms();
        $data['invoice'] = $this->purchase->purchase_details($tid);
        $data['products'] = $this->purchase->purchase_products($tid);;
        $head['title'] = "Verify Invoice #$tid";
        $head['usernm'] = $this->aauth->get_user()->username;
        $data['warehouse'] = $this->purchase->warehouses();
        $data['currency'] = $this->purchase->currencies();
        $this->load->model('plugins_model', 'plugins');
        $data['exchange'] = $this->plugins->universal_api(5);
        $this->load->library("Common");
        $data['taxlist'] = $this->common->taxlist_edit($data['invoice']['taxstatus']);
        $this->load->view('fixed/header', $head);
        $this->load->view('purchase/verify', $data);
        $this->load->view('fixed/footer');

    }
  public function editaction()
    {

        $currency = $this->input->post('mcurrency');
        $customer_id = $this->input->post('customer_id');
        $invocieno = $this->input->post('iid');
        $invoicedate = $this->input->post('invoicedate');
        $invocieduedate = $this->input->post('invocieduedate');
        $notes = $this->input->post('notes', true);
        $tax = $this->input->post('tax_handle');
        $refer = $this->input->post('refer', true);
        $total = rev_amountExchange_s($this->input->post('total'), $currency, $this->aauth->get_user()->loc);
        $total_tax = 0;
        $total_discount = 0;
        $discountFormat = $this->input->post('discountFormat');
        $pterms = $this->input->post('pterms');
        $ship_taxtype = $this->input->post('ship_taxtype');
        $subtotal = rev_amountExchange_s($this->input->post('subtotal'), $currency, $this->aauth->get_user()->loc);
        $shipping = rev_amountExchange_s($this->input->post('shipping'), $currency, $this->aauth->get_user()->loc);
        $shipping_tax = rev_amountExchange_s($this->input->post('ship_tax'), $currency, $this->aauth->get_user()->loc);
        if ($ship_taxtype == 'incl') $shipping = $shipping - $shipping_tax;
        $itc = 0;
        if ($discountFormat == '0') {
            $discstatus = 0;
        } else {
            $discstatus = 1;
        }

        if ($customer_id == 0) {
            echo json_encode(array('status' => 'Error', 'message' =>
                "Please add a new supplier or search from a previous added!"));
            exit();
        }

        $this->db->trans_start();
        $flag = false;
        $transok = true;


        //Product Data
        $pid_list = $this->input->post('pid');
        $pid=array();
        $product_id=array();
        $productlist = array();
        $packing = array();
        $product_des = array();
        $prodindex = 0;
        $this->db->delete('geopos_purchase_items', array('tid' => $invocieno));

        $product_name1_list = $this->input->post('product_name', true);
        $product_name1 = array();
        $product_qty_list = $this->input->post('product_qty');
        $product_qty = array();
        $old_product_qty = $this->input->post('old_product_qty');
        if ($old_product_qty == '') $old_product_qty = 0;
        $product_price_list = $this->input->post('product_price');
        $product_price = array();
        $product_tax_list = $this->input->post('product_tax');
        $product_tax = array();
        $product_discount_list = $this->input->post('product_discount');
        $product_discount = array();
        $product_subtotal_list = $this->input->post('product_subtotal');
        $product_subtotal = array();
        $ptotal_tax1_list = $this->input->post('taxa');
        $product_tax1 = array();
        $ptotal_disc_list = $this->input->post('disca');
        $ptotal_disc = array();
        $product_description_list = $this->input->post('product_description', true);
        $product_description = array();
        $product_unit_list = $this->input->post('unit');
        $product_unit = array();
        $product_hsn_list = $this->input->post('hsn');
        $product_hsn = array();
        $packing_list = $this->input->post('packing_type');
        $packing = array();

        foreach ($pid_list as $key => $value) {
            if($value!=0){
                $pid[$i]=$value;
                $product_id[$i]=$value;
                $packing[$i]=$packing_list[$key];
                $product_des[$i]= $product_description[$key];
                $product_name1[$i]= $product_name1_list[$key];
                $product_qty[$i]= $product_qty_list[$key];
                $product_price[$i]= $product_price_list[$key];
                $product_tax[$i]= $product_tax_list[$key];
                $product_discount[$i]= $product_discount_list[$key];
                $product_subtotal[$i]= $product_subtotal_list[$key];
                $product_tax1[$i]= $ptotal_tax1_list[$key];
                $ptotal_disc[$i]= $ptotal_disc_list[$key];
                $product_description[$i]= $product_description_list[$key];
                $product_unit[$i]= $product_unit_list[$key];
                $product_hsn[$i]= $product_hsn_list[$key];
                $packing[$i]= $packing_list[$key];
                $i++;
            }
        }
        


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
                'totaltax' => rev_amountExchange_s($product_tax1[$key], $currency, $this->aauth->get_user()->loc),
                'totaldiscount' => rev_amountExchange_s($ptotal_disc[$key], $currency, $this->aauth->get_user()->loc),
                'product_des' => $product_description[$key],
                'unit' => $product_unit[$key],
                'packing' => $packing[$key]
            );


            $productlist[$prodindex] = $data;

            $prodindex++;
            $amt = numberClean($product_qty[$key]);
            $itc += $amt;

            if ($this->input->post('update_stock') == 'yes') {
                $amt = numberClean(@$product_qty[$key]) - numberClean(@$old_product_qty[$key]);
                $this->db->set('qty', "qty+$amt", FALSE);
                $this->db->where('pid', $product_id[$key]);
                $this->db->update('geopos_products');
            }
            $flag = true;
        }

        $bill_date = datefordatabase($invoicedate);
        $bill_due_date = datefordatabase($invocieduedate);
        $total_discount = rev_amountExchange_s(amountFormat_general($total_discount), $currency, $this->aauth->get_user()->loc);
        $total_tax = rev_amountExchange_s(amountFormat_general($total_tax), $currency, $this->aauth->get_user()->loc);
            if ($this->input->post('update_stock') == 'yes') {
               $Verify_status="Verified";
               $redirect_check="PKDUR";
            }else{
                $Verify_status="Due";
                $redirect_check="PKDUR1";
            }
        $data = array('invoicedate' => $bill_date, 'invoiceduedate' => $bill_due_date, 'subtotal' => $subtotal, 'shipping' => $shipping, 'ship_tax' => $shipping_tax, 'ship_tax_type' => $ship_taxtype, 'discount' => $total_discount, 'tax' => $total_tax, 'total' => $total, 'notes' => $notes, 'csd' => $customer_id, 'items' => $itc, 'taxstatus' => $tax, 'discstatus' => $discstatus, 'format_discount' => $discountFormat, 'refer' => $refer, 'term' => $pterms, 'multi' => $currency,'status' =>$Verify_status);
        $this->db->set($data);
        $this->db->where('id', $invocieno);

        if ($flag) {

            if ($this->db->update('geopos_purchase', $data)) {
                $this->db->insert_batch('geopos_purchase_items', $productlist);
                echo json_encode(array('status' => 'Success', 'pid' => $invocieno,'controller' =>$redirect_check, 'message' =>
                    "Purchase order has  been updated successfully! <a href='view?id=$invocieno' class='btn btn-info btn-lg'><span class='fa fa-eye' aria-hidden='true'></span> View </a> "));
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

        public function invoice_export(){


 // print_r($data['invoice']);die();

        $this->load->model('accounts_model');
        $data['acclist'] = $this->accounts_model->accountslist((integer)$this->aauth->get_user()->loc);
        $tid = intval($this->input->get('id'));
        $data['id'] = $tid;
        $head['title'] = "Purchase $tid";
        $data['invoice'] = $this->purchase->purchase_details($tid);
        $data['products'] = $this->purchase->purchase_products($tid);
        $data['activity'] = $this->purchase->purchase_transactions($tid);
        $data['attach'] = $this->purchase->attach($tid);
        $data['employee'] = $this->purchase->employee($data['invoice']['eid']);
        $head['usernm'] = $this->aauth->get_user()->username;
  ob_end_clean();

  $data_tr="";
  foreach ($data['products'] as $key => $row) {

    $data_tr .= '
  <tr>
   <td>'.$row["product"].'</td>
   <td>'.$row["product_des"].'</td>
   <td>'.$row["packing"].'</td>
   <td>'.$row["qty"].'</td>
  </tr>
 ';
        
    }
 
// $total=0;
// foreach ($data as $key => $row) {

//    $total+=$row["total"];
        
//     }


$invoice_date=$data['invoice']['invoicedate'];
$invoice_due_date=$data['invoice']['invoiceduedate'];
$company_name=$data['invoice']['name'];
$company_address=$data['invoice']['address'];
$company_city=$data['invoice']['city'];
$company_region=$data['invoice']['region'];
$company_country=$data['invoice']['country'];
$company_phone=$data['invoice']['phone'];
$company_email=$data['invoice']['email'];
$time=date("h:i:s");
$html = "
<html>
<head>
    <style>
        @page { margin: 120px 50px; }
        #header{ position: fixed;  top: -90px;}
        .left_header{float:left;}
        .center_header{margin-left:120px;width:400px}
        .right_header{position: fixed;  top: -90px;left:610px;}
        .pagenum:after {content: counter(page)}
        table{width:100%;}
    </style>
</head>
<body>
    <div id='header'>
       <div class='left_header'>
          <div>Order Date: $invoice_date</div>
          <div>Due Date: $invoice_due_date</div>

      </div>
      <div class='center_header'  style='font-size:18px;text-align:center;'>
          <div>
           <u><b> PEARL FOOD UK LTD </b>  </u>
        </div>
        <div>
            <b> Purchase Order </b> 
        </div>
      </div >
      <div class='right_header'>
        <span class='pagenum'>Page:</span>
      </div>
    </div>
<div>
Bill form<br><br>
$company_name
<br>
$company_address
<br>
$company_city $company_region $company_country
<br>
$company_phone
<br>
$company_email
</div>
<br><br>
<table>
  <thead>
    <tr>
        <th>
          Product
        </th>
        <th>
           Description
        </th>
        <th>
          Packing
        </th>

        <th>
          Quantity
        </th>
       
    </tr>
</thead>
$data_tr
<tr>
<td></td>
<td></td>
<td></td>

<td></td>

</tr>
</table>


</body>
</html>
";

        $this->dpdf->loadHtml($html);
        $this->dpdf->render();
        $this->dpdf->stream(""."BreakDownSheet".".pdf", array("Attachment"=>0));      
    }

    public function update_status()
    {
        $tid = $this->input->post('tid');
        $status = $this->input->post('status');


        $this->db->set('status', $status);
        $this->db->where('id', $tid);
        $this->db->update('geopos_purchase');

        echo json_encode(array('status' => 'Success', 'message' =>
            'Purchase Order Status updated successfully!', 'pstatus' => $status));
    }

    public function file_handling()
    {
        if ($this->input->get('op')) {
            $name = $this->input->get('name');
            $invoice = $this->input->get('invoice');
            if ($this->purchase->meta_delete($invoice, 4, $name)) {
                echo json_encode(array('status' => 'Success'));
            }
        } else {
            $id = $this->input->get('id');
            $this->load->library("Uploadhandler_generic", array(
                'accept_file_types' => '/\.(gif|jpe?g|png|docx|docs|txt|pdf|xls)$/i', 'upload_dir' => FCPATH . 'userfiles/attach/', 'upload_url' => base_url() . 'userfiles/attach/'
            ));
            $files = (string)$this->uploadhandler_generic->filenaam();
            if ($files != '') {

                $this->purchase->meta_insert($id, 4, $files);
            }
        }
    }
}
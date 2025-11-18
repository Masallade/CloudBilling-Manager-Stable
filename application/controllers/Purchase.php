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

use PHPMailer\PHPMailer\PHPMailer;

use PHPMailer\PHPMailer\Exception;

class Purchase extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('purchase_model', 'purchase');
		$this->load->model('settings_model', 'settings');
		$this->load->model('plugins_model', 'plugins');
		$this->load->model('customers_model', 'customers');
		$this->load->model('Stockreturn_model', 'stockreturn');
		$this->load->model('invoices_model', 'invoices');
		$this->load->model('employee_model', 'employee');
		$this->load->library("Custom");
		$this->load->library("Aauth");
		if (!$this->aauth->is_loggedin()) {
			redirect('/user/', 'refresh');
		}

		if (!$this->aauth->permission_new(null, 'purchaseAccess')) {

			exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
		}
		$this->li_a = 'purchase';
		//exit('Under Dev Mode');
		$this->load->library('dpdf');
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

	public function send_email($mailto, $mailtotitle, $subject, $message, $attachmenttrue = false, $attachment = '')
	{
		$this->load->library('ultimatemailer');
		$this->db->select('host,port,auth,auth_type,username,password,sender');
		$this->db->from('geopos_smtp');
		$query = $this->db->get();
		$smtpresult = $query->row_array();
		$host = $smtpresult['host'];
		$port = $smtpresult['port'];
		$auth = $smtpresult['auth'];
		$auth_type = $smtpresult['auth_type'];
		$username = $smtpresult['username'];;
		$password = $smtpresult['password'];
		$mailfrom = $smtpresult['sender'];
		$mailfromtilte = $this->config->item('ctitle');
		$this->ultimatemailer->load($host, $port, $auth, $auth_type, $username, $password, $mailfrom, $mailfromtilte, $mailto, $mailtotitle, $subject, $message,      $attachmenttrue, $attachment);
	}
	public function creditnotes_po()
	{
		if (!$this->aauth->permission_new(null, 'purchaseCreditNotePO')) {
			exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
		}
		$head['title'] = "Manage Credit Notes";
		$head['usernm'] = $this->aauth->get_user()->username;
		$this->load->view('fixed/header', $head);
		$this->load->view('stockreturn/creditnotes_supplier');
		$this->load->view('fixed/footer');
	}

	public function create_credit_note_po()
	{

		$data['emp'] = $this->plugins->universal_api(69);
		if ($data['emp']['key1']) {
			$data['employee'] = $this->employee->list_employee();
		}

		$this->load->library("Common");
		$data['custom_fields_c'] = $this->custom->add_fields(1);

		$data['exchange'] = $this->plugins->universal_api(5);
		$data['customergrouplist'] = $this->customers->group_list();
		$data['lastinvoice'] = $this->stockreturn->lastcredit();
		$data['warehouse'] = $this->invoices->warehouses();
		$data['terms'] = $this->invoices->billingterms();
		$data['currency'] = $this->invoices->currencies();

		$this->load->library("Common");
		$data['taxlist'] = $this->common->taxlist($this->config->item('tax'));
		$head['title'] = "New PO";
		$head['usernm'] = $this->aauth->get_user()->username;
		$data['taxdetails'] = $this->common->taxdetail();
		$data['custom_fields'] = $this->custom->add_fields(2);

		$this->load->view('fixed/header', $head);
		$this->load->view('stockreturn/newCrdeitnote_po', $data);
		$this->load->view('fixed/footer');
	}

	public function stock_report_export()
	{
		$data = $this->purchase->stock_report_export($_POST['hidden_fields'], $_POST['unposted_fields']);
		// var_dump($data);


		$cat_name = "";
		$start_date = $_POST['hidden_start_date'];
		$end_date = $_POST['hidden_end_date'];
		$cat = $_POST['categories_name'];
		// var_dump($cat); die();
		if ($cat) {
			$sql = "select title from geopos_product_cat where id IN  (" . $cat . ") ";


			$query = $this->db->query($sql);
			$res_array = $query->result_array();
			foreach ($res_array as $row) {
				$cat_name .= $row['title'] . ",  ";
			}
		}



		$data_tr = "";
		$end_cat = $_POST['hidden_end_category'];
		$start_cat = $_POST['hidden_start_category'];

		$categories = $_POST['categories_name'];
		$categories = explode(",", $categories);

		if ($start_cat == "" || $end_cat == "") {
			$start_cat = 1;
			$end_cat = 999;
		}





		$netsubtotal = 0;
		$subtotal = 0;
		ob_end_clean();
		$total_qty = 0;
		$total_sub = 0;
		$total_tax = 0;
		$currentCategory = null;
		$total_qty_cat = 0;
		$netsubtotal_cat = 0;
		$total_tax_cat = 0;
		$total_sub_cat = 0;

		foreach ($data as $key => $row) {
			if (in_array($row["id"], $categories)) {
				if ($row["title"] !== $currentCategory) {
					if ($currentCategory !== null) {
						$data_tr .= "<tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td style='border-top:2px solid black;border-bottom:2px solid black'> " . intval($total_qty_cat) . "</td>
                <td style='border-top:2px solid black;border-bottom:2px solid black'> " . amountExchange_s($netsubtotal_cat) . "</td>
                <td style='border-top:2px solid black;border-bottom:2px solid black'> " . amountExchange_s($total_tax_cat) . "</td>
                <td style='border-top:2px solid black;border-bottom:2px solid black'> " . amountExchange_s($total_sub_cat) . "</td>
            </tr>";
					}
					$currentCategory = $row["title"];
					$total_qty_cat = 0;
					$netsubtotal_cat = 0;
					$total_tax_cat = 0;
					$total_sub_cat = 0;
					$data_tr .= '<tr><td><u>' . $currentCategory . '</u></td></tr>';
				}

				$subtotal = $row["subtotal"] - $row["totaltax"];
				$netsubtotal += $subtotal;
				$netsubtotal_cat += $subtotal;

				$data_tr .= '
        <tr>
            <td>' . $row["po_no"] . '</td>
            <td>' . $row["supplier_name"] . '</td>
            <td>' . $row["product"] . '</td>
            <td>' . $row["po_date"] . '</td>
            <td><b>' . intval($row["qty"]) . '</b></td>
            <td>' . amountExchange_s($subtotal) . '</td>
            <td>' . amountExchange_s($row["totaltax"]) . '</td>
            <td>' . amountExchange_s($row["subtotal"]) . '</td>
        </tr>
    ';

				$total_qty += $row["qty"];
				$total_qty_cat += $row["qty"];
				$total_sub += $row["subtotal"];
				$total_sub_cat += $row["subtotal"];
				$total_tax += $row["totaltax"];
				$total_tax_cat += $row["totaltax"];
			}
		}

		$data_tr .= "<tr>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td style='border-top:2px solid black;border-bottom:2px solid black'> " . intval($total_qty_cat) . "</td>
	<td style='border-top:2px solid black;border-bottom:2px solid black'> " . amountExchange_s($netsubtotal_cat) . "</td>
	<td style='border-top:2px solid black;border-bottom:2px solid black'> " . amountExchange_s($total_tax_cat) . "</td>
	<td style='border-top:2px solid black;border-bottom:2px solid black'> " . amountExchange_s($total_sub_cat) . "</td>
</tr>";















		// foreach ($data as $key => $row) {

		//     $this->db->select('geopos_products.pid,geopos_products.pcat');
		//     $this->db->from('geopos_products');
		//     $this->db->where('product_code', $row["product"]);
		//     $query = $this->db->get();
		//     $product = $query->row_array();
		//     $pid = $product["pid"];
		//     $category=$product["pcat"];

		//     $this->db->select('geopos_products.*,geopos_product_cat.title as prod_cat_title');
		//     $this->db->from('geopos_products');
		//     $this->db->join('geopos_product_cat', 'geopos_products.pcat= geopos_product_cat.id');
		//     $this->db->where('geopos_products.pid', $pid);
		//     $query = $this->db->get();
		//     $product = $query->row_array();

		//     if (in_array($category, $categories)) {

		//         $subtotal= $row["subtotal"]-$row["totaltax"];
		//         $netsubtotal+=$subtotal;
		//         $data_tr .= '
		//         <tr>
		//         <td>'.$row["product"].'</td>
		//         <td>'.$row["product_des"].'</td>
		//         <td><b>'.$row["qty"].'</b></td>
		//         <td>'.$subtotal.'</td>
		//         <td>'.$row["totaltax"].'</td>
		//         <td>'.$row["subtotal"].'</td>
		//         </tr>
		//         ';
		//         $total_qty+=$row["qty"];
		//         $total_sub+=$row["subtotal"];
		//         $total_tax+=$row["totaltax"];
		//     }
		// }




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
      <div style='font-size:17px;text-align:center;' class='center_header'>
          <div>
           <u><b> " . $company['cname'] . " </b>  </u>
        </div>
        <div style='font-size:16px;text-align:center;'>
            <u><b> Purchase By Product(Summary)</b></u> 
        </div>
      </div >
      <div class='right_header'>
        <b><span class='pagenum'>Page:</span></b>
      </div>
    </div>
	<div style='font-size:13px;'>
              <div  style='float:left'>
                  <div><b>Date From:</b> $start_date</div>
              </div>
              <div style='float:right; margin-right:100px'>
              <div><b>Date To:</b> $end_date</div>
              </div>
    </div><br>
    <b>   Product Categories:</b> $cat_name
    <div style='font-size:13px;'>
      
        <div  style='float:left'>
            <div><b>Stock Code From:$start_cat</b></div>
            <div><b>Stock Code To:$end_cat</b></div>
        </div>
    </div>

    <div style='clear:both'>
    </div>
<br>
<table>
  <thead>
    <tr>
        <th>
            PO#
        </th>
        <th>
            Supplier
        </th>
        <th>
            Product Code
        </th>
        <th>
            Purchase Date
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
<td></td>
<td></td>
<td style='border-top:2px solid black;border-bottom:2px solid black'>" . intval($total_qty) . "</td>
<td style='border-top:2px solid black;border-bottom:2px solid black'>" . amountExchange_s($netsubtotal) . "</td>
<td style='border-top:2px solid black;border-bottom:2px solid black'>" . amountExchange_s($total_tax) . "</td>
<td style='border-top:2px solid black;border-bottom:2px solid black'>" . amountExchange_s($total_sub) . "</td>
</tr>
</table>



</body>
</html>
";
		$this->dpdf->loadHtml($html);
		$this->dpdf->render();
		$this->dpdf->stream("" . "Stock Report" . ".pdf", array("Attachment" => 0));
	}


	//create invoice
	public function create()
	{
		if (!$this->aauth->permission_new(null, 'purchaseCreateOrder')) {
			exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
		}
		$this->load->library("Common");
		$company = $this->settings->company_details(1);
		$head['company'] = $company;
		$head['usernm'] = $this->aauth->get_user()->username;
		$data['taxlist'] = $this->common->taxlist($this->config->item('tax'));
		$data['exchange'] = $this->plugins->universal_api(5);
		$data['currency'] = $this->purchase->currencies();
		$data['customergrouplist'] = $this->customers->group_list();
		$data['lastinvoice'] = $this->purchase->lastpurchase();
		$data['terms'] = $this->purchase->billingterms();
		$head['title'] = $this->lang->line('CreatePurchase');
		$head['usernm'] = $this->aauth->get_user()->username;
		$data['warehouse'] = $this->purchase->warehouses();
		$data['taxdetails'] = $this->common->taxdetail();
		$data['custom_fields'] = $this->custom->add_fields(2);
		$this->load->view('fixed/header', $head);
		$this->load->view('purchase/newinvoice', $data);
		$this->load->view('fixed/footer');
	}



	//invoices Sale Report
	public function extended()
	{
		$this->li_a = "purhcase_reports";
		$head['title'] = "Purchase By Product";
		$head['usernm'] = $this->aauth->get_user()->username;
		$this->db->select('geopos_product_cat.id, geopos_product_cat.title');
		$this->db->from('geopos_product_cat');
		$query = $this->db->get();
		$head['categories'] = $query->result_array();
		// var_dump($head['categories']); die();

		$this->load->view('fixed/header', $head);
		$this->load->view('purchase/invoicesale');
		$this->load->view('fixed/footer');
	}
	public function vat_purchase()
	{
		$this->li_a = "purhcase_reports";
		$head['title'] = "Purchase By Product";
		$head['usernm'] = $this->aauth->get_user()->username;
		$this->db->select('geopos_purchase.id, geopos_purchase.tax');
		$this->db->from('geopos_purchase');
		$query = $this->db->get();
		$head['categories'] = $query->result_array();
		// var_dump($head['categories']); die();

		$this->load->view('fixed/header', $head);
		$this->load->view('purchase/vat_purchase');
		$this->load->view('fixed/footer');
	}

	public function vat_purchase_report()
	{
		$company = $this->settings->company_details(1);
		$data_tr = "";
		$total_qty = 0;
		$total_amount = 0;

		$s_date = $_POST['start_date'];
		$e_date = $_POST['end_date'];
		$start = date("Y-m-d H:i:s", strtotime($_POST['start_date']));
		$end = date("Y-m-d H:i:s", strtotime($_POST['end_date'] . ' 23:59:59'));

		ob_end_clean();
		$no = 1;

		$this->db->select('geopos_purchase.invoicedate, geopos_purchase.status, geopos_purchase_items.product, geopos_purchase.tax');
		$this->db->from('geopos_purchase');
		$this->db->join('geopos_purchase_items', 'geopos_purchase.id = geopos_purchase_items.tid');
		$this->db->where('geopos_purchase.invoicedate BETWEEN "' . $start . '" AND "' . $end . '"', null, false);

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
                        <b><u> Purchase Report</u></b> 
                    </div>
                </div>
                <div class='right_header'>
                    <b><span class='pagenum'>Page:</span></b>
                </div>
            </div>
            <div style='font-size:13px;'>
                <div style='float:left'>
                    <div><b>Date From:</b> $s_date</div>
                </div>
                <div style='float:right; margin-right:100px'>
                    <div><b>Date To:</b> $e_date</div>
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


	public function purchase_by_supplier()
	{
		$this->load->library("Common");
		$data['taxlist'] = $this->common->taxlist($this->config->item('tax'));
		$data['exchange'] = $this->plugins->universal_api(5);
		$data['currency'] = $this->purchase->currencies();
		$data['customergrouplist'] = $this->customers->group_list();
		$data['lastinvoice'] = $this->purchase->lastpurchase();
		$data['terms'] = $this->purchase->billingterms();
		$head['title'] = "New Purchase";
		$head['usernm'] = $this->aauth->get_user()->username;
		$data['warehouse'] = $this->purchase->warehouses();
		$data['taxdetails'] = $this->common->taxdetail();
		$this->li_a = "purhcase_reports";
		$head['usernm'] = $this->aauth->get_user()->username;
		$head['title'] = 'Supplier';
		$this->load->view('fixed/header', $head);
		$this->load->view('purchase/purchase_by_supplier');
		$this->load->view('fixed/footer');
	}

	public function purchase_by_supplier_report()
	{
		$company = $this->settings->company_details(1);
		$data_tr = "";
		$total_quanity = 0;
		$id = $_POST['customer_id'];
		//   var_dump($name); die();
		//   var_dump($id); exit();
		$s_date = $_POST['start_date'];
		$e_date = $_POST['end_date'];
		$start = date("Y-m-d H:i:s", strtotime($_POST['start_date']));
		//   $end=date("Y-m-d H:i:s", strtotime($_POST['end_date']));

		$end = date("Y-m-d H:i:s", strtotime($_POST['end_date'] . ' 23:59:59'));

		$this->db->select('geopos_supplier.name');
		$this->db->from('geopos_supplier');
		$this->db->where('geopos_supplier.id', $id);
		$query = $this->db->get();
		$result = $query->result_array();
		foreach ($result as $key => $row) {
			$name = $row["name"];
		}
		ob_end_clean();
		$no = 1;

		$customer_name = "";
		$added_by = "";
		ob_end_clean();

		$this->db->select('geopos_supplier.name, geopos_purchase.total, geopos_purchase.tid, geopos_purchase_items.product, geopos_purchase_items.qty');
		$this->db->from('geopos_supplier');
		$this->db->join('geopos_purchase', 'geopos_supplier.id = geopos_purchase.csd');
		$this->db->join('geopos_purchase_items', 'geopos_purchase.id = geopos_purchase_items.tid');
		$this->db->where('geopos_supplier.id', $id);
		$this->db->where('geopos_purchase.invoicedate BETWEEN "' . $start . '" AND "' . $end . '"', null, false);

		$query = $this->db->get();
		$result = $query->result_array();

		$no_flag = '';
		$subtotal_qty = 0;
		$subtotal_total = 0;
		$subtotal = 0;
		$subqty = 0;
		$total_qty = 0;
		$total = 0;
		$title = null;
		$total_qty_group = 0; // New variable to track sum of quantities within each group
		$total_total_group = 0; // New variable to track sum of totals within each group
		$first_iteration = true;
		foreach ($result as $key => $row) {
			if ($row["tid"] !== $title) {
				if ($title !== null) {
					$data_tr .= "<tr>
					<td></td>
					<td style='border-top:2px solid black;border-bottom:2px solid black'>" . intval($subtotal_qty) . "</td>
					<td style='border-top:2px solid black;border-bottom:2px solid black'>" . amountExchange_s($subtotal_total) . "</td>
            </tr>";
				}
				$title = $row["tid"];
				$subtotal_qty = 0;
				$subtotal_total = 0;
				$data_tr .= '<tr><td><u>PO# ' . $title . '</u></td></tr>';
			}

			$subqty = $row["qty"];
			$subtotal_qty += $subqty;
			$total_qty += $subqty;
			$subtotal = $row["total"];
			$subtotal_total += $subtotal;
			$total += $subtotal;

			$data_tr .= '
        <tr>
            <td><b>' . $row["product"] . '</b></td>
            <td><b>' . intval($row["qty"]) . '</b></td>
            <td><b>' . amountExchange_s($row["total"]) . '</b></td>
        </tr>
    ';
		}

		$data_tr .= "<tr>
    <td></td>
	<td style='border-top:2px solid black;border-bottom:2px solid black'>" . intval($subtotal_qty) . "</td>
	<td style='border-top:2px solid black;border-bottom:2px solid black'>" . amountExchange_s($subtotal_total) . "</td>
</tr>";

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
                  <b><u> Purchase Report</u></b> 
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
			  <b> Supplier Name:</b> &nbsp;&nbsp;&nbsp;  $name
              <br>
             
          </div>
      
          <div style='clear:both'>
          </div>
      <br>
      <table>
        <thead  style='font-size:13px;'>
          <tr>
	   <td>
		  <u> Products </u>
	   </td>
	   <td>
		  <u> Item Quantity</u>
	   </td>
	   <td>
		  <u> Total Ammount</u>
	   </td>
          </tr>
      </thead>
      <tbody style='font-size:13px;'>
      $data_tr
	  <tr>
		<td></td>
		<td style='border-top:2px solid black;border-bottom:2px solid black'> " . intval($total_qty) . "</td>
		<td style='border-top:2px solid black;border-bottom:2px solid black'> " . amountExchange_s($total) . "</td>
		</tr>
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
	public function supplier_payment()
	{
		$this->load->library("Common");
		$data['taxlist'] = $this->common->taxlist($this->config->item('tax'));
		$data['exchange'] = $this->plugins->universal_api(5);
		$data['currency'] = $this->purchase->currencies();
		$data['customergrouplist'] = $this->customers->group_list();
		$data['lastinvoice'] = $this->purchase->lastpurchase();
		$data['terms'] = $this->purchase->billingterms();
		$head['title'] = "New Purchase";
		$head['usernm'] = $this->aauth->get_user()->username;
		$data['warehouse'] = $this->purchase->warehouses();
		$data['taxdetails'] = $this->common->taxdetail();
		$this->li_a = "purhcase_reports";
		$head['usernm'] = $this->aauth->get_user()->username;
		$head['title'] = 'Supplier';
		$this->load->view('fixed/header', $head);
		$this->load->view('purchase/supplier_payment_report');
		$this->load->view('fixed/footer');
	}

	public function supplier_payment_report()
	{
		$company = $this->settings->company_details(1);
		$data_tr = "";
		$total_quanity = 0;
		$id = $_POST['customer_id'];
		//   var_dump($name); die();
		//   var_dump($id); exit();
		$s_date = $_POST['start_date'];
		$e_date = $_POST['end_date'];
		$start = date("Y-m-d H:i:s", strtotime($_POST['start_date']));
		//   $end=date("Y-m-d H:i:s", strtotime($_POST['end_date']));

		$end = date("Y-m-d H:i:s", strtotime($_POST['end_date'] . ' 23:59:59'));
		ob_end_clean();
		$no = 1;

		ob_end_clean();

		$this->db->select('geopos_supplier.id, geopos_supplier.name, SUM(geopos_purchase.total) as total, SUM(geopos_purchase.pamnt) as total_pamnt');
		$this->db->from('geopos_supplier');
		$this->db->join('geopos_purchase', 'geopos_supplier.id = geopos_purchase.csd');
		$this->db->group_by('geopos_supplier.id');
		$this->db->where('geopos_purchase.invoicedate BETWEEN "' . $start . '" AND "' . $end . '"', null, false);

		$query = $this->db->get();
		$result = $query->result_array();
		// var_dump($result);
		// exit();
		$total_paid = 0;
		$total_due = 0;
		$total_all = 0;
		foreach ($result as $key => $row) {
			$name = $row['name'];
			$pamnt = $row['total_pamnt'];
			$total = $row['total'];
			$due = $row['total'] - $row['total_pamnt'];

			$data_tr .= '
        <tr>
            <td>' . $name . '</td>
            <td>' . $pamnt . '</td>
            <td>' . $due . '</td>
            <td>' . $total . '</td>
        </tr>';

			$total_paid += $row["total_pamnt"];
			$total_due += $due;
			$total_all += $row["total"];
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
                 <u><b>" . $company['cname'] . "</b>  </u>
              </div>
              <div >
                  <b><u> Supplier Payment Report</u></b> 
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
             
          </div>
      
          <div style='clear:both'>
          </div>
      <br>
      <table>
        <thead  style='font-size:13px;'>
          <tr>
	   <td>
		  <u> Supplier Name </u>
	   </td>
	   <td>
		  <u> Paid Ammount </u>
	   </td>
	   <td>
		  <u> Due Ammont</u>
	   </td>
	   <td>
		  <u> Total Ammount</u>
	   </td>
          </tr>
      </thead>
      <tbody style='font-size:13px;'>
      $data_tr
	  <tr>
		<td></td>
		<td style='border-top:2px solid black;border-bottom:2px solid black'> $total_paid</td>
		<td style='border-top:2px solid black;border-bottom:2px solid black'> $total_due</td>
		<td style='border-top:2px solid black;border-bottom:2px solid black'> $total_all</td>
		</tr>
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
	public function payment_date()
	{
		$this->load->library("Common");
		$data['taxlist'] = $this->common->taxlist($this->config->item('tax'));
		$data['exchange'] = $this->plugins->universal_api(5);
		$data['currency'] = $this->purchase->currencies();
		$data['customergrouplist'] = $this->customers->group_list();
		$data['lastinvoice'] = $this->purchase->lastpurchase();
		$data['terms'] = $this->purchase->billingterms();
		$head['title'] = "New Purchase";
		$head['usernm'] = $this->aauth->get_user()->username;
		$data['warehouse'] = $this->purchase->warehouses();
		$data['taxdetails'] = $this->common->taxdetail();
		$this->li_a = "purhcase_reports";
		$head['usernm'] = $this->aauth->get_user()->username;
		$head['title'] = 'Supplier';
		$this->load->view('fixed/header', $head);
		$this->load->view('purchase/payment_date_report');
		$this->load->view('fixed/footer');
	}

	public function payment_date_report()
	{
		$company = $this->settings->company_details(1);
		$data_tr = "";
		$total_quanity = 0;
		$id = $_POST['customer_id'];
		//   var_dump($name); die();
		//   var_dump($id); exit();
		$s_date = $_POST['start_date'];
		$e_date = $_POST['end_date'];
		$start = date("Y-m-d H:i:s", strtotime($_POST['start_date']));
		//   $end=date("Y-m-d H:i:s", strtotime($_POST['end_date']));

		$end = date("Y-m-d H:i:s", strtotime($_POST['end_date'] . ' 23:59:59'));
		$this->db->select('geopos_supplier.name');
		$this->db->from('geopos_supplier');
		$this->db->where('geopos_supplier.id', $id);
		$query = $this->db->get();
		$result = $query->result_array();
		foreach ($result as $key => $row) {
			$name = $row["name"];
		}
		ob_end_clean();
		$no = 1;

		ob_end_clean();

		$this->db->select('geopos_transactions.*, SUM(geopos_purchase.total) AS total_purchase , SUM(geopos_purchase.pamnt) AS total_pamnt');
		$this->db->from('geopos_transactions');
		$this->db->join('geopos_purchase', 'geopos_transactions.tid = geopos_purchase.id'); // Adjust the join condition accordingly
		$this->db->where('geopos_transactions.payerid', $id);
		$this->db->where('geopos_transactions.cat', 'Purchase');
		$this->db->where('geopos_transactions.ext', 1);
		$this->db->where('geopos_transactions.date BETWEEN "' . $start . '" AND "' . $end . '"', null, false);
		$this->db->group_by('geopos_transactions.id'); // Adjust the group by clause based on your table structure

		$total = '';
		$query = $this->db->get();
		$result = $query->result_array();
		// var_dump($result); exit();

		foreach ($result as $key => $row) {
			$t_date = $row['date'];
			$note = $row['note'];
			$debit = $row['debit'];
			$method = $row['method'];
			$total += $row['debit'];
			$outstanding_balance = $row['total_purchase'] - $row['total_pamnt'];

			$data_tr .= '
        <tr>
		<td>' . $note . '</td>
		<td>' . $method . '</td>
		<td>' . $t_date . '</td>
		<td>' . amountExchange_s($debit) . '</td>
        </tr>';
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
                 <u><b>" . $company['cname'] . "</b>  </u>
              </div>
              <div >
                  <b><u> Supplier Payment Report</u></b> 
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
			  <b> Supplier Name:</b> &nbsp;&nbsp;&nbsp;  $name
              <br>
             
          </div>
      
          <div style='clear:both'>
          </div>
      <br>
      <table>
        <thead  style='font-size:13px;'>
          <tr>
	   <td>
		  <u> Note </u>
	   </td>
	   <td>
		  <u> Method </u>
	   </td>
	   <td>
		  <u> Date </u>
	   </td>
	   <td>
		  <u> Ammount</u>
	   </td>
          </tr>
      </thead>
      <tbody style='font-size:13px;'>
      $data_tr
	  	<tr><td></td><td></td><th>Total Payment</th><td style='border-top:2px solid black;border-bottom:2px solid black'> " . amountExchange_s($total) . "</td></tr>
		<tr><td></td><td></td><th>Outstanding Balance</th><td style='border-top:2px solid black;border-bottom:2px solid black'>" . amountExchange_s($outstanding_balance) . "</td></tr>
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


	public function ajax_sale_list()
	{
		// $list = $this->purchase->get_sa_datatables();
		// var_dump($list); die();
		// $list = array_merge($list,$list2);
		$data = array();
		$no = $this->input->post('start');
		$cat_list = explode(",", $this->input->post('start_cat'));
		$s_date = $_POST['start_date'];
		$e_date = $_POST['end_date'];
		$start = date("Y-m-d H:i:s", strtotime($_POST['start_date']));
		//   $end=date("Y-m-d H:i:s", strtotime($_POST['end_date']));

		$end = date("Y-m-d H:i:s", strtotime($_POST['end_date'] . ' 23:59:59'));

		$this->db->select('geopos_purchase_items.tid,geopos_transactions.note,geopos_purchase.id, geopos_supplier.name, geopos_purchase.invoicedate, geopos_purchase.total');
		$this->db->from('geopos_purchase_items');
		$this->db->join('geopos_products', 'geopos_purchase_items.pid = geopos_products.pid');
		$this->db->join('geopos_product_cat', 'geopos_products.pcat = geopos_product_cat.id');
		$this->db->join('geopos_purchase', 'geopos_purchase_items.tid = geopos_purchase.id');
		$this->db->join('geopos_supplier', 'geopos_purchase.csd = geopos_supplier.id');
		$this->db->join('geopos_transactions', 'geopos_purchase.id = geopos_transactions.tid', 'left');
		$this->db->where_in('geopos_product_cat.id', $cat_list);
		$this->db->where('geopos_purchase.invoicedate BETWEEN "' . $start . '" AND "' . $end . '"', null, false);

		$query = $this->db->get();
		$result = $query->result();
		// var_dump($result); die();

		foreach ($result as $list) {
			$id = $list->id;
			$supplierName = $list->name;
			$invoiceDate = $list->invoicedate;
			$total = $list->total;
			// $tid = $list->tid;
			// var_dump($tid); die();

			// Additional SQL query to fetch cat_id values
			$sql = "SELECT `cat_id` FROM `geopos_purchase_items` WHERE `tid` = '$id'";
			// var_dump($sql); die();

			$query = $this->db->query($sql);
			$cats = $query->result_array();
			$cat_ids = array_map(function ($item) {
				return $item["cat_id"];
			}, $cats);

			// Check for intersection between $cat_list and $cat_ids
			$intersect = array_intersect($cat_list, $cat_ids);

			if (!empty($intersect)) {
				$no++;

				// Build row data
				$row = array();
				$row[] = $id;
				$row[] = $supplierName;
				$row[] = dateformat($invoiceDate);
				$row[] = $total;
				$row[] = '<input type="checkbox"  class="checkbox"  name="receipt" data-id="' . $list->id . '" value="' . $list->id . '">';

				// Add row to data array
				$data[] = $row;
			}
		}
		// foreach ($list as $invoices) {
		// 	$sql = "SELECT `cat_id` FROM `geopos_purchase_items` WHERE `tid` = '$invoices->id'";
		// 	$query = $this->db->query($sql);
		// 	$cats = $query->result_array();
		// 	$cat_ids = array_map(function ($item) {
		// 		return $item["cat_id"];
		// 	}, $cats);
		// 	$intersect = array_intersect($cat_list, $cat_ids);

		// 	if (!empty($intersect)) {

		// 		$no++;

		// 		$row = array();
		// 		$row[] =  $invoices->tid;
		// 		$row[] =  $invoices->name;
		// 		$row[] =  $invoices->company;
		// 		$row[] = dateformat($invoices->invoicedate);

		// 		$row[] = $invoices->total;
		// 		$row[] = '<input type="checkbox"  class="checkbox"  name="receipt" data-id="' . $invoices->id . '" value="' . $invoices->id . '">';
		// 		// $row[] = $cat_list;
		// 		// $row[] = $cat_ids;
		// 		$data[] = $row;
		// 	}
		// }
		$output = array(
			"draw" => $this->input->post('draw'),
			"data" => $data,
		);
		//output to json format
		echo json_encode($output);
	}



	public function send()
	{
		// Load PHPMailer library
		$this->load->library('PHPMailer_Lib');
		//print_r($this->load->library('PHPMailer_Lib'));die();
		// PHPMailer object
		$mail = $this->PHPMailer_Lib->load();

		// SMTP configuration
		$mail->isSMTP();
		$mail->Host     = 'mail.primefooduk.com';
		$mail->SMTPAuth = true;
		$mail->Username = 'ascheema@primefooduk.com';
		$mail->Password = 'Prime@2021';
		$mail->SMTPSecure = 'ssl';
		$mail->Port     = 587;

		$mail->setFrom('ascheema@primefooduk.com', 'Prime Food');
		$mail->addReplyTo('ascheema@primefooduk.com', 'Prime Food');

		// Add a recipient
		$mail->addAddress('darab_khan123@yahoo.com');

		// Add cc or bcc 
		$mail->addCC('muhammad.ibrahim139@gmail.com');
		//$mail->addBCC('bcc@example.com');

		// Email subject
		$mail->Subject = 'Send Email via SMTP using PHPMailer in CodeIgniter';

		// Set email format to HTML
		$mail->isHTML(true);

		// Email body content
		$mailContent = "<h1>Send HTML Email using SMTP in CodeIgniter</h1>
		<p>This is a test email sending using SMTP mail server with PHPMailer.</p>";
		$mail->Body = $mailContent;

		// Send email
		if (!$mail->send()) {
			echo 'Message could not be sent.';
			echo 'Mailer Error: ' . $mail->ErrorInfo;
		} else {
			echo 'Message has been sent';
		}
	}
















	//edit invoice
	public function edit()
	{

		$tid = $this->input->get('id');
		$data['id'] = $tid;
		$data['title'] = "Purchase Order $tid";
		$data['customergrouplist'] = $this->customers->group_list();
		$data['terms'] = $this->purchase->billingterms();
		$data['invoice'] = $this->purchase->purchase_details($tid);
		$data['products'] = $this->purchase->purchase_products($tid);;
		$head['title'] = "Edit Invoice #$tid";
		$head['usernm'] = $this->aauth->get_user()->username;
		$data['warehouse'] = $this->purchase->warehouses();
		$data['currency'] = $this->purchase->currencies();
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
		if (!$this->aauth->permission_new(null, 'purchaseManageOrders')) {
			exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
		}
		// Fetch 'param' from the query string
		$param = $this->input->get('param');
		$head['title'] = "Manage Purchase Orders";
		$head['usernm'] = $this->aauth->get_user()->username;
		// Pass the 'param' to the view
		$data['param'] = $param;
		$this->load->view('fixed/header', $head);
		$this->load->view('purchase/invoices', $data);
		$this->load->view('fixed/footer');
	}
	public function verification()
	{
		if (!$this->aauth->permission_new(null, 'purchaseVerifyOrder')) {
			exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
		}
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
			$row[] = $this->aauth->permission_new(null, 'purchaseManageOrders') ? '<a href="' . base_url("purchase/view?id=$invoices->id") . '">&nbsp; ' . $invoices->tid . '</a>' : '<span>' . $invoices->tid . '</span>';
			$row[] = $invoices->name;
			$row[] = dateformat($invoices->invoicedate);
			// $row[] = amountExchange($invoices->total, 0, $this->aauth->get_user()->loc);
			// $row[] = '<span class="st-' . $invoices->status . '">' . $this->lang->line(ucwords($invoices->status)) . '</span>';

			$row[] = '<span class="badge badge-' . (($invoices->verified == "Verified") ? 'success' : 'light') . '">' . $invoices->verified . '</span>';

		if ($invoices->status == "due") {
			$row[] = '<span class="st-' . $invoices->status . '"> Payment Due </span>';
		} else {
			$row[] = '<span class="st-' . $invoices->status . '">' . $this->format_status_text($invoices->status) . '</span>';
		}

		$row[] = $this->aauth->permission_new(null, 'purchaseViewOrder') ? '<a href="' . base_url("purchase/verify?id=$invoices->id") . '" class="btn btn-success btn-xs"><i class="fa fa-eye"></i> View & Verify</a> ' : '';
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

	public function ajax_list()
	{
		$param = $this->input->get('param');
		$list = $this->purchase->get_datatables($param);
		$data = array();
		$no = $this->input->post('start');
		foreach ($list as $invoices) {
			$no++;
			$row = array();
			$row[] = $no;

			if ($invoices->status != "paid" && $invoices->verified != "Verified")
				$row[] = $this->aauth->permission_new(null, 'purchaseEditOrder') ? '<a href="' . base_url("purchase/edit?id=$invoices->id") . '">' . $invoices->tid . '</a>' : '<span>' . $invoices->tid . '</span>';
			else
				$row[] = $invoices->tid;

			$row[] = $invoices->invoice_ref;
			$row[] = $invoices->name;
			
			// Format Order Date - direct approach
			$order_date = '';
			// Access invoicedate property directly
			if (isset($invoices->invoicedate) && !empty($invoices->invoicedate)) {
				$date_value = trim($invoices->invoicedate);
				// Skip invalid MySQL dates
				if ($date_value && $date_value != '0000-00-00' && $date_value != '0000-00-00 00:00:00') {
					// Parse the date (format: YYYY-MM-DD or YYYY-MM-DD HH:MM:SS)
					$date_parts = explode(' ', $date_value);
					$date_only = $date_parts[0]; // Get just the date part
					
					// Try to format using dateformat function
					try {
						$formatted = dateformat($date_only);
						if (!empty($formatted)) {
							$order_date = $formatted;
						} else {
							// If dateformat returns empty, format manually
							$timestamp = strtotime($date_only);
							if ($timestamp !== false && $timestamp > 0) {
								$ci =& get_instance();
								$date_format = $ci->config->item('dformat');
								if (!$date_format) {
									$date_format = 'd-m-Y';
								}
								$order_date = date($date_format, $timestamp);
							}
						}
					} catch (Exception $e) {
						// If dateformat throws exception, format manually
						$timestamp = strtotime($date_only);
						if ($timestamp !== false && $timestamp > 0) {
							$ci =& get_instance();
							$date_format = $ci->config->item('dformat');
							if (!$date_format) {
								$date_format = 'd-m-Y';
							}
							$order_date = date($date_format, $timestamp);
						} else {
							// Show raw date as last resort
							$order_date = $date_only;
						}
					}
				}
			}
			$row[] = $order_date;
			// $row[] = amountExchange($invoices->total, 0, $this->aauth->get_user()->loc);


			$row[] = '<span class="badge badge-' . (($invoices->verified == "Verified") ? 'success' : 'light') . '">' . $invoices->verified . '</span>';

		if ($invoices->status == "due") {
			$row[] = '<span class="st-' . $invoices->status . '"> Payment Due </span>';
		} else {
			$row[] = '<span class="st-' . $invoices->status . '">' . $this->format_status_text($invoices->status) . '</span>';
		}


		$row[] =
				($this->aauth->permission_new(null, 'purchaseViewOrder') ? '<a href="' . base_url("purchase/view?id={$invoices->id}") . '" class="btn btn-success btn-xs" target="_blank" title="View"><i class="fa fa-eye"></i></a> ' : '') .
				($this->aauth->permission_new(null, 'purchaseExportOrder') ? '<a href="' . base_url("purchase/purchase_export?id={$invoices->id}") . '&d=1" class="btn btn-info btn-xs" target="_blank" title="Download"><i class="fa fa-download"></i></a> ' : '') .
				($this->aauth->permission_new(null, 'purchaseDeleteOrder') ? '<a href="#" data-object-id="' . $invoices->id . '" class="btn btn-danger btn-xs delete-object" target="_blank" title="Delete"><i class="fa fa-trash"></i></a>' : '');
			$data[] = $row;
		}

		$output = array(
			"draw" => $_POST['draw'],
			"recordsTotal" => $this->purchase->count_all(),
			"recordsFiltered" => $this->purchase->count_filtered($param),
			"data" => $data,
		);
		//output to json format
		// Ensure no output before JSON and set proper content type
		if (ob_get_level()) {
			ob_clean();
		}
		$this->output->set_content_type('application/json');
		echo json_encode($output);
	}

	public function view()
	{
		$this->load->model('accounts_model');
		$data['acclist'] = $this->accounts_model->accountslist((int)$this->aauth->get_user()->loc);
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
		// if ($data['invoice']['i_class'] == 1) {
		// 	$pref = prefix(7);
		// } else {
		// 	$pref = $this->config->item('prefix');
		// }

		//var_dump($data['products']); exit; 

		ob_end_clean();
		ob_start();
		ini_set('memory_limit', '64M');

		$data_tr = "";

		$left_logo = base_url("assets/images/prime_food_logo.jpg");
		$data['left_logo'] = $left_logo;
		$right_logo = base_url("assets/images/halal_logo.jpg");
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

	public function action()
	{
		include_once APPPATH . '/third_party/PHPMailer/vendor/autoload.php';
		// dd($this->input->post());
		$company = $this->settings->company_details(1);

		$product_price = $this->input->post('product_price');
		$product_qty = $this->input->post('product_qty');
		$product_tax = $this->input->post('product_tax');

		$total = 0;
		$tax = 0;

		foreach ($product_price as $key => $price) {
			if (!empty($price) && !empty($product_qty[$key])) {
				$total += floatval($price) * intval($product_qty[$key]);
				$tax += floatval($product_tax[$key]) * intval($product_qty[$key]);
			}
		}
		$amount = $tax + $total;
		$acid = 2;
		$this->db->select('holder');
		$this->db->from('geopos_accounts');
		$this->db->where('id', $acid);
		$query = $this->db->get();
		$account = $query->row_array();
		// var_dump('Total', $total, 'Tax', $tax);
		// die();	
		$currency = $this->input->post('mcurrency');
		$customer_id = $this->input->post('customer_id');
		$cst = $this->input->post('cst');
		$invocieno = $this->input->post('invocieno');
		$invoice_ref = $this->input->post('invoice_ref');
		$unique_invoice_ref = 'INV-' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
		$this->db->select_max('tid');
		$this->db->from('geopos_purchase');
		$query = $this->db->get();
		$pr = $query->row_array();
		$inv_no = $pr['tid'];
		$invocieno =  $inv_no + 1;
		$invoicedate = $this->input->post('invoicedate');
		$pstatus = $this->input->post('pstatus');
		$invocieduedate = $this->input->post('invocieduedate');
		$notes = $this->input->post('notes', true);
		$tax = $this->input->post('tax_handle');
		$subtotal = rev_amountExchange_s($this->input->post('subtotal'), $currency, $this->aauth->get_user()->loc);
		// var_dump($subtotal); die();
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
		$packing_list1 = $this->input->post('packing_type');
		$pid_list = $this->input->post('pid');

		log_message('debug', '=== PURCHASE ORDER CREATE START ===');
		log_message('debug', 'Customer ID: ' . $customer_id);
		log_message('debug', 'Products count: ' . count(array_filter($pid_list)));

		$this->db->trans_start();
		//products
		$transok = true;
		//Invoice Data
		$bill_date = datefordatabase($invoicedate);
		$bill_due_date = datefordatabase($invocieduedate);
		
		// Calculate totals from payload
		$product_name1_list = $this->input->post('product_name', true);
		$product_qty_list = $this->input->post('product_qty');
		$product_unit_list = $this->input->post('product_units');
		$product_price_list = $this->input->post('product_price');
		$product_tax_list = $this->input->post('product_tax');
		
		$calculated_subtotal = 0;
		$calculated_tax = 0;
		foreach ($product_price_list as $key => $price) {
			if (!empty($price) && !empty($product_qty_list[$key])) {
				$qty = intval($product_qty_list[$key]) * intval($product_unit_list[$key] ?: 1);
				$calculated_subtotal += floatval($price) * $qty;
				$calculated_tax += floatval($product_tax_list[$key]) * $qty;
			}
		}
		$calculated_total = $calculated_subtotal + $calculated_tax;
		
		$product_names_str = implode(", ", array_filter($product_name1_list));
		log_message('debug', 'Calculated Subtotal: ' . $calculated_subtotal);
		log_message('debug', 'Calculated Tax: ' . $calculated_tax);
		log_message('debug', 'Calculated Total: ' . $calculated_total);
		
		$this->aauth->applog("[Purchase Order ($invocieno) - Created] - Supplier: " . $customer_id . ", Products: $product_names_str", $this->aauth->get_user()->username);
		
		$data = array(
			'status' => $pstatus, 
			'tid' => $invocieno, 
			'invoice_ref' => $invoice_ref, 
			'inv_id' => $unique_invoice_ref,  
			'invoicedate' => $bill_date, 
			'invoiceduedate' => $bill_due_date, 
			'subtotal' => $calculated_subtotal, 
			'tax' => $calculated_tax,
			'shipping' => $shipping ?: 0, 
			'ship_tax' => $shipping_tax ?: 0, 
			'ship_tax_type' => $ship_taxtype, 
			'total' => $calculated_total, 
			'notes' => $notes, 
			'csd' => $customer_id, 
			'eid' => $this->aauth->get_user()->id, 
			'taxstatus' => $tax, 
			'discstatus' => $discstatus, 
			'format_discount' => $discountFormat, 
			'refer' => $refer, 
			'term' => $pterms ?: 1, 
			'loc' => $this->aauth->get_user()->loc, 
			'multi' => $currency ?: 0, 
			'verified' => 'Unverified'
		);

		// dd($data);
		log_message('debug', 'Attempting to insert into geopos_purchase...');
		log_message('debug', 'Purchase data: ' . json_encode($data));
		
		if ($this->db->insert('geopos_purchase', $data)) {
			$invocieno = $this->db->insert_id();
			log_message('debug', 'SUCCESS: Purchase inserted with ID: ' . $invocieno);
			log_message('debug', 'Last query: ' . $this->db->last_query());
			$pid_list = $this->input->post('pid');
			$pid = array();
			$product_id = array();
			$productlist = array();
			$prodindex = 0;
			$itc = 0;
			$flag = false;
			$product_id_list = $this->input->post('pid');
			// Already fetched above
			$product_name1 = array();
			$product_qty = array();
			$product_price = array();
			$packing_list1 = $this->input->post('packing_type');
			$packing = array();
			$product_discount_list = $this->input->post('product_discount');
			$product_discount = array();
			$product_subtotal_list = $this->input->post('product_subtotal');
			$product_subtotal = array();
			$product_tax = array();
			$ptotal_disc_list = $this->input->post('disca');
			$product_disc = array();
			$product_des_list = $this->input->post('product_description', true);
			$product_des = array();
			$product_unit = array();
			$product_hsn_list = $this->input->post('hsn');
			$product_hsn = array();
			$gross = 0;
			$i = 0;
			foreach ($pid_list as $key => $value) {
				if ($value != 0 && !empty($product_name1_list[$key])) {
					$pid[$i] = $value;
					$product_id[$i] = $value;
					$product_name1[$i] = $product_name1_list[$key];
					$product_qty[$i] = intval($product_qty_list[$key]) * intval($product_unit_list[$key] ?: 1);
					$product_price[$i] = $product_price_list[$key];
					$product_discount[$i] = $product_discount_list[$key] ?: 0;
					$product_subtotal[$i] = $product_subtotal_list[$key] ?: 0;
					$product_tax[$i] = $product_tax_list[$key] ?: 0;
					$product_disc[$i] = $ptotal_disc_list[$key] ?: 0;
					$product_des[$i] = $product_des_list[$key];
					$product_unit[$i] = $product_unit_list[$key] ?: 1;
					$product_hsn[$i] = $product_hsn_list[$key] ?: '';
					$packing[$i] = $packing_list1[$key] ?: 'pallet';
					$i++;
				}
			}
			$totalTax = 0;

			foreach ($pid as $key => $value) {
				$new_price = rev_amountExchange_s($product_price[$key], $currency, $this->aauth->get_user()->loc);
				$new_qty = numberClean($product_qty[$key]);

				$tax_rate = numberClean($product_tax[$key]);
				$dis_rate = numberClean($product_discount[$key]);

				$category = '';
				$old_price = 0;
				$old_qty = 0;

				if (isset($product_id[$key]) && $product_id[$key] > 0) {
					$this->db->select('product_price, qty, fproduct_price, pcat');
					$this->db->from('geopos_products');
					$this->db->where('pid', $product_id[$key]);
					$avg_query = $this->db->get();
					if ($avg_query && $avg_query->num_rows() > 0) {
						$avg_data_ret = $avg_query->row_array();
						$old_price = $avg_data_ret['fproduct_price'];
						$category = $avg_data_ret['pcat'];
						$old_purchase = $avg_data_ret['product_price'];
						$old_qty = $avg_data_ret['qty'];
					}
				}

				$unit_sub = ($new_price * $new_qty) + ($tax_rate * $new_qty);

				$data = array(
					'tid' => $invocieno,
					'pid' => $product_id[$key],
					'cat_id' => $category,
					'product' => $product_name1[$key],
					'code' => $product_hsn[$key],
					'qty' => $new_qty,
					'price' => $old_price,
					'purchase' => $new_price,
					'tax' => $tax_rate,
					'discount' => $dis_rate,
					'subtotal' => $unit_sub,
					'totaltax' => ($tax_rate * $new_qty),
					'totaldiscount' => ($dis_rate * $new_qty),
					'product_des' => $product_des[$key],
					'unit' => $product_unit[$key],
					'packing' => $packing[$key]
				);
				
				$total_discount += ($dis_rate * $new_qty);
				$total_tax += ($tax_rate * $new_qty);
				$gross += $unit_sub;
				$flag = true;
				$productlist[$prodindex] = $data;
				$i++;
				$prodindex++;
				
				if ($product_id[$key] > 0) {
					if ($this->input->post('update_stock') == 'yes') {
						$this->db->set('qty', "qty+$new_qty", FALSE);
						$this->db->where('pid', $product_id[$key]);
						$this->db->update('geopos_products');
					}
					$itc += $new_qty;
				}
			}
			
			if ($prodindex > 0) {
				log_message('debug', 'Products to insert: ' . $prodindex);
				log_message('debug', 'Product list: ' . json_encode($productlist));
				
				// Insert transaction ONCE for the entire purchase
				$data_t = array(
					'acid' => $acid,
					'account' => $account['holder'] ?: 'Purchase Account',
					'type' => 'Expense',
					'cat' => 'Purchase',
					'credit' => $calculated_total,
					'payer' => $cst ?: 'Supplier',
					'payerid' => $customer_id,
					'method' => '',
					'date' => $bill_date,
					'eid' => $this->aauth->get_user()->id,
					'tid' => $invocieno,
					'note' => 'Purchase Order #' . $invocieno,
					'ext' => 1,
					'loc' => $this->aauth->get_user()->loc,
					'trans_ref' => $unique_invoice_ref,
					'inv_id' => $invoice_ref
				);
				
				log_message('debug', 'Inserting transaction...');
				$trans_result = $this->db->insert('geopos_transactions', $data_t);
				log_message('debug', 'Transaction insert result: ' . ($trans_result ? 'SUCCESS' : 'FAILED'));
				if (!$trans_result) {
					log_message('error', 'Transaction insert error: ' . $this->db->error()['message']);
				}
				
				log_message('debug', 'Inserting ' . count($productlist) . ' products...');
				$items_result = $this->db->insert_batch('geopos_purchase_items', $productlist);
				log_message('debug', 'Items insert result: ' . ($items_result ? 'SUCCESS' : 'FAILED'));
				if (!$items_result) {
					log_message('error', 'Items insert error: ' . $this->db->error()['message']);
				}
				// print_r($this->db->last_query());    die();
				log_message('debug', 'Updating purchase totals...');
				log_message('debug', 'Total discount: ' . $total_discount . ', Total tax: ' . $total_tax . ', Gross: ' . $gross . ', Items: ' . $itc);
				
				$this->db->set(array('discount' => rev_amountExchange_s(amountFormat_general($total_discount), $currency, $this->aauth->get_user()->loc), 'tax' => rev_amountExchange_s(amountFormat_general($total_tax), $currency, $this->aauth->get_user()->loc), 'total' => $gross, 'items' => $itc));
				$this->db->where('id', $invocieno);
				$update_result = $this->db->update('geopos_purchase');
				log_message('debug', 'Purchase update result: ' . ($update_result ? 'SUCCESS' : 'FAILED'));
				if (!$update_result) {
					log_message('error', 'Purchase update error: ' . $this->db->error()['message']);
				}
			} else {
				echo json_encode(array('status' => 'Error', 'message' =>
				"Please choose product from product list. Go to Item manager section if you have not added the products."));
				$transok = false;
			}

			$this->db->select('*');
			$this->db->from('geopos_purchase_items');
			$this->db->where('tid', $invocieno);
			$query = $this->db->get();
			$products_info = $query->result_array();
			$tdata = "<br><table style='width:100%;text-align:left;border:1px solid black'><tr><th style='width:50%'>Product</th><th style='width:25%'>Packing</th><th style='width:25%'>Quantity</th></tr>";
			foreach ($products_info as $value) {
				$tdata .= "<tr>";
				$tdata .= "<td >" . $value['product_des'] . "</td>";
				$tdata .= "<td >" . $value['packing'] . "</td>";
				$tdata .= "<td >" . $value['qty'] . "</td>";
				$tdata .= "</tr>";
			}
			$tdata .= "</table>";

			$html = "<html>
			<head>
			<meta charset='utf-8'>
			<title>Purchase Order</title>
			</head>
			<body>
			<b>Please Find our order details </b> <br>";
			$html .= $tdata;
			$html .= "
			<br>
			Thanks & Regards
			<br>
			<b>" . $company['cname'] . "</b><br>
			" . $company['address'] . " " . $company['city'] . " " . $company['region'] . ", " . $company['postbox'] . ", " . $company['country'] . " <br>
			" . $company['phone'] . " <br>
			Mobile:&nbsp;&nbsp;" . $company['mobile'] . "
			<br>

			</body>
			</html>";


			$this->load->library('ultimatemailer');
			$this->db->select('host,port,auth,auth_type,username,password,sender');
			$this->db->from('geopos_smtp');
			$query = $this->db->get();
			$smtpresult = $query->row_array();
			// print_r($smtpresult);die();
			$host = $smtpresult['host'];
			$port = $smtpresult['port'];
			$auth = $smtpresult['auth'];
			$auth_type = $smtpresult['auth_type'];
			$username = $smtpresult['username'];;
			$password = $smtpresult['password'];
			$mailfrom = $smtpresult['sender'];
			$mailfromtilte = $company['cname'];

			$this->db->select('*');
			$this->db->from('geopos_supplier');
			$this->db->where('id', $customer_id);
			$query = $this->db->get();
			$supplierinfo = $query->row_array();
			$email1 = $supplierinfo['email'];
			$email2 = $supplierinfo['email2'];
			$email3 = $supplierinfo['email3'];
			// $email4=$supplierinfo['email4'];
			if ($email1 != null && $email1 != "") {

				$mail = new PHPMailer;
				$mail->isSMTP();
				$mail->Host         = $host;
				$mail->SMTPAuth     = $auth;
				$mail->Username     = $username;
				$mail->Password     = $password;
				$mail->SMTPSecure   = $auth_type;
				$mail->Port         = $port;
				// $mail->SMTPDebug    = 2;
				$mail->setFrom($mailfrom, $mailfromtilte);
				$mail->addAddress($email1, 'Recipient');
				$mail->addCC($mailfrom, 'CC Recipient');

				if ($email2 != null && $email2 != "") {

					$mail->addCC($email2, 'CC Recipient');
				}
				if ($email3 != null && $email3 != "") {

					$mail->addCC($email3, 'CC Recipient');
				}


				$mail->isHTML(true);
				$mail->Subject = 'Purchase Order';
				$mail->Body = $html;
				$mail->send();
			}



			log_message('debug', 'Main purchase insert and items processing complete');
			// Success - transaction will be completed below
		} else {
			log_message('error', 'FAILED: Could not insert into geopos_purchase');
			log_message('error', 'DB Error: ' . $this->db->error()['message']);
			log_message('error', 'Last query: ' . $this->db->last_query());
			echo json_encode(array('status' => 'Error', 'message' => $this->lang->line('ERROR') . ': ' . $this->db->error()['message']));
			$transok = false;
		}
		
		// Complete the database transaction
		log_message('debug', 'Completing transaction... transok=' . ($transok ? 'TRUE' : 'FALSE'));
		
		if ($transok) {
			$this->db->trans_complete();
			log_message('debug', 'Transaction completed');
			
			// Check if transaction was successful
			$trans_status = $this->db->trans_status();
			log_message('debug', 'Transaction status: ' . ($trans_status ? 'TRUE (SUCCESS)' : 'FALSE (FAILED)'));
			
			if ($trans_status === FALSE) {
				log_message('error', '=== TRANSACTION FAILED ===');
				log_message('error', 'DB Error: ' . json_encode($this->db->error()));
				log_message('error', 'Last query: ' . $this->db->last_query());
				echo json_encode(array('status' => 'Error', 'message' => 'Transaction failed. Data not saved. DB Error: ' . $this->db->error()['message']));
			} else {
				log_message('debug', '=== TRANSACTION SUCCESS === Purchase ID: ' . $invocieno);
				echo json_encode(array('status' => 'Success', 'pid' => $invocieno, 'controller' => 'purchase', 'message' => $this->lang->line('Purchase order success') . "<a href='purchase/view?id=$invocieno' class='btn btn-info btn-lg'><span class='fa fa-eye' aria-hidden='true'></span> " . $this->lang->line('View') . " </a>"));
			}
		} else {
			log_message('error', 'Rolling back transaction due to transok=FALSE');
			$this->db->trans_rollback();
		}
	}

	public function verify()
	{

		$tid = $this->input->get('id');
		$data['id'] = $tid;
		$data['title'] = "Purchase Order $tid";
		$data['customergrouplist'] = $this->customers->group_list();
		$data['terms'] = $this->purchase->billingterms();
		$data['invoice'] = $this->purchase->purchase_details($tid);
		$data['products'] = $this->purchase->purchase_products($tid);;
		$head['title'] = "Verify Invoice #$tid";
		$head['usernm'] = $this->aauth->get_user()->username;
		$data['warehouse'] = $this->purchase->warehouses();
		$data['currency'] = $this->purchase->currencies();
		$data['exchange'] = $this->plugins->universal_api(5);
		$this->load->library("Common");
		$data['taxlist'] = $this->common->taxlist_edit($data['invoice']['taxstatus']);
		$this->load->view('fixed/header', $head);
		$this->load->view('purchase/verify', $data);
		$this->load->view('fixed/footer');
	}

	public function editaction2()
	{
		$company = $this->settings->company_details(1);

		$invoicedate = $this->input->post('invoicedate');
		$invocieduedate = $this->input->post('invocieduedate');
		$bill_date = datefordatabase($invoicedate);
		$bill_due_date = datefordatabase($invocieduedate);
		$currency = $this->input->post('mcurrency');
		$customer_id = $this->input->post('customer_id');
		$invocieno = $this->input->post('iid');
		$pstatus = $this->input->post('pstatus');
		$invoice_ref = $this->input->post('invoice_ref');

		// Check if the purchase order is already verified
		$sql = "SELECT status,tid,verified FROM geopos_purchase WHERE id=$invocieno";
		$query = $this->db->query($sql);
		$response = $query->result_array();
		$inv_status_check = $response[0]['verified'];
		$ptid = $response[0]['tid'];

		if ($inv_status_check == "Verified") {
			echo json_encode(array('status' => 'Error', 'message' => "Cannot edit. This Purchase Order is already verified!"));
			exit();
		}

		$this->aauth->applog("[Purchase Order - $ptid] Status changed to $pstatus ", $this->aauth->get_user()->username);

		// Start transaction
		$this->db->trans_start();
		
		// Delete existing purchase items
		$this->db->delete('geopos_purchase_items', array('tid' => $invocieno));

		// Calculate totals from payload
		$product_name1_list = $this->input->post('product_name', true);
		$product_qty_list = $this->input->post('product_qty');
		$product_unit_list = $this->input->post('product_units');
		$product_price_list = $this->input->post('product_price');
		$product_tax_list = $this->input->post('product_tax');
		
		$calculated_subtotal = 0;
		$calculated_tax = 0;
		foreach ($product_price_list as $key => $price) {
			if (!empty($price) && !empty($product_qty_list[$key])) {
				$qty = intval($product_qty_list[$key]) * intval($product_unit_list[$key] ?: 1);
				$calculated_subtotal += floatval($price) * $qty;
				$calculated_tax += floatval($product_tax_list[$key]) * $qty;
			}
		}
		$calculated_total = $calculated_subtotal + $calculated_tax;

		$data = array(
			'status' => $pstatus, 
			'invoice_ref' => $invoice_ref,  
			'invoicedate' => $bill_date, 
			'invoiceduedate' => $bill_due_date,
			'subtotal' => $calculated_subtotal, 
			'tax' => $calculated_tax,
			'total' => $calculated_total
		);
		$this->db->set($data);
		$this->db->where('id', $invocieno);

		if ($this->db->update('geopos_purchase')) {
			$pid_list = $this->input->post('pid');
			$pid = array();
			$product_id = array();
			$productlist = array();
			$prodindex = 0;
			$itc = 0;
			$flag = false;
			$product_id_list = $this->input->post('pid');
			// Already fetched above
			$product_name1 = array();
			$product_qty = array();
			$product_price = array();
			$packing_list1 = $this->input->post('packing_type');
			$packing = array();
			$product_discount_list = $this->input->post('product_discount');
			$product_discount = array();
			$product_tax = array();
			$ptotal_disc_list = $this->input->post('disca');
			$product_disc = array();
			$product_des_list = $this->input->post('product_description', true);
			$product_des = array();
			$product_unit = array();
			$product_hsn_list = $this->input->post('hsn');
			$product_hsn = array();
			$gross = 0;
			$i = 0;
			foreach ($pid_list as $key => $value) {
				if ($value != 0 && !empty($product_name1_list[$key])) {
					$pid[$i] = $value;
					$product_id[$i] = $value;
					$product_name1[$i] = $product_name1_list[$key];
					$product_qty[$i] = intval($product_qty_list[$key]) * intval($product_unit_list[$key] ?: 1);
					$product_price[$i] = $product_price_list[$key];
					$product_discount[$i] = $product_discount_list[$key] ?: 0;
					$product_tax[$i] = $product_tax_list[$key] ?: 0;
					$product_disc[$i] = $ptotal_disc_list[$key] ?: 0;
					$product_des[$i] = $product_des_list[$key];
					$product_unit[$i] = $product_unit_list[$key] ?: 1;
					$product_hsn[$i] = $product_hsn_list[$key] ?: '';
					$packing[$i] = $packing_list1[$key] ?: 'pallet';
					$i++;
				}
			}
			$total_tax = 0;
			$total_discount = 0;

			foreach ($pid as $key => $value) {
				$new_price = rev_amountExchange_s($product_price[$key], $currency, $this->aauth->get_user()->loc);
				$new_qty = numberClean($product_qty[$key]);

				$tax_rate = numberClean($product_tax[$key]);
				$dis_rate = numberClean($product_discount[$key]);

				$category = '';
				$old_price = 0;
				$old_qty = 0;

				if (isset($product_id[$key]) && $product_id[$key] > 0) {
					$this->db->select('product_price, qty, fproduct_price, pcat');
					$this->db->from('geopos_products');
					$this->db->where('pid', $product_id[$key]);
					$avg_query = $this->db->get();
					if ($avg_query && $avg_query->num_rows() > 0) {
						$avg_data_ret = $avg_query->row_array();
						$old_price = $avg_data_ret['fproduct_price'];
						$category = $avg_data_ret['pcat'];
						$old_qty = $avg_data_ret['qty'];
					}
				}

				$unit_sub = ($new_price * $new_qty) + ($tax_rate * $new_qty);

				$data = array(
					'tid' => $invocieno,
					'pid' => $product_id[$key],
					'cat_id' => $category,
					'product' => $product_name1[$key],
					'code' => $product_hsn[$key],
					'qty' => $new_qty,
					'price' => $old_price,
					'purchase' => $new_price,
					'tax' => $tax_rate,
					'discount' => $dis_rate,
					'subtotal' => $unit_sub,
					'totaltax' => ($tax_rate * $new_qty),
					'totaldiscount' => ($dis_rate * $new_qty),
					'product_des' => $product_des[$key],
					'unit' => $product_unit[$key],
					'packing' => $packing[$key]
				);

				$total_discount += ($dis_rate * $new_qty);
				$total_tax += ($tax_rate * $new_qty);
				$gross += $unit_sub;
				$flag = true;
				$productlist[$prodindex] = $data;
				$i++;
				$prodindex++;
				
				if ($product_id[$key] > 0) {
					if ($this->input->post('update_stock') == 'yes') {
						$this->db->set('qty', "qty+$new_qty", FALSE);
						$this->db->where('pid', $product_id[$key]);
						$this->db->update('geopos_products');
					}
					$itc += $new_qty;
				}
			}
			if ($prodindex > 0) {
				$this->db->insert_batch('geopos_purchase_items', $productlist);
				// print_r($this->db->last_query());    die();
				$this->db->set(array('discount' => rev_amountExchange_s(amountFormat_general($total_discount), $currency, $this->aauth->get_user()->loc), 'tax' => rev_amountExchange_s(amountFormat_general($total_tax), $currency, $this->aauth->get_user()->loc), 'total' => $gross, 'items' => $itc));
				$this->db->where('id', $invocieno);
				$this->db->update('geopos_purchase');
			} else {
				echo json_encode(array('status' => 'Error', 'message' =>
				"Please choose product from product list. Go to Item manager section if you have not added the products."));
				$transok = false;
			}

			$this->db->select('*');
			$this->db->from('geopos_purchase_items');
			$this->db->where('tid', $invocieno);
			$query = $this->db->get();
			$products_info = $query->result_array();
			$tdata = "<br><table style='width:100%;text-align:left'><tr><th style='width:50%'>Product</th><th style='width:25%'>Packing</th><th style='width:25%'>Quantity</th></tr>";
			foreach ($products_info as $value) {
				$tdata .= "<tr>";
				$tdata .= "<td>" . $value['product_des'] . "</td>";
				$tdata .= "<td>" . $value['packing'] . "</td>";
				$tdata .= "<td>" . $value['qty'] . "</td>";
				$tdata .= "</tr>";
			}
			$tdata .= "</table>";

			$html = "<html>
			<head>
			<meta charset='utf-8'>
			<title>Purchase Order</title>
			</head>
			<body>
			Please Find our order details <br>";
			$html .= $tdata;
			$html .= "
			<br>
			Thanks & Regards
			<br>
			<b>" . $company['cname'] . "</b><br>
			" . $company['address'] . " " . $company['city'] . " " . $company['region'] . ", " . $company['postbox'] . ", " . $company['country'] . " <br>
			" . $company['phone'] . " <br>
			Mobile:&nbsp;&nbsp;" . $company['mobile'] . "

			</body>
			</html>";


			$this->load->library('ultimatemailer');
			$this->db->select('host,port,auth,auth_type,username,password,sender');
			$this->db->from('geopos_smtp');
			$query = $this->db->get();
			$smtpresult = $query->row_array();
			//print_r($smtpresult);die();
			$host = $smtpresult['host'];
			$port = $smtpresult['port'];
			$auth = $smtpresult['auth'];
			$auth_type = $smtpresult['auth_type'];
			$username = $smtpresult['username'];;
			$password = $smtpresult['password'];
			$mailfrom = $smtpresult['sender'];
			$mailfromtilte = $company['cname'];

			$this->db->select('*');
			$this->db->from('geopos_supplier');
			$this->db->where('id', $customer_id);
			$query = $this->db->get();
			$supplierinfo = $query->row_array();
			$email1 = $supplierinfo['email'];
			$email2 = $supplierinfo['email2'];
			$email3 = $supplierinfo['email3'];
			// $email4=$supplierinfo['email4'];
			if ($email1 != null && $email1 != "") {
				$this->ultimatemailer->load($host, $port, $auth, $auth_type, $username, $password, $mailfrom, $mailfromtilte, $email1, $supplierinfo['name'], "Purchase Order", $html, "false", "false");
			}
			if ($email2 != null && $email2 != "") {
				$this->ultimatemailer->load($host, $port, $auth, $auth_type, $username, $password, $mailfrom, $mailfromtilte, $email2, $supplierinfo['name'], "Purchase Order", $html, "false", "false");
			}
			if ($email3 != null && $email3 != "") {
				$this->ultimatemailer->load($host, $port, $auth, $auth_type, $username, $password, $mailfrom, $mailfromtilte, $email3, $supplierinfo['name'], "Purchase Order", $html, "false", "false");
			}

			// Success - transaction will be completed below
		}
		
		// Complete the database transaction
		$this->db->trans_complete();
		
		// Check if transaction was successful
		if ($this->db->trans_status() === FALSE) {
			echo json_encode(array('status' => 'Error', 'message' => 'Transaction failed. Data not saved. Please try again.'));
		} else {
			echo json_encode(array('status' => 'Success', 'pid' => $invocieno, 'controller' => 'purchase', 'message' => $this->lang->line('Purchase order success') . "<a href='purchase/view?id=$invocieno' class='btn btn-info btn-lg'><span class='fa fa-eye' aria-hidden='true'></span> " . $this->lang->line('View') . " </a>"));
		}
	}
	public function editaction()
	{
		$invoicedate = $this->input->post('invoicedate');
		$invocieduedate = $this->input->post('invocieduedate');
		$bill_date = datefordatabase($invoicedate);
		$bill_due_date = datefordatabase($invocieduedate);
		$currency = $this->input->post('mcurrency');
		$customer_id = $this->input->post('customer_id');
		$invocieno = $this->input->post('iid');
		$pstatus = $this->input->post('pstatus');
		$sql = "select status,tid,verified from geopos_purchase where id=$invocieno";
		$query = $this->db->query($sql);
		$response = $query->result_array();
		$inv_status_check = $response[0]['verified'];
		$ptid = $response[0]['tid'];
		if ($inv_status_check == "Verified") {
			echo json_encode(array('status' => 'Error', 'message' =>
			"Can not Edit, This Purchase Order Is Already Verified!"));
			exit();
		}
		if ($this->input->post('update_stock') != 'yes') {
			$this->aauth->applog("[Purcahse Order - $ptid] Status changed to $pstatus ", $this->aauth->get_user()->username);
			$data = array('status' => $pstatus, 'invoicedate' => $bill_date, 'invoiceduedate' => $bill_due_date);
			$this->db->set($data);
			$this->db->where('id', $invocieno);
			if ($this->db->update('geopos_purchase', $data)) {
				$this->db->insert_batch('geopos_purchase_items', $productlist);
				echo json_encode(array('status' => 'Success', 'pid' => $invocieno, 'controller' => 'PKDUR1', 'message' =>
				"Purchase order has  been updated successfully! <a href='view?id=$invocieno' class='btn btn-info btn-lg'><span class='fa fa-eye' aria-hidden='true'></span> View </a> "));
				exit();
			}
		}

		$listofmissingp2b;
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
		$pid = array();
		$product_id = array();
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
		$sales_price_list = $this->input->post('sales_price');
		$sales_price = array();
		$purchase_price_list = $this->input->post('purchase_price');
		$purchase_price = array();
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
		$i = 0;
		$gross = 0;
		foreach ($pid_list as $key => $value) {

			if ($value != 0) {
				// Fetch product_name from geopos_products table
				$this->db->select('product_name');
				$this->db->from('geopos_products');
				$this->db->where('pid', $value);
				$query = $this->db->get();
				$product = $query->row();

				// Store product_name in pname array
				$pname[$i] = $product->product_name;
				$pid[$i] = $value;
				$product_id[$i] = $value;
				$packing[$i] = $packing_list[$key];
				$product_des[$i] = $product_description[$key];
				$product_name1[$i] = $product_name1_list[$key];
				$product_qty[$i] = $product_qty_list[$key];
				$product_price[$i] = $product_price_list[$key];
				$product_tax[$i] = $product_tax_list[$key];
				$product_discount[$i] = $product_discount_list[$key];
				$product_subtotal[$i] = $product_subtotal_list[$key];
				$sales_price[$i] = $sales_price_list[$key];
				$purchase_price[$i] = $purchase_price_list[$key];
				$product_tax1[$i] = $ptotal_tax1_list[$key];
				$ptotal_disc[$i] = $ptotal_disc_list[$key];
				$product_description[$i] = $product_description_list[$key];
				$product_unit[$i] = $product_unit_list[$key];
				$product_hsn[$i] = $product_hsn_list[$key];
				$packing[$i] = $packing_list[$key];
				$i++;
			}
		}



		foreach ($pid as $key => $value) {
			$total_discount += numberClean(@$ptotal_disc[$key]);
			$total_tax += ($product_tax[$key] * $product_qty[$key]);
			$unit_sub = ($purchase_price[$key] + numberClean($product_tax[$key])) * $product_qty[$key];
			$gross += $unit_sub;

			$data = array(
				'tid' => $invocieno,
				'pid' => $product_id[$key],
				'product' => $product_name1[$key],
				'code' => $product_hsn[$key],
				'qty' => numberClean($product_qty[$key]),
				'price' => $sales_price[$key],
				'purchase' => $purchase_price[$key],
				'tax' => numberClean($product_tax[$key]),
				'discount' => numberClean($product_discount[$key]),
				'subtotal' => $unit_sub,
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
				//$p_to_b_pid=$product_id[$key];
				$sql = "select p_to_b,product_name, qty from geopos_products where pid=$product_id[$key]";
				$query = $this->db->query($sql);
				$response = $query->result_array();
				$p_to_b = $response[0]['p_to_b'];
				$qty_ = $response[0]['qty'];
				$p_to_b_name = $response[0]['product_name'];
				// if($p_to_b!="" && $p_to_b!=null && $p_to_b!=0 && $packing[$key]=="pallet" ){
				//     $amt=$p_to_b*$amt;
				// }

				if ($packing[$key] == "pallet") {
					if ($p_to_b == "" || $p_to_b == null || $p_to_b == 0) {
						$listofmissingp2b .= $p_to_b_name . ",";
					}
				}


				//$amt = numberClean(@$product_qty[$key]) - numberClean(@$old_product_qty[$key]);
				$this->db->set('qty', "qty+$amt", FALSE);
				$this->db->set('product_price', $purchase_price[$key]);
				$this->db->set('fproduct_price', $sales_price[$key]);
				$this->db->where('pid', $product_id[$key]);
				$this->db->update('geopos_products');


				$histroy_data = array(
					"pid" =>  $product_id[$key],
					'product_name' => $pname[$key],
					// 'product_name' => $product_name1[$key],
					'product_price' => $purchase_price[$key],
					'fproduct_price' => $sales_price[$key],
					'qty' => $qty_ + $amt,
					'old_qty' => $qty_,
					'added_by' => $this->aauth->get_user()->username,
				);
				$this->db->insert('products_history', $histroy_data);
			}
			$flag = true;
		}

		$Verify_status = "Verified";

		$total_discount = rev_amountExchange_s(amountFormat_general($total_discount), $currency, $this->aauth->get_user()->loc);
		$total_tax = rev_amountExchange_s(amountFormat_general($total_tax), $currency, $this->aauth->get_user()->loc);
		if ($this->input->post('update_stock') == 'yes') {
			$this->aauth->applog("[Purcahse Order - $ptid] Verified ", $this->aauth->get_user()->username);
			$Verify_status = "Verified";
			$redirect_check = "PKDUR";
		} else {
			$Verify_status = "Unverified";
			$redirect_check = "PKDUR1";
		}
		// ,'status' =>$Verify_status
		$data = array('verified' => $Verify_status, 'invoicedate' => $bill_date, 'invoiceduedate' => $bill_due_date, 'subtotal' => $subtotal, 'shipping' => $shipping, 'ship_tax' => $shipping_tax, 'ship_tax_type' => $ship_taxtype, 'discount' => $total_discount, 'tax' => $total_tax, 'total' => $gross, 'notes' => $notes, 'csd' => $customer_id, 'items' => $itc, 'taxstatus' => $tax, 'discstatus' => $discstatus, 'format_discount' => $discountFormat, 'refer' => $refer, 'term' => $pterms, 'multi' => $currency);
		$this->db->set($data);
		$this->db->where('id', $invocieno);

		if ($flag) {

			if ($this->db->update('geopos_purchase', $data)) {
				$this->db->insert_batch('geopos_purchase_items', $productlist);
				$this->session->set_flashdata('message', $listofmissingp2b);
				echo json_encode(array('status' => 'Success', 'pid' => $invocieno, 'controller' => $redirect_check, 'message' =>
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

	public function purchase_export()
	{
		// print_r($data['invoice']);die();

		$this->load->model('accounts_model');
		$data['acclist'] = $this->accounts_model->accountslist((int)$this->aauth->get_user()->loc);
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

		$data_tr = "";
		foreach ($data['products'] as $key => $row) {

			$data_tr .= '
			<tr>
			<td>' . (int)$row["qty"] . '</td>
			<td>' . $row["code"] . '</td>
			<td>' . $row["product_des"] . '</td>
			<td>' . $row["purchase"] . '</td>
			<td>' . $row["tax"] . '</td>
			<td>' . $row["purchase"] * (int)$row["qty"] . '</td>
			</tr>
			';
		}

		// $total=0;
		// foreach ($data as $key => $row) {

		//    $total+=$row["total"];

		//     }


		$invoice_date = $data['invoice']['invoicedate'];
		$invoice_ref = $data['invoice']['invoice_ref'];
		$invoice_due_date = $data['invoice']['invoiceduedate'];
		$company_name = $data['invoice']['name'];
		$company_cname = $data['invoice']['company'];
		$company_address = $data['invoice']['address'];
		$company_city = $data['invoice']['city'];
		$company_region = $data['invoice']['region'];
		$company_country = $data['invoice']['country'];
		$company_phone = $data['invoice']['phone'];
		$company_email = $data['invoice']['email'];
		$status = $data['invoice']['status'];
		$pmethod = $data['invoice']['pmethod'];
		$time = date("h:i:s");
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
		<u><b>" . $company['cname'] . "</b>  </u>
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
		<div style='float: left;'>
		Bill from
		</div>
		<div style='float: right;'>
		Invoice Ref # $invoice_ref<br>
		PO Status: $status<br>
		Payment Method: $pmethod
		</div>
		<div style='clear: both;'></div>
		$company_cname
		<br>
		$company_address
		<br>
		$company_city $company_region $company_country
		<br>
		$company_phone
		<br>
		$company_email
		</div>
		<br>
		<hr>
		<table>
		<thead>
		<tr>
		<th>
		Quantity
		</th>
		<th>
		Product Code
		</th>
		<th>
		Description
		</th>
		<th>
		Unit Price
		</th>
		<th>
		Unit VAT
		</th>
		<th>
		Amount
		</th>
		
		</tr>
		</thead>
		$data_tr
		</table>
		<hr>
		<br>
		<br>
		<br>
		<table style='float: left; width: 40%;'>
			<tr>
				<td><strong>Total Paid</strong></td>
				<td>" . amountFormat_general($data['invoice']['pamnt']) . "</td>
			</tr>
			<tr>
				<td><strong>Balance Due</strong></td>
				<td>" . amountFormat_general($data['invoice']['total'] - $data['invoice']['pamnt']) . "</td>
			</tr>
		</table>

		<table style='float: right; width: 40%;'>
			<tr>
				<td><strong>Net Total</strong></td>
				<td>" . amountFormat_general($data['invoice']['total'] - $data['invoice']['tax']) . "</td>
			</tr>
			<tr>
				<td><strong>Total VAT</strong></td>
				<td>" . amountFormat_general($data['invoice']['tax']) . "</td>
			</tr>
			<tr>
				<td><strong>Grand Total</strong></td>
				<td>" . amountFormat_general($data['invoice']['total']) . "</td>
			</tr>
		</table>
		</body>
		</html>
		";

		$this->dpdf->loadHtml($html);
		$this->dpdf->render();
		$this->dpdf->stream("" . "Purchase Order" . ".pdf", array("Attachment" => 0));
	}
	// public function invoice_export()
	// {


	// 	$company = $this->settings->company_details(1);
	// 	// print_r($data['invoice']);die();

	// 	$this->load->model('accounts_model');
	// 	$data['acclist'] = $this->accounts_model->accountslist((int)$this->aauth->get_user()->loc);
	// 	$tid = intval($this->input->get('id'));
	// 	$data['id'] = $tid;
	// 	$head['title'] = "Purchase $tid";
	// 	$data['invoice'] = $this->purchase->purchase_details($tid);
	// 	$data['products'] = $this->purchase->purchase_products($tid);
	// 	$data['activity'] = $this->purchase->purchase_transactions($tid);
	// 	$data['attach'] = $this->purchase->attach($tid);
	// 	$data['employee'] = $this->purchase->employee($data['invoice']['eid']);
	// 	$head['usernm'] = $this->aauth->get_user()->username;
	// 	ob_end_clean();

	// 	$data_tr = "";
	// 	foreach ($data['products'] as $key => $row) {

	// 		$data_tr .= '
	// 		<tr>
	// 		<td>' . $row["product"] . '</td>
	// 		<td>' . $row["product_des"] . '</td>
	// 		<td>' . $row["qty"] / $row["unit"] . ' ' . $row["packing"] . 's</td>
	// 		<td>' . $row["unit"] . '/' . $row["packing"] . '</td>
	// 		<td>' . (int)$row["qty"] . '</td>
	// 		<td>' . $row["purchase"] * (int)$row["qty"] . '</td>
	// 		<td>' . $row["tax"] * (int)$row["qty"] . '</td>
	// 		</tr>
	// 		';
	// 	}

	// 	// $total=0;
	// 	// foreach ($data as $key => $row) {

	// 	//    $total+=$row["total"];

	// 	//     }


	// 	$invoice_date = $data['invoice']['invoicedate'];
	// 	$invoice_due_date = $data['invoice']['invoiceduedate'];
	// 	$company_name = $data['invoice']['name'];
	// 	$company_cname = $data['invoice']['company'];
	// 	$company_address = $data['invoice']['address'];
	// 	$company_city = $data['invoice']['city'];
	// 	$company_region = $data['invoice']['region'];
	// 	$company_country = $data['invoice']['country'];
	// 	$company_phone = $data['invoice']['phone'];
	// 	$company_email = $data['invoice']['email'];
	// 	$time = date("h:i:s");
	// 	$html = "
	// 	<html>
	// 	<head>
	// 	<style>
	// 	@page { margin: 120px 50px; }
	//     #header{ position: fixed;  top: -90px;}
	// 	.left_header{float:left;}
	// 	.center_header{margin-left:120px;width:400px}
	// 	.right_header{position: fixed;  top: -90px;left:610px;}
	// 	.pagenum:after {content: counter(page)}
	// 	table{width:100%;}
	// 	</style>
	// 	</head>
	// 	<body>
	// 	<div id='header'>
	// 	<div class='left_header'>
	// 	<div>Order Date: $invoice_date</div>
	// 	<div>Due Date: $invoice_due_date</div>
	// 	</div>
	// 	<div class='center_header'  style='font-size:18px;text-align:center;'>
	// 	<div>
	// 	<u><b>" . $company['cname'] . "</b>  </u>
	// 	</div>
	// 	<div>
	// 	<b> Purchase Order </b> 
	// 	</div>
	// 	</div >
	// 	<div class='right_header'>
	// 	<span class='pagenum'>Page:</span>
	// 	</div>
	// 	</div>
	// 	<div>
	// 	Bill from<br><br>
	//     $company_cname
	//     <br>
	// 	$company_name
	// 	<br>
	// 	$company_address
	// 	<br>
	// 	$company_city $company_region $company_country
	// 	<br>
	// 	$company_phone
	// 	<br>
	// 	$company_email
	// 	</div>
	// 	<br>
	// 	<hr>
	// 	<table>
	// 	<thead>
	// 	<tr>
	// 	<th>
	// 	Product
	// 	</th>
	// 	<th>
	// 	Description
	// 	</th>
	// 	<th>
	// 	Packs
	// 	</th>
	// 	<th>
	// 	Units
	// 	</th>
	// 	<th>
	// 	Quantity
	// 	</th>
	// 	<th>
	// 	Price
	// 	</th>
	// 	<th>
	// 	VAT
	// 	</th>

	// 	</tr>
	// 	</thead>
	// 	$data_tr
	// 	</table>
	// 	<hr>
	// 	<br>
	// 	<br>
	// 	<br>
	// 	<table style='float: right; width: 40%'>
	// 	<tr>
	// 		<td>
	// 			<strong>Total</strong>
	// 		</td>
	// 		<td>" . amountFormat_general($data['invoice']['total'] - $data['invoice']['tax']) . "</td>
	// 	</tr>
	// 	<tr>
	// 		<td>
	// 			<strong>Total VAT</strong>
	// 		</td>
	// 		<td>" . amountFormat_general($data['invoice']['tax']) . "</td>
	// 	</tr>
	// 	<tr>
	// 		<td>
	// 			<strong>Grand Total</strong>
	// 		</td>
	// 		<td>" . amountFormat_general($data['invoice']['total']) . "</td>
	// 	</tr>
	// 	</table>
	// 	</body>
	// 	</html>
	// 	";

	// 	$this->dpdf->loadHtml($html);
	// 	$this->dpdf->render();
	// 	$this->dpdf->stream("" . "Purchase Order" . ".pdf", array("Attachment" => 0));
	// }

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
				'accept_file_types' => '/\.(gif|jpe?g|png|docx|docs|txt|pdf|xls)$/i',
				'upload_dir' => FCPATH . 'userfiles/attach/',
				'upload_url' => base_url() . 'userfiles/attach/'
			));
			$files = (string)$this->uploadhandler_generic->filenaam();
			if ($files != '') {

				$this->purchase->meta_insert($id, 4, $files);
			}
		}
	}
}

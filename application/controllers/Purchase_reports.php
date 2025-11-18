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



class Purchase_reports extends CI_Controller

{

	public function __construct()

	{

		parent::__construct();

		$this->load->model('purchase_model', 'purchase');

		$this->load->model('settings_model', 'settings');

		$this->load->model('settings_model', 'settings');

		$this->load->library("Aauth");

		if (!$this->aauth->is_loggedin()) {

			redirect('/user/', 'refresh');

		}



		// Check if user has purchase reports access permission
		if (!$this->aauth->permission_new(null, 'purchaseReportsAccess')) {
			exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
		}

		$this->li_a = 'purhcase_reports';

		//exit('Under Dev Mode');

		$this->load->library('dpdf');

	}



    public function extended()

	{
		// Check specific permission for purchase by category report
		if (!$this->aauth->permission_new(null, 'purchaseReportsByCategory')) {
			exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
		}

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



    public function purchase_by_supplier()

	{
		// Check specific permission for purchase by supplier report
		if (!$this->aauth->permission_new(null, 'purchaseReportsBySupplier')) {
			exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
		}

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

		$this->li_a = "purhcase_reports";

		$head['usernm'] = $this->aauth->get_user()->username;

		$head['title'] = 'Supplier';

		$this->load->view('fixed/header', $head);

		$this->load->view('purchase/purchase_by_supplier');

		$this->load->view('fixed/footer');

	}



    public function supplier_payment()

	{
		// Check specific permission for supplier payment report
		if (!$this->aauth->permission_new(null, 'purchaseReportsSupplierPayment')) {
			exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
		}

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

		$this->li_a = "purhcase_reports";

		$head['usernm'] = $this->aauth->get_user()->username;

		$head['title'] = 'Supplier';

		$this->load->view('fixed/header', $head);

		$this->load->view('purchase/supplier_payment_report');

		$this->load->view('fixed/footer');

	}



    public function vat_purchase()

	{
		// Check specific permission for VAT purchase report
		if (!$this->aauth->permission_new(null, 'purchaseReportsVatCollection')) {
			exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
		}

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



    public function payment_date()

	{
		// Check specific permission for transactions by supplier report
		if (!$this->aauth->permission_new(null, 'purchaseReportsTransactionsBySupplier')) {
			exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
		}

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

		$this->li_a = "purhcase_reports";

		$head['usernm'] = $this->aauth->get_user()->username;

		$head['title'] = 'Supplier';

		$this->load->view('fixed/header', $head);

		$this->load->view('purchase/payment_date_report');

		$this->load->view('fixed/footer');

	}



}
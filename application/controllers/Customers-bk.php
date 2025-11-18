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
        $this->load->model('customers_model', 'customers');
        $this->load->library("Aauth");
        if (!$this->aauth->is_loggedin()) {
            redirect('/user/', 'refresh');
        }
        /**if (!$this->aauth->premission(3)) {

            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');

        } */
        $this->load->library("Custom");
        $this->load->library("dpdf");
        $this->li_a = 'crm';
    }

    public function index()
    {
        $head['usernm'] = $this->aauth->get_user()->username;
        $head['title'] = 'Customers';
        $this->load->view('fixed/header', $head);
        $this->load->view('customers/clist');
        $this->load->view('fixed/footer');
    }

    public function create()
    {
        $this->load->library("Common");
        $data['langs'] = $this->common->languages();
        $data['customergrouplist'] = $this->customers->group_list();
        $head['usernm'] = $this->aauth->get_user()->username;
        $data['custom_fields'] = $this->custom->add_fields(1);
        $head['title'] = 'Create Customer';
        $this->load->view('fixed/header', $head);
        $this->load->view('customers/create', $data);
        $this->load->view('fixed/footer');
    }

    public function view()
    {
        if (!$this->aauth->premission(8)) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $custid = $this->input->get('id');
        $data['details'] = $this->customers->details($custid);
        $data['customergroup'] = $this->customers->group_info($data['details']['gid']);
        $data['money'] = $this->customers->money_details($custid);
        $data['due'] = $this->customers->due_details($custid);
        $head['usernm'] = $this->aauth->get_user()->username;
        $data['activity'] = $this->customers->activity($custid);
        $data['custom_fields'] = $this->custom->view_fields_data($custid, 1);
        $head['title'] = 'View Customer';
        $this->load->view('fixed/header', $head);
        if ($data['details']['id']) $this->load->view('customers/view', $data);
        $this->load->view('fixed/footer');
    }
	
	public function list_price(){
		$head['usernm'] = $this->aauth->get_user()->username;
        $head['title'] = 'Customers';
        $this->load->view('fixed/header', $head);
        $this->load->view('customers/cplist');
        $this->load->view('fixed/footer');
	}
	
	 public function loadprice_list()
    {
        $no = $this->input->post('start');
		
		$cid = $this->input->post('cid');
		
		if($cid == 0){
			 $list = $this->customers->get_price_datatables();
		}else{
			 $list = $this->customers->get_cprice_datatables($cid);
		}
       
        $data = array();
            foreach ($list as $customers) {
                $no++;
                $row = array();
                $row[] = $no;
                //$row[] = $customers->account;
                $row[] = $customers->productcode;
				$row[] = $customers->description;
                $row[] = amountExchange($customers->salesprice);
                $data[] = $row;
            }
        $output = array(
            "draw" => $this->input->post('draw'),
            "recordsTotal" => $this->customers->count_all(),
            //"recordsFiltered" => $this->customers->count_filtered(),
            "data" => $data,
        );
        //output to json format
        echo json_encode($output);
    }


    public function load_list()
    {
        $no = $this->input->post('start');

        $list = $this->customers->get_datatables();
        $data = array();
        if ($this->input->post('due')) {
            foreach ($list as $customers) {
                $no++;
                $row = array();
                $row[] = $no . ' <input type="checkbox" name="cust[]" class="checkbox" value="' . $customers->id . '"> ';
                $row[] = ' <a href="customers/view?id=' . $customers->id . '">' . $customers->name . '</a>';
                $row[] = amountExchange($customers->total - $customers->pamnt, 0, $this->aauth->get_user()->loc);
                $row[] = $customers->company;
                $row[] = $customers->balance;
                $row[] = $customers->phone;
                $row[] = '<a href="customers/view?id=' . $customers->id . '" class="btn btn-info btn-sm"><span class="fa fa-eye"></span>  ' . $this->lang->line('View') . '</a> <a href="customers/edit?id=' . $customers->id . '" class="btn btn-primary btn-sm"><span class="fa fa-pencil"></span>  ' . $this->lang->line('Edit') . '</a> <a href="#" data-object-id="' . $customers->id . '" class="btn btn-danger btn-sm delete-object"><span class="fa fa-trash"></span></a>';
                $data[] = $row;
            }
        } else {
            foreach ($list as $customers) {
                $no++;
                $row = array();
                $row[] = $no . ' <input type="checkbox" name="cust[]" class="checkbox" value="' . $customers->id . '"> ';
                $row[] = '<a href="customers/view?id=' . $customers->id . '">' . $customers->name . '</a>';
                $row[] = $customers->company;
                $row[] = amountExchange($customers->balance);
                $row[] = $customers->phone;
                $row[] = '<a href="customers/view?id=' . $customers->id . '" class="btn btn-info btn-sm"><span class="fa fa-eye"></span>  ' . $this->lang->line('View') . '</a> <a href="customers/edit?id=' . $customers->id . '" class="btn btn-primary btn-sm"><span class="fa fa-pencil"></span>  ' . $this->lang->line('Edit') . '</a> <a href="#" data-object-id="' . $customers->id . '" class="btn btn-danger btn-sm delete-object"><span class="fa fa-trash"></span></a>';
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
        if (!$this->aauth->premission(8)) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $this->load->library("Common");
        $pid = $this->input->get('id');
        $data['customer'] = $this->customers->details($pid);
        $data['customergroup'] = $this->customers->group_info($data['customer']['gid']);
        $data['customergrouplist'] = $this->customers->group_list();
        $head['usernm'] = $this->aauth->get_user()->username;
        $data['custom_fields'] = $this->custom->view_edit_fields($pid, 1);
        $head['title'] = 'Edit Customer';
        $data['langs'] = $this->common->languages();
        $this->load->view('fixed/header', $head);
        $this->load->view('customers/edit', $data);
        $this->load->view('fixed/footer');
    }

    public function addcustomer()
    {
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
        $this->customers->add($name, $company, $phone, $email, $address, $city, $region, $country, $postbox, $customergroup, $taxid, $name_s, $phone_s, $email_s, $address_s, $city_s, $region_s, $country_s, $postbox_s, $language, $create_login, $password, $docid, $custom, $discount);


    }

    function sendSelected()
    {
        if (!$this->aauth->premission(8)) {
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
        if (!$this->aauth->premission(8)) {
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
        if (!$this->aauth->premission(8)) {
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
        if ($id) {
            $this->customers->edit($id, $name, $company, $phone, $email, $address, $city, $region, $country, $postbox, $customergroup, $taxid, $name_s, $phone_s, $email_s, $address_s, $city_s, $region_s, $country_s, $postbox_s, $docid, $custom, $language, $discount);
        }
    }

    public function changepassword()
    {
        if (!$this->aauth->premission(8)) {
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


    public function delete_i()
    {
        if ($this->aauth->premission(11)) {

            $id = $this->input->post('deleteid');
            if ($id > 1) {
                if ($this->customers->delete($id)) {
                    echo json_encode(array('status' => 'Success', 'message' => 'Customer details deleted Successfully!'));
                } else {
                    echo json_encode(array('status' => 'Error', 'message' => 'Error!'));
                }
            } else if ($this->input->post('cust')) {
                $customers = $this->input->post('cust');
                foreach ($customers as $row) {
                    $this->customers->delete($row);
                }
                echo json_encode(array('status' => 'Success', 'message' => 'Customer details deleted Successfully!'));
            }
        } else {
            echo json_encode(array('status' => 'Error', 'message' =>
                $this->lang->line('ERROR')));
        }
    }

    public function displaypic()
    {
        if (!$this->aauth->premission(8)) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $id = $this->input->get('id');
        $this->load->library("uploadhandler", array(
            'accept_file_types' => '/\.(gif|jpe?g|png)$/i', 'upload_dir' => FCPATH . 'userfiles/customers/'
        ));
        $img = (string)$this->uploadhandler->filenaam();
        if ($img != '') {
            $this->customers->editpicture($id, $img);
        }
    }


    public function translist()
    {
        if (!$this->aauth->premission(8)) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $cid = $this->input->post('cid');
        $list = $this->customers->trans_table($cid);
        $data = array();
        // $no = $_POST['start'];
        $no = $this->input->post('start');
        foreach ($list as $prd) {
            $no++;
            $row = array();
            $pid = $prd->id;
            $row[] = $prd->id;
            $row[] = $prd->date;
            $row[] = $prd->tid;

            if($prd->method == 'Balance'){
				$lbl = 'Payment trans-'.$pid;
			}else{
				$lbl = $prd->note;
			}
            
            $row[] = $lbl;
            $row[] = $prd->balance;
           
            $row[] = amountExchange($prd->credit, 0, $this->aauth->get_user()->loc); $row[] = amountExchange($prd->debit, 0, $this->aauth->get_user()->loc);
            //$row[] = $prd->account;
			
            //$row[] = $lbl;
            $row[] = '<a href="' . base_url() . 'transactions/view?id=' . $pid . '" class="btn btn-primary btn-xs"><span class="fa fa-eye"></span>  ' . $this->lang->line('View') . '</a> <a href="#" data-object-id="' . $pid . '" class="btn btn-danger btn-xs delete-object"><span class="fa fa-trash"></span></a>';
            $data[] = $row;
        }
        $output = array(
            "draw" => $this->input->post('draw'),
            "recordsTotal" => $this->customers->count_all(),
            "recordsFiltered" => $this->customers->trans_count_filtered($cid),
            "data" => $data,
        );
        //output to json format
        echo json_encode($output);
    }

    public function inv_list()
    {
        if (!$this->aauth->premission(8)) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $cid = $this->input->post('cid');
        $tid = $this->input->post('tyd');

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
		
            $row[] = '<a href="' . base_url("invoices/view?id=$invoices->id") . '" class="btn btn-success btn-xs" title="View Invoice"><i class="fa fa-file-text"></i> </a> <a target=”_blank” href="' . base_url("invoices/printinvoice?id=$invoices->id") . '&d=1" class="btn btn-info btn-xs"  title="Download"><span class="fa fa-download"></span></a> <a href="#" data-object-id="' . $invoices->id . '" class="btn btn-danger btn-xs delete-object" title="Delete"><span class="fa fa-trash"></span></a> ';
            $data[] = $row;
        }
        $output = array(
            "draw" => $this->input->post('draw'),
            "recordsTotal" => $this->customers->inv_count_all($cid),
            "recordsFiltered" => $this->customers->inv_count_filtered($cid),
            "data" => $data,
        );
        //output to json format
        echo json_encode($output);
    }

    public function transactions()
    {
        if (!$this->aauth->premission(8)) {
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
        if (!$this->aauth->premission(8)) {
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
        if (!$this->aauth->premission(8)) {
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
	
	public function add_balance2(){
		//echo "hello";exit;
 		
			
			$new_balance = $this->input->get('balance');
			
			$descr = $this->input->get('description');
			
			$start_date = datefordatabase($this->input->get('end_date'));
			
	        $custid = $this->input->get('cstid');

			$details = $this->customers->details($custid);
			
			$this->db->set('balance', "balance+$new_balance", FALSE);
                    $this->db->where('id', $custid );
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
			'ext' => 0,'loc' => 0
        );

        $this->db->insert('geopos_transactions', $data);
		
		redirect('customers/view?id='.$custid);
	
		
	}

    public function qto_list()
    {
        if (!$this->aauth->premission(8)) {
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
            $row[] = '<a href="' . base_url("quote/view?id=$invoices->id") . '" class="btn btn-success btn-xs" title="View Invoice"><i class="fa fa-file-text"></i> </a> <a href="' . base_url("quote/printquote?id=$invoices->id") . '&d=1" class="btn btn-info btn-xs"  title="Download"><span class="fa fa-download"></span></a> <a href="#" data-object-id="' . $invoices->id . '" class="btn btn-danger btn-xs delete-object" title="Delete"><span class="fa fa-trash"></span></a> ';
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
        if (!$this->aauth->premission(8)) {
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
        if (!$this->aauth->premission(8)) {
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
        if (!$this->aauth->premission(8)) {
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

            $row[] = '<a href="' . base_url() . 'projects/explore?id=' . $project->id . '" class="btn btn-primary btn-sm rounded" data-id="' . $project->id . '" data-stat="0"> ' . $this->lang->line('View') . ' </a> <a class="btn btn-info btn-sm" href="' . base_url() . 'projects/edit?id=' . $project->id . '" data-object-id="' . $project->id . '"> <i class="fa fa-pencil"></i> </a>&nbsp;<a class="btn btn-danger btn-sm delete-object" href="#" data-object-id="' . $project->id . '"> <i class="fa fa-trash"></i> </a>';


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
        if (!$this->aauth->premission(8)) {
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
        if (!$this->aauth->premission(8)) {
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

            $row[] = '<a href="editnote?id=' . $note->id . '&cid=' . $note->fid . '" class="btn btn-info btn-sm"><span class="fa fa-eye"></span> ' . $this->lang->line('View') . '</a> <a class="btn btn-danger btn-sm delete-object" href="#" data-object-id="' . $note->id . '"> <i class="fa fa-trash"></i> </a>';
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
        if (!$this->aauth->premission(8)) {
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
        if (!$this->aauth->premission(8)) {
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
        if ($this->input->post()) {
        ob_end_clean();
        ob_start();
            $this->load->model('reports_model');
            $customer = $this->input->post('customer');
            $trans_type = $this->input->post('trans_type');
            $sdate = datefordatabase($this->input->post('sdate'));
            $edate = datefordatabase($this->input->post('edate'));
            $data['customer'] = $this->customers->details($customer);
          $data['list'] = $this->reports_model->get_customer_statements($customer, $trans_type, $sdate, $edate);
          
          $company= $data['customer']['company'];
          $address= $data['customer']['address'];
          $postbox= $data['customer']['postbox'];
          
          $city= $data['customer']['city'];
          $region= $data['customer']['region'];
          $country= $data['customer']['country'];
          $phone_s= $data['customer']['phone_s'];
          $email_s= $data['customer']['email_s'];
          $name= $data['customer']['name'];
        //   echo "<pre>";
           //print_r($data['list']);exit();
           $data_tr="";
           $balance=0;

foreach ($data['list'] as $key => $row) {
        //  print_r($row);die();
        //$balance+= $row["credit"]-$row["debit"];
        if($row["tid"]>=1){

             $data_tr .= '
             <tr>
             <td>'.$row["date"].'</td>
             <td>'. $row["tid"].'</td>
             <td>  '.$row["note"].'</td>
             <td>'.$row["credit"].'</td>
             <td>'.$row["debit"].'</td>
             
             <td>'.$row["balance"].'</td>
             </tr>';
        }
        else{
            $data_tr .= '
             <tr>
             <td>'.$row["date"].'</td>
             <td></td>
             <td>'.$row["note"].'</td>
              <td>'.$row["credit"].'</td>
             <td>'.$row["debit"].'</td>
            
              <td>'.$row["balance"].'</td>
             </tr>';
        }
    }
$date=date("d/m/y");
$time=date("h:i:s");
$prev_balance = $this->aauth->get_customer($data['customer']['id']);

$prev_balance = $prev_balance->balance;

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
          <div>PEARL FOOD UK LTD</div>
          <div>UNIT 33 MIDDLE MORE ROAD</div>
          <div>BUSINESS PARK MIDDLE MORE ROAD</div>
          <div>BIRMINGHAM</div>
          <div>UK</div>
          <br>
          <div>Tel: 01215581177</div>
          <div>Email: contact@pearlfoods.co.uk</div>
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

<div >All values are shown in Pound Sterling 
<br>
<table >
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
   $this->dpdf->loadHtml($html);
   $this->dpdf->render();
   $this->dpdf->stream("Account Statement.pdf", array("Attachment"=>0));
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

            $row[] = '<a href="' . base_url('userfiles/documents/' . $document->filename) . '" class="btn btn-success btn-xs"><i class="fa fa-file-text"></i> ' . $this->lang->line('View') . '</a> <a class="btn btn-danger btn-xs delete-object" href="#" data-object-id="' . $document->id . '"> <i class="fa fa-trash"></i> </a>';


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
        if (!$this->aauth->premission(8)) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $data['id'] = $this->input->get('id');
        $data['details'] = $this->customers->details($data['id']);
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->model('accounts_model');
        $data['acclist'] = $this->accounts_model->accountslist((integer)$this->aauth->get_user()->loc);
        $this->session->set_userdata("cid", $data['id']);
        $head['title'] = 'Bulk Payment Invoices';
        $this->load->view('fixed/header', $head);
        $this->load->view('customers/bulkpayment', $data);
        $this->load->view('fixed/footer');
    }

    public function bulk_post()
    {
        if (!$this->aauth->premission(8)) {
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
        if (!$this->aauth->premission(8)) {
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

}
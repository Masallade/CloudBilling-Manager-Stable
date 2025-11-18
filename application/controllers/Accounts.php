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

class Accounts extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library("Aauth");
        if (!$this->aauth->is_loggedin()) {
            redirect('/user/', 'refresh');
        }

        if (!$this->aauth->permission_new(null, 'accountsAccess')) {
            exit('<h3>Sorry! You have insufficient permissions to access Accounts</h3>');
        }

        $this->load->model('accounts_model', 'accounts');
        $this->li_a = 'accounts';
    }
    
    public function accountstatement()
    {
        if (!$this->aauth->permission_new(null, 'accountsStatements')) {
            exit('<h3>Sorry! You have insufficient permissions to access Account Statements</h3>');
        }
        
        $this->load->model('transactions_model');
        $data['accounts'] = $this->transactions_model->acc_list();
        $head['title'] = "Account Statement";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $this->load->view('reports/statement', $data);
        $this->load->view('fixed/footer');
    }

    public function index()
    {
        if (!$this->aauth->permission_new(null, 'accountsList')) {
            exit('<h3>Sorry! You have insufficient permissions to access Accounts List</h3>');
        }

        $data['accounts'] = $this->accounts->accountslist();
        $head['usernm'] = $this->aauth->get_user()->username;
        $head['title'] = 'Accounts';
        $this->load->view('fixed/header', $head);
        $this->load->view('accounts/list', $data);
        $this->load->view('fixed/footer');
    }

    public function view()
    {
        if (!$this->aauth->permission_new(null, 'accountsList')) {
            exit('<h3>Sorry! You have insufficient permissions to view Account details</h3>');
        }

        $acid = $this->input->get('id');
        $data['account'] = $this->accounts->details($acid);
        $head['usernm'] = $this->aauth->get_user()->username;
        $head['title'] = 'View Account';
        $this->load->view('fixed/header', $head);
        $this->load->view('accounts/view', $data);
        $this->load->view('fixed/footer');
    }

    public function add()
    {
        if (!$this->aauth->permission_new(null, 'accountsManage')) {
            exit('<h3>Sorry! You have insufficient permissions to add new accounts</h3>');
        }

        $this->load->helper('form');
        $user = $this->aauth->get_user();
        $head['usernm'] = $user ? $user->username : '';
        $this->load->model('locations_model');
        $data['locations'] = $this->locations_model->locations_list2();
        $head['title'] = 'Add Account';
        $this->load->view('fixed/header', $head);
        $this->load->view('accounts/add', $data);
        $this->load->view('fixed/footer');
    }

    public function addacc()
    {
        if (!$this->aauth->permission_new(null, 'accountsManage')) {
            exit('<h3>Sorry! You have insufficient permissions to add accounts</h3>');
        }

        $accno = $this->input->post('accno');
        $holder = $this->input->post('holder');
        $intbal = numberClean($this->input->post('intbal'));
        $acode = $this->input->post('acode');
        $lid = $this->input->post('lid');
        $account_type = $this->input->post('account_type');

        if ($this->aauth->get_user()->loc) {
            $lid = $this->aauth->get_user()->loc;
        }

        if ($accno) {
            $this->accounts->addnew($accno, $holder, $intbal, $acode, $lid, $account_type);
        }
    }

    public function delete_i()
    {
        if (!$this->aauth->permission_new(null, 'accountsManage')) {
            exit('<h3>Sorry! You have insufficient permissions to delete accounts</h3>');
        }

        $id = $this->input->post('deleteid');
        if ($id) {
            $whr = array('id' => $id);
            if ($this->aauth->get_user()->loc) {
                $whr = array('id' => $id, 'loc' => $this->aauth->get_user()->loc);
            }
            $this->db->delete('geopos_accounts', $whr);
            echo json_encode(array('status' => 'Success', 'message' => $this->lang->line('ACC_DELETED')));
        } else {
            echo json_encode(array('status' => 'Error', 'message' => $this->lang->line('ERROR')));
        }
    }

    //view for edit
    public function edit()
    {
        if (!$this->aauth->permission_new(null, 'accountsManage')) {
            exit('<h3>Sorry! You have insufficient permissions to edit accounts</h3>');
        }

        $this->load->helper('form');
        $catid = $this->input->get('id');
        $this->db->select('*');
        $this->db->from('geopos_accounts');
        $this->db->where('id', $catid);
        if ($this->aauth->get_user()->loc) {
            $this->db->where('loc', $this->aauth->get_user()->loc);
        }
        $query = $this->db->get();
        $data['account'] = $query->row_array();
        $this->load->model('locations_model');
        $data['locations'] = $this->locations_model->locations_list();
        $head['usernm'] = $this->aauth->get_user()->username;
        $head['title'] = 'Edit Account';
        $this->load->view('fixed/header', $head);
        $this->load->view('accounts/edit', $data);
        $this->load->view('fixed/footer');
    }

    public function editacc()
    {
        if (!$this->aauth->permission_new(null, 'accountsManage')) {
            exit('<h3>Sorry! You have insufficient permissions to edit accounts</h3>');
        }

        $acid = $this->input->post('acid');
        $accno = $this->input->post('accno');
        $holder = $this->input->post('holder');
        $acode = $this->input->post('acode');
        $lid = $this->input->post('lid');
        $account_type = $this->input->post('account_type');
        $equity = numberClean($this->input->post('balance'));

        if ($this->aauth->get_user()->loc) {
            $lid = $this->aauth->get_user()->loc;
        }

        if ($acid) {
            $this->accounts->edit($acid, $accno, $holder, $acode, $lid, $equity, $account_type);
        }
    }

    public function balancesheet()
    {
        if (!$this->aauth->permission_new(null, 'accountsBalanceSheet')) {
            exit('<h3>Sorry! You have insufficient permissions to access Balance Sheet</h3>');
        }

        $head['title'] = "Balance Summary";
        $head['usernm'] = $this->aauth->get_user()->username;
        $data['accounts'] = $this->accounts->accountslist();
        $this->load->view('fixed/header', $head);
        $this->load->view('transactions/balance', $data);
        $this->load->view('fixed/footer');
    }

    public function account_stats()
    {
        if (!$this->aauth->permission_new(null, 'accountsList')) {
            exit('<h3>Sorry! You have insufficient permissions to access Account Statistics</h3>');
        }

        $this->accounts->account_stats();
    }

    public function ajax_list()
    {
        if (!$this->aauth->permission_new(null, 'accountsList')) {
            echo json_encode(array('status' => 'Error', 'message' => 'Insufficient permissions'));
            return;
        }

        $this->accounts->ajax_accounts_list();
    }
}
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

class Locations extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library("Aauth");
        if (!$this->aauth->is_loggedin()) {
            redirect('/user/', 'refresh');
        }
        if (!$this->aauth->permission_new(null, 'usersLocations')) {

            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');

        }
        $this->li_a = 'company';
        $this->load->library("Common");
        $this->load->model('locations_model', 'locations');
    }

    public function index()
    {

        $head['title'] = "Locations";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $this->load->view('locations/index');
        $this->load->view('fixed/footer');
    }

    public function loc_list()
    {
        // Server-side response for Locations DataTable
        $all = $this->locations->locations_list();
        $draw = intval($this->input->post('draw'));
        $start = intval($this->input->post('start'));
        $length = intval($this->input->post('length'));

        $total = is_array($all) ? count($all) : 0;
        $slice = ($length > 0) ? array_slice($all, $start, $length) : $all;

        $data = array();
        $no = $start + 1;
        foreach ($slice as $row) {
            $cid = $row['id'];
            $name = $row['cname'];
            $addr = $row['address'] . ', ' . $row['city'];

            $actions = "<a href='" . base_url("locations/edit?id=$cid") . "' class='btn btn-info btn-xs' title='" . $this->lang->line('Edit') . "'><i class='fa fa-edit'></i> </a>&nbsp;";
            $actions .= "<a href='#' data-object-id='" . $cid . "' class='btn btn-danger btn-xs delete-object' title='" . $this->lang->line('Delete') . "'><i class='fa fa-trash'></i></a>";

            $data[] = array($no, $name, $addr, $actions);
            $no++;
        }

        $output = array(
            'draw' => $draw,
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $data,
        );
        echo json_encode($output);
    }


    public function view()
    {
        $data['cat'] = $this->products_cat->category_stock();
        $head['title'] = "View Product Category";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $this->load->view('products/category_view', $data);
        $this->load->view('fixed/footer');
    }


    public function create()
    {
        if ($this->input->post()) {
            $name = $this->input->post('name', true);
            $address = $this->input->post('address', true);
            $city = $this->input->post('city', true);
            $region = $this->input->post('region', true);
            $country = $this->input->post('country', true);
            $postbox = $this->input->post('postbox', true);
            $phone = $this->input->post('phone', true);
            $email = $this->input->post('email', true);
            $taxid = $this->input->post('taxid', true);
            $image = $this->input->post('image', true);
            // $cur_id = $this->input->post('cur_id', true);
            $ac_id = $this->input->post('account', true);
            $wid = $this->input->post('wid');
            $this->locations->create($name, $address, $city, $region, $country, $postbox, $phone, $email, $taxid, $image, $ac_id, $wid);
        } else {


            $head['title'] = "Add Location";
            $data['currency'] = $this->locations->currencies();
            $data['warehouse'] = $this->locations->warehouses();
            $data['accounts'] = $this->locations->accountslist();
            $head['usernm'] = $this->aauth->get_user()->username;
            $this->load->view('fixed/header', $head);
            $this->load->view('locations/create', $data);
            $this->load->view('fixed/footer');
        }
    }

    public function edit()
    {
        if ($this->input->post()) {
            $id = $this->input->post('id');
            $name = $this->input->post('name', true);
            $address = $this->input->post('address', true);
            $city = $this->input->post('city', true);
            $region = $this->input->post('region', true);
            $country = $this->input->post('country', true);
            $postbox = $this->input->post('postbox', true);
            $phone = $this->input->post('phone', true);
            $email = $this->input->post('email', true);
            $taxid = $this->input->post('taxid', true);
            $image = $this->input->post('image', true);
            $cur_id = $this->input->post('cur_id', true);
            $ac_id = $this->input->post('account_v', true);
            $wid = $this->input->post('wid');
            $this->locations->edit($id, $name, $address, $city, $region, $country, $postbox, $phone, $email, $taxid, $image, $cur_id, $ac_id, $wid);
        } else {


            $head['title'] = "Edit Location";
            $head['usernm'] = $this->aauth->get_user()->username;
            $data = $this->locations->view($this->input->get('id'));
            $data['currency'] = $this->locations->currencies();
            $data['accounts'] = $this->locations->accountslist();
            $data['warehouse'] = $this->locations->warehouses();
            $data['online_pay'] = $this->locations->online_pay_settings($this->input->get('id'));
            $this->load->view('fixed/header', $head);
            $this->load->view('locations/edit', $data);
            $this->load->view('fixed/footer');
        }
    }


    public function delete_i()
    {
        $id = $this->input->post('deleteid');
        if ($id) {

            $this->db->delete('geopos_locations', array('id' => $id));


            echo json_encode(array('status' => 'Success', 'message' => $this->lang->line('DELETED')));
        } else {
            echo json_encode(array('status' => 'Error', 'message' => $this->lang->line('ERROR')));
        }
    }

    public function load_list()
    {
        $list = $this->promo->get_datatables();
        $data = array();
        $no = $this->input->post('start');
        foreach ($list as $promo) {
            $no++;
            switch ($promo->active) {
                case 0 :
                    $promo_status = '<span class="st-paid">' . $this->lang->line('Active') . '</a>';
                    break;
                case 1 :
                    $promo_status = '<span class="st-partial">' . $this->lang->line('Used') . '</a>';
                    break;
                case 2 :
                    $promo_status = '<span class="st-due">' . $this->lang->line('Expired') . '</a>';
                    break;
            }
            $row = array();
            $row[] = $no;
            $row[] = $promo->code;
            $row[] = $promo->amount;
            $row[] = $promo->valid;
            $row[] = $promo_status;
            $row[] = $promo->available . ' (' . $promo->qty . ')';
            $row[] = '<a href="#" class="btn btn-primary btn-sm rounded set-task" data-id="' . $promo->id . '" data-stat="0"> SET </a> <a href="#" data-object-id="' . $promo->id . '" class="btn btn-danger btn-sm delete-object"><span class="icon-bin"></span></a>';


            $data[] = $row;
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->promo->count_all(),
            "recordsFiltered" => $this->promo->count_filtered(),
            "data" => $data,
        );
        //output to json format
        echo json_encode($output);
    }

    public function promo_stats()
    {

        $this->promo->promo_stats();


    }

    public function set_status()
    {
        $id = $this->input->post('tid');
        $stat = $this->input->post('stat');
        $this->promo->set_status($id, $stat);
        echo json_encode(array('status' => 'Success', 'message' => $this->lang->line('UPDATED'), 'pstatus' => 'Success'));


    }

    public function file_handling()
    {
        if ($this->input->get('op')) {
            $name = $this->input->get('name');
            if ($this->products->meta_delete($name)) {
                echo json_encode(array('status' => 'Success'));
            }
        } else {
            $id = $this->input->get('id');
            $this->load->library("Uploadhandler_generic", array(
                'accept_file_types' => '/\.(gif|jpe?g|png)$/i', 'upload_dir' => FCPATH . 'userfiles/company/', 'upload_url' => base_url() . 'userfile/company/'
            ));


        }


    }


}
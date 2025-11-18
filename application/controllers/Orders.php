<?php


defined('BASEPATH') or exit('No direct script access allowed');

class Orders extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('order_model', 'order');
        $this->load->library("Aauth");
        if (!$this->aauth->is_loggedin()) {
            redirect('/user/', 'refresh');
        }
        if (!$this->aauth->premission(5)) {

            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');

        }
       // $this->load->library("Coupon");
        $this->li_a = 'order';

    }

    public function index()
    {

        $head['title'] = "App Orders";
		$data['totalt'] = $this->order->count_all();
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $this->load->view('order/index', $data);
        $this->load->view('fixed/footer');
    }

 public function delete_i()
    {
        $id = $this->input->post('deleteid');
        if ($id) {
            $this->db->select('*');
            $this->db->from('app_orders');
            $this->db->where('id', $id);
            $query = $this->db->get();
            $promo = $query->row_array();
            $this->db->delete('app_orders', array('id' => $id));

            echo json_encode(array('status' => 'Success', 'message' => $this->lang->line('DELETED')));
        } else {
            echo json_encode(array('status' => 'Error', 'message' => $this->lang->line('ERROR')));
        }
    }
	

 public function load_list()
    {
        $list = $this->order->get_datatables();
        $data = array();
        $no = $this->input->post('start');
        foreach ($list as $order) {
            $no++;
           
            $row = array();
            $row[] = $no;
			$row[] = $this->order->getItemName($order->item_id);
			$row[] = $this->order->getUserName($order->user_id);
			$row[] = $order->delivery_address;
			$row[] = $order->qty;
			$row[] = $order->sale_price;
			$row[] = $this->order->getWareName($order->warehouse_id);
			$row[] = $order->order_date;
            $row[] = '<a href="#" data-object-id="' . $order->id . '" class="btn btn-danger btn-sm delete-object"><span class="fa fa-trash"></span></a>';


            $data[] = $row;
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->order->count_all(),
            "recordsFiltered" => $this->order->count_filtered(),
            "data" => $data,
        );
        //output to json format
        echo json_encode($output);
    }

}
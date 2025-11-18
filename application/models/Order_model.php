<?php


defined('BASEPATH') OR exit('No direct script access allowed');

class Order_model extends CI_Model
{

    var $table = 'app_orders';
    var $column_order = array(null, 'warehouse_id', 'item_id', 'sale_price', null);
    var $column_search = array('warehouse_id', 'item_id', 'sale_price');
    var $order = array('id' => 'desc');

    private function _get_datatables_query($id = '')
    {

        $this->db->from($this->table);
                if ($this->aauth->get_user()->loc) {
            $this->db->group_start();
            $this->db->where('location', $this->aauth->get_user()->loc);
            if (BDATA) $this->db->or_where('location', 0);
            $this->db->group_end();
        } elseif (!BDATA) {
            $this->db->where('location', 0);
        }
        if ($id != '') {
            $this->db->where('gid', $id);
        }
        $i = 0;

        foreach ($this->column_search as $item) // loop column
        {
            $search = $this->input->post('search');
            $value = $search['value'];
            if ($value) // if datatable send POST for search
            {

                if ($i === 0) // first loop
                {
                    $this->db->group_start(); // open bracket. query Where with OR clause better with bracket. because maybe can combine with other WHERE with AND.
                    $this->db->like($item, $value);
                } else {
                    $this->db->or_like($item, $value);
                }

                if (count($this->column_search) - 1 == $i) //last loop
                    $this->db->group_end(); //close bracket
            }
            $i++;
        }
        $search = $this->input->post('order');
        if ($search) // here order processing
        {
            $this->db->order_by($this->column_order[$search['0']['column']], $search['0']['dir']);
        } else if (isset($this->order)) {
            $order = $this->order;
            $this->db->order_by(key($order), $order[key($order)]);
        }
    }

    function get_datatables($id = '')
    {
        $this->_get_datatables_query($id);
        if ($this->input->post('length') != -1)
            $this->db->limit($this->input->post('length'), $this->input->post('start'));
                     if ($this->aauth->get_user()->loc) {
            $this->db->group_start();
            $this->db->where('location', $this->aauth->get_user()->loc);
            if (BDATA) $this->db->or_where('location', 0);
            $this->db->group_end();
        } elseif (!BDATA) {
            $this->db->where('location', 0);
        }
        $query = $this->db->get();
        return $query->result();
    }

    function count_filtered($id = '')
    {
        $this->_get_datatables_query();
                     if ($this->aauth->get_user()->loc) {
            $this->db->group_start();
            $this->db->where('location', $this->aauth->get_user()->loc);
            if (BDATA) $this->db->or_where('location', 0);
            $this->db->group_end();
        } elseif (!BDATA) {
            $this->db->where('location', 0);
        }
        $query = $this->db->get();
        return $query->num_rows($id = '');
    }

    public function count_all($id = '')
    {
        $this->_get_datatables_query();
                     if ($this->aauth->get_user()->loc) {
            $this->db->group_start();
            $this->db->where('location', $this->aauth->get_user()->loc);
            if (BDATA) $this->db->or_where('location', 0);
            $this->db->group_end();
        } elseif (!BDATA) {
            $this->db->where('location', 0);
        }
        $query = $this->db->get();
        return $query->num_rows($id = '');
    }
	
	public function getWareName($id){
		
		$this->db->select('title');
        $this->db->from('geopos_warehouse');
        $this->db->where('id', $id);
        $query = $this->db->get();
        return $query->row()->title;
		
	}
	public function getItemName($id){
		
		$this->db->select('product_name');
        $this->db->from('geopos_products');
        $this->db->where('pid', $id);
        $query = $this->db->get();
        return $query->row()->product_name;
		
	}
	public function getUserName($id){
		
		$this->db->select('name');
        $this->db->from('geopos_customers');
        $this->db->where('id', $id);
        $query = $this->db->get();
        return $query->row()->name;
		
	}
	
	

    public function details($custid)
    {

        $this->db->select('*');
        $this->db->from($this->table);
        $this->db->where('id', $custid);
        $query = $this->db->get();
        return $query->row_array();
    }


    

  

}
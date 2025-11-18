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



class Purchase_model extends CI_Model

{

    var $table = 'geopos_purchase';

    var $column_order = array(null, 'geopos_purchase.tid', 'geopos_supplier.name', 'geopos_purchase.invoicedate', 'geopos_purchase.total', 'geopos_purchase.status', null);

    var $column_search = array('geopos_purchase.tid', 'geopos_supplier.name', 'geopos_purchase.invoicedate', 'geopos_purchase.total', 'geopos_purchase.status');

    var $order = array('geopos_purchase.tid' => 'desc');



    public function __construct()

    {

        parent::__construct();
    }



    public function lastpurchase()

    {

        $this->db->select('tid');

        $this->db->from($this->table);

        $this->db->order_by('tid', 'DESC');

        $this->db->limit(1);

        $query = $this->db->get();

        if ($query->num_rows() > 0) {

            return $query->row()->tid;
        } else {

            return 1000;
        }
    }

    function get_sa_datatables($opt = '')
    {
        $this->_get_sa_datatables_query($opt);
        if ($_POST['length'] != -1)
            $this->db->limit($_POST['length'], $_POST['start']);
        $query = $this->db->get();
        // $this->db->where('geopos_purchase.i_class', 0);
        if ($this->aauth->get_user()->loc) {
            $this->db->where('geopos_purchase.loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('geopos_purchase.loc', 0);
        }

        return $query->result();
    }

    private function _get_sa_datatables_query($opt = '')
    {

        $this->db->select('geopos_purchase.id,geopos_purchase.tid,geopos_purchase.csd,geopos_purchase.invoicedate,geopos_purchase.invoiceduedate,geopos_purchase.total,geopos_purchase.pamnt,geopos_purchase.status,geopos_customers.name,geopos_customers.company');
        $this->db->from($this->table);
        // $this->db->where('geopos_purchase.i_class', 0);
        //$this->db->where('geopos_purchase.status !=', 'paid');
        if ($opt) {
            $this->db->where('geopos_purchase.eid', $opt);
        }
        if ($this->aauth->get_user()->loc) {
            $this->db->where('geopos_purchase.loc', $this->aauth->get_user()->loc);
        }
        if (!$this->aauth->premission(19)) {
            //$this->db->where('geopos_purchase.inv_type !=', 'DAYPASS');
        } elseif (!BDATA) {
            $this->db->where('geopos_purchase.loc', 0);
        }
        if ($this->input->post('start_date') && $this->input->post('end_date')) // if datatable send POST for search
        {
            $this->db->where('DATE(geopos_purchase.invoicedate) >=', datefordatabase($this->input->post('start_date')));
            $this->db->where('DATE(geopos_purchase.invoicedate) <=', datefordatabase($this->input->post('end_date')));
        }

        if ($this->input->post('end_cat')) // if datatable send POST for search
        {

            $cats = $myArray = explode(',', $this->input->post('end_cat'));

            $this->db->where_in('geopos_invoice_items.cat_id.id', $cats);

            $this->db->group_by('geopos_purchase.id');

            // $this->db->where('geopos_invoice_items.cat_id <=', $this->input->post('end_cat'));

            $this->db->join('geopos_invoice_items', 'geopos_invoice_items.tid=geopos_purchase.id', 'left');
        }

        $this->db->join('geopos_customers', 'geopos_purchase.csd=geopos_customers.id', 'left');

        $i = 0;

        foreach ($this->column_search as $item) // loop column
        {
            if ($this->input->post('search')['value']) // if datatable send POST for search
            {

                if ($i === 0) // first loop
                {
                    $this->db->group_start(); // open bracket. query Where with OR clause better with bracket. because maybe can combine with other WHERE with AND.
                    $this->db->like($item, $this->input->post('search')['value']);
                } else {
                    $this->db->or_like($item, $this->input->post('search')['value']);
                }

                if (count($this->column_search) - 1 == $i) //last loop
                    $this->db->group_end(); //close bracket
            }
            $i++;
        }

        if (isset($_POST['order'])) // here order processing
        {
            $this->db->order_by($this->column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        } else if (isset($this->order)) {
            $order = $this->order;
            $this->db->order_by(key($order), $order[key($order)]);
        }
    }



    public function warehouses()

    {

        $this->db->select('*');

        $this->db->from('geopos_warehouse');

        if ($this->aauth->get_user()->loc) {

            $this->db->where('loc', $this->aauth->get_user()->loc);

            if (BDATA) $this->db->or_where('loc', 0);
        } elseif (!BDATA) {

            $this->db->where('loc', 0);
        }

        $query = $this->db->get();

        return $query->result_array();
    }



    public function purchase_details($id)

    {



        $this->db->select('geopos_purchase.*,geopos_purchase.id AS iid,SUM(geopos_purchase.shipping + geopos_purchase.ship_tax) AS shipping,geopos_supplier.*,geopos_supplier.id AS cid,geopos_terms.id AS termid,geopos_terms.title AS termtit,geopos_terms.terms AS terms');

        $this->db->from($this->table);

        $this->db->where('geopos_purchase.id', $id);

        if ($this->aauth->get_user()->loc) {

            $this->db->where('geopos_purchase.loc', $this->aauth->get_user()->loc);

            if (BDATA) $this->db->or_where('geopos_purchase.loc', 0);
        } elseif (!BDATA) {

            $this->db->where('geopos_purchase.loc', 0);
        }

        $this->db->join('geopos_supplier', 'geopos_purchase.csd = geopos_supplier.id', 'left');

        $this->db->join('geopos_terms', 'geopos_terms.id = geopos_purchase.term', 'left');

        $query = $this->db->get();

        return $query->row_array();
    }

    public function stock_report_export($ids = '', $upids = '', $categories = '', $end_cat = '')
    {

        $str_arr = explode(",", $ids);
        $str_arr_up = explode(",", $upids);

        // $categories = explode (",", $categories); 
        $start_cat = $categories;

        $items = array();
        // foreach ($str_arr as $key => $value) { 
        //     if($start_cat=="" || $end_cat=="")
        //      {
        //           $sql="select * from geopos_invoice_items where tid='".$value."'";
        //      }
        //      else
        //      {
        //           $sql="select * from geopos_invoice_items where tid='".$value."' and cat_id between '".$start_cat."' and '".$end_cat."'";
        //      }

        //   $query = $this->db->query($sql);
        //   $items= array_merge($items,$query->result_array());
        // }
        //print_r( $this->db->last_query());die();

        if ($start_cat == "" || $end_cat == "") {
            $sql = "SELECT 
                sum(ii.subtotal) as subtotal,
                sum(ii.totaltax) as totaltax,
                ic.title,
                ic.id,
                ii.product,
                ii.product_des,
                sum(qty) as qty, 
              gp.id AS po_no,
              gp.invoicedate AS po_date,
            gs.name AS supplier_name
        FROM geopos_purchase_items ii
        JOIN geopos_product_cat ic ON ii.cat_id = ic.id
        JOIN geopos_purchase gp ON ii.tid = gp.id
        JOIN geopos_supplier gs ON gp.csd = gs.id
        WHERE ii.tid IN (" . ($ids == "" ? "-1" : $ids) . ")
        GROUP BY ic.title, ii.product, gp.id, gp.invoicedate, gs.name;";
            $query = $this->db->query($sql);
            $data = $query->result_array();

            return $data;

            // $sql = "(SELECT 
            //     SUM(ii.subtotal) AS subtotal,
            //     SUM(ii.totaltax) AS totaltax,
            //     ic.title,
            //     ic.id,
            //     ii.product,
            //     ii.product_des,
            //     SUM(ii.qty) AS qty
            // FROM geopos_invoice_items ii
            // JOIN geopos_product_cat ic ON ii.cat_id = ic.id
            // WHERE ii.tid IN (".($ids == "" ? "-1" : $ids).")
            // GROUP BY ic.title, ii.product)
            // UNION
            // (SELECT 
            //     SUM(ii.subtotal) AS subtotal,
            //     SUM(ii.totaltax) AS totaltax,
            //     ic.title,
            //     ic.id,
            //     ii.product,
            //     ii.product_des,
            //     SUM(ii.qty) AS qty
            // FROM geopos_invoice_items_bfr_post ii
            // JOIN geopos_product_cat ic ON ii.cat_id = ic.id
            // JOIN geopos_invoices_bfr_post ibp ON ii.tid = ibp.tid
            // WHERE ii.tid IN (".($upids == "" ? "-1" : $upids).") AND ibp.is_posted = 1
            // GROUP BY ic.title, ii.product)";

            // $query = $this->db->query($sql);

        } else {
            $sql = "SELECT sum(ii.subtotal) as subtotal,sum(ii.totaltax) as totaltax,
            ic.title,
            ic.id,
            ii.product,
            ii.product_des,
            sum(qty) as qty, 
            gp.id AS po_no,
            gp.invoicedate AS po_date,
            gs.name AS supplier_name
        FROM geopos_purchase_items ii
        JOIN geopos_product_cat ic ON ii.cat_id = ic.id
        JOIN geopos_purchase gp ON ii.tid = gp.id
        JOIN geopos_supplier gs ON gp.csd = gs.id
        WHERE ii.tid IN (" . $ids . ")
        GROUP BY ic.title, ii.product, gp.id, gp.invoicedate, gs.name;";
            $query = $this->db->query($sql);
            return  $query->result_array();
        }

        $query = $this->db->query($sql);
        return  $query->result_array();


        // print_r($query->result_array());die();

    }



    public function purchase_products($id)

    {

        $this->db->select('*');

        $this->db->from('geopos_purchase_items');

        $this->db->where('tid', $id);

        $query = $this->db->get();

        return $query->result_array();
    }



    public function purchase_transactions($id)

    {

        $this->db->select('*');

        $this->db->from('geopos_transactions');

        $this->db->where('tid', $id);

        $this->db->where('ext', 1);

        $query = $this->db->get();

        return $query->result_array();
    }



    public function purchase_delete($id)

    {

        $this->db->trans_start();

        $this->db->select('pid,qty');

        $this->db->from('geopos_purchase_items');

        $this->db->where('tid', $id);

        $query = $this->db->get();

        $prevresult = $query->result_array();

        foreach ($prevresult as $prd) {

            $amt = $prd['qty'];

            $this->db->set('qty', "qty-$amt", FALSE);

            $this->db->where('pid', $prd['pid']);

            $this->db->update('geopos_products');
        }

        $whr = array('id' => $id);

        if ($this->aauth->get_user()->loc) {

            $whr = array('id' => $id, 'loc' => $this->aauth->get_user()->loc);
        } elseif (!BDATA) {

            $whr = array('id' => $id, 'loc' => 0);
        }

        $this->db->delete('geopos_purchase', $whr);
        $this->db->where('tid', $id);
        $this->db->where('cat', 'Purchase');
        $this->db->delete('geopos_transactions');
        if ($this->db->affected_rows()) $this->db->delete('geopos_purchase_items', array('tid' => $id));

        if ($this->db->trans_complete()) {

            return true;
        } else {

            return false;
        }
    }





    private function _get_datatables_query($param = '')

    {
        // Initialize start and end parameters
        $start_param = '';
        $end_param = '';

        // Get today's date in the format YYYY-MM-DD
        $today = date('Y-m-d');

        // Get tomorrow's dateq 
        $tomorrow = date("Y-m-d", strtotime("+1 day"));

        // Get the first day of the previous month
        $first_day_last_month = date('Y-m-01', strtotime('first day of last month'));

        // Get the last day of the previous month
        $last_day_last_month = date('Y-m-t', strtotime('last day of last month'));
        $last_day_this_month = date('Y-m-t', strtotime('last day of this month'));

        // Check the value of $param and set the start and end date accordingly
        if ($param == 'today') {
            // If 'today', set both start and end to today's date
            $start_param = $today;
            $end_param = $today;
        } elseif ($param == 'month') {
            // If 'month', set the start date to the first of the current month and the end date to today
            $start_param = date('Y-m-01');  // First day of this month
            $end_param = $last_day_this_month;  // Today
        } elseif ($param == 'year') {
            // If 'year', set the start date to the first of January and the end date to today
            $start_param = date('Y-01-01');  // First day of the current year
            $end_param = $today;  // Today
        } elseif ($param == 'total') {
            // If 'total', we don't need to set any date range
            // No filtering by date is applied in this case
            $start_param = '';
            $end_param = '';
        }
        $this->db->select('geopos_purchase.id,geopos_purchase.invoice_ref,geopos_purchase.tid,geopos_purchase.invoicedate,geopos_purchase.invoiceduedate,geopos_purchase.total,geopos_purchase.status,geopos_purchase.verified,geopos_supplier.name');

        $this->db->from($this->table);

        $this->db->join('geopos_supplier', 'geopos_purchase.csd=geopos_supplier.id', 'left');
        
        if ($this->aauth->get_user()->loc) {

            $this->db->where('geopos_purchase.loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('geopos_purchase.loc', 0);
        }

        if (!$param) {
            if ($this->input->post('start_date') && $this->input->post('end_date')) // if datatable send POST for search

            {

                $this->db->where('DATE(geopos_purchase.invoicedate) >=', datefordatabase($this->input->post('start_date')));

                $this->db->where('DATE(geopos_purchase.invoicedate) <=', datefordatabase($this->input->post('end_date')));
            }
        } else {
            if ($start_param && $end_param) {
                $this->db->where('DATE(geopos_purchase.invoicedate) >=', $start_param);
                $this->db->where('DATE(geopos_purchase.invoicedate) <=', $end_param);
            }
        }

        $i = 0;

        foreach ($this->column_search as $item) // loop column

        {

            if ($this->input->post('search')['value']) // if datatable send POST for search

            {



                if ($i === 0) // first loop

                {

                    $this->db->group_start(); // open bracket. query Where with OR clause better with bracket. because maybe can combine with other WHERE with AND.

                    $this->db->like($item, $this->input->post('search')['value']);
                } else {

                    $this->db->or_like($item, $this->input->post('search')['value']);
                }



                if (count($this->column_search) - 1 == $i) //last loop

                    $this->db->group_end(); //close bracket

            }

            $i++;
        }



        if (isset($_POST['order'])) // here order processing

        {

            $this->db->order_by($this->column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        } else if (isset($this->order)) {

            $order = $this->order;

            $this->db->order_by(key($order), $order[key($order)]);
        }

    }



    function get_datatables($param = '')  // Add default value
    {
        $this->_get_datatables_query($param);

        if ($_POST['length'] != -1)

            $this->db->limit($_POST['length'], $_POST['start']);

        $query = $this->db->get();
        return $query->result();
    }



    function count_filtered($param = '')

    {

        $this->_get_datatables_query($param);

        $query = $this->db->get();

        return $query->num_rows();
    }



    public function count_all()

    {

        $this->db->from($this->table);

        if ($this->aauth->get_user()->loc) {

            $this->db->where('geopos_purchase.loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('geopos_purchase.loc', 0);
        }

        return $this->db->count_all_results();
    }





    public function billingterms()

    {

        $this->db->select('id,title');

        $this->db->from('geopos_terms');

        $this->db->where('type', 4);

        $this->db->or_where('type', 0);

        $query = $this->db->get();

        return $query->result_array();
    }



    public function currencies()

    {



        $this->db->select('*');

        $this->db->from('geopos_currencies');

        $query = $this->db->get();

        return $query->result_array();
    }



    public function currency_d($id)

    {

        $this->db->select('*');

        $this->db->from('geopos_currencies');

        $this->db->where('id', $id);

        $query = $this->db->get();

        return $query->row_array();
    }



    public function employee($id)

    {

        $this->db->select('geopos_employees.name,geopos_employees.sign,geopos_users.roleid');

        $this->db->from('geopos_employees');

        $this->db->where('geopos_employees.id', $id);

        $this->db->join('geopos_users', 'geopos_employees.id = geopos_users.id', 'left');

        $query = $this->db->get();

        return $query->row_array();
    }



    public function meta_insert($id, $type, $meta_data)

    {



        $data = array('type' => $type, 'rid' => $id, 'col1' => $meta_data);

        if ($id) {

            return $this->db->insert('geopos_metadata', $data);
        } else {

            return 0;
        }
    }



    public function attach($id)

    {

        $this->db->select('geopos_metadata.*');

        $this->db->from('geopos_metadata');

        $this->db->where('geopos_metadata.type', 4);

        $this->db->where('geopos_metadata.rid', $id);

        $query = $this->db->get();

        return $query->result_array();
    }



    public function meta_delete($id, $type, $name)

    {

        if (@unlink(FCPATH . 'userfiles/attach/' . $name)) {

            return $this->db->delete('geopos_metadata', array('rid' => $id, 'type' => $type, 'col1' => $name));
        }
    }
}

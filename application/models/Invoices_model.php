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

class Invoices_model extends CI_Model
{
    var $table = 'geopos_invoices';
    var $column_order = array(null, 'geopos_invoices.tid', 'geopos_customers.name', 'geopos_customers.company', 'geopos_invoices.inv_type', 'geopos_invoices.invoicedate', 'geopos_invoices.total', 'geopos_invoices.status', null);
    var $column_search = array('geopos_invoices.tid', 'geopos_customers.name', 'geopos_customers.company', 'geopos_invoices.inv_type', 'geopos_invoices.invoicedate', 'geopos_invoices.total', 'geopos_invoices.status');
    var $order = array('geopos_invoices.tid' => 'desc');

    public function __construct()
    {
        parent::__construct();
        $this->load->model('settings_model', 'settings');
    }

    public function lastinvoice()
    {
        $auto_post = $this->settings->auto_post();
        $invoice_table = 'geopos_invoices_bfr_post';
        if ($auto_post) {
            $invoice_table = 'geopos_invoices';
        }

        $this->db->select('tid');
        $this->db->from($invoice_table);
        $this->db->order_by('tid', 'DESC');
        $this->db->limit(1);
        $this->db->where('i_class', 0);
        $query = $this->db->get();
        //echo $this->db->last_query(); exit; 
        if ($query->num_rows() > 0) {
            return $query->row()->tid;
        } else {
            return 0;
            //return 1000;
        }
    }




    public function lastadvance()
    {
        $this->db->select('inv_id');
        $this->db->from('geopos_customer_transactions');
        $this->db->order_by('inv_id', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->row()->inv_id;
        } else {
            return 1000;
        }
    }





    public function invoice_details_bfr_post($id = '', $eid = '', $p = true)
    {
        $this->db->select('geopos_invoices_bfr_post.*,SUM(geopos_invoices_bfr_post.shipping + geopos_invoices_bfr_post.ship_tax) AS shipping,geopos_customers.*,geopos_invoices_bfr_post.loc as loc,geopos_invoices_bfr_post.id AS iid,geopos_customers.id AS cid,geopos_terms.id AS termid,geopos_terms.title AS termtit,geopos_terms.terms AS terms');
        $this->db->from('geopos_invoices_bfr_post');
        $this->db->where('geopos_invoices_bfr_post.id', $id);
        if ($eid) {
            $this->db->where('geopos_invoices_bfr_post.eid', $eid);
        }
        if ($p) {


            if ($this->aauth->get_user()->loc) {
                $this->db->where('geopos_invoices_bfr_post.loc', $this->aauth->get_user()->loc);
            } elseif (!BDATA) {
                $this->db->where('geopos_invoices_bfr_post.loc', 0);
            }
        }
        $this->db->join('geopos_customers', 'geopos_invoices_bfr_post.csd = geopos_customers.id', 'left');
        $this->db->join('geopos_terms', 'geopos_terms.id = geopos_invoices_bfr_post.term', 'left');
        // $this->db->order_by('geopos_invoices_bfr_post.id', 'ASC');

        $query = $this->db->get();
        return $query->row_array();
    }


    public function purchase_details($id)
    {


        $this->db->select('geopos_invoices.*,geopos_invoices.id AS iid,geopos_customers.*');
        $this->db->from('geopos_invoices');
        $this->db->where('geopos_invoices.id', $id);
        $this->db->join('geopos_customers', 'geopos_invoices.csd = geopos_customers.id', 'left');
        $query = $this->db->get();
        return $query->row_array();
    }

    public function purchase_products($id)
    {

        $this->db->select('*');
        $this->db->from('geopos_invoice_items');
        $this->db->where('tid', $id);

        $query = $this->db->get();
        return $query->result_array();
    }


    public function purchase_transactions($id)
    {

        $this->db->select('*');
        $this->db->from('geopos_transactions');
        $this->db->where('tid', $id);
        $this->db->where('ext', 6);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function items_with_product_bfr_post($id)
    {

        $this->db->select('geopos_invoice_items_bfr_post.*,geopos_products.qty AS alert, geopos_products.code_type AS vattype, geopos_products.product_price AS cost');
        $this->db->from('geopos_invoice_items_bfr_post');
        $this->db->where('geopos_invoice_items_bfr_post.tid', $id);
        $this->db->join('geopos_products', 'geopos_products.pid = 	geopos_invoice_items_bfr_post.pid', 'left');
        $this->db->order_by('geopos_invoice_items_bfr_post.id', 'ASC');

        $query = $this->db->get();
        return $query->result_array();
    }


    public function invoice_details_for_credit($id = '', $eid = '', $p = true)
    {
        $this->db->select('geopos_invoices.*,SUM(geopos_invoices.shipping + geopos_invoices.ship_tax) AS shipping,geopos_customers.*,geopos_invoices.loc as loc,geopos_invoices.id AS iid,geopos_customers.id AS cid,geopos_terms.id AS termid,geopos_terms.title AS termtit,geopos_terms.terms AS terms');
        $this->db->from('geopos_invoices');
        $this->db->where('geopos_invoices.id', $id);
        if ($eid) {
            $this->db->where('geopos_invoices.eid', $eid);
        }
        if ($p) {


            if ($this->aauth->get_user()->loc) {
                $this->db->where('geopos_invoices.loc', $this->aauth->get_user()->loc);
            } elseif (!BDATA) {
                $this->db->where('geopos_invoices.loc', 0);
            }
        }
        $this->db->join('geopos_customers', 'geopos_invoices.csd = geopos_customers.id', 'left');
        $this->db->join('geopos_terms', 'geopos_terms.id = geopos_invoices.term', 'left');
        $query = $this->db->get();
        return $query->row_array();
    }

    public function items_with_product_for_credit($id)
    {

        $this->db->select('	geopos_invoice_items.*,geopos_products.qty AS alert');
        $this->db->from('	geopos_invoice_items');
        $this->db->where('tid', $id);
        $this->db->join('geopos_products', 'geopos_products.pid = 	geopos_invoice_items.pid', 'left');
        $query = $this->db->get();
        return $query->result_array();
    }




    public function invoice_details($id = '', $eid = '', $p = true)
    {
        $this->db->select('geopos_invoices.*,SUM(geopos_invoices.shipping + geopos_invoices.ship_tax) AS shipping,geopos_customers.*,geopos_invoices.loc as loc,geopos_invoices.id AS iid,geopos_customers.id AS cid,geopos_terms.id AS termid,geopos_terms.title AS termtit,geopos_terms.terms AS terms');
        $this->db->from($this->table);
        $this->db->where('geopos_invoices.id', $id);
        if ($eid) {
            $this->db->where('geopos_invoices.eid', $eid);
        }
        if ($p) {


            if ($this->aauth->get_user()->loc) {
                $this->db->where('geopos_invoices.loc', $this->aauth->get_user()->loc);
            } elseif (!BDATA) {
                $this->db->where('geopos_invoices.loc', 0);
            }
        }
        $this->db->join('geopos_customers', 'geopos_invoices.csd = geopos_customers.id', 'left');
        $this->db->join('geopos_terms', 'geopos_terms.id = geopos_invoices.term', 'left');
        $query = $this->db->get();
        return $query->row_array();
    }

    public function invoice_products($id)
    {

        $this->db->select('*');
        $this->db->from('geopos_invoice_items');
        $this->db->where('tid', $id);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function invoice_products_bfr_post($id)
    {

        $this->db->select('*');
        $this->db->from('geopos_invoice_items_bfr_post');
        $this->db->where('tid', $id);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function items_with_product($id)
    {
        $this->db->select('geopos_invoice_items.*, geopos_products.qty AS alert, geopos_products.code_type AS vattype, geopos_products.product_price AS cost');
        $this->db->from('geopos_invoice_items');
        $this->db->where('geopos_invoice_items.tid', $id); // Ensure the correct table prefix for `tid`
        $this->db->join('geopos_products', 'geopos_products.pid = geopos_invoice_items.pid', 'left');
        $query = $this->db->get();
        return $query->result_array();
    }

    // COMMENTED OUT: Multi-currency support - Now using ONLY geopos_system.currency
    // public function currencies()
    // {
    //     $this->db->select('*');
    //     $this->db->from('geopos_currencies');
    //     $query = $this->db->get();
    //     return $query->result_array();
    // }

    // public function currency_d($id, $loc = 0)
    // {
    //     if ($loc) {
    //         $query = $this->db->query("SELECT cur FROM geopos_locations WHERE id='$loc' LIMIT 1");
    //         $row = $query->row_array();
    //         $id = $row['cur'];
    //     }
    //     $this->db->select('*');
    //     $this->db->from('geopos_currencies');
    //     $this->db->where('id', $id);
    //     $query = $this->db->get();
    //     return $query->row_array();
    // }
    
    // NEW: Get currency from geopos_system
    public function get_system_currency()
    {
        $this->db->select('currency');
        $this->db->from('geopos_system');
        $this->db->where('id', 1);
        $query = $this->db->get();
        $row = $query->row_array();
        return strtoupper($row['currency']);
    }

    public function warehouses()
    {
        $this->db->select('*');
        $this->db->from('geopos_warehouse');
        if ($this->aauth->get_user()->loc) {
            $this->db->where('loc', $this->aauth->get_user()->loc);
            if (BDATA)  $this->db->or_where('loc', 0);
        } elseif (!BDATA) {
            $this->db->where('loc', 0);
        }

        $query = $this->db->get();

        return $query->result_array();
    }

    public function daybook_export()
    {
        $start = date("Y-m-d", strtotime($_POST['hidden_start_date']));
        $end = date("Y-m-d", strtotime($_POST['hidden_end_date']));

        //$sql="select * from geopos_invoices WHERE  (invoicedate BETWEEN '".$start."' AND '".$end." ORDER BY  invoicedate ASC')  ";

        $daypass_check = $_POST['hidden_daypass_check'];
        if ($daypass_check == 1) {
            $sql = "select  geopos_invoices.*,geopos_customers.name from geopos_invoices join geopos_customers on geopos_invoices.csd=geopos_customers.id WHERE  geopos_invoices.invoicedate BETWEEN '" . $start . "' AND '" . $end . "' ORDER BY  geopos_invoices.invoicedate ASC";
        } else {
            $sql = "select  geopos_invoices.*,geopos_customers.name from geopos_invoices join geopos_customers on geopos_invoices.csd=geopos_customers.id WHERE  geopos_invoices.invoicedate BETWEEN '" . $start . "' AND '" . $end . "' and geopos_invoices.tid <> 0 ORDER BY  geopos_invoices.invoicedate ASC   ";
        }











        //  $sql="select  geopos_transactions.id as trasid,geopos_invoices.*,geopos_customers.name from geopos_invoices join geopos_customers on geopos_invoices.csd=geopos_customers.id join geopos_transactions on geopos_invoices.id=geopos_transactions.tid WHERE  (invoicedate BETWEEN '".$start."' AND '".$end." and geopos_transactions.debit=0.00 ORDER BY  geopos_invoices.invoicedate ASC')  ";

        $query = $this->db->query($sql);



        return  $query->result_array();
    }

    public function invoices_export()
    {
        $start = date("Y-m-d", strtotime($_POST['hidden_start_date']));
        $end = date("Y-m-d", strtotime($_POST['hidden_end_date']));

        $note = "Sale Receipt";
        $sql = "SELECT id,acid,type,cat,sum(debit) as debit,sum(credit) as credit, payer,payerid,paymt_method, note,date,created FROM `geopos_transactions` WHERE geopos_transactions.date BETWEEN '" . $start . "' AND '" . $end . "' and note='" . $note . "' GROUP by credit,created,payer ORDER BY `geopos_transactions`.`date` DESC";



        $query = $this->db->query($sql);



        return  $query->result_array();
    }

    public function invoice_transactions($id)
    {

        $this->db->select('*');
        $this->db->from('geopos_transactions');
        $this->db->where('tid', $id);
        $this->db->where('ext', 0);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function invoice_delete($id, $eid = '')
    {
        $this->db->trans_start();
        $this->db->select('tid,total,status,csd');
        $this->db->from('geopos_invoices');
        $this->db->where('id', $id);
        $query = $this->db->get();
        $result = $query->row_array();

        $new_balance = $result['total'];

        // Revert Balance in customer

        //$this->db->query("update geopos_customers SET balance = (balance-$new_balance) WHERE id = $result['csd'] ");

        $this->db->set('balance', "balance-$new_balance", FALSE);
        $this->db->where('id', $result['csd']);
        $this->db->update('geopos_customers');

        // Remove geopos_transactions entry

        //$this->db->delete('geopos_transactions', array('tid' => $id));
        $this->db->where('tid', $id);
        $this->db->where('inv_id', $result['tid']);
        $this->db->delete('geopos_transactions');

        if ($this->aauth->get_user()->loc) {
            if ($eid) {

                $res = $this->db->delete('geopos_invoices', array('id' => $id, 'eid' => $eid, 'loc' => $this->aauth->get_user()->loc));
            } else {
                $res = $this->db->delete('geopos_invoices', array('id' => $id, 'loc' => $this->aauth->get_user()->loc));
            }
        } else {
            if (BDATA) {
                if ($eid) {

                    $res = $this->db->delete('geopos_invoices', array('id' => $id, 'eid' => $eid));
                } else {
                    $res = $this->db->delete('geopos_invoices', array('id' => $id));
                }
            } else {


                if ($eid) {

                    $res = $this->db->delete('geopos_invoices', array('id' => $id, 'eid' => $eid, 'loc' => 0));
                } else {
                    $res = $this->db->delete('geopos_invoices', array('id' => $id, 'loc' => 0));
                }
            }
        }

        $affect = $this->db->affected_rows();

        if ($res) {
            if ($result['status'] != 'canceled') {
                $this->db->select('pid,qty');
                $this->db->from('geopos_invoice_items');
                $this->db->where('tid', $id);
                $query = $this->db->get();
                $prevresult = $query->result_array();

                foreach ($prevresult as $prd) {
                    $amt = $prd['qty'];
                    $this->db->set('qty', "qty+$amt", FALSE);
                    $this->db->where('pid', $prd['pid']);
                    $this->db->update('geopos_products');
                }
            }


            if ($affect) $this->db->delete('geopos_invoice_items', array('tid' => $id));

            $data = array('type' => 9, 'rid' => $id);
            $this->db->delete('geopos_metadata', $data);

            $alert = $this->custom->api_config(66);
            if ($alert['method'] == 1) {
                $this->load->model('communication_model');
                $subject = $result['tid'] . ' ' . $this->lang->line('DELETED');
                $body = $subject . '<br> ' . $this->lang->line('Amount') . ' ' . $result['total'] . '<br> ' . $this->lang->line('Employee') . ' ' . $this->aauth->get_user()->username . '<br> ID# ' . $result['tid'];
                $out = $this->communication_model->send_corn_email($alert['url'], $alert['url'], $subject, $body, false, '');
            }

            if ($this->db->trans_complete()) {
                return true;
            } else {
                return false;
            }
        }
    }


    private function _get_datatables_query_daypass($opt = '')
    {
        $this->db->select('geopos_invoices.id,geopos_invoices.tid,geopos_invoices.csd,geopos_invoices.invoicedate,geopos_invoices.inv_type,geopos_invoices.invoiceduedate,geopos_invoices.total,geopos_invoices.pamnt,geopos_invoices.status,geopos_customers.name,geopos_customers.company,geopos_invoices.print_status');
        $this->db->from($this->table);
        $this->db->where('geopos_invoices.i_class', 0);
        // $this->db->where('geopos_invoices.tid', 0);
        $this->db->where('geopos_invoices.inv_type', 'DAYPASS');
        $this->db->where('geopos_invoices.r_time !=', 'mig');
        if ($opt) {
            $this->db->where('geopos_invoices.eid', $opt);
        }
        if (!$this->aauth->premission(19)) {
            //$this->db->where('geopos_invoices.inv_type !=', 'DAYPASS');
        }

        if ($this->aauth->get_user()->loc) {
            $this->db->where('geopos_invoices.loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('geopos_invoices.loc', 0);
        }
        if ($this->input->post('start_date') && $this->input->post('end_date')) // if datatable send POST for search
        {
            $this->db->where('DATE(geopos_invoices.invoicedate) >=', datefordatabase($this->input->post('start_date')));
            $this->db->where('DATE(geopos_invoices.invoicedate) <=', datefordatabase($this->input->post('end_date')));
        }

        if ($this->input->post('start_cat') && $this->input->post('end_cat')) // if datatable send POST for search
        {
            $this->db->where('DATE(geopos_invoices.invoicedate) >=', datefordatabase($this->input->post('start_date')));
            $this->db->where('DATE(geopos_invoices.invoicedate) <=', datefordatabase($this->input->post('end_date')));
        }

        $this->db->join('geopos_customers', 'geopos_invoices.csd=geopos_customers.id', 'left');

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

    private function _get_datatables_query($opt = '', $include_date = true, $param = '', $due_date = '')
    {
        // Initialize start and end parameters
        $start_param = '';
        $end_param = '';

        // Define common date ranges
        $today = date('Y-m-d');
        $tomorrow = date("Y-m-d", strtotime("+1 day"));
        $first_day_last_month = date('Y-m-01', strtotime('first day of last month'));
        $last_day_last_month = date('Y-m-t', strtotime('last day of last month'));
        $last_day_this_month = date('Y-m-t', strtotime('last day of this month'));

        // Determine the start and end date based on $param
        switch ($param) {
            case 'today':
                $start_param = $today;
                $end_param = $today;
                break;
            case 'month':
                $start_param = date('Y-m-01');
                $end_param = $last_day_this_month;
                break;
            case 'year':
                $start_param = date('Y-01-01');
                $end_param = $today;
                break;
            case 'total':
                $start_param = '';
                $end_param = '';
                break;
            case 'tomorrow':
                $start_param = $tomorrow;
                $end_param = $tomorrow;
                break;
            case 'lastmonth':
                $start_param = $first_day_last_month;
                $end_param = $last_day_last_month;
                break;
        }
        // var_dump($start_param, $end_param);
        // die();
        // Build the query
        $this->db->select('
            geopos_invoices.id,
            geopos_invoices.tid,
            geopos_invoices.tax,
            geopos_invoices.csd,
            geopos_invoices.weight_qty,
            geopos_invoices.invoicedate,
            geopos_invoices.inv_type,
            geopos_invoices.invoiceduedate,
            geopos_invoices.total,
            geopos_invoices.pamnt,
            geopos_invoices.status,
            geopos_customers.name,
            geopos_customers.company,
            geopos_invoices.print_status,
            SUM(geopos_invoice_items.tax * geopos_invoice_items.qty) AS item_tax,
            SUM(geopos_invoice_items.qty) AS item_qty,
            SUM(geopos_invoice_items.price * geopos_invoice_items.qty) AS item_price
        ');
        $this->db->from($this->table);
        $this->db->join('geopos_customers', 'geopos_invoices.csd = geopos_customers.id', 'left');
        $this->db->join('geopos_invoice_items', 'geopos_invoice_items.tid = geopos_invoices.id', 'left');
        $this->db->group_by('geopos_invoices.id'); // Ensure correct aggregation

        // Filter by due date or param
        if ($due_date) {
            $this->db->where('geopos_invoices.status', 'due');
            $this->db->where('DATE(geopos_invoices.invoiceduedate)', $due_date);
        } else {
            $this->db->where_in('geopos_invoices.inv_type', ['INVOICE', 'DAYPASS']);
            $this->db->where('geopos_invoices.i_class', 0);
            $this->db->where('geopos_invoices.r_time !=', 'mig');
            if ($start_param && $end_param) {
                $this->db->where('DATE(geopos_invoices.invoicedate) >=', $start_param);
                $this->db->where('DATE(geopos_invoices.invoicedate) <=', $end_param);
            }
            if ($param == 'date_override') {
                if ($include_date && $this->input->post('start_date') && $this->input->post('end_date')) {
                    $this->db->where('DATE(geopos_invoices.invoicedate) >=', datefordatabase($this->input->post('start_date')));
                    $this->db->where('DATE(geopos_invoices.invoicedate) <=', datefordatabase($this->input->post('end_date')));
                }
            }



            if ($opt) {
                $this->db->where('geopos_invoices.eid', $opt);
            }

            if ($this->aauth->get_user()->loc) {
                $this->db->where('geopos_invoices.loc', $this->aauth->get_user()->loc);
            } elseif (!BDATA) {
                $this->db->where('geopos_invoices.loc', 0);
            }
        }

        // Search functionality
        $i = 0;
        foreach ($this->column_search as $item) {
            if ($this->input->post('search')['value']) {
                if ($i === 0) {
                    $this->db->group_start();
                    $this->db->like($item, $this->input->post('search')['value']);
                } else {
                    $this->db->or_like($item, $this->input->post('search')['value']);
                }
                if (count($this->column_search) - 1 == $i) {
                    $this->db->group_end();
                }
            }
            $i++;
        }

        // Ordering
        if (isset($_POST['order'])) {
            $this->db->order_by($this->column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        } else if (isset($this->order)) {
            $order = $this->order;
            $this->db->order_by(key($order), $order[key($order)]);
        }
    }


    public function get_daily_break_down_sheet($ids)
    {

        $str_arr = explode(",", $ids);


        $data = array();
        foreach ($str_arr as $key => $value) {
            $sql = "select geopos_invoices.total,geopos_invoices.tid,geopos_customers.name,geopos_customers.company
          FROM geopos_invoices JOIN geopos_customers ON geopos_invoices.csd=geopos_customers.id
          WHERE geopos_invoices.id = '" . $value . "'";
            $query = $this->db->query($sql);

            $data = array_merge($data, $query->result_array());
        }
        return $data;
    }



    // public function stock_report_export($ids = '', $upids = '', $categories = '', $end_cat = '')
    // {

    //     $str_arr = explode(",", $ids);
    //     $str_arr_up = explode(",", $upids);

    //     // $categories = explode (",", $categories); 
    //     $start_cat = $categories;

    //     $items = array();

    //     if ($start_cat == "" || $end_cat == "") {
    //         $sql = "SELECT 
    //             sum(ii.subtotal) as subtotal,
    //             sum(ii.totaltax) as totaltax,
    //             ic.title,
    //             ic.id,
    //             ii.product,
    //             ii.product_des,
    //             sum(qty) as qty 
    //         FROM geopos_invoice_items ii
    //         JOIN geopos_product_cat ic ON ii.cat_id = ic.id
    //         WHERE ii.tid IN (" . ($ids == "" ? "-1" : $ids) . ")
    //         GROUP BY ic.title, ii.product;";
    //         $query = $this->db->query($sql);
    //         $data = $query->result_array();

    //         $sql = "SELECT 
    //             sum(ii.subtotal) as subtotal,
    //             sum(ii.totaltax) as totaltax,
    //             ic.title,
    //             ic.id,
    //             ii.product,
    //             ii.product_des,
    //             sum(qty) as qty 
    //         FROM geopos_invoice_items_bfr_post ii
    //         JOIN geopos_product_cat ic ON ii.cat_id = ic.id
    //         WHERE ii.tid IN (" . ($upids == "" ? "-1" : $upids) . ") AND ii.is_posted = 0
    //         GROUP BY ic.title, ii.product;";
    //         $query = $this->db->query($sql);
    //         // $data= $query->result_array();

    //         $data = array_merge($data, $query->result_array());

    //         return $data;
    //     } else {
    //         $sql = "SELECT sum(ii.subtotal) as subtotal,sum(ii.totaltax) as totaltax,
    //         ic.title,
    //         ic.id,
    //         ii.product,
    //         ii.product_des,
    //         sum(qty) as qty 
    //     FROM geopos_invoice_items_bfr_post ii
    //     JOIN geopos_product_cat ic ON ii.cat_id = ic.id
    //     WHERE ii.tid IN (" . $ids . ")
    //     GROUP BY ic.title, ii.product;";
    //         $query = $this->db->query($sql);
    //         return  $query->result_array();
    //     }

    //     $query = $this->db->query($sql);
    //     return  $query->result_array();
    // }
    public function stock_report_export($ids = '', $start_cat = '', $end_cat = '')
    {

        $str_arr = explode(",", $ids);

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
            $sql = "select sum(subtotal) as subtotal,sum(totaltax) as totaltax,product,product_des,sum(qty) as qty from geopos_invoice_items where tid IN (" . $ids . ") group by product";
        } else {
            $sql = "select sum(subtotal) as subtotal,sum(totaltax) as totaltax,product,product_des,sum(qty) as qty from geopos_invoice_items where tid IN  (" . $ids . ") and cat_id between '" . $start_cat . "' and '" . $end_cat . "' group by product";
        }
        $query = $this->db->query($sql);
        //print_r($query->result_array());die();

        return  $query->result_array();
    }

    function get_sa_datatables($opt = '')
    {
        $this->_get_sa_datatables_query($opt);
        if ($_POST['length'] != -1)
            $this->db->limit($_POST['length'], $_POST['start']);
        $query = $this->db->get();
        $this->db->where('geopos_invoices.i_class', 0);
        if ($this->aauth->get_user()->loc) {
            $this->db->where('geopos_invoices.loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('geopos_invoices.loc', 0);
        }

        return $query->result();
    }

    function get_sa_datatables2($opt = '')
    {
        $this->db->reset_query();
        $this->_get_sa_datatables_query2($opt);
        if ($_POST['length'] != -1)
            $this->db->limit($_POST['length'], $_POST['start']);
        $query = $this->db->get();
        if ($this->aauth->get_user()->loc) {
            $this->db->where('geopos_invoices_bfr_post.loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('geopos_invoices_bfr_post.loc', 0);
        }

        return $query->result();
    }

    private function _get_sa_datatables_query($opt = '')
    {

        $this->db->select('geopos_invoices.id,geopos_invoices.tid,geopos_invoices.csd,geopos_invoices.invoicedate,geopos_invoices.invoiceduedate,geopos_invoices.total,geopos_invoices.pamnt,geopos_invoices.status,geopos_customers.name,geopos_customers.company');
        $this->db->from($this->table);
        $this->db->where('geopos_invoices.i_class', 0);
        //$this->db->where('geopos_invoices.status !=', 'paid');
        if ($opt) {
            $this->db->where('geopos_invoices.eid', $opt);
        }
        if ($this->aauth->get_user()->loc) {
            $this->db->where('geopos_invoices.loc', $this->aauth->get_user()->loc);
        }
        if (!$this->aauth->premission(19)) {
            //$this->db->where('geopos_invoices.inv_type !=', 'DAYPASS');
        } elseif (!BDATA) {
            $this->db->where('geopos_invoices.loc', 0);
        }
        if ($this->input->post('start_date') && $this->input->post('end_date')) // if datatable send POST for search
        {
            $this->db->where('DATE(geopos_invoices.invoicedate) >=', datefordatabase($this->input->post('start_date')));
            $this->db->where('DATE(geopos_invoices.invoicedate) <=', datefordatabase($this->input->post('end_date')));
        }

        if ($this->input->post('end_cat')) // if datatable send POST for search
        {

            $cats = $myArray = explode(',', $this->input->post('end_cat'));

            $this->db->where_in('geopos_invoice_items.cat_id.id', $cats);

            $this->db->group_by('geopos_invoices.id');

            // $this->db->where('geopos_invoice_items.cat_id <=', $this->input->post('end_cat'));

            $this->db->join('geopos_invoice_items', 'geopos_invoice_items.tid=geopos_invoices.id', 'left');
        }

        $this->db->join('geopos_customers', 'geopos_invoices.csd=geopos_customers.id', 'left');

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


    private function _get_sa_datatables_query2($opt = '')
    {

        $this->db->select('geopos_invoices_bfr_post.id,geopos_invoices_bfr_post.tid,geopos_invoices_bfr_post.csd,geopos_invoices_bfr_post.invoicedate,geopos_invoices_bfr_post.invoiceduedate,geopos_invoices_bfr_post.total,geopos_invoices_bfr_post.pamnt,geopos_invoices_bfr_post.status,geopos_customers.name,geopos_customers.company');
        $this->db->from('geopos_invoices_bfr_post');
        $this->db->where('geopos_invoices_bfr_post.is_posted', 0);
        //$this->db->where('geopos_invoices.status !=', 'paid');
        if ($opt) {
            $this->db->where('geopos_invoices_bfr_post.eid', $opt);
        }
        if ($this->aauth->get_user()->loc) {
            $this->db->where('geopos_invoices_bfr_post.loc', $this->aauth->get_user()->loc);
        }
        if (!$this->aauth->premission(19)) {
            //$this->db->where('geopos_invoices.inv_type !=', 'DAYPASS');
        } elseif (!BDATA) {
            $this->db->where('geopos_invoices_bfr_post.loc', 0);
        }
        if ($this->input->post('start_date') && $this->input->post('end_date')) // if datatable send POST for search
        {
            $this->db->where('DATE(geopos_invoices_bfr_post.invoicedate) >=', datefordatabase($this->input->post('start_date')));
            $this->db->where('DATE(geopos_invoices_bfr_post.invoicedate) <=', datefordatabase($this->input->post('end_date')));
        }

        if ($this->input->post('end_cat')) // if datatable send POST for search
        {

            $cats = $myArray = explode(',', $this->input->post('end_cat'));

            $this->db->where_in('geopos_invoice_items_bfr_post.cat_id.id', $cats);

            $this->db->group_by('geopos_invoices_bfr_post.id');

            // $this->db->where('geopos_invoice_items.cat_id <=', $this->input->post('end_cat'));

            $this->db->join('geopos_invoice_items_bfr_post', 'geopos_invoice_items_bfr_post.tid=geopos_invoices_bfr_post.id', 'left');
        }

        $this->db->join('geopos_customers', 'geopos_invoices_bfr_post.csd=geopos_customers.id', 'left');

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
            $this->db->order_by("geopos_invoices_bfr_post.tid", $order[key($order)]);
        }
    }


    // Counts matching the same filters as SA list (with and without global search)
    function sa_count_filtered($opt = '')
    {
        $this->_get_sa_datatables_query($opt);
        // Location/base filters are already applied inside the builder
        $query = $this->db->get();
        return $query->num_rows();
    }

    function sa_count_all($opt = '')
    {
        // Build the same base query as _get_sa_datatables_query, but without global search conditions
        $this->db->select('geopos_invoices.id');
        $this->db->from($this->table);
        $this->db->where('geopos_invoices.i_class', 0);
        if ($opt) {
            $this->db->where('geopos_invoices.eid', $opt);
        }
        if ($this->aauth->get_user()->loc) {
            $this->db->where('geopos_invoices.loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('geopos_invoices.loc', 0);
        }

        if ($this->input->post('start_date') && $this->input->post('end_date')) {
            $this->db->where('DATE(geopos_invoices.invoicedate) >=', datefordatabase($this->input->post('start_date')));
            $this->db->where('DATE(geopos_invoices.invoicedate) <=', datefordatabase($this->input->post('end_date')));
        }

        if ($this->input->post('start_cat') && $this->input->post('end_cat')) {
            $this->db->join('geopos_invoice_items', 'geopos_invoice_items.tid=geopos_invoices.id', 'left');
            $this->db->where('geopos_invoice_items.cat_id >=', $this->input->post('start_cat'));
            $this->db->where('geopos_invoice_items.cat_id <=', $this->input->post('end_cat'));
            $this->db->group_by('geopos_invoices.id');
        }

        return $this->db->count_all_results();
    }

    function bk_count_filtered($opt = '')
    {
        $this->bk_get_datatables_query($opt);
        if ($opt) {
            $this->db->where('eid', $opt);
        }
        if ($this->aauth->get_user()->loc) {
            $this->db->where('geopos_invoices.loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('geopos_invoices.loc', 0);
        }
        $query = $this->db->get();
        return $query->num_rows();
    }


    private function bk_get_datatables_query($opt = '')
    {

        $this->db->select('geopos_invoices.id,geopos_invoices.tid,geopos_invoices.csd,geopos_invoices.invoicedate,geopos_invoices.invoiceduedate,geopos_invoices.total,   geopos_invoices.pamnt,geopos_invoices.status,geopos_customers.name,geopos_customers.company');
        $this->db->from($this->table);
        $this->db->where('geopos_invoices.i_class', 0);
        //$this->db->where('geopos_invoices.status !=', 'paid');
        if ($opt) {
            $this->db->where('geopos_invoices.eid', $opt);
        }
        if (!$this->aauth->premission(19)) {
            //$this->db->where('geopos_invoices.inv_type !=', 'DAYPASS');
        }
        if ($this->aauth->get_user()->loc) {
            $this->db->where('geopos_invoices.loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('geopos_invoices.loc', 0);
        }
        if ($this->input->post('start_date') && $this->input->post('end_date')) // if datatable send POST for search
        {
            $this->db->where('DATE(geopos_invoices.invoicedate) >=', datefordatabase($this->input->post('start_date')));
            $this->db->where('DATE(geopos_invoices.invoicedate) <=', datefordatabase($this->input->post('end_date')));
        }

        if ($this->input->post('start_cat') && $this->input->post('end_cat')) // if datatable send POST for search
        {
            $this->db->where('DATE(geopos_invoices.invoicedate) >=', datefordatabase($this->input->post('start_date')));
            $this->db->where('DATE(geopos_invoices.invoicedate) <=', datefordatabase($this->input->post('end_date')));
        }

        $this->db->join('geopos_customers', 'geopos_invoices.csd=geopos_customers.id', 'left');

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

    function bk_get_datatables($opt = '')
    {

        $this->bk_get_datatables_query($opt);
        if ($_POST['length'] != -1)
            $this->db->limit($_POST['length'], $_POST['start']);
        $query = $this->db->get();
        $this->db->where('geopos_invoices.i_class', 0);
        if ($this->aauth->get_user()->loc) {
            $this->db->where('geopos_invoices.loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('geopos_invoices.loc', 0);
        }

        return $query->result();
    }


    function get_datatables_bfr_post($opt = '', $expression = '', $expression2 = '', $value = '', $value2 = '', $joinQuery = '', $invoices = [], $driver = '')
    {
        // Ensure $invoices is an array
        if (!is_array($invoices)) {
            $invoices = [];
        }
        
        $this->_get_datatables_query_bfr_post($opt, !($expression != "" && $expression2 != "" && $value != "0" && $value2 != "0" || count($invoices) > 0));

        if (count($invoices) > 0) {
            $this->db->where_in('tid', $invoices);
        } else if ($expression != "" && $expression2 != "" && $value != "0" && $value2 != "0") {
            $this->db->where($expression, $value);
            if ($joinQuery == " and ") {
                $this->db->where($expression2, $value2);
            } else if ($joinQuery == " or ") {
                $this->db->or_where($expression2, $value2);
            }
        }
        if ($driver != "") {
            $this->db->like('driver_name', $driver, 'both');
        }

        if ($_POST['length'] != -1)
            $this->db->limit($_POST['length'], $_POST['start']);
        $query = $this->db->get();

        // var_dump($opt, $expression, $expression2, $value, $value2, $joinQuery, $this->db->last_query()); exit;

        // $this->db->where('geopos_invoices_bfr_post.i_class', 0); 

        if ($this->aauth->get_user()->loc) {
            $this->db->where('geopos_invoices_bfr_post.loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('geopos_invoices_bfr_post.loc', 0);
        }
        // var_dump($this->db->last_query());exit();

        return $query->result();
    }


    public function get_datatables($opt = '', $expression = '', $expression2 = '', $value = '', $value2 = '', $joinQuery = '', $invoices = [], $driver = '', $param = '', $due_date = '')
    {
        // Ensure $invoices is an array
        if (!is_array($invoices)) {
            $invoices = [];
        }
        
        // Build query
        $this->_get_datatables_query(
            $opt,
            !($expression != "" && $expression2 != "" && $value != "0" && $value2 != "0" || count($invoices) > 0),
            $param,
            $due_date
        );

        // Apply filters
        if (count($invoices) > 0) {
            $this->db->where_in('geopos_invoices.tid', $invoices);
        } elseif ($expression != "" && $expression2 != "" && $value != "0" && $value2 != "0") {
            $this->db->where($expression, $value);
            if ($joinQuery == " and ") {
                $this->db->where($expression2, $value2);
            } elseif ($joinQuery == " or ") {
                $this->db->or_where($expression2, $value2);
            }
        }

        if ($driver != "") {
            $this->db->like('geopos_invoices.driver_name', $driver, 'both');
        }

        // Location filter
        if ($this->aauth->get_user()->loc) {
            $this->db->where('geopos_invoices.loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('geopos_invoices.loc', 0);
        }

        // Limit for pagination (clamped)
        $defaultLength = 25;
        $maxLength = 100;
        $postLength = isset($_POST['length']) ? (int)$_POST['length'] : $defaultLength;
        $postStart = isset($_POST['start']) ? (int)$_POST['start'] : 0;
        if ($postLength === -1) {
            $length = -1; // All
        } else {
            $length = $postLength > 0 ? $postLength : $defaultLength;
            $length = ($length > $maxLength) ? $maxLength : $length;
        }
        $start = $postStart >= 0 ? $postStart : 0;
        if ($length != -1) {
            $this->db->limit($length, $start);
        }

        $query = $this->db->get();
        return $query->result();
    }

    public function count_filtered_with_params($opt = '', $expression = '', $expression2 = '', $value = '', $value2 = '', $joinQuery = '', $invoices = [], $driver = '', $param = '', $due_date = '')
    {
        // Ensure $invoices is an array
        if (!is_array($invoices)) {
            $invoices = [];
        }
        
        $this->_get_datatables_query(
            $opt,
            !($expression != "" && $expression2 != "" && $value != "0" && $value2 != "0" || count($invoices) > 0),
            $param,
            $due_date
        );

        if (count($invoices) > 0) {
            $this->db->where_in('geopos_invoices.tid', $invoices);
        } elseif ($expression != "" && $expression2 != "" && $value != "0" && $value2 != "0") {
            $this->db->where($expression, $value);
            if ($joinQuery == " and ") {
                $this->db->where($expression2, $value2);
            } elseif ($joinQuery == " or ") {
                $this->db->or_where($expression2, $value2);
            }
        }

        if ($driver != "") {
            $this->db->like('geopos_invoices.driver_name', $driver, 'both');
        }

        if ($this->aauth->get_user()->loc) {
            $this->db->where('geopos_invoices.loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('geopos_invoices.loc', 0);
        }

        return $this->db->count_all_results();
    }

    // Lightweight queries for Merge And Print Invoices page
    private function _build_print_base_query()
    {
        $this->db->select('
            geopos_invoices.id,
            geopos_invoices.tid,
            geopos_invoices.invoicedate,
            geopos_invoices.inv_type,
            geopos_invoices.total,
            geopos_invoices.status,
            geopos_customers.name,
            geopos_customers.company,
            SUM(geopos_invoice_items.price * geopos_invoice_items.qty) AS item_price
        ');
        $this->db->from('geopos_invoices');
        $this->db->join('geopos_customers', 'geopos_invoices.csd = geopos_customers.id', 'left');
        $this->db->join('geopos_invoice_items', 'geopos_invoice_items.tid = geopos_invoices.id', 'left');

        $this->db->where('geopos_invoices.i_class', 0);
        $this->db->where('geopos_invoices.r_time !=', 'mig');
        $this->db->where_in('geopos_invoices.inv_type', ['INVOICE', 'DAYPASS']);

        // Location filter
        if ($this->aauth->get_user()->loc) {
            $this->db->where('geopos_invoices.loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('geopos_invoices.loc', 0);
        }

        // Date range (from view post)
        if ($this->input->post('start_date') && $this->input->post('end_date')) {
            $this->db->where('DATE(geopos_invoices.invoicedate) >=', datefordatabase($this->input->post('start_date')));
            $this->db->where('DATE(geopos_invoices.invoicedate) <=', datefordatabase($this->input->post('end_date')));
        }

        // Group by invoice for aggregate item_price
        $this->db->group_by('geopos_invoices.id');

        // Global search across a few indexed/light columns
        $searchValue = $this->input->post('search')['value'] ?? '';
        if ($searchValue !== '') {
            $this->db->group_start();
            $this->db->like('geopos_invoices.tid', $searchValue);
            $this->db->or_like('geopos_customers.company', $searchValue);
            $this->db->or_like('geopos_customers.name', $searchValue);
            $this->db->or_like('geopos_invoices.inv_type', $searchValue);
            $this->db->group_end();
        }
    }

    public function get_datatables_for_print()
    {
        $this->db->reset_query();
        $this->_build_print_base_query();
        $this->db->where('geopos_invoices.status', 'due');
        $this->db->order_by('geopos_invoices.invoicedate', 'DESC');


        // Ordering
        if (isset($_POST['order'])) {
            $columnIndex = $_POST['order']['0']['column'];
            $dir = $_POST['order']['0']['dir'];
            $columns = [
                'geopos_invoices.tid',
                'geopos_customers.company',
                'geopos_customers.name',
                'geopos_invoices.inv_type',
                'geopos_invoices.invoicedate',
                'SUM(geopos_invoice_items.price * geopos_invoice_items.qty) AS item_price',
                'geopos_invoices.total',
                'geopos_invoices.status'
            ];
            if (isset($columns[$columnIndex])) {
                $this->db->order_by($columns[$columnIndex], $dir);
            }
        }

        // Pagination
        if (isset($_POST['length']) && $_POST['length'] != -1) {
            $this->db->limit($_POST['length'], $_POST['start']);
        }

        $query = $this->db->get();
        return $query->result();
    }

    public function count_filtered_for_print()
    {
        $this->db->reset_query();
        $this->_build_print_base_query();
        $this->db->where('geopos_invoices.status', 'due');
        return $this->db->count_all_results();
    }

    public function count_all_for_print()
    {
        $this->db->reset_query();
        $this->_build_print_base_query();
        $this->db->where('geopos_invoices.status', 'due');
        // Remove date filter for total count if DataTables expects overall total
        if ($this->input->post('start_date') && $this->input->post('end_date')) {
            // rebuild without date conditions
            $this->db->reset_query();
            $this->db->select('geopos_invoices.id');
            $this->db->from('geopos_invoices');
            $this->db->where('geopos_invoices.i_class', 0);
            $this->db->where('geopos_invoices.r_time !=', 'mig');
            $this->db->where_in('geopos_invoices.inv_type', ['INVOICE', 'DAYPASS']);
            $this->db->where('geopos_invoices.status', 'due');
            if ($this->aauth->get_user()->loc) {
                $this->db->where('geopos_invoices.loc', $this->aauth->get_user()->loc);
            } elseif (!BDATA) {
                $this->db->where('geopos_invoices.loc', 0);
            }
        }
        return $this->db->count_all_results();
    }

    // Dedicated helpers for Customer Invoices DataTable (same behavior as *for_print)
    public function get_customer_invoices_datatables()
    {
        $this->db->reset_query();
        $this->_build_print_base_query();

        if (isset($_POST['order'])) {
            $columnIndex = $_POST['order']['0']['column'];
            $dir = $_POST['order']['0']['dir'];
            $columns = ['geopos_invoices.tid', 'geopos_customers.company', 'geopos_customers.name', 'geopos_invoices.inv_type', 'geopos_invoices.invoicedate', 'geopos_invoices.total', 'geopos_invoices.status'];
            if (isset($columns[$columnIndex])) {
                $this->db->order_by($columns[$columnIndex], $dir);
            }
        } else {
            $this->db->order_by('geopos_invoices.invoicedate', 'DESC');
        }

        if (isset($_POST['length']) && $_POST['length'] != -1) {
            $this->db->limit($_POST['length'], $_POST['start']);
        }

        $query = $this->db->get();
        return $query->result();
    }

    public function count_filtered_customer_invoices()
    {
        $this->db->reset_query();
        $this->_build_print_base_query();
        return $this->db->count_all_results();
    }

    public function count_all_customer_invoices()
    {
        $this->db->reset_query();
        $this->_build_print_base_query();
        if ($this->input->post('start_date') && $this->input->post('end_date')) {
            $this->db->reset_query();
            $this->db->select('geopos_invoices.id');
            $this->db->from('geopos_invoices');
            $this->db->where('geopos_invoices.i_class', 0);
            $this->db->where('geopos_invoices.r_time !=', 'mig');
            $this->db->where_in('geopos_invoices.inv_type', ['INVOICE', 'DAYPASS']);
            if ($this->aauth->get_user()->loc) {
                $this->db->where('geopos_invoices.loc', $this->aauth->get_user()->loc);
            } elseif (!BDATA) {
                $this->db->where('geopos_invoices.loc', 0);
            }
        }
        return $this->db->count_all_results();
    }


    function get_datatables_daypass($opt = '')
    {
        $this->_get_datatables_query_daypass($opt);
        if ($_POST['length'] != -1)
            $this->db->limit($_POST['length'], $_POST['start']);
        $query = $this->db->get();
        $this->db->where('geopos_invoices.i_class', 0);

        if ($this->aauth->get_user()->loc) {
            $this->db->where('geopos_invoices.loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('geopos_invoices.loc', 0);
        }

        return $query->result();
    }



    private function _get_datatables_query_bfr_post($opt = '', $include_date = true)
    {
        $this->db->select('geopos_invoices_bfr_post.id,geopos_invoices_bfr_post.tid,geopos_invoices_bfr_post.tax,geopos_invoices_bfr_post.csd,geopos_invoices_bfr_post.invoicedate,geopos_invoices_bfr_post.inv_type,geopos_invoices_bfr_post.invoiceduedate,geopos_invoices_bfr_post.total,geopos_invoices_bfr_post.pamnt,geopos_invoices_bfr_post.weight_qty,geopos_invoices_bfr_post.status,geopos_customers.name,geopos_customers.company,geopos_invoices_bfr_post.print_status');
        $this->db->from('geopos_invoices_bfr_post');
        $this->db->where('geopos_invoices_bfr_post.i_class', 0);
        $this->db->where('geopos_invoices_bfr_post.r_time !=', 'mig');

        if ($opt) {
            $this->db->where('geopos_invoices_bfr_post.eid', $opt);
        }
        if (!$this->aauth->premission(19)) {
            //$this->db->where('geopos_invoices.inv_type !=', 'DAYPASS');
        }

        if ($this->aauth->get_user()->loc) {
            $this->db->where('geopos_invoices_bfr_post.loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('geopos_invoices_bfr_post.loc', 0);
        }
        if ($this->input->post('start_date') && $this->input->post('end_date') && $include_date) // if datatable send POST for search
        {
            $this->db->where('DATE(geopos_invoices_bfr_post.invoicedate) >=', datefordatabase($this->input->post('start_date')));
            $this->db->where('DATE(geopos_invoices_bfr_post.invoicedate) <=', datefordatabase($this->input->post('end_date')));
        }
        // if(isset($_POST['post_invoices']) && $_POST['post_invoices'] == '1'){

        $this->db->where('geopos_invoices_bfr_post.is_posted =', 0);

        // }
        if ($this->input->post('start_cat') && $this->input->post('end_cat') && $include_date) // if datatable send POST for search
        {
            $this->db->where('DATE(geopos_invoices_bfr_post.invoicedate) >=', datefordatabase($this->input->post('start_date')));
            $this->db->where('DATE(geopos_invoices_bfr_post.invoicedate) <=', datefordatabase($this->input->post('end_date')));
        }

        $this->db->join('geopos_customers', 'geopos_invoices_bfr_post.csd=geopos_customers.id', 'left');

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
        //     var_dump();exit();
        if (isset($_POST['order'])) // here order processing
        {
            $this->db->order_by($this->column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        } else if (isset($this->order)) {
            $order = $this->order;
            $this->db->order_by('geopos_invoices_bfr_post.tid', $order[key($order)]);
        }
    }


    function get_datatables_for_merged($opt = '')
    {
        $this->_get_datatables_query_for_merged($opt);
        if ($_POST['length'] != -1)
            $this->db->limit($_POST['length'], $_POST['start']);
        $query = $this->db->get();
        $this->db->where('geopos_invoices.i_class', 0);
        if ($this->aauth->get_user()->loc) {
            $this->db->where('geopos_invoices.loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('geopos_invoices.loc', 0);
        }
        return $query->result();
    }

    private function _get_datatables_query_for_merged($opt = '')
    {
        $this->db->select('geopos_invoices.id,geopos_invoices.tid,geopos_invoices.csd,geopos_invoices.invoicedate,geopos_invoices.inv_type,geopos_invoices.invoiceduedate,geopos_invoices.weight_qty,geopos_invoices.total,geopos_invoices.pamnt,geopos_invoices.status,geopos_customers.name,geopos_customers.company');
        $this->db->from($this->table);
        $this->db->where('geopos_invoices.i_class', 0);
        $this->db->where('geopos_invoices.r_time !=', 'mig');
        // $this->db->where('geopos_invoices.tid !=', 0);
        if ($opt) {
            $this->db->where('geopos_invoices.eid', $opt);
        }
        if (!$this->aauth->premission(19)) {
            //$this->db->where('geopos_invoices.inv_type !=', 'DAYPASS');
        }

        if ($this->aauth->get_user()->loc) {
            $this->db->where('geopos_invoices.loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('geopos_invoices.loc', 0);
        }
        if ($this->input->post('start_date') && $this->input->post('end_date')) // if datatable send POST for search
        {
            $this->db->where('DATE(geopos_invoices.invoicedate) >=', datefordatabase($this->input->post('start_date')));
            $this->db->where('DATE(geopos_invoices.invoicedate) <=', datefordatabase($this->input->post('end_date')));
        }

        if ($this->input->post('start_cat') && $this->input->post('end_cat')) // if datatable send POST for search
        {
            $this->db->where('DATE(geopos_invoices.invoicedate) >=', datefordatabase($this->input->post('start_date')));
            $this->db->where('DATE(geopos_invoices.invoicedate) <=', datefordatabase($this->input->post('end_date')));
        }

        if ($this->input->post('customer_id')) {
            $this->db->where('geopos_invoices.csd', $this->input->post('customer_id'));
        }

        $this->db->join('geopos_customers', 'geopos_invoices.csd=geopos_customers.id', 'left');

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





    private function _get_rec_datatables_query($opt = '')
    {
        $this->db->select('geopos_invoices.id,geopos_invoices.tid,geopos_invoices.inv_type,geopos_invoices.csd,geopos_invoices.invoicedate,geopos_invoices.invoiceduedate,geopos_invoices.total,geopos_invoices.pamnt,geopos_invoices.status,geopos_invoices.tax,geopos_customers.name');
        $this->db->from($this->table);
        $this->db->where('geopos_invoices.i_class', 0);
        $this->db->where('geopos_invoices.status !=', 'paid');
        $this->db->order_by('geopos_invoices.invoicedate', 'ASC');
        if (!$this->aauth->premission(19)) {
            //$this->db->where('geopos_invoices.inv_type !=', 'DAYPASS');
        }

        $check_search = $_POST['id'];
        if (empty($check_search)) {
            $date = new DateTime("now");
            $curr_date = $date->format('Y-m-d ');
            // $this->db->where('DATE(geopos_invoices.invoicedate) ',$curr_date);
        }
        if ($opt) {
            $this->db->where('geopos_invoices.csd', $opt);
        }
        if ($this->aauth->get_user()->loc) {
            $this->db->where('geopos_invoices.loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('geopos_invoices.loc', 0);
        }
        if ($this->input->post('start_date') && $this->input->post('end_date')) // if datatable send POST for search
        {
            $this->db->where('DATE(geopos_invoices.invoicedate) >=', datefordatabase($this->input->post('start_date')));
            $this->db->where('DATE(geopos_invoices.invoicedate) <=', datefordatabase($this->input->post('end_date')));
        }
        $this->db->join('geopos_customers', 'geopos_invoices.csd=geopos_customers.id', 'left');

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




    private function _get_invoice_datatables_query($id)
    {
        $this->db->select('geopos_invoice_items.*');
        $this->db->from('geopos_invoice_items');
        //        $this->db->where('geopos_invoices.i_class', 0);
        $this->db->where('geopos_invoice_items.tid', $id);
    }

    public function get_invoice_items_datatables($invoiceId)
    {
        $this->_get_invoice_datatables_query($invoiceId);
        if (isset($_POST['length']) && $_POST['length'] != -1) {
            $this->db->limit((int)$_POST['length'], (int)$_POST['start']);
        }
        $query = $this->db->get();
        return $query->result();
    }

    public function count_invoice_items_filtered($invoiceId)
    {
        $this->_get_invoice_datatables_query($invoiceId);
        return $this->db->count_all_results();
    }

    public function count_invoice_items_all($invoiceId)
    {
        $this->db->from('geopos_invoice_items');
        $this->db->where('geopos_invoice_items.tid', $invoiceId);
        return $this->db->count_all_results();
    }

    function get_rec_datatables($opt = '')
    {
        $this->_get_rec_datatables_query($opt);
        if ($_POST['length'] != -1)
            $this->db->limit($_POST['length'], $_POST['start']);
        $query = $this->db->get();
        $this->db->where('geopos_invoices.i_class', 0);
        if ($this->aauth->get_user()->loc) {
            $this->db->where('geopos_invoices.loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('geopos_invoices.loc', 0);
        }

        return $query->result();
    }

    function get_invoice_datatables($id)
    {
        $this->_get_invoice_datatables_query($id);

        $query = $this->db->get();
        $this->db->where('geopos_invoices.i_class', 0);
        if ($this->aauth->get_user()->loc) {
            $this->db->where('geopos_invoices.loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('geopos_invoices.loc', 0);
        }

        return $query->result();
    }

    function count_filtered($opt = '')
    {
        $this->_get_datatables_query($opt);
        if ($opt) {
            $this->db->where('eid', $opt);
        }
        if ($this->aauth->get_user()->loc) {
            $this->db->where('geopos_invoices.loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('geopos_invoices.loc', 0);
        }
        $query = $this->db->get();
        return $query->num_rows();
    }

    public function count_all($opt = '')
    {
        $this->db->select('geopos_invoices.id');
        $this->db->from($this->table);
        $this->db->where('geopos_invoices.i_class', 0);
        if ($opt) {
            $this->db->where('geopos_invoices.eid', $opt);
        }
        if ($this->aauth->get_user()->loc) {
            $this->db->where('geopos_invoices.loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('geopos_invoices.loc', 0);
        }
        return $this->db->count_all_results();
    }

    // Count methods for bfr_post table
    function count_filtered_bfr_post($opt = '')
    {
        $this->_get_datatables_query_bfr_post($opt);
        if ($opt) {
            $this->db->where('geopos_invoices_bfr_post.eid', $opt);
        }
        if ($this->aauth->get_user()->loc) {
            $this->db->where('geopos_invoices_bfr_post.loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('geopos_invoices_bfr_post.loc', 0);
        }
        $query = $this->db->get();
        return $query->num_rows();
    }

    public function count_all_bfr_post($opt = '')
    {
        $this->db->select('geopos_invoices_bfr_post.id');
        $this->db->from('geopos_invoices_bfr_post');
        $this->db->where('geopos_invoices_bfr_post.i_class', 0);
        $this->db->where('geopos_invoices_bfr_post.r_time !=', 'mig');
        $this->db->where('geopos_invoices_bfr_post.is_posted', 0);
        if ($opt) {
            $this->db->where('geopos_invoices_bfr_post.eid', $opt);
        }
        if ($this->aauth->get_user()->loc) {
            $this->db->where('geopos_invoices_bfr_post.loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('geopos_invoices_bfr_post.loc', 0);
        }
        return $this->db->count_all_results();
    }


    public function billingterms()
    {
        $this->db->select('id,title');
        $this->db->from('geopos_terms');
        $this->db->where('type', 1);
        $this->db->or_where('type', 0);
        $query = $this->db->get();
        return $query->result_array();
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


    public function employee_bfr_post($id)
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
        $this->db->where('geopos_metadata.type', 1);
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

    public function gateway_list($enable = '')
    {
        $this->db->from('geopos_gateways');
        if ($enable == 'Yes') {
            $this->db->where('enable', 'Yes');
        }
        $query = $this->db->get();
        return $query->result_array();
    }
}

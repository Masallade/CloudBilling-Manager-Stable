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

class Customers_model extends CI_Model
{

    var $table = 'geopos_customers';
    // var $column_order = array(null, 'geopos_customers.name', 'geopos_customers.address', 'geopos_customers.email', 'geopos_customers.phone', null);
    var $column_order = array(null, 'geopos_customers.name', 'geopos_customers.company', 'geopos_customers.phone', null);
    var $column_search = array('geopos_customers.name', 'geopos_customers.company', 'geopos_customers.phone', 'geopos_customers.balance', 'geopos_customers.address', 'geopos_customers.city', 'geopos_customers.email', 'geopos_customers.docid', 'geopos_customers.postbox');
    // var $column_order = array(null, 'geopos_customers.name', 'geopos_customers.address', 'geopos_customers.balance', 'geopos_customers.phone', null);
    // var $column_search = array(null,'geopos_customers.name', 'geopos_customers.address', 'geopos_customers.balance', 'geopos_customers.phone', null);

    var $trans_column_order = array('id', 'date', 'tid', 'note', 'balance', 'credit', 'debit');
    var $trans_column_search = array('id', 'date', 'tid', 'note', 'balance', 'credit', 'debit');
    var $inv_column_order = array('geopos_invoices.tid', 'geopos_invoices.invoicedate', 'geopos_invoices.total', 'geopos_invoices.status', null);
    var $inv_column_search = array('geopos_invoices.tid', 'geopos_invoices.invoicedate', 'geopos_invoices.total', 'geopos_invoices.status');
    var $order = array('id' => 'desc');
    var $inv_order = array('geopos_invoices.tid' => 'desc');
    var $qto_order = array('geopos_quotes.tid' => 'desc');
    var $notecolumn_order = array(null, 'title', 'cdate', null);
    var $notecolumn_search = array('id', 'title', 'cdate');
    var $pcolumn_order = array('geopos_projects.status', 'geopos_projects.name', 'geopos_projects.edate', 'geopos_projects.worth', null);
    var $pcolumn_search = array('geopos_projects.name', 'geopos_projects.edate', 'geopos_projects.status');
    var $ptcolumn_order = array('status', 'name', 'duedate', 'start', null, null);
    var $ptcolumn_search = array('name', 'edate', 'status');
    var $porder = array('id' => 'desc');

    // code change by babar for group customer pricing start

    public function get_customer_by_id($customer_id)
    {
        $this->db->where('id', $customer_id);
        $query = $this->db->get('geopos_customers');
        return $query->row_array(); // Return customer data as an associative array
    }

    public function get_product_by_id($product_id)
    {
        $this->db->where('pid', $product_id);
        $query = $this->db->get('geopos_products');
        return $query->row_array(); // Return product data as an associative array
    }


    // code change by babar for group customer pricing end

    private function _get_datatables_query($id = '')
    {
        $due = $this->input->post('due');
        if ($due) {

            $this->db->select('geopos_customers.*,SUM(geopos_invoices.total) AS total,SUM(geopos_invoices.pamnt) AS pamnt');
            $this->db->from('geopos_invoices');
            $this->db->where('geopos_invoices.status!=', 'paid');
            $this->db->join('geopos_customers', 'geopos_customers.id = geopos_invoices.csd', 'left');


            // $this->db->select('SELECT sum(geopos_invoices.total)-sum(geopos_invoices.pamnt) as "cust_balance",geopos_customers.*,SUM(geopos_invoices.total) AS total,SUM(geopos_invoices.pamnt) AS pamnt');
            // $this->db->from('geopos_invoices');
            // $this->db->where('geopos_invoices.status!=', 'paid');
            // $this->db->join('geopos_customers', 'geopos_customers.id = geopos_invoices.csd', 'left');


            // $sql='SELECT sum(total)-sum(pamnt) as "cust_balance" FROM geopos_invoices WHERE status="due" and csd="'.$customers->id.'" or status="partial" and csd="'.$customers->id.'"'; 




            if ($this->aauth->get_user()->loc) {
                $this->db->where('geopos_customers.loc', $this->aauth->get_user()->loc);
            } elseif (!BDATA) {
                $this->db->where('geopos_customers.loc', 0);
            }
            if ($id != '') {
                $this->db->where('geopos_customers.gid', $id);
            }
            $this->db->group_by('geopos_invoices.csd');
            $this->db->order_by('total', 'desc');
        } else {
            // Base customers with computed fields to avoid N+1 queries in controller
            $this->db->select(
                "{$this->table}.*,
                 (SELECT COALESCE(SUM(total) - SUM(pamnt), 0)
                  FROM geopos_invoices gi
                  WHERE gi.csd = {$this->table}.id AND gi.status IN('due','partial')) AS cust_balance,
                 (SELECT MAX(invoicedate)
                  FROM geopos_invoices gi2
                  WHERE gi2.csd = {$this->table}.id) AS last_invoicedate"
            );
            $this->db->from($this->table);
            if ($this->aauth->get_user()->loc) {
                $this->db->where('loc', $this->aauth->get_user()->loc);
            } elseif (!BDATA) {
                $this->db->where('loc', 0);
            }
            if ($id != '') {
                $this->db->where('gid', $id);
            }
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
                    $this->db->like("replace($item,' ','')", $value);
                } else {
                    $this->db->or_like($item, $value);
                    $this->db->or_like("replace($item,' ','')", $value);
                }

                if (count($this->column_search) - 1 == $i) //last loop
                    $this->db->group_end(); //close bracket
            }
            $i++;
        }
        $search = $this->input->post('order');
        if ($search) // here order processing
        {
            //    print_r($this->column_order[$search['0']['column']]);die();
            $this->db->order_by($this->column_order[$search['0']['column']], $search['0']['dir']);
        } else if (isset($this->order)) {

            $order = $this->order;
            $this->db->order_by(key($order), $order[key($order)]);
        }
    }


    private function _get_pricedatatables_query($id = '')
    {
        $this->db->from('geopos_customer_pricing');
        if ($this->aauth->get_user()->loc) {
            $this->db->where('loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('loc', 0);
        }
        if (($id != '') || ($id  != 0)) {
            $this->db->where('custid', $id);
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



    private function _get_cpricedatatables_query($id = '')
    {
        $this->db->from('geopos_customer_pricing');
        if ($this->aauth->get_user()->loc) {
            $this->db->where('loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('loc', 0);
        }
        if (($id != '') || ($id  != 0)) {
            $this->db->where('custid', $id);
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

    public function get_all_customers()
    {
        // Use caching for customers list
        $cache_key = 'all_customers_' . $this->aauth->get_user()->loc;
        $this->load->driver('cache');
        
        $customers = $this->cache->get($cache_key);
        
        if ($customers === FALSE) {
            $this->db->select('id,name,postbox,name_s');
            $this->db->from('geopos_customers');
            if ($this->aauth->get_user()->loc) {
                $this->db->where('loc', $this->aauth->get_user()->loc);
            } elseif (!BDATA) {
                $this->db->where('loc', 0);
            }
            $this->db->order_by('name', 'ASC');
            $this->db->limit(50); // Limit to 50 customers for performance
            $query = $this->db->get();
            $customers = $query->result_array();
            
            // Cache for 5 minutes
            $this->cache->save($cache_key, $customers, 300);
        }
        
        return $customers;
    }

    function get_datatables($id = '')
    {
        $this->_get_datatables_query($id);
        if ($this->aauth->get_user()->loc) {
            // $this->db->where('loc', $this->aauth->get_user()->loc);
        }
        if ($this->input->post('length') != -1)
            $this->db->limit($this->input->post('length'), $this->input->post('start'));
        $query = $this->db->get();
        return $query->result();
    }

    function get_price_datatables($id = '')
    {
        $this->_get_pricedatatables_query($id);
        if ($this->aauth->get_user()->loc) {
            // $this->db->where('loc', $this->aauth->get_user()->loc);
        }
        if ($this->input->post('length') != -1)
            $this->db->limit($this->input->post('length'), $this->input->post('start'));
        $query = $this->db->get();
        return $query->result();
    }

    public function get_cprice_datatables($id)
    {
        $start = (int) $this->input->post('start');
        $length = (int) $this->input->post('length');

        // Ensure $id is safely formatted for SQL IN clause
        if (is_array($id)) {
            $id = implode(',', array_map('intval', $id));
        } else {
            $id = intval($id);
        }

        $sql = "
            SELECT 
                i.product AS productcode,
                i.code,
                i.price AS salesprice,
                i.product_des AS description,
                i.added_on AS added_on,
                i.id,
                p.fproduct_price AS old_price,
                c.name AS customer_name,
                c.company AS customer_company
            FROM 
                geopos_invoice_items i
            INNER JOIN (
                SELECT 
                    product, 
                    MAX(tid) AS max_tid
                FROM 
                    geopos_invoice_items
                WHERE 
                    cid IN ($id)
                GROUP BY 
                    product
            ) t 
                ON i.product = t.product AND i.tid = t.max_tid
            INNER JOIN 
                geopos_customers c 
                ON i.cid = c.id
            LEFT JOIN 
                geopos_products p 
                ON i.product = p.product_code
            WHERE 
                i.cid IN ($id)
            ORDER BY 
                i.tid DESC
        ";

        // Only add LIMIT when length is not -1 (DataTables uses -1 to mean "all")
        if ($length !== -1) {
            // sanitize start/length and append directly
            $sql .= " LIMIT " . intval($start) . ", " . intval($length);
        }

        $query = $this->db->query($sql);
        return $query->result();
    }

    public function get_cprice_datatables_()
    {
        $start = (int) $this->input->post('start');
        $length = (int) $this->input->post('length');

        $sql = "
            SELECT 
                i.product AS productcode,
                i.code,
                i.price AS salesprice,
                i.product_des AS description,
                i.added_on AS added_on,
                p.fproduct_price AS old_price,
                i.id,
                c.name AS customer_name,
                c.company AS customer_company
            FROM 
                geopos_invoice_items i
            INNER JOIN (
                SELECT 
                    product, 
                    MAX(tid) AS max_tid
                FROM 
                    geopos_invoice_items
                GROUP BY 
                    product
            ) t 
                ON i.product = t.product AND i.tid = t.max_tid
            INNER JOIN 
                geopos_customers c 
                ON i.cid = c.id
            LEFT JOIN 
                geopos_products p 
                ON i.product = p.product_code 
            ORDER BY 
                i.tid DESC
        ";

        if ($length !== -1) {
            $sql .= " LIMIT " . intval($start) . ", " . intval($length);
        }

        $query = $this->db->query($sql);
        return $query->result();
    }


    public function count_filtered_price($cid = null)
    {
        $this->db->select('COUNT(DISTINCT i.product) as count');
        $this->db->from('geopos_invoice_items i');
        $this->db->join('geopos_customers c', 'i.cid = c.id', 'INNER');

        if (!empty($cid)) {
            $this->db->where_in('i.cid', $cid);
        }

        $query = $this->db->get();
        return $query->row()->count;
    }

    function count_filtered($id = '')
    {
        $this->_get_datatables_query();
        $query = $this->db->get();
        if ($id != '') {
            $this->db->where('geopos_customers.gid', $id);
        }
        if ($this->aauth->get_user()->loc) {
            $this->db->where('geopos_customers.loc', $this->aauth->get_user()->loc);
        }
        return $query->num_rows($id = '');
    }

    public function count_all($id = '')
    {
        $this->_get_datatables_query();
        if ($this->aauth->get_user()->loc) {
            $this->db->where('geopos_customers.loc', $this->aauth->get_user()->loc);
        }
        if ($id != '') {
            $this->db->where('geopos_customers.gid', $id);
        }
        $query = $this->db->get();
        return $query->num_rows($id = '');
    }

    public function get_customer_statements($pay_acc, $trans_type, $sdate, $edate)
    {
        // Define the initial balance as 0
        $balance = 0;
        if ($trans_type == 'All') {
            $where = "payerid='$pay_acc' AND ext=0";
        } else {
            $where = "payerid='$pay_acc' AND type='$trans_type' AND ext=0";
        }

        $this->db->select('id,acid,type,cat, sum(debit) as debit, sum(credit) as credit,payer,payerid,paymt_method,note,date,created,tid,paymt_date');

        $this->db->from('geopos_transactions');
        $this->db->where($where);
        $this->db->group_by('created');

        $this->db->order_by('date', 'ASC');
        $this->db->order_by('created', 'ASC');
        $this->db->order_by('debit', 'ASC');


        $query = $this->db->get();
        //echo $this->db->last_query(); exit;
        $result = $query->result_array();

        // Calculate the balance for each entry and add it to the result array
        foreach ($result as &$row) {
            $row['balance'] = $balance + $row['credit'] - $row['debit'];
            $balance = $row['balance'];
        }
        return $result;
    }


    public function details($custid, $loc = true)
    {
        $this->db->select('geopos_customers.*,users.lang');
        $this->db->from($this->table);
        $this->db->join('users', 'users.cid=geopos_customers.id', 'left');
        $this->db->where('geopos_customers.id', $custid);
        if ($loc) {
            if ($this->aauth->get_user()->loc) {
                $this->db->where('geopos_customers.loc', $this->aauth->get_user()->loc);
            } elseif (!BDATA) {
                $this->db->where('geopos_customers.loc', 0);
            }
        }
        $query = $this->db->get();
        return $query->row_array();
    }

    public function money_details($custid)
    {

        $this->db->select('SUM(debit) AS debit,SUM(credit) AS credit');
        $this->db->from('geopos_transactions');
        $this->db->where('payerid', $custid);
        $this->db->where('ext', 0);
        $query = $this->db->get();
        return $query->row_array();
    }

    public function due_details($custid)
    {

        $this->db->select('SUM(total) AS total,SUM(pamnt) AS pamnt,SUM(discount) AS discount,');
        $this->db->from('geopos_invoices');
        $this->db->where('csd', $custid);
        $query = $this->db->get();
        return $query->row_array();
    }
    public function get_invoice_count($customer_id)
    {
        $this->db->where('csd', $customer_id);
        $this->db->where_in('status', ['due', 'partial']);
        $this->db->from('geopos_invoices');
        return $this->db->count_all_results();
    }
    public function get_customer_balance($customer_id)
    {
        $this->db->select('SUM(total) - SUM(pamnt) AS cust_balance');
        $this->db->from('geopos_invoices');
        $this->db->where('csd', $customer_id);
        $this->db->where_in('status', ['due', 'partial']);
        $query = $this->db->get();
        $result = $query->row_array();
        return $result['cust_balance'] ?? 0;  // Return 0 if balance is NULL
    }
    public function get_limit_values($customer_id)
    {
        $this->db->select('limit');
        $this->db->from('geopos_customers');
        $this->db->where('id', $customer_id);
        $query = $this->db->get();
        $result = $query->row_array();

        if (isset($result['limit'])) {
            $limit_data = json_decode($result['limit'], true);
            $limit_values = [];

            foreach ($limit_data as $id => $value) {
                list($type, $amount) = explode(':', $value);
                $limit_values[$id] = [
                    'type' => $type,
                    'amount' => $amount
                ];
            }

            return $limit_values;
        }

        return [];
    }


    public function check_limit_values($customer_id)
    {
        $limit_values = $this->get_limit_values($customer_id);
        $invoice_count = $this->get_invoice_count($customer_id);
        $customer_balance = $this->get_customer_balance($customer_id);
        foreach ($limit_values as $id => $value) {
            // Check if type is 'invoice' or 'balance'
            if ($value['type'] === 'invoices') {
                if ($invoice_count >= $value['amount']) {
                    return false; // If the invoice count exceeds the limit, return false
                }
            } elseif ($value['type'] === 'balance') {
                if ($customer_balance >= $value['amount']) {
                    return false; // If the customer balance exceeds the limit, return false
                }
            }
        }

        // If no limit is exceeded, return true
        return true;
    }

    public function add(
        $name,
        $company,
        $phone,
        $email,
        $address,
        $city,
        $region,
        $country,
        $postbox,
        $customergroup,
        $taxid,
        $name_s,
        $phone_s,
        $email_s,
        $address_s,
        $city_s,
        $region_s,
        $country_s,
        $postbox_s,
        $language = '',
        $create_login = true,
        $password = '',
        $docid = '',
        $custom,
        $custom_fields_data = '',
        $discount = 0,
        $bank_accounts_json = ''
    ) {
        // var_dump($bank_accounts_json); die();
        $this->db->select('email');
        $this->db->from('geopos_customers');
        $this->db->where('email', $email);
        $query = $this->db->get();
        $valid = $query->row_array();

        if (!$valid) {
            if (!$discount) {
                $this->db->select('disc_rate');
                $this->db->from('geopos_cust_group');
                $this->db->where('id', $customergroup);
                $query = $this->db->get();
                $result = $query->row_array();
                // Check if result exists and has disc_rate before accessing
                if (!empty($result) && is_array($result) && isset($result['disc_rate'])) {
                    $discount = $result['disc_rate'];
                } else {
                    $discount = 0; // Default discount if customer group not found
                }
            }
            // Set default gid to 1 if customergroup is null or empty
            $gid = (!empty($customergroup)) ? $customergroup : 1;
            
            // Constructing data array
            $data = array(
                'name' => $name,
                'company' => $company,
                'phone' => $phone,
                'email' => $email,
                'address' => $address,
                'city' => $city,
                'region' => $region,
                'country' => $country,
                'postbox' => $postbox,
                'gid' => $gid,
                'taxid' => $taxid,
                'name_s' => $name_s,
                'phone_s' => $phone_s,
                'email_s' => $email_s,
                'address_s' => $address_s,
                'city_s' => $city_s,
                'region_s' => $region_s,
                'country_s' => $country_s,
                'postbox_s' => $postbox_s,
                'docid' => $docid,
                'discount_c' => $discount,
                'custom1' => $custom,
                'limit' => $custom_fields_data,
                'bank_accounts' => $bank_accounts_json
            );

            if ($this->aauth->get_user()->loc) {
                $data['loc'] = $this->aauth->get_user()->loc;
            }

            if ($this->db->insert('geopos_customers', $data)) {
                $cid = $this->db->insert_id();
                $p_string = '';
                $temp_password = '';
                if ($create_login) {

                    if ($password) {
                        $temp_password = $password;
                    } else {
                        $temp_password = rand(200000, 999999);
                    }

                    $pass = password_hash($temp_password, PASSWORD_DEFAULT);
                    $data = array(
                        'user_id' => 1,
                        'status' => 'active',
                        'is_deleted' => 0,
                        'name' => $name,
                        'password' => $pass,
                        'email' => $email,
                        'user_type' => 'Member',
                        'cid' => $cid,
                        'lang' => $language
                    );

                    $this->db->insert('users', $data);
                    $p_string = ' Temporary Password is ' . $temp_password . ' ';
                }
                $this->aauth->applog("[Client Added] $name ID " . $cid, $this->aauth->get_user()->username);
                echo json_encode(array('status' => 'Success', 'message' => $this->lang->line('ADDED') . $p_string . '&nbsp;<a href="' . base_url('customers/view?id=' . $cid) . '" class="btn btn-info btn-sm"><span class="icon-eye"></span>' . $this->lang->line('View') . '</a>', 'cid' => $cid, 'pass' => $temp_password, 'discount' => amountFormat_general($discount)));

                $this->custom->save_fields_data($cid, 1);

                $this->db->select('other');
                $this->db->from('univarsal_api');
                $this->db->where('id', 64);
                $query = $this->db->get();
                $othe = $query->row_array();

                if ($othe['other']) {
                    $auto_mail = $this->send_mail_auto($email, $name, $temp_password);
                    $this->load->model('communication_model');
                    $attachmenttrue = false;
                    $attachment = '';
                    $this->communication_model->send_corn_email($email, $name, $auto_mail['subject'], $auto_mail['message'], $attachmenttrue, $attachment);
                }
            } else {
                echo json_encode(array('status' => 'Error', 'message' =>
                $this->lang->line('ERROR')));
            }
        } else {
            echo json_encode(array('status' => 'Error', 'message' =>
            'Duplicate Email'));
        }
    }


    public function edit($id, $name, $company, $phone, $email, $address, $city, $region, $country, $postbox, $customergroup, $taxid, $name_s, $phone_s, $email_s, $address_s, $city_s, $region_s, $country_s, $postbox_s, $docid = '', $custom = '', $language = '', $discount = 0, $custom_fields_data_json = '', $bank_accounts_json = '')
    {
        // var_dump($bank_accounts_json); die();
        // Set default gid to 1 if customergroup is null or empty
        $gid = (!empty($customergroup)) ? $customergroup : 1;
        
        $data = array(
            'name' => $name,
            'company' => $company,
            'phone' => $phone,
            'email' => $email,
            'address' => $address,
            'city' => $city,
            'region' => $region,
            'country' => $country,
            'postbox' => $postbox,
            'gid' => $gid,
            'taxid' => $taxid,
            'name_s' => $name_s,
            'phone_s' => $phone_s,
            'email_s' => $email_s,
            'address_s' => $address_s,
            'city_s' => $city_s,
            'region_s' => $region_s,
            'country_s' => $country_s,
            'postbox_s' => $postbox_s,
            'docid' => $docid,
            'custom1' => $custom,
            'discount_c' => $discount,
            'limit' => $custom_fields_data_json,
            'bank_accounts' => $bank_accounts_json // Add the bank accounts data
        );

        $this->db->set($data);
        $this->db->where('id', $id);

        if ($this->aauth->get_user()->loc) {
            $this->db->where('loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('loc', 0);
        }

        if ($this->db->update('geopos_customers')) {
            $data = array(
                'name' => $name,
                'email' => $email,
                'lang' => $language
            );
            $this->db->set($data);
            $this->db->where('cid', $id);
            $this->db->update('users');
            $this->aauth->applog("[Client Updated] $name ID " . $id, $this->aauth->get_user()->username);
            echo json_encode(array('status' => 'Success', 'message' => $this->lang->line('UPDATED')));

            $this->custom->edit_save_fields_data($id, 1);
        } else {
            echo json_encode(array('status' => 'Error', 'message' => $this->lang->line('ERROR')));
        }
    }

    public function editpicture($id, $pic)
    {
        $this->db->select('picture');
        $this->db->from($this->table);
        $this->db->where('id', $id);

        $query = $this->db->get();
        $result = $query->row_array();


        $data = array(
            'picture' => $pic
        );


        $this->db->set($data);
        $this->db->where('id', $id);
        if ($this->aauth->get_user()->loc) {
            $this->db->where('loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('loc', 0);
        }
        if ($this->db->update('geopos_customers') and $result['picture'] != 'example.png') {

            unlink(FCPATH . 'userfiles/customers/' . $result['picture']);
            unlink(FCPATH . 'userfiles/customers/thumbnail/' . $result['picture']);
        }
    }

    public function group_list()
    {
        $whr = "";
        if ($this->aauth->get_user()->loc) {
            $whr = "WHERE (geopos_customers.loc=" . $this->aauth->get_user()->loc . " ) ";
            if (BDATA) $whr = "WHERE (geopos_customers.loc=" . $this->aauth->get_user()->loc . " OR geopos_customers.loc=0 ) ";
        } elseif (!BDATA) {
            $whr = "WHERE  geopos_customers.loc=0  ";
        }

        $query = $this->db->query("SELECT c.*,p.pc FROM geopos_cust_group AS c LEFT JOIN ( SELECT gid,COUNT(gid) AS pc FROM geopos_customers $whr GROUP BY gid) AS p ON p.gid=c.id");
        return $query->result_array();
    }
    public function view_edit_limit_fields($id)
    {
        // Assuming the limit column is named 'limit' in your geopos_customers table
        $this->db->select('limit');
        $this->db->where('id', $id);
        $query = $this->db->get('geopos_customers');
        return $query->row_array();
    }
    public function view_limit_fields()
    {
        // Assuming the limit column is named 'limit' in your geopos_customers table
        $this->db->select('*');
        $query = $this->db->get('geopos_customers');
        return $query;
    }

    public function delete($id)
    {


        if ($this->aauth->get_user()->loc) {
            $this->db->delete('geopos_customers', array('id' => $id, 'loc' => $this->aauth->get_user()->loc));
        } elseif (!BDATA) {
            $this->db->delete('geopos_customers', array('id' => $id, 'loc' => 0));
        } else {
            $this->db->delete('geopos_customers', array('id' => $id));
        }

        if ($this->db->affected_rows()) {
            $this->aauth->applog("[Client Deleted]  ID " . $id, $this->aauth->get_user()->username);
            $this->db->delete('users', array('cid' => $id));
            $this->custom->del_fields($id, 1);
            $this->db->delete('geopos_notes', array('fid' => $id, 'rid' => 1));
            //docs
            $this->db->select('filename');
            $this->db->from('geopos_documents');
            $this->db->where('id', $id);
            $query = $this->db->get();
            $result = $query->row_array();
            if ($this->db->delete('geopos_documents', array('fid' => $id, 'rid' => 1))) {
                @unlink(FCPATH . 'userfiles/documents/' . $result['filename']);
                $this->aauth->applog("[Client Doc Deleted]  DocId $id CID " . $id, $this->aauth->get_user()->username);
                //docs

            }
            return true;
        }
    }



    public function activities_table($start_date, $end_date)
    {
        $this->db->select('id,acid,type,cat, debit as debit, credit as credit,payer,payerid,paymt_method,note,date,created,tid,paymt_date');
        $this->db->from('geopos_transactions');
        $this->db->where('date >= ', $start_date);
        $this->db->where('date <= ', $end_date);

        // Default ordering: latest first, then higher debit first
        $this->db->order_by('date', 'DESC');
        $this->db->order_by('debit', 'DESC');

        // DataTables pagination
        $length = (int) $this->input->post('length');
        $start  = (int) $this->input->post('start');
        if ($length && $length != -1) {
            $this->db->limit($length, $start);
        }

        $query = $this->db->get();
        return $query->result_array();
    }
    public function trans_table($id)
    {
        $balance = 0;

        $this->db->select('id,eid, acid, type, inv_id, trans_ref, cat, SUM(debit) as debit, SUM(credit) as credit, payer, payerid, paymt_method, note, date, created, tid, paymt_date');
        $this->db->from('geopos_transactions');
        $this->db->where('payerid', $id);
        $this->db->group_by('created');
        

        $query = $this->db->get();
        //echo $this->db->last_query(); exit;
        $result = $query->result_array();

        // Calculate the balance for each entry and add it to the result array
        foreach ($result as &$row) {
            $row['balance'] = $balance + $row['credit'] - $row['debit'];
            $balance = $row['balance'];
        }

        usort($result, function ($a, $b) {
            $dateComparison = strtotime($b['created']) - strtotime($a['created']);

            if ($dateComparison == 0) {
                $dateComparison = $b['debit'] - $a['debit'];
            }

            return $dateComparison;
        });

        return $result;
    }


    private function _get_trans_table_query($id)
    {
        $this->db->select('id,acid,type,tid,inv_id,cat,method,sum(debit) as debit,sum(credit) as credit, payer,payerid,paymt_method, note,date,created');
        //  $this->db->select('*');
        $this->db->from('geopos_transactions');

        $this->db->where('payerid', $id);
        // $this->db->where('ext', 0);
        // $this->db->group_by('created');
        // $this->db->order_by("date", "DESC");
        // var_dump($id);exit;
        // if ($this->aauth->get_user()->loc) {
        //     $this->db->where('loc', $this->aauth->get_user()->loc);
        // } elseif (!BDATA) {
        //     $this->db->where('loc', 0);
        // }
        $i = 0;
        foreach ($this->trans_column_search as $item) // loop column
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

                if (count($this->trans_column_search) - 1 == $i) //last loop
                    $this->db->group_end(); //close bracket
            }
            $i++;
        }
        $search = $this->input->post('order');
        if ($search) // here order processing
        {
            $this->db->order_by($this->trans_column_order[$search['0']['column']], $search['0']['dir']);
        } else if (isset($this->order)) {
            $order = $this->order;
            $this->db->order_by(key($order), $order[key($order)]);
        }
    }

    function trans_count_filtered($id = '')
    {
        $this->db->select('COUNT(DISTINCT created) as count');
        $this->db->from('geopos_transactions');
        $this->db->where('payerid', $id);

        // Apply search filters if any
        $i = 0;
        foreach ($this->trans_column_search as $item) {
            if ($this->input->post('search') && isset($this->input->post('search')['value']) && $this->input->post('search')['value'] != '') {
                $value = $this->input->post('search')['value'];
                if ($i === 0) {
                    $this->db->group_start();
                    $this->db->like($item, $value);
                } else {
                    $this->db->or_like($item, $value);
                }
                if (count($this->trans_column_search) - 1 == $i) {
                    $this->db->group_end();
                }
            }
            $i++;
        }

        $query = $this->db->get();
        return $query->row()->count;
    }

    public function trans_count_all($id = '')
    {
        $this->db->select('COUNT(DISTINCT created) as count');
        $this->db->from('geopos_transactions');
        $this->db->where('payerid', $id);
        $query = $this->db->get();
        return $query->row()->count;
    }

    public function credit_notes_count_all($id = '')
    {
        $this->db->from('geopos_credit_notes');
        $this->db->where('csd', $id);
        return $this->db->count_all_results();
    }

    public function credit_notes_count_filtered($id = '')
    {
        $this->db->from('geopos_credit_notes');
        $this->db->where('csd', $id);
        $query = $this->db->get();
        return $query->num_rows();
    }

    public function advance_payments_count_all($id = '')
    {
        $this->db->from('geopos_transactions');
        $this->db->where('payerid', $id);
        $this->db->where('is_advance_payment', 1);
        return $this->db->count_all_results();
    }

    public function advance_payments_count_filtered($id = '')
    {
        $this->db->from('geopos_transactions');
        $this->db->where('payerid', $id);
        $this->db->where('is_advance_payment', 1);
        $query = $this->db->get();
        return $query->num_rows();
    }

    private function _inv_datatables_query($id, $tyd = 0)
    {
        $this->db->select('geopos_invoices.*');
        $this->db->from('geopos_invoices');
        $this->db->where('geopos_invoices.csd', $id);
        $this->db->where('geopos_invoices.items >', 0.00);
        if ($this->aauth->get_user()->loc) {
            $this->db->where('geopos_invoices.loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('geopos_invoices.loc', 0);
        }

        if ($tyd) $this->db->where('geopos_invoices.i_class>', 1);
        $this->db->join('geopos_customers', 'geopos_invoices.csd=geopos_customers.id', 'left');

        // Check if search is requested
        if ($this->input->post('search') && isset($this->input->post('search')['value']) && $this->input->post('search')['value'] != '') {
            $search_value = $this->input->post('search')['value'];
            $i = 0;
            
            foreach ($this->inv_column_search as $item) // loop column
            {
                if ($i === 0) // first loop
                {
                    $this->db->group_start(); // open bracket. query Where with OR clause better with bracket. because maybe can combine with other WHERE with AND.
                    $this->db->like($item, $search_value);
                } else {
                    $this->db->or_like($item, $search_value);
                }

                if (count($this->inv_column_search) - 1 == $i) //last loop
                    $this->db->group_end(); //close bracket
                    
                $i++;
            }
        }

        if (isset($_POST['order'])) // here order processing
        {
            $this->db->order_by($this->inv_column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        } else if (isset($this->inv_order)) {
            $order = $this->inv_order;
            $this->db->order_by(key($order), $order[key($order)]);
        }
    }

    function inv_datatables($id, $tyd = 0)
    {
        $this->_inv_datatables_query($id, $tyd);

        $length = $this->input->post('length');
        if ($length && $length != -1)
            $this->db->limit($length, $this->input->post('start'));
        $query = $this->db->get();
        return $query->result();
    }

    function inv_count_filtered($id, $tyd = 0)
    {
        $this->_inv_datatables_query($id, $tyd);
        $query = $this->db->get();
        return $query->num_rows();
    }

    public function inv_count_all($id, $tyd = 0)
    {
        $this->db->from('geopos_invoices');
        $this->db->where('csd', $id);
        $this->db->where('items >', 0.00);
        if ($this->aauth->get_user()->loc) {
            $this->db->where('loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('loc', 0);
        }
        if ($tyd) $this->db->where('i_class>', 1);
        return $this->db->count_all_results();
    }


    private function _qto_datatables_query($id, $tyd = 0)
    {
        $this->db->select('geopos_quotes.*');
        $this->db->from('geopos_quotes');
        $this->db->where('geopos_quotes.csd', $id);
        if ($this->aauth->get_user()->loc) {
            $this->db->where('geopos_quotes.loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('geopos_quotes.loc', 0);
        }
        $this->db->join('geopos_customers', 'geopos_quotes.csd=geopos_customers.id', 'left');

        $i = 0;

        foreach ($this->inv_column_search as $item) // loop column
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

                if (count($this->inv_column_search) - 1 == $i) //last loop
                    $this->db->group_end(); //close bracket
            }
            $i++;
        }

        if (isset($_POST['order'])) // here order processing
        {
            $this->db->order_by($this->qto_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        } else if (isset($this->qto_order)) {
            $order = $this->qto_order;
            $this->db->order_by(key($order), $order[key($order)]);
        }
    }

    function qto_datatables($id, $tyd = 0)
    {
        $this->_qto_datatables_query($id);
        if ($_POST['length'] != -1)
            $this->db->limit($_POST['length'], $_POST['start']);
        $query = $this->db->get();
        return $query->result();
    }

    function qto_count_filtered($id)
    {
        $this->_qto_datatables_query($id);
        $query = $this->db->get();
        return $query->num_rows();
    }

    public function qto_count_all($id)
    {
        $this->db->from('geopos_quotes');
        $this->db->where('csd', $id);
        return $this->db->count_all_results();
    }

    public function group_info($id)
    {

        $this->db->from('geopos_cust_group');
        $this->db->where('id', $id);
        $query = $this->db->get();
        return $query->row_array();
    }

    public function activity($id)
    {
        $this->db->select('*');
        $this->db->from('geopos_metadata');
        $this->db->where('type', 21);
        $this->db->where('rid', $id);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function recharge($id, $amount)
    {

        $this->db->set('balance', "balance+$amount", FALSE);
        $this->db->where('id', $id);

        $this->db->update('geopos_customers');

        $data = array(
            'type' => 21,
            'rid' => $id,
            'col1' => $amount,
            'col2' => date('Y-m-d H:i:s') . ' Account Recharge by ' . $this->aauth->get_user()->username
        );


        if ($this->db->insert('geopos_metadata', $data)) {
            $this->aauth->applog("[Client Wallet Recharge] Amt-$amount ID " . $id, $this->aauth->get_user()->username);
            return true;
        } else {
            return false;
        }
    }

    private function _project_datatables_query($cday = '')
    {
        $this->db->select("geopos_projects.*,geopos_customers.name AS customer");
        $this->db->from('geopos_projects');
        $this->db->join('geopos_customers', 'geopos_projects.cid = geopos_customers.id', 'left');


        $this->db->where('geopos_projects.cid=', $cday);


        $i = 0;

        foreach ($this->pcolumn_search as $item) // loop column
        {
            $search = $this->input->post('search');
            $value = $search['value'];
            if ($value) {

                if ($i === 0) {
                    $this->db->group_start();
                    $this->db->like($item, $value);
                } else {
                    $this->db->or_like($item, $value);
                }

                if (count($this->pcolumn_search) - 1 == $i) //last loop
                    $this->db->group_end(); //close bracket
            }
            $i++;
        }
        $search = $this->input->post('order');
        if ($search) {
            $this->db->order_by($this->column_order[$search['0']['column']], $search['0']['dir']);
        } else if (isset($this->porder)) {
            $order = $this->porder;
            $this->db->order_by(key($order), $order[key($order)]);
        }
    }

    function project_datatables($cday = '')
    {


        $this->_project_datatables_query($cday);

        if ($this->input->post('length') != -1)
            $this->db->limit($this->input->post('length'), $this->input->post('start'));
        $query = $this->db->get();
        return $query->result();
    }

    function project_count_filtered($cday = '')
    {
        $this->_project_datatables_query($cday);
        $query = $this->db->get();
        return $query->num_rows();
    }

    public function project_count_all($cday = '')
    {
        $this->_project_datatables_query($cday);
        $query = $this->db->get();
        return $query->num_rows();
    }

    //notes

    private function _notes_datatables_query($id)
    {

        $this->db->from('geopos_notes');
        $this->db->where('fid', $id);
        $this->db->where('ntype', 1);
        $i = 0;

        foreach ($this->notecolumn_search as $item) // loop column
        {
            $search = $this->input->post('search');
            $value = $search['value'];
            if ($value) {

                if ($i === 0) {
                    $this->db->group_start();
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
        if ($search) {
            $this->db->order_by($this->notecolumn_order[$search['0']['column']], $search['0']['dir']);
        } else if (isset($this->order)) {
            $order = $this->order;
            $this->db->order_by(key($order), $order[key($order)]);
        }
    }

    function notes_datatables($id)
    {
        $this->_notes_datatables_query($id);
        if ($this->input->post('length') != -1)
            $this->db->limit($this->input->post('length'), $this->input->post('start'));
        $query = $this->db->get();
        return $query->result();
    }

    function notes_count_filtered($id)
    {
        $this->_notes_datatables_query($id);
        $query = $this->db->get();
        return $query->num_rows();
    }

    public function notes_count_all($id)
    {
        $this->_notes_datatables_query($id);
        $query = $this->db->get();
        return $query->num_rows();
    }

    public function editnote($id, $title, $content, $cid)
    {

        $data = array('title' => $title, 'content' => $content, 'last_edit' => date('Y-m-d H:i:s'));


        $this->db->set($data);
        $this->db->where('id', $id);
        $this->db->where('fid', $cid);


        if ($this->db->update('geopos_notes')) {
            $this->aauth->applog("[Client Note Edited]  NoteId $id CID " . $cid, $this->aauth->get_user()->username);
            return true;
        } else {
            return false;
        }
    }

    public function note_v($id, $cid)
    {
        $this->db->select('*');
        $this->db->from('geopos_notes');
        $this->db->where('id', $id);
        $this->db->where('fid', $cid);
        $query = $this->db->get();
        return $query->row_array();
    }

    function addnote($title, $content, $cid)
    {
        $this->aauth->applog("[Client Note Added]  NoteId $title CID " . $cid, $this->aauth->get_user()->username);
        $data = array('title' => $title, 'content' => $content, 'cdate' => date('Y-m-d'), 'last_edit' => date('Y-m-d H:i:s'), 'cid' => $this->aauth->get_user()->id, 'fid' => $cid, 'rid' => 1, 'ntype' => 1);
        return $this->db->insert('geopos_notes', $data);
    }

    function deletenote($id, $cid)
    {
        $this->aauth->applog("[Client Note Deleted]  NoteId $id CID " . $cid, $this->aauth->get_user()->username);
        return $this->db->delete('geopos_notes', array('id' => $id, 'fid' => $cid, 'rid' => 1));
    }

    //documents list

    var $doccolumn_order = array(null, 'title', 'cdate', null);
    var $doccolumn_search = array('title', 'cdate');

    public function documentlist($cid)
    {
        $this->db->select('*');
        $this->db->from('geopos_documents');
        $this->db->where('fid', $cid);
        $this->db->where('rid', 1);
        $query = $this->db->get();
        return $query->result_array();
    }

    function adddocument($title, $filename, $cid)
    {
        $this->aauth->applog("[Client Doc Added]  DocId $title CID " . $cid, $this->aauth->get_user()->username);
        $data = array('title' => $title, 'filename' => $filename, 'cdate' => date('Y-m-d'), 'cid' => $this->aauth->get_user()->id, 'fid' => $cid, 'rid' => 1);
        return $this->db->insert('geopos_documents', $data);
    }

    function deletedocument($id, $cid)
    {
        $this->db->select('filename');
        $this->db->from('geopos_documents');
        $this->db->where('id', $id);
        $query = $this->db->get();
        $result = $query->row_array();
        $this->db->trans_start();
        if ($this->db->delete('geopos_documents', array('id' => $id, 'fid' => $cid, 'rid' => 1))) {
            if (@unlink(FCPATH . 'userfiles/documents/' . $result['filename'])) {
                $this->aauth->applog("[Client Doc Deleted]  DocId $id CID " . $cid, $this->aauth->get_user()->username);
                $this->db->trans_complete();
                return true;
            } else {
                $this->db->trans_rollback();
                return false;
            }
        } else {
            return false;
        }
    }


    function document_datatables($cid)
    {
        $this->document_datatables_query($cid);
        if ($this->input->post('length') != -1)
            $this->db->limit($this->input->post('length'), $this->input->post('start'));
        $query = $this->db->get();
        return $query->result();
    }

    private function document_datatables_query($cid)
    {

        $this->db->from('geopos_documents');
        $this->db->where('fid', $cid);
        $this->db->where('rid', 1);
        $i = 0;

        foreach ($this->doccolumn_search as $item) // loop column
        {
            $search = $this->input->post('search');
            $value = $search['value'];
            if ($value) {

                if ($i === 0) {
                    $this->db->group_start();
                    $this->db->like($item, $value);
                } else {
                    $this->db->or_like($item, $value);
                }

                if (count($this->doccolumn_search) - 1 == $i) //last loop
                    $this->db->group_end(); //close bracket
            }
            $i++;
        }
        $search = $this->input->post('order');
        if ($search) {
            $this->db->order_by($this->doccolumn_order[$search['0']['column']], $search['0']['dir']);
        } else if (isset($this->order)) {
            $order = $this->order;
            $this->db->order_by(key($order), $order[key($order)]);
        }
    }

    function document_count_filtered($cid)
    {
        $this->document_datatables_query($cid);
        $query = $this->db->get();
        return $query->num_rows();
    }

    public function document_count_all($cid)
    {
        $this->document_datatables_query($cid);
        $query = $this->db->get();
        return $query->num_rows();
    }

    public function send_mail_auto($email, $name, $password)
    {
        $this->load->library('parser');
        $this->load->model('templates_model', 'templates');
        $template = $this->templates->template_info(16);

        $data = array(
            'Company' => $this->config->item('ctitle'),
            'NAME' => $name
        );
        $subject = $this->parser->parse_string($template['key1'], $data, TRUE);

        $data = array(
            'Company' => $this->config->item('ctitle'),
            'NAME' => $name,
            'EMAIL' => $email,
            'URL' => base_url() . 'crm',
            'PASSWORD' => $password,
            'CompanyDetails' => '<h6><strong>' . $this->config->item('ctitle') . ',</strong></h6>
<address>' . $this->config->item('address') . '<br>' . $this->config->item('address2') . '</address>
             ' . $this->lang->line('Phone') . ' : ' . $this->config->item('phone') . '<br>  ' . $this->lang->line('Email') . ' : ' . $this->config->item('email'),


        );
        $message = $this->parser->parse_string($template['other'], $data, TRUE);


        return array('subject' => $subject, 'message' => $message);
    }


    public function recipients($ids)
    {

        $this->db->select('id,name,email,phone');
        $this->db->from('geopos_customers');
        $this->db->where_in('id', $ids);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function sales_due($sdate, $edate, $csd, $trans_type, $pay = true, $amount = 0, $acc = 0, $pay_method = '', $note = '')
    {
        if ($pay) {
            $this->db->select_sum('total');
            $this->db->select_sum('pamnt');
            $this->db->from('geopos_invoices');
            $this->db->where('DATE(invoicedate) >=', $sdate);
            $this->db->where('DATE(invoicedate) <=', $edate);
            $this->db->where('csd', $csd);
            $this->db->where('status', $trans_type);
            if ($this->aauth->get_user()->loc) {
                $this->db->where('loc', $this->aauth->get_user()->loc);
            } elseif (!BDATA) {
                $this->db->where('loc', 0);
            }

            $query = $this->db->get();
            $result = $query->row_array();
            return $result;
        } else {
            if ($amount) {
                $this->db->select('id,tid,total,pamnt');
                $this->db->from('geopos_invoices');
                $this->db->where('DATE(invoicedate) >=', $sdate);
                $this->db->where('DATE(invoicedate) <=', $edate);
                $this->db->where('csd', $csd);
                $this->db->where('status', $trans_type);
                if ($this->aauth->get_user()->loc) {
                    $this->db->where('loc', $this->aauth->get_user()->loc);
                } elseif (!BDATA) {
                    $this->db->where('loc', 0);
                }

                $query = $this->db->get();
                $result = $query->result_array();
                $amount_custom = $amount;

                foreach ($result as $row) {
                    $note .= ' #' . $row['tid'];
                    $due = $row['total'] - $row['pamnt'];
                    if ($amount_custom >= $due) {
                        $this->db->set('status', 'paid');
                        $this->db->set('pamnt', "pamnt+$due", FALSE);
                        $amount_custom = $amount_custom - $due;
                    } elseif ($amount_custom > 0 and $amount_custom < $due) {
                        $this->db->set('status', 'partial');
                        $this->db->set('pamnt', "pamnt+$amount_custom", FALSE);
                        $amount_custom = 0;
                    }

                    $this->db->set('pmethod', $pay_method);
                    $this->db->where('id', $row['id']);
                    $this->db->update('geopos_invoices');

                    if ($amount_custom == 0) break;
                }
                $this->db->select('id,holder');
                $this->db->from('geopos_accounts');
                $this->db->where('id', $acc);
                $query = $this->db->get();
                $account = $query->row_array();

                $data = array(
                    'acid' => $account['id'],
                    'account' => $account['holder'],
                    'type' => 'Income',
                    'cat' => 'Sales',
                    'credit' => $amount,
                    'payer' => $this->lang->line('Bulk Payment Invoices'),
                    'payerid' => $csd,
                    'method' => $pay_method,
                    'date' => date('Y-m-d'),
                    'eid' => $this->aauth->get_user()->id,
                    'tid' => 0,
                    'note' => $note,
                    'loc' => $this->aauth->get_user()->loc
                );

                $this->db->insert('geopos_transactions', $data);
                $tttid = $this->db->insert_id();
                $this->db->set('lastbal', "lastbal+$amount", FALSE);
                $this->db->where('id', $account['id']);
                $this->db->update('geopos_accounts');
            }
        }
    }

    public function customers_list()
    {
        $this->db->select('id, name, company, email, phone, address, city, region, country, postbox, gid, taxid, name_s, phone_s, email_s, address_s, city_s, region_s, country_s, postbox_s, docid, custom1, discount_c, limit, bank_accounts');
        $this->db->from('geopos_customers');
        
        if ($this->aauth->get_user()->loc) {
            $this->db->where('loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('loc', 0);
        }
        
        $this->db->order_by('name', 'ASC');
        $query = $this->db->get();
        return $query->result_array();
    }
}

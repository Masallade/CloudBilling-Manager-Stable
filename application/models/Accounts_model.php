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





class Accounts_model extends CI_Model

{

    var $table = 'geopos_accounts';



    public function __construct()

    {

        parent::__construct();
    }



    public function accountslist($l = true, $lid = 0)

    {

        $this->db->select('*');

        $this->db->from($this->table);

        // if ($l) {

        //     if ($this->aauth->get_user()->loc) {

        //         $this->db->where('loc', $this->aauth->get_user()->loc);

        //         if (BDATA) $this->db->or_where('loc', 0);
        //     } else {

        //         if (!BDATA) $this->db->where('loc', 0);
        //     }
        // } else {

        //     $this->db->where('loc', $lid);
        // }



        $query = $this->db->get();

        return $query->result_array();
    }



    public function details($acid)

    {



        $this->db->select('*');

        $this->db->from('geopos_accounts');

        $this->db->where('id', $acid);

        if ($this->aauth->get_user()->loc) {

            $this->db->group_start();

            $this->db->where('loc', $this->aauth->get_user()->loc);

            if (BDATA)  $this->db->or_where('loc', 0);

            $this->db->group_end();
        }

        $query = $this->db->get();

        return $query->row_array();
    }



    public function addnew($accno, $holder, $intbal, $acode, $lid, $account_type)

    {

        $data = array(

            'acn' => $accno,

            'holder' => $holder,

            'adate' => date('Y-m-d H:i:s'),

            'lastbal' => $intbal,

            'code' => $acode,

            'loc' => $lid,

            'account_type' => $account_type

        );



        if ($this->db->insert('geopos_accounts', $data)) {

            $this->aauth->applog("[Account Created] $accno - $intbal ID " . $this->db->insert_id(), $this->aauth->get_user()->username);

            echo json_encode(array('status' => 'Success', 'message' =>

            $this->lang->line('ADDED') . "  <a href='" . base_url('accounts') . "' class='btn btn-blue btn-lg'><span class='fa fa-list-alt' aria-hidden='true'></span>  </a> <a href='add' class='btn btn-info btn-lg'><span class='fa fa-plus-circle' aria-hidden='true'></span>  </a>"));
        } else {

            echo json_encode(array('status' => 'Error', 'message' =>

            $this->lang->line('ERROR')));
        }
    }



    public function edit($acid, $accno, $holder, $acode, $lid, $account_equity = '', $account_type = '')

    {

        if ($account_equity) {

            $data = array(

                'acn' => $accno,

                'holder' => $holder,

                'code' => $acode,

                'loc' => $lid,

                'lastbal' => $account_equity,

                'account_type' => $account_type

            );
        } else {

            $data = array(

                'acn' => $accno,

                'holder' => $holder,

                'code' => $acode,

                'loc' => $lid,
                
                'account_type' => $account_type

            );
        }



        $this->db->set($data);

        $this->db->where('id', $acid);

        if ($this->aauth->get_user()->loc) {

            $this->db->where('loc', $this->aauth->get_user()->loc);
        }



        if ($this->db->update('geopos_accounts')) {

            $this->aauth->applog("[Account Edited] $accno - ID " . $acid, $this->aauth->get_user()->username);

            echo json_encode(array('status' => 'Success', 'message' =>

            $this->lang->line('UPDATED')));
        } else {

            echo json_encode(array('status' => 'Error', 'message' =>

            $this->lang->line('ERROR')));
        }
    }



    public function account_stats()

    {

        $whr = ' ';

        if ($this->aauth->get_user()->loc) {

            $whr = ' WHERE loc=' . $this->aauth->get_user()->loc;

            if (BDATA) $whr .= 'OR loc=0 ';
        }



        $query = $this->db->query("SELECT SUM(lastbal) AS balance,COUNT(id) AS count_a FROM geopos_accounts $whr");



        $result = $query->row_array();

        echo json_encode(array(0 => array('balance' => amountExchange($result['balance'], 0, $this->aauth->get_user()->loc), 'count_a' => $result['count_a'])));
    }

    public function ajax_accounts_list()
    {
        $draw = intval($this->input->get("draw"));
        $start = intval($this->input->get("start"));
        $length = intval($this->input->get("length"));
        $search = $this->input->get("search")["value"];

        $this->db->select('*');
        $this->db->from($this->table);

        // Apply location filter
        if ($this->aauth->get_user()->loc) {
            $this->db->group_start();
            $this->db->where('loc', $this->aauth->get_user()->loc);
            if (BDATA) $this->db->or_where('loc', 0);
            $this->db->group_end();
        }

        // Apply search filter
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('acn', $search);
            $this->db->or_like('holder', $search);
            $this->db->or_like('account_type', $search);
            $this->db->group_end();
        }

        // Get total records count
        $totalRecords = $this->db->count_all_results('', false);

        // Apply pagination (handle -1 for "show all")
        if ($length > 0) {
            $this->db->limit($length, $start);
        }

        // Apply ordering
        $orderColumn = $this->input->get("order")[0]["column"];
        $orderDir = $this->input->get("order")[0]["dir"];
        $columns = array('id', 'acn', 'holder', 'lastbal', 'account_type');

        if (isset($columns[$orderColumn])) {
            $this->db->order_by($columns[$orderColumn], $orderDir);
        }

        $query = $this->db->get();
        $accounts = $query->result_array();

        $data = array();
        foreach ($accounts as $row) {
            $aid = $row['id'];
            $acn = $row['acn'];
            $holder = $row['holder'];
            $balance = amountExchange($row['lastbal'], 0, $this->aauth->get_user()->loc);
            $type = $row['account_type'];

            // Create action buttons
            $actions = '<a href="' . base_url("accounts/view?id=$aid") . '" class="btn btn-success btn-xs"><i class="fa fa-eye"></i> ' . $this->lang->line('View') . '</a>&nbsp;';
            $actions .= '<a href="' . base_url("accounts/edit?id=$aid") . '" class="btn btn-warning btn-xs"><i class="fa fa-pencil"></i> ' . $this->lang->line('Edit') . '</a>&nbsp;';
            $actions .= '<a href="#" data-object-id="' . $aid . '" class="btn btn-danger btn-xs delete-object" title="Delete"><i class="fa fa-trash"></i></a>';


            $data[] = array(
                $aid,
                $acn,
                $holder,
                $balance,
                $type,
                $actions
            );
        }

        $output = array(
            "draw" => $draw,
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $totalRecords,
            "data" => $data
        );

        echo json_encode($output);
    }
}

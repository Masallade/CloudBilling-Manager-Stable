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

class Reports_model extends CI_Model
{

    public function export_profitloss()
    {
        $overall_data;
        $start = date("Y-m-d", strtotime($_POST['hidden_start_date']));
        $end = date("Y-m-d", strtotime($_POST['hidden_end_date']));
        $sql = "select geopos_invoices.*  from geopos_invoices  WHERE  invoicedate BETWEEN '" . $start . "' AND '" . $end . "' and total>0 ORDER BY  geopos_invoices.invoicedate ASC  ";
        $query = $this->db->query($sql);
        $invoice_ids = $query->result_array();
        // print_r( $this->db->last_query());die();
        //print_r($invoice_ids);die();
        $inv_ids;
        foreach ($invoice_ids as $key => $inv_value) {
            $inv_ids[$key] = $inv_value['id'];
            $inv_id = $inv_value['id'];
            $sql = "select * from geopos_invoice_items where tid=" . $inv_id . " ";
            $query = $this->db->query($sql);
            $ivoice_items = $query->result_array();
            $sum_product_subtotal = 0;
            $sum_inv_subtotal = 0;
            foreach ($ivoice_items as $value) {
                $this->db->select('*');
                $this->db->from('geopos_products');
                $this->db->where('pid', $value['pid']);
                $query = $this->db->get();
                $pr = $query->row_array();
                $product_price = $pr['product_price'];
                $sum_product_subtotal += $product_price * $value['qty'];
                $sum_inv_subtotal += $value['subtotal'] - $value['totaltax'];
            }
            $overall_data[$key]['inv_profit_loss'] = $sum_inv_subtotal - $sum_product_subtotal;
            $overall_data[$key]['inv_id'] = $inv_id;
            $overall_data[$key]['inv_tid'] = $inv_value['tid'];
            $overall_data[$key]['inv_date'] = $inv_value['invoicedate'];
            $overall_data[$key]['inv_total'] = $inv_value['total'] - $inv_value['tax'];
            $this->db->select('name');
            $this->db->from('geopos_customers');
            $this->db->where('id', $inv_value['csd']);
            $query = $this->db->get();
            $pr = $query->row_array();
            $cust_name = $pr['name'];
            $overall_data[$key]['cust_name'] = $cust_name;
        }






        return  $overall_data;
    }




    public function viewstatement($pay_acc, $trans_type, $sdate, $edate, $ttype)
    {

        if ($trans_type == 'All') {
            $where = "acid='$pay_acc' AND (DATE(date) BETWEEN '$sdate' AND '$edate') ";
        } else {
            $where = "acid='$pay_acc' AND (DATE(date) BETWEEN '$sdate' AND '$edate') AND type='$trans_type'";
        }
        if ($this->aauth->get_user()->loc) {
            $where .= " AND loc='" . $this->aauth->get_user()->loc . "'";
        } elseif (!BDATA) {
            $where .= " AND type='$trans_type AND loc='0'";
        }
        $this->db->select('*');
        $this->db->from('geopos_transactions');
        $this->db->where($where);
        $this->db->order_by('id', 'DESC');
        $query = $this->db->get();
        $result = $query->result_array();

        return $result;
    }

    public function get_statements($pay_acc, $trans_type, $sdate, $edate)
    {

        if ($trans_type == 'All') {
            $where = "acid='$pay_acc' AND (DATE(date) BETWEEN '$sdate' AND '$edate') ";
        } else {
            $where = "acid='$pay_acc' AND (DATE(date) BETWEEN '$sdate' AND '$edate') AND type='$trans_type'";
        }
        if ($this->aauth->get_user()->loc) {
            $where .= " AND loc='" . $this->aauth->get_user()->loc . "'";
        } elseif (!BDATA) {
            $where .= " AND loc='0'";
        }
        $this->db->select('*');
        $this->db->from('geopos_transactions');
        $this->db->where($where);


        //  $this->db->order_by('id', 'DESC');
        $query = $this->db->get();
        $result = $query->result_array();

        return $result;
    }

    public function get_statements_employee($pay_emp, $trans_type, $sdate, $edate)
    {

        if ($trans_type == 'All') {
            $where = "payerid	='$pay_emp'  AND ext='4' AND (DATE(date) BETWEEN '$sdate' AND '$edate') ";
        } else {
            $where = "payerid	='$pay_emp'  AND ext='4' AND (DATE(date) BETWEEN '$sdate' AND '$edate') AND type='$trans_type'";
        }
        if ($this->aauth->get_user()->loc) {
            $where .= " AND loc='" . $this->aauth->get_user()->loc . "'";
        } elseif (!BDATA) {
            $where .= " AND loc='0'";
        }
        $this->db->select('*');
        $this->db->from('geopos_transactions');
        $this->db->where($where);


        //  $this->db->order_by('id', 'DESC');
        $query = $this->db->get();
        $result = $query->result_array();

        return $result;
    }

    public function get_statements_cat($pay_cat, $trans_type, $sdate, $edate)
    {

        if ($trans_type == 'All') {
            $where = "cat='$pay_cat' AND (DATE(date) BETWEEN '$sdate' AND '$edate') ";
        } else {
            $where = "cat='$pay_cat' AND (DATE(date) BETWEEN '$sdate' AND '$edate') AND type='$trans_type'";
        }
        if ($this->aauth->get_user()->loc) {
            $where .= " AND loc='" . $this->aauth->get_user()->loc . "'";
        } elseif (!BDATA) {
            $where .= " AND loc='0'";
        }
        $this->db->select('*');
        $this->db->from('geopos_transactions');
        $this->db->where($where);


        //  $this->db->order_by('id', 'DESC');
        $query = $this->db->get();
        $result = $query->result_array();

        return $result;
    }

    //transaction account statement

    var $table = 'geopos_transactions';
    var $column_order = array(null, 'account', 'type', 'cat', 'amount', 'stat');
    var $column_search = array('id', 'account');
    var $order = array('id' => 'asc');
    var $opt = '';


    //income statement


    public function incomestatement()
    {
        $this->db->select_sum('lastbal');
        $this->db->from('geopos_accounts');
        if ($this->aauth->get_user()->loc) {
            $this->db->where('loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('loc', 0);
        }

        $query = $this->db->get();
        $result = $query->row_array();

        $lastbal = $result['lastbal'];

        $this->db->select_sum('credit');
        $this->db->from('geopos_transactions');
        if ($this->aauth->get_user()->loc) {
            $this->db->where('loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('loc', 0);
        }
        $this->db->where('type', 'Income');
        $month = date('Y-m');
        $today = date('Y-m-d');
        $this->db->where('DATE(date) >=', "$month-01");
        $this->db->where('DATE(date) <=', $today);

        $query = $this->db->get();
        $result = $query->row_array();

        $motnhbal = $result['credit'];
        return array('lastbal' => $lastbal, 'monthinc' => $motnhbal);
    }

    public function customincomestatement($acid, $sdate, $edate)
    {


        $this->db->select_sum('credit');
        $this->db->from('geopos_transactions');
        if ($acid > 0) {
            $this->db->where('acid', $acid);
        }
        if ($this->aauth->get_user()->loc) {
            $this->db->where('loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('loc', 0);
        }
        $this->db->where('type', 'Income');
        $this->db->where('DATE(date) >=', $sdate);
        $this->db->where('DATE(date) <=', $edate);
        // $this->db->where("DATE(date) BETWEEN '$sdate' AND '$edate'");
        $query = $this->db->get();
        $result = $query->row_array();

        return $result;
    }

    //expense statement


    public function expensestatement()
    {


        $this->db->select_sum('debit');
        $this->db->from('geopos_transactions');
        if ($this->aauth->get_user()->loc) {
            $this->db->where('loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('loc', 0);
        }
        $this->db->where('type', 'Expense');
        $month = date('Y-m');
        $today = date('Y-m-d');
        $this->db->where('DATE(date) >=', "$month-01");
        $this->db->where('DATE(date) <=', $today);
        if ($this->aauth->get_user()->loc) {
            $this->db->where('loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('loc', 0);
        }
        $query = $this->db->get();
        $result = $query->row_array();

        $motnhbal = $result['debit'];
        return array('monthinc' => $motnhbal);
    }

    public function customexpensestatement($acid, $sdate, $edate)
    {


        $this->db->select_sum('debit');
        $this->db->from('geopos_transactions');
        if ($acid > 0) {
            $this->db->where('acid', $acid);
        }
        if ($this->aauth->get_user()->loc) {
            $this->db->where('loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('loc', 0);
        }
        $this->db->where('type', 'Expense');
        $this->db->where('DATE(date) >=', $sdate);
        $this->db->where('DATE(date) <=', $edate);
        $query = $this->db->get();
        $result = $query->row_array();

        return $result;
    }

    public function statistics($limit = false)
    {
        $this->db->from('geopos_reports');
        // if($limit) $this->db->limit(12);
        $this->db->order_by('id', 'DESC');
        $query = $this->db->get();
        $result = $query->result_array();
        return $result;
    }

    public function get_supplier_statements($pay_acc, $trans_type, $sdate, $edate)
    {
        // Build the base WHERE clause
        $this->db->where('payerid', $pay_acc);
        $this->db->where('DATE(date) >=', $sdate);
        $this->db->where('DATE(date) <=', $edate);
        $this->db->where('ext', 1);

        // Add condition for trans_type if it's not 'All'
        if ($trans_type != 'All') {
            $this->db->where('type', $trans_type);
        }

        // Handle location filter
        if ($this->aauth->get_user()->loc) {
            $this->db->where('loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('loc', 0);
        }

        // Select data and fetch results
        $this->db->select('*');
        $this->db->from('geopos_transactions');
        $query = $this->db->get();
        $result = $query->result_array();

        return $result;
    }



    //
    //income statement


    public function profitstatement() {}

    public function customprofitstatement($lid, $sdate, $edate)
    {


        $this->db->select_sum('geopos_metadata.col1');
        $this->db->from('geopos_metadata');
        $this->db->where('geopos_metadata.type', 9);
        $this->db->where('DATE(geopos_metadata.d_date) >=', $sdate);
        $this->db->where('DATE(geopos_metadata.d_date) <=', $edate);
        $this->db->join('geopos_invoices', 'geopos_invoices.id = geopos_metadata.rid', 'left');

        if ($this->aauth->get_user()->loc) {
            $this->db->where('geopos_invoices.loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('geopos_invoices.loc', $lid);
        } else {
            $this->db->group_start();
            $this->db->where('geopos_invoices.loc', $lid);
            $this->db->or_where('geopos_invoices.loc', 0);
            $this->db->group_end();
        }

        $query = $this->db->get();
        $result = $query->row_array();

        return $result;
    }

    public function customcommission($lid, $sdate, $edate)
    {

        $this->db->select('c_rate');
        $this->db->from('geopos_employees');
        $this->db->where('id', $lid);
        $query = $this->db->get();
        $result_e = $query->row_array();
        $this->db->select_sum('total');
        $this->db->from('geopos_invoices');
        $this->db->where('eid', $lid);
        $this->db->where('status !=', 'canceled');
        $this->db->where('DATE(geopos_invoices.invoicedate) >=', $sdate);
        $this->db->where('DATE(geopos_invoices.invoiceduedate) <=', $edate);
        $query = $this->db->get();
        $result = $query->row_array();
        if ($result_e['c_rate'] > 0 and $result['total'] > 0) {
            $amount = ($result_e['c_rate'] * $result['total']) / 100;
            return $amount;
        } else {
            return 0;
        }
    }

    //sales statement


    public function salesstatement() {}

    public function customsalesstatement($lid, $sdate, $edate)
    {
        $this->db->select_sum('total');
        $this->db->from('geopos_invoices');
        $this->db->where('DATE(invoicedate) >=', $sdate);
        $this->db->where('DATE(invoicedate) <=', $edate);
        if (!$this->aauth->premission(19)) {
            $this->db->where('inv_type !=', 'DAYPASS');
        }
        if ($this->aauth->get_user()->loc) {
            $this->db->where('geopos_invoices.loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('geopos_invoices.loc', $lid);
        } else {
            $this->db->group_start();
            $this->db->where('geopos_invoices.loc', $lid);
            $this->db->or_where('geopos_invoices.loc', 0);
            $this->db->group_end();
        }

        $query = $this->db->get();
        $result = $query->row_array();
        return $result;
    }

    //products statement


    public function productsstatement()
    {
        $this->db->select_sum('qty');
        $this->db->select_sum('subtotal');
        $this->db->from('geopos_invoice_items');
        $query = $this->db->get();
        $result = $query->row_array();
        $qty = $result['qty'];
        $subtotal = $result['subtotal'];

        $this->db->select_sum('geopos_invoice_items.qty');
        $this->db->select_sum('geopos_invoice_items.subtotal');
        $this->db->from('geopos_invoice_items');
        $this->db->join('geopos_invoices', 'geopos_invoices.id = geopos_invoice_items.tid', 'left');
        $month = date('Y-m');
        $today = date('Y-m-d');
        $this->db->where('DATE(geopos_invoices.invoicedate) >=', "$month-01");
        $this->db->where('DATE(geopos_invoices.invoicedate) <=', $today);
        if ($this->aauth->get_user()->loc) {
            $this->db->where('geopos_invoices.loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('geopos_invoices.loc', 0);
        }
        $query = $this->db->get();
        $result = $query->row_array();
        $qty_m = $result['qty'];
        $subtotal_m = $result['subtotal'];
        return array('qty' => $qty, 'qty_m' => $qty_m, 'subtotal' => $subtotal, 'subtotal_m' => $subtotal_m);
    }

    public function customproductsstatement($lid, $sdate, $edate)
    {

        $this->db->select_sum('geopos_invoice_items.qty');
        $this->db->select_sum('geopos_invoice_items.subtotal');
        $this->db->from('geopos_invoice_items');
        $this->db->join('geopos_invoices', 'geopos_invoices.id = geopos_invoice_items.tid', 'left');
        $this->db->where('DATE(geopos_invoices.invoicedate) >=', $sdate);
        $this->db->where('DATE(geopos_invoices.invoicedate) <=', $edate);
        if (!$this->aauth->premission(19)) {
            $this->db->where('geopos_invoices.inv_type !=', 'DAYPASS');
        }
        if ($this->aauth->get_user()->loc) {
            $this->db->where('geopos_invoices.loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('geopos_invoices.loc', $lid);
        } else {
            $this->db->group_start();
            $this->db->where('geopos_invoices.loc', $lid);
            $this->db->or_where('geopos_invoices.loc', 0);
            $this->db->group_end();
        }

        $query = $this->db->get();
        $result = $query->row_array();

        return $result;
    }

    public function customproductsstatement_cat($lid, $sdate, $edate)
    {

        $this->db->select_sum('geopos_invoice_items.qty');
        $this->db->select_sum('geopos_invoice_items.subtotal');
        $this->db->from('geopos_invoice_items');
        $this->db->join('geopos_invoices', 'geopos_invoices.id = geopos_invoice_items.tid', 'left');
        $this->db->join('geopos_products', 'geopos_products.pid = geopos_invoice_items.pid', 'left');
        $this->db->where('DATE(geopos_invoices.invoicedate) >=', $sdate);
        $this->db->where('DATE(geopos_invoices.invoicedate) <=', $edate);
        if (!$this->aauth->premission(19)) {
            $this->db->where('geopos_invoices.inv_type !=', 'DAYPASS');
        }
        if ($lid > 0) {
            $this->db->where('geopos_products.pid', $lid);
        }
        $query = $this->db->get();
        $result = $query->row_array();
        return $result;
    }

    //fetch data

    public function fetchdata($page)
    {
        switch ($page) {
            case 'products':
                $this->db->select_sum('geopos_invoice_items.qty');
                $this->db->select_sum('geopos_invoice_items.subtotal');
                $this->db->from('geopos_invoice_items');
                $this->db->join('geopos_invoices', 'geopos_invoices.id = geopos_invoice_items.tid', 'left');
                if ($this->aauth->get_user()->loc) {
                    $this->db->where('geopos_invoices.loc', $this->aauth->get_user()->loc);
                } elseif (!BDATA) {
                    $this->db->where('geopos_invoices.loc', 0);
                }
                if (!$this->aauth->premission(19)) {
                    $this->db->where('geopos_invoices.inv_type !=', 'DAYPASS');
                }
                $query = $this->db->get();
                $result = $query->row_array();
                $qty = $result['qty'];
                $subtotal = $result['subtotal'];
                $this->db->select_sum('geopos_invoice_items.qty');
                $this->db->select_sum('geopos_invoice_items.subtotal');
                $this->db->from('geopos_invoice_items');
                $this->db->join('geopos_invoices', 'geopos_invoices.id = geopos_invoice_items.tid', 'left');
                if ($this->aauth->get_user()->loc) {
                    $this->db->where('geopos_invoices.loc', $this->aauth->get_user()->loc);
                } elseif (!BDATA) {
                    $this->db->where('geopos_invoices.loc', 0);
                }
                if (!$this->aauth->premission(19)) {
                    $this->db->where('geopos_invoices.inv_type !=', 'DAYPASS');
                }
                $month = date('Y-m');
                $today = date('Y-m-d');
                $this->db->where('DATE(geopos_invoices.invoicedate) >=', "$month-01");
                $this->db->where('DATE(geopos_invoices.invoicedate) <=', $today);
                if (!$this->aauth->premission(19)) {
                    $this->db->where('geopos_invoices.inv_type !=', 'DAYPASS');
                }
                $query = $this->db->get();
                $result = $query->row_array();
                $qty_m = $result['qty'];
                $subtotal_m = $result['subtotal'];
                return array('p1' => $qty, 'p2' => $qty_m, 'p3' => amountExchange($subtotal, 0, $this->aauth->get_user()->loc), 'p4' => amountExchange($subtotal_m, 0, $this->aauth->get_user()->loc));
                break;
            case 'sales':
                $this->db->select_sum('total');
                $this->db->from('geopos_invoices');
                if ($this->aauth->get_user()->loc) {
                    $this->db->where('geopos_invoices.loc', $this->aauth->get_user()->loc);
                } elseif (!BDATA) {
                    $this->db->where('geopos_invoices.loc', 0);
                }
                if (!$this->aauth->premission(19)) {
                    $this->db->where('geopos_invoices.inv_type !=', 'DAYPASS');
                }
                $query = $this->db->get();
                $result = $query->row_array();
                $lastbal = $result['total'];
                $this->db->select_sum('total');
                $this->db->from('geopos_invoices');
                if ($this->aauth->get_user()->loc) {
                    $this->db->where('geopos_invoices.loc', $this->aauth->get_user()->loc);
                } elseif (!BDATA) {
                    $this->db->where('geopos_invoices.loc', 0);
                }
                $month = date('Y-m');
                $today = date('Y-m-d');
                $this->db->where('DATE(invoicedate) >=', "$month-01");
                $this->db->where('DATE(invoicedate) <=', $today);
                if (!$this->aauth->premission(19)) {
                    $this->db->where('geopos_invoices.inv_type !=', 'DAYPASS');
                }
                $query = $this->db->get();
                $result = $query->row_array();
                $motnhbal = $result['total'];
                return array('p1' => amountExchange($lastbal, 0, $this->aauth->get_user()->loc), 'p2' => amountExchange($motnhbal, 0, $this->aauth->get_user()->loc), 'p3' => 0, 'p4' => 0);

                break;

            case 'profit':

                $this->db->select_sum('geopos_metadata.col1');
                $this->db->from('geopos_metadata');
                $this->db->join('geopos_invoices', 'geopos_invoices.id = geopos_metadata.rid', 'left');
                $this->db->where('geopos_metadata.type', 9);
                if ($this->aauth->get_user()->loc) {
                    $this->db->where('geopos_invoices.loc', $this->aauth->get_user()->loc);
                } elseif (!BDATA) {
                    $this->db->where('geopos_invoices.loc', 0);
                }
                if (!$this->aauth->premission(19)) {
                    $this->db->where('geopos_invoices.inv_type !=', 'DAYPASS');
                }
                $query = $this->db->get();
                $result = $query->row_array();
                $lastbal = $result['col1'];
                $this->db->select_sum('geopos_metadata.col1');
                $this->db->from('geopos_metadata');
                $this->db->where('geopos_metadata.type', 9);
                $this->db->join('geopos_invoices', 'geopos_invoices.id = geopos_metadata.rid', 'left');
                if ($this->aauth->get_user()->loc) {
                    $this->db->where('geopos_invoices.loc', $this->aauth->get_user()->loc);
                } elseif (!BDATA) {
                    $this->db->where('geopos_invoices.loc', 0);
                }
                $month = date('Y-m');
                $today = date('Y-m-d');
                $this->db->where('DATE(geopos_metadata.d_date) >=', "$month-01");
                $this->db->where('DATE(geopos_metadata.d_date) <=', $today);
                if (!$this->aauth->premission(19)) {
                    $this->db->where('geopos_invoices.inv_type !=', 'DAYPASS');
                }
                $query = $this->db->get();
                $result = $query->row_array();
                $motnhbal = $result['col1'];
                return array('p1' => amountExchange($lastbal, 0, $this->aauth->get_user()->loc), 'p2' => amountExchange($motnhbal, 0, $this->aauth->get_user()->loc), 'p3' => 0, 'p4' => 0);
        }
    }


    public function product_customer_statements($customer, $sdate, $edate)
    {
        $this->db->select('geopos_invoice_items.*,geopos_invoices.invoicedate,geopos_invoices.tid AS inv');
        $this->db->from('geopos_invoice_items');
        $this->db->join('geopos_invoices', 'geopos_invoices.id = geopos_invoice_items.tid', 'left');

        $this->db->where('DATE(geopos_invoices.invoicedate) >=', $sdate);
        $this->db->where('DATE(geopos_invoices.invoicedate) <=', $edate);
        if ($this->aauth->get_user()->loc) {
            $this->db->where('geopos_invoices.loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('geopos_invoices.loc', 0);
        }
        $this->db->where('geopos_invoices.csd', $customer);
        $query = $this->db->get();
        $result = $query->result_array();

        return $result;
    }


    public function product_supplier_statements($customer, $sdate, $edate)
    {
        $this->db->select('geopos_purchase_items.*,geopos_purchase.invoicedate,geopos_purchase.tid AS inv');
        $this->db->from('geopos_purchase_items');
        $this->db->join('geopos_purchase', 'geopos_purchase.id = geopos_purchase_items.tid', 'left');
        $this->db->where('DATE(geopos_purchase.invoicedate) >=', $sdate);
        $this->db->where('DATE(geopos_purchase.invoicedate) <=', $edate);
        if ($this->aauth->get_user()->loc) {
            $this->db->where('geopos_purchase.loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('geopos_purchase.loc', 0);
        }
        $this->db->where('geopos_purchase.csd', $customer);
        $query = $this->db->get();
        $result = $query->result_array();

        return $result;
    }
}

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

class Quotations_model extends CI_Model
{
    var $table = 'geopos_quotations';
    var $column_order = array(null, 'geopos_quotations.tid', 'geopos_customers.name', 'geopos_customers.company', 'geopos_quotations.invoicedate', 'geopos_quotations.total', 'geopos_quotations.status', null);
    var $column_search = array('geopos_quotations.tid', 'geopos_customers.name', 'geopos_customers.company', 'geopos_quotations.invoicedate', 'geopos_quotations.total', 'geopos_quotations.status');
    var $order = array('geopos_quotations.tid' => 'desc');

    public function __construct()
    {
        parent::__construct();
        $this->load->model('settings_model', 'settings');
    }

    public function lastquotation()
    {
        // Use caching for last quotation number
        $cache_key = 'last_quotation_' . $this->aauth->get_user()->loc;
        $this->load->driver('cache');
        
        $last_quotation = $this->cache->get($cache_key);
        
        if ($last_quotation === FALSE) {
            $this->db->select('tid');
            $this->db->from($this->table);
            $this->db->order_by('tid', 'DESC');
            $this->db->limit(1);
            $this->db->where('i_class', 0);
            
            if ($this->aauth->get_user()->loc) {
                $this->db->where('loc', $this->aauth->get_user()->loc);
            } elseif (!BDATA) {
                $this->db->where('loc', 0);
            }
            
            $query = $this->db->get();
            if ($query->num_rows() > 0) {
                $last_quotation = $query->row()->tid;
            } else {
                $last_quotation = 0;
            }
            
            // Cache for 1 minute
            $this->cache->save($cache_key, $last_quotation, 60);
        }
        
        return $last_quotation;
    }

    public function quotation_details($id = '', $eid = '', $p = true)
    {
        $this->db->select('geopos_quotations.*,SUM(geopos_quotations.shipping + geopos_quotations.ship_tax) AS shipping, geopos_quotations.tid AS tid, geopos_customers.*,geopos_quotations.loc as loc,geopos_quotations.id AS iid,geopos_customers.id AS cid,geopos_terms.id AS termid,geopos_terms.title AS termtit,geopos_terms.terms AS terms');
        $this->db->from($this->table);
        $this->db->where('geopos_quotations.id', $id);
        if ($eid) {
            $this->db->where('geopos_quotations.eid', $eid);
        }
        if ($p) {
            if ($this->aauth->get_user()->loc) {
                $this->db->where('geopos_quotations.loc', $this->aauth->get_user()->loc);
            } elseif (!BDATA) {
                $this->db->where('geopos_quotations.loc', 0);
            }
        }
        $this->db->join('geopos_customers', 'geopos_quotations.csd = geopos_customers.id', 'left');
        $this->db->join('geopos_terms', 'geopos_terms.id = geopos_quotations.term', 'left');
        $query = $this->db->get();
        return $query->row_array();
    }

    public function quotation_products($id)
    {
        $this->db->select('geopos_quotation_items.*, geopos_products.product_name, geopos_products.product_code as product_code');
        $this->db->from('geopos_quotation_items');
        $this->db->join('geopos_products', 'geopos_products.pid = geopos_quotation_items.pid', 'left');
        $this->db->where('geopos_quotation_items.tid', $id);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function items_with_product($id)
    {
        $this->db->select('geopos_quotation_items.*, geopos_products.qty AS alert, geopos_products.code_type AS vattype, geopos_products.product_price AS cost');
        $this->db->from('geopos_quotation_items');
        $this->db->where('geopos_quotation_items.tid', $id);
        $this->db->join('geopos_products', 'geopos_products.pid = geopos_quotation_items.pid', 'left');
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

    // COMMENTED OUT: Multi-currency support - Now using ONLY geopos_system.currency
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
        $currency = strtoupper($row['currency']);
        return array('code' => $currency, 'symbol' => $currency);
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

    public function quotation_transactions($id)
    {
        $this->db->select('*');
        $this->db->from('geopos_quotation_transactions');
        $this->db->where('tid', $id);
        $this->db->where('ext', 0);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function quotation_delete($id, $eid = '')
    {
        $this->db->trans_start();
        $this->db->select('tid,total,status,csd');
        $this->db->from('geopos_quotations');
        $this->db->where('id', $id);
        $query = $this->db->get();
        $result = $query->row_array();

        // Remove quotation transactions entry
        $this->db->where('tid', $id);
        $this->db->delete('geopos_quotation_transactions');

        if ($this->aauth->get_user()->loc) {
            if ($eid) {
                $res = $this->db->delete('geopos_quotations', array('id' => $id, 'eid' => $eid, 'loc' => $this->aauth->get_user()->loc));
            } else {
                $res = $this->db->delete('geopos_quotations', array('id' => $id, 'loc' => $this->aauth->get_user()->loc));
            }
        } else {
            if (BDATA) {
                if ($eid) {
                    $res = $this->db->delete('geopos_quotations', array('id' => $id, 'eid' => $eid));
                } else {
                    $res = $this->db->delete('geopos_quotations', array('id' => $id));
                }
            } else {
                if ($eid) {
                    $res = $this->db->delete('geopos_quotations', array('id' => $id, 'eid' => $eid, 'loc' => 0));
                } else {
                    $res = $this->db->delete('geopos_quotations', array('id' => $id, 'loc' => 0));
                }
            }
        }

        $affect = $this->db->affected_rows();

        if ($res) {
            if ($affect) $this->db->delete('geopos_quotation_items', array('tid' => $id));

            $data = array('type' => 10, 'rid' => $id);
            $this->db->delete('geopos_metadata', $data);

            if ($this->db->trans_complete()) {
                return true;
            } else {
                return false;
            }
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

        // Build the query
        $this->db->select('
            geopos_quotations.id,
            geopos_quotations.tid,
            geopos_quotations.tax,
            geopos_quotations.csd,
            geopos_quotations.salesperson,
            geopos_quotations.invoicedate,
            geopos_quotations.invoiceduedate,
            geopos_quotations.total,
            geopos_quotations.pamnt,
            geopos_quotations.status,
            geopos_customers.name,
            geopos_customers.company,
            geopos_quotations.print_status,
            SUM(geopos_quotation_items.tax * geopos_quotation_items.qty) AS item_tax,
            SUM(geopos_quotation_items.qty) AS item_qty,
            SUM(geopos_quotation_items.price * geopos_quotation_items.qty) AS item_price
        ');
        $this->db->from($this->table);
        $this->db->join('geopos_customers', 'geopos_quotations.csd = geopos_customers.id', 'left');
        $this->db->join('geopos_quotation_items', 'geopos_quotation_items.tid = geopos_quotations.id', 'left');
        $this->db->group_by('geopos_quotations.id');

        // Filter by due date or param
        if ($due_date) {
            $this->db->where('geopos_quotations.status', 'pending');
            $this->db->where('DATE(geopos_quotations.invoiceduedate)', $due_date);
        } else {
            $this->db->where('geopos_quotations.i_class', 0);
            $this->db->where('geopos_quotations.r_time !=', 'mig');
            if ($start_param && $end_param) {
                $this->db->where('DATE(geopos_quotations.invoicedate) >=', $start_param);
                $this->db->where('DATE(geopos_quotations.invoicedate) <=', $end_param);
            }
            if ($param == 'date_override') {
                if ($include_date && $this->input->post('start_date') && $this->input->post('end_date')) {
                    $this->db->where('DATE(geopos_quotations.invoicedate) >=', datefordatabase($this->input->post('start_date')));
                    $this->db->where('DATE(geopos_quotations.invoicedate) <=', datefordatabase($this->input->post('end_date')));
                }
            }

            if ($opt) {
                $this->db->where('geopos_quotations.eid', $opt);
            }

            if ($this->aauth->get_user()->loc) {
                $this->db->where('geopos_quotations.loc', $this->aauth->get_user()->loc);
            } elseif (!BDATA) {
                $this->db->where('geopos_quotations.loc', 0);
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

    public function get_datatables($opt = '', $expression = '', $expression2 = '', $value = '', $value2 = '', $joinQuery = '', $invoices = [], $driver = '', $param = '', $due_date = '')
    {
        // Build query
        $this->_get_datatables_query(
            $opt,
            !($expression != "" && $expression2 != "" && $value != "0" && $value2 != "0" || count($invoices) > 0),
            $param,
            $due_date
        );

        // Apply filters
        if (count($invoices) > 0) {
            $this->db->where_in('geopos_quotations.tid', $invoices);
        } elseif ($expression != "" && $expression2 != "" && $value != "0" && $value2 != "0") {
            $this->db->where($expression, $value);
            if ($joinQuery == " and ") {
                $this->db->where($expression2, $value2);
            } elseif ($joinQuery == " or ") {
                $this->db->or_where($expression2, $value2);
            }
        }

        if ($driver != "") {
            $this->db->like('geopos_quotations.driver_name', $driver, 'both');
        }

        // Location filter
        if ($this->aauth->get_user()->loc) {
            $this->db->where('geopos_quotations.loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('geopos_quotations.loc', 0);
        }

        // Limit for pagination
        $defaultLength = 25;
        $maxLength = 100;
        $postLength = isset($_POST['length']) ? (int)$_POST['length'] : $defaultLength;
        $postStart = isset($_POST['start']) ? (int)$_POST['start'] : 0;
        if ($postLength === -1) {
            $length = -1;
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
        $this->_get_datatables_query(
            $opt,
            !($expression != "" && $expression2 != "" && $value != "0" && $value2 != "0" || count($invoices) > 0),
            $param,
            $due_date
        );

        if (count($invoices) > 0) {
            $this->db->where_in('geopos_quotations.tid', $invoices);
        } elseif ($expression != "" && $expression2 != "" && $value != "0" && $value2 != "0") {
            $this->db->where($expression, $value);
            if ($joinQuery == " and ") {
                $this->db->where($expression2, $value2);
            } elseif ($joinQuery == " or ") {
                $this->db->or_where($expression2, $value2);
            }
        }

        if ($driver != "") {
            $this->db->like('geopos_quotations.driver_name', $driver, 'both');
        }

        if ($this->aauth->get_user()->loc) {
            $this->db->where('geopos_quotations.loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('geopos_quotations.loc', 0);
        }

        return $this->db->count_all_results();
    }

    public function count_all($opt = '')
    {
        $this->db->select('geopos_quotations.id');
        $this->db->from($this->table);
        $this->db->where('geopos_quotations.i_class', 0);
        if ($opt) {
            $this->db->where('geopos_quotations.eid', $opt);
        }
        if ($this->aauth->get_user()->loc) {
            $this->db->where('geopos_quotations.loc', $this->aauth->get_user()->loc);
        } elseif (!BDATA) {
            $this->db->where('geopos_quotations.loc', 0);
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
        $this->db->where('geopos_metadata.type', 10);
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

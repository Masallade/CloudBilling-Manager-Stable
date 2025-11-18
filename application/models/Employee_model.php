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

class Employee_model extends CI_Model
{

    public function list_employee()
    {
        $this->db->select('geopos_employees.*, geopos_users.banned, geopos_users.roleid, geopos_users.loc, geopos_users.email, roles.role_name');
        $this->db->from('geopos_employees');
        $this->db->join('geopos_users', 'geopos_employees.id = geopos_users.id', 'left');
        $this->db->join('roles', 'geopos_users.roleid = roles.id', 'left'); // Added this line

        if ($this->aauth->get_user()->loc) {
            $this->db->group_start();
            $this->db->where('geopos_users.loc', $this->aauth->get_user()->loc);
            if (BDATA) $this->db->or_where('geopos_users.loc', 0);
            $this->db->group_end();
        }

        $this->db->order_by('geopos_users.id', 'DESC');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function list_employee_driver()
    {
        $this->db->select('geopos_employees.*,geopos_users.banned,geopos_users.roleid,geopos_users.loc');
        $this->db->from('geopos_employees');

        $this->db->join('geopos_users', 'geopos_employees.id = geopos_users.id', 'left');
        $this->db->where('geopos_users.roleid', 4);
        if ($this->aauth->get_user()->loc) {
            $this->db->group_start();
            $this->db->where('geopos_users.loc', $this->aauth->get_user()->loc);
            if (BDATA) $this->db->or_where('loc', 0);
            $this->db->group_end();
        }
        $this->db->order_by('geopos_users.roleid', 'DESC');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function list_project_employee($id)
    {
        $this->db->select('geopos_employees.*');
        $this->db->from('geopos_project_meta');
        $this->db->where('geopos_project_meta.pid', $id);
        $this->db->where('geopos_project_meta.meta_key', 19);
        $this->db->join('geopos_employees', 'geopos_employees.id = geopos_project_meta.meta_data', 'left');
        $this->db->join('geopos_users', 'geopos_employees.id = geopos_users.id', 'left');
        $this->db->order_by('geopos_users.roleid', 'DESC');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function employee_details($id)
    {
        $this->db->select('geopos_employees.*,geopos_users.email,geopos_users.loc,geopos_users.roleid,');
        $this->db->from('geopos_employees');
        $this->db->where('geopos_employees.id', $id);
        $this->db->join('geopos_users', 'geopos_employees.id = geopos_users.id', 'left');
        $query = $this->db->get();
        return $query->row_array();
    }

    public function salary_history($id)
    {
        $this->db->select('*');
        $this->db->from('geopos_hrm');
        $this->db->where('typ', 1);
        $this->db->where('rid', $id);
        $query = $this->db->get();
        return $query->result_array();
    }
    public function get_roles()
    {
        return $this->db->get('roles')->result_array();
    }

    public function get_role($role_id)
    {
        return $this->db->where('id', $role_id)
            ->get('roles')
            ->row_array();
    }

    public function get_role_by_name($role_name)
    {
        return $this->db->where('role_name', $role_name)
            ->get('roles')
            ->row_array();
    }

    public function role_exists($role_name)
    {
        return $this->db->where('role_name', $role_name)
            ->get('roles')
            ->num_rows() > 0;
    }

    public function get_role_permissions($role_id)
    {
        return $this->db->where('role_id', $role_id)
            ->get('role_permissions')
            ->result_array();
    }

    public function add_role($data)
    {
        $this->db->insert('roles', $data);
        return $this->db->insert_id();
    }

    public function update_role($role_id, $data)
    {
        $this->db->where('id', $role_id);
        return $this->db->update('roles', $data);
    }

    public function save_permissions($role_id, $permissions)
    {
        try {
            $this->db->trans_start();

            $this->db->where('role_id', $role_id);
            $this->db->delete('role_permissions');

            $data = [];
            foreach ($permissions as $key => $value) {
                $data[] = [
                    'role_id' => $role_id,
                    'permission_key' => $key,
                    'permission_value' => (int)$value
                ];
            }

            if (!empty($data)) {
                $this->db->insert_batch('role_permissions', $data);
            }

            $this->db->trans_complete();
            return $this->db->trans_status();
        } catch (Exception $e) {
            log_message('error', 'Failed to save permissions: ' . $e->getMessage());
            return false;
        }
    }


    public function update_employee($id, $name, $phone, $phonealt, $address, $city, $region, $country, $postbox, $location, $salary = 0, $department = -1, $commission = 0, $roleid = false)
    {
        // Start transaction for data integrity
        $this->db->trans_start();

        try {
            // Get current salary for comparison
            $this->db->select('salary');
            $this->db->from('geopos_employees');
            $this->db->where('id', $id);
            $query = $this->db->get();
            $sal = $query->row_array();

            // Get current role
            $this->db->select('roleid');
            $this->db->from('geopos_users');
            $this->db->where('id', $id);
            $query = $this->db->get();
            $role = $query->row_array();

            // Validate employee exists
            if (!$sal) {
                $this->db->trans_rollback();
                ob_clean();
                echo json_encode(array(
                    'status' => 'Error',
                    'message' => $this->lang->line('ERROR') . ': Employee not found.'
                ));
                return;
            }

            // Prepare employee data
            $data = array(
                'name' => $name,
                'phone' => $phone,
                'phonealt' => $phonealt,
                'address' => $address,
                'city' => $city,
                'region' => $region,
                'country' => $country,
                'postbox' => $postbox,
                'salary' => $salary,
                'c_rate' => $commission
            );

            // Add department if provided
            if ($department > -1) {
                $data['dept'] = $department;
            }

            // Update employee record
            $this->db->where('id', $id);
            if (!$this->db->update('geopos_employees', $data)) {
                $this->db->trans_rollback();
                ob_clean();
                echo json_encode(array(
                    'status' => 'Error',
                    'message' => $this->lang->line('ERROR') . ': Failed to update employee record.'
                ));
                return;
            }

            // Update user record (location and role)
            if ($roleid !== false) {
                $userData = array(
                    'loc' => $location,
                    'roleid' => $roleid
                );

                $this->db->where('id', $id);
                if (!$this->db->update('geopos_users', $userData)) {
                    $this->db->trans_rollback();
                    ob_clean();
                    echo json_encode(array(
                        'status' => 'Error',
                        'message' => $this->lang->line('ERROR') . ': Failed to update user record.'
                    ));
                    return;
                }
            } else {
                // Update only location if roleid not provided
                $this->db->where('id', $id);
                if (!$this->db->update('geopos_users', array('loc' => $location))) {
                    $this->db->trans_rollback();
                    ob_clean();
                    echo json_encode(array(
                        'status' => 'Error',
                        'message' => $this->lang->line('ERROR') . ': Failed to update user location.'
                    ));
                    return;
                }
            }

            // Record salary change in HRM if salary changed
            if (isset($sal['salary']) && ($salary != $sal['salary']) && ($salary > 0.00)) {
                $hrmData = array(
                    'typ' => 1,
                    'rid' => $id,
                    'val1' => $salary,
                    'val2' => $sal['salary'],
                    'val3' => date('Y-m-d H:i:s')
                );
                if (!$this->db->insert('geopos_hrm', $hrmData)) {
                    // Log error but don't fail the entire update
                    log_message('error', 'Failed to record salary change for employee ID: ' . $id);
                }
            }

            // Commit transaction
            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                ob_clean();
                echo json_encode(array(
                    'status' => 'Error',
                    'message' => $this->lang->line('ERROR') . ': Transaction failed.'
                ));
                return;
            }

            // Set flash message for fallback (if redirect needed)
            $this->session->set_flashdata('success', $this->lang->line('UPDATED'));

            // Return success response
            ob_clean();
            echo json_encode(array(
                'status' => 'Success',
                'message' => $this->lang->line('UPDATED') . ': Employee updated successfully!'
            ));
            return;
        } catch (Exception $e) {
            $this->db->trans_rollback();
            log_message('error', 'Employee update failed: ' . $e->getMessage());
            ob_clean();
            echo json_encode(array(
                'status' => 'Error',
                'message' => $this->lang->line('ERROR') . ': ' . $e->getMessage()
            ));
            return;
        }
    }

    public function update_password($id, $cpassword, $newpassword, $renewpassword) {}

    public function editpicture($id, $pic)
    {
        $this->db->select('picture');
        $this->db->from('geopos_employees');
        $this->db->where('id', $id);

        $query = $this->db->get();
        $result = $query->row_array();

        $data = array(
            'picture' => $pic
        );

        $this->db->set($data);
        $this->db->where('id', $id);
        if ($this->db->update('geopos_employees')) {
            $this->db->set($data);
            $this->db->where('id', $id);
            $this->db->update('geopos_users');

            // Delete old picture files if they exist
            if (!empty($result['picture'])) {
                if (file_exists(FCPATH . 'userfiles/employee/' . $result['picture'])) {
                    unlink(FCPATH . 'userfiles/employee/' . $result['picture']);
                }
                if (file_exists(FCPATH . 'userfiles/employee/thumbnail/' . $result['picture'])) {
                    unlink(FCPATH . 'userfiles/employee/thumbnail/' . $result['picture']);
                }
            }

            // Return success response
            echo json_encode(array(
                'status' => 'Success',
                'message' => 'Profile picture updated successfully!',
                'filename' => $pic
            ));
            return true;
        } else {
            // Return error response
            echo json_encode(array(
                'status' => 'Error',
                'message' => 'Failed to update profile picture.'
            ));
            return false;
        }
    }


    public function editsign($id, $pic)
    {
        $this->db->select('sign');
        $this->db->from('geopos_employees');
        $this->db->where('id', $id);

        $query = $this->db->get();
        $result = $query->row_array();

        $data = array(
            'sign' => $pic
        );

        $this->db->set($data);
        $this->db->where('id', $id);
        if ($this->db->update('geopos_employees')) {
            // Delete old signature files if they exist
            if (!empty($result['sign'])) {
                if (file_exists(FCPATH . 'userfiles/employee_sign/' . $result['sign'])) {
                    unlink(FCPATH . 'userfiles/employee_sign/' . $result['sign']);
                }
                if (file_exists(FCPATH . 'userfiles/employee_sign/thumbnail/' . $result['sign'])) {
                    unlink(FCPATH . 'userfiles/employee_sign/thumbnail/' . $result['sign']);
                }
            }

            // Return success response
            echo json_encode(array(
                'status' => 'Success',
                'message' => 'Signature updated successfully!',
                'filename' => $pic
            ));
            return true;
        } else {
            // Return error response
            echo json_encode(array(
                'status' => 'Error',
                'message' => 'Failed to update signature.'
            ));
            return false;
        }
    }


    var $table = 'geopos_invoices';
    var $column_order = array(null, 'geopos_invoices.tid', 'geopos_invoices.invoicedate', 'geopos_invoices.total', 'geopos_invoices.status');
    var $column_search = array('geopos_invoices.tid', 'geopos_invoices.invoicedate', 'geopos_invoices.total', 'geopos_invoices.status');
    var $order = array('geopos_invoices.tid' => 'asc');


    private function _invoice_datatables_query($id)
    {
        $this->db->select('geopos_invoices.*,geopos_customers.name');
        $this->db->from('geopos_invoices');
        $this->db->where('geopos_invoices.eid', $id);
        $this->db->join('geopos_customers', 'geopos_invoices.csd=geopos_customers.id', 'left');

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

    function invoice_datatables($id)
    {
        $this->_invoice_datatables_query($id);
        if ($this->input->post('length') != -1)
            $this->db->limit($this->input->post('length'), $this->input->post('start'));
        $query = $this->db->get();
        return $query->result();
    }

    function invoicecount_filtered($id)
    {
        $this->_invoice_datatables_query($id);
        $query = $this->db->get();
        return $query->num_rows();
    }

    public function invoicecount_all($id)
    {
        $this->db->select('geopos_invoices.*,geopos_customers.name');
        $this->db->from('geopos_invoices');
        $this->db->where('geopos_invoices.eid', $id);
        $this->db->join('geopos_customers', 'geopos_invoices.csd=geopos_customers.id', 'left');
        $query = $this->db->get();
        return $query->num_rows();
    }

    //transaction


    var $tcolumn_order = array(null, 'account', 'type', 'cat', 'amount', 'stat');
    var $tcolumn_search = array('id', 'account');
    var $torder = array('id' => 'asc');
    var $eid = '';

    private function _get_datatables_query()
    {

        $this->db->from('geopos_transactions');

        $this->db->where('eid', $this->eid);


        $i = 0;

        foreach ($this->tcolumn_search as $item) // loop column
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

                if (count($this->tcolumn_search) - 1 == $i) //last loop
                    $this->db->group_end(); //close bracket
            }
            $i++;
        }

        if (isset($_POST['order'])) // here order processing
        {
            $this->db->order_by($this->tcolumn_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        } else if (isset($this->torder)) {
            $order = $this->torder;
            $this->db->order_by(key($order), $order[key($order)]);
        }
    }

    function get_datatables($eid)
    {
        $this->eid = $eid;
        $this->_get_datatables_query();
        if ($_POST['length'] != -1)
            $this->db->limit($_POST['length'], $_POST['start']);
        $query = $this->db->get();
        return $query->result();
    }

    function count_filtered($eid)
    {
        $this->eid = $eid;
        $this->_get_datatables_query();
        $query = $this->db->get();
        return $query->num_rows();
    }

    public function count_all($eid)
    {
        $this->db->from('geopos_transactions');
        $this->db->where('eid', $eid);
        return $this->db->count_all_results();
    }


    public function add_employee($id, $username, $name, $roleid, $phone, $address, $city, $region, $country, $postbox, $location, $salary = 0, $commission = 0, $department = 0, $picture_file = null, $sign_file = null)
    {
        // Start transaction for data integrity
        $this->db->trans_start();

        try {
            $data = array(
                'id' => $id,
                'username' => $username,
                'name' => $name,
                'address' => $address,
                'city' => $city,
                'region' => $region,
                'country' => $country,
                'postbox' => $postbox,
                'phone' => $phone,
                'dept' => $department,
                'salary' => $salary,
                'c_rate' => $commission
            );

            // Add picture if provided
            if (!empty($picture_file)) {
                $data['picture'] = $picture_file;
            }

            // Add signature if provided
            if (!empty($sign_file)) {
                $data['sign'] = $sign_file;
            }

            // Insert employee record
            if (!$this->db->insert('geopos_employees', $data)) {
                $this->db->trans_rollback();
                return array(
                    'status' => 'Error',
                    'message' => $this->lang->line('ERROR') . ': Failed to create employee record.',
                    'redirect_url' => ''
                );
            }

            // Update user record with role and location
            $data1 = array(
                'roleid' => $roleid,
                'loc' => $location
            );

            // Update picture in users table if provided
            if (!empty($picture_file)) {
                $data1['picture'] = $picture_file;
            }

            $this->db->where('id', $id);
            if (!$this->db->update('geopos_users', $data1)) {
                $this->db->trans_rollback();
                return array(
                    'status' => 'Error',
                    'message' => $this->lang->line('ERROR') . ': Failed to update user record.',
                    'redirect_url' => ''
                );
            }

            // Commit transaction
            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                return array(
                    'status' => 'Error',
                    'message' => $this->lang->line('ERROR') . ': Transaction failed.',
                    'redirect_url' => ''
                );
            }

            // Set flash message for success notification on redirect
            $CI = &get_instance();
            if (!isset($CI->session)) {
                $CI->load->library('session');
            }
            $CI->session->set_flashdata('success_message', $this->lang->line('ADDED') . ': Employee created successfully! User can now login with username: ' . $username);

            // Return success response
            return array(
                'status' => 'Success',
                'message' => $this->lang->line('ADDED') . ': Employee created successfully! User can now login with username: ' . $username,
                'redirect_url' => base_url('employee')
            );
        } catch (Exception $e) {
            $this->db->trans_rollback();
            log_message('error', 'Employee creation failed: ' . $e->getMessage());
            return array(
                'status' => 'Error',
                'message' => $this->lang->line('ERROR') . ': ' . $e->getMessage(),
                'redirect_url' => ''
            );
        }
    }

    public function employee_validate($email)
    {
        $this->db->select('*');
        $this->db->from('geopos_users');
        $this->db->where('email', $email);
        $query = $this->db->get();
        return $query->row_array();
    }

    public function money_details($eid)
    {
        $this->db->select('SUM(debit) AS debit,SUM(credit) AS credit');
        $this->db->from('geopos_transactions');
        $this->db->where('eid', $eid);
        $query = $this->db->get();
        return $query->row_array();
    }

    public function sales_details($eid)
    {
        $this->db->select('SUM(pamnt) AS total');
        $this->db->from('geopos_invoices');
        $this->db->where('eid', $eid);
        $query = $this->db->get();
        return $query->row_array();
    }

    public function employee_permissions()
    {
        $this->db->select('*');
        $this->db->from('geopos_premissions');
        $this->db->order_by('sequence', 'ASC');
        $query = $this->db->get();
        return $query->result_array();
    }

    //documents list

    var $doccolumn_order = array(null, 'val1', 'val2', null);
    var $doccolumn_search = array('val1', 'val2');


    function addholidays($loc, $hday, $hdayto, $note)
    {
        $data = array('typ' => 2, 'rid' => $loc, 'val1' => $hday, 'val2' => $hdayto, 'val3' => $note);
        return $this->db->insert('geopos_hrm', $data);
    }

    function deleteholidays($id)
    {

        if ($this->db->delete('geopos_hrm', array('id' => $id, 'typ' => 2))) {


            return true;
        } else {
            return false;
        }
    }


    function holidays_datatables()
    {
        $this->holidays_datatables_query();
        if ($this->input->post('length') != -1)
            $this->db->limit($this->input->post('length'), $this->input->post('start'));
        $query = $this->db->get();
        return $query->result();
    }

    private function holidays_datatables_query()
    {

        $this->db->from('geopos_hrm');
        $this->db->where('typ', 2);
        if ($this->aauth->get_user()->loc) {
            $this->db->where('rid', $this->aauth->get_user()->loc);
        }
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
            $order = $this->doccolumn_order;
            $this->db->order_by(key($order), $order[key($order)]);
        }
    }

    function holidays_count_filtered()
    {
        $this->holidays_datatables_query();
        $query = $this->db->get();
        return $query->num_rows();
    }

    public function holidays_count_all()
    {
        $this->holidays_datatables_query();
        $query = $this->db->get();
        return $query->num_rows();
    }

    public function hday_view($id, $loc)
    {
        $this->db->select('*');
        $this->db->from('geopos_hrm');
        $this->db->where('id', $id);
        $this->db->where('typ', 2);
        if ($this->aauth->get_user()->loc) {
            $this->db->where('rid', $loc);
        }

        $query = $this->db->get();
        return $query->row_array();
    }

    public function edithday($id, $loc, $from, $todate, $note)
    {

        $data = array('typ' => 2, 'val1' => $from, 'val2' => $todate, 'val3' => $note);


        $this->db->set($data);
        $this->db->where('id', $id);
        if ($this->aauth->get_user()->loc) {
            $this->db->where('rid', $loc);
        }


        $this->db->update('geopos_hrm');
        return true;
    }

    public function department_list($rid)
    {
        // var_dump($id, $rid);
        // exit;
        $this->db->select('*');
        $this->db->from('geopos_hrm');
        $this->db->where('typ', 3);
        if ($this->aauth->get_user()->loc) {
            $this->db->where('rid', $rid);
        }
        $query = $this->db->get();
        return $query->result_array();
    }

    public function department_elist($id)
    {
        $this->db->select('*');
        $this->db->from('geopos_employees');

        $this->db->where('dept', $id);
        $query = $this->db->get();
        return $query->result_array();
    }


    public function department_view($id, $loc)
    {
        $this->db->select('*');
        $this->db->from('geopos_hrm');
        $this->db->where('id', $id);
        $this->db->where('typ', 3);
        if ($this->aauth->get_user()->loc) {
            $this->db->where('rid', $loc);
        }


        $query = $this->db->get();
        return $query->row_array();
    }

    function adddepartment($loc, $name)
    {
        $data = array('typ' => 3, 'rid' => $loc, 'val1' => $name);
        return $this->db->insert('geopos_hrm', $data);
    }

    function deletedepartment($id)
    {

        if ($this->db->delete('geopos_hrm', array('id' => $id, 'typ' => 3))) {


            return true;
        } else {
            return false;
        }
    }

    public function editdepartment($id, $loc, $name)
    {

        $data = array(
            'val1' => $name
        );


        $this->db->set($data);
        $this->db->where('id', $id);
        if ($this->aauth->get_user()->loc) {
            $this->db->where('rid', $loc);
        }


        $this->db->update('geopos_hrm');
        return true;
    }

    //payroll

    private function _pay_get_datatables_query($eid)
    {

        $this->db->from('geopos_transactions');
        if ($this->aauth->get_user()->loc) {
            $this->db->where('loc', $this->aauth->get_user()->loc);
        }
        $this->db->where('ext', 4);
        if ($eid) {
            $this->db->where('supplier', $eid);
        }


        $i = 0;

        foreach ($this->tcolumn_search as $item) // loop column
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

                if (count($this->tcolumn_search) - 1 == $i) //last loop
                    $this->db->group_end(); //close bracket
            }
            $i++;
        }

        if (isset($_POST['order'])) // here order processing
        {
            $this->db->order_by($this->tcolumn_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        } else if (isset($this->torder)) {
            $order = $this->torder;
            $this->db->order_by(key($order), $order[key($order)]);
        }
    }

    function pay_get_datatables($eid)
    {

        $this->_pay_get_datatables_query($eid);
        if ($_POST['length'] != -1)
            $this->db->limit($_POST['length'], $_POST['start']);
        $query = $this->db->get();
        return $query->result();
    }

    function pay_count_filtered($eid)
    {
        $this->db->from('geopos_transactions');
        $this->db->where('ext', 4);
        if ($eid) {
            $this->db->where('supplier', $eid);
        }
        $query = $this->db->get();
        return $query->num_rows();
    }

    public function pay_count_all($eid)
    {
        $this->db->from('geopos_transactions');
        $this->db->where('ext', 4);
        if ($eid) {
            $this->db->where('supplier', $eid);
        }
        return $this->db->count_all_results();
    }


    function addattendance($emp, $adate, $tfrom, $tto, $note)
    {
        $insertedCount = 0;
        $skippedCount = 0;

        foreach ($emp as $row) {
            $this->db->where('emp', $row);
            $this->db->where('DATE(adate)', $adate);
            $num = $this->db->count_all_results('geopos_attendance');

            if (!$num) {
                $data = array('emp' => $row, 'created' => date('Y-m-d H:i:s'), 'adate' => $adate, 'tfrom' => $tfrom, 'tto' => $tto, 'note' => $note);
                if ($this->db->insert('geopos_attendance', $data)) {
                    $insertedCount++;
                }
            } else {
                $skippedCount++;
            }
        }

        return array('inserted' => $insertedCount, 'skipped' => $skippedCount);
    }

    function deleteattendance($id)
    {

        if ($this->db->delete('geopos_attendance', array('id' => $id))) {
            return true;
        } else {
            return false;
        }
    }

    var $acolumn_order = array(null, 'geopos_attendance.emp', 'geopos_attendance.adate', null, null);
    var $acolumn_search = array('geopos_employees.name', 'geopos_attendance.adate');

    function attendance_datatables($cid)
    {
        $this->attendance_datatables_query($cid);
        if ($this->input->post('length') != -1)
            $this->db->limit($this->input->post('length'), $this->input->post('start'));
        $query = $this->db->get();
        return $query->result();
    }

    private function attendance_datatables_query($cid = 0)
    {
        $this->db->select('geopos_attendance.*,geopos_employees.name');
        $this->db->from('geopos_attendance');
        $this->db->join('geopos_employees', 'geopos_employees.id=geopos_attendance.emp', 'left');
        if ($this->aauth->get_user()->loc) {
            $this->db->join('geopos_users', 'geopos_users.id=geopos_attendance.emp', 'left');
            $this->db->where('geopos_users.loc', $this->aauth->get_user()->loc);
        }
        if ($cid) $this->db->where('geopos_attendance.emp', $cid);
        $i = 0;

        foreach ($this->acolumn_search as $item) // loop column
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

                if (count($this->acolumn_search) - 1 == $i) //last loop
                    $this->db->group_end(); //close bracket
            }
            $i++;
        }
        $search = $this->input->post('order');
        if ($search) {
            $this->db->order_by($this->acolumn_order[$search['0']['column']], $search['0']['dir']);
        } else if (isset($this->acolumn_order)) {
            $order = $this->acolumn_order;
            $this->db->order_by(key($order), $order[key($order)]);
        }
    }

    function attendance_count_filtered($cid)
    {
        $this->attendance_datatables_query($cid);
        $query = $this->db->get();
        return $query->num_rows();
    }

    public function attendance_count_all($cid)
    {
        $this->attendance_datatables_query($cid);
        $query = $this->db->get();
        return $query->num_rows();
    }

    public function getAttendance($emp, $start, $end)
    {

        $sql = "SELECT  CONCAT(tfrom, ' - ', tto) AS title,DATE(adate) as start ,DATE(adate) as end FROM geopos_attendance WHERE (emp='$emp') AND (DATE(adate) BETWEEN ? AND ? ) ORDER BY DATE(adate) ASC";
        return $this->db->query($sql, array($start, $end))->result();
    }

    public function getHolidays($loc, $start, $end)
    {

        $sql = "SELECT  CONCAT(DATE(val1), ' - ', DATE(val2),' - ',val3) AS title,DATE(val1) as start ,DATE(val2) as end FROM geopos_hrm WHERE  (typ='2') AND  (rid='$loc') AND (DATE(val1) BETWEEN ? AND ? ) ORDER BY DATE(val1) ASC";
        return $this->db->query($sql, array($start, $end))->result();
    }


    public function salary_view($eid)
    {
        $this->db->from('geopos_transactions');
        $this->db->where('ext', 4);
        $this->db->where('supplier', $eid);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function autoattend($opt)
    {
        $this->db->set('key1', $opt);
        $this->db->where('id', 62);

        $this->db->update('univarsal_api');
        return true;
    }
}

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

class Employee extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('employee_model', 'employee');
        $this->load->model('settings_model', 'settings');
        $this->load->library('form_validation');
        $this->load->library("Aauth");
        if (!$this->aauth->is_loggedin()) {
            redirect('/user/', 'refresh');
        }
        if (!$this->aauth->permission_new(null, 'usersAccess')) {

            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $this->li_a = 'emp';
    }

    public function index()
    {
        $head['usernm'] = $this->aauth->get_user()->username;
        $head['title'] = 'Employees List';
        $data['employee'] = $this->employee->list_employee();

        // Get flash message if exists (from redirect after adding employee)
        $data['success_message'] = $this->session->flashdata('success_message');
        $data['error_message'] = $this->session->flashdata('error_message');

        $this->load->view('fixed/header', $head);
        $this->load->view('employee/list', $data);
        $this->load->view('fixed/footer');
    }

    public function employee_list()
    {
        $list = $this->employee->list_employee();
        $data = array();
        $no = 1;
        $current_user_id = $this->aauth->get_user()->id; // Get current user ID

        foreach ($list as $row) {
            $aid = $row['id'];
            $username = $row['username'];
            $name = $row['name'];
            $email = $row['email'];
            $role = $row['role_name'];
            $status = $row['banned'];
            $is_current_user = ($aid == $current_user_id); // Check if this is current user

            if ($status == 1) {
                $status_text = 'Deactive';
                // Hide enable button for current user
                $btn = $is_current_user ? '' : "<a href='#' data-object-id='" . $aid . "' class='btn btn-info btn-xs manage-object' title='Enable'><i class='fa fa-power-off'></i></a>";
            } else {
                $status_text = 'Active';
                // Hide disable button for current user
                $btn = $is_current_user ? '' : "<a href='#' data-object-id='" . $aid . "' class='btn btn-warning btn-xs manage-object' title='Disable'><i class='fa fa-power-off'></i></a>";
            }

            $row_data = array();
            $row_data[] = $no;
            $row_data[] = $username;
            $row_data[] = $email;
            $row_data[] = $role;
            $row_data[] = $status_text;

            $actions = '';
            if ($this->aauth->permission_new(null, 'usersEmployeesView')) {
                $actions .= "<a href='" . base_url("employee/view?id=$aid") . "' class='btn btn-success btn-xs' title='View'><i class='fa fa-eye' aria-hidden='true'></i></a>&nbsp;";
            }

            if ($this->aauth->permission_new(null, 'usersEmployeesManage')) {
                $actions .= "$btn &nbsp;";
            }

            if ($this->aauth->permission_new(null, 'usersEmployeesDelete')) {
                // Hide delete button for current user
                if (!$is_current_user) {
                    $actions .= "<a href='#pop_model' data-toggle='modal' data-remote='false' data-object-id='" . $aid . "' class='btn btn-danger btn-xs delemp' title='Delete'><i class='fa fa-trash' aria-hidden='true'></i></a>";
                }
            }

            $row_data[] = $actions;
            $data[] = $row_data;
            $no++;
        }

        $output = array(
            "draw" => intval($this->input->post('draw')),
            "recordsTotal" => count($list),
            "recordsFiltered" => count($list),
            "data" => $data,
        );

        echo json_encode($output);
    }

    public function salaries()
    {
        $head['usernm'] = $this->aauth->get_user()->username;
        $head['title'] = 'Employees Salaries';
        $this->load->view('fixed/header', $head);
        $this->load->view('employee/salaries');
        $this->load->view('fixed/footer');
    }

    public function salaries_list()
    {
        // Get DataTables parameters
        $draw = intval($this->input->post('draw'));
        $start = intval($this->input->post('start'));
        $length = intval($this->input->post('length'));
        $search_value = isset($this->input->post('search')['value']) ? $this->input->post('search')['value'] : '';

        // Get all employees
        $all_employees = $this->employee->list_employee();
        $total_records = count($all_employees);

        // Apply search filter if provided
        $filtered_employees = $all_employees;
        if (!empty($search_value)) {
            $filtered_employees = array();
            foreach ($all_employees as $row) {
                $search_fields = array(
                    strtolower($row['name']),
                    strtolower($row['role_name'] ?? ''),
                    strtolower($row['banned'] == 1 ? 'Deactive' : 'Active'),
                    strtolower(amountExchange($row['salary'], 0, $row['loc']))
                );

                $search_lower = strtolower($search_value);
                foreach ($search_fields as $field) {
                    if (strpos($field, $search_lower) !== false) {
                        $filtered_employees[] = $row;
                        break;
                    }
                }
            }
        }
        $filtered_count = count($filtered_employees);

        // Apply sorting
        $order_column = 0;
        $order_dir = 'asc';
        if (isset($this->input->post('order')[0])) {
            $order_column = intval($this->input->post('order')[0]['column']);
            $order_dir = $this->input->post('order')[0]['dir'];
        }

        $sortable_columns = array('id', 'name', 'salary', 'role', 'status');
        if (isset($sortable_columns[$order_column])) {
            $sort_column = $sortable_columns[$order_column];

            if ($sort_column == 'role') {
                usort($filtered_employees, function ($a, $b) use ($order_dir) {
                    $role_a = $a['role_name'] ?? '';
                    $role_b = $b['role_name'] ?? '';
                    return $order_dir == 'asc' ? strcmp($role_a, $role_b) : strcmp($role_b, $role_a);
                });
            } elseif ($sort_column == 'status') {
                usort($filtered_employees, function ($a, $b) use ($order_dir) {
                    $status_a = $a['banned'] == 1 ? 'Deactive' : 'Active';
                    $status_b = $b['banned'] == 1 ? 'Deactive' : 'Active';
                    return $order_dir == 'asc' ? strcmp($status_a, $status_b) : strcmp($status_b, $status_a);
                });
            } elseif ($sort_column == 'salary') {
                usort($filtered_employees, function ($a, $b) use ($order_dir) {
                    $salary_a = floatval($a['salary']);
                    $salary_b = floatval($b['salary']);
                    return $order_dir == 'asc' ? $salary_a <=> $salary_b : $salary_b <=> $salary_a;
                });
            } else {
                usort($filtered_employees, function ($a, $b) use ($sort_column, $order_dir) {
                    $val_a = $a[$sort_column] ?? '';
                    $val_b = $b[$sort_column] ?? '';
                    return $order_dir == 'asc' ? strcmp($val_a, $val_b) : strcmp($val_b, $val_a);
                });
            }
        }

        // Apply pagination (if length is -1, meaning "All", show all records without pagination)
        $paginated_employees = $filtered_employees;
        // If length is greater than 0 (not -1), apply pagination with start and length
        if ($length > 0) {
            $paginated_employees = array_slice($filtered_employees, $start, $length);
        }
        // If length is -1, show all filtered records (ignore start parameter)

        // Format data for DataTables
        $data = array();
        // Adjust row number start based on pagination
        $no = ($length > 0) ? $start + 1 : 1;

        foreach ($paginated_employees as $row) {
            $aid = $row['id'];
            $name = $row['name'];
            $role = $row['role_name'] ?? '';
            $status = $row['banned'];
            $salary = amountExchange($row['salary'], 0, $row['loc']);

            if ($status == 1) {
                $status_text = 'Deactive';
            } else {
                $status_text = 'Active';
            }

            $row_data = array();
            $row_data[] = $no;
            $row_data[] = $name;
            $row_data[] = $salary;
            $row_data[] = $role;
            $row_data[] = $status_text;
            $row_data[] = "<a href='" . site_url("employee/history?id=$aid") . "' class='btn btn-success btn-xs' title='" . $this->lang->line('History') . "'><i class='fa fa-list-ul' aria-hidden='true'></i> " . $this->lang->line('History') . "</a>";

            $data[] = $row_data;
            $no++;
        }

        $output = array(
            "draw" => $draw,
            "recordsTotal" => $total_records,
            "recordsFiltered" => $filtered_count,
            "data" => $data,
        );

        echo json_encode($output);
    }


    public function view()
    {
        $id = $this->input->get('id');
        $head['usernm'] = $this->aauth->get_user()->username;
        $head['title'] = 'Employee Details';
        $data['employee'] = $this->employee->employee_details($id);
        $data['eid'] = intval($id);
        $this->load->view('fixed/header', $head);
        $this->load->view('employee/view', $data);
        $this->load->view('fixed/footer');
    }

    public function history()
    {
        $id = $this->input->get('id');
        $head['usernm'] = $this->aauth->get_user()->username;
        $head['title'] = 'Employee Salary History';
        $data['employee'] = $this->employee->employee_details($id);
        $data['eid'] = intval($id);
        $this->load->view('fixed/header', $head);
        $this->load->view('employee/history', $data);
        $this->load->view('fixed/footer');
    }

    public function history_list()
    {
        // Get employee ID and DataTables parameters
        $eid = intval($this->input->get('id')) ?: intval($this->input->post('eid'));
        $draw = intval($this->input->post('draw'));
        $start = intval($this->input->post('start'));
        $length = intval($this->input->post('length'));
        $search_value = isset($this->input->post('search')['value']) ? $this->input->post('search')['value'] : '';

        // Get employee details for location
        $employee = $this->employee->employee_details($eid);
        $employee_loc = isset($employee['loc']) ? $employee['loc'] : 0;

        // Get all salary history for this employee
        $all_history = $this->employee->salary_history($eid);
        $total_records = count($all_history);

        // Apply search filter if provided
        $filtered_history = $all_history;
        if (!empty($search_value)) {
            $filtered_history = array();
            foreach ($all_history as $row) {
                $current_amount = amountExchange($row['val1'], 0, $employee_loc);
                $previous_amount = amountExchange($row['val2'], 0, $employee_loc);
                $date = $row['val3'];
                $diff = $row['val1'] - $row['val2'];
                $diffp = $row['val2'] != 0 ? round(($diff / $row['val2']) * 100, 2) : 0;

                $search_fields = array(
                    strtolower($date),
                    strtolower($current_amount),
                    strtolower($previous_amount),
                    strtolower($diffp . '%')
                );

                $search_lower = strtolower($search_value);
                foreach ($search_fields as $field) {
                    if (strpos($field, $search_lower) !== false) {
                        $filtered_history[] = $row;
                        break;
                    }
                }
            }
        }
        $filtered_count = count($filtered_history);

        // Apply sorting
        $order_column = 0;
        $order_dir = 'desc'; // Default: newest first
        if (isset($this->input->post('order')[0])) {
            $order_column = intval($this->input->post('order')[0]['column']);
            $order_dir = $this->input->post('order')[0]['dir'];
        }

        $sortable_columns = array('id', 'date', 'current', 'previous', 'change');
        if (isset($sortable_columns[$order_column])) {
            $sort_column = $sortable_columns[$order_column];

            if ($sort_column == 'date') {
                usort($filtered_history, function ($a, $b) use ($order_dir) {
                    $date_a = strtotime($a['val3']);
                    $date_b = strtotime($b['val3']);
                    return $order_dir == 'asc' ? $date_a - $date_b : $date_b - $date_a;
                });
            } elseif ($sort_column == 'current') {
                usort($filtered_history, function ($a, $b) use ($order_dir) {
                    $val_a = floatval($a['val1']);
                    $val_b = floatval($b['val1']);
                    return $order_dir == 'asc' ? $val_a <=> $val_b : $val_b <=> $val_a;
                });
            } elseif ($sort_column == 'previous') {
                usort($filtered_history, function ($a, $b) use ($order_dir) {
                    $val_a = floatval($a['val2']);
                    $val_b = floatval($b['val2']);
                    return $order_dir == 'asc' ? $val_a <=> $val_b : $val_b <=> $val_a;
                });
            } elseif ($sort_column == 'change') {
                usort($filtered_history, function ($a, $b) use ($order_dir) {
                    $diff_a = $a['val2'] != 0 ? ($a['val1'] - $a['val2']) / $a['val2'] * 100 : 0;
                    $diff_b = $b['val2'] != 0 ? ($b['val1'] - $b['val2']) / $b['val2'] * 100 : 0;
                    return $order_dir == 'asc' ? $diff_a <=> $diff_b : $diff_b <=> $diff_a;
                });
            }
        }

        // Apply pagination (if length is -1, meaning "All", show all records without pagination)
        $paginated_history = $filtered_history;
        // If length is greater than 0 (not -1), apply pagination with start and length
        if ($length > 0) {
            $paginated_history = array_slice($filtered_history, $start, $length);
        }

        // Format data for DataTables
        $data = array();
        // Adjust row number start based on pagination
        $no = ($length > 0) ? $start + 1 : 1;

        foreach ($paginated_history as $row) {
            $diff = $row['val1'] - $row['val2'];
            $diffp = $row['val2'] != 0 ? round(($diff / $row['val2']) * 100, 2) : 0;

            $row_data = array();
            $row_data[] = $no;
            $row_data[] = $row['val3'];
            $row_data[] = amountExchange($row['val1'], 0, $employee_loc);
            $row_data[] = amountExchange($row['val2'], 0, $employee_loc);
            $row_data[] = $diffp . '%';

            $data[] = $row_data;
            $no++;
        }

        $output = array(
            "draw" => $draw,
            "recordsTotal" => $total_records,
            "recordsFiltered" => $filtered_count,
            "data" => $data,
        );

        echo json_encode($output);
    }


    public function add()
    {
        $head['usernm'] = $this->aauth->get_user()->username;
        $head['title'] = 'Add Employee';

        // Get departments - handle empty table case
        $data['dept'] = $this->employee->department_list($this->aauth->get_user()->loc);
        if (empty($data['dept'])) {
            $data['dept'] = array(); // Empty array if no departments
        }
        
        // Get roles - handle empty table case
        $data['roles'] = $this->employee->get_roles();
        if (empty($data['roles'])) {
            // Create default roles if none exist
            $data['roles'] = array(
                array('id' => 1, 'role_name' => 'Admin'),
                array('id' => 2, 'role_name' => 'Manager'),
                array('id' => 3, 'role_name' => 'Employee')
            );
        }

        $this->load->view('fixed/header', $head);
        $this->load->view('employee/add', $data);
        $this->load->view('fixed/footer');
    }

    public function submit_user()
    {
        // Allow creation even with empty tables - remove role restriction for first user
        $current_user_role = $this->aauth->get_user()->roleid ?? 0;

        // Only check role restriction if there are existing users
        if ($current_user_role > 0 && $current_user_role < 4) {
            redirect('/dashboard/', 'refresh');
        }

        $username = $this->input->post('username', true);
        $password = $this->input->post('password', true);
        $email = $this->input->post('email', true);
        $roleid = $this->input->post('roleid', true) ?: 3; // Default to role 3 if not specified

        // Validate required fields
        if (empty($username) || empty($password) || empty($email)) {
            ob_clean();
            echo json_encode(array(
                'status' => 'Error',
                'message' => $this->lang->line('ERROR') . ': Username, password, and email are required.',
                'redirect_url' => ''
            ));
            return;
        }

        // Validate username format (alphanumeric only)
        if (!preg_match('/^[a-z0-9]+$/i', $username)) {
            ob_clean();
            echo json_encode(array(
                'status' => 'Error',
                'message' => $this->lang->line('ERROR') . ': Username can only contain letters and numbers.',
                'redirect_url' => ''
            ));
            return;
        }

        // Validate password length
        if (strlen($password) < 6 || strlen($password) > 20) {
            ob_clean();
            echo json_encode(array(
                'status' => 'Error',
                'message' => $this->lang->line('ERROR') . ': Password must be between 6 and 20 characters.',
                'redirect_url' => ''
            ));
            return;
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            ob_clean();
            echo json_encode(array(
                'status' => 'Error',
                'message' => $this->lang->line('ERROR') . ': Please enter a valid email address.',
                'redirect_url' => ''
            ));
            return;
        }

        // Get form data
        $location = $this->input->post('location', true) ?: 0;
        $name = $this->input->post('name', true);
        $phone = $this->input->post('phone', true);
        $address = $this->input->post('address', true);
        $city = $this->input->post('city', true);
        $region = $this->input->post('region', true);
        $country = $this->input->post('country', true);
        $postbox = $this->input->post('postbox', true);
        $salary = numberClean($this->input->post('salary', true)) ?: 0;
        $commission = $this->input->post('commission', true) ?: 0;
        $department = $this->input->post('department', true) ?: 0;

        // Handle file uploads
        $picture_file = null;
        $sign_file = null;

        // Handle profile picture upload if provided
        if (!empty($_FILES['picture']['name']) && $_FILES['picture']['error'] == 0) {
            $config = array(
                'upload_path' => FCPATH . 'userfiles/employee/',
                'allowed_types' => 'gif|jpg|png|jpeg',
                'max_size' => 2048, // 2MB
                'encrypt_name' => true
            );
            $this->load->library('upload');
            $this->upload->initialize($config);

            if ($this->upload->do_upload('picture')) {
                $upload_data = $this->upload->data();
                $picture_file = $upload_data['file_name'];
            }
        }

        // Handle signature upload if provided
        if (!empty($_FILES['signature']['name']) && $_FILES['signature']['error'] == 0) {
            $config = array(
                'upload_path' => FCPATH . 'userfiles/employee_sign/',
                'allowed_types' => 'gif|jpg|png|jpeg',
                'max_size' => 2048, // 2MB
                'encrypt_name' => true
            );
            $this->upload->initialize($config);

            if ($this->upload->do_upload('signature')) {
                $upload_data = $this->upload->data();
                $sign_file = $upload_data['file_name'];
            }
        }

        try {
            // Check if username already exists BEFORE creating user
            $existing_user = $this->aauth->get_user_by_username($username);
            if ($existing_user) {
                ob_clean();
                echo json_encode(array(
                    'status' => 'Error',
                    'message' => $this->lang->line('ERROR') . ': Username already exists. Please choose a different username.',
                    'redirect_url' => ''
                ));
                return;
            }

            // Check if email already exists BEFORE creating user
            $existing_email = $this->aauth->get_user_by_email($email);
            if ($existing_email) {
                ob_clean();
                echo json_encode(array(
                    'status' => 'Error',
                    'message' => $this->lang->line('ERROR') . ': Email already exists. Please use a different email address.',
                    'redirect_url' => ''
                ));
                return;
            }

            // Create user account
            $user_id = $this->aauth->create_user($email, $password, $username);

            if ($user_id && $user_id > 0) {
                // Assign role to user
                $this->aauth->assign_user($user_id, $roleid);

                // Call model to add employee - model handles all employee creation and returns response
                $result = $this->employee->add_employee(
                    $user_id,
                    $username,
                    $name,
                    $roleid,
                    $phone,
                    $address,
                    $city,
                    $region,
                    $country,
                    $postbox,
                    $location,
                    $salary,
                    $commission,
                    $department,
                    $picture_file,
                    $sign_file
                );

                // If employee creation failed, clean up user
                if ($result['status'] === 'Error') {
                    $this->aauth->delete_user($user_id);

                    // Clean up uploaded files if employee creation failed
                    if ($picture_file && file_exists(FCPATH . 'userfiles/employee/' . $picture_file)) {
                        unlink(FCPATH . 'userfiles/employee/' . $picture_file);
                    }
                    if ($sign_file && file_exists(FCPATH . 'userfiles/employee_sign/' . $sign_file)) {
                        unlink(FCPATH . 'userfiles/employee_sign/' . $sign_file);
                    }
                }

                // Return model's response
                ob_clean();
                echo json_encode($result);
            } else {
                ob_clean();
                echo json_encode(array(
                    'status' => 'Error',
                    'message' => $this->lang->line('ERROR') . ': Failed to create user account. Please try again.',
                    'redirect_url' => ''
                ));
            }
        } catch (Exception $e) {
            ob_clean();
            echo json_encode(array(
                'status' => 'Error',
                'message' => $this->lang->line('ERROR') . ': ' . $e->getMessage(),
                'redirect_url' => ''
            ));
        }
    }

    // Check username availability
    public function check_username()
    {
        $username = $this->input->post('username', true);

        if (empty($username)) {
            echo json_encode(array('exists' => false));
            return;
        }

        $existing_user = $this->aauth->get_user_by_username($username);
        echo json_encode(array('exists' => $existing_user ? true : false));
    }

    // Check email availability
    public function check_email()
    {
        $email = $this->input->post('email', true);

        if (empty($email)) {
            echo json_encode(array('exists' => false));
            return;
        }

        $existing_email = $this->aauth->get_user_by_email($email);
        echo json_encode(array('exists' => $existing_email ? true : false));
    }

    public function invoices()
    {
        $id = $this->input->get('id');
        $head['usernm'] = $this->aauth->get_user()->username;
        $head['title'] = 'Employee Invoices';
        $data['employee'] = $this->employee->employee_details($id);
        $data['eid'] = intval($id);
        $this->load->view('fixed/header', $head);
        $this->load->view('employee/invoices', $data);
        $this->load->view('fixed/footer');
    }

    public function invoices_list()
    {

        $eid = $this->input->post('eid');
        $list = $this->employee->invoice_datatables($eid);
        $company = $this->settings->company_details(1);
        $invoiceFormat = ($company['invoice_format'] == 1) ? '' : $company['invoice_format'];
        $data = array();

        $no = $this->input->post('start');


        foreach ($list as $invoices) {
            $no++;
            $row = array();
            $row[] = $no;
            $row[] = $invoices->tid;
            $row[] = $invoices->name;
            $row[] = $invoices->invoicedate;
            $row[] = amountExchange($invoices->total, 0, $this->aauth->get_user()->loc);
            $row[] = '<span class="st-' . $invoices->status . '">' . $this->lang->line(ucwords($invoices->status)) . '</span>';
            $row[] = '<a href="' . base_url("invoices/view_invoice?id=$invoices->id") . '" class="btn btn-success btn-xs" target="_blank" title="View">
                    <i class="fa fa-eye" aria-hidden="true"></i></a>
                    &nbsp; ' . ($this->aauth->permission_new(null, 'salesPrintInvoices') ? ' <a href="' . base_url("invoices/printinvoice" . $invoiceFormat . "?id=$invoices->id") . '&d=1"
                     class="btn btn-info btn-xs" target="_blank" title="Download">
                    <i class="fa fa-file-pdf-o" aria-hidden="true"></i></a>' : '') . '&nbsp;';

            $data[] = $row;
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->employee->invoicecount_all($eid),
            "recordsFiltered" => $this->employee->invoicecount_filtered($eid),
            "data" => $data,
        );
        //output to json format
        echo json_encode($output);
    }

    public function transactions()
    {
        $id = $this->input->get('id');
        $head['usernm'] = $this->aauth->get_user()->username;
        $head['title'] = 'Employee Transactions';
        $data['employee'] = $this->employee->employee_details($id);
        $data['eid'] = intval($id);
        $this->load->view('fixed/header', $head);
        $this->load->view('employee/transactions', $data);
        $this->load->view('fixed/footer');
    }

    public function translist()
    {
        $eid = $this->input->post('eid');
        $list = $this->employee->get_datatables($eid);
        $data = array();
        $no = $this->input->post('start');
        foreach ($list as $prd) {
            $no++;
            $row = array();
            $pid = $prd->id;
            $row[] = $prd->date;
            // $row[] = $prd->account;
            $row[] = amountExchange($prd->credit, 0, $this->aauth->get_user()->loc);
            $row[] = amountExchange($prd->debit, 0, $this->aauth->get_user()->loc);
            if ($prd->trans_ref === null) {
                $row[] = $prd->inv_id;
            } else {
                $row[] = $prd->trans_ref;
            }
            $row[] = $prd->payer;
            $row[] = $prd->paymt_method;
            $row[] = '<a href="' . base_url() . 'transactions/view?id=' . $pid . '" class="btn btn-success btn-xs" title="View"><i class="fa fa-eye" aria-hidden="true"></i></a>';
            $data[] = $row;
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->employee->count_all($eid),
            "recordsFiltered" => $this->employee->count_filtered($eid),
            "data" => $data,
        );
        //output to json format
        echo json_encode($output);
    }


    function disable_user()
    {
        if (!$this->aauth->get_user()->roleid == 5) {
            redirect('/dashboard/', 'refresh');
        }
        $uid = intval($this->input->post('deleteid'));

        $nuid = intval($this->aauth->get_user()->id);

        if ($nuid == $uid) {
            echo json_encode(array('status' => 'Error', 'message' =>
            'You can not disable yourself!'));
        } else {

            $this->db->select('banned');
            $this->db->from('geopos_users');
            $this->db->where('id', $uid);
            $query = $this->db->get();
            $result = $query->row_array();
            if ($result['banned'] == 0) {
                $this->aauth->ban_user($uid);
            } else {
                $this->aauth->unban_user($uid);
            }

            echo json_encode(array('status' => 'Success', 'message' =>
            'User Profile updated successfully!'));
        }
    }

    function enable_user()
    {
        if (!$this->aauth->get_user()->roleid == 5) {
            redirect('/dashboard/', 'refresh');
        }
        $uid = intval($this->input->post('deleteid'));

        $nuid = intval($this->aauth->get_user()->id);

        if ($nuid == $uid) {
            echo json_encode(array('status' => 'Error', 'message' =>
            'You can not disable yourself!'));
        } else {


            $a = $this->aauth->unban_user($uid);

            echo json_encode(array('status' => 'Success', 'message' =>
            'User Profile disabled successfully!'));
        }
    }

    function delete_user()
    {
        if (!$this->aauth->permission_new(null, 'usersEmployeesDelete')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        if (!$this->aauth->get_user()->roleid == 5) {
            redirect('/dashboard/', 'refresh');
        }
        $uid = intval($this->input->post('empid'));

        $nuid = intval($this->aauth->get_user()->id);

        if ($nuid == $uid) {
            echo json_encode(array('status' => 'Error', 'message' =>
            'You can not delete yourself!'));
        } else {

            $this->db->delete('geopos_employees', array('id' => $uid));

            $this->db->delete('geopos_users', array('id' => $uid));

            echo json_encode(array('status' => 'Success', 'message' =>
            'User Profile deleted successfully! Please refresh the page!'));
        }
    }


    public function calc_income()
    {
        $eid = $this->input->post('eid');

        if ($this->employee->money_details($eid)) {
            $details = $this->employee->money_details($eid);

            echo json_encode(array('status' => 'Success', 'message' =>
            '<br> Total Income: ' . amountExchange($details['credit'], 0, $this->aauth->get_user()->loc) . '<br> Total Expenses: ' . amountExchange($details['debit'], 0, $this->aauth->get_user()->loc)));
        }
    }

    public function calc_sales()
    {
        $eid = $this->input->post('eid');

        if ($this->employee->sales_details($eid)) {
            $details = $this->employee->sales_details($eid);

            echo json_encode(array('status' => 'Success', 'message' =>
            'Total Sales (Paid Payment):  ' . amountExchange($details['total'], 0, $this->aauth->get_user()->loc)));
        }
    }

    public function update()
    {
        // Check if user is logged in
        if (!$this->aauth->is_loggedin()) {
            if ($this->input->is_ajax_request()) {
                ob_clean();
                echo json_encode(['status' => 'error', 'message' => 'Please log in to continue.']);
                return;
            } else {
                redirect('/user/', 'refresh');
            }
        }

        // Check permissions
        if (!$this->aauth->permission_new(null, 'usersEmployeesEdit')) {
            if ($this->input->is_ajax_request()) {
                ob_clean();
                echo json_encode(['status' => 'error', 'message' => 'Sorry! You have insufficient permissions to access this section.']);
                return;
            } else {
                exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
            }
        }

        $id = $this->input->get('id');
        $this->load->model('employee_model', 'employee');

        if ($this->input->post()) {
            // Set JSON response header
            $this->output->set_content_type('application/json');
            ob_clean(); // Clear any output before JSON response

            $eid = $this->input->post('eid', true);
            $name = $this->input->post('name', true);
            $phone = $this->input->post('phone', true);
            $phonealt = $this->input->post('phonealt', true);
            $address = $this->input->post('address', true);
            $city = $this->input->post('city', true);
            $region = $this->input->post('region', true);
            $country = $this->input->post('country', true);
            $postbox = $this->input->post('postbox', true);
            $location = $this->input->post('location', true);
            $salary = numberClean($this->input->post('salary', true));
            $department = $this->input->post('department', true);
            $commission = $this->input->post('commission', true);
            $roleid = $this->input->post('roleid', true);

            // Validate required fields
            if (empty($eid)) {
                $this->output->set_output(json_encode(array(
                    'status' => 'Error',
                    'message' => $this->lang->line('ERROR') . ': Employee ID is required.'
                )));
                return;
            }

            if (empty($name)) {
                $this->output->set_output(json_encode(array(
                    'status' => 'Error',
                    'message' => $this->lang->line('ERROR') . ': Name is required.'
                )));
                return;
            }

            // Call the model to update the employee
            // The model will output JSON response directly
            $this->employee->update_employee($eid, $name, $phone, $phonealt, $address, $city, $region, $country, $postbox, $location, $salary, $department, $commission, $roleid);
        } else {
            $head['usernm'] = $this->aauth->get_user($id)->username;
            $head['title'] = $head['usernm'] . ' Profile';
            $data['user'] = $this->employee->employee_details($id);
            $data['dept'] = $this->employee->department_list($this->aauth->get_user()->loc);
            // dd($data['dept']);
            $data['eid'] = intval($id);
            $data['roles'] = $this->employee->get_roles();
            $this->load->view('fixed/header', $head);
            $this->load->view('employee/edit', $data);
            $this->load->view('fixed/footer');
        }
    }


    public function displaypic()
    {

        if (!$this->aauth->is_loggedin()) {
            redirect('/user/', 'refresh');
        }

        $this->load->model('employee_model', 'employee');
        $id = $this->input->get('id');
        $this->load->library("uploadhandler", array(
            'accept_file_types' => '/\.(gif|jpe?g|png)$/i',
            'upload_dir' => FCPATH . 'userfiles/employee/'
        ));
        $img = (string)$this->uploadhandler->filenaam();
        if ($img != '') {
            $this->employee->editpicture($id, $img);
        }
    }


    public function user_sign()
    {
        if (!$this->aauth->is_loggedin()) {
            redirect('/user/', 'refresh');
        }
        $this->load->model('employee_model', 'employee');
        $id = $this->input->get('id');
        $this->load->library("uploadhandler", array(
            'accept_file_types' => '/\.(gif|jpe?g|png)$/i',
            'upload_dir' => FCPATH . 'userfiles/employee_sign/'
        ));
        $img = (string)$this->uploadhandler->filenaam();
        if ($img != '') {
            $this->employee->editsign($id, $img);
        }
    }


    public function updatepassword()
    {

        if (!$this->aauth->is_loggedin()) {
            redirect('/user/', 'refresh');
        }
        $this->load->library("form_validation");

        $id = $this->input->get('id');
        $this->load->model('employee_model', 'employee');


        if ($this->input->post()) {
            $eid = $this->input->post('eid');
            $this->form_validation->set_rules('newpassword', 'Password', 'required');
            $this->form_validation->set_rules('renewpassword', 'Confirm Password', 'required|matches[newpassword]');

            ob_clean(); // Clear any output before JSON response

            if ($this->form_validation->run() == FALSE) {
                echo json_encode(array(
                    'status' => 'Error',
                    'message' => $this->lang->line('ERROR') . ': Password length should be at least 6 [a-z-0-9] allowed! New Password & Re New Password should be same!'
                ));
            } else {
                $newpassword = $this->input->post('newpassword');

                // Update password
                $update_result = $this->aauth->update_user($eid, false, $newpassword, false);

                if ($update_result) {
                    echo json_encode(array(
                        'status' => 'Success',
                        'message' => $this->lang->line('UPDATED') . ': Password Updated Successfully!'
                    ));
                } else {
                    echo json_encode(array(
                        'status' => 'Error',
                        'message' => $this->lang->line('ERROR') . ': Failed to update password. Please try again.'
                    ));
                }
            }
        } else {
            $head['usernm'] = $this->aauth->get_user()->username;
            $head['title'] = $head['usernm'] . ' Profile';
            $data['user'] = $this->employee->employee_details($id);
            $data['eid'] = intval($id);
            $this->load->view('fixed/header', $head);
            $this->load->view('employee/password', $data);
            $this->load->view('fixed/footer');
        }
    }

    public function permissions()
    {
        if (!$this->aauth->permission_new(null, 'usersEmployeesPermissions')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $head['usernm'] = $this->aauth->get_user()->username;
        $head['title'] = 'Employee Permissions';

        $data['permission'] = $this->employee->employee_permissions();
        $data['employee'] = $this->employee->list_employee();
        $data['roles'] = $this->employee->get_roles();

        $data['success_message'] = $this->session->flashdata('success');

        $this->load->view('fixed/header', $head);
        $this->load->view('employee/permissions', $data);
        $this->load->view('fixed/footer');
    }

    public function get_roles()
    {
        if (!$this->aauth->permission_new(null, 'usersEmployeesPermissions')) {
            ob_clean();
            echo json_encode(['draw' => 0, 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => []]);
            return;
        }
        // DataTables params
        $draw = intval($this->input->post('draw'));
        $start = intval($this->input->post('start'));
        $length = intval($this->input->post('length'));
        $search_value = $this->input->post('search')['value'] ?? '';

        // Total count
        $this->db->from('roles');
        $recordsTotal = $this->db->count_all_results();

        // Filtered count
        $this->db->from('roles');
        if (!empty($search_value)) {
            $this->db->group_start();
            $this->db->like('role_name', $search_value);
            $this->db->or_like('role_description', $search_value);
            $this->db->group_end();
        }
        $recordsFiltered = $this->db->count_all_results();

        // Fetch data (respect length=-1 for All)
        $this->db->select('id, role_name, role_description');
        $this->db->from('roles');
        if (!empty($search_value)) {
            $this->db->group_start();
            $this->db->like('role_name', $search_value);
            $this->db->or_like('role_description', $search_value);
            $this->db->group_end();
        }
        if ($length > 0) {
            $this->db->limit($length, $start);
        }
        $query = $this->db->get();

        // Build rows like holidays list (all HTML in controller)
        $data = array();
        $no = $start + 1;

        $canEdit = $this->aauth->permission_new(null, 'usersEmployeesPermissionsEdit');
        $canDelete = $this->aauth->permission_new(null, 'usersEmployeesPermissionsDelete');

        foreach ($query->result_array() as $row) {
            $actions = '';
            if ($canEdit) {
                $actions .= "<a href='" . base_url("employee/permission_edit?id=" . $row['id']) . "' class='btn btn-info btn-xs' title='Edit'><i class='fa fa-edit'></i></a> ";
            }
            if ($canDelete) {
                $actions .= "<a href='#' data-object-id='" . $row['id'] . "' class='btn btn-danger btn-xs delete-object' title='Delete'><i class='fa fa-trash'></i></a>";
            }

            $data[] = array(
                $no,
                $row['role_name'],
                $row['role_description'],
                $actions
            );
            $no++;
        }

        $response = array(
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        );

        ob_clean();
        echo json_encode($response);
    }

    public function add_role_permissions()
    {
        if (!$this->aauth->permission_new(null, 'usersEmployeesPermissionsAdd')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $head['usernm'] = $this->aauth->get_user()->username;
        $head['title'] = 'Employee Permissions';

        $data['permission'] = $this->employee->employee_permissions(); // Existing permissions logic
        $data['employee'] = $this->employee->list_employee();          // Employee list
        $data['roles'] = $this->employee->get_roles();                 // Add this line to fetch roles

        $this->load->view('fixed/header', $head);
        $this->load->view('employee/add_role_permissions', $data); // Load view with roles
        $this->load->view('fixed/footer');
    }

    public function add_role()
    {
        // Check if it's an AJAX request
        if ($this->input->is_ajax_request()) {
            if (!$this->aauth->permission_new(null, 'usersEmployeesPermissionsAdd')) {
                $this->output->set_content_type('application/json')
                    ->set_output(json_encode(['status' => 'error', 'message' => 'Insufficient permissions.']));
                return;
            }

            // Get the POST data
            $postData = $this->input->post();

            // Validate the required fields
            $this->form_validation->set_rules('roleName', 'Role Name', 'required|trim|is_unique[roles.role_name]');

            if ($this->form_validation->run() === FALSE) {
                // Return validation errors
                $response = array(
                    'status' => 'error',
                    'message' => validation_errors()
                );
            } else {
                // Start transaction
                $this->db->trans_begin();

                try {
                    // Prepare role data
                    $roleData = array(
                        'role_name' => $postData['roleName'],
                        'role_description' => $postData['roleDescription'] ?? ''
                    );

                    // Insert into roles table
                    $this->db->insert('roles', $roleData);
                    $role_id = $this->db->insert_id();

                    // Prepare permissions data
                    $permissionsData = array();
                    foreach ($postData as $key => $value) {
                        if (in_array($key, ['roleName', 'roleDescription', $this->security->get_csrf_token_name()])) {
                            continue;
                        }

                        $permissionsData[] = array(
                            'role_id' => $role_id,
                            'permission_key' => $key,
                            'permission_value' => (int)$value
                        );
                    }

                    // Batch insert permissions
                    if (!empty($permissionsData)) {
                        $this->db->insert_batch('role_permissions', $permissionsData);
                    }

                    // Commit or rollback
                    if ($this->db->trans_status() === FALSE) {
                        $this->db->trans_rollback();
                        throw new Exception('Database transaction failed');
                    } else {
                        $this->db->trans_commit();

                        // Set flash message
                        $this->session->set_flashdata('success', 'Role and permissions added successfully!');

                        // Return success response
                        $response = array(
                            'status' => 'success',
                            'message' => 'Role and permissions added successfully!',
                            'redirect' => base_url('employee/permissions'),
                            'role_id' => $role_id
                        );
                    }
                } catch (Exception $e) {
                    $this->db->trans_rollback();
                    $response = array(
                        'status' => 'error',
                        'message' => 'Error: ' . $e->getMessage()
                    );
                }
            }

            // Return JSON response
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
        } else {
            // Not an AJAX request - redirect
            redirect('employee/permissions');
        }
    }

    public function permission_edit($role_id = null)
    {
        if (!$this->aauth->permission_new(null, 'usersEmployeesPermissionsEdit')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        // Get role_id from query string if not provided in URL segment
        $role_id = $role_id ?? $this->input->get('id', TRUE);

        $head['usernm'] = $this->aauth->get_user()->username;
        $head['title']  = 'Edit Role Permissions';

        // Load role data
        $this->db->select('*');
        $this->db->from('roles');
        $this->db->where('id', $role_id);
        $data['role'] = $this->db->get()->row();

        // Load permissions for this role
        $this->db->select('permission_key, permission_value');
        $this->db->from('role_permissions');
        $this->db->where('role_id', $role_id);
        $permissions = $this->db->get()->result_array();

        $data['permissions'] = array_column($permissions, 'permission_value', 'permission_key');

        $this->load->view('fixed/header', $head);
        $this->load->view('employee/permission_edit', $data);
        $this->load->view('fixed/footer');
    }

    public function save_permissions($role_id)
    {
        if (!$this->aauth->permission_new(null, 'usersEmployeesPermissionsEdit')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $permissions = $this->input->post('permissions') ?? [];

        // Define all possible permissions in your system
        $all_keys = ['dashboard', 'users', 'reports', 'settings'];
        // ⬆️ extend this with all your permission keys

        foreach ($all_keys as $key) {
            $value = isset($permissions[$key]) ? $permissions[$key] : 0;

            // insert/update into DB
            $this->db->replace('role_permissions', [
                'role_id'         => $role_id,
                'permission_key'  => $key,
                'permission_value' => $value
            ]);
        }

        $this->session->set_flashdata('success', 'Permissions updated successfully.');
        redirect('employee/permission_edit/' . $role_id);
    }



    public function update_role_permissions()
    {
        if (!$this->aauth->permission_new(null, 'usersEmployeesPermissionsEdit')) {
            if ($this->input->is_ajax_request()) {
                $this->output->set_content_type('application/json')
                    ->set_output(json_encode(['status' => 'error', 'message' => 'Insufficient permissions.']));
                return;
            }
            redirect('employee/permissions');
        }
        if ($this->input->is_ajax_request()) {
            $postData = $this->input->post();

            $role_id = $postData['role_id'];

            // Start transaction
            $this->db->trans_begin();

            try {
                // Update role info
                $roleData = array(
                    'role_name' => $postData['roleName'],
                    'role_description' => $postData['roleDescription']
                );
                $this->db->where('id', $role_id)->update('roles', $roleData);

                // Fetch existing permissions from DB
                $existingPermissions = $this->db
                    ->where('role_id', $role_id)
                    ->get('role_permissions')
                    ->result_array();

                $existing = array_column($existingPermissions, 'permission_value', 'permission_key');

                // Get all keys submitted in POST that are permission keys
                $submittedKeys = array_diff_key($postData, array_flip(['role_id', 'roleName', 'roleDescription']));

                // Loop through submitted permissions
                foreach ($submittedKeys as $key => $value) {
                    $value = (int)$value;

                    if (isset($existing[$key])) {
                        // Update if value has changed
                        if ($existing[$key] != $value) {
                            $this->db->where([
                                'role_id' => $role_id,
                                'permission_key' => $key
                            ])->update('role_permissions', [
                                'permission_value' => $value
                            ]);
                        }
                        unset($existing[$key]); // Mark this as processed
                    } else {
                        // Insert if not exists
                        $this->db->insert('role_permissions', [
                            'role_id' => $role_id,
                            'permission_key' => $key,
                            'permission_value' => $value
                        ]);
                    }
                }

                // Delete remaining permissions that were not submitted (unchecked)
                foreach ($existing as $key => $oldValue) {
                    $this->db->where([
                        'role_id' => $role_id,
                        'permission_key' => $key
                    ])->delete('role_permissions');
                }

                // Check transaction status
                if ($this->db->trans_status() === FALSE) {
                    $this->db->trans_rollback();
                    throw new Exception('Database transaction failed');
                } else {
                    $this->db->trans_commit();

                    $this->session->set_flashdata('success', 'Role and permissions updated successfully!');
                    $response = array(
                        'status' => 'success',
                        'message' => 'Role and permissions updated successfully!'
                    );
                }
            } catch (Exception $e) {
                $this->db->trans_rollback();
                $this->session->set_flashdata('error', 'Error: ' . $e->getMessage());
                $response = array(
                    'status' => 'error',
                    'message' => 'Error: ' . $e->getMessage()
                );
            }

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
        } else {
            redirect('employee/permissions');
        }
    }


    public function delete_role()
    {
        if (!$this->aauth->permission_new(null, 'usersEmployeesPermissionsDelete')) {
            ob_clean();
            echo json_encode(['status' => 'error', 'message' => 'Insufficient permissions.']);
            return;
        }
        $role_id = intval($this->input->post('role_id'));

        // Validate input
        if (!$role_id || !is_numeric($role_id)) {
            ob_clean();
            echo json_encode(['status' => 'error', 'message' => 'Invalid role ID.']);
            return;
        }

        // Delete role permissions
        $this->db->where('role_id', $role_id);
        $this->db->delete('role_permissions');

        // Delete the role itself
        $this->db->where('id', $role_id);
        $this->db->delete('roles');

        ob_clean();
        echo json_encode(['status' => 'success', 'message' => 'Role and its permissions deleted successfully.']);
    }



    public function permissions_update()
    {

        $head['usernm'] = $this->aauth->get_user()->username;
        $head['title'] = 'Employee Permissions';
        $permission = $this->employee->employee_permissions();

        foreach ($permission as $row) {
            $i = $row['id'];
            $name1 = 'r_' . $i . '_1';
            $name2 = 'r_' . $i . '_2';
            $name3 = 'r_' . $i . '_3';
            $name4 = 'r_' . $i . '_4';
            $name5 = 'r_' . $i . '_5';
            $name6 = 'r_' . $i . '_6';
            $name7 = 'r_' . $i . '_7';
            $name8 = 'r_' . $i . '_8';
            $val1 = 0;
            $val2 = 0;
            $val3 = 0;
            $val4 = 0;
            $val5 = 0;
            $val6 = 0;
            $val7 = 0;
            $val8 = 0;
            if ($this->input->post($name1)) $val1 = 1;
            if ($this->input->post($name2)) $val2 = 1;
            if ($this->input->post($name3)) $val3 = 1;
            if ($this->input->post($name4)) $val4 = 1;
            if ($this->input->post($name5)) $val5 = 1;
            if ($this->input->post($name6)) $val6 = 1;
            if ($this->input->post($name7)) $val7 = 1;
            if ($this->input->post($name8)) $val8 = 1;
            if ($this->aauth->get_user()->roleid == 5 && $i == 9) $val5 = 1;
            $data = array('r_1' => $val1, 'r_2' => $val2, 'r_3' => $val3, 'r_4' => $val4, 'r_5' => $val5, 'r_6' => $val6, 'r_7' => $val7, 'r_8' => $val8);
            $this->db->set($data);
            $this->db->where('id', $i);
            $this->db->update('geopos_premissions');
        }

        echo json_encode(array('status' => 'Success', 'message' =>
        $this->lang->line('UPDATED')));
    }


    public function holidays()
    {

        $head['usernm'] = $this->aauth->get_user()->username;
        $head['title'] = 'Holidays';
        $this->load->view('fixed/header', $head);
        $this->load->view('employee/holidays');
        $this->load->view('fixed/footer');
    }


    public function hday_list()
    {
        $list = $this->employee->holidays_datatables();
        $data = array();
        $no = $this->input->post('start');
        foreach ($list as $obj) {
            $datetime1 = date_create($obj->val1);
            $datetime2 = date_create($obj->val2);
            $interval = date_diff($datetime1, $datetime2);
            $day = $interval->format('%a days');
            $no++;
            $row = array();
            $row[] = $no;
            $row[] = $obj->val1;
            $row[] = $obj->val2;
            $row[] = $day;
            $row[] = $obj->val3;
            $row[] = "<a href='" . base_url("employee/editholiday?id=$obj->id") . "' class='btn btn-blue btn-xs' title='" . $this->lang->line('Edit') . "'><i class='fa fa-edit' aria-hidden='true'></i></a> "
             . '<a href="#" data-object-id="' . $obj->id . '" class="btn btn-danger btn-xs delete-object" title="' . $this->lang->line('Delete') . '"><i class="fa fa-trash" aria-hidden="true"></i></a>';


            $data[] = $row;
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->employee->holidays_count_all(),
            "recordsFiltered" => $this->employee->holidays_count_filtered(),
            "data" => $data,
        );
        //output to json format
        echo json_encode($output);
    }

    public function delete_hday()
    {
        $id = $this->input->post('deleteid');


        if ($this->employee->deleteholidays($id)) {
            echo json_encode(array('status' => 'Success', 'message' => $this->lang->line('DELETED')));
        } else {
            echo json_encode(array('status' => 'Error', 'message' => $this->lang->line('ERROR')));
        }
    }

    public function addhday()
    {

        if ($this->input->post()) {

            $from = datefordatabase($this->input->post('from'));
            $todate = datefordatabase($this->input->post('todate'));
            $note = $this->input->post('note', true);

            $date1 = new DateTime($from);
            $date2 = new DateTime($todate);
            if ($date1 <= $date2) {


                if ($this->employee->addholidays($this->aauth->get_user()->loc, $from, $todate, $note)) {
                    echo json_encode(array('status' => 'Success', 'message' => $this->lang->line('ADDED') . "   <a href='addhday' class='btn btn-indigo btn-lg'><span class='icon-plus-circle' aria-hidden='true'></span>  </a> <a href='holidays' class='btn btn-grey btn-lg'><span class='icon-eye' aria-hidden='true'></span>  </a>"));
                }
            } else {
                echo json_encode(array('status' => 'Error', 'message' => $this->lang->line('ERROR') . '- Invalid'));
            }
        } else {
            $data['id'] = $this->input->get('id');
            $head['usernm'] = $this->aauth->get_user()->username;
            $head['title'] = 'Add Holiday';
            $this->load->view('fixed/header', $head);
            $this->load->view('employee/addholyday', $data);
            $this->load->view('fixed/footer');
        }
    }


    public function editholiday()
    {

        if ($this->input->post()) {


            $id = $this->input->post('did');
            $from = datefordatabase($this->input->post('from'));
            $todate = datefordatabase($this->input->post('todate'));
            $note = $this->input->post('note', true);

            if ($this->employee->edithday($id, $this->aauth->get_user()->loc, $from, $todate, $note)) {
                echo json_encode(array('status' => 'Success', 'message' => $this->lang->line('ADDED') . "  <a href='addhday' class='btn btn-indigo btn-lg'><span class='icon-plus-circle' aria-hidden='true'></span>  </a> <a href='holidays' class='btn btn-grey btn-lg'><span class='icon-eye' aria-hidden='true'></span>  </a>"));
            } else {
                echo json_encode(array('status' => 'Error', 'message' => $this->lang->line('ERROR')));
            }
        } else {
            $data['id'] = $this->input->get('id');
            $data['hday'] = $this->employee->hday_view($data['id'], $this->aauth->get_user()->loc);
            $head['usernm'] = $this->aauth->get_user()->username;
            $head['title'] = 'Edit Holiday';
            $this->load->view('fixed/header', $head);
            $this->load->view('employee/edithday', $data);
            $this->load->view('fixed/footer');
        }
    }


    public function departments()
    {

        $head['usernm'] = $this->aauth->get_user()->username;
        $head['title'] = 'Departments';
        $this->load->view('fixed/header', $head);
        $this->load->view('employee/departments');
        $this->load->view('fixed/footer');
    }

    public function dep_list()
    {
        // Server-side list for Departments DataTable
        $list = $this->employee->department_list($this->aauth->get_user()->loc);
        $data = array();
        $no = $this->input->post('start');

        foreach ($list as $row) {
            $no++;
            $aid = $row['id'];
            $name = $row['val1'];

            $actions = "<a href='" . base_url("employee/department?id=$aid") . "' class='btn btn-success btn-sm'><i class='fa fa-eye'></i> " . $this->lang->line('View') . "</a> ";
            $actions .= "<a href='" . base_url("employee/editdep?id=$aid") . "' class='btn btn-blue btn-sm'><i class='fa fa-pencil'></i> " . $this->lang->line('Edit') . "</a> ";
            $actions .= "<a href='#' data-object-id='" . $aid . "' class='btn btn-danger btn-sm delete-object'><i class='fa fa-trash'></i></a>";

            $data[] = array($no, $name, $actions);
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => count($list),
            "recordsFiltered" => count($list),
            "data" => $data,
        );
        echo json_encode($output);
    }

    public function department()
    {

        $data['id'] = $this->input->get('id');
        $head['usernm'] = $this->aauth->get_user()->username;
        $data['department'] = $this->employee->department_view($data['id'], $this->aauth->get_user()->loc);
        $head['title'] = 'Departments';
        $this->load->view('fixed/header', $head);
        $this->load->view('employee/department', $data);
        $this->load->view('fixed/footer');
    }

    public function dep_emp_list()
    {
        $depId = intval($this->input->post('id')) ?: intval($this->input->get('id'));
        $draw = intval($this->input->post('draw'));
        $start = intval($this->input->post('start'));
        $length = intval($this->input->post('length'));

        $all = $this->employee->department_elist($depId);
        $total = count($all);
        $slice = ($length > 0) ? array_slice($all, $start, $length) : $all;

        $data = array();
        $no = $start + 1;
        foreach ($slice as $row) {
            $aid = $row['id'];
            $name = $row['name'];
            $salary = $row['salary'];

            $actions = "<a href='" . base_url("employee/attendances?id=$aid") . "' class='btn btn-success btn-xs'><i class='fa fa-chain'></i> " . $this->lang->line('Attendance') . "</a>  ";
            $actions .= "<a href='" . base_url("employee/payroll_emp?id=$aid") . "' class='btn btn-blue btn-xs'><i class='fa fa-money'></i> " . $this->lang->line('Payroll') . "</a> ";
            $actions .= "<a href='" . base_url("employee/view?id=$aid") . "' class='btn btn-info btn-xs'><i class='icon-eye'></i> " . $this->lang->line('View') . "</a> ";
            $actions .= "<a href='" . base_url("employee/history?id=$aid") . "' class='btn btn-purple btn-xs'><i class='fa fa-clock-o'></i> " . $this->lang->line('History') . "</a>";

            $data[] = array($no, $name, $salary, $actions);
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

    public function delete_dep()
    {

        $id = $this->input->post('deleteid');


        if ($this->employee->deletedepartment($id)) {
            echo json_encode(array('status' => 'Success', 'message' => $this->lang->line('DELETED')));
        } else {
            echo json_encode(array('status' => 'Error', 'message' => $this->lang->line('ERROR')));
        }
    }

    public function adddep()
    {

        if ($this->input->post()) {

            $name = $this->input->post('name', true);


            if ($this->employee->adddepartment($this->aauth->get_user()->loc, $name)) {
                echo json_encode(array('status' => 'Success', 'message' => $this->lang->line('ADDED') . "  <a href='adddep' class='btn btn-indigo btn-lg'><span class='icon-plus-circle' aria-hidden='true'></span>  </a> <a href='departments' class='btn btn-grey btn-lg'><span class='icon-eye' aria-hidden='true'></span>  </a>"));
            } else {
                echo json_encode(array('status' => 'Error', 'message' => $this->lang->line('ERROR')));
            }
        } else {

            $head['usernm'] = $this->aauth->get_user()->username;
            $head['title'] = 'Add Department';
            $this->load->view('fixed/header', $head);
            $this->load->view('employee/adddep');
            $this->load->view('fixed/footer');
        }
    }

    public function editdep()
    {

        if ($this->input->post()) {

            $name = $this->input->post('name', true);
            $id = $this->input->post('did');

            if ($this->employee->editdepartment($id, $this->aauth->get_user()->loc, $name)) {
                echo json_encode(array('status' => 'Success', 'message' => $this->lang->line('ADDED') . "  <a href='adddep' class='btn btn-indigo btn-lg'><span class='icon-plus-circle' aria-hidden='true'></span>  </a> <a href='departments' class='btn btn-grey btn-lg'><span class='icon-eye' aria-hidden='true'></span>  </a>"));
            } else {
                echo json_encode(array('status' => 'Error', 'message' => $this->lang->line('ERROR')));
            }
        } else {
            $data['id'] = $this->input->get('id');
            $data['department'] = $this->employee->department_view($data['id'], $this->aauth->get_user()->loc);
            $head['usernm'] = $this->aauth->get_user()->username;
            $head['title'] = 'Edit Department';
            $this->load->view('fixed/header', $head);
            $this->load->view('employee/editdep', $data);
            $this->load->view('fixed/footer');
        }
    }

    public function payroll_create()
    {
        $this->load->library("Custom");
        $data['dual'] = $this->custom->api_config(65);
        $this->load->model('transactions_model', 'transactions');
        $data['cat'] = $this->transactions->categories();
        $data['accounts'] = $this->transactions->acc_list();
        $head['title'] = "Add Transaction";
        $head['usernm'] = $this->aauth->get_user()->username;
        $this->load->view('fixed/header', $head);
        $this->load->view('employee/payroll_create', $data);
        $this->load->view('fixed/footer');
    }

    public function emp_search()
    {

        $name = $this->input->get('keyword', true);


        $whr = '';
        if ($this->aauth->get_user()->loc) {
            $whr = ' (geopos_users.loc=' . $this->aauth->get_user()->loc . ') AND ';
        }
        if ($name) {
            $query = $this->db->query("SELECT geopos_employees.* ,geopos_users.email FROM geopos_employees  LEFT JOIN geopos_users ON geopos_users.id=geopos_employees.id  WHERE $whr (UPPER(geopos_employees.name)  LIKE '%" . strtoupper($name) . "%' OR UPPER(geopos_employees.phone)  LIKE '" . strtoupper($name) . "%') LIMIT 6");
            $result = $query->result_array();
            echo '<ol>';
            $i = 1;
            foreach ($result as $row) {

                echo "<li onClick=\"selectPay('" . $row['id'] . "','" . $row['name'] . " ','" . amountFormat_general($row['salary']) . "')\"><span>$i</span><p>" . $row['name'] . " &nbsp; &nbsp  " . $row['phone'] . "</p></li>";
                $i++;
            }
            echo '</ol>';
        }
    }

    public function payroll()
    {

        $head['usernm'] = $this->aauth->get_user()->username;
        $head['title'] = 'Employee Payroll Transactions';


        $this->load->view('fixed/header', $head);
        $this->load->view('employee/payroll');
        $this->load->view('fixed/footer');
    }

    public function payroll_emp()
    {

        $id = $this->input->get('id');
        $head['usernm'] = $this->aauth->get_user()->username;
        $head['title'] = 'Employee Payroll Transactions';
        $data['employee'] = $this->employee->employee_details($id);
        $data['eid'] = intval($id);
        $this->load->view('fixed/header', $head);
        $this->load->view('employee/payroll_employee', $data);
        $this->load->view('fixed/footer');
    }


    public function payrolllist()
    {

        $eid = $this->input->post('eid');
        $list = $this->employee->pay_get_datatables($eid);
        // dd($list);
        $data = array();
        $no = $this->input->post('start');
        foreach ($list as $prd) {
            $no++;
            $row = array();
            $pid = $prd->id;
            $row[] = $prd->date;

            $row[] = amountExchange($prd->debit, 0, $this->aauth->get_user()->loc);
            $row[] = amountExchange($prd->credit, 0, $this->aauth->get_user()->loc);
            $row[] = $prd->account;
            $row[] = $prd->payer;
            $row[] = $prd->method;
            $row[] = '<a href="' . base_url() . 'transactions/view?id=' . $pid . '" class="btn btn-success btn-xs"><i class="fa fa-eye"></i></a> 
            <a  href="#" data-object-id="' . $pid . '" class="btn btn-danger btn-xs delete-object"><i class="fa fa-trash"></i></a> ';
            $data[] = $row;
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->employee->pay_count_all($eid),
            "recordsFiltered" => $this->employee->pay_count_filtered($eid),
            "data" => $data,
        );
        //output to json format
        echo json_encode($output);
    }

    public function attendances()
    {

        $head['usernm'] = $this->aauth->get_user()->username;
        $head['title'] = 'Attendance';
        $this->load->view('fixed/header', $head);
        $this->load->view('employee/attendance_list');
        $this->load->view('fixed/footer');
    }

    public function attendance()
    {
        if ($this->input->post()) {
            $emp = $this->input->post('employee');
            $adate = datefordatabase($this->input->post('adate'));
            $from = timefordatabase($this->input->post('from'));
            $todate = timefordatabase($this->input->post('to'));
            $note = $this->input->post('note');

            // Basic validation
            if (empty($emp) || !is_array($emp)) {
                echo json_encode(array('status' => 'Error', 'message' => $this->lang->line('PleaseSelectEmployee')));
                return;
            }
            if (empty($adate)) {
                echo json_encode(array('status' => 'Error', 'message' => $this->lang->line('PleaseSelectDate')));
                return;
            }
            if (empty($from)) {
                echo json_encode(array('status' => 'Error', 'message' => $this->lang->line('PleaseSelectTimeFrom')));
                return;
            }
            if (empty($todate)) {
                echo json_encode(array('status' => 'Error', 'message' => $this->lang->line('PleaseSelectTimeTo')));
                return;
            }

            $result = $this->employee->addattendance($emp, $adate, $from, $todate, $note);

            if (is_array($result) && isset($result['inserted'])) {
                if ($result['inserted'] > 0) {
                    echo json_encode(array('status' => 'Success', 'message' => $this->lang->line('ADDED') . "  <a href='attendance' class='btn btn-blue btn-lg'><span class='fa fa-plus-circle' aria-hidden='true'></span>  </a> <a href='attendances' class='btn btn-grey btn-lg'><span class='fa fa-eye' aria-hidden='true'></span>  </a>"));
                } else {
                    echo json_encode(array('status' => 'Error', 'message' => $this->lang->line('AttendanceAlreadyExists')));
                }
            } else {
                echo json_encode(array('status' => 'Error', 'message' => $this->lang->line('ERROR')));
            }
        } else {
            $data['emp'] = $this->employee->list_employee();
            $head['usernm'] = $this->aauth->get_user()->username;
            $head['title'] = 'New Attendance';
            $this->load->view('fixed/header', $head);
            $this->load->view('employee/attendance', $data);
            $this->load->view('fixed/footer');
        }
    }

    public function auto_attendance()
    {
        if ($this->input->post()) {
            $auto_attand = $this->input->post('attend');

            if ($this->employee->autoattend($auto_attand)) {
                echo json_encode(array('status' => 'Success', 'message' => $this->lang->line('UPDATED')));
            } else {
                echo json_encode(array('status' => 'Error', 'message' => $this->lang->line('ERROR')));
            }
        } else {
            $this->load->model('plugins_model', 'plugins');

            $data['auto'] = $this->plugins->universal_api(62);


            $head['usernm'] = $this->aauth->get_user()->username;
            $head['title'] = 'Auto Attendance';
            $this->load->view('fixed/header', $head);
            $this->load->view('employee/autoattend', $data);
            $this->load->view('fixed/footer');
        }
    }


    public function att_list()
    {
        // Get DataTables parameters
        $cid = $this->input->post('cid');
        $draw = intval($this->input->post('draw'));
        $start = intval($this->input->post('start'));
        $length = intval($this->input->post('length'));

        // Get attendance data using model (model handles pagination with -1)
        $list = $this->employee->attendance_datatables($cid);
        $data = array();
        $no = $start + 1;

        foreach ($list as $obj) {
            $row = array();
            $row[] = $no;
            $row[] = $obj->name;
            $row[] = dateformat($obj->adate) . ' &nbsp; ' . $obj->tfrom . ' - ' . $obj->tto;
            $row[] = round((strtotime($obj->tto) - strtotime($obj->tfrom)) / 3600, 2);
            $row[] = round($obj->actual_hours / 3600, 2);
            $row[] = $obj->note;
            $row[] = '<a href="#" data-object-id="' . $obj->id . '" class="btn btn-danger btn-sm delete-object"><i class="fa fa-trash"></i></a>';

            $data[] = $row;
            $no++;
        }

        $output = array(
            "draw" => $draw,
            "recordsTotal" => $this->employee->attendance_count_all($cid),
            "recordsFiltered" => $this->employee->attendance_count_filtered($cid),
            "data" => $data,
        );

        echo json_encode($output);
    }

    public function delete_attendance()
    {
        $id = $this->input->post('deleteid');


        if ($this->employee->deleteattendance($id)) {
            echo json_encode(array('status' => 'Success', 'message' => $this->lang->line('DELETED')));
        } else {
            echo json_encode(array('status' => 'Error', 'message' => $this->lang->line('ERROR')));
        }
    }
}

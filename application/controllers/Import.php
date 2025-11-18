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



use PhpOffice\PhpSpreadsheet\IOFactory;



class Import extends CI_Controller

{





    public function __construct()

    {

        parent::__construct();

        $this->load->library("Aauth");

        $this->load->model('export_model', 'export');

        if (!$this->aauth->is_loggedin()) {

            redirect('/user/', 'refresh');

            exit;
        }



        if ($this->aauth->get_user()->roleid < 5) {



            exit('Not Allowed!');
        }

        $this->date = 'backup_' . date('Y_m_d_H_i_s');
    }


    function stock_adjustment()
    {
        $this->load->helper(array('form'));
        $this->load->model('categories_model');
        $head['title'] = "Stock Adjustment";
        $head['usernm'] = $this->aauth->get_user()->username;
        $data['cat'] = $this->categories_model->category_list();
        $data['warehouse'] = $this->categories_model->warehouse_list();
        $this->load->view('fixed/header', $head);
        $this->load->view('import/stock_adjustment', $data);
        $this->load->view('fixed/footer');
    }

    public function stock_adjustment_upload()
    {
        $this->load->helper(array('form'));
        $this->load->library('session'); // Load session for flash messages
        $this->load->library('upload');

        $head['usernm'] = $this->aauth->get_user()->username;
        $head['title'] = 'Stock Adjustment';

        $this->load->view('fixed/header', $head);

        if (!empty($_FILES['userfile']['name'])) {
            // Set upload configuration
            $config['upload_path']   = './userfiles/product/stock_update/';
            $config['allowed_types'] = 'csv';
            $config['max_size']      = 6000;

            $this->upload->initialize($config);

            if (!$this->upload->do_upload('userfile')) {
                $this->session->set_flashdata('error', 'File Upload Error: ' . $this->upload->display_errors());
                redirect('import/stock_adjustment'); // Redirect back to form
            } else {
                $uploadData = $this->upload->data();
                $filePath = $uploadData['full_path'];

                // Read file content
                $file = fopen($filePath, "r");
                $csvData = [];
                $row = 0;
                $user_id = $this->aauth->get_user()->id;
                $username = $this->aauth->get_user()->username;

                while (($line = fgetcsv($file, 1000, ",")) !== FALSE) {
                    if ($row == 0) {
                        // Skip header row if needed
                        $row++;
                        continue;
                    }

                    $stock_code = trim($line[0]); // First column (Stock Code)
                    $quantity   = (int) trim($line[1]); // Second column (Quantity)

                    // Fetch product details
                    $this->db->where('product_code', $stock_code);
                    $product = $this->db->get('geopos_products')->row_array();

                    if ($product) {
                        $old_qty = $product['qty'];
                        $new_qty = $old_qty - ($quantity);

                        // Update product quantity in geopos_products
                        $this->db->where('product_code', $stock_code);
                        $this->db->update('geopos_products', ['qty' => $new_qty]);

                        // Prepare data for stock_adjustments
                        $csvData[] = [
                            'pid'            => $product['pid'],
                            'product_name'   => $product['product_name'],
                            'product_price'  => $product['product_price'],
                            'fproduct_price' => $product['fproduct_price'],
                            'qty'        => $new_qty,
                            'old_qty'        => $old_qty,
                            'added_on'     => date('Y-m-d H:i:s'),
                            'added_by'       => $username
                        ];
                    }
                    $row++;
                }
                fclose($file);
                // dd($csvData);
                // if (!empty($csvData)) {
                //     // Insert batch data into stock_adjustments
                //     $this->db->insert_batch('products_history', $csvData);
                //     $this->session->set_flashdata('success', 'Stock adjustment file imported successfully!');
                // } else {
                //     $this->session->set_flashdata('error', 'CSV file is empty or contains invalid product codes.');
                // }
                if (!empty($csvData)) {
                    $this->db->insert_batch('products_history', $csvData);

                    // Check if rows were inserted
                    if ($this->db->affected_rows() > 0) {
                        $this->session->set_flashdata('success', 'Stock adjustment file imported successfully!');
                    } else {
                        echo "<pre>";
                        print_r($this->db->error()); // Show any DB errors
                        echo "</pre>";
                        exit(); // Stop execution to check error details
                    }
                } else {
                    $this->session->set_flashdata('error', 'CSV file is empty or contains invalid product codes.');
                }


                redirect('import/stock_adjustment'); // Redirect back
            }
        } else {
            $this->session->set_flashdata('error', 'Please select a file to upload.');
            redirect('import/stock_adjustment');
        }

        $this->load->view('fixed/footer');
    }




    function products()

    {

        $this->load->helper(array('form'));

        $this->load->model('categories_model');

        $head['title'] = "Import Products";

        $head['usernm'] = $this->aauth->get_user()->username;

        $data['cat'] = $this->categories_model->category_list();

        $data['warehouse'] = $this->categories_model->warehouse_list();

        $this->load->view('fixed/header', $head);

        $this->load->view('import/products', $data);

        $this->load->view('fixed/footer');
    }



    public function products_upload()

    {



        $this->load->helper(array('form'));

        $data['response'] = 3;

        $head['usernm'] = $this->aauth->get_user()->username;

        $head['title'] = 'Import Product';



        $this->load->view('fixed/header', $head);



        if ($this->input->post('product_cat', true)) {

            $data['pc'] = $this->input->post('product_cat', true);

            $data['wid'] = $this->input->post('product_warehouse', true);

            $config['upload_path'] = './userfiles';

            $config['allowed_types'] = 'csv';

            $config['max_size'] = 6000;

            $this->load->library('upload', $config);



            if (!$this->upload->do_upload('userfile')) {

                $data['response'] = 0;

                $data['responsetext'] = 'File Upload Error';
            } else {

                $data['response'] = 1;

                $data['responsetext'] = 'Document Uploaded Successfully.';

                $data['filename'] = $this->upload->data()['file_name'];
            }



            $this->load->view('import/wizard', $data);
        } else {





            echo ' error';
        }

        $this->load->view('fixed/footer');
    }





    public function start_process()

    {

        require APPPATH . 'third_party/vendor/autoload.php';



        $name = $this->input->post('name');

        $pcat = $this->input->post('pc');

        $warehouse = $this->input->post('wid');

        $inputFileName = FCPATH . 'userfiles/' . $name;



        $spreadsheet = IOFactory::load($inputFileName);

        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);

        //print_r($sheetData);



        $products = array();



        foreach ($sheetData as $row) {

            $barcode = rand(100, 999) . '-' . rand(0, 9) . '-' . rand(1000000, 9999999) . '-' . rand(0, 9);



            $products[] = array(

                'pid' => null,

                'pcat' => $pcat,

                'warehouse' => $warehouse,

                'product_name' => $row[0],

                'product_code' => $row[1],

                'product_price' => $row[2],

                'fproduct_price' => $row[3],

                'taxrate' => $row[4],

                'disrate' => $row[5],

                'qty' => $row[6],

                'product_des' => $row[7],

                'alert' => $row[8],

                'barcode' => $barcode

            );
        }

        unlink(FCPATH . 'userfiles/' . $name);

        if (count($sheetData[0]) == 9) {

            $out = $this->db->insert_batch('geopos_products', $products);

            if ($out) {

                echo json_encode(array('status' => 'Success', 'message' =>

                "Product Data Imported Successfully!"));
            } else {

                echo json_encode(array('status' => 'Error', 'message' =>

                "Database Import Error! Please use proper encoding of file and its content."));
            }
        } else {

            echo json_encode(array('status' => 'Error', 'message' =>

            "Please correct the format of CSV file, it should be as per template."));
        }
    }





    //customer





    function customers()

    {

        $this->load->helper(array('form'));

        $this->load->model('categories_model');

        $head['title'] = "Import Products";

        $head['usernm'] = $this->aauth->get_user()->username;

        $data['cat'] = $this->categories_model->category_list();

        $data['warehouse'] = $this->categories_model->warehouse_list();

        $this->load->view('fixed/header', $head);

        $this->load->view('import/customers', $data);

        $this->load->view('fixed/footer');
    }



    public function customers_upload()

    {



        $this->load->helper(array('form'));

        $data['response'] = 3;

        $head['usernm'] = $this->aauth->get_user()->username;

        $head['title'] = 'Import Customers';



        $this->load->view('fixed/header', $head);





        $config['upload_path'] = FCPATH . '/userfiles';

        $config['allowed_types'] = 'csv';

        $config['max_size'] = 6000;

        $this->load->library('upload', $config);



        if (!$this->upload->do_upload('userfile')) {

            $data['response'] = 0;

            $data['responsetext'] = 'File Upload Error';
        } else {

            $data['response'] = 1;

            $data['responsetext'] = 'Document Uploaded Successfully.';

            $data['filename'] = $this->upload->data()['file_name'];

            $data['password_s'] = $this->input->post('c_password');

            $data['password_v'] = $this->input->post('c_password_static');
        }



        $this->load->view('import/wizard2', $data);



        $this->load->view('fixed/footer');
    }





    public function customers_start_process()

    {

        require APPPATH . 'third_party/vendor/autoload.php';



        $name = $this->input->post('name');

        $c_pass = $this->input->post('c_pass');

        $c_pass_v = $this->input->post('c_pass_v');



        $inputFileName = FCPATH . 'userfiles/' . $name;



        $spreadsheet = IOFactory::load($inputFileName);

        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);

        //print_r($sheetData);



        $data = array();

        $data2 = array();



        $this->db->select('id');

        $this->db->from('geopos_customers');

        $this->db->order_by('id', 'DESC');

        $this->db->limit(1);



        $query = $this->db->get();

        if ($query->num_rows() > 0) {

            $l_id = +$query->row()->id + 1;
        } else {

            $l_id = 1;
        }



        foreach ($sheetData as $row) {





            $data[] = array(

                'id' => $l_id,

                'name' => $row[0],

                'phone' => $row[1],

                'address' => $row[2],

                'city' => $row[3],

                'region' => $row[4],

                'country' => $row[5],

                'postbox' => $row[6],

                'email' => $row[7],

                'gid' => $row[8],

                'taxid' => $row[9],

                'company' => $row[10],

                'name_s' => $row[11],

                'phone_s' => $row[12],

                'email_s' => $row[13],

                'address_s' => $row[14],

                'city_s' => $row[15],

                'region_s' => $row[16],

                'country_s' => $row[17],

                'postbox_s' => $row[18]

            );



            if ($c_pass == 'random') {

                $temp_password = rand(200000, 999999);

                $pass = password_hash($temp_password, PASSWORD_DEFAULT);
            } else {

                $pass = password_hash($c_pass_v, PASSWORD_DEFAULT);
            }



            $data2[] = array(

                'users_id' => null,

                'user_id' => 1,

                'status' => 'active',

                'is_deleted' => 0,

                'name' => $row[0],

                'password' => $pass,

                'email' => $row[7],

                'profile_pic' => $row[5],

                'user_type' => 'Member',

                'cid' => $l_id

            );

            $l_id++;
        }

        // unlink(FCPATH . 'userfiles/' . $name);

        if (count($sheetData[0]) == 19) {

            $out = $this->db->insert_batch('geopos_customers', $data);

            $out = $this->db->insert_batch('users', $data2);

            if ($out) {

                echo json_encode(array('status' => 'Success', 'message' =>

                "Customer Data Imported Successfully!"));
            } else {

                echo json_encode(array('status' => 'Error', 'message' =>

                "Database Import Error! Please use proper encoding of file and its content."));
            }
        } else {

            echo json_encode(array('status' => 'Error', 'message' =>

            "Please correct the format of CSV file, it should be as per template."));
        }
    }
}

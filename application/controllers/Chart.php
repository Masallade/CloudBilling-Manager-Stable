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



class Chart extends CI_Controller

{

    public function __construct()

    {



        parent::__construct();

        $this->load->library("Aauth");

        if (!$this->aauth->is_loggedin()) {

            redirect('/user/', 'refresh');

        }

        if (!$this->aauth->permission_new(null, 'chartAccess')) {



            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');



        }





        $this->load->model('chart_model', 'chart');

        $this->li_a = 'data';





    }



    public function index()

    {
        if (!$this->aauth->permission_new(null, 'chartAccess')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }


        $head['title'] = "Register";

        $data = $this->registerlog->lists();

        $head['usernm'] = $this->aauth->get_user()->username;

        $this->load->view('fixed/header', $head);

        $this->load->view('register/index', $data);

        $this->load->view('fixed/footer');



    }



    public function product_cat()

    {
        if (!$this->aauth->permission_new(null, 'salesreportsProductCategory')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }


        $head['title'] = "Product Categories";

        $type = $this->input->get('p');

        $data['chart'] = $this->chart->productcat($type);

        $head['usernm'] = $this->aauth->get_user()->username;

        $this->load->view('fixed/header', $head);

        $this->load->view('chart/productcat', $data);

        $this->load->view('fixed/footer');





    }



    public function product_update()

    {
        if (!$this->aauth->permission_new(null, 'salesreportsProductCategory')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $type = $this->input->post('p');

        $d1 = $this->input->post('sdate');

        $d2 = $this->input->post('edate');



        $out = $this->chart->productcat($type, $d1, $d2);

        $chart_array = array();

        foreach ($out as $item) {

            $chart_array[] = array('label' => $item['title'] . ' | ' . +$item['qty'], 'value' => $item['subtotal']);

        }

        echo json_encode($chart_array);



    }



    public function trending_products()

    {
        if (!$this->aauth->permission_new(null, 'salesreportTrendingProduct')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }


        $head['title'] = "Trending Products";

        $type = $this->input->get('p');

        $data['chart'] = $this->chart->trendingproducts($type);

        $head['usernm'] = $this->aauth->get_user()->username;

        $this->load->view('fixed/header', $head);

        $this->load->view('chart/trending', $data);

        $this->load->view('fixed/footer');





    }



    public function trending_products_update()

    {
        if (!$this->aauth->permission_new(null, 'salesreportTrendingProduct')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $type = $this->input->post('p');

        $d1 = $this->input->post('sdate');

        $d2 = $this->input->post('edate');



        $out = $this->chart->trendingproducts($type, $d1, $d2);

        $chart_array = array();

        foreach ($out as $item) {

            $chart_array[] = array('y' => $item['product_name'], 'a' => $item['qty']);

        }

        echo json_encode($chart_array);



    }



    public function profit()

    {
        if (!$this->aauth->permission_new(null, 'chartAccess')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }



        $head['title'] = "Profit Reports";

        $type = $this->input->get('p');

        $data['chart'] = $this->chart->profitchart($type);

        $head['usernm'] = $this->aauth->get_user()->username;

        $this->load->view('fixed/header', $head);

        $this->load->view('chart/profit', $data);

        $this->load->view('fixed/footer');





    }



    public function profit_update()

    {
        if (!$this->aauth->permission_new(null, 'chartAccess')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $type = $this->input->post('p');

        $d1 = $this->input->post('sdate');

        $d2 = $this->input->post('edate');



        $out = $this->chart->profitchart($type, $d1, $d2);

        $chart_array = array();

        foreach ($out as $item) {

            $chart_array[] = array('y' => $item['d_date'], 'a' => $item['col1']);

        }

        echo json_encode($chart_array);



    }



    public function topcustomers()

    {
        if (!$this->aauth->permission_new(null, 'salesreportsTopCustomers')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }



        $head['title'] = "Customer Reports";

        $type = $this->input->get('p');

        $data['chart'] = $this->chart->customerchart($type);

        $head['usernm'] = $this->aauth->get_user()->username;

        $this->load->view('fixed/header', $head);

        $this->load->view('chart/customer', $data);

        $this->load->view('fixed/footer');





    }





    public function customer_update()

    {
        if (!$this->aauth->permission_new(null, 'salesreportsTopCustomers')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $type = $this->input->post('p');

        $d1 = $this->input->post('sdate');

        $d2 = $this->input->post('edate');



        $out = $this->chart->customerchart($type, $d1, $d2);

        $chart_array = array();

        foreach ($out as $item) {

            $chart_array[] = array('y' => $item['name'], 'a' => $item['total']);

        }

        echo json_encode($chart_array);



    }



    public function income()

    {
        if (!$this->aauth->permission_new(null, 'chartAccess')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }



        $head['title'] = "Income Reports";

        $type = $this->input->get('p');

        $data['chart'] = $this->chart->incomechart($type);

        $head['usernm'] = $this->aauth->get_user()->username;

        $this->load->view('fixed/header', $head);

        $this->load->view('chart/income', $data);

        $this->load->view('fixed/footer');





    }



    public function income_update()

    {
        if (!$this->aauth->permission_new(null, 'chartAccess')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $type = $this->input->post('p');

        $d1 = $this->input->post('sdate');

        $d2 = $this->input->post('edate');



        $out = $this->chart->incomechart($type, $d1, $d2);

        $chart_array = array();

        foreach ($out as $item) {

            $chart_array[] = array('y' => $item['date'], 'a' => $item['credit']);

        }

        echo json_encode($chart_array);



    }



    public function expenses()

    {

        if (!$this->aauth->permission_new(null, 'chartAccess')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }

        $head['title'] = "Expenses Reports";

        $type = $this->input->get('p');

        $data['chart'] = $this->chart->expenseschart($type);

        $head['usernm'] = $this->aauth->get_user()->username;

        $this->load->view('fixed/header', $head);

        $this->load->view('chart/expenses', $data);

        $this->load->view('fixed/footer');





    }



    public function expenses_update()

    {
        if (!$this->aauth->permission_new(null, 'chartAccess')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
        $type = $this->input->post('p');

        $d1 = $this->input->post('sdate');

        $d2 = $this->input->post('edate');



        $out = $this->chart->expenseschart($type, $d1, $d2);

        $chart_array = array();

        foreach ($out as $item) {

            $chart_array[] = array('y' => $item['date'], 'a' => $item['debit']);

        }

        echo json_encode($chart_array);



    }



    public function incvsexp()

    {
        if (!$this->aauth->permission_new(null, 'chartAccess')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }



        $head['title'] = "Income vs Expense";

        $type = $this->input->get('p');

        $data['chart'] = $this->chart->incexp($type);

        $head['usernm'] = $this->aauth->get_user()->username;

        $this->load->view('fixed/header', $head);

        $this->load->view('chart/incvsexp', $data);

        $this->load->view('fixed/footer');





    }



    public function incvsexp_update()

    {
        if (!$this->aauth->permission_new(null, 'chartAccess')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }               
        $type = $this->input->post('p');

        $d1 = $this->input->post('sdate');

        $d2 = $this->input->post('edate');



        $out = $this->chart->incexp($type, $d1, $d2);

        $chart_array = array();

        foreach ($out as $item) {

            if ($item['type'] == 'Income') {

                $chart_array[] = array('label' => $item['type'], 'value' => $item['credit']);

            } elseif ($item['type'] == 'Expense') {

                $chart_array[] = array('label' => $item['type'], 'value' => $item['debit']);

            }



        }

        echo json_encode($chart_array);



    }





}
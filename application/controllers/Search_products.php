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

class Search_products extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library("Aauth");
        $this->load->model('search_model');
        $this->load->model('settings_model', 'settings');
        if (!$this->aauth->is_loggedin()) {
            redirect('/user/', 'refresh');
        }
        if (!$this->aauth->permission_new(null, 'stockAccess')) {
            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');
        }
    }

    //search product in invoice
    public function search()
    {
        $this->load->model('plugins_model', 'plugins');
        $auto_post = $this->settings->auto_post();
        $auto_post = $this->settings->auto_post();
        $auto_pricing = $this->settings->auto_pricing();
        $billing_settings = $this->plugins->universal_api(67);
        $result = array();
        $out = array();
        $row_num = $this->input->post('row_num', true);
        $name = $this->input->post('name_startsWith', true);
        $wid = $this->input->post('wid', true);
        $cid = $this->input->post('cid', true);
        $qw = '';
        if ($wid > 0) {
            $qw = "(geopos_products.warehouse='$wid') AND ";
        }
        if ($billing_settings['key2']) $qw .= "(geopos_products.expiry IS NULL OR DATE (geopos_products.expiry)<" . date('Y-m-d') . ") AND ";
        $join = '';

        if ($this->aauth->get_user()->loc) {
            $join = 'LEFT JOIN geopos_warehouse ON geopos_warehouse.id=geopos_products.warehouse';
            $join2 = 'LEFT JOIN geopos_warehouse ON geopos_warehouse.id=geopos_products.warehouse';
            if (BDATA) $qw .= '(geopos_warehouse.loc=' . $this->aauth->get_user()->loc . ' OR geopos_warehouse.loc=0) AND ';
            else $qw .= '(geopos_warehouse.loc=' . $this->aauth->get_user()->loc . ' ) AND ';
        } elseif (!BDATA) {
            $join = 'LEFT JOIN geopos_warehouse ON geopos_warehouse.id=geopos_products.warehouse';
            $qw .= '(geopos_warehouse.loc=0) AND ';
        }
        $e = '';
        if ($billing_settings['key1'] == 1) {
            $e .= ',geopos_product_serials.serial';
            $join .= 'LEFT JOIN geopos_product_serials ON geopos_product_serials.product_id=geopos_products.pid';
            $qw .= '(geopos_product_serials.status=0) AND ';
        }

        if ($name) {

            if ($billing_settings['key1'] == 2) {
                $e .= ',geopos_product_serials.serial';
                $query = $this->db->query("SELECT geopos_products.pid,geopos_products.product_name,geopos_products.weight_qty,geopos_products.weight_unit,geopos_products.product_price,geopos_products.fproduct_price,geopos_products.product_code,geopos_products.taxrate,geopos_products.disrate,geopos_products.product_des,geopos_products.qty,geopos_products.code_type,geopos_products.unit $e  FROM geopos_product_serials LEFT JOIN geopos_products  ON geopos_products.pid=geopos_product_serials.product_id $join WHERE " . $qw . "(UPPER(geopos_product_serials.serial) LIKE '%" . $name . "%')  OR (UPPER(geopos_products.product_des) LIKE '" . $name . "%') LIMIT 10");
            } else {
                $query = $this->db->query("SELECT geopos_products.pid,geopos_products.product_name,geopos_products.weight_qty,geopos_products.weight_unit,geopos_products.product_price,geopos_products.fproduct_price,geopos_products.product_code,geopos_products.taxrate,geopos_products.disrate,geopos_products.product_des,geopos_products.qty,geopos_products.code_type,geopos_products.unit $e  FROM geopos_products $join WHERE " . $qw . "(UPPER(geopos_products.product_code) LIKE '" . $name . "%') OR (UPPER(geopos_products.product_des) LIKE '" . $name . "%') LIMIT 50");
            }
            //echo $this->db->last_query(); exit;
            $result = $query->result_array();

            //var_dump($result); exit; 

            foreach ($result as $row) {

                $price = 0;
                // First, check geopos_invoice_items
                if ($cid > 0) {
                    if ($auto_pricing) {
                        // First, check geopos_invoice_items
                        $customer_pricing = $this->db->query("SELECT geopos_invoice_items.price 
                                                              FROM geopos_invoice_items 
                                                              WHERE geopos_invoice_items.cid = " . $cid . " 
                                                              AND geopos_invoice_items.pid = " . $row['pid'] . " 
                                                              ORDER BY geopos_invoice_items.id DESC LIMIT 1");
                        $res = $customer_pricing->result_array();

                        if (!empty($res)) {
                            $price = $res[0]['price']; // Price found in geopos_invoice_items
                        }
                    } else {
                        // If no price found in geopos_invoice_items, check geopos_customer_pricing
                        if ($price == 0) {
                            $customer_pricing_group = $this->db->query("SELECT geopos_customer_pricing.salesprice 
                                                                    FROM geopos_customer_pricing 
                                                                    WHERE geopos_customer_pricing.custid = " . $cid . " 
                                                                    AND geopos_customer_pricing.productid = " . $row['pid'] . " 
                                                                    ORDER BY geopos_customer_pricing.id DESC LIMIT 1");
                            $res_group = $customer_pricing_group->result_array();

                            if (!empty($res_group)) {
                                $price = $res_group[0]['salesprice']; // Price found in geopos_customer_pricing
                            }
                        }
                    }
                }

                // If no price found in either, use the product price from geopos_products
                if ($price == 0) {
                    $price = $row['fproduct_price'];  // Default to the base product price
                }
                // Prepare the product data with the determined price
                if ($price == 0) {
                    $name = array(
                        $row['product_name'],
                        amountExchange_s($row['fproduct_price'], 0, $this->aauth->get_user()->loc),
                        $row['pid'],
                        amountFormat_general($row['taxrate']),
                        amountFormat_general($row['disrate']),
                        $row['product_des'],
                        $row['unit'],
                        $row['product_code'],
                        amountFormat_general($row['qty']),
                        $row_num,
                        @$row['serial'],
                        $row['code_type'],
                        amountExchange_s($row['product_price'], 0, $this->aauth->get_user()->loc),
                        $row['weight_qty'],
                        $row['weight_unit']
                    );
                } else {
                    $name = array(
                        $row['product_name'],
                        amountExchange_s($price, 0, $this->aauth->get_user()->loc),
                        $row['pid'],
                        amountFormat_general($row['taxrate']),
                        amountFormat_general($row['disrate']),
                        $row['product_des'],
                        $row['unit'],
                        $row['product_code'],
                        amountFormat_general($row['qty']),
                        $row_num,
                        @$row['serial'],
                        $row['code_type'],
                        amountExchange_s($row['product_price'], 0, $this->aauth->get_user()->loc),
                        $row['weight_qty'],
                        $row['weight_unit']
                    );
                }

                array_push($out, $name);

                // if ($cid > 0) {

                //     // if ($auto_post == 1) {
                //     $customer_pricing = $this->db->query("SELECT geopos_invoice_items.price FROM geopos_invoice_items WHERE geopos_invoice_items.cid = " . $cid . " AND geopos_invoice_items.pid =" . $row['pid'] . " ORDER BY geopos_invoice_items.id DESC LIMIT 1");
                //     // } else {
                //     //     $customer_pricing = $this->db->query("SELECT geopos_invoice_items_bfr_post.price FROM geopos_invoice_items_bfr_post WHERE geopos_invoice_items_bfr_post.cid = " . $cid . " AND geopos_invoice_items_bfr_post.pid =" . $row['pid'] . " ORDER BY geopos_invoice_items_bfr_post.id DESC LIMIT 1");
                //     // }
                //     $res = $customer_pricing->result_array();

                //     if (is_array($res) && count($res) > 0) {

                //         foreach ($res as $rows) {

                //             $price = $rows['price'];
                //         }
                //     } else {
                //         $price = 0;
                //     }
                // }
                // if ($price == 0) {
                //     $name = array($row['product_name'], amountExchange_s($row['fproduct_price'], 0, $this->aauth->get_user()->loc), $row['pid'], amountFormat_general($row['taxrate']), amountFormat_general($row['disrate']), $row['product_des'], $row['unit'], $row['product_code'], amountFormat_general($row['qty']), $row_num, @$row['serial'], $row['code_type'], amountExchange_s($row['product_price'], 0, $this->aauth->get_user()->loc), $row['weight_qty'], $row['weight_unit']);
                // } else {
                //     $name = array($row['product_name'], amountExchange_s($price), $row['pid'], amountFormat_general($row['taxrate']), amountFormat_general($row['disrate']), $row['product_des'], $row['unit'], $row['product_code'], amountFormat_general($row['qty']), $row_num, @$row['serial'], $row['code_type'], amountExchange_s($row['product_price'], 0, $this->aauth->get_user()->loc), $row['weight_qty'], $row['weight_unit']);
                // }
                // array_push($out, $name);
            }
            echo json_encode($out);
        }
    }

    public function CustomerSearchCreditNote()
    {

        $result = array();

        $out = array();

        $whr = '';

        $name = $this->input->get('keyword', true);

        if ($name) {

            // $query2 ="SELECT trans_id FROM geopos_stock_r where id=".$name;
            // $query_str = $this->db->query($query2);
            // $result_st_r = $query->result_array();
            // var_dump(($query_str->num_rows());
            //   if ($query_str->num_rows() == 0) {
            $query2 = "SELECT csd,tid,id,pamnt as inv_balance FROM geopos_invoices where tid LIKE '%" . $name . "%'";
            // $query2 ="SELECT csd,tid,id,total-pamnt as inv_balance FROM geopos_invoices where tid LIKE '%".$name."%'";
            $query = $this->db->query($query2);
            $result2 = $query->result_array();

            foreach ($result2 as $row2) {

                if ($row2['inv_balance'] > 0) {




                    $query = $this->db->query("SELECT id,name,address,city,postbox,phone_s,email,company,discount_c,balance FROM geopos_customers WHERE id=" . $row2['csd']);

                    // $query = $this->db->query("SELECT id,name,address,city,postbox,phone_s,email,company,discount_c,balance FROM geopos_customers WHERE $whr (
                    //     UPPER(company)  LIKE '%" . strtoupper($name) . "%' OR UPPER(name)  LIKE '%" . strtoupper($name) . "%' OR UPPER(phone)  LIKE '" . strtoupper($name) . "%') LIMIT 6");

                    $result = $query->result_array();
                    foreach ($result as $row) {

                        $sql = 'SELECT sum(total)-sum(pamnt) as "cust_balance" FROM geopos_invoices WHERE status="paid" and csd="' . $row['id'] . '"';
                        // $sql = 'SELECT sum(total)-sum(pamnt) as "cust_balance" FROM geopos_invoices WHERE status="paid" and csd="' . $row['id'] . '" or status="partial" and csd="' . $row['id'] . '"';
                        // $sql='SELECT sum(total)-sum(pamnt) as "cust_balance" FROM geopos_invoices WHERE status="due" and csd="'.$row['id'].'" or status="partial" and csd="'.$row['id'].'"';    
                        $query = $this->db->query($sql);
                        $response = $query->result_array();
                        if ($row2['csd'] == $row['id']) {
                            $name = array($row['id'], $row2['tid'] . '-' . $row['name'], $row['address'], $row['city'], $row['phone_s'], $row['email'], $row['company'], amountFormat_general($response[0]['cust_balance']), $row['city'], $row['postbox'], $row2['id'], $row2['inv_balance']);

                            array_push($out, $name);
                        }
                    }
                }
            }


            echo json_encode($out);
            // }
        }
    }
    public function SupplierSearchCreditNote_po()
    {

        $result = array();

        $out = array();

        $whr = '';

        $name = $this->input->get('keyword', true);
        if ($name) {

            // $query2 ="SELECT trans_id FROM geopos_stock_r where id=".$name;
            // $query_str = $this->db->query($query2);
            // $result_st_r = $query->result_array();
            // var_dump(($query_str->num_rows());
            //   if ($query_str->num_rows() == 0) {
            $query2 = "SELECT csd,tid,id,pamnt as inv_balance FROM geopos_purchase where tid LIKE '%" . $name . "%'";
            $query = $this->db->query($query2);
            $result2 = $query->result_array();
            // var_dump($result2); die();

            foreach ($result2 as $row2) {

                if ($row2['inv_balance'] > 0) {




                    $query = $this->db->query("SELECT id,name,address,city,postbox,phone1,email,company FROM geopos_supplier WHERE id=" . $row2['csd']);

                    // $query = $this->db->query("SELECT id,name,address,city,postbox,phone_s,email,company,discount_c,balance FROM geopos_customers WHERE $whr (
                    //     UPPER(company)  LIKE '%" . strtoupper($name) . "%' OR UPPER(name)  LIKE '%" . strtoupper($name) . "%' OR UPPER(phone)  LIKE '" . strtoupper($name) . "%') LIMIT 6");

                    $result = $query->result_array();
                    foreach ($result as $row) {

                        $sql = 'SELECT sum(total)-sum(pamnt) as "cust_balance" FROM geopos_purchase WHERE status="paid" and csd="' . $row['id'] . '"';
                        // $sql = 'SELECT sum(total)-sum(pamnt) as "cust_balance" FROM geopos_purchase WHERE status="paid" and csd="' . $row['id'] . '" or status="partial" and csd="' . $row['id'] . '"';
                        // $sql='SELECT sum(total)-sum(pamnt) as "cust_balance" FROM geopos_purchase WHERE status="due" and csd="'.$row['id'].'" or status="partial" and csd="'.$row['id'].'"';    
                        $query = $this->db->query($sql);
                        $response = $query->result_array();
                        if ($row2['csd'] == $row['id']) {
                            $name = array($row['id'], $row2['tid'] . '-' . $row['name'], $row['address'], $row['city'], $row['phone1'], $row['email'], $row['company'], amountFormat_general($response[0]['cust_balance']), $row['city'], $row['postbox'], $row2['id'], $row2['inv_balance']);

                            array_push($out, $name);
                        }
                    }
                }
            }


            echo json_encode($out);
            // }
        }
    }

    public function puchase_search()
    {
        $out = array();
        $row_num = $this->input->post('row_num', true);
        $name = $this->input->post('name_startsWith', true);
        $wid = $this->input->post('wid', true);
        $cid = $this->input->post('cid', true);
        $qw = '';

        if ($wid > 0) {
            $qw = "(geopos_products.warehouse='$wid' ) AND ";
        }

        $join = '';

        if ($this->aauth->get_user()->loc) {
            $join = 'LEFT JOIN geopos_warehouse ON geopos_warehouse.id=geopos_products.warehouse';
            if (BDATA) {
                $qw .= '(geopos_warehouse.loc=' . $this->aauth->get_user()->loc . ' OR geopos_warehouse.loc=0) AND ';
            } else {
                $qw .= '(geopos_warehouse.loc=' . $this->aauth->get_user()->loc . ' ) AND ';
            }
        } elseif (!BDATA) {
            $join = 'LEFT JOIN geopos_warehouse ON geopos_warehouse.id=geopos_products.warehouse';
            $qw .= '(geopos_warehouse.loc=0) AND ';
        }

        if ($name) {
            $query = $this->db->query("SELECT * FROM `geopos_products` $join WHERE " . $qw . " (product_name LIKE '%" . $name . "%' OR product_code LIKE '%" . $name . "%') LIMIT 50");
            $result = $query->result_array();

            foreach ($result as $row) {
                $pid = $row['pid'];
                $codetype = $row['code_type'];

                // You can customize the array based on your needs
                $product_info = array(
                    $row['product_des'],
                    amountExchange_s($row['product_price'], 0, $this->aauth->get_user()->loc),
                    $row['pid'],
                    0,
                    0,
                    $row['product_des'],
                    1 /* Quantity */,
                    $row['product_code'],
                    ($row['p_to_b'] == 0 ? 1 : $row['p_to_b']),
                    $row['id'],
                    $codetype, // VAT type (T1 or T0) - FIXED: was 0
                    $codetype
                );

                array_push($out, $product_info);
            }

            echo json_encode($out);
        }
    }


    // public function puchase_search()
    // {
    //     $product_ids=array();
    //     $result = array();
    //     $out = array();
    //     $row_num = $this->input->post('row_num', true);
    //     $name = $this->input->post('name_startsWith', true);
    //     $wid = $this->input->post('wid', true);
    //     $cid = $this->input->post('cid', true);
    //     $qw = '';
    //     if ($wid > 0) {
    //         $qw = "(geopos_products.warehouse='$wid' ) AND ";
    //     }
    //     $join = '';
    //     if ($this->aauth->get_user()->loc) {
    //         $join = 'LEFT JOIN geopos_warehouse ON geopos_warehouse.id=geopos_products.warehouse';
    //         if (BDATA) $qw .= '(geopos_warehouse.loc=' . $this->aauth->get_user()->loc . ' OR geopos_warehouse.loc=0) AND '; else $qw .= '(geopos_warehouse.loc=' . $this->aauth->get_user()->loc . ' ) AND ';
    //     } elseif (!BDATA) {
    //         $join = 'LEFT JOIN geopos_warehouse ON geopos_warehouse.id=geopos_products.warehouse';
    //         $qw .= '(geopos_warehouse.loc=0) AND ';
    //     }
    //     if ($name) {

    //         $query = $this->db->query("SELECT id FROM `geopos_invoices` WHERE status='due' ORDER BY `id` DESC ");  
    //         $result = $query->result_array();

    //         foreach ($result as $row3) {


    //         $query2 = $this->db->query("SELECT  geopos_invoice_items.pid,  geopos_invoice_items.product,geopos_invoice_items.code,geopos_invoice_items.price,geopos_invoice_items.tax,geopos_invoice_items.discount,geopos_invoice_items.product_des,geopos_invoice_items.unit,geopos_invoice_items.qty FROM geopos_invoice_items WHERE tid = " . $row3['id'] ."   LIMIT 50 ");  
    //         $result2 = $query2->result_array();

    //             foreach ($result2 as $row) {
    //                 if ($row['qty']>0) {

    //                      if(!in_array($row['pid'], $product_ids)) {

    //                     $query=  $this->db->query("SELECT code_type FROM `geopos_products` WHERE pid=".$row['pid']);
    //                     $row2 = $query->result_array();
    //                     $codetype=$row2['0']['code_type'];
    //                     $name = array($row['product_des'], amountExchange_s($row['price'], 0, $this->aauth->get_user()->loc), $row['pid'], amountFormat_general($row['tax']), amountFormat_general($row['discount']), $row['product_des'], $row['unit'], $row['code'], $row_num, $row3['id'],$codetype);
    //             array_push($out, $name);
    //             array_push($product_ids, $row['pid']);
    //                      }
    //                 }
    //             }
    //         }

    //         echo json_encode($out);
    //     }

    // }

    public function search_stock()
    {

        $result = array();

        $out = array();

        $whr = '';

        $name = $this->input->get('keyword', true);

        if ($name) {


            $query = $this->db->query("SELECT geopos_products.pid,geopos_products.product_name,geopos_products.pcat,geopos_products.product_price,geopos_products.fproduct_price,geopos_products.product_code,geopos_products.taxrate,geopos_products.disrate,geopos_products.product_des,geopos_products.qty,geopos_products.code_type,geopos_products.unit $e  FROM geopos_products  WHERE "  . "(UPPER(geopos_products.product_code) LIKE '" . $name . "%') LIMIT 50");

            $result = $query->result_array();

            foreach ($result as $row) {
                $query2 = $this->db->query("SELECT geopos_product_cat.id,geopos_product_cat.title FROM geopos_product_cat  WHERE id=" . $row['pcat']);

                $result2 = $query2->result_array();
                $name = array($row['pid'], $row['product_name'], $row['product_code'], $row['product_des'], $result2[0]['title']);

                array_push($out, $name);
            }

            echo json_encode($out);
        }
    }


    public function csearch()
    {
        $result = array();
        $out = array();
        $name = $this->input->get('keyword', true);
        $whr = '';
        if ($this->aauth->get_user()->loc) {
            $whr = ' (loc=' . $this->aauth->get_user()->loc . ' OR loc=0) AND ';
            if (!BDATA) $whr = ' (loc=' . $this->aauth->get_user()->loc . ' ) AND ';
        } elseif (!BDATA) {
            $whr = ' (loc=0) AND ';
        }
        if ($name) {
            $query = $this->db->query("SELECT id,name,address,city,phone,email,company,discount_c,balance FROM geopos_customers WHERE $whr (UPPER(name)  LIKE '%" . strtoupper($name) . "%' OR UPPER(phone)  LIKE '" . strtoupper($name) . "%') LIMIT 6");
            $result = $query->result_array();
            echo '<ol>';
            $i = 1;
            foreach ($result as $row) {

                echo "<li onClick=\"selectCustomer('" . $row['id'] . "','" . $row['name'] . " ','" . $row['address'] . "','" . $row['city'] . "','" . $row['phone'] . "','" . $row['company'] . "','" . amountFormat_general($row['balance']) . "','" . amountFormat_general($row['discount_c']) . "')\"><span>$i</span><p>" . $row['name'] . " &nbsp; &nbsp  " . $row['company'] . "</p></li>";
                $i++;
            }
            echo '</ol>';
        }
    }


    public function crsearch()
    {
        $result = array();
        $out = array();
        $whr = '';
        $name = $this->input->get('keyword', true);

        if ($name) {


            $query = $this->db->query("SELECT id,name,address,city,postbox,phone_s,email,company,discount_c,balance FROM geopos_customers WHERE $whr (
                UPPER(company)  LIKE '%" . strtoupper($name) . "%' OR UPPER(name)  LIKE '%" . strtoupper($name) . "%' OR UPPER(phone)  LIKE '" . strtoupper($name) . "%') LIMIT 6");
            $result = $query->result_array();

            foreach ($result as $row) {
                $sql = 'SELECT sum(total)-sum(pamnt) as "cust_balance" FROM geopos_invoices WHERE status="due" and csd="' . $row['id'] . '" or status="partial" and csd="' . $row['id'] . '"';
                $query = $this->db->query($sql);
                $response = $query->result_array();
                $cust_balance = $response[0]['cust_balance'];


                $name = array($row['id'], $row['name'], $row['address'], $row['city'], $row['phone_s'], $row['email'], $row['company'], amountFormat_general($cust_balance), $row['city'], $row['postbox']);

                // $name = array($row['id'],$row['name'], $row['address'],$row['city'], $row['phone_s'],$row['email'],$row['company'],amountFormat_general($row['balance']), $row['city'], $row['postbox']);

                array_push($out, $name);
            }

            echo json_encode($out);
        }
    }


    public function party_search()
    {
        $result = array();
        $out = array();
        $tbl = 'geopos_customers';
        $name = $this->input->get('keyword', true);

        $ty = $this->input->get('ty', true);
        if ($ty) $tbl = 'geopos_supplier';
        $whr = '';


        if ($this->aauth->get_user()->loc) {
            $whr = ' (loc=' . $this->aauth->get_user()->loc . ' OR loc=0) AND ';
            if (!BDATA) $whr = ' (loc=' . $this->aauth->get_user()->loc . ' ) AND ';
        } elseif (!BDATA) {
            $whr = ' (loc=0) AND ';
        }


        if ($name) {
            $query = $this->db->query("SELECT id,name,address,city,phone,email FROM $tbl  WHERE $whr (UPPER(name)  LIKE '%" . strtoupper($name) . "%' OR UPPER(phone)  LIKE '" . strtoupper($name) . "%') LIMIT 6");
            $result = $query->result_array();
            echo '<ol>';
            $i = 1;
            foreach ($result as $row) {

                echo "<li onClick=\"selectCustomer('" . $row['id'] . "','" . $row['name'] . " ','" . $row['address'] . "','" . $row['city'] . "','" . $row['phone'] . "','" . $row['email'] . "')\"><span>$i</span><p>" . $row['name'] . " &nbsp; &nbsp  " . $row['phone'] . "</p></li>";
                $i++;
            }
            echo '</ol>';
        }
    }

    public function pos_c_search()
    {
        $result = array();
        $out = array();
        $name = $this->input->get('keyword', true);
        $whr = '';
        if ($this->aauth->get_user()->loc) {
            $whr = ' (loc=' . $this->aauth->get_user()->loc . ' OR loc=0) AND ';
            if (!BDATA) $whr = ' (loc=' . $this->aauth->get_user()->loc . ' ) AND ';
        } elseif (!BDATA) {
            $whr = ' (loc=0) AND ';
        }

        if ($name) {
            $query = $this->db->query("SELECT id,name,phone,discount_c FROM geopos_customers WHERE $whr (UPPER(name)  LIKE '%" . strtoupper($name) . "%' OR UPPER(phone)  LIKE '" . strtoupper($name) . "%') LIMIT 6");
            $result = $query->result_array();
            echo '<ol>';
            $i = 1;
            foreach ($result as $row) {
                echo "<li onClick=\"PselectCustomer('" . $row['id'] . "','" . $row['name'] . " ','" . amountFormat_general($row['discount_c']) . "')\"><span>$i</span><p>" . $row['name'] . " &nbsp; &nbsp  " . $row['phone'] . "</p></li>";
                $i++;
            }
            echo '</ol>';
        }
    }


    public function supplier()
    {

        $result = array();

        $out = array();

        $whr = '';

        $name = $this->input->get('keyword', true);

        if ($name) {


            $query = $this->db->query("SELECT id,name,address,city,phone,email FROM geopos_supplier WHERE $whr (UPPER(name)  LIKE '%" . strtoupper($name) . "%' OR UPPER(phone)  LIKE '" . strtoupper($name) . "%') LIMIT 6");

            $result = $query->result_array();

            foreach ($result as $row) {

                $name = array($row['id'], $row['name'], $row['address'], $row['city'], $row['phone'], $row['email']);

                array_push($out, $name);
            }

            echo json_encode($out);
        }
    }

    public function pos_search()
    {

        $out = '';
        $this->load->model('plugins_model', 'plugins');
        $billing_settings = $this->plugins->universal_api(67);
        $name = $this->input->post('name', true);
        $cid = $this->input->post('cid', true);
        $wid = $this->input->post('wid', true);
        $qw = '';
        if ($wid > 0) {
            $qw .= "(geopos_products.warehouse='$wid') AND ";
        }
        if ($billing_settings['key2']) $qw .= "(geopos_products.expiry IS NULL OR DATE (geopos_products.expiry)<" . date('Y-m-d') . ") AND ";
        if ($cid > 0) {
            $qw .= "(geopos_products.pcat='$cid') AND ";
        }
        $join = '';
        if ($this->aauth->get_user()->loc) {
            $join = 'LEFT JOIN geopos_warehouse ON geopos_warehouse.id=geopos_products.warehouse';
            if (BDATA) $qw .= '(geopos_warehouse.loc=' . $this->aauth->get_user()->loc . ' OR geopos_warehouse.loc=0) AND ';
            else $qw .= '(geopos_warehouse.loc=' . $this->aauth->get_user()->loc . ' ) AND ';
        } elseif (!BDATA) {
            $join = 'LEFT JOIN geopos_warehouse ON geopos_warehouse.id=geopos_products.warehouse';
            $qw .= '(geopos_warehouse.loc=0) AND ';
        }

        $e = '';
        if ($billing_settings['key1'] == 1) {
            $e .= ',geopos_product_serials.serial';
            $join .= 'LEFT JOIN geopos_product_serials ON geopos_product_serials.product_id=geopos_products.pid ';
            $qw .= '(geopos_product_serials.status=0) AND  ';
        }


        $bar = '';
        if (is_numeric($name)) {
            $b = array('-', '-', '-');
            $c = array(3, 4, 11);
            $barcode = $name;
            for ($i = count($c) - 1; $i >= 0; $i--) {
                $barcode = substr_replace($barcode, $b[$i], $c[$i], 0);
            }

            $bar = " OR (geopos_products.barcode LIKE '" . (substr($barcode, 0, -1)) . "%' OR geopos_products.barcode LIKE '" . $name . "%')";
        }
        if ($billing_settings['key1'] == 2) {

            $query = "SELECT geopos_products.*,geopos_product_serials.serial FROM geopos_product_serials  LEFT JOIN geopos_products  ON geopos_products.pid=geopos_product_serials.product_id $join WHERE " . $qw . "geopos_product_serials.serial LIKE '" . strtoupper($name) . "%'  AND (geopos_products.qty>0) LIMIT 16";
        } else {
            $query = "SELECT geopos_products.* $e FROM geopos_products $join WHERE " . $qw . "(UPPER(geopos_products.product_name) LIKE '%" . strtoupper($name) . "%' $bar OR geopos_products.product_code LIKE '" . strtoupper($name) . "%') AND (geopos_products.qty>0) LIMIT 16";
        }


        $query = $this->db->query($query);

        $result = $query->result_array();
        $i = 0;
        echo '<div class="row match-height">';
        foreach ($result as $row) {

            $out .= '    <div class="col-3 border mb-1 "><div class="rounded">
                                 <a   id="posp' . $i . '"  class="select_pos_item btn btn-outline-light-blue round"   data-name="' . $row['product_name'] . '"  data-price="' . amountExchange_s($row['product_price'], 0, $this->aauth->get_user()->loc) . '"  data-tax="' . amountFormat_general($row['taxrate']) . '"  data-discount="' . amountFormat_general($row['disrate']) . '"   data-pcode="' . $row['product_code'] . '"   data-pid="' . $row['pid'] . '"  data-stock="' . amountFormat_general($row['qty']) . '" data-unit="' . $row['unit'] . '" data-serial="' . @$row['serial'] . '">
                                       
                                        <div class="text-xs-center text">
                                       
                                            <small style="white-space: pre-wrap;">' . $row['product_name'] . '</small>

                                            
                                        </div></a>
                                  
                                </div></div>';

            $i++;
            //   if ($i % 4 == 0) $out .= '</div><div class="row">';
        }

        echo $out;
    }


    public function v2_pos_search()
    {

        $out = '';
        $this->load->model('plugins_model', 'plugins');
        $billing_settings = $this->plugins->universal_api(67);
        $name = (string)$this->input->post('name', true);
        $cid = (int)$this->input->post('cid', true);
        $wid = (int)$this->input->post('wid', true);
        $enable_bar = (string)$this->input->post('bar', true);
        $flag_p = false;

        $qw = '';

        if ($wid > 0) {
            $qw .= "(geopos_products.warehouse='$wid') AND ";
        }
        if ($billing_settings['key2']) $qw .= "(geopos_products.expiry IS NULL OR DATE (geopos_products.expiry)<" . date('Y-m-d') . ") AND ";
        if ($cid > 0) {
            $qw .= "(geopos_products.pcat='$cid') AND ";
        }
        $join = '';

        if ($this->aauth->get_user()->loc) {
            $join = 'LEFT JOIN geopos_warehouse ON geopos_warehouse.id=geopos_products.warehouse';
            if (BDATA) $qw .= '(geopos_warehouse.loc=' . $this->aauth->get_user()->loc . ' OR geopos_warehouse.loc=0) AND ';
            else $qw .= '(geopos_warehouse.loc=' . $this->aauth->get_user()->loc . ' ) AND ';
        } elseif (!BDATA) {
            $join = 'LEFT JOIN geopos_warehouse ON geopos_warehouse.id=geopos_products.warehouse';
            $qw .= '(geopos_warehouse.loc=0) AND ';
        }

        $e = '';
        if ($billing_settings['key1'] == 1) {
            $e .= ',geopos_product_serials.serial';
            $join .= 'LEFT JOIN geopos_product_serials ON geopos_product_serials.product_id=geopos_products.pid ';
            $qw .= '(geopos_product_serials.status=0) AND  ';
        }

        $bar = '';
        $p_class = 'v2_select_pos_item';
        if ($enable_bar == 'true' and is_numeric($name) and strlen($name) >= 8) {
            $flag_p = true;
            $bar = " (geopos_products.barcode = '" . (substr($name, 0, -1)) . "' OR geopos_products.barcode LIKE '" . $name . "%')";

            $query = "SELECT geopos_products.*  FROM geopos_products $join WHERE " . $qw . "$bar AND (geopos_products.qty>0) ORDER BY geopos_products.product_name LIMIT 6";
            $p_class = 'v2_select_pos_item_bar';
        } elseif ($enable_bar == 'false' or !$enable_bar) {
            $flag_p = true;
            if ($billing_settings['key1'] == 2) {

                $query = "SELECT geopos_products.*,geopos_product_serials.serial FROM geopos_product_serials  LEFT JOIN geopos_products  ON geopos_products.pid=geopos_product_serials.product_id $join WHERE " . $qw . "geopos_product_serials.serial LIKE '" . strtoupper($name) . "%'  AND (geopos_products.qty>0) LIMIT 18";
            } else {

                $query = "SELECT geopos_products.* $e FROM geopos_products $join WHERE " . $qw . "(UPPER(geopos_products.product_name) LIKE '%" . strtoupper($name) . "%' $bar OR geopos_products.product_code LIKE '" . strtoupper($name) . "%') AND (geopos_products.qty>0) ORDER BY geopos_products.product_name LIMIT 18";
            }
        }

        if ($flag_p) {
            $query = $this->db->query($query);
            $result = $query->result_array();
            $i = 0;
            $out = '<div class="row match-height">';
            foreach ($result as $row) {
                if ($bar) $bar = $row['barcode'];
                $out .= '    <div class="col-2 border mb-1"  ><div class=" rounded" >
                                 <a  id="posp' . $i . '"  class="' . $p_class . ' round"   data-name="' . $row['product_name'] . '"  data-price="' . amountExchange_s($row['product_price'], 0, $this->aauth->get_user()->loc) . '"  data-tax="' . amountFormat_general($row['taxrate']) . '"  data-discount="' . amountFormat_general($row['disrate']) . '" data-pcode="' . $row['product_code'] . '"   data-pid="' . $row['pid'] . '"  data-stock="' . amountFormat_general($row['qty']) . '" data-unit="' . $row['unit'] . '" data-serial="' . @$row['serial'] . '" data-bar="' . $bar . '">
                                      
                                        <div class="text-center" style="margin-top: 4px;">
                                       
                                            <b style="white-space: pre-wrap;">' . $row['product_name'] . '</b>

                                            
                                        </div></a>
                                  
                                </div></div>';

                $i++;
            }


            $out .= '</div>';

            echo $out;
        }
    }
    //search new customer in invoice
    public function CustomerSearch()
    {

        $result = array();

        $out = array();

        $whr = '';

        $name = $this->input->get('keyword', true);

        if ($name) {


            $query = $this->db->query("SELECT id,name,address,city,postbox,phone_s,email,company,discount_c,balance FROM geopos_customers WHERE $whr (
                UPPER(company)  LIKE '%" . strtoupper($name) . "%' OR UPPER(name)  LIKE '%" . strtoupper($name) . "%' OR UPPER(phone)  LIKE '" . strtoupper($name) . "%') LIMIT 6");

            $result = $query->result_array();

            foreach ($result as $row) {

                $sql = 'SELECT sum(total)-sum(pamnt) as "cust_balance" FROM geopos_invoices WHERE status="due" and csd="' . $row['id'] . '" or status="partial" and csd="' . $row['id'] . '"';
                $query = $this->db->query($sql);
                $response = $query->result_array();
                $name = array($row['id'], $row['name'], $row['address'], $row['city'], $row['phone_s'], $row['email'], $row['company'], amountFormat_general($response[0]['cust_balance']), $row['city'], $row['postbox']);

                array_push($out, $name);
            }

            echo json_encode($out);
        }
    }

    //search customer invoices
    public function CustomerInvoiceSearch()
    {
        $result = array();
        $out = array();
        $whr = '';
        $name = $this->input->get('keyword', true);

        if ($name) {
            $query2 = "SELECT csd,tid,id,pamnt as inv_balance FROM geopos_invoices where csd=" . intval($name) . " AND (status='due' OR status='partial') ORDER BY id DESC LIMIT 10";
            $query = $this->db->query($query2);
            $result2 = $query->result_array();

            foreach ($result2 as $row2) {
                if ($row2['inv_balance'] > 0) {
                    $query = $this->db->query("SELECT id,name,address,city,postbox,phone_s,email,company,discount_c,balance FROM geopos_customers WHERE id=" . $row2['csd']);
                    $result = $query->result_array();
                    
                    foreach ($result as $row) {
                        $sql = 'SELECT sum(total)-sum(pamnt) as "cust_balance" FROM geopos_invoices WHERE status="paid" and csd="' . $row['id'] . '"';
                        $query = $this->db->query($sql);
                        $response = $query->result_array();
                        
                        if ($row2['csd'] == $row['id']) {
                            $name = array(
                                $row['id'], 
                                $row2['tid'] . '-' . $row['name'], 
                                $row['address'], 
                                $row['city'], 
                                $row['phone_s'], 
                                $row['email'], 
                                $row['company'], 
                                amountFormat_general($response[0]['cust_balance']), 
                                $row['city'], 
                                $row['postbox'], 
                                $row2['id'], 
                                $row2['inv_balance']
                            );
                            array_push($out, $name);
                        }
                    }
                }
            }
            echo json_encode($out);
        }
    }


    public function group_pos_search()
    {

        $out = '';
        $this->load->model('plugins_model', 'plugins');
        $billing_settings = $this->plugins->universal_api(67);
        $name = $this->input->post('name', true);
        $cid = $this->input->post('cid', true);
        $wid = $this->input->post('wid', true);


        $qw = '';

        if ($wid > 0) {
            $qw .= "(geopos_product_groups.warehouse='$wid') AND ";
        }

        $join = '';

        if ($this->aauth->get_user()->loc) {
            $qw .= "(geopos_product_groups.loc='" . $this->aauth->get_user()->loc . "') AND ";
            $join = 'LEFT JOIN geopos_warehouse ON geopos_warehouse.id=geopos_products.warehouse';
            if (BDATA) $qw .= '(geopos_warehouse.loc=' . $this->aauth->get_user()->loc . ' OR geopos_warehouse.loc=0) AND ';
            else $qw .= '(geopos_warehouse.loc=' . $this->aauth->get_user()->loc . ' ) AND ';
        } elseif (!BDATA) {
            $join = 'LEFT JOIN geopos_warehouse ON geopos_warehouse.id=geopos_products.warehouse';
            $qw .= '(geopos_warehouse.loc=0) AND ';
        }

        $e = '';
        if ($billing_settings['key1'] == 1) {
            $e .= ',geopos_product_serials.serial';
            $join .= 'LEFT JOIN geopos_product_serials ON geopos_product_serials.product_id=geopos_products.pid ';
            $qw .= '(geopos_product_serials.status=0) AND  ';
        }

        $bar = '';

        if (is_numeric($name)) {
            $b = array('-', '-', '-');
            $c = array(3, 4, 11);
            $barcode = $name;
            for ($i = count($c) - 1; $i >= 0; $i--) {
                $barcode = substr_replace($barcode, $b[$i], $c[$i], 0);
            }
            //    echo(substr($barcode, 0, -1));
            $bar = " OR (geopos_products.barcode LIKE '" . (substr($barcode, 0, -1)) . "%' OR geopos_products.barcode LIKE '" . $name . "%')";
            //  $query = "SELECT geopos_products.* FROM geopos_products $join WHERE " . $qw . " $bar AND (geopos_products.qty>0) LIMIT 16";
        }
        if ($billing_settings['key1'] == 2) {

            $query = "SELECT geopos_products.*,geopos_product_serials.serial FROM geopos_product_serials  LEFT JOIN geopos_products  ON geopos_products.pid=geopos_product_serials.product_id $join WHERE " . $qw . "geopos_product_serials.serial LIKE '" . strtoupper($name) . "%'  AND (geopos_products.qty>0) LIMIT 18";
        } else {
            $query = "SELECT geopos_products.* $e FROM geopos_products $join WHERE " . $qw . "(UPPER(geopos_products.product_name) LIKE '%" . strtoupper($name) . "%' $bar OR geopos_products.product_code LIKE '" . strtoupper($name) . "%') AND (geopos_products.qty>0) ORDER BY geopos_products.product_name LIMIT 18";
        }

        $query = $this->db->query($query);
        $result = $query->result_array();
        $i = 0;
        echo '<div class="row match-height">';
        foreach ($result as $row) {

            $out .= '    <div class="col-2 border mb-1"  ><div class=" rounded" >
                                 <a  id="posp' . $i . '"  class="v2_select_pos_item round"   data-name="' . $row['product_name'] . '"  data-price="' . amountExchange_s($row['product_price'], 0, $this->aauth->get_user()->loc) . '"  data-tax="' . amountFormat_general($row['taxrate']) . '"  data-discount="' . amountFormat_general($row['disrate']) . '" data-pcode="' . $row['product_code'] . '"   data-pid="' . $row['pid'] . '"  data-stock="' . amountFormat_general($row['qty']) . '" data-unit="' . $row['unit'] . '" data-serial="' . @$row['serial'] . '">
                                        <img class="round"
                                             src="' . base_url('userfiles/product/' . $row['image']) . '"  style="max-height: 100%;max-width: 100%">
                                        <div class="text-center" style="margin-top: 4px;">
                                       
                                            <small style="white-space: pre-wrap;">' . $row['product_name'] . '</small>

                                            
                                        </div></a>
                                  
                                </div></div>';

            $i++;
        }

        echo $out;
    }
}

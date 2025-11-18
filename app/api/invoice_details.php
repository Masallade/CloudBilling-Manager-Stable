<?php
// required headers
header("Access-Control-Allow-Origin: https://cloudbillingmanager.com/api/invoices.php");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
require_once "./include/configdb.php";
include_once './include/core.php';
include_once './libs/php-jwt-master/src/BeforeValidException.php';
include_once './libs/php-jwt-master/src/ExpiredException.php';
include_once './libs/php-jwt-master/src/SignatureInvalidException.php';
include_once './libs/php-jwt-master/src/JWT.php';
use \Firebase\JWT\JWT;
// echo $_GET["jwt"];

// $data = json_decode(file_get_contents("php://input"));
$jwt=isset($_GET["jwt"]) ? $_GET["jwt"] : "";
// if jwt is not empty
if($jwt){
    
    // if decode succeed, show user details
    try {
        // decode jwt
        $decoded = JWT::decode($jwt, $key, array('HS256'));
        // set user property values here
         $c_id=$decoded->data->id;
         $invoice_id=$_GET["invoice_id"];
        // $sql = "SELECT SUM(geopos_invoices.shipping + geopos_invoices.ship_tax) AS shipping, `geopos_invoices`.`loc` as `loc`, `geopos_invoices`.`id` AS `iid`, `geopos_customers`.`id` AS `cid`, `geopos_terms`.`id` AS `termid`, `geopos_terms`.`title` AS `termtit`, `geopos_terms`.`terms` AS `terms` FROM `geopos_invoices` LEFT JOIN `geopos_customers` ON `geopos_invoices`.`csd` = `geopos_customers`.`id` LEFT JOIN `geopos_terms` ON `geopos_terms`.`id` = `geopos_invoices`.`term` WHERE `geopos_invoices`.`id` = '$invoice_id'";


    $sql='SELECT sum(total)-sum(pamnt) as "cust_balance" FROM geopos_invoices WHERE status="due" and csd="'.$c_id.'" or status="partial" and csd="'.$c_id.'"';  
    if ($result = mysqli_query($link, $sql)) {
        if (mysqli_num_rows($result)==1){
            while ($row = mysqli_fetch_array($result)) {
                $cust_balance = $row['cust_balance'];
            }
        }
    }

$sql = "SELECT `geopos_invoices`.*, SUM(geopos_invoices.shipping + geopos_invoices.ship_tax) AS shipping, `geopos_customers`.*, `geopos_invoices`.`loc` as `loc`, `geopos_invoices`.`id` AS `iid`, `geopos_customers`.`id` AS `cid`, `geopos_terms`.`id` AS `termid`, `geopos_terms`.`title` AS `termtit`, `geopos_terms`.`terms` AS `terms` FROM `geopos_invoices` LEFT JOIN `geopos_customers` ON `geopos_invoices`.`csd` = `geopos_customers`.`id` LEFT JOIN `geopos_terms` ON `geopos_terms`.`id` = `geopos_invoices`.`term` WHERE `geopos_invoices`.`id` = '$invoice_id'";

        if ($result = mysqli_query($link, $sql)) {
            if (mysqli_num_rows($result)){
                while ($row = mysqli_fetch_assoc($result)) {
                   // print_r($row);die();
                    $invoice_details['id']=$row['id'];
                    $invoice_details['tid']=$row['tid'];
                    $invoice_details['inv_type']=$row['inv_type'];
                    $invoice_details['invoicedate']=$row['invoicedate'];                  
                    $invoice_details['invoiceduedate']=$row['invoiceduedate'];
                    $invoice_details['subtotal']=$row['subtotal'];
                    $invoice_details['shipping']=$row['shipping'];
                    $invoice_details['ship_tax']=$row['ship_tax'];
                    $invoice_details['ship_tax_type']=$row['ship_tax_type'];
                    $invoice_details['discount']=$row['discount'];
                    $invoice_details['discount_rate']=$row['discount_rate'];
                    $invoice_details['tax']=$row['tax'];
                    $invoice_details['total']=$row['total'];
                    $invoice_details['pmethod']=$row['pmethod'];
                    $invoice_details['notes']=$row['notes'];
                    $invoice_details['status']=$row['status'];
                    $invoice_details['csd']=$row['csd'];
                    $invoice_details['eid']=$row['eid'];
                    $invoice_details['pamnt']=$row['pamnt'];
                    $invoice_details['items']=$row['items'];
                    $invoice_details['taxstatus']=$row['taxstatus'];
                    $invoice_details['discstatus']=$row['discstatus'];
                    $invoice_details['format_discount']=$row['format_discount'];
                    $invoice_details['refer']=$row['refer'];
                    $invoice_details['term']=$row['term'];
                    $invoice_details['multi']=$row['multi'];
                    $invoice_details['i_class']=$row['i_class'];
                    $invoice_details['loc']=$row['loc'];
                    $invoice_details['r_time']=$row['r_time'];
                    $invoice_details['cust_name']=$row['cust_name'];
                    $invoice_details['cust_address']=$row['cust_address'];
                    $invoice_details['cust_city']=$row['cust_city'];
                    $invoice_details['cust_postcode']=$row['cust_postcode'];
                    $invoice_details['shipping']=$row['shipping'];
                    $invoice_details['name']=$row['name'];
                    $invoice_details['phone']=$row['phone'];
                    $invoice_details['address']=$row['address'];
                    $invoice_details['city']=$row['city'];
                    $invoice_details['region']=$row['region'];
                    $invoice_details['country']=$row['country'];
                    $invoice_details['postbox']=$row['postbox'];
                    $invoice_details['email']=$row['email'];
                    $invoice_details['picture']=$row['picture'];
                    $invoice_details['gid']=$row['gid'];
                    $invoice_details['company']=$row['company'];
                    $invoice_details['taxid']=$row['taxid'];
                    $invoice_details['name_s']=$row['name_s'];
                    $invoice_details['phone_s']=$row['phone_s'];
                    $invoice_details['email_s']=$row['email_s'];
                    $invoice_details['address_s']=$row['address_s'];
                    $invoice_details['city_s']=$row['city_s'];
                    $invoice_details['region_s']=$row['region_s'];
                    $invoice_details['country_s']=$row['country_s'];
                    $invoice_details['postbox_s']=$row['postbox_s'];
                    $invoice_details['balance']=$cust_balance;    //$row[55];
                    $invoice_details['balance_2']=$row['balance_2'];
                    $invoice_details['loc']=$row['loc'];
                    $invoice_details['docid']=$row['docid'];
                    $invoice_details['custom1']=$row['custom1'];
                    $invoice_details['discount_c']=$row['discount_c'];
                    $invoice_details['reg_date']=$row['reg_date'];
                    $invoice_details['loc_c']=$row['loc_c'];
                    $invoice_details['iid']=$row['iid'];
                    $invoice_details['cid']=$row['cid'];
                    $invoice_details['termid']=$row['termid'];
                    $invoice_details['termtit']=$row['termtit'];
                    $invoice_details['terms']=$row['terms'];
                    if($row[2]=='INVOICE'){
                        if($row[55]>=$row[12]){
                            $invoice_details['previous_balance']=$row[55]-$row[12];
                        }
                        else{ 
                             $invoice_details['previous_balance']=$row[55];
                        }
                    }else{
                        $invoice_details['previous_balance']=0.00;
                        //$invoice_details['name']='';
                        $invoice_details['tid']='DAYPASS';
                    }
               }

               $total_net_ammount;
                $sql = "SELECT * FROM `geopos_invoice_items` WHERE `tid` = '$invoice_id'";
                if ($result = mysqli_query($link, $sql)) {
                    if (mysqli_num_rows($result)){
                        $i=0;
                        while ($row = mysqli_fetch_array($result)) {
                        $invoice_items[$i]['id']=$row[0];
                        $invoice_items[$i]['tid']=$row[1];
                        $invoice_items[$i]['pid']=$row[2];
                        $invoice_items[$i]['cat_id']=$row[3];
                        $invoice_items[$i]['cid']=$row[4];
                        $invoice_items[$i]['product']=$row[5];
                        $invoice_items[$i]['code']=$row[6];
                        $invoice_items[$i]['qty']=$row[7];
                        $invoice_items[$i]['price']=$row[8];
                        $invoice_items[$i]['tax']=$row[9];
                        $invoice_items[$i]['discount']=$row[10];
                        $invoice_items[$i]['subtotal']=$row[11];
                        $invoice_items[$i]['totaltax']=$row[12];
                        $invoice_items[$i]['totaldiscount']=$row[13];
                        $invoice_items[$i]['product_des']=$row[14];
                        $invoice_items[$i]['i_class']=$row[15];
                        $invoice_items[$i]['unit']=$row[16];
                        $invoice_items[$i]['serial']=$row[17];
                        $total_net_ammount+=$row[8]*$row[7];
                        $i++;
                        }
                    }
                }
                 $invoice_details['total_net_ammount']=$total_net_ammount;
                $eid= $invoice_details['eid'];
                $sql = "SELECT `geopos_employees`.`name`, `geopos_employees`.`sign`, `geopos_users`.`roleid` FROM `geopos_employees` LEFT JOIN `geopos_users` ON `geopos_employees`.`id` = `geopos_users`.`id` WHERE `geopos_employees`.`id` = ' $eid'";
                if ($result = mysqli_query($link, $sql)) {
                    if (mysqli_num_rows($result)){
                        $i=0;
                        while ($row = mysqli_fetch_array($result)) {
                        $employee['name']=$row[0];
                        $employee['sign']=$row[1];
                        $employee['roleid']=$row[2];
                        $i++;
                        }
                    }
                }
               echo json_encode(array(
               "message" => "data retrived.",
               "status" => "200",
               "invoice_details" => $invoice_details,
               "invoice_items" => $invoice_items,
               "employee" =>$employee
               ));
            }else{
                echo json_encode(array(
                "message" => "Record Not Found.",
                "status" => "100",
               ));
            }

        }
    }
    // catch failed decoding will be here
    // if decode fails, it means jwt is invalid
    catch (Exception $e){
    // set response code
    http_response_code(401);
    // show error message
    echo json_encode(array(
        "message" => "Access denied.",
        "error" => $e->getMessage()
    ));
    }
}
// error message if jwt is empty will be here 
 ?>
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
         //var_dump($c_id); exit; 
        $sql = "SELECT *  FROM geopos_invoices WHERE csd='$c_id' ORDER BY invoicedate DESC";
        //echo $sql; exit; 
        if ($result = mysqli_query($link, $sql)) {
            $i=0;
            if (mysqli_num_rows($result)){
                while ($row = mysqli_fetch_array($result)) {
                  $all_data[$i]['id']=$row[0];
                  $all_data[$i]['tid']=$row[1];
                  $all_data[$i]['inv_type']=$row[2];
                   $all_data[$i]['invoicedate']=$row[3];
                  $all_data[$i]['invoiceduedate']=$row[4];
                  $all_data[$i]['subtotal']=$row[5];
                   $all_data[$i]['shipping']=$row[6];
                  $all_data[$i]['ship_tax']=$row[7];
                  $all_data[$i]['ship_tax_type']=$row[8];
                   $all_data[$i]['discount']=$row[9];
                  $all_data[$i]['discount_rate']=$row[10];
                  $all_data[$i]['tax']=$row[11];
                   $all_data[$i]['total']=$row[12];
                  $all_data[$i]['pmethod']=$row[13];
                  $all_data[$i]['notes']=$row[14];
                   $all_data[$i]['status']=$row[15];
                  $all_data[$i]['csd']=$row[16];
                  $all_data[$i]['eid']=$row[17];
                   $all_data[$i]['pamnt']=$row[18];
                  $all_data[$i]['items']=$row[19];
                  $all_data[$i]['taxstatus']=$row[20];
                   $all_data[$i]['discstatus']=$row[21];
                  $all_data[$i]['format_discount']=$row[22];
                  $all_data[$i]['refer']=$row[23];
                   $all_data[$i]['term']=$row[24];
                  $all_data[$i]['multi']=$row[25];
                  $all_data[$i]['i_class']=$row[26];
                   $all_data[$i]['loc']=$row[27];
                  $all_data[$i]['r_time']=$row[28];
                  $i++;
                  
               }
            //    echo json_encode(array(
            //    "message" => "data retrived.",
            //    "status" => "200",
            //    "customer_profile" => $all_data
            //    ));
                   echo json_encode( $all_data);
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
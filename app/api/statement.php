<?php
// required headers
header("Access-Control-Allow-Origin: https://cloudbillingmanager.com/api/customer_profile.php");
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
        $sql = "SELECT `geopos_customers`.*, `users`.`lang` FROM `geopos_customers` LEFT JOIN `users` ON `users`.`cid`=`geopos_customers`.`id` WHERE `geopos_customers`.`id` = '$c_id'";
        if ($result = mysqli_query($link, $sql)) {
            if (mysqli_num_rows($result)==1){
                while ($row = mysqli_fetch_array($result)) {
                  $customer_details['id']=$row[0];
                  $customer_details['name']=$row[1];
                  $customer_details['phone']=$row[2];
                  $customer_details['address']=$row[3];
                  $customer_details['city']=$row[4];
                  $customer_details['region']=$row[5];
                  $customer_details['country']=$row[6];
                  $customer_details['postbox']=$row[7];
                  $customer_details['email']=$row[8];
                  $customer_details['picture']=$row[9];
                  $customer_details['gid']=$row[10];
                  $customer_details['company']=$row[11];
                  $customer_details['taxid']=$row[12];
                  $customer_details['name_s']=$row[13];
                  $customer_details['phone_s']=$row[14];
                  $customer_details['email_s']=$row[15];
                  $customer_details['address_s']=$row[16];
                  $customer_details['city_s']=$row[17];
                  $customer_details['region_s']=$row[18];
                  $customer_details['country_s']=$row[19];
                  $customer_details['postbox_s']=$row[20];
                  $customer_details['balance']=$row[21];
                  $customer_details['loc']=$row[22];
                  $customer_details['docid']=$row[23];
                  $customer_details['custom1']=$row[24];
                  $customer_details['discount_c']=$row[25];
                  $customer_details['reg_date']=$row[26];
                  $customer_details['lang']=$row[27];
               }
               $start_date=$_GET['start_date'];
               $end_date=$_GET['end_date'];
                  $sql = "SELECT * FROM `geopos_transactions` WHERE `payerid` = ' $c_id' AND (DATE(date) BETWEEN '$start_date' AND '$end_date') AND `ext` =0";
                if ($result = mysqli_query($link, $sql)) {
                    if (mysqli_num_rows($result)){
                        $i=0;
                        while ($row = mysqli_fetch_array($result)) {
                        $transactions[$i]['id']=$row[0];
                        $transactions[$i]['acid']=$row[1];
                        $transactions[$i]['account']=$row[2];
                        $transactions[$i]['type']=$row[3];
                        $transactions[$i]['cat']=$row[4];
                        $transactions[$i]['credit']=$row[6];
                        $transactions[$i]['debit']=$row[5];
                        $transactions[$i]['balance']=$row[7];
                        $transactions[$i]['payer']=$row[8];
                        $transactions[$i]['payerid']=$row[9];
                        $transactions[$i]['method']=$row[10];
                        $transactions[$i]['date']=$row[11];
                        $transactions[$i]['tid']=$row[12];
                        $transactions[$i]['eid']=$row[13];
                        $transactions[$i]['note']=$row[14];
                        // if(strpos($row[13], 'Invoice #') !== false){
                        //   $transactions[$i]['ref']=substr($row[13],9,15);  
                        // }else{
                        //     $transactions[$i]['ref']="";
                        // }
                      
                        $transactions[$i]['ext']=$row[15];
                        $transactions[$i]['loc']=$row[16];
                        $i++;
                        }
                    }
                }
               
               
               echo json_encode(array(
               "message" => "data retrived.",
               "status" => "200",
               "customer_details" => $customer_details,
               "transactions" => $transactions
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
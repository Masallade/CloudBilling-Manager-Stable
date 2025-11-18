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
         $c_name=$decoded->data->name;
        $sql = "SELECT *  FROM geopos_customers WHERE name = '$c_name'";
        if ($result = mysqli_query($link, $sql)) {
            if (mysqli_num_rows($result)==1){
                while ($row = mysqli_fetch_array($result)) {
                  $all_data['id']=$row[1];
                  $all_data['name']=$row[2];
                  $all_data['phone']=$row[3];
                  $all_data['address']=$row[4];
                  $all_data['city']=$row[5];
                  $all_data['region']=$row[6];
                  $all_data['country']=$row[7];
                  $all_data['postbox']=$row[8];
                  $all_data['email']=$row[9];
                  $all_data['picture']=$row[10];
                  $all_data['gid']=$row[11];
                  $all_data['company']=$row[12];
                  $all_data['taxid']=$row[13];
                  $all_data['name_s']=$row[14];
                  $all_data['phone_s']=$row[15];
                  $all_data['email_s']=$row[16];
                  $all_data['address_s']=$row[17];
                  $all_data['city_s']=$row[18];
                  $all_data['region_s']=$row[19];
                  $all_data['country_s']=$row[20];
                  $all_data['postbox_s']=$row[21];
                  $all_data['balance']=$row[22];
                  $all_data['loc']=$row[23];
                  $all_data['docid']=$row[24];
                  $all_data['custom1']=$row[25];
                  $all_data['discount_c']=$row[26];
                  $all_data['reg_date']=$row[27];

               }
               echo json_encode(array(
               "message" => "data retrived.",
               "status" => "200",
               "customer_profile" => $all_data
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
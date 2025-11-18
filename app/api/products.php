<?php
// required headers
header("Access-Control-Allow-Origin: https://cloudbillingmanager.com/api/products.php");
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
// $jwt=isset($_GET["jwt"]) ? $_GET["jwt"] : "";
// if jwt is not empty
// if($jwt){
    
    // if decode succeed, show user details
    // try {
        // decode jwt
        // $decoded = JWT::decode($jwt, $key, array('HS256'));
        // set user property values here
       
         //var_dump($c_id); exit; 
        $sql = "SELECT * FROM `geopos_products` WHERE qty>1";
        //  echo $sql; exit; 
        if ($result = mysqli_query($link, $sql)) {
            $i=0;
            
            if (mysqli_num_rows($result)){
                while ($row = mysqli_fetch_array($result)) {
                  $all_data[$i]['pid']=$row[0];
                  $all_data[$i]['pcat']=$row[1];
                  $all_data[$i]['warehouse']=$row[2];
                   $all_data[$i]['product_name']=$row[3];
                  $all_data[$i]['product_code']=$row[4];
                  $all_data[$i]['product_price']=$row[5];
                   $all_data[$i]['fproduct_price']=$row[6];
                  $all_data[$i]['taxrate']=$row[7];
                  $all_data[$i]['disrate']=$row[8];
                   $all_data[$i]['qty']=$row[9];
                  $all_data[$i]['product_des']=$row[10];
                  $all_data[$i]['alert']=$row[11];
                   $all_data[$i]['unit']=$row[12];
                  $all_data[$i]['image']=$row[13];
                  $all_data[$i]['barcode']=$row[14];
                   $all_data[$i]['merge']=$row[15];
                  $all_data[$i]['sub']=$row[16];
                  $all_data[$i]['vb']=$row[17];
                   $all_data[$i]['expiry']=$row[18];
                  $all_data[$i]['code_type']=$row[19];
                  $all_data[$i]['sub_id']=$row[20];
                   $all_data[$i]['b_id']=$row[21];
                 $all_data[$i]['product_image']="https://cloudbillingmanager.com/userfiles/company/16214317631903768300.png";
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
    // }
    // catch failed decoding will be here
    // if decode fails, it means jwt is invalid
    // catch (Exception $e){
    // // set response code
    // http_response_code(401);
    // // show error message
    // echo json_encode(array(
    //     "message" => "Access denied.",
    //     "error" => $e->getMessage()
    // ));
    // }
// }
// error message if jwt is empty will be here 
 ?>
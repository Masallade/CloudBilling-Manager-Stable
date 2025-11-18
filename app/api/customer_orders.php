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


$jwt=isset($_GET["jwt"]) ? $_GET["jwt"] : "";
$user_id=$_GET["user_id"];

 if($jwt){
    
    
   try {
       
         $decoded = JWT::decode($jwt, $key, array('HS256'));
          $c_id=$decoded->data->id;
       
        $sql ="SELECT * FROM `app_orders` WHERE user_id='$user_id'";
      //  echo  $sql;
        
        if ($result = mysqli_query($link, $sql)) {
            $i=0;
            if (mysqli_num_rows($result)){
                while ($row = mysqli_fetch_array($result)) {
                 $all_data[$i]['order_id']=$row[0];
                 
                  $all_data[$i]['total_amount']=$row[9];
                  $all_data[$i]['order_date']=$row[12];
                  $all_data[$i]['delivery_address']=$row[13];
                   $all_data[$i]['location']=$row[14];
                
                 
                  $i++;
                  
               }
           
                   echo json_encode( $all_data);
                }else{
                    echo json_encode(array(
                    "message" => "Record Not Found.",
                    "status" => "100",
                 ));
            }

        }
   }
   
    catch (Exception $e){
    
    http_response_code(401);
    
    echo json_encode(array(
        "message" => "Access denied.",
        "error" => $e->getMessage()
    ));
    }
}

 ?>
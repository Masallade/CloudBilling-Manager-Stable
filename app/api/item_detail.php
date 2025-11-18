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
$pid=$_GET["p_id"];

 if($jwt){
    
    
   try {
       
         $decoded = JWT::decode($jwt, $key, array('HS256'));
          $c_id=$decoded->data->id;
       
        $sql ="SELECT * FROM `order_item` WHERE p_id='$pid'";
      //  echo  $sql;
        
        if ($result = mysqli_query($link, $sql)) {
            $i=0;
            if (mysqli_num_rows($result)){
                while ($row = mysqli_fetch_array($result)) {
                  $all_data[$i]['id']=$row[0];
                 $all_data[$i]['price']=$row[2];
                 $all_data[$i]['name']=$row[3];
                  $all_data[$i]['qty']=$row[5];
                  $all_data[$i]['subtotal']=$row[6];
                   
                 
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
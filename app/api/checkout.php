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
$rawdata = file_get_contents("php://input");
$rawdata = json_decode($rawdata, true);
$jwt=isset($_GET["jwt"]) ? $_GET["jwt"] : "";
$user_id=$_GET["user_id"];
$count="";

if($jwt){
 try {
       
         $decoded = JWT::decode($jwt, $key, array('HS256'));
          $c_id=$decoded->data->id;
         
$sql="INSERT INTO app_orders (user_id) VALUES ('$user_id')";
$count++;
//echo $ql;
if (mysqli_query($link, $sql)) {
  $last_id = mysqli_insert_id($link);
  // die();
  }else {
  echo json_encode(array('messeage' => 'error.', 'status'=> false));
  die();
}
 foreach($rawdata as $row)
{
    $ePrice = stripslashes($row['Price']);
    $eName = stripcslashes($row['Name']);
    $etex = stripslashes($row['tex']); 
    $eqty = stripcslashes($row['qty']);
    $esubTotal = stripslashes($row['subTotal']);
    $slq="INSERT INTO order_item (p_id,price,Name,tex,qty,subTotal)VALUES ('$last_id','$ePrice', '$eName', '$etex','$eqty',' $esubTotal')";
    mysqli_query($link,$slq);
//die();
 }
if ($count>0){
// if ($link->query($sql) === TRUE) {
   echo json_encode(array('messeage' => 'New record created successfully.', 'status'=> true));   } else {
   echo json_encode(array('messeage' => 'no record found.', 'status'=> false));
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
<?php
header("Access-Control-Allow-Origin: https://cloudbillingmanager.com/api/login.php");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
require_once "./include/configdb.php";
include_once './include/core.php';
include_once './libs/php-jwt-master/src/BeforeValidException.php';
include_once './libs/php-jwt-master/src/ExpiredException.php';
include_once './libs/php-jwt-master/src/SignatureInvalidException.php';
include_once './libs/php-jwt-master/src/JWT.php';
use \Firebase\JWT\JWT;
    // $name = trim($_POST["user_name"]);
    // $password = trim($_POST["password"]);
    $data = json_decode(file_get_contents("php://input"));

    $email=$data->email;
    $password=$data->password;
    //echo "helo";

 $sql = "SELECT *  FROM users WHERE email ='$email'";
 
    if ($result = mysqli_query($link, $sql)) {
        if (mysqli_num_rows($result)==1){
            while ($row = mysqli_fetch_array($result)) {
                $hashed_password = $row['password'];
                $status = $row['status'];
                $email = $row['email'];
                $name = $row['name'];
                $user_id = $row['cid'];
            }
            
       
    // $sql2 = "SELECT count() FROM geopos_invoices WHERE csd = '19'";
    //     if ($result2 = mysqli_query($link, $sql2)) {
    //        $total_invoices=mysql_fetch_row($result2);
    //        print_r($total_invoices);die();
    //      }    
    $sql="SELECT * FROM geopos_invoices WHERE csd = '$user_id'";
if ($result=mysqli_query($link,$sql))
  {
  $rowcount=mysqli_num_rows($result);
  }

    $sql="SELECT geopos_customers.Balance  FROM geopos_customers WHERE id = '$user_id'";
    if ($result = mysqli_query($link, $sql)) {
        if (mysqli_num_rows($result)==1){
            while ($row = mysqli_fetch_array($result)) {
                $cust_balance = $row['Balance'];
            }
        }
    }
            if(password_verify($password, $hashed_password)){
                if($status=='active'){
	                // $response["status"] = 200;
                    // $response["message"] = 'logged in';
                    $token = array(
                        "iat" => $issued_at,
                        "exp" => $expiration_time,
                        "iss" => $issuer,
                        "data" => array(
                        "id" => $user_id,
                        "name" =>  $name,
                        "email" =>  $email
                        )
                    );
                // set response code
                http_response_code(200);
                // generate jwt
                $jwt = JWT::encode($token, $key);
                settype($rowcount, "string");
                settype($cust_balance, "string");
              settype($jwt, "string"); 
                // $rowcount=preg_replace("/<!--.*?-->/", "", $rowcount);
                // $cust_balance=preg_replace("/<!--.*?-->/", "", $cust_balance);
                $res= array(
                 "status" => "200",
                 "message" => "Successful login.",
                 "jwt" => $jwt,
                "total_invoices"=>$rowcount,
                "cust_balance"=>$cust_balance,
                'uid' => $user_id,
                'name' => $name
                );
          echo json_encode($res);
                // echo json_encode($response,JSON_UNESCAPED_SLASHES);
                }
                else{
                    $response["status"] = 100;
                    $response["message"] ="Your Account Has Been Blocked.";
                    echo json_encode($response,JSON_UNESCAPED_SLASHES);
                }
                }
                else{
                    $response["status"] = 100;
                    $response["message"] = $password_err = "Password you entered was not valid.";
                    echo json_encode($response,JSON_UNESCAPED_SLASHES);
                }
                }
                 else{
                    $response["status"] = 100;
                    $response["message"] = $email_err = "No account found with this user name.";
                    echo json_encode($response,JSON_UNESCAPED_SLASHES);
                }
        }
?>
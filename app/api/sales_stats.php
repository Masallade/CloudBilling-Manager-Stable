<?php
// header("Access-Control-Allow-Origin: https://cloudbillingmanager.com/api/login.php");
// header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
require_once "./include/configdb.php";
  $today = date("Y-m-d");
 $month = date("m");
 $year = date("Y");
 $tomorrow = date("Y-m-d",strtotime("+1 day")); 



 ?>
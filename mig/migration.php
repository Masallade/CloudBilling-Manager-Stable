<?php   
 
//require_once(__DIR__.'/create_customer.php');
error_reporting(E_ALL);
ini_set('display_errors', '1');
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fpos";

$servernamec = "localhost";
$usernamec = "cloudbilling_primefooduk";
$passwordc = "Pakistan1!";
$dbnamec = "cloudbilling_primefooduk2";

// Create connection
$cloud_link = mysqli_connect($servernamec, $usernamec, $passwordc, $dbnamec);
if(mysqli_connect_errno()){
}

/**$local_link = mysqli_connect($servername, $username, $password, $dbname);
if(mysqli_connect_errno()){
} */

$sql1 = "SELECT id,payer,payerid FROM `geopos_transactions` group by payer";
//$sql1 = "SELECT id,payer,payerid FROM `geopos_transactions` where payer='PERI021'";
$result1 = mysqli_query($cloud_link, $sql1);

while($row = mysqli_fetch_assoc($result1)) { 

$balance=0;

$name = $row['payer'];
$sql2 = "SELECT * FROM `geopos_transactions` where payer='$name' order by date asc";
$result2 = mysqli_query($cloud_link, $sql2);

while($row2 = mysqli_fetch_assoc($result2)) {
    
    $balance += $row2['credit'] - $row2['debit'];

	$iid = $row2['id'];

    $query ="UPDATE `geopos_transactions` SET balance = '$balance' WHERE id = $iid";	

    $res = mysqli_query($cloud_link, $query); 
    
    
}
    $query2 ="UPDATE `geopos_customers` SET balance = '$balance' WHERE name = '$name' ";	

    $res = mysqli_query($cloud_link, $query2);
}
/*
 $tid=9261;
$sql2 = "SELECT id,tid, inv_type from `geopos_invoices` where invoicedate='2021-09-27' and inv_type='INVOICE' ORDER by `tid` ASC";
$result2 = mysqli_query($cloud_link, $sql2);

while($row2 = mysqli_fetch_assoc($result2)) {
     //$tid=$row2['tid'];
     
     $id=$row2['id'];
    
   

    $tid = $tid+1;
   
    $query ="UPDATE `geopos_invoices` SET tid = '$tid' where id = '$id'";

 // echo $query; 
  //   die();
    $res = mysqli_query($cloud_link, $query);

}*/



 ?>
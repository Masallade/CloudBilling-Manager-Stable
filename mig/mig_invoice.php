<?php   
 
//require_once(__DIR__.'/create_customer.php');
error_reporting(E_ALL);
ini_set('display_errors', '1');
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fpos";

$servernamec = "localhost";
$usernamec = "cloudbilling_starfoods";
$passwordc = "KBec44jHLiNFr";
$dbnamec = "cloudbilling_starfoods";

// Create connection
$cloud_link = mysqli_connect($servernamec, $usernamec, $passwordc, $dbnamec);

if(mysqli_connect_errno()){
}

/**$local_link = mysqli_connect($servername, $username, $password, $dbname);
if(mysqli_connect_errno()){
}*/


$sql2 = "SELECT id,name,address FROM geopos_customers";
$result2 = mysqli_query($cloud_link, $sql2);
$k =0;
while($row2 = mysqli_fetch_assoc($result2)) {
	echo 'Iteration Goes #'. $k. '<br>';
	$k++; 
    /**$csd = $row['id'];
	$incre = $row['tid'];
	$eid = '13';
	$tid = $incre + $k;
	$inv_type = 'INVOICE';
	$date_old = '2021-07-10';
	$invoicedate = date ('Y-m-d', strtotime($date_old));
	$invoiceduedate = date ('Y-m-d', strtotime($date_old));
	$subtotal = $row['balance'];
	$ship_tax_type = 'incl';
	$total = $row['balance'];
	$pamnt = '0.00';
	$status = 'due';
	$taxstatus = 'no';
	$format_discount = '%';*/
	$payer_id = $row2['id'];
    $name = $row2['name'];
	$cst_address = $row2['address'];
    if(empty($cst_address)){
        $cst_address = 'N/A';
    }
	
$query ="UPDATE geopos_invoices SET csd = $payer_id, cust_address = '$cst_address' WHERE cust_name= '$name' ";	


//echo $query. '<br>'; exit;

//exit;
$res = mysqli_query($cloud_link, $query);
//$k++;
}


 ?>
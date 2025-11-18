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


$sql2 = "SELECT sum(total) as total_balance,csd FROM `geopos_invoices` GROUP BY csd";
$result2 = mysqli_query($cloud_link, $sql2);
$k =0;
while($row2 = mysqli_fetch_assoc($result2)) {
	
	/**$k++; 
    $csd = $row['id'];
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
	$payer_id = $row2['total_balance'];
	$cst_address = $row2['csd'];
	
$query ="UPDATE `geopos_customers` SET balance = $payer_id WHERE id = '$cst_address'";	

$res = mysqli_query($cloud_link, $query);
}


 ?>
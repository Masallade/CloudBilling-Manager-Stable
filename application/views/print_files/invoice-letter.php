<html>
<head>
<style>
body{ font-size:12px;}
@page { margin-top: 320px;margin-left: 80px;  margin-right: 80px;}
#header{ position: fixed;  top: -280px;}
#footer{ position: fixed;  bottom: 80px;}
.left_header{float:left;margin-left:-10px}
.center_header{float:left;text-align:center;width:450px;color:rgb(48, 37, 133);margin-left:-40px}
.right_header{float:left;width:150px}
table{width:100%;}
tr td th{padding: 3px;}
 .maintable { 
    border: 1px solid #1367A4;
    border-collapse: separate;
    border-left: 0;
    border-radius: 4px;
    border-spacing: 0px;
}

.maintable thead {
    display: table-header-group;
    vertical-align: middle;
    border-color: inherit;
    border-collapse: separate;
    background-color:lightgrey;
}
.maintable tr {
    display: table-row;
    vertical-align: inherit;
   
}
.maintable th{
    padding: 5px 4px 6px 4px; 
    text-align: left;
    vertical-align: top;
    border-left: 1px solid #1367A4; 
    border-bottom: 1px solid #1367A4;
}
.maintable td {
    padding: 5px 4px 6px 4px; 
    text-align: left;
    vertical-align: top;
    border-left: 1px solid  #1367A4; 
    border-bottom:none;
     border-top: none;  
}
.maintable thead:first-child tr:first-child th:first-child, tbody:first-child tr:first-child td:first-child {
    border-radius: 4px 0 0 0;
}
.maintable thead:last-child tr:last-child th:first-child, tbody:last-child tr:last-child td:first-child {
    border-radius: 0 0 0 4px;
}


    .maintable2 { 
    border: 1px solid #1367A4;
    border-collapse: separate;
    border-left: 0;
    border-radius: 4px;
    border-spacing: 0px;
            border: 1px solid #1367A4;
}
.maintable2 thead {
    display: table-header-group;
    vertical-align: middle;
    border-color: inherit;
    border-collapse: separate;
    background: lightgrey;
    
}
.maintable2 tr {
     
    display: table-row;
    vertical-align: inherit;
    border-color: inherit;
}
.maintable2 th {
    padding: 5px 4px 6px 4px; 
    text-align: left;
    vertical-align: top;
    border: 1px solid #1367A4;
    border-bottom: none;
    border-right: none;
   
    
}
.maintable2 td {
    border-left: none;
    border-top: 1px solid #1367A4;
    border-bottom: none;
    border-right: none;
    
}

</style>
</head>
<body>
<div id='header'>
<div class='left_header'>
    <img src="<?= $left_logo; ?>" height='160px' width='170px'>
</div>

<div class='center_header' style='font-family: 'Times New Roman', Times, serif;'>
    <div style=' font-size: 45px;'><b>Pearl Food UK</b></div>
    <div style=' font-size: 15px;'>
    <div>Unit 33, Middlemore Road</div>
    <div>Middlemore Industrial Estate, Smethwick B66 2EP</div>
    <div>Tel: 01215581177 - Mob:07957 475 887 , 07900 940 225</div>
    <div>Email: contact@pearlfoods.co.uk</div>
    </div>
</div>
<div class='right_header'>
    <img src="<?= $right_logo; ?>" height='80px' width='80px'>
</div>
    <div style='clear:both'></div>
<div style='border:1px solid #1367A4;width:50%;height:100px; padding: 8px 10px 8px; border-radius: 6px;float:left;margin-top:5px'> <br>
<?php 
                if ($invoice['company']) echo $invoice['company'] . '<br>';

                echo $invoice['address'] . '<br>' . $invoice['city'] . ', ' . $invoice['region'];
                if ($invoice['country']) echo '<br>' . $invoice['country'];
                if ($invoice['postbox']) echo ' <br> ' . $invoice['postbox'];
                if ($invoice['phone']) //echo '<br>' . $this->lang->line('Phone') . ': ' . $invoice['phone'];
                if ($invoice['email']) echo '<br> ' . $this->lang->line('Email') . ': ' . $invoice['email'];

                if ($invoice['taxid']) echo '<br>' . $this->lang->line('Tax') . ' ID: ' . $invoice['taxid'];
               
                ?> </div>
<div style='float:right;margin-left:350px;margin-top:-30px;width:250px' >
<div style=' font-size: 25px;'><b>&nbsp;Invoice</b></div>
<table class='maintable2' style='width:100%; font-size: 15px;'>
  <tr>
    <td style='width: 50%; background: lightgrey;padding: 8px 0px 8px 7px;'>&nbsp;Invoice No.</td>
       <td> <?= $invoice['tid'] ?> </td>
  </tr>
        
        <tr >
    <td style='width: 50%; background: lightgrey;padding:  8px 0px 8px 7px;'>&nbsp;Invoice/Tax Date</td>
       <td> <?php echo dateformat($invoice['invoicedate']) ?></td>
      
  </tr>
    <tr >
    <td style='width: 50%; background: lightgrey;padding: 8px 0px 8px 7px;'>&nbsp;Account No.</td>
       <td><?php echo $invoice['name'] ?></td>
  </tr>  
</table>
<div>&nbsp;VAT Reg No: 375 6160 81</div>
</div>
</div>

<div style='clear:both'></div>
<br>
<table class='maintable'  >
<thead>
  <tr style='font-size: 15px;'>
    <th style='width: 40%; '>Details</th>
    <th style='width: 15%; '>Quantity</th>
    <th style='width: 15%; '>Unit Price</th>
      <th style='width: 20%; '>Net Amount</th>
      <th style='width: 10%; '>VAT</th>
  </tr>
  </thead>

   <?php for($k =0; $k<=10; $k++){ 
        $fill = true;
        $sub_t = 0;
        $sub_t_col = 3;
        $n = 1; $ns = 0;
        foreach ($products as $row) { $ns++;
		
            $cols = 4;
            if ($fill == true) {
                $flag = ' mfill';
            } else {
                $flag = '';
            }
            $sub_t += $row['price'] * $row['qty'];
				$up = 	amountExchange($row['price'], $invoice['multi'], $invoice['loc']);
				$vat = amountExchange($row['totaltax'], $invoice['multi'], $invoice['loc']);
				$netamount = amountExchange($row['subtotal'], $invoice['multi'], $invoice['loc']);
				$ds = amountExchange($row['totaldiscount'], $invoice['multi'], $invoice['loc']);
            if ($row['serial']) $row['product_des'] .= ' - ' . $row['serial'];
			
			
            echo '<tr> 
			<td>' . $row['product_des'] . '</td>
			<td>' . $row['qty'] . $row['unit'] . '</td>  
			<td>' . substr($up,3,100). '</td> ';
            echo '<td>' . substr($netamount,3,100) . '</td>';

                echo '<td>' . substr($vat,3,100) . ' </td>';
 
           echo '</tr>';

	
	
		} ?>
 <?php } ?>
 
</table>

<div id="footer">

<div style='border:1px solid #1367A4;width:50%;padding: 8px 0px 5px 4px; border-radius: 6px;float:left;margin-top:5px'>
    <div style='color:rgb(48, 37, 133)'><b>*MAXIMUM CHANGE ACCEPTED=&#163;60</b></div>
    <div style=' font-size: 10px;'>All goods remain the property of Pearl Food UK until all monies are paid in full<br>Any complaints should be made within 48 hours of purchasing.</div>
<br>
<div style='border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left'>
Print Name:
</div>

<div style='border:1px solid black;width:10%;height:18px;float:left;margin-left:190px'></div>
<div style='float:left;margin-left:230px;margin-top:3px;font-size: 12px;'><b>Cash Tendered</b></div>
<div style='clear:both'></div>
<div style='height:31px'></div>
<div style='border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left;'>
Signature:
</div>
<div style='font-size: 17px;padding-left:10px;border:1px solid black;width:35%;height:20px;float:left;padding-top:3px;padding-bottom:3px;margin-left:190px;margin-top:-8px'>
&#163;
</div>
<div style='clear:both'></div>
</div>

<div style='float:right;margin-left:350px;width:250px' >
<table class='maintable2' style='width:100%; font-size: 15px;'>
  <tr >
    <td style='width: 50%; font-weight: bold; background: lightgrey;padding: 4px 0px 4px 7px;'>&nbsp;Previous Balance</td>
       <td><?= $invoice['balance'] ?></td>
  </tr>
        
        <tr >
    <td style='width: 60%; font-weight: bold; background: lightgrey;padding:  4px 0px 4px 7px;'>&nbsp;Total NET Amount</td>
       <td> <?php echo $sub_t; ?></td>
      
  </tr>
    <tr >
    <td style='width: 60%; font-weight: bold; background: lightgrey;padding: 4px 0px 4px 7px;'>&nbsp;Total VAT Amount</td>
       <td> <?php echo $invoice['tax']; ?> </td>
    </tr>
    <tr>
    <td style='width: 60%; font-weight: bold; background: lightgrey;padding: 4px 0px 4px 7px;'>&nbsp;Invoice Total</td>
       <td> <?php echo $invoice['total']; ?>  </td>
    </tr>
    <tr>
     <td style='width: 60%; font-weight: bold; background: lightgrey;padding:  4px 0px 4px 7px;'>&nbsp;Balance Now Due</td>
       <td>  <?php echo ($invoice['balance'] + $invoice['total']); ?>  </td>
    </tr> 
</table>
</div>
  
</div>
</body>
</html>
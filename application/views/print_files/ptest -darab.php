<html>
<head>
<style>

table.myProducts tr td{
border-left: 1px dotted #1367A4;}
</style>
 </head>
<body>
<header>
<?php if ($invoice['inv_type'] == 'INVOICE' && $invoice['company'] != 'COUNTER SALE' ){ ?>
<div style="width:100%; ">

    <div style="width:23%; float:left; text-align:center;">  <img src="<?= $left_logo; ?>" height='160px' width='170px'> </div>
    
    <div style="width:48%; margin-left:20px; float:left; text-align:center;">  
        <div style="font-size: 40px;"><b>Pearl Food UK</b></div>
        <div style="font-size: 14px;">
            <div>Unit 33, Middlemore Road</div>
            <div>Middlemore Industrial Estate, Smethwick B66 2EP</div>
            <div>Tel: 01215581177 - Mob:07957 475 887 , 07900 940 225</div>
            <div>Email: contact@pearlfoods.co.uk</div>
        </div> 
    </div>

    <div style="width:25%; float:left; text-align:center;">  <img src="<?= $right_logo; ?>" height='100px' width='100px'> </div>
    
</div>

<div style='clear:both'></div>
<?php } else{ ?> 

<br><br> 
<p style="text-align:center;">Note: This is not a valid Invoice  </p>
<br><br>

<?php }?>
<div style='border:1px solid #1367A4;width:50%;height:100px; padding: 8px 10px 8px; border-radius: 6px;float:left;margin-top:5px'> 
<?php 

                if ($invoice['company']) echo $invoice['cust_name'] . '<br>';
				
  if ($invoice['inv_type'] == 'INVOICE' && $invoice['company'] != 'COUNTER SALE' ){ 
                echo $invoice['cust_address'];
                if ($invoice['country']) echo '<br>' . $invoice['country'];
  }		
                if ($invoice['postbox']) echo ' <br> ' . $invoice['postbox'];
                if ($invoice['phone']) //echo '<br>' . $this->lang->line('Phone') . ': ' . $invoice['phone'];
                if ($invoice['email']) echo '<br> ' . $this->lang->line('Email') . ': ' . $invoice['email'];

               // if ($invoice['taxid']) echo '<br>' . $this->lang->line('Tax') . ' ID: ' . $invoice['taxid'];
               
?> </div><?php ?>
<div style='float:right;margin-left:350px;margin-top:-30px;width:250px' >
<div style=' font-size: 25px;'><?php if ($invoice['inv_type'] == 'INVOICE' && $invoice['company'] != 'COUNTER SALE' ){ ?>
<b>&nbsp;Invoice</b> <?php }else{?> &nbsp;  <?php } ?></div>
<table style='width:100%; font-size: 14px; border:1px solid #1367A4;'>
  <tr>
    <td style='width: 50%; border-bottom:1px solid #1367A4;background: lightgrey;padding: 8px 0px 8px 7px;'>&nbsp;Invoice No.</td>
       <td  style="border-bottom:1px solid #1367A4;"> <?php if ($invoice['inv_type'] == 'INVOICE' && $invoice['company'] != 'COUNTER SALE' ){ ?>
<?= $invoice['tid'] ?> <?php }else{?> Day Pass <?php } ?> </td>
  </tr>
        
        <tr>
    <td style='width: 50%; border-bottom:1px solid #1367A4;background: lightgrey;padding:  8px 0px 8px 7px;'>&nbsp;Invoice Date</td>
       <td style="border-bottom:1px solid #1367A4;"> <?php echo dateformat($invoice['invoicedate']) ?></td>
      
  </tr>
    <tr>
    <td style='width: 50%; background: lightgrey;padding: 8px 0px 8px 7px;'>&nbsp;Account No.</td>
       <td><?php echo $invoice['name'] ?></td>
  </tr>  
</table>
<div><?php if ($invoice['inv_type'] == 'INVOICE' && $invoice['company'] != 'COUNTER SALE' ){ ?> &nbsp;VAT Reg No: 375 6160 81 <?php } ?> </div>
</div>

</header>

<div style="clear:both"></div>

<div style="width:100%; margin-top:10px; height:530px; ">

<table width="100%" class="myProducts" style="border:1px solid #1367A4;">
<thead>
  <tr style='font-size: 14px; background:#ccc; padding:4px;'>
    <th style='width: 45%; text-align:left;'>Details</th>
    <th style='width: 12%; text-align:left; border-left: 1px solid #1367A4;'>Quantity</th>
    <th style='width: 13%; text-align:left; border-left: 1px solid #1367A4;'>Unit Price</th>
      <th style='width: 20%; text-align:left; border-left: 1px solid #1367A4;'>Net Amount</th>
      <th style='width: 10%;  text-align:left;border-left: 1px solid #1367A4;'>VAT</th>
  </tr>
 </thead>
<tbody style="font-size:12px;">
   <?php $ns = 0; //for($k =0; $k<=30; $k++){ 
      
        $sub_t = 0;
        
        foreach ($products as $row) { $ns++;
		
         
            $sub_t += $row['price'] * $row['qty'];
				$up = 	amountExchange($row['price'], $invoice['multi'], $invoice['loc']);
				$vat = amountExchange($row['totaltax'], $invoice['multi'], $invoice['loc']);
				$netamount = amountExchange($row['subtotal'], $invoice['multi'], $invoice['loc']);
			
			if(empty($row['product_des']) || $row['product_des'] == ""){ ?> <?php }else{
         ?>
			
           <tr <?php if($row['serial'] == '1'){ ?> style="background:#FBF36D;" <?php } ?>> <?php 
			 echo '<td>' . $row['product_des'] . '</td>
			<td>' . $row['qty'] . $row['unit'] . '</td>  
			<td>' . substr($up,3,100). '</td> ';
            echo '<td>' . substr($netamount,3,100) . '</td>';

                echo '<td>' . substr($vat,3,100) . ' </td>';
 
           echo '</tr>';

            }
	
		} ?>
 <?php //} ?>
 </tbody>
</table>
</div>


 <div style="margin-top: 5px;">

<?php if ($invoice['inv_type'] == 'INVOICE' && $invoice['company'] != 'COUNTER SALE' ){ ?>
<div style='border:1px solid #1367A4;width:50%;height:140px; padding: 8px 0px 5px 4px; border-radius: 6px;float:left;margin-top:5px'>

    <div style='color:rgb(48, 37, 133)'><b>*MAXIMUM CHANGE ACCEPTED=&#163;60</b></div>

    <div style=' font-size: 10px;'>All goods remain the property of Pearl Food UK until all monies are paid in full<br>Any complaints should be made within 48 hours of purchasing.</div>

<br>
<div style="width:100%">

<div style='border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left'>
Print Name:
</div>

<div style='border:1px solid black;width:10%;height:18px;float:left;margin-left:10px'>
</div>

<div style='float:left;margin-left:8px;margin-top:3px;font-size: 12px;'><b>Cash Tendered</b></div>

<div style='clear:both'></div>

<div style='border:1px solid black;width:50%;height:18px; padding: 0px 0px 11px 4px;float:left;'>
Signature:</div>
<div style='font-size: 17px;padding-left:10px;border:1px solid black;width:35%;height:20px;float:left;padding-top:3px;padding-bottom:3px;margin-left:10px;margin-top:-8px'>&#163;</div>


<div style='clear:both'></div>

</div>
 
<div style='clear:both'></div>

</div>
<?php } ?>

  <?php $sub_ts =0; foreach ($products as $row) { 
		
         
            $sub_ts += $row['price'] * $row['qty']; } ?>
<div style='float:right;margin-left:320px;width:280px' >
<table class='maintable2' style='width:100%; border:1px solid #1367A4;font-size: 14px;'>
  <?php if ($invoice['inv_type'] == 'INVOICE' && $invoice['company'] != 'COUNTER SALE' ){ ?> <tr >
    <td style='width: 50%;  background: lightgrey;padding: 4px 0px 4px 7px;'>&nbsp;Previous Balance</td>
       <td><?php $invbalance = 0 ; 
       if($invoice['balance'] >= $invoice['total']){ 
           $invbalance =  $invoice['balance'] - $invoice['total']; 
           echo   substr( amountExchange($invbalance, $invoice['multi'], $invoice['loc']),3,100);  
           }else{ 
               echo substr(amountExchange($invoice['balance'], $invoice['multi'], $invoice['loc']),3,100);    } ?></td>
  </tr> <?php } ?>
        
        <tr>
    <td style='width: 60%;  background: lightgrey;padding:  4px 0px 4px 7px;'>&nbsp;Total NET Amount</td>
       <td> <?php echo $sub_ts; ?></td>
      
  </tr>
    <tr >
    <td style='width: 60%;  background: lightgrey;padding: 4px 0px 4px 7px;'>&nbsp;Total VAT Amount</td>
       <td> <?php echo $invoice['tax']; ?> </td>
    </tr>
    <tr>
    <td style='width: 60%;  background: lightgrey;padding: 4px 0px 4px 7px;'>&nbsp;Invoice Total</td>
       <td> <?php echo $invoice['total']; ?>  </td>
    </tr>
    <?php if ($invoice['inv_type'] == 'INVOICE' && $invoice['company'] != 'COUNTER SALE' ){ ?>

    <tr>
     <td style='width: 60%; background: lightgrey;padding:  4px 0px 4px 7px;'>&nbsp;Balance Now Due</td>
       <td>  <?php echo ($invoice['balance']); ?>  </td>
    </tr>  <?php } ?>
</table>
</div>

<div style="clear:both"></div>
<?php if ($invoice['inv_type'] == 'INVOICE' && $invoice['company'] != 'COUNTER SALE' ){ ?>
<p style="font-size:8px"> Pearl Food UK is the trading name of Prime Food Distribution Ltd, Company No. 11671004. &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Bank Account Details: Sort Code: 40-09-06 A/C No: 92007894</p> <?php } ?>
  </div>
  
</body>
</html>
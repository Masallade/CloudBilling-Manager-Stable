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

            $sql2='SELECT sum(total)-sum(pamnt) as "cust_balance" FROM geopos_invoices WHERE status="due" or status="partial"';    
                                          $response = mysqli_query($link, $sql2);
                                         $row = mysqli_fetch_array($response);
                                        $cust_balance= $row['cust_balance'];
                                        
            $sql='SELECT sum(total) as "total" FROM geopos_invoices WHERE DATE(invoicedate) ="'.$today.'"';   
                     
                                          $response = mysqli_query($link, $sql);
                                         $row = mysqli_fetch_array($response);
                                        $today_sales= $row['total'];
                    
                                        
            $sql='SELECT sum(total) as "total" FROM geopos_invoices WHERE DATE(invoicedate) ="'.$tomorrow.'"';                       
                                          $response = mysqli_query($link, $sql);
                                         $row = mysqli_fetch_array($response);
                                        $tomorrow_sales= $row['total'];         
                                                           
            $sql='SELECT count(id) as "total_invoices" FROM geopos_invoices WHERE DATE(invoicedate) ="'.$today.'"';                       
                                          $response = mysqli_query($link, $sql);
                                         $row = mysqli_fetch_array($response);
                                        $today_invoices= $row['total_invoices'];  

            $sql='SELECT sum(id) as "tomorrow_invoices" FROM geopos_invoices WHERE DATE(invoicedate) ="'.$tomorrow.'"';                        
                                          $response = mysqli_query($link, $sql);
                                         $row = mysqli_fetch_array($response);
                                        $tomorrow_invoices= $row['tomorrow_invoices']; 
                                   $today = date('Y-m-d');
                                   $days=date("t", strtotime($today));
                                   $where = "invoicedate BETWEEN '$year-$month-01' AND '$year-$month-$days'";
            $sql='SELECT * FROM geopos_invoices WHERE '.$where.'';                              
                                          $response = mysqli_query($link, $sql);                                     
                                         $rowcount=mysqli_num_rows($response);       

            $sql="SELECT COUNT(id) AS ttlid,SUM(total) AS total,DATE(invoicedate) as date FROM geopos_invoices where (DATE(invoicedate) BETWEEN '".$today."' -                             INTERVAL 30 DAY AND '".$today."') GROUP BY DATE(invoicedate) ORDER BY date DESC";  
            $result = mysqli_query($link, $sql);
                   $i=0;
                while ($row = mysqli_fetch_array($result)) {
                 $chart_data[$i]['id']=(int)$row[0];
                 $chart_data[$i]['total']=(int)$row[1];
                 $chart_data[$i]['date']=$row[2];
                 $i++;
               }   
                              
            
            $sql="SELECT account,debit,credit,paymt_method,geopos_transactions.created FROM geopos_transactions inner join geopos_invoices on geopos_transactions.tid=geopos_invoices.id
                      ORDER by geopos_transactions.id DESC LIMIT 13";     
                                           
                                          $response = mysqli_query($link, $sql);  
                                           $i=0;                                                                          
                                      while ($row = mysqli_fetch_array($response)) {
                                                  $recent_trans_data[$i]['account']=$row[0];
                                                  $recent_trans_data[$i]['debit']=$row[1];
                                                  $recent_trans_data[$i]['credit']=$row[2];
                                                  $recent_trans_data[$i]['paymt_method']=$row[3];
                                                  $recent_trans_data[$i]['date']=$row[4];
                                                            $i++;
                                                                          }

            $sql="SELECT i.id,i.tid,i.invoicedate,i.total,i.status,i.i_class,c.name,c.picture,i.csd
FROM geopos_invoices AS i LEFT JOIN geopos_customers AS c ON i.csd=c.id WHERE (i.inv_type != 'DAYPASS') ORDER BY i.id DESC LIMIT 10";     
                                           
                                          $response = mysqli_query($link, $sql);  
                                           $i=0;                                                                          
                                      while ($row = mysqli_fetch_array($response)) {
                                                  $recent_invoice_data[$i]['id']=$row[0];
                                                  $recent_invoice_data[$i]['tid']=$row[1];
                                                  $recent_invoice_data[$i]['invoicedate']=$row[2];
                                                  $recent_invoice_data[$i]['total']=$row[3];
                                                  $recent_invoice_data[$i]['status']=$row[4];
                                                   $recent_invoice_data[$i]['i_class']=$row[5];
                                                  $recent_invoice_data[$i]['name']=$row[6];
                                                  $recent_invoice_data[$i]['picture']=$row[7];
                                                  $recent_invoice_data[$i]['csd']=$row[8];
                                                            $i++;
                                                                          }

            $sql="SELECT geopos_products.*,geopos_warehouse.title FROM geopos_products LEFT JOIN geopos_warehouse ON geopos_products.warehouse=geopos_warehouse.id  WHERE (geopos_products.qty<=geopos_products.alert)  ORDER BY geopos_products.product_name ASC LIMIT 10";     

                                          $response = mysqli_query($link, $sql);  
                                    
                                           $i=0;                                                                          
                                      while ($row = mysqli_fetch_array($response)) {
                                                  $stock_alert_data[$i]['product_name']=$row[3];
                                                  $stock_alert_data[$i]['qty']=$row[9];
                                                  $stock_alert_data[$i]['title']=$row[23];
                                                            $i++;
                                                                          }


     
            // $sql="select id, tax, total from geopos_invoices where invoicedate ='".$today."'";
            //       $response = mysqli_query($link, $sql); 
            //         $row = mysqli_fetch_array($response);
            //         var_dump($row);exit();
        
//         $invoiceids=array();
//         foreach($todayinvoices as $todayinvoice){
//             array_push($invoiceids,$todayinvoice['id']);
//         }
//         $invoiceids = implode(', ', $invoiceids);
//         if(empty($invoiceids)){
//             return 0;
//         }
//         $sql="select pid,qty,price,totaltax,subtotal from geopos_invoice_items where tid IN  (".$invoiceids.")";

//         $query = $this->db->query($sql);
//         $ivoice_items=$query->result_array();
       
//         // print_r();die(); 
// $invoice_items_total=0;
// $products_price_total=0;
//  foreach($ivoice_items as $ivoice_item){
//            $sql="select product_price,fproduct_price,product_name from geopos_products where pid =".$ivoice_item['pid']."";
//            $query = $this->db->query($sql);
//            $product=$query->result_array();
//            $invoice_items_total+=$ivoice_item['price']*$ivoice_item['qty'];
//            $products_price_total+=$product[0]['product_price']*$ivoice_item['qty'];
//         }
// return $invoice_items_total-$products_price_total;
       
//     }
            

                                    echo json_encode(
                                            array(
                                            'customer_balance'=>$cust_balance,
                                            'today_sales'=>$today_sales,
                                            'tomorrow_sales'=>$tomorrow_sales,
                                            'today_invoices'=>$today_invoices,
                                            'tomorrow_sales'=>$tomorrow_sales,
                                            'today_invoices'=>$today_invoices,
                                            'tomorrow_invoices'=>$tomorrow_invoices,
                                            'this_month_invoices'=>$rowcount,
                                            'chart'=>$chart_data,
                                            'recent_trans_data'=>$recent_trans_data,
                                            'recent_invoice_data'=>$recent_invoice_data,
                                            'stock_alert_data'=>$stock_alert_data

                                            )

                                    );
                                         
                                 
                    
                
             
             
              
        
?>
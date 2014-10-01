<?php
require( dirname( dirname( dirname( dirname(__FILE__ )))). '/config.php' );  
require( dirname( dirname( dirname( dirname(__FILE__ )))). '/system/library/currency.php');  

$link = mysql_connect(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, true);
mysql_select_db(DB_DATABASE, $link);

		date_default_timezone_set(TIMEZONE);
      
       if(isset($_GET['amount']) && $_GET['auction'])
       {        
               $date = date('Y-m-d H:i:s');
               $sql="SELECT MAX(user_bid) id FROM " . DB_PREFIX . "wkauctionbids WHERE auction_id = '" . (int)$_GET['auction'] . "'";
               $res=mysql_query($sql,$link);
               $bid_id= mysql_fetch_array($res);

               if(count($bid_id)!=0 && $_GET['amount']<=$bid_id['id']){
                  echo 'not';
               }
               else{
               $query="INSERT INTO " . DB_PREFIX . "wkauctionbids SET winner='0',sold='0',auction_id = '" . (int)$_GET['auction']. "', user_id = '" .(int)$_GET['user']."', product_id = '" .(int)$_GET['product_id']."', start_date = '" .$_GET['start_date']. "', end_date = '" .$_GET['end_date']."', date = '" .$date."', user_bid = '" .(int)$_GET['amount']."'";
              
              // $query2="Insert into " . DB_PREFIX . "wkauctionbids values('".$_POST['product_id']."','".$_POST['EXTENSION']."','".$_POST['AREA_COUNT']."','$axis')";
               mysql_query($query, $link);
               echo 'done';
              }
       }


      else if(isset($_GET['auction']) && $_GET['bids'])
       {        
         $left=$_GET['left'];
         $right=$_GET['right'];
         $value=$_GET['value'];
         $text="";
         $data ="SELECT * FROM " . DB_PREFIX . "wkauctionbids WHERE auction_id = '" . (int)$_GET['auction'] . "' ORDER BY user_bid DESC";
         $result=mysql_query($data,$link);
        while($row = mysql_fetch_array($result))
           {
          $sql="SELECT * FROM ".DB_PREFIX ."customer WHERE customer_id='" . (int)$row['user_id'] . "'";
          $res=mysql_query($sql,$link);
          $customer= mysql_fetch_array($res);
          $ubi=$row['user_bid']*$value;
          $ubi=round($ubi, 2);
          $ubid="";
          $first=$customer['firstname'];
          $last=$customer['lastname'];
          if($left)
          {
          $ubid=$left.$ubi;
           }
           else{
          $ubid=$ubi.$right;
           }
          
         
          $text=$text."<div class=\"bids\" title=\"".$first.' '.$last."\">$ubid , Bid by ".substr($first.' '.$last,0,10)."</div>";
         
         }
        
        echo $text;
   
       }

     

    mysql_close($link);
 ?>
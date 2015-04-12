<?php
require( dirname( dirname( dirname( dirname(__FILE__ )))). '/config.php' );  

$link = mysql_connect(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, true);
   mysql_select_db(DB_DATABASE, $link);

	  if(isset($_GET['auction_id']))
       {     $auction_id=$_GET['auction_id'];
             $bids=$this->getDb()->query("SELECT MAX(user_bid) id FROM " . DB_PREFIX . "wkauctionbids WHERE auction_id = '" . (int)$auction_id. "'");
             $bid_id=$bids->row;

             $this->getDb()->query("UPDATE " . DB_PREFIX . "wkauction SET isauction=0 WHERE id='" . (int)$auction_id . "'");
             $this->getDb()->query("UPDATE " . DB_PREFIX . "wkauctionbids SET winner=1 WHERE user_bid='" . (int)$bid_id['id'] . "'");
            // $this->getDb()->query("UPDATE " . DB_PREFIX . "wkauctionbids SET winner=1 WHERE user_bid='" . (int)$bid_id['id'] . "'");
       }


    mysql_close($link);
 ?>
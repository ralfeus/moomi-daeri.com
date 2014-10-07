<?php
################################################################################################
#  Product auction for Opencart 1.5.1.x From webkul http://webkul.com  	  	       #
################################################################################################
class ModelModuleWkproductauction extends Model {
	
	public function createEventTable() 
	{
		$sql="CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "wkauction(id INT PRIMARY KEY AUTO_INCREMENT, product_id integer,name varchar(50), isauction varchar(5), min varchar(25),max varchar(25),start_date varchar(30),end_date varchar(30))";
		$this->db->query($sql);
		$sql="CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "wkauctionbids(id INT PRIMARY KEY AUTO_INCREMENT, product_id integer, auction_id integer,user_id integer,date varchar(30),start_date varchar(30),end_date varchar(30), user_bid varchar(25),winner varchar(2),sold varchar(2))";
		$this->db->query($sql);
	
	}

}
?>

<?php
################################################################################################
# Auction Bids  Opencart 1.5.1.x From Webkul  http://webkul.com 	#
################################################################################################
class ModelCatalogWkauctionbids extends Model {
	
	public function addEvent($name,$date,$desc) {
	$sql="INSERT INTO " . DB_PREFIX . "wkevent SET name = '" . $this->db->escape($name) . "', date = '" . $this->db->escape($date) . "', descs = '" . $this->db->escape($desc)."'";
		$this->db->query($sql);
		
		
			
		$this->cache->delete('wkevent');
	}
	
	  	
	public function getBids($data) 
	{
		$sql="SELECT a.id,p.name, c.firstname ,c.lastname,a.start_date,a.end_date,a.user_bid,a.date,a.winner,a.sold FROM ".DB_PREFIX."wkauctionbids As a LEFT JOIN ".DB_PREFIX."product_description As p ON p.product_id = a.product_id LEFT JOIN ".DB_PREFIX."customer As c ON c.customer_id = a.user_id GROUP BY a.id";
		$result=$this->db->query($sql);

		return $result->rows;
		
		}
	public function getProduct($product) 
	{
		$sql ="SELECT name FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$product . "'";
		$result=$this->db->query($sql);
		return $result->rows;
		
	}
	public function getCustomer($customer) 
	{
		$sql ="SELECT * FROM " . DB_PREFIX . "wkauctionbids";
		$result=$this->db->query($sql);
		return $result->rows;
		
	}
	public function getAuction($auction) 
	{
		$sql ="SELECT * FROM " . DB_PREFIX . "wkauctionbids";
		$result=$this->db->query($sql);
		return $result->rows;
		
	}



	
	public function getTotalEvents($data) 
	{
		$sql ="SELECT  * FROM " . DB_PREFIX . "wkevent";
		$result=$this->db->query($sql);
		return count($result->rows);
		
	}
        public function deleteBid($id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "wkauctionbids WHERE id = '" . (int)$id . "'");
		
		$this->cache->delete('wkauctionbids');
	}
	
	public function editEvent($id,$name,$date,$desc) {
		$this->db->query("UPDATE " . DB_PREFIX . "wkevent SET name = '" . $this->db->escape($name) . "', date = '" . $this->db->escape($date) . "', descs = '" . $this->db->escape($desc) ."' WHERE id = '" . (int)$id . "'");
		
						
		$this->cache->delete('wkevent');
	}
	public function getEvent($id) {
		$sql ="SELECT  * FROM " . DB_PREFIX . "wkevent WHERE id = '" . (int)$id . "'";
		$result=$this->db->query($sql);
		return $result->rows;
	}
}
?>
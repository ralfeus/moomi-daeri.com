<?php

################################################################################################
# Product auction for Opencart 1.5.1.x From webkul http://webkul.com    #
################################################################################################
class ModelModuleWkproductauction extends Model {
	
	public function getAuction($productId) {
			
		$data = $this->getDb()->query("
			SELECT * FROM wkauction WHERE product_id = :productId AND isauction = 1
			", [':productId' => $productId]);
		
		return $data->rows;
	}
	public function getAuctionbids($auction_id) {
			
		$data = $this->getDb()->query("SELECT * FROM " . DB_PREFIX . "wkauctionbids WHERE sold=0 AND auction_id = '" . (int)$auction_id . "' ORDER BY id");

		return $data->rows;
	}
	public function updateAuctionbids($auction_id) {
		//LOAD LANGUAGE
		$this->language->load('module/wkproduct_auction');

		$bids=$this->getDb()->query("SELECT MAX(user_bid) id FROM " . DB_PREFIX . "wkauctionbids WHERE auction_id = '" . (int)$auction_id . "'");
		$bid_id=$bids->row;
		
        $ids=$this->getDb()->query("SELECT * FROM " . DB_PREFIX . "wkauctionbids WHERE user_bid='" . (int)$bid_id['id'] . "'");
        $record=$ids->row;

        if(!isset($record['product_id'])){
        	$this->getDb()->query("UPDATE " . DB_PREFIX . "wkauction SET isauction=0 WHERE id = '" .(int)$auction_id . "'");
        	return true;
        }
        
        $price=$this->getDb()->query("SELECT price FROM " . DB_PREFIX . "product WHERE product_id='" . (int)$record['product_id'] . "'");
		$price=$price->row;
		$code=rand();
		$discount=$price['price']-$bid_id['id'];
		$this->getDb()->query("INSERT INTO " . DB_PREFIX . "coupon SET name ='Bid Coupen',uses_total=1,uses_customer='1',type='F',status=1,code = '" .$code. "',discount = '" .$discount. "'");
		
		$coupen=$this->getDb()->query("SELECT MAX(coupon_id) id FROM " . DB_PREFIX . "coupon");
		$coupen=$coupen->row;

		$this->getDb()->query("INSERT INTO " . DB_PREFIX . "coupon_product SET coupon_id = '" .(int)$coupen['id']. "',product_id = '" .(int)$record['product_id']. "'");
		$this->getDb()->query("UPDATE " . DB_PREFIX . "wkauctionbids SET winner=1 WHERE user_bid='" . (int)$bid_id['id'] . "'");
        $this->getDb()->query("UPDATE " . DB_PREFIX . "wkauction SET isauction=0 WHERE id = '" .(int)$auction_id . "'");

        $prod=$this->getDb()->query("SELECT name FROM " . DB_PREFIX . "product_description WHERE product_id='" . (int)$record['product_id'] . "'");
        $prod=$prod->row;
        
        $detail= $this->language->get('bid_message_customer_message1').$prod['name'].$this->language->get('bid_message_customer_message2').$code. $this->language->get('bid_message_customer_message3');

        $sql="SELECT * FROM ".DB_PREFIX ."customer WHERE customer_id='" . (int)$record['user_id'] . "'";
		$customer=$this->getDb()->query($sql)->row;
		
		$message  = '<html dir="ltr" lang="en">' . "\n";
		$message .= '  <head>' . "\n";
		$message .= '    <title>'.$this->language->get('bid_message_customer_title').'</title>' . "\n";
		$message .= '    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">' . "\n";
		$message .= '  </head>' . "\n";
		$message .= '  <body>' . html_entity_decode($detail, ENT_QUOTES, 'UTF-8') . '</body>' . "\n";
		$message .= '</html>' . "\n";

		$toseller=array();
		$toseller['emailto']=$customer['email'];
		$toseller['message']=$message;
		$toseller['mailfrom']=$this->config->get('config_email');
		$toseller['subject']=$this->language->get('bid_message_customer_subject');
		$toseller['name']=$this->config->get('config_name');
$file = DIR_ROOT . 'auction_mail.txt';
$handle = fopen($file, 'a+'); 
fwrite($handle, "Mail to: " . $toseller['emailto'] . "\r\n");
fwrite($handle, "Mail from: " . $toseller['mailfrom'] . "\r\n");
fwrite($handle, "Subject: " . $toseller['subject'] . "\r\n");
fwrite($handle, "Message: " . $toseller['message'] . "\r\n");
fwrite($handle, "================================================================================================================================================" . "\r\n");
fclose($handle); 		
		$this->sendMail($toseller);

		$detail= $this->language->get('bid_message_admin_message1').$customer['firstname']." ".$customer['lastname'] .$this->language->get('bid_message_admin_message2').$prod['name'].$this->language->get('bid_message_admin_message3').$code.'.';
		$message  = '<html dir="ltr" lang="en">' . "\n";
		$message .= '  <head>' . "\n";
		$message .= '    <title>'.$this->language->get('bid_message_customer_title').'</title>' . "\n";
		$message .= '    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">' . "\n";
		$message .= '  </head>' . "\n";
		$message .= '  <body>' . html_entity_decode($detail, ENT_QUOTES, 'UTF-8') . '</body>' . "\n";
		$message .= '</html>' . "\n";
		
		$toAdmin=array();
		$toAdmin['emailto']=$this->config->get('config_email');
		$toAdmin['message']=$message;
		$toAdmin['mailfrom']=$customer['email'];
		$toAdmin['subject']=$this->language->get('bid_message_customer_subject');
		$toAdmin['name']=$customer['firstname']." ".$customer['lastname'];
$file = DIR_ROOT . 'auction_mail.txt';
$handle = fopen($file, 'a+'); 
fwrite($handle, "Mail to: " . $toAdmin['emailto'] . "\r\n");
fwrite($handle, "Mail from: " . $toAdmin['mailfrom'] . "\r\n");
fwrite($handle, "Subject: " . $toAdmin['subject'] . "\r\n");
fwrite($handle, "Message: " . $toAdmin['message'] . "\r\n");
fwrite($handle, "================================================================================================================================================" . "\r\n");
fclose($handle); 
		$this->sendMail($toAdmin);

		return True;
	}

    public function sendMail($data){
		$mail = new Mail();	
		$mail->protocol = $this->config->get('config_mail_protocol');
		$mail->parameter = $this->config->get('config_mail_parameter');
		$mail->hostname = $this->config->get('config_smtp_host');
		$mail->username = $this->config->get('config_smtp_username');
		$mail->password = $this->config->get('config_smtp_password');
		$mail->port = $this->config->get('config_smtp_port');
		$mail->timeout = $this->config->get('config_smtp_timeout');				
		$mail->setTo($data['emailto']);
		$mail->setFrom($data['mailfrom']);
		$mail->setSender($data['name']);
		$mail->setSubject(html_entity_decode($data['subject'], ENT_QUOTES, 'UTF-8'));					
		$mail->setHtml($data['message']);
		$mail->send();
	
	}

	public function wkauctionbids_viewbids($auction){
	    $query =$this->getDb()->query("SELECT c.nickname, wa.user_bid FROM " . DB_PREFIX . "wkauctionbids wa LEFT JOIN ". DB_PREFIX ."customer c ON (c.customer_id=wa.user_id) WHERE wa.auction_id = '" . (int)$auction . "' ORDER BY wa.id DESC");
	    return $query->rows;
	}

	public function wkauctionbids_insertbids($data,$user){
	    $date = date('Y-m-d H:i:s');
	    $sql=$this->getDb()->query("SELECT MAX(wab.user_bid) id,wa.min,wa.max FROM " . DB_PREFIX . "wkauctionbids wab RIGHT JOIN ". DB_PREFIX . "wkauction wa ON(wab.auction_id=wa.id) WHERE wa.id = '" . (int)$data['auction'] . "'");
	    $bid_id= $sql->row;

	    if ($data['amount']<=$bid_id['id'] && !empty($bid_id['id'])) {
		return 'not'; //only for checking not mesaages
	    } elseif ($data['amount']>=$bid_id['min'] && $data['amount']<=$bid_id['max'] && empty($bid_id['id'])) {
		$query=$this->getDb()->query("INSERT INTO " . DB_PREFIX . "wkauctionbids SET winner='0',sold='0',auction_id = '" . (int)$data['auction']. "', user_id = '" .(int)$user."', product_id = '" .(int)$data['product_id']."', start_date = '" .$this->getDb()->escape($data['start_date']). "', end_date = '" .$this->getDb()->escape($data['end_date'])."', date = '" .$date."', user_bid = '" .(int)$data['amount']."'");
		return 'done';
	    } elseif ($data['amount']>=$bid_id['min'] && $data['amount']<=$bid_id['max'] && !empty($bid_id['id']) && $data['amount']>$bid_id['id']) {
		$query=$this->getDb()->query("INSERT INTO " . DB_PREFIX . "wkauctionbids SET winner='0',sold='0',auction_id = '" . (int)$data['auction']. "', user_id = '" .(int)$user."', product_id = '" .(int)$data['product_id']."', start_date = '" .$this->getDb()->escape($data['start_date']). "', end_date = '" .$this->getDb()->escape($data['end_date'])."', date = '" .$date."', user_bid = '" .(int)$data['amount']."'");
		return 'done';  //only for checking not mesaages
	    } else {
		return 'not_done';  //only for checking not mesaages
	    }

	}

}

?>

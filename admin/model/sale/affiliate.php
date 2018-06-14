<?php
class ModelSaleAffiliate extends \system\engine\Model {
	public function addAffiliate($data) {
      	$this->db->query("INSERT INTO affiliate SET firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', email = '" . $this->db->escape($data['email']) . "', telephone = '" . $this->db->escape($data['telephone']) . "', fax = '" . $this->db->escape($data['fax']) . "', password = '" . $this->db->escape(md5($data['password'])) . "', company = '" . $this->db->escape($data['company']) . "', address_1 = '" . $this->db->escape($data['address_1']) . "', address_2 = '" . $this->db->escape($data['address_2']) . "', city = '" . $this->db->escape($data['city']) . "', postcode = '" . $this->db->escape($data['postcode']) . "', country_id = '" . (int)$data['country_id'] . "', zone_id = '" . (int)$data['zone_id'] . "', code = '" . $this->db->escape($data['code']) . "', commission = '" . (float)$data['commission'] . "', tax = '" . $this->db->escape($data['tax']) . "', payment = '" . $this->db->escape($data['payment']) . "', cheque = '" . $this->db->escape($data['cheque']) . "', paypal = '" . $this->db->escape($data['paypal']) . "', bank_name = '" . $this->db->escape($data['bank_name']) . "', bank_branch_number = '" . $this->db->escape($data['bank_branch_number']) . "', bank_swift_code = '" . $this->db->escape($data['bank_swift_code']) . "', bank_account_name = '" . $this->db->escape($data['bank_account_name']) . "', bank_account_number = '" . $this->db->escape($data['bank_account_number']) . "', status = '" . (int)$data['status'] . "', date_added = NOW()");
	}
	
	public function editAffiliate($affiliate_id, $data) {
		$this->db->query("UPDATE affiliate SET firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', email = '" . $this->db->escape($data['email']) . "', telephone = '" . $this->db->escape($data['telephone']) . "', fax = '" . $this->db->escape($data['fax']) . "', company = '" . $this->db->escape($data['company']) . "', address_1 = '" . $this->db->escape($data['address_1']) . "', address_2 = '" . $this->db->escape($data['address_2']) . "', city = '" . $this->db->escape($data['city']) . "', postcode = '" . $this->db->escape($data['postcode']) . "', country_id = '" . (int)$data['country_id'] . "', zone_id = '" . (int)$data['zone_id'] . "', code = '" . $this->db->escape($data['code']) . "', commission = '" . (float)$data['commission'] . "', tax = '" . $this->db->escape($data['tax']) . "', payment = '" . $this->db->escape($data['payment']) . "', cheque = '" . $this->db->escape($data['cheque']) . "', paypal = '" . $this->db->escape($data['paypal']) . "', bank_name = '" . $this->db->escape($data['bank_name']) . "', bank_branch_number = '" . $this->db->escape($data['bank_branch_number']) . "', bank_swift_code = '" . $this->db->escape($data['bank_swift_code']) . "', bank_account_name = '" . $this->db->escape($data['bank_account_name']) . "', bank_account_number = '" . $this->db->escape($data['bank_account_number']) . "', status = '" . (int)$data['status'] . "' WHERE affiliate_id = '" . (int)$affiliate_id . "'");
	
      	if ($data['password']) {
        	$this->db->query("UPDATE affiliate SET password = '" . $this->db->escape(md5($data['password'])) . "' WHERE affiliate_id = '" . (int)$affiliate_id . "'");
      	}
	}
	
	public function deleteAffiliate($affiliate_id) {
		$this->db->query("DELETE FROM affiliate WHERE affiliate_id = '" . (int)$affiliate_id . "'");
		$this->db->query("DELETE FROM affiliate_transaction WHERE affiliate_id = '" . (int)$affiliate_id . "'");
	}
	
	public function getAffiliate($affiliate_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM affiliate WHERE affiliate_id = '" . (int)$affiliate_id . "'");
	
		return $query->row;
	}
	
	public function getAffiliateByEmail($email) {
		$query = $this->db->query("SELECT DISTINCT * FROM affiliate WHERE email = '" . $this->db->escape($email) . "'");
	
		return $query->row;
	}
			
	public function getAffiliates($data = array()) {
		$sql = "SELECT *, CONCAT(a.firstname, ' ', a.lastname) AS name, (SELECT SUM(at.amount) FROM affiliate_transaction at WHERE at.affiliate_id = a.affiliate_id GROUP BY at.affiliate_id) AS balance FROM affiliate a";

		$implode = array();
		
		if (!empty($data['filter_name'])) {
			$implode[] = "LCASE(CONCAT(a.firstname, ' ', a.lastname)) LIKE '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "%'";
		}

		if (!empty($data['filter_email'])) {
			$implode[] = "a.email = '" . $this->db->escape($data['filter_email']) . "'";
		}
		
		if (!empty($data['filter_code'])) {
			$implode[] = "a.code = '" . $this->db->escape($data['filter_code']) . "'";
		}
					
		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$implode[] = "a.status = '" . (int)$data['filter_status'] . "'";
		}	
		
		if (isset($data['filter_approved']) && !is_null($data['filter_approved'])) {
			$implode[] = "a.approved = '" . (int)$data['filter_approved'] . "'";
		}		
		
		if (!empty($data['filter_date_added'])) {
			$implode[] = "DATE(a.date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}
		
		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}
		
		$sort_data = array(
			'name',
			'a.email',
			'a.code',
			'a.status',
			'a.approved',
			'a.date_added'
		);	
			
		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];	
		} else {
			$sql .= " ORDER BY name";	
		}
			
		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}
		
		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}			

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}	
			
			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}		
		
		$query = $this->db->query($sql);
		
		return $query->rows;	
	}
	
	public function approve($affiliate_id) {
		$affiliate_info = $this->getAffiliate($affiliate_id);
			
		if ($affiliate_info) {
			$this->db->query("UPDATE affiliate SET approved = '1' WHERE affiliate_id = '" . (int)$affiliate_id . "'");
			
			$this->load->language('mail/affiliate');
	
			$message  = sprintf($this->language->get('text_approve_welcome'), $this->config->get('config_name')) . "\n\n";
			$message .= $this->language->get('text_approve_login') . "\n";
			$message .= HTTP_CATALOG . 'index.php?route=affiliate/login' . "\n\n";
			$message .= $this->language->get('text_approve_services') . "\n\n";
			$message .= $this->language->get('text_approve_thanks') . "\n";
			$message .= $this->config->get('config_name');
	
			$mail = new Mail();
			$mail->protocol = $this->config->get('config_mail_protocol');
			$mail->hostname = $this->config->get('config_smtp_host');
			$mail->username = $this->config->get('config_smtp_username');
			$mail->password = $this->config->get('config_smtp_password');
			$mail->port = $this->config->get('config_smtp_port');
			$mail->timeout = $this->config->get('config_smtp_timeout');							
			$mail->setTo($affiliate_info['email']);
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender($this->config->get('config_name'));
			$mail->setSubject(sprintf($this->language->get('text_approve_subject'), $this->config->get('config_name')));
			$mail->setText(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
			$mail->send();
		}
	}
	
	public function getAffiliatesByNewsletter() {
		$query = $this->db->query("SELECT * FROM affiliate WHERE newsletter = '1' ORDER BY firstname, lastname, email");
	
		return $query->rows;
	}
		
	public function getTotalAffiliates($data = array()) {
      	$sql = "SELECT COUNT(*) AS total FROM affiliate";
		
		$implode = array();
		
		if (!empty($data['filter_name'])) {
			$implode[] = "CONCAT(firstname, ' ', lastname) LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
		}
		
		if (!empty($data['filter_email'])) {
			$implode[] = "email = '" . $this->db->escape($data['filter_email']) . "'";
		}	
				
		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$implode[] = "status = '" . (int)$data['filter_status'] . "'";
		}			
		
		if (isset($data['filter_approved']) && !is_null($data['filter_approved'])) {
			$implode[] = "approved = '" . (int)$data['filter_approved'] . "'";
		}		
				
		if (!empty($data['filter_date_added'])) {
			$implode[] = "DATE(date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}
		
		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}
				
		$query = $this->db->query($sql);
				
		return $query->row['total'];
	}
		
	public function getTotalAffiliatesAwaitingApproval() {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM affiliate WHERE status = '0' OR approved = '0'");

		return $query->row['total'];
	}
	
	public function getTotalAffiliatesByCountryId($country_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM affiliate WHERE country_id = '" . (int)$country_id . "'");
		
		return $query->row['total'];
	}	
	
	public function getTotalAffiliatesByZoneId($zone_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM affiliate WHERE zone_id = '" . (int)$zone_id . "'");
		
		return $query->row['total'];
	}
		
	public function addTransaction($affiliate_id, $description = '', $amount = '', $order_id = 0, $order_product_id = 0) {
		$affiliate_info = $this->getAffiliate($affiliate_id);
		
		if ($affiliate_info) { 
			$this->db->query("INSERT INTO affiliate_transaction SET affiliate_id = '" . (int)$affiliate_id . "', order_id = '" . (float)$order_id . "', description = '" . $this->db->escape($description) . "', amount = '" . (float)$amount . "', date_added = NOW()"
				. ", order_product_id = '" . (int)$order_product_id . "'"
				);
		
			$this->language->load('mail/affiliate');
							
			$message  = sprintf($this->language->get('text_transaction_received'), $this->currency->format($amount, $this->config->get('config_currency'))) . "\n\n";
			$message .= sprintf($this->language->get('text_transaction_total'), $this->currency->format($this->getTransactionTotal($affiliate_id), $this->config->get('config_currency')));
								
			$mail = new Mail();
			$mail->protocol = $this->config->get('config_mail_protocol');
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->hostname = $this->config->get('config_smtp_host');
			$mail->username = $this->config->get('config_smtp_username');
			$mail->password = $this->config->get('config_smtp_password');
			$mail->port = $this->config->get('config_smtp_port');
			$mail->timeout = $this->config->get('config_smtp_timeout');
			$mail->setTo($affiliate_info['email']);
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender($this->config->get('config_name'));
			$mail->setSubject(sprintf($this->language->get('text_transaction_subject'), $this->config->get('config_name')));
			$mail->setText($message);
			$mail->send();
		}
	}
	
	public function deleteTransaction($order_id, $order_product_id = 0) {
		$order_product_id = (int)$order_product_id;
		if ($order_product_id) {
			$this->db->query("DELETE FROM affiliate_transaction WHERE order_product_id = '" . (int)$order_product_id . "'");
		} elseif (null !== $order_id) {
			$this->db->query("DELETE FROM affiliate_transaction WHERE order_id = '" . (int)$order_id . "'");
		}
	}
	
	public function getTransactions($affiliate_id, $start = 0, $limit = 10) {
		$query = $this->db->query("SELECT * FROM affiliate_transaction WHERE affiliate_id = '" . (int)$affiliate_id . "' ORDER BY date_added DESC LIMIT " . (int)$start . "," . (int)$limit);
	
		return $query->rows;
	}

	public function getTotalTransactions($affiliate_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total  FROM affiliate_transaction WHERE affiliate_id = '" . (int)$affiliate_id . "'");
	
		return $query->row['total'];
	}
			
	public function getTransactionTotal($affiliate_id) {
		$query = $this->db->query("SELECT SUM(amount) AS total FROM affiliate_transaction WHERE affiliate_id = '" . (int)$affiliate_id . "'");
	
		return $query->row['total'];
	}	
	
	public function getTotalTransactionsByOrderId($order_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM affiliate_transaction WHERE order_id = '" . (int)$order_id . "'");
	
		return $query->row['total'];
	}

	public function getProductAffiliateCommission($product_id) {

		$affiliate_commission = $this->getProductAffiliateCommissions($product_id);
		if (!empty($affiliate_commission['pac'])) {
			$affiliate_commission = $affiliate_commission['pac'];
		} elseif (!empty($affiliate_commission['cac'])) {
			$affiliate_commission = $affiliate_commission['cac'];
		} else {
			$affiliate_commission = $affiliate_commission['gac'];
		}
		return (float)$affiliate_commission;

	}

	public function getProductAffiliateCommissions($product_id) {

		$query = $this->db->query(
			"SELECT p.affiliate_commission AS pac, c.affiliate_commission AS cac FROM\n"
			. DB_PREFIX . "product p\n"
			. "INNER JOIN product_to_category p2c ON (p.product_id = p2c.product_id)\n"
			. "INNER JOIN category c ON (c.category_id = p2c.category_id)\n"
			. "WHERE p2c.main_category = 1 AND p.product_id = '" . (int)$product_id . "'"
			);
		$query->row['gac'] = $this->config->get('config_commission');
		return $query->row;

	}

	public function setOrderProductAffiliateCommission($order_product_id) {

		$order_product_id = (int)$order_product_id;
		$query = $this->db->query(
			"SELECT op.product_id, op.total, o.affiliate_id, o.order_id, at.affiliate_transaction_id FROM\n"
			. DB_PREFIX . "order_product op\n"
			. "INNER JOIN `order` o ON (o.order_id = op.order_id)\n"
			. "LEFT JOIN affiliate_transaction at ON (op.order_product_id = at.order_product_id)\n"
			. "WHERE op.order_product_id = '" . $order_product_id . "'"
			);
		if (!$query->row) {
			return false;
		}
		if ($query->row['affiliate_transaction_id']) {
			return false;
		}
		$this->addTransaction(
			$query->row['affiliate_id'],
			'Order Product ID: #' . $order_product_id,
			round($this->getProductAffiliateCommission($query->row['product_id']) / 100 * $query->row['total'], 4),
			$query->row['order_id'],
			$order_product_id
			);
		return true;

	}

}
?>
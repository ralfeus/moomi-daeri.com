<?php
class ModelAccountReturn extends \system\engine\Model {
	public function addReturn($data) {			      	
		$this->getDb()->query("INSERT INTO `return` SET customer_id = '" . (int)$this->customer->getId() . "', order_id = '" . (int)$data['order_id'] . "', date_ordered = '" . $this->getDb()->escape($data['date_ordered']) . "', firstname = '" . $this->getDb()->escape($data['firstname']) . "', lastname = '" . $this->getDb()->escape($data['lastname']) . "', email = '" . $this->getDb()->escape($data['email']) . "', telephone = '" . $this->getDb()->escape($data['telephone']) . "', return_status_id = '" . (int)$this->config->get('config_return_status_id') . "', comment = '" . $this->getDb()->escape($data['comment']) . "', date_added = NOW(), date_modified = NOW()");
      	
		$return_id = $this->getDb()->getLastId();
		
		foreach ($data['return_product'] as $return_product) {
      		$this->getDb()->query("INSERT INTO return_product SET return_id = '" . (int)$return_id . "', name = '" . $this->getDb()->escape($return_product['name']) . "', model = '" . $this->getDb()->escape($return_product['model']) . "', quantity = '" . (int)$return_product['quantity'] . "', return_reason_id = '" . (int)$return_product['return_reason_id'] . "', opened = '" . (int)$return_product['opened'] . "', comment = '" . $this->getDb()->escape($return_product['comment']) . "'");
		}
	}
	
	public function getReturn($return_id) {
		$query = $this->getDb()->query("SELECT * FROM `return`WHERE return_id = '" . (int)$return_id . "' AND customer_id = '" . $this->customer->getId() . "'");
		
		return $query->row;
	}
	
	public function getReturns($start = 0, $limit = 20) {
		if ($start < 0) {
			$start = 0;
		}
				
		$query = $this->getDb()->query("SELECT r.return_id, r.order_id, r.firstname, r.lastname, rs.name as status, r.date_added FROM `return` r LEFT JOIN return_status rs ON (r.return_status_id = rs.return_status_id) WHERE r.customer_id = '" . $this->customer->getId() . "' AND rs.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY r.return_id DESC LIMIT " . (int)$start . "," . (int)$limit);
		
		return $query->rows;
	}
			
	public function getTotalReturns() {
		$query = $this->getDb()->query("SELECT COUNT(*) AS total FROM `return`WHERE customer_id = '" . $this->customer->getId() . "'");
		
		return $query->row['total'];
	}
	
	public function getTotalReturnProductsByReturnId($return_id) {
		$query = $this->getDb()->query("SELECT COUNT(*) AS total FROM return_product WHERE return_id = '" . (int)$return_id . "'");
		
		return $query->row['total'];
	}	
	
	public function getReturnProducts($return_id) {
		$query = $this->getDb()->query("SELECT *, (SELECT rr.name FROM return_reason rr WHERE rr.return_reason_id = rp.return_reason_id AND rr.language_id = '" . (int)$this->config->get('config_language_id') . "') AS reason, (SELECT ra.name FROM return_action ra WHERE ra.return_action_id = rp.return_action_id AND ra.language_id = '" . (int)$this->config->get('config_language_id') . "') AS action FROM return_product rp WHERE rp.return_id = '" . $return_id . "'");
		
		return $query->rows;	
	}	
	
	public function getReturnHistories($return_id) {
		$query = $this->getDb()->query("SELECT rh.date_added, rs.name AS status, rh.comment, rh.notify FROM return_history rh LEFT JOIN return_status rs ON rh.return_status_id = rs.return_status_id WHERE rh.return_id = '" . (int)$return_id . "' AND rs.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY rh.date_added ASC");

		return $query->rows;
	}			
}
?>
<?php
class ModelAffiliateTransaction extends \system\engine\Model {
	public function getTransactions($data = array()) {
		$sql = "SELECT * FROM `affiliate_transaction` WHERE affiliate_id = '" . (int)$this->affiliate->getId() . "'";
		   
		$sort_data = array(
			'amount',
			'description',
			'date_added'
		);
	
		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];	
		} else {
			$sql .= " ORDER BY date_added";	
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

		$query = $this->getDb()->query($sql);
	
		return $query->rows;
	}	
		
	public function getTotalTransactions() {
      	$query = $this->getDb()->query("SELECT COUNT(*) AS total FROM `affiliate_transaction` WHERE affiliate_id = '" . (int)$this->affiliate->getId() . "'");
			
		return $query->row['total'];
	}	
			
	public function getBalance() {
		$query = $this->getDb()->query("SELECT SUM(amount) AS total FROM `affiliate_transaction` WHERE affiliate_id = '" . (int)$this->affiliate->getId() . "' GROUP BY affiliate_id");
		
		if ($query->num_rows) {
			return $query->row['total'];
		} else {
			return 0;	
		}
	}
}
?>
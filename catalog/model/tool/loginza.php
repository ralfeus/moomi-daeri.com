<?php
class ModelToolLoginza extends Model {
	function check_identity($identity) {
		$query = $this->db->query("
			SELECT customer_id
			FROM " . DB_PREFIX . "customer
			WHERE identity = '". $this->db->escape($identity) ."'"
		);

		if ($query->num_rows) {
			return $query->row['customer_id'];
		} else {
			return false;
		}
	}
	
	public function add_customer($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "customer (identity, firstname, lastname, email, telephone, fax, newsletter, customer_group_id, password, status, date_added, approved) VALUES ('" . $this->db->escape($data['identity']) . "', '" . $this->db->escape($data['firstname']) . "', '" . $this->db->escape($data['lastname']) . "', '" . $this->db->escape($data['email']) . "', '', '', '0', '" . (int)$data['customer_group_id'] . "', '" . $this->db->escape(md5($data['password'])) . "', '1', NOW(), '1')");

		return $this->db->getLastId(); // customer_id
	}
	
	public function login($customer_id) {
		$customer_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer WHERE customer_id = '" . (int)$customer_id . "' AND status = '1'");
		
		
		if ($customer_query->num_rows) {
			$this->session->data['customer_id'] = $customer_query->row['customer_id'];	
		    
			if (($customer_query->row['cart']) && (is_string($customer_query->row['cart']))) {
				$cart = unserialize($customer_query->row['cart']);
				
				foreach ($cart as $key => $value) {
					if (!array_key_exists($key, $this->session->data['cart'])) {
						$this->session->data['cart'][$key] = $value;
					} else {
						$this->session->data['cart'][$key] += $value;
					}
				}			
			}
			
			$this->customer_id = $customer_query->row['customer_id'];
			$this->firstname = $customer_query->row['firstname'];
			$this->lastname = $customer_query->row['lastname'];
			$this->email = $customer_query->row['email'];
			$this->telephone = $customer_query->row['telephone'];
			$this->fax = $customer_query->row['fax'];
			$this->newsletter = $customer_query->row['newsletter'];
			$this->customer_group_id = $customer_query->row['customer_group_id'];
			$this->address_id = $customer_query->row['address_id'];
      
	  		return TRUE;
    	} else {
      		return FALSE;
    	}
  	}
}
?>
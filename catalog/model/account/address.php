<?php
class ModelAccountAddress extends \system\engine\Model {
	public function addAddress($data) {
		$this->getDb()->query("INSERT INTO address SET customer_id = '" . (int)$this->customer->getId() . "', company = '" . $this->getDb()->escape($data['company']) . "', firstname = '" . $this->getDb()->escape($data['firstname']) . "', lastname = '" . $this->getDb()->escape($data['lastname']) . "', phone='" . $this->getDb()->escape($data['phone']) . "', address_1 = '" . $this->getDb()->escape($data['address_1']) . "', address_2 = '" . $this->getDb()->escape($data['address_2']) . "', postcode = '" . $this->getDb()->escape($data['postcode']) . "', city = '" . $this->getDb()->escape($data['city']) . "', zone_id = '" . (int)$data['zone_id'] . "', country_id = '" . (int)$data['country_id'] . "'");
		
		$address_id = $this->getDb()->getLastId();
		
		if (isset($data['default']) && $data['default'] == '1') {
			$this->getDb()->query("UPDATE customer SET address_id = '" . (int)$address_id . "' WHERE customer_id = '" . (int)$this->customer->getId() . "'");
		}
		
		return $address_id;
	}
	
	public function editAddress($address_id, $data) {
		$this->getDb()->query("UPDATE address SET company = '" . $this->getDb()->escape($data['company']) . "', firstname = '" . $this->getDb()->escape($data['firstname']) . "', lastname = '" . $this->getDb()->escape($data['lastname']) . "', address_1 = '" . $this->getDb()->escape($data['address_1']) . "', address_2 = '" . $this->getDb()->escape($data['address_2']) . "', postcode = '" . $this->getDb()->escape($data['postcode']) . "', city = '" . $this->getDb()->escape($data['city']) . "', zone_id = '" . (int)$data['zone_id'] . "', country_id = '" . (int)$data['country_id'] . "' WHERE address_id  = '" . (int)$address_id . "' AND customer_id = '" . (int)$this->customer->getId() . "'");
	
		if (isset($data['default']) && $data['default'] == '1') {
			$this->getDb()->query("UPDATE customer SET address_id = '" . (int)$address_id . "' WHERE customer_id = '" . (int)$this->customer->getId() . "'");
		}
	}
	
	public function deleteAddress($address_id) {
		$this->getDb()->query("DELETE FROM address WHERE address_id = '" . (int)$address_id . "' AND customer_id = '" . (int)$this->customer->getId() . "'");
	}	
	
	public function getAddress($address_id) {
		$sql = "SELECT DISTINCT * FROM address WHERE address_id = '" . (int)$address_id . "' AND customer_id = '" . (int)$this->customer->getId() . "'";
		$address_query = $this->getDb()->query($sql);
		
		if ($address_query->num_rows) {
			$country_query = $this->getDb()->query("SELECT * FROM `country` WHERE country_id = '" . (int)$address_query->row['country_id'] . "'");
			
			if ($country_query->num_rows) {
				$country = $country_query->row['name'];
				$iso_code_2 = $country_query->row['iso_code_2'];
				$iso_code_3 = $country_query->row['iso_code_3'];
				$address_format = $country_query->row['address_format'];
			} else {
				$country = '';
				$iso_code_2 = '';
				$iso_code_3 = '';	
				$address_format = '';
			}
			
			$zone_query = $this->getDb()->query("SELECT * FROM `zone` WHERE zone_id = '" . (int)$address_query->row['zone_id'] . "'");
			
			if ($zone_query->num_rows) {
				$zone = $zone_query->row['name'];
				$code = $zone_query->row['code'];
			} else {
				$zone = '';
				$code = '';
			}		
			
			$address_data = array(
				'firstname'      => $address_query->row['firstname'],
				'lastname'       => $address_query->row['lastname'],
				'company'        => $address_query->row['company'],
				'phone'      		 => $address_query->row['phone'],
				'address_1'      => $address_query->row['address_1'],
				'address_2'      => $address_query->row['address_2'],
				'postcode'       => $address_query->row['postcode'],
				'city'           => $address_query->row['city'],
				'zone_id'        => $address_query->row['zone_id'],
				'zone'           => $zone,
				'zone_code'      => $code,
				'country_id'     => $address_query->row['country_id'],
				'country'        => $country,	
				'iso_code_2'     => $iso_code_2,
				'iso_code_3'     => $iso_code_3,
				'address_format' => $address_format
			);
			
			return $address_data;
		} else {
			return false;	
		}
	}
	
	public function getAddresses() {
		$address_data = array();
		
		$query = $this->getDb()->query("SELECT * FROM address WHERE customer_id = '" . (int)$this->customer->getId() . "'");
	
		foreach ($query->rows as $result) {
			$country_query = $this->getDb()->query("SELECT * FROM `country` WHERE country_id = '" . (int)$result['country_id'] . "'");
			
			if ($country_query->num_rows) {
				$country = $country_query->row['name'];
				$iso_code_2 = $country_query->row['iso_code_2'];
				$iso_code_3 = $country_query->row['iso_code_3'];
				$address_format = $country_query->row['address_format'];
			} else {
				$country = '';
				$iso_code_2 = '';
				$iso_code_3 = '';	
				$address_format = '';
			}
			
			$zone_query = $this->getDb()->query("SELECT * FROM `zone` WHERE zone_id = '" . (int)$result['zone_id'] . "'");
			
			if ($zone_query->num_rows) {
				$zone = $zone_query->row['name'];
				$code = $zone_query->row['code'];
			} else {
				$zone = '';
				$code = '';
			}		
		
			$address_data[] = array(
				'address_id'     => $result['address_id'],
				'firstname'      => $result['firstname'],
				'lastname'       => $result['lastname'],
				'company'        => $result['company'],
				'address_1'      => $result['address_1'],
				'address_2'      => $result['address_2'],
				'postcode'       => $result['postcode'],
				'city'           => $result['city'],
				'zone_id'        => $result['zone_id'],
				'zone'           => $zone,
				'zone_code'      => $code,
				'country_id'     => $result['country_id'],
				'country'        => $country,	
				'iso_code_2'     => $iso_code_2,
				'iso_code_3'     => $iso_code_3,
				'address_format' => $address_format
			);
		}		
		
		return $address_data;
	}	
	
	public function getTotalAddresses() {
		$query = $this->getDb()->query("SELECT COUNT(*) AS total FROM address WHERE customer_id = '" . (int)$this->customer->getId() . "'");
	
		return $query->row['total'];
	}
}
?>
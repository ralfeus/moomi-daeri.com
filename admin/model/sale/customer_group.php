<?php
class ModelSaleCustomerGroup extends \system\engine\Model {
	public function addCustomerGroup($data) {
		$this->db->query("
		    INSERT INTO customer_group
		    SET
		        name = '" . $this->db->escape($data['name']) . "',
		        allow_overdraft = " . (int)(isset($data['allowOverdraft']) && $data['allowOverdraft']) . ",
		        await_invoice_confirmation = " . (int)(isset($data['awaitInvoiceConfirmation']) && $data['awaitInvoiceConfirmation'])
        );
	}
	
	public function editCustomerGroup($customer_group_id, $data) {
		$this->db->query("
		    UPDATE customer_group
		    SET
		        name = '" . $this->db->escape($data['name']) . "',
		        allow_overdraft = " . (int)(isset($data['allowOverdraft']) && $data['allowOverdraft']) . ",
		        await_invoice_confirmation = " . (int)(isset($data['awaitInvoiceConfirmation']) && $data['awaitInvoiceConfirmation']) . "
            WHERE customer_group_id = '" . (int)$customer_group_id . "'
        ");
	}
	
	public function deleteCustomerGroup($customer_group_id) {
		$this->db->query("DELETE FROM customer_group WHERE customer_group_id = '" . (int)$customer_group_id . "'");
		$this->db->query("DELETE FROM product_discount WHERE customer_group_id = '" . (int)$customer_group_id . "'");
	}
	
	public function getCustomerGroup($customer_group_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM customer_group WHERE customer_group_id = '" . (int)$customer_group_id . "'");
		
		return $query->row;
	}
	
	public function getCustomerGroups($data = array()) {
		$sql = "SELECT * FROM customer_group";
		
		$sql .= " ORDER BY name";	
			
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
	
	public function getTotalCustomerGroups() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM customer_group");
		
		return $query->row['total'];
	}
}
?>
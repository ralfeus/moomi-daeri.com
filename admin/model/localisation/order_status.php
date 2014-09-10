<?php 
class ModelLocalisationOrderStatus extends Model {
	public function addOrderStatus($data) {
        $query = $this->db->query("
            SELECT MAX(status_id) AS last_id
            FROM statuses
            WHERE group_id = " . GROUP_ORDER_STATUS
        );
        $newOrderStatusId = $query->row['last_id'] + 1;
        $sql = "INSERT INTO statuses (group_id, status_id, language_id, name, public_name) VALUES";
		foreach ($data['order_status'] as $language_id => $value) {
            $sql .= "
                (" .
                    GROUP_ORDER_STATUS . ",
                    $newOrderStatusId,
                    " . (int)$language_id . ",
                    '" . $this->db->escape($value['name']) . "',
                    '" . $this->db->escape($value['name']) . "'
                ),";
		}
		$this->db->query(substr($sql, 0, strlen($sql) - 1));
		$this->cache->delete('order_status');
	}

	public function editOrderStatus($order_status_id, $data) {
		$this->db->query("
		    DELETE FROM statuses
		    WHERE group_id = " . GROUP_ORDER_STATUS . " AND status_id = " . (int)$order_status_id
        );

        $sql = "INSERT INTO statuses (group_id, status_id, language_id, name, public_name) VALUES";
        foreach ($data['order_status'] as $language_id => $value) {
            $sql .= "
                (" .
                GROUP_ORDER_STATUS . ",
                    " . (int)$order_status_id . ",
                    " . (int)$language_id . ",
                    '" . $this->db->escape($value['name']) . "',
                    '" . $this->db->escape($value['name']) . "'
                ),";
        }
        $this->db->query(substr($sql, 0, strlen($sql) - 1));
		$this->cache->delete('order_status');
	}
	
	public function deleteOrderStatus($order_status_id) {
		$this->db->query("
		    DELETE FROM statuses
		    WHERE group_id = " . GROUP_ORDER_STATUS . " AND status_id = " . (int)$order_status_id
        );
		$this->cache->delete('order_status');
	}
		
	public function getOrderStatus($order_status_id) {
		$query = $this->db->query("
		    SELECT status_id AS order_status_id, language_id, name
		    FROM statuses
		    WHERE
		        group_id = " . GROUP_ORDER_STATUS . "
		        AND status_id = " . (int)$order_status_id . "
		        AND language_id = " . (int)$this->config->get('config_language_id')
        );
		
		return $query->row;
	}
		
	public function getOrderStatuses($data = array()) {
      	if ($data) {
//			foreach ($ORDER_STATUS as $order_status_name => $order_status_id)
//			{
//				$statuses['id'] = $order_status_id;
//				$query = $this->db->query("SELECT * FROM order_status WHERE order_status_id = $order_status_id");
//
//			}
			$sql = "
			    SELECT status_id AS order_status_id, language_id, name
			    FROM statuses
			    WHERE group_id = " . GROUP_ORDER_STATUS . " AND language_id = " . (int)$this->config->get('config_language_id')
            ;
			
			$sql .= " ORDER BY name";	
			
			if (isset($data['order']) && ($data['order'] == 'DESC')) {
				$sql .= " DESC";
			} else {
				$sql .= " ASC";
			}
			
			$query = $this->db->query($sql);
			return $query->rows;
		} else {
			$order_status_data = $this->cache->get('order_status.' . (int)$this->config->get('config_language_id'));
		
			if (!$order_status_data) {
                $query = $this->db->query("
                    SELECT status_id AS order_status_id, name
                    FROM statuses
                    WHERE group_id = " . GROUP_ORDER_STATUS . " AND language_id = " . (int)$this->config->get('config_language_id') . "
                    ORDER BY name
                ");
	
				$order_status_data = $query->rows;
			
				$this->cache->set('order_status.' . (int)$this->config->get('config_language_id'), $order_status_data);
			}	
	
			return $order_status_data;				
		}
	}
	
	public function getOrderStatusDescriptions($order_status_id) {
		$order_status_data = array();
		$query = $this->db->query("
		    SELECT status_id AS order_status_id, language_id, name
            FROM statuses
            WHERE group_id = " . GROUP_ORDER_STATUS . " AND status_id = " . (int)$order_status_id
        );

		foreach ($query->rows as $result) {
			$order_status_data[$result['language_id']] = array('name' => $result['name']);
		}
		
		return $order_status_data;
	}
	
	public function getTotalOrderStatuses() {
      	$query = $this->db->query("
      	    SELECT COUNT(*) AS total
      	    FROM statuses
      	    WHERE group_id = " . GROUP_ORDER_STATUS . " AND language_id = " . (int)$this->config->get('config_language_id')
        );
		
		return $query->row['total'];
	}	
}
<?php
class ModelCatalogInformation extends \system\engine\Model {
	public function addInformation($data) {
		$this->db->query("
		    INSERT INTO information
		    SET
		        parent_node_id = " . (int)$data['parentId'] . ",
		        sort_order = '" . (int)$this->request->post['sort_order'] . "',
		        status = '" . (int)$data['status'] . "'
        ");

		$information_id = $this->db->getLastId(); 
			
		foreach ($data['information_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO information_description SET information_id = '" . (int)$information_id . "', language_id = '" . (int)$language_id . "', title = '" . $this->db->escape($value['title']) . "', description = '" . $this->db->escape($value['description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', seo_title = '" . $this->db->escape($value['seo_title']) . "', seo_h1 = '" . $this->db->escape($value['seo_h1']) . "'");
		}
		
		if (isset($data['information_store'])) {
			foreach ($data['information_store'] as $store_id) {
				$this->db->query("INSERT INTO information_to_store SET information_id = '" . (int)$information_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

		if (isset($data['information_layout'])) {
			foreach ($data['information_layout'] as $store_id => $layout) {
				if ($layout) {
					$this->db->query("INSERT INTO information_to_layout SET information_id = '" . (int)$information_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout['layout_id'] . "'");
				}
			}
		}
				
		if ($data['keyword']) {
			$this->db->query("INSERT INTO url_alias SET query = 'information_id=" . (int)$information_id . "', keyword = '" . $this->db->escape($data['keyword']) . "'");
		}
		
		$this->cache->delete('information');
	}
	
	public function editInformation($information_id, $data) {
		$this->db->query("
		    UPDATE information
		    SET
		        parent_node_id = " . (int)$data['parentId'] . ",
		        sort_order = '" . (int)$data['sort_order'] . "',
		        status = '" . (int)$data['status'] . "'
            WHERE information_id = '" . (int)$information_id . "'
        ");

		$this->db->query("DELETE FROM information_description WHERE information_id = '" . (int)$information_id . "'");
					
		foreach ($data['information_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO information_description SET information_id = '" . (int)$information_id . "', language_id = '" . (int)$language_id . "', title = '" . $this->db->escape($value['title']) . "', description = '" . $this->db->escape($value['description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', seo_title = '" . $this->db->escape($value['seo_title']) . "', seo_h1 = '" . $this->db->escape($value['seo_h1']) . "'");
		}

		$this->db->query("DELETE FROM information_to_store WHERE information_id = '" . (int)$information_id . "'");
		
		if (isset($data['information_store'])) {
			foreach ($data['information_store'] as $store_id) {
				$this->db->query("INSERT INTO information_to_store SET information_id = '" . (int)$information_id . "', store_id = '" . (int)$store_id . "'");
			}
		}
		
		$this->db->query("DELETE FROM information_to_layout WHERE information_id = '" . (int)$information_id . "'");

		if (isset($data['information_layout'])) {
			foreach ($data['information_layout'] as $store_id => $layout) {
				if ($layout['layout_id']) {
					$this->db->query("INSERT INTO information_to_layout SET information_id = '" . (int)$information_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout['layout_id'] . "'");
				}
			}
		}
				
		$this->db->query("DELETE FROM url_alias WHERE query = 'information_id=" . (int)$information_id. "'");
		
		if ($data['keyword']) {
			$this->db->query("INSERT INTO url_alias SET query = 'information_id=" . (int)$information_id . "', keyword = '" . $this->db->escape($data['keyword']) . "'");
		}
		
		$this->cache->delete('information');
	}
	
	public function deleteInformation($information_id) {
		$this->db->query("DELETE FROM information WHERE information_id = '" . (int)$information_id . "'");
		$this->db->query("DELETE FROM information_description WHERE information_id = '" . (int)$information_id . "'");
		$this->db->query("DELETE FROM information_to_store WHERE information_id = '" . (int)$information_id . "'");
		$this->db->query("DELETE FROM information_to_layout WHERE information_id = '" . (int)$information_id . "'");
		$this->db->query("DELETE FROM url_alias WHERE query = 'information_id=" . (int)$information_id . "'");

		$this->cache->delete('information');
	}	

	public function getInformation($information_id) {
		$query = $this->db->query("
		    SELECT DISTINCT 
		        *, 
		        (
		            SELECT keyword 
		            FROM url_alias 
		            WHERE query = 'information_id=" . (int)$information_id . "'
                ) AS keyword 
            FROM information 
            WHERE information_id = '" . (int)$information_id . "'
        ");
		
		return $query->row;
	}
		
	public function getInformations($data = array()) {
		if ($data) {
			$sql = "
			    SELECT *
			    FROM
                    information AS i
                    LEFT JOIN information_description AS id ON (i.information_id = id.information_id)
			    WHERE id.language_id = '" . (int)$this->config->get('config_language_id') . "'
            ";
		
			$sort_data = array(
				'id.title',
				'i.sort_order'
			);		
		
			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];	
			} else {
				$sql .= " ORDER BY id.title";	
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
		} else {
            $query = $this->db->query("
                SELECT *
                FROM
                    information AS i
                    LEFT JOIN information_description AS id ON (i.information_id = id.information_id)
                WHERE id.language_id = '" . (int)$this->config->get('config_language_id') . "'
                ORDER BY id.title
            ");

            $information_data = $query->rows;
            $this->cache->set('information.' . (int)$this->config->get('config_language_id'), $information_data);

			return $information_data;			
		}
	}
	
	public function getInformationDescriptions($information_id) {
		$information_description_data = array();
		
		$query = $this->db->query("SELECT * FROM information_description WHERE information_id = '" . (int)$information_id . "'");

		foreach ($query->rows as $result) {
			$information_description_data[$result['language_id']] = array(
				'seo_title'        => $result['seo_title'],
				'seo_h1'           => $result['seo_h1'],
				'meta_keyword'     => $result['meta_keyword'],
				'meta_description' => $result['meta_description'],
				'title'       => $result['title'],
				'description' => $result['description']
			);
		}
		
		return $information_description_data;
	}
	
	public function getInformationStores($information_id) {
		$information_store_data = array();
		
		$query = $this->db->query("SELECT * FROM information_to_store WHERE information_id = '" . (int)$information_id . "'");

		foreach ($query->rows as $result) {
			$information_store_data[] = $result['store_id'];
		}
		
		return $information_store_data;
	}

	public function getInformationLayouts($information_id) {
		$information_layout_data = array();
		
		$query = $this->db->query("SELECT * FROM information_to_layout WHERE information_id = '" . (int)$information_id . "'");
		
		foreach ($query->rows as $result) {
			$information_layout_data[$result['store_id']] = $result['layout_id'];
		}
		
		return $information_layout_data;
	}
		
	public function getTotalInformations() {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM information");
		
		return $query->row['total'];
	}	
	
	public function getTotalInformationsByLayoutId($layout_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM information_to_layout WHERE layout_id = '" . (int)$layout_id . "'");

		return $query->row['total'];
	}	
}
?>
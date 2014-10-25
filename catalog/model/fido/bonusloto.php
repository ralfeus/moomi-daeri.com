<?php
class ModelFidobonusloto extends Model {
	public function getbonuslotoStory($bonusloto_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "bonusloto` bl LEFT JOIN `" . DB_PREFIX . "bonusloto_description` bld ON (bl.bonusloto_id = bld.bonusloto_id) LEFT JOIN `" . DB_PREFIX . "bonusloto_to_store` bl2s ON (bl.bonusloto_id = bl2s.bonusloto_id) WHERE bl.bonusloto_id = '" . (int)$bonusloto_id . "' AND bld.language_id = '" . (int)$this->config->get('config_language_id') . "' AND bl2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND bl.status = '1'");
		return $query->row;
	}

	public function getbonusloto() {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "bonusloto` bl LEFT JOIN `" . DB_PREFIX . "bonusloto_description` bld ON (bl.bonusloto_id = bld.bonusloto_id) LEFT JOIN `" . DB_PREFIX . "bonusloto_to_store` bl2s ON (bl.bonusloto_id = bl2s.bonusloto_id) WHERE bld.language_id = '" . (int)$this->config->get('config_language_id') . "' AND bl2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND bl.status = '1' ORDER BY bl.date_added DESC");
		return $query->rows;
	}

	public function getbonuslotoShorts($limit) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "bonusloto` bl LEFT JOIN `" . DB_PREFIX . "bonusloto_description` bld ON (bl.bonusloto_id = bld.bonusloto_id) LEFT JOIN `" . DB_PREFIX . "bonusloto_to_store` bl2s ON (bl.bonusloto_id = bl2s.bonusloto_id) WHERE bld.language_id = '" . (int)$this->config->get('config_language_id') . "' AND bl2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND bl.status = '1' ORDER BY bl.date_added DESC LIMIT " . (int)$limit); 
		return $query->rows;
	}

	public function getTotalbonusloto() {
     	$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "bonusloto` bl LEFT JOIN `" . DB_PREFIX . "bonusloto_to_store` bl2s ON (bl.bonusloto_id = bl2s.bonusloto_id) WHERE bl2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND bl.status = '1'");
		if ($query->row) {
			return $query->row['total'];
		} else {
			return FALSE;
		}
	}
	public function getCustomer($customer_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "customer` WHERE customer_id = '" . (int)$customer_id . "'");

		return $query->row;
	}

	public function addReward($customer_id, $description = 'Social post', $points = '', $order_id = 0) {
		$customer_info = $this->getCustomer($customer_id);
		if ($customer_info) { 
			$this->db->query("INSERT INTO `" . DB_PREFIX . "customer_reward` SET customer_id = '" . (int)$customer_id . "', order_id = '" . (int)$order_id . "', points = '" . (int)$points . "', description = '" . $this->db->escape($description) . "', date_added = NOW()");

		}
	}	
	public function getLastRewards($customer_id, $description = 'Social post' ,$start = 0, $limit = 1) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer_reward WHERE customer_id = '" . (int)$customer_id . "' and description = '" . $this->db->escape($description) . "' ORDER BY date_added DESC LIMIT " . (int)$start . "," . (int)$limit);
		if ($query->row) {
			return $query->row['date_added'];
		} else {
			return FALSE;
		}
	}
	public function deleteVipProduct($product_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "product` WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_attribute` WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_description` WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_discount` WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_filter` WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_image` WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_option` WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_option_value` WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_related` WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_related` WHERE related_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_reward` WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_special` WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_to_category` WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_to_download` WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_to_layout` WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_to_store` WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_profile` WHERE product_id = " . (int)$product_id);
		$this->db->query("DELETE FROM `" . DB_PREFIX . "review` WHERE product_id = '" . (int)$product_id . "'");

		$this->db->query("DELETE FROM `" . DB_PREFIX . "url_alias` WHERE query = 'product_id=" . (int)$product_id. "'");

		$this->cache->delete('product');
	}

	public function BonuslotoSortDateTime($array) {
		if (isset($array)) {
			function mysort($b,$c) { 
				$b1 = date_create($b['game_data'] . ' ' . $b['game_time']);
        	        	$b2 = strtotime($b1->format('Y-m-d H:i:s'));
				$c1 = date_create($c['game_data'] . ' ' . $c['game_time']);
        	        	$c2 = strtotime($c1->format('Y-m-d H:i:s'));
				return strcmp($b2, $c2); 
			}
			usort($array, 'mysort');
		}  
		if($this->config->get('bonusloto_timezone') !=''){
			date_default_timezone_set($this->config->get('bonusloto_timezone'));
		}
		foreach ($array as $key => $d) {
			if (isset($d['status'])) {
				$datefixed = date_create($d['game_data'] . ' ' .$d['game_time']);
	                	$datenow = date_create('now');
        	        	$timefixed = strtotime($datefixed->format('Y-m-d H:i:s'));
                		$timenow = strtotime($datenow->format('Y-m-d H:i:s'));
				if ($timefixed < $timenow ) {
                			unset($array[$key]['status']);
        	        	}
			}
		}
		return $array;
	}
}
?>
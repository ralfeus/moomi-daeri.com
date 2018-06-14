<?php
class ModelFidobonusloto extends \system\engine\Model {
	public function deletebonusloto($bonusloto_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "bonusloto` WHERE bonusloto_id = '" . (int)$bonusloto_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "bonusloto_description` WHERE bonusloto_id = '" . (int)$bonusloto_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "bonusloto_winner` WHERE bonusloto_id = '" . (int)$bonusloto_id . "'");
//		$this->db->query("DELETE FROM `" . DB_PREFIX . "url_alias` WHERE query = 'bonusloto_id=" . (int)$bonusloto_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "bonusloto_to_store` WHERE bonusloto_id = '" . (int)$bonusloto_id . "'");
		$this->cache->delete('bonusloto');
	}	

	public function getbonuslotoStory($bonusloto_id) {
		$query = $this->db->query("SELECT DISTINCT *, (SELECT keyword FROM `" . DB_PREFIX . "url_alias` WHERE query = 'bonusloto_id=" . (int)$bonusloto_id . "') AS keyword FROM " . DB_PREFIX . "bonusloto WHERE bonusloto_id = '" . (int)$bonusloto_id . "'");
		return $query->row;
	}

	public function getbonuslotoDescriptions($bonusloto_id) {
		$bonusloto_description_data = array();
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "bonusloto_description` WHERE bonusloto_id = '" . (int)$bonusloto_id . "'");
		foreach ($query->rows as $result) {
			$bonusloto_description_data[$result['language_id']] = array(
				'title'            => $result['title'],
				'meta_description' => $result['meta_description'],
				'description'      => $result['description']
			);
		}
		return $bonusloto_description_data;
	}

	public function getbonuslotoStores($bonusloto_id) {
		$bonuslotopage_store_data = array();
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "bonusloto_to_store` WHERE bonusloto_id = '" . (int)$bonusloto_id . "'");
		foreach ($query->rows as $result) {
			$bonuslotopage_store_data[] = $result['store_id'];
		}
		return $bonuslotopage_store_data;
	}

	public function getbonusloto() {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "bonusloto` b LEFT JOIN `" . DB_PREFIX . "bonusloto_description` bd ON (b.bonusloto_id = bd.bonusloto_id) LEFT JOIN `" . DB_PREFIX . "bonusloto_winner` bw ON (bd.bonusloto_id = bw.bonusloto_id) WHERE bd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY b.date_added");
		return $query->rows;
	}


	public function updateKeywordBonusloto($keyword) {

		$this->db->query("DELETE FROM `" . DB_PREFIX . "url_alias` WHERE query = 'information/bonusloto'");

		if ($keyword) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "url_alias` SET query = 'information/bonusloto', keyword = '" . $this->db->escape($keyword) . "'");
		}

	}

	public function getKeywordBonusloto() {
		$query = $this->db->query("SELECT keyword FROM `" . DB_PREFIX . "url_alias` WHERE query = 'information/bonusloto'");
		if(isset($query->row['keyword'])) {
			return $query->row['keyword'];
		} else {
			return;
		}
	}


	public function getTotalbonusloto() {
		$this->checkbonusloto();
     	$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "bonusloto`");
		return $query->row['total'];
	}	

	public function checkbonusloto() {
		$create_bonusloto = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "bonusloto` (`bonusloto_id` int(11) NOT NULL auto_increment, `status` int(1) NOT NULL default '0', `image` varchar(255) collate utf8_general_ci default NULL, `image_size` int(1) NOT NULL default '0', `date_added` datetime default NULL, PRIMARY KEY  (`bonusloto_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
		$this->db->query($create_bonusloto);
		$create_bonusloto_descriptions = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "bonusloto_description` (`bonusloto_id` int(11) NOT NULL default '0', `language_id` int(11) NOT NULL default '0', `title` varchar(64) collate utf8_general_ci NOT NULL default '', `meta_description` varchar(255) collate utf8_general_ci NOT NULL, `description` text collate utf8_general_ci NOT NULL, PRIMARY KEY  (`bonusloto_id`,`language_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
		$this->db->query($create_bonusloto_descriptions);
		$create_bonusloto_to_store = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "bonusloto_to_store` (`bonusloto_id` int(11) NOT NULL, `store_id` int(11) NOT NULL, PRIMARY KEY  (`bonusloto_id`, `store_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
		$this->db->query($create_bonusloto_to_store);
		$create_bonusloto_winner = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "bonusloto_winner` (`bonusloto_id` int(11) NOT NULL, `winner_last` int(11) DEFAULT NULL,`winner_name` varchar(255) DEFAULT NULL, `winner_id` int(11) NOT NULL,`winner_email` varchar(255) DEFAULT NULL,`winner_bonus` varchar(255) ,`winner_date` varchar(255) DEFAULT NULL, `winner_expir` datetime DEFAULT NULL, PRIMARY KEY (`bonusloto_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
		$this->db->query($create_bonusloto_winner);

		$query = $this->db->query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='" . DB_DATABASE . "' AND TABLE_NAME='" . DB_PREFIX . "bonusloto_winner' AND COLUMN_NAME='winner_last'");
		if ($query->row['COLUMN_NAME'] == "") {
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "bonusloto_winner` ADD COLUMN `winner_last` int(11) DEFAULT NULL");
		}
	}

	public function getCoupons($data = array()) {
		$sql = "SELECT coupon_id, name, code, discount, date_start, date_end, status FROM `" . DB_PREFIX . "coupon`";

		$sort_data = array(
			'name',
			'code',
			'discount',
			'date_start',
			'date_end',
			'status'
		);	

		if (!empty($data['filter_name'])) {
			$sql .= " WHERE name LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
		}

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
	public function addProduct($data) {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "product` SET model = '" . $this->db->escape($data['model']) . "', sku = '" . $this->db->escape($data['sku']) . "', upc = '" . $this->db->escape($data['upc']) . "', ean = '" . $this->db->escape($data['ean']) . "', jan = '" . $this->db->escape($data['jan']) . "', isbn = '" . $this->db->escape($data['isbn']) . "', mpn = '" . $this->db->escape($data['mpn']) . "', location = '" . $this->db->escape($data['location']) . "', quantity = '" . (int)$data['quantity'] . "', minimum = '" . (int)$data['minimum'] . "', subtract = '" . (int)$data['subtract'] . "', stock_status_id = '" . (int)$data['stock_status_id'] . "', date_available = '" . $this->db->escape($data['date_available']) . "', manufacturer_id = '" . (int)$data['manufacturer_id'] . "', shipping = '" . (int)$data['shipping'] . "', price = '" . (float)$data['price'] . "', points = '" . (int)$data['points'] . "', weight = '" . (float)$data['weight'] . "', weight_class_id = '" . (int)$data['weight_class_id'] . "', length = '" . (float)$data['length'] . "', width = '" . (float)$data['width'] . "', height = '" . (float)$data['height'] . "', length_class_id = '" . (int)$data['length_class_id'] . "', status = '" . (int)$data['status'] . "', tax_class_id = '" . $this->db->escape($data['tax_class_id']) . "', sort_order = '" . (int)$data['sort_order'] . "', date_added = NOW()");

		$product_id = $this->db->getLastId();

		if (isset($data['image'])) {
			$this->db->query("UPDATE `" . DB_PREFIX . "product` SET image = '" . $this->db->escape(html_entity_decode($data['image'], ENT_QUOTES, 'UTF-8')) . "' WHERE product_id = '" . (int)$product_id . "'");
		}

		foreach ($data['product_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "product_description` SET product_id = '" . (int)$product_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', description = '" . $this->db->escape($value['description']) . "', tag = '" . $this->db->escape($value['tag']) . "'");
		}

		if (isset($data['product_store'])) {
			foreach ($data['product_store'] as $store_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "product_to_store` SET product_id = '" . (int)$product_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

		if (isset($data['product_category'])) {
			foreach ($data['product_category'] as $category_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "product_to_category` SET product_id = '" . (int)$product_id . "', category_id = '" . (int)$category_id . "'");
			}
		}

		if ($data['keyword']) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "url_alias` SET query = 'product_id=" . (int)$product_id . "', keyword = '" . $this->db->escape($data['keyword']) . "'");
		}

		return $product_id;
	}

	public function editProduct($product_id, $data) {
		$this->db->query("UPDATE `" . DB_PREFIX . "product` SET  quantity = '" . (int)$data['quantity'] . "', price = '" . (float)$data['price'] . "', date_modified = NOW() WHERE product_id = '" . (int)$product_id . "'");
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

	public function BonuslotoSaveCron($data) {
               	$cronCommands   = array();
                $cronFolder     = dirname(DIR_APPLICATION) . '/cron/bonusloto/';
		if (isset($data['CronEnabled']) && $data['CronEnabled'] == 'on') {
			if (isset($data['WgetPath']) && $data['WgetPath'] =='') {
				$data['WgetPath'] = shell_exec('which wget');
			} 
			$cronUrl	= HTTP_CATALOG .'index.php?route=module/lototron/start&action=start-';
			$wgetParam = '';
			if (strpos(HTTP_CATALOG, 'https') !== false){
				$wgetParam = '--no-check-certificate';
			}
	
			$cronExec	= $data['WgetPath'] . ' ' . $wgetParam . ' -O - -q -t 1';
        	        $dateForSorting = array();
                	if (isset($data['PeriodicCronValue']) && isset($data['SecritCode'])) {
				if (isset($data['LogEnabler']) && $data['LogEnabler'] == 'on') {
					$cronLog	= '>>' . $cronFolder . 'cronbonusloto_' . $data['SecritCode'] . '.log && echo >>' . $cronFolder . 'cronbonusloto_' . $data['SecritCode'] . '.log';
				} else {
					$cronLog	= '>' . $cronFolder . $data['SecritCode'] . '.log 2>&1';
				}
	                	$cronCommands[] = $data['PeriodicCronValue'] . " " . $cronExec ." '" . $cronUrl . $data['SecritCode'] . "' " .$cronLog;
        	        }
                	if (isset($cronCommands)) {
	                    $cronCommands      = implode(PHP_EOL, $cronCommands);
        	            $currentCronBackup = shell_exec('crontab -l');
                	    $currentCronBackup = explode(PHP_EOL, $currentCronBackup);
	                    foreach ($currentCronBackup as $key => $command) {
        	                if (strpos($command, $data['SecritCode']) || empty($command) || $command == ' ' || $command == PHP_EOL) {
                	            unset($currentCronBackup[$key]);
	                        }
        	            }
                	    $currentCronBackup = implode(PHP_EOL, $currentCronBackup);
	                    file_put_contents($cronFolder . 'cron.txt', $currentCronBackup . PHP_EOL . $cronCommands . PHP_EOL);
	                }
		} else {
       	                $currentCronBackup = shell_exec('crontab -l');
                	$currentCronBackup = explode(PHP_EOL, $currentCronBackup);
	                foreach ($currentCronBackup as $key => $command) {
        	            if (strpos($command, $data['SecritCode']) || empty($command) || $command == ' ' || $command == PHP_EOL) {
                	        unset($currentCronBackup[$key]);
	                    }
        	        }
                	$currentCronBackup = implode(PHP_EOL, $currentCronBackup);
	                file_put_contents($cronFolder . 'cron.txt', $currentCronBackup . PHP_EOL);
		}
		exec('crontab -r');
		exec('crontab ' . $cronFolder . 'cron.txt');
	}

}
?>
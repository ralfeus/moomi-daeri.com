<?php
class ModelLocalisationGeoZone extends Model {
	public function addGeoZone($data) {

		$desc = $this->json_encode_cyr($data['description']);

		$this->db->query("INSERT INTO " . DB_PREFIX . "geo_zone SET name = '" . $this->db->escape($data['name']) . "', description = '" . $desc . "', date_added = NOW()");

		$geo_zone_id = $this->db->getLastId();
		
		if (isset($data['zone_to_geo_zone'])) {
			foreach ($data['zone_to_geo_zone'] as $value) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "zone_to_geo_zone SET country_id = '"  . (int)$value['country_id'] . "', zone_id = '"  . (int)$value['zone_id'] . "', geo_zone_id = '"  .(int)$geo_zone_id . "', date_added = NOW()");
			}
		}
		
		$this->cache->delete('geo_zone');
	}
	
	public function editGeoZone($geo_zone_id, $data) {

		/*print_r($data['description']);
		foreach ($data['description'] as $key => $value) {
			$data['description'][$key] = iconv('cp1251', 'utf-8', $value);
		}*/
		$desc = $this->json_encode_cyr($data['description']);
//print_r($data); die();
		$this->db->query("UPDATE " . DB_PREFIX . "geo_zone SET name = '" . $this->db->escape($data['name']) . "', description = '" . $desc . "', date_modified = NOW() WHERE geo_zone_id = '" . (int)$geo_zone_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$geo_zone_id . "'");
		
		if (isset($data['zone_to_geo_zone'])) {
			foreach ($data['zone_to_geo_zone'] as $value) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "zone_to_geo_zone SET country_id = '"  . (int)$value['country_id'] . "', zone_id = '"  . (int)$value['zone_id'] . "', geo_zone_id = '"  .(int)$geo_zone_id . "', date_added = NOW()");
			}
		}
		
		$this->cache->delete('geo_zone');
	}

	private function json_encode_cyr($str) {

		$arr_replace_utf = array('\u0410', '\u0430','\u0411','\u0431','\u0412','\u0432',

		'\u0413','\u0433','\u0414','\u0434','\u0415','\u0435','\u0401','\u0451','\u0416',

		'\u0436','\u0417','\u0437','\u0418','\u0438','\u0419','\u0439','\u041a','\u043a',

		'\u041b','\u043b','\u041c','\u043c','\u041d','\u043d','\u041e','\u043e','\u041f',

		'\u043f','\u0420','\u0440','\u0421','\u0441','\u0422','\u0442','\u0423','\u0443',

		'\u0424','\u0444','\u0425','\u0445','\u0426','\u0446','\u0427','\u0447','\u0428',

		'\u0448','\u0429','\u0449','\u042a','\u044a','\u042b','\u044b','\u042c','\u044c',

		'\u042d','\u044d','\u042e','\u044e','\u042f','\u044f');

		$arr_replace_cyr = array('А', 'а', 'Б', 'б', 'В', 'в', 'Г', 'г', 'Д', 'д', 'Е', 'е',

		'Ё', 'ё', 'Ж','ж','З','з','И','и','Й','й','К','к','Л','л','М','м','Н','н','О','о',

		'П','п','Р','р','С','с','Т','т','У','у','Ф','ф','Х','х','Ц','ц','Ч','ч','Ш','ш',

		'Щ','щ','Ъ','ъ','Ы','ы','Ь','ь','Э','э','Ю','ю','Я','я');

		$str1 = json_encode($str);

		$str2 = str_replace($arr_replace_utf,$arr_replace_cyr,$str1);

		return $str2;

	}
	
	public function deleteGeoZone($geo_zone_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "geo_zone WHERE geo_zone_id = '" . (int)$geo_zone_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$geo_zone_id . "'");

		$this->cache->delete('geo_zone');
	}
	
	public function getGeoZone($geo_zone_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "geo_zone WHERE geo_zone_id = '" . (int)$geo_zone_id . "'");
		
		return $query->row;
	}

	public function getGeoZones($data = array()) {
		if ($data) {
			$sql = "SELECT * FROM " . DB_PREFIX . "geo_zone";
	
			$sort_data = array(
				'name',
				'description'
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
		} else {
			$geo_zone_data = $this->cache->get('geo_zone');

			if (!$geo_zone_data) {
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "geo_zone ORDER BY name ASC");
	
				$geo_zone_data = $query->rows;
			
				$this->cache->set('geo_zone', $geo_zone_data);
			}
			
			return $geo_zone_data;				
		}
	}
	
	public function getTotalGeoZones() {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "geo_zone");
		
		return $query->row['total'];
	}	
	
	public function getZoneToGeoZones($geo_zone_id) {	
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$geo_zone_id . "'");
		
		return $query->rows;	
	}		

	public function getTotalZoneToGeoZoneByGeoZoneId($geo_zone_id) {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$geo_zone_id . "'");
		
		return $query->row['total'];
	}
	
	public function getTotalZoneToGeoZoneByCountryId($country_id) {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "zone_to_geo_zone WHERE country_id = '" . (int)$country_id . "'");
		
		return $query->row['total'];
	}	
	
	public function getTotalZoneToGeoZoneByZoneId($zone_id) {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "zone_to_geo_zone WHERE zone_id = '" . (int)$zone_id . "'");
		
		return $query->row['total'];
	}		
}
?>
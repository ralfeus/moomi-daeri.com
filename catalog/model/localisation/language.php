<?php
class ModelLocalisationLanguage extends Model {
	public function getLanguageInfo($language_id) {
		$query = $this->getDb()->query("SELECT * FROM language WHERE language_id = '" . (int)$language_id . "'");
		
		return $query->row;	
	}

	public function getLanguages() {
		$language_data = $this->cache->get('language');
		
		if (!$language_data) {		
			$language_data = array();
			
			$query = $this->getDb()->query("SELECT * FROM language ORDER BY sort_order, name");
		
    		foreach ($query->rows as $result) {
      			$language_data[$result['language_id']] = array(
        			'language_id' => $result['language_id'],
        			'name'        => $result['name'],
        			'code'        => $result['code'],
					'locale'      => $result['locale'],
					'image'       => $result['image'],
					'directory'   => $result['directory'],
					'filename'    => $result['filename'],
					'sort_order'  => $result['sort_order'],
					'status'      => $result['status']
      			);
    		}	
			
			$this->cache->set('language', $language_data);
            $this->log->write("Language data is cached. Data is: " . print_r($language_data, true));
		}
		
		return $language_data;	
	}
}
?>
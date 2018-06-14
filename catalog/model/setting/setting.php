<?php 
class ModelSettingSetting extends \system\engine\Model {
	public function getSetting($group, $store_id = 0) {
		$data = array(); 
		
		$query = $this->getDb()->query("SELECT * FROM setting WHERE store_id = '" . (int)$store_id . "' AND `group` = '" . $this->getDb()->escape($group) . "'");
		
		foreach ($query->rows as $result) {
			if (!$result['serialized']) {
				$data[$result['key']] = $result['value'];
			} else {
				$data[$result['key']] = unserialize($setting['value']);
			}
		}

		return $data;
	}
}
?>
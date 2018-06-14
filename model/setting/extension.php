<?php
class ModelSettingExtension extends \system\engine\Model {
    function getExtensions($type, $installedOnly = true, $enabledOnly = false)
    {
        $result = array();
        if ($installedOnly) {
            $query = $this->getDb()->query("
				SELECT code
				FROM extension
				WHERE `type` = :type
				", [ ':type' => $type ]
			);
            foreach ($query->rows as $extension)
                if (!$enabledOnly || $this->config->get($extension['code'] . '_status'))
                    $result[] = $extension['code'];
            return $result;
        }
    }

	public function getInstalled($type) {
		$extension_data = array();
		
		$query = $this->getDb()->query("SELECT * FROM extension WHERE `type` = :type", [":type" => $type]);
		
		foreach ($query->rows as $result) {
			$extension_data[] = $result['code'];
		}
		
		return $extension_data;
	}
	
	public function install($type, $code) {
		$this->getDb()->query("INSERT INTO extension SET `type` = '" . $this->getDb()->escape($type) . "', `code` = '" . $this->getDb()->escape($code) . "'");
	}
	
	public function uninstall($type, $code) {
		$this->getDb()->query("DELETE FROM extension WHERE `type` = '" . $this->getDb()->escape($type) . "' AND `code` = '" . $this->getDb()->escape($code) . "'");
	}
}
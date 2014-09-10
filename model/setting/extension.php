<?php
class ModelSettingExtension extends Model {
    function getExtensions($type, $installedOnly = true, $enabledOnly = false)
    {
        $result = array();
        if ($installedOnly)
        {
            $query = $this->db->query("SELECT code FROM extension WHERE `type` = '" . $this->db->escape($type) . "'");
            foreach ($query->rows as $extension)
                if (!$enabledOnly || $this->config->get($extension['code'] . '_status'))
                    $result[] = $extension['code'];
            return $result;
        }
    }

	public function getInstalled($type) {
		$extension_data = array();
		
		$query = $this->db->query("SELECT * FROM extension WHERE `type` = '" . $this->db->escape($type) . "'");
		
		foreach ($query->rows as $result) {
			$extension_data[] = $result['code'];
		}
		
		return $extension_data;
	}
	
	public function install($type, $code) {
		$this->db->query("INSERT INTO extension SET `type` = '" . $this->db->escape($type) . "', `code` = '" . $this->db->escape($code) . "'");
	}
	
	public function uninstall($type, $code) {
		$this->db->query("DELETE FROM extension WHERE `type` = '" . $this->db->escape($type) . "' AND `code` = '" . $this->db->escape($code) . "'");
	}
}
?>
<?php
namespace model\setting;
use model\DAO;

class ExtensionDAO extends DAO {
    /**
     * @param string $type
     * @param bool $installedOnly
     * @param bool $enabledOnly
     * @return DAO[]
     */
    function getExtensions($type, $installedOnly = true, $enabledOnly = true) {
        $result = array();
        if ($installedOnly) {
            $query = $this->getDb()->query("
                SELECT *
                FROM extension
                WHERE `type` = ?
                ", array("s:$type")
            );
            foreach ($query->rows as $extension) {
                if (!$enabledOnly || $this->config->get($extension['code'] . '_status')) {
                    $extension = $extension['class']::getInstance();
                    $result[] = $extension;
                }
            }
            return $result;
        }
    }

	public function install($type, $code) {
		$this->db->query("INSERT INTO extension SET `type` = '" . $this->db->escape($type) . "', `code` = '" . $this->db->escape($code) . "'");
	}
	
	public function uninstall($type, $code) {
		$this->db->query("DELETE FROM extension WHERE `type` = '" . $this->db->escape($type) . "' AND `code` = '" . $this->db->escape($code) . "'");
	}
}
?>
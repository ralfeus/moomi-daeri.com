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
		$this->getDb()->query("INSERT INTO extension SET `type` = '" . $this->getDb()->escape($type) . "', `code` = '" . $this->getDb()->escape($code) . "'");
	}
	
	public function uninstall($type, $code) {
		$this->getDb()->query("DELETE FROM extension WHERE `type` = '" . $this->getDb()->escape($type) . "' AND `code` = '" . $this->getDb()->escape($code) . "'");
	}
}
?>
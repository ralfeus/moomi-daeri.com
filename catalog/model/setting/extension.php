<?php
class ModelSettingExtension extends \system\engine\Model {
	function getExtensions($type) {
		$query = $this->getDb()->query("SELECT * FROM extension WHERE `type` = '" . $this->getDb()->escape($type) . "'");

		return $query->rows;
	}
}
?>
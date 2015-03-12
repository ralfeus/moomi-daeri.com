<?php
class ModelSettingExtension extends Model {
	function getExtensions($type) {
		$query = $this->getDb()->query("SELECT * FROM extension WHERE `type` = '" . $this->getDb()->escape($type) . "'");

		return $query->rows;
	}
}
?>
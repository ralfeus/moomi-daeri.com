<?php

class ModelLocalisationDescription extends \system\engine\Model {
	function getDescription($geo_zone_id = null, $schipping_name = '') {
		if(!is_null($geo_zone_id)) {
			$query = "SELECT * FROM geo_zone WHERE geo_zone_id = '" . $geo_zone_id . "'";
		}
		else {
			$query = "SELECT * FROM geo_zone WHERE name = '" . $schipping_name . "'";
		}
		$result = $this->getDb()->query($query);
		return $result->rows;
	}
}

?>
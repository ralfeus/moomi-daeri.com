<?php

class ModelShippingDescription extends Model {
	function getDescription($geo_zone_id = null, $schipping_name = '') {
		if(!is_null($geo_zone_id)) {
			$query = "SELECT * FROM " . DB_PREFIX . "geo_zone WHERE geo_zone_id = '" . $geo_zone_id . "'";
		}
		else {
			$query = "SELECT * FROM " . DB_PREFIX . "geo_zone WHERE name = '" . $schipping_name . "'";
		}
		$result = $this->db->query($query);
		return $result->rows;
	}
}

?>
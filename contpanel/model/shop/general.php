<?php
class ModelShopGeneral extends Model {
	public function addHoliday($data) {
		
		$this->db->query("INSERT INTO shop_holiday SET start = '" . $this->db->escape($data['start']) . "', end = '" . $this->db->escape($data['end']) . "', name = '" . $this->db->escape(strip_tags($data['name'])) . "'");

		return $this->db->getLastId();

	}
	
	public function deleteHoliday($holiday_id) {
		
		$this->db->query("DELETE FROM shop_holiday WHERE holiday_id = '" . $holiday_id . "'");

	}

	public function getAllHolidays() {
		
		$query = $this->db->query("SELECT * FROM shop_holiday");

		return $query->rows;
	}

}
?>
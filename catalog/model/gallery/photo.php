<?php
class ModelGalleryPhoto extends Model {
	public function addPhoto($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "gallery_photo SET name = '" . $data['name'] . "', `description` = '" . $data['description'] . "', `path` = '" . $data['path'] . "', uploaded_at = '" . $data['date'] . "'");
	
		$category_id = $this->db->getLastId();
	}
}
?>  
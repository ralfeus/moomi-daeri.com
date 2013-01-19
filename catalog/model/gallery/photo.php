<?php
class ModelGalleryPhoto extends Model {
	public function addPhoto($data) {
		
		$this->db->query("INSERT INTO " . DB_PREFIX . "gallery_photo SET customer_id = " . $this->customer->getId() . ", name = '" . $data['name'] . "', `description` = '" . $data['description'] . "', `path` = '" . $data['path'] . "', uploaded_at = '" . $data['date'] . "'");
	
	}

	public function addVote($data) {
		
		$query = "INSERT INTO " . DB_PREFIX . " gallery_photo_voting SET "; 
		
		if($data['photoType'] == 'gallery_photo') {
			$query .= "photo_id = " . $data['photoID'];
		}
		else {
			$query .= "review_image_id = " . $data['photoID'];
		}
		
		$query .= ", stars = '" . $data['stars'] . "', `comment` = '" . $data['comment'] . "', created_at = '" . $data['date'] . "'";
		$this->db->query($query);

		return true;
	
	}

	/*public function getAllApprovedPhotos(){
		
		$query = "SELECT * FROM " . DB_PREFIX . " gallery_photo WHERE approved_at IS NOT NULL AND approved_at != '0000-00-00'"; 
		$result = $this->db->query($query);
	
	}*/
}
?>  
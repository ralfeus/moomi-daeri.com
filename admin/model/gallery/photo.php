<?php
class ModelGalleryPhoto extends Model {
	public function addReview($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "review SET author = '" . $this->db->escape($data['author']) . "', product_id = '" . $this->db->escape($data['product_id']) . "', text = '" . $this->db->escape(strip_tags($data['text'])) . "', rating = '" . (int)$data['rating'] . "', status = '" . (int)$data['status'] . "', date_added = NOW()");
	}
	
	public function editReview($review_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "review SET author = '" . $this->db->escape($data['author']) . "', product_id = '" . $this->db->escape($data['product_id']) . "', text = '" . $this->db->escape(strip_tags($data['text'])) . "', rating = '" . (int)$data['rating'] . "', status = '" . (int)$data['status'] . "', date_added = NOW() WHERE review_id = '" . (int)$review_id . "'");
	}
	
	public function deleteReview($review_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "review WHERE review_id = '" . (int)$review_id . "'");
	}
	
	public function getReview($review_id) {
		$query = $this->db->query("SELECT DISTINCT *, (SELECT pd.name FROM " . DB_PREFIX . "product_description pd WHERE pd.product_id = r.product_id AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS product FROM " . DB_PREFIX . "review r WHERE r.review_id = '" . (int)$review_id . "'");
		
		return $query->row;
	}


	public function getPhotos($data = array()) {
		
		$sql = "SELECT gallery_photo.*, customer.nickname FROM gallery_photo LEFT JOIN customer USING(customer_id) WHERE approved_at IS NULL OR approved_at='0000-00-00'";																																							  
																																							  
		$query = $this->db->query($sql);																																			
		
		return $query->rows;	
	}
	
	public function getTotalPhotos() {
		
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "gallery_photo WHERE approved_at IS NULL OR approved_at='0000-00-00'");
		
		return $query->row['total'];
	}
	
	public function getTotalReviewsAwaitingApproval() {
		
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "review WHERE status = '0'");
		
		return $query->row['total'];
	}	

	public function getVoteList() {
		$sql = "SELECT gallery_photo_voting.*, gallery_photo.path, review_images.image_path FROM gallery_photo_voting LEFT JOIN gallery_photo USING(photo_id) LEFT JOIN review_images USING(review_image_id) WHERE gallery_photo_voting.approved_at IS NULL OR gallery_photo_voting.approved_at='0000-00-00'";																																							  
																																							  
		$query = $this->db->query($sql);																																			
		
		return $query->rows;
	}
}
?>
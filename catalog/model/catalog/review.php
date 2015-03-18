<?php
class ModelCatalogReview extends Model {		
	public function addReview($product_id, $data) {
		$this->getDb()->query("
		    INSERT INTO review
		    SET
		        author = '" . $this->getDb()->escape($data['name']) . "',
		        customer_id = '" . (int)$this->customer->getId() . "',
		        product_id = '" . (int)$product_id . "',
		        text = '" . $this->getDb()->escape(strip_tags($data['text'])) . "',
		        rating = '" . (int)$data['rating'] . "',
		        date_added = NOW()
        ");
        if (!empty($data['imageFilePath']))
            $this->addReviewImages($this->getDb()->getLastId(), $data['imageFilePath']);
	}

    private function addReviewImages($reviewId, $imagePaths)
    {
        $this->log->write(print_r($imagePaths, true));
        if (!file_exists(DIR_IMAGE . '/reviews'))
            mkdir(DIR_IMAGE . '/reviews');
        foreach ($imagePaths as $imagePath)
        {
            $fullFilePath = DIR_IMAGE . $imagePath;
            $this->log->write($fullFilePath);
            if (file_exists($fullFilePath))
            {
                $this->log->write(basename($fullFilePath));
                copy($fullFilePath, DIR_IMAGE . '/reviews/' . basename($fullFilePath));
                $this->getDb()->query("
                    INSERT INTO review_images
                    SET
                        review_id = " . (int)$reviewId . ",
                        image_path = '" . $this->getDb()->escape('/reviews/' . basename($fullFilePath)) . "'
                ");
                unlink($fullFilePath);
            }
        }
    }

    public function getReviewImages($reviewId)
    {
        $query = $this->getDb()->query("
            SELECT *
            FROM review_images
            WHERE review_id = " . (int)$reviewId
        );
        $result = array();
        foreach ($query->rows as $row)
            $result[] = $row['image_path'];
        return $result;
    }
		
	public function getReviewsByProductId($product_id, $start = 0, $limit = 20) {
		$query = $this->getDb()->query("
		    SELECT r.review_id, r.author, r.rating, r.text, p.product_id, pd.name, p.price, p.image, r.date_added
		    FROM
		        review r
		        LEFT JOIN product p ON (r.product_id = p.product_id)
		        LEFT JOIN product_description pd ON (p.product_id = pd.product_id)
            WHERE
                p.product_id = '" . (int)$product_id . "'
                AND p.date_available <= '" . date('Y-m-d H:00:00') . "'
                AND p.status = '1'
                AND r.status = '1'
                AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'
            ORDER BY r.date_added DESC
            LIMIT " . (int)$start . "," . (int)$limit
        );
		
		return $query->rows;
	}
	
	public function getAverageRating($product_id) {
		$query = $this->getDb()->query("SELECT AVG(rating) AS total FROM review WHERE status = '1' AND product_id = '" . (int)$product_id . "' GROUP BY product_id");
		
		if (isset($query->row['total'])) {
			return (int)$query->row['total'];
		} else {
			return 0;
		}
	}	
	
	public function getTotalReviews() {
		$query = $this->getDb()->query("
		    SELECT COUNT(*) AS total
            FROM
                review r
                LEFT JOIN product p ON (r.product_id = p.product_id)
            WHERE p.date_available <= '" . date('Y-m-d H:00:00') . "'
            AND p.status = '1'
            AND r.status = '1'
        ");
		
		return $query->row['total'];
	}

	public function getTotalReviewsByProductId($product_id) {
		$query = $this->getDb()->query("
		    SELECT COUNT(*) AS total
		    FROM
		        review r
		        LEFT JOIN product p ON (r.product_id = p.product_id)
		        LEFT JOIN product_description pd ON (p.product_id = pd.product_id)
            WHERE
                p.product_id = '" . (int)$product_id . "'
                AND p.date_available <= '" . date('Y-m-d H:00:00') . "'
                AND p.status = '1'
                AND r.status = '1'
                AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'
        ");
		
		return $query->row['total'];
	}
}
?>
<?php
class ModelGalleryPhoto extends Model {
	public function addPhoto($data) {
		
		$this->db->query("INSERT INTO gallery_photo SET customer_id = " . $this->customer->getId() . ", name = '" . $data['name'] . "', `description` = '" . $data['description'] . "', `path` = '" . $data['path'] . "', uploaded_at = '" . $data['date'] . "'");
	
	}

	public function addVote($data) {
		
		$query = "INSERT INTO  gallery_photo_voting SET ";
		
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

	public function getAllApprovedPhotos()
  {
   
    $query = "SELECT gallery_photo.*, vote.avg_vote FROM gallery_photo LEFT JOIN (SELECT photo_id, SUM(stars)/COUNT(*) AS avg_vote FROM gallery_photo_voting WHERE photo_id IS NOT NULL && photo_id != '' AND approved_at IS NOT NULL AND approved_at != '0000-00-00' GROUP BY photo_id) AS vote USING(photo_id) WHERE approved_at IS NOT NULL AND approved_at != '0000-00-00' ORDER BY uploaded_at DESC";
    $result = $this->db->query($query);

    if(count($result->rows) > 0) {
      foreach ($result->rows as $row) {
        $filePath = DIR_IMAGE . $row['path'];
        $size = getimagesize($filePath);
        $w = $size[0];
        $h = $size[1];

        if($h <= $w) {
          $photoType = "horizont";
        }
        else {
          $photoType = "vertical";
        }
        $data['images'][] = array('path' => HTTP_IMAGE . $row['path'], 'date' => $row['uploaded_at'], 'photo_id' => $row['photo_id'], 'photo_type' => 'gallery_photo', 'iframe_type' => $photoType, 'avg_vote' => $row['avg_vote']);
      }
    }
        

    $query = "SELECT review.*, review_images.image_path, review_images.review_image_id, vote.avg_vote FROM review JOIN review_images USING(review_id)  LEFT JOIN (SELECT review_image_id, SUM(stars)/COUNT(*) AS avg_vote FROM gallery_photo_voting WHERE review_image_id IS NOT NULL && review_image_id != '' AND approved_at IS NOT NULL AND approved_at != '0000-00-00' GROUP BY review_image_id) AS vote USING(review_image_id)";
    $result = $this->db->query($query);
    
    if(count($result->rows) > 0) {
      foreach ($result->rows as $row) {
        $row['image_path'] = substr($row['image_path'], 1);
        $filePath = DIR_IMAGE . $row['image_path'];
        //print_r($filePath); die();
        if(!is_dir($filePath) && file_exists($filePath)) {
          $size = getimagesize($filePath);
          $w = $size[0];
          $h = $size[1];

          if($h <= $w) {
            $photoType = "horizont";
          }
          else {
            $photoType = "vertical";
          }
          $data['images'][] = array('path' => HTTP_IMAGE . 'reviews/' . basename($filePath), 'date' => $row['date_added'], 'photo_id' => $row['review_image_id'], 'photo_type' => 'review_image', 'iframe_type' => $photoType, 'avg_vote' => $row['avg_vote']);
        }
      }
    }

    $sortedPhotos = $this->sortImages($data['images']);

    $data['total_photos'] = count($sortedPhotos);
    
    $page = 1;
    
    return array_slice($sortedPhotos, ($page-1)*GALLERY_PAGE_SIZE, GALLERY_PAGE_SIZE);
  }

  private function sortImages($arr) {
    usort($arr, "compareImages");
    return $arr;
  }
}

function compareImages($a, $b)
{
  $atime = strtotime($a['date']);
  $btime = strtotime($b['date']);

  if ($atime == $btime) {
    return 0;
  }
  return ($atime > $btime) ? -1 : 1;
}
?>  
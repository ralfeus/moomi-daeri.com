<?php
/**
 * Created by PhpStorm.
 * User: ralfeus
 * Date: 26.07.2016
 * Time: 12:25
 */

namespace model\gallery;

use model\DAO;

class PhotoDAO extends DAO {
    public function addPhoto($data) {

        $this->getDb()->query("INSERT INTO gallery_photo SET customer_id = " . $this->getCurrentCustomer()->getId() . ", name = '" . $data['name'] . "', `description` = '" . $data['description'] . "', `path` = '" . $data['path'] . "', uploaded_at = '" . $data['date'] . "'");

    }

    /**
     * @param array $data
     * @return bool
     */
    public function addVote($data) {
        $field = $data['photoType'] == 'gallery_photo' ? "photo_id" : "review_image_id";
        $this->getDb()->query("INSERT INTO  gallery_photo_voting 
            (`$field`, stars, `comment`, created_at)
            VALUES (:photo, :stars, :comment, NOW())
            ", [
                ':photo' => $data['photoID'],
                ':stars' => $data['stars'],
                ':comment' => $data['comment']
            ]
        );

        return true;
    }

    public function getAllApprovedPhotos() {
        $query = "SELECT gallery_photo.*, vote.avg_vote FROM gallery_photo LEFT JOIN (SELECT photo_id, SUM(stars)/COUNT(*) AS avg_vote FROM gallery_photo_voting WHERE photo_id IS NOT NULL && photo_id != '' AND approved_at IS NOT NULL AND approved_at != '0000-00-00' GROUP BY photo_id) AS vote USING(photo_id) WHERE approved_at IS NOT NULL AND approved_at != '0000-00-00' ORDER BY uploaded_at DESC";
        $result = $this->getDb()->query($query);
        $data['images'] = array();

        if(count($result->rows) > 0) {
            foreach ($result->rows as $row) {
                $filePath = DIR_IMAGE . $row['path'];
                if (file_exists($filePath)) {
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
        }


        $query = "SELECT review.*, review_images.image_path, review_images.review_image_id, vote.avg_vote FROM review JOIN review_images USING(review_id)  LEFT JOIN (SELECT review_image_id, SUM(stars)/COUNT(*) AS avg_vote FROM gallery_photo_voting WHERE review_image_id IS NOT NULL && review_image_id != '' AND approved_at IS NOT NULL AND approved_at != '0000-00-00' GROUP BY review_image_id) AS vote USING(review_image_id)";
        $result = $this->getDb()->query($query);

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
        usort(
            $arr,
            function ($a, $b) {
                $atime = strtotime($a['date']);
                $btime = strtotime($b['date']);

                if ($atime == $btime) {
                    return 0;
                }
                return ($atime > $btime) ? -1 : 1;
            }
        );
        return $arr;
    }
}


<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dev
 * Date: 26.12.12
 * Time: 20:12
 * To change this template use File | Settings | File Templates.
 */
class ControllerProductGallery extends Controller {
    private function getData() {
        $this->data['files'] = array();
        $this->data['heading_title'] = $this->language->get('heading_title');
        $this->data['gallery_add_photo'] = $this->language->get('gallery_add_photo');
        $this->data['text_gifts_for_photos'] = $this->language->get('text_gifts_for_photos');
//        $this->log->write(print_r(glob(DIR_IMAGE . 'reviews/*'), true));

        $result = $this->getDb()->query(<<<SQL
            SELECT gallery_photo.*, vote.avg_vote
            FROM
                gallery_photo
                LEFT JOIN (
                    SELECT photo_id, SUM(stars)/COUNT(*) AS avg_vote
                    FROM gallery_photo_voting
                    WHERE
                        photo_id IS NOT NULL && photo_id != ''
                        AND approved_at IS NOT NULL
                        AND approved_at != '0000-00-00'
                    GROUP BY photo_id
                ) AS vote
            USING(photo_id)
            WHERE approved_at IS NOT NULL AND approved_at != '0000-00-00'
            ORDER BY uploaded_at DESC
SQL
        );

        if(count($result->rows) > 0) {
            foreach ($result->rows as $row) {
                $filePath = DIR_IMAGE . $row['path'];
                if (!file_exists($filePath)) {
                    $filePath = DIR_IMAGE . 'no_image.jpg';
                }
                $size = getimagesize($filePath);
                $w = $size[0];
                $h = $size[1];

                if($h <= $w) {
                    $photoType = "horizont";
                } else {
                    $photoType = "vertical";
                }
                $this->data['images'][] = array('path' => HTTP_IMAGE . $row['path'], 'date' => $row['uploaded_at'], 'photo_id' => $row['photo_id'], 'photo_type' => 'gallery_photo', 'iframe_type' => $photoType, 'avg_vote' => $row['avg_vote']);
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
            $this->data['images'][] = array('path' => HTTP_IMAGE . 'reviews/' . basename($filePath), 'date' => $row['date_added'], 'photo_id' => $row['review_image_id'], 'photo_type' => 'review_image', 'iframe_type' => $photoType, 'avg_vote' => $row['avg_vote']);
          }
        }
      }

      $sortedPhotos = $this->sortImages($this->data['images']);

      $this->data['total_photos'] = count($sortedPhotos);

      $page = isset($_GET['page']) ? $this->request->get['page'] : 1;

      $this->data['images'] = array_slice($sortedPhotos, ($page-1)*GALLERY_PAGE_SIZE, GALLERY_PAGE_SIZE);
    }

    private function sortImages($arr) {
      usort($arr, "compareImages");
      return $arr;
    }

    public function index()
    {
      $this->getData();

      /// Set interface
      $this->setBreadcrumbs();
      $this->template = 'default/template/product/gallery.php';
      $this->children = array(
          'common/column_left',
          'common/column_right',
          'common/footer',
          'common/header'
      );

      //$this->load->model('gallery/photo');

      $pagination = new Pagination();
      $pagination->total = $this->data['total_photos'];
      $pagination->page = isset($_GET['page']) ? $_GET['page'] : 1;
      $pagination->limit = GALLERY_PAGE_SIZE;
      $pagination->text = $this->language->get('text_pagination');
      $pagination->url = $this->url->link('product/gallery', 'page={page}');

      $this->data['pagination'] = $pagination->render();

      $this->getResponse()->setOutput($this->render());
    }

    protected function setBreadcrumbs()
    {
        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home'),
            'separator' => "::"
        );
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('GALLERY'),
            'href'      => $this->url->link('product/gallery'),
            'separator' => "::"
        );
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
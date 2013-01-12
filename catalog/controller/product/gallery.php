<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dev
 * Date: 26.12.12
 * Time: 20:12
 * To change this template use File | Settings | File Templates.
 */
class ControllerProductGallery extends Controller
{
    private function getData()
    {//print_r(glob(DIR_IMAGE . 'reviews/*')); die();
        $this->data['files'] = array(); //print_r($this->language); die();
        $this->data['heading_title'] = $this->language->get('heading_title');
        $this->data['gallery_add_photo'] = $this->language->get('gallery_add_photo');
        $this->log->write(print_r(glob(DIR_IMAGE . 'reviews/*'), true));
        
        $query = "SELECT * FROM gallery_photo WHERE approved_at IS NOT NULL AND approved_at != '0000-00-00' ORDER BY uploaded_at DESC";
        $result = $this->db->query($query);

        foreach ($result->rows as $row) {
           $this->data['images'][] = HTTP_IMAGE . $row['path'];
        }
            

        $query = "SELECT review.*, review_images.image_path FROM review JOIN review_images USING(review_id)";
        $result = $this->db->query($query);
        
        foreach ($result->rows as $row) {
            $row['image_path'] = substr($row['image_path'], 1);
            $filePath = DIR_IMAGE . $row['image_path'];
            //print_r($filePath); die();
            if(!is_dir($filePath) && file_exists($filePath)) {//print_r($filePath); die();
                $this->data['images'][] = HTTP_IMAGE . 'reviews/' . basename($filePath);
            }
        }

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
        $this->response->setOutput($this->render());
    }

    private function setBreadcrumbs()
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

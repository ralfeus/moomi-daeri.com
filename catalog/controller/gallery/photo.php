<?php  
class ControllerGalleryPhoto extends Controller {
	public function index() {
		$this->document->setTitle($this->config->get('config_title'));
		$this->document->setDescription($this->config->get('config_meta_description'));

		$this->data['heading_title'] = $this->config->get('config_title');
		
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/common/home.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/common/home.tpl';
		} else {
			$this->template = 'default/template/common/home.tpl';
		}
		
		$this->children = array(
			'common/column_left',
			'common/column_right',
			'common/content_top',
			'common/content_bottom',
			'common/footer',
			'common/header'
		);

    $this->response->setOutput($this->render());
	}

	public function addPhoto() {

    if (!$this->customer->isLogged()) {
      $this->session->data['redirect'] = $this->url->link('account/account', '', 'SSL');
  
      $this->redirect($this->url->link('account/login', '', 'SSL'));
    }
		
		$this->language->load('gallery/general');

		$this->data = $this->getGalleryGeneralData();
		
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/gallery/photo.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/gallery/photo.tpl';
		} else {
			$this->template = 'default/template/gallery/photo.tpl';
		}
		
		$this->children = array(
			'common/column_left',
			'common/column_right',
			'common/content_top',
			'common/content_bottom',
			'common/footer',
			'common/header'
		);
						
		$this->response->setOutput($this->render());
  }

  public function uploadPhoto() {
    if (!$this->customer->isLogged()) {
      $this->session->data['redirect'] = $this->url->link('account/account', '', 'SSL');
  
      $this->redirect($this->url->link('account/login', '', 'SSL'));
    }

  	$allowedExtension = array('jpeg', 'jpg', 'png', 'gif');

  	$photoName = isset($_POST['galery_photo_name']) ? $_POST['galery_photo_name'] : '';
  	$photoDescription = isset($_POST['galery_photo_description']) ? $_POST['galery_photo_description'] : '';

    $uniqFileName = uniqid() . "_" . $_FILES['imageFile']['name'];
  	$tempFileName = DIR_IMAGE . "gallery/temp/" . $uniqFileName;
  	$fileName = DIR_IMAGE . "gallery/" . $uniqFileName;
    $wattermark = imagecreatefrompng(DIR_IMAGE . "gallery/wattermark.png");
  	move_uploaded_file($_FILES['imageFile']['tmp_name'], $tempFileName);

  	$pathInfo = pathinfo($tempFileName);
  	$pathInfo['extension'] = strtolower($pathInfo['extension']);

  	$this->language->load('gallery/general');

  	$this->children = array(
			'common/column_left',
			'common/column_right',
			'common/content_top',
			'common/content_bottom',
			'common/footer',
			'common/header'
		);

  	if(!in_array($pathInfo['extension'], $allowedExtension)){
  		
  		$this->data = $this->getGalleryGeneralData();

  		$this->data['galery_photo_error'] = $this->language->get('galery_photo_error_not_alloed');
  		$this->data['galery_photo_post_name'] = $photoName;
  		$this->data['galery_photo_post_description'] = $photoDescription;

  		$this->template = $this->config->get('config_template') . '/template/gallery/photo.tpl';

  		unlink($tempFileName);
  		
  		$this->response->setOutput($this->render());
  	}
  	else {
  		$size = getimagesize($tempFileName);
  		$w = $size[0];
      $h = $size[1];

      if($h <= $w) {

      	$toWidth = 640;
        $toHeight = 480;

      }
      else {

      	$toWidth = 480;
        $toHeight = 640;

      }

      switch ($pathInfo['extension']) {
      	case 'jpeg':
      	case 'jpg':
      		$image = imagecreatefromjpeg($tempFileName);
      		break;
      	
      	case 'png':
      		$image = imagecreatefrompng($tempFileName);
      		break;

      	case 'gif':
      		$image = imagecreatefromgif($tempFileName);
      		break;
      }

      $resizedImage = imagecreatetruecolor($toWidth, $toHeight);
    	imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $toWidth, $toHeight, $w, $h);

      $marge_right = 10;
      $marge_bottom = 10;
      $sx = imagesx($wattermark);
      $sy = imagesy($wattermark);

      imagecopy($resizedImage, $wattermark, imagesx($resizedImage) - $sx - $marge_right, imagesy($resizedImage) - $sy - $marge_bottom, 0, 0, imagesx($wattermark), imagesy($wattermark));

    	switch ($pathInfo['extension']) {
      	case 'jpeg':
      	case 'jpg':
      		imagejpeg($resizedImage, $fileName, 75);
      		break;
      	
      	case 'png':
      		imagepng($resizedImage, $fileName);
      		break;

      	case 'gif':
      		imagegif($resizedImage, $fileName);
      		break;
      }

      unlink($tempFileName);

      $modelData = array();
      $modelData['name'] = $photoName;
      $modelData['description'] = $photoDescription;
      $modelData['path'] = "gallery/" . $uniqFileName;
      $modelData['date'] = date("Y-m-d H:i:s");

      $this->load->model('gallery/photo');
      $this->model_gallery_photo->addPhoto($modelData);

      $this->language->load('gallery/general');

      $this->data = $this->getGalleryGeneralData();
      
      if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/gallery/success.tpl')) {
        $this->template = $this->config->get('config_template') . '/template/gallery/success.tpl';
      } else {
        $this->template = 'default/template/gallery/success.tpl';
      }
      
      $this->children = array(
        'common/column_left',
        'common/column_right',
        'common/content_top',
        'common/content_bottom',
        'common/footer',
        'common/header'
      );

      $templateUrl = DIR_TEMPLATE . "default/gallery/success.tpl";

      $this->response->setOutput($this->render($templateUrl));

    }

  }

  public function getGalleryGeneralData() {
  	$data = array();

  	$this->language->load('gallery/general');

  	$data['breadcrumbs'] = array();
		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),			
			'separator' => false
		);

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('breadcrumbs_gallery'),
			'href'      => $this->url->link('product/gallery'),
			'separator' => $this->language->get('text_separator')
		);

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('gallery_add_photo'),
			'href'      => $this->url->link('gallery/photo/addPhoto'),
			'separator' => $this->language->get('text_separator')
		);

		$data['galery_photo_name'] = $this->language->get('galery_photo_name');
		$data['galery_photo_description'] = $this->language->get('galery_photo_description');
		$data['heading_title'] = $this->language->get('gallery_add_photo');
		$data['galery_photo_name_empty'] = $this->language->get('galery_photo_name_empty');
		$data['galery_photo_description_to_long'] = $this->language->get('galery_photo_description_to_long');
		$data['galery_photo_file_empty'] = $this->language->get('galery_photo_file_empty');
    $data['galery_photo_upload_success'] = $this->language->get('galery_photo_upload_success');

		return $data;
  }

  public function showLargePhoto() {
    
    $photo_id = isset($_GET['photo_id']) ? $_GET['photo_id'] : '';
    $photo_type = isset($_GET['photo_type']) ? $_GET['photo_type'] : '';

    $this->data['photo_type'] = $photo_type;

    if($photo_type == 'gallery_photo') {
      $query = "SELECT * FROM gallery_photo WHERE photo_id = '" . $photo_id . "'";
      $result = $this->db->query($query);

      foreach ($result->rows as $row) {
        $this->data['image'] = HTTP_IMAGE . $row['path'];
        $this->data['photo_id'] = $row['photo_id'];
        list($width, $height, $type, $attr) = getimagesize(DIR_IMAGE . $row['path']);
      }
    }
    elseif($photo_type == 'review_image') {
      $query = "SELECT image_path AS path, review_image_id FROM review_images WHERE review_image_id = '" . $photo_id . "'";

      $result = $this->db->query($query);

      foreach ($result->rows as $row) {
        $this->data['image'] = HTTP_IMAGE . substr($row['path'], 1);
        $this->data['photo_id'] = $row['review_image_id'];
        list($width, $height, $type, $attr) = getimagesize(DIR_IMAGE . substr($row['path'], 1));
      }
    }

    if($width >= $height) {
      $this->data['image_width'] = 640;
      $this->data['image_height'] = 480;
    }
    else {
      $this->data['image_width'] = 480;
      $this->data['image_height'] = 640;
    }

    $this->template = 'default/template/gallery/iframeVote.tpl';
    $this->language->load('gallery/general');
    $this->data['message_vote_success'] = $this->language->get('galery_message_vote_success');

    $this->response->setOutput($this->render());
    /*else {
      //Error
      //.....................
      //.....................
      //.....................
    }*/

  }

  public function addVote() {
    $modelData['photoID'] = isset($_POST['photoID']) ? $_POST['photoID'] : '';
    $modelData['photoType'] = isset($_POST['photoType']) ? $_POST['photoType'] : ''; 
    $modelData['stars'] = isset($_POST['stars']) ? $_POST['stars'] : '0';
    $modelData['comment'] = isset($_POST['comment']) ? $_POST['comment'] : '';
    $modelData['date'] = date('Y-m-d H:i:s');

    $this->language->load('gallery/general');

    $this->load->model('gallery/photo');
    $result = $this->model_gallery_photo->addVote($modelData);

    $response = array();
    if($result) {
      $response['success'] = true;
      $response['photo_id'] = $_POST['photoID'];
      $response['photo_type'] = $_POST['photoType'];
      $response['message'] = $this->language->get('galery_message_vote_success');
    }

    print_r(json_encode($response));

  }
}
?>
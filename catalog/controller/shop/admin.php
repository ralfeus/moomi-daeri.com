<?php  
class ControllerShopAdmin extends Controller {
  public function getHolidays() {
    /*$modelData['photoID'] = isset($_POST['photoID']) ? $_POST['photoID'] : '';
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

    print_r(json_encode($response));*/

  }

  public function getAllHolidaysForCalendar() {
    $this->load->model('shop/general');

    $json_holidays = $this->model_shop_general->getAllHolidaysForCalendar();

    echo $json_holidays;

  }
}
?>
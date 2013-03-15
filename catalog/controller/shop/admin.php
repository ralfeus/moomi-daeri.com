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

  public function showPage() {
    $page_id  = isset($this->request->get['page_id']) ? $this->request->get['page_id'] : null;
    $lang = $this->language->get('code');

    $this->load->model('shop/general');
    $this->load->language('shop/general');

    $result = $this->model_shop_general->getPage($page_id, $lang);

    $pages = $result['pages'];

    $children = $result['children'];

    $this->children = array(
      'common/content_top',
      'common/footer',
      'common/header'
    );

    $this->data['breadcrumbs'] = array();

    $this->data['breadcrumbs'][] = array(
      'text'      => $this->language->get('text_home'),
      'href'      => $this->url->link('common/home'),
      'separator' => false
    );

    $this->data['breadcrumbs'][] = array(
      'text'      => $this->language->get('text_account'),
      'href'      => $this->url->link('account/account', '', 'SSL'),
      'separator' => $this->language->get('text_separator')
    );

    $this->data['breadcrumbs'][] = array(
      'text'      => $this->language->get('text_login'),
      'href'      => $this->url->link('account/login', '', 'SSL'),
      'separator' => $this->language->get('text_separator')
    );

    if($page_id == null) {
      $this->data['heading_title'] = $this->language->get('text_title_page');
    }
    else {
      $this->data['heading_title'] = $pages[0]['page_title'];
    }

    $this->data['text_back_to_root'] = $this->language->get('text_back_to_root');

    $this->data['pages'] = $pages;
    $this->data['children'] = $children;
    $this->data['page_id'] = $page_id;

    $this->template = 'default/template/shop/page.tpl';

    $this->response->setOutput($this->render());

    //print_r($result);
  }
}
?>
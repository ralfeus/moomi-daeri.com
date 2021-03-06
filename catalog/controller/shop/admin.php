<?php
namespace catalog\controller\shop;

use model\shop\GeneralDAO;
use system\engine\Controller;

class ControllerShopAdmin extends Controller {

    public function __construct($registry) {
        parent::__construct($registry);
        $this->getLoader()->language('shop/general');
    }

    public function getHolidays() {
        /*$modelData['photoID'] = isset($_POST['photoID']) ? $_POST['photoID'] : '';
        $modelData['photoType'] = isset($_POST['photoType']) ? $_POST['photoType'] : '';
        $modelData['stars'] = isset($_POST['stars']) ? $_POST['stars'] : '0';
        $modelData['comment'] = isset($_POST['comment']) ? $_POST['comment'] : '';
        $modelData['date'] = date('Y-m-d H:i:s');

        $this->language->load('gallery/general');

        $this->getLoader()->model('gallery/photo');
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
        $json_holidays = GeneralDAO::getInstance()->getAllHolidaysForCalendar();

        echo $json_holidays;
    }

    public function showPage() {
        $page_id = isset($this->request->get['page_id']) ? $this->request->get['page_id'] : null;
        $lang = $this->getConfig()->get('config_language_id');
        $result = GeneralDAO::getInstance()->getPage($page_id, $lang);

        $pages = $result['pages'];
        $children = $result['children'];

        $this->children = array(
            'common/content_top',
            'common/footer',
            'common/header'
        );

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->getUrl()->link('common/home'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_account'),
            'href' => $this->getUrl()->link('account/account', '', 'SSL'),
            'separator' => $this->language->get('text_separator')
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_login'),
            'href' => $this->getUrl()->link('account/login', '', 'SSL'),
            'separator' => $this->language->get('text_separator')
        );

        if ($page_id == null) {
            $this->data['heading_title'] = $this->language->get('text_title_page');
        } else {
            $this->data['heading_title'] = $pages[0]['page_title'];
        }

        $this->data['text_back_to_root'] = $this->language->get('text_back_to_root');

        $this->data['pages'] = $pages;
        $this->data['children'] = $children;
        $this->data['page_id'] = $page_id;

        $this->getResponse()->setOutput($this->render('default/template/shop/page.tpl'));

        //print_r($result);
    }

    public function hasAction() {
        $actions = $this->getPromotion();
        $response['result'] = !empty($actions);
        //$response['group_id'] = print_r($actions, true);
        print(json_encode($response));
    }

    public function showAction($action = null) {

        $this->getLoader()->language('shop/general');

        $this->data['action'] = isset($action) ? $action : $this->getPromotion();

        $urls = $this->object2array(json_decode($this->data['action']['jsonUrls']));
        $images = $this->object2array(json_decode($this->data['action']['jsonImages']));

        $cur_lang = $this->language->get('code');

        $this->data['action_url'] = $urls[$cur_lang];
        $this->data['action_image'] = HTTP_SERVER . "admin/view/image/actions/" . $images[$cur_lang];
        $this->data['action_message'] = $this->language->get('text_action_no_show_more');

        $this->getResponse()->setOutput($this->render('default/template/shop/action.tpl.php'));
    }

    private function getPromotion() {
        $customer_group_id = $this->customer->getCustomerGroupId();
        $data['customer_group_id'] = empty($customer_group_id) ? 0 : $customer_group_id;
        $data['current_date'] = date("Y-m-d");

        $result = GeneralDAO::getInstance()->getAction($data);

        return !empty($result) ? $result[0] : null;
    }

    /**
     * @param $obj
     * @return array
     */
    public function object2array($obj) {
        $_arr = is_object($obj) ? get_object_vars($obj) : $obj;
        $arr = [];
        foreach ($_arr as $key => $val) {
            $val = (is_array($val) || is_object($val)) ? $this->object2array($val) : $val;
            $arr[$key] = $val;
        }
        return $arr;
    }
}
<?php
class ControllerShopAction extends Controller {
  public function index() {

    $this->load->language('shop/admin');
    $this->data['breadcrumbs'] = array();

    $this->data['breadcrumbs'][] = array(
        'text'      => $this->language->get('text_home'),
        'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
        'separator' => false
    );

    $this->data['breadcrumbs'][] = array(
        'text'      => $this->language->get('heading_title_action'),
        'href'      => $this->url->link('shop/action', 'token=' . $this->session->data['token'], 'SSL'),
        'separator' => ' :: '
    );

    $this->data['heading_title_action'] = $this->language->get('heading_title_action');

    $this->load->model('shop/action');
    $this->data['actions'] = $this->model_shop_action->getAllActions();
    $this->data['edit_text'] = $this->language->get('edit_text');
    foreach ($this->data['actions'] as $index => $action) {
      $this->data['actions'][$index]['edit_link'] = $this->url->link('shop/action/edit', 'token=' . $this->session->data['token'] . '&action_id=' . $action['id'], 'SSL');
    }
    //$this->data['edit_link'] = $this->url->link('shop/action/edit', 'token=' . $this->session->data['token'], 'SSL');
    $this->load->language('shop/page');
    $this->data['button_insert'] = $this->language->get('button_insert');
    $this->data['button_delete'] = $this->language->get('button_delete');

    $this->data['insert'] = $this->url->link('shop/action/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
    $this->data['delete'] = $this->url->link('shop/action/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');

    $this->data['token'] = $this->session->data['token'];
    $this->template = 'shop/action_list.tpl';
    $this->children = array(
      'common/header',
      'common/footer'
    );

    $this->getResponse()->setOutput($this->render());
  }

  public function insert() {

    $this->load->language('shop/admin');
    $this->data['breadcrumbs'] = array();

    $this->data['breadcrumbs'][] = array(
      'text'      => $this->language->get('text_home'),
      'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      'separator' => false
    );

    $this->data['breadcrumbs'][] = array(
      'text'      => $this->language->get('heading_title_action'),
      'href'      => $this->url->link('shop/action/insert', 'token=' . $this->session->data['token'], 'SSL'),
      'separator' => ' :: '
    );

    $this->data['button_save']           = $this->language->get('button_save');
    $this->data['button_cancel']         = $this->language->get('button_cancel');
    $this->data['entry_action_name']     = $this->language->get('entry_action_name');
    $this->data['entry_all_groups']      = $this->language->get('entry_all_groups');
    $this->data['entry_customer_group']  = $this->language->get('entry_customer_group');
    $this->data['entry_action_start']    = $this->language->get('entry_action_start');
    $this->data['entry_action_end']      = $this->language->get('entry_action_end');
    $this->data['entry_img_ru']          = $this->language->get('entry_img_ru');
    $this->data['entry_url_ru']          = $this->language->get('entry_url_ru');
    $this->data['entry_img_en']          = $this->language->get('entry_img_en');
    $this->data['entry_url_en']          = $this->language->get('entry_url_en');
    $this->data['entry_img_jp']          = $this->language->get('entry_img_jp');
    $this->data['entry_url_jp']          = $this->language->get('entry_url_jp');

    $this->load->model('sale/customer_group');
    $this->data['customer_groups'] = $this->model_sale_customer_group->getCustomerGroups();

    $this->data['action_add_url'] = $this->url->link('shop/action/addAction', 'token=' . $this->session->data['token'], 'SSL');

    $this->data['token'] = $this->session->data['token'];
    $this->template = 'shop/action_form.tpl';
    $this->children = array(
      'common/header',
      'common/footer'
    );

    $this->getResponse()->setOutput($this->render());
  }

  public function addAction() {
    $pathToFolder = DIR_TEMPLATE . '../image/actions/';
    if(!file_exists($pathToFolder)) {
      mkdir($pathToFolder, 0777);
    }

    $images = array();
    move_uploaded_file($_FILES['image']['tmp_name']['ru'], $pathToFolder.$_FILES['image']['name']['ru']);
    $images['ru'] = $_FILES['image']['name']['ru'];
    move_uploaded_file($_FILES['image']['tmp_name']['en'], $pathToFolder.$_FILES['image']['name']['en']);
    $images['en'] = $_FILES['image']['name']['en'];
    move_uploaded_file($_FILES['image']['tmp_name']['jp'], $pathToFolder.$_FILES['image']['name']['jp']);
    $images['jp'] = $_FILES['image']['name']['jp'];
    //echo "--->" . print_r($images, true);
    $data = array();
    $data['name'] = isset($_POST['actionName']) ? $_POST['actionName'] : '';
    $data['customer_group_id'] = isset($_POST['customer_group']) ? $_POST['customer_group'] : '';
    $data['start_date'] = isset($_POST['actionStart']) ? $_POST['actionStart'] : '';
    $data['finish_date'] = isset($_POST['actionEnd']) ? $_POST['actionEnd'] : '';
    $data['jsonUrls'] = isset($_POST['url']) ? json_encode($_POST['url']) : '';
    $data['jsonImages'] = json_encode($images);

    $this->load->model('shop/action');
    $this->model_shop_action->addAction($data);

    $this->redirect($this->url->link('shop/action', 'token=' . $this->session->data['token'], 'SSL'));

  }

  public function delete() {
    $in = implode(",", $_REQUEST['selected']);

    $this->load->model('shop/action');
    $this->model_shop_action->deleteActions($in);

    $this->redirect($this->url->link('shop/action', 'token=' . $this->session->data['token'], 'SSL'));
  }

  public function edit() {
    $data['action_id'] = $_REQUEST['action_id'];
    //print_r($action_id);
    $this->load->model('shop/action');
    $result = $this->model_shop_action->getAction($data);

    $this->data['action'] = $result[0];
    $this->data['action_images'] = $this->object2array(json_decode($result[0]['jsonImages']));
    $this->data['action_urls'] = $this->object2array(json_decode($result[0]['jsonUrls']));

    $this->load->language('shop/admin');
    $this->data['breadcrumbs'] = array();

    $this->data['breadcrumbs'][] = array(
      'text'      => $this->language->get('text_home'),
      'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      'separator' => false
    );

    $this->data['breadcrumbs'][] = array(
      'text'      => $this->language->get('heading_title_action'),
      'href'      => $this->url->link('shop/action/insert', 'token=' . $this->session->data['token'], 'SSL'),
      'separator' => ' :: '
    );

    $this->data['button_save']           = $this->language->get('button_save');
    $this->data['button_cancel']         = $this->language->get('button_cancel');
    $this->data['entry_action_name']     = $this->language->get('entry_action_name');
    $this->data['entry_all_groups']      = $this->language->get('entry_all_groups');
    $this->data['entry_customer_group']  = $this->language->get('entry_customer_group');
    $this->data['entry_action_start']    = $this->language->get('entry_action_start');
    $this->data['entry_action_end']      = $this->language->get('entry_action_end');
    $this->data['entry_img_ru']          = $this->language->get('entry_img_ru');
    $this->data['entry_url_ru']          = $this->language->get('entry_url_ru');
    $this->data['entry_img_en']          = $this->language->get('entry_img_en');
    $this->data['entry_url_en']          = $this->language->get('entry_url_en');
    $this->data['entry_img_jp']          = $this->language->get('entry_img_jp');
    $this->data['entry_url_jp']          = $this->language->get('entry_url_jp');

    $this->load->model('sale/customer_group');
    $this->data['customer_groups'] = $this->model_sale_customer_group->getCustomerGroups();

    $this->data['action_add_url'] = $this->url->link('shop/action/updateAction', 'token=' . $this->session->data['token'] . "&action_id=".$this->data['action']['id'], 'SSL');

    $this->data['token'] = $this->session->data['token'];
    $this->template = 'shop/action_form.tpl';
    $this->children = array(
      'common/header',
      'common/footer'
    );

    $this->getResponse()->setOutput($this->render());
  }

  public function object2array($obj) {
    $_arr = is_object($obj) ? get_object_vars($obj) : $obj;
    foreach ($_arr as $key => $val) {
        $val = (is_array($val) || is_object($val)) ? object2array($val) : $val;
        $arr[$key] = $val;
    }
    return $arr;
  }

  public function updateAction() {
    $data['action_id'] = $_REQUEST['action_id'];
    $data['name'] = isset($_POST['actionName']) ? $_POST['actionName'] : '';
    $data['customer_group_id'] = isset($_POST['customer_group']) ? $_POST['customer_group'] : '';
    $data['start_date'] = isset($_POST['actionStart']) ? $_POST['actionStart'] : '';
    $data['finish_date'] = isset($_POST['actionEnd']) ? $_POST['actionEnd'] : '';
    $data['jsonUrls'] = isset($_POST['url']) ? json_encode($_POST['url']) : '';

    $this->load->model('shop/action');
    $this->model_shop_action->updateAction($data);

    $this->redirect($this->url->link('shop/action', 'token=' . $this->session->data['token'], 'SSL'));
  }
}
?>
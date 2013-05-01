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

    $this->response->setOutput($this->render());
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

    $this->data['action'] = $this->url->link('shop/action/addAction', 'token=' . $this->session->data['token'], 'SSL');

    $this->data['token'] = $this->session->data['token'];
    $this->template = 'shop/action_form.tpl';
    $this->children = array(
      'common/header',
      'common/footer'
    );

    $this->response->setOutput($this->render());
  }

  public function addAction() {
    print_r($_REQUEST); die();
  }

  public function delete() {

  }
}
?>
<?php
use system\engine\Controller;

class ControllerModuleShortCheckout extends \system\engine\Controller {
	private $error = array();

	public function index() {
		$this->load->language('module/short_checkout');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('short_checkout', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->redirect($this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
                $this->data['text_required'] = $this->language->get('text_required');
		$this->data['text_not_required'] = $this->language->get('text_not_required');
		$this->data['entry_status'] = $this->language->get('entry_status');
                $this->data['text_fields'] = $this->language->get('text_fields');
                $this->data['entry_firstname'] = $this->language->get('entry_firstname');
                $this->data['entry_lastname'] = $this->language->get('entry_lastname');
                $this->data['entry_email'] = $this->language->get('entry_email');
                $this->data['entry_telephone'] = $this->language->get('entry_telephone');
                $this->data['entry_fax'] = $this->language->get('entry_fax');
                $this->data['entry_company'] = $this->language->get('entry_company');
                $this->data['entry_address_1'] = $this->language->get('entry_address_1');
                $this->data['entry_address_2'] = $this->language->get('entry_address_2');
                $this->data['entry_city'] = $this->language->get('entry_city');
                $this->data['entry_postcode'] = $this->language->get('entry_postcode');
                $this->data['entry_country'] = $this->language->get('entry_country');
                $this->data['entry_min_order_sum'] = $this->language->get('entry_min_order_sum');


		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_cancel'] = $this->language->get('button_cancel');

		$this->data['token'] = $this->session->data['token'];
                $this->data['settings'] = $this->model_setting_setting->getSetting('short_checkout');

 		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

  		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_module'),
			'href'      => $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('module/short_checkout', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);

		$this->data['action'] = $this->url->link('module/short_checkout', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL');




		$this->template = 'module/short_checkout.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render());
	}

	private function validate() {
		if (!$this->user->hasPermission('modify', 'module/short_checkout')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}
}
?>
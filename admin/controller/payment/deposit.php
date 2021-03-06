<?php
use system\engine\Controller;

class ControllerPaymentDeposit extends \system\engine\Controller {
	private $error = array(); 
	
	public function index() { 
		$this->load->language('payment/deposit');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('setting/setting');
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('deposit', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');
			
			$this->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}
		
		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		
		$this->data['entry_order_status'] = $this->language->get('entry_order_status');		
		$this->data['entry_total'] = $this->language->get('entry_total');	
		$this->data['entry_status'] = $this->language->get('entry_status');
		$this->data['entry_sort_order'] = $this->language->get('entry_sort_order');
		
		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_cancel'] = $this->language->get('button_cancel');

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
			'text'      => $this->language->get('text_payment'),
			'href'      => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
			);
		
		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('payment/deposit', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
			);
		
		$this->data['action'] = $this->url->link('payment/deposit', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');	
		
		if (isset($this->request->post['deposit_total'])) {
			$this->data['deposit_total'] = $this->request->post['deposit_total'];
		} else {
			$this->data['deposit_total'] = $this->config->get('deposit_total'); 
		}
		
		if (isset($this->request->post['deposit_order_status_id'])) {
			$this->data['deposit_order_status_id'] = $this->request->post['deposit_order_status_id'];
		} else {
			$this->data['deposit_order_status_id'] = $this->config->get('deposit_order_status_id'); 
		} 
		
		$this->load->model('localisation/order_status');
		
		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		if (isset($this->request->post['deposit_status'])) {
			$this->data['deposit_status'] = $this->request->post['deposit_status'];
		} else {
			$this->data['deposit_status'] = $this->config->get('deposit_status');
		}
		
		if (isset($this->request->post['deposit_sort_order'])) {
			$this->data['deposit_sort_order'] = $this->request->post['deposit_sort_order'];
		} else {
			$this->data['deposit_sort_order'] = $this->config->get('deposit_sort_order');
		}

		$this->template = 'payment/deposit.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
			);
		
		$this->getResponse()->setOutput($this->render());
	}
	
	private function validate() {
		if (!$this->user->hasPermission('modify', 'payment/deposit')) {
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
<?php 
	//trigger_error ( ' URL api make: ' . $m_url, E_USER_NOTICE );

class ControllerPaymentMultiPayDep extends \system\engine\Controller {
	private $error = array();

    public function install() {
		$this->load->model('setting/setting');
        $this->model_setting_setting->editSetting('multi_pay_dep', array(
				// as multi_pay 'multi_pay_dep_order_status_id'=>2, // 'Proccessing',
				'multi_pay_dep_status'=>1,
				'multi_pay_dep_sort_order'=>99,
				));
		
    }
	public function index() {
		$this->language->load('payment/cod');
		$this->language->load('payment/multi_pay_dep');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('setting/setting');
			
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			
			$this->model_setting_setting->editSetting('multi_pay', $this->request->post);				
			
			$this->session->data['success'] = $this->language->get('text_success');

			$this->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_enabled'        ] = $this->language->get('text_enabled');
		$this->data['text_disabled'       ] = $this->language->get('text_disabled');
		
		$this->data['entry_order_status'  ] = $this->language->get('entry_order_status');
		$this->data['entry_status'        ] = $this->language->get('entry_status');
		$this->data['entry_sort_order'    ] = $this->language->get('entry_sort_order');

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
			'href'      => $this->url->link('payment/multi_pay', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);

		$this->data['action'] = $this->url->link('payment/multi_pay', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');


		$this->load->model('localisation/order_status');
		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        if (isset($this->request->post['multi_pay_dep_order_status_id'])) {
			$this->data['multi_pay_dep_order_status_id'] = $this->request->post['multi_pay_dep_order_status_id'];
		} else {
			$this->data['multi_pay_dep_order_status_id'] = $this->config->get('multi_pay_dep_order_status_id'); 
		} 		

		if (isset($this->request->post['multi_pay_dep_status'])) {
			$this->data['multi_pay_dep_status'] = $this->request->post['multi_pay_dep_status'];
		} else {
			$this->data['multi_pay_dep_status'] = $this->config->get('multi_pay_dep_status');
		}
		
		if (isset($this->request->post['multi_pay_dep_sort_order'])) {
			$this->data['multi_pay_dep_sort_order'] = $this->request->post['multi_pay_dep_sort_order'];
		} else {
			$this->data['multi_pay_dep_sort_order'] = $this->config->get('multi_pay_dep_sort_order');
		}
		
		$this->template = 'payment/multi_pay_dep.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'payment/multi_pay')) {
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
<?php 
class ControllerTotalPaymentMethodDiscounts extends Controller { 
	private $error = array(); 
	 
	public function index() { 
		$this->load->language('total/paymentmethoddiscounts');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('setting/setting');
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate())) {
			$this->model_setting_setting->editSetting('paymentmethoddiscounts', $this->request->post);
		
			$this->session->data['success'] = $this->language->get('text_success');
			
			$this->redirect($this->url->link('extension/total', 'token=' . $this->session->data['token'], 'SSL'));
		}
		
		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_none'] = $this->language->get('text_none');
		$this->data['text_ed'] = $this->language->get('text_ed');
		$this->data['text_percent'] = $this->language->get('text_percent');
		
		$this->data['entry_payment'] = $this->language->get('entry_payment');
		$this->data['entry_skidka'] = $this->language->get('entry_skidka');
		$this->data['entry_tax_class'] = $this->language->get('entry_tax_class');
		$this->data['entry_status'] = $this->language->get('entry_status');
		$this->data['entry_sort_order'] = $this->language->get('entry_sort_order');
		$this->data['entry_donate_me'] = $this->language->get('entry_donate_me');
		
					
		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_cancel'] = $this->language->get('button_cancel');
		$this->data['button_add_discount'] = $this->language->get('button_add_discount');
		$this->data['button_remove'] = $this->language->get('button_remove');
		

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
       		'text'      => $this->language->get('text_total'),
			'href'      => $this->url->link('extension/total', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);
		
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('total/paymentmethoddiscounts', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);
		
		$this->data['action'] = $this->url->link('total/paymentmethoddiscounts', 'token=' . $this->session->data['token'], 'SSL');
		
		$this->data['cancel'] = $this->url->link('extension/total', 'token=' . $this->session->data['token'], 'SSL');

		if (isset($this->request->post['paymentmethoddiscounts_discount'])) {
			$this->data['paymentmethoddiscounts_discount'] = $this->request->post['paymentmethoddiscounts_discount'];
		} else {
			$this->data['paymentmethoddiscounts_discount'] = $this->config->get('paymentmethoddiscounts_discount');
		}
		
		if(!$this->data['paymentmethoddiscounts_discount'])$this->data['paymentmethoddiscounts_discount'] = array();

		if (isset($this->request->post['paymentmethoddiscounts_sort_order'])) {
			$this->data['paymentmethoddiscounts_sort_order'] = $this->request->post['paymentmethoddiscounts_sort_order'];
		} else {
			$this->data['paymentmethoddiscounts_sort_order'] = $this->config->get('paymentmethoddiscounts_sort_order');
		}
		
		if (isset($this->request->post['paymentmethoddiscounts_status'])) {
			$this->data['paymentmethoddiscounts_status'] = $this->request->post['paymentmethoddiscounts_status'];
		} else {
			$this->data['paymentmethoddiscounts_status'] = $this->config->get('paymentmethoddiscounts_status');
		}
		
		$this->load->model('localisation/tax_class');
		
		$this->data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();
		
		$this->load->model('setting/extension');
		$extensions = \model\setting\ExtensionDAO::getInstance()->getExtensions('payment');
		$this->data['extensions'] = array();
						
		$files = glob(DIR_APPLICATION . 'controller/payment/*.php');
		
		if ($files) {
			foreach ($files as $file) {
				$extension = basename($file, '.php');
				
				$this->load->language('payment/' . $extension);
	
				$action = array();
				
				
				
				$text_link = $this->language->get('text_' . $extension);
				
				if ($text_link != 'text_' . $extension) {
					$link = $this->language->get('text_' . $extension);
				} else {
					$link = '';
				}
				
				if($this->config->get($extension . '_status')){
					$this->data['extensions'][] = array(
						'name'       => $this->language->get('heading_title'),
						'code'		=> $extension,
						'sort_order' => $this->config->get($extension . '_sort_order')
					);
				}
			}
		}
		
		
		
		$this->template = 'total/paymentmethoddiscounts.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
	}

	private function validate() {
		if (!$this->user->hasPermission('modify', 'total/paymentmethoddiscounts')) {
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
<?php
use system\engine\Controller;

class ControllerPaymentAlertPay extends \system\engine\Controller {
	protected function index() {
		$this->data['button_confirm'] = $this->language->get('button_confirm');

		$this->load->model('checkout/order');
		
		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		
		$this->data['action'] = 'https://www.alertpay.com/PayProcess.aspx';

		$this->data['ap_merchant'] = $this->config->get('alertpay_merchant');
		$this->data['ap_amount'] = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);
		$this->data['ap_currency'] = $order_info['currency_code'];
		$this->data['ap_purchasetype'] = 'Item';
		$this->data['ap_itemname'] = $this->config->get('config_name') . ' - #' . $this->session->data['order_id'];
		$this->data['ap_itemcode'] = $this->session->data['order_id'];
		$this->data['ap_returnurl'] = $this->url->link('checkout/success');
		$this->data['ap_cancelurl'] = $this->url->link('checkout/checkout', '', 'SSL');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/alertpay.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/payment/alertpay.tpl';
		} else {
			$this->template = 'default/template/payment/alertpay.tpl';
		}		
		
		$this->render();
	}
	
	public function callback() {
		if (isset($this->request->post['ap_securitycode']) && ($this->request->post['ap_securitycode'] == $this->config->get('alertpay_security'))) {
			$this->load->model('checkout/order');
			
			$this->model_checkout_order->confirm($this->request->post['ap_itemcode'], $this->config->get('alertpay_order_status_id'));
		}
	}
}
?>
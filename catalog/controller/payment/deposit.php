<?php
class ControllerPaymentDeposit extends Controller {
	protected function index() {
		$this->data['button_confirm'] = $this->language->get('button_confirm');

		$this->data['continue'] = $this->url->link('checkout/success');

        $templateName = '/template/payment/deposit.tpl.php';
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . $templateName)) {
			$this->template = $this->config->get('config_template') . $templateName;
		} else {
			$this->template = 'default' . $templateName;
		}	
		
		$this->render();
	}
	
	public function confirm() {
		//$this->log->write("ControllerPaymentDeposit::confirm()");
		$this->load->model('checkout/order');
		$this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('deposit_order_status_id'));
		
		/// Decrease deposit amount
		//$this->load->model('account/customer_deposit');
		//$order = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		//$this->model_account_customer_deposit->subtractAmount($order);
	}
}
?>
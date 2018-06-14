<?php
//trigger_error ( 'kk' 'SSL'), E_USER_NOTICE );

class ControllerPaymentMultiPay extends \system\engine\Controller {

	// открыть выбор способов оплаты для данной корзины и создать из нее заказ
	public function pay() {
	
		$this->load->helper('multi_pay');
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link(_url($_GET), 'SSL');
			$this->redirect($this->url->link('account/login', '', 'SSL'));
		}
	
        $order_id = _g($_GET,'order_id', _g($this->session->data,'order_id'));
		if (!$order_id) $this->redirect($this->url->link('account/order'));
		$this->session->data['order_id'] = $order_id;
		
		// подтверждаем заказ (если он уже есть то там будет это проигнорировано)
		$this->load->model('payment/multi_pay_tools');
		$this->model_payment_multi_pay_tools->order_confirm($order_id, '');
	
		// просто перейдем на оплату разными способами для заказа
		$this->redirect($this->url->link('account/multi_pay/order', '&order_id='.$order_id, 'SSL'));
	}

	public function index() {

		$this->load->helper('multi_pay');
		$this->data['button_confirm' ] = $this->language->get('button_confirm');
		// страницу окончания создания заказа свою грузим
		$this->data['action'] = $this->url->link('payment/multi_pay/pay');
	
		// добавили этот шаблон в общую папку
		$this->template = _tpl('/template/common/payment_confirm.tpl', $this->config->get('config_template'));

		$this->render();
	}
}
?>
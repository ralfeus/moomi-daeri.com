<?php
use system\engine\Controller;

class ControllerPaymentQiwi extends \system\engine\Controller {
	protected function index() {
    	$this->data['button_confirm'] = $this->language->get('button_confirm');
		$this->data['button_back'] = $this->language->get('button_back');


		$this->data['action'] = 'https://w.qiwi.ru/setInetBill_utf.do';

		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);


		// TETS
		$this->load->language('payment/qiwi');
		$this->data['sub_text_info'] = $this->language->get('sub_text_info');
		$this->data['sub_text_info_phone'] = $this->language->get('sub_text_info_phone');

        $this->data['description'] = $this->config->get('config_store') . ' ' . $order_info['payment_firstname'] . ' ' . $order_info['payment_address_1'] . ' ' . $order_info['payment_address_2'] . ' ' . $order_info['payment_city'] . ' ' . $order_info['email'];

		// Переменные
		$this->data['from'] = $this->config->get('qiwi_shop_id');
		$this->data['txn_id'] = $this->session->data['order_id'];
		$this->data['com'] = html_entity_decode($this->data['description'], ENT_QUOTES, 'UTF-8');
		$rur_code = 'RUB';
        $rur_order_total = $this->currency->convert($order_info['total'], $order_info['currency_code'], $rur_code);
	   $this->data['summ'] = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);
//$this->currency->format($rur_order_total, $rur_code, $order_info['currency_value'], FALSE);

		$this->data['check_agt'] = false;
		$this->data['lifetime'] = $this->config->get('qiwi_lifetime');



		$this->data['return'] = HTTP_SERVER . 'index.php?route=checkout/success';




		$this->id = 'payment';

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/qiwi.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/payment/qiwi.tpl';
		} else {
			$this->template = 'default/template/payment/qiwi.tpl';
		}

		$this->render();
	}

	public function confirm() {

		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		if(!$order_info) return;

		$order_id = $this->session->data['order_id'];

		if( $order_info['order_status_id'] == 0) {
			$this->model_checkout_order->confirm($order_id, $this->config->get('qiwi_order_status_progress_id'), 'QIWI');
			return;
		}

		if( $order_info['order_status_id'] != $this->config->get('qiwi_order_status_progress_id')) {
			$this->model_checkout_order->update($order_id, $this->config->get('qiwi_order_status_progress_id'),'QIWI',TRUE);
		}

   	}

	public function fail() {

		$this->redirect(HTTP_SERVER . 'index.php?route=checkout/payment');

		return TRUE;
	}

	public function success() {

		$this->redirect(HTTP_SERVER . 'index.php?route=checkout/success');

		return TRUE;
	}

	public function callback() {

		 $s = new SoapServer(DIR_CONFIG . 'ishopclientws.wsdl');
		 $s->setClass('qiwiSoap');
		 $s->handle();
		 exit;
	}

}

class qiwiSoap extends \system\engine\Model {

	public function __construct() {
		global $registry;
		$this->registry = $registry;
	}

	public function updateBill($param) {

/*
		$k = var_export($param, true);
		$this->log->write('PHP Fatal Error:  ' . $k . ' in qiwi');
*/

		// Проверка на ID магазина
		if($param->login != $this->config->get('qiwi_shop_id')) {
			$param->updateBillResult = 150;
			return $param;
		}


		$order_id = (int)$param->txn;
		$hash = strtoupper( md5( $order_id . strtoupper( md5($this->config->get('qiwi_password')))));
		// Проверка на пароль
		if($param->password != $hash) {
			$param->updateBillResult = 150;
			return $param;
		}

		// Проверка на номер заказа
		$this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->getOrder($order_id);
		if ( ! $order_info) {
			$param->updateBillResult = 210;
			return $param;
		}

		$param->updateBillResult = 0;

		// Изменяем статус заказа

		// Стутс проведения счета.
		if( $param->status == 60 ) {

			if( $order_info['order_status_id'] == 0) {
				$this->model_checkout_order->confirm($order_id, $this->config->get('qiwi_order_status_id'), 'QIWI');
				return $param;
			}

			if( $order_info['order_status_id'] != $this->config->get('qiwi_order_status_id')) {
				$this->model_checkout_order->update($order_id, $this->config->get('qiwi_order_status_id'),'QIWI',TRUE);
			}
		} elseif( $param->status >= 150) {

			if( $order_info['order_status_id'] == 0) {
				$this->model_checkout_order->confirm($order_id, $this->config->get('qiwi_order_status_cancel_id'), 'QIWI');
				return $param;
			}

			if( $order_info['order_status_id'] != $this->config->get('qiwi_order_status_cancel_id')) {
				$this->model_checkout_order->update($order_id, $this->config->get('qiwi_order_status_cancel_id'),'QIWI',TRUE);
			}
		} else {

			if( $order_info['order_status_id'] == 0) {
				$this->model_checkout_order->confirm($order_id, $this->config->get('qiwi_order_status_progress_id'), 'QIWI');
				return $param;
			}

			if( $order_info['order_status_id'] != $this->config->get('qiwi_order_status_progress_id')) {
				$this->model_checkout_order->update($order_id, $this->config->get('qiwi_order_status_progress_id'),'QIWI',TRUE);
			}

		}


		return $param;
	}
}

?>
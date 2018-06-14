<?php 
// автор: icreator@mail.ru 
// добавка для ЯДеньги к MultiPay


class ModelPaymentMultiPayYandexMoney extends \system\engine\Model {
	
	//      для проверки платежа на достоверность и прочее
	// взять хэш
	private function get_hash($pars, $org=false) {
		$password = $this->config->get('yandexmoney_password');
		if ($org) {
			$string = $pars['action'].';'.$pars['orderSumAmount'].';'.$pars['orderSumCurrencyPaycash'].';'.$pars['orderSumBankPaycash'].';'.$pars['shopId'].';'.$pars['invoiceId'].';'.$pars['customerNumber'].';'.$password;
			return strtoupper(md5($string));
		} else {
			$string = $pars['notification_type'].'&'.$pars['operation_id'].'&'.$pars['amount'].'&'.$pars['currency'].'&'.$pars['datetime'].'&'.$pars['sender'].'&'.$pars['codepro'].'&'.$password.'&'.$pars['label'];
			return sha1($string);
		}
	}

	private function check_sign($pars, $org=false) {
		$hash = $this->get_hash($pars, $org);
		$check = $org?$pars['md5']==$hash:$pars['sha1_hash']==$hash;
		if (!$check){
			header('HTTP/1.0 401 Unauthorized');
			//trigger_error ( ' check false hash=' . $hash, E_USER_NOTICE );			
			return false;
		}
		return true;
	}

	// проверим - это платеж в наш магазин за заказ и без кода возврата?
	// у организации проверку делает яндекс
	private function check_payment($pars, $org=false) {
	
		if (!$this->check_sign($pars, $org) || !$org && ( 
				$pars['operation_id'] == "test-notification" || // это не тестовая операция
				$pars['notification_type'] != 'p2p-incoming' || // это сообщение о входящем платеже
				$pars["codepro"] != "false" // это платеж без возможности отзыва (нужно подтверждение для приема платежа)
				)
			) {
			//trigger_error ( ' check false ', E_USER_NOTICE );			
			return false;
		}
		return true;
	}
		
	// запоним платеж - сколько в оплату заказа и сколько на депозит
	private function add_payment_0($pars, $org=false) {
	
		// взять нужные нам параметры
			// post: {"test_notification":"true","sender":"41001000040","amount":"436.52",
				// "operation_id":"test-notification",
				// "sha1_hash":"4883b0621a8b26552b70361e0c875a7d744902d8",
				// "notification_type":"p2p-incoming","codepro":"false",
				// "label":"","datetime":"2014-10-07T07:11:42Z","currency":"643"}

		$shopid = $this->config->get('yandexmoney_shopid');
		$account = $this->config->get('yandexmoney_account');
		$service_account = $org? $shopid: $account;
		
		$service_cod = 'yandexmoney_MP';
		$operation_id = $pars['operation_id'];
		$datetime = $pars['datetime'];
		if ($pars['currency']=='643') {
			$curr = 'RUB';
		} else {
			$curr = '???';
		}
		$amount = $pars['amount'];
		
		// выделим номер заказа или номер покупателя
		$label = explode(' ',$pars['label'])[1];  // возьмем 2-е слово как номер заказа		
		$this->load->model('payment/multi_pay');
		$customer_id = $this->model_payment_multi_pay->is_deposit($label);
		$order_id = $customer_id?false:$label;

		$description = 'sender:' . $pars['sender'] . ' ';

		//trigger_error ( 'order: ' . $order_id . ' amount: ' . $curr . $amount . '<br>', E_USER_NOTICE );
		
		// используем готовую функцию в модели muli_pay
		$this->load->model('payment/multi_pay');
		$this->model_payment_multi_pay->add_payment($service_cod, $service_account, $operation_id,  $datetime, $curr, $amount, $order_id, $customer_id, $description);
	}

/////////////////////////////////////////////////////////////////
	public function add_payment_test($pars, $org=false) {
		$pars["operation_id"] = "test" . $pars["operation_id"];
		$this->add_payment_0($pars, $org);
	}
	public function add_payment($pars, $org=false) {
		if ($this->check_payment($pars, $org)) {
			$this->add_payment_0($pars, $org);
		}
	}
}

?>
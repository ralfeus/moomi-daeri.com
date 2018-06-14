<?php
class ModelPaymentMultiPay extends \system\engine\Model {

	// вся инфо по модулю
	public function get_info() {
		$this->language->load('payment/multi_pay');
		return array(
				'code'       => 'multi_pay',
				'title'      => $this->language->get('text_title'),
				'description'=> $this->language->get('text_description'),
				'icon'		 => 'catalog/view/image/payment/multi_pay_icon.jpg',
				'action'	 => '', // ссылка на функцию для создания счета
				'sort_order' => $this->config->get('crypto_pay_sort_order'),
				//'to_deposit'	 => true, // call by index.php?route=payment/MODULE_NAME/deposit
			);
	}

	public function getMethod($address, $total) {

		return $this->get_info();
	}

	public function cancel_order($order_id, $description='') {
		//загружаем модель заказа
		$this->load->model('account/order');
		// берем заказ
		$order_info = $this->model_account_order->getOrder($order_id);
		// если остался депозит (переплата) то занесем его на депозит покупателю
		if ($order_info['order_status_id'] !=2 ) {
			// если заказ еще не оплачен -то его нельзя отменять так как может быть оплата уже пошла
			// поэтому разрешаем пользователю отменять только те заказы которые уже оплачены
			// и имеют статус =2 - Обработка
			return;
		}
		//$curr_order = $order_info['currency_code']; - валюта в которую пересчитали заказ при выборе валюты пользователем
		//$value_order = $order_info['currency_value']; - цена зазака в валюте которую выбрал пользователь
		$curr_glob = $this->config->get('config_currency'); //- нам не надо тут пересччет делать
		$customer_id = $order_info['customer_id'];
		$order_total = (float)$order_info['total']; // - цена в глобальной валюте магазина
		// она и так в валюте магазина $amo_to_deposit = (float)$this->currency->convert($order_total, $curr_order, $curr_glob);
		$amo_to_deposit = $order_total;
		$this->load->model('checkout/order');
		$rec_id = $this->model_checkout_order->update(
				$order_id,
				7,
				$description . ', ' . $amo_to_deposit . $curr_glob . ' '
					. $this->language->get('text_return_to_deposit'),
				true);

		$this->load->model('payment/multi_pay_tools');
		$rec_id = $this->model_payment_multi_pay_tools->update_deposit($customer_id,
				$amo_to_deposit, $this->language->get('Order №').$order_id . ' - ' . $description . ' '
				. ' ' . $order_total . $curr_glob . '->');
	}

////////////////////////////////////////////////////
	// собрать список методов оплаты для пополнения депозита или оплаты заказа
	public function payment_methods($method_data, $to_deposit=false) {
		
		$this->language->load('account/multi_pay');

		$s ='<div>'; 
		foreach ($method_data as $cod => $payment_method) {
			if ($cod == 'multi_pay' ) continue;
			//trigger_error ( 'pm:' . json_encode($payment_method), E_USER_NOTICE );
			
			// call by index.php?route=payment/MODULE_NAME/deposit
			$href = 'index.php?route=payment/'
				. $cod . '/' . _g($payment_method,'action','pay')
				// добавим для пополнения депозита ссылку
				. ($to_deposit?'_dep':'');
			if (isset($payment_method['icon'])) {
				$icon = '<img src="' . $payment_method['icon'] . '" width=180</img>';
			} else {
				$icon = '';
			}
			if (isset($payment_method['description'])) {
				//$dscr = '<help>' . $payment_method['description'] . '</help>';
				$dscr = $payment_method['description'];
			} else {
				$dscr = '';
			}
			
			// если способ оплаты с бонусом то покажем это
			$bonus = (float)$this->config->get($cod . '_bonus');
			if ($bonus != 0) {
				if ($bonus > 0) $txt = $this->language->get('text_bonus');
				else  $txt = $this->language->get('text_bonus_rev');
				$dscr .= '<br><b>' . $txt
					. ' ' . abs($bonus) . '%</b>';
			}


			$s = $s . '
				<div style="margin:10px;float:left;">
					<a target="_blank" class="btn alert-info" href="'. $href .'">
						<div style="text-align:center;margin:3px;">' . $payment_method['title']
							. _g($payment_method,'version','')
							. '</div>'
						. '<div style="margin:5px;">' . $icon . '</div>'
						. '<div style="white-space: normal; padding: 5px 5px; width:200px;">' . $dscr . '</div>
					</a>
				</div>';
		}
		return $s . '</div><div style="clear:left;"></div>'; 
	}


	// считаем сколько надо оплатить
	public function get_payment_methods($payment_address, $total, $to_deposit=false) {

		// Payment Methods
		$method_data = array();

		$this->load->model('setting/extension');

		$results = \model\setting\ExtensionDAO::getInstance()->getExtensions('payment');

		$cart_has_recurring = $this->cart->hasRecurringProducts();

		foreach ($results as $result) {
			$code = $result['code'];
			if ($this->config->get($code . '_status')) {
				if ($code == 'multi_pay' || $code == 'code' ) continue;
				$this->load->model('payment/' . $code);

				if ($to_deposit) {
					/// если ищем способы для пополнения депозита
					// то найдем те у кого есть метод get_info
					if(method_exists($this->{'model_payment_' . $code},'get_info')) {
						$method = $this->{'model_payment_' . $code}->get_info();
					 } else continue;
				} else {
					$method = $this->{'model_payment_' . $code}->getMethod($payment_address, $total);
				}


				if ($method) {
					if($cart_has_recurring > 0){
						if (method_exists($this->{'model_payment_' . $code},'recurringPayments')) {
							if($this->{'model_payment_' . $code}->recurringPayments() == true){
								$method_data[$code] = $method;
							}
						}
					} elseif ( $to_deposit ) {
						// если на входе отбирать только для пополнения депозита
						if ( _g($method,'to_deposit') ) {
							$method_data[$code] = $method;
						}
					} else {
						$method_data[$code] = $method;
					}					
				}
			}
		}

		$sort_order = array(); 

		foreach ($method_data as $key => $value) {
			$sort_order[$key] = $value['sort_order'];
		}

		array_multisort($sort_order, SORT_ASC, $method_data);			

		return $method_data;	
	}

	// адрес плательщика - по первой строчке
	public function get_payment_address($customer_id, $payment_address_1) {
		$sql = "SELECT * FROM " . DB_PREFIX . "address WHERE address_1 = '"
			. $payment_address_1 . "' AND customer_id = '" . $customer_id . "'";
		return $this->db->query($sql);
	}
	// адрес плательщика - по первой строчке
	public function get_customer_address($customer_id) {
		$sql = "SELECT * FROM " . DB_PREFIX . "address WHERE customer_id = '" . $customer_id . "'";
		return $this->db->query($sql);
	}
	

}
?>
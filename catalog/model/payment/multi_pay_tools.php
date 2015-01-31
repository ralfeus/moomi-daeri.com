<?php

class ModelPaymentMultiPayTools extends Model {

	// выдает кнопки для управления оплатой
	public function get_order_descr($order_id) {	
		
		$s = $this->url->link('account/multi_pay/order', '&order_id=' .$order_id, 'SSL');
		$this->language->load('payment/multi_pay_tools');
		// если еще можно доплатить то кнопку покажем
		$s = '<a href="' . $s . '" class="button">' . $this->language->get('b_pay_url'). '</a>';
		return sprintf($this->language->get('t_pay_url'), $s);
	}

	// для записи в историю заказа что для него пришло уведомление от платежной службы
	// если статус заказ был больше 2-х то статус не меняем - так как заказ мог быть отменен
	public function order_bill_update($order_id, $order_info, $new_status_id, $description='') {
		$order_status_id = (int)$order_info['order_status_id'];
		if ($order_status_id > 2) {
			// статус не Ожидание и не Обработка - значит статус нельзя менять
			$new_status_id = $order_status_id;
		}
		// заказ сформирован и еще не оплачен
		// - только для такого можно создавать счета на оплату
		$this->load->model('checkout/order');
				$this->model_checkout_order->update(
					$order_id
					,$new_status_id
					,$description
					,true);
	}

	// для записи в историю заказа что для него был создан счет в платежной службе
	// при этом статус заказ не меняем!
	public function order_bill_make($order_id, $order_info, $description='') {
		$order_status_id = (int)$order_info['order_status_id'];
		if ($order_status_id == 1) {
			// заказ сформирован и еще не оплачен
			// - только для такого можно создавать счета на оплату
			$this->load->model('checkout/order');
			$this->model_checkout_order->update(
				$order_id
				,order_status_id // статус не меняем при создании счета (он уже должен быть 1
				,$description
				,true);
		}
	}
	
	// при вызове способа оплаты - создаем заказ из корзины
	// если заказ уже создан то игнорируем
	public function order_confirm($order_id, $description='', $order_info=false) {
		$this->load->model('checkout/order');
		
		// если заказ еще из базы не получили - возьмем его
		if (!$order_info) $order_info = $this->model_checkout_order->getOrder($order_id);
		
		// проверим, может уже создан был заказ с помощью Multi_Pay или другого способа
		if ( (int)$order_info['order_status_id'] == 0) {
			// корзина еще не подтверждалась - "подтверждаем заказ" из корзины
			$this->model_checkout_order->confirm(
					$order_id
					,1 // Ожидание $status_id
					,$this->get_order_descr($order_id) . ' ' . $description
					,true);
			
			// и теперь очищаем корзину
            $this->cart->clear();
            unset($this->session->data['shipping_method']);
            unset($this->session->data['shipping_methods']);
            unset($this->session->data['payment_method']);
            unset($this->session->data['payment_methods']);
            unset($this->session->data['guest']);
            unset($this->session->data['comment']);
            unset($this->session->data['order_id']);
            unset($this->session->data['coupon']);
            unset($this->session->data['reward']);
            unset($this->session->data['voucher']);
            unset($this->session->data['vouchers']);
		}
	}
	
/////////////////////////////////////////////

	// это депозит или заказ?
	public function is_deposit($label) {				
		if (substr($label,0,3) == 'DEP') {
			return (int)substr($label, 3);
		}
	}

	// взять платеж по его ИД
	public function get_payment($payment_id) {		
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "multi_pay_payment` WHERE payment_id = '" . $payment_id  . "'");
		return $query->row;
	}
	
	// проверить на такой идентификатоттр платежа от заданной службы платежной
	public function ckeck_payment($operation_id, $service_cod) {		
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "multi_pay_payment` WHERE operation_id = '"
			. $operation_id  . "' and service_cod='" . $service_cod . "'");
		return $query->row;
	}

	// запоним платеж - сколько из оплату заказа и сколько на депозит
	// если такой платеж уже был то игнор
	// если статус заказа !=1 то в депозит катаем
	// если суммы хватило то статус заказ в 2 и отсаток на депозит катаем
	// если номер зака пуст то все в депозит катаем
	public function add_payment($service_cod, $service_account, $operation_id,  $datetime, $curr,
			$amount, $order_id, $customer_id, $description) {
	
		$row = $this->ckeck_payment($operation_id, $service_cod);
		if ($row) {
			// уже запись обрабатывали
			//trigger_error ( 'ERROR: service[' . $service_cod . '] operation_id: ' . $operation_id .' - already exist<br>', E_USER_NOTICE );
			return $row;
		}
		$amo_to_deposit = 0;
		$curr_global = $this->config->get('config_currency');
		if ($order_id) {
			//загружаем модель заказа
			$this->load->model('checkout/order');
			// берем заказ
			$order_info = $this->model_checkout_order->getOrder($order_id);
			//$value_order = $order_info['currency_value']; - цена зазака в валюте которую выбрал пользователь
			//$curr_order = $order_info['currency_code']; - это валюта которую выбрал пользоователь при созддании счета
			$customer_id = $order_info['customer_id'];
			// не надо тут конвертировать цену заказа - она в глобальной валюте уже
			// $amount_conv = (float)$this->currency->convert($amount, $curr, $curr_global);
			$amount_conv = (float)$amount;
			$order_total = (float)$order_info['total'];
			$amount_conv_left = $amount_conv - $order_total;
			if ($amount_conv_left < 0) {
				// не весь заказ покрывается - переведем его весь на депозит тогда
				$amount_conv_left = $amount_conv;
			} else {
				// закрываем заказ и остаток в депозит
				$order_status_id = $order_info['order_status_id'];
				if ((int)$order_status_id == 1) {
					// статус заказа был - ожидание - засчитываем платеж в его оплату
					$description_o = $service_cod . ':' . $service_account . '->' . $order_total . $curr_global . ' op_id:'
							. $operation_id .' dt:' . $datetime;
					$rec_id = $this->model_checkout_order->update(
						$order_id,
						2, // Обработка - заказ оплачен
						$description_o,
						true);
						//trigger_error ( 'ORDER rec_id:' . $rec_id . ' -> ' . $order_total . $curr_global, E_USER_NOTICE );			
				} else {
					// это не новый заказ -- по нему уже что-то было оплата или еще что-то
					$amount_conv_left = $amount_conv;
				}				
			}
			//$amo_to_deposit = $this->currency->convert($amount_conv_left, $curr_order, $curr_global);
			$amo_to_deposit = $amount_conv_left;
		} else {
			// нет заказа на входе - всю сумму в депозит
			//$amo_to_deposit = (float)$this->currency->convert($amount, $curr, $curr_global);
			$amo_to_deposit = (float)$amount;
			$order_id = 0;
		}
		
		// если осталось что-то после оплаты заказ - на депозит положим
		if ($amo_to_deposit > 0) {
			// если остался депозит (переплата) то занесем его на депозит покупателю
			$description_d = $service_cod . ':' . $service_account . '->' . $amount . $curr . ' op_id:'
							. $operation_id .' dt:' . $datetime;
			//$this->load->model('account/multi_pay_tools');
			$rec_id = $this->update_deposit($customer_id, $amo_to_deposit, $description_d);
			//trigger_error ( 'DEPOSIT rec_id:' . $rec_id . ' -> ' . $amo_to_deposit . $curr_global, E_USER_NOTICE );			
		}

		//сохраняем
		$id_tr = $this->db->query('insert ignore into '.DB_PREFIX.'multi_pay_payment set '
			. ' service_cod="' . $service_cod . '", service_account="' . $service_account . '",'
			. ' operation_id="' . $operation_id.'", datetime="'.$datetime.'", curr="'. $curr.'", amount='. $amount
			. ', order_id=' . $order_id.', customer_id='. $customer_id . ', description="' . $description . '"');
		//trigger_error ( ' id_tr ' . $id_tr, E_USER_NOTICE );			

	}

	// изменим данные по депозиту пользователя
	// добавляется транзакция
	public function update_deposit($customer_id, $amount, $description) {		

		$sql = 'INSERT INTO ' . DB_PREFIX . 'customer_transaction SET '
			. 'customer_id = ' . $customer_id
			. ', order_id = 0'
			. ', amount = ' . $amount
			. ', description = "' . $description . '"'
			. ', date_added = NOW()';
		//trigger_error ( ' sql: ' . $sql, E_USER_NOTICE );
		$this->db->query($sql);

	}

	private function send_mail() {
		// взято из adm\controller\sale\customer.php
		//$mail = new Mail();
		//$mail->protocol = $this->config->get('config_mail_protocol');
		//$mail->parameter = $this->config->get('config_mail_parameter');
		//$mail->hostname = $this->config->get('config_smtp_host');
		//$mail->username = $this->config->get('config_smtp_username');
		//$mail->password = $this->config->get('config_smtp_password');
		//$mail->port = $this->config->get('config_smtp_port');
		//$mail->timeout = $this->config->get('config_smtp_timeout');
		//$mail->setTo($customer_info['email']);
		//$mail->setFrom($this->config->get('config_email'));
		//mail->setSender($store_name);
		//$mail->setSubject(html_entity_decode(sprintf($this->language->get('text_transaction_subject'), $this->config->get('config_name')), ENT_QUOTES, 'UTF-8'));
		//$mail->setText(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
		//$mail->send();
	}

}
?>
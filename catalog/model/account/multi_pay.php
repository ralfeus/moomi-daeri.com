<?php 

// это для работы с аккаунтом пользователя - депозитами его
// поэтому не записываем его в payment/
class ModelAccountMultiPay extends Model {

	// выдает кнопки для управления оплатой и отменой заказа в страницу account/order/info
	public function order_info($order_info) {	
		//trigger_error ( ' order_info: ' . json_encode($order_info), E_USER_NOTICE );
		
		$s = $this->url->link('account/multi_pay/order', '&order_id=' .$order_info['order_id'], 'SSL');
		$this->language->load('account/multi_pay');
		// если еще можно доплатить то кнопку покажем
		$pay = ($order_info['total'] > 0 and $order_info['order_status_id'] == 1)?
			'<a href="' . $s . '" class="button">' . $this->language->get('text_pay'). '</a>': '';
		$s = $this->url->link('account/multi_pay/cancel', '&order_id=' .$order_info['order_id'], 'SSL');
		// если статус - обработка (его оплатили) - то его можно отменить и вернуть на депозит деньги
		$cancel = $order_info['order_status_id'] ==2? 
			'<a href="' . $s . '" class="button">' . $this->language->get('text_cancel'). '</a>': '';
		return $pay . ' ' . $cancel;
	}

}
?>
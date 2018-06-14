<?php
class ModelPaymentMultiPayDep extends \system\engine\Model {

	// вся инфо по модулю
	public function get_info() {
		$this->language->load('payment/multi_pay_dep');
		
		$this->load->model('account/transaction');
		$total = $this->model_account_transaction->getTotalAmount();
		return array(
				'code'       => 'multi_pay_dep',
				'title'      => $this->language->get('dep_text_title'),
				'description'=> $this->language->get('dep_text_description')
						. ' '. $total . $this->config->get('config_currency'),
				//'icon'		 => 'catalog/view/image/payment/cryptopay.png',
				//'action'	 => '', // ссылка на функцию для создания счета - по умолчанию: pay
				'sort_order' => $this->config->get('multi_pay_dep_sort_order'),
				'deposit'	 => true, // call by index.php?route=payment/MODULE_NAME/deposit
			);
	}

	public function getMethod($address, $total) {

		if ($total == 0) return;
		return $this->get_info();
	}
}
?>
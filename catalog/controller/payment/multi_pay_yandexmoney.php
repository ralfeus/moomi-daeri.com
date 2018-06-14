<?php
class ControllerPaymentMultiPayYandexMoney extends \system\engine\Controller {

// for test use LINK 
//  index.php?route=payment/multi_pay_yandexmoney/callback&test&operation_id=12345&sender=7654321&currency=643&codepro=false&notification_type=p2p-incoming&datetime=2014-10-07T07:11:42Z&label=zakaz+71&amount=12
// SET: &order_id=... &amount=... &operation_id=...
	public function callback() {
	
		$org_mode = ($this->config->get('yandexmoney_mode') == 2);

		$this->load->model('payment/multi_pay_yandexmoney');
		
		if (isset($_GET['test'])) {
			// если этот ест из строки браузера то без проверки сделаем
			$this->model_payment_multi_pay_yandexmoney->add_payment_test($_GET, $org_mode );
		} else {
			$this->model_payment_multi_pay_yandexmoney->add_payment($_POST, $org_mode );
		}
	}
}
?>
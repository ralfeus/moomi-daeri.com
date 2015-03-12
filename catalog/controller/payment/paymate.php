<?php 
class ControllerPaymentPaymate extends Controller {
	protected function index() {
    	$this->data['button_confirm'] = $this->language->get('button_confirm');

		$this->load->model('checkout/order');
		
		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		
		$this->data['action'] = 'https://www.paymate.com/PayMate/ExpressPayment';
		
		$this->data['mid'] = $this->config->get('paymate_username');

		$this->data['return'] = $this->url->link('payment/paymate/callback', 'oid=' . $order_info['order_id'] . '&conf=' . base64_encode($order_info['payment_firstname'] . $order_info['payment_lastname']));

		if ($this->config->get('paymate_include_order')) {
			$this->data['ref'] = html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8') . " (#" . $order_info['order_id'] . ")";
		} else {
			$this->data['ref'] = html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8');
		}

		$currency = array(
			'AUD',
			'NZD',
			'USD',
			'EUR',
			'GBP'
		);

		if (in_array(strtoupper($order_info['currency_code']), $currency)) {
			$this->data['currency'] = $order_info['currency_code'];
			$this->data['amt'] = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false); 
		} else {
			for ($findcur = 0; $findcur < sizeof($currency); $findcur++) {
				if ($this->currency->getValue($currency[$findcur])) {
					$this->data['currency'] = $currency[$findcur];
					$this->data['amt'] = $this->currency->format($order_info['total'], $currency[$findcur], '',false);
					break;
				} elseif ($findcur == (sizeof($currency) - 1)){
					$this->data['currency'] = 'AUD';
					$this->data['amt'] = $order_info['total'];
				}
			}
		}

		$this->data['pmt_contact_firstname'] = html_entity_decode($order_info['payment_firstname'], ENT_QUOTES, 'UTF-8');
		$this->data['pmt_contact_surname'] = html_entity_decode($order_info['payment_lastname'], ENT_QUOTES, 'UTF-8');
		$this->data['pmt_contact_phone'] = $order_info['telephone'];
		$this->data['pmt_sender_email'] = $order_info['email'];
		$this->data['regindi_address1'] = html_entity_decode($order_info['payment_address_1'], ENT_QUOTES, 'UTF-8');
		$this->data['regindi_address2'] = html_entity_decode($order_info['payment_address_2'], ENT_QUOTES, 'UTF-8');
		$this->data['regindi_sub'] = html_entity_decode($order_info['payment_city'], ENT_QUOTES, 'UTF-8');
		$this->data['regindi_state'] = html_entity_decode($order_info['payment_zone'], ENT_QUOTES, 'UTF-8');
		$this->data['regindi_pcode'] = html_entity_decode($order_info['payment_postcode'], ENT_QUOTES, 'UTF-8');
		$this->data['pmt_country'] = $order_info['payment_iso_code_2'];
		
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/paymate.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/payment/paymate.tpl';
		} else {
			$this->template = 'default/template/payment/paymate.tpl';
		}	
		
		$this->render();
	}
	
	public function callback() {
	 	$this->load->language('payment/paymate');
		
		$error = '';

		if (isset($this->request->post['responseCode'])) {
			if($this->request->post['responseCode'] == 'PA' || $this->request->post['responseCode'] == 'PP') {
				if (isset($this->request->get['oid']) && isset($this->request->get['conf'])) {
					$order_id = base64_decode($this->request->get['oid']);

					$this->load->model('checkout/order');
					
					$order_info = $this->model_checkout_order->getOrder($order_id);

					if ((isset($order_info['payment_firstname']) && isset($order_info['payment_lastname'])) && strcmp(base64_decode($this->request->get['conf']), $order_info['payment_firstname'] . $order_info['payment_lastname']) == 0) {
						$this->model_checkout_order->confirm($order_id, $this->config->get('paymate_order_status_id'));
					} else {
						$error = $this->language->get('text_unable');
					}
				} else {
					$error = $this->language->get('text_unable');
				}
			} else {
				$error = $this->language->get('text_declined'); 
			}
		} else {
			$error = $this->language->get('text_unable');
		}

		if ($error != '') {
			$this->data['heading_title'] = $this->language->get('text_failed');
			$this->data['text_message'] = sprintf($this->language->get('text_failed_message'), $error, $this->url->link('information/contact'));
			$this->data['button_continue'] = $this->language->get('button_continue');
			$this->data['continue'] = $this->url->link('common/home');
			
			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/common/success.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/common/success.tpl';
			} else {
				$this->template = 'default/template/common/success.tpl';
			}
			
			$this->children = array(
				'common/column_left',
				'common/column_right',
				'common/content_top',
				'common/content_bottom',
				'common/footer',
				'common/header'
			);
			
			$this->getResponse()->setOutput($this->render());
		} else {
			$this->redirect($this->url->link('checkout/success'));
		}
	}
}
?>
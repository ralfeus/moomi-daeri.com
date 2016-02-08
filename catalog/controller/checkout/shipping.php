<?php
use model\shipping\ShippingMethodDAO;
use system\exception\NotImplementedException;

class ControllerCheckoutShipping extends Controller {
  	public function index() {
//        $this->log->write("Opening checkout/shipping");
		$this->language->load('checkout/checkout');

		$json = array();

		$this->load->model('account/address');

		if ($this->customer->isLogged()) {
			$shipping_address = $this->model_account_address->getAddress($this->session->data['shipping_address_id']);
		} elseif (isset($this->session->data['guest'])) {
			$shipping_address = $this->session->data['guest']['shipping'];
		}
		if (empty($shipping_address)) {
			$json['redirect'] = $this->url->link('checkout/checkout', '', 'SSL');
		}

		if ((!$this->cart->hasProducts() && (!isset($this->session->data['vouchers']) || !$this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
			$json['redirect'] = $this->url->link('checkout/cart');
		}

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			if (!$json) {
				if (!isset($this->request->post['shipping_method'])) {
					$json['error']['warning'] = $this->language->get('error_shipping');
				} else {
					$shipping = explode('.', $this->request->post['shipping_method']);
					if (!isset($this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]])) {
						$json['error']['warning'] = $this->language->get('error_shipping');
					}
				}
			}

			if (!$json) {
				$shipping = explode('.', $this->request->post['shipping_method']);

				$this->session->data['shipping_method'] = $this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]];

				$this->session->data['comment'] = strip_tags($this->request->post['comment']);
			}
		} else {
            if (isset($shipping_address)) {
				if (!isset($this->session->data['shipping_methods'])) {
					$quote_data = array();

					$this->load->model('setting/extension');
					$this->load->model('localisation/description');

					$results = $this->model_setting_extension->getExtensions('shipping');
                    $this->log->write(print_r($results, true));
//print_r($results); die();
				foreach ($results as $result) {
			  		$shippingMethod = ShippingMethodDAO::getInstance()->getMethod($result['code']);
			  		if ($shippingMethod->isEnabled()) {
//						if ($this->config->get($result['code'] . '_status')) {
//                            $this->log->write("Trying to load shipping/" . $result['code']);
//							$this->load->model('shipping/' . $result['code']);
						$quote = $shippingMethod->getQuote($shipping_address);
						//                            $this->log->write(print_r($quote, true));

						if ($quote) {
							//$res = $this->model_localisation_description->getDescription(null, $quote['quote'][$result['code']]['title']);
							//$tempArr = reset($quote['quote']);
							//$desc = '' . print_r($tempArr[@description], true);
							//print_r($quote['quote']); die();
							$quote_data[$result['code']] = array(
								'title'      => $quote['title'],
								//'description'=> $desc,
								'quote'      => $quote['quote'],
								'sort_order' => $quote['sort_order'],
								'error'      => $quote['error']
							);
						}
					}
				}

					$sort_order = array();

					foreach ($quote_data as $key => $value) {
						$sort_order[$key] = $value['sort_order'];
					}

					array_multisort($sort_order, SORT_ASC, $quote_data);
					//print_r($quote_data); die();
					$this->session->data['shipping_methods'] = $quote_data;
				}
			}

			$this->data['text_shipping_method'] = $this->language->get('text_shipping_method');
			$this->data['text_comments'] = $this->language->get('text_comments');

			$this->data['button_continue'] = $this->language->get('button_continue');

			if (isset($this->session->data['shipping_methods']) && !$this->session->data['shipping_methods']) {
				$this->data['error_warning'] = sprintf($this->language->get('error_no_shipping'), $this->url->link('information/contact'));
			} else {
				$this->data['error_warning'] = '';
			}

			if (isset($this->session->data['shipping_methods'])) {
				$this->data['shipping_methods'] = $this->session->data['shipping_methods'];
			} else {
				$this->data['shipping_methods'] = array();
			}

			if (isset($this->session->data['shipping_method']['code'])) {
				$this->data['code'] = $this->session->data['shipping_method']['code'];
			} else {
				$this->data['code'] = '';
			}

			if (isset($this->session->data['comment'])) {
				$this->data['comment'] = $this->session->data['comment'];
			} else {
				$this->data['comment'] = '';
			}

			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/checkout/shipping.tpl.php')) {
				$this->template = $this->config->get('config_template') . '/template/checkout/shipping.tpl.php';
			} else {
				$this->template = 'default/template/checkout/shipping.tpl.php';
			}

			$json['output'] = $this->render();
		}
		//$this->log->write(print_r($json, true));
		$this->getResponse()->setOutput(json_encode($json));
  	}
}
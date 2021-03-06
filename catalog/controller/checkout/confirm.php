<?php
use model\shipping\ShippingMethodDAO;
use system\engine\CustomerController;

class ControllerCheckoutConfirm extends CustomerController {
    /** @var ModelCheckoutOrder  */
    private $modelCheckoutOrder;

    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->modelCheckoutOrder = $this->load->model('checkout/order');
    }

    public function confirm()
    {
//        $this->log->write("ControllerPaymentDeposit::confirm()");
        $this->modelCheckoutOrder->confirm($this->session->data['order_id'], ORDER_STATUS_IN_PROGRESS);
        $this->redirect($this->url->link('checkout/success', '', 'SSL'));
        /// Decrease deposit amount
        //$this->load->model('account/customer_deposit');
        //$order = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        //$this->model_account_customer_deposit->subtractAmount($order);
    }

	public function index() {
		if ((!$this->getCart()->hasProducts() && (!isset($this->session->data['vouchers']) || !$this->session->data['vouchers'])) || (!$this->getCart()->hasStock() && !$this->getConfig()->get('config_stock_checkout'))) {
	  		$json['redirect'] = $this->url->link('checkout/cart');
    	}

		$this->load->model('account/address');

    	if ($this->getCart()->hasShipping()) {
			$this->load->model('account/address');

			if ($this->customer->isLogged()) {
				$shippingAddress = $this->model_account_address->getAddress($this->session->data['shipping_address_id']);
			} elseif (isset($this->session->data['guest'])) {
				$shippingAddress = $this->session->data['guest']['shipping'];
			}

			if (!isset($shippingAddress)) {
				$json['redirect'] = $this->url->link('checkout/checkout', '', 'SSL');
			}

			if (!isset($this->session->data['shipping_method'])) {
	  			$json['redirect'] = $this->url->link('checkout/checkout', '', 'SSL');
    		}
		} else {
			unset($this->session->data['guest']['shipping']);
			unset($this->session->data['shipping_address_id']);
			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
		}

		$json = array();

		if (!$json) {
			$total_data = array();
			$total = 0;
			$taxes = $this->getCart()->getTaxes(true);
            $this->modelCheckoutOrder->getTotals($total_data, $total, $taxes, null, true);
//
//			$this->load->model('setting/extension');
//
//			$sort_order = array();
//
//			$results = \model\setting\ExtensionDAO::getInstance()->getExtensions('total');
//
//			foreach ($results as $key => $value) {
//				$sort_order[$key] = $this->getConfig()->get($value['code'] . '_sort_order');
//			}
//
//			array_multisort($sort_order, SORT_ASC, $results);
//
//			foreach ($results as $result) {
//				if ($this->getConfig()->get($result['code'] . '_status')) {
//					$this->load->model('total/' . $result['code']);
//
//					$this->{'model_total_' . $result['code']}->getTotal($total_data, $total, $taxes, true);
//				}
//			}
//
//			$sort_order = array();
//
//			foreach ($total_data as $key => $value) {
//				$sort_order[$key] = $value['sort_order'];
//			}
//
//			array_multisort($sort_order, SORT_ASC, $total_data);

			$this->language->load('checkout/checkout');

			$data = array();

			$data['invoice_prefix'] = $this->getConfig()->get('config_invoice_prefix');
			$data['store_id'] = $this->getConfig()->get('config_store_id');
			$data['store_name'] = $this->getConfig()->get('config_name');

			if ($data['store_id']) {
				$data['store_url'] = $this->getConfig()->get('config_url');
			} else {
				$data['store_url'] = HTTP_SERVER;
			}

			if ($this->customer->isLogged()) {
				$data['customer_id'] = $this->customer->getId();
				$data['customer_group_id'] = $this->customer->getCustomerGroupId();
				$data['firstname'] = $this->customer->getFirstName();
				$data['lastname'] = $this->customer->getLastName();
				$data['email'] = $this->customer->getEmail();
				$data['telephone'] = $this->customer->getTelephone();
				$data['fax'] = $this->customer->getFax();

				$this->load->model('account/address');

				//$payment_address = $this->model_account_address->getAddress($this->session->data['payment_address_id']);
			} elseif (isset($this->session->data['guest'])) {
				$data['customer_id'] = 0;
				$data['customer_group_id'] = $this->getConfig()->get('config_customer_group_id');
				$data['firstname'] = $this->session->data['guest']['firstname'];
				$data['lastname'] = $this->session->data['guest']['lastname'];
				$data['email'] = $this->session->data['guest']['email'];
				$data['telephone'] = $this->session->data['guest']['telephone'];
				$data['fax'] = $this->session->data['guest']['fax'];

//				$payment_address = $this->session->data['guest']['payment'];
			}

			if (isset($this->session->data['payment_method']['title'])) {
				$data['payment_method'] = $this->session->data['payment_method']['title'];
			} else {
				$data['payment_method'] = '';
			}

			if ($this->getCart()->hasShipping()) {
				$shippingAddress = [];
				if ($this->customer->isLogged()) {
					$this->load->model('account/address');
					$shippingAddress = $this->model_account_address->getAddress($this->session->data['shipping_address_id']);
				} elseif (isset($this->session->data['guest'])) {
					$shippingAddress = $this->session->data['guest']['shipping'];
				}
				try {
					$shippingMethod = explode('.', $this->session->data['shipping_method']['code']);
					$shippingAddress = array_merge(
						$shippingAddress,
						ShippingMethodDAO::getInstance()->getMethod($shippingMethod[0])->getAddress($shippingMethod[1])
					);
				} catch (Exception $exc) {}


				$data['shipping_firstname'] = $shippingAddress['firstname'];
				$data['shipping_lastname'] = $shippingAddress['lastname'];
				$data['shipping_company'] = $shippingAddress['company'];
				$data['shipping_phone'] = $shippingAddress['phone'];
				$data['shipping_address_1'] = $shippingAddress['address_1'];
				$data['shipping_address_2'] = $shippingAddress['address_2'];
				$data['shipping_city'] = $shippingAddress['city'];
				$data['shipping_postcode'] = $shippingAddress['postcode'];
				$data['shipping_zone'] = $shippingAddress['zone'];
				$data['shipping_zone_id'] = $shippingAddress['zone_id'];
				$data['shipping_country'] = $shippingAddress['country'];
				$data['shipping_country_id'] = $shippingAddress['country_id'];
				$data['shipping_address_format'] = $shippingAddress['address_format'];

				if (isset($this->session->data['shipping_method']['title'])) {
                    //$this->log->write(print_r($this->session->data['shipping_method'], true));
					//$data['shipping_method'] = $this->session->data['shipping_method']['title'];
                    $data['shipping_method'] = $this->session->data['shipping_method']['code'];
				} else {
					$data['shipping_method'] = '';
				}
			} else {
				$data['shipping_firstname'] = '';
				$data['shipping_lastname'] = '';
				$data['shipping_company'] = '';
				$data['shipping_phone'] = '';
				$data['shipping_address_1'] = '';
				$data['shipping_address_2'] = '';
				$data['shipping_city'] = '';
				$data['shipping_postcode'] = '';
				$data['shipping_zone'] = '';
				$data['shipping_zone_id'] = '';
				$data['shipping_country'] = '';
				$data['shipping_country_id'] = '';
				$data['shipping_address_format'] = '';
				$data['shipping_method'] = '';
			}

			//$this->load->library('encryption');

			$product_data = array();

			foreach ($this->getCart()->getProducts(true) as $product) {
				$option_data = array();

				foreach ($product['option'] as $option) {
					if ($option['type'] != 'file') {
						$option_data[] = array(
							'product_option_id'       => $option['product_option_id'],
							'product_option_value_id' => $option['product_option_value_id'],
//							'product_option_id'       => $option['product_option_id'],
//							'product_option_value_id' => $option['product_option_value_id'],
							'option_id'               => $option['option_id'],
							'option_value_id'         => $option['option_value_id'],
							'name'                    => $option['name'],
							'value'                   => $option['option_value'],
							'type'                    => $option['type']
						);
					} else {
						$encryption = new Encryption($this->getConfig()->get('config_encryption'));

						$option_data[] = array(
							'product_option_id'       => $option['product_option_id'],
							'product_option_value_id' => $option['product_option_value_id'],
//							'product_option_id'       => $option['product_option_id'],
//							'product_option_value_id' => $option['product_option_value_id'],
							'option_id'               => $option['option_id'],
							'option_value_id'         => $option['option_value_id'],
							'name'                    => $option['name'],
							'value'                   => $encryption->decrypt($option['option_value']),
							'type'                    => $option['type']
						);
					}
				}

				$product_data[] = array(
					'product_id' => $product['product_id'],
					'name'       => $product['name'],
					'model'      => $product['model'],
					'option'     => $option_data,
					'download'   => $product['download'],
					'quantity'   => $product['quantity'],
					'subtract'   => $product['subtract'],
					'price'      => $product['price'],
					'total'      => $product['total'],
					'tax'        => $this->tax->getTax($product['total'], $product['tax_class_id'])
				);
			}

			// Gift Voucher
			if (isset($this->session->data['vouchers']) && $this->session->data['vouchers']) {
				foreach ($this->session->data['vouchers'] as $voucher) {
					$product_data[] = array(
						'product_id' => 0,
						'name'       => $voucher['description'],
						'model'      => '',
						'option'     => array(),
						'download'   => array(),
						'quantity'   => 1,
						'subtract'   => false,
						'price'      => $voucher['amount'],
						'total'      => $voucher['amount'],
						'tax'        => 0
					);
				}
			}

			$data['products'] = $product_data;
			$data['totals'] = $total_data;
			$data['comment'] = $this->session->data['comment'];
			$data['total'] = $total;
			$data['reward'] = $this->getCart()->getTotalRewardPoints();

			$this->customer->setAffiliateId();
			$data['affiliate_id'] = $this->customer->getAffiliateId();
			$data['commission'] = 0;

			$data['language_id'] = $this->getConfig()->get('config_language_id');
			$data['currency_id'] = $this->getCurrency()->getId();
			$data['currency_code'] = $this->getCurrency()->getCode();
			$data['currency_value'] = $this->getCurrency()->getValue($this->getCurrency()->getCode());
			$data['ip'] = $this->request->server['REMOTE_ADDR'];

			$this->session->data['order_id'] = $this->modelCheckoutOrder->create($data);

			// Gift Voucher
			if (isset($this->session->data['vouchers']) && is_array($this->session->data['vouchers'])) {
				$this->load->model('checkout/voucher');

				foreach ($this->session->data['vouchers'] as $voucher) {
					$this->model_checkout_voucher->addVoucher($this->session->data['order_id'], $voucher);
				}
			}

			$this->data['column_name'] = $this->language->get('column_name');
			$this->data['column_model'] = $this->language->get('column_model');
			$this->data['column_quantity'] = $this->language->get('column_quantity');
			$this->data['column_price'] = $this->language->get('column_price');
			$this->data['column_total'] = $this->language->get('column_total');

			$this->data['products'] = array();

			foreach ($this->getCart()->getProducts(true) as $product) {
				$option_data = array();
	
				foreach ($product['option'] as $option) {
					if ($option['type'] != 'file') {
						$option_data[] = array(
							'name'  => $option['name'],
							'value' => utf8_truncate($option['option_value'])
						);
					} else {
						//$this->load->library('encryption');
						
						$encryption = new Encryption($this->getConfig()->get('config_encryption'));
						
						$file = substr($encryption->decrypt($option['option_value']), 0, strrpos($encryption->decrypt($option['option_value']), '.'));
						
						$option_data[] = array(
							'name'  => $option['name'],
							'value' => utf8_truncate($file)
						);												
					}
				}  
	 
				$this->data['products'][] = array(
					'product_id' => $product['product_id'],
					'name'       => $product['name'],
					'model'      => $product['model'],
					'option'     => $option_data,
					'quantity'   => $product['quantity'],
					'subtract'   => $product['subtract'],
					'tax'        => $this->tax->getTax($product['total'], $product['tax_class_id']),
					'price'      => $this->getCurrency()->format($product['price']),
					'total'      => $this->getCurrency()->format($product['total']),
					'href'       => $this->url->link('product/product', 'product_id=' . $product['product_id'])
				); 
			} 
			
			// Gift Voucher
			$this->data['vouchers'] = array();
			
			if (isset($this->session->data['vouchers']) && $this->session->data['vouchers']) {
				foreach ($this->session->data['vouchers'] as $key => $voucher) {
					$this->data['vouchers'][] = array(
						'description' => $voucher['description'],
						'amount'      => $this->getCurrency()->format($voucher['amount'])
					);
				}
			}  
						
			$this->data['totals'] = $total_data;
	
			$this->data['payment'] = $this->getChild('payment/' . $this->getSession()->data['payment_method']['code']);
            $this->data['button_confirm'] = $this->language->get('button_confirm');
            $this->data['urlConfirm'] = $this->url->link('checkout/confirm/confirm', '', 'SSL');
            $this->data['continue'] = $this->url->link('checkout/success');

            $templateName = '/template/checkout/confirm.tpl.php';
			if (file_exists(DIR_TEMPLATE . $this->getConfig()->get('config_template') . $templateName)) {
				$this->template = $this->getConfig()->get('config_template') . $templateName;
			} else {
				$this->template = 'default' . $templateName;
			}
		
			$json['output'] = $this->render();
		}
//		$this->log->write(print_r($json, true));
		$this->getResponse()->setOutput(json_encode($json));
  	}

    protected function initParameters()
    {
        $this->parameters['selected'] = empty($_REQUEST['selected']) ? null : $_REQUEST['selected'];
    }
}

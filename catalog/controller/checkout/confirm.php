<?php
class ControllerCheckoutConfirm extends Controller {
    private $modelCheckoutOrder;

    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->modelCheckoutOrder = $this->load->model('checkout/order');
    }

    public function confirm()
    {
//        $this->log->write("ControllerPaymentDeposit::confirm()");
        $this->modelCheckoutOrder->confirm($this->session->data['order_id'], OS_IN_PROGRESS);
        $this->response->redirect($this->url->link('checkout/success', '', 'SSL'));
        /// Decrease deposit amount
        //$this->load->model('account/customer_deposit');
        //$order = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        //$this->model_account_customer_deposit->subtractAmount($order);
    }

	public function index() {
		if ((!$this->cart->hasProducts() && (!isset($this->session->data['vouchers']) || !$this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
	  		$json['redirect'] = $this->url->link('checkout/cart');
    	}			
		
		$this->load->model('account/address');
		
    	if ($this->cart->hasShipping()) {
			$this->load->model('account/address');
			
			if ($this->customer->isLogged()) {
				$shipping_address = $this->model_account_address->getAddress($this->session->data['shipping_address_id']);		
			} elseif (isset($this->session->data['guest'])) {
				$shipping_address = $this->session->data['guest']['shipping'];
			}				

			if (!isset($shipping_address)) {								
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
			$taxes = $this->cart->getTaxes(true);
            $this->modelCheckoutOrder->getTotals($total_data, $total, $taxes, null, true);
//
//			$this->load->model('setting/extension');
//
//			$sort_order = array();
//
//			$results = $this->model_setting_extension->getExtensions('total');
//
//			foreach ($results as $key => $value) {
//				$sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
//			}
//
//			array_multisort($sort_order, SORT_ASC, $results);
//
//			foreach ($results as $result) {
//				if ($this->config->get($result['code'] . '_status')) {
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
			
			$data['invoice_prefix'] = $this->config->get('config_invoice_prefix');
			$data['store_id'] = $this->config->get('config_store_id');
			$data['store_name'] = $this->config->get('config_name');
			
			if ($data['store_id']) {
				$data['store_url'] = $this->config->get('config_url');		
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
				$data['customer_group_id'] = $this->config->get('config_customer_group_id');
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
			
			if ($this->cart->hasShipping()) {
				if ($this->customer->isLogged()) {
					$this->load->model('account/address');
					
					$shipping_address = $this->model_account_address->getAddress($this->session->data['shipping_address_id']);
				} elseif (isset($this->session->data['guest'])) {
					$shipping_address = $this->session->data['guest']['shipping'];
				}			
				
				$data['shipping_firstname'] = $shipping_address['firstname'];
				$data['shipping_lastname'] = $shipping_address['lastname'];	
				$data['shipping_company'] = $shipping_address['company'];	
				$data['shipping_phone'] = $shipping_address['phone'];
				$data['shipping_address_1'] = $shipping_address['address_1'];
				$data['shipping_address_2'] = $shipping_address['address_2'];
				$data['shipping_city'] = $shipping_address['city'];
				$data['shipping_postcode'] = $shipping_address['postcode'];
				$data['shipping_zone'] = $shipping_address['zone'];
				$data['shipping_zone_id'] = $shipping_address['zone_id'];
				$data['shipping_country'] = $shipping_address['country'];
				$data['shipping_country_id'] = $shipping_address['country_id'];
				$data['shipping_address_format'] = $shipping_address['address_format'];

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
			
			$this->load->library('encryption');
			
			$product_data = array();
		
			foreach ($this->cart->getProducts(true) as $product) {
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
						$encryption = new Encryption($this->config->get('config_encryption'));
						
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
			$data['reward'] = $this->cart->getTotalRewardPoints(true);
			
			if (isset($this->request->cookie['tracking'])) {
				$this->load->model('affiliate/affiliate');
				
				$affiliate_info = $this->model_affiliate_affiliate->getAffiliateByCode($this->request->cookie['tracking']);
				
				if ($affiliate_info) {
					$data['affiliate_id'] = $affiliate_info['affiliate_id']; 
					$data['commission'] = ($total / 100) * $affiliate_info['commission']; 
				} else {
					$data['affiliate_id'] = 0;
					$data['commission'] = 0;
				}
			} else {
				$data['affiliate_id'] = 0;
				$data['commission'] = 0;
			}
			
			$data['language_id'] = $this->config->get('config_language_id');
			$data['currency_id'] = $this->currency->getId();
			$data['currency_code'] = $this->currency->getCode();
			$data['currency_value'] = $this->currency->getValue($this->currency->getCode());
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
	
			foreach ($this->cart->getProducts(true) as $product) {
				$option_data = array();
	
				foreach ($product['option'] as $option) {
					if ($option['type'] != 'file') {
						$option_data[] = array(
							'name'  => $option['name'],
							'value' => utf8_truncate($option['option_value'])
						);
					} else {
						$this->load->library('encryption');
						
						$encryption = new Encryption($this->config->get('config_encryption'));
						
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
					'price'      => $this->currency->format($product['price']),
					'total'      => $this->currency->format($product['total']),
					'href'       => $this->url->link('product/product', 'product_id=' . $product['product_id'])
				); 
			} 
			
			// Gift Voucher
			$this->data['vouchers'] = array();
			
			if (isset($this->session->data['vouchers']) && $this->session->data['vouchers']) {
				foreach ($this->session->data['vouchers'] as $key => $voucher) {
					$this->data['vouchers'][] = array(
						'description' => $voucher['description'],
						'amount'      => $this->currency->format($voucher['amount'])
					);
				}
			}  
						
			$this->data['totals'] = $total_data;
	
//			$this->data['payment'] = $this->getChild('payment/deposit');
            $this->data['button_confirm'] = $this->language->get('button_confirm');
            $this->data['urlConfirm'] = $this->url->link('checkout/confirm/confirm', '', 'SSL');
            $this->data['continue'] = $this->url->link('checkout/success');

            $templateName = '/template/checkout/confirm.tpl';
			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . $templateName)) {
				$this->template = $this->config->get('config_template') . $templateName;
			} else {
				$this->template = 'default' . $templateName;
			}
		
			$json['output'] = $this->render();
		}
		$this->log->write(print_r($json, true));
		$this->response->setOutput(json_encode($json));		
  	}

    protected function initParameters()
    {
        $this->parameters['selected'] = empty($_REQUEST['selected']) ? null : $_REQUEST['selected'];
    }
}

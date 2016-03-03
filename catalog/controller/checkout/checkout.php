<?php  
class ControllerCheckoutCheckout extends Controller {
    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->language->load('checkout/checkout');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->data['heading_title'] = $this->language->get('heading_title');
    }

	public function index() {
		if ((!$this->cart->hasProducts() && !empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout')))
	  		$this->redirect($this->url->link('checkout/cart'));
        $this->session->data['selectedCartItems'] = count($this->parameters['selected']) ? $this->parameters['selected'] : null;

//        $this->log->write(print_r($this->parameters, true));
        $products = $this->cart->getProducts(true);
//        $this->log->write(print_r($products, true));
		foreach ($products as $product) {
			$product_total = 0;
				
			foreach ($products as $product_2) {
				if ($product_2['product_id'] == $product['product_id']) {
					$product_total += $product_2['quantity'];
				}
			}		
			
			if ($product['minimum'] > $product_total) {
				$this->redirect($this->url->link('checkout/cart'));
			}				
		}

        /// Set up interface
        $this->setBreadcrumbs([[
			'text' => $this->language->get('text_cart'),
			'route'      => 'checkout/cart'
		]]);
		$this->data['text_checkout_option'] = sprintf($this->language->get('text_checkout_option'));
		$this->data['text_checkout_account'] = $this->language->get('text_checkout_account');
		$this->data['text_checkout_payment_address'] = $this->language->get('text_checkout_payment_address');
		$this->data['text_checkout_shipping_address'] = $this->language->get('text_checkout_shipping_address');
		$this->data['text_checkout_shipping_method'] = $this->language->get('text_checkout_shipping_method');
		$this->data['text_checkout_payment_method'] = $this->language->get('text_checkout_payment_method');		
		$this->data['text_checkout_confirm'] = $this->language->get('text_checkout_confirm');
		$this->data['text_modify'] = $this->language->get('text_modify');
		
		$this->data['logged'] = $this->customer->isLogged();
		$this->data['shipping_required'] = $this->cart->hasShipping();	
		//$this->log->write(print_r($this->data, true));
		
		$templateFileName = '/template/checkout/checkout.tpl.php';
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . $templateFileName))
			$this->template = $this->config->get('config_template') . $templateFileName;
		else
			$this->template = 'default' . $templateFileName;
		//print_r($this->template); die();
		$this->children = array(
			'common/column_left',
			'common/column_right',
			'common/content_top',
			'common/content_bottom',
			'common/footer',
			'common/header'	
		);
				
		$this->getResponse()->setOutput($this->render());
  	}

    protected function initParameters() {
		$this->initParametersWithDefaults([
			'selected' => null
		]);
    }
}
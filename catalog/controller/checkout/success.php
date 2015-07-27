<?php 
class ControllerCheckoutSuccess extends Controller { 
	public function index() { 
		if (isset($this->session->data['order_id'])) {
            $this->cart->clear($this->session->data['selectedCartItems']);

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
									   
		$this->language->load('checkout/success');
		
		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->data['breadcrumbs'] = array(); 

      	$this->data['breadcrumbs'][] = array(
        	'href'      => $this->url->link('common/home'),
        	'text'      => $this->language->get('text_home'),
        	'separator' => false
      	); 
		
      	$this->data['breadcrumbs'][] = array(
        	'href'      => $this->url->link('checkout/cart'),
        	'text'      => $this->language->get('text_basket'),
        	'separator' => $this->language->get('text_separator')
      	);
				
		$this->data['breadcrumbs'][] = array(
			'href'      => $this->url->link('checkout/checkout', '', 'SSL'),
			'text'      => $this->language->get('text_checkout'),
			'separator' => $this->language->get('text_separator')
		);	
					
      	$this->data['breadcrumbs'][] = array(
        	'href'      => $this->url->link('checkout/success'),
        	'text'      => $this->language->get('text_success'),
        	'separator' => $this->language->get('text_separator')
      	);
		
    	$this->data['heading_title'] = $this->language->get('heading_title');

		if ($this->customer->isLogged()) {
            $text = $this->load->model('cms/text')->getText(CMS_ORDER_CONFIRMATION, $this->config->get('config_language_id'));
            if (empty($text['text']))
                $successText = $this->language->get('text_customer');
            else
                $successText = $text['text'];
            $this->data['text_message'] = $successText; //sprintf(
//                    $successText,
//                    $this->url->link('account/account', '', 'SSL'),
//                    $this->url->link('account/order', '', 'SSL'),
//                    $this->url->link('account/download', '', 'SSL'),
//                    $this->url->link('information/contact')
//                );
		} else {
    		$this->data['text_message'] = sprintf($this->language->get('text_guest'), $this->url->link('information/contact'));
		}
		
    	$this->data['button_continue'] = $this->language->get('button_continue');

    	$this->data['continue'] = $this->url->link('common/home');

        $templateName = '/template/common/success.tpl';
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . $templateName)) {
			$this->template = $this->config->get('config_template') . $templateName;
		} else {
			$this->template = 'default' . $templateName;
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
  	}
}
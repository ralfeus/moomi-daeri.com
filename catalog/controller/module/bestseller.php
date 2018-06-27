<?php
use catalog\model\tool\ModelToolImage;
use model\catalog\ProductDAO;
use system\engine\Controller;

class ControllerModuleBestSeller extends Controller {
	protected function index($setting) {
		$this->language->load('module/bestseller');
 
      	$this->data['heading_title'] = $this->language->get('heading_title');
		$this->data['button_cart'] = $this->language->get('button_cart');
		
		$this->data['products'] = array();

		$results = ProductDAO::getInstance()->getBestSellerProducts($setting['limit']);
		
		foreach ($results as $result) {
            $image = $result['image'] ? (new ModelToolImage($this->getRegistry()))->resize($result['image'], $setting['image_width'], $setting['image_height']) : false;
			
			if (($this->getConfig()->get('config_customer_price') && $this->customer->isLogged()) || !$this->getConfig()->get('config_customer_price')) {
				$price = $this->getCurrentCurrency()->format($this->getTax()->calculate($result['price'], $result['tax_class_id'], $this->getConfig()->get('config_tax')));
			} else {
				$price = false;
			}
					
			if (key_exists('special', $result) && (float)$result['special']) {
				$special = $this->getCurrentCurrency()->format($this->getTax()->calculate($result['special'], $result['tax_class_id'], $this->getConfig()->get('config_tax')));
			} else {
				$special = false;
			}	
			
			if ($this->getConfig()->get('config_review_status')) {
				$rating = $result['rating'];
			} else {
				$rating = false;
			}
							
			$this->data['products'][] = array(
				'product_id' => $result['product_id'],
				'thumb'   	 => $image,
				'name'    	 => key_exists('name', $result) ? $result['name'] : '',
				'price'   	 => $price,
				'special' 	 => $special,
				'rating'     => $rating,
				'reviews'    => sprintf($this->language->get('text_reviews'), key_exists('reviews', $result) ? (int)$result['reviews'] : 0),
				'href'    	 => $this->getUrl()->link('product/product', 'product_id=' . $result['product_id']),
			);
		}

		if (file_exists(DIR_TEMPLATE . $this->getConfig()->get('config_template') . '/template/module/bestseller.tpl.php')) {
			$template = $this->getConfig()->get('config_template') . '/template/module/bestseller.tpl.php';
		} else {
			$template = 'default/template/module/bestseller.tpl.php';
		}

		$this->render($template);
	}
}
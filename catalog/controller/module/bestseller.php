<?php
use catalog\model\tool\ModelToolImage;
use system\engine\Controller;

class ControllerModuleBestSeller extends \system\engine\Controller {
	protected function index($setting) {
		$this->language->load('module/bestseller');
 
      	$this->data['heading_title'] = $this->language->get('heading_title');
				
		$this->data['button_cart'] = $this->language->get('button_cart');
		
		$this->getLoader()->model('catalog/product');
		
		//$this->getLoader()->model('tool/image');

		$this->data['products'] = array();

		$results = $this->model_catalog_product->getBestSellerProducts($setting['limit']);
		
		foreach ($results as $result) {
            $image = $result['image'] ? (new ModelToolImage($this->getRegistry()))->resize($result['image'], $setting['image_width'], $setting['image_height']) : false;
			
			if (($this->getConfig()->get('config_customer_price') && $this->customer->isLogged()) || !$this->getConfig()->get('config_customer_price')) {
				$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->getConfig()->get('config_tax')));
			} else {
				$price = false;
			}
					
			if ((float)$result['special']) {
				$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->getConfig()->get('config_tax')));
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
				'name'    	 => $result['name'],
				'price'   	 => $price,
				'special' 	 => $special,
				'rating'     => $rating,
				'reviews'    => sprintf($this->language->get('text_reviews'), (int)$result['reviews']),
				'href'    	 => $this->url->link('product/product', 'product_id=' . $result['product_id']),
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
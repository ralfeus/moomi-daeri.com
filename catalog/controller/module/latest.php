<?php
class ControllerModuleLatest extends Controller {
    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->language->load('module/latest');
        $this->data['heading_title'] = $this->language->get('heading_title');
        $this->load->model('catalog/product');
        $this->load->model('tool/image');
    }

	protected function index($setting) {
		$this->data['button_cart'] = $this->language->get('button_cart');
		$this->data['products'] = array();

        if (isset($_REQUEST['latest_page']))
            $page = $_REQUEST['latest_page'];
        else
            $page = 1;

		$data = array(
			'sort'  => 'p.date_added',
			'order' => 'DESC',
			'start' => ($page - 1) * $setting['limit'],
			'limit' => $setting['limit'],
            'nocache'   => 1
//			'limit' => 20
		);
        //print_r($data);exit();

		$results = $this->model_catalog_product->getProducts($data);
        //print_r($results);exit();

		foreach ($results as $result) {
			if ($result['image']) {
				$image = $this->model_tool_image->resize($result['image'], $setting['image_width'], $setting['image_height']);
			} else {
				$image = false;
			}
						
			if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
				$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')));
			} else {
				$price = false;
			}
					
			if ((float)$result['special']) {
				$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')));
			} else {
				$special = false;
			}
			
			if ($this->config->get('config_review_status')) {
				$rating = $result['rating'];
			} else {
				$rating = false;
			}

            $date_added = getdate(strtotime($result['date_added']));
            $date_added = mktime(0, 0, 0, $date_added['mon'], $date_added['mday'], $date_added['year']);
			
			$this->data['products'][] = array(
				'product_id' => $result['product_id'],
				'thumb'   	 => $image,
				'name'    	 => $result['name'],
				'price'   	 => $price,
				'special' 	 => $special,
				'rating'     => $rating,
				'reviews'    => sprintf($this->language->get('text_reviews'), (int)$result['reviews']),
				'href'    	 => $this->url->link('product/product', 'product_id=' . $result['product_id']),
                'hot'           => $date_added + 86400 * $this->config->get('config_product_hotness_age') > time()
			);
			//print_r($data['products']);exit();
		}

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/module/latest.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/module/latest.tpl';
		} else {
			$this->template = 'default/template/module/latest.tpl';
		}

        $pagination = new Pagination();
        $pagination->total = $this->model_catalog_product->getTotalProducts($data);
        $pagination->page = $page;
        $pagination->limit = $this->setting['limit'];
        $pagination->text = $this->language->get('text_pagination');
        $pagination->url = $this->modifyUrl("latest_page", "{page}");
        //$pagination->url = $this->url->link('sale/order_items', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

        $this->data['pagination'] = $pagination->render();

		$this->render();
	}

    private function modifyUrl($name, $value)
    {
        $queryString = '';
//        $this->log->write(print_r($_REQUEST, true));
        foreach ($_REQUEST as $param => $paramValue)
        {
//            $this->log->write("$param => $paramValue");
            if ($param == $name)
            {
                $queryString .= "&$param=$value";
                $paramExists = true;
            }
            else
                $queryString .= "&$param=$paramValue";
        }
        if (empty($paramExists))
            $queryString .= "&$name=$value";
        return $_SERVER['PHP_SELF'] . '?' . substr($queryString, 1);
    }
}
?>
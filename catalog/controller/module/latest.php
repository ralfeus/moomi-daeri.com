<?php
class ControllerModuleLatest extends Controller {
    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->language->load('module/latest');
        $this->data['heading_title'] = $this->language->get('heading_title');
        $this->load->model('catalog/category');
        $this->load->model('catalog/product');
        $this->load->model('tool/image');
    }
    
    private function _endc( $array ) { return end( $array ); }
	
    protected function index($setting) {
		$this->data['button_cart'] = $this->language->get('button_cart');

    $this->data['isSaler'] = $this->customer->getCustomerGroupId() == 6;

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
			'filter_category_id' => $setting['category_ids'],
      'nocache'   => 1
		);

        if ((isset($this->request->get['route'])) AND ($this->request->get['route'] == 'product/category'))
        {
            $category_id = $this->_endc(explode('_', (string)$this->request->get['path']));       
            $data['filter_category_id'] = $category_id;
            $data['filter_sub_category'] = TRUE;
        }
         
         
        if ((isset($this->request->get['route'])) AND (isset($this->request->get['manufacturer_id'])) AND ($this->request->get['route'] == 'product/manufacturer/product'))
        {
            $manufacturer_id = $this->request->get['manufacturer_id'];       
            $data['filter_manufacturer_id'] = $manufacturer_id;           
        }

		$results = $this->model_catalog_product->getProducts($data);

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

      #kabantejay synonymizer start
			$result['description'] = preg_replace_callback('/\{  (.*?)  \}/xs', function ($m) {$ar = explode("|", $m[1]);return $ar[array_rand($ar, 1)];}, $result['description']);
			#kabantejay synonymizer end

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
		}
    $listCategoryId = array();
    $strCategories = explode(",", $data['filter_category_id']); 
    for($i=0; $i<count($strCategories); $i++) {
      $strCategoryId = $strCategories[$i];
      array_push($listCategoryId,$strCategoryId);
    }
    $this->data['listCategories'] = array();
    foreach ($listCategoryId as $cat) {
      
				$category_info = $this->model_catalog_category->getCategory($cat);

				if ($category_info) {
	       			$this->data['listCategories'][] = array(
  	    				'text'      => $category_info['name'],
						'href'      => $this->url->link('product/category', 'path=' . $category_info['category_id']),
        			);
				}
    }

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/module/latest.tpl.php')) {
			$this->template = $this->config->get('config_template') . '/template/module/latest.tpl.php';
		} else {
			$this->template = 'default/template/module/latest.tpl.php';
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
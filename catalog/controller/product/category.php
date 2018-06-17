<?php
use model\catalog\CategoryDAO;
use model\catalog\ProductDAO;
use system\helper\ImageService;

class ControllerProductCategory extends \system\engine\Controller {
	public function index() {
		$this->language->load('product/category');

		$this->data['isSaler'] = $this->customer->getCustomerGroupId() == 6;

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'p.date_added';
			$order = 'DESC';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		if (isset($this->request->get['limit'])) {
			$limit = $this->request->get['limit'];
		} else {
			$limit = $this->getConfig()->get('config_catalog_limit');
		}

		if (isset($this->request->get['path'])) {
			$path = '';

			$parts = explode('_', (string)$this->request->get['path']);

			foreach ($parts as $path_id) {
				if (!$path) {
					$path = $path_id;
				} else {
					$path .= '_' . $path_id;
				}

				$category = CategoryDAO::getInstance()->getCategory($path_id);

				#kabantejay synonymizer start
				$razdel = $category->getDescription()->getName();
				#kabantejay synonymizer end
      
				if ($category) {
				    $this->setBreadcrumbs([[
   	    				'text'      => $category->getDescription()->getName(),
						'route'      => 'product/category',
                        'args' => ['path' => $path],
        				'separator' => $this->getLanguage()->get('text_separator')
        			]]);
				}
			}

			$category_id = array_pop($parts);
		} else {
			$category_id = 0;
		}

		$category = CategoryDAO::getInstance()->getCategory($category_id);

		if ($category) {
			if ($category->getDescription()->getSeoTitle()) {
		  		$this->document->setTitle($category->getDescription()->getSeoTitle());
			} else {
		  		$this->document->setTitle($category->getDescription()->getName());
			}

			$this->document->setDescription($category->getDescription()->getMetaDescription());
			$this->document->setKeywords($category->getDescription()->getMetaKeyword());

			$this->data['seo_h1'] = $category->getDescription()->getSeoH1();

			$this->data['heading_title'] = $category->getDescription()->getName();

			$this->data['text_refine'] = $this->language->get('text_refine');
			$this->data['text_empty'] = $this->language->get('text_empty');
			$this->data['text_quantity'] = $this->language->get('text_quantity');
			$this->data['text_manufacturer'] = $this->language->get('text_manufacturer');
			$this->data['text_model'] = $this->language->get('text_model');
			$this->data['text_price'] = $this->language->get('text_price');
			$this->data['text_tax'] = $this->language->get('text_tax');
			$this->data['text_points'] = $this->language->get('text_points');
			$this->data['text_compare'] = sprintf($this->language->get('text_compare'), (isset($this->session->data['compare']) ? count($this->session->data['compare']) : 0));
			$this->data['text_display'] = $this->language->get('text_display');
			$this->data['text_list'] = $this->language->get('text_list');
			$this->data['text_grid'] = $this->language->get('text_grid');
			$this->data['text_sort'] = $this->language->get('text_sort');
			$this->data['text_limit'] = $this->language->get('text_limit');

			$this->data['button_cart'] = $this->language->get('button_cart');
			$this->data['button_wishlist'] = $this->language->get('button_wishlist');
			$this->data['button_compare'] = $this->language->get('button_compare');
			$this->data['button_continue'] = $this->language->get('button_continue');

			if ($category->getImage()) {
				$this->data['thumb'] = ImageService::getInstance()->resize(
				    $category->getImage(),
                    $this->getConfig()->get('config_image_category_width'),
                    $this->getConfig()->get('config_image_category_height'));
			} else {
				$this->data['thumb'] = '';
			}

			$this->data['description'] = html_entity_decode($category->getDescription()->getDescription(), ENT_QUOTES, 'UTF-8');
			$this->data['compare'] = $this->getUrl()->link('product/compare');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$this->data['categories'] = array();

			$results = CategoryDAO::getInstance()->getCategories($category_id);

			foreach ($results as $result) {
				$filter = array(
					'filterCategoryId'  => $result['category_id'],
					'filterSubCategories' => true
				);

				$product_total = ProductDAO::getInstance()->getProductsCount($filter, true);

				$this->data['categories'][] = array(
					'name'  => $result['name'] . ' (' . $product_total . ')',
					'href'  => $this->getUrl()->link('product/category', 'path=' . $this->request->get['path'] . '_' . $result['category_id'] . $url)
				);
			}

			$this->data['products'] = array();

            #kabantejay synonymizer start
			$this->data['description'] = preg_replace_callback('/\{  (.*?)  \}/xs', function ($m) {$ar = explode("|", $m[1]);return $ar[array_rand($ar, 1)];}, $this->data['description']);
			#kabantejay synonymizer end
      

            $filter = ['filterCategoryId' => $category_id];
            $product_total = ProductDAO::getInstance()->getProductsCount($filter, true);
            $results = ProductDAO::getInstance()->getProducts($filter, $sort, $order, ($page -1) * $limit, $limit, true, true);
			foreach ($results as $result) {
				if ($result->getImagePath()) {
					$image = ImageService::getInstance()->resize(
					    $result->getImagePath(),
                        $this->getConfig()->get('config_image_product_width'),
                        $this->getConfig()->get('config_image_product_height')
                    );
				} else {
					$image = false;
				}

				if (($this->getConfig()->get('config_customer_price') && $this->customer->isLogged()) || !$this->getConfig()->get('config_customer_price')) {
					$price = $this->getCurrency()->format($result->getPrice());
				} else {
					$price = false;
				}

				if ((float)$result->getSpecialPrice($this->getCurrentCustomer()->getCustomerGroupId())) {
					$special = $this->getCurrency()->format($result->getSpecialPrice($this->getCurrentCustomer()->getCustomerGroupId()));
				} else {
					$special = false;
				}

				$tax = false;

//				if ($this->getConfig()->get('config_review_status')) {
//					$rating = (int)$result['rating'];
//				} else {
//					$rating = false;
//				}

				$date_added = getdate(strtotime($result->getDateAdded()));
				$date_added = mktime(0, 0, 0, $date_added['mon'], $date_added['mday'], $date_added['year']);

                #kabantejay synonymizer start
                $brand = $result->getManufacturer()->getName();
                if (!isset($razdel)) {
                  $razdel = '';
                }
                $syncat = $result->getName() ? $category->getDescription()->getName() : '';
                $synmod = $result->getModel() ? $result->getModel() : '';
                $synprice = $special ? $special : $price;

                $syntext=array(
                  array("%H1%",$result->getName()),
                  array("%BRAND%",$brand),
                  array("%RAZDEL%",$razdel),
                  array("%CATEGORY%",$syncat),
                  array("%MODEL%",$synmod),
                  array("%PRICE%",$synprice)
                );

                $description = '';
                try {
                    for ($it = 0; $it < 6; $it++) {
                        $description = str_replace($syntext[$it][0], $syntext[$it][1], $result->getDescription()->getDescription());
                    }
                    $description = preg_replace_callback('/\{  (.*?)  \}/xs',
                        function ($m) {
                            $ar = explode("|", $m[1]);
                            return $ar[array_rand($ar, 1)];
                        }, $description);
                } catch (InvalidArgumentException $exception) {} // $description will remain empty
                #kabantejay synonymizer end

				$this->data['products'][] = array(
					'product_id'  => $result->getId(),
					'thumb'       => $image,
					'name'        => $result->getName(),
					'description' => utf8_truncate(strip_tags(html_entity_decode($description, ENT_QUOTES, 'UTF-8')), 400, '&nbsp;&hellip;', true),
					'price'       => $price,
					'isSaler'	  => $this->data['isSaler'],
					'special'     => $special,
					'tax'         => $tax,
					'rating'      => $result->getRating(),
					'reviews'     => sprintf($this->language->get('text_reviews'), (int)$result->getReviewsCount()),
					'href'        => $this->getUrl()->link('product/product', 'path=' . $this->request->get['path'] . '&product_id=' . $result->getId()),
					'hot'           => $date_added + 86400 * $this->getConfig()->get('config_product_hotness_age') > time()
				);
			}

			$url = '';

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$this->data['sorts'] = array();

			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_default'),
				'value' => 'p.sort_order-ASC',
				'href'  => $this->getUrl()->link('product/category', 'path=' . $this->request->get['path'] . '&sort=p.sort_order&order=ASC' . $url)
			);

			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_name_asc'),
				'value' => 'pd.name-ASC',
				'href'  => $this->getUrl()->link('product/category', 'path=' . $this->request->get['path'] . '&sort=pd.name&order=ASC' . $url)
			);

			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_name_desc'),
				'value' => 'pd.name-DESC',
				'href'  => $this->getUrl()->link('product/category', 'path=' . $this->request->get['path'] . '&sort=pd.name&order=DESC' . $url)
			);

			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_price_asc'),
				'value' => 'p.price-ASC',
				'href'  => $this->getUrl()->link('product/category', 'path=' . $this->request->get['path'] . '&sort=p.price&order=ASC' . $url)
			);

			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_price_desc'),
				'value' => 'p.price-DESC',
				'href'  => $this->getUrl()->link('product/category', 'path=' . $this->request->get['path'] . '&sort=p.price&order=DESC' . $url)
			);

			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_rating_desc'),
				'value' => 'rating-DESC',
				'href'  => $this->getUrl()->link('product/category', 'path=' . $this->request->get['path'] . '&sort=rating&order=DESC' . $url)
			);

			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_rating_asc'),
				'value' => 'rating-ASC',
				'href'  => $this->getUrl()->link('product/category', 'path=' . $this->request->get['path'] . '&sort=rating&order=ASC' . $url)
			);

			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_model_asc'),
				'value' => 'p.model-ASC',
				'href'  => $this->getUrl()->link('product/category', 'path=' . $this->request->get['path'] . '&sort=p.model&order=ASC' . $url)
			);

			$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_model_desc'),
				'value' => 'p.model-DESC',
				'href'  => $this->getUrl()->link('product/category', 'path=' . $this->request->get['path'] . '&sort=p.model&order=DESC' . $url)
			);

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			$this->data['limits'] = array();

			$this->data['limits'][] = array(
				'text'  => $this->getConfig()->get('config_catalog_limit'),
				'value' => $this->getConfig()->get('config_catalog_limit'),
				'href'  => $this->getUrl()->link('product/category', 'path=' . $this->request->get['path'] . $url . '&limit=' . $this->getConfig()->get('config_catalog_limit'))
			);

			$this->data['limits'][] = array(
				'text'  => 25,
				'value' => 25,
				'href'  => $this->getUrl()->link('product/category', 'path=' . $this->request->get['path'] . $url . '&limit=25')
			);

			$this->data['limits'][] = array(
				'text'  => 50,
				'value' => 50,
				'href'  => $this->getUrl()->link('product/category', 'path=' . $this->request->get['path'] . $url . '&limit=50')
			);

			$this->data['limits'][] = array(
				'text'  => 75,
				'value' => 75,
				'href'  => $this->getUrl()->link('product/category', 'path=' . $this->request->get['path'] . $url . '&limit=75')
			);

			$this->data['limits'][] = array(
				'text'  => 100,
				'value' => 100,
				'href'  => $this->getUrl()->link('product/category', 'path=' . $this->request->get['path'] . $url . '&limit=100')
			);

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$pagination = new Pagination();
			$pagination->total = $product_total;
			$pagination->page = $page;
			$pagination->limit = $limit;
			$pagination->text = $this->language->get('text_pagination');
			$pagination->url = $this->getUrl()->link('product/category', 'path=' . $this->request->get['path'] . $url . '&page={page}');

			$this->data['pagination'] = $pagination->render();

			$this->data['sort'] = $sort;
			$this->data['order'] = $order;
			$this->data['limit'] = $limit;

			$this->data['continue'] = $this->getUrl()->link('common/home');

            $templateName = '/template/product/category.tpl';
			if (file_exists(DIR_TEMPLATE . $this->getConfig()->get('config_template') . $templateName)) {
				$this->template = $this->getConfig()->get('config_template') . $templateName;
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

			$this->load->language('shop/general');
			$this->data['text_button_download'] = $this->language->get('text_button_download');
			$this->data['isSaler'] = $this->customer->getCustomerGroupId() == 6;
			$this->data['showDownload'] = false;
			if($this->request->get['route'] == "product/category" || $this->request->get['route'] == "common/home") {
				$this->data['showDownload'] = true;
			}

			$this->getResponse()->setOutput($this->render());
    	} else {
			$url = '';

			if (isset($this->request->get['path'])) {
				$url .= '&path=' . $this->request->get['path'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_error'),
				'href'      => $this->getUrl()->link('product/category', $url),
				'separator' => $this->language->get('text_separator')
			);

			$this->document->setTitle($this->language->get('text_error'));

      		$this->data['heading_title'] = $this->language->get('text_error');

      		$this->data['text_error'] = $this->language->get('text_error');

      		$this->data['button_continue'] = $this->language->get('button_continue');

      		$this->data['continue'] = $this->getUrl()->link('common/home');

			if (file_exists(DIR_TEMPLATE . $this->getConfig()->get('config_template') . '/template/error/not_found.tpl')) {
				$this->template = $this->getConfig()->get('config_template') . '/template/error/not_found.tpl';
			} else {
				$this->template = 'default/template/error/not_found.tpl';
			}

			$this->children = array(
				'common/column_left',
				'common/column_right',
				'common/content_top',
				'common/content_bottom',
				'common/footer',
				'common/header'
			);
			$this->load->language('shop/general');
			$this->data['text_button_download'] = $this->language->get('text_button_download');
			$this->data['isSaler'] = $this->customer->getCustomerGroupId() == 6;
			$this->data['showDownload'] = false;
			if (isset($this->request->get['route']) &&
                ($this->request->get['route'] == "product/category" || $this->request->get['route'] == "common/home")) {
				$this->data['showDownload'] = true;
			}

			$this->getResponse()->setOutput($this->render());
		}
  	}
}
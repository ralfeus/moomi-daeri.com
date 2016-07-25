<?php
use model\catalog\ProductDAO;
use system\helper\ImageService;

class ControllerProductSpecial extends Controller {
    protected function loadStrings() {
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
    }

    public function index() { 
    	$this->language->load('product/special');
		
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'p.sort_order';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
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
				    	
		$this->document->setTitle($this->language->get('heading_title'));

		$url = '';
		
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
					
    	$this->data['heading_title'] = $this->language->get('heading_title');
		$this->data['compare'] = $this->getUrl()->link('product/compare');
		$this->data['products'] = array();

		$data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $limit,
			'limit' => $limit
		);

        $customerGroupId = $this->getCurrentCustomer()->isLogged()
            ? $this->getCurrentCustomer()->getCustomerGroupId()
            : $this->getConfig()->get('config_customer_group_id');

		$product_total = ProductDAO::getInstance()->getProductsCount($data);
		$products = ProductDAO::getInstance()->getDiscountedProductsByCustomerGroupId($customerGroupId, $sort, $order, ($page - 1) * $limit, $limit);
			
		foreach ($products as $product) {
            #kabantejay synonymizer start
            if (is_null($product->getDescription()) || is_null($product->getDescription()->getDescription($this->getLanguage()->getId()))) {
                $productDescription = '';
            } else {
                $productDescription = preg_replace_callback(
                    '/\{  (.*?)  \}/xs',
                    function ($m) {
                        $ar = explode("|", $m[1]);
                        return $ar[array_rand($ar, 1)];
                    },
                    $product->getDescription()->getDescription($this->getLanguage()->getId())->getDescription()
                );
            }
            #kabantejay synonymizer end

            if ($product->getImagePath()) {
				$image = ImageService::getInstance()->resize($product->getImagePath(), $this->getConfig()->get('config_image_product_width'), $this->getConfig()->get('config_image_product_height'));
			} else {
				$image = false;
			}
			
			if (($this->getConfig()->get('config_customer_price') && $this->customer->isLogged()) || !$this->getConfig()->get('config_customer_price')) {
				$price = $this->getCurrency()->format($product->getPrice());
			} else {
				$price = false;
			}
			
			if ((float)$product->getSpecialPrice($customerGroupId)) {
				$special = $this->getCurrency()->format($product->getSpecialPrice($customerGroupId));
			} else {
				$special = false;
			}	
			
			if ($this->getConfig()->get('config_tax')) {
				$tax = $this->getCurrency()->format((float)$product->getSpecialPrice($customerGroupId) ? $product->getSpecialPrice($customerGroupId) : $product->getPrice());
			} else {
				$tax = false;
			}				
						
			$this->data['products'][] = array(
				'product_id'  => $product->getId(),
				'thumb'       => $image,
				'name'        => $product->getName(),
				'description' => utf8_truncate(strip_tags(html_entity_decode($productDescription, ENT_QUOTES, 'UTF-8')), 400, '&nbsp;&hellip;', true),
				'price'       => $price,
				'special'     => $special,
				'tax'         => $tax,
				'rating'      => $product->getRating(),
				'reviews'     => sprintf($this->language->get('text_reviews'), (int)$product->getReviewsCount()),
				'href'        => $this->getUrl()->link('product/product', $url . '&product_id=' . $product->getId())
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
			'href'  => $this->getUrl()->link('product/special', 'sort=p.sort_order&order=ASC' . $url)
		);
		
		$this->data['sorts'][] = array(
			'text'  => $this->language->get('text_name_asc'),
			'value' => 'pd.name-ASC',
			'href'  => $this->getUrl()->link('product/special', 'sort=pd.name&order=ASC' . $url)
		); 

		$this->data['sorts'][] = array(
			'text'  => $this->language->get('text_name_desc'),
			'value' => 'pd.name-DESC',
			'href'  => $this->getUrl()->link('product/special', 'sort=pd.name&order=DESC' . $url)
		);  

		$this->data['sorts'][] = array(
			'text'  => $this->language->get('text_price_asc'),
			'value' => 'ps.price-ASC',
			'href'  => $this->getUrl()->link('product/special', 'sort=ps.price&order=ASC' . $url)
		); 

		$this->data['sorts'][] = array(
			'text'  => $this->language->get('text_price_desc'),
			'value' => 'special-DESC',
			'href'  => $this->getUrl()->link('product/special', 'sort=special&order=DESC' . $url)
		); 
			
		$this->data['sorts'][] = array(
			'text'  => $this->language->get('text_rating_desc'),
			'value' => 'rating-DESC',
			'href'  => $this->getUrl()->link('product/special', 'sort=rating&order=DESC' . $url)
		); 
			
		$this->data['sorts'][] = array(
			'text'  => $this->language->get('text_rating_asc'),
			'value' => 'rating-ASC',
			'href'  => $this->getUrl()->link('product/special', 'sort=rating&order=ASC' . $url)
		);
		
		$this->data['sorts'][] = array(
				'text'  => $this->language->get('text_model_asc'),
				'value' => 'p.model-ASC',
				'href'  => $this->getUrl()->link('product/special', 'sort=p.model&order=ASC' . $url)
		); 

		$this->data['sorts'][] = array(
			'text'  => $this->language->get('text_model_desc'),
			'value' => 'p.model-DESC',
			'href'  => $this->getUrl()->link('product/special', 'sort=p.model&order=DESC' . $url)
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
			'href'  => $this->getUrl()->link('product/special', $url . '&limit=' . $this->getConfig()->get('config_catalog_limit'))
		);
					
		$this->data['limits'][] = array(
			'text'  => 25,
			'value' => 25,
			'href'  => $this->getUrl()->link('product/special', $url . '&limit=25')
		);
		
		$this->data['limits'][] = array(
			'text'  => 50,
			'value' => 50,
			'href'  => $this->getUrl()->link('product/special', $url . '&limit=50')
		);

		$this->data['limits'][] = array(
			'text'  => 75,
			'value' => 75,
			'href'  => $this->getUrl()->link('product/special', $url . '&limit=75')
		);
		
		$this->data['limits'][] = array(
			'text'  => 100,
			'value' => 100,
			'href'  => $this->getUrl()->link('product/special', $url . '&limit=100')
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
		$pagination->url = $this->getUrl()->link('product/special', $url . '&page={page}');
			
		$this->data['pagination'] = $pagination->render();
			
		$this->data['sort'] = $sort;
		$this->data['order'] = $order;
		$this->data['limit'] = $limit;

        $this->setBreadcrumbs();
		$this->children = array(
			'common/column_left',
			'common/column_right',
			'common/content_top',
			'common/content_bottom',
			'common/footer',
			'common/header'
		);

		$this->getResponse()->setOutput($this->render($this->getConfig()->get('config_template') . '/template/product/special.tpl'));
  	}
}
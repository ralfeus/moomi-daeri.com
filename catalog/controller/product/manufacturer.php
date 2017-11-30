<?php
use model\catalog\ManufacturerDAO;
use model\catalog\ProductDAO;
use system\helper\ImageService;

class ControllerProductManufacturer extends Controller {
    public function __construct($registry) {
        parent::__construct($registry);
        $this->getLanguage()->load('product/manufacturer');
        $this->document->setTitle($this->getLanguage()->get('heading_title'));
        $this->data['heading_title'] = $this->getLanguage()->get('heading_title');
    }

    protected function initParameters() {
        parent::initParameters();
        $this->initParametersWithDefaults([
            'manufacturer_id' => 0,
            'limit' => $this->getConfig()->get('config_catalog_limit'),
            'order' => 'ASC',
            'page' => 1,
            'sort' => 'p.sort_order'
        ]);
    }

    protected function loadStrings() {
        $this->data['text_empty'] = $this->getLanguage()->get('text_empty');
        $this->data['text_quantity'] = $this->getLanguage()->get('text_quantity');
        $this->data['text_manufacturer'] = $this->getLanguage()->get('text_manufacturer');
        $this->data['text_model'] = $this->getLanguage()->get('text_model');
        $this->data['text_price'] = $this->getLanguage()->get('text_price');
        $this->data['text_tax'] = $this->getLanguage()->get('text_tax');
        $this->data['text_points'] = $this->getLanguage()->get('text_points');
        $this->data['text_compare'] = sprintf($this->getLanguage()->get('text_compare'), (isset($this->getSession()->data['compare']) ? count($this->getSession()->data['compare']) : 0));
        $this->data['text_display'] = $this->getLanguage()->get('text_display');
        $this->data['text_list'] = $this->getLanguage()->get('text_list');
        $this->data['text_grid'] = $this->getLanguage()->get('text_grid');
        $this->data['text_sort'] = $this->getLanguage()->get('text_sort');
        $this->data['text_limit'] = $this->getLanguage()->get('text_limit');
        $this->data['text_error'] = $this->getLanguage()->get('text_error');

        $this->data['button_cart'] = $this->getLanguage()->get('button_cart');
        $this->data['button_wishlist'] = $this->getLanguage()->get('button_wishlist');
        $this->data['button_compare'] = $this->getLanguage()->get('button_compare');
        $this->data['button_continue'] = $this->getLanguage()->get('button_continue');
    }

    public function index() {
		$this->data['text_index'] = $this->getLanguage()->get('text_index');
		$this->data['text_empty'] = $this->getLanguage()->get('text_empty');
		
		$this->data['button_continue'] = $this->getLanguage()->get('button_continue');
		
		$this->data['categories'] = array();
									
		$manufacturers = ManufacturerDAO::getInstance()->getManufacturers();
	
		foreach ($manufacturers as $manufacturer) {
			if (is_numeric(utf8_substr($manufacturer->getName(), 0, 1))) {
				$key = '0 - 9';
			} else {
				$key = utf8_truncate(utf8_strtoupper($manufacturer->getName()), 1, '');
			}
			
			if (!isset($this->data['manufacturers'][$key])) {
				$this->data['categories'][$key]['name'] = $key;
			}
			
			$this->data['categories'][$key]['manufacturer'][] = array(
				'name' => $manufacturer->getName(),
				'href' => $this->getUrl()->link('product/manufacturer/product', 'manufacturer_id=' . $manufacturer->getId())
			);
		}
		
		$this->data['continue'] = $this->getUrl()->link('common/home');

        $this->setBreadcrumbs();
        $this->children = array(
			'common/column_left',
			'common/column_right',
			'common/content_top',
			'common/content_bottom',
			'common/footer',
			'common/header'
		);

        $templatePath = file_exists(DIR_TEMPLATE . $this->getConfig()->get('config_template') . '/template/product/manufacturer_list.tpl.php')
            ? $this->getConfig()->get('config_template')
            : 'default';
		$this->getResponse()->setOutput($this->render($templatePath . '/template/product/manufacturer_list.tpl.php'));
  	}
	
	public function product() {
		$manufacturer = ManufacturerDAO::getInstance()->getManufacturer($this->parameters['manufacturerId']);
	
		if ($manufacturer) {
			if (!is_null($manufacturer->getDescription($this->getLanguage()->getId())) &&
                !is_null($manufacturer->getDescription($this->getLanguage()->getId())->getSeoTitle())) {
				$this->document->setTitle($manufacturer->getDescription($this->getLanguage()->getId())->getSeoTitle());
			} else {
				$this->document->setTitle($manufacturer->getName());
			}

			if (!is_null($manufacturer->getDescription($this->getLanguage()->getId())) &&
                !is_null($manufacturer->getDescription($this->getLanguage()->getId())->getMetaDescription())) {
                $this->document->setDescription($manufacturer->getDescription($this->getLanguage()->getId())->getMetaDescription());
            }
            if (!is_null($manufacturer->getDescription($this->getLanguage()->getId())) &&
                !is_null($manufacturer->getDescription($this->getLanguage()->getId())->getMetaKeyword())) {
                $this->document->setKeywords($manufacturer->getDescription($this->getLanguage()->getId())->getMetaKeyword());
            }
            if (!is_null($manufacturer->getDescription($this->getLanguage()->getId())) &&
                !is_null($manufacturer->getDescription($this->getLanguage()->getId())->getSeoH1())) {
                $this->data['seo_h1'] = $manufacturer->getDescription($this->getLanguage()->getId())->getSeoH1();
            }
            $this->data = array_merge($this->data, $this->parameters);
			$this->data['heading_title'] = $manufacturer->getName();
			$this->data['compare'] = $this->getUrl()->link('product/compare');

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

            #kabantejay synonymizer start
			$this->data['description'] = preg_replace_callback(
			    '/\{  (.*?)  \}/xs', 
                function ($m) {
			        $ar = explode("|", $m[1]);
                    return $ar[array_rand($ar, 1)];
			    },
                html_entity_decode(
                    !is_null($manufacturer->getDescription($this->getLanguage()->getId()))
                        ? $manufacturer->getDescription($this->getLanguage()->getId())->getDescription()
                        : "",
                    ENT_QUOTES, 'UTF-8')
            );
			#kabantejay synonymizer end
			
			$this->data['products'] = array();
			
			$data = array(
				'filterManufacturerId' => $this->parameters['manufacturerId'],
				'sort'                   => $this->parameters['sort'],
				'order'                  => $this->parameters['order'],
				'start'                  => ($this->parameters['page'] - 1) * $this->parameters['limit'],
				'limit'                  => $this->parameters['limit']
			);
					
			$product_total = ProductDAO::getInstance()->getProductsCount($data);
			$products = ProductDAO::getInstance()->getProducts(
			    $data,
                $this->parameters['sort'],
                $this->parameters['order'],
                ($this->parameters['page'] - 1) * $this->parameters['limit'],
                $this->parameters['limit']
            );
					
			foreach ($products as $product) {
				if ($product->getImagePath()) {
					$image = ImageService::getInstance()->resize($product->getImagePath(), $this->getConfig()->get('config_image_product_width'), $this->getConfig()->get('config_image_product_height'));
				} else {
					$image = false;
				}
				
				if (($this->getConfig()->get('config_customer_price') && $this->customer->isLogged()) || !$this->getConfig()->get('config_customer_price')) {
//					$price = $this->currency->format($this->tax->calculate($product->getPrice(), $product->getTaxClassId(), $this->getConfig()->get('config_tax')));
                    $price = $this->getCurrentCurrency()->format($product->getPrice());
				} else {
					$price = false;
				}
				
				if ((float)$product->getSpecialPrice($this->getCurrentCustomer()->getCustomerGroupId())) {
//					$special = $this->currency->format($this->tax->calculate($product->getSpecials(), $product->getTaxClassId(), $this->getConfig()->get('config_tax')));
                    $special = $this->getCurrentCurrency()->format($product->getSpecialPrice($this->getCurrentCustomer()->getCustomerGroupId()));
				} else {
					$special = false;
				}	
				
//				if ($this->getConfig()->get('config_tax')) {
//					$tax = $this->currency->format((float)$product->getSpecial() ? $product->getSpecial() : $product->getPrice());
//				} else {
//					$tax = false;
//				}
				
//              #kabantejay synonymizer start
                if (is_null($product->getDescriptions())) {
                    $description = '';
                } else {
                    $description = preg_replace_callback(
                        '/\{  (.*?)  \}/xs',
                        function ($m) {
                            $ar = explode("|", $m[1]);
                            return $ar[array_rand($ar, 1)];
                        },
                        $product->getDescriptions()->getDescription($this->getLanguage()->getId())->getDescription()
                    );
                }
    //	   		#kabantejay synonymizer end
			
				$this->data['products'][] = array(
					'product_id'  => $product->getId(),
					'thumb'       => $image,
					'name'        => $product->getName(),
					'description' => utf8_truncate(strip_tags(html_entity_decode($description, ENT_QUOTES, 'UTF-8')), 400, '&nbsp;&hellip;', true),
					'price'       => $price,
					'special'     => $special,
//					'tax'         => $tax,
					'rating'      => $this->getConfig()->get('config_review_status') ? $product->getRating() : false,
					'reviews'     => sprintf($this->getLanguage()->get('text_reviews'), $product->getReviewsCount()),
					'href'        => $this->getUrl()->link('product/product', $url . '&manufacturer_id=' . $product->getManufacturer()->getId() . '&product_id=' . $product->getId())
				);
			}
					
			$url = '';
			
			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}
						
			$this->data['sorts'] = array();
			
			$this->data['sorts'][] = array(
				'text'  => $this->getLanguage()->get('text_default'),
				'value' => 'p.sort_order-ASC',
				'href'  => $this->getUrl()->link('product/manufacturer/product', 'manufacturer_id=' . $this->request->get['manufacturer_id'] . '&sort=p.sort_order&order=ASC' . $url)
			);
			
			$this->data['sorts'][] = array(
				'text'  => $this->getLanguage()->get('text_name_asc'),
				'value' => 'pd.name-ASC',
				'href'  => $this->getUrl()->link('product/manufacturer/product', 'manufacturer_id=' . $this->request->get['manufacturer_id'] . '&sort=pd.name&order=ASC' . $url)
			); 
	
			$this->data['sorts'][] = array(
				'text'  => $this->getLanguage()->get('text_name_desc'),
				'value' => 'pd.name-DESC',
				'href'  => $this->getUrl()->link('product/manufacturer/product', 'manufacturer_id=' . $this->request->get['manufacturer_id'] . '&sort=pd.name&order=DESC' . $url)
			);
	
			$this->data['sorts'][] = array(
				'text'  => $this->getLanguage()->get('text_price_asc'),
				'value' => 'p.price-ASC',
				'href'  => $this->getUrl()->link('product/manufacturer/product', 'manufacturer_id=' . $this->request->get['manufacturer_id'] . '&sort=p.price&order=ASC' . $url)
			); 
	
			$this->data['sorts'][] = array(
				'text'  => $this->getLanguage()->get('text_price_desc'),
				'value' => 'p.price-DESC',
				'href'  => $this->getUrl()->link('product/manufacturer/product', 'manufacturer_id=' . $this->request->get['manufacturer_id'] . '&sort=p.price&order=DESC' . $url)
			); 
			
			$this->data['sorts'][] = array(
				'text'  => $this->getLanguage()->get('text_rating_desc'),
				'value' => 'rating-DESC',
				'href'  => $this->getUrl()->link('product/manufacturer/product', 'manufacturer_id=' . $this->request->get['manufacturer_id'] . '&sort=rating&order=DESC' . $url)
			); 
			
			$this->data['sorts'][] = array(
				'text'  => $this->getLanguage()->get('text_rating_asc'),
				'value' => 'rating-ASC',
				'href'  => $this->getUrl()->link('product/manufacturer/product', 'manufacturer_id=' . $this->request->get['manufacturer_id'] . '&sort=rating&order=ASC' . $url)
			);
			
			$this->data['sorts'][] = array(
				'text'  => $this->getLanguage()->get('text_model_asc'),
				'value' => 'p.model-ASC',
				'href'  => $this->getUrl()->link('product/manufacturer/product', 'manufacturer_id=' . $this->request->get['manufacturer_id'] . '&sort=p.model&order=ASC' . $url)
			); 
	
			$this->data['sorts'][] = array(
				'text'  => $this->getLanguage()->get('text_model_desc'),
				'value' => 'p.model-DESC',
				'href'  => $this->getUrl()->link('product/manufacturer/product', 'manufacturer_id=' . $this->request->get['manufacturer_id'] . '&sort=p.model&order=DESC' . $url)
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
				'href'  => $this->getUrl()->link('product/manufacturer/product', 'manufacturer_id=' . $this->request->get['manufacturer_id'] . $url . '&limit=' . $this->getConfig()->get('config_catalog_limit'))
			);
						
			$this->data['limits'][] = array(
				'text'  => 25,
				'value' => 25,
				'href'  => $this->getUrl()->link('product/manufacturer/product', 'manufacturer_id=' . $this->request->get['manufacturer_id'] . $url . '&limit=25')
			);
			
			$this->data['limits'][] = array(
				'text'  => 50,
				'value' => 50,
				'href'  => $this->getUrl()->link('product/manufacturer/product', 'manufacturer_id=' . $this->request->get['manufacturer_id'] . $url . '&limit=50')
			);
	
			$this->data['limits'][] = array(
				'text'  => 75,
				'value' => 75,
				'href'  => $this->getUrl()->link('product/manufacturer/product', 'manufacturer_id=' . $this->request->get['manufacturer_id'] . $url . '&limit=75')
			);
			
			$this->data['limits'][] = array(
				'text'  => 100,
				'value' => 100,
				'href'  => $this->getUrl()->link('product/manufacturer/product', 'manufacturer_id=' . $this->request->get['manufacturer_id'] . $url . '&limit=100')
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

            $this->setBreadcrumbs([[
                'text'      => $this->getLanguage()->get('text_brand'),
                'route'      => $this->getUrl()->link('product/manufacturer')
            ]]);

            $pagination = new Pagination();
			$pagination->total = $product_total;
			$pagination->page = $this->parameters['page'];
			$pagination->limit = $this->parameters['limit'];
			$pagination->text = $this->getLanguage()->get('text_pagination');
			$pagination->url = $this->getUrl()->link('product/manufacturer/product','manufacturer_id=' . $this->request->get['manufacturer_id'] .  $url . '&page={page}');
			
			$this->data['pagination'] = $pagination->render();
			$this->data['continue'] = $this->getUrl()->link('common/home');
			
			$this->children = array(
				'common/column_left',
				'common/column_right',
				'common/content_top',
				'common/content_bottom',
				'common/footer',
				'common/header'
			);
			$templateFile = '/template/product/manufacturerInfo.tpl.php';
            $templateDir = file_exists(DIR_TEMPLATE . $this->getConfig()->get('config_template') . $templateFile)
                ? $this->getConfig()->get('config_template')
                : 'default';
			$this->getResponse()->setOutput($this->render($templateDir . $templateFile));
		} else {
			$this->document->setTitle($this->getLanguage()->get('text_error'));
            $this->data['heading_title'] = $this->getLanguage()->get('text_error');
      		$this->data['continue'] = $this->getUrl()->link('common/home');
            $this->setBreadcrumbs();
			$this->children = array(
				'common/column_left',
				'common/column_right',
				'common/content_top',
				'common/content_bottom',
				'common/footer',
				'common/header'
			);
            $templateFile = '/template/error/not_found.tpl';
            $templateDir = file_exists(DIR_TEMPLATE . $this->getConfig()->get('config_template') . $templateFile)
                ? $this->getConfig()->get('config_template')
                : 'default';
            $this->getResponse()->setOutput($this->render($templateDir . $templateFile));
		}
  	}
}
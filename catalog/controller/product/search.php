<?php
use model\catalog\CategoryDAO;
use model\catalog\Product;
use model\catalog\ProductDAO;
use system\helper\ImageService;

class ControllerProductSearch extends Controller {
	public function index() {
//        $this->log->write(print_r($this->request, true));
    	$this->getLanguage()->load('product/search');

		if (isset($this->request->get['filter_name'])) {
			$filter_name = $this->request->get['filter_name'];
		} else {
			$filter_name = '';
		} 
//
//		if (isset($this->request->get['filter_description'])) {
//			$filter_description = $this->request->get['filter_description'];
//		} else {
//			$filter_description = '';
//		}

		if (isset($this->request->get['filter_category_id'])) {
			$filter_category_id = $this->request->get['filter_category_id'];
		} else {
			$filter_category_id = 0;
		} 
		
		if (isset($this->request->get['filter_sub_category'])) {
			$filter_sub_category = $this->request->get['filter_sub_category'];
		} else {
			$filter_sub_category = '';
		} 
								
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = null;
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
		
		if (isset($this->request->get['keyword'])) {
			$this->document->setTitle($this->getLanguage()->get('heading_title') .  ' - ' . $this->request->get['keyword']);
		} else {
			$this->document->setTitle($this->getLanguage()->get('heading_title'));
		}

		$url = '';
		
		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . $this->request->get['filter_name'];
		}
		
		if (isset($this->request->get['filter_tag'])) {
			$url .= '&filter_tag=' . $this->request->get['filter_tag'];
		}
				
		if (isset($this->request->get['filter_description'])) {
			$url .= '&filter_description=' . $this->request->get['filter_description'];
		}
				
		if (isset($this->request->get['filter_category_id'])) {
			$url .= '&filter_category_id=' . $this->request->get['filter_category_id'];
		}
		
		if (isset($this->request->get['filter_sub_category'])) {
			$url .= '&filter_sub_category=' . $this->request->get['filter_sub_category'];
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
       		'text'      => $this->getLanguage()->get('heading_title'),
			'href'      => $this->getUrl()->link('product/search', $url),
      		'separator' => $this->getLanguage()->get('text_separator')
   		);
		
    	$this->data['heading_title'] = $this->getLanguage()->get('heading_title');
		
		$this->data['text_empty'] = $this->getLanguage()->get('text_empty');
    	$this->data['text_critea'] = $this->getLanguage()->get('text_critea');
    	$this->data['text_search'] = $this->getLanguage()->get('text_search');
		$this->data['text_keyword'] = $this->getLanguage()->get('text_keyword');
		$this->data['text_category'] = $this->getLanguage()->get('text_category');
		$this->data['text_sub_category'] = $this->getLanguage()->get('text_sub_category');
		$this->data['text_quantity'] = $this->getLanguage()->get('text_quantity');
		$this->data['text_manufacturer'] = $this->getLanguage()->get('text_manufacturer');
		$this->data['text_model'] = $this->getLanguage()->get('text_model');
		$this->data['text_price'] = $this->getLanguage()->get('text_price');
		$this->data['text_tax'] = $this->getLanguage()->get('text_tax');
		$this->data['text_points'] = $this->getLanguage()->get('text_points');
		$this->data['text_compare'] = sprintf($this->getLanguage()->get('text_compare'), (isset($this->session->data['compare']) ? count($this->session->data['compare']) : 0));
		$this->data['text_display'] = $this->getLanguage()->get('text_display');
		$this->data['text_list'] = $this->getLanguage()->get('text_list');
		$this->data['text_grid'] = $this->getLanguage()->get('text_grid');		
		$this->data['text_sort'] = $this->getLanguage()->get('text_sort');
		$this->data['text_limit'] = $this->getLanguage()->get('text_limit');
		
		$this->data['entry_search'] = $this->getLanguage()->get('entry_search');
    	$this->data['entry_description'] = $this->getLanguage()->get('entry_description');
		  
    	$this->data['button_search'] = $this->getLanguage()->get('button_search');
		$this->data['button_cart'] = $this->getLanguage()->get('button_cart');
		$this->data['button_wishlist'] = $this->getLanguage()->get('button_wishlist');
		$this->data['button_compare'] = $this->getLanguage()->get('button_compare');

		$this->data['compare'] = $this->getUrl()->link('product/compare');
		
		// 3 Level Category Search
		$this->data['categories'] = array();
					
		$categories_1 = CategoryDAO::getInstance()->getCategoriesByParentId(0);
		
		foreach ($categories_1 as $category_1) {
			$level_2_data = array();
			
			$categories_2 = CategoryDAO::getInstance()->getCategoriesByParentId($category_1->getId());
			
			foreach ($categories_2 as $category_2) {
				$level_3_data = array();
				
				$categories_3 = CategoryDAO::getInstance()->getCategoriesByParentId($category_2->getId());
				
				foreach ($categories_3 as $category_3) {
					$level_3_data[] = array(
						'category_id' => $category_3->getId(),
						'name'        => $category_3->getDescription()->getName(),
					);
				}
				
				$level_2_data[] = array(
					'category_id' => $category_2->getId(),
					'name'        => $category_2->getDescription()->getName(),
					'children'    => $level_3_data
				);					
			}
			
			$this->data['categories'][] = array(
				'category_id' => $category_1->getId(),
				'name'        => $category_1->getDescription()->getName(),
				'children'    => $level_2_data
			);
		}

        #kabantejay synonymizer start
        $result['description'] = null;
        /// Expression below makes no sense as it refers to non-initialized variable
//        $result['description'] = preg_replace_callback(
//            '/\{  (.*?)  \}/xs',
//            function ($m) {
//                $ar = explode("|", $m[1]);
//                return $ar[array_rand($ar, 1)];
//            },
//            $result['description']
//        );
        #kabantejay synonymizer end
		
		$this->data['products'] = array();
		
		if (isset($this->request->get['filter_name']) || isset($this->request->get['filter_tag'])) {
			$data = array(
				'filterName'         => $filter_name,
//				'filterTag'          => $filter_tag,
//				'filterDescription'  => $filter_description,
				'filterCategoryId'  => $filter_category_id,
				'filterSubCategories' => $filter_sub_category,
				'sort'                => $sort,
				'order'               => $order,
				'start'               => ($page - 1) * $limit,
				'limit'               => $limit
			);
					
			$product_total = ProductDAO::getInstance()->getProductsCount($data);
			$results = ProductDAO::getInstance()->getProducts($data);
            if ($sort == null) {
                $results = $this->sortByRelevance($results, $filter_name);
            }

			foreach ($results as $product) {
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
				
				if ((float)$product->getSpecialPrice($this->getCurrentCustomer()->getCustomerGroupId())) {
					$special = $this->getCurrency()->format($product->getSpecialPrice($this->getCurrentCustomer()->getCustomerGroupId()));
				} else {
					$special = false;
				}	
//
//                if ($this->getConfig()->get('config_review_status')) {
//					$rating = (int)$product->getRating();
//				} else {
//					$rating = false;
//				}
			
				$this->data['products'][] = array(
					'product_id'  => (isset($product) ? $product->getId() : ''),
					'thumb'       => (isset($product) ? $image : ''),
					'name'        => (isset($product) ? $product->getName() : ''),
					'description' => (isset($product) ? utf8_truncate(strip_tags(html_entity_decode($product->getDescription()->getDescription($this->getLanguage()->getId())->getDescription(), ENT_QUOTES, 'UTF-8')), 400, '&nbsp;&hellip;', true) : ''),
					'price'       => (isset($product) ? $price : ''),
					'special'     => (isset($product) ? $special : ''),
//					'tax'         => (isset($product) ? $tax : ''),
					'rating'      => (isset($product) ? $product->getRating() : ''),
					'reviews'     => (isset($product) ? sprintf($this->getLanguage()->get('text_reviews'), (int)$product->getReviewsCount()) : ''),
					'href'        => (isset($product) ? $this->getUrl()->link('product/product', $url . '&product_id=' . $product->getId()) : '')
				);
			}
					
			$url = '';
			
			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . $this->request->get['filter_name'];
			}
			
			if (isset($this->request->get['filter_tag'])) {
				$url .= '&filter_tag=' . $this->request->get['filter_tag'];
			}
					
			if (isset($this->request->get['filter_description'])) {
				$url .= '&filter_description=' . $this->request->get['filter_description'];
			}
			
			if (isset($this->request->get['filter_category_id'])) {
				$url .= '&filter_category_id=' . $this->request->get['filter_category_id'];
			}
			
			if (isset($this->request->get['filter_sub_category'])) {
				$url .= '&filter_sub_category=' . $this->request->get['filter_sub_category'];
			}
					
						
			$this->data['sorts'] = array();
			
//			$this->data['sorts'][] = array(
//				'text'  => $this->getLanguage()->get('text_default'),
//				'value' => 'p.sort_order-ASC',
//				'href'  => $this->getUrl()->link('product/search', 'sort=p.sort_order&order=ASC' . $url)
//			);

            $this->data['sorts'][] = array(
                'text'  => $this->getLanguage()->get('RELEVANCE'),
                'value' => null,
                'href'  => $this->getUrl()->link('product/search', '' . $url)
            );


            $this->data['sorts'][] = array(
				'text'  => $this->getLanguage()->get('text_name_asc'),
				'value' => 'pd.name-ASC',
				'href'  => $this->getUrl()->link('product/search', 'sort=pd.name&order=ASC' . $url)
			); 
	
			$this->data['sorts'][] = array(
				'text'  => $this->getLanguage()->get('text_name_desc'),
				'value' => 'pd.name-DESC',
				'href'  => $this->getUrl()->link('product/search', 'sort=pd.name&order=DESC' . $url)
			);
	
			$this->data['sorts'][] = array(
				'text'  => $this->getLanguage()->get('text_price_asc'),
				'value' => 'p.price-ASC',
				'href'  => $this->getUrl()->link('product/search', 'sort=p.price&order=ASC' . $url)
			); 
	
			$this->data['sorts'][] = array(
				'text'  => $this->getLanguage()->get('text_price_desc'),
				'value' => 'p.price-DESC',
				'href'  => $this->getUrl()->link('product/search', 'sort=p.price&order=DESC' . $url)
			); 
			
			$this->data['sorts'][] = array(
				'text'  => $this->getLanguage()->get('text_rating_desc'),
				'value' => 'rating-DESC',
				'href'  => $this->getUrl()->link('product/search', 'sort=rating&order=DESC' . $url)
			); 
			
			$this->data['sorts'][] = array(
				'text'  => $this->getLanguage()->get('text_rating_asc'),
				'value' => 'rating-ASC',
				'href'  => $this->getUrl()->link('product/search', 'sort=rating&order=ASC' . $url)
			);
			
			$this->data['sorts'][] = array(
				'text'  => $this->getLanguage()->get('text_model_asc'),
				'value' => 'p.model-ASC',
				'href'  => $this->getUrl()->link('product/search', 'sort=p.model&order=ASC' . $url)
			); 
	
			$this->data['sorts'][] = array(
				'text'  => $this->getLanguage()->get('text_model_desc'),
				'value' => 'p.model-DESC',
				'href'  => $this->getUrl()->link('product/search', 'sort=p.model&order=DESC' . $url)
			);
	
			$this->data['limits'] = array();
			
			$this->data['limits'][] = array(
				'text'  => $this->getConfig()->get('config_catalog_limit'),
				'value' => $this->getConfig()->get('config_catalog_limit'),
				'href'  => $this->getUrl()->link('product/search', $url . '&limit=' . $this->getConfig()->get('config_catalog_limit'))
			);
						
			$this->data['limits'][] = array(
				'text'  => 25,
				'value' => 25,
				'href'  => $this->getUrl()->link('product/search', $url . '&limit=25')
			);
			
			$this->data['limits'][] = array(
				'text'  => 50,
				'value' => 50,
				'href'  => $this->getUrl()->link('product/search', $url . '&limit=50')
			);
	
			$this->data['limits'][] = array(
				'text'  => 75,
				'value' => 75,
				'href'  => $this->getUrl()->link('product/search', $url . '&limit=75')
			);
			
			$this->data['limits'][] = array(
				'text'  => 100,
				'value' => 100,
				'href'  => $this->getUrl()->link('product/search', $url . '&limit=100')
			);
					
					
			$pagination = new Pagination();
			$pagination->total = $product_total;
			$pagination->page = $page;
			$pagination->limit = $limit;
			$pagination->text = $this->getLanguage()->get('text_pagination');
			$pagination->url = $this->getUrl()->link('product/search', $url . '&page={page}');
			
			$this->data['pagination'] = $pagination->render();
		}	
		
		$this->data['filter_name'] = $filter_name;
//		$this->data['filter_description'] = $filter_description;
		$this->data['filter_category_id'] = $filter_category_id;
		$this->data['filter_sub_category'] = $filter_sub_category;
				
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

        $templateFile = '/template/product/search.tpl.php';
        $templateDir = (file_exists(DIR_TEMPLATE . $this->getConfig()->get('config_template') . $templateFile))
            ? $this->getConfig()->get('config_template')
            : $templateDir = 'default';
		$this->getResponse()->setOutput($this->render($templateDir . $templateFile));
  	}

    /**
     * @param Product[] $results
     * @param string $filter_name
     * @return mixed
     */
    private function sortByRelevance($results, $filter_name) {
        $searchWords = explode(' ', $filter_name);
        $resultsWeights = array();
        foreach (array_keys($results) as $i) {
            $resultsWeights[$i] = 0;
            if (is_numeric(stripos($results[$i]->getModel(), $filter_name))) {
                $resultsWeights[$i] += SEARCH_WEIGHT_FULL_PHRASE_MODEL;
            }
            if (is_numeric(stripos($results[$i]->getName(), $filter_name))) {
                $resultsWeights[$i] += SEARCH_WEIGHT_FULL_PHRASE_NAME;
            }
            if (is_numeric(stripos(implode(' ', $results[$i]->getTags()), $filter_name))) {
                $resultsWeights[$i] += SEARCH_WEIGHT_FULL_PHRASE_TAG;
            }
            if (is_numeric(stripos($results[$i]->getDescription()->getDescription($this->getLanguage()->getId())->getDescription(), $filter_name))) {
                $resultsWeights[$i] += SEARCH_WEIGHT_FULL_PHRASE_DESCR;
            }
            foreach ($searchWords as $searchWord) {
                if (is_numeric(stripos($results[$i]->getModel(), $searchWord))) {
                    $resultsWeights[$i] += SEARCH_WEIGHT_WORD_MODEL;
                }
                if (is_numeric(stripos($results[$i]->getName(), $searchWord))) {
                    $resultsWeights[$i] += SEARCH_WEIGHT_WORD_NAME;
                }
                if (is_numeric(stripos(implode(' ', $results[$i]->getTags()), $searchWord))) {
                    $resultsWeights[$i] += SEARCH_WEIGHT_WORD_TAG;
                }
                if (is_numeric(stripos($results[$i]->getDescription()->getDescription($this->getLanguage()->getId())->getDescription(), $searchWord))) {
                    $resultsWeights[$i] += SEARCH_WEIGHT_WORD_DESCR;
                }
            }
        }
        array_multisort($resultsWeights, SORT_DESC, $results);
        return $results;
    }
}
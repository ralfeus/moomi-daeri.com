<?php
use model\catalog\ProductDAO;
use model\sale\CustomerDAO;
use system\engine\AdminController;

class ControllerCommonHome extends AdminController {
    public function __construct($registry) {
        parent::__construct($registry);
    }

	public function index() {

    $this->getLoader()->language('common/home');
	 
		$this->document->setTitle($this->getLanguage()->get('heading_title'));
		
    $this->data['heading_title'] = $this->getLanguage()->get('heading_title');
		
		$this->data['text_overview'] = $this->getLanguage()->get('text_overview');
		$this->data['text_statistics'] = $this->getLanguage()->get('text_statistics');
		$this->data['text_latest_10_orders'] = $this->getLanguage()->get('text_latest_10_orders');
		$this->data['text_total_sale'] = $this->getLanguage()->get('text_total_sale');
		$this->data['text_total_sale_year'] = $this->getLanguage()->get('text_total_sale_year');
		$this->data['text_total_order'] = $this->getLanguage()->get('text_total_order');
		$this->data['text_total_customer'] = $this->getLanguage()->get('text_total_customer');
		$this->data['text_total_customer_approval'] = $this->getLanguage()->get('text_total_customer_approval');
		$this->data['text_total_review_approval'] = $this->getLanguage()->get('text_total_review_approval');
		$this->data['text_total_affiliate'] = $this->getLanguage()->get('text_total_affiliate');
		$this->data['text_total_affiliate_approval'] = $this->getLanguage()->get('text_total_affiliate_approval');
		$this->data['text_day'] = $this->getLanguage()->get('text_day');
		$this->data['text_week'] = $this->getLanguage()->get('text_week');
		$this->data['text_month'] = $this->getLanguage()->get('text_month');
		$this->data['text_year'] = $this->getLanguage()->get('text_year');
		$this->data['text_no_results'] = $this->getLanguage()->get('text_no_results');

		$this->data['column_order'] = $this->getLanguage()->get('column_order');
		$this->data['column_customer'] = $this->getLanguage()->get('column_customer');
		$this->data['column_status'] = $this->getLanguage()->get('column_status');
		$this->data['column_date_added'] = $this->getLanguage()->get('column_date_added');
		$this->data['column_total'] = $this->getLanguage()->get('column_total');
		$this->data['column_firstname'] = $this->getLanguage()->get('column_firstname');
		$this->data['column_lastname'] = $this->getLanguage()->get('column_lastname');
		$this->data['column_action'] = $this->getLanguage()->get('column_action');
		
		$this->data['entry_range'] = $this->getLanguage()->get('entry_range');
		
										
		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->getLanguage()->get('text_home'),
					'href'      => $this->getUrl()->link('common/home', 'token=' . $this->getSession()->data['token'], 'SSL'),
      		'separator' => false
   		);

		$this->data['token'] = $this->getSession()->data['token'];
		
		$this->getLoader()->model('sale/order');

		$this->data['total_sale'] = $this->getCurrentCurrency()->format($this->model_sale_order->getTotalSales(), $this->config->get('config_currency'));
		$this->data['total_sale_year'] = $this->getCurrentCurrency()->format($this->model_sale_order->getTotalSalesByYear(date('Y')), $this->config->get('config_currency'));
		$this->data['total_order'] = $this->model_sale_order->getTotalOrders();
		
		$this->data['total_customer'] = CustomerDAO::getInstance()->getTotalCustomers();
		$this->data['total_customer_approval'] = CustomerDAO::getInstance()->getTotalCustomersAwaitingApproval();
		
		$this->getLoader()->model('catalog/review');
		
		$this->data['total_review'] = $this->model_catalog_review->getTotalReviews();
		$this->data['total_review_approval'] = $this->model_catalog_review->getTotalReviewsAwaitingApproval();
		
		$this->getLoader()->model('sale/affiliate');
		
		$this->data['total_affiliate'] = $this->model_sale_affiliate->getTotalAffiliates();
		$this->data['total_affiliate_approval'] = $this->model_sale_affiliate->getTotalAffiliatesAwaitingApproval();
				
		$this->data['orders'] = array(); 
		
		$data = array(
			'sort'  => 'o.date_added',
			'order' => 'DESC',
			'start' => 0,
			'limit' => 10
		);
		
		$results = $this->model_sale_order->getOrders($data);
    	
    	foreach ($results as $result) {
			$action = array();
			 
			$action[] = array(
				'text' => $this->getLanguage()->get('text_view'),
				'href' => $this->getUrl()->link('sale/order/info', 'token=' . $this->getSession()->data['token'] . '&order_id=' . $result['order_id'], 'SSL')
			);
					
			$this->data['orders'][] = array(
				'order_id'   => $result['order_id'],
				'customer'   => $result['customer'],
				'status'     => $result['status'],
				'date_added' => date($this->getLanguage()->get('date_format_short'), strtotime($result['date_added'])),
				'total'      => $this->getCurrentCurrency()->format($result['total'], $result['currency_code'], $result['currency_value']),
				'action'     => $action
			);
		}

//		if ($this->config->get('config_currency_auto')) {
//			$this->getLoader()->model('localisation/currency');
//		
//			$this->model_localisation_currency->updateCurrencies();
//		}

//if ($this->getUser()->getUsergroupId() == 1) {
//		$this->template = ;
//} else {
//		$this->template = 'common/homecont.tpl.php';
//}

		
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->getProducts();
				
		$this->getResponse()->setOutput($this->render('common/home.tpl.php'));
    
    //// <---- Clear cache button handler:
$this->data['clear_cache'] = $this->data['home'] = HTTPS_SERVER . 'index.php?route=common/home&clear_cache=true&token=' . $this->getSession()->data['token'];
if(isset($this->request->get['clear_cache'])){
	
	// specify an array of what we need to clear:
	$cacheDirs = array(
		'image_cache' => DIR_IMAGE . 'cache'
		, 'system_cache' => DIR_CACHE
	);

	foreach ($cacheDirs as $cacheDir) {
		foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($cacheDir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $path) {
			$path->isDir() ? rmdir($path->getPathname()) : unlink($path->getPathname());
		}
	}
}
//// ---->
  }
	
	public function chart() {
		$this->getLoader()->language('common/home');
		
		$data = array();
		
		$data['order'] = array();
		$data['customer'] = array();
		$data['xaxis'] = array();
		
		$data['order']['label'] = $this->getLanguage()->get('text_order');
		$data['customer']['label'] = $this->getLanguage()->get('text_customer');
		
		if (isset($this->request->get['range'])) {
			$range = $this->request->get['range'];
		} else {
			$range = 'month';
		}
		
		switch ($range) {
			case 'day':
				for ($i = 0; $i < 24; $i++) {
					$query = $this->db->query("
					    SELECT COUNT(*) AS total
					    FROM `order`
					    WHERE
					        order_status_id > '0'
					        AND DATE(date_added) = '" . date('Y-m-d 00:00:00') . "'
                            AND HOUR(date_added) = '" . (int)$i . "'
                        GROUP BY HOUR(date_added)
                        ORDER BY date_added ASC");
					
					if ($query->num_rows) {
						$data['order']['data'][]  = array($i, (int)$query->row['total']);
					} else {
						$data['order']['data'][]  = array($i, 0);
					}
					
					$query = $this->db->query("
					    SELECT COUNT(*) AS total
					    FROM customer
					    WHERE
					        DATE(date_added) = '" . date('Y-m-d 00:00:00') . "'
					        AND HOUR(date_added) = '" . (int)$i . "'
                        GROUP BY HOUR(date_added)
                        ORDER BY date_added ASC");
					
					if ($query->num_rows) {
						$data['customer']['data'][] = array($i, (int)$query->row['total']);
					} else {
						$data['customer']['data'][] = array($i, 0);
					}
			
					$data['xaxis'][] = array($i, date('H', mktime($i, 0, 0, date('n'), date('j'), date('Y'))));
				}					
				break;
			case 'week':
				$date_start = strtotime('-' . date('w') . ' days'); 
				
				for ($i = 0; $i < 7; $i++) {
					$date = date('Y-m-d', $date_start + ($i * 86400));

					$query = $this->db->query("SELECT COUNT(*) AS total FROM `order` WHERE order_status_id > '0' AND DATE(date_added) = '" . $this->db->escape($date) . "' GROUP BY DATE(date_added)");
			
					if ($query->num_rows) {
						$data['order']['data'][] = array($i, (int)$query->row['total']);
					} else {
						$data['order']['data'][] = array($i, 0);
					}
				
					$query = $this->db->query("SELECT COUNT(*) AS total FROM `customer` WHERE DATE(date_added) = '" . $this->db->escape($date) . "' GROUP BY DATE(date_added)");
			
					if ($query->num_rows) {
						$data['customer']['data'][] = array($i, (int)$query->row['total']);
					} else {
						$data['customer']['data'][] = array($i, 0);
					}
		
					$data['xaxis'][] = array($i, date('D', strtotime($date)));
				}
				
				break;
			default:
			case 'month':
				for ($i = 1; $i <= date('t'); $i++) {
					$date = date('Y') . '-' . date('m') . '-' . $i;
					
					$query = $this->db->query("SELECT COUNT(*) AS total FROM `order` WHERE order_status_id > '0' AND (DATE(date_added) = '" . $this->db->escape($date) . "') GROUP BY DAY(date_added)");
					
					if ($query->num_rows) {
						$data['order']['data'][] = array($i, (int)$query->row['total']);
					} else {
						$data['order']['data'][] = array($i, 0);
					}	
				
					$query = $this->db->query("SELECT COUNT(*) AS total FROM customer WHERE DATE(date_added) = '" . $this->db->escape($date) . "' GROUP BY DAY(date_added)");
			
					if ($query->num_rows) {
						$data['customer']['data'][] = array($i, (int)$query->row['total']);
					} else {
						$data['customer']['data'][] = array($i, 0);
					}	
					
					$data['xaxis'][] = array($i, date('j', strtotime($date)));
				}
				break;
			case 'year':
				for ($i = 1; $i <= 12; $i++) {
					$query = $this->db->query("SELECT COUNT(*) AS total FROM `order` WHERE order_status_id > '0' AND YEAR(date_added) = '" . date('Y') . "' AND MONTH(date_added) = '" . $i . "' GROUP BY MONTH(date_added)");
					
					if ($query->num_rows) {
						$data['order']['data'][] = array($i, (int)$query->row['total']);
					} else {
						$data['order']['data'][] = array($i, 0);
					}
					
					$query = $this->db->query("SELECT COUNT(*) AS total FROM customer WHERE YEAR(date_added) = '" . date('Y') . "' AND MONTH(date_added) = '" . $i . "' GROUP BY MONTH(date_added)");
					
					if ($query->num_rows) { 
						$data['customer']['data'][] = array($i, (int)$query->row['total']);
					} else {
						$data['customer']['data'][] = array($i, 0);
					}
					
					$data['xaxis'][] = array($i, date('M', mktime(0, 0, 0, $i, 1, date('Y'))));
				}			
				break;	
		} 
		
		$this->getResponse()->setOutput(json_encode($data));
	}
	
	public function login() {
		$route = '';
		
		if (isset($this->request->get['route'])) {
			$part = explode('/', $this->request->get['route']);
			
			if (isset($part[0])) {
				$route .= $part[0];
			}
			
			if (isset($part[1])) {
				$route .= '/' . $part[1];
			}
		}
		
		$ignore = array(
			'common/login',
			'common/forgotten',
			'common/reset'
		);	
					
		if (!$this->getUser()->isLogged() && !in_array($route, $ignore)) {
			return $this->forward('common/login');
		}
		
		if (isset($this->request->get['route'])) {
			$ignore = array(
				'common/login',
				'common/logout',
				'common/forgotten',
				'common/reset',
				'error/not_found',
				'error/permission'
			);
						
			$config_ignore = array();
			
			if ($this->config->get('config_token_ignore')) {
				$config_ignore = unserialize($this->config->get('config_token_ignore'));
			}
				
			$ignore = array_merge($ignore, $config_ignore);
			if (!in_array($route, $ignore) && (!isset($_REQUEST['token']) || !isset($this->getSession()->data['token']) || ($_REQUEST['token'] != $this->getSession()->data['token']))) {
				return $this->forward('common/login');
			}
		} else {
			if (!isset($_REQUEST['token']) || !isset($this->getSession()->data['token']) || ($_REQUEST['token'] != $this->getSession()->data['token'])) {
				return $this->forward('common/login');
			}
		}
	}
	
	public function permission() {
		if (isset($this->request->get['route'])) {
			$route = '';
			
			$part = explode('/', $this->request->get['route']);
			
			if (isset($part[0])) {
				$route .= $part[0];
			}
			
			if (isset($part[1])) {
				$route .= '/' . $part[1];
			}
			
			$ignore = array(
				'common/home',
				'common/login',
				'common/logout',
				'common/forgotten',
				'common/reset',
				'error/not_found',
				'error/permission'		
			);			
						
			if (!in_array($route, $ignore) && !$this->getUser()->hasPermission('access', $route)) {
				return $this->forward('error/permission');
			}
		}
	}

	public function getProducts($filter = array()) { //print_r($this->parameters); die();

		if (isset($this->request->get['filter_price'])) {
			$filter_price = $this->request->get['filter_price'];
		} else {
			$filter_price = null;
		}

		if (isset($this->request->get['filter_korean_name'])) {
			$filter_korean_name = $this->request->get['filter_korean_name'];
		} else {
			$filter_korean_name = null;
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'pd.name';
		}
		
		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}
		
		$url = '';
						
		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . $this->request->get['filter_name'];
		}
		
		if (isset($this->request->get['filterModel'])) {
			$url .= '&filterModel=' . $this->request->get['filterModel'];
		}
		
		if (isset($this->request->get['filter_price'])) {
			$url .= '&filter_price=' . $this->request->get['filter_price'];
		}
		
		if (isset($this->request->get['filter_korean_name'])) {
			$url .= '&filter_korean_name=' . $this->request->get['filter_korean_name'];
		}

		if (isset($this->request->get['filter_user_name'])) {
			$url .= '&filter_user_name=' . $this->request->get['filter_user_name'];
		}			

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}
		
		if (isset($this->request->get['filter_manufacturer'])) {
			$url .= '&filter_manufacturer=' . $this->request->get['filter_manufacturer'];
		}
		
		if (isset($this->request->get['filter_supplier'])) {
			$url .= '&filter_supplier=' . $this->request->get['filter_supplier'];
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

		$this->initParameters();

		$filter = array('limit' => ADMIN_LIMIT_PRODUCTS);

		if(isset($this->parameters['page'])) {
			$filter['start'] = ($this->parameters['page'] - 1) * ADMIN_LIMIT_PRODUCTS;
		}
		else {
			$filter['start'] = 0;
		}

		$lastMonthStart  = mktime(0, 0, 0, date("m")-1, 1,   date("Y"));
		$lastMonthEnd        = mktime(0, 0, 0, date("m"), 0, date("Y"));

		if(isset($this->parameters['filterDateAddedFrom'])) {
			$filter['filterDateAddedFrom'] = $this->parameters['filterDateAddedFrom'];
			$this->data['filterDateAddedFrom'] = $this->parameters['filterDateAddedFrom'];
		}
		else {
			$this->data['filterDateAddedFrom'] = date("Y-m-d", $lastMonthStart);
		}

		if(isset($this->parameters['filterDateAddedTo'])) {
			$filter['filterDateAddedTo'] = $this->parameters['filterDateAddedTo'];
			$this->data['filterDateAddedTo'] = $this->parameters['filterDateAddedTo'];
		}
		else {
			$this->data['filterDateAddedTo'] = date("Y-m-d", $lastMonthEnd);
		}

		if(isset($this->parameters['filterUserNameId'])) {
			$filter['filterUserNameId'] = $this->parameters['filterUserNameId'];
		}

		$this->getLoader()->model('catalog/product');
		$products = ProductDAO::getInstance()->getProducts($filter, $filter['sort'], $filter['order'], $filter['start'], $filter['limit']);
		$product_total = ProductDAO::getInstance()->getProductsCount($filter);


		foreach ($products as $product) {//print_r($results); die();

			$action = array();
			$action[] = array(
				'text' => $this->getLanguage()->get('text_edit'),
				'href' => $this->getUrl()->link(
					'catalog/product/update', 
					'token=' . $this->getSession()->data['token'] . '&product_id=' . $product->getId(), 'SSL')
			);

			$link = array();
			$link[] = array(
				'text' => 'click',
				'href' => $product->getSupplierUrl()
			);

			if ($product->getImagePath() && file_exists(DIR_IMAGE . $product->getImagePath())) {
				//$image = $modelToolImage->resize($result['image'], 40, 40);
			} else {
				//$image = $modelToolImage->resize('no_image.jpg', 40, 40);
			}

			$this->data['products'][] = array(
				'product_id' => $product->getId(),
        		'dateAdded' => date('Y-m-d', strtotime($product->getDateAdded())),
				'name'       => $product->getName(),
				'model'      => $product->getModel(),
				'price'      => $product->getPrice(),
				'special'    => null,//$special,
				'image'      => null, //$image,
				'user_name'  => $product->getUser()['username'],
				'status'     => ($product->getStatus() ? $this->getLanguage()->get('text_enabled') : $this->getLanguage()->get('text_disabled')),
				'selected'   => !is_null($this->getRequest()->getParam('selected')) && in_array($product->getId(), $this->getRequest()->getParam('selected')),
				'action'     => $action,
				'link'     	 => $link,
				'korean_name'=> $product->getKoreanName()
			);
		}

		$this->data = array_merge($this->data, $this->parameters);

		$this->data['usernames'] = $this->getUserNames();

		$this->data['button_filter'] = $this->getLanguage()->get('FILTER');
    	$this->data['textResetFilter'] = $this->getLanguage()->get('RESET_FILTER');

		$pagination = new Pagination();
		$pagination->total = $product_total;
		$pagination->page = isset($this->parameters['page']) ? $this->parameters['page'] : 1;
		//echo ADMIN_LIMIT_PRODUCTS; die();
		$pagination->limit = ADMIN_LIMIT_PRODUCTS;
		$pagination->text = $this->getLanguage()->get('text_pagination');
    	unset($this->parameters['page']);
		$pagination->url = $this->getUrl()->link('common/home', 'page={page}', 'SSL');
		$this->data['pagination'] = $pagination->render();

		//return $this->data['products'];
		
	}

    protected function initParameters() {
        if (empty($this->getSession()->data['parameters']['catalog/product']))
            $this->getSession()->data['parameters']['catalog/product'] = array();
        if (empty($_REQUEST['resetFilter'])) {
            foreach ($_REQUEST as $key => $value)
                if (strpos($key, 'filter') === 0) {
                    $this->getSession()->data['parameters']['catalog/product'][$key] = $value;
                }
        }
        else
            $this->getSession()->data['parameters']['catalog/product'] = array();


        $lastMonthStart  = mktime(0, 0, 0, date("m")-1, 1,   date("Y"));
        $lastMonthEnd        = mktime(0, 0, 0, date("m"), 0, date("Y"));

      if (empty($this->getSession()->data['parameters']['common/home']['filterDateAddedFrom']))
          $this->getSession()->data['parameters']['common/home']['filterDateAddedFrom'] = date("Y-m-d", $lastMonthStart);
      if (empty($this->getSession()->data['parameters']['common/home']['filterDateAddedTo']))
          $this->getSession()->data['parameters']['common/home']['filterDateAddedTo'] =  date("Y-m-d", $lastMonthEnd);
      if (empty($this->getSession()->data['parameters']['catalog/product']['filterManufacturerId']) || !is_array($this->getSession()->data['parameters']['catalog/product']['filterManufacturerId']))
          $this->getSession()->data['parameters']['catalog/product']['filterManufacturerId'] =  array();
      if (empty($this->getSession()->data['parameters']['catalog/product']['filterModel']))
          $this->getSession()->data['parameters']['catalog/product']['filterModel'] = null;
      if (empty($this->getSession()->data['parameters']['catalog/product']['filterName']))
          $this->getSession()->data['parameters']['catalog/product']['filterName'] = null;
      if (!isset($this->getSession()->data['parameters']['catalog/product']['filterPrice']) || !is_numeric($this->getSession()->data['parameters']['catalog/product']['filterPrice']))
          $this->getSession()->data['parameters']['catalog/product']['filterPrice'] = null;
      if (!isset($this->getSession()->data['parameters']['catalog/product']['filterKoreanName']))
          $this->getSession()->data['parameters']['catalog/product']['filterKoreanName'] = null;
      if (empty($this->getSession()->data['parameters']['catalog/product']['filterUserNameId']) || !is_array($this->getSession()->data['parameters']['catalog/product']['filterUserNameId']))
          $this->getSession()->data['parameters']['catalog/product']['filterUserNameId'] =  array();
      if (!isset($this->getSession()->data['parameters']['catalog/product']['filterStatus']) || !is_numeric($this->getSession()->data['parameters']['catalog/product']['filterStatus']))
          $this->getSession()->data['parameters']['catalog/product']['filterStatus'] = null;
      if (empty($this->getSession()->data['parameters']['catalog/product']['filterSupplierId']) || !is_array($this->getSession()->data['parameters']['catalog/product']['filterSupplierId']))
          $this->getSession()->data['parameters']['catalog/product']['filterSupplierId'] = array();
      $this->parameters = $this->getSession()->data['parameters']['catalog/product'];
      $this->parameters['page'] = empty($_REQUEST['page']) ? 1 : $_REQUEST['page'];
      $this->parameters['sort'] = empty($_REQUEST['sort']) ? null : $_REQUEST['sort'];
      if (isset($this->getSession()->data['token'])) {
		  $this->parameters['token'] = $this->getSession()->data['token'];
	  } elseif (isset($_REQUEST['token'])) {
		  $this->parameters['token'] = $_REQUEST['token'];
	  } else {
		  $this->parameters['token'] = null;
	  }
  }

  private function getUserNames() {
	  $data = [];
      foreach ($this->parameters as $key => $value) {
          if (strpos($key, 'filter') === false)
              continue;
          $data[$key] = $value;
      }
      unset($data['filterUserNameId']);
      $tmpResult = array();
      $userNames = ProductDAO::getInstance()->getProductUserNames($data);
      
      foreach ($userNames as $userName) {
          if (!in_array($userName['user_id'], $tmpResult))
              $tmpResult[$userName['user_id']] = $userName['user_name'];
      }
      natcasesort($tmpResult);
      return $tmpResult;
  }
}
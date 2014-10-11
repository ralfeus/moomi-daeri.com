<?php   
class ControllerCommonHome extends Controller {   
	public function index() {

    $this->load->language('common/home');
	 
		$this->document->setTitle($this->language->get('heading_title'));
		
    $this->data['heading_title'] = $this->language->get('heading_title');
		
		$this->data['text_overview'] = $this->language->get('text_overview');
		$this->data['text_statistics'] = $this->language->get('text_statistics');
		$this->data['text_latest_10_orders'] = $this->language->get('text_latest_10_orders');
		$this->data['text_total_sale'] = $this->language->get('text_total_sale');
		$this->data['text_total_sale_year'] = $this->language->get('text_total_sale_year');
		$this->data['text_total_order'] = $this->language->get('text_total_order');
		$this->data['text_total_customer'] = $this->language->get('text_total_customer');
		$this->data['text_total_customer_approval'] = $this->language->get('text_total_customer_approval');
		$this->data['text_total_review_approval'] = $this->language->get('text_total_review_approval');
		$this->data['text_total_affiliate'] = $this->language->get('text_total_affiliate');
		$this->data['text_total_affiliate_approval'] = $this->language->get('text_total_affiliate_approval');
		$this->data['text_day'] = $this->language->get('text_day');
		$this->data['text_week'] = $this->language->get('text_week');
		$this->data['text_month'] = $this->language->get('text_month');
		$this->data['text_year'] = $this->language->get('text_year');
		$this->data['text_no_results'] = $this->language->get('text_no_results');

		$this->data['column_order'] = $this->language->get('column_order');
		$this->data['column_customer'] = $this->language->get('column_customer');
		$this->data['column_status'] = $this->language->get('column_status');
		$this->data['column_date_added'] = $this->language->get('column_date_added');
		$this->data['column_total'] = $this->language->get('column_total');
		$this->data['column_firstname'] = $this->language->get('column_firstname');
		$this->data['column_lastname'] = $this->language->get('column_lastname');
		$this->data['column_action'] = $this->language->get('column_action');
		
		$this->data['entry_range'] = $this->language->get('entry_range');
		
										
		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
					'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);

		$this->data['token'] = $this->session->data['token'];
		
		$this->load->model('sale/order');

		$this->data['total_sale'] = $this->currency->format($this->model_sale_order->getTotalSales(), $this->config->get('config_currency'));
		$this->data['total_sale_year'] = $this->currency->format($this->model_sale_order->getTotalSalesByYear(date('Y')), $this->config->get('config_currency'));
		$this->data['total_order'] = $this->model_sale_order->getTotalOrders();
		
		$this->load->model('sale/customer');
		
		$this->data['total_customer'] = $this->model_sale_customer->getTotalCustomers();
		$this->data['total_customer_approval'] = $this->model_sale_customer->getTotalCustomersAwaitingApproval();
		
		$this->load->model('catalog/review');
		
		$this->data['total_review'] = $this->model_catalog_review->getTotalReviews();
		$this->data['total_review_approval'] = $this->model_catalog_review->getTotalReviewsAwaitingApproval();
		
		$this->load->model('sale/affiliate');
		
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
				'text' => $this->language->get('text_view'),
				'href' => $this->url->link('sale/order/info', 'token=' . $this->session->data['token'] . '&order_id=' . $result['order_id'], 'SSL')
			);
					
			$this->data['orders'][] = array(
				'order_id'   => $result['order_id'],
				'customer'   => $result['customer'],
				'status'     => $result['status'],
				'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'total'      => $this->currency->format($result['total'], $result['currency_code'], $result['currency_value']),
				'action'     => $action
			);
		}

		if ($this->config->get('config_currency_auto')) {
			$this->load->model('localisation/currency');
		
			$this->model_localisation_currency->updateCurrencies();
		}
		
		$this->template = 'common/home.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->getProducts();
				
		$this->response->setOutput($this->render());
  }
	
	public function chart() {
		$this->load->language('common/home');
		
		$data = array();
		
		$data['order'] = array();
		$data['customer'] = array();
		$data['xaxis'] = array();
		
		$data['order']['label'] = $this->language->get('text_order');
		$data['customer']['label'] = $this->language->get('text_customer');
		
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
		
		$this->response->setOutput(json_encode($data));
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
					
		if (!$this->user->isLogged() && !in_array($route, $ignore)) {
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
			if (!in_array($route, $ignore) && (!isset($_REQUEST['token']) || !isset($this->session->data['token']) || ($_REQUEST['token'] != $this->session->data['token']))) {
				return $this->forward('common/login');
			}
		} else {
			if (!isset($_REQUEST['token']) || !isset($this->session->data['token']) || ($_REQUEST['token'] != $this->session->data['token'])) {
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
						
			if (!in_array($route, $ignore) && !$this->user->hasPermission('access', $route)) {
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

		$this->load->model('catalog/product');
		$results = $this->model_catalog_product->getProducts($filter);
		$products = array();
		$product_total = $this->model_catalog_product->getTotalProducts($filter);


		foreach ($results as $result) {//print_r($results); die();

			$action = array();
			$action[] = array(
				'text' => $this->language->get('text_edit'),
				'href' => $this->url->link('catalog/product/update', 'token=' . $this->session->data['token'] . '&product_id=' . $result['product_id']/* . $url*/, 'SSL')
			);

			$link = array();
			$link[] = array(
				'text' => 'click',
				'href' => $result['link']
			);

			if ($result['image'] && file_exists(DIR_IMAGE . $result['image'])) {
				//$image = $this->model_tool_image->resize($result['image'], 40, 40);
			} else {
				//$image = $this->model_tool_image->resize('no_image.jpg', 40, 40);
			}

			$this->data['products'][] = array(
				'product_id' => $result['product_id'],
        'dateAdded' => date('Y-m-d', strtotime($result['date_added'])),
				'name'       => $result['name'],
				'model'      => $result['model'],
				'price'      => $result['price'],
				'special'    => null,//$special,
				'image'      => null, //$image,
				'user_name'  => $result['user_name'],
				'status'     => ($result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled')),
				'selected'   => isset($this->request->post['selected']) && in_array($result['product_id'], $this->request->post['selected']),
				'action'     => $action,
				'link'     	 => $link,
				'korean_name'=> $result['korean_name'],
				'manufacturer_page_url' => empty($result['manufacturer_page_url']) ? '' : $result['manufacturer_page_url']
			);
		}

		$this->data = array_merge($this->data, $this->parameters);

		$this->data['usernames'] = $this->getUserNames();

		$this->data['button_filter'] = $this->language->get('FILTER');
    $this->data['textResetFilter'] = $this->language->get('RESET_FILTER');

		$pagination = new Pagination();
		$pagination->total = $product_total;
		$pagination->page = isset($this->parameters['page']) ? $this->parameters['page'] : 1;
		//echo ADMIN_LIMIT_PRODUCTS; die();
		$pagination->limit = ADMIN_LIMIT_PRODUCTS;
		$pagination->text = $this->language->get('text_pagination');
    unset($this->parameters['page']);
		$pagination->url = $this->url->link('common/home', 'page={page}', 'SSL');
		$this->data['pagination'] = $pagination->render();

		//return $this->data['products'];
		
	}

    protected function initParameters() {
        if (empty($this->session->data['parameters']['catalog/product']))
            $this->session->data['parameters']['catalog/product'] = array();
        if (empty($_REQUEST['resetFilter'])) {
            foreach ($_REQUEST as $key => $value)
                if (strpos($key, 'filter') === 0) {
                    $this->session->data['parameters']['catalog/product'][$key] = $value;
                }
        }
        else
            $this->session->data['parameters']['catalog/product'] = array();


        $lastMonthStart  = mktime(0, 0, 0, date("m")-1, 1,   date("Y"));
        $lastMonthEnd        = mktime(0, 0, 0, date("m"), 0, date("Y"));

      if (empty($this->session->data['parameters']['common/home']['filterDateAddedFrom']))
          $this->session->data['parameters']['common/home']['filterDateAddedFrom'] = date("Y-m-d", $lastMonthStart);
      if (empty($this->session->data['parameters']['common/home']['filterDateAddedTo']))
          $this->session->data['parameters']['common/home']['filterDateAddedTo'] =  date("Y-m-d", $lastMonthEnd);
      if (empty($this->session->data['parameters']['catalog/product']['filterManufacturerId']) || !is_array($this->session->data['parameters']['catalog/product']['filterManufacturerId']))
          $this->session->data['parameters']['catalog/product']['filterManufacturerId'] =  array();
      if (empty($this->session->data['parameters']['catalog/product']['filterModel']))
          $this->session->data['parameters']['catalog/product']['filterModel'] = null;
      if (empty($this->session->data['parameters']['catalog/product']['filterName']))
          $this->session->data['parameters']['catalog/product']['filterName'] = null;
      if (!isset($this->session->data['parameters']['catalog/product']['filterPrice']) || !is_numeric($this->session->data['parameters']['catalog/product']['filterPrice']))
          $this->session->data['parameters']['catalog/product']['filterPrice'] = null;
      if (!isset($this->session->data['parameters']['catalog/product']['filterKoreanName']))
          $this->session->data['parameters']['catalog/product']['filterKoreanName'] = null;
      if (empty($this->session->data['parameters']['catalog/product']['filterUserNameId']) || !is_array($this->session->data['parameters']['catalog/product']['filterUserNameId']))
          $this->session->data['parameters']['catalog/product']['filterUserNameId'] =  array();
      if (!isset($this->session->data['parameters']['catalog/product']['filterStatus']) || !is_numeric($this->session->data['parameters']['catalog/product']['filterStatus']))
          $this->session->data['parameters']['catalog/product']['filterStatus'] = null;
      if (empty($this->session->data['parameters']['catalog/product']['filterSupplierId']) || !is_array($this->session->data['parameters']['catalog/product']['filterSupplierId']))
          $this->session->data['parameters']['catalog/product']['filterSupplierId'] = array();
      $this->parameters = $this->session->data['parameters']['catalog/product'];
      $this->parameters['page'] = empty($_REQUEST['page']) ? 1 : $_REQUEST['page'];
      $this->parameters['sort'] = empty($_REQUEST['sort']) ? null : $_REQUEST['sort'];
      $this->parameters['token'] = isset($this->session->data['token']) ? $this->session->data['token'] : $_REQUEST['token'];
  }

  private function getUserNames()
  {
      foreach ($this->parameters as $key => $value)
      {
          if (strpos($key, 'filter') === false)
              continue;
          $data[$key] = $value;
      }
      unset($data['filterUserNameId']);
      $tmpResult = array();
      $usernames = $this->model_catalog_product->getProductUserNames($data);
      
      foreach ($usernames as $username)
      {
          if (!in_array($username['user_id'], $tmpResult))
              $tmpResult[$username['user_id']] = $username['user_name'];
      }
      natcasesort($tmpResult);
      return $tmpResult;
  }


}
?>
<?php 
class ControllerCatalogProduct extends Controller {
	private $error = array();
    /** @var ModelCatalogProduct */
    private $modelCatalogProduct;

    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->load->language('catalog/product');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->data['heading_title'] = $this->language->get('heading_title');
        $this->modelCatalogProduct = $this->load->model('catalog/product');
        $this->takeSessionVariables();
    }

  	public function index() {
	    $this->getList();
  	}

    protected function initParameters()
    {
        if (empty($this->session->data['parameters']['catalog/product']))
            $this->session->data['parameters']['catalog/product'] = array();
        if (empty($_REQUEST['resetFilter']))
        {
            foreach ($_REQUEST as $key => $value)
                if (strpos($key, 'filter') == 0)
                    $this->session->data['parameters']['catalog/product'][$key] = $value;
        }
        else
            $this->session->data['parameters']['catalog/product'] = array();

        if (empty($this->session->data['parameters']['catalog/product']['filterDateAddedFrom']))
            $this->session->data['parameters']['catalog/product']['filterDateAddedFrom'] = date('Y-01-01');
        if (empty($this->session->data['parameters']['catalog/product']['filterDateAddedTo']))
            $this->session->data['parameters']['catalog/product']['filterDateAddedTo'] =  date('Y-01-01', time() + 31536000);
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
        $this->parameters['confirm'] = empty($_REQUEST['confirm']) ? false : $_REQUEST['confirm'];
        $this->parameters['page'] = empty($_REQUEST['page']) ? 1 : $_REQUEST['page'];
        $this->parameters['selectedItems'] = empty($_REQUEST['selected']) ? array() : $_REQUEST['selected'];
        $this->parameters['sort'] = empty($_REQUEST['sort']) ? null : $_REQUEST['sort'];
        $this->parameters['token'] = $this->session->data['token'];
    }
  
  	public function insert() {

    	if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->addMemoOption();
    		$data = $this->request->post;
    		$data['user_id'] = $this->user->getId();
            $this->model_catalog_product->addProduct($data);
	            Audit::getInstance($this->registry)->addAdminEntry(AUDIT_ADMIN_PRODUCT_CREATE, $_REQUEST);
				$this->session->data['success'] = $this->language->get('text_success');
		  
				$url = '';
				
				if (isset($this->request->get['filter_name'])) {
					$url .= '&filter_name=' . $this->request->get['filter_name'];
				}
			
				if (isset($this->request->get['filter_model'])) {
					$url .= '&filter_model=' . $this->request->get['filter_model'];
				}
				
				if (isset($this->request->get['filter_price'])) {
					$url .= '&filter_price=' . $this->request->get['filter_price'];
				}
				
				if (isset($this->request->get['filter_korean_name'])) {
					$url .= '&filter_korean_name=' . $this->request->get['filter_korean_name'];
				}
				
				if (isset($this->request->get['filter_status'])) {
					$url .= '&filter_status=' . $this->request->get['filter_status'];
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
				
				$this->redirect($this->url->link('catalog/product', 'token=' . $this->session->data['token'] . $url, 'SSL'));
    	}
	
    	$this->getForm();
  	}

  	public function update() {
    	if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_catalog_product->editProduct($this->request->get['product_id'], $this->request->post);
			Audit::getInstance($this->registry)->addAdminEntry(
                AUDIT_ADMIN_PRODUCT_UPDATE,
                array('route' => $_REQUEST['route'], 'productId' => $_REQUEST['product_id'])
            );
			$this->session->data['success'] = $this->language->get('text_success');
			
			$url = '';
			
			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . $this->request->get['filter_name'];
			}
		
			if (isset($this->request->get['filter_model'])) {
				$url .= '&filter_model=' . $this->request->get['filter_model'];
			}
			
			if (isset($this->request->get['filter_price'])) {
				$url .= '&filter_price=' . $this->request->get['filter_price'];
			}
			
			if (isset($this->request->get['filter_korean_name'])) {
				$url .= '&filter_korean_name=' . $this->request->get['filter_korean_name'];
			}	
		
			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
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
			
			$this->redirect($this->url->link('catalog/product', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

    	$this->getForm();
  	}

  	public function delete() {
		if (isset($this->parameters['selectedItems']) && $this->validateDelete()) {
			foreach ($this->parameters['selectedItems'] as $product_id) {
				$this->modelCatalogProduct->deleteProduct($product_id);
                Audit::getInstance($this->registry)->addAdminEntry(
                    AUDIT_ADMIN_PRODUCT_DELETE,
                    array('route' => $this->parameters['route'], 'selectedItems' => $this->parameters['selectedItems'])
                );
	  		}

			$this->session->data['success'] = $this->language->get('text_success');
			$this->redirect($this->url->link('catalog/product', $this->buildUrlParameterString($this->parameters, array('route' => null)), 'SSL'));
		}

    	$this->redirect($this->url->link('catalog/product', $this->buildUrlParameterString($this->parameters, array('route' => null)), 'SSL'));
  	}

  	public function copy() {
		if (isset($this->request->post['selected']) && $this->validateCopy()) {
			foreach ($this->request->post['selected'] as $product_id) {
				$this->modelCatalogProduct->copyProduct($product_id);
	  		}

			$this->session->data['success'] = $this->language->get('text_success');
			
			$url = '';
			
			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . $this->request->get['filter_name'];
			}
		
			if (isset($this->request->get['filter_model'])) {
				$url .= '&filter_model=' . $this->request->get['filter_model'];
			}
			
			if (isset($this->request->get['filter_price'])) {
				$url .= '&filter_price=' . $this->request->get['filter_price'];
			}
			
			if (isset($this->request->get['filter_korean_name'])) {
				$url .= '&filter_korean_name=' . $this->request->get['filter_korean_name'];
			}	
		
			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
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
			
			$this->redirect($this->url->link('catalog/product', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

    	$this->getList();
  	}
	
  private function getList() {				
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
		
		if (isset($this->request->get['filter_model'])) {
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

		$this->data['insert'] = $this->url->link('catalog/product/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['copy'] = $this->url->link('catalog/product/copy', 'token=' . $this->session->data['token'] . $url, 'SSL');	
		$this->data['delete'] = $this->url->link('catalog/product/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');
        $this->data['enable'] = $this->url->link('catalog/product/enable', 'token=' . $this->session->data['token'] . $url, 'SSL');
        $this->data['disable'] = $this->url->link('catalog/product/disable', 'token=' . $this->session->data['token'] . $url, 'SSL');

		$this->data['products'] = array();
        $data = $this->parameters;

        $data['start']           = ($data['page'] - 1) * $this->config->get('config_admin_limit');
        $data['limit']           = $this->config->get('config_admin_limit');
		$data['filter_price']	  = $filter_price;
		$data['filter_korean_name'] = $filter_korean_name;
		$data['sort']            = $sort;
		$data['order']           = $order;

		$this->load->model('tool/image');
		$this->load->model('catalog/supplier');
		$this->load->model('catalog/manufacturer');

		$product_total = $this->model_catalog_product->getTotalProducts($data);

		$results = $this->model_catalog_product->getProducts($data);
        $this->data['suppliers'] = $this->getSuppliers();
        $this->data['usernames'] = $this->getUserNames();
        $this->data['manufacturers'] = $this->getManufacturers();

    
		foreach ($results as $result) {
			$action = array();
			$action[] = array(
				'text' => $this->language->get('text_edit'),
				'href' => $this->url->link('catalog/product/update', 'token=' . $this->session->data['token'] . '&product_id=' . $result['product_id'] . $url, 'SSL')
			);

			$link = array();
			$link[] = array(
				'text' => 'click',
				'href' => $result['link']

			);
			
			if ($result['image'] && file_exists(DIR_IMAGE . $result['image'])) {
				$image = $this->model_tool_image->resize($result['image'], 100, 100);
			} else {
				$image = $this->model_tool_image->resize('no_image.jpg', 100, 100);
			}
	
			$special = false;
			$product_specials = $this->model_catalog_product->getProductSpecials($result['product_id']);
			
			foreach ($product_specials  as $product_special) {
				if (($product_special['date_start'] == '0000-00-00' || $product_special['date_start'] > date('Y-m-d')) && ($product_special['date_end'] == '0000-00-00' || $product_special['date_end'] < date('Y-m-d'))) {
					$special = $product_special['price'];
					break;
				}					
			}
			$suppliers = $this->model_catalog_supplier->getSupplier($result['supplier_id']);
			if (empty($suppliers))
				$suppliers['name'] = "";

            $manufacturers = $this->model_catalog_manufacturer->getManufacturer($result['manufacturer_id']);
			if (empty($manufacturers))
				$manufacturers['name'] = "";

      $this->data['products'][] = array(
				'product_id' => $result['product_id'],
        'dateAdded' => date('Y-m-d', strtotime($result['date_added'])),
				'name'       => $result['name'],
				'model'      => $result['model'],
				'price'      => $result['price'],
				'special'    => $special,
				'image'      => $image,
				'user_name'  => $result['user_name'],
				'status'     => ($result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled')),
				'manufacturer'=> $manufacturers['name'],
				'supplier'	 => $suppliers['name'],
				'selected'   => isset($this->request->post['selected']) && in_array($result['product_id'], $this->request->post['selected']),
				'action'     => $action,
				'link'     	 => $link,
				'korean_name'=> $result['korean_name'],
				'manufacturer_page_url' => empty($result['manufacturer_page_url']) ? '' : $result['manufacturer_page_url']
			);
    }
		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');		
		$this->data['text_no_results'] = $this->language->get('text_no_results');		
		$this->data['text_image_manager'] = $this->language->get('text_image_manager');		
			
		$this->data['column_image'] = $this->language->get('column_image');		
		$this->data['column_name'] = $this->language->get('column_name');		
		$this->data['column_model'] = $this->language->get('column_model');		
		$this->data['column_price'] = $this->language->get('column_price');		
		$this->data['column_korean_name'] = $this->language->get('column_korean_name');
		$this->data['column_user_name'] = $this->language->get('column_user_name');		
		$this->data['column_status'] = $this->language->get('column_status');
		$this->data['columnManufacturer'] = $this->language->get('COLUMN_MANUFACTURER');
		$this->data['columnSupplier'] = $this->language->get('COLUMN_SUPPLIER');
    $this->data['textDateAdded'] = $this->language->get('DATE_ADDED');
		$this->data['column_action'] = $this->language->get('column_action');		
				
		$this->data['button_copy'] = $this->language->get('button_copy');		
		$this->data['button_insert'] = $this->language->get('button_insert');		
		$this->data['button_delete'] = $this->language->get('button_delete');		
		$this->data['button_filter'] = $this->language->get('FILTER');
    $this->data['textResetFilter'] = $this->language->get('RESET_FILTER');
		$this->data['buttonManufacturer'] = $this->language->get('BUTTON_MANUFACTURER');
    $this->data['buttonSupplier'] = $this->language->get('BUTTON_SUPPLIER');
    $this->data['button_enable'] = $this->language->get('ENABLE');
    $this->data['button_disable'] = $this->language->get('DISABLE');


 		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];
		
			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}

		$url = '';

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . $this->request->get['filter_name'];
		}
		
		if (isset($this->request->get['filter_model'])) {
			$url .= '&filter_model=' . $this->request->get['filter_model'];
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
								
		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
					
		$this->data['sort_name'] = $this->url->link('catalog/product', 'token=' . $this->session->data['token'] . '&sort=pd.name' . $url, 'SSL');
		$this->data['sort_model'] = $this->url->link('catalog/product', 'token=' . $this->session->data['token'] . '&sort=p.model' . $url, 'SSL');
		$this->data['sort_price'] = $this->url->link('catalog/product', 'token=' . $this->session->data['token'] . '&sort=p.price' . $url, 'SSL');
		$this->data['sort_korean_name'] = $this->url->link('catalog/product', 'token=' . $this->session->data['token'] . '&sort=a.text' . $url, 'SSL');
		$this->data['sort_user_name'] = $this->url->link('catalog/product', 'token=' . $this->session->data['token'] . '&sort=u.username' . $url, 'SSL');
		$this->data['sort_status'] = $this->url->link('catalog/product', 'token=' . $this->session->data['token'] . '&sort=p.status' . $url, 'SSL');
		$this->data['manufacturer'] = $this->url->link('catalog/product', 'token=' . $this->session->data['token'] . '&sort=p.manufacturer' . $url, 'SSL');
		$this->data['supplier'] = $this->url->link('catalog/product', 'token=' . $this->session->data['token'] . '&sort=p.supplier' . $url, 'SSL');
		$this->data['sort_order'] = $this->url->link('catalog/product', 'token=' . $this->session->data['token'] . '&sort=p.sort_order' . $url, 'SSL');

		$pagination = new Pagination();
		$pagination->total = $product_total;
		$pagination->page = $this->parameters['page'];
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text = $this->language->get('text_pagination');
        unset($this->parameters['page']);
		$pagination->url = $this->url->link('catalog/product', $this->buildUrlParameterString($this->parameters) . '&page={page}', 'SSL');
		$this->data['pagination'] = $pagination->render();
	
		$this->data['filter_price'] = $filter_price;
		$this->data['filter_korean_name'] = $filter_korean_name;
		$this->data['sort'] = $sort;
		$this->data['order'] = $order;
    $this->data = array_merge($this->data, $this->parameters);
    $this->setBreadcrumbs();
		$this->template = 'catalog/product_list.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
		$this->response->setOutput($this->render());
  }

  private function getForm() {
    	$this->data['text_enabled'] = $this->language->get('text_enabled');
    	$this->data['text_disabled'] = $this->language->get('text_disabled');
    	$this->data['text_none'] = $this->language->get('text_none');
    	$this->data['text_yes'] = $this->language->get('text_yes');
    	$this->data['text_no'] = $this->language->get('text_no');
		$this->data['text_plus'] = $this->language->get('text_plus');
		$this->data['text_minus'] = $this->language->get('text_minus');
		$this->data['text_default'] = $this->language->get('text_default');
		$this->data['text_image_manager'] = $this->language->get('text_image_manager');
		$this->data['text_browse'] = $this->language->get('text_browse');
		$this->data['text_clear'] = $this->language->get('text_clear');
		$this->data['text_option'] = $this->language->get('text_option');
		$this->data['text_option_value'] = $this->language->get('text_option_value');
		$this->data['text_select'] = $this->language->get('text_select');
        $this->data['textSelectAll'] = $this->language->get('text_select');
        $this->data['textUnselectAll'] = $this->language->get('text_select');
		$this->data['text_percent'] = $this->language->get('text_percent');
		$this->data['text_amount'] = $this->language->get('text_amount');

		$this->data['entry_name'] = $this->language->get('entry_name');
		$this->data['entry_meta_description'] = $this->language->get('entry_meta_description');
		$this->data['entry_meta_keyword'] = $this->language->get('entry_meta_keyword');
		$this->data['entry_description'] = $this->language->get('entry_description');
		$this->data['entry_store'] = $this->language->get('entry_store');
		$this->data['entry_keyword'] = $this->language->get('entry_keyword');
    	$this->data['entry_model'] = $this->language->get('entry_model');
		$this->data['entry_sku'] = $this->language->get('entry_sku');
		$this->data['entry_upc'] = $this->language->get('entry_upc');
		$this->data['entry_location'] = $this->language->get('entry_location');
		$this->data['entry_minimum'] = $this->language->get('entry_minimum');
		$this->data['entry_manufacturer'] = $this->language->get('entry_manufacturer');
        $this->data['entry_supplier'] = $this->language->get('entry_supplier');
    	$this->data['entry_shipping'] = $this->language->get('entry_shipping');
    	$this->data['entry_date_available'] = $this->language->get('entry_date_available');
    	$this->data['entry_quantity'] = $this->language->get('entry_quantity');
		$this->data['entry_stock_status'] = $this->language->get('entry_stock_status');
    	$this->data['entry_price'] = $this->language->get('entry_price');
		$this->data['entry_tax_class'] = $this->language->get('entry_tax_class');
		$this->data['entry_points'] = $this->language->get('entry_points');
		$this->data['entry_option_points'] = $this->language->get('entry_option_points');
		$this->data['entry_subtract'] = $this->language->get('entry_subtract');
    	$this->data['entry_weight_class'] = $this->language->get('entry_weight_class');
    	$this->data['entry_weight'] = $this->language->get('entry_weight');
		$this->data['entry_dimension'] = $this->language->get('entry_dimension');
		$this->data['entry_length'] = $this->language->get('entry_length');
    	$this->data['entry_image'] = $this->language->get('entry_image');
    	$this->data['entry_download'] = $this->language->get('entry_download');
    	$this->data['entry_category'] = $this->language->get('entry_category');
		$this->data['entry_related'] = $this->language->get('entry_related');
		$this->data['entry_attribute'] = $this->language->get('entry_attribute');
		$this->data['entry_text'] = $this->language->get('entry_text');
		$this->data['entry_option'] = $this->language->get('entry_option');
		$this->data['entry_option_value'] = $this->language->get('entry_option_value');
		$this->data['entry_required'] = $this->language->get('entry_required');
		$this->data['entry_sort_order'] = $this->language->get('entry_sort_order');
		$this->data['entry_status'] = $this->language->get('entry_status');
		$this->data['entry_customer_group'] = $this->language->get('entry_customer_group');
		$this->data['entry_date_start'] = $this->language->get('entry_date_start');
		$this->data['entry_date_end'] = $this->language->get('entry_date_end');
		$this->data['entry_priority'] = $this->language->get('entry_priority');
		$this->data['entry_tag'] = $this->language->get('entry_tag');
		$this->data['entry_customer_group'] = $this->language->get('entry_customer_group');
		$this->data['entry_reward'] = $this->language->get('entry_reward');
		$this->data['entry_layout'] = $this->language->get('entry_layout');
		$this->data['entry_main_category'] = $this->language->get('entry_main_category');
		$this->data['entry_seo_title'] = $this->language->get('entry_seo_title');
		$this->data['entry_seo_h1'] = $this->language->get('entry_seo_h1');
				
    	$this->data['button_save'] = $this->language->get('button_save');
    	$this->data['button_cancel'] = $this->language->get('button_cancel');
		$this->data['button_add_attribute'] = $this->language->get('button_add_attribute');
		$this->data['button_add_option'] = $this->language->get('button_add_option');
		$this->data['button_add_option_value'] = $this->language->get('button_add_option_value');
		$this->data['button_add_discount'] = $this->language->get('button_add_discount');
		$this->data['button_add_special'] = $this->language->get('button_add_special');
		$this->data['button_add_image'] = $this->language->get('button_add_image');
		$this->data['button_remove'] = $this->language->get('button_remove');
        $this->data['textImageManager'] = $this->language->get('IMAGE_MANAGER');
		
    	$this->data['tab_general'] = $this->language->get('tab_general');
    	$this->data['tab_data'] = $this->language->get('tab_data');
		$this->data['tab_attribute'] = $this->language->get('tab_attribute');
      	$this->data['tab_option'] = $this->language->get('tab_option');
      	$this->data['tab_creat_option'] = $this->language->get('tab_creat_option');
		$this->data['tab_discount'] = $this->language->get('tab_discount');
		$this->data['tab_special'] = $this->language->get('tab_special');
    	$this->data['tab_image'] = $this->language->get('tab_image');		
		$this->data['tab_links'] = $this->language->get('tab_links');
		$this->data['tab_reward'] = $this->language->get('tab_reward');
		$this->data['tab_design'] = $this->language->get('tab_design');
		 
 		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

 		if (isset($this->error['name'])) {
			$this->data['error_name'] = $this->error['name'];
		} else {
			$this->data['error_name'] = array();
		}

 		if (isset($this->error['meta_description'])) {
			$this->data['error_meta_description'] = $this->error['meta_description'];
		} else {
			$this->data['error_meta_description'] = array();
		}		
   
   		if (isset($this->error['description'])) {
			$this->data['error_description'] = $this->error['description'];
		} else {
			$this->data['error_description'] = array();
		}	
		
   		if (isset($this->error['model'])) {
			$this->data['error_model'] = $this->error['model'];
		} else {
			$this->data['error_model'] = '';
		}		
     	
		if (isset($this->error['date_available'])) {
			$this->data['error_date_available'] = $this->error['date_available'];
		} else {
			$this->data['error_date_available'] = '';
		}	

		$url = '';

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . $this->request->get['filter_name'];
		}
		
		if (isset($this->request->get['filter_model'])) {
			$url .= '&filter_model=' . $this->request->get['filter_model'];
		}
		
		if (isset($this->request->get['filter_price'])) {
			$url .= '&filter_price=' . $this->request->get['filter_price'];
		}
		
		if (isset($this->request->get['filter_korean_name'])) {
			$url .= '&filter_korean_name=' . $this->request->get['filter_korean_name'];
		}	
		
		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
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

  		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('catalog/product', 'token=' . $this->session->data['token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);
									
		if (!isset($this->request->get['product_id'])) {
			$this->data['action'] = $this->url->link('catalog/product/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$this->data['action'] = $this->url->link('catalog/product/update', 'token=' . $this->session->data['token'] . '&product_id=' . $this->request->get['product_id'] . $url, 'SSL');
		}
		
		$this->data['cancel'] = $this->url->link('catalog/product', 'token=' . $this->session->data['token'] . $url, 'SSL');

		$this->data['token'] = $this->session->data['token'];

		if (isset($this->request->get['product_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
      		$product_info = $this->model_catalog_product->getProduct($this->request->get['product_id']);
    	}

		$this->load->model('localisation/language');
		
		$this->data['languages'] = $this->model_localisation_language->getLanguages();
		
		if (isset($this->request->post['product_description'])) {
			$this->data['product_description'] = $this->request->post['product_description'];
		} elseif (isset($this->request->get['product_id'])) {
			$this->data['product_description'] = $this->model_catalog_product->getProductDescriptions($this->request->get['product_id']);
		} else {
			$this->data['product_description'] = array();
		}
		
		if (isset($this->request->post['model'])) {
      		$this->data['model'] = $this->request->post['model'];
    	} elseif (!empty($product_info)) {
			$this->data['model'] = $product_info['model'];
		} else {
      		$this->data['model'] = '';
    	}

		if (isset($this->request->post['sku'])) {
      		$this->data['sku'] = $this->request->post['sku'];
    	} elseif (!empty($product_info)) {
			$this->data['sku'] = $product_info['sku'];
		} else {
      		$this->data['sku'] = '';
    	}
		
		if (isset($this->request->post['upc'])) {
      		$this->data['upc'] = $this->request->post['upc'];
    	} elseif (!empty($product_info)) {
			$this->data['upc'] = $product_info['upc'];
		} else {
      		$this->data['upc'] = '';
    	}
				
		if (isset($this->request->post['location'])) {
      		$this->data['location'] = $this->request->post['location'];
    	} elseif (!empty($product_info)) {
			$this->data['location'] = $product_info['location'];
		} else {
      		$this->data['location'] = '';
    	}

		$this->load->model('setting/store');
		
		$this->data['stores'] = $this->model_setting_store->getStores();
		
		if (isset($this->request->post['product_store'])) {
			$this->data['product_store'] = $this->request->post['product_store'];
		} elseif (isset($this->request->get['product_id'])) {
			$this->data['product_store'] = $this->model_catalog_product->getProductStores($this->request->get['product_id']);
		} else {
			$this->data['product_store'] = array(0);
		}	
		
		if (isset($this->request->post['keyword'])) {
			$this->data['keyword'] = $this->request->post['keyword'];
		} elseif (!empty($product_info)) {
			$this->data['keyword'] = $product_info['keyword'];
		} else {
			$this->data['keyword'] = '';
		}
		
		if (isset($this->request->post['product_tag'])) {
			$this->data['product_tag'] = $this->request->post['product_tag'];
		} elseif (isset($this->request->get['product_id'])) {
			$this->data['product_tag'] = $this->model_catalog_product->getProductTags($this->request->get['product_id']);
		} else {
			$this->data['product_tag'] = array();
		}
		
		if (isset($this->request->post['image'])) {
			$this->data['image'] = $this->request->post['image'];
		} elseif (!empty($product_info)) {
			$this->data['image'] = $product_info['image'];
		} else {
			$this->data['image'] = '';
		}
		
		$this->load->model('tool/image');
		
		if (!empty($product_info) && $product_info['image'] && file_exists(DIR_IMAGE . $product_info['image'])) {
			$this->data['thumb'] = $this->model_tool_image->resize($product_info['image'], 100, 100);
		} else {
			$this->data['thumb'] = $this->model_tool_image->resize('no_image.jpg', 100, 100);
		}
	
		$this->load->model('catalog/manufacturer');
		
    	$this->data['manufacturers'] = $this->model_catalog_manufacturer->getManufacturers();
    	if (isset($this->request->post['manufacturer_id'])) {
      		$this->data['manufacturer_id'] = $this->request->post['manufacturer_id'];
		} elseif (!empty($product_info)) {
			$this->data['manufacturer_id'] = $product_info['manufacturer_id'];
		} else {
      		$this->data['manufacturer_id'] = 0;
    	}

        $this->load->model('catalog/supplier');
        $this->data['suppliers'] = $this->model_catalog_supplier->getSuppliers();

        if (isset($this->request->post['supplier_id'])) {
            $this->data['supplier_id'] = $this->request->post['supplier_id'];
        } elseif (!empty($product_info)) {
            $this->data['supplier_id'] = $product_info['supplier_id'];
        } else {
            $this->data['supplier_id'] = 0;
        }

          if (isset($this->request->post['shipping'])) {
      		$this->data['shipping'] = $this->request->post['shipping'];
    	} elseif (!empty($product_info)) {
      		$this->data['shipping'] = $product_info['shipping'];
    	} else {
			$this->data['shipping'] = 1;
		}
		
    	if (isset($this->request->post['price'])) {
      		$this->data['price'] = $this->request->post['price'];
    	} else if (!empty($product_info)) {
			$this->data['price'] = $product_info['price'];
		} else {
      		$this->data['price'] = '';
    	}
		
		$this->load->model('localisation/tax_class');
		
		$this->data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();
    	
		if (isset($this->request->post['tax_class_id'])) {
      		$this->data['tax_class_id'] = $this->request->post['tax_class_id'];
    	} else if (!empty($product_info)) {
			$this->data['tax_class_id'] = $product_info['tax_class_id'];
		} else {
      		$this->data['tax_class_id'] = 0;
    	}
		      	
		if (isset($this->request->post['date_available'])) {
       		$this->data['date_available'] = $this->request->post['date_available'];
		} elseif (!empty($product_info)) {
			$this->data['date_available'] = date('Y-m-d', strtotime($product_info['date_available']));
		} else {
			$this->data['date_available'] = date('Y-m-d', time() - 86400);
		}
											
    	if (isset($this->request->post['quantity'])) {
      		$this->data['quantity'] = $this->request->post['quantity'];
    	} elseif (!empty($product_info)) {
      		$this->data['quantity'] = $product_info['quantity'];
    	} else {
			$this->data['quantity'] = '';
		}
		
		if (isset($this->request->post['minimum'])) {
      		$this->data['minimum'] = $this->request->post['minimum'];
    	} elseif (!empty($product_info)) {
      		$this->data['minimum'] = $product_info['minimum'];
    	} else {
			$this->data['minimum'] = 1;
		}
		
		if (isset($this->request->post['subtract'])) {
      		$this->data['subtract'] = $this->request->post['subtract'];
    	} elseif (!empty($product_info)) {
      		$this->data['subtract'] = $product_info['subtract'];
    	} else {
			$this->data['subtract'] = 0;
		}
		
		if (isset($this->request->post['sort_order'])) {
      		$this->data['sort_order'] = $this->request->post['sort_order'];
    	} elseif (!empty($product_info)) {
      		$this->data['sort_order'] = $product_info['sort_order'];
    	} else {
			$this->data['sort_order'] = 1;
		}

		$this->load->model('localisation/stock_status');
		
		$this->data['stock_statuses'] = $this->model_localisation_stock_status->getStockStatuses();
    	
		if (isset($this->request->post['stock_status_id'])) {
      		$this->data['stock_status_id'] = $this->request->post['stock_status_id'];
    	} else if (!empty($product_info)) {
      		$this->data['stock_status_id'] = $product_info['stock_status_id'];
    	} else {
			$this->data['stock_status_id'] = $this->config->get('config_stock_status_id');
		}
				
    	if (isset($this->request->post['status'])) {
      		$this->data['status'] = $this->request->post['status'];
    	} else if (!empty($product_info)) {
			$this->data['status'] = $product_info['status'];
		} else {
      		$this->data['status'] = 1;
    	}

		if (isset($this->request->post['affiliate_commission'])) {
			$this->data['affiliate_commission'] = $this->request->post['affiliate_commission'];
		} elseif (!empty($product_info)) {
			$this->data['affiliate_commission'] = $product_info['affiliate_commission'];
		} else {
			$this->data['affiliate_commission'] = 0;
		}

    	if (isset($this->request->post['weight'])) {
      		$this->data['weight'] = $this->request->post['weight'];
		} else if (!empty($product_info)) {
			$this->data['weight'] = $product_info['weight'];
    	} else {
      		$this->data['weight'] = '';
    	} 
		
		$this->load->model('localisation/weight_class');
		
		$this->data['weight_classes'] = $this->model_localisation_weight_class->getWeightClasses();
    	
		if (isset($this->request->post['weight_class_id'])) {
      		$this->data['weight_class_id'] = $this->request->post['weight_class_id'];
    	} elseif (!empty($product_info)) {
      		$this->data['weight_class_id'] = $product_info['weight_class_id'];
		} else {
      		$this->data['weight_class_id'] = $this->config->get('config_weight_class_id');
    	}
		
		if (isset($this->request->post['length'])) {
      		$this->data['length'] = $this->request->post['length'];
    	} elseif (!empty($product_info)) {
			$this->data['length'] = $product_info['length'];
		} else {
      		$this->data['length'] = '';
    	}
		
		if (isset($this->request->post['width'])) {
      		$this->data['width'] = $this->request->post['width'];
		} elseif (!empty($product_info)) {	
			$this->data['width'] = $product_info['width'];
    	} else {
      		$this->data['width'] = '';
    	}
		
		if (isset($this->request->post['height'])) {
      		$this->data['height'] = $this->request->post['height'];
		} elseif (!empty($product_info)) {
			$this->data['height'] = $product_info['height'];
    	} else {
      		$this->data['height'] = '';
    	}

		$this->load->model('localisation/length_class');
		
		$this->data['length_classes'] = $this->model_localisation_length_class->getLengthClasses();
    	
		if (isset($this->request->post['length_class_id'])) {
      		$this->data['length_class_id'] = $this->request->post['length_class_id'];
    	} elseif (!empty($product_info)) {
      		$this->data['length_class_id'] = $product_info['length_class_id'];
    	} else {
      		$this->data['length_class_id'] = $this->config->get('config_length_class_id');
		}

		if (isset($this->request->post['product_attribute'])) {
			$this->data['product_attributes'] = $this->request->post['product_attribute'];
		} elseif (isset($this->request->get['product_id'])) {
			$this->data['product_attributes'] = $this->model_catalog_product->getProductAttributes($this->request->get['product_id']);
		} else {
			$this->data['product_attributes'] = array();
		}
		
		if (isset($this->request->post['product_option'])) {
			$product_options = $this->request->post['product_option'];
		} elseif (isset($this->request->get['product_id'])) {
			$product_options = $this->model_catalog_product->getProductOptions($this->request->get['product_id']);			
		} else {
			$product_options = array();
		}			
		
		$this->data['product_options'] = array();
			
		foreach ($product_options as $product_option) {
			if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image') {
				$product_option_value_data = array();
				
				foreach ($product_option['product_option_value'] as $product_option_value) {
					$product_option_value_data[] = array(
						'product_option_value_id' => $product_option_value['product_option_value_id'],
						'option_value_id'         => $product_option_value['option_value_id'],
						'quantity'                => $product_option_value['quantity'],
						'subtract'                => $product_option_value['subtract'],
						'price'                   => $product_option_value['price'],
						'price_prefix'            => $product_option_value['price_prefix'],
						'points'                  => $product_option_value['points'],
						'points_prefix'           => $product_option_value['points_prefix'],						
						'weight'                  => $product_option_value['weight'],
						'weight_prefix'           => $product_option_value['weight_prefix']	
					);						
				}
				
				$this->data['product_options'][] = array(
					'product_option_id'    => $product_option['product_option_id'],
					'option_id'            => $product_option['option_id'],
					'name'                 => $product_option['name'],
					'type'                 => $product_option['type'],
					'product_option_value' => $product_option_value_data,
					'required'             => $product_option['required']
				);				
			} else {
				$this->data['product_options'][] = array(
					'product_option_id' => $product_option['product_option_id'],
					'option_id'         => $product_option['option_id'],
					'name'              => $product_option['name'],
					'type'              => $product_option['type'],
					'option_value'      => $product_option['option_value'],
					'required'          => $product_option['required']
				);				
			}
		}

        $this->data['create_option_block'] = $this->getOptionForm();

        $this->load->model('sale/customer_group');
		
		$this->data['customer_groups'] = $this->model_sale_customer_group->getCustomerGroups();
		
		if (isset($this->request->post['product_discount'])) {
			$this->data['product_discounts'] = $this->request->post['product_discount'];
		} elseif (isset($this->request->get['product_id'])) {
			$this->data['product_discounts'] = $this->model_catalog_product->getProductDiscounts($this->request->get['product_id']);
		} else {
			$this->data['product_discounts'] = array();
		}

		if (isset($this->request->post['product_special'])) {
			$this->data['product_specials'] = $this->request->post['product_special'];
		} elseif (isset($this->request->get['product_id'])) {
			$this->data['product_specials'] = $this->model_catalog_product->getProductSpecials($this->request->get['product_id']);
		} else {
			$this->data['product_specials'] = array();
		}
		
		if (isset($this->request->post['product_image'])) {
			$product_images = $this->request->post['product_image'];
		} elseif (isset($this->request->get['product_id'])) {
			$product_images = $this->model_catalog_product->getProductImages($this->request->get['product_id']);
		} else {
			$product_images = array();
		}
		
		$this->data['product_images'] = array();
		
		foreach ($product_images as $product_image) {
			if ($product_image['image'] && file_exists(DIR_IMAGE . $product_image['image'])) {
				$image = $product_image['image'];
			} else {
				$image = 'no_image.jpg';
			}
			
			$this->data['product_images'][] = array(
				'image'      => $image,
				'thumb'      => $this->model_tool_image->resize($image, 100, 100),
				'sort_order' => $product_image['sort_order'],
			);
		}

		$this->data['no_image'] = $this->model_tool_image->resize('no_image.jpg', 100, 100);

		$this->load->model('catalog/download');
		
		$this->data['downloads'] = $this->model_catalog_download->getDownloads();
		
		if (isset($this->request->post['product_download'])) {
			$this->data['product_download'] = $this->request->post['product_download'];
		} elseif (isset($this->request->get['product_id'])) {
			$this->data['product_download'] = $this->model_catalog_product->getProductDownloads($this->request->get['product_id']);
		} else {
			$this->data['product_download'] = array();
		}		
		
		$this->load->model('catalog/category');
				
		$categories = $this->model_catalog_category->getAllCategories();

		$this->data['categories'] = $this->getAllCategories($categories);
		
		if (isset($this->request->post['main_category_id'])) {
			$this->data['main_category_id'] = $this->request->post['main_category_id'];
		} elseif (isset($product_info)) {
			$this->data['main_category_id'] = $this->model_catalog_product->getProductMainCategoryId($this->request->get['product_id']);
		} else {
			$this->data['main_category_id'] = 0;
		}

		if (isset($this->request->post['product_category'])) {
			$this->data['product_category'] = $this->request->post['product_category'];
		} elseif (isset($this->request->get['product_id'])) {
			$this->data['product_category'] = $this->model_catalog_product->getProductCategories($this->request->get['product_id']);
		} else {
			$this->data['product_category'] = array();
		}		
		
		if (isset($this->request->post['product_related'])) {
			$products = $this->request->post['product_related'];
		} elseif (isset($this->request->get['product_id'])) {		
			$products = $this->model_catalog_product->getProductRelated($this->request->get['product_id']);
		} else {
			$products = array();
		}
	
		$this->data['product_related'] = array();
		
		foreach ($products as $product_id) {
			$related_info = $this->model_catalog_product->getProduct($product_id);
			
			if ($related_info) {
				$this->data['product_related'][] = array(
					'product_id' => $related_info['product_id'],
					'name'       => $related_info['name']
				);
			}
		}

    	if (isset($this->request->post['points'])) {
      		$this->data['points'] = $this->request->post['points'];
    	} else if (!empty($product_info)) {
			$this->data['points'] = $product_info['points'];
		} else {
      		$this->data['points'] = '';
    	}
						
		if (isset($this->request->post['product_reward'])) {
			$this->data['product_reward'] = $this->request->post['product_reward'];
		} elseif (isset($this->request->get['product_id'])) {
			$this->data['product_reward'] = $this->model_catalog_product->getProductRewards($this->request->get['product_id']);
		} else {
			$this->data['product_reward'] = array();
		}
		
		if (isset($this->request->post['product_layout'])) {
			$this->data['product_layout'] = $this->request->post['product_layout'];
		} elseif (isset($this->request->get['product_id'])) {
			$this->data['product_layout'] = $this->model_catalog_product->getProductLayouts($this->request->get['product_id']);
		} else {
			$this->data['product_layout'] = array();
		}

		$this->load->model('design/layout');
		
		$this->data['layouts'] = $this->model_design_layout->getLayouts();
										
		$this->template = 'catalog/product_form.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
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
        $usernames = $this->modelCatalogProduct->getProductUserNames($data);
        
        foreach ($usernames as $username)
        {
            if (!in_array($username['user_id'], $tmpResult))
                $tmpResult[$username['user_id']] = $username['user_name'];
        }
        natcasesort($tmpResult);
        return $tmpResult;
    }

    private function getManufacturers()
    {
        foreach ($this->parameters as $key => $value)
        {
            if (strpos($key, 'filter') === false)
                continue;
            $data[$key] = $value;
        }
        unset($data['filterManufacturerId']);
        $tmpResult = array();
        $products = $this->modelCatalogProduct->getProductManufacturers($data);
        foreach ($products as $product)
        {
            if (!in_array($product['manufacturer_id'], $tmpResult))
                $tmpResult[$product['manufacturer_id']] = $product['manufacturer_name'];
        }
        natcasesort($tmpResult);
        return $tmpResult;
    }

    private function getSuppliers()
    {
        foreach ($this->parameters as $key => $value)
        {
            if (strpos($key, 'filter') === false)
                continue;
            $data[$key] = $value;
        }
        unset($data['filterSupplierId']);
        $tmpResult = array();
        $products = $this->modelCatalogProduct->getProductSuppliers($data);
        $this->log->write(sizeof($products));
        foreach ($products as $product)
        {
            if (!in_array($product['supplier_id'], $tmpResult))
                $tmpResult[$product['supplier_id']] = $product['supplier_name'];
        }
        natcasesort($tmpResult);
        $this->log->write(sizeof($tmpResult));
        return $tmpResult;
    }

  	private function validateForm() { 
    	if (!$this->user->hasPermission('modify', 'catalog/product')) {
      		$this->error['warning'] = $this->language->get('error_permission');
    	}

    	foreach ($this->request->post['product_description'] as $language_id => $value) {
      		if ((utf8_strlen($value['name']) < 1) || (utf8_strlen($value['name']) > 255)) {
        		$this->error['name'][$language_id] = $this->language->get('error_name');
      		}
    	}
		
    	if ((utf8_strlen($this->request->post['model']) < 1) || (utf8_strlen($this->request->post['model']) > 64)) {
      		$this->error['model'] = $this->language->get('error_model');
    	}
		
		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}
					
    	if (!$this->error) {
			return true;
    	} else {
      		return false;
    	}
  	}
	
  	private function validateDelete() {
    	if (!$this->user->hasPermission('modify', 'catalog/product')) {
            $this->session->data['notifications']['warning'] = $this->language->get('error_permission');
    	} elseif (!($this->parameters['confirm'])) {
            $modelSaleOrderItem = $this->load->model('sale/order_item');
            foreach ($this->parameters['selectedItems'] as $productId) {
                $productsCount = $modelSaleOrderItem->getOrderItemsCount(null, "op.product_id = " . (int)$productId);
                if ($productsCount) {
                    $this->session->data['notifications']['confirm']['text'] .= "$productId => $productsCount";
                }
            }
            if (isset($this->session->data['notifications']['confirm'])) {
                $this->session->data['notifications']['confirm']['title'] = $this->language->get('TITLE_DELETION_CONFIRM');
                $this->session->data['notifications']['confirm']['urlYes'] = $this->selfUrl . '&confirm=1';
                $this->session->data['notifications']['confirm']['text'] =
                    $this->language->get('DELETION_CONFIRM') . $this->session->data['notifications']['confirm']['text'];
            }
        }
		if (!$this->session->data['notifications']) {
	  		return true;
		} else {
	  		return false;
		}
  	}
  	
  	private function validateCopy() {
    	if (!$this->user->hasPermission('modify', 'catalog/product')) {
      		$this->error['warning'] = $this->language->get('error_permission');  
    	}
		
		if (!$this->error) {
	  		return true;
		} else {
	  		return false;
		}
  	}
	
	public function option() {
		$output = ''; 
		
		$this->load->model('catalog/option');
		
		$results = $this->model_catalog_option->getOptionValues($this->request->get['option_id']);
		
		foreach ($results as $result) {
			$output .= '<option value="' . $result['option_value_id'] . '"';

			if (isset($this->request->get['option_value_id']) && ($this->request->get['option_value_id'] == $result['option_value_id'])) {
				$output .= ' selected="selected"';
			}

			$output .= '>' . $result['name'] . '</option>';
		}

		$this->response->setOutput($output);
	}
		
	public function autocomplete() {
		$json = array();
		
		if (isset($this->request->get['filter_name']) || isset($this->request->get['filter_model']) || isset($this->request->get['filter_category_id'])) {
			$this->load->model('catalog/product');
			
			if (isset($this->request->get['filter_name'])) {
				$filter_name = $this->request->get['filter_name'];
			} else {
				$filter_name = '';
			}
			
			if (isset($this->request->get['filter_model'])) {
				$filter_model = $this->request->get['filter_model'];
			} else {
				$filter_model = '';
			}
						
			if (isset($this->request->get['filter_category_id'])) {
				$filter_category_id = $this->request->get['filter_category_id'];
			} else {
				$filter_category_id = '';
			}
			
			if (isset($this->request->get['filter_sub_category'])) {
				$filter_sub_category = $this->request->get['filter_sub_category'];
			} else {
				$filter_sub_category = '';
			}
			
			if (isset($this->request->get['limit'])) {
				$limit = $this->request->get['limit'];	
			} else {
				$limit = 20;	
			}			
						
			$data = array(
				'filter_name'         => $filter_name,
				'filter_model'        => $filter_model,
				'filter_category_id'  => $filter_category_id,
				'filter_sub_category' => $filter_sub_category,
				'start'               => 0,
				'limit'               => $limit
			);
			
			$results = $this->model_catalog_product->getProducts($data);
			
			foreach ($results as $result) {
				$option_data = array();
				
				$product_options = $this->model_catalog_product->getProductOptions($result['product_id']);	
				
				foreach ($product_options as $product_option) {
					if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image') {
						$option_value_data = array();
					
						foreach ($product_option['product_option_value'] as $product_option_value) {
							$option_value_data[] = array(
								'product_option_value_id' => $product_option_value['product_option_value_id'],
								'option_value_id'         => $product_option_value['option_value_id'],
								'name'                    => $product_option_value['name'],
								'price'                   => (float)$product_option_value['price'] ? $this->currency->format($product_option_value['price'], $this->config->get('config_currency')) : false,
								'price_prefix'            => $product_option_value['price_prefix']
							);	
						}
					
						$option_data[] = array(
							'product_option_id' => $product_option['product_option_id'],
							'option_id'         => $product_option['option_id'],
							'name'              => $product_option['name'],
							'type'              => $product_option['type'],
							'option_value'      => $option_value_data,
							'required'          => $product_option['required']
						);	
					} else {
						$option_data[] = array(
							'product_option_id' => $product_option['product_option_id'],
							'option_id'         => $product_option['option_id'],
							'name'              => $product_option['name'],
							'type'              => $product_option['type'],
							'option_value'      => $product_option['option_value'],
							'required'          => $product_option['required']
						);				
					}
				}
				
				$json[] = array(
					'product_id' => $result['product_id'],
					'name'       => html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'),	
					'model'      => $result['model'],
					'option'     => $option_data,
					'price'      => $result['price']
				);	
			}
		}

		$this->response->setOutput(json_encode($json));
	}

	private function getAllCategories($categories, $parent_id = 0, $parent_name = '') {
		$output = array();

		if (array_key_exists($parent_id, $categories)) {
			if ($parent_name != '') {
				$parent_name .= $this->language->get('text_separator');
			}

			foreach ($categories[$parent_id] as $category) {
				$output[$category['category_id']] = array(
					'category_id' => $category['category_id'],
					'name'        => $parent_name . $category['name']
				);

				$output += $this->getAllCategories($categories, $category['category_id'], $parent_name . $category['name']);
			}
		}

		return $output;
	}

    private function validateChange() {
        if (!$this->user->hasPermission('modify', 'catalog/product')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }

    public function enable() {
        $this->changeStatusProducts(1);
        Audit::getInstance($this->registry)->addAdminEntry(AUDIT_ADMIN_PRODUCT_ENABLE, $_REQUEST);
    }

    public function disable() {
        $this->changeStatusProducts(0);
        Audit::getInstance($this->registry)->addAdminEntry(AUDIT_ADMIN_PRODUCT_DISABLE, $_REQUEST);
    }

    private function changeStatusProducts($status) {
        if (isset($this->request->post['selected']) && $this->validateChange()) {
            $this->model_catalog_product->changeStatusProducts($this->request->post['selected'], $status);

            $this->session->data['success'] = $this->language->get('text_success');

            $url = '';

            if (isset($this->request->get['filter_name'])) {
                $url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
            }

            if (isset($this->request->get['filter_model'])) {
                $url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
            }

            if (isset($this->request->get['filter_price'])) {
                $url .= '&filter_price=' . $this->request->get['filter_price'];
            }

            if (isset($this->request->get['filter_korean_name'])) {
                $url .= '&filter_korean_name=' . $this->request->get['filter_korean_name'];
            }

            if (isset($this->request->get['filter_status'])) {
                $url .= '&filter_status=' . $this->request->get['filter_status'];
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

            $this->redirect($this->url->link('catalog/product', 'token=' . $this->session->data['token'] . $url, 'SSL'));
        }
        $this->getList();
    }

    protected function setBreadcrumbs()
    {
        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('catalog/product', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );
    }

    private function addMemoOption() {
        if (empty($this->request->post['product_option'])) {
            $this->request->post['product_option'] = array();
        }
        foreach ($this->request->post['product_option'] as $productOption) {
            if ($productOption['option_id'] == OPTION_MEMO_OPTION_ID) {
                return;
            }
        }
        $this->request->post['product_option'][] = array(
            'name' => 'Memo',
            'option_id' => OPTION_MEMO_OPTION_ID,
            'option_value' => null,
            'product_option_id' => null,
            'required' => false,
            'type' => 'textarea'
        );
    }

    private function getOptionForm() {
    	$this->load->language('catalog/option');

    	$this->data['text_create_option'] = $this->language->get('text_create_option');
    	$this->data['text_choose'] = $this->language->get('text_choose');
    	$this->data['text_option_select'] = $this->language->get('text_option_select');
    	$this->data['text_radio'] = $this->language->get('text_radio');
    	$this->data['text_checkbox'] = $this->language->get('text_checkbox');
    	$this->data['text_image'] = $this->language->get('text_image');
    	$this->data['text_input'] = $this->language->get('text_input');
    	$this->data['text_text'] = $this->language->get('text_text');
    	$this->data['text_textarea'] = $this->language->get('text_textarea');
    	$this->data['text_file'] = $this->language->get('text_file');
    	$this->data['text_date'] = $this->language->get('text_date');
    	$this->data['text_datetime'] = $this->language->get('text_datetime');
    	$this->data['text_time'] = $this->language->get('text_time');
    	$this->data['text_image_manager'] = $this->language->get('text_image_manager');
    	$this->data['text_browse'] = $this->language->get('text_browse');
    	$this->data['text_clear'] = $this->language->get('text_clear');

    	$this->data['entry_option_name'] = $this->language->get('entry_option_name');
    	$this->data['entry_type'] = $this->language->get('entry_type');
    	$this->data['entry_value'] = $this->language->get('entry_value');
    	$this->data['entry_image'] = $this->language->get('entry_image');
    	$this->data['entry_sort_order'] = $this->language->get('entry_sort_order');

    	$this->data['button_add_option_value'] = $this->language->get('button_add_option_value');
    	$this->data['button_remove'] = $this->language->get('button_remove');

    	$this->data['token'] = $this->session->data['token'];

    	if(isset($this->error['warning'])) {
            $this->data['error_warning'] = $this->error['warning'];
        } else {
            $this->data['error_warning'] = '';
        }

    	if(isset($this->session->data['success'])) {
            $this->data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $this->data['success'] = '';
        }

    	if(isset($this->error['name'])) {
            $this->data['error_name'] = $this->error['name'];
        } else {
            $this->data['error_name'] = array();
        }

    	if(isset($this->error['option_value'])) {
            $this->data['error_option_value'] = $this->error['option_value'];
        } else {
            $this->data['error_option_value'] = array();
        }

    	$this->load->model('localisation/language');

    	$this->data['languages'] = $this->model_localisation_language->getLanguages();

    	if(isset($this->request->post['option_description']) && !empty($this->error)) {
            $this->data['option_description'] = $this->request->post['option_description'];
        } else {
            $this->data['option_description'] = array();
        }

    	if(isset($this->request->post['type']) && !empty($this->error)) {
            $this->data['type'] = $this->request->post['type'];
        } else {
            $this->data['type'] = '';
        }

    	if(isset($this->request->post['sort_order']) && !empty($this->error)) {
            $this->data['sort_order'] = $this->request->post['sort_order'];
        } else {
            $this->data['sort_order'] = '';
        }

    	if(isset($this->request->post['option_value'])  && !empty($this->error)) {
            $option_values = $this->request->post['option_value'];
        } else {
            $option_values = array();
        }

    	$this->load->model('tool/image');

    	$this->data['option_values'] = array();

    	foreach($option_values as $option_value) {
            if($option_value['image'] && file_exists(DIR_IMAGE . $option_value['image'])) {
                $image = $option_value['image'];
            } else {
                $image = 'no_image.jpg';
            }

            $this->data['option_values'][] = array(
                'option_value_id'          => $option_value['option_value_id'],
                'option_value_description' => $option_value['option_value_description'],
                'image'                    => $image,
                'thumb'                    => $this->model_tool_image->resize($image, 100, 100),
                'sort_order'               => $option_value['sort_order']
            );
	    }

    	$this->error = array();

    	$this->data['no_image'] = $this->model_tool_image->resize('no_image.jpg', 100, 100);

    	$this->template = 'catalog/product_option_form.tpl';

    	return $this->render();
    }

    public function createOption() {
    	$this->load->model('catalog/option');
    	$this->load->language('catalog/option');

    	if(($this->request->server['REQUEST_METHOD'] == 'POST') && !empty($this->request->post) && $this->validateOptionForm()) {
            $this->model_catalog_option->addOption($this->request->post);

            $this->session->data['success'] = $this->language->get('text_create_success');
        }

    	$json['data'] = $this->getOptionForm();

    	$this->response->setOutput(json_encode($json));
    }

    private function validateOptionForm() {
    	if(!$this->user->hasPermission('modify', 'catalog/option')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

    	foreach($this->request->post['option_description'] as $language_id => $value) {
            if((utf8_strlen($value['name']) < 1) || (utf8_strlen($value['name']) > 128)) {
                $this->error['name'][$language_id] = $this->language->get('error_name');
            }
        }

    	if((
			$this->request->post['type'] == 'select' ||
			$this->request->post['type'] == 'radio' ||
			$this->request->post['type'] == 'checkbox'
    		) &&
		    !isset($this->request->post['option_value'])
    	) {
            $this->error['warning'] = $this->language->get('error_type');
        }

        if(isset($this->request->post['option_value'])) {
            foreach($this->request->post['option_value'] as $option_value_id => $option_value) {
                foreach($option_value['option_value_description'] as $language_id => $option_value_description) {
                    if((utf8_strlen($option_value_description['name']) < 1) || (utf8_strlen($option_value_description['name']) > 128)) {
                        $this->error['option_value'][$option_value_id][$language_id] = $this->language->get('error_option_value');
                    }
                }
            }
        }

	    if($this->error && !isset($this->error['warning'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

	    if(!$this->error) {
            return true;
        } else {
            return false;
        }
    }
}
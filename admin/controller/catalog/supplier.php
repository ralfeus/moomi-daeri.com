<?php
use model\catalog\SupplierDAO;
use model\catalog\SupplierGroupDAO;

class ControllerCatalogSupplier extends Controller { 
	private $error = array();

    public function __construct($registry) {
        parent::__construct($registry);
        $this->load->language('catalog/supplier');
        $this->document->setTitle($this->language->get('heading_title'));
    }
  
  	public function index() {
    	$this->getList();
  	}

    protected function initParameters() {
        global $_REQUEST;
        $this->parameters['internalModel'] = !empty($_REQUEST['internalModel']) ? $_REQUEST['internalModel'] : '';
        $this->parameters['name'] = !empty($_REQUEST['name']) ? $_REQUEST['name'] : '';
        $this->parameters['order'] = !empty($_REQUEST['order']) ? $_REQUEST['order'] : 'ASC';
        $this->parameters['page'] = !empty($_REQUEST['page']) ? $_REQUEST['page'] : '1';
        $this->parameters['selected'] = !empty($_REQUEST['selected']) ? $_REQUEST['selected'] : array();
        $this->parameters['shippingCost'] = !empty($_REQUEST['shippingCost']) ? $_REQUEST['shippingCost'] : 0;
        $this->parameters['sort'] = !empty($_REQUEST['sort']) ? $_REQUEST['sort'] : 'name';
        $this->parameters['supplierGroupId'] = !empty($_REQUEST['supplierGroupId']) ? $_REQUEST['supplierGroupId'] : null;
        $this->parameters['supplierId'] = !empty($_REQUEST['supplierId']) ? $_REQUEST['supplierId'] : null;
    }
  
    public function insert() {
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateInsert() && $this->validateForm()) {
            SupplierDAO::getInstance()->addSupplier($this->parameters);
            //print_r($this->error);exit();

              $this->session->data['success'] = $this->language->get('text_success');
			
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
              $this->redirect($this->url->link('catalog/supplier', 'token=' . $this->session->data['token'] . $url, 'SSL'));
          }
    
    	$this->getForm();
  	} 
   
  	public function update() {
    	if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			SupplierDAO::getInstance()->editSupplier($this->parameters['supplierId'], $this->parameters);

			$this->session->data['success'] = $this->language->get('text_success');

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
			
			$this->redirect($this->url->link('catalog/supplier', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}
    
    	$this->getForm();
  	}   

  	public function delete() {
    	if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $supplierId) {
				SupplierDAO::getInstance()->deleteSupplier($supplierId);
			}

			$this->session->data['success'] = $this->language->get('text_success');
			
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
			
			$this->redirect($this->url->link('catalog/supplier', 'token=' . $this->session->data['token'] . $url, 'SSL'));
    	}
	
    	$this->getList();
  	}  
    
  	private function getList() {
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

  		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('catalog/supplier', 'token=' . $this->session->data['token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);
							
		$this->data['insert'] = $this->url->link('catalog/supplier/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['delete'] = $this->url->link('catalog/supplier/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');	

		$this->data['suppliers'] = array();

		$data = array(
			'sort'  => $this->parameters['sort'],
			'order' => $this->parameters['order'],
			'start' => ($this->parameters['page'] - 1) * $this->config->get('config_admin_limit'),
			'limit' => $this->config->get('config_admin_limit')
		);
		
		$supplier_total = SupplierDAO::getInstance()->getTotalSuppliers();
		$suppliers = SupplierDAO::getInstance()->getSuppliers($data);
 
    	foreach ($suppliers as $supplier) {
			$action = array();
			
			$action[] = array(
				'text' => $this->language->get('text_edit'),
				'href' => $this->url->link('catalog/supplier/update', 'token=' . $this->session->data['token'] . '&supplierId=' . $supplier->getId() . $url, 'SSL')
			);
						
			$this->data['suppliers'][] = array(
				'supplierId' => $supplier->getId(),
                'supplier_group_id' => $supplier->getGroupId(),
				'name'            => $supplier->getName(),
                'internal_model'    => $supplier->getInternalModel(),
				'selected'        => isset($this->request->post['selected']) && in_array($supplier->getId(), $this->request->post['selected']),
				'action'          => $action
			);
		}	
	
		$this->data['heading_title'] = $this->language->get('heading_title');
		
		$this->data['text_no_results'] = $this->language->get('text_no_results');

        $this->data['column_name'] = $this->language->get('field_name');
        $this->data['column_supplier_group'] = $this->language->get('field_supplier_group');
        $this->data['column_internal_model'] = $this->language->get('field_internal_model');
		$this->data['column_action'] = $this->language->get('field_action');
		
		$this->data['button_insert'] = $this->language->get('button_insert');
		$this->data['button_delete'] = $this->language->get('button_delete');
 
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

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
		
		$this->data['sort_name'] = $this->url->link('catalog/supplier', 'token=' . $this->session->data['token'] . '&sort=name' . $url, 'SSL');

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}
												
		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $supplier_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('catalog/supplier', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');
			
		$this->data['pagination'] = $pagination->render();

		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

		$this->template = 'catalog/supplier_list.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
	}
  
  	private function getForm() {
        $this->data['text_amount'] = $this->language->get('text_amount');
    	$this->data['text_enabled'] = $this->language->get('text_enabled');
    	$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_default'] = $this->language->get('text_default');
    	$this->data['text_image_manager'] = $this->language->get('text_image_manager');
		$this->data['text_browse'] = $this->language->get('text_browse');
		$this->data['text_clear'] = $this->language->get('text_clear');
        $this->data['text_none'] = $this->language->get('text_none');
		$this->data['text_percent'] = $this->language->get('text_percent');
        $this->data['textShippingCost'] = $this->language->get('textShippingCost');

		$this->data['entry_name'] = $this->language->get('field_name') . ':';
        $this->data['entry_supplier_group'] = $this->language->get('field_supplier_group') . ':';
        $this->data['entry_internal_model'] = $this->language->get('field_internal_model') . ':';

    	$this->data['button_save'] = $this->language->get('button_save');
    	$this->data['button_cancel'] = $this->language->get('button_cancel');
		
		$this->data['tab_general'] = $this->language->get('tab_general');
			  
 		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

 		foreach ($this->error as $key => $value) {
            $this->data["error_$key"] = $value;
        }
		    
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
		
  		$this->data['breadcrumbs'] = array();
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('catalog/supplier', 'token=' . $this->session->data['token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);
							
		if (!isset($this->request->get['supplierId'])) {
			$this->data['action'] = $this->url->link('catalog/supplier/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$this->data['action'] = $this->url->link('catalog/supplier/update', 'token=' . $this->session->data['token'] . '&supplierId=' . $this->request->get['supplierId'] . $url, 'SSL');
		}
		
		$this->data['cancel'] = $this->url->link('catalog/supplier', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['token'] = $this->session->data['token'];
		
    	if (isset($this->request->get['supplierId']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
      		$supplier = SupplierDAO::getInstance()->getSupplier($this->request->get['supplierId']);
    	} else {
            $supplier = null;
        }

		$this->load->model('localisation/language');
		
		$this->data['languages'] = $this->model_localisation_language->getLanguages();
        $this->data['supplier_groups'] = SupplierGroupDAO::getInstance()->getSupplierGroups();
        $this->data = array_merge($this->data, $this->parameters);

    	if (empty($this->data['name']) && !is_null($supplier)) {
            $this->data['name'] = $supplier->getName();
        }
        if (empty($this->data['supplierGroupId']) && !is_null($supplier)) {
              $this->data['supplierGroupId'] = $supplier->getGroupId();
        }
        if (empty($this->data['internalModel']) && !is_null($supplier)) {
            $this->data['internalModel'] = $supplier->getInternalModel();
        }
        if (empty($this->data['shippingCost']) && !is_null($supplier)) {
            $this->data['shippingCost'] = $supplier->getShippingCost();
        }


        $this->template = 'catalog/supplierForm.tpl.php';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
	}

    /**
     * @return bool
     */
  	private function validateForm() {
        if (!$this->user->hasPermission('modify', 'catalog/supplier')) {
      		$this->error['warning'] = $this->language->get('error_permission');
    	}

    	if ((utf8_strlen($this->parameters['name']) < 3) || (utf8_strlen($this->parameters['name']) > 64)) {
      		$this->error['name'] = $this->language->get('error_name');
    	}
        if (!is_numeric($this->parameters['shippingCost'])) {
            $this->error['shippingCost'] = $this->language->get('error_shippingCost');
        }
		
		return empty($this->error);
  	}    

    private function validateInsert() {
        $supplier_info = SupplierDAO::getInstance()->getSupplierByName($this->parameters['name']);
        if ($supplier_info) {
            $this->error['warning'] = $this->language->get('error_exists');
        }

        return !$this->error;
    }

  	private function validateDelete() {
    	if (!$this->user->hasPermission('modify', 'catalog/supplier')) {
			$this->error['warning'] = $this->language->get('error_permission');
    	}	
		
		$this->load->model('catalog/product');

		foreach ($this->parameters['selected'] as $supplierId) {
  			$product_total = $this->model_catalog_product->getTotalProductsBySupplierId($supplierId);
    
			if ($product_total) {
	  			$this->error['warning'] = sprintf($this->language->get('error_product'), $product_total);	
			}	
	  	} 
		
		if (!$this->error) {
	  		return true;
		} else {
	  		return false;
		}  
  	}
}
<?php
use model\catalog\SupplierDAO;
use model\catalog\SupplierGroupDAO;

class ControllerCatalogSupplierGroup extends Controller {
	private $error = array();

    public function __construct($registry) {
        parent::__construct($registry);
        $this->load->language('catalog/supplier_group');
        $this->document->setTitle($this->language->get('heading_title'));
    }

  	public function index() {
    	$this->getList();
  	}
  
    public function insert() {
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            SupplierGroupDAO::getInstance()->addSupplierGroup($this->request->post);
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
              $this->redirect($this->url->link('catalog/supplier_group', 'token=' . $this->session->data['token'] . $url, 'SSL'));
          }
    
    	$this->getForm();
  	} 
   
  	public function update() {
    	if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			SupplierGroupDAO::getInstance()->editSupplierGroup($this->request->get['supplier_group_id'], $this->request->post);

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
			
			$this->redirect($this->url->link('catalog/supplier_group', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}
    
    	$this->getForm();
  	}   

  	public function delete() {
    	if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $supplier_group_id) {
				SupplierGroupDAO::getInstance()->deleteSupplierGroup($supplier_group_id);
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
			
			$this->redirect($this->url->link('catalog/supplier_group', 'token=' . $this->session->data['token'] . $url, 'SSL'));
    	}
	
    	$this->getList();
  	}  
    
  	private function getList() {
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'name';
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
			'href'      => $this->url->link('catalog/supplier_group', 'token=' . $this->session->data['token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);
							
		$this->data['insert'] = $this->url->link('catalog/supplier_group/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['delete'] = $this->url->link('catalog/supplier_group/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');	

		$this->data['supplier_groups'] = array();

		$data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_admin_limit'),
			'limit' => $this->config->get('config_admin_limit')
		);
		
		$supplier_group_total = SupplierGroupDAO::getInstance()->getTotalSupplierGroups();
		$results = SupplierGroupDAO::getInstance()->getSupplierGroups($data);
 
    	foreach ($results as $result) {
			$action = array();
			
			$action[] = array(
				'text' => $this->language->get('text_edit'),
				'href' => $this->url->link('catalog/supplier_group/update', 'token=' . $this->session->data['token'] . '&supplier_group_id=' . $result['supplier_group_id'] . $url, 'SSL')
			);
						
			$this->data['supplier_groups'][] = array(
				'supplier_group_id' => $result['supplier_group_id'],
				'name'            => $result['name'],
				'selected'        => isset($this->request->post['selected']) && in_array($result['supplier_group_id'], $this->request->post['selected']),
				'action'          => $action
			);
		}	
	
		$this->data['heading_title'] = $this->language->get('heading_title');
		
		$this->data['text_no_results'] = $this->language->get('text_no_results');

        $this->data['column_name'] = $this->language->get('field_name');
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
		
		$this->data['sort_name'] = $this->url->link('catalog/supplier_group', 'token=' . $this->session->data['token'] . '&sort=name' . $url, 'SSL');

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}
												
		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $supplier_group_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('catalog/supplier_group', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');
			
		$this->data['pagination'] = $pagination->render();

		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

		$this->template = 'catalog/supplier_group_list.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->getResponse()->setOutput($this->render());
	}
  
  	private function getForm() {
        $this->data['heading_title'] = $this->language->get('heading_title');

        $this->data['text_amount'] = $this->language->get('text_amount');
    	$this->data['text_enabled'] = $this->language->get('text_enabled');
    	$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_default'] = $this->language->get('text_default');
    	$this->data['text_image_manager'] = $this->language->get('text_image_manager');
		$this->data['text_browse'] = $this->language->get('text_browse');
		$this->data['text_clear'] = $this->language->get('text_clear');
        $this->data['text_none'] = $this->language->get('text_none');
		$this->data['text_percent'] = $this->language->get('text_percent');

		$this->data['entry_name'] = $this->language->get('field_name') . ':';

    	$this->data['button_save'] = $this->language->get('button_save');
    	$this->data['button_cancel'] = $this->language->get('button_cancel');
		
		$this->data['tab_general'] = $this->language->get('tab_general');
			  
 		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

 		if (isset($this->error['name'])) {
			$this->data['error_name'] = $this->error['name'];
		} else {
			$this->data['error_name'] = '';
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
			'href'      => $this->url->link('catalog/supplier_group', 'token=' . $this->session->data['token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);
							
		if (!isset($this->request->get['supplier_group_id'])) {
			$this->data['action'] = $this->url->link('catalog/supplier_group/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$this->data['action'] = $this->url->link('catalog/supplier_group/update', 'token=' . $this->session->data['token'] . '&supplier_group_id=' . $this->request->get['supplier_group_id'] . $url, 'SSL');
		}
		
		$this->data['cancel'] = $this->url->link('catalog/supplier_group', 'token=' . $this->session->data['token'] . $url, 'SSL');

		$this->data['token'] = $this->session->data['token'];
		
    	if (isset($this->request->get['supplier_group_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
      		$supplier_group_info = SupplierGroupDAO::getInstance()->getSupplierGroup($this->request->get['supplier_group_id']);
    	}

		$this->load->model('localisation/language');
		
		$this->data['languages'] = $this->model_localisation_language->getLanguages();

    	if (isset($this->request->post['name'])) {
      		$this->data['name'] = $this->request->post['name'];
    	} elseif (!empty($supplier_group_info)) {
			$this->data['name'] = $supplier_group_info['name'];
		} else {	
      		$this->data['name'] = '';
    	}

		$this->template = 'catalog/supplier_group_form.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->getResponse()->setOutput($this->render());
	}  
	 
  	private function validateForm() {
        if (!$this->user->hasPermission('modify', 'catalog/supplier_group')) {
      		$this->error['warning'] = $this->language->get('error_permission');
    	}

        $supplier_info = SupplierGroupDAO::getInstance()->getSupplierGroupByName($this->request->post['name']);
        if ($supplier_info)
        {
            $this->error['warning'] = $this->language->get('error_exists');
        }

    	if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
      		$this->error['name'] = $this->language->get('error_name');
    	}
		
		if (!$this->error) {
	  		return true;
		} else {
	  		return false;
		}
  	}    

  	private function validateDelete() {
    	if (!$this->user->hasPermission('modify', 'catalog/supplier_group')) {
			$this->error['warning'] = $this->language->get('error_permission');
    	}	
		
		foreach ($this->request->post['selected'] as $supplier_group_id) {
  			$supplier_total = SupplierDAO::getInstance()->getTotalSuppliersBySupplierGroupId($supplier_group_id);

			if ($supplier_total) {
	  			$this->error['warning'] = sprintf($this->language->get('error_supplier'), $supplier_total);
			}
	  	}
		
		if (!$this->error) {
	  		return true;
		} else {
	  		return false;
		}  
  	}
}
<?php
use system\engine\Controller;

class ControllerCatalogOption extends \system\engine\Controller {
	private $error = array();  
 
	public function index() {
		$this->load->language('catalog/option');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('catalog/option');
		
		$this->getList();
	}

	public function insert() {
		$this->load->language('catalog/option');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('catalog/option');
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_catalog_option->addOption($this->request->post);
			
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
			
			$this->redirect($this->url->link('catalog/option', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getForm();
	}

	public function update() {
		$this->load->language('catalog/option');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('catalog/option');
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_catalog_option->editOption($this->request->get['option_id'], $this->request->post);
			
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
			
			$this->redirect($this->url->link('catalog/option', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language('catalog/option');

		$this->document->setTitle($this->language->get('heading_title'));
 		
		$this->load->model('catalog/option');
		
		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $option_id) {
				$this->model_catalog_option->deleteOption($option_id);
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
			
			$this->redirect($this->url->link('catalog/option', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getList();
	}

	private function getList() {
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'od.name';
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
			'href'      => $this->url->link('catalog/option', 'token=' . $this->session->data['token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);
		
		$this->data['insert'] = $this->url->link('catalog/option/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['delete'] = $this->url->link('catalog/option/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');
		 
		$this->data['options'] = array();
		
		$data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_admin_limit'),
			'limit' => $this->config->get('config_admin_limit')
		);
		
		$option_total = $this->model_catalog_option->getTotalOptions();
		
		$results = $this->model_catalog_option->getOptions($data);
		
		foreach ($results as $result) {
			$action = array();
			
			$action[] = array(
				'text' => $this->language->get('text_edit'),
				'href' => $this->url->link('catalog/option/update', 'token=' . $this->session->data['token'] . '&option_id=' . $result['option_id'] . $url, 'SSL'),
				'text_value' => $this->language->get('text_edit_value'),
				'href_value' => $this->url->link('catalog/option/list_value', 'token=' . $this->session->data['token'] . '&option_id=' . $result['option_id'] . $url, 'SSL')
			);

			$this->data['options'][] = array(
				'option_id'  => $result['option_id'],
				'name'       => $result['name'],
				'sort_order' => $result['sort_order'],
				'selected'   => isset($this->request->post['selected']) && in_array($result['option_id'], $this->request->post['selected']),
				'total_values'=> count($this->model_catalog_option->getOptionValuesTotal($result['option_id'])),
				'action'     => $action
			);
		}

		$this->data['heading_title'] = $this->language->get('heading_title');
		
		$this->data['text_no_results'] = $this->language->get('text_no_results');
		
		$this->data['column_name'] = $this->language->get('column_name');
		$this->data['column_sort_order'] = $this->language->get('column_sort_order');
		$this->data['column_action'] = $this->language->get('column_action');	
		$this->data['column_total'] = $this->language->get('column_total');	

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
		
		$this->data['sort_name'] = $this->url->link('catalog/option', 'token=' . $this->session->data['token'] . '&sort=od.name' . $url, 'SSL');
		$this->data['sort_sort_order'] = $this->url->link('catalog/option', 'token=' . $this->session->data['token'] . '&sort=o.sort_order' . $url, 'SSL');
		
		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}
												
		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $option_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('catalog/option', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();
		
		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

		$this->template = 'catalog/option_list.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->getResponse()->setOutput($this->render());
	}

	private function getForm() {
		$this->data['heading_title'] = $this->language->get('heading_title');
		
		$this->data['text_choose'] = $this->language->get('text_choose');
		$this->data['text_select'] = $this->language->get('text_select');
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
		
		$this->data['entry_name'] = $this->language->get('entry_name');
		$this->data['entry_type'] = $this->language->get('entry_type');
		$this->data['entry_value'] = $this->language->get('entry_value');
		$this->data['entry_image'] = $this->language->get('entry_image');
		$this->data['entry_sort_order'] = $this->language->get('entry_sort_order');
		$this->data['entry_total'] = $this->language->get('entry_total');	

		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_cancel'] = $this->language->get('button_cancel');
		$this->data['button_add_option_value'] = $this->language->get('button_add_option_value');
		$this->data['button_remove'] = $this->language->get('button_remove');

		$this->data['tab_general'] = $this->language->get('tab_general');

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
				
 		if (isset($this->error['option_value'])) {
			$this->data['error_option_value'] = $this->error['option_value'];
		} else {
			$this->data['error_option_value'] = array();
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
		
		if (!isset($this->request->get['option_id'])) {
			$this->data['action'] = $this->url->link('catalog/option/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else { 
			$this->data['action'] = $this->url->link('catalog/option/update', 'token=' . $this->session->data['token'] . '&option_id=' . $this->request->get['option_id'] . $url, 'SSL');
		}

		$this->data['cancel'] = $this->url->link('catalog/option', 'token=' . $this->session->data['token'] . $url, 'SSL');

		$this->data['token'] = $this->session->data['token'];

		if (isset($this->request->get['option_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
      		$option_info = $this->model_catalog_option->getOption($this->request->get['option_id']);
    	}
		
		$this->load->model('localisation/language');
		
		$this->data['languages'] = $this->model_localisation_language->getLanguages();
		
		if (isset($this->request->post['option_description'])) {
			$this->data['option_description'] = $this->request->post['option_description'];
		} elseif (isset($this->request->get['option_id'])) {
			$this->data['option_description'] = $this->model_catalog_option->getOptionDescriptions($this->request->get['option_id']);
		} else {
			$this->data['option_description'] = array();
		}	

		if (isset($this->request->post['type'])) {
			$this->data['type'] = $this->request->post['type'];
		} elseif (!empty($option_info)) {
			$this->data['type'] = $option_info['type'];
		} else {
			$this->data['type'] = '';
		}
		
		if (isset($this->request->post['sort_order'])) {
			$this->data['sort_order'] = $this->request->post['sort_order'];
		} elseif (!empty($option_info)) {
			$this->data['sort_order'] = $option_info['sort_order'];
		} else {
			$this->data['sort_order'] = '';
		}
		

		$this->template = 'catalog/option_form.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
		

		$this->getResponse()->setOutput($this->render());
	}

	public function list_value() {
		$this->load->language('catalog/option');

		$this->document->setTitle($this->language->get('heading_value_title'));
		
		$this->load->model('catalog/option');
		
		$this->getOptionValues();
	}

	public function edit_value() {
		$this->load->language('catalog/option');

		$this->document->setTitle($this->language->get('heading_value_title'));
		$this->load->model('localisation/language');
		$this->data['languages'] = $this->model_localisation_language->getLanguages();
		
		$this->data['entry_value'] = $this->language->get('entry_value');
		$this->data['entry_sort_order'] = $this->language->get('entry_sort_order');

		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_cancel'] = $this->language->get('button_cancel');

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
				
 		if (isset($this->error['option_value'])) {
			$this->data['error_option_value'] = $this->error['option_value'];
		} else {
			$this->data['error_option_value'] = array();
		}	

		$url = '';
						
		if (isset($this->request->get['option_id'])) {
			$url .= '&option_id=' . $this->request->get['option_id'];
		}

		if (!isset($this->request->get['option_value_id'])) {
			$this->data['action'] = $this->url->link('catalog/option/update_value', 'token=' . $this->session->data['token'] . '&option_value_id=0' . $url, 'SSL');
		} else { 
			$this->data['action'] = $this->url->link('catalog/option/update_value', 'token=' . $this->session->data['token'] . '&option_value_id=' . $this->request->get['option_value_id'] . $url, 'SSL');
		}


		$this->data['cancel'] = $this->url->link('catalog/option/list_value', 'token=' . $this->session->data['token'] . $url, 'SSL');

		if (isset($this->request->get['option_id'])) {
			$option_id = $this->request->get['option_id'];
		} else {
			$option_id = null;
		}

		if (isset($this->request->get['option_value_id'])) {
			$option_value_id = $this->request->get['option_value_id'];
		} else {
			$option_value_id = null;
		}

		$this->load->model('catalog/option');
		$option = $this->model_catalog_option->getOption($option_id);
		$this->data['option_name'] = $option['name'];
		$this->data['option_type'] = $option['type'];
		
		$this->data['languages'] = $this->model_localisation_language->getLanguages();

 		$this->data['option_value'] = array();
 			$data = array(
				'option_id' => $option_id,
				'option_value_id' => $option_value_id,
			);

			$this->data['option_value'] = $this->model_catalog_option->getOptionValueDescription($data);

		$this->template = 'catalog/optionValueEdit.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
		

		$this->getResponse()->setOutput($this->render());
 	}


	private function validateValueForm() {
		if (!$this->user->hasPermission('modify', 'catalog/option')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		if (($this->request->post['option_type'] == 'select' || $this->request->post['option_type'] == 'radio' || $this->request->post['option_type'] == 'checkbox') && !isset($this->request->post['option_value'])) {
			$this->error['warning'] = $this->language->get('error_type');
		}

		if (isset($this->request->post['option_value'])) {
				foreach ($this->request->post['option_value']['option_value_description'] as $language_id => $option_value_description) {
					if ((utf8_strlen($option_value_description['name']) <= 0) || (utf8_strlen($option_value_description['name']) > 128)) {
						$this->error['option_value'][$language_id] = $this->language->get('error_option_value'); 
					}					
				}
		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}

	public function update_value() {
		$this->load->language('catalog/option');

		$this->document->setTitle($this->language->get('heading_value_title'));
		
		$this->load->model('catalog/option');
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateValueForm()) {

			$this->model_catalog_option->editOptionValue($this->request->get['option_id'], $this->request->post);
			
			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';
			
			if (isset($this->request->get['option_id'])) {
				$url .= '&option_id=' . $this->request->get['option_id'];
			}

			if (isset($this->request->get['option_value_id']) && $this->request->get['option_value_id'] <> 0) {
				$url .= '&option_value_id=' . $this->request->get['option_value_id'];
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

			$this->redirect($this->url->link('catalog/option/list_value', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->edit_value();
	}

	public function delete_value() {
		$this->load->language('catalog/option');

		$this->document->setTitle($this->language->get('heading_value_title'));
 		
		$this->load->model('catalog/option');
		
		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $option_value_id) {
				$this->model_catalog_option->deleteOptionValue($this->request->get['option_id'], $option_value_id);
			}
			
			$this->session->data['success'] = $this->language->get('text_success');
			
			$url = '';
			
			if (isset($this->request->get['option_id'])) {
				$url .= '&option_id=' . $this->request->get['option_id'];
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
			
			$this->redirect($this->url->link('catalog/option/list_value', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getOptionValues();
	}

	private function getOptionValues() {
		$this->load->model('localisation/language');
		
		$this->data['languages'] = $this->model_localisation_language->getLanguages();
//		$this->data['heading_value_title'] = $this->language->get('heading_value_title');

		$this->data['text_limit'] = $this->language->get('text_limit');

		$this->data['column_action'] = $this->language->get('column_action');
		$this->data['column_option_value'] = $this->language->get('column_option_value');
		$this->data['column_sort_value'] = $this->language->get('column_sort_value');

		$this->data['button_insert'] = $this->language->get('button_insert');		
		$this->data['button_delete'] = $this->language->get('button_delete');		
		$this->data['button_cancel'] = $this->language->get('button_cancel');

		if (isset($this->request->get['limit'])) {
			$limit = $this->request->get['limit'];
		} else {
			$limit = $this->config->get('config_admin_limit');
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		if (isset($this->request->get['option_id'])) {
			$option_id = $this->request->get['option_id'];
		} else {
			$option_id = null;
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'ovd.name';
		}
		
		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}
		
		$url = '';
						
		if (isset($this->request->get['filterOptionValue'])) {
			$url .= '&filterOptionValue=' . $this->request->get['filterOptionValue'];
		}
	
		if (isset($this->request->get['option_id'])) {
			$url .= '&option_id=' . $this->request->get['option_id'];
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

		$this->data['limits'] = array();

		$this->data['limits'][] = array(
			'text'  => $this->config->get('config_admin_limit'),
			'value' => $this->config->get('config_admin_limit'),
			'href'  => $this->url->link('catalog/option/list_value', 'token=' . $this->session->data['token'] . '&limit=' . $this->config->get('config_admin_limit'). $url, 'SSL')
		);

		$this->data['limits'][] = array(
			'text'  => 150,
			'value' => 150,
			'href'  => $this->url->link('catalog/option/list_value', 'token=' . $this->session->data['token'] . '&limit=150' . $url, 'SSL')
		);

		$this->data['limits'][] = array(
			'text'  => 100,
			'value' => 100,
			'href'  => $this->url->link('catalog/option/list_value', 'token=' . $this->session->data['token'] . '&limit=100' . $url, 'SSL')
		);

		$this->data['limits'][] = array(
			'text'  => 50,
			'value' => 50,
			'href'  => $this->url->link('catalog/option/list_value', 'token=' . $this->session->data['token'] . '&limit=50' . $url, 'SSL')
		);

		$this->data['limits'][] = array(
			'text'  => 25,
			'value' => 25,
			'href'  => $this->url->link('catalog/option/list_value', 'token=' . $this->session->data['token'] . '&limit=25' . $url, 'SSL')
		);


		$this->data['insert'] = $this->url->link('catalog/option/update_value', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['delete'] = $this->url->link('catalog/option/delete_value', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['cancel'] = $this->url->link('catalog/option', 'token=' . $this->session->data['token'], 'SSL');

 		$data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $limit,
			'limit' => $limit,
			'option_id' => $option_id
		);

		$option = $this->model_catalog_option->getOption($this->request->get['option_id']);
		$this->data['option_name'] = $option['name'];
		$this->data['option_type'] = $option['type'];
	
		$total_values = count($this->model_catalog_option->getOptionValuesTotal($this->request->get['option_id']));
		$option_values = $this->model_catalog_option->getOptionValuesWithoutValues($data);

		foreach ($option_values as $option_value) {
			$action = array();
			$action[] = array(
				'text' => $this->language->get('text_edit'),
				'href' => $this->url->link('catalog/option/edit_value', 'token=' . $this->session->data['token'] . '&option_value_id=' . $option_value['option_value_id'] . $url, 'SSL')
			);

			if ($option_value['image'] && file_exists(DIR_IMAGE . $option_value['image'])) {
				$image = $option_value['image'];
			} else {
				$image = 'no_image.jpg';
			}
			
			$this->data['option_values'][] = array(
				'option_value_id'          => $option_value['option_value_id'],
				'name'						 => $option_value['name'],
				'sort_order'               => $option_value['sort_order'],
				'selected'   				=> isset($this->request->post['selected']) && in_array($option_value['option_value_id'], $this->request->post['selected']),
				'action'					=> $action

			);
		}

		$url2 = '';
						
		if (isset($this->request->get['filterOptionValue'])) {
			$url2 .= '&filterOptionValue=' . $this->request->get['filterOptionValue'];
		}
	
		if (isset($this->request->get['option_id'])) {
			$url2 .= '&option_id=' . $this->request->get['option_id'];
		}

		if (isset($this->request->get['sort'])) {
			$url2 .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url2 .= '&order=' . $this->request->get['order'];
		}
		
		if (isset($this->request->get['limit'])) {
			$url2 .= '&limit=' . $this->request->get['limit'];
		}

		$pagination = new Pagination();
		$pagination->total = $total_values;
		$pagination->page = $page;
		$pagination->limit = $limit;
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('catalog/option/list_value', 'token=' . $this->session->data['token'] . $url2 . '&page={page}', 'SSL');
		$this->data['pagination'] = $pagination->render();

		$this->data['filterOptionValue'] = $filterOptionValue;
		$this->data['sort'] = $sort;
		$this->data['order'] = $order;
		$this->data['limit'] = $limit;

		$this->template = 'catalog/option_value.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
		

		$this->getResponse()->setOutput($this->render());
 	}



	private function validateForm() {
		if (!$this->user->hasPermission('modify', 'catalog/option')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		foreach ($this->request->post['option_description'] as $language_id => $value) {
			if ((utf8_strlen($value['name']) < 1) || (utf8_strlen($value['name']) > 128)) {
				$this->error['name'][$language_id] = $this->language->get('error_name');
			}
		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}


	private function validateDelete() {
		if (!$this->user->hasPermission('modify', 'catalog/option')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
/* [webme] deny order deletions by specified user_group - begin */
$pureManagerGroupId = 11; // ��� ��� ID ��������� ������, ������� ��������� ������� ������.
if ($this->user->getUsergroupId() == $pureManagerGroupId) {
$this->error['warning'] = $this->language->get('error_options_deletion_denied');
}
/* [webme] deny order deletions by specified user_group - end */

		$this->load->model('catalog/product');
		
		foreach ($this->request->post['selected'] as $option_id) {
			$product_total = $this->model_catalog_product->getTotalProductsByOptionId($option_id);

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
	
	public function autocomplete() {
		$json = array();
		
		if (isset($this->request->get['filter_name'])) {
			$this->load->language('catalog/option');
			
			$this->load->model('catalog/option');
			
			$this->load->model('tool/image');
			
			$data = array(
				'filter_name' => $this->request->get['filter_name'],
				'start'       => 0,
				'limit'       => 20
			);
			
			$options = $this->model_catalog_option->getOptions($data);
			
			foreach ($options as $option) {
				$option_value_data = array();
				
				if ($option['type'] == 'select' || $option['type'] == 'radio' || $option['type'] == 'checkbox' || $option['type'] == 'image') {
					$option_values = $this->model_catalog_option->getOptionValues($option['option_id']);
					
					foreach ($option_values as $option_value) {
						if ($option_value['image'] && file_exists(DIR_IMAGE . $option_value['image'])) {
							$image = $this->model_tool_image->resize($option_value['image'], 50, 50);
						} else {
							$image = '';
						}
													
						$option_value_data[] = array(
							'option_value_id' => $option_value['option_value_id'],
							'name'            => html_entity_decode($option_value['name'], ENT_QUOTES, 'UTF-8'),
							'image'           => $image					
						);
					}
					
					$sort_order = array();
				  
					foreach ($option_value_data as $key => $value) {
						$sort_order[$key] = $value['name'];
					}
			
					array_multisort($sort_order, SORT_ASC, $option_value_data);					
				}
				
				$type = '';
				
				if ($option['type'] == 'select' || $option['type'] == 'radio' || $option['type'] == 'checkbox' || $option['type'] == 'image') {
					$type = $this->language->get('text_choose');
				}
				
				if ($option['type'] == 'text' || $option['type'] == 'textarea') {
					$type = $this->language->get('text_input');
				}
				
				if ($option['type'] == 'file') {
					$type = $this->language->get('text_file');
				}
				
				if ($option['type'] == 'date' || $option['type'] == 'datetime' || $option['type'] == 'time') {
					$type = $this->language->get('text_date');
				}
												
				$json[] = array(
					'option_id'    => $option['option_id'],
					'name'         => html_entity_decode($option['name'], ENT_QUOTES, 'UTF-8'),
					'category'     => $type,
					'type'         => $option['type'],
					'option_value' => $option_value_data
				);
			}
		}

		$sort_order = array();
	  
		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['name'];
		}

		array_multisort($sort_order, SORT_ASC, $json);
				
		$this->getResponse()->setOutput(json_encode($json));
	}
}
?>
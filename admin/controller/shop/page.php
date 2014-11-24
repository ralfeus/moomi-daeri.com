<?php
class ControllerShopPage extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('shop/page');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->getList();
	}

	public function insert() {
		$this->load->language('shop/page');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('shop/page');

		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
			$this->model_shop_page->addPage($this->request->post);

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

			$this->redirect($this->url->link('shop/page', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getForm();
	}

	public function update() {
		$this->load->language('shop/page');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('shop/page');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_shop_page->editPage($this->request->get['page_id'], $this->request->post);

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

			$this->redirect($this->url->link('shop/page', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language('shop/page');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('shop/page');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $page_id) {
				$this->model_shop_page->deletePage($page_id);
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

			$this->redirect($this->url->link('shop/page', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getList();
	}

	private function getList() {
		$lang = $this->language->get('code');
		$this->load->model('shop/page');
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'page_name_' . $lang;
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
			'href'      => $this->url->link('shop/page', 'token=' . $this->session->data['token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);

		$this->data['insert'] = $this->url->link('shop/page/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['delete'] = $this->url->link('shop/page/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');

		$this->data['pages'] = array();

		$data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_admin_limit'),
			'limit' => $this->config->get('config_admin_limit')
		);

		$pages_total = $this->model_shop_page->getAllPages();

		$results = $this->model_shop_page->getPages($data);

    	foreach ($results as $result) {
			$action = array();

			$action[] = array(
				'text' => $this->language->get('text_edit'),
				'href' => $this->url->link('shop/page/update', 'token=' . $this->session->data['token'] . '&page_id=' . $result['page_id'] . $url, 'SSL')
			);

			$this->data['pages'][] = array(
				'page_id' 			=> $result['page_id'],
				'title'         => $result['page_name_' . $lang],
				'sort_order'    => $result['parent_page_order'],
				'selected'      => isset($this->request->post['selected']) && in_array($result['page_id'], $this->request->post['selected']),
				'action'        => $action
			);
		}

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_no_results'] = $this->language->get('text_no_results');

		$this->data['column_title'] = $this->language->get('column_title');
		$this->data['column_sort_order'] = $this->language->get('column_sort_order');
		$this->data['column_action'] = $this->language->get('column_action');

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

		$this->data['sort_title'] = $this->url->link('shop/page', 'token=' . $this->session->data['token'] . '&sort=id.title' . $url, 'SSL');
		$this->data['sort_sort_order'] = $this->url->link('shop/page', 'token=' . $this->session->data['token'] . '&sort=i.sort_order' . $url, 'SSL');

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $information_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('shop/page', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

		$this->template = 'shop/page_list.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->getResponse()->setOutput($this->render());
	}

	private function getForm() {
		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_default'] = $this->language->get('text_default');
		$this->data['text_enabled'] = $this->language->get('text_enabled');
    	$this->data['text_disabled'] = $this->language->get('text_disabled');

		$this->data['entry_title'] = $this->language->get('entry_title');
		$this->data['entry_description'] = $this->language->get('entry_description');
		$this->data['entry_title_en'] = $this->language->get('entry_title_en');
		$this->data['entry_content_en'] = $this->language->get('entry_content_en');
		$this->data['entry_title_ru'] = $this->language->get('entry_title_ru');
		$this->data['entry_content_ru'] = $this->language->get('entry_content_ru');
		$this->data['entry_title_jp'] = $this->language->get('entry_title_jp');
		$this->data['entry_content_jp'] = $this->language->get('entry_content_jp');
		$this->data['entry_parent_page'] = $this->language->get('entry_parent_page');
		$this->data['entry_parent_order'] = $this->language->get('entry_parent_order');
		$this->data['entry_no_parent'] = $this->language->get('entry_no_parent');

		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_cancel'] = $this->language->get('button_cancel');

		$this->data['tab_general'] = $this->language->get('tab_general');
    	$this->data['tab_data'] = $this->language->get('tab_data');
		$this->data['tab_design'] = $this->language->get('tab_design');

		$this->data['token'] = $this->session->data['token'];

 		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

 		if (isset($this->error['title'])) {
			$this->data['error_title'] = $this->error['title'];
		} else {
			$this->data['error_title'] = array();
		}

	 	if (isset($this->error['description'])) {
			$this->data['error_description'] = $this->error['description'];
		} else {
			$this->data['error_description'] = array();
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
			'href'      => $this->url->link('shop/page', 'token=' . $this->session->data['token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);

		if (!isset($this->request->get['page_id'])) {
			$this->data['action'] = $this->url->link('shop/page/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$this->data['action'] = $this->url->link('shop/page/update', 'token=' . $this->session->data['token'] . '&page_id=' . $this->request->get['page_id'] . $url, 'SSL');
		}

		$this->data['cancel'] = $this->url->link('shop/page', 'token=' . $this->session->data['token'] . $url, 'SSL');

		if (isset($this->request->get['page_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$page_info = $this->model_shop_page->getPage($this->request->get['page_id']);
		}

		$this->load->model('localisation/language');

		$this->data['languages'] = $this->model_localisation_language->getLanguages();


		if (isset($this->request->post['status'])) {
			$this->data['status'] = $this->request->post['status'];
		} elseif (!empty($page_info)) {
			$this->data['status'] = $page_info['status'];
		} else {
			$this->data['status'] = 1;
		}

		if (isset($this->request->post['parent'])) {
			$this->data['parent'] = $this->request->post['parent'];
		} elseif (!empty($page_info)) {
			$this->data['parent'] = $page_info['parent_page_id'];
		} else {
			$this->data['parent'] = 0;
		}
//print_r($this->data['parent']); die();
		if (isset($this->request->post['parent_order'])) {
			$this->data['parent_order'] = $this->request->post['parent_order'];
		} elseif (!empty($page_info)) {
			$this->data['parent_order'] = $page_info['parent_page_order'];
		} else {
			$this->data['parent_order'] = 0;
		}

		if (isset($this->request->post['title']['en'])) {
			$this->data['title']['en'] = $this->request->post['title']['en'];
		} elseif (!empty($page_info)) {
			$this->data['title']['en'] = $page_info['page_name_en'];
		} else {
			$this->data['title']['en'] = '';
		}

		if (isset($this->request->post['content']['en'])) {
			$this->data['content']['en'] = $this->request->post['content']['en'];
		} elseif (!empty($page_info)) {
			$this->data['content']['en'] = $page_info['page_content_en'];
		} else {
			$this->data['content']['en'] = '';
		}

		if (isset($this->request->post['title']['ru'])) {
			$this->data['title']['ru'] = $this->request->post['title']['ru'];
		} elseif (!empty($page_info)) {
			$this->data['title']['ru'] = $page_info['page_name_ru'];
		} else {
			$this->data['title']['ru'] = '';
		}

		if (isset($this->request->post['content']['ru'])) {
			$this->data['content']['ru'] = $this->request->post['content']['ru'];
		} elseif (!empty($page_info)) {
			$this->data['content']['ru'] = $page_info['page_content_ru'];
		} else {
			$this->data['content']['ru'] = '';
		}

		if (isset($this->request->post['title']['jp'])) {
			$this->data['title']['jp'] = $this->request->post['title']['jp'];
		} elseif (!empty($page_info)) {
			$this->data['title']['jp'] = $page_info['page_name_jp'];
		} else {
			$this->data['title']['jp'] = '';
		}

		if (isset($this->request->post['content']['jp'])) {
			$this->data['content']['jp'] = $this->request->post['content']['jp'];
		} elseif (!empty($page_info)) {
			$this->data['content']['jp'] = $page_info['page_content_jp'];
		} else {
			$this->data['content']['jp'] = '';
		}

		$this->load->model('design/layout');

		$this->data['allPages'] = $this->model_shop_page->getAllPages();

		$this->data['layouts'] = $this->model_design_layout->getLayouts();

		$this->template = 'shop/page_form.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->getResponse()->setOutput($this->render());
	}

	private function validateForm() {
		if (!$this->user->hasPermission('modify', 'shop/page')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		foreach ($this->request->post['information_description'] as $language_id => $value) {
			if ((utf8_strlen($value['title']) < 3) || (utf8_strlen($value['title']) > 64)) {
				$this->error['title'][$language_id] = $this->language->get('error_title');
			}

			if (utf8_strlen($value['description']) < 3) {
				$this->error['description'][$language_id] = $this->language->get('error_description');
			}
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
		if (!$this->user->hasPermission('modify', 'shop/page')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		$this->load->model('setting/store');

		foreach ($this->request->post['selected'] as $information_id) {
			if ($this->config->get('config_account_id') == $information_id) {
				$this->error['warning'] = $this->language->get('error_account');
			}

			if ($this->config->get('config_checkout_id') == $information_id) {
				$this->error['warning'] = $this->language->get('error_checkout');
			}

			if ($this->config->get('config_affiliate_id') == $information_id) {
				$this->error['warning'] = $this->language->get('error_affiliate');
			}

			$store_total = $this->model_setting_store->getTotalStoresByInformationId($information_id);

			if ($store_total) {
				$this->error['warning'] = sprintf($this->language->get('error_store'), $store_total);
			}
		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}
}
?>
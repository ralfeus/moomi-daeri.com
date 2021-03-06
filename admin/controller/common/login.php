<?php
use system\engine\Controller;

class ControllerCommonLogin extends \system\engine\Controller {
	private $error = array();
	          
	public function index() { 
    	$this->load->language('common/login');

		$this->document->setTitle($this->language->get('heading_title'));

		if ($this->user->isLogged() && isset($_REQUEST['token']) && ($_REQUEST['token'] == $this->session->data['token'])) {
			if ($this->user->getUsergroupId() == 1) {
				$this->redirect($this->url->link('sale/order', 'token=' . $this->session->data['token'], 'SSL'));
			} else {
				$this->redirect($this->url->link('catalog/product', 'token=' . $this->session->data['token'], 'SSL'));
			}
		}

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->session->data['token'] = md5(mt_rand());
		
			if (isset($this->request->post['redirect'])) {
				$this->redirect($this->request->post['redirect'] . '&token=' . $this->session->data['token']);
			} else {
				$this->redirect($this->url->link('catalog/product', 'token=' . $this->session->data['token'], 'SSL'));
			}
		}
		
    	$this->data['heading_title'] = $this->language->get('heading_title');
		
		$this->data['text_login'] = $this->language->get('text_login');
		$this->data['text_forgotten'] = $this->language->get('text_forgotten');
		
		$this->data['entry_username'] = $this->language->get('entry_username');
    	$this->data['entry_password'] = $this->language->get('entry_password');

    	$this->data['button_login'] = $this->language->get('button_login');
		
		if ((isset($this->session->data['token']) && !isset($_REQUEST['token'])) || ((isset($_REQUEST['token']) && (isset($this->session->data['token']) && ($_REQUEST['token'] != $this->session->data['token']))))) {
			$this->error['warning'] = $this->language->get('error_token');
		}
		
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
				
    	$this->data['action'] = $this->getUrl()->link('common/login', '', 'SSL');

		if (isset($this->request->post['username'])) {
			$this->data['username'] = $this->request->post['username'];
		} else {
			$this->data['username'] = '';
		}
		
		if (isset($this->request->post['password'])) {
			$this->data['password'] = $this->request->post['password'];
		} else {
			$this->data['password'] = '';
		}

		if (isset($this->request->get['route'])) {
			$route = $this->request->get['route'];
			
			unset($this->request->get['route']);
			
			if (isset($_REQUEST['token'])) {
				unset($_REQUEST['token']);
			}
			
			$url = '';
						
			if ($this->request->get) {
				$url .= http_build_query($this->request->get);
			}
			
			$this->data['redirect'] = $this->url->link($route, $url, 'SSL');
		} else {
			$this->data['redirect'] = '';	
		}
	
		$this->data['forgotten'] = $this->url->link('common/forgotten', '', 'SSL');
	
		$this->template = 'common/login.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->getResponse()->setOutput($this->render());
  	}
		
	private function validate() {
		$loginFailed = !$this->user->login($this->request->post['username'], $this->request->post['password']);
		if (isset($this->request->post['username']) && isset($this->request->post['password']) && $loginFailed) {
			$this->error['warning'] = $this->language->get('error_login');
		}
		
		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}
}
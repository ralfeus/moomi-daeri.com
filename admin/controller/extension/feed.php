<?php
class ControllerExtensionFeed extends Controller {
	public function index() {
		$this->load->language('extension/feed');
		 
		$this->document->setTitle($this->language->get('heading_title')); 

  		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('extension/feed', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);
		
		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_no_results'] = $this->language->get('text_no_results');
		$this->data['text_confirm'] = $this->language->get('text_confirm');

		$this->data['column_name'] = $this->language->get('column_name');
		$this->data['column_status'] = $this->language->get('column_status');
		$this->data['column_action'] = $this->language->get('column_action');

		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];
		
			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}

		if (isset($this->session->data['error'])) {
			$this->data['error'] = $this->session->data['error'];
		
			unset($this->session->data['error']);
		} else {
			$this->data['error'] = '';
		}

		$this->load->model('setting/extension');

		$extensions = \model\setting\ExtensionDAO::getInstance()->getExtensions('feed');
		
		foreach ($extensions as $key => $value) {
			if (!file_exists(DIR_APPLICATION . 'controller/feed/' . $value['code'] . '.php')) {
				\model\setting\ExtensionDAO::getInstance()->uninstall('feed', $value['code']);
				
				unset($extensions[$key]);
			}
		}
		
		$this->data['extensions'] = array();
						
		$files = glob(DIR_APPLICATION . 'controller/feed/*.php');
		
		if ($files) {
			foreach ($files as $file) {
				$extensionFileName = basename($file, '.php');
			
				$this->load->language('feed/' . $extensionFileName);

				$action = array(); $installed = false;
				foreach ($extensions as $extensionCode => $extension) {
					if ($extensionCode == $extensionFileName) {
						$installed = true;
						break;
					}
				}
				if (!$installed) {
					$action[] = array(
						'text' => $this->language->get('text_install'),
						'href' => $this->url->link('extension/feed/install', 'token=' . $this->session->data['token'] . '&extension=' . $extensionFileName, 'SSL')
					);
				} else {
					$action[] = array(
						'text' => $this->language->get('text_edit'),
						'href' => $this->url->link('feed/' . $extensionFileName . '', 'token=' . $this->session->data['token'], 'SSL')
					);
							
					$action[] = array(
						'text' => $this->language->get('text_uninstall'),
						'href' => $this->url->link('extension/feed/uninstall', 'token=' . $this->session->data['token'] . '&extension=' . $extensionFileName, 'SSL')
					);
				}
									
				$this->data['extensions'][] = array(
					'name'   => $this->language->get('heading_title'),
					'status' => $this->config->get($extensionFileName . '_status') ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
					'action' => $action
				);
			}
		}

		$this->template = 'extension/feed.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->getResponse()->setOutput($this->render());
	}
	
	public function install() {
    	if (!$this->user->hasPermission('modify', 'extension/feed')) {
      		$this->session->data['error'] = $this->language->get('error_permission'); 
			
			$this->redirect($this->url->link('extension/feed', 'token=' . $this->session->data['token'], 'SSL'));
    	} else {
			$this->load->model('setting/extension');
		
			\model\setting\ExtensionDAO::getInstance()->install('feed', $this->request->get['extension']);
		
			$this->load->model('user/user_group');
		
			$this->model_user_user_group->addPermission($this->user->getId(), 'access', 'feed/' . $this->request->get['extension']);
			$this->model_user_user_group->addPermission($this->user->getId(), 'modify', 'feed/' . $this->request->get['extension']);
		
			require_once(DIR_APPLICATION . 'controller/feed/' . $this->request->get['extension'] . '.php');
			
			$class = 'ControllerFeed' . str_replace('_', '', $this->request->get['extension']);
			$class = new $class($this->registry);
			
			if (method_exists($class, 'install')) {
				$class->install();
			}
		
			$this->redirect($this->url->link('extension/feed', 'token=' . $this->session->data['token'], 'SSL'));			
		}
	}
	
	public function uninstall() {
    	if (!$this->user->hasPermission('modify', 'extension/feed')) {
      		$this->session->data['error'] = $this->language->get('error_permission'); 
			
			$this->redirect($this->url->link('extension/feed', 'token=' . $this->session->data['token'], 'SSL'));
    	} else {		
			$this->load->model('setting/extension');
			$this->load->model('setting/setting');
			
			\model\setting\ExtensionDAO::getInstance()->uninstall('feed', $this->request->get['extension']);
		
			$this->model_setting_setting->deleteSetting($this->request->get['extension']);
		
			require_once(DIR_APPLICATION . 'controller/feed/' . $this->request->get['extension'] . '.php');
			
			$class = 'ControllerFeed' . str_replace('_', '', $this->request->get['extension']);
			$class = new $class($this->registry);
			
			if (method_exists($class, 'uninstall')) {
				$class->uninstall();
			}
		
			$this->redirect($this->url->link('extension/feed', 'token=' . $this->session->data['token'], 'SSL'));
		}
	}
}
?>
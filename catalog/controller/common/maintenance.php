<?php
use system\engine\Controller;
use system\library\User;

class ControllerCommonMaintenance extends \system\engine\Controller {
    public function index() {
        if ($this->config->get('config_maintenance')) {
			$route = '';
			
			if (isset($this->request->get['route'])) {
				$part = explode('/', $this->request->get['route']);
				
				if (isset($part[0])) {
					$route .= $part[0];
				}			
			}
			
			// Show site if logged in as admin
			//$this->load->library('user');
			
			$this->user = new User($this->registry);

			if ($route != 'payment') {
				return $this->forward('common/maintenance/info');
			}						
        }
    }
		
	public function info() {
        $this->load->language('common/maintenance');
        
        $this->document->setTitle($this->language->get('heading_title'));
        
        $this->data['heading_title'] = $this->language->get('heading_title');
                
        $this->document->breadcrumbs = array();

        $this->document->breadcrumbs[] = array(
            'text'      => $this->language->get('text_maintenance'),
			'href'      => $this->url->link('common/maintenance'),
            'separator' => false
        ); 
        
        $this->data['message'] = $this->language->get('text_message');
      
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/common/maintenance.tpl')) {
            $this->template = $this->config->get('config_template') . '/template/common/maintenance.tpl';
        } else {
            $this->template = 'default/template/common/maintenance.tpl';
        }
		
		$this->children = array(
			'common/footer',
			'common/header'
		);
		
		$this->getResponse()->setOutput($this->render());
    }
}
?>
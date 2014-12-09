<?php  
class ControllerModuleOk extends Controller {
	protected function index($setting) {
		$this->language->load('module/ok');
		
    	$this->data['heading_title'] = $this->language->get('heading_title');
		
		
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/module/ok.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/module/ok.tpl';
		} else {
			$this->template = 'default/template/module/ok.tpl';
		}
		
		$this->render();
  	}
}
?>
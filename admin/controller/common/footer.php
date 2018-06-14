<?php
class ControllerCommonFooter extends \system\engine\Controller {
	protected function index() {
		$this->load->language('common/footer');
		
		$this->data['text_footer'] = sprintf($this->language->get('text_footer'), VERSION);
		
		$this->template = 'common/footer.tpl';
	
    	$this->render();
  	}
}
?>
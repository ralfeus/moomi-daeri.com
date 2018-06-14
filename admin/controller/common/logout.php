<?php
use system\engine\Controller;

class ControllerCommonLogout extends \system\engine\Controller {
	public function index() { 
    	$this->user->logout();
 
 		unset($this->session->data['token']);

		$this->redirect($this->url->link('common/login', '', 'SSL'));
  	}
}  
?>
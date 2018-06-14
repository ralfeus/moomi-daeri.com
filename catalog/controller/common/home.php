<?php
use system\engine\Controller;

class ControllerCommonHome extends \system\engine\Controller {

	public function index() {
		$this->document->setTitle($this->config->get('config_title'));
		$this->document->setDescription($this->config->get('config_meta_description'));

		$this->data['heading_title'] = $this->config->get('config_title');

		$this->language->load('shop/general');
		$this->data['text_button_download'] = $this->language->get('text_button_download');
		$this->data['isSaler'] = $this->customer->getCustomerGroupId() == 6;
		$this->data['showDownload'] = false;
        if (empty($_REQUEST['route']))
            $_REQUEST['route'] = 'common/home';
		if($_REQUEST['route'] == "product/category" || $_REQUEST['route'] == "common/home") {
			$this->data['showDownload'] = true;
		}

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/common/home.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/common/home.tpl';
		} else {
			$this->template = 'default/template/common/home.tpl';
		}

		$this->children = array(
			'common/column_left',
			'common/column_right',
			'common/content_top',
			'common/content_bottom',
			'common/footer',
			'common/header'
		);

        $this->getResponse()->setOutput($this->render());
	}
}
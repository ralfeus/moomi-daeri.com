<?php
class ControllerCommonSpecialBottom extends Controller {
	public function index() {
//		$this->load->model('design/layout');
		$this->load->model('catalog/category');
		$this->load->model('catalog/product');
		$this->load->model('catalog/information');

		


		$this->language->load('common/calendar');
		$this->data['text_our_holidays'] = $this->language->get('text_our_holidays');
		$this->data['text_workday'] = $this->language->get('text_workday');
		$this->data['text_holiday'] = $this->language->get('text_holiday');
		
		$this->language->load('common/header');
        $this->data['text_auction'] = $this->language->get('text_auction');
		//print_r($photos);
 if($this->config->get('wk_auction_timezone_set')){
    $this->data['menuauction'] = $this->url->link('catalog/wkallauctions', '', 'SSL');
}
		$this->language->load('shop/general');
		$this->data['text_button_download'] = $this->language->get('text_button_download');
		$this->data['isSaler'] = $this->customer->getCustomerGroupId() == 6;
		$this->data['showDownload'] = false;
        if (empty($_REQUEST['route']))
            $_REQUEST['route'] = 'information/specaction';
		if($_REQUEST['route'] == "product/category" || $_REQUEST['route'] == "information/specaction") {
			$this->data['showDownload'] = true;
		}

		if (isset($this->request->get['route'])) {
			$route = $this->request->get['route'];
		} else {
			$route = 'common/home';
		}
		$this->data['route'] = $route;
		

		$layout_id = 0;

		if (substr($route, 0, 16) == 'product/category' && isset($this->request->get['path'])) {
			$path = explode('_', (string)$this->request->get['path']);

			$layout_id = $this->model_catalog_category->getCategoryLayoutId(end($path));
		}

		if (substr($route, 0, 15) == 'product/product' && isset($this->request->get['product_id'])) {
			$layout_id = $this->model_catalog_product->getProductLayoutId($this->request->get['product_id']);
		}

		if (substr($route, 0, 23) == 'information/information' && isset($this->request->get['information_id'])) {
			$layout_id = $this->model_catalog_information->getInformationLayoutId($this->request->get['information_id']);
		}

		if (!$layout_id) {
			$layout_id = \model\design\LayoutDAO::getInstance()->getLayout($route);
		}

		if (!$layout_id) {
			$layout_id = $this->config->get('config_layout_id');
		}

		$module_data = array();

		$this->load->model('setting/extension');

		$extensions = \model\setting\ExtensionDAO::getInstance()->getExtensions('module');

		foreach ($extensions as $extension) {
			$modules = $this->config->get($extension['code'] . '_module');

			if ($modules) {
				foreach ($modules as $module) {
					if ($module['layout_id'] == $layout_id && $module['position'] == 'special_bottom' && $module['status']) {
						$module_data[] = array(
							'code'       => $extension['code'],
							'setting'    => $module,
							'sort_order' => $module['sort_order']
						);
					}
				}
			}
		}

		//print_r($modules); die();

		$sort_order = array();

		foreach ($module_data as $key => $value) {
    	$sort_order[$key] = $value['sort_order'];
    }

		array_multisort($sort_order, SORT_ASC, $module_data);

		$this->data['modules'] = array();

		$module_data = array_reverse($module_data);
		foreach ($module_data as $module) {
			$module = $this->getChild('module/' . $module['code'], $module['setting']);

			if ($module) {
				$this->data['modules'][] = $module;
			}
		}

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/common/special_bottom.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/common/special_bottom.tpl';
		} else {
			$this->template = 'default/template/common/special_bottom.tpl';
		}

		//print_r($module_data); die();

		$this->render();
	}
}
?>
<?php
use catalog\model\tool\ModelToolImage;
use system\engine\Controller;

class ControllerModuleBanner extends \system\engine\Controller {
	protected function index($setting) {
		static $module = 0;
		
//		$this->load->model('design/banner');
//		$modelToolImage = new \catalog\model\tool\ModelToolImage($this->getRegistry());
		
		$this->document->addScript('catalog/view/javascript/jquery/jquery.cycle.js');
				
		$this->data['banners'] = array();
		
		$results = \model\design\BannerDAO::getInstance()->getBanner($setting['banner_id']);
		  
		foreach ($results as $result) {
			if (file_exists(DIR_IMAGE . $result['image'])) {
				$this->data['banners'][] = array(
					'title' => $result['title'],
					'link'  => $result['link'],
					'image' => (new ModelToolImage($this->getRegistry()))->resize($result['image'], $setting['width'], $setting['height'])
				);
			}
		}
		
		$this->data['module'] = $module++;
				
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/module/banner.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/module/banner.tpl';
		} else {
			$this->template = 'default/template/module/banner.tpl';
		}
		
		$this->render();
	}
}
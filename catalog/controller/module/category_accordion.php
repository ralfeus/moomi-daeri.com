<?php  
############################################################################################
#  Category Accordion for Opencart 1.5.1.x from HostJars http://opencart.hostjars.com      #
############################################################################################
use system\engine\Controller;

class ControllerModuleCategoryAccordion extends \system\engine\Controller {
	protected function index($settings) {
		$this->language->load('module/category');
		
    	$this->data['heading_title'] = $this->language->get('heading_title');
							
		$this->load->model('catalog/category');
		$this->load->model('catalog/product');

		//ACCORDION
		$this->data['categories'] = $this->getCategoriesAccordion(0);
//		$this->data['scripts'] = '$("#").accordion({header : "> li > a.kids", active : "none", collapsible : true, autoHeight: false, event : "mouseup"});';
		$this->data['scripts'] = '$("#navigation ul").treeview({persist: "location",	collapsed: true, unique: true	});';
		
		
		
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/module/category_accordion.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/module/category_accordion.tpl';
		} else {
			$this->template = 'default/template/module/category_accordion.tpl';
		}
		
		$this->render();
  	}
  	
	
	private function getCategoriesAccordion($parent_id, $current_path = '') {
		$results = $this->model_catalog_category->getCategories($parent_id);

		$output = '<ul>';
		
		foreach ($results as $result) {
			if (!$current_path) {
				$new_path = $result['category_id'];
			} else {
				$new_path = $current_path . '_' . $result['category_id'];
			}

			$children = $this->model_catalog_category->getCategories($result['category_id']);
			$caturl = $this->url->link("product/category", "path=" . $new_path);
			if (empty($children)) {
				$output .= '<li><a href="' . $caturl . '">' . $result['name'] . '</a></li>';
			} else {
				$output .= '<li><a class="kids" href="' . $caturl . '">' . $result['name'] . '</a>';
				$output .= $this->getCategoriesAccordion($result['category_id'], $new_path);
				$output .= '</li>';
			}
		}
		
		$output .= '</ul>';

		return $output;
	}
}
?>
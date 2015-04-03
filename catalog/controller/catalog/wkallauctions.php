<?php
class ControllerCatalogWkallauctions extends Controller {
	private $error = array();

	public function index() {

		$this->language->load('catalog/wkallauctions');
		
		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('account/customer');

      	$this->data['breadcrumbs'] = array();

      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),     	
        	'separator' => false
      	); 
		
		$this->document->addScript('catalog/view/javascript/wkproduct_auction/jquery.countdown.js');
		$this->document->addStyle('catalog/view/theme/default/stylesheet/wkauction/wkallauctions.css'); 
		$this->document->addScript('catalog/view/javascript/wkproduct_auction/jquery.quick.pagination.min.js');

		$this->data['heading_title'] = $this->language->get('heading_title');
		$this->data['entry_empty'] = $this->language->get('entry_empty');
		
		
		$this->load->model('catalog/wkallauctions');

		$this->data['allauctions']=array();
		$wkauctions=$this->model_catalog_wkallauctions->getAuctions();
		
		foreach($wkauctions as $wkau)
		{
			$this->data['allauctions'][]=array(
					'product_id'=>$wkau['product_id'],
					'aumin'=>$this->currency->format($wkau['min']),
					'aumax'=>$this->currency->format($wkau['max']),
					'austart'=>$wkau['start_date'],
					'auend'=>$wkau['end_date'],
					'name'=>$wkau['name'],
					'image'=>$wkau['image'],
				);
		}
			
		
		
		
		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/catalog/wkallauctions.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/catalog/wkallauctions.tpl';
		} else {
			$this->template = 'default/template/catalog/wkallauctions.tpl';
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
?>
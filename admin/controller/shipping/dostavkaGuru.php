<?php
use model\setting\SettingsDAO;
use model\shipping\ShippingMethodDAO;
use system\engine\Controller;

class ControllerShippingDostavkaGuru extends \system\engine\Controller {
	private $error = array();

	public function __construct(Registry $registry) {
		parent::__construct($registry);
		$this->load->model('setting/setting');
	}

	public function getCost() {
//        $this->log->write(print_r($this->request->request, true));
        $shippingMethodDostavkaGuru = ShippingMethodDAO::getInstance()->getMethod('dostavkaGuru');
        $shippingMethodElements = explode('.', $this->request->request['method']);
        $cost = $shippingMethodDostavkaGuru->getCost(
            $shippingMethodElements[1],
            null,
            array('weight' => $this->request->request['weight']));
        $json = array(
            'cost' => $cost
        );
        $this->getResponse()->setOutput(json_encode($json));
    }

	protected function initParameters() {
		$this->initParametersWithDefaults([
			'dostavkaGuruTaxClassId' => SettingsDAO::getInstance()->getSetting('dostavkaGuru', 'dostavkaGuruTaxClassId'),
			'dostavkaGuruStatus' => SettingsDAO::getInstance()->getSetting('dostavkaGuru', 'dostavkaGuruStatus'),
			'dostavkaGuruSortOrder' => SettingsDAO::getInstance()->getSetting('dostavkaGuru', 'dostavkaGuruSortOrder'),
			'intermediateZoneRate' => SettingsDAO::getInstance()->getSetting('dostavkaGuru', 'intermediateZoneRate'),
			'description' => SettingsDAO::getInstance()->getSetting('dostavkaGuru', 'description')
		]);
	}


	public function index() {  
		$this->load->language('shipping/dostavkaGuru');
		$this->document->setTitle($this->language->get('heading_title'));
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			SettingsDAO::getInstance()->updateSettings('dostavkaGuru', $this->parameters);
			$this->session->data['success'] = $this->language->get('text_success');
			$this->redirect($this->url->link('extension/shipping', 'token=' . $this->session->data['token'], 'SSL'));
		}
		
		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_none'] = $this->language->get('text_none');
		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');

		$this->data['entry_rate'] = $this->language->get('entry_rate');
		$this->data['entry_tax_class'] = $this->language->get('entry_tax_class');
		$this->data['entry_status'] = $this->language->get('entry_status');
		$this->data['entry_sort_order'] = $this->language->get('entry_sort_order');
		
		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_cancel'] = $this->language->get('button_cancel');

		$this->data['tab_general'] = $this->language->get('tab_general');

 		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		$this->setBreadcrumbs([[
			'text' => $this->language->get('text_shipping'),
			'route' => 'extension/shipping'
		]]);

		$this->data = array_merge($this->data, $this->parameters);
		$this->data['action'] = $this->url->link('shipping/dostavkaGuru', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['cancel'] = $this->url->link('extension/shipping', 'token=' . $this->session->data['token'], 'SSL');

		$this->load->model('localisation/geo_zone');
		$geo_zones = $this->model_localisation_geo_zone->getGeoZones();
		$this->data['geo_zones'] = $geo_zones;

		$this->load->model('localisation/tax_class');
				
		$this->data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

		$this->template = 'shipping/dostavkaGuru.tpl.php';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->getResponse()->setOutput($this->render());
	}
		
	private function validate() {
		if (!$this->user->hasPermission('modify', 'shipping/dostavkaGuru')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if (!$this->error) {
			return true;
		} else {
			return false;
		}	
	}
}
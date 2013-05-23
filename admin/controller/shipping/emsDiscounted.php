<?php
class ControllerShippingEMSDiscounted extends Controller {
	private $error = array();

    public function getCost()
    {
//        $this->log->write(print_r($this->request->request, true));
        $model = $this->load->model('shipping/emsDiscounted');
        $shippingMethodElements = explode('.', $this->parameters['method']);
        $cost = $model->getCost(
            $shippingMethodElements[1],
            null,
            array('weight' => $this->parameters['weight']));
        $json = array(
            'cost' => $cost
        );
        $this->response->setOutput(json_encode($json));
    }

	public function index() {  
		$this->load->language('shipping/emsDiscounted');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('setting/setting');
				 
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('emsDiscounted', $this->parameters);
			$this->session->data['success'] = $this->language->get('text_success');
			$this->redirect($this->url->link('extension/shipping', 'token=' . $this->session->data['token'], 'SSL'));
		}
		$this->data = $this->parameters;
		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_none'] = $this->language->get('text_none');
		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
        $this->data['textDiscountAmount'] = $this->language->get('DISCOUNT_AMOUNT');
		$this->data['entry_rate'] = $this->language->get('entry_rate');
		$this->data['entry_status'] = $this->language->get('entry_status');
		$this->data['entry_sort_order'] = $this->language->get('entry_sort_order');
		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_cancel'] = $this->language->get('button_cancel');
		$this->data['tab_general'] = $this->language->get('tab_general');

        $this->data['error_warning'] = empty($this->error['warning']) ? '' : $this->error['warning'];

		$this->data['action'] = $this->url->link('shipping/emsDiscounted', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['cancel'] = $this->url->link('extension/shipping', 'token=' . $this->session->data['token'], 'SSL');
		$this->load->model('localisation/geo_zone');
		$geo_zones = $this->model_localisation_geo_zone->getGeoZones();

		foreach ($geo_zones as $geo_zone)
			if (empty($this->data['emsDiscounted_' . $geo_zone['geo_zone_id'] . '_status']))
				$this->data['emsDiscounted_' . $geo_zone['geo_zone_id'] . '_status'] = $this->config->get('emsDiscounted_' . $geo_zone['geo_zone_id'] . '_status');
		$this->data['geo_zones'] = $geo_zones;

        $this->setBreadcrumbs();
		$this->template = 'shipping/emsDiscounted.php';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
	}

    protected function initParameters()
    {
        $this->parameters = $_REQUEST;
        if (empty($_REQUEST['emsDiscounted_discountAmount']))
            $this->parameters['emsDiscounted_discountAmount'] = $this->config->get('emsDiscounted_discountAmount');
        if (empty($_REQUEST['emsDiscounted_sortOrder']))
            $this->parameters['emsDiscounted_sortOrder'] = $this->config->get('emsDiscounted_sortOrder');
        if (empty($_REQUEST['emsDiscounted_status']))
            $this->parameters['emsDiscounted_status']  = $this->config->get('emsDiscounted_status');
        if (empty($_REQUEST['method'])) $this->parameters['method'] = null;
        $this->parameters['token'] = $this->session->data['token'];
        if (empty($_REQUEST['weight'])) $this->parameters['weight'] =  null;
    }

    protected function setBreadcrumbs()
    {
        $this->data['breadcrumbs'] = array();
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false
        );
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_shipping'),
            'href'      => $this->url->link('extension/shipping', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('shipping/emsDiscounted', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );
    }
		
	private function validate() {
		if (!$this->user->hasPermission('modify', 'shipping/emsDiscounted')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if (!$this->error) {
			return true;
		} else {
			return false;
		}	
	}
}
?>
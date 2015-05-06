<?php
class ControllerShopAdmin extends Controller {
	public function showHoliday() {

		$this->load->language('shop/admin');
		$this->data['breadcrumbs'] = array();

 		$this->data['breadcrumbs'][] = array(
     		'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
    		'separator' => false
 		);

 		$this->data['breadcrumbs'][] = array(
     		'text'      => $this->language->get('heading_title'),
				'href'      => $this->url->link('gallery/admin', 'token=' . $this->session->data['token'], 'SSL'),
    		'separator' => ' :: '
 		);

 		$this->data['heading_title_holiday'] = $this->language->get('heading_title_holiday');

 		$this->load->model('shop/general');
 		$this->data['holidays'] = $this->model_shop_general->getAllHolidays();
 		//print_r($this->data['holidays']); die();

 		$this->data['token'] = $this->session->data['token'];
 		$this->template = 'shop/holidayList.tpl.php';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->getResponse()->setOutput($this->render());
	}

	public function addHoliday() {
		$start = $_POST['start'];
		$end = $_POST['end'];
		$name = $_POST['name'];

		$data['start'] = $start;
		$data['end'] = $end;
		$data['name'] = $name;

		$this->load->model('shop/general');

		$holiday_id = $this->model_shop_general->addHoliday($data);

		$response['success'] = true;
		$response['start'] = $start;
		$response['end'] = $end;
		$response['name'] = $name;
		$response['holiday_id'] = $holiday_id;

		print(json_encode($response));

	}

	public function deleteHoliday() {
		$holiday_id = $_POST['holiday_id'];

		$this->load->model('shop/general');

		$holiday_id = $this->model_shop_general->deleteHoliday($holiday_id);

		$response['success'] = true;

		print(json_encode($response));

	}
}
?>
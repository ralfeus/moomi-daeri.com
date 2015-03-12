<?php
################################################################################################
# Product auction Opencart 1.5.1.x From Webkul  http://webkul.com 	#
################################################################################################
class ControllerModuleWkproductauction extends Controller {
	
	private $error = array(); 
	
	public function install() {
		$this->load->model('module/wkproduct_auction');
		$this->model_module_wkproduct_auction->createEventTable();
	}
	
	public function index() {   
		//LOAD LANGUAGE
		$this->load->language('module/wkproduct_auction');

		//SET TITLE
		$this->document->setTitle($this->language->get('heading_title'));
		
		//LOAD SETTINGS
		$this->load->model('setting/setting');
		
		//SAVE SETTINGS (on submission of form)
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('wkproduct_auction', $this->request->post);		
					
			$this->session->data['success'] = $this->language->get('text_success');
						
			$this->redirect($this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'));
		}

		//LANGUAGE
		$text_strings = array(
				'heading_title',
				'text_enabled',
				'text_disabled',
				'text_content_top',
				'text_content_bottom',
				'text_column_left',
				'text_column_right',
				'text_left',
				'text_right',
				'text_module',
				'text_success',
				'text_count_unique',
				'text_count_all',
				'entry_layout',
				'entry_position',
				'entry_status',
				'entry_sort_order',
				'button_save',
				'button_cancel',
				'button_add_module',
				'button_remove',
				'error_permission',

				'ur_timezone',
				
				
				
				
		);
		
		foreach ($text_strings as $text) {
			$this->data[$text] = $this->language->get($text);
		}
	
		//CONFIG
		$config_data = array(
				
				'wk_auction_timezone_set',

		);

		foreach ($config_data as $conf) {
			if (isset($this->request->post[$conf])) {
				$this->data[$conf] = $this->request->post[$conf];
			} else {
				$this->data[$conf] = $this->config->get($conf);
			}
		}
	
		$this->data['wk_zonelist'] = array('Kwajalein' => '(GMT-12:00) International Date Line West',
									'Pacific/Midway' => '(GMT-11:00) Midway Island',
									'Pacific/Samoa' => '(GMT-11:00) Samoa',
									'Pacific/Honolulu' => '(GMT-10:00) Hawaii',
									'America/Anchorage' => '(GMT-09:00) Alaska',
									'America/Los_Angeles' => '(GMT-08:00) Pacific Time (US &amp; Canada)',
									'America/Tijuana' => '(GMT-08:00) Tijuana, Baja California',
									'America/Denver' => '(GMT-07:00) Mountain Time (US &amp; Canada)',
									'America/Chihuahua' => '(GMT-07:00) Chihuahua',
									'America/Mazatlan' => '(GMT-07:00) Mazatlan',
									'America/Phoenix' => '(GMT-07:00) Arizona',
									'America/Regina' => '(GMT-06:00) Saskatchewan',
									'America/Tegucigalpa' => '(GMT-06:00) Central America',
									'America/Chicago' => '(GMT-06:00) Central Time (US &amp; Canada)',
									'America/Mexico_City' => '(GMT-06:00) Mexico City',
									'America/Monterrey' => '(GMT-06:00) Monterrey',
									'America/New_York' => '(GMT-05:00) Eastern Time (US &amp; Canada)',
									'America/Bogota' => '(GMT-05:00) Bogota',
									'America/Lima' => '(GMT-05:00) Lima',
									'America/Rio_Branco' => '(GMT-05:00) Rio Branco',
									'America/Indiana/Indianapolis' => '(GMT-05:00) Indiana (East)',
									'America/Caracas' => '(GMT-04:30) Caracas',
									'America/Halifax' => '(GMT-04:00) Atlantic Time (Canada)',
									'America/Manaus' => '(GMT-04:00) Manaus',
									'America/Santiago' => '(GMT-04:00) Santiago',
									'America/La_Paz' => '(GMT-04:00) La Paz',
									'America/St_Johns' => '(GMT-03:30) Newfoundland',
									'America/Argentina/Buenos_Aires' => '(GMT-03:00) Georgetown',
									'America/Sao_Paulo' => '(GMT-03:00) Brasilia',
									'America/Godthab' => '(GMT-03:00) Greenland',
									'America/Montevideo' => '(GMT-03:00) Montevideo',
									'Atlantic/South_Georgia' => '(GMT-02:00) Mid-Atlantic',
									'Atlantic/Azores' => '(GMT-01:00) Azores',
									'Atlantic/Cape_Verde' => '(GMT-01:00) Cape Verde Is.',
									'Europe/Dublin' => '(GMT) Dublin',
									'Europe/Lisbon' => '(GMT) Lisbon',
									'Europe/London' => '(GMT) London',
									'Africa/Monrovia' => '(GMT) Monrovia',
									'Atlantic/Reykjavik' => '(GMT) Reykjavik',
									'Africa/Casablanca' => '(GMT) Casablanca',
									'Europe/Belgrade' => '(GMT+01:00) Belgrade',
									'Europe/Bratislava' => '(GMT+01:00) Bratislava',
									'Europe/Budapest' => '(GMT+01:00) Budapest',
									'Europe/Ljubljana' => '(GMT+01:00) Ljubljana',
									'Europe/Prague' => '(GMT+01:00) Prague',
									'Europe/Sarajevo' => '(GMT+01:00) Sarajevo',
									'Europe/Skopje' => '(GMT+01:00) Skopje',
									'Europe/Warsaw' => '(GMT+01:00) Warsaw',
									'Europe/Zagreb' => '(GMT+01:00) Zagreb',
									'Europe/Brussels' => '(GMT+01:00) Brussels',
									'Europe/Copenhagen' => '(GMT+01:00) Copenhagen',
									'Europe/Madrid' => '(GMT+01:00) Madrid',
									'Europe/Paris' => '(GMT+01:00) Paris',
									'Africa/Algiers' => '(GMT+01:00) West Central Africa',
									'Europe/Amsterdam' => '(GMT+01:00) Amsterdam',
									'Europe/Berlin' => '(GMT+01:00) Berlin',
									'Europe/Rome' => '(GMT+01:00) Rome',
									'Europe/Stockholm' => '(GMT+01:00) Stockholm',
									'Europe/Vienna' => '(GMT+01:00) Vienna',
									'Europe/Minsk' => '(GMT+02:00) Minsk',
									'Africa/Cairo' => '(GMT+02:00) Cairo',
									'Europe/Helsinki' => '(GMT+02:00) Helsinki',
									'Europe/Riga' => '(GMT+02:00) Riga',
									'Europe/Sofia' => '(GMT+02:00) Sofia',
									'Europe/Tallinn' => '(GMT+02:00) Tallinn',
									'Europe/Vilnius' => '(GMT+02:00) Vilnius',
									'Europe/Athens' => '(GMT+02:00) Athens',
									'Europe/Bucharest' => '(GMT+02:00) Bucharest',
									'Europe/Istanbul' => '(GMT+02:00) Istanbul',
									'Asia/Jerusalem' => '(GMT+02:00) Jerusalem',
									'Asia/Amman' => '(GMT+02:00) Amman',
									'Asia/Beirut' => '(GMT+02:00) Beirut',
									'Africa/Windhoek' => '(GMT+02:00) Windhoek',
									'Africa/Harare' => '(GMT+02:00) Harare',
									'Asia/Kuwait' => '(GMT+03:00) Kuwait',
									'Asia/Riyadh' => '(GMT+03:00) Riyadh',
									'Asia/Baghdad' => '(GMT+03:00) Baghdad',
									'Africa/Nairobi' => '(GMT+03:00) Nairobi',
									'Asia/Tbilisi' => '(GMT+03:00) Tbilisi',
									'Europe/Moscow' => '(GMT+03:00) Moscow',
									'Europe/Volgograd' => '(GMT+03:00) Volgograd',
									'Asia/Tehran' => '(GMT+03:30) Tehran',
									'Asia/Muscat' => '(GMT+04:00) Muscat',
									'Asia/Baku' => '(GMT+04:00) Baku',
									'Asia/Yerevan' => '(GMT+04:00) Yerevan',
									'Asia/Yekaterinburg' => '(GMT+05:00) Ekaterinburg',
									'Asia/Karachi' => '(GMT+05:00) Karachi',
									'Asia/Tashkent' => '(GMT+05:00) Tashkent',
									'Asia/Kolkata' => '(GMT+05:30) Calcutta',
									'Asia/Colombo' => '(GMT+05:30) Sri Jayawardenepura',
									'Asia/Katmandu' => '(GMT+05:45) Kathmandu',
									'Asia/Dhaka' => '(GMT+06:00) Dhaka',
									'Asia/Almaty' => '(GMT+06:00) Almaty',
									'Asia/Novosibirsk' => '(GMT+06:00) Novosibirsk',
									'Asia/Rangoon' => '(GMT+06:30) Yangon (Rangoon)',
									'Asia/Krasnoyarsk' => '(GMT+07:00) Krasnoyarsk',
									'Asia/Bangkok' => '(GMT+07:00) Bangkok',
									'Asia/Jakarta' => '(GMT+07:00) Jakarta',
									'Asia/Brunei' => '(GMT+08:00) Beijing',
									'Asia/Chongqing' => '(GMT+08:00) Chongqing',
									'Asia/Hong_Kong' => '(GMT+08:00) Hong Kong',
									'Asia/Urumqi' => '(GMT+08:00) Urumqi',
									'Asia/Irkutsk' => '(GMT+08:00) Irkutsk',
									'Asia/Ulaanbaatar' => '(GMT+08:00) Ulaan Bataar',
									'Asia/Kuala_Lumpur' => '(GMT+08:00) Kuala Lumpur',
									'Asia/Singapore' => '(GMT+08:00) Singapore',
									'Asia/Taipei' => '(GMT+08:00) Taipei',
									'Australia/Perth' => '(GMT+08:00) Perth',
									'Asia/Seoul' => '(GMT+09:00) Seoul',
									'Asia/Tokyo' => '(GMT+09:00) Tokyo',
									'Asia/Yakutsk' => '(GMT+09:00) Yakutsk',
									'Australia/Darwin' => '(GMT+09:30) Darwin',
									'Australia/Adelaide' => '(GMT+09:30) Adelaide',
									'Australia/Canberra' => '(GMT+10:00) Canberra',
									'Australia/Melbourne' => '(GMT+10:00) Melbourne',
									'Australia/Sydney' => '(GMT+10:00) Sydney',
									'Australia/Brisbane' => '(GMT+10:00) Brisbane',
									'Australia/Hobart' => '(GMT+10:00) Hobart',
									'Asia/Vladivostok' => '(GMT+10:00) Vladivostok',
									'Pacific/Guam' => '(GMT+10:00) Guam',
									'Pacific/Port_Moresby' => '(GMT+10:00) Port Moresby',
									'Asia/Magadan' => '(GMT+11:00) Magadan',
									'Pacific/Fiji' => '(GMT+12:00) Fiji',
									'Asia/Kamchatka' => '(GMT+12:00) Kamchatka',
									'Pacific/Auckland' => '(GMT+12:00) Auckland',
									'Pacific/Tongatapu' => '(GMT+13:00) Nukualofa');



 		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}
		
  		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_module'),
			'href'      => $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);
		
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('module/wkproduct_auction', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);
		
		$this->data['action'] = $this->url->link('module/wkproduct_auction', 'token=' . $this->session->data['token'], 'SSL');
		
		$this->data['cancel'] = $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL');

	
		$this->data['modules'] = array();
		
		if (isset($this->request->post['wkproduct_auction_module'])) {
			$this->data['modules'] = $this->request->post['wkproduct_auction_module'];
		} elseif ($this->config->get('wkproduct_auction_module')) { 
			$this->data['modules'] = $this->config->get('wkproduct_auction_module');
		}		

		$this->load->model('design/layout');
		
		$this->data['layouts'] = $this->model_design_layout->getLayouts();

		//Choose which template file will be used to display this request.
		$this->template = 'module/wkproduct_auction.tpl';
		$this->children = array(
			'common/header',
			'common/footer',
		);

		$this->getResponse()->setOutput($this->render());
	}
	
	private function validate() {
		if (!$this->user->hasPermission('modify', 'module/wkproduct_auction')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if (!$this->error) {
			return TRUE;
		} else {
			return FALSE;
		}	
	}


}
?>
<?php
class ControllerModulebonusloto extends Controller {
	private $error = array(); 

	public function index() {
		$this->load->model('fido/bonusloto');
		$this->model_fido_bonusloto->checkBonusloto();
		$this->load->language('module/bonusloto');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('setting/setting');
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate())) {
			$this->request->post['bonusloto_game'] = $this->model_fido_bonusloto->BonuslotoSortDateTime($this->request->post['bonusloto_game']);
			$this->BonuslotoVipProduct();
			$this->model_setting_setting->editSetting('bonusloto', $this->request->post);	
			$this->model_fido_bonusloto->updateKeywordBonusloto($this->request->post['keyword']);
			$this->saveCron();
			$this->cache->delete('product');
			$this->session->data['success'] = $this->language->get('text_success');
			$this->redirect($this->url->link('extension/module','token=' . $this->session->data['token'], 'SSL'));
		}
		$this->getList();
	}

	public function delete() {
		$this->load->language('module/bonusloto');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('fido/bonusloto');
		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $bonusloto_id) {
				$this->model_fido_bonusloto->deleteBonusloto($bonusloto_id);
			}
			$this->session->data['success'] = $this->language->get('text_success');
			$this->redirect($this->url->link('extension/module','token=' . $this->session->data['token'], 'SSL'));
		}
		$this->getList();
	}
	
	public function getList() {   

		$this->document->addStyle(HTTP_CATALOG.'catalog/view/javascript/bonusloto/css/colorbox.css');
		$this->document->addScript(HTTP_CATALOG.'catalog/view/javascript/bonusloto/js/jquery.colorbox.js');
		$this->document->addScript('view/javascript/bonusloto/js/jquery-cron.js');

		$this->load->language('module/bonusloto');
		$this->load->model('fido/bonusloto');

		$this->data['text_homepage'] = $this->language->get('text_homepage');
		$this->data['text_no_results'] = $this->language->get('text_no_results');

		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_content_top'] = $this->language->get('text_content_top');
		$this->data['text_content_bottom'] = $this->language->get('text_content_bottom');		
		$this->data['text_column_left'] = $this->language->get('text_column_left');
		$this->data['text_column_right'] = $this->language->get('text_column_right');
		$this->data['text_format_timer'] = $this->language->get('text_format_timer');
		$this->data['text_timer'] = $this->language->get('text_timer');
		$this->data['text_module_disabled'] = $this->language->get('text_module_disabled');

		$this->data['text_game_type_cupon'] = $this->language->get('text_game_type_cupon');
		$this->data['text_game_type_point'] = $this->language->get('text_game_type_point');
		$this->data['text_game_type_product'] = $this->language->get('text_game_type_product');
		$this->data['text_game_type_other'] = $this->language->get('text_game_type_other');

		$this->data['text_game_requir_cash'] = $this->language->get('text_game_requir_cash');
		$this->data['text_game_requir_point'] = $this->language->get('text_game_requir_point');
		$this->data['text_game_requir_product'] = $this->language->get('text_game_requir_product');
		$this->data['text_game_requir_post'] = $this->language->get('text_game_requir_post');
		$this->data['text_game_requir_point_buy'] = $this->language->get('text_game_requir_point_buy');
		$this->data['text_game_vip_edit'] = $this->language->get('text_game_vip_edit');

		$this->data['text_image_manager'] = $this->language->get('text_image_manager');
		$this->data['text_browse'] = $this->language->get('text_browse');
		$this->data['text_clear'] = $this->language->get('text_clear');


		$this->data['column_title'] = $this->language->get('column_title');
		$this->data['column_status'] = $this->language->get('column_status');
		$this->data['column_email'] = $this->language->get('column_email');
		
		$this->data['tab_general'] = $this->language->get('tab_general');
		$this->data['tab_graph'] = $this->language->get('tab_graph');
		$this->data['tab_data'] = $this->language->get('tab_data');
		$this->data['tab_about'] = $this->language->get('tab_about');

		$this->data['tab_games_time'] = $this->language->get('tab_games_time');
		$this->data['tab_games_type'] = $this->language->get('tab_games_type');
		$this->data['tab_games_req'] = $this->language->get('tab_games_req');
		$this->data['tab_games_vip'] = $this->language->get('tab_games_vip');

		$this->data['heading_title'] = $this->language->get('heading_title');
		$this->data['bonus_version'] = $this->language->get('bonus_version');
		$this->data['text_about'] = $this->language->get('text_about');
		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_left'] = $this->language->get('text_left');
		$this->data['text_right'] = $this->language->get('text_right');
		$this->data['text_d_email'] = $this->language->get('text_d_email');
		$this->data['text_d_name'] = $this->language->get('text_d_name');

		$this->data['entry_bonusloto'] = $this->language->get('entry_bonusloto');
		$this->data['entry_bonusloto_timezone'] = $this->language->get('entry_bonusloto_timezone');
		$this->data['entry_bonusloto_cron'] = $this->language->get('entry_bonusloto_cron');
		$this->data['entry_bonusloto_cron_wget_path'] = $this->language->get('entry_bonusloto_cron_wget_path');
		$this->data['entry_bonusloto_start_url'] = $this->language->get('entry_bonusloto_start_url');
		$this->data['entry_bonusloto_display'] = $this->language->get('entry_bonusloto_display');
		$this->data['entry_bonusloto_greeting_text'] = $this->language->get('entry_bonusloto_greeting_text');

		$this->data['entry_bonusloto_game'] = $this->language->get('entry_bonusloto_game');
		$this->data['entry_bonusloto_social_background'] = $this->language->get('entry_bonusloto_social_background');
		$this->data['entry_bonusloto_social_color_title'] = $this->language->get('entry_bonusloto_social_color_title');
		$this->data['entry_bonusloto_social_border_color'] = $this->language->get('entry_bonusloto_social_border_color');
		$this->data['entry_bonusloto_posting_button'] = $this->language->get('entry_bonusloto_posting_button');
		$this->data['entry_bonusloto_custom_style'] = $this->language->get('entry_bonusloto_custom_style');


		$this->data['entry_bonusloto_posting_status'] = $this->language->get('entry_bonusloto_posting_status');
		$this->data['entry_bonusloto_posting_time'] = $this->language->get('entry_bonusloto_posting_time');
		$this->data['entry_bonusloto_posting_points'] = $this->language->get('entry_bonusloto_posting_points');

		$this->data['entry_bonusloto_start_lototron'] = $this->language->get('entry_bonusloto_start_lototron');
		$this->data['entry_headline_chars'] = $this->language->get('entry_headline_chars');
		$this->data['entry_position'] = $this->language->get('entry_position');
		$this->data['entry_status'] = $this->language->get('entry_status');
		$this->data['entry_sort_order'] = $this->language->get('entry_sort_order');

		$this->data['entry_game_status'] = $this->language->get('entry_game_status');
		$this->data['entry_game_type'] = $this->language->get('entry_game_type');
		$this->data['entry_game_data'] = $this->language->get('entry_game_data');
		$this->data['entry_game_time'] = $this->language->get('entry_game_time');
		$this->data['entry_game_prize'] = $this->language->get('entry_game_prize');
		$this->data['entry_game_code'] = $this->language->get('entry_game_code');
		$this->data['entry_game_requir'] = $this->language->get('entry_game_requir');
		$this->data['entry_game_requir_val'] = $this->language->get('entry_game_requir_val');

		$this->data['entry_game_vip_product'] = $this->language->get('entry_game_vip_product');
		$this->data['entry_game_status_vip'] = $this->language->get('entry_game_status_vip');
		$this->data['entry_game_vip_count'] = $this->language->get('entry_game_vip_count');
		$this->data['entry_game_vip_val'] = $this->language->get('entry_game_vip_val');
		$this->data['entry_game_do'] = $this->language->get('entry_game_do');
		$this->data['entry_bonusloto_rotater_time'] = $this->language->get('entry_bonusloto_rotater_time');

		$this->data['entry_bonusloto_images_countdown'] = $this->language->get('entry_bonusloto_images_countdown');
		$this->data['entry_bonusloto_background'] = $this->language->get('entry_bonusloto_background');
		$this->data['entry_bonusloto_background_img'] = $this->language->get('entry_bonusloto_background_img');
		$this->data['entry_bonusloto_gamer_count'] = $this->language->get('entry_bonusloto_gamer_count');
		$this->data['entry_keyword'] = $this->language->get('entry_keyword');
		$this->data['entry_limit'] = $this->language->get('entry_limit');
		$this->data['entry_image'] = $this->language->get('entry_image');
		$this->data['entry_layout'] = $this->language->get('entry_layout');

		$this->data['entry_cron_status'] = $this->language->get('entry_cron_status');
		$this->data['entry_cron_log'] = $this->language->get('entry_cron_log');
		$this->data['entry_cron_value'] = $this->language->get('entry_cron_value');
		$this->data['entry_cron_test'] = $this->language->get('entry_cron_test');
		$this->data['entry_cron_question'] = $this->language->get('entry_cron_question');
		$this->data['entry_cron_question_well'] = $this->language->get('entry_cron_question_well');

		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_cancel'] = $this->language->get('button_cancel');
		$this->data['button_delete'] = $this->language->get('button_delete');
		$this->data['button_add_game'] = $this->language->get('button_add_game');
		$this->data['button_remove'] = $this->language->get('button_remove');
		$this->data['button_stop_lototron'] = $this->language->get('button_stop_lototron');
		$this->data['button_save_cron'] = $this->language->get('button_save_cron');
		$this->data['button_delete_cron'] = $this->language->get('button_delete_cron');
		$this->data['button_test_cron'] = $this->language->get('button_test_cron');
		$this->data['button_cron_log'] = $this->language->get('button_cron_log');


 		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

 		if (isset($this->error['num_chars'])) {
			$this->data['error_num_chars'] = $this->error['num_chars'];
		} else {
			$this->data['error_num_chars'] = '';
		} 

  		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
       		'text'      => $this->language->get('text_home'),
      		'separator' => FALSE
   		);

   		$this->data['breadcrumbs'][] = array(
       		'href'      => $this->url->link('extension/module','token=' . $this->session->data['token'], 'SSL'),
       		'text'      => $this->language->get('text_module'),
      		'separator' => ' :: '
   		);
		
   		$this->data['breadcrumbs'][] = array(
       		'href'      => $this->url->link('module/bonusloto','token=' . $this->session->data['token'], 'SSL'),
       		'text'      => $this->language->get('heading_title'),
      		'separator' => ' :: '
   		);
		
		$this->data['game_url_edit_product'] = $this->url->link('catalog/product/update', 'token=' . $this->session->data['token'] . '&product_id=' , 'SSL');

		$this->data['action'] 		= $this->url->link('module/bonusloto','token=' . $this->session->data['token'], 'SSL');
		$this->data['cancel'] 		= $this->url->link('extension/module','token=' . $this->session->data['token'], 'SSL');
		$this->data['delete'] 		= $this->url->link('module/bonusloto/delete','token=' . $this->session->data['token'], 'SSL');
		$this->data['test_cron_av'] 	= $this->url->link('module/bonusloto/testcron','token=' . $this->session->data['token'], 'SSL');
		$this->data['cronlog'] 		= $this->url->link('module/bonusloto/cronlogview', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['token'] 		= $this->session->data['token'];

		$this->data['bonuslotos'] = array();
		$bonusloto_total = $this->model_fido_bonusloto->getTotalbonusloto();
		$results = $this->model_fido_bonusloto->getbonusloto();
	    	foreach ($results as $result) {
			$this->data['bonuslotos'][] = array(
				'bonusloto_id'  => $result['bonusloto_id'],
				'title'       	=> $result['title'],
				'status'	=> $result['description'],
				'email'		=> $result['winner_email'],
				'selected'     	=> isset($this->request->post['selected']) && in_array($result['bonusloto_id'], $this->request->post['selected'])
			);
		}



		if (isset($this->request->post['bonusloto'])) {
			$this->data['bonusloto'] = $this->request->post['bonusloto'];
		} else {
			$this->data['bonusloto'] = $this->config->get('bonusloto');
		}

		if (isset($this->request->post['bonusloto_timezone'])) {
			$this->data['bonusloto_timezone'] = $this->request->post['bonusloto_timezone'];
		} else {
			$this->data['bonusloto_timezone'] = $this->config->get('bonusloto_timezone');
		}

		$this->data['keyword'] = $this->model_fido_bonusloto->getKeywordBonusloto();

		$this->data['games'] = array();

		if (isset($this->request->post['bonusloto_game'])) {
			$this->data['games'] = $this->request->post['bonusloto_game'];
		} elseif ($this->config->get('bonusloto_game')) { 
			$this->data['games'] = $this->config->get('bonusloto_game');
		}	


		if (isset($this->request->post['bonusloto_cron_wget_path'])) {
			$this->data['bonusloto_cron_wget_path'] = $this->request->post['bonusloto_cron_wget_path'];
		} elseif ($this->config->get('bonusloto_cron_wget_path') != '') {
			$this->data['bonusloto_cron_wget_path'] = $this->config->get('bonusloto_cron_wget_path');
		} else {
			$this->data['bonusloto_cron_wget_path'] = shell_exec('which wget');
		}


		$pass = $this->config->get('bonusloto_start_pass');
		if (!isset($pass) || $pass =='') {
			$pass = str_shuffle('abcefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890');
			$this->data['bonusloto_start_pass'] = substr($pass,4,8);
		} else {
			$this->data['bonusloto_start_pass'] = $this->config->get('bonusloto_start_pass');
		}
		$wgetParam = '';
		if (strpos(HTTP_CATALOG, 'https') !== false){
			$wgetParam = '--no-check-certificate';
		}
	
		if (isset($this->data['bonusloto_cron_wget_path']) && $this->data['bonusloto_cron_wget_path'] !='') {
			$this->data['bonusloto_start_url'] = $this->data['bonusloto_cron_wget_path'] . ' ' . $wgetParam . ' -O - -q -t 1 &apos;' . HTTP_CATALOG .'index.php?route=module/lototron/start&action=start-' . $this->data['bonusloto_start_pass'] .'&apos;';
		} else {
			$this->data['bonusloto_start_url'] = '/usr/local/bin/wget ' . $wgetParam . ' -O - -q -t 1 &apos;' . HTTP_CATALOG .'index.php?route=module/lototron/start&action=start-' . $this->data['bonusloto_start_pass'] .'&apos;';
		}
		$this->data['stop_lototron'] = HTTP_CATALOG .'index.php?route=module/lototron/start&action=start-' . $this->data['bonusloto_start_pass'];


		if (isset($this->request->post['PeriodicCronValue'])) {
			$this->data['PeriodicCronValue'] = $this->request->post['PeriodicCronValue'];
		} else {
			$this->data['PeriodicCronValue'] = $this->config->get('PeriodicCronValue');
		} 

		if (isset($this->request->post['CronEnabled'])) {
			$this->data['CronEnabled'] = $this->request->post['CronEnabled'];
		} else {
			$this->data['CronEnabled'] = $this->config->get('CronEnabled');
		} 

		if (isset($this->request->post['bonusloto_cron_log'])) {
			$this->data['LogEnabled'] = $this->request->post['bonusloto_cron_log'];
		} elseif ($this->config->get('bonusloto_cron_log')) { 
			$this->data['LogEnabled'] = $this->config->get('bonusloto_cron_log');
		}

		if (isset($this->request->post['bonusloto_headline_chars'])) {
			$this->data['bonusloto_headline_chars'] = $this->request->post['bonusloto_headline_chars'];
		} else {
			$this->data['bonusloto_headline_chars'] = $this->config->get('bonusloto_headline_chars');
		} 

		if (isset($this->request->post['bonusloto_images_countdown'])) {
			$this->data['bonusloto_images_countdown'] = $this->request->post['bonusloto_images_countdown'];
		} else {
			$this->data['bonusloto_images_countdown'] = $this->config->get('bonusloto_images_countdown');
		} 

		if (isset($this->request->post['bonusloto_posting'])) {
			$this->data['bonusloto_posting'] = $this->request->post['bonusloto_posting'];
		} else {
			$this->data['bonusloto_posting'] = $this->config->get('bonusloto_posting');
		} 

		$this->load->model('tool/image');

		if (isset($this->request->post['bonusloto_background_img'])) {
			$bonusloto_background_img = $this->request->post['bonusloto_background_img'];
		} else {
			$bonusloto_background_img = $this->config->get('bonusloto_background_img');
		}

		$this->data['bonusloto_background_img'] = array();

		if (($bonusloto_background_img['image']) && file_exists(DIR_IMAGE . $bonusloto_background_img['image'])) {
			$image = $bonusloto_background_img['image'];
		} else {
			$image = 'no_image.jpg';
		}			
		$this->data['bonusloto_background_img'] = array(
			'image'                    => $image,
			'thumb'                    => $this->model_tool_image->resize($image, 100, 100)
		);	


		$this->data['no_image'] = $this->model_tool_image->resize('no_image.jpg', 100, 100);		


		if (isset($this->request->post['bonusloto_posting_time'])) {
			$this->data['bonusloto_posting_time'] = $this->request->post['bonusloto_posting_time'];
		} else {
			$this->data['bonusloto_posting_time'] = $this->config->get('bonusloto_posting_time');
		} 

		if (isset($this->request->post['bonusloto_social_background'])) {
			$this->data['bonusloto_social_background'] = $this->request->post['bonusloto_social_background'];
		} else {
			$this->data['bonusloto_social_background'] = $this->config->get('bonusloto_social_background');
		} 

		if (isset($this->request->post['bonusloto_custom_style'])) {
			$this->data['bonusloto_custom_style'] = $this->request->post['bonusloto_custom_style'];
		} else {
			$this->data['bonusloto_custom_style'] = $this->config->get('bonusloto_custom_style');
		} 

		if (isset($this->request->post['bonusloto_rotater_time'])) {
			$this->data['bonusloto_rotater_time'] = $this->request->post['bonusloto_rotater_time'];
		} else {
			$this->data['bonusloto_rotater_time'] = $this->config->get('bonusloto_rotater_time');
		} 


		if (isset($this->request->post['bonusloto_social_color_title'])) {
			$this->data['bonusloto_social_color_title'] = $this->request->post['bonusloto_social_color_title'];
		} else {
			$this->data['bonusloto_social_color_title'] = $this->config->get('bonusloto_social_color_title');
		} 

		if (isset($this->request->post['bonusloto_social_border_color'])) {
			$this->data['bonusloto_social_border_color'] = $this->request->post['bonusloto_social_border_color'];
		} else {
			$this->data['bonusloto_social_border_color'] = $this->config->get('bonusloto_social_border_color');
		} 

		if (isset($this->request->post['bonusloto_posting_vk'])) {
			$this->data['bonusloto_posting_vk'] = $this->request->post['bonusloto_posting_vk'];
		} else {
			$this->data['bonusloto_posting_vk'] = $this->config->get('bonusloto_posting_vk');
		} 

		if (isset($this->request->post['bonusloto_posting_odk'])) {
			$this->data['bonusloto_posting_odk'] = $this->request->post['bonusloto_posting_odk'];
		} else {
			$this->data['bonusloto_posting_odk'] = $this->config->get('bonusloto_posting_odk');
		} 

		if (isset($this->request->post['bonusloto_posting_fb'])) {
			$this->data['bonusloto_posting_fb'] = $this->request->post['bonusloto_posting_fb'];
		} else {
			$this->data['bonusloto_posting_fb'] = $this->config->get('bonusloto_posting_fb');
		} 

		if (isset($this->request->post['bonusloto_posting_tw'])) {
			$this->data['bonusloto_posting_tw'] = $this->request->post['bonusloto_posting_tw'];
		} else {
			$this->data['bonusloto_posting_tw'] = $this->config->get('bonusloto_posting_tw');
		} 

		if (isset($this->request->post['bonusloto_posting_mail'])) {
			$this->data['bonusloto_posting_mail'] = $this->request->post['bonusloto_posting_mail'];
		} else {
			$this->data['bonusloto_posting_mail'] = $this->config->get('bonusloto_posting_mail');
		} 

		if (isset($this->request->post['bonusloto_posting_ya'])) {
			$this->data['bonusloto_posting_ya'] = $this->request->post['bonusloto_posting_ya'];
		} else {
			$this->data['bonusloto_posting_ya'] = $this->config->get('bonusloto_posting_ya');
		} 

		if (isset($this->request->post['bonusloto_posting_points'])) {
			$this->data['bonusloto_posting_points'] = $this->request->post['bonusloto_posting_points'];
		} else {
			$this->data['bonusloto_posting_points'] = $this->config->get('bonusloto_posting_points');
		} 

		if (isset($this->request->post['bonusloto_gamer_count'])) {
			$this->data['bonusloto_gamer_count'] = $this->request->post['bonusloto_gamer_count'];
		} else {
			$this->data['bonusloto_gamer_count'] = $this->config->get('bonusloto_gamer_count');
		} 

		if (isset($this->request->post['bonusloto_count_background'])) {
			$this->data['bonusloto_count_background'] = $this->request->post['bonusloto_count_background'];
		} else {
			$this->data['bonusloto_count_background'] = $this->config->get('bonusloto_count_background');
		}

		if (isset($this->request->post['bonusloto_greeting_text'])) {
			$this->data['bonusloto_greeting_text'] = $this->request->post['bonusloto_greeting_text'];
		} else {
			$this->data['bonusloto_greeting_text'] = $this->config->get('bonusloto_greeting_text');
		}

		if (isset($this->request->post['bonusloto_start_lototron'])) {
			$this->data['bonusloto_start_lototron'] = $this->request->post['bonusloto_start_lototron'];
		} else {
			$this->data['bonusloto_start_lototron'] = $this->config->get('bonusloto_start_lototron');
		}	
		if (isset($this->request->post['bonusloto_display'])) {
			$this->data['bonusloto_display'] = $this->request->post['bonusloto_display'];
		} else {
			$this->data['bonusloto_display'] = $this->config->get('bonusloto_display');
		}	

		if (isset($this->request->post['bonusloto_status'])) {
			$this->data['bonusloto_status'] = $this->request->post['bonusloto_status'];
		} else {
			$this->data['bonusloto_status'] = $this->config->get('bonusloto_status');
		}
		
		if (isset($this->request->post['bonusloto_sort_order'])) {
			$this->data['bonusloto_sort_order'] = $this->request->post['bonusloto_sort_order'];
		} else {
	


		$this->data['bonusloto_sort_order'] = $this->config->get('bonusloto_sort_order');
		}				

		$this->data['modules'] = array();

		if (isset($this->request->post['bonusloto_module'])) {
			$this->data['modules'] = $this->request->post['bonusloto_module'];
		} elseif ($this->config->get('bonusloto_module')) { 
			$this->data['modules'] = $this->config->get('bonusloto_module');
		}	

		
		$this->template = 'module/bonusloto.tpl';
		$this->children = array(
			'common/header',	
			'common/footer'	
		);
		
		$this->getResponse()->setOutput($this->render(TRUE), $this->config->get('config_compression'));
	}
	
	private function validate() {
		if (!$this->user->hasPermission('modify', 'module/bonusloto')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (isset($this->request->post['bonusloto_game'])) {
			foreach ($this->request->post['bonusloto_game'] as $key => $value) {
				if (!$value['game_data'] || !$value['game_time'] || !$value['game_prize_name'] || !$value['game_code']) {
					$this->error['warning'] = $this->language->get('error_warning');
				}			
			}
		}

		if (isset($this->request->post['bonusloto_rotater_time'])) {
			if ($this->request->post['bonusloto_rotater_time'] < '90' ) {
				$this->error['warning'] = $this->language->get('error_warning');
			}			
		}


		if (!$this->error) {
			return TRUE;
		} else {
			return FALSE;
		}	
	}

	private function BonuslotoVipProduct() {
		$this->load->language('module/bonusloto');
		if (isset($this->request->post['bonusloto_game'])) {
			foreach ($this->request->post['bonusloto_game'] as $key => $value) {
				if ((isset($value['game_status_vip'])) and (isset($value['status']))){
					$this->load->model('localisation/language');
					$languages = $this->model_localisation_language->getLanguages();
					$this->load->model('setting/store');
					$stores = $this->model_setting_store->getStores();
					$product_store = array(0);
					foreach ($stores as $store) { 
						$product_store[] = $store['store_id'];
					}
					$this->load->model('catalog/product');
					if($this->model_catalog_product->getProduct($value['game_vip_id'])) {
						$this->vip = array(
							'quantity' => $value['game_vip_count'],
							'price' => $value['game_vip_buy_val']
						);
						$this->model_fido_bonusloto->editProduct($value['game_vip_id'],$this->vip);
					} else {
						$this->load->model('fido/bonusloto');
						if ($value['game_requir'] == '1'){
							$game_requir = html_entity_decode(sprintf($this->language->get('text_game_vip_d_requir'),$this->language->get('text_game_vip_d_requir_cash'), $this->language->get('text_game_vip_dd_requir_min'), $value['game_requir_val'],$this->language->get('text_game_vip_dd_cash')), ENT_QUOTES, 'UTF-8');
						}

						if ($value['game_requir'] == '2'){
							$game_requir = html_entity_decode(sprintf($this->language->get('text_game_vip_d_requir'),$this->language->get('text_game_vip_d_requir_point'),$this->language->get('text_game_vip_dd_requir_min'), $value['game_requir_val'],$this->language->get('text_game_vip_dd_point')), ENT_QUOTES, 'UTF-8');
						}
	
						if ($value['game_requir'] == '3'){
							$game_requir = html_entity_decode(sprintf($this->language->get('text_game_vip_d_requir'),$this->language->get('text_game_vip_d_requir_product'),$this->language->get('text_game_vip_dd_requir_min'), $value['game_requir_val'],$this->language->get('text_game_vip_dd_product')), ENT_QUOTES, 'UTF-8');
						}

						if ($value['game_requir'] == '4'){
							$game_requir = html_entity_decode(sprintf($this->language->get('text_game_vip_d_requir'),$this->language->get('text_game_vip_d_requir_post'),$this->language->get('text_game_vip_dd_requir_min'), $value['game_requir_val'],$this->language->get('text_game_vip_dd_post')), ENT_QUOTES, 'UTF-8');
						}
						if ($value['game_requir'] == '5'){
							$game_requir = html_entity_decode(sprintf($this->language->get('text_game_vip_d_requir'),$this->language->get('text_game_vip_d_requir_point_buy'),'', $value['game_requir_val'],$this->language->get('text_game_point')), ENT_QUOTES, 'UTF-8');
						}

   						foreach ($languages as $language) { 
							$product_description[$language['language_id']] = array(
								'name' => sprintf($this->language->get('text_game_vip_name'),$value['game_data'],$value['game_time']),
								'meta_description' => sprintf($this->language->get('text_game_vip_meta_description'),$value['game_data'],$value['game_time']),
								'meta_keyword' =>'',
								'description' => $game_requir,
								'tag' =>''						
							);
						}
						$this->vip = array(
							'model' => "bonusloto",
							'sku' => "",
							'upc' => "",
							'ean' => "",
							'jan' => "",
							'isbn' => "",
							'mpn' => "",
							'minimum' => "1",
							'location' => "",
							'quantity' => $value['game_vip_count'],
							'subtract' => "1",
							'stock_status_id' => "7",
							'date_available' => date('Y-m-d', time() - 86400),
							'manufacturer_id' => "0",
							'shipping' => "0",
							'price' => $value['game_vip_buy_val'],
							'points' => "0",
							'weight' => "0",
							'weight_class_id' => "0",
							'length' => "0",
							'width' => "0",
							'height' => "0",
							'length_class_id' => "0",
							'status' => "1",
							'tax_class_id' => "0",
							'sort_order' => "0",
							'image' => "data/vip-ticket.png",
							'product_description' => $product_description,
							'product_store' => $product_store,
							'keyword' => "bonusloto" . $key
						);
						$this->request->post['bonusloto_game'][$key]['game_vip_id'] = $this->model_fido_bonusloto->addProduct($this->vip);
					}	
				} else {
					if ((isset($value['game_vip_id'])) and ($value['game_vip_id'] !='')) {
						$this->load->model('catalog/product');
						$this->model_catalog_product->deleteProduct($value['game_vip_id']);
					}
					unset($this->request->post['bonusloto_game'][$key]['game_status_vip']);
					unset($this->request->post['bonusloto_game'][$key]['game_vip_id']);
					unset($this->request->post['bonusloto_game'][$key]['game_vip_count']);
					unset($this->request->post['bonusloto_game'][$key]['game_vip_buy_val']);
				}
			}
		}
	}

	private function saveCron() {
		$this->load->language('module/bonusloto');
		$cronData = array(
			'CronEnabled' 		=> (isset($this->request->post['CronEnabled']) ? $this->request->post['CronEnabled'] : ''),
			'PeriodicCronValue' 	=> $this->request->post['PeriodicCronValue'],
			'SecritCode' 		=> $this->request->post['bonusloto_start_pass'],
			'LogEnabler'		=> (isset($this->request->post['bonusloto_cron_log']) ? $this->request->post['bonusloto_cron_log'] : ''),
			'WgetPath'		=> $this->request->post['bonusloto_cron_wget_path']
		);
		$this->model_fido_bonusloto->BonuslotoSaveCron($cronData);
	}

	public function testcron() {
	        if (function_exists('shell_exec') && trim(shell_exec('echo EXEC')) == 'EXEC') {
        	    $this->data['shell_exec_status'] = 'Enabled';
	        } else {
        	    $this->data['shell_exec_status'] = 'Disabled';
	        }
        	if ($this->data['shell_exec_status'] == 'Enabled') {
		   $cronFolder = dirname(DIR_APPLICATION) . '/cron/bonusloto/';
	           if (shell_exec('crontab -l')) {
	                $this->data['cronjob_status']    = 'Enabled';
	                $curentCronjobs                  = shell_exec('crontab -l');
	                $this->data['current_cron_jobs'] = explode(PHP_EOL, $curentCronjobs);
	                file_put_contents($cronFolder . 'cron.txt', '* * * * * echo "test" ' . PHP_EOL);
	           } else {
			file_put_contents($cronFolder . 'cron.txt', '* * * * * echo "test" ' . PHP_EOL);
	                if (file_exists($cronFolder . 'cron.txt')) {
	                    exec('crontab ' . $cronFolder . 'cron.txt');
        		            if (shell_exec('crontab -l')) {
		                        $this->data['cronjob_status'] = 'Enabled';
		                        shell_exec('crontab -r');
                		    } else {
		                        $this->data['cronjob_status'] = 'Disabled';
		                    }
	                }
	           }
	           if (file_exists($cronFolder . 'cron.txt')) {
	                $this->data['folder_permission'] = "Writable";
	                unlink($cronFolder . 'cron.txt');
	           } else {
	                $this->data['folder_permission'] = "Unwritable";
	           }
	        }
	        $this->data['cron_folder'] = $cronFolder;
	        $this->template            = 'module/bonusloto_test_cron.tpl';
	        $this->getResponse()->setOutput($this->render());
	}

	public function cronlogview() {		
		$this->language->load('tool/error_log');
		$this->load->language('module/bonusloto');
		$cronFolder = dirname(DIR_APPLICATION) . '/cron/bonusloto/';
		$this->document->setTitle($this->language->get('heading_title_cronlog'));
		$this->data['heading_title'] = $this->language->get('heading_title_cronlog');
		$this->data['button_clear'] = $this->language->get('button_clear');
		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}
		$this->data['breadcrumbs'] = array();
		$this->data['clear'] = $this->url->link('module/bonusloto/clearcronlog', 'token=' . $this->session->data['token'], 'SSL');
		$file = $cronFolder . 'cronbonusloto_'.$this->config->get('bonusloto_start_pass') . '.log';
		if (file_exists($file)) {
			$this->data['log'] = file_get_contents($file, FILE_USE_INCLUDE_PATH, null);
		} else {
			$this->data['log'] = '';
		}
		$this->template = 'tool/error_log.tpl';
		$this->data['header'] = '';
		$this->data['footer'] = '';
		$this->getResponse()->setOutput($this->render());
	}

	public function clearcronlog() {
		$this->language->load('tool/error_log');
		$this->load->language('module/bonusloto');
		$cronFolder = dirname(DIR_APPLICATION) . '/cron/bonusloto/';
		$file = $cronFolder . 'cronbonusloto_'.$this->config->get('bonusloto_start_pass') . '.log';
		$handle = fopen($file, 'w+'); 
		fclose($handle); 			
		$this->session->data['success'] = $this->language->get('text_success_clear');
		$this->redirect($this->url->link('module/bonusloto/cronlogview', 'token=' . $this->session->data['token'], 'SSL'));		
	}

	private function validateDelete() {
		if (!$this->user->hasPermission('modify', 'module/bonusloto')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		if (!$this->error) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	public function autocompleteCoupon() {
		$json = array();

		if (isset($this->request->get['filter_name'])) {
			$this->load->model('fido/bonusloto');

			if (isset($this->request->get['filter_name'])) {
				$filter_name = $this->request->get['filter_name'];
			} else {
				$filter_name = '';
			}

			if (isset($this->request->get['limit'])) {
				$limit = $this->request->get['limit'];	
			} else {
				$limit = 20;	
			}			

			$data = array(
				'filter_name'  => $filter_name,
				'start'        => 0,
				'limit'        => $limit
			);

			$results = $this->model_fido_bonusloto->getCoupons($data);


			foreach ($results as $result) {

				$json[] = array(
					'coupon_id'  => $result['coupon_id'],
					'name'       => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
					'code'       => $result['code'],
					'discount'   => $result['discount']
				);
			}
		}
		$this->getResponse()->setOutput(json_encode($json));
	}
}
?>
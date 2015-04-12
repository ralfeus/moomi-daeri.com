<?php
class ControllerInformationBonusloto extends Controller {
	public function index() {
    	$this->load->language('information/bonusloto');
		$this->load->model('fido/bonusloto');
		$this->load->model('catalog/information');

		$this->data['breadcrumbs'] = array();
		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),
			'separator' => false
		);
		$parts = array();
		$parts = array(
			'appme' 	=> 'appme',
			'rules' 	=> 'rules',
			'whyiswhy' 	=> 'whyiswhy'
		);

		foreach ($parts as $part) {
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "url_alias WHERE keyword = '" . $this->db->escape($part) . "'");
			if ($query->num_rows) {
				$url = explode('=', $query->row['query']);
		     		$this->data[$part] = $url[1];
			}

		}
		$this->data['styles'] = array();
		$this->data['styles'][] = array(
					'style_tg'	=>	'.social_list_title',
					'param'		=>	'padding: 7px 12px 5px 12px!important;'
		);	


		$bonusloto_background_img = $this->config->get('bonusloto_background_img');
		$this->load->model('tool/image');
		$this->data['bonusloto_background_img'] = array();
		if ($bonusloto_background_img['image'] != 'no_image.jpg' && file_exists(DIR_IMAGE . $bonusloto_background_img['image'])) {
			$image = $bonusloto_background_img['image'];
			$this->data['styles'][] = array(
					'style_tg'	=>	'#lott-timer-box',
					'param'		=>	'background-image: url('.$this->model_tool_image->resize($image, 500, 220).');background-repeat: no-repeat;'
			);	
		}			

		if ($this->config->get('bonusloto_images_countdown') != '') {
			$images_countWH = explode("|" , $this->config->get('bonusloto_images_countdown'));
			if ($images_countWH[1] == '53x77') {
				$this->data['styles'][] = array(
					'style_tg'	=>	'#lott-timer-box',
					'param'		=>	'padding-top:20px!important;padding-right:0px!important;padding-left:0px!important;padding-bottom:0px!important;'
				);	
				$this->data['styles'][] = array(
					'style_tg'	=>	'ul.desc li',
					'param'		=>	'margin-left:0px;!important;margin-right:6px;!important;padding-left:26px!important;'
				);	
			}
		}
		if ($this->config->get('bonusloto_count_background') != '') {
				$this->data['styles'][] = array(
					'style_tg'	=>	'#lott-timer-box',
					'param'		=>	'background-color:#'.$this->config->get('bonusloto_count_background').'!important;'
				);	
		}

		if ($this->config->get('bonusloto_social_background') != '') {
				$this->data['styles'][] = array(
					'style_tg'	=>	'.social_list_title',
					'param'		=>	'background:#'.$this->config->get('bonusloto_social_background').'!important;padding: 7px 12px 5px 12px!important;'
				);	
		}

		if ($this->config->get('bonusloto_social_color_title') != '') {
				$this->data['styles'][] = array(
					'style_tg'	=>	'.social_list_title',
					'param'		=>	'color:#'.$this->config->get('bonusloto_social_color_title').'!important;'
				);	
		}

		if ($this->config->get('bonusloto_social_border_color') != '') {
				$this->data['styles'][] = array(
					'style_tg'	=>	'.social_list',
					'param'		=>	'border:1px solid #'.$this->config->get('bonusloto_social_border_color').'!important;'
				);	
		}

		$this->data['custom_styles'] = array();
		if ($this->config->get('bonusloto_custom_style') != '') {
				$this->data['custom_styles'][] = array(
					'style'	=>	$this->config->get('bonusloto_custom_style')
				);	
		}

		$this->data['posting_social_button'] = ',';
		$this->data['posting_social_button'] .= ',' . $this->config->get('bonusloto_posting_vk');
		$this->data['posting_social_button'] .= ',' . $this->config->get('bonusloto_posting_odk');
		$this->data['posting_social_button'] .= ',' . $this->config->get('bonusloto_posting_fb');
		$this->data['posting_social_button'] .= ',' . $this->config->get('bonusloto_posting_tw');
		$this->data['posting_social_button'] .= ',' . $this->config->get('bonusloto_posting_mail');
		$this->data['posting_social_button'] .= ',' . $this->config->get('bonusloto_posting_ya');
		$this->data['posting_social_button'] = trim ($this->data['posting_social_button'],",");

		$this->data['text_info_posting'] = $this->language->get('text_info_posting');
		$this->data['text_info_posted'] = $this->language->get('text_info_posting');

		$this->data['text_game_requir'] = $this->language->get('text_game_requir');
		$this->data['text_game_requir_not'] = $this->language->get('text_game_requir_not');
		$this->data['text_game_requir_cash'] = $this->language->get('text_game_requir_cash');
		$this->data['text_game_requir_point'] = $this->language->get('text_game_requir_point');
		$this->data['text_game_requir_product'] = $this->language->get('text_game_requir_product');
		$this->data['text_game_requir_post'] = $this->language->get('text_game_requir_post');
		$this->data['text_game_requir_point_buy'] = $this->language->get('text_game_requir_point_buy');

		$this->data['setPoints_url'] = $this->url->link('information/bonusloto/addPointsPost');
		$this->data['posting_points'] ='';
		$this->data['setpoints'] = '';
		$this->data['time_post_again'] = 86400;
		if ($this->config->get('bonusloto_posting') == '1') {
				$this->data['text_info_posting'] = sprintf($this->language->get('text_info_posting_points'),$this->config->get('bonusloto_posting_points'));
				if ($this->config->get('bonusloto_posting_time') == '') { 
					$time_post_again = '1';
				} else { 
					$time_post_again = $this->config->get('bonusloto_posting_time');
				}
				$this->data['time_post_again'] = $time_post_again*86400;
				$this->data['setpoints'] = 'setPoints(\''.$this->customer->isLogged().'\',\''.md5(md5($this->customer->getEmail())).'\','.$this->data['time_post_again'].');';
				$this->data['posting_points'] = $this->config->get('bonusloto_posting_points');
		}
		$bonusloto_game = $this->config->get('bonusloto_game');
		$this->data['games_list'] = $this->language->get('text_bonusloto_point_text_1');
		$count_game = 0;
		$game_requir='';
	if ((isset($bonusloto_game)) and ($bonusloto_game !=0)) {
		foreach ($bonusloto_game as $key => $game) { 
			if (isset($game['status']) and ($game['status'] =='on')){
				if (isset($game['game_requir'])){

				if ($game['game_requir'] == '0'){
					$game_requir = $this->language->get('text_game_requir_not');
				}
				if ($game['game_requir'] == '1'){
					$game_requir = html_entity_decode(sprintf($this->language->get('text_game_requir'),$this->language->get('text_game_requir_cash'), $this->language->get('text_game_requir_min'), $game['game_requir_val'],$this->language->get('text_game_cash')), ENT_QUOTES, 'UTF-8');
				}
				if ($game['game_requir'] == '2'){
					$game_requir = html_entity_decode(sprintf($this->language->get('text_game_requir'),$this->language->get('text_game_requir_point'),$this->language->get('text_game_requir_min'), $game['game_requir_val'],$this->language->get('text_game_point')), ENT_QUOTES, 'UTF-8');
				}
				if ($game['game_requir'] == '3'){
					$game_requir = html_entity_decode(sprintf($this->language->get('text_game_requir'),$this->language->get('text_game_requir_product'),$this->language->get('text_game_requir_min'), $game['game_requir_val'],$this->language->get('text_game_product')), ENT_QUOTES, 'UTF-8');
				}
				if ($game['game_requir'] == '4'){
					$game_requir = html_entity_decode(sprintf($this->language->get('text_game_requir'),$this->language->get('text_game_requir_post'),$this->language->get('text_game_requir_min'), $game['game_requir_val'],$this->language->get('text_game_post')), ENT_QUOTES, 'UTF-8');
				}
				if ($game['game_requir'] == '5'){
					$game_requir = html_entity_decode(sprintf($this->language->get('text_game_requir'),$this->language->get('text_game_requir_point_buy'),'', $game['game_requir_val'],$this->language->get('text_game_point')), ENT_QUOTES, 'UTF-8');
				}
				} else {
					$game_requir = $this->language->get('text_game_requir_not');
				}
				if ($game['game_type'] == '1'){
					$this->data['prize'] = '"' . $game['game_prize_name'] .'"';
				}
				if ($game['game_type'] == '2'){
					$this->data['prize'] = html_entity_decode('"' . $game['game_prize_name'] . ' ' . $this->language->get('text_bonusloto_point_text') .'"', ENT_QUOTES, 'UTF-8');
				}
				if ($game['game_type'] == '3'){
					$this->data['prize'] = html_entity_decode('"<a class="lott-link-description" href="' . $this->url->link('product/product&product_id=' . $game['game_prize_id']) . '">' . $game['game_prize_name'].'</a>"', ENT_QUOTES, 'UTF-8');
				}
				if ($game['game_type'] == '4'){
					$this->data['prize'] = $game['game_prize_name'];
				}
				if (isset($game['game_status_vip']) and ($game['game_status_vip'] =='on')){
					$vip = html_entity_decode('<a class="lott-vip-buy1" href="' . $this->url->link('product/product&product_id=' . $game['game_vip_id']) . '">' . $this->language->get('text_bonusloto_games_vip_buy').'</a>', ENT_QUOTES, 'UTF-8');
				} else {
					$vip = $this->language->get('text_bonusloto_games_vip_not');
				}
				$this->data['games_list'] .= html_entity_decode(sprintf($this->language->get('text_bonusloto_point_text_2'), $game['game_data'] , $game['game_time'], $this->config->get('bonusloto_timezone'), $this->data['prize'], $game_requir, $vip));
				$count_game +=1;
			}
		}
	}	
		$this->data['games_list'] .= html_entity_decode($this->language->get('text_bonusloto_point_text_3'));		

		if ($count_game == 0) {
			$this->data['games_list'] = $this->language->get('text_error');
		}
		if (isset($this->request->get['bonusloto_id'])) {
			$bonusloto_id = $this->request->get['bonusloto_id'];
		} else {
			$bonusloto_id = 0;
		}

		$bonusloto_info = $this->model_fido_bonusloto->getbonuslotoStory($bonusloto_id);
		if ($bonusloto_info) {
	  		$this->document->setTitle($bonusloto_info['title']);
     		$this->data['breadcrumbs'][] = array(
        		'href'      => $this->url->link('information/bonusloto'),
        		'text'      => $this->language->get('heading_title'),
        		'separator' => $this->language->get('text_separator')
     		);
     		$this->data['breadcrumbs'][] = array(
        		'href'      => $this->url->link('information/bonusloto&bonusloto_id=' . $this->request->get['bonusloto_id']),
        		'text'      => $bonusloto_info['title'],
        		'separator' => $this->language->get('text_separator')
     		);
     		$this->data['bonusloto_info'] = $bonusloto_info;
     		$this->data['heading_title'] = $bonusloto_info['title'];
		$this->data['description'] = html_entity_decode($bonusloto_info['description'], ENT_QUOTES, 'UTF-8');
     		$this->data['button_bonusloto'] = $this->language->get('button_bonusloto');
		$this->data['bonusloto'] = $this->url->link('information/bonusloto');
     		$this->data['button_back'] = $this->language->get('button_back');
		$this->data['back'] = $this->url->link('information/bonusloto');

			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/information/bonusloto.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/information/bonusloto.tpl';
			} else {
				$this->template = 'default/template/information/bonusloto.tpl';
			}
			$this->children = array(
				'common/column_left',
				'common/column_right',
				'common/content_top',
				'common/content_bottom',
				'common/footer',
				'common/header'
			);
			$this->getResponse()->setOutput($this->render(TRUE), $this->config->get('config_compression'));
	  	} else {
	  		$bonusloto_data = $this->model_fido_bonusloto->getbonusloto();
	  		if ($bonusloto_data) {
				foreach ($bonusloto_data as $result) {
				if ($this->config->get('bonusloto_headline_chars') ==''){
					$cut_descr_symbols = 50;
				} else {
					$cut_descr_symbols = $this->config->get('bonusloto_headline_chars');
				}
				$descr_plaintext = strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'));
				if ( mb_strlen($descr_plaintext, 'UTF-8') > $cut_descr_symbols )	{
					$descr_plaintext = mb_substr($descr_plaintext, 0, $cut_descr_symbols, 'UTF-8');
				}
					$this->data['bonusloto_data'][] = array(
						'title'        => $result['title'],
						'description'  => $descr_plaintext,
						'href'         => $this->url->link('information/bonusloto&bonusloto_id=' . $result['bonusloto_id']),
						'date_added'   => date($this->language->get('date_format_short'), strtotime($result['date_added']))
					);
				}
				$this->document->setTitle($this->language->get('heading_title'));
				$this->data['breadcrumbs'][] = array(
					'href'      => $this->url->link('information/bonusloto'),
					'text'      => $this->language->get('heading_title'),
					'separator' => $this->language->get('text_separator')
				);
				$this->data['heading_title'] = $this->language->get('heading_title');
				$this->data['text_read_more'] = $this->language->get('text_read_more');
				$this->data['text_date_added'] = $this->language->get('text_date_added');
				$this->data['text_lott_timer_title'] = $this->language->get('text_lott_timer_title');
				$this->data['text_lott_priz_title'] = $this->language->get('text_lott_priz_title');
				$this->data['text_user_list_title'] = $this->language->get('text_user_list_title');
				$this->data['text_info_title'] = $this->language->get('text_info_title');
				$this->data['text_info_menu_1'] = $this->language->get('text_info_menu_1');
				$this->data['text_info_menu_2'] = $this->language->get('text_info_menu_2');
				$this->data['text_info_menu_3'] = $this->language->get('text_info_menu_3');
				$this->data['text_info_menu_4'] = $this->language->get('text_info_menu_4');
				$this->data['text_lott_timer_day'] = $this->language->get('text_lott_timer_day');
				$this->data['text_lott_timer_houre'] = $this->language->get('text_lott_timer_houre');
				$this->data['text_lott_timer_minutes'] = $this->language->get('text_lott_timer_minutes');
				$this->data['text_lott_timer_seconds'] = $this->language->get('text_lott_timer_seconds');
				$this->data['button_continue'] = $this->language->get('button_continue');
				$this->data['continue'] = $this->url->link('common/home');
				if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/information/bonusloto.tpl')) {
					$this->template = $this->config->get('config_template') . '/template/information/bonusloto.tpl';
				} else {
					$this->template = 'default/template/information/bonusloto.tpl';
				}
			$this->children = array(
				'common/column_left',
				'common/column_right',
				'common/content_top',
				'common/content_bottom',
				'common/footer',
				'common/header'
			);
				$this->getResponse()->setOutput($this->render(TRUE), $this->config->get('config_compression'));
	    	} else {
		  		$this->document->setTitle($this->language->get('text_error'));
	     		$this->data['breadcrumbs'][] = array(
	        		'href'      => $this->url->link('information/bonusloto'),
	        		'text'      => $this->language->get('text_error'),
	        		'separator' => $this->language->get('text_separator')
	     		);
	     		$this->data['heading_title'] = $this->language->get('heading_title');
	     		$this->data['text_error'] = $this->language->get('text_error');
			$this->data['text_lott_timer_title'] = $this->language->get('text_lott_timer_title');
			$this->data['text_lott_priz_title'] = $this->language->get('text_lott_priz_title');
			$this->data['text_user_list_title'] = $this->language->get('text_user_list_title');
			$this->data['text_info_title'] = $this->language->get('text_info_title');
			$this->data['text_info_menu_1'] = $this->language->get('text_info_menu_1');
			$this->data['text_info_menu_2'] = $this->language->get('text_info_menu_2');
			$this->data['text_info_menu_3'] = $this->language->get('text_info_menu_3');
			$this->data['text_info_menu_4'] = $this->language->get('text_info_menu_4');
			$this->data['text_lott_timer_day'] = $this->language->get('text_lott_timer_day');
			$this->data['text_lott_timer_houre'] = $this->language->get('text_lott_timer_houre');
			$this->data['text_lott_timer_minutes'] = $this->language->get('text_lott_timer_minutes');
			$this->data['text_lott_timer_seconds'] = $this->language->get('text_lott_timer_seconds');
	     		$this->data['button_continue'] = $this->language->get('button_continue');
	     		$this->data['continue'] = $this->url->link('common/home');
				if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/information/bonusloto_not_found.tpl')) {
					$this->template = $this->config->get('config_template') . '/template/information/bonusloto_not_found.tpl';
				} else {
					$this->template = 'default/template/information/bonusloto_not_found.tpl';
				}
			$this->children = array(
				'common/column_left',
				'common/column_right',
				'common/content_top',
				'common/content_bottom',
				'common/footer',
				'common/header'
			);
				$this->getResponse()->setOutput($this->render(TRUE), $this->config->get('config_compression'));
		  	}
		}
	}

	public function addPointsPost() {
	if ($this->config->get('bonusloto_posting') == '1'){
	    	$this->load->language('information/bonusloto');
		$this->load->model('fido/bonusloto');
		if ($this->config->get('bonusloto_text_thanks_point') != '') {
			$this->data['text_thanks_point'] = sprintf($this->config->get('bonusloto_text_thanks_point'),$this->config->get('bonusloto_posting_points'));
		} else {
			$this->data['text_thanks_point'] = sprintf($this->language->get('text_thanks_point'),$this->config->get('bonusloto_posting_points'));
		}
		if ($this->config->get('bonusloto_posting_time') == '') { 
			$time_post_again = '1';
		} else { 
			$time_post_again = $this->config->get('bonusloto_posting_time');
		}
		if ($this->config->get('bonusloto_text_thanks_point_time') != '') {
			$this->data['text_thanks_point_time'] = sprintf($this->config->get('bonusloto_text_thanks_point_time'),$time_post_again);
		} else {
			$this->data['text_thanks_point_time'] = sprintf($this->language->get('text_thanks_point_time'),$time_post_again);
		}
		if (($this->request->server['REQUEST_METHOD'] == 'POST')) { 
			if(isset($_COOKIE['bl_hashid'])){
				if (($this->request->post['id'] != '') and ($this->customer->isLogged() == $this->request->post['id']) and ($_COOKIE['bl_hashid'] == md5(md5($this->customer->getEmail())))) {
					$last_post = $this->model_fido_bonusloto->getLastRewards($this->request->post['id']);
					if ($last_post !='') {
			$last_post = strtotime($last_post . ' +' . $time_post_again . ' day');
                	$datefixed = $last_post;
                	$datenow = date_create('now');
                	$timefixed = $last_post;
                	$timenow = strtotime($datenow->format('Y-m-d H:i:s'));
						if ($timefixed < $timenow ) {
							$this->model_fido_bonusloto->addReward($this->request->post['id'], 'Social post', $this->config->get('bonusloto_posting_points'));
							die($this->data['text_thanks_point']);
						} else {
							die($this->data['text_thanks_point_time']);
						}
					} else {
						$this->model_fido_bonusloto->addReward($this->request->post['id'], 'Social post', $this->config->get('bonusloto_posting_points'));
						die($this->data['text_thanks_point']);
					}
				} else {
					die($this->language->get('text_thanks_point_not_login'));
				}
			} else {
				die($this->language->get('text_thanks_point_not_login'));
			}
		} else {
			$this->data['success'] = '';
		}
	}
	}
}
?>
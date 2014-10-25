<?php
class ControllerModulelototron extends Controller {
	public function start() {

	function super_unique($array) {
		$result = array_map("unserialize", array_unique(array_map("serialize", $array)));
		foreach ($result as $key => $value) {
			if (is_array($value)) {
				$result[$key] = super_unique($value);
			}
		}
		return $result;
	}

	$this->load->language('module/bonusloto'); 
	if($this->config->get('bonusloto_timezone') !=''){
		date_default_timezone_set($this->config->get('bonusloto_timezone'));
	}
	$lottery['run'] = '0';
	$lottery['datetime'] = '01.01.1970 00:00:00';
	$lottery['description'] = '';
	$lottery['cupone'] = '';
	$lottery['pass'] = $this->config->get('bonusloto_start_pass');

	$bonusloto_game = array();
	$game_row = 0;
	$bonusloto_game = $this->config->get('bonusloto_game');

   if ((isset($bonusloto_game)) and ($bonusloto_game != 0 )) {
	foreach ($bonusloto_game as $key => $game) { 
		if (isset($game['status']) and ($game['status'] =='on')){
			if (isset($game['game_status_vip']) and ($game['game_status_vip'] =='on')){
				$var_start_time_data[1][] = array(
					'datetime' 	=> $game['game_data'] . ' ' .$game['game_time'],
					'type'		=> $game['game_type'],
					'description'	=> $game['game_prize_name'],
					'description_id'=> $game['game_prize_id'],
					'cupone'	=> $game['game_code'],
					'requir'	=> $game['game_requir'],
					'requir_val'	=> $game['game_requir_val'],
					'status_vip'	=> $game['game_status_vip'],
					'vip_count'	=> $game['game_vip_count'],
					'vip_buy_val'	=> $game['game_vip_buy_val'],
					'vip_id'	=> $game['game_vip_id']
				);
			} else {
				$var_start_time_data[1][] = array(
					'datetime' 	=> $game['game_data'] . ' ' .$game['game_time'],
					'type'		=> $game['game_type'],
					'description'	=> $game['game_prize_name'],
					'description_id'=> $game['game_prize_id'],
					'cupone'	=> $game['game_code'],
					'requir'	=> $game['game_requir'],
					'requir_val'	=> $game['game_requir_val']
				);
			}
		} else {
				$var_start_time_data[0][] = array(
				'datetime' 	=> $game['game_data'] . ' ' .$game['game_time'],
				'type'		=> $game['game_type'],
				'description'	=> $game['game_prize_name'],
				'description_id'=> $game['game_prize_id'],
				'cupone'	=> $game['game_code'],
				'requir'	=> $game['game_requir'],
				'requir_val'	=> $game['game_requir_val']
			);
		}

	}
   }
	if (isset($var_start_time_data[1])) {
		$var_games = $var_start_time_data[1][0];
		
		$lottery['run'] = '1';
		$lottery['datetime'] = $var_games['datetime'];
		$lottery['type'] = $var_games['type'];
		$lottery['requir'] = $var_games['requir'];
		$lottery['requir_val'] = $var_games['requir_val'];
		$vip_query = array();
		if (isset($var_games['status_vip']) and ($var_games['status_vip'] =='on')){
			$lottery['status_vip'] = 'on';
			$lottery['vip_id'] = $var_games['vip_id'];
			$lottery['vip'] = html_entity_decode('<a class="lott-vip-buy2" href="' . $this->url->link('product/product&product_id=' . $lottery['vip_id']) . '">' . $this->language->get('text_bonusloto_games_vip_buy').'</a>', ENT_QUOTES, 'UTF-8');
			$lottery['vip_count'] = $var_games['vip_count'];
			$vip_query = $this->db->query("SELECT c.customer_id, c.firstname, c.lastname, c.email, c.status, c.approved, c.date_added FROM `" . DB_PREFIX . "customer` AS c INNER JOIN `" . DB_PREFIX . "order` AS v ON c.customer_id = v.customer_id INNER JOIN `" . DB_PREFIX . "order_product` AS vp ON v.order_id = vp.order_id WHERE c.status = '1' AND c.approved = '1' AND v.order_status_id = '".$this->config->get('config_complete_status_id')."' AND vp.product_id = '" . $lottery['vip_id'] . "' GROUP BY c.customer_id");
		} else {
			$lottery['status_vip'] = 'off';
			$lottery['vip'] = $this->language->get('text_bonusloto_games_vip_not');
		}
		$lottery['description'] = '"' . $var_games['description'] .'"';
		$lottery['description_id'] = $var_games['description_id'];
		if ($var_games['type'] == '3'){
			$lottery['description'] = html_entity_decode('"<a class="lott-link-description" href="' . $this->url->link('product/product&product_id=' . $var_games['description_id']) . '">' . $var_games['description'].'</a>"', ENT_QUOTES, 'UTF-8');
		}
		if ($var_games['type'] == '2'){
			$lottery['description'] = html_entity_decode('"' . $var_games['description'] . ' ' . $this->language->get('text_bonusloto_point_text') .'"', ENT_QUOTES, 'UTF-8');
			$lottery['description_id'] = $var_games['description'];
		}
		$lottery['cupone'] = $var_games['cupone'];	
		$lottery['status'] = $this->config->get('bonusloto_status');

		if ($lottery['requir'] == '1' ) {
			$query = $this->db->query("SELECT c.customer_id, c.firstname, c.lastname, c.email, c.status, c.approved, c.date_added, SUM(o.total) FROM `" . DB_PREFIX . "customer` AS c INNER JOIN `" . DB_PREFIX . "order` AS o ON c.customer_id = o.customer_id WHERE c.status = '1' AND c.approved = '1' AND o.order_status_id = '".$this->config->get('config_complete_status_id')."' GROUP BY c.customer_id HAVING SUM(o.total) >= '" . $lottery['requir_val'] . "'");
		} elseif (($lottery['requir'] == '2') or ($lottery['requir'] == '5' )) {
			$query = $this->db->query("SELECT c.customer_id, c.firstname, c.lastname, c.email, c.status, c.approved, c.date_added, SUM(o.points) FROM `" . DB_PREFIX . "customer` AS c INNER JOIN `" . DB_PREFIX . "customer_reward` AS o ON c.customer_id = o.customer_id WHERE c.status = '1' AND c.approved = '1' GROUP BY c.customer_id HAVING SUM(o.points) >= '" . $lottery['requir_val'] . "'");
		} elseif ($lottery['requir'] == '3' ) {
			$query = $this->db->query("SELECT c.customer_id, c.firstname, c.lastname, c.email, c.status, c.approved, c.date_added, SUM(p.quantity) FROM `" . DB_PREFIX . "customer` AS c INNER JOIN `" . DB_PREFIX . "order` AS o ON c.customer_id = o.customer_id INNER JOIN `" . DB_PREFIX . "order_product` AS p ON o.order_id = p.order_id WHERE c.status = '1' AND c.approved = '1' AND o.order_status_id = '".$this->config->get('config_complete_status_id')."' GROUP BY c.customer_id HAVING SUM(p.quantity) >= '" . $lottery['requir_val'] . "'");
		} elseif ($lottery['requir'] == '4' ) {
			$query = $this->db->query("SELECT c.customer_id, c.firstname, c.lastname, c.email, c.status, c.approved, c.date_added, COUNT(o.status) FROM `" . DB_PREFIX . "customer` AS c INNER JOIN `" . DB_PREFIX . "review` AS o ON c.customer_id = o.customer_id WHERE c.status = '1' AND c.approved = '1' AND o.status = '1' GROUP BY c.customer_id HAVING COUNT(o.status) >= '" . $lottery['requir_val'] . "'");
		} else {
			$query = $this->db->query("SELECT c.customer_id, c.firstname, c.lastname, c.email, c.status, c.approved, c.date_added FROM `" . DB_PREFIX . "customer` AS c WHERE c.status = '1' and c.approved = '1'");
		}

		if ($lottery['requir'] != '0' ) {
			if ($lottery['status_vip'] == 'on') {
				if (count($vip_query->rows)>0) {
					if (count($query->rows)>0) {
						$query->rows = array_merge_recursive($query->rows,$vip_query->rows);
					} else {
						$query->rows = $vip_query->rows;
					}
				}
			}
		} else {
			if ($lottery['status_vip'] == 'on') {
				$query->rows = $vip_query->rows;
			}
		}
			if (count($query->rows)> 0) {
			foreach ($query->rows as $customer) {
				$this->data['customers'][] = array(
					'customer_id'    => $customer['customer_id'],
					'firstname'      => $customer['firstname'],
					'lastname'       => $customer['lastname'],
					'email'          => $customer['email'],
					'status'         => $customer['status'],
					'approved'       => $customer['approved'],
					'date_added'     => date('d.m.Y', strtotime($customer['date_added']))
				);
			}

			$customers = super_unique($this->data['customers']);
			$this->data['customers'] =array();
			foreach ($customers as $customer) {
				$this->data['customers'][] = $customer;
			}
		}

	} else {
		$lottery['run'] = '0';
	}


	if (isset($this->request->post['action'])) {
		$action = $this->request->post['action'];
	} elseif(isset($this->request->get['action'])){
		$action = $this->request->get['action'];
	} else {
		$action = ''; 
	}

	$json = (isset($_POST['json'])) ? json_decode($_POST['json'], true) : array();
	$jsonBox = array();
	$error = array();

    	if(!empty($action) && json_last_error() == JSON_ERROR_NONE) {
				$jsonBox['users_not'] = $this->language->get('text_bonusloto_games_users_not');
				$jsonBox['text_game1'] = $this->language->get('text_bonusloto_games1');
				$jsonBox['text_game2'] = $this->language->get('text_bonusloto_games2');
				$jsonBox['text_game3'] = $this->language->get('text_bonusloto_games3');
				$jsonBox['text_error1'] = $this->language->get('error_bonuslot_json_2');
				$jsonBox['text_error2'] = $this->language->get('error_bonuslot_json_7');
				$jsonBox['text_error3'] = $this->language->get('error_bonuslot_json_8');
        switch($action) {
        	case 'get-config':
			$jsonBox['lottery'] = 'off';
			if ($lottery['run'] == '0') {
        			$jsonBox['lottery'] = 'on';
			}
        		if(isset($this->data['customers'])) {
                		$blist = array();
                     		foreach($this->data['customers'] as $k => $user) {
					if($this->config->get('bonusloto_display') == 1) {
                         			$blist[$k]['name'] = $user['firstname'];
                         			$blist[$k]['uid'] = $user['email'];
					} elseif ($this->config->get('bonusloto_display') == 0) {
			 			$exp = explode('@',$user['email']);
			 			$blist[$k]['name'] = substr($user['email'],0,3).'...@'.$exp[1];
                         			$blist[$k]['uid'] = $user['email'];
					}
                     		}
/*
			} elseif ($lottery['status_vip'] == 'on') {
				$blist['1']['name'] = 'user list...';
				$blist['1']['uid'] = 'user list...';
*/
              		} else {
                  		//$error[] = $this->language->get('error_bonuslot_json_1');
				$blist['1']['name'] = 'user list...';
				$blist['1']['uid'] = 'user list...';
              		}
                	$datefixed = date_create($lottery['datetime']);
                	$datenow = date_create('now');
                	$timefixed = strtotime($datefixed->format('Y-m-d H:i:s'));
                	$timenow = strtotime($datenow->format('Y-m-d H:i:s'));
			if ($timefixed < $timenow || $lottery['status'] == '0' || $lottery['run'] == '0') {
                		$jsonBox['lottery'] = 'off';
                		$error[] = date('Y-m-d H:i:s') . ' - ' . $this->language->get('error_bonuslot_json_2');
                	}
              		if (count($error)) {
                		$jsonBox['errors'] = $error;
              		} else {
                		if ($timefixed > $timenow) {
		                	$diff = date_diff($datefixed, $datenow);
                			$days = $diff->days;
                			$times = $diff->format('%h:%i:%s');
                			$outdiff = $days.":".$times;
	                	} else {
        		        	$outdiff = "00:00:00:00";
                		}
                 		$jsonBox['start_time'] = $timefixed;
                 		$jsonBox['curr_time'] = $timenow;
                 		$jsonBox['difference'] = $outdiff;
                 		$jsonBox['user_list'] = $blist;
                 		$jsonBox['priz_desc'] = $lottery['description'];
                 		$jsonBox['vip'] = $lottery['vip'];
				$jsonBox['count_user_list'] = $this->config->get('bonusloto_gamer_count');
				if (($lottery['requir'] == '0' ) and ($lottery['status_vip'] == 'on')){
					if ($lottery['vip_count'] >= count($blist) ){
						$jsonBox['user_vip_only'] = '1';
						$jsonBox['user_vip_text'] = sprintf($this->language->get('text_bonusloto_gamer_users_count_requir'),$lottery['vip_count']);
					}
				}
				if ($this->config->get('bonusloto_images_countdown') != '') {
					$images_countWH = explode("|" , $this->config->get('bonusloto_images_countdown'));
	                 		$images_digitWH = explode("x" , $images_countWH[1]);
					$jsonBox['images_countdown'] = 'catalog/view/javascript/bonusloto/img/d/'.$images_countWH[0];
	                 		$jsonBox['images_digitWidth'] = (int)$images_digitWH[0];
	                 		$jsonBox['images_digitHeight'] = (int)$images_digitWH[1];
				} else {
	                 		$jsonBox['images_countdown'] = 'catalog/view/javascript/bonusloto/img/d/digits_transparent-w46.png';
	                 		$jsonBox['images_digitWidth'] = 46;
	                 		$jsonBox['images_digitHeight'] = 67;
				}
             		}
              		die(json_encode($jsonBox));
	        	break;
            	case 'synchronise-time':
                	$jsonBox['current_time'] = date('H:i:s');
             		die(json_encode($jsonBox));
            		break;
	    	case 'start-'. $lottery['pass']:
                	header("Content-Type: text/html; charset=utf-8");
			if (strtotime($lottery['datetime']) < time() && strtotime($lottery['datetime']) + 300 > time()) {
				$this->db->query("UPDATE `" . DB_PREFIX . "setting` SET  `value` = '1' WHERE `group` = 'bonusloto' AND `key` = 'bonusloto_start_lototron'");
                	}
			if (strtotime($lottery['datetime']) > time()) {
                     		die(date('Y-m-d H:i:s') . ' - ' . $this->language->get('error_bonuslot_json_3').' '.$lottery['datetime']);
                	}
			if (!isset($lottery['datetime']) || $lottery['datetime'] =='' || $this->config->get('bonusloto_start_lototron') == '0' || $lottery['run'] == '0') {
                     		die(date('Y-m-d H:i:s') . ' - ' . $this->language->get('error_bonuslot_json_5'));
                	}
              		if (isset($this->data['customers'])) {
                     		$list = array();
                     		foreach($this->data['customers'] as $k => $user) {
					if ($this->config->get('bonusloto_display') == 1) {
                         			$list[$k]['name'] = $user['firstname'];
                         			$list[$k]['uid'] = $user['email'];
			 			$list[$k]['customer_id'] = $user['customer_id'];
						if ($lottery['requir'] == '5' ) {
							$this->load->model('fido/bonusloto');
							$this->model_fido_bonusloto->addReward($list[$k]['customer_id'], $this->language->get('text_bonusloto_lottery_ticket_buy') . ':' . $lottery['datetime'], '-' . $lottery['requir_val']);
						}
					} elseif ($this->config->get('bonusloto_display') == 0) {
			 			$exp = explode('@',$user['email']);
			 			$list[$k]['name'] = substr($user['email'],0,3).'...@'.$exp[1];
                         			$list[$k]['uid'] = $user['email'];
			 			$list[$k]['customer_id'] = $user['customer_id'];
						if ($lottery['requir'] == '5' ) {
							$this->load->model('fido/bonusloto');
							$this->model_fido_bonusloto->addReward($list[$k]['customer_id'], $this->language->get('text_bonusloto_lottery_ticket_buy') . ':' . $lottery['datetime'], '-' . $lottery['requir_val']);
						}
					}
                     		}
              		} else {
                  		$error[] = date('Y-m-d H:i:s') . ' - ' . $this->language->get('error_bonuslot_json_4');
				$this->data['bonusloto_description'][] = array(
					'meta_description'      => $this->language->get('entry_bonuslot_meta_description_no'),
					'description'          	=> $this->language->get('entry_bonuslot_description_no'),
					'title'         	=> $this->language->get('entry_bonuslot_title').' '.date('d.m.Y', strtotime($lottery['datetime']))
				);
				$this->db->query("INSERT INTO " . DB_PREFIX . "bonusloto SET status = '1', date_added = now()");
				$bonusloto_id = $this->db->getLastId();
				foreach ($this->data['bonusloto_description'] as  $value) {
					$this->db->query("UPDATE `" . DB_PREFIX . "bonusloto_winner` SET `winner_last`='0' WHERE `winner_last`='1'");
					$this->db->query("INSERT INTO `" . DB_PREFIX . "bonusloto_winner` SET bonusloto_id = '" . (int)$bonusloto_id . "',`winner_id`='', `winner_name`='".$this->language->get('text_bonusloto_games_users_not')."',`winner_email`='', `winner_date`='".$lottery['datetime']."', `winner_bonus`='".$lottery['description']."|".$lottery['cupone']."',`winner_last`='1'");
					$this->db->query("INSERT INTO `" . DB_PREFIX . "bonusloto_description` SET bonusloto_id = '" . (int)$bonusloto_id . "', language_id = '".(int)$this->config->get('config_language_id')."', title = '" . $this->db->escape($value['title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', description = '" . $this->db->escape($value['description']) . "'");
					$this->db->query("INSERT INTO `" . DB_PREFIX . "bonusloto_to_store` SET bonusloto_id = '" . (int)$bonusloto_id . "', store_id = '" . (int)$this->config->get('config_store_id') . "'");
					if ($lottery['status_vip'] == 'on'){
						$this->load->model('fido/bonusloto');
						$this->model_fido_bonusloto->deleteVipProduct($lottery['vip_id']);
					}
				}
				$this->savegame($var_start_time_data);
              		}
              		if (count($error)) {
                   		$jsonBox['errors'] = $error;
              		} else {
				$shuffle_cnt = mt_rand (5, 25);
              			for ($i = 0; $i < $shuffle_cnt; $i++) { shuffle($list);}
              			$cnt = count($list)-1; 
              			$rand_number = mt_rand(0, $cnt); 
              			$winner = $list[$rand_number]; 
				$this->data['bonusloto_description'][] = array(
					'meta_description'      => $this->language->get('entry_bonuslot_meta_description').' '.$list[$rand_number]['name'],
					'description'          	=> sprintf($this->language->get('entry_bonuslot_description'), $list[$rand_number]['name'], $lottery['description']),
					'title'         	=> $this->language->get('entry_bonuslot_title').' '.date('d.m.Y', strtotime($lottery['datetime']))
				);
				$this->db->query("INSERT INTO " . DB_PREFIX . "bonusloto SET status = '1', date_added = now()");
				$bonusloto_id = $this->db->getLastId();
				foreach ($this->data['bonusloto_description'] as  $value) {
					$this->db->query("UPDATE `" . DB_PREFIX . "bonusloto_winner` SET `winner_last`='0' WHERE `winner_last`='1'");
					$this->db->query("INSERT INTO `" . DB_PREFIX . "bonusloto_winner` SET bonusloto_id = '" . (int)$bonusloto_id . "',`winner_id`='".$list[$rand_number]['customer_id']."', `winner_name`='".$list[$rand_number]['name']."',`winner_email`='".$list[$rand_number]['uid']."', `winner_date`='".$lottery['datetime']."', `winner_bonus`='".$lottery['description']."|".$lottery['cupone']."',`winner_last`='1'");
					$this->db->query("INSERT INTO `" . DB_PREFIX . "bonusloto_description` SET bonusloto_id = '" . (int)$bonusloto_id . "', language_id = '".(int)$this->config->get('config_language_id')."', title = '" . $this->db->escape($value['title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', description = '" . $this->db->escape($value['description']) . "'");
					$this->db->query("INSERT INTO `" . DB_PREFIX . "bonusloto_to_store` SET bonusloto_id = '" . (int)$bonusloto_id . "', store_id = '" . (int)$this->config->get('config_store_id') . "'");
					if ($lottery['type'] == '2'){
						$this->load->model('fido/bonusloto');
						$this->model_fido_bonusloto->addReward($list[$rand_number]['customer_id'], $this->language->get('text_bonusloto_point_description') . ':' . $lottery['datetime'], $lottery['description_id']);
					}
					if ($lottery['status_vip'] == 'on'){
						$this->load->model('fido/bonusloto');
						$this->model_fido_bonusloto->deleteVipProduct($lottery['vip_id']);
					}
				}
				$this->load->model('fido/bonuslotomail');
				$this->model_fido_bonuslotomail->sendBonuslotoMailWinner($bonusloto_id);
				$this->model_fido_bonuslotomail->sendBonuslotoMailAdmin($bonusloto_id);

				$this->savegame($var_start_time_data);

                	}
			die(date('Y-m-d H:i:s') . ' - ' . $this->language->get('error_bonuslot_json_6'));
                	break;
		case 'startlototron':
			$jsonBox['ok'] = true;
			break;
            	case 'checklototron':
                	$jsonBox['ok'] = true;
			if (isset($var_start_time_data[0]) && $this->config->get('bonusloto_start_lototron') == '0') {
				$last_game = array_pop($var_start_time_data[0]);
				$lottery['datetime'] = $last_game['datetime'];
				$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "bonusloto_winner` WHERE `winner_date`='".$lottery['datetime']."' AND `winner_last`='1'");
				foreach ($query->rows as $winner) {
					$this->data['winner'][] = array(
						'winner_id'    	 => $winner['winner_id'],
						'name'      	 => $winner['winner_name'],
						'email'          => $winner['winner_email'],
						'bonusloto_id'   => $winner['bonusloto_id']
					);
				}
			}
              		if (isset($this->data['winner'])) {
                        	$bwinner = array();
                     		foreach($this->data['winner'] as $k => $user) {
					if ($this->config->get('bonusloto_display') == 1) {
                         			$bwinner[$k]['name'] = $user['name'];
                         			$bwinner[$k]['uid'] = $user['winner_id'];
					} elseif ($this->config->get('bonusloto_display') == 0) {
			 			$exp = explode('@',$user['email']);
			 			$bwinner[$k]['name'] = substr($user['email'],0,3).'...@'.$exp[1];
                         			$bwinner[$k]['uid'] = $user['winner_id'];
					}
		     		}
                	} else {
                    		$jsonBox['ok'] = false;
                	}
                	if (count($error)) {
                    		$jsonBox['errors'] = $error;
                	} else {
	                	if ($jsonBox['ok']) {
                    			$jsonBox['winner'] = $bwinner;
				}
                	}
                	die(json_encode($jsonBox));
            		break;
        }
    	}
	}

	public function savegame($var_start_time_data) {
		$var_start_time_data[0][] = array_shift($var_start_time_data[1]);
		$game_off = array();
		$game_on = array();
		if (isset($var_start_time_data[0])) { 
			foreach	($var_start_time_data[0] as $key=>$var) {
				$date_time = explode(" ", $var['datetime']);
				$game_off[$key]['game_data'] = $date_time[0];
				$game_off[$key]['game_time'] = $date_time[1];
				$game_off[$key]['game_type'] = $var['type'];
				$game_off[$key]['game_prize_name'] = $var['description'];
				$game_off[$key]['game_prize_id'] = $var['description_id'];
				$game_off[$key]['game_code'] = $var['cupone'];
				$game_off[$key]['game_requir'] = $var['requir'];
				$game_off[$key]['game_requir_val'] = $var['requir_val'];
			}
		}
		if (isset($var_start_time_data[1])) { 
			foreach	($var_start_time_data[1] as $key=>$var) {
				$game_on[$key]['status'] = 'on';
				$date_time = explode(" ", $var['datetime']);
				$game_on[$key]['game_data'] = $date_time[0];
				$game_on[$key]['game_time'] = $date_time[1];
				$game_on[$key]['game_type'] = $var['type'];
				$game_on[$key]['game_prize_name'] = $var['description'];
				$game_on[$key]['game_prize_id'] = $var['description_id'];
				$game_on[$key]['game_code'] = $var['cupone'];
				$game_on[$key]['game_requir'] = $var['requir'];
				$game_on[$key]['game_requir_val'] = $var['requir_val'];
				if (isset($var['status_vip']) and ($var['status_vip'] == 'on')) {
					$game_on[$key]['game_status_vip'] = $var['status_vip'];
					$game_on[$key]['game_vip_count'] = $var['vip_count'];
					$game_on[$key]['game_vip_buy_val'] = $var['vip_buy_val'];
					$game_on[$key]['game_vip_id'] = $var['vip_id'];
				}
			}					

		}
		$game = array_merge_recursive($game_off,$game_on);
		$this->load->model('fido/bonusloto');
		$game = $this->model_fido_bonusloto->BonuslotoSortDateTime($game);
		$this->db->query("UPDATE `" . DB_PREFIX . "setting` SET  `value` = '" . $this->db->escape(serialize($game)) . "', `serialized` = '1' WHERE `group` = 'bonusloto' AND `key` = 'bonusloto_game'");
		$this->db->query("UPDATE `" . DB_PREFIX . "setting` SET  `value` = '0' WHERE `group` = 'bonusloto' AND `key` = 'bonusloto_start_lototron'");
	}
}
?>
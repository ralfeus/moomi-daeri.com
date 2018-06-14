<?php
class ModelFidobonuslotoMail extends \system\engine\Model {
	public function sendBonuslotoMailWinner($bonusloto_id) {
			$this->load->language('mail/bonusloto_confirm');
			
			$this->load->model('localisation/currency');

			$query_winner = $this->getDb()->query("SELECT * FROM `" . DB_PREFIX . "bonusloto_winner` WHERE `bonusloto_id`='".$bonusloto_id."'");
			$subject = sprintf($this->language->get('text_subject'), $this->config->get('config_name'), $query_winner->row['winner_date']);
			$pieces = explode("|", $query_winner->row['winner_bonus']);			

			// HTML Mail
			$template = new Template();
			
			$template->data['title'] = sprintf($this->language->get('text_subject'), html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'), $query_winner->row['winner_date']);
			if ($this->config->get('bonusloto_greeting_text') != '') {
//				$template->data['text_greeting'] = sprintf($this->config->get('bonusloto_greeting_text'), html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'), $pieces[0], $pieces[1]);
//{winner}- ����������
//{date} - ���� ���������
//{winner_bonus} - �������
//{winner_code} - ��� ������
//{magazin_name} - �������� ��������

				$bonusloto_greeting = $this->config->get('bonusloto_greeting_text');
				$bonusloto_greeting = str_replace('{winner}', $query_winner->row['winner_email'], $bonusloto_greeting); 
				$bonusloto_greeting = str_replace('{date}', $query_winner->row['winner_date'], $bonusloto_greeting);
				$bonusloto_greeting = str_replace('{winner_bonus}', $pieces[0], $bonusloto_greeting);
				$bonusloto_greeting = str_replace('{winner_code}', $pieces[1], $bonusloto_greeting);
				$bonusloto_greeting = str_replace('{magazin_name}', $this->config->get('config_name'), $bonusloto_greeting);
				$template->data['text_greeting'] = html_entity_decode($bonusloto_greeting, ENT_QUOTES, 'UTF-8');

			} else {
				$template->data['text_greeting'] = sprintf($this->language->get('text_greeting'), html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'), $pieces[0], $pieces[1]);
			}
			$template->data['text_powered_by'] = $this->language->get('text_powered_by');
			$template->data['logo'] = 'cid:' . basename($this->config->get('config_logo'));
			$template->data['store_name'] = $this->config->get('config_name');
			$template->data['store_url'] = $this->config->get('config_url');
			
			
			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/mail/bonusloto_confirm.tpl')) {
				$html = $template->fetch($this->config->get('config_template') . '/template/mail/bonusloto_confirm.tpl');
			} else {
				$html = $template->fetch('default/template/mail/bonusloto_confirm.tpl');
			}

			$mail = new Mail();
			$mail->protocol = $this->config->get('config_mail_protocol');
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->hostname = $this->config->get('config_smtp_host');
			$mail->username = $this->config->get('config_smtp_username');
			$mail->password = $this->config->get('config_smtp_password');
			$mail->port = $this->config->get('config_smtp_port');
			$mail->timeout = $this->config->get('config_smtp_timeout');			
			$mail->setTo($query_winner->row['winner_email']);
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender($this->config->get('config_name'));
			$mail->setSubject($subject);
			$mail->setHtml($html);
			$mail->addAttachment(DIR_IMAGE . $this->config->get('config_logo'));
			$mail->send();
	}	
	public function sendBonuslotoMailAdmin($bonusloto_id) {
			$this->load->language('mail/bonusloto_confirm');
			
			$this->load->model('localisation/currency');

			$query_winner = $this->getDb()->query("SELECT * FROM `" . DB_PREFIX . "bonusloto_winner` WHERE `bonusloto_id`='".$bonusloto_id."'");
			$subject = sprintf($this->language->get('text_subject_admin'), $this->config->get('config_name'), $query_winner->row['winner_date']);
			$pieces = explode("|", $query_winner->row['winner_bonus']);			

			// HTML Mail
			$template = new Template();
			
			$template->data['title'] = sprintf($this->language->get('text_subject_admin'), html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'), $query_winner->row['winner_date']);
			
			$template->data['text_greeting'] = sprintf($this->language->get('text_notifi_greeting'), html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'), $query_winner->row['winner_email'], $pieces[0]);
			$template->data['text_powered_by'] = $this->language->get('text_powered_by');
			$template->data['logo'] = 'cid:' . basename($this->config->get('config_logo'));
			$template->data['store_name'] = $this->config->get('config_name');
			$template->data['store_url'] = $this->config->get('config_url');
			
			
			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/mail/bonusloto_confirm.tpl')) {
				$html = $template->fetch($this->config->get('config_template') . '/template/mail/bonusloto_confirm.tpl');
			} else {
				$html = $template->fetch('default/template/mail/bonusloto_confirm.tpl');
			}

			$mail = new Mail();
			$mail->protocol = $this->config->get('config_mail_protocol');
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->hostname = $this->config->get('config_smtp_host');
			$mail->username = $this->config->get('config_smtp_username');
			$mail->password = $this->config->get('config_smtp_password');
			$mail->port = $this->config->get('config_smtp_port');
			$mail->timeout = $this->config->get('config_smtp_timeout');			
			$mail->setTo($this->config->get('config_email'));
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender($this->config->get('config_name'));
			$mail->setSubject($subject);
			$mail->setHtml($html);
			$mail->addAttachment(DIR_IMAGE . $this->config->get('config_logo'));
			$mail->send();
	}	

}
?>

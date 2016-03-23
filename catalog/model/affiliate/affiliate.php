<?php
class ModelAffiliateAffiliate extends Model {
	public function addAffiliate($data) {
      	$this->getDb()->query("
			INSERT INTO affiliate 
			(firstname, lastname, email, telephone, fax, password, company, address_1, address_2, city, postcode, country_id, 
			zone_id, code, commission, tax, payment, cheque, paypal, bank_name, bank_branch_number, bank_swift_code, bank_account_name,
			bank_account_number, status, approved, date_added)
			VALUES (
				:firstName, :lastName, :email, :phone, :fax, :password, :company, :address1, :address2, :city, :zip, 
				:countryId, :zoneId, :code, :commission, :tax, :payment, :cheque, :paypal, :bankName, :bankBranchNumber, 
				:bankSwiftCode, :bankAccountName, :bankAccountNumber, 1, 1, NOW()
			)
			", [
			":firstName" => $data['firstname'],
			":lastName" => $data['lastname'],
			":email" => $data['email'],
			":phone" => $data['telephone'],
			":fax" => $data['fax'],
			":password" => md5($data['password']),
			":company" => $data['company'],
			":address1" => $data['address_1'],
			":address2" => $data['address_2'],
			":city" => $data['city'],
			":zip" => $data['postcode'],
			":countryId" => $data['country_id'],
			":zoneId" => $data['zone_id'],
			":code" => uniqid(),
			":commission" => $this->config->get('config_commission'),
			":tax" => $data['tax'],
			":payment" => $data['payment'],
			":cheque" => $data['cheque'],
			":paypal" => $data['paypal'],
			":bankName" => $data['bank_name'],
			":bankBranchNumber" => $data['bank_branch_number'],
			":bankSwiftCode" => $data['bank_swift_code'],
			":bankAccountName" => $data['bank_account_name'],
			":bankAccountNumber" => $data['bank_account_number']
		]);
	
		$this->language->load('mail/affiliate');
		
		$subject = sprintf($this->language->get('text_subject'), $this->config->get('config_name'));
		
		$message  = sprintf($this->language->get('text_welcome'), $this->config->get('config_name')) . "\n\n";
		$message .= $this->language->get('text_approval') . "\n";
		$message .= $this->url->link('affiliate/login', '', 'SSL') . "\n\n";
		$message .= $this->language->get('text_services') . "\n\n";
		$message .= $this->language->get('text_thanks') . "\n";
		$message .= $this->config->get('config_name');
		
		$mail = new Mail();
		$mail->protocol = $this->config->get('config_mail_protocol');
		$mail->parameter = $this->config->get('config_mail_parameter');
		$mail->hostname = $this->config->get('config_smtp_host');
		$mail->username = $this->config->get('config_smtp_username');
		$mail->password = $this->config->get('config_smtp_password');
		$mail->port = $this->config->get('config_smtp_port');
		$mail->timeout = $this->config->get('config_smtp_timeout');				
		$mail->setTo($this->request->post['email']);
		$mail->setFrom($this->config->get('config_email'));
		$mail->setSender($this->config->get('config_name'));
		$mail->setSubject($subject);
		$mail->setText($message);
		$mail->send();
	}
	
	public function editAffiliate($data) {
		$this->getDb()->query("UPDATE affiliate SET firstname = '" . $this->getDb()->escape($data['firstname']) . "', lastname = '" . $this->getDb()->escape($data['lastname']) . "', email = '" . $this->getDb()->escape($data['email']) . "', telephone = '" . $this->getDb()->escape($data['telephone']) . "', fax = '" . $this->getDb()->escape($data['fax']) . "', company = '" . $this->getDb()->escape($data['company']) . "', address_1 = '" . $this->getDb()->escape($data['address_1']) . "', address_2 = '" . $this->getDb()->escape($data['address_2']) . "', city = '" . $this->getDb()->escape($data['city']) . "', postcode = '" . $this->getDb()->escape($data['postcode']) . "', country_id = '" . (int)$data['country_id'] . "', zone_id = '" . (int)$data['zone_id'] . "' WHERE affiliate_id = '" . (int)$this->affiliate->getId() . "'");
	}

	public function editPayment($data) {
      	$this->getDb()->query("UPDATE affiliate SET tax = '" . $this->getDb()->escape($data['tax']) . "', payment = '" . $this->getDb()->escape($data['payment']) . "', cheque = '" . $this->getDb()->escape($data['cheque']) . "', paypal = '" . $this->getDb()->escape($data['paypal']) . "', bank_name = '" . $this->getDb()->escape($data['bank_name']) . "', bank_branch_number = '" . $this->getDb()->escape($data['bank_branch_number']) . "', bank_swift_code = '" . $this->getDb()->escape($data['bank_swift_code']) . "', bank_account_name = '" . $this->getDb()->escape($data['bank_account_name']) . "', bank_account_number = '" . $this->getDb()->escape($data['bank_account_number']) . "' WHERE affiliate_id = '" . (int)$this->affiliate->getId() . "'");
	}
	
	public function editPassword($email, $password) {
      	$this->getDb()->query("UPDATE affiliate SET password = '" . $this->getDb()->escape(md5($password)) . "' WHERE email = '" . $this->getDb()->escape($email) . "'");
	}
				
	public function getAffiliate($affiliate_id) {
		$query = $this->getDb()->query("SELECT * FROM affiliate WHERE affiliate_id = '" . (int)$affiliate_id . "'");
		
		return $query->row;
	}
	
	public function getAffiliateByCode($code) {
		$query = $this->getDb()->query("SELECT * FROM affiliate WHERE code = '" . $this->getDb()->escape($code) . "'");
		
		return $query->row;
	}
			
	public function getTotalAffiliatesByEmail($email) {
		$query = $this->getDb()->query("SELECT COUNT(*) AS total FROM affiliate WHERE email = '" . $this->getDb()->escape($email) . "'");
		
		return $query->row['total'];
	}
}
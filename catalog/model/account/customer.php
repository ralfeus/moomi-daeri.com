<?php
class ModelAccountCustomer extends \system\engine\Model {
	public function addCustomer($data) {
      	$this->db->query("
      	    INSERT INTO customer
      	    SET
      	        balance = 0,
      	        base_currency_code = '" . $this->db->escape($data['baseCurrency']) . "',
      	        store_id = '" . (int)$this->config->get('config_store_id') . "',
      	        firstname = '" . $this->db->escape($data['firstname']) . "',
      	        lastname = '" . $this->db->escape($data['lastname']) . "',
                nickname = '" . $this->db->escape($data['nickname']) . "',
      	        email = '" . $this->db->escape($data['email']) . "',
      	        telephone = '" . $this->db->escape($data['telephone']) . "',
      	        fax = '" . $this->db->escape($data['fax']) . "',
      	        password = '" . $this->db->escape(md5($data['password'])) . "',
      	        newsletter = '" . (isset($data['newsletter']) ? (int)$data['newsletter'] : 0) . "',
      	        customer_group_id = '" . (int)$this->config->get('config_registred_group_id') . "',
      	        status = '1',
      	        date_added = NOW()"
        );
      	
		$customer_id = $this->db->getLastId();
			
      	$this->db->query("INSERT INTO address SET customer_id = '" . (int)$customer_id . "', firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', company = '" . $this->db->escape($data['company']) . "', address_1 = '" . $this->db->escape($data['address_1']) . "', address_2 = '" . $this->db->escape($data['address_2']) . "', city = '" . $this->db->escape($data['city']) . "', postcode = '" . $this->db->escape($data['postcode']) . "', country_id = '" . (int)$data['country_id'] . "', zone_id = '" . (int)$data['zone_id'] . "'");
		
		$address_id = $this->db->getLastId();

      	$this->db->query("UPDATE customer SET address_id = '" . (int)$address_id . "' WHERE customer_id = '" . (int)$customer_id . "'");
		
		if (!$this->config->get('config_customer_approval')) {
			$this->db->query("UPDATE customer SET approved = '1' WHERE customer_id = '" . (int)$customer_id . "'");
		}	
		
		$this->language->load('mail/customer');
		
		$subject = sprintf($this->language->get('text_subject'), $this->config->get('config_name'));
		
		$message = sprintf($this->language->get('text_welcome'), $this->config->get('config_name')) . "\n\n";
		
		if (!$this->config->get('config_customer_approval')) {
			$message .= $this->language->get('text_login') . "\n";
		} else {
			$message .= $this->language->get('text_approval') . "\n";
		}
		
		$message .= $this->url->link('account/login', '', 'SSL') . "\n\n";
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
		$mail->setTo($data['email']);
		$mail->setFrom($this->config->get('config_email'));
		$mail->setSender($this->config->get('config_name'));
		$mail->setSubject($subject);
		$mail->setText($message);
		$mail->send();

		// Send to main admin email if new account email is enabled
		if ($this->config->get('config_account_mail')) {
			$mail->setTo($this->config->get('config_email'));
			$mail->send();

			// Send to additional alert emails if new account email is enabled
			$emails = explode(',', $this->config->get('config_alert_emails'));

			foreach ($emails as $email) {
				if (strlen($email) > 0 && preg_match('/^[^\@]+@.*\.[a-z]{2,6}$/i', $email)) {
					$mail->setTo($email);
					$mail->send();
				}
			}
		}
	}
	
	public function editCustomer($data) {
        /// Currency change is turned off temporary //TODO: restore when RUB problem will have gone
		$this->db->query("
		    UPDATE customer
		    SET
		        firstname = '" . $this->db->escape($data['firstname']) . "',
		        lastname = '" . $this->db->escape($data['lastname']) . "',
                nickname = '" . $this->db->escape($data['nickname']) . "',
		        email = '" . $this->db->escape($data['email']) . "',
		        telephone = '" . $this->db->escape($data['telephone']) . "',
		        fax = '" . $this->db->escape($data['fax']) . "'/*,
		        base_currency_code = '" . $this->db->escape($data['baseCurrency']) . "'*/
            WHERE customer_id = '" . (int)$this->customer->getId() . "'"
        );
        /// Change customer base currency
//        if ($this->customer->getBaseCurrency()->getCode() != $data['baseCurrency']) {
//            $this->customer->setBalance(round(
//                $this->currency->convert(
//                    $this->currency->convert(
//                        $this->customer->getBalance(),
//                        $this->customer->getBaseCurrency()->getCode(),
//                        $this->config->get('config_currency')),
//                    $this->config->get('config_currency'),
//                    $data['baseCurrency'])),
//                $this->currency->getDecimalPlace($data['baseCurrency']));
//        }
	}

	public function editPassword($email, $password) {
      	$this->db->query("UPDATE customer SET password = '" . $this->db->escape(md5($password)) . "' WHERE email = '" . $this->db->escape($email) . "'");
	}

	public function editNewsletter($newsletter) {
		$this->db->query("UPDATE customer SET newsletter = '" . (int)$newsletter . "' WHERE customer_id = '" . (int)$this->customer->getId() . "'");
	}
					
	public function getCustomer($customerId) {
		$query = $this->db->query("SELECT * FROM customer WHERE customer_id = '" . (int)$customerId . "'");
		
		return $query->row;
	}

    public function  getCustomerByNickname($nickname) {
        $query = $this->db->query("
            SELECT *
            FROM customer
            WHERE nickname = '" . $this->db->escape($nickname) . "'
        ");
        if ($query)
            return $query->row;
        else
            return null;
    }
	
	public function getCustomerByToken($token) {
		$query = $this->db->query("SELECT * FROM customer WHERE token = '" . $this->db->escape($token) . "' AND token != ''");
		
		$this->db->query("UPDATE customer SET token = ''");
		
		return $query->row;
	}
		
	public function getCustomers($data = array()) {
		$sql = "SELECT *, CONCAT(c.firstname, ' ', c.lastname) AS name, cg.name AS customer_group FROM customer c LEFT JOIN customer_group cg ON (c.customer_group_id = cg.customer_group_id) ";

		$implode = array();

		if (isset($data['filter_name']) && !is_null($data['filter_name'])) {
			$implode[] = "LCASE(CONCAT(c.firstname, ' ', c.lastname)) LIKE '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "%'";
		}

		if (isset($data['filter_email']) && !is_null($data['filter_email'])) {
			$implode[] = "c.email = '" . $this->db->escape($data['filter_email']) . "'";
		}

		if (isset($data['filter_customer_group_id']) && !is_null($data['filter_customer_group_id'])) {
			$implode[] = "cg.customer_group_id = '" . $this->db->escape($data['filter_customer_group_id']) . "'";
		}

		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$implode[] = "c.status = '" . (int)$data['filter_status'] . "'";
		}

		if (isset($data['filter_approved']) && !is_null($data['filter_approved'])) {
			$implode[] = "c.approved = '" . (int)$data['filter_approved'] . "'";
		}

		if (isset($data['filter_ip']) && !is_null($data['filter_ip'])) {
			$implode[] = "c.customer_id IN (SELECT customer_id FROM customer_ip WHERE ip = '" . $this->db->escape($data['filter_ip']) . "')";
		}

		if (isset($data['filter_date_added']) && !is_null($data['filter_date_added'])) {
			$implode[] = "DATE(c.date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}

		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}

		$sort_data = array(
			'name',
			'c.email',
			'customer_group',
			'c.status',
			'c.ip',
			'c.date_added'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY name";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}
		
	public function getTotalCustomersByEmail($email) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM customer WHERE email = '" . $this->db->escape($email) . "'");

		return $query->row['total'];
	}
}
?>
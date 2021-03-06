<?php
namespace model\sale;

use Mail;
use model\DAO;

class CustomerDAO extends DAO {
	public function addCustomer($data) {
      	$this->getDb()->query("
      	    INSERT INTO customer
      	    SET
      	        firstname = '" . $this->getDb()->escape($data['firstname']) . "',
      	        lastname = '" . $this->getDb()->escape($data['lastname']) . "',
      	        nickname = '" . $this->getDb()->escape($data['nickname']) . "',
      	        email = '" . $this->getDb()->escape($data['email']) . "',
      	        telephone = '" . $this->getDb()->escape($data['telephone']) . "',
      	        fax = '" . $this->getDb()->escape($data['fax']) . "',
      	        newsletter = '" . (int)$data['newsletter'] . "',
      	        customer_group_id = '" . (int)$data['customer_group_id'] . "',
      	        password = '" . $this->getDb()->escape(md5($data['password'])) . "',
      	        status = '" . (int)$data['status'] . "', date_added = NOW()");
      	
      	$customer_id = $this->getDb()->getLastId();
      	
      	if (isset($data['address'])) {		
      		foreach ($data['address'] as $address) {	
      			$this->getDb()->query("
                    INSERT INTO address 
                        (customer_id, firstname, lastname, company, address_1, address_2, city, postcode, country_id, zone_id)
                         VALUES (:customerId, :firstName, :lastName, :company, :address1, :address2, :city, :postcode, :countryId, :zoneId)
                    ", [
                        ':customerId' => $customer_id,
                        ':firstName' => $address['firstname'],
                        ':lastName' => $address['lastname'],
                        ':company' => $address['company'],
                        ':address1' => $address['address_1'],
                        ':address2' => $address['address_2'],
                        ':city' => $address['city'],
                        ':postcode' => $address['postcode'],
                        ':countryId' => $address['country_id'],
                        ':zoneId' => $address['zone_id']

                ]);
				if (isset($address['default'])) {
					$address_id = $this->getDb()->getLastId();
					
					$this->getDb()->query("UPDATE customer SET address_id = :addressId WHERE customer_id = :customerId", [':addressId' => $address_id, ':customerId' => $customer_id]);
				}
			}
		}
	}

    private function buildFilterString($data = array())
    {
//        $this->log->write(print_r($data, true));
        $filter = "";
        if (isset($data['selected_items']) && count($data['selected_items']))
            $filter = "op.order_product_id in (" . implode(', ', $data['selected_items']) . ")";
        else
        {
            if (!empty($data['filterCustomerId']))
                $filter .= ($filter ? " AND " : "") . "c.customer_id IN (" . implode(', ', $data['filterCustomerId']) . ")";
            if (!empty($data['filterName']))
                $filter .= ($filter ? " AND " : "") . "LCASE(CONCAT(c.firstname, ' ', c.lastname)) LIKE '" . $this->getDb()->escape(utf8_strtolower($data['filterName'])) . "%'";
            if (!empty($data['filterNickname']))
                $filter .= ($filter ? " AND " : "") . "LCASE(c.nickname) LIKE '" . $this->getDb()->escape(utf8_strtolower($data['filterNickname'])) . "%'";
            if (!empty($data['filterEmail']))
                $filter .= ($filter ? " AND " : "") . "LCASE(c.email) LIKE '" . $this->getDb()->escape(utf8_strtolower($data['filterEmail'])) . "%'";
            if (isset($data['filterCustomerGroupId']) && is_numeric($data['filterCustomerGroupId']))
                $filter .= ($filter ? " AND " : "") . "cg.customer_group_id = " . (int)$data['filterCustomerGroupId'];
            if (isset($data['filterStatus']) && is_numeric($data['filterStatus']))
                $filter .= ($filter ? " AND " : "") . "c.status = '" . (int)$data['filterStatus'] . "'";
            if (isset($data['filterApproved']) && is_numeric($data['filterApproved']))
                $filter .= ($filter ? " AND " : "") . "c.approved = '" . (int)$data['filterApproved'] . "'";
            if (!empty($data['filterIp']))
                $filter .= ($filter ? " AND " : "") . "c.customer_id IN (SELECT customer_id FROM customer_ip WHERE ip = '" . $this->getDb()->escape($data['filterIp']) . "')";
            if (!empty($data['filterDateAdded']))
                $filter .= ($filter ? " AND " : "") . "DATE(c.date_added) = DATE('" . $this->getDb()->escape($data['filterDateAdded']) . "')";
        }
        return $filter;
    }
	
	public function editCustomer($customer_id, $data) {
        /// Convert deposit amount in case currency is changed
        $customer = $this->getCustomer($customer_id);
        if ($customer['base_currency_code'] != $data['baseCurrency']) {
            $balance = round(
                $this->getCurrentCurrency()->convert(
                    $this->getCurrentCurrency()->convert(
                        $customer['balance'],
                        $customer['base_currency_code'],
                        $this->getConfig()->get('config_currency')),
                    $this->getConfig()->get('config_currency'),
                    $data['baseCurrency']),
                $this->getCurrentCurrency()->getDecimalPlace($data['baseCurrency']));
        }
        else
            $balance = $customer['balance'];

        $this->getDb()->query("
		    UPDATE customer
		    SET
		        firstname = :firstName,
		        lastname = :lastName,
		        nickname = :nickname,
		        email = :email,
		        telephone = :phone,
		        fax = :fax,
		        newsletter = :newsletter,
		        customer_group_id = :customerGroupId,
		        status = :status,
		        base_currency_code = :baseCurrencyCode,
		        balance = :balance
            WHERE customer_id = :customerId
            ", [
                ':firstName' => $data['firstname'],
                ':lastName' => $data['lastname'],
                ':nickname' => $data['nickname'],
                ':email' => $data['email'],
                ':phone' => $data['telephone'],
                ':fax' => $data['fax'],
                ':newsletter' => $data['newsletter'],
                ':customerGroupId' => $data['customer_group_id'],
                ':status' => $data['status'],
                ':baseCurrencyCode' => $data['baseCurrency'],
                ':balance' => $balance,
                ':customerId' => $customer_id
            ]
        );

      	if ($data['password']) {
        	$this->getDb()->query("UPDATE customer SET password = '" . $this->getDb()->escape(md5($data['password'])) . "' WHERE customer_id = '" . (int)$customer_id . "'");
      	}
      	
      	$this->getDb()->query("DELETE FROM address WHERE customer_id = '" . (int)$customer_id . "'");
      	
      	if (isset($data['address'])) {
      		foreach ($data['address'] as $address) {
				if ($address['address_id']) {
					$this->getDb()->query("INSERT INTO address SET address_id = '" . $this->getDb()->escape($address['address_id']) . "', customer_id = '" . (int)$customer_id . "', firstname = '" . $this->getDb()->escape($address['firstname']) . "', lastname = '" . $this->getDb()->escape($address['lastname']) . "', company = '" . $this->getDb()->escape($address['company']) . "', address_1 = '" . $this->getDb()->escape($address['address_1']) . "', address_2 = '" . $this->getDb()->escape($address['address_2']) . "', city = '" . $this->getDb()->escape($address['city']) . "', postcode = '" . $this->getDb()->escape($address['postcode']) . "', country_id = '" . (int)$address['country_id'] . "', zone_id = '" . (int)$address['zone_id'] . "'");
					
					if (isset($address['default'])) {
						$this->getDb()->query("UPDATE customer SET address_id = '" . (int)$address['address_id'] . "' WHERE customer_id = '" . (int)$customer_id . "'");
					}
				} else {
					$this->getDb()->query("INSERT INTO address SET customer_id = '" . (int)$customer_id . "', firstname = '" . $this->getDb()->escape($address['firstname']) . "', lastname = '" . $this->getDb()->escape($address['lastname']) . "', company = '" . $this->getDb()->escape($address['company']) . "', address_1 = '" . $this->getDb()->escape($address['address_1']) . "', address_2 = '" . $this->getDb()->escape($address['address_2']) . "', city = '" . $this->getDb()->escape($address['city']) . "', postcode = '" . $this->getDb()->escape($address['postcode']) . "', country_id = '" . (int)$address['country_id'] . "', zone_id = '" . (int)$address['zone_id'] . "'");
					
					if (isset($address['default'])) {
						$address_id = $this->getDb()->getLastId();
						
						$this->getDb()->query("UPDATE customer SET address_id = '" . (int)$address_id . "' WHERE customer_id = '" . (int)$customer_id . "'");
					}
				}
			}
		}
        $this->getCache()->delete('customer.' . $customer_id);
    }

	public function editToken($customer_id, $token) {
		$this->getDb()->query("
		    UPDATE customer
		    SET token = '" . $this->getDb()->escape($token) . "'
		    WHERE customer_id = '" . (int)$customer_id . "'
        ");
        $this->getCache()->delete('customer.' . $customer_id);
    }
	
	public function deleteCustomer($customer_id) {
		$this->getDb()->query("DELETE FROM customer WHERE customer_id = '" . (int)$customer_id . "'");
		$this->getDb()->query("DELETE FROM customer_reward WHERE customer_id = '" . (int)$customer_id . "'");
		$this->getDb()->query("DELETE FROM customer_transaction WHERE customer_id = '" . (int)$customer_id . "'");
		$this->getDb()->query("DELETE FROM customer_ip WHERE customer_id = '" . (int)$customer_id . "'");
		$this->getDb()->query("DELETE FROM address WHERE customer_id = '" . (int)$customer_id . "'");
        $this->getCache()->delete('customer.' . $customer_id);
	}

    /**
     * @param $customerId
     * @return array
     * @throws \InvalidArgumentException In case customer is not found
     */
    public function getCustomer($customerId) {
        $cacheKey = 'customer.' . $customerId;
        $result = $this->getCache()->get($cacheKey);
        if (!is_null($result)) {
            return $result;
        }
		$query = $this->getDb()->query(<<<SQL
		    SELECT DISTINCT *
		    FROM
		        customer AS c
		        JOIN customer_group AS cg ON c.customer_group_id = cg.customer_group_id
		    WHERE customer_id = :customerId
SQL
          , [ ':customerId' => $customerId ]
        );
        if (!$query->num_rows) {
            throw new \InvalidArgumentException("Customer ID#$customerId was not found");
        }

        $this->getCache()->set($cacheKey, $query->row);
		return $query->row;
	}

    public function getCustomerBalance($customerId)
    {
        $query = $this->getDb()->query("
            SELECT balance
            FROM customer
            WHERE customer_id = " . (int)$customerId
        );
        if ($query->num_rows)
            return $query->row['balance'];
        else
            return null;
    }
	
	public function getCustomerByEmail($email) {
		$query = $this->getDb()->query("SELECT DISTINCT * FROM customer WHERE email = '" . $this->getDb()->escape($email) . "'");
	
		return $query->row;
	}

    /**
     * @param array $data
     * @return array
     */
    public function getCustomers($data = array()) {
        $filter = $this->buildFilterString($data);
		$sql = /** @lang text */
            "
		    SELECT *, CONCAT(c.firstname, ' ', c.lastname) AS name, cg.name AS customer_group
		    FROM
		        customer c
		        LEFT JOIN customer_group cg ON (c.customer_group_id = cg.customer_group_id)" .
            ($filter ? "WHERE $filter" : '')
        ;

		$sort_data = array(
			'name',
            'nickname',
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
//		$this->log->write($sql);
		$query = $this->getDb()->query($sql);
		
		return $query->rows;	
	}
	
	public function approve($customer_id) {
		$customer_info = $this->getCustomer($customer_id);

		if ($customer_info) {
			$this->getDb()->query("UPDATE customer SET approved = '1' WHERE customer_id = '" . (int)$customer_id . "'");

			$this->getLoader()->language('mail/customer');
			
			$this->getLoader()->model('setting/store');
						
			$store_info = $this->model_setting_store->getStore($customer_info['store_id']);
			
			if ($store_info) {
				$store_name = $store_info['name'];
				$store_url = $store_info['url'] . 'index.php?route=account/login';
			} else {
				$store_name = $this->getConfig()->get('config_name');
				$store_url = HTTP_CATALOG . 'index.php?route=account/login';
			}
	
			$message  = sprintf($this->getLanguage()->get('text_approve_welcome'), $store_name) . "\n\n";
			$message .= $this->getLanguage()->get('text_approve_login') . "\n";
			$message .= $store_url . "\n\n";
			$message .= $this->getLanguage()->get('text_approve_services') . "\n\n";
			$message .= $this->getLanguage()->get('text_approve_thanks') . "\n";
			$message .= $store_name;
	
			$mail = new Mail();
			$mail->protocol = $this->getConfig()->get('config_mail_protocol');
			$mail->parameter = $this->getConfig()->get('config_mail_parameter');
			$mail->hostname = $this->getConfig()->get('config_smtp_host');
			$mail->username = $this->getConfig()->get('config_smtp_username');
			$mail->password = $this->getConfig()->get('config_smtp_password');
			$mail->port = $this->getConfig()->get('config_smtp_port');
			$mail->timeout = $this->getConfig()->get('config_smtp_timeout');							
			$mail->setTo($customer_info['email']);
			$mail->setFrom($this->getConfig()->get('config_email'));
			$mail->setSender($store_name);
			$mail->setSubject(sprintf($this->getLanguage()->get('text_approve_subject'), $store_name));
			$mail->setText(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
			$mail->send();
            $this->getCache()->delete('customer.' . $customer_id);
		}
	}
		
	public function getCustomersByNewsletter() {
		$query = $this->getDb()->query("SELECT * FROM customer WHERE newsletter = '1' ORDER BY firstname, lastname, email");
	
		return $query->rows;
	}
	
	public function getCustomersByCustomerGroupId($customer_group_id) {
		$query = $this->getDb()->query("SELECT * FROM customer WHERE customer_group_id = '" . (int)$customer_group_id . "' ORDER BY firstname, lastname, email");
	
		return $query->rows;
	}
		
	public function getCustomersByProduct($product_id) {
		if ($product_id) {
			$query = $this->getDb()->query("SELECT DISTINCT `email` FROM `order` o LEFT JOIN order_product op ON (o.order_id = op.order_id) WHERE op.product_id = '" . (int)$product_id . "' AND o.order_status_id <> '0'");
	
			return $query->rows;
		} else {
			return array();	
		}
	}
	
	public function getAddress($address_id) {
		$address_query = $this->getDb()->query("SELECT * FROM address WHERE address_id = '" . (int)$address_id . "'");

		$default_query = $this->getDb()->query("SELECT address_id FROM customer WHERE customer_id = '" . (int)$address_query->row['customer_id'] . "'");
				
		if ($address_query->num_rows) {
			$country_query = $this->getDb()->query("SELECT * FROM `country` WHERE country_id = '" . (int)$address_query->row['country_id'] . "'");
			
			if ($country_query->num_rows) {
				$country = $country_query->row['name'];
				$iso_code_2 = $country_query->row['iso_code_2'];
				$iso_code_3 = $country_query->row['iso_code_3'];
				$address_format = $country_query->row['address_format'];
			} else {
				$country = '';
				$iso_code_2 = '';
				$iso_code_3 = '';	
				$address_format = '';
			}
			
			$zone_query = $this->getDb()->query("SELECT * FROM `zone` WHERE zone_id = '" . (int)$address_query->row['zone_id'] . "'");
			
			if ($zone_query->num_rows) {
				$zone = $zone_query->row['name'];
				$code = $zone_query->row['code'];
			} else {
				$zone = '';
				$code = '';
			}		
		
			return array(
				'address_id'     => $address_query->row['address_id'],
				'customer_id'    => $address_query->row['customer_id'],
				'firstname'      => $address_query->row['firstname'],
				'lastname'       => $address_query->row['lastname'],
				'company'        => $address_query->row['company'],
				'address_1'      => $address_query->row['address_1'],
				'address_2'      => $address_query->row['address_2'],
				'postcode'       => $address_query->row['postcode'],
				'city'           => $address_query->row['city'],
				'zone_id'        => $address_query->row['zone_id'],
				'zone'           => $zone,
				'zone_code'      => $code,
				'country_id'     => $address_query->row['country_id'],
				'country'        => $country,	
				'iso_code_2'     => $iso_code_2,
				'iso_code_3'     => $iso_code_3,
				'address_format' => $address_format,
				'default'		 => ($default_query->row['address_id'] == $address_query->row['address_id']) ? true : false
			);
		} else {
		    throw new \InvalidArgumentException("There is no address with such ID");
        }
	}
		
	public function getAddresses($customer_id) {
		$address_data = array();
		
		$query = $this->getDb()->query("SELECT address_id FROM address WHERE customer_id = '" . (int)$customer_id . "'");
	
		foreach ($query->rows as $result) {
			$address_info = $this->getAddress($result['address_id']);
		
			if ($address_info) {
				$address_data[] = $address_info;
			}
		}		
		
		return $address_data;
	}	
				
	public function getTotalCustomers($data = array()) {
      	$sql = "SELECT COUNT(*) AS total FROM customer";
		
		$implode = array();
		
		if (!empty($data['filter_name'])) {
			$implode[] = "LCASE(CONCAT(firstname, ' ', lastname)) LIKE '" . $this->getDb()->escape(utf8_strtolower($data['filter_name'])) . "%'";
		}
        if (!empty($data['filter_nickname'])) {
            $implode[] = "LCASE(nickname) LIKE '" . $this->getDb()->escape(utf8_strtolower($data['filter_nickname'])) . "%'";
        }
		if (!empty($data['filter_email'])) {
			$implode[] = "LCASE(email) LIKE '" . $this->getDb()->escape(utf8_strtolower($data['filter_email'])) . "%'";
		}
		
		if (!empty($data['filter_customer_group_id'])) {
			$implode[] = "customer_group_id = '" . $this->getDb()->escape($data['filter_customer_group_id']) . "'";
		}	
				
		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$implode[] = "status = '" . (int)$data['filter_status'] . "'";
		}			
		
		if (isset($data['filter_approved']) && !is_null($data['filter_approved'])) {
			$implode[] = "approved = '" . (int)$data['filter_approved'] . "'";
		}		
				
		if (!empty($data['filter_date_added'])) {
			$implode[] = "DATE(date_added) = DATE('" . $this->getDb()->escape($data['filter_date_added']) . "')";
		}
		
		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}
				
		$query = $this->getDb()->query($sql);
				
		return $query->row['total'];
	}
		
	public function getTotalCustomersAwaitingApproval() {
      	$query = $this->getDb()->query("SELECT COUNT(*) AS total FROM customer WHERE status = '0' OR approved = '0'");

		return $query->row['total'];
	}
	
	public function getTotalAddressesByCustomerId($customer_id) {
      	$query = $this->getDb()->query("SELECT COUNT(*) AS total FROM address WHERE customer_id = '" . (int)$customer_id . "'");
		
		return $query->row['total'];
	}
	
	public function getTotalAddressesByCountryId($country_id) {
		$query = $this->getDb()->query("SELECT COUNT(*) AS total FROM address WHERE country_id = '" . (int)$country_id . "'");
		
		return $query->row['total'];
	}	
	
	public function getTotalAddressesByZoneId($zone_id) {
		$query = $this->getDb()->query("SELECT COUNT(*) AS total FROM address WHERE zone_id = '" . (int)$zone_id . "'");
		
		return $query->row['total'];
	}
	
	public function getTotalCustomersByCustomerGroupId($customer_group_id) {
		$query = $this->getDb()->query("SELECT COUNT(*) AS total FROM customer WHERE customer_group_id = '" . (int)$customer_group_id . "'");
		
		return $query->row['total'];
	}
			
//	public function addTransaction($customerId, $description = '', $amount = '', $order_id = 0) {
//		$customer_info = $this->getCustomer($customerId);
//
//		if ($customer_info) {
//            /// Add transaction
//			$this->getDb()->query("
//			    INSERT INTO customer_transaction
//			    SET
//			        customer_id = '" . (int)$customerId . "',
//			        order_id = '" . (int)$order_id . "',
//			        description = '" . $this->getDb()->escape($description) . "',
//			        amount = '" . (float)$amount . "',
//			        date_added = NOW()"
//            );
//            /// Update customer's balance
//            $this->getDb()->query("
//                UPDATE customer
//                SET
//                    balance = balance - " . (float)$amount . "
//                WHERE customer_id = " . (int)$customerId
//            );
//
//			$this->getLanguage()->load('mail/customer');
//
//			if ($customer_info['store_id']) {
//				$this->getLoader()->model('setting/store');
//
//				$store_info = $this->model_setting_store->getStore($customer_info['store_id']);
//
//				if ($store_info) {
//					$store_name = $store_info['store_name'];
//				} else {
//					$store_name = $this->getConfig()->get('config_name');
//				}
//			} else {
//				$store_name = $this->getConfig()->get('config_name');
//			}
//
//			$message  = sprintf($this->getLanguage()->get('text_transaction_received'), $this->getCurrentCurrency()->format($amount, $this->getConfig()->get('config_currency'))) . "\n\n";
//			$message .= sprintf($this->getLanguage()->get('text_transaction_total'), $this->getCurrentCurrency()->format($this->getTransactionTotal($customerId)));
//
//			$mail = new Mail();
//			$mail->protocol = $this->getConfig()->get('config_mail_protocol');
//			$mail->parameter = $this->getConfig()->get('config_mail_parameter');
//			$mail->hostname = $this->getConfig()->get('config_smtp_host');
//			$mail->username = $this->getConfig()->get('config_smtp_username');
//			$mail->password = $this->getConfig()->get('config_smtp_password');
//			$mail->port = $this->getConfig()->get('config_smtp_port');
//			$mail->timeout = $this->getConfig()->get('config_smtp_timeout');
//			$mail->setTo($customer_info['email']);
//			$mail->setFrom($this->getConfig()->get('config_email'));
//			$mail->setSender($store_name);
//			$mail->setSubject(sprintf($this->getLanguage()->get('text_transaction_subject'), $this->getConfig()->get('config_name')));
//			$mail->setText($message);
//			$mail->send();
//            $this->getCache()->delete('customer' . $customerId);
//		}
//	}
	
//	public function deleteTransaction($order_id)
//    {
//        /// Delete transaction
//		$this->getDb()->query("
//		    DELETE FROM customer_transaction
//		    WHERE order_id = " . (int)$order_id
//        );
//	}
	
	public function getTransactions($customer_id, $start = 0, $limit = 10) {
		$query = $this->getDb()->query("
		    SELECT * FROM customer_transaction
		    WHERE customer_id = '" . (int)$customer_id . "'
		    ORDER BY date_added DESC LIMIT " . (int)$start . "," . (int)$limit
        );
        return $query->rows;
	}

	public function getTotalTransactions($customer_id) {
		$query = $this->getDb()->query("SELECT COUNT(*) AS total  FROM customer_transaction WHERE customer_id = '" . (int)$customer_id . "'");
	
		return $query->row['total'];
	}
			
	public function getTransactionTotal($customer_id) {
		$query = $this->getDb()->query("SELECT SUM(amount) AS total FROM customer_transaction WHERE customer_id = '" . (int)$customer_id . "'");
	
		return $query->row['total'];
	}
	
	public function getTotalTransactionsByOrderId($order_id) {
		$query = $this->getDb()->query("SELECT COUNT(*) AS total FROM customer_transaction WHERE order_id = '" . (int)$order_id . "'");
	
		return $query->row['total'];
	}	
				
	public function addReward($customer_id, $description = '', $points = '', $order_id = 0) {
		$customer_info = $this->getCustomer($customer_id);
			
		if ($customer_info) { 
			$this->getDb()->query("INSERT INTO customer_reward SET customer_id = '" . (int)$customer_id . "', order_id = '" . (int)$order_id . "', points = '" . $points . "', description = '" . $this->getDb()->escape($description) . "', date_added = NOW()");

			$this->getLanguage()->load('mail/customer');
			
			if ($order_id) {
				$this->getLoader()->model('sale/order');
		
				$order_info = $this->model_sale_order->getOrder($order_id);
				
				if ($order_info) {
					$store_name = $order_info['store_name'];
				} else {
					$store_name = $this->getConfig()->get('config_name');
				}	
			} else {
				$store_name = $this->getConfig()->get('config_name');
			}		
				
			$message  = sprintf($this->getLanguage()->get('text_reward_received'), $points) . "\n\n";
			$message .= sprintf($this->getLanguage()->get('text_reward_total'), $this->getRewardTotal($customer_id));
				
			$mail = new Mail();
			$mail->protocol = $this->getConfig()->get('config_mail_protocol');
			$mail->parameter = $this->getConfig()->get('config_mail_parameter');
			$mail->hostname = $this->getConfig()->get('config_smtp_host');
			$mail->username = $this->getConfig()->get('config_smtp_username');
			$mail->password = $this->getConfig()->get('config_smtp_password');
			$mail->port = $this->getConfig()->get('config_smtp_port');
			$mail->timeout = $this->getConfig()->get('config_smtp_timeout');
			$mail->setTo($customer_info['email']);
			$mail->setFrom($this->getConfig()->get('config_email'));
			$mail->setSender($store_name);
			$mail->setSubject(sprintf($this->getLanguage()->get('text_reward_subject'), $store_name));
			$mail->setText($message);
			$mail->send();
		}
	}

	public function deleteReward($order_id) {
		$this->getDb()->query("DELETE FROM customer_reward WHERE order_id = '" . (int)$order_id . "'");
	}
	
	public function getRewards($customer_id, $start = 0, $limit = 10) {
		$query = $this->getDb()->query("SELECT * FROM customer_reward WHERE customer_id = '" . (int)$customer_id . "' ORDER BY date_added DESC LIMIT " . (int)$start . "," . (int)$limit);
	
		return $query->rows;
	}
	
	public function getTotalRewards($customer_id) {
		$query = $this->getDb()->query("SELECT COUNT(*) AS total FROM customer_reward WHERE customer_id = '" . (int)$customer_id . "'");
	
		return $query->row['total'];
	}
			
	public function getRewardTotal($customer_id) {
		$query = $this->getDb()->query("SELECT SUM(points) AS total FROM customer_reward WHERE customer_id = '" . (int)$customer_id . "'");
	
		return $query->row['total'];
	}		
	
	public function getTotalCustomerRewardsByOrderId($order_id) {
		$query = $this->getDb()->query("SELECT COUNT(*) AS total FROM customer_reward WHERE order_id = '" . (int)$order_id . "'");
	
		return $query->row['total'];
	}
	
	public function getIpsByCustomerId($customer_id) {
		$query = $this->getDb()->query("SELECT * FROM customer_ip WHERE customer_id = '" . (int)$customer_id . "'");

		return $query->rows;
	}	
	
	public function getTotalCustomersByIp($ip) {
		$query = $this->getDb()->query("SELECT COUNT(*) AS total FROM customer_ip WHERE ip = '" . $this->getDb()->escape($ip) . "'");

		return $query->row['total'];
	}

    public function purgeCart($customerId)
    {
        $this->getDb()->query("
            UPDATE customer
            SET purge_cart = 1
            WHERE customer_id = " . (int)$customerId
        );
        $this->getCache()->delete('customer.' . $customerId);
    }
}
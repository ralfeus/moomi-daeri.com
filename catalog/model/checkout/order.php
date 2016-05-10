<?php
class ModelCheckoutOrder extends Model {	
	public function addOrderItems($orderId, $order_items)
	{
        /// Get totals from DB and cart
        $totalData = array();
        $total = 0;
        $taxes = 0;
        $this->getTotals($totalData, $total, $taxes, $orderId, true);

		foreach ($order_items as $order_item)
		{
			$this->getDb()->query("
				INSERT INTO order_product
				(order_id, product_id, name, model, quantity, price, total, tax)
				VALUES(" . 
					(int)$orderId . ", " .
					(int)$order_item['product_id'] . ", '" . 
					$this->getDb()->escape($order_item['name']) . "', '" .
					$this->getDb()->escape($order_item['model']) . "', " .
					(int)$order_item['quantity'] . ", " .
					(float)$order_item['price'] . ", " .
					(float)$order_item['total'] . ", " .
					(float)$order_item['tax'] . ")
			");			
			$order_item_id = $this->getDb()->getLastId();
            /// Set initial status of newly added order item
            $this->setOrderItemStatus($order_item_id, $this->getOrderItemInitialStatus($order_item));
			/// Add order item options
			if (count($order_item['options']))
			{
				$records = "";
				foreach ($order_item['options'] as $option) 
				{
					$records .= "(" .
						(int)$orderId . ", " .
						(int)$order_item_id . ", " .
						(int)$option['product_option_id'] . ", " .
						(int)$option['product_option_value_id'] . ", '" .
						$this->getDb()->escape($option['name']) . "', '" .
						$this->getDb()->escape($option['option_value']) . "', '" .
						$this->getDb()->escape($option['type']) . "'
					),";
				}
				$records = rtrim($records, ",");
				$this->getDb()->query("
					INSERT INTO order_option
					(order_id, order_product_id, product_option_id,	product_option_value_id, name, `value`, `type`)
					VALUES
					$records
				");
			}
		
			/// Add order item downloads (whatever it is)
			if (count($order_item['download'])) 
			{
				$records = "";
				foreach ($order_item['download'] as $download) 
				{
					$records .= "VALUES(" .
						(int)$orderId . ", " .
						(int)$order_item_id . ", '" .
						$this->getDb()->escape($download['name']) . "', '" .
						$this->getDb()->escape($download['filename']) . "', '" .
						$this->getDb()->escape($download['mask']) . "', " .
						(int)($download['remaining'] * $order_item['quantity']) . "
					),";
				}
				$records = rtrim($records, ",");
				$this->getDb()->query("
					INSERT INTO order_download
					(order_id, order_product_id, name, filename, mask, remaining)
					$records
				");
			}
		}
        $this->updateTotals($orderId, $totalData);
	}
	
	public function create($data) {
        $sql = "
			INSERT INTO `order`
			SET 
				invoice_prefix = '" . $this->getDb()->escape($data['invoice_prefix']) . "',
				store_id = '" . (int)$data['store_id'] . "', 
				store_name = '" . $this->getDb()->escape($data['store_name']) . "',
				store_url = '" . $this->getDb()->escape($data['store_url']) . "',
				customer_id = '" . (int)$data['customer_id'] . "', 
				customer_group_id = '" . (int)$data['customer_group_id'] . "', 
				firstname = '" . $this->getDb()->escape($data['firstname']) . "',
				lastname = '" . $this->getDb()->escape($data['lastname']) . "',
				email = '" . $this->getDb()->escape($data['email']) . "',
				telephone = '" . $this->getDb()->escape($data['telephone']) . "',
				fax = '" . $this->getDb()->escape($data['fax']) . "',
				shipping_firstname = '" . $this->getDb()->escape($data['shipping_firstname']) . "',
				shipping_lastname = '" . $this->getDb()->escape($data['shipping_lastname']) . "',
				shipping_company = '" . $this->getDb()->escape($data['shipping_company']) . "',
				shipping_phone = '" . $this->getDb()->escape($data['shipping_phone']) . "',
				shipping_address_1 = '" . $this->getDb()->escape($data['shipping_address_1']) . "',
				shipping_address_2 = '" . $this->getDb()->escape($data['shipping_address_2']) . "',
				shipping_city = '" . $this->getDb()->escape($data['shipping_city']) . "',
				shipping_postcode = '" . $this->getDb()->escape($data['shipping_postcode']) . "',
				shipping_country = '" . $this->getDb()->escape($data['shipping_country']) . "',
				shipping_country_id = '" . (int)$data['shipping_country_id'] . "', 
				shipping_zone = '" . $this->getDb()->escape($data['shipping_zone']) . "',
				shipping_zone_id = '" . (int)$data['shipping_zone_id'] . "', 
				shipping_address_format = '" . $this->getDb()->escape($data['shipping_address_format']) . "',
				shipping_method = '" . $this->getDb()->escape($data['shipping_method']) . "',
				payment_firstname = '" . $this->getDb()->escape($data['shipping_firstname']) . "',
				payment_lastname = '" . $this->getDb()->escape($data['shipping_lastname']) . "',
				payment_company = '" . $this->getDb()->escape($data['shipping_company']) . "',
				payment_address_1 = '" . $this->getDb()->escape($data['shipping_address_1']) . "',
				payment_address_2 = '" . $this->getDb()->escape($data['shipping_address_2']) . "',
				payment_city = '" . $this->getDb()->escape($data['shipping_city']) . "',
				payment_postcode = '" . $this->getDb()->escape($data['shipping_postcode']) . "',
				payment_country = '" . $this->getDb()->escape($data['shipping_country']) . "',
				payment_country_id = '" . (int)$data['shipping_country_id'] . "', 
				payment_zone = '" . $this->getDb()->escape($data['shipping_zone']) . "',
				payment_zone_id = '" . (int)$data['shipping_zone_id'] . "', 
				payment_address_format = '" . $this->getDb()->escape($data['shipping_address_format']) . "',
				payment_method = '" . $this->getDb()->escape($data['shipping_method']) . "',
				comment = '" . $this->getDb()->escape($data['comment']) . "',
				total = '" . (float)$data['total'] . "', 
				reward = '" . (float)$data['reward'] . "', 
				affiliate_id = '" . (int)$data['affiliate_id'] . "', 
				commission = '" . (float)$data['commission'] . "', 
				language_id = '" . (int)$data['language_id'] . "', 
				currency_id = '" . (int)$data['currency_id'] . "', 
				currency_code = '" . $this->getDb()->escape($data['currency_code']) . "',
				currency_value = '" . (float)$data['currency_value'] . "', 
				ip = '" . $this->getDb()->escape($data['ip']) . "',
				date_added = NOW(), 
				date_modified = NOW()";
        //$this->log->write($sql);
        $this->getDb()->query($sql);
		$order_id = $this->getDb()->getLastId();

		foreach ($data['products'] as $product) { 
			$this->getDb()->query("
			    INSERT INTO order_product
			    SET
			        order_id = '" . (int)$order_id . "',
			        product_id = '" . (int)$product['product_id'] . "',
			        name = '" . $this->getDb()->escape($product['name']) . "',
			        model = '" . $this->getDb()->escape($product['model']) . "',
			        quantity = '" . (int)$product['quantity'] . "',
			        price = '" . (float)$product['price'] . "',
			        status_id = " . $this->getOrderItemInitialStatus($product) . ",
			        total = '" . (float)$product['total'] . "',
			        tax = '" . (float)$product['tax'] . "'
            ");
            $order_product_id = $this->getDb()->getLastId();

			foreach ($product['option'] as $option) {
				$this->getDb()->query("
				    INSERT INTO order_option
				    SET
				        order_id = :orderId,
				        order_product_id = :orderProductId,
				        product_option_id = :productOptionId,
				        product_option_value_id = :productOptionValueId,
				        name = :name,
				        `value` = :value,
				        `type` = :type
                ", array(
                    ':orderId' => $order_id,
                    ':orderProductId' => $order_product_id,
                    ':productOptionId' => $option['product_option_id'],
                    ':productOptionValueId' => $option['product_option_value_id'],
                    ':name' => $option['name'],
                    ':value' => $option['value'],
                    ':type' => $option['type']
                ));
			}
				
			foreach ($product['download'] as $download) {
				$this->getDb()->query("INSERT INTO order_download SET order_id = '" . (int)$order_id . "', order_product_id = '" . (int)$order_product_id . "', name = '" . $this->getDb()->escape($download['name']) . "', filename = '" . $this->getDb()->escape($download['filename']) . "', mask = '" . $this->getDb()->escape($download['mask']) . "', remaining = '" . (int)($download['remaining'] * $product['quantity']) . "'");
			}	
		}
		
		foreach ($data['totals'] as $total) {
			$this->getDb()->query("INSERT INTO order_total SET order_id = '" . (int)$order_id . "', code = '" . $this->getDb()->escape($total['code']) . "', title = '" . $this->getDb()->escape($total['title']) . "', text = '" . $this->getDb()->escape($total['text']) . "', `value` = '" . (float)$total['value'] . "', sort_order = '" . (int)$total['sort_order'] . "'");
		}	

		return $order_id;
	}

	public function getOrder($order_id) {
		$order_query = $this->getDb()->query("SELECT *, (SELECT os.name FROM `order_status` os WHERE os.order_status_id = o.order_status_id AND os.language_id = o.language_id) AS order_status FROM `order` o WHERE o.order_id = '" . (int)$order_id . "'");
			
		if ($order_query->num_rows) {
			$country_query = $this->getDb()->query("SELECT * FROM `country` WHERE country_id = '" . (int)$order_query->row['shipping_country_id'] . "'");
			
			if ($country_query->num_rows) {
				$shipping_iso_code_2 = $country_query->row['iso_code_2'];
				$shipping_iso_code_3 = $country_query->row['iso_code_3'];
			} else {
				$shipping_iso_code_2 = '';
				$shipping_iso_code_3 = '';				
			}
			
			$zone_query = $this->getDb()->query("SELECT * FROM `zone` WHERE zone_id = '" . (int)$order_query->row['shipping_zone_id'] . "'");
			
			if ($zone_query->num_rows) {
				$shipping_zone_code = $zone_query->row['code'];
			} else {
				$shipping_zone_code = '';
			}
			
			$country_query = $this->getDb()->query("SELECT * FROM `country` WHERE country_id = '" . (int)$order_query->row['shipping_country_id'] . "'");
			
			if ($country_query->num_rows) {
				$payment_iso_code_2 = $country_query->row['iso_code_2'];
				$payment_iso_code_3 = $country_query->row['iso_code_3'];
			} else {
				$payment_iso_code_2 = '';
				$payment_iso_code_3 = '';				
			}
			
			$zone_query = $this->getDb()->query("SELECT * FROM `zone` WHERE zone_id = '" . (int)$order_query->row['shipping_zone_id'] . "'");
			
			if ($zone_query->num_rows) {
				$payment_zone_code = $zone_query->row['code'];
			} else {
				$payment_zone_code = '';
			}

			$this->load->model('localisation/language');
			
			$language_info = $this->model_localisation_language->getLanguageInfo($order_query->row['language_id']);
			
			if ($language_info) {
				$language_code = $language_info['code'];
				$language_filename = $language_info['filename'];
				$language_directory = $language_info['directory'];
			} else {
				$language_code = '';
				$language_filename = '';
				$language_directory = '';
			}
		 			
			return array(
				'order_id'                => $order_query->row['order_id'],
				'invoice_no'              => $order_query->row['invoice_no'],
				'invoice_prefix'          => $order_query->row['invoice_prefix'],
				'store_id'                => $order_query->row['store_id'],
				'store_name'              => $order_query->row['store_name'],
				'store_url'               => $order_query->row['store_url'],				
				'customer_id'             => $order_query->row['customer_id'],
				'firstname'               => $order_query->row['firstname'],
				'lastname'                => $order_query->row['lastname'],
				'telephone'               => $order_query->row['telephone'],
				'fax'                     => $order_query->row['fax'],
				'email'                   => $order_query->row['email'],
				'shipping_firstname'      => $order_query->row['shipping_firstname'],
				'shipping_lastname'       => $order_query->row['shipping_lastname'],				
				'shipping_company'        => $order_query->row['shipping_company'],
				'shipping_address_1'      => $order_query->row['shipping_address_1'],
				'shipping_address_2'      => $order_query->row['shipping_address_2'],
				'shipping_postcode'       => $order_query->row['shipping_postcode'],
				'shipping_city'           => $order_query->row['shipping_city'],
				'shipping_zone_id'        => $order_query->row['shipping_zone_id'],
				'shipping_zone'           => $order_query->row['shipping_zone'],
				'shipping_zone_code'      => $shipping_zone_code,
				'shipping_country_id'     => $order_query->row['shipping_country_id'],
				'shipping_country'        => $order_query->row['shipping_country'],	
				'shipping_iso_code_2'     => $shipping_iso_code_2,
				'shipping_iso_code_3'     => $shipping_iso_code_3,
				'shipping_address_format' => $order_query->row['shipping_address_format'],
				'shipping_method'         => $order_query->row['shipping_method'],
				'payment_firstname'       => $order_query->row['shipping_firstname'],
				'payment_lastname'        => $order_query->row['shipping_lastname'],				
				'payment_company'         => $order_query->row['shipping_company'],
				'payment_address_1'       => $order_query->row['shipping_address_1'],
				'payment_address_2'       => $order_query->row['shipping_address_2'],
				'payment_postcode'        => $order_query->row['shipping_postcode'],
				'payment_city'            => $order_query->row['shipping_city'],
				'payment_zone_id'         => $order_query->row['shipping_zone_id'],
				'payment_zone'            => $order_query->row['shipping_zone'],
				'payment_zone_code'       => $payment_zone_code,
				'payment_country_id'      => $order_query->row['shipping_country_id'],
				'payment_country'         => $order_query->row['shipping_country'],	
				'payment_iso_code_2'      => $payment_iso_code_2,
				'payment_iso_code_3'      => $payment_iso_code_3,
				'payment_address_format'  => $order_query->row['shipping_address_format'],
				'payment_method'          => $order_query->row['payment_method'],
				'comment'                 => $order_query->row['comment'],
				'total'                   => $order_query->row['total'],
				'order_status_id'         => $order_query->row['order_status_id'],
				'order_status'            => $order_query->row['order_status'],
				'language_id'             => $order_query->row['language_id'],
				'language_code'           => $language_code,
				'language_filename'       => $language_filename,
				'language_directory'      => $language_directory,
				'currency_id'             => $order_query->row['currency_id'],
				'currency_code'           => $order_query->row['currency_code'],
				'currency_value'          => $order_query->row['currency_value'],
				'date_modified'           => $order_query->row['date_modified'],
				'date_added'              => $order_query->row['date_added'],
				'ip'                      => $order_query->row['ip']
			);
		} else {
			return false;	
		}
	}	

	public function confirm($order_id, $order_status_id = ORDER_STATUS_IN_PROGRESS, $comment = '', $notify = false) {
        $order_status_id = ORDER_STATUS_IN_PROGRESS; // TODO: In Progress. Will be fixed
		$orderInfo = $this->getOrder($order_id);
		 
		if ($orderInfo && !$orderInfo['order_status_id']) {
			$this->getDb()->query("
			    UPDATE `order`
			    SET
			        order_status_id = '" . (int)$order_status_id . "',
			        date_modified = NOW()
                WHERE order_id = " . (int)$order_id
            );

			$this->getDb()->query("INSERT INTO order_history SET order_id = '" . (int)$order_id . "', order_status_id = '" . (int)$order_status_id . "', notify = '1', comment = '" . $this->getDb()->escape(($comment && $notify) ? $comment : '') . "', date_added = NOW()");

			$order_product_query = $this->getDb()->query("SELECT * FROM order_product WHERE order_id = '" . (int)$order_id . "'");

			foreach ($order_product_query->rows as $order_product) {
                /// Add initial statuses of each ordered item
                $this->getDb()->query("
                    UPDATE order_product
                    SET
                        status_id = ?
                    WHERE order_product_id = ?
                    ", array("i:" . $this->getOrderItemInitialStatus($order_product), "i:" . $order_product['order_product_id'])
                );

				$this->getDb()->query("UPDATE product SET quantity = (quantity - " . (int)$order_product['quantity'] . ") WHERE product_id = '" . (int)$order_product['product_id'] . "' AND subtract = '1'");
				
				$order_option_query = $this->getDb()->query("SELECT * FROM order_option WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$order_product['order_product_id'] . "'");
			
				foreach ($order_option_query->rows as $option) {
					$this->getDb()->query("UPDATE product_option_value SET quantity = (quantity - " . (int)$order_product['quantity'] . ") WHERE product_option_value_id = '" . (int)$option['product_option_value_id'] . "' AND subtract = '1'");
				}
			}
			
			$this->cache->delete('product');
			
			$order_total_query = $this->getDb()->query("SELECT * FROM `order_total` WHERE order_id = '" . (int)$order_id . "'");
			
			foreach ($order_total_query->rows as $order_total) {
				$this->load->model('total/' . $order_total['code']);
				
				if (method_exists($this->{'model_total_' . $order_total['code']}, 'confirm')) {
					$this->{'model_total_' . $order_total['code']}->confirm($orderInfo, $order_total);
				}
			}
			
			// Send out any gift voucher mails
			if ($this->config->get('config_complete_status_id') == $order_status_id) {
				$this->load->model('checkout/voucher');
				$this->model_checkout_voucher->confirm($order_id);
			}
            try { $this->sendMail($orderInfo, $comment, $notify); }
            catch (Exception $exc)
            {
                $this->log->write("Couldn't send a mail.\n" . print_r($exc, true));
            }
			if ($this->config->get('config_sms_alert'))
				$this->sendSms($orderInfo);
		}
	}
	
	public function update($order_id, $order_status_id, $comment = '', $notify = false) {
		$order_info = $this->getOrder($order_id);

		if ($order_info && $order_info['order_status_id']) {
			$this->getDb()->query("UPDATE `order` SET order_status_id = '" . (int)$order_status_id . "', date_modified = NOW() WHERE order_id = '" . (int)$order_id . "'");
		
			$this->getDb()->query("INSERT INTO order_history SET order_id = '" . (int)$order_id . "', order_status_id = '" . (int)$order_status_id . "', notify = '" . (int)$notify . "', comment = '" . $this->getDb()->escape($comment) . "', date_added = NOW()");
	
			// Send out any gift voucher mails
			if ($this->config->get('config_complete_status_id') == $order_status_id) {
				$this->load->model('checkout/voucher');
	
				$this->model_checkout_voucher->confirm($order_id);
			}	
	
			if ($notify) {
				$language = new Language($order_info['language_directory']);
				$language->load($order_info['language_filename']);
				$language->load('mail/order');
			
				$subject = sprintf($language->get('text_update_subject'), html_entity_decode($order_info['store_name'], ENT_QUOTES, 'UTF-8'), $order_id);
	
				$message  = $language->get('text_update_order') . ' ' . $order_id . "\n";
				$message .= $language->get('text_update_date_added') . ' ' . date($language->get('date_format_short'), strtotime($order_info['date_added'])) . "\n\n";
				
				$order_status_query = $this->getDb()->query("SELECT * FROM order_status WHERE order_status_id = '" . (int)$order_status_id . "' AND language_id = '" . (int)$order_info['language_id'] . "'");
				
				if ($order_status_query->num_rows) {
					$message .= $language->get('text_update_order_status') . "\n\n";
					$message .= $order_status_query->row['name'] . "\n\n";					
				}
				
				if ($order_info['customer_id']) {
					$message .= $language->get('text_update_link') . "\n";
					$message .= $order_info['store_url'] . 'index.php?route=account/order/info&order_id=' . $order_id . "\n\n";
				}
				
				if ($comment) { 
					$message .= $language->get('text_update_comment') . "\n\n";
					$message .= $comment . "\n\n";
				}
					
				$message .= $language->get('text_update_footer');

				$mail = new Mail();
				$mail->protocol = $this->config->get('config_mail_protocol');
				$mail->parameter = $this->config->get('config_mail_parameter');
				$mail->hostname = $this->config->get('config_smtp_host');
				$mail->username = $this->config->get('config_smtp_username');
				$mail->password = $this->config->get('config_smtp_password');
				$mail->port = $this->config->get('config_smtp_port');
				$mail->timeout = $this->config->get('config_smtp_timeout');				
				$mail->setTo($order_info['email']);
				$mail->setFrom($this->config->get('config_email'));
				$mail->setSender($order_info['store_name']);
				$mail->setSubject($subject);
				$mail->setText(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
				$mail->send();
			}
		}
	}

    private function  getOrderItemInitialStatus($orderItem)
    {
        if ($orderItem['product_id'] == REPURCHASE_ORDER_PRODUCT_ID)
            return REPURCHASE_ORDER_ITEM_STATUS_WAITING;
        else
            return ORDER_ITEM_STATUS_WAITING;
    }

    public function getTotals(&$total_data, &$total, &$taxes, $orderId = null, $chosenOnes = false)
    {

        $this->load->model('setting/extension');

        $sort_order = array();

        $results = \model\setting\ExtensionDAO::getInstance()->getExtensions('total');
        foreach ($results as $key => $value) {
            $sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
        }
        array_multisort($sort_order, SORT_ASC, $results);

        foreach ($results as $result) {
            if ($this->config->get($result['code'] . '_status')) {
                $this->load->model('total/' . $result['code']);

                if ($orderId)
                    $this->{'model_total_' . $result['code']}->getOrderTotal($total_data, $total, $taxes, $orderId, $chosenOnes);
                else
                    $this->{'model_total_' . $result['code']}->getTotal($total_data, $total, $taxes, $chosenOnes);
            }
        }

        $sort_order = array();
        foreach ($total_data as $key => $value)
            $sort_order[$key] = $value['sort_order'];
        array_multisort($sort_order, SORT_ASC, $total_data);
    }

    private function sendMail($orderInfo, $comment, $notify)
    {
        // Send out order confirmation mail
        $language = new Language($orderInfo['language_directory']);
        $language->load($orderInfo['language_filename']);
        $language->load('mail/order');

        $order_status_query = $this->getDb()->query("SELECT * FROM order_status WHERE order_status_id = '" . (int)$orderInfo['order_status_id'] . "' AND language_id = '" . (int)$orderInfo['language_id'] . "'");

        if ($order_status_query->num_rows) {
            $order_status = $order_status_query->row['name'];
        } else {
            $order_status = '';
        }

        $order_product_query = $this->getDb()->query("SELECT * FROM order_product WHERE order_id = " . (int)$orderInfo['order_id']);
        $order_total_query = $this->getDb()->query("SELECT * FROM order_total WHERE order_id = " . (int)$orderInfo['order_id'] . " ORDER BY sort_order ASC");
        $order_download_query = $this->getDb()->query("SELECT * FROM order_download WHERE order_id = " . (int)$orderInfo['order_id']);

        $subject = sprintf($language->get('text_new_subject'), $orderInfo['store_name'], $orderInfo['order_id']);

        // HTML Mail
        $template = new Template();

        $template->data['title'] = sprintf($language->get('text_new_subject'), html_entity_decode($orderInfo['store_name'], ENT_QUOTES, 'UTF-8'), $orderInfo['order_id']);

        $template->data['text_greeting'] = sprintf($language->get('text_new_greeting'), html_entity_decode($orderInfo['store_name'], ENT_QUOTES, 'UTF-8'));
        $template->data['text_link'] = $language->get('text_new_link');
        $template->data['text_download'] = $language->get('text_new_download');
        $template->data['text_order_detail'] = $language->get('text_new_order_detail');
        $template->data['text_instruction'] = $language->get('text_new_instruction');
        $template->data['text_order_id'] = $language->get('text_new_order_id');
        $template->data['text_date_added'] = $language->get('text_new_date_added');
        $template->data['text_payment_method'] = $language->get('text_new_payment_method');
        $template->data['text_shipping_method'] = $language->get('text_new_shipping_method');
        $template->data['text_email'] = $language->get('text_new_email');
        $template->data['text_telephone'] = $language->get('text_new_telephone');
        $template->data['text_ip'] = $language->get('text_new_ip');
        $template->data['text_payment_address'] = $language->get('text_new_payment_address');
        $template->data['text_shipping_address'] = $language->get('text_new_shipping_address');
        $template->data['text_product'] = $language->get('text_new_product');
        $template->data['text_model'] = $language->get('text_new_model');
        $template->data['text_quantity'] = $language->get('text_new_quantity');
        $template->data['text_price'] = $language->get('text_new_price');
        $template->data['text_total'] = $language->get('text_new_total');
        $template->data['text_footer'] = $language->get('text_new_footer');
        $template->data['text_powered'] = $language->get('text_new_powered');

        $template->data['logo'] = 'cid:' . md5(basename($this->config->get('config_logo')));
        $template->data['store_name'] = $orderInfo['store_name'];
        $template->data['store_url'] = $orderInfo['store_url'];
        $template->data['customer_id'] = $orderInfo['customer_id'];
        $template->data['link'] = $orderInfo['store_url'] . 'index.php?route=account/order/info&order_id=' . $orderInfo['order_id'];

        if ($order_download_query->num_rows) {
            $template->data['download'] = $orderInfo['store_url'] . 'index.php?route=account/download';
        } else {
            $template->data['download'] = '';
        }

        $template->data['order_id'] = $orderInfo['order_id'];
        $template->data['date_added'] = date($language->get('date_format_short'), strtotime($orderInfo['date_added']));
        $template->data['payment_method'] = $orderInfo['payment_method'];
        $template->data['shipping_method'] = $orderInfo['shipping_method'];
        $template->data['email'] = $orderInfo['email'];
        $template->data['telephone'] = $orderInfo['telephone'];
        $template->data['ip'] = $orderInfo['ip'];

        if ($comment && $notify) {
            $template->data['comment'] = nl2br($comment);
        } else {
            $template->data['comment'] = '';
        }

        if ($orderInfo['shipping_address_format']) {
            $format = $orderInfo['shipping_address_format'];
        } else {
            $format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
        }

        $find = array(
            '{firstname}',
            '{lastname}',
            '{company}',
            '{address_1}',
            '{address_2}',
            '{city}',
            '{postcode}',
            '{zone}',
            '{zone_code}',
            '{country}'
        );

        $replace = array(
            'firstname' => $orderInfo['shipping_firstname'],
            'lastname'  => $orderInfo['shipping_lastname'],
            'company'   => $orderInfo['shipping_company'],
            'address_1' => $orderInfo['shipping_address_1'],
            'address_2' => $orderInfo['shipping_address_2'],
            'city'      => $orderInfo['shipping_city'],
            'postcode'  => $orderInfo['shipping_postcode'],
            'zone'      => $orderInfo['shipping_zone'],
            'zone_code' => $orderInfo['shipping_zone_code'],
            'country'   => $orderInfo['shipping_country']
        );

        $template->data['shipping_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

        if ($orderInfo['payment_address_format']) {
            $format = $orderInfo['payment_address_format'];
        } else {
            $format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
        }

        $find = array(
            '{firstname}',
            '{lastname}',
            '{company}',
            '{address_1}',
            '{address_2}',
            '{city}',
            '{postcode}',
            '{zone}',
            '{zone_code}',
            '{country}'
        );

        $replace = array(
            'firstname' => $orderInfo['payment_firstname'],
            'lastname'  => $orderInfo['payment_lastname'],
            'company'   => $orderInfo['payment_company'],
            'address_1' => $orderInfo['payment_address_1'],
            'address_2' => $orderInfo['payment_address_2'],
            'city'      => $orderInfo['payment_city'],
            'postcode'  => $orderInfo['payment_postcode'],
            'zone'      => $orderInfo['payment_zone'],
            'zone_code' => $orderInfo['payment_zone_code'],
            'country'   => $orderInfo['payment_country']
        );

        $template->data['payment_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

        $template->data['products'] = array();

        foreach ($order_product_query->rows as $product) {

            $option_data = array();

            $order_option_query = $this->getDb()->query("SELECT * FROM order_option WHERE order_id = '" . (int)$orderInfo['order_id'] . "' AND order_product_id = '" . (int)$product['order_product_id'] . "'");

            foreach ($order_option_query->rows as $option) {
                if ($option['type'] != 'file') {
                    $option_data[] = array(
                        'name'  => $option['name'],
                        'value' => utf8_truncate($option['value'])
                    );
                } else {
                    $filename = substr($option['value'], 0, strrpos($option['value'], '.'));

                    $option_data[] = array(
                        'name'  => $option['name'],
                        'value' => utf8_truncate($filename)
                    );
                }
            }

            $template->data['products'][] = array(
                'name'     => $product['name'],
                'model'    => $product['model'],
                'option'   => $option_data,
                'quantity' => $product['quantity'],
                'price'    => $this->currency->format($product['price'], $orderInfo['currency_code'], $orderInfo['currency_value']),
                'total'    => $this->currency->format($product['total'], $orderInfo['currency_code'], $orderInfo['currency_value'])
            );
        }

        $template->data['totals'] = $order_total_query->rows;

        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/mail/order.tpl')) {
            $html = $template->fetch($this->config->get('config_template') . '/template/mail/order.tpl');
        } else {
            $html = $template->fetch('default/template/mail/order.tpl');
        }

        // Text Mail
        $text  = sprintf($language->get('text_new_greeting'), html_entity_decode($orderInfo['store_name'], ENT_QUOTES, 'UTF-8')) . "\n\n";
        $text .= $language->get('text_new_order_id') . ' ' . $orderInfo['order_id'] . "\n";
        $text .= $language->get('text_new_date_added') . ' ' . date($language->get('date_format_short'), strtotime($orderInfo['date_added'])) . "\n";
        $text .= $language->get('text_new_order_status') . ' ' . $order_status . "\n\n";

        if ($comment && $notify) {
            $text .= $language->get('text_new_instruction') . "\n\n";
            $text .= $comment . "\n\n";
        }

        $text .= $language->get('text_new_products') . "\n";

        foreach ($order_product_query->rows as $result) {
            $text .= $result['quantity'] . 'x ' . $result['name'] . ' (' . $result['model'] . ') ' . html_entity_decode($this->currency->format($result['total'], $orderInfo['currency_code'], $orderInfo['currency_value']), ENT_NOQUOTES, 'UTF-8') . "\n";

            $order_option_query = $this->getDb()->query("SELECT * FROM order_option WHERE order_id = '" . (int)$orderInfo['order_id'] . "' AND order_product_id = '" . $result['order_product_id'] . "'");

            foreach ($order_option_query->rows as $option) {
                $text .= chr(9) . '-' . $option['name'] . ' ' . utf8_truncate($option['value']) . "\n";
            }
        }

        $text .= "\n";

        $text .= $language->get('text_new_order_total') . "\n";

        foreach ($order_total_query->rows as $result) {
            $text .= $result['title'] . ' ' . html_entity_decode($result['text'], ENT_NOQUOTES, 'UTF-8') . "\n";
        }

        $text .= "\n";

        if ($orderInfo['customer_id']) {
            $text .= $language->get('text_new_link') . "\n";
            $text .= $orderInfo['store_url'] . 'index.php?route=account/order/info&order_id=' . $orderInfo['order_id'] . "\n\n";
        }

        if ($order_download_query->num_rows) {
            $text .= $language->get('text_new_download') . "\n";
            $text .= $orderInfo['store_url'] . 'index.php?route=account/download' . "\n\n";
        }

        if ($orderInfo['comment']) {
            $text .= $language->get('text_new_comment') . "\n\n";
            $text .= $orderInfo['comment'] . "\n\n";
        }

        $text .= $language->get('text_new_footer') . "\n\n";

        $mail = new Mail();
        $mail->protocol = $this->config->get('config_mail_protocol');
        $mail->parameter = $this->config->get('config_mail_parameter');
        $mail->hostname = $this->config->get('config_smtp_host');
        $mail->username = $this->config->get('config_smtp_username');
        $mail->password = $this->config->get('config_smtp_password');
        $mail->port = $this->config->get('config_smtp_port');
        $mail->timeout = $this->config->get('config_smtp_timeout');
        $mail->setTo($orderInfo['email']);
        $mail->setFrom($this->config->get('config_email'));
        $mail->setSender($orderInfo['store_name']);
        $mail->setSubject($subject);
        $mail->setHtml($html);
        $mail->setText(html_entity_decode($text, ENT_QUOTES, 'UTF-8'));
        $mail->addAttachment(DIR_IMAGE . $this->config->get('config_logo'), md5(basename($this->config->get('config_logo'))));
        $mail->send();

        // Admin Alert Mail
        if ($this->config->get('config_alert_mail')) {
            $subject = sprintf($language->get('text_new_subject'), html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'), $orderInfo['order_id']);

            // Text
            $text  = $language->get('text_new_received') . "\n\n";
            $text .= $language->get('text_new_order_id') . ' ' . $orderInfo['order_id'] . "\n";
            $text .= $language->get('text_new_date_added') . ' ' . date($language->get('date_format_short'), strtotime($orderInfo['date_added'])) . "\n";
            $text .= $language->get('text_new_order_status') . ' ' . $order_status . "\n\n";
            $text .= $language->get('text_new_products') . "\n";

            foreach ($order_product_query->rows as $result) {
                $text .= $result['quantity'] . 'x ' . $result['name'] . ' (' . $result['model'] . ') ' . html_entity_decode($this->currency->format($result['total'], $orderInfo['currency_code'], $orderInfo['currency_value']), ENT_NOQUOTES, 'UTF-8') . "\n";

                $order_option_query = $this->getDb()->query("SELECT * FROM order_option WHERE order_id = '" . (int)$orderInfo['order_id'] . "' AND order_product_id = '" . $result['order_product_id'] . "'");

                foreach ($order_option_query->rows as $option) {
                    $text .= chr(9) . '-' . $option['name'] . ' ' . utf8_truncate($option['value']) . "\n";
                }
            }

            $text .= "\n";

            $text .= $language->get('text_new_order_total') . "\n";

            foreach ($order_total_query->rows as $result) {
                $text .= $result['title'] . ' ' . html_entity_decode($result['text'], ENT_NOQUOTES, 'UTF-8') . "\n";
            }

            $text .= "\n";

            if ($orderInfo['comment'] != '') {
                $comment = ($orderInfo['comment'] .  "\n\n" . $comment);
            }

            if ($comment) {
                $text .= $language->get('text_new_comment') . "\n\n";
                $text .= $comment . "\n\n";
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
            $mail->setSender($orderInfo['store_name']);
            $mail->setSubject($subject);
            $mail->setText($text);
            $mail->send();

            // Send to additional alert emails
            $emails = explode(',', $this->config->get('config_alert_emails'));

            foreach ($emails as $email) {
                if ($email && preg_match('/^[^\@]+@.*\.[a-z]{2,6}$/i', $email)) {
                    $mail->setTo($email);
                    $mail->send();
                }
            }
        }
    }

    private function sendSms($orderInfo)
    {
        $options = array(
            'to'       => $this->config->get('config_sms_to'),
            'copy'     => $this->config->get('config_sms_copy'),
            'from'     => $this->config->get('config_sms_from'),
            'username' => $this->config->get('config_sms_gate_username'),
            'password' => $this->config->get('config_sms_gate_password'),
            'message'  => str_replace(array('{ID}', '{DATE}', '{TIME}', '{SUM}', '{PHONE}'),
                array($orderInfo['order_id'], date('d.m.Y'), date('H:i'), floatval($orderInfo['total']), $orderInfo['telephone']),
                $this->config->get('config_sms_message'))
        );

        $this->load->library('sms');
        $sms = new Sms($this->config->get('config_sms_gatename'), $options);
        $sms->send();
    }

    private function setOrderItemStatus($orderItemId, $orderItemStatusId)
    {
//        $this->log->write("$order_item_id, $order_item_status_id");
        $this->getDb()->query("
            UPDATE order_product
            SET status_id = ?
            WHERE order_product_id = ?
            ", array("i:$orderItemStatusId", "i:$orderItemId")
        );
    }

    private function updateTotals($orderId, $totalData)
    {
        foreach ($totalData as $totalsElement) {
            $this->{'model_total_' . $totalsElement['code']}->updateOrderTotal($orderId, $totalsElement);
        }
    }
}
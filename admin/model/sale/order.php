<?php
use model\sale\CustomerDAO;
use model\sale\OrderItemDAO;
use model\shipping\ShippingMethodDAO;

class ModelSaleOrder extends \system\engine\Model {
	public function addOrder($data) { //echo "asdsadas"; die();
		$invoice_no = '';
		$total = '';

		$this->load->model('setting/store');

		$store_info = $this->model_setting_store->getStore($data['store_id']);

		if ($store_info) {
			$store_name = $store_info['name'];
			$store_url = $store_info['url'];
		} else {
			$store_name = $this->config->get('config_name');
			$store_url = HTTP_CATALOG;
		}

		$customer_info = CustomerDAO::getInstance()->getCustomer($data['customer_id']);

		if ($customer_info) {
			$customer_group_id = $customer_info['customer_group_id'];
		} elseif ($store_info) {
			$customer_group_id = $store_info['customer_group_id'];
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}

		$this->load->model('localisation/country');

		$country_info = $this->model_localisation_country->getCountry($data['shipping_country_id']);

		if ($country_info) {
			$shipping_country = $country_info['name'];
			$shipping_address_format = $country_info['address_format'];
		} else {
			$shipping_country = '';
			$shipping_address_format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
		}

		$this->load->model('localisation/zone');

		$zone_info = $this->model_localisation_zone->getZone($data['shipping_zone_id']);

		if ($zone_info) {
			$shipping_zone = $zone_info['name'];
		} else {
			$shipping_zone = '';
		}

		$country_info = $this->model_localisation_country->getCountry($data['payment_country_id']);

		if ($country_info) {
			$payment_country = $country_info['name'];
			$payment_address_format = $country_info['address_format'];
		} else {
			$payment_country = '';
			$payment_address_format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
		}

		$zone_info = $this->model_localisation_zone->getZone($data['payment_zone_id']);

		if ($zone_info) {
			$payment_zone = $zone_info['name'];
		} else {
			$payment_zone = '';
		}

		$this->load->model('localisation/currency');

		$currency_info = $this->model_localisation_currency->getCurrencyByCode($this->config->get('config_currency'));

		if ($currency_info) {
			$currency_id = $currency_info['currency_id'];
			$currency_code = $this->config->get('config_currency');
			$currency_value = $currency_info['value'];
		} else {
			$currency_id = 0;
			$currency_code = $currency_info['code'];
			$currency_value = 1.00000;
		}

      	$this->getDb()->query("INSERT INTO order SET invoice_no = '" . (int)$invoice_no . "', invoice_prefix = '" . $this->getDb()->escape($this->config->get('config_invoice_prefix')) . "', store_id = '" . (int)$data['store_id'] . "', store_name = '" . $this->getDb()->escape($store_name) . "', store_url = '" . $this->getDb()->escape($store_url) . "', customer_id = '" . (int)$data['customer_id'] . "', customer_group_id = '" . (int)$customer_group_id . "', firstname = '" . $this->getDb()->escape($data['firstname']) . "', lastname = '" . $this->getDb()->escape($data['lastname']) . "', email = '" . $this->getDb()->escape($data['email']) . "', telephone = '" . $this->getDb()->escape($data['telephone']) . "', shipping_phone = '" . $this->getDb()->escape($data['phone']) . "', fax = '" . $this->getDb()->escape($data['fax']) . "', shipping_firstname = '" . $this->getDb()->escape($data['shipping_firstname']) . "', shipping_lastname = '" . $this->getDb()->escape($data['shipping_lastname']) . "',  shipping_company = '" . $this->getDb()->escape($data['shipping_company']) . "', shipping_address_1 = '" . $this->getDb()->escape($data['shipping_address_1']) . "', shipping_address_2 = '" . $this->getDb()->escape($data['shipping_address_2']) . "', shipping_city = '" . $this->getDb()->escape($data['shipping_city']) . "', shipping_postcode = '" . $this->getDb()->escape($data['shipping_postcode']) . "', shipping_country = '" . $this->getDb()->escape($shipping_country) . "', shipping_country_id = '" . (int)$data['shipping_country_id'] . "', shipping_zone = '" . $this->getDb()->escape($shipping_zone) . "', shipping_zone_id = '" . (int)$data['shipping_zone_id'] . "', shipping_address_format = '" . $this->getDb()->escape($shipping_address_format) . "', shipping_method = '" . $this->getDb()->escape($data['shipping_method']) . "', payment_firstname = '" . $this->getDb()->escape($data['payment_firstname']) . "', payment_lastname = '" . $this->getDb()->escape($data['payment_lastname']) . "', payment_company = '" . $this->getDb()->escape($data['payment_company']) . "', payment_address_1 = '" . $this->getDb()->escape($data['payment_address_1']) . "', payment_address_2 = '" . $this->getDb()->escape($data['payment_address_2']) . "', payment_city = '" . $this->getDb()->escape($data['payment_city']) . "', payment_postcode = '" . $this->getDb()->escape($data['payment_postcode']) . "', payment_country = '" . $this->getDb()->escape($payment_country) . "', payment_country_id = '" . (int)$data['payment_country_id'] . "', payment_zone = '" . $this->getDb()->escape($payment_zone) . "', payment_zone_id = '" . (int)$data['payment_zone_id'] . "', payment_address_format = '" . $this->getDb()->escape($payment_address_format) . "', payment_method = '" . $this->getDb()->escape($data['payment_method']) . "', comment = '" . $this->getDb()->escape($data['comment']) . "', total = '" . (float)$total . "', order_status_id = '" . (int)$data['order_status_id'] . "', affiliate_id  = '" . (int)$data['affiliate_id'] . "', language_id = '" . (int)$data['affiliate_id'] . "', currency_id = '" . $this->getDb()->escape($this->config->get('config_currency')) . "', currency_code = '" . $this->getDb()->escape($currency_code) . "', currency_value = '" . (float)$currency_value . "', date_added = NOW(), date_modified = NOW()");

      	$order_id = $this->getDb()->getLastId();

      	if (isset($data['order_product'])) {
      		foreach ($data['order_product'] as $order_product) {
      			$this->getDb()->query("INSERT INTO order_product SET order_id = '" . (int)$order_id . "', product_id = '" . (int)$order_product['product_id'] . "', name = '" . $this->getDb()->escape($order_product['name']) . "', model = '" . $this->getDb()->escape($order_product['model']) . "', quantity = '" . (int)$order_product['quantity'] . "', price = '" . (float)$order_product['price'] . "', total = '" . (float)$order_product['total'] . "', tax = '" . (float)$order_product['tax'] . "'");

				$order_product_id = $this->getDb()->getLastId();

				foreach ($order_product['order_option'] as $option) {
					if ($option['type'] != 'checkbox') {
						$this->getDb()->query("
						    INSERT INTO order_option
						    SET
						        order_id = '" . (int)$order_id . "',
						        order_product_id = '" . (int)$order_product_id . "',
						        product_option_id = '" . (int)$option['product_option_id'] . "',
						        product_option_value_id = '" . (int)$option['product_option_value_id'] . "',
						        name = '" . $this->getDb()->escape($option['name']) . "',
						        `value` = '" . $this->getDb()->escape($option['value']) . "',
						        `type` = '" . $this->getDb()->escape($option['type']) . "'"
                        );
					} else {
						foreach ($option['option_value'] as $option_value) {
							$this->getDb()->query("
							    INSERT INTO order_option
							    SET
							        order_id = '" . (int)$order_id . "',
							        order_product_id = '" . (int)$order_product_id . "',
							        product_option_id = '" . (int)$option['product_option_id'] . "',
							        product_option_value_id = '" . (int)$option['product_option_value_id'] . "',
							        name = '" . $this->getDb()->escape($option['name']) . "',
							        `value` = '" . $this->getDb()->escape($option['value']) . "',
							        `type` = '" . $this->getDb()->escape($option['type']) . "'"
                            );
						}
					}
				}

				foreach ($order_product['download'] as $download) {
					$this->getDb()->query("
					    INSERT INTO order_download 
                        SET 
                            order_id = '" . (int)$order_id . "', 
                            order_product_id = '" . (int)$order_product_id . "', 
                            name = '" . $this->getDb()->escape($download['name']) . "', 
                            filename = '" . $this->getDb()->escape($download['filename']) . "', 
                            mask = '" . $this->getDb()->escape($download['mask']) . "', 
                            remaining = '" . (int)($download['remaining'] * $order_product['quantity']) . "'
                    ");
				}
			}
		}

        /// Unknown bullshit
//      	if (isset($data['order_total'])) {
//      		foreach ($data['order_total'] as $order_total) {
//      			$this->getDb()->query("
//      			    INSERT INTO order_total
//      			    SET
//      			        order_id = '" . (int)$order_id . "',
//      			        product_id = '" . (int)$return_product['product_id'] . "',
//      			        name = '" . $this->getDb()->escape($return_product['name']) . "',
//      			        model = '" . $this->getDb()->escape($return_product['model']) . "',
//      			        quantity = '" . (int)$return_product['quantity'] . "',
//      			        manufacturer = '" . (int)$return_product['manufacturer'] . "',
//      			        return_reason_id = '" . (int)$return_product['return_reason_id'] . "',
//      			        opened = '" . (int)$return_product['opened'] . "',
//      			        comment = '" . $this->getDb()->escape($return_product['comment']) . "',
//      			        return_action_id = '" . (int)$return_product['return_action_id'] . "'
//                ");
//			}
//		}
	}

    private function buildFilterString($data = array()) {
        $filter = "";
        if (isset($data['selected']) && count($data['selected'])) {
            $filter = "o.order_id in (" . implode(', ', $data['selected']) . ")";
        } else {
            if (!empty($data['filter_date_added']))
                $filter .= ($filter ? " AND " : "") . "DATE(o.date_added) = DATE('" . $this->getDb()->escape($data['filter_date_added']) . "')";
            if (!empty($data['filter_customer']))
                $filter .= ($filter ? " AND " : "") . "LCASE(CONCAT(o.firstname, ' ', o.lastname)) LIKE '" . $this->getDb()->escape(utf8_strtolower($data['filter_customer'])) . "%'";
            if (!empty($data['filter_order_id']))
                $filter .= ($filter ? " AND " : "") . "o.order_id = " . (int)$data['filter_order_id'];
            if (!empty($data['filter_order_status_id']))
                $filter .= ($filter ? " AND " : "") . "o.order_status_id = " . (int)$data['filter_order_status_id'];
            if (!empty($data['filter_total']))
                $filter .= ($filter ? " AND " : "") . "o.total = '" . (float)$data['filter_total'] . "'";
            if (!empty($data['filterCustomerId']))
                $filter .= ($filter ? " AND " : "") . "o.customer_id IN (" . implode(', ', $data['filterCustomerId']) . ")";
            if (!empty($data['filterStatusId']))
                $filter .= ($filter ? " AND " : "") . "o.order_status_id IN (" . implode(', ', $data['filterStatusId']) . ")";
            if (!empty($data['filterOrderId']))
                $filter .= ($filter ? " AND " : "") . "o.order_id = " . (int)$data['filterOrderId'];
        }
        return $filter;
    }


	public function editOrder($order_id, $data) {
		$this->getDb()->query("
		    UPDATE order 
		    SET 
		        order_id = '" . (int)$data['order_id'] . "', 
		        customer_id = '" . (int)$data['customer_id'] . "', 
		        invoice_no = '" . $this->getDb()->escape($data['invoice_no']) . "', 
		        invoice_date = '" . $this->getDb()->escape($data['invoice_date']) . "', 
		        firstname = '" . $this->getDb()->escape($data['firstname']) . "', 
		        lastname = '" . $this->getDb()->escape($data['lastname']) . "', 
		        email = '" . $this->getDb()->escape($data['email']) . "', 
		        telephone = '" . $this->getDb()->escape($data['telephone']) . "', 
		        fax = '" . $this->getDb()->escape($data['fax']) . "', 
		        company = '" . $this->getDb()->escape($data['company']) . "', 
		        address_1 = '" . $this->getDb()->escape($data['address_1']) . "', 
		        address_2 = '" . $this->getDb()->escape($data['address_2']) . "', 
		        city = '" . $this->getDb()->escape($data['city']) . "', 
		        postcode = '" . $this->getDb()->escape($data['postcode']) . "', 
		        country_id = '" . (int)$data['country_id'] . "', 
		        zone_id = '" . (int)$data['zone_id'] . "', 
		        return_status_id = '" . (int)$data['return_status_id'] . "', 
		        comment = '" . $this->getDb()->escape($data['comment']) . "', 
		        date_modified = NOW() 
            WHERE order_id = '" . (int)$order_id . "'");

		$this->getDb()->query("DELETE FROM order_product WHERE return_id = '" . (int)$order_id . "'");

		if (isset($data['return_product'])) {
      		foreach ($data['return_product'] as $return_product) {
      			$this->getDb()->query("
      			    INSERT INTO return_product 
      			    SET 
      			        order_id = '" . (int)$order_id . "', 
      			        product_id = '" . (int)$return_product['product_id'] . "', 
      			        name = '" . $this->getDb()->escape($return_product['name']) . "',
      			        model = '" . $this->getDb()->escape($return_product['model']) . "', 
      			        quantity = '" . (int)$return_product['quantity'] . "', 
      			        return_reason_id = '" . (int)$return_product['return_reason_id'] . "', 
      			        opened = '" . (int)$return_product['opened'] . "', 
      			        comment = '" . $this->getDb()->escape($return_product['comment']) . "', 
      			        return_action_id = '" . (int)$return_product['return_action_id'] . "'
                    ");
			}
		}
	}

	public function deleteOrder($order_id) {
		if ($this->config->get('config_stock_subtract')) {
			$order_query = $this->getDb()->query("SELECT * FROM `order` WHERE order_status_id > '0' AND order_id = '" . (int)$order_id . "'");

			if ($order_query->num_rows) {
				$product_query = $this->getDb()->query("SELECT * FROM order_product WHERE order_id = '" . (int)$order_id . "'");

				foreach($product_query->rows as $product) {
                    /// Remove ordered items status change history
                    $this->getDb()->query("
                        DELETE FROM ". DB_PREFIX ."order_item_history
                        WHERE order_item_id = " . (int)$product['product_id']
                    );

					$this->getDb()->query("UPDATE `product` SET quantity = (quantity + " . (int)$product['quantity'] . ") WHERE product_id = '" . (int)$product['product_id'] . "'");

					$option_query = $this->getDb()->query("SELECT * FROM order_option WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$product['order_product_id'] . "'");

					foreach ($option_query->rows as $option) {
						$this->getDb()->query("UPDATE product_option_value SET quantity = (quantity + " . (int)$product['quantity'] . ") WHERE product_option_value_id = '" . (int)$option['product_option_value_id'] . "' AND subtract = '1'");
					}
				}
			}
		}

		$this->getDb()->query("DELETE FROM `order` WHERE order_id = '" . (int)$order_id . "'");
      	$this->getDb()->query("DELETE FROM order_history WHERE order_id = '" . (int)$order_id . "'");
      	$this->getDb()->query("DELETE FROM order_product WHERE order_id = '" . (int)$order_id . "'");
      	$this->getDb()->query("DELETE FROM order_option WHERE order_id = '" . (int)$order_id . "'");
	  	$this->getDb()->query("DELETE FROM order_download WHERE order_id = '" . (int)$order_id . "'");
      	$this->getDb()->query("DELETE FROM order_total WHERE order_id = '" . (int)$order_id . "'");
//		$this->getDb()->query("DELETE FROM customer_transaction WHERE order_id = '" . (int)$order_id . "'");
		$this->getDb()->query("DELETE FROM customer_reward WHERE order_id = '" . (int)$order_id . "'");
		$this->getDb()->query("DELETE FROM affiliate_transaction WHERE order_id = '" . (int)$order_id . "'");
		$this->getDb()->query("DELETE FROM coupon_history WHERE order_id = '" . (int)$order_id . "'");
	}

	public function getOrder($order_id) {
		$order_query = $this->getDb()->query("
		    SELECT
		        *, CONCAT(firstname, ' ', lastname) AS customer
            FROM `order` o
            WHERE o.order_id = '" . (int)$order_id . "'
        ");

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

			$country_query = $this->getDb()->query("SELECT * FROM `country` WHERE country_id = '" . (int)$order_query->row['payment_country_id'] . "'");

			if ($country_query->num_rows) {
				$payment_iso_code_2 = $country_query->row['iso_code_2'];
				$payment_iso_code_3 = $country_query->row['iso_code_3'];
			} else {
				$payment_iso_code_2 = '';
				$payment_iso_code_3 = '';
			}

			$zone_query = $this->getDb()->query("SELECT * FROM `zone` WHERE zone_id = '" . (int)$order_query->row['payment_zone_id'] . "'");

			if ($zone_query->num_rows) {
				$payment_zone_code = $zone_query->row['code'];
			} else {
				$payment_zone_code = '';
			}

			if ($order_query->row['affiliate_id']) {
				$affiliate_id = $order_query->row['affiliate_id'];
			} else {
				$affiliate_id = 0;
			}

			$this->load->model('sale/affiliate');

			$affiliate_info = $this->model_sale_affiliate->getAffiliate($affiliate_id);

			if ($affiliate_info) {
				$affiliate_firstname = $affiliate_info['firstname'];
				$affiliate_lastname = $affiliate_info['lastname'];
			} else {
				$affiliate_firstname = '';
				$affiliate_lastname = '';
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
				'customer'                => $order_query->row['customer'],
				'customer_group_id'       => $order_query->row['customer_group_id'],
				'firstname'               => $order_query->row['firstname'],
				'lastname'                => $order_query->row['lastname'],
				'telephone'               => $order_query->row['telephone'],
				'fax'                     => $order_query->row['fax'],
				'email'                   => $order_query->row['email'],
				'shipping_firstname'      => $order_query->row['shipping_firstname'],
				'shipping_lastname'       => $order_query->row['shipping_lastname'],
				'shipping_company'        => $order_query->row['shipping_company'],
				'shipping_phone'      		=> $order_query->row['shipping_phone'],
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
				'payment_firstname'       => $order_query->row['payment_firstname'],
				'payment_lastname'        => $order_query->row['payment_lastname'],
				'payment_company'         => $order_query->row['payment_company'],
				'payment_address_1'       => $order_query->row['payment_address_1'],
				'payment_address_2'       => $order_query->row['payment_address_2'],
				'payment_postcode'        => $order_query->row['payment_postcode'],
				'payment_city'            => $order_query->row['payment_city'],
				'payment_zone_id'         => $order_query->row['payment_zone_id'],
				'payment_zone'            => $order_query->row['payment_zone'],
				'payment_zone_code'       => $payment_zone_code,
				'payment_country_id'      => $order_query->row['payment_country_id'],
				'payment_country'         => $order_query->row['payment_country'],
				'payment_iso_code_2'      => $payment_iso_code_2,
				'payment_iso_code_3'      => $payment_iso_code_3,
				'payment_address_format'  => $order_query->row['payment_address_format'],
				'payment_method'          => $order_query->row['payment_method'],
				'comment'                 => $order_query->row['comment'],
				'total'                   => $order_query->row['total'],
				'reward'                  => $order_query->row['reward'],
				'order_status_id'         => $order_query->row['order_status_id'],
				'affiliate_id'            => $order_query->row['affiliate_id'],
				'affiliate_firstname'     => $affiliate_firstname,
				'affiliate_lastname'      => $affiliate_lastname,
				'commission'              => $order_query->row['commission'],
				'language_id'             => $order_query->row['language_id'],
				'language_code'           => $language_code,
				'language_filename'       => $language_filename,
				'language_directory'      => $language_directory,
				'currency_id'             => $order_query->row['currency_id'],
				'currency_code'           => $order_query->row['currency_code'],
				'currency_value'          => $order_query->row['currency_value'],
				'date_added'              => $order_query->row['date_added'],
				'date_modified'           => $order_query->row['date_modified'],
				'ip'                      => $order_query->row['ip']
			);
		} else {
			return false;
		}
	}

	public function getOrders($data = array()) {
        $filter = $this->buildFilterString($data);
		$sql = "
		    SELECT
		        o.order_id, CONCAT(o.firstname, ' ', o.lastname) AS customer,
		        (
		            SELECT os.name
		            FROM order_status os
		            WHERE os.order_status_id = o.order_status_id AND os.language_id = '" . (int)$this->config->get('config_language_id') . "'
                ) AS status, o.order_status_id AS status_id, o.total, o.currency_code, o.currency_value, o.date_added, o.date_modified
            FROM `order` AS o
            " . ($filter ? "WHERE $filter" : "")
        ;

		$sort_data = array(
			'o.order_id',
			'customer',
			'status',
			'o.date_added',
			'o.date_modified',
			'o.total',
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY o.order_id";
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
//        $this->log->write($sql);
		$query = $this->getDb()->query($sql);

		return $query->rows;
	}

	public function getOrderOptions($order_id, $order_product_id) {
		$query = $this->getDb()->query("SELECT * FROM order_option WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$order_product_id . "'");

		return $query->rows;
	}

	public function getOrderTotals($order_id) {
		$query = $this->getDb()->query("SELECT * FROM order_total WHERE order_id = '" . (int)$order_id . "' ORDER BY sort_order");

		return $query->rows;
	}

	public function getOrderByShippingAddressId($shipping_address_id) {
		$query = $this->getDb()->query("
            SELECT *
            FROM `order`
            WHERE shipping_address_id = " . (int)$shipping_address_id
        );
		return $query->rows[0];
	}

	public function getOrderDownloads($order_id) {
		$query = $this->getDb()->query("SELECT * FROM order_download WHERE order_id = '" . (int)$order_id . "' ORDER BY name");

		return $query->rows;
	}

    public function getShippingAddressId($orderId)
    {
        $query = $this->getDb()->query("
            SELECT shipping_address_id
            FROM `order`
            WHERE order_id = " . (int)$orderId
        );
        if ($query->num_rows) // there is such order
        {
            $addressModel = $this->load->model('reference/address');
            if ($query->row['shipping_address_id']) // order already has address ID
                $orderAddressId = $query->row['shipping_address_id'];
            else // if order doesn't have address ID it's necessary to create one from existing address in order
            {
                $order = $this->getOrder($orderId);
                $orderShippingAddress = // get text representation of order shipping address
                    $addressModel->getAddress(
                        $order['shipping_lastname'], $order['shipping_firstname'],
                        $order['shipping_company'],
                        $order['shipping_address_1'], $order['shipping_address_2'],
                        $order['shipping_city'], $order['shipping_postcode'],
                        $order['shipping_zone_id'],
                        $order['shipping_country_id']
                    );
                $orderAddressId = 0;
                foreach (CustomerDAO::getInstance()->getAddresses($order['customer_id']) as $customerAddress) // go through customer's addresses
                {
                    if ($addressModel->getAddress($customerAddress['address_id']) == $orderShippingAddress)
                    {
                        $orderAddressId = $customerAddress['address_id']; // found address, use its ID as order address ID
                        break;
                    }
                }
                if (!$orderAddressId)
                { // no address found, create new one
                    $orderAddressId = $addressModel->addAddress(
                        $order['shipping_lastname'], $order['shipping_firstname'],
                        $order['shipping_company'],
                        $order['shipping_address_1'], $order['shipping_address_2'],
                        $order['shipping_city'], $order['shipping_postcode'],
                        $order['shipping_country_id'],
                        $order['shipping_zone_id'],
                        $order['customer_id']
                    );
                }
                $this->getDb()->query("
                        UPDATE `order`
                        SET shipping_address_id = $orderAddressId
                        WHERE order_id = " . (int)$orderId
                );
            }
            return $orderAddressId;
        }
        else
            return null;
    }

	private function getShippingCost($shipping_method, $orderItems, $ext = array())	{
		$shipping_method = explode(".", $shipping_method);
//		$this->load->model("shipping/" . $shipping_method[0]);
		$shippingMethod = ShippingMethodDAO::getInstance()->getMethod($shipping_method[0]);
		return $shippingMethod->getCost($shipping_method[1], $orderItems, $ext);
	}

	public function getTotalOrders($data = array()) {
        $filter = $this->buildFilterString($data);
      	$sql = "
      	    SELECT COUNT(*) AS total
      	    FROM `order` AS o
            " . ($filter ? "WHERE $filter" : "")
        ;


		$query = $this->getDb()->query($sql);

		return $query->row['total'];
	}

	public function getTotalOrdersByStoreId($store_id) {
      	$query = $this->getDb()->query("SELECT COUNT(*) AS total FROM `order` WHERE store_id = '" . (int)$store_id . "'");

		return $query->row['total'];
	}

	public function getTotalOrdersByOrderStatusId($order_status_id) {
      	$query = $this->getDb()->query("SELECT COUNT(*) AS total FROM `order` WHERE order_status_id = '" . (int)$order_status_id . "' AND order_status_id > '0'");

		return $query->row['total'];
	}

	public function getTotalOrdersByLanguageId($language_id) {
      	$query = $this->getDb()->query("SELECT COUNT(*) AS total FROM `order` WHERE language_id = '" . (int)$language_id . "' AND order_status_id > '0'");

		return $query->row['total'];
	}

	public function getTotalOrdersByCurrencyId($currency_id) {
      	$query = $this->getDb()->query("SELECT COUNT(*) AS total FROM `order` WHERE currency_id = '" . (int)$currency_id . "' AND order_status_id > '0'");

		return $query->row['total'];
	}

	public function getTotalSales() {
      	$query = $this->getDb()->query("SELECT SUM(total) AS total FROM `order` WHERE order_status_id > '0'");

		return $query->row['total'];
	}

	public function getTotalSalesByYear($year) {
      	$query = $this->getDb()->query("SELECT SUM(total) AS total FROM `order` WHERE order_status_id > '0' AND YEAR(date_added) = '" . (int)$year . "'");

		return $query->row['total'];
	}

	public function createInvoiceNo($order_id) {
		$order_info = $this->getOrder($this->request->get['order_id']);

		if ($order_info && !$order_info['invoice_no']) {
			$query = $this->getDb()->query("SELECT MAX(invoice_no) AS invoice_no FROM `order` WHERE invoice_prefix = '" . $this->getDb()->escape($order_info['invoice_prefix']) . "'");

			if ($query->row['invoice_no']) {
				$invoice_no = $query->row['invoice_no'] + 1;
			} else {
				$invoice_no = 1;
			}

			$this->getDb()->query("UPDATE `order` SET invoice_no = '" . (int)$invoice_no . "', invoice_prefix = '" . $this->getDb()->escape($order_info['invoice_prefix']) . "' WHERE order_id = '" . (int)$order_id . "'");

			return $order_info['invoice_prefix'] . $invoice_no;
		}
	}

	public function addOrderHistory($order_id, $data = array()) {
		$this->getDb()->query("
		    UPDATE `order`
		    SET
		        order_status_id = '" . (int)$data['order_status_id'] . "',
		        date_modified = NOW()
            WHERE order_id = '" . (int)$order_id . "'
        ");

		$this->getDb()->query("
		    INSERT INTO order_history
		    SET
		        order_id = '" . (int)$order_id . "',
		        order_status_id = '" . (int)$data['order_status_id'] . "',
		        notify = '" . (isset($data['notify']) ? (int)$data['notify'] : 0) . "',
		        comment = '" . $this->getDb()->escape(strip_tags($data['comment'])) . "',
		        date_added = NOW()
        ");

		$order_info = $this->getOrder($order_id);

		// Send out any gift voucher mails
		if ($this->config->get('config_complete_status_id') == $data['order_status_id']) {
			$this->load->model('sale/voucher');

			$results = $this->model_sale_voucher->getVouchersByOrderId($order_id);

			foreach ($results as $result) {
				$this->model_sale_voucher->sendVoucher($result['voucher_id']);
			}
		}

      	if ($data['notify']) {
			$language = new Language($order_info['language_directory']);
			$language->load($order_info['language_filename']);
			$language->load('mail/order');

			$subject = sprintf($language->get('text_subject'), $order_info['store_name'], $order_id);

			$message  = $language->get('text_order') . ' ' . $order_id . "\n";
			$message .= $language->get('text_date_added') . ' ' . date($language->get('date_format_short'), strtotime($order_info['date_added'])) . "\n\n";

			$order_status_query = $this->getDb()->query("SELECT * FROM order_status WHERE order_status_id = '" . (int)$data['order_status_id'] . "' AND language_id = '" . (int)$order_info['language_id'] . "'");

			if ($order_status_query->num_rows) {
				$message .= $language->get('text_order_status') . "\n";
				$message .= $order_status_query->row['name'] . "\n\n";
			}

			if ($order_info['customer_id']) {
				$message .= $language->get('text_link') . "\n";
				$message .= html_entity_decode($order_info['store_url'] . 'index.php?route=account/order/info&order_id=' . $order_id, ENT_QUOTES, 'UTF-8') . "\n\n";
			}

			if ($data['comment']) {
				$message .= $language->get('text_comment') . "\n\n";
				$message .= strip_tags(html_entity_decode($data['comment'], ENT_QUOTES, 'UTF-8')) . "\n\n";
			}

			$message .= $language->get('text_footer');

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

	public function getOrderHistories($order_id, $start = 0, $limit = 10) {
		$query = $this->getDb()->query("SELECT oh.date_added, os.name AS status, oh.comment, oh.notify FROM order_history oh LEFT JOIN order_status os ON oh.order_status_id = os.order_status_id WHERE oh.order_id = '" . (int)$order_id . "' AND os.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY oh.date_added ASC LIMIT " . (int)$start . "," . (int)$limit);

		return $query->rows;
	}

	public function getTotalOrderHistories($order_id) {
	  	$query = $this->getDb()->query("SELECT COUNT(*) AS total FROM order_history WHERE order_id = '" . (int)$order_id . "'");

		return $query->row['total'];
	}

	public function getTotalOrderHistoriesByOrderStatusId($order_status_id) {
	  	$query = $this->getDb()->query("SELECT COUNT(*) AS total FROM order_history WHERE order_status_id = '" . (int)$order_status_id . "'");

		return $query->row['total'];
	}

	public function updateOrderTotals($order_id)
	{
		$order = $this->getOrder($order_id);
		$orderItemStatusModel = $this->load->model('localisation/order_item_status');
		$total_price = 0;
		$total_weight = 0;
		$orderItems = $this->getOrderProducts($order_id);

		foreach ($orderItems as $item) {
		    $query = $this->getDb()->query("SELECT product_id FROM product WHERE product_id = '" . (int)$item['product_id'] . "'")->row['product_id'];
		    if(empty($query)) $deleted = 1;
		}
		foreach ($orderItems as $order_item)
		{
			$orderItemStatus = $orderItemStatusModel->getOrderItemStatus($order_item['status_id']);

			if ($orderItemStatus['workflow_order'] != 1000) {
				$orderItem = OrderItemDAO::getInstance()->getOrderItem($order_item['order_product_id']);
				if (!empty($orderItem)) {
				    $total_price += $order_item['total'];
				    $total_weight += $this->weight->convert($orderItem->getWeight(), $orderItem->getWeightClassId(), $this->config->get('config_weight_class_id')) * $order_item['quantity'];
				}
			}
		}
		$shipping_cost = $this->getShippingCost($order['shipping_method'], $orderItems, array('weight' => $total_weight));
        if ($order['total'] != $total_price + $shipping_cost && !$deleted)
        {
            $this->getDb()->query("UPDATE order_total SET value = $total_price, text = '" . $this->currency->format($total_price). "' WHERE order_id = $order_id AND code = 'sub_total'");
            $this->getDb()->query("UPDATE order_total SET value = $shipping_cost, text = '" . $this->currency->format($shipping_cost). "' WHERE order_id = $order_id AND code = 'shipping'");
            $this->getDb()->query("UPDATE order_total SET value = " . ($total_price + $shipping_cost) . ", text = '" . $this->currency->format($total_price + $shipping_cost). "' WHERE order_id = $order_id AND code = 'total'");
            $this->getDb()->query("UPDATE `order` SET total = " . ($total_price + $shipping_cost) . " WHERE order_id = $order_id");
        }
	}

	public function getAllCustomerOrders($customer_id) {
		$query = "SELECT * FROM `order` WHERE customer_id = " . (int)$customer_id;
		$result = $this->getDb()->query($query);
		return $result->rows;
	}

	public function getOrderProduct($order_item_id) {
		$query = "SELECT * FROM order_product WHERE order_product_id = " . (int) $order_item_id;
		$result = $this->getDb()->query($query);

		return $result->row;
	}

    public function getOrderProducts($order_id) {
        $query = $this->getDb()->query("
		    SELECT *
		    FROM order_product
		    WHERE order_id = '" . (int)$order_id . "'
        ");

        return $query->rows;
    }

	public function setOrderStatus($order_id, $status_id) {
		$query = "UPDATE `order` SET order_status_id = " . (int)$status_id . " WHERE order_id = " . (int)$order_id;
		$result = $this->getDb()->query($query);

	}

    /**
     * @param $orderId int
     * @return void
     */
    public function verifyOrderCompletion($orderId) {
        $items = $this->getOrderProducts($orderId);
        $isReadyToShip = true;
        $finishStatuses = array(
            ORDER_ITEM_STATUS_SOLDOUT,
            ORDER_ITEM_STATUS_CANCELLED,
            ORDER_ITEM_STATUS_FINISH,
            ORDER_ITEM_STATUS_PACKED,
            REPURCHASE_ORDER_ITEM_STATUS_SOLDOUT,
            REPURCHASE_ORDER_ITEM_STATUS_REJECTED,
            REPURCHASE_ORDER_ITEM_STATUS_FINISH,
            REPURCHASE_ORDER_ITEM_STATUS_PACKED
        );
        foreach ($items as $item) {
            $isReadyToShip &= in_array($item['status_id'], $finishStatuses);
        }
        if ($isReadyToShip) {
            $this->setOrderStatus($orderId, ORDER_STATUS_READY_TO_SHIP);
        }
    }

    public function getCouponProducts($code) {
	    
	    $coupon_id = $this->getDb()->query("SELECT coupon_id FROM " . DB_PREFIX . "coupon WHERE code='" . $this->getDb()->escape($code) . "'")->row['coupon_id'];
	    $_products = $this->getDb()->query("SELECT product_id FROM " . DB_PREFIX . "coupon_product WHERE coupon_id='" . (int)$coupon_id . "'")->rows;
	    foreach ($_products as $k => $v) {
		$products[] = $v['product_id'];
	    }
	    
	    return $products;
    }
}
<?php
class ControllerAccountOrder extends Controller {
	private $error = array();
    private $modelAccountOrderItem;
    private $modelToolImage;

    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->load->library('Status');
        $this->language->load('account/order');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->data['heading_title'] = $this->language->get('heading_title');
    }

	public function index() {
    	if (!$this->customer->isLogged()) {
      		$this->session->data['redirect'] = $this->url->link('account/order', '', 'SSL');

	  		$this->redirect($this->url->link('account/login', '', 'SSL'));
    	}

        $this->setBreadcrumbs();

		$this->data['text_order_id'] = $this->language->get('text_order_id');
		$this->data['text_status'] = $this->language->get('text_status');
		$this->data['text_date_added'] = $this->language->get('text_date_added');
		$this->data['text_customer'] = $this->language->get('text_customer');
		$this->data['text_products'] = $this->language->get('text_products');
		$this->data['text_total'] = $this->language->get('text_total');
		$this->data['text_empty'] = $this->language->get('text_empty');

		$this->data['button_view'] = $this->language->get('button_view');
		$this->data['button_continue'] = $this->language->get('button_continue');

		$this->data['action'] = $this->url->link('account/order', '', 'SSL');

		$this->load->model('account/order');

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$this->data['orders'] = array();

		$order_total = $this->model_account_order->getTotalOrders();

		$results = $this->model_account_order->getOrders(($page - 1) * $this->config->get('config_catalog_limit'), $this->config->get('config_catalog_limit'));

		foreach ($results as $result) {
            $product_total = $this->model_account_order->getTotalOrderProductsByOrderId($result['order_id']);

			$this->data['orders'][] = array(
				'order_id'   => $result['order_id'],
				'name'       => $result['firstname'] . ' ' . $result['lastname'],
				'status'     => $result['status'],
				'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'products'   => $product_total,
				'total'      => $this->currency->format($result['total'], $result['currency_code'], $result['currency_value']),
				'href'       => $this->url->link('account/order/info', 'order_id=' . $result['order_id'], 'SSL')
			);
		}

		$pagination = new Pagination();
		$pagination->total = $order_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_catalog_limit');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('account/order', 'page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['continue'] = $this->url->link('account/account', '', 'SSL');

        $templateName = '/template/account/order_list.tpl';
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . $templateName)) {
			$this->template = $this->config->get('config_template') . $templateName;
		} else {
			$this->template = 'default' . $templateName;
		}

		$this->children = array(
			'common/column_left',
			'common/column_right',
			'common/content_top',
			'common/content_bottom',
			'common/footer',
			'common/header'
		);

		$this->response->setOutput($this->render());
	}

	public function info() {
        $this->modelAccountOrderItem = $this->load->model('account/order_item');
        $this->modelToolImage = $this->load->model('tool/image');
		if (isset($this->request->get['order_id'])) {
			$order_id = $this->request->get['order_id'];
		} else {
			$order_id = 0;
		}

		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/order/info', 'order_id=' . $order_id, 'SSL');

			$this->redirect($this->url->link('account/login', '', 'SSL'));
    	}

		$this->language->load('account/order');

		$this->load->model('account/order');

		$order_info = $this->model_account_order->getOrder($order_id);

		if ($order_info) {
			if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
				if ($this->request->post['action'] == 'reorder') {
					$order_products = $this->model_account_order->getOrderProducts($order_id);

					foreach ($order_products as $order_product) {
						if (in_array($order_product['order_product_id'], $this->request->post['selected'])) {
							$option_data = array();

							$order_options = $this->model_account_order->getOrderOptions($order_id, $order_product['order_product_id']);

							foreach ($order_options as $order_option) {
								if ($order_option['type'] == 'select' || $order_option['type'] == 'radio') {
									$option_data[$order_option['product_option_id']] = $order_option['product_option_value_id'];
								} elseif ($order_option['type'] == 'checkbox') {
									$option_data[$order_option['product_option_id']][] = $order_option['product_option_value_id'];
								} elseif ($order_option['type'] == 'input' || $order_option['type'] == 'textarea' || $order_option['type'] == 'file' || $order_option['type'] == 'date' || $order_option['type'] == 'datetime' || $order_option['type'] == 'time') {
									$option_data[$order_option['product_option_id']] = $order_option['value'];
								}
							}

							$this->cart->add($order_product['product_id'], $order_product['quantity'], $option_data);
						}
					}

					$this->redirect($this->url->link('checkout/cart', '', 'SSL'));
				}
			}

			$this->setBreadcrumbs();

			$url = '';

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->data['text_order_detail'] = $this->language->get('text_order_detail');
			$this->data['text_invoice_no'] = $this->language->get('text_invoice_no');
    		$this->data['text_order_id'] = $this->language->get('text_order_id');
			$this->data['text_date_added'] = $this->language->get('text_date_added');
      		$this->data['text_shipping_method'] = $this->language->get('text_shipping_method');
			$this->data['text_shipping_address'] = $this->language->get('text_shipping_address');
      		$this->data['text_payment_method'] = $this->language->get('text_payment_method');
      		$this->data['text_payment_address'] = $this->language->get('text_payment_address');
      		$this->data['text_history'] = $this->language->get('text_history');
			$this->data['text_comment'] = $this->language->get('text_comment');
			$this->data['text_action'] = $this->language->get('text_action');
			$this->data['text_selected'] = $this->language->get('text_selected');
			$this->data['text_reorder'] = $this->language->get('text_reorder');
			$this->data['text_return'] = $this->language->get('text_return');
            $this->data['textAction'] = $this->language->get('ACTION');
            $this->data['textComment'] = $this->language->get('COMMENT');
            $this->data['textOrderItemId'] = $this->language->get('ORDER_ITEM_ID');
            $this->data['textOrderItemImage'] = $this->language->get('IMAGE');

      		$this->data['column_name'] = $this->language->get('column_name');
      		$this->data['column_model'] = $this->language->get('column_model');
      		$this->data['column_quantity'] = $this->language->get('column_quantity');
      		$this->data['column_price'] = $this->language->get('column_price');
      		$this->data['column_total'] = $this->language->get('column_total');
			$this->data['column_date_added'] = $this->language->get('column_date_added');
      		$this->data['column_status'] = $this->language->get('column_status');

      		$this->data['button_continue'] = $this->language->get('button_continue');
			$this->data['button_invoice'] = $this->language->get('button_invoice');

			if (isset($this->error['warning'])) {
				$this->data['error_warning'] = $this->error['warning'];
			} else {
				$this->data['error_warning'] = '';
			}

			$this->data['action'] = $this->url->link('account/order/info', 'order_id=' . $this->request->get['order_id'], 'SSL');

			if ($order_info['invoice_no']) {
				$this->data['invoice_no'] = $order_info['invoice_prefix'] . $order_info['invoice_no'];
			} else {
				$this->data['invoice_no'] = '';
			}

			$this->data['order_id'] = $this->request->get['order_id'];
			$this->data['date_added'] = date($this->language->get('date_format_short'), strtotime($order_info['date_added']));

			if ($order_info['shipping_address_format']) {
      			$format = $order_info['shipping_address_format'];
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
	  			'firstname' => $order_info['shipping_firstname'],
	  			'lastname'  => $order_info['shipping_lastname'],
	  			'company'   => $order_info['shipping_company'],
      			'address_1' => $order_info['shipping_address_1'],
      			'address_2' => $order_info['shipping_address_2'],
      			'city'      => $order_info['shipping_city'],
      			'postcode'  => $order_info['shipping_postcode'],
      			'zone'      => $order_info['shipping_zone'],
				'zone_code' => $order_info['shipping_zone_code'],
      			'country'   => $order_info['shipping_country']
			);

			$this->data['shipping_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

			$this->data['shipping_method'] = Shipping::getName($order_info['shipping_method'], $this->registry);

			if ($order_info['payment_address_format']) {
      			$format = $order_info['payment_address_format'];
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
	  			'firstname' => $order_info['payment_firstname'],
	  			'lastname'  => $order_info['payment_lastname'],
	  			'company'   => $order_info['payment_company'],
      			'address_1' => $order_info['payment_address_1'],
      			'address_2' => $order_info['payment_address_2'],
      			'city'      => $order_info['payment_city'],
      			'postcode'  => $order_info['payment_postcode'],
      			'zone'      => $order_info['payment_zone'],
				'zone_code' => $order_info['payment_zone_code'],
      			'country'   => $order_info['payment_country']
			);

			$this->data['payment_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));
      		$this->data['payment_method'] = $order_info['payment_method'];

			$this->data['products'] = array();

			$products = $this->modelAccountOrderItem->getOrderItems(
                array('filterOrderId' => $this->request->get['order_id'])
            );
      		foreach ($products as $product) {
                $actions = array();
                if (($product['status'] & 0x0000FFFF) <= 2)
                    $actions[] = array(
                        'text' => $this->language->get('CANCEL'),
                        'href' => $this->url->link(
                            'account/orderItems/cancel',
                            'orderItemId=' . $product['order_product_id'] . '&returnUrl=' . urlencode($this->selfUrl))
                    );
                $option_data = array();
				$options = $this->modelAccountOrderItem->getOrderItemOptions($product['order_product_id']);
//                $this->log->write(print_r($options, true));

         		foreach ($options as $option) {
          			if ($option['type'] != 'file') {
						$option_data[] = array(
							'name'  => $option['name'],
							'value' => utf8_truncate($option['value']),
						);
					} else {
						$filename = substr($option['value'], 0, strrpos($option['value'], '.'));

						$option_data[] = array(
							'name'  => $option['name'],
							'value' => utf8_truncate($filename)
						);
					}
        		}
            //print_r($product);
                  if($product['image_path'] == '' || $product['image_path'] == "data/event/agent-moomidae.jpg") {
                    $options = $this->modelOrderItem->getOrderItemOptions($product['order_product_id']);
                    $itemUrl = !empty($options[REPURCHASE_ORDER_IMAGE_URL_OPTION_ID]['value'])
                    ? $options[REPURCHASE_ORDER_IMAGE_URL_OPTION_ID]['value'] : '';
                    $response[$index]['image_path'] = !empty($itemUrl) ? $itemUrl : $product['image_path'];
                  }

                  if ($product['image_path'] && file_exists(DIR_IMAGE . $product['image_path']))
                      $image = $this->modelToolImage->resize($product['image_path'], 100, 100);
                  else
                      $image = $this->modelToolImage->resize('no_image.jpg', 100, 100);

        		$this->data['products'][] = array(
					'order_product_id' => $product['order_product_id'],
                    'actions' => $actions,
                    'comment' => $product['public_comment'],
          			'name'             => $product['name'],
          			'model'            => $product['model'],
          			'option'           => $option_data,
          			'quantity'         => $product['quantity'],
          			'price'            => $this->currency->format($product['price'], $order_info['currency_code'], $order_info['currency_value']),
					'total'            => $this->currency->format($product['total'], $order_info['currency_code'], $order_info['currency_value']),
                    'imagePath' => $image,
                    'item_status'      => Status::getStatus($product['status_id'], $this->config->get('language_id'), true),
                    'selected'         => false //isset($this->request->post['selected']) && in_array($result['order_product_id'], $this->request->post['selected'])
        		);
      		}

      		$this->data['totals'] = $this->model_account_order->getOrderTotals($this->request->get['order_id']);
			$this->data['comment'] = $order_info['comment'];
			$this->data['histories'] = array();
			$results = $this->model_account_order->getOrderHistories($this->request->get['order_id']);

      		foreach ($results as $result) {
        		$this->data['histories'][] = array(
          			'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
          			'status'     => $result['status'],
          			'comment'    => nl2br($result['comment'])
        		);
      		}

      		$this->data['continue'] = $this->url->link('account/order', '', 'SSL');

			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/account/order_info.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/account/order_info.tpl';
			} else {
				$this->template = 'default/template/account/order_info.tpl';
			}

			$this->children = array(
				'common/column_left',
				'common/column_right',
				'common/content_top',
				'common/content_bottom',
				'common/footer',
				'common/header'
			);

			$this->response->setOutput($this->render());
    	} else {
			$this->document->setTitle($this->language->get('text_order'));

      		$this->data['heading_title'] = $this->language->get('text_order');

      		$this->data['text_error'] = $this->language->get('text_error');

      		$this->data['button_continue'] = $this->language->get('button_continue');

			$this->setBreadcrumbs();

      		$this->data['continue'] = $this->url->link('account/order', '', 'SSL');

			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/error/not_found.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/error/not_found.tpl';
			} else {
				$this->template = 'default/template/error/not_found.tpl';
			}

			$this->children = array(
				'common/column_left',
				'common/column_right',
				'common/content_top',
				'common/content_bottom',
				'common/footer',
				'common/header'
			);

			$this->response->setOutput($this->render());
    	}
  	}

    protected function initParameters()
    {
        if (empty($_REQUEST['orderId']))
            if (empty($_REQUEST['order_id']))
                $this->parameters['orderId'] = null;
            else
                $this->parameters['orderId'] = $_REQUEST['order_id'];
        else
            $this->parameters['orderId'] = $_REQUEST['orderId'];
        $this->parameters['orderItemId'] = empty($_REQUEST['orderItemId']) ? null : $_REQUEST['orderItemId'];
    }

	public function invoice() {
		$this->load->language('account/order');

		$this->data['title'] = $this->language->get('heading_title');

		if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
			$this->data['base'] = HTTPS_SERVER;
		} else {
			$this->data['base'] = HTTP_SERVER;
		}

		$this->data['direction'] = $this->language->get('direction');
		$this->data['language'] = $this->language->get('code');

		$this->data['text_invoice'] = $this->language->get('text_invoice');

		$this->data['text_order_id'] = $this->language->get('text_order_id');
		$this->data['text_invoice_no'] = $this->language->get('text_invoice_no');
		$this->data['text_invoice_date'] = $this->language->get('text_invoice_date');
		$this->data['text_date_added'] = $this->language->get('text_date_added');
		$this->data['text_telephone'] = $this->language->get('text_telephone');
		$this->data['text_fax'] = $this->language->get('text_fax');
		$this->data['text_to'] = $this->language->get('text_to');
		$this->data['text_ship_to'] = $this->language->get('text_ship_to');

		$this->data['column_product'] = $this->language->get('column_product');
		$this->data['column_model'] = $this->language->get('column_model');
		$this->data['column_quantity'] = $this->language->get('column_quantity');
		$this->data['column_price'] = $this->language->get('column_price');
		$this->data['column_total'] = $this->language->get('column_total');
		$this->data['column_comment'] = $this->language->get('column_comment');

		$this->load->model('account/order');

		$this->load->model('setting/setting');

		$this->data['orders'] = array();

		$orders = array();

		if (isset($this->request->get['order_id']))
			$order_id = $this->request->get['order_id'];

		$order_info = $this->model_account_order->getOrder($order_id);

		if ($order_info) {
			$store_info = $this->model_setting_setting->getSetting('config', $order_info['store_id']);

			if ($store_info) {
				$store_address = $store_info['config_address'];
				$store_email = $store_info['config_email'];
				$store_telephone = $store_info['config_telephone'];
				$store_fax = $store_info['config_fax'];
			} else {
				$store_address = $this->config->get('config_address');
				$store_email = $this->config->get('config_email');
				$store_telephone = $this->config->get('config_telephone');
				$store_fax = $this->config->get('config_fax');
			}

			if ($order_info['invoice_no']) {
				$invoice_no = $order_info['invoice_prefix'] . $order_info['invoice_no'];
			} else {
				$invoice_no = '';
			}

			if ($order_info['shipping_address_format']) {
				$format = $order_info['shipping_address_format'];
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
				'firstname' => $order_info['shipping_firstname'],
				'lastname'  => $order_info['shipping_lastname'],
				'company'   => $order_info['shipping_company'],
				'address_1' => $order_info['shipping_address_1'],
				'address_2' => $order_info['shipping_address_2'],
				'city'      => $order_info['shipping_city'],
				'postcode'  => $order_info['shipping_postcode'],
				'zone'      => $order_info['shipping_zone'],
				'zone_code' => $order_info['shipping_zone_code'],
				'country'   => $order_info['shipping_country']
				);

			$shipping_address = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

			if ($order_info['payment_address_format']) {
				$format = $order_info['payment_address_format'];
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
				'firstname' => $order_info['payment_firstname'],
				'lastname'  => $order_info['payment_lastname'],
				'company'   => $order_info['payment_company'],
				'address_1' => $order_info['payment_address_1'],
				'address_2' => $order_info['payment_address_2'],
				'city'      => $order_info['payment_city'],
				'postcode'  => $order_info['payment_postcode'],
				'zone'      => $order_info['payment_zone'],
				'zone_code' => $order_info['payment_zone_code'],
				'country'   => $order_info['payment_country']
				);

			$payment_address = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

			$product_data = array();

			$products = $this->model_account_order->getOrderProducts($order_id);

			foreach ($products as $product) {
				$option_data = array();

				$options = $this->model_account_order->getOrderOptions($order_id, $product['order_product_id']);

				foreach ($options as $option) {
					if ($option['type'] != 'file') {
						$option_data[] = array(
							'name'  => $option['name'],
							'value' => $option['value']
							);
					} else {
						$option_data[] = array(
							'name'  => $option['name'],
							'value' => utf8_substr($option['value'], 0, strrpos($option['value'], '.'))
							);
					}
				}

				$product_data[] = array(
					'name'     => $product['name'],
					'model'    => $product['model'],
					'option'   => $option_data,
					'quantity' => $product['quantity'],
					'price'    => $this->currency->format($product['price'], $order_info['currency_code'], $order_info['currency_value']),
					'total'    => $this->currency->format($product['total'], $order_info['currency_code'], $order_info['currency_value'])
					);
			}

			$total_data = $this->model_account_order->getOrderTotals($order_id);

			$this->data['orders'][] = array(
				'order_id'	       => $order_id,
				'invoice_no'       => $invoice_no,
				'date_added'       => date($this->language->get('date_format_short'), strtotime($order_info['date_added'])),
				'store_name'       => $order_info['store_name'],
				'store_url'        => rtrim($order_info['store_url'], '/'),
				'store_address'    => nl2br($store_address),
				'store_email'      => $store_email,
				'store_telephone'  => $store_telephone,
				'store_fax'        => $store_fax,
				'email'            => $order_info['email'],
				'telephone'        => $order_info['telephone'],
				'shipping_address' => $shipping_address,
				'payment_address'  => $payment_address,
				'product'          => $product_data,
				'total'            => $total_data,
				'comment'          => nl2br($order_info['comment'])
				);
		}

		$templateFileName = '/template/account/order_invoice.tpl';
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . $templateFileName))
			$this->template = $this->config->get('config_template') . $templateFileName;
		else
			$this->template = 'default' . $templateFileName;

		$this->response->setOutput($this->render());
	}

    private function setBreadcrumbs()
    {
        $this->data['breadcrumbs'] = array();
      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),
        	'separator' => false
      	);

      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_account'),
			'href'      => $this->url->link('account/account', '', 'SSL'),
        	'separator' => $this->language->get('text_separator')
      	);

      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('account/order', '', 'SSL'),
        	'separator' => $this->language->get('text_separator')
      	);
    }

	private function validate() {
		if (!isset($this->request->post['selected']) || !isset($this->request->post['action']) || !$this->request->post['action']) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		if (!$this->error) {
      		return true;
    	} else {
      		return false;
    	}
	}
}
?>
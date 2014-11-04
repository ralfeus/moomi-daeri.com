<?php
use model\checkout\CartDAO;
use model\catalog\ProductDAO;
use system\engine\CustomerController;

class ControllerCheckoutCart extends CustomerController {
	private $error = array();

    private function checkLocalShipping($supplierId, $orderTotal) {
        if ($orderTotal >= $this->data['suppliers'][$supplierId]['freeShippingThreshold']) {
            $this->data['suppliers'][$supplierId]['shippingCost'] = 0;
            $this->data['suppliers'][$supplierId]['textShippingCost'] = $this->getLanguage()->get('LOCAL_SHIPPING_FREE');
        } else {
            $this->data['suppliers'][$supplierId]['textShippingCost'] =
                sprintf(
                    $this->getLanguage()->get('LOCAL_SHIPPING_YET_TO_ORDER'),
                    $this->getCurrency()->format($this->data['suppliers'][$supplierId]['freeShippingThreshold'] - $orderTotal)
                );
        }
        $this->data['suppliers'][$supplierId]['shippingCostFormatted'] =
            $this->getCurrency()->format($this->data['suppliers'][$supplierId]['shippingCost']);
    }

	public function index() {
		$this->language->load('checkout/cart');
		// Remove
		if (isset($this->request->get['remove'])) {
			$this->getCart()->remove($this->request->get['remove']);
			$this->redirect($this->url->link('checkout/cart'));
		}

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
      		if (isset($this->request->post['quantity'])) {
				if (!is_array($this->request->post['quantity'])) {
					if (isset($this->request->post['option'])) {
						$option = $this->request->post['option'];
					} else {
						$option = array();
					}

      				$this->getCart()->add($this->request->post['product_id'], $this->request->post['quantity'], $option);
				} else {
					foreach ($this->request->post['quantity'] as $key => $value) {
	      				$this->cart->update($key, $value);
					}
				}
      		}

      		if (isset($this->request->post['remove'])) {
	    		foreach ($this->request->post['remove'] as $key) {
          			$this->cart->remove($key);
				}
      		}

      		if (isset($this->request->post['voucher']) && $this->request->post['voucher']) {
	    		foreach ($this->request->post['voucher'] as $key) {
          			if (isset($this->session->data['vouchers'][$key])) {
						unset($this->session->data['vouchers'][$key]);
					}
				}
      		}

			if (isset($this->request->post['redirect'])) {
				$this->session->data['redirect'] = $this->request->post['redirect'];
			}

			if (isset($this->request->post['quantity']) || isset($this->request->post['remove']) || isset($this->request->post['voucher'])) {
				unset($this->session->data['shipping_methods']);
				unset($this->session->data['shipping_method']);
				unset($this->session->data['payment_methods']);
				unset($this->session->data['payment_method']);
				unset($this->session->data['reward']);

				$this->redirect($this->url->link('checkout/cart'));
			}
    	}

    	$this->document->setTitle($this->language->get('heading_title'));

      	$this->data['breadcrumbs'] = array();

      	$this->data['breadcrumbs'][] = array(
        	'href'      => $this->url->link('common/home'),
        	'text'      => $this->language->get('text_home'),
        	'separator' => false
      	);

      	$this->data['breadcrumbs'][] = array(
        	'href'      => $this->url->link('checkout/cart'),
        	'text'      => $this->language->get('heading_title'),
        	'separator' => $this->language->get('text_separator')
      	);

    	if ($this->cart->hasProducts() || (isset($this->session->data['vouchers']) && $this->session->data['vouchers'])) {
      		$this->data['heading_title'] = $this->language->get('heading_title');

			$this->data['text_select'] = $this->language->get('text_select');
			$this->data['text_weight'] = $this->language->get('text_weight');

     		$this->data['column_remove'] = $this->language->get('column_remove');
      		$this->data['column_image'] = $this->language->get('column_image');
      		$this->data['column_name'] = $this->language->get('column_name');
      		$this->data['column_model'] = $this->language->get('column_model');
      		$this->data['column_quantity'] = $this->language->get('column_quantity');
			$this->data['column_price'] = $this->language->get('column_price');
      		$this->data['column_total'] = $this->language->get('column_total');

      		$this->data['button_update'] = $this->language->get('button_update');
     		$this->data['button_remove'] = $this->language->get('button_remove');
     		$this->data['button_shopping'] = $this->language->get('button_shopping');
      		$this->data['button_checkout'] = $this->language->get('button_checkout');
            $this->data['textBrand'] = $this->language->get('MANUFACTURER');
            $this->data['textCheckoutSelected'] = $this->language->get('CHECKOUT_SELECTED');

			if ($this->config->get('config_customer_price') && !$this->customer->isLogged()) {
				$this->data['attention'] = sprintf($this->language->get('text_login'), $this->url->link('account/login'), $this->url->link('account/register'));
			} else {
				$this->data['attention'] = '';
			}

			if (!$this->cart->hasStock() && (!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning'))) {
      			$this->data['error_warning'] = $this->language->get('error_stock');
			} elseif (isset($this->session->data['error'])) {
				$this->data['error_warning'] = $this->session->data['error'];

				unset($this->session->data['error']);
			} else {
				$this->data['error_warning'] = '';
			}

			if (isset($this->session->data['success'])) {
				$this->data['success'] = $this->session->data['success'];

				unset($this->session->data['success']);
			} else {
				$this->data['success'] = '';
			}

			if ($this->config->get('config_cart_weight')) {
				$this->data['weight'] = $this->weight->format($this->cart->getWeight(), $this->config->get('config_weight_class_id'), $this->language->get('decimal_point'), $this->language->get('thousand_point'));
			} else {
				$this->data['weight'] = false;
			}

			$this->load->model('tool/image');

      		$this->data['products'] = array();
			$products = CartDAO::getInstance()->getProducts();
            uasort($products, function($a, $b) {
                if ($a['supplierId'] < $b['supplierId']) {
                    return -1;
                } elseif ($a['supplierId'] > $b['supplierId']) {
                    return 1;
                } else {
                    return 0;
                }
            });
            $currentSupplierId = null; $currentSupplierOrderTotal = 0;
      		foreach ($products as $product) {
				$product_total = 0;

				foreach ($products as $product_2) {
					if ($product_2['product_id'] == $product['product_id']) {
						$product_total += $product_2['quantity'];
					}
				}

				if ($product['minimum'] > $product_total) {
					$this->data['error_warning'] = sprintf($this->language->get('error_minimum'), $product['name'], $product['minimum']);
				}
        //print_r($product);
        //$this->load->model('shop/general');
        //$order_product = $this->model_shop_general->getOrderProduct($pro)
        /*if($product['image'] == '' || $product['image'] == "data/event/agent-moomidae.jpg") {
          $options = $this->modelOrderItem->getOptions($product['order_product_id']);
          $itemUrl = !empty($options[REPURCHASE_ORDER_IMAGE_URL_OPTION_ID]['value'])
          ? $options[REPURCHASE_ORDER_IMAGE_URL_OPTION_ID]['value'] : '';
          $product['image'] = !empty($itemUrl) ? $itemUrl : $product['image'];
        }*/
				$product_image = ProductDAO::getInstance()->getImage($product['product_id']);
        if ($product['image']) {
					$image = $this->model_tool_image->resize($product['image'], $this->config->get('config_image_cart_width'), $this->config->get('config_image_cart_height'));
				} else {
					$image = $this->model_tool_image->resize($product_image, $this->config->get('config_image_cart_width'), $this->config->get('config_image_cart_height'));
				}

				$option_data = array();

        		foreach ($product['option'] as $option) {
					if ($option['type'] != 'file') {
						$option_data[] = array(
							'name'  => $option['name'],
							'value' => utf8_truncate($option['option_value'])
						);
					} else {
						$this->load->library('encryption');

						$encryption = new Encryption($this->config->get('config_encryption'));

						$file = substr($encryption->decrypt($option['option_value']), 0, strrpos($encryption->decrypt($option['option_value']), '.'));

						$option_data[] = array(
							'name'  => $option['name'],
							'value' => utf8_truncate($file)
						);
					}
        		}

				if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')));
				} else {
					$price = false;
				}

				if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
					$total = $this->currency->format($this->tax->calculate($product['total'], $product['tax_class_id'], $this->config->get('config_tax')));
				} else {
					$total = false;
				}

        		$this->data['products'][] = array(
                    'actionDeleteUrl' => $this->url->link('checkout/cart/removeItem', 'key=' . urlencode($product['key']), 'SSL'),
          			'key'      => $product['key'],
          			'thumb'    => $image,
					'name'     => $product['name'],
          			'model'    => $product['model'],
          			'option'   => $option_data,
          			'quantity' => $product['quantity'],
          			'stock'    => $product['stock'],
					'reward'   => ($product['reward'] ? sprintf($this->language->get('text_reward'), $product['reward']) : ''),
					'price'    => $price,
					'total'    => $total,
					'href'     => $this->url->link('product/product', 'product_id=' . $product['product_id']),
					'remove'   => $this->url->link('checkout/cart', 'remove=' . $product['key']),
                    'supplierId' => $product['supplierId']
        		);
                /// Show local shipping cost
                if ($currentSupplierId != $product['supplierId']) {
                    if (!is_null($currentSupplierId)) {
                        $this->checkLocalShipping($currentSupplierId, $currentSupplierOrderTotal);
                    }
                    $currentSupplierId = $product['supplierId'];
                    $this->data['suppliers'][$currentSupplierId]['name'] = $product['brand'];
                    $this->data['suppliers'][$currentSupplierId]['shippingCost'] = $product['supplierShippingCost'];
                    $this->data['suppliers'][$currentSupplierId]['freeShippingThreshold'] = $product['supplierFreeShippingThreshold'];
                    $currentSupplierOrderTotal = $product['total'];
                } else {
                    $currentSupplierOrderTotal += $product['total'];
                }
      		}
            /// Post-products last supplier check
            $this->checkLocalShipping($currentSupplierId, $currentSupplierOrderTotal);

            // Gift Voucher
			$this->data['vouchers'] = array();

			if (isset($this->session->data['vouchers']) && $this->session->data['vouchers']) {
				foreach ($this->session->data['vouchers'] as $key => $voucher) {
					$this->data['vouchers'][] = array(
						'key'         => $key,
						'description' => $voucher['description'],
						'amount'      => $this->currency->format($voucher['amount'])
					);
				}
			}

			$total_data = array();
			$total = 0;
			$taxes = $this->getCart()->getTaxes();

			if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
				$this->load->model('setting/extension');

				$sort_order = array();

				$results = $this->model_setting_extension->getExtensions('total');
				foreach ($results as $key => $value) {
					$sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
				}
				array_multisort($sort_order, SORT_ASC, $results);

				foreach ($results as $result) {
					if ($this->config->get($result['code'] . '_status')) {
						$this->load->model('total/' . $result['code']);

						$this->{'model_total_' . $result['code']}->getTotal($total_data, $total, $taxes);
					}
				}

				$sort_order = array();

				foreach ($total_data as $key => $value) {
					$sort_order[$key] = $value['sort_order'];
				}

				array_multisort($sort_order, SORT_ASC, $total_data);
			}

			$this->data['totals'] = $total_data;

			// Modules
			$this->data['modules'] = array();

			if (isset($results)) {
				foreach ($results as $result) {
					if ($this->config->get($result['code'] . '_status') && file_exists(DIR_APPLICATION . 'controller/total/' . $result['code'] . '.php')) {
						$this->data['modules'][] = $this->getChild('total/' . $result['code']);
					}
				}
			}

			if (isset($this->session->data['redirect'])) {
      			$this->data['continue'] = $this->session->data['redirect'];

				unset($this->session->data['redirect']);
			} else {
				$this->data['continue'] = $this->url->link('common/home');
			}

			$this->data['urlCheckout'] = $this->url->link('checkout/checkout', '', 'SSL');

			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/checkout/cart.tpl.php')) {
				$this->template = $this->config->get('config_template') . '/template/checkout/cart.tpl.php';
			} else {
				$this->template = 'default/template/checkout/cart.tpl.php';
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
      		$this->data['heading_title'] = $this->language->get('heading_title');
      		$this->data['text_error'] = $this->language->get('text_empty');
      		$this->data['button_continue'] = $this->language->get('button_continue');
      		$this->data['continue'] = $this->url->link('common/home');

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
        $this->parameters['itemKey'] = empty($_REQUEST['itemKey']) ? null : $_REQUEST['itemKey'];
        $this->parameters['selected'] = empty($_REQUEST['selected']) ? false : $_REQUEST['selected'];
        $this->parameters['itemPrice'] = empty($_REQUEST['itemPrice']) || !is_numeric($_REQUEST['itemPrice']) ? 0 : $_REQUEST['itemPrice'];
        $this->session->data['selectedCartItems'] = count($this->parameters['selected']) ? $this->parameters['selected'] : null;
//        $this->log->write(print_r($this->parameters, true));
    }

    public function removeItem()
    {
        if (isset($_REQUEST['key']))
        {
//            $this->log->write(print_r($_REQUEST, true));
            $this->cart->remove($_REQUEST['key']);
        }
        $this->redirect('index.php?route=checkout/cart');
    }

	public function update() {
//        $this->log->write(print_r($this->request->request, true));
		$this->language->load('checkout/cart');

		$json = array();
		if (isset($this->request->post['product_id'])) {
			$this->load->model('catalog/product');

//            $this->log->write("Getting product info");
			$product_info = $this->model_catalog_product->getProduct($this->request->post['product_id']);
//            $this->log->write(print_r($product_info, true));

			if ($product_info) {
                    if (empty($this->request->post['quantity']))
                        $quantity = 1;
                    else
                        $quantity = $this->request->post['quantity'];

                    $product_total = 0;

                    $products = $this->cart->getProducts();

                    foreach ($products as $product_2) {
                        if ($product_2['product_id'] == $this->request->post['product_id']) {
                            $product_total += $product_2['quantity'];
                        }
				}

				if ($product_info['minimum'] > ($product_total + $quantity)) {
					$json['error']['warning'] = sprintf($this->language->get('error_minimum'), $product_info['name'], $product_info['minimum']);
				}

				if (empty($this->request->post['option']))
                    $option = array();
				else
                    $option = array_filter($this->request->post['option']);

				$product_options = $this->model_catalog_product->getProductOptions($this->request->post['product_id']);

				foreach ($product_options as $product_option)
					if ($product_option['required'] && (empty($this->request->post['option'][$product_option['product_option_id']]) || !$this->request->post['option'][$product_option['product_option_id']]))
						$json['error'][$product_option['product_option_id']] = sprintf($this->language->get('error_required'), $product_option['name']);
			}

			if (!isset($json['error'])) {
//                $this->log->write(print_r($option, true));
				$this->cart->add($this->request->post['product_id'], $this->parameters['itemPrice'], $quantity, $option);

				$json['success'] = sprintf($this->language->get('text_success'), $this->url->link('product/product', 'product_id=' . $this->request->post['product_id']), $product_info['name'], $this->url->link('checkout/cart'));

				unset($this->session->data['shipping_methods']);
				unset($this->session->data['shipping_method']);
				unset($this->session->data['payment_methods']);
				unset($this->session->data['payment_method']);
			} else {
				$json['redirect'] = str_replace('&amp;', '&', $this->url->link('product/product', 'product_id=' . $this->request->post['product_id']));
			}
		}

      	if (isset($this->request->post['remove'])) {
        	$this->cart->remove($this->request->post['remove']);

			unset($this->session->data['shipping_methods']);
			unset($this->session->data['shipping_method']);
			unset($this->session->data['payment_methods']);
			unset($this->session->data['payment_method']);
      	}

      	if (isset($this->request->post['voucher'])) {
			if ($this->session->data['vouchers'][$this->request->post['voucher']]) {
				unset($this->session->data['vouchers'][$this->request->post['voucher']]);
			}
		}

		$this->load->model('tool/image');

		$this->data['text_empty'] = $this->language->get('text_empty');

		$this->data['button_checkout'] = $this->language->get('button_checkout');
		$this->data['button_remove'] = $this->language->get('button_remove');

		$this->data['products'] = array();

		foreach ($this->cart->getProducts() as $result) {
			if ($result['image']) {
				$image = $this->model_tool_image->resize($result['image'], 40, 40);
			} else {
				$image = '';
			}

			$option_data = array();
//            $this->log->write(print_r($result['option'], true));
			foreach ($result['option'] as $option) {
                if ($option['type'] != 'file') {
                    $option_data[] = array(
                        'name'  => $option['name'],
                        'value' => utf8_truncate($option['option_value'])
					);
				} else {
					$this->load->library('encryption');

					$encryption = new Encryption($this->config->get('config_encryption'));

					$file = substr($encryption->decrypt($option['option_value']), 0, strrpos($encryption->decrypt($option['option_value']), '.'));

					$option_data[] = array(
						'name'  => $option['name'],
						'value' => utf8_truncate($file)
					);
				}
			}

			if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
				$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')));
			} else {
				$price = false;
			}

			if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
				$total = $this->currency->format($this->tax->calculate($result['total'], $result['tax_class_id'], $this->config->get('config_tax')));
			} else {
				$total = false;
			}

			$this->data['products'][] = array(
				'key'        => $result['key'],
				'product_id' => $result['product_id'],
				'thumb'      => $image,
				'name'       => $result['name'],
				'model'      => $result['model'],
				'option'     => $option_data,
				'quantity'   => $result['quantity'],
				'stock'      => $result['stock'],
				'price'      => $price,
				'total'      => $total,
				'href'       => $this->url->link('product/product', 'product_id=' . $result['product_id'])
			);
		}

		// Gift Voucher
		$this->data['vouchers'] = array();

		if (isset($this->session->data['vouchers']) && $this->session->data['vouchers']) {
			foreach ($this->session->data['vouchers'] as $key => $voucher) {
				$this->data['vouchers'][] = array(
					'key'         => $key,
					'description' => $voucher['description'],
					'amount'      => $this->currency->format($voucher['amount'])
				);
			}
		}

		// Calculate Totals
		$total_data = array();
		$total = 0;
		$taxes = $this->cart->getTaxes();

		if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
			$this->load->model('setting/extension');

			$sort_order = array();

			$results = $this->model_setting_extension->getExtensions('total');

			foreach ($results as $key => $value) {
				$sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
			}

			array_multisort($sort_order, SORT_ASC, $results);

			foreach ($results as $result) {
   				if ($this->config->get($result['code'] . '_status')) {
					$this->load->model('total/' . $result['code']);

					$this->{'model_total_' . $result['code']}->getTotal($total_data, $total, $taxes);
				}
			}

			$sort_order = array();

			foreach ($total_data as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
			}

			array_multisort($sort_order, SORT_ASC, $total_data);
		}

		$json['total'] = sprintf($this->language->get('text_items'), $this->cart->countProducts() + (isset($this->session->data['vouchers']) ? count($this->session->data['vouchers']) : 0), $this->currency->format($total));
    $json['total_data'] = sprintf($this->language->get('text_items_data'), $this->cart->countProducts());

		$this->data['totals'] = $total_data;

		$this->data['checkout'] = $this->url->link('checkout/checkout', '', 'SSL');

        $templateName = '/template/common/cart.tpl';
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . $templateName)) {
			$this->template = $this->config->get('config_template') . $templateName;
		} else {
			$this->template = 'default' . $templateName;
		}

		$json['output'] = $this->render();

		$this->response->setOutput(json_encode($json));
	}
}
?>
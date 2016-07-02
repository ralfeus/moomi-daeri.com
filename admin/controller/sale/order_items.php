<?php
use model\catalog\SupplierDAO;
use model\catalog\SupplierGroupDAO;
use model\sale\CustomerDAO;
use model\sale\OrderItem;
use model\sale\OrderItemDAO;
use system\engine\AdminController;

class ControllerSaleOrderItems extends AdminController {
	private $error = array();
	/** @var \ModelSaleOrder */
	private $modelSaleOrder;

	public function __construct($registry) {
		parent::__construct($registry);
        $this->takeSessionVariables();
		$this->load->language('sale/order_items');

		$this->load->model('catalog/product');
		$this->load->library('Status');
		$this->modelSaleOrder = $this->load->model('sale/order');
		$this->load->model('sale/order_item_history');
		$this->load->model('tool/image');
	}

	public function index() {
		$this->getList();
	}

    public function finishSupplierOrders() {
        $orderItems = OrderItemDAO::getInstance()->getOrderItems(array(
                'filterStatusId' => array(ORDER_ITEM_STATUS_ORDERED),
                'filterTimeModifiedFrom' => date('Y-m-d'),
                'filterTimeModifiedTo' => date('Y-m-d H:i:s')
            ), null, true
        );
        $localShipping = array();
        foreach ($orderItems as $orderItem) {
            if (array_key_exists($orderItem->getSupplierId(), $localShipping)) {
                $localShipping[$orderItem->getSupplierId()]['total'] += $orderItem->getPrice() * $orderItem->getQuantity();
                $localShipping[$orderItem->getSupplierId()]['orderItemIds'] .= ', ' . $orderItem->getId();
                $orderItem->setShippingCost(0);
                OrderItemDAO::getInstance()->saveOrderItem($orderItem, true);
            } else {
                $localShipping[$orderItem->getSupplierId()]['orderItem'] = $orderItem;
                $localShipping[$orderItem->getSupplierId()]['total'] = $orderItem->getPrice() * $orderItem->getQuantity();
                $localShipping[$orderItem->getSupplierId()]['orderItemIds'] = $orderItem->getId();
                $orderItem->setShippingCost($orderItem->getSupplier()->getShippingCost());
            }
        }
        /// Check whether suppliers have free shipping
        $this->session->data['notifications']['success'] = "Successfully calculated local shipping cost for following order items:<br />";
        foreach ($localShipping as $supplierId => $supplierEntry) {
            /** @var OrderItem $orderItem */
            $orderItem = $supplierEntry['orderItem'];
            if ($supplierEntry['total'] >= $orderItem->getSupplier()->getFreeShippingThreshold()) {
                $orderItem->setShippingCost(0);
            }
            OrderItemDAO::getInstance()->saveOrderItem($orderItem, true);
            $this->session->data['notifications']['success'] .= $supplierId . '=>' . $supplierEntry['orderItemIds'] . "<br />";
        }
        $this->redirect($this->url->link('sale/order_items', 'token=' . $this->parameters['token'], 'SSL'));
    }

	private function getCustomers()	{
		$result = array();
		foreach (OrderItemDAO::getInstance()->getOrderItemsCustomers($this->parameters) as $customer) {
            $result[$customer->getId()] = array(
                'nickname_name' => $customer->getName() . ' / ' . $customer->getNickName()
                //'isCustomerOrderReady' => $this->isCustomerOrderReady($customer['customer_id'])
            );
        }
		uasort($result, function($a, $b) {
            return strnatcasecmp($a['nickname_name'], $b['nickname_name']);
        });
		return $result;
	}

	private function getSuppliers()	{
        $result = array();
		foreach (OrderItemDAO::getInstance()->getOrderItemsSuppliers($this->parameters) as $supplier) {
            $result[$supplier->getId()] = $supplier->getName();
        }
		natcasesort($result);
		return $result;
	}

//	private function isCustomerOrderReady($customer_id) {
//		if (empty($customer_id)) {
//			return false;
//		}
//		$readyOrders = $this->modelSaleOrder->getTotalOrders(array(
//				'filterCustomerId' => array($customer_id),
//				'filterStatusId' => array(ORDER_STATUS_READY_TO_SHIP)
//		));
//		return (bool)$readyOrders;
//
//	  $this->load->model('sale/order');
//	  $orders = $this->model_sale_order->getAllCustomerOrders($customer_id);
//
//	  foreach ($orders as $index => $order) {
//		if($this->isOrderReady($order['order_id'])) {
//		  //var_dump($customer_id); echo "<br />";
//		  //var_dump($order['order_id']); echo "<br /><br />";
//		  return true;
//		}
//	  }
//	  return false;
//	}

	private function getFilterStrings()	{
		$result = array();
		foreach ($this->parameters as $key => $value) {
			if ((strpos($key, 'filter') === false) || empty($value))
				continue;
			if ($key == 'filterCustomerId')
			{
				$customers = $this->getCustomers();
				$filterCustomerString = '';
				foreach ($value as $customerId)
					$filterCustomerString .= ',' . $customers[$customerId];
				$result['CustomerId'] = substr($filterCustomerString, 1);
			}
			elseif ($key == 'filterStatusId')
			{
				$filterStatusString = '';
				foreach ($value as $statusId)
					$filterStatusString .= ',' . Status::getStatus($statusId, $this->config->get('config_language_id'));
				$result['StatusId'] = substr($filterStatusString, 1);
			}
			elseif ($key == 'filterSupplierId')
			{
				$suppliers = $this->getSuppliers();
				$filterSupplierString = '';
				foreach ($value as $supplierId)
					$filterSupplierString .= ',' . $suppliers[$supplierId];
				$result['SupplierId'] = substr($filterSupplierString, 1);
			}
			elseif (is_array($value))
				$result[substr($key, 6)] = implode(', ', $value);
			else
				$result[substr($key, 6)] = $value;
		}
		return $result;
	}

	private function getList() 	{
        /** @var ModelSaleAffiliate $modelSaleAffiliate */
        $modelSaleAffiliate = $this->load->model('sale/affiliate');

		$order = '';
		$sort = "";

		$this->document->setTitle($this->language->get('heading_title'));

		$filterParams = $this->getFilterParameters();
		$filterParams['token'] = $this->parameters['token'];
		$urlFilterParameters = $this->buildUrlParameterString($filterParams);
		$urlParameters = $urlFilterParameters .
			'&page=' . $this->parameters['page'];

		$this->setBreadcrumbs();

		$this->data['invoice'] = $this->url->link('sale/invoice/showForm', $urlParameters, 'SSL');
		$this->data['print'] = $this->url->link('sale/order_items/print_page', $urlParameters, 'SSL');
		$this->data['printWithoutNick'] = $this->url->link('sale/order_items/print_page_removed_nickname', $urlParameters, 'SSL');
		$this->data['customers'] = $this->getCustomers();
		$this->data['suppliers'] = $this->getSuppliers();
		/// Build sort URLs
		$this->data['sort_order_id'] = $this->url->link('sale/order_items', "$urlParameters&sort=order_id", 'SSL');
		$this->data['sort_order_item_id'] = $this->url->link('sale/order_items', "$urlParameters&sort=order_item_id", 'SSL');
		$this->data['sort_supplier'] = $this->url->link('sale/order_items', "$urlParameters&sort=supplier_name", 'SSL');

		$this->data['order_items'] = array();
		$data = $this->parameters;
        $data['sort'] = 'supplier_name, op.name';
		$data['start']           = ($data['page'] - 1) * $this->config->get('config_admin_limit');
		$data['limit']           = $this->config->get('config_admin_limit');

		$orderItems = OrderItemDAO::getInstance()->getOrderItems($data, null, true);

		$arrReady = array();

		if ($orderItems) {
			foreach ($orderItems as $orderItem) {
                $temp = $this->model_sale_order->getOrderTotals($orderItem->getOrderId());

                $cuoponed = 0;
                foreach ($temp as $v) {
                    if ($v['code'] == 'coupon') {
                    $_code = explode('(',$v['title']);
                    $_code = explode(')',$_code[1]);
                    $code = $_code[0];
                    $coupon_products = $this->model_sale_order->getCouponProducts($code);
                        if ($coupon_products) {
                            if (in_array($orderItem->getProductId(), $coupon_products)){
                            $cuoponed = 1;
                            } else {
                            $cuoponed = 0;
                            }
                        } else {
                            $cuoponed = 1;
                        }
                    }
                }

				if (!empty($orderItem->getAffiliateId())) {
					$orderItem->setAffiliateTransactionAmount($this->getCurrency()->format(
                            $orderItem->getTotal() * $modelSaleAffiliate->getProductAffiliateCommission($orderItem->getProductId()) / 100, 
                            $this->config->get('config_currency')
                    ));
				}

				if(!isset($arrReady[$orderItem->getOrderId()])) {
				  $arrReady[$orderItem->getOrderId()] = $this->isOrderReady($orderItem->getOrderId());
				  /*if($arrReady[$orderItem->getOrderId()]) {
					echo $orderItem->getOrderId(); die();
				  }*/
				}
				if ($orderItem->getProductId() == REPURCHASE_ORDER_PRODUCT_ID)
				{
					$productOptions = OrderItemDAO::getInstance()->getOptions($orderItem->getId());
					if (!empty($productOptions[REPURCHASE_ORDER_IMAGE_URL_OPTION_ID]))
					{
						$orderItem->setImagePath($productOptions[REPURCHASE_ORDER_IMAGE_URL_OPTION_ID]['value']);
					}
				}

				if ($orderItem->getImagePath() && file_exists(DIR_IMAGE . $orderItem->getImagePath())) {
					$image = $this->model_tool_image->resize($orderItem->getImagePath(), 100, 100);
				} else {
					$image = $this->model_tool_image->resize('no_image.jpg', 100, 100);
				}

//				$supplier_url = "";
//				foreach ($this->model_catalog_product->getProductAttributes($orderItem['product_id']) as $attribute)
//					if ($attribute['name'] == 'Supplier URL')
//					{
//						foreach ($attribute['product_attribute_description'] as $attribute_language_version)
//							if ($attribute_language_version['text'])
//							{
//								$supplier_url = $attribute_language_version['text'];
//								break;
//							}
//						break;
//					}

				$action = array();

				if (!empty($orderItem->getAffiliateId())) {
					if (empty($orderItem->getAffiliateTransactionId())) {
						$action[] = array(
							'text' => '<a href="#" class="commissionAction" data-onclick="commissionAction(\'add\', ' . $orderItem->getId() . ')">' .
							$this->language->get('text_commission_add') . " {$orderItem->getAffiliateTransactionAmount()}"
							.'</a>',
							);
					} else {
						$action[] = array(
							'text' => '<a href="#" class="commissionAction" data-onclick="commissionAction(\'del\', ' . $orderItem->getId() . ')">' .
							$this->language->get('text_commission_remove') . " {$orderItem->getAffiliateTransactionAmount()}"
							.'</a>',
							);
					}
				}

				$action[] = array(
					'text' => $this->language->get('text_supplier_url'),
					'href' => $orderItem->getSupplierUrl()
				);
				$action[] = array(
					'text' => $this->language->get('text_get_history'),
					'href' => 'javascript:getStatusHistory(' . $orderItem->getId() . ')'
				);

				$this->data['order_items'][] = array(
					'comment'                   => $orderItem->getPrivateComment(),
					'id'			                  => $orderItem->getId(),
					'image_path'	              => $image,
					'name'			                => $orderItem->getName(),
					'model'                     => $orderItem->getModel(),
					'name_korean'	              => $orderItem->getKoreanName(),
					'order_id'					        => $orderItem->getOrderId(),
					'isOrderReady'              => $arrReady[$orderItem->getOrderId()],
                    'order_url' => $this->url->link('sale/order/info', 'order_id=' . $orderItem->getOrderId() . '&token=' . $this->session->data['token'], 'SSL'),
					'customer_name'             => $orderItem->getCustomer()['firstname'] . ' ' . $orderItem->getCustomer()['lastname'],
					'customer_nick'             => $orderItem->getCustomer()['nickname'],
					'options'                   => nl2br(OrderItemDAO::getInstance()->getOrderItemOptionsString($orderItem->getId())),
                    'privateComment' => $orderItem->getPrivateComment(),
					'publicComment'             => $orderItem->getPublicComment(),
					'supplier_name'	            => $orderItem->getSupplier()->getName(),
					'supplier_url'	            => $orderItem->getSupplierUrl(),
					'supplier_internal_model'   => $orderItem->getInternalModel(),
					'status'       	            => $orderItem->getStatusId() ? Status::getStatus($orderItem->getStatusId(), $this->config->get('language_id')) : "",
					'price'			                => $this->getCurrency()->format($orderItem->getPrice(), $this->config->get('config_currency')),
					'weight'		                => $this->weight->format($orderItem->getWeight(), $orderItem->getWeightClassId()),
					'quantity'		              => $orderItem->getQuantity(),
					'selected'                  =>
						isset($_REQUEST['selectedItems'])
						&& is_array($_REQUEST['selectedItems'])
						&& in_array($orderItem->getId(), $_REQUEST['selectedItems']),
					'action'                    => $action,
					'cuoponed'                    => $cuoponed
				); 
			}
		}
 
		$this->data['heading_title'] = $this->language->get('heading_title');
		$this->data['text_missing'] = $this->language->get('text_missing');
		$this->data['text_no_results'] = $this->language->get('text_no_results');
		$this->data['text_no_selected_items'] = $this->language->get('error_no_selected_items');
		$this->data['column_item_image'] = $this->language->get('column_item_image');
		$this->data['column_item_name'] = $this->language->get('field_item');
		$this->data['field_order_id'] = $this->language->get('field_order_id');
		$this->data['column_order_item_id'] = $this->language->get('field_order_item_id');
		$this->data['column_customer'] = $this->language->get('column_customer');
		$this->data['column_customer_nick'] = $this->language->get('field_customer_nick');
		$this->data['column_supplier'] = $this->language->get('column_supplier');
//        $this->data['column_supplier_group'] = $this->language->get('field_supplier_group');
		$this->data['column_internal_model'] = $this->language->get('field_internal_model');
		$this->data['column_status'] = $this->language->get('field_status');
		$this->data['column_price'] = $this->language->get('column_price');
		$this->data['column_quantity'] = $this->language->get('column_quantity');
		$this->data['column_timeline'] = $this->language->get('field_timeline');
		$this->data['column_action'] = $this->language->get('column_action');
		$this->data['columnWeight'] = $this->language->get('COLUMN_WEIGHT');

		$this->data['button_cancel'] = $this->language->get('button_cancel');
		$this->data['button_filter'] = $this->language->get('FILTER');
		$this->data['button_invoice'] = $this->language->get('button_invoice');
		$this->data['button_print'] = $this->language->get('button_print');
		$this->data['button_ready'] = $this->language->get('button_ready');
		$this->data['button_soldout'] = $this->language->get('button_soldout');
		$this->data['textPrintWithoutNick'] = $this->language->get('PRINT_NO_NICKNAME');

		$this->data['token'] = $this->session->data['token'];

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}


	    $this->initStatuses();

		$pagination = new Pagination();
		$pagination->total = OrderItemDAO::getInstance()->getOrderItemsCount($data);
		$pagination->page = $this->parameters['page'];
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('sale/order_items', "$urlParameters&page={page}", 'SSL');
		$this->data['pagination'] = $pagination->render();

	    $this->data = array_merge($this->data, $this->parameters);

//		$this->load->model('localisation/order_status');
		///$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		$this->template = 'sale/orderItemsList.tpl.php';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->getResponse()->setOutput($this->render());
	}

	private function isOrderReady($order_id) {
		$order = $this->modelSaleOrder->getOrder($order_id);
		return $order['order_status_id'] == ORDER_STATUS_READY_TO_SHIP;

	  $items = $this->model_sale_order->getOrderProducts($order_id);
	  $isReady = true;
	  $flagReady = false;
	  foreach ($items as $index => $item) {
		if($item['status_id'] == 327683) {
		  $flagReady = true;
		}
		else {
		  if(($item['status_id'] == 327684 || $item['status_id'] == 327686 || $item['status_id'] == 327780 || $item['status_id'] == 327781) || ($item['status_id']>=393217 && $item['status_id'] != 393219 && $item['status_id'] != 393223)) {
			$isReady = $isReady & true;
		  }
		  else {
			$isReady = $isReady & false;
		  }
		}
	  }

	  return $flagReady && $isReady;
	}

	private function getProductAttribute($product_id, $attribute_name)
	{
		foreach ($this->model_catalog_product->getProductAttributes($product_id) as $attribute)
			if ($attribute['name'] == $attribute_name)
			{
				foreach ($attribute['product_attribute_description'] as $attribute_language_version)
					if ($attribute_language_version['text'])
						return $attribute_language_version['text'];
			}
		return "";
	}

	protected function initParameters()
	{
		$this->parameters['comment'] = empty($_REQUEST['comment']) ? null : $_REQUEST['comment'];
        $this->parameters['filterComment'] = empty($_REQUEST['filterComment']) ? null : $_REQUEST['filterComment'];
		$this->parameters['filterStatusId'] = empty($_REQUEST['filterStatusId']) ? array() : $_REQUEST['filterStatusId'];
		$this->parameters['filterCustomerId'] = empty($_REQUEST['filterCustomerId']) ? array() : $_REQUEST['filterCustomerId'];
		$this->parameters['filterItem'] = empty($_REQUEST['filterItem']) ? null : $_REQUEST['filterItem'];
//        $this->parameters['filter_supplier_group'] = empty($_REQUEST['filter_supplier_group']) ? null : $_REQUEST['filter_supplier_group'];
		$this->parameters['filterOrderId'] = empty($_REQUEST['filterOrderId']) ? null : $_REQUEST['filterOrderId'];
		$this->parameters['filterOrderItemId'] = empty($_REQUEST['filterOrderItemId']) ? null : $_REQUEST['filterOrderItemId'];
		$this->parameters['filterSupplierId'] = empty($_REQUEST['filterSupplierId']) ? array() : $_REQUEST['filterSupplierId'];
		$this->parameters['order'] = empty($_REQUEST['order']) ? null : $_REQUEST['order'];
		$this->parameters['orderItemId'] = empty($_REQUEST['orderItemId']) ? null : $_REQUEST['orderItemId'];
		$this->parameters['page'] = empty($_REQUEST['page']) ? 1 : $_REQUEST['page'];
		$this->parameters['private'] = empty($_REQUEST['private']) ? false : $_REQUEST['private'];
		$this->parameters['selectedItems'] = empty($_REQUEST['selectedItems']) ? array() : $_REQUEST['selectedItems'];
		$this->parameters['sort'] = empty($_REQUEST['sort']) ? null : $_REQUEST['sort'];
		$this->parameters['token'] = $this->session->data['token'];

	}

	private function initStatuses()
	{
		$this->data['statuses'] = array();
		foreach (Status::getStatuses(GROUP_ORDER_ITEM_STATUS, $this->config->get('language_id')) as $statusId => $status)
			$this->data['statuses'][GROUP_ORDER_ITEM_STATUS][] = array(
				'id'    => $statusId,
				'name' => $status,
				'settable' => true,
				'viewable' => true,
				'set_status_url' => $this->url->link('sale/order_items/set_status',
					"order_item_new_status=$statusId&token=" . $this->parameters['token'], 'SSL')
			);

		foreach (Status::getStatuses(GROUP_REPURCHASE_ORDER_ITEM_STATUS, $this->config->get('language_id')) as $statusId => $status)
			$this->data['statuses'][GROUP_REPURCHASE_ORDER_ITEM_STATUS][] = array(
				'id' => $statusId,
				'name' => $status
			);
	}

	private function isValidOrderItemId($order_item_id) {
		return isset($order_item_id) && OrderItemDAO::getInstance()->getOrderItem($order_item_id);
	}

    /**
     * @throws Exception
     */
    public function print_page() {
		$this->load->language('sale/order_items');

		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('catalog/product');
		$this->load->model('tool/image');

		if (isset($_REQUEST['order'])) {
			$order = $_REQUEST['order'];
		} else {
			$order = 'ASC';
		}

		$data = array(
			'selected_items'    => $_REQUEST['selectedItems'],
			'sort'              => 'supplier_name, op.name',
			'order'             => $order
		);

        $this->data['canSeeSuppliers'] = $this->canSeeSuppliers();

		foreach (OrderItemDAO::getInstance()->getOrderItems($data, null, true) as $orderItem) {
			if ($orderItem->getImagePath() && file_exists(DIR_IMAGE . $orderItem->getImagePath())) {
				$image = $this->model_tool_image->resize($orderItem->getImagePath(), 100, 100);
			} else {
				$image = $this->model_tool_image->resize('no_image.jpg', 100, 100);
			}

			if (SupplierGroupDAO::getInstance()->getSupplierGroup($orderItem->getSupplierGroupId()))
			{
				$supplier_group = SupplierGroupDAO::getInstance()->getSupplierGroup($orderItem->getSupplierGroupId());
				$supplier_group_name = $supplier_group['name'];
			}
			else
				$supplier_group_name = "";

			$this->data['order_items'][] = array(
				'comment'       => $orderItem->getPrivateComment(),
				'customer_name' => $orderItem->getCustomer()['firstname'] . ' ' . $orderItem->getCustomer()['lastname'],
				'customer_nick' => $orderItem->getCustomer()['nickname'],
				'id'            => $orderItem->getId(),
				'image_path'	=> $image,
				'name'			=> $orderItem->getName(),
				'name_korean'	=> $orderItem->getKoreanName(),
				'options'       => OrderItemDAO::getInstance()->getOrderItemOptionsString($orderItem->getId()),
				'order_id'      => $orderItem->getOrderId(),
                'privateComment' => $orderItem->getPrivateComment(),
                'publicComment' => $orderItem->getPublicComment(),
				'quantity'		=> $orderItem->getQuantity(),
				'status'       	    => $orderItem->getStatusId(),
				'supplier_group'    => $supplier_group_name,
				'supplier_name'	    => $orderItem->getSupplier()->getName()
			);
		}

		$this->data['filters'] = $this->getFilterStrings();
		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_no_results'] = $this->language->get('text_no_results');
		$this->data['text_missing'] = $this->language->get('text_missing');

		$this->data['column_comment'] = $this->language->get('field_comment');
		$this->data['column_item_image'] = $this->language->get('column_item_image');
		$this->data['column_item'] = $this->language->get('field_item');
		$this->data['column_customer'] = $this->language->get('column_customer');
		$this->data['column_nickname'] = $this->language->get('field_customer_nick');
		$this->data['column_order_id'] = $this->language->get('field_order_id');
		$this->data['column_order_item_id'] = $this->language->get('field_order_item_id');
		$this->data['textSupplier'] = $this->language->get('column_supplier');
		$this->data['column_status'] = $this->language->get('column_status');
		$this->data['column_price'] = $this->language->get('column_price');
		$this->data['column_quantity'] = $this->language->get('column_quantity');
		$this->data['textFilters'] = $this->language->get('FILTERS');

		//$this->data['token'] = $this->session->data['token'];

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}

//		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

		$this->template = 'sale/order_items_list_print.tpl.php';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->getResponse()->setOutput($this->render());
	}

	public function print_page_removed_nickname() {
		$this->load->language('sale/order_items');

		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('catalog/product');
		$this->load->model('catalog/supplier_group');
		$this->load->model('tool/image');

		if (isset($_REQUEST['sort'])) {
			$sort = $_REQUEST['sort'];
		} else {
			$sort = 'order_item_id';
		}

		if (isset($_REQUEST['order'])) {
			$order = $_REQUEST['order'];
		} else {
			$order = 'ASC';
		}

		$data = array(
			'selected_items'    => $_REQUEST['selectedItems'],
			'sort'              => $sort,
			'order'             => $order
		);

		foreach (OrderItemDAO::getInstance()->getOrderItems($data, null, true) as $orderItem)
		{
			if ($orderItem->getImagePath() && file_exists(DIR_IMAGE . $orderItem->getImagePath())) {
				$image = $this->model_tool_image->resize($orderItem->getImagePath(), 100, 100);
			} else {
				$image = $this->model_tool_image->resize('no_image.jpg', 100, 100);
			}

			if (SupplierGroupDAO::getInstance()->getSupplierGroup($orderItem->getSupplierGroupId()))
			{
				$supplier_group = SupplierGroupDAO::getInstance()->getSupplierGroup($orderItem->getSupplierGroupId());
				$supplier_group_name = $supplier_group['name'];
			}
			else
				$supplier_group_name = "";

			$this->data['order_items'][] = array(
				'comment'       => $orderItem->getPrivateComment(),
			   // 'customer_name' => $order_item['customer_name'],
			  //  'customer_nick' => $order_item['customer_nick'],
				'id'            => $orderItem->getId(),
				'image_path'	=> $image,
				'name'			=> $orderItem->getName(),
				'name_korean'	=> $orderItem->getKoreanName(),
				'options'       => OrderItemDAO::getInstance()->getOrderItemOptionsString($orderItem->getId()),
				'order_id'      => $orderItem->getOrderId(),
				'quantity'		=> $orderItem->getQuantity(),
				'status'       	    => $orderItem->getStatusId(),
				'supplier_group'    => $supplier_group_name,
				'supplier_name'	    => $orderItem->getSupplier()->getName()
			);
		}
		$this->data['storeAddress'] = $this->config->get('config_address');
		$this->data['storeName'] = $this->config->get('config_name');
		$this->data['storePhone'] = $this->config->get('config_telephone');

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_no_results'] = $this->language->get('text_no_results');
		$this->data['text_missing'] = $this->language->get('text_missing');

		$this->data['column_comment'] = $this->language->get('field_comment');
		$this->data['column_item_image'] = $this->language->get('column_item_image');
		$this->data['column_item'] = $this->language->get('field_item');
		// $this->data['column_customer'] = $this->language->get('column_customer');
		// $this->data['column_nickname'] = $this->language->get('field_customer_nick');
		$this->data['column_order_id'] = $this->language->get('field_order_id');
		$this->data['column_order_item_id'] = $this->language->get('field_order_item_id');
		$this->data['column_supplier_group'] = $this->language->get('field_supplier_group');
		$this->data['column_status'] = $this->language->get('column_status');
		$this->data['column_price'] = $this->language->get('column_price');
		$this->data['column_quantity'] = $this->language->get('column_quantity');

		//$this->data['token'] = $this->session->data['token'];

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}

		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

		$this->template = 'sale/order_items_list_print_nickname_removed.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->getResponse()->setOutput($this->render());
	}
	public function set_status() {
		$this->load->language('sale/order_items');
		$orderItems = array();

		if (isset($this->parameters['selectedItems']))
			$orderItems = array_values($this->parameters['selectedItems']);
		else
			$this->error['warning'] = $this->language->get('error_no_selected_items');
        $orderItemNewStatus = null;
		if (isset($_REQUEST['order_item_new_status']))
            $orderItemNewStatus = $_REQUEST['order_item_new_status'];
		else
			$this->error['warning'] = $this->language->get('error_no_status_set');

		if (!isset($this->error['warning'])) {
			$this->error['warning'] = '';
			$this->session->data['success'] = '';
			$this->load->model('localisation/order_item_status');

			foreach ($orderItems as $orderItemId) {
				if (OrderItemDAO::getInstance()->setStatus($orderItemId, $orderItemNewStatus)) {
					$this->session->data['success'] .= sprintf(
						$this->language->get("text_status_set"),
						$orderItemId,
						Status::getStatus($orderItemNewStatus, $this->config->get('language_id')));
				}
				else {
					$this->error['warning'] .= sprintf(
						$this->language->get('error_status_already_set'),
						$orderItemId,
						Status::getStatus($orderItemNewStatus, $this->config->get('language_id')));
				}
				$orderItem = OrderItemDAO::getInstance()->getOrderItem($orderItemId);
				$this->modelSaleOrder->verifyOrderCompletion($orderItem->getOrderId());
			}
//			$this->clearSelection();
		}

		$this->index();
	}

	public function save_comment() {

		if (!$this->isValidOrderItemId($this->parameters['orderItemId']))
			$this->getResponse()->addHeader("HTTP/1.0 400 Bad request");
		else
			OrderItemDAO::getInstance()->setOrderItemComment(
				$this->parameters['orderItemId'],
				$this->parameters['comment'],
				$this->parameters['private']
			);

		$this->getResponse()->setOutput('');
	}

	public function saveQuantity() {
		if (isset($_REQUEST['orderItemId']))
			$orderItemId = $_REQUEST['orderItemId'];
		if (isset($_REQUEST['quantity']))
			$quantity = $_REQUEST['quantity'];

		if (!$this->isValidOrderItemId($orderItemId))
			$this->getResponse()->addHeader("HTTP/1.0 400 Bad request");
		else
			OrderItemDAO::getInstance()->setOrderItemQuantity($orderItemId, $quantity);
		$this->getResponse()->setOutput('');
	}

    public function saveShipping() {
        $orderItemId = isset($_REQUEST['orderItemId']) ? $_REQUEST['orderItemId'] : null;
        $shipping = isset($_REQUEST['shipping']) ? $_REQUEST['shipping'] : null;

        if (!$this->isValidOrderItemId($orderItemId))
            $this->getResponse()->addHeader("HTTP/1.0 400 Bad request");
        else {
            $orderItem = OrderItemDAO::getInstance()->getOrderItem($orderItemId);
            $orderItem->setShippingCost($shipping);
            OrderItemDAO::getInstance()->saveOrderItem($orderItem, true);
        }
        $this->getResponse()->setOutput('');
    }

	protected function setBreadcrumbs()
	{
		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => false
		);
		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('sale/order_items', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
		);
	}


	public function commission() {

		$json = array();

		if (!$this->user->hasPermission('modify', 'sale/order_items')) {
			$json['error'] = $this->language->get('error_permission'); 
		} elseif (
			isset($this->request->get['order_product_id']) &&
			(int)$this->request->get['order_product_id'] &&
			isset($this->request->get['action']) &&
			in_array($this->request->get['action'], array('add', 'del'))
			) {

			$this->load->model('sale/affiliate');
			if ('add' == $this->request->get['action']) {
				$success = $this->model_sale_affiliate->setOrderProductAffiliateCommission((int)$this->request->get['order_product_id']);
				if ($success) {
					$json['success'] = $this->language->get('text_commission_added');
					$json['text'] = $this->language->get('text_commission_remove');
					$json['action'] = 'del';
				} else {
					$json['error'] = $this->language->get('error_action');
				}
			} elseif ('del' == $this->request->get['action']) {
					$this->model_sale_affiliate->deleteTransaction(null, (int)$this->request->get['order_product_id']);
					$json['success'] = $this->language->get('text_commission_removed');
					$json['text'] = $this->language->get('text_commission_add');
					$json['action'] = 'add';
			}
		} else {
			$json['error'] = $this->language->get('error_bad_request'); 
		}

		$this->getResponse()->setOutput(json_encode($json));
	}

    /**
     * @return bool
     */
    private function canSeeSuppliers() {
         return $this->getUser()->getUsergroupId() == 1;
    }
}
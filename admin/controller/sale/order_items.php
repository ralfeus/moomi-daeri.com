<?php
use model\sale\OrderItemDAO;

class ControllerSaleOrderItems extends Controller {
	private $error = array();
	/** @var \ModelSaleOrderItem */
	private $modelSaleOrderItem;
	/** @var \ModelSaleOrder */
	private $modelSaleOrder;

	public function __construct($registry)
	{
		parent::__construct($registry);

		$this->load->language('sale/order_items');

		$this->load->model('catalog/product');
		$this->load->model('catalog/supplier_group');
		$this->load->library('Status');
		$this->modelSaleOrder = $this->load->model('sale/order');
		$this->modelSaleOrderItem = $this->load->model('sale/order_item');
		$this->load->model('sale/order_item_history');
		$this->load->model('tool/image');
	}

	public function index()
	{
		$this->getList();
	}

	private function clearSelection()
	{
		//unset($this->session->data['selected_items']['sale/order_items']);
	}

	private function getCustomers()
	{
		$data = array();
		foreach ($this->parameters as $key => $value) {
			if (strpos($key, 'filter') === false)
				continue;
			$data[$key] = $value;

		}
		unset($data['filterCustomerId']);
		$result = array(); $tmpResult = array();
		foreach ($this->modelSaleOrderItem->getOrderItems($data) as $orderItem)
			//if (!in_array($orderItem['customer_id'], $tmpResult))
			if (!isset($tmpResult[$orderItem['customer_id']]))
				$tmpResult[$orderItem['customer_id']] = array('nickname_name' => $orderItem['customer_name'] . ' / ' . $orderItem['customer_nick'], 'isCustomerOrderReady' => $this->isCustomerOrderReady($orderItem['customer_id']));
		natcasesort($tmpResult);
 //var_dump($this->isCustomerOrderReady($orderItem['customer_id']));die();
		return $tmpResult;
	}

	private function getSuppliers()
	{
		$data = array();
		foreach ($this->parameters as $key => $value)
		{
			if (strpos($key, 'filter') === false)
				continue;
			$data[$key] = $value;
		}
		unset($data['filterSupplierId']);
		$result = array(); $tmpResult = array();
		foreach ($this->modelSaleOrderItem->getOrderItems($data) as $orderItem)
			if (!in_array($orderItem['supplier_id'], $tmpResult))
				$tmpResult[$orderItem['supplier_id']] = $orderItem['supplier_name'];
		natcasesort($tmpResult);
		return $tmpResult;
	}

	private function isCustomerOrderReady($customer_id) {
		if (empty($customer_id)) {
			return false;
		}
		$readyOrders = $this->modelSaleOrder->getTotalOrders(array(
				'filterCustomerId' => array($customer_id),
				'filterStatusId' => array(ORDER_STATUS_READY_TO_SHIP)
		));
		return (bool)$readyOrders;

	  $this->load->model('sale/order');
	  $orders = $this->model_sale_order->getAllCustomerOrders($customer_id);

	  foreach ($orders as $index => $order) {
		if($this->isOrderReady($order['order_id'])) {
		  //var_dump($customer_id); echo "<br />";
		  //var_dump($order['order_id']); echo "<br /><br />";
		  return true;
		}
	  }
	  return false;
	}

	private function getFilterStrings()
	{
		$result = array();
		foreach ($this->parameters as $key => $value)
		{
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

		$order = '';
		$sort = "";

		$this->document->setTitle($this->language->get('heading_title'));

		$filterParams = array();
		foreach ($this->parameters as $key => $value)
			if (!(strpos($key, 'filter') === false))
				$filterParams[$key] = $value;
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
		$data['start']           = ($data['page'] - 1) * $this->config->get('config_admin_limit');
		$data['limit']           = $this->config->get('config_admin_limit');

		$orderItems = $this->modelSaleOrderItem->getOrderItems($data);

		$arrReady = array();

		if ($orderItems) {

			foreach ($orderItems as $orderItem) {
                /** @var ModelSaleAffiliate $modelSaleAffiliate */
			    $modelSaleAffiliate = $this->load->model('sale/affiliate');
				if (!empty($orderItem['affiliate_id'])) {
					$orderItem['affiliate_transaction_amount'] = $this->currency->format($orderItem['total'] * $modelSaleAffiliate->getProductAffiliateCommission($orderItem['product_id']) / 100, $this->config->get('config_currency'));
				}

				if(!isset($arrReady[$orderItem['order_id']])) {
				  $arrReady[$orderItem['order_id']] = $this->isOrderReady($orderItem['order_id']);
				  /*if($arrReady[$orderItem['order_id']]) {
					echo $orderItem['order_id']; die();
				  }*/
				}
				if ($orderItem['product_id'] == REPURCHASE_ORDER_PRODUCT_ID)
				{
					$productOptions = $this->modelSaleOrderItem->getOrderItemOptions($orderItem['order_product_id']);
					if (!empty($productOptions[REPURCHASE_ORDER_IMAGE_URL_OPTION_ID]))
					{
						$orderItem['image_path'] = $productOptions[REPURCHASE_ORDER_IMAGE_URL_OPTION_ID]['value'];
					}
				}

				if ($orderItem['image_path'] && file_exists(DIR_IMAGE . $orderItem['image_path'])) {
					$image = $this->model_tool_image->resize($orderItem['image_path'], 100, 100);
				} else {
					$image = $this->model_tool_image->resize('no_image.jpg', 100, 100);
				}

				$supplier_url = "";
				foreach ($this->model_catalog_product->getProductAttributes($orderItem['product_id']) as $attribute)
					if ($attribute['name'] == 'Supplier URL')
					{
						foreach ($attribute['product_attribute_description'] as $attribute_language_version)
							if ($attribute_language_version['text'])
							{
								$supplier_url = $attribute_language_version['text'];
								break;
							}
						break;
					}

				$action = array();

				if (!empty($orderItem['affiliate_id'])) {
					if (empty($orderItem['affiliate_transaction_id'])) {
						$action[] = array(
							'text' => '<a href="#" class="commissionAction" data-onclick="commissionAction(\'add\', ' . $orderItem['order_product_id'] . ')">' .
							$this->language->get('text_commission_add') . " {$orderItem['affiliate_transaction_amount']}"
							.'</a>',
							);
					} else {
						$action[] = array(
							'text' => '<a href="#" class="commissionAction" data-onclick="commissionAction(\'del\', ' . $orderItem['order_product_id'] . ')">' .
							$this->language->get('text_commission_remove') . " {$orderItem['affiliate_transaction_amount']}"
							.'</a>',
							);
					}
				}

				$action[] = array(
					'text' => $this->language->get('text_supplier_url'),
					'href' => $supplier_url
				);
				$action[] = array(
					'text' => $this->language->get('text_get_history'),
					'href' => 'javascript:getStatusHistory(' . $orderItem['order_product_id'] . ')'
				);

				$this->data['order_items'][] = array(
					'comment'                   => $orderItem['comment'],
					'id'			                  => $orderItem['order_product_id'],
					'image_path'	              => $image,
					'name'			                => $orderItem['name'],
					'model'                     => $orderItem['model'],
					'name_korean'	              => $this->getProductAttribute($orderItem['product_id'], "Name Korean"),
					'order_id'					        => $orderItem['order_id'],
					'isOrderReady'              => $arrReady[$orderItem['order_id']],
							  'order_url'					        => $this->url->link('sale/order/info', 'order_id=' . $orderItem['order_id'] . '&token=' . $this->session->data['token'], 'SSL'),
					'customer_name'             => $orderItem['customer_name'],
					'customer_nick'             => $orderItem['customer_nick'],
					'options'                   => nl2br($this->modelSaleOrderItem->getOrderItemOptionsString($orderItem['order_product_id'])),
					'publicComment'             => $orderItem['public_comment'],
					'supplier_name'	            => $orderItem['supplier_name'],
					'supplier_url'	            => $supplier_url,
					'supplier_internal_model'   => $orderItem['internal_model'],
					'status'       	            => $orderItem['status'] ? Status::getStatus($orderItem['status'], $this->config->get('language_id')) : "",
					'price'			                => $this->currency->format($orderItem['price'], $this->config->get('config_currency')),
					'weight'		                => $this->weight->format($orderItem['weight'],$orderItem['weight_class_id']),
					'quantity'		              => $orderItem['quantity'],
					'selected'                  =>
						isset($_REQUEST['selectedItems'])
						&& is_array($_REQUEST['selectedItems'])
						&& in_array($orderItem['order_product_id'], $_REQUEST['selectedItems']),
					'action'                    => $action
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
		$pagination->total = $this->model_sale_order_item->getOrderItemsCount($data);
		$pagination->page = $this->parameters['page'];
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('sale/order_items', "$urlParameters&page={page}", 'SSL');
		$this->data['pagination'] = $pagination->render();

	$this->data = array_merge($this->data, $this->parameters);

//		$this->load->model('localisation/order_status');
		///$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		$this->template = 'sale/order_items_list.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render());
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

	public function print_page() {
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

		foreach ($this->modelSaleOrderItem->getOrderItems($data) as $order_item)
		{
			if ($order_item['image_path'] && file_exists(DIR_IMAGE . $order_item['image_path'])) {
				$image = $this->model_tool_image->resize($order_item['image_path'], 100, 100);
			} else {
				$image = $this->model_tool_image->resize('no_image.jpg', 100, 100);
			}

			if ($this->model_catalog_supplier_group->getSupplierGroup($order_item['supplier_group_id']))
			{
				$supplier_group = $this->model_catalog_supplier_group->getSupplierGroup($order_item['supplier_group_id']);
				$supplier_group_name = $supplier_group['name'];
			}
			else
				$supplier_group_name = "";

			$this->data['order_items'][] = array(
				'comment'       => $order_item['comment'],
				'customer_name' => $order_item['customer_name'],
				'customer_nick' => $order_item['customer_nick'],
				'id'            => $order_item['order_product_id'],
				'image_path'	=> $image,
				'name'			=> $order_item['name'],
				'name_korean'	=> $this->getProductAttribute($order_item['product_id'], "Name Korean"),
				'options'       => $this->modelSaleOrderItem->getOrderItemOptionsString($order_item['order_item_id']),
				'order_id'      => $order_item['order_id'],
				'quantity'		=> $order_item['quantity'],
				'status'       	    => $order_item['status'],
				'supplier_group'    => $supplier_group_name,
				'supplier_name'	    => $order_item['supplier_name']
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

		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

		$this->template = 'sale/order_items_list_print.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render());
	}

	public function print_page_removed_nickname() {
		$this->load->language('sale/order_items');

		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('catalog/product');
		$this->load->model('catalog/supplier_group');
		$this->load->model('sale/order_item');
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

		foreach ($this->modelSaleOrderItem->getOrderItems($data) as $order_item)
		{
			if ($order_item['image_path'] && file_exists(DIR_IMAGE . $order_item['image_path'])) {
				$image = $this->model_tool_image->resize($order_item['image_path'], 100, 100);
			} else {
				$image = $this->model_tool_image->resize('no_image.jpg', 100, 100);
			}

			if ($this->model_catalog_supplier_group->getSupplierGroup($order_item['supplier_group_id']))
			{
				$supplier_group = $this->model_catalog_supplier_group->getSupplierGroup($order_item['supplier_group_id']);
				$supplier_group_name = $supplier_group['name'];
			}
			else
				$supplier_group_name = "";

			$this->data['order_items'][] = array(
				'comment'       => $order_item['comment'],
			   // 'customer_name' => $order_item['customer_name'],
			  //  'customer_nick' => $order_item['customer_nick'],
				'id'            => $order_item['order_product_id'],
				'image_path'	=> $image,
				'name'			=> $order_item['name'],
				'name_korean'	=> $this->getProductAttribute($order_item['product_id'], "Name Korean"),
				'options'       => $this->modelSaleOrderItem->getOrderItemOptionsString($order_item['order_item_id']),
				'order_id'      => $order_item['order_id'],
				'quantity'		=> $order_item['quantity'],
				'status'       	    => $order_item['status'],
				'supplier_group'    => $supplier_group_name,
				'supplier_name'	    => $order_item['supplier_name']
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

		$this->response->setOutput($this->render());
	}
	public function set_status() {
		$this->load->language('sale/order_items');
		$order_items = array();

		if (isset($this->parameters['selectedItems']))
			$order_items = array_values($this->parameters['selectedItems']);
		else
			$this->error['warning'] = $this->language->get('error_no_selected_items');

		if (isset($_REQUEST['order_item_new_status']))
			$order_item_new_status = $_REQUEST['order_item_new_status'];
		else
			$this->error['warning'] = $this->language->get('error_no_status_set');

		if (!isset($this->error['warning'])) {
			$this->error['warning'] = '';
			$this->session->data['success'] = '';
			$this->load->model('sale/order_item');
			$this->load->model('localisation/order_item_status');

			foreach ($order_items as $order_item_id) {
				if ($this->modelSaleOrderItem->setOrderItemStatus($order_item_id, $order_item_new_status)) {
					$this->session->data['success'] .= sprintf(
						$this->language->get("text_status_set"),
						$order_item_id,
						Status::getStatus($order_item_new_status, $this->config->get('language_id')));
				}
				else {
					$this->error['warning'] .= sprintf(
						$this->language->get('error_status_already_set'),
						$order_item_id,
						Status::getStatus($order_item_new_status, $this->config->get('language_id')));
				}
				$orderItem = OrderItemDAO::getInstance()->getOrderItem($order_item_id);
				$this->modelSaleOrder->verifyOrderCompletion($orderItem->getOrderId());
			}
			$this->clearSelection();
		}

		$this->index();
	}

	public function save_comment() {

		if (!$this->isValidOrderItemId($this->parameters['orderItemId']))
			$this->response->addHeader("HTTP/1.0 400 Bad request");
		else
			$this->modelSaleOrderItem->setOrderItemComment(
				$this->parameters['orderItemId'],
				$this->parameters['comment'],
				$this->parameters['private']
			);

		$this->response->setOutput('');
	}

	public function saveQuantity()
	{
		if (isset($_REQUEST['orderItemId']))
			$orderItemId = $_REQUEST['orderItemId'];
		if (isset($_REQUEST['quantity']))
			$quantity = $_REQUEST['quantity'];

		if (!$this->isValidOrderItemId($orderItemId))
			$this->response->addHeader("HTTP/1.0 400 Bad request");
		else
			$this->modelSaleOrderItem->setOrderItemQuantity($orderItemId, $quantity);
		$this->response->setOutput('');
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

		$this->response->setOutput(json_encode($json));
	}


}
?>
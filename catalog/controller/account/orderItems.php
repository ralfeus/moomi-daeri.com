<?php
class ControllerAccountOrderItems extends Controller {
	private $error = array();
    private $modelAccountOrderItem;
    private $orderModel;

    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->route = 'account/orderItems';
        $this->load->language($this->route);
        $this->document->setTitle($this->language->get('HEADING_TITLE'));

        $this->load->model('catalog/product');
        $this->load->library('Status');
        $this->orderModel = $this->load->model('account/order');
        $this->modelAccountOrderItem = $this->load->model('account/order_item');
//        $this->load->model('account/order_item_history');
        $this->load->model('tool/image');
    }

    public function cancel()
    {
        $orderItem = $this->modelAccountOrderItem->getOrderItem($this->parameters['orderItemId']);
//        $this->log->write(print_r($orderItem, true));
        $cancelledStatus =
            $orderItem['status'] // take original status
            & 0xFFFF0000 // clean up status value but keep group
            | (ORDER_ITEM_STATUS_CANCELLED & 0x0000FFFF); // set cancelled status
        $this->log->write($cancelledStatus);
        $this->modelAccountOrderItem->setOrderItemStatus($this->parameters['orderItemId'], $cancelledStatus);
        $this->redirect($this->parameters['returnUrl']);
    }

    public function index()
    {
    	$this->getList();
  	}

    private function getList() 	{
//		$this->log->write(print_r($this->request,true));
        $filterParams = array();
        foreach ($this->parameters as $key => $value)
            if (!(strpos($key, 'filter') === false))
                $filterParams[$key] = $value;
        $urlFilterParameters = $this->buildUrlParameterString($filterParams);
        $urlParameters = $urlFilterParameters .
            '&page=' . $this->parameters['page'];

        /// Build sort URLs
        $this->data['sort_order_id'] = $this->url->link($this->route, "$urlParameters&sort=order_id", 'SSL');
        $this->data['sort_order_item_id'] = $this->url->link($this->route, "$urlParameters&sort=order_item_id", 'SSL');
        $this->data['sort_supplier'] = $this->url->link($this->route, "$urlParameters&sort=supplier_name", 'SSL');

        $this->data['order_items'] = array();
        $data = $this->parameters;
        $data['start']           = ($data['page'] - 1) * $this->config->get('config_admin_limit');
        $data['limit']           = $this->config->get('config_admin_limit');
        $data['order']           = 'DESC';
//		$this->log->write(print_r($data, true));
        $orderItems = $this->modelAccountOrderItem->getOrderItems($data);
//        $this->log->write(count($orderItems));
        if ($orderItems)
        {
            foreach ($orderItems as $orderItem)
            {
                if ($orderItem['image_path'] && file_exists(DIR_IMAGE . $orderItem['image_path'])):
                    $image = $this->model_tool_image->resize($orderItem['image_path'], 100, 100);
                else:
                    $image = $this->model_tool_image->resize('no_image.jpg', 100, 100);
                endif;

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

                $this->data['order_items'][] = array(
                    'id'			            => $orderItem['order_product_id'],
                    'image_path'	            => $image,
                    'name'			 => $orderItem['name'],
                    'model'                     => $orderItem['model'],
                    'name_korean'	            => $this->getProductAttribute($orderItem['product_id'], "Name Korean"),
                    'order_id'					=> $orderItem['order_id'],
					'order_url'					=> $this->url->link('sale/order/info', 'order_id=' . $orderItem['order_id'], 'SSL'),
                    'options'       => nl2br($this->modelAccountOrderItem->getOrderItemOptionsString($orderItem['order_product_id'])),
                    'publicComment'                   => $orderItem['public_comment'],
					'status'       	=> $orderItem['status'] ? Status::getStatus($orderItem['status'], $this->config->get('language_id'), true) : "",
                    'price'			=> $this->currency->format($orderItem['price'], $this->config->get('config_currency')),
		            'weight'		=> $this->weight->format($orderItem['weight'],$orderItem['weight_class_id']),
                    'quantity'		=> $orderItem['quantity'],
					'selected'      =>
                        isset($_REQUEST['selectedItems'])
                        && is_array($_REQUEST['selectedItems'])
                        && in_array($orderItem['order_product_id'], $_REQUEST['selectedItems']),
                    'action'                    => $action
                );
            }
//            $this->data['customers'] = $this->getCustomers($data);
        }

		$this->data['headingTitle'] = $this->language->get('HEADING_TITLE');
        $this->data['text_missing'] = $this->language->get('text_missing');
        $this->data['text_no_results'] = $this->language->get('text_no_results');
        $this->data['text_no_selected_items'] = $this->language->get('error_no_selected_items');
		$this->data['column_action'] = $this->language->get('column_action');

        $this->data['button_filter'] = $this->language->get('FILTER');
        $this->data['textItem'] = $this->language->get('ITEM');
        $this->data['textOrderId'] = $this->language->get('ORDER_ID');
        $this->data['textOrderItemId'] = $this->language->get('ORDER_ITEM_ID');
        $this->data['textPrice'] = $this->language->get('PRICE');
        $this->data['textQuantity'] = $this->language->get('QUANTITY');
        $this->data['textStatus'] = $this->language->get('STATUS');
        $this->data['textWeight'] = $this->language->get('COLUMN_WEIGHT');
        $this->data['urlFormAction'] = $this->url->link($this->selfRoute, '', 'SSL');

		if (isset($this->error['warning']))
			$this->data['error_warning'] = $this->error['warning'];
		else
			$this->data['error_warning'] = '';

		if (isset($this->session->data['success']))
        {
			$this->data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		}
        else
			$this->data['success'] = '';

        /// Set up interface
        $this->initStatuses();
        $this->setBreadcrumbs();
		$pagination = new Pagination();
		$pagination->total = $this->modelAccountOrderItem->getOrderItemsCount($data);
		$pagination->page = $this->parameters['page'];
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link($this->route, "$urlParameters&page={page}", 'SSL');
		$this->data['pagination'] = $pagination->render();

        $this->data = array_merge($this->data, $this->parameters);

        $template_name = '/template/account/orderItemsList.tpl';
        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . $template_name))
            $this->template = $this->config->get('config_template') . $template_name;
        else
            $this->template = 'default' . $template_name;

        $this->children = array(
            'common/column_right',
            'common/column_left',
            'common/content_top',
            'common/content_bottom',
            'common/footer',
            'common/header'
        );
//        $this->log->write(print_r($this->data, true));
		$this->response->setOutput($this->render());
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
        $this->log->write(print_r($_REQUEST, true));
        $this->parameters['comment'] = empty($_REQUEST['comment']) ? null : $_REQUEST['comment'];
        $this->parameters['filterStatusId'] = empty($_REQUEST['filterStatusId']) ? array() : $_REQUEST['filterStatusId'];
        $this->parameters['filterItem'] = empty($_REQUEST['filterItem']) ? null : $_REQUEST['filterItem'];
//        $this->parameters['filter_supplier_group'] = empty($_REQUEST['filter_supplier_group']) ? null : $_REQUEST['filter_supplier_group'];
        $this->parameters['filterOrderId'] = empty($_REQUEST['filterOrderId']) ? null : $_REQUEST['filterOrderId'];
        $this->parameters['filterOrderItemId'] = empty($_REQUEST['filterOrderItemId']) ? null : $_REQUEST['filterOrderItemId'];
        $this->parameters['order'] = empty($_REQUEST['order']) ? null : $_REQUEST['order'];
        $this->parameters['orderItemId'] = empty($_REQUEST['orderItemId']) ? null : $_REQUEST['orderItemId'];
        $this->parameters['page'] = empty($_REQUEST['page']) ? 1 : $_REQUEST['page'];
        $this->parameters['private'] = empty($_REQUEST['private']) ? false : $_REQUEST['private'];
        $this->parameters['returnUrl'] = empty($_REQUEST['returnUrl']) ? null : urldecode($_REQUEST['returnUrl']);
        $this->parameters['selectedItems'] = empty($_REQUEST['selectedItems']) ? array() : $_REQUEST['selectedItems'];
        $this->parameters['sort'] = empty($_REQUEST['sort']) ? null : $_REQUEST['sort'];
//        $this->log->write(print_r($this->parameters, true));
    }

    private function initStatuses()
    {
        $this->data['statuses'] = array();
        foreach (Status::getStatuses(GROUP_ORDER_ITEM_STATUS, $this->config->get('language_id')) as $order_item_status)
            $this->data['statuses'][GROUP_ORDER_ITEM_STATUS][] = array(
                'id'    => $order_item_status['status_id'],
                'name' => $order_item_status['name'],
                'settable' => true,
                'viewable' => true,
                'set_status_url' => $this->url->link('sale/order_items/set_status', $this->buildUrlParameterString($this->parameters) . "&order_item_new_status=" . $order_item_status['status_id'], 'SSL')
            );
        foreach (Status::getStatuses(GROUP_REPURCHASE_ORDER_ITEM_STATUS, $this->config->get('language_id')) as $repurchaseOrderStatus)
            $this->data['statuses'][GROUP_REPURCHASE_ORDER_ITEM_STATUS][] = array(
                'id' => $repurchaseOrderStatus['status_id'],
                'name' => $repurchaseOrderStatus['name']
            );
    }

    private function isValidOrderItemId($order_item_id) {
        return isset($order_item_id) && $this->model_sale_order_item->getOrderItem($order_item_id);
    }

    private function setBreadcrumbs()
    {
        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('ACCOUNT'),
            'href'      => $this->url->link('account/account', '', 'SSL'),
            'separator' => ' :: '
        );
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('HEADING_TITLE'),
            'href'      => $this->url->link($this->route, '', 'SSL'),
            'separator' => ' :: '
        );
    }

    public function setStatus() {
        $order_items = array();

		if (isset($_REQUEST['selectedItems']))
			$order_items = array_values($_REQUEST['selectedItems']);
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
            $this->log->write("Setting status '$order_item_new_status' to items:\n" . print_r($order_items, true));

            foreach ($order_items as $order_item_id)
                if ($this->modelAccountOrderItem->setOrderItemStatus($order_item_id, $order_item_new_status))
					$this->session->data['success'] .= sprintf(
						$this->language->get("text_status_set"),
						$order_item_id,
						Status::getStatus($order_item_new_status, $this->config->get('language_id')));
                else
					$this->error['warning'] .= sprintf(
						$this->language->get('error_status_already_set'),
						$order_item_id,
                        Status::getStatus($order_item_new_status, $this->config->get('language_id')));
            $this->clearSelection();
        }

        //print_r($this->data);exit();
        $this->index();
    }

    public function saveComment() {
        $this->log->write(print_r($this->parameters, true));
        if (!$this->isValidOrderItemId($this->parameters['orderItemId']))
            $this->response->addHeader("HTTP/1.0 400 Bad request");
        else
            $this->modelAccountOrderItem->setOrderItemComment(
                $this->parameters['orderItemId'],
                $this->parameters['comment'],
                $this->parameters['private']
            );

        $this->response->setOutput('');
    }

//    public function saveQuantity()
//    {
//        if (isset($_REQUEST['orderItemId']))
//            $orderItemId = $_REQUEST['orderItemId'];
//        if (isset($_REQUEST['quantity']))
//            $quantity = $_REQUEST['quantity'];
//
//        if (!$this->isValidOrderItemId($orderItemId))
//            $this->response->addHeader("HTTP/1.0 400 Bad request");
//        else
//            $this->modelAccountOrderItem->setOrderItemQuantity($orderItemId, $quantity);
//        $this->response->setOutput('');
//    }
}
?>
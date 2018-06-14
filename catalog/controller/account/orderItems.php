<?php
use model\sale\OrderItemDAO;
use system\engine\CustomerZoneController;

class ControllerAccountOrderItems extends CustomerZoneController {
	private $error = array();
    private $orderModel;

    public function __construct($registry) {
        parent::__construct($registry);
        $this->route = 'account/orderItems';
        $this->load->language($this->route);
        $this->document->setTitle($this->language->get('HEADING_TITLE'));

        $this->load->model('catalog/product');
        //$this->load->library('Status');
        $this->orderModel = $this->load->model('account/order');
//        $this->load->model('account/order_item_history');
        $this->load->model('tool/image');
    }

    public function cancel() {
        $orderItem = OrderItemDAO::getInstance()->getOrderItem($this->parameters['orderItemId']);
//        $this->log->write(print_r($orderItem, true));
        $cancelledStatus =
            $orderItem->getStatusId() // take original status
            & 0xFFFF0000 // clean up status value but keep group
            | (ORDER_ITEM_STATUS_CANCELLED & 0x0000FFFF); // set cancelled status
//        $this->log->write($cancelledStatus);
        OrderItemDAO::getInstance()->setStatus($this->parameters['orderItemId'], $cancelledStatus);
        $this->redirect($this->parameters['returnUrl']);
    }

    public function index() {
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
        $data['filterCustomerId'] = $this->getCurrentCustomer()->getId();
        $data['sort'] = 'order_item_id';
        $data['start']           = ($data['page'] - 1) * $this->config->get('config_admin_limit');
        $data['limit']           = $this->config->get('config_admin_limit');
        $data['order']           = 'DESC';
//		$this->log->write(print_r($data, true));
        $orderItems = OrderItemDAO::getInstance()->getOrderItems($data, null, true);
//        $this->log->write(count($orderItems));
        if ($orderItems) {
            foreach ($orderItems as $orderItem) {
                if($orderItem->getImagePath() == '' || $orderItem->getImagePath() == "data/event/agent-moomidae.jpg") {
                  $options = OrderItemDAO::getInstance()->getOptions($orderItem->getId());
                  $itemUrl = !empty($options[REPURCHASE_ORDER_IMAGE_URL_OPTION_ID]['value'])
                    ? $options[REPURCHASE_ORDER_IMAGE_URL_OPTION_ID]['value'] 
                      : '';
                  $orderItem->setImagePath(!empty($itemUrl) ? $itemUrl : $orderItem->getImagePath());
                }
                if ($orderItem->getImagePath() && file_exists(DIR_IMAGE . $orderItem->getImagePath())):
                    $image = $this->model_tool_image->resize($orderItem->getImagePath(), 80, 80);
                else:
                    $image = $this->model_tool_image->resize('no_image.jpg', 80, 80);
                endif;

                $action = array();

                $this->data['order_items'][] = [
                    'id'			            => $orderItem->getId(),
                    'image_path'	            => $image,
                    'name'			 => $orderItem->getName(),
                    'model'                     => $orderItem->getModel(),
//                    'name_korean'	            => $orderItem->getKoreanName(),
                    'order_id'					=> $orderItem->getOrderId(),
					'order_url'					=> $this->url->link('account/order/info', 'order_id=' . $orderItem->getOrderId(), 'SSL'),
                    'options'       => nl2br(OrderItemDAO::getInstance()->getOrderItemOptionsString($orderItem->getId())),
                    'publicComment'                   => $orderItem->getPublicComment(),
					'status'       	=> $orderItem->getStatusId() ? Status::getStatus($orderItem->getStatusId(), $this->config->get('language_id'), true) : "",
                    'price'			=> $orderItem->getCurrency()->getString($orderItem->getPrice(true)),
		            'weight'		=> $this->weight->format($orderItem->getWeight(), $orderItem->getWeightClassId()),
                    'quantity'		=> $orderItem->getQuantity(),
					'selected'      =>
                        isset($_REQUEST['selectedItems'])
                        && is_array($_REQUEST['selectedItems'])
                        && in_array($orderItem->getId(), $_REQUEST['selectedItems']),
                    'action'                    => $action
                ];
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
		$pagination->total = OrderItemDAO::getInstance()->getOrderItemsCount($data);
		$pagination->page = $this->parameters['page'];
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link($this->route, "$urlParameters&page={page}", 'SSL');
		$this->data['pagination'] = $pagination->render();

        $this->data = array_merge($this->data, $this->parameters);

        $template_name = '/template/account/orderItemsList.tpl.php';
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
		$this->getResponse()->setOutput($this->render());
  	}

    protected function initParameters() {
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
        $this->parameters['private'] = empty($_REQUEST['private']) ? false : filter_var($_REQUEST['private'], FILTER_VALIDATE_BOOLEAN);
        $this->parameters['returnUrl'] = empty($_REQUEST['returnUrl']) ? null : urldecode($_REQUEST['returnUrl']);
        $this->parameters['selectedItems'] = empty($_REQUEST['selectedItems']) ? array() : $_REQUEST['selectedItems'];
        $this->parameters['sort'] = empty($_REQUEST['sort']) ? null : $_REQUEST['sort'];
//        $this->log->write(print_r($this->parameters, true));
    }

    private function initStatuses()
    {
        $this->data['statuses'] = array();
        foreach (Status::getStatuses(GROUP_ORDER_ITEM_STATUS, $this->config->get('language_id'), true) as $statusId => $status)
            $this->data['statuses'][GROUP_ORDER_ITEM_STATUS][] = array(
                'id'    => $statusId,
                'name' => $status,
                'settable' => true,
                'viewable' => true,
                'set_status_url' => $this->url->link('sale/order_items/set_status', $this->buildUrlParameterString($this->parameters) . "&order_item_new_status=$statusId", 'SSL')
            );
        foreach (Status::getStatuses(GROUP_REPURCHASE_ORDER_ITEM_STATUS, $this->config->get('language_id'), true) as $statusId => $status)
            $this->data['statuses'][GROUP_REPURCHASE_ORDER_ITEM_STATUS][] = array(
                'id' => $statusId,
                'name' => $status
            );
    }

    private function isValidOrderItemId($order_item_id) {
        return isset($order_item_id) && OrderItemDAO::getInstance()->getOrderItem($order_item_id);
    }

    protected function setBreadcrumbs()
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
            $this->load->model('localisation/order_item_status');
            $this->log->write("Setting status '$order_item_new_status' to items:\n" . print_r($order_items, true));

            foreach ($order_items as $order_item_id)
                if (OrderItemDAO::getInstance()->setStatus($order_item_id, $order_item_new_status))
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
//        $this->log->write(print_r($this->parameters, true));
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
}
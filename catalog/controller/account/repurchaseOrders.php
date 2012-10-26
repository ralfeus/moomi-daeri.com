<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dev
 * Date: 19.6.12
 * Time: 19:28
 * To change this template use File | Settings | File Templates.
 */
class ControllerAccountRepurchaseOrders extends Controller
{
    private $error = array();
    private $modelAccountRepurchaseOrder;

    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->modelAccountRepurchaseOrder = $this->load->model('account/repurchaseOrder');
        $this->language->load('account/repurchaseOrders');
        $this->document->setTitle($this->language->get('HEADING_TITLE'));
        $this->data['headingTitle'] = $this->language->get('HEADING_TITLE');
        $this->load->library('Status');
    }

    public function index()
    {
        if (!$this->customer->isLogged())
        {
            $this->session->data['redirect'] = $this->url->link('account/repurchaseOrders', '', 'SSL');
            $this->redirect($this->url->link('account/login', '', 'SSL'));
        }

        if (isset($this->session->data['success'])) {
            $this->data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $this->data['success'] = '';
        }

        $this->getList();
    }

    public function accept()
    {
        if (empty($this->request->request['orderId']))
            return;

        $this->setStatus($this->request->request['orderId'], REPURCHASE_ORDER_ITEM_STATUS_ACCEPTED);
    }

    public function create()
    {
        if ($this->request->server['REQUEST_METHOD'] == 'POST')
        {
            //print_r($this->request->post);exit();
            if ($this->customer->isLogged())
            {
                $this->modelAccountRepurchaseOrder->addOrder($this->customer->getId(), $this->request->post['order_items']);
                $this->redirect($this->url->link('account/repurchaseOrders', '', 'SSL'));
            }
        }
        else
        {
            $this->data['action'] = $this->url->link('account/repurchaseOrders/create', '', 'SSL');
            $this->showForm();
        }
   }

    private function getList()
    {
        if (isset($this->request->request['page']))
            $page = $this->request->request['page'];
        else
            $page = 1;

        $this->data['start'] = ($page - 1) * $this->config->get("config_catalog_limit");
        $this->data['limit'] = $this->config->get("config_catalog_limit");
        $this->data['orders'] = array();
        $this->data['statuses'] = Status::getStatuses(GROUP_REPURCHASE_ORDER_ITEM_STATUS, $this->config->get('language_id'));
        $data = $this->parameters;
//        $this->log->write(print_r($data, true));
        foreach ($this->modelAccountRepurchaseOrder->getOrders($data) as $repurchase_order)
        {
            if (file_exists(DIR_IMAGE . $repurchase_order['imagePath']))
                $repurchase_order['imagePath'] = $this->load->model('tool/image')->resize($repurchase_order['imagePath'], 100, 100);
            $this->data['orders'][] = array(
                'comment' => $repurchase_order['comment'],
                'orderItemId' => $repurchase_order['orderItemId'],
                'imagePath' => $repurchase_order['imagePath'],
                'itemUrl' => $repurchase_order['itemUrl'],
                'options' => $this->load->model('account/order_item')->getOrderItemOptions($repurchase_order['orderItemId']),
                'quantity' => $repurchase_order['quantity'],
                'statusId' => $repurchase_order['status'],
                'statusName' => Status::getStatus($repurchase_order['status'], $this->config->get('config_language_id'), true),
                'timeAdded'    => $repurchase_order['timeAdded'],
                'total'         => $this->currency->format($repurchase_order['total']),
            );
        }
//        $this->log->write(print_r($this->data['orders'], true));

        /// Set interface
        $this->setBreadcrumbs();

        $this->data['textAccept'] = $this->language->get('ACCEPT');
        $this->data['textComment'] = $this->language->get('COMMENT');
        $this->data['textFilter'] = $this->language->get('FILTER');
        $this->data['textTimeAdded'] = $this->language->get('TIME_ADDED');
        $this->data['textNoItems'] = $this->language->get('NO_ITEMS');
        $this->data['textOrderItemId'] = $this->language->get('ORDER_ITEM_ID');
        $this->data['textQuantity'] = $this->language->get('QUANTITY');
        $this->data['textReject'] = $this->language->get('REJECT');
        $this->data['textStatus'] = $this->language->get('STATUS');
        $this->data['textTotal'] = $this->language->get('TOTAL');

        $template_name = '/template/account/repurchaseOrdersList.tpl';
        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . $template_name)) {
            $this->template = $this->config->get('config_template') . $template_name;
        } else {
            $this->template = 'default' . $template_name;
        }

        $this->children = array(
            'common/column_right',
            'common/column_left',
            'common/content_top',
            'common/content_bottom',
            'common/footer',
            'common/header'
        );

        $pagination = new Pagination();
        $pagination->total = $this->modelAccountRepurchaseOrder->getOrdersCount($data);
        $pagination->page = $page;
        $pagination->limit = $this->config->get("config_catalog_limit");
        $pagination->text = $this->language->get('text_pagination');
        //$pagination->url = $this->modifyUrl("latest_page", "{page}");
        $this->data['pagination'] = $pagination->render();
        $this->data = array_merge($this->data, $this->parameters);

        $this->response->setOutput($this->render());
    }

    protected function initParameters()
    {
        $this->parameters['filterStatusId'] = empty($_REQUEST['filterStatusId']) ? array() : $_REQUEST['filterStatusId'];
    }

    public function reject()
    {
        if (empty($this->request->request['orderId']))
            return;

        $this->setStatus($this->request->request['orderId'], REPURCHASE_ORDER_ITEM_STATUS_REJECTED);
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
            'href'      => $this->url->link('account/repurchaseOrders', '', 'SSL'),
            'separator' => $this->language->get('text_separator')
        );
    }

    private function setStatus($orderId, $statusId)
    {
        $this->modelAccountRepurchaseOrder->setStatus($orderId, $statusId);

        $json['newStatusName'] = Status::getStatus(
            $statusId, $this->config->get('config_language_id'));
//        $this->log->write(print_r($json, true));

        $this->response->setOutput(json_encode($json));
    }

    public function showForm()
    {
        if ($this->request->server['REQUEST_METHOD'] == 'POST')
        {
            $order_id = $this->request->request['order_id'];
            $this->data['order_items'] = array();
            foreach ($this->request->request['order_items'] as $order_item)
                if ($order_item['action'] == 'add')
                    $this->model_account_repurchase_order->addOrderItem($order_id, $order_item);
                elseif ($order_item['action'] == 'remove')
                    $this->model_account_repurchase_order->deleteOrderItem($order_item['order_item_id']);
            if (!$this->error)
                $this->getList();
        }

        $this->data['heading_title'] = $this->language->get('heading_title');
        $this->data['breadcrumbs'] = array();
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home'),
            'separator' => false
        );
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_repurchase_order'),
            'href'      => $this->url->link('account/repurchase_order', '', 'SSL'),
            'separator' => $this->language->get('text_separator')
        );
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_show_repurchase_order'),
            'href'      => $_SERVER['REQUEST_URI'] ,
            'separator' => $this->language->get('text_separator')
        );

        $this->data['entry_color'] = $this->language->get('field_color');
        $this->data['entry_fee'] = $this->language->get('field_fee');
        $this->data['entry_image_path'] = $this->language->get('field_image_path');
        $this->data['entry_item_url'] = $this->language->get('field_item_url');
        $this->data['entry_original_price'] = $this->language->get('field_original_price');
        $this->data['entry_quantity'] = $this->language->get('field_quantity');
        $this->data['entry_size'] = $this->language->get('field_size');
        $this->data['entry_subtotal'] = $this->language->get('field_subtotal');
        $this->data['entry_total'] = $this->language->get('field_total');
        $this->data['button_add'] = $this->language->get('button_add');
        $this->data['button_back'] = $this->language->get('button_back');
        $this->data['button_continue'] = $this->language->get('button_continue');
        $this->data['button_delete'] = $this->language->get('button_delete');

        if (!isset($this->request->request['order_id']))
        {
            $this->data['order_items'][] = array(
                'order_item_id'     => 'new0',
                'image_path'        => '',
                'item_url'          => '',
                'size'              => '',
                'color'             => '',
                'original_price'    => '',
                'quantity'          => '',
                'subtotal'          => '',
                'fee'               => '',
                'total'             => ''
            );
        }
        else
        {
            if (isset($this->request->request['order_items']))
                $order_items = $this->request->request['order_items'];
            else
                $order_items = $this->model_account_repurchase_order->getOrderItems($this->request->request['order_id']);
            //print_r($order_items);exit();
            $this->data['order_id'] = $this->request->request['order_id'];
            foreach ($order_items as $order_item)
            {
                $this->data['order_items'][] = array(
                    'order_item_id' => $order_item['repurchase_order_item_id'],
                    'item_url'      => $order_item['item_url'],
                    'size'          => $this->model_account_repurchase_order->getOrderItemProperty($order_item['repurchase_order_item_id'], 'size'),
                    'color'         => $this->model_account_repurchase_order->getOrderItemProperty($order_item['repurchase_order_item_id'], 'color'),
                    'original_price'    => $order_item['original_price'],
                    'quantity'          => $order_item['quantity'],
                    'subtotal'          => $order_item['subtotal'],
                    'fee'               => $order_item['fee'],
                    'total'             => $order_item['total']
                );
            }
        }

        $template_name = '/template/account/repurchaseOrderForm.tpl';
        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . $template_name)) {
            $this->template = $this->config->get('config_template') . $template_name;
        } else {
            $this->template = 'default' . $template_name;
        }

        $this->children = array(
            'common/column_right',
            'common/footer',
            'common/header'
        );

        $this->response->setOutput($this->render());
    }
}

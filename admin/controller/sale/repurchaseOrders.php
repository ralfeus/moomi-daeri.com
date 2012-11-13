<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dev
 * Date: 2.8.12
 * Time: 8:16
 * To change this template use File | Settings | File Templates.
 */
class ControllerSaleRepurchaseOrders extends Controller
{
    private $modelSaleRepurchaseOrder;

    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->data['notifications'] = array();
        $this->modelSaleRepurchaseOrder = $this->load->model('sale/repurchaseOrder');
        $this->load->language('sale/repurchaseOrders');
        $this->load->library('Status');
        $this->document->setTitle($this->language->get('HEADING_TITLE'));
        $this->data['headingTitle'] = $this->language->get('HEADING_TITLE');
    }

    private function getData() 	{
        $modelToolImage = $this->load->model('tool/image');
//        $this->log->write(print_r($this->request,true));
        $order = '';
        $urlParameters = array();

        /// Filter initialization

        if (!isset($_REQUEST['filterAmount']))
            $filterAmount = null;
        else
        {
            $filterAmount = $_REQUEST['filterAmount'];
            $urlParameters[] = "filterAmount=$filterAmount";
        }
        if (empty($_REQUEST['filterCustomerId']))
            $filterCustomerId = array();
        else
        {
            $filterCustomerId = $_REQUEST['filterCustomerId'];
            $urlParameters[] = "filterCustomerId[]=" . implode("&filterCustomerId[]=", $filterCustomerId);
        }
        if (empty($_REQUEST['filterOrderId']))
            $filterOrderId = null;
        else
        {
            $filterOrderId = $_REQUEST['filterOrderId'];
            $urlParameters[] = "filterOrderId=$filterOrderId";
        }
        if (empty($_REQUEST['filterSiteName']))
            $filterSiteName = null;
        else
        {
            $filterSiteName = $_REQUEST['filterSiteName'];
            $urlParameters[] = "filterSiteName=" . urlencode($filterSiteName);
        }
        if (empty($_REQUEST['filterStatusId']))
            $filterStatusId = array();
        else
        {
            $filterStatusId = $_REQUEST['filterStatusId'];
            $urlParameters[] = "filterStatusId[]=" . implode("&filterStatusId[]=", $filterStatusId);
        }
        if (empty($_REQUEST['filterWhoOrders']))
            $filterWhoOrders = null;
        else
        {
            $filterWhoOrders = $_REQUEST['filterWhoOrders'];
            $urlParameters[] = "filterWhoOrders=$filterWhoOrders";
        }

        if (empty($_REQUEST['sort']))
            $sort = "";
        else
        {
            $sort = $_REQUEST['sort'];
            $urlParameters[] = "sort=$sort";
        }
        if (empty($_REQUEST['order']))
            $order = '';
        else
        {
            $order = $_REQUEST['order'];
            $urlParameters['order'] = "order=$order";
        }
        if (empty($_REQUEST['page']))
            $page = 1;
        else
            $page = $_REQUEST['page'];
        $urlParameters[] = "page=$page";

        $url = '&' . implode("&", $urlParameters);

        $this->data['invoice'] = $this->url->link('sale/invoice/showForm' . $url, 'token=' . $this->session->data['token'], 'SSL');
        $this->data['print'] = $this->url->link('sale/repurchaseOrders/printPage' . $url, 'token=' . $this->session->data['token'], 'SSL');
        $this->data['orders'] = array();

        $data = array(
            'filterAmount' => $filterAmount,
            'filterCustomerId' => $filterCustomerId,
            'filterOrderId' => $filterOrderId,
            'filterSiteName' => $filterSiteName,
            'filterStatusId'=> $filterStatusId,
            'filterWhoOrders' => $filterWhoOrders,
            'sort'            => $sort,
            'order'           => $order,
            'start'           => ($page - 1) * $this->config->get('config_admin_limit'),
            'limit'           => $this->config->get('config_admin_limit')
        );
        $data = array_merge($data, $this->parameters);
//        $this->log->write(print_r($data, true));
        $order_items = $this->modelSaleRepurchaseOrder->getOrders($data);
//        $this->log->write(print_r($order_items, true));
        $showedCustomerIds = array();
        $this->data['customers'] = array();
        foreach ($order_items as $order_item)
        {
            if (!in_array($order_item['customerId'], $showedCustomerIds))
            {
                $this->data['customers'][] = array(
                    'id' => $order_item['customerId'],
                    'name' => $order_item['customerName'] . '/' . $order_item['customerNick']
                );
                $showedCustomerIds[] = $order_item['customerId'];
            }
            $actions = array();

            $actions[] = array(
                'text' => $this->language->get('GOTO_ITEM'),
                'href' => $order_item['itemUrl']
            );
            /// Check if item URL is valid
            if (preg_match('/https?:\/\/([\w\-\.]+)/', $order_item['itemUrl'], $matches))
                $siteName = $matches[1];
            else
                $siteName = 'Wrong URL format';
            /// Get image path or URL

            if (file_exists(DIR_IMAGE . $order_item['imagePath']))
            {
                $image = $modelToolImage->resize($order_item['imagePath'], 100, 100);
                $hint = '';
            }
            else
            {
                $image = $modelToolImage->resize('no_image.jpg', 100, 100);
                $hint = $this->language->get('WARNING_HTML_PAGE_PROVIDED');
            }

            $this->data['orders'][] = array(
                'comment'                   => $order_item['comment'],
                'orderId'			            => $order_item['orderItemId'],
                'hint' => $hint,
                'imagePath'	            => $image,
                'siteName'			 => $siteName,
                'customerName' => $order_item['customerName'],
                'customerNick' => $order_item['customerNick'],
                'customerUrl' => $this->url->link(
                    'sale/customer/update',
                    'token=' . $this->session->data['token'] . '&customer_id=' . $order_item['customerId'],
                    'SSL'),
                'itemUrl'	            => $order_item['itemUrl'],
                'options'       => nl2br($this->modelSaleRepurchaseOrder->getOrderOptionsString($order_item['orderItemId'])),
                'originalImagePath' => file_exists(DIR_IMAGE . $order_item['imagePath'])
                    ? HTTP_IMAGE . $order_item['imagePath']
                    : $order_item['imagePath'],
                'publicComment' => $order_item['publicComment'],
                'status'       	=> $order_item['status']
                    ? Status::getStatus(
                        $order_item['status'],
                        $this->config->get('config_language_id'))
                    : "",
                'amount' => $order_item['total'],
                'quantity'		=> $order_item['quantity'],
//                'selected'      =>
//                    (isset($_REQUEST['selectedItems'])
//                        && is_array($_REQUEST['selectedItems'])
//                        && in_array($order_item['order_product_id'], $this->parameters['selectedItems']))
//                        ? '"selected"'
//                        : '',
                'actions'                    => $actions,
                'whoOrders' => $order_item['whoOrders']
            );
        }

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

//        $urlParameters['order'] = $order == 'ASC' ? 'DESC' : 'DESC';
//        $url = implode('&', $urlParameters);
//        $this->data['sort_order_id'] = $this->url->link('sale/order_items', 'token=' .$this->session->data['token'] . '&sort=order_id' . $url, 'SSL');
//        $this->data['sort_order_item_id'] = $this->url->link('sale/order_items', 'token=' .$this->session->data['token'] . '&sort=order_item_id' . $url, 'SSL');
//        $this->data['sort_supplier'] = $this->url->link('sale/order_items', 'token=' . $this->session->data['token'] . '&sort=supplier_name' . $url, 'SSL');
//        $this->data['sort_supplier_group'] = $this->url->link('sale/order_items', 'token=' . $this->session->data['token'] . '&sort=supplier_group_id' . $url, 'SSL');

        $pagination = new Pagination();
        $pagination->total = $this->model_sale_order_item->getOrderItemsCount($data);
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_admin_limit');
        $pagination->text = $this->language->get('text_pagination');
        $pagination->url = $this->url->link('sale/order_items', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

        $this->data['pagination'] = $pagination->render();
        $this->data['filterAmount'] = $filterAmount;
        $this->data['filterCustomerId'] = $filterCustomerId;
        $this->data['filterOrderId'] = $filterOrderId;
        $this->data['filterSiteName'] = $filterSiteName;
        $this->data['filterStatusId'] = $filterStatusId;
        $this->data['filterWhoOrders'] = $filterWhoOrders;
        $this->data['sort'] = $sort;
        $this->data['order'] = $order;
        $this->data['currencyCode'] = $this->config->get('config_currency');
        $this->data['invoiceUrl'] = $this->url->link('sale/invoice/showForm', 'token=' . $this->session->data['token'], 'SSL');
//        $this->log->write(print_r($this->data['orders'], true));
    }

    public function index()
    {
        //$this->log->write(print_r($this, true));
        $this->getData();

        /// Set interface
        $this->data['text_missing'] = $this->language->get('text_missing');
        $this->data['text_no_results'] = $this->language->get('NO_ITEMS');
        $this->data['text_no_selected_items'] = $this->language->get('error_no_selected_items');

        $this->data['textActions'] = $this->language->get('ACTIONS');
        $this->data['textAmount'] = $this->language->get('AMOUNT');
        $this->data['textComment'] = $this->language->get('COMMENT');
        $this->data['textFilter'] = $this->language->get('FILTER');
        $this->data['textItemImage'] = $this->language->get('ITEM_IMAGE');
        $this->data['textOrderId'] = $this->language->get('ORDER_ID');
        $this->data['textCustomer'] = $this->language->get('CUSTOMER');
        $this->data['textStatus'] = $this->language->get('STATUS');
        $this->data['textQuantity'] = $this->language->get('QUANTITY');
        $this->data['textWeight'] = $this->language->get('WEIGHT');
        $this->data['textFilter'] = $this->language->get('FILTER');
        $this->data['textInvoice'] = $this->language->get('INVOICE');
        $this->data['textNoSelectedItems'] = $this->language->get('NO_SELECTED_ITEMS');
        $this->data['textPrint'] = $this->language->get('PRINT');
        $this->data['textSiteName'] = $this->language->get('SITE_NAME');
        $this->data['textWhoOrders'] = $this->language->get('WHO_ORDERS');

        $this->setBreadcrumbs();
        $this->initStatuses();
        $this->template = 'sale/repurchaseOrdersList.tpl';
        $this->children = array(
            'common/header',
            'common/footer'
        );
        $this->response->setOutput($this->render());
    }

    protected function initParameters()
    {
        $this->parameters['orderId'] = empty($_REQUEST['orderId']) ? null : $_REQUEST['orderId'];
        if (!empty($_REQUEST['propName']) && in_array($_REQUEST['propName'], array('amount', 'quantity')))
            $this->parameters['propName'] = $_REQUEST['propName'];
        else
            $this->parameters['propName'] = null;
        $this->parameters['selectedItems'] = empty($_REQUEST['selectedItems']) ? array() : $_REQUEST['selectedItems'];
        $this->parameters['value'] = !empty($_REQUEST['value']) && is_numeric($_REQUEST['value']) ? $_REQUEST['value'] : null;
    }

    private function initStatuses()
    {
        $this->data['statuses'] = array();
        foreach (Status::getStatuses(
                     GROUP_REPURCHASE_ORDER_ITEM_STATUS,
                     $this->config->get('config_language_id')) as $order_item_status)
            $this->data['statuses'][] = array(
                'statusId'    => $order_item_status['status_id'],
                'name' => $order_item_status['name']
            );
//        $this->log->write(print_r($this->data['statuses'], true));
    }

    public function printPage()
    {
        $this->getData();

        /// Set interface
        $this->data['textAmount'] = $this->language->get('AMOUNT');
        $this->data['textComment'] = $this->language->get('COMMENT');
        $this->data['textItemImage'] = $this->language->get('ITEM_IMAGE');
        $this->data['textOrderId'] = $this->language->get('ORDER_ID');
        $this->data['textCustomer'] = $this->language->get('CUSTOMER');
        $this->data['textStatus'] = $this->language->get('STATUS');
        $this->data['textQuantity'] = $this->language->get('QUANTITY');
        $this->data['textWeight'] = $this->language->get('WEIGHT');
        $this->data['textFilter'] = $this->language->get('FILTER');
        $this->data['textInvoice'] = $this->language->get('INVOICE');
        $this->data['textSiteName'] = $this->language->get('SITE_NAME');
        $this->data['textWhoOrders'] = $this->language->get('WHO_ORDERS');

        $this->template = 'sale/repurchaseOrdersListPrint.tpl';
        $this->response->setOutput($this->render());
    }

    public function setProperty()
    {
        if (empty($this->parameters['orderId']))
            return;
        if (empty($this->parameters['value']))
            return;
        if (empty($this->parameters['propName']))
            return;
        switch ($this->parameters['propName'])
        {
            case 'amount':
                $this->modelSaleRepurchaseOrder->setAmount($this->parameters['orderId'], $this->parameters['value']);
                break;
            case 'quantity':
                $this->modelSaleRepurchaseOrder->setQuantity($this->parameters['orderId'], $this->parameters['value']);
                break;
        }
        $json['result'] = 'Done';
        $this->log->write(print_r($json, true));
        $this->response->setOutput(json_encode($json));
    }

    private function setBreadcrumbs()
    {
        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false
        );
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('HEADING_TITLE'),
            'href'      => $this->url->link('sale/repurchaseOrders', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );
    }

    public function setStatus()
    {
        if (empty($_REQUEST['statusId']))
            return;

        foreach ($this->parameters['selectedItems'] as $orderId)
            $this->modelSaleRepurchaseOrder->setStatus($orderId, $_REQUEST['statusId']);

        $json['newStatusName'] = Status::getStatus(
            $_REQUEST['statusId'], $this->config->get('config_language_id'));
//        $this->log->write(print_r($json, true));

        $this->response->setOutput(json_encode($json));
    }
}

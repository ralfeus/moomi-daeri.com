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
        $urlParameters = $this->buildUrlParameterString($this->parameters);

        $this->data['invoice'] = $this->url->link('sale/invoice/showForm' . $urlParameters, 'token=' . $this->session->data['token'], 'SSL');
        $this->data['print'] = $this->url->link('sale/repurchaseOrders/printPage' . $urlParameters, 'token=' . $this->session->data['token'], 'SSL');
        $this->data['orders'] = array();

        $data = array(
            'start'           => ($this->parameters['page'] - 1) * $this->config->get('config_admin_limit'),
            'limit'           => $this->config->get('config_admin_limit')
        );
        $data = array_merge($data, $this->parameters);

        $order_items = $this->modelSaleRepurchaseOrder->getOrders($data);
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
            $actions[] = array(
                'text' => $this->language->get('CHANGE_PICTURE'),
                'href' => '',
                'onclick' => 'imageManager(' . $order_item['orderItemId'] . ', $(this).parent().parent().find(\'img\'))'
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
                'underlyingOrderId' => $order_item['orderId'],
                'hint' => $hint,
                'imagePath'	            => $image,
                'itemName' => $order_item['itemName'],
                'itemUrl'	            => $order_item['itemUrl'],
                'shopName' => $order_item['shopName'],
                'siteName'			 => $siteName,
                'customerName' => $order_item['customerName'],
                'customerNick' => $order_item['customerNick'],
                'customerUrl' => $this->url->link(
                    'sale/customer/update',
                    'token=' . $this->session->data['token'] . '&customer_id=' . $order_item['customerId'],
                    'SSL'),
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
                'quantity'		=> $order_item['quantity'],
                'price' => $order_item['price'],
                'shipping' => $order_item['shipping'],
                'amount' => $order_item['total'],
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

        $pagination = new Pagination();
        $pagination->total = $this->model_sale_order_item->getOrderItemsCount($data);
        $pagination->page = $this->parameters['page'];
        $pagination->limit = $this->config->get('config_admin_limit');
        $pagination->text = $this->language->get('text_pagination');
        $pagination->url = $this->url->link('sale/order_items', 'token=' . $urlParameters . '&page={page}', 'SSL');

        $this->data['pagination'] = $pagination->render();
        $this->data['currencyCode'] = $this->config->get('config_currency');
        $this->data['invoiceUrl'] = $this->url->link('sale/invoice/showForm', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['urlImageChange'] = $this->url->link('sale/repurchaseOrders/setProperty', 'propName=image&token=' . $this->parameters['token'], 'SSL');
        $this->data['urlImageManager'] = $this->url->link('common/filemanager', 'field=image&token=' . $this->parameters['token'], 'SSL');
        $this->data = array_merge($this->data, $this->parameters);
    }

    public function index()
    {
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
        $this->data['textUnderlyingOrderId'] = $this->language->get('UNDERLYING_ORDER_ID');
        $this->data['textCustomer'] = $this->language->get('CUSTOMER');
        $this->data['textStatus'] = $this->language->get('STATUS');
        $this->data['textQuantity'] = $this->language->get('QUANTITY');
        $this->data['textPricePerItem'] = $this->language->get('PricePerItem');
        $this->data['textShipping'] = $this->language->get('Shipping');
        $this->data['textWeight'] = $this->language->get('WEIGHT');
        $this->data['textFilter'] = $this->language->get('FILTER');
        $this->data['textInvoice'] = $this->language->get('INVOICE');
        $this->data['textNoSelectedItems'] = $this->language->get('NO_SELECTED_ITEMS');
        $this->data['textPrint'] = $this->language->get('PRINT');
        $this->data['textShop'] = $this->language->get('SHOP_NAME') . '/' . $this->language->get('SITE_NAME');
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
        $this->parameters['filterAmount'] = !isset($_REQUEST['filterAmount']) ? null : $_REQUEST['filterAmount'];
        $this->parameters['filterCustomerId'] = empty($_REQUEST['filterCustomerId']) ? array() : $_REQUEST['filterCustomerId'];
        $this->parameters['filterOrderId'] = empty($_REQUEST['filterOrderId']) ? null : $_REQUEST['filterOrderId'];
        $this->parameters['filterShopName'] = empty($_REQUEST['filterShopName']) ? null : $_REQUEST['filterShopName'];
        $this->parameters['filterSiteName'] = empty($_REQUEST['filterSiteName']) ? null : $_REQUEST['filterSiteName'];
        $this->parameters['filterStatusId'] = empty($_REQUEST['filterStatusId']) ? array() : $_REQUEST['filterStatusId'];
        $this->parameters['filterWhoOrders'] = empty($_REQUEST['filterWhoOrders']) ? null : $_REQUEST['filterWhoOrders'];
        $filterSet = false;
        foreach ($this->parameters as $parameter) {
            if (!empty($parameter)) {
                $filterSet = true;
                break;
            }
        }
        if (!$filterSet && ($_SERVER['REQUEST_METHOD'] == "GET"))
            $this->parameters['filterStatusId'] = array(REPURCHASE_ORDER_ITEM_STATUS_WAITING);

        $this->parameters['order'] = empty($_REQUEST['order']) ? '' : $_REQUEST['order'];
        $this->parameters['orderId'] = empty($_REQUEST['orderId']) ? null : $_REQUEST['orderId'];
        $this->parameters['page'] = empty($_REQUEST['page']) ? 1 : $_REQUEST['page'];
        if (!empty($_REQUEST['propName']) && in_array($_REQUEST['propName'], array('amount', 'image', 'quantity')))
            $this->parameters['propName'] = $_REQUEST['propName'];
        else
            $this->parameters['propName'] = null;
        $this->parameters['selectedItems'] = empty($_REQUEST['selectedItems']) ? array() : $_REQUEST['selectedItems'];
        $this->parameters['sort'] = empty($_REQUEST['sort']) ? '' : $_REQUEST['sort'];
        $this->parameters['token'] = $this->session->data['token'];
        $this->parameters['value'] =
            !empty($_REQUEST['value']) && $this->isValidPropValue($this->parameters['propName'], $_REQUEST['value'])
                ? $_REQUEST['value'] : null;
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
    }

    private function isValidPropValue($propName, $propValue)
    {
        switch ($propName)
        {
            case 'amount':
            case 'quantity':
                return is_numeric($propValue);
            case 'image':
                return exif_imagetype(DIR_IMAGE . $propValue);
        }
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
        $this->log->write(print_r($_GET, true));
        $this->parameters = $_GET;
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
            case 'image':
                $this->modelSaleRepurchaseOrder->setImage($this->parameters['orderId'], $this->parameters['value']);
                $json['image'] = $this->load->model('tool/image')->resize($this->parameters['value'], 100, 100);
                break;
            case 'quantity':
                $this->modelSaleRepurchaseOrder->setQuantity($this->parameters['orderId'], $this->parameters['value']);
                break;
            case 'price':
                $this->modelSaleRepurchaseOrder->setPrice($this->parameters['orderId'], $this->parameters['value']);
                break;
            case 'shipping':
                $this->modelSaleRepurchaseOrder->setShipping($this->parameters['orderId'], $this->parameters['value']);
                break;
        }
        $rows = $this->modelSaleRepurchaseOrder->getPrices($this->parameters['orderId']);
        $json['itemId'] = $rows[0]['order_product_id'];
        $json['price'] = $rows[0]['price'];
        $json['total'] = $rows[0]['total'];
        $json['result'] = 'Done';
        $this->log->write(print_r($json, true));
        $this->response->setOutput(json_encode($json));
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

        $this->response->setOutput(json_encode($json));
    }
}

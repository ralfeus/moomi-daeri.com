<?php
use model\sale\RepurchaseOrderDAO;
use system\engine\Controller;

/**
 * Created by JetBrains PhpStorm.
 * User: dev
 * Date: 2.8.12
 * Time: 8:16
 * To change this template use File | Settings | File Templates.
 */
class ControllerSaleRepurchaseOrders extends \system\engine\Controller {
    public function __construct($registry) {
        parent::__construct($registry);
        $this->data['notifications'] = array();
        $this->load->language('sale/repurchaseOrders');
        //$this->load->library('Status');
        $this->document->setTitle($this->getLanguage()->get('HEADING_TITLE'));
        $this->data['headingTitle'] = $this->getLanguage()->get('HEADING_TITLE');
    }

    private function getData() 	{
        $modelToolImage = $modelToolImage = new \catalog\model\tool\ModelToolImage($this->getRegistry());
        $urlParameters = $this->buildUrlParameterString($this->parameters);

        $this->data['invoice'] = $this->url->link('sale/invoice/showForm', $urlParameters, 'SSL');
        $this->data['print'] = $this->url->link('sale/repurchaseOrders/printPage', $urlParameters, 'SSL');
        $this->data['orders'] = array();

        $data = array(
            'start'           => ($this->parameters['page'] - 1) * $this->config->get('config_admin_limit'),
            'limit'           => $this->config->get('config_admin_limit')
        );
        $data = array_merge($data, $this->parameters);

        $order_items = RepurchaseOrderDAO::getInstance()->getOrders($data);
        $showedCustomerIds = array();
        $this->data['customers'] = array();
        foreach ($order_items as $order_item) {
            if (!in_array($order_item['customerId'], $showedCustomerIds)) {
                $this->data['customers'][] = array(
                    'id' => $order_item['customerId'],
                    'name' => $order_item['customerName'] . '/' . $order_item['customerNick']
                );
                $showedCustomerIds[] = $order_item['customerId'];
            }
            $actions = array();

            $actions[] = array(
                'text' => $this->getLanguage()->get('GOTO_ITEM'),
                'href' => $order_item['itemUrl']
            );
            $actions[] = array(
                'text' => $this->getLanguage()->get('CHANGE_PICTURE'),
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
                $hint = $this->getLanguage()->get('WARNING_HTML_PAGE_PROVIDED');
            }

            $this->data['orders'][] = array(
                'privateComment'                   => $order_item['privateComment'],
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
                'options'       => nl2br(RepurchaseOrderDAO::getInstance()->getOptionsString($order_item['orderItemId'])),
                'originalImagePath' => file_exists(DIR_IMAGE . $order_item['imagePath'])
                    ? HTTP_IMAGE . $order_item['imagePath']
                    : $order_item['imagePath'],
                'comment' => $order_item['comment'],
                'publicComment' => $order_item['comment'],
                'status'       	=> $order_item['status']
                    ? Status::getStatus(
                        $order_item['status'],
                        $this->config->get('config_language_id'))
                    : "",
                'quantity'		=> $order_item['quantity'],
                'price' => (float)$order_item['price'],
                'whiteprice' => (float)$order_item['whiteprice'],
                'shipping' => (float)$order_item['shipping'],
                'amount' => (float)$order_item['total'],
                'actions'                    => $actions,
                'whoOrders' => $order_item['whoOrders']
            );
        }

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
        $pagination->total = RepurchaseOrderDAO::getInstance()->getOrdersCount($data);
        $pagination->page = $this->parameters['page'];
        $pagination->limit = $this->config->get('config_admin_limit');
        $pagination->text = $this->getLanguage()->get('text_pagination');
        $pagination->url = $this->url->link('sale/repurchaseOrders', $urlParameters . '&page={page}', 'SSL');

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
        $this->data['text_missing'] = $this->getLanguage()->get('text_missing');
        $this->data['text_no_results'] = $this->getLanguage()->get('NO_ITEMS');
        $this->data['text_no_selected_items'] = $this->getLanguage()->get('error_no_selected_items');

        $this->data['textActions'] = $this->getLanguage()->get('ACTIONS');
        $this->data['textAmount'] = $this->getLanguage()->get('AMOUNT');
        $this->data['textComment'] = $this->getLanguage()->get('COMMENT');
        $this->data['textFilter'] = $this->getLanguage()->get('FILTER');
        $this->data['textItem'] = $this->getLanguage()->get('ITEM');
        $this->data['textOrderId'] = $this->getLanguage()->get('ORDER_ID');
        $this->data['textUnderlyingOrderId'] = $this->getLanguage()->get('UNDERLYING_ORDER_ID');
        $this->data['textCustomer'] = $this->getLanguage()->get('CUSTOMER');
        $this->data['textStatus'] = $this->getLanguage()->get('STATUS');
        $this->data['textQuantity'] = $this->getLanguage()->get('QUANTITY');
        $this->data['textPricePerItem'] = $this->getLanguage()->get('PricePerItem');
        $this->data['textRecalculateShipping'] = $this->getLanguage()->get('RECALCULATE_SHIPPING');
        $this->data['textShipping'] = $this->getLanguage()->get('Shipping');
        $this->data['textWeight'] = $this->getLanguage()->get('WEIGHT');
        $this->data['textFilter'] = $this->getLanguage()->get('FILTER');
        $this->data['textInvoice'] = $this->getLanguage()->get('INVOICE');
        $this->data['textNoSelectedItems'] = $this->getLanguage()->get('NO_SELECTED_ITEMS');
        $this->data['textPrint'] = $this->getLanguage()->get('PRINT');
        $this->data['textShopName'] = $this->getLanguage()->get('SHOP_NAME');
        $this->data['textSiteName'] = $this->getLanguage()->get('SITE_NAME');
        $this->data['textWhoOrders'] = $this->getLanguage()->get('WHO_ORDERS');

        $this->setBreadcrumbs();
        $this->initStatuses();
        $this->template = 'sale/repurchaseOrdersList.tpl.php';
        $this->children = array(
            'common/header',
            'common/footer'
        );
        $this->getResponse()->setOutput($this->render());
    }

    protected function initParameters()
    {
        $this->parameters['filterAmount'] = is_numeric($this->getRequest()->getParam('filterAmount')) ? $this->getRequest()->getParam('filterAmount') : null;
        $this->parameters['filterCustomerId'] = empty($_REQUEST['filterCustomerId']) ? array() : $_REQUEST['filterCustomerId'];
        $this->parameters['filterItemName'] = empty($_REQUEST['filterItemName']) ? null : $_REQUEST['filterItemName'];
        $this->parameters['filterOrderId'] = empty($_REQUEST['filterOrderId']) ? null : $_REQUEST['filterOrderId'];
        $this->parameters['filterShopName'] = empty($_REQUEST['filterShopName']) ? null : $_REQUEST['filterShopName'];
        $this->parameters['filterSiteName'] = empty($_REQUEST['filterSiteName']) ? null : $_REQUEST['filterSiteName'];
        if (empty($_REQUEST['filterStatusSetDate'])) {
            $this->parameters['filterStatusId'] = empty($_REQUEST['filterStatusId']) ? array() : $_REQUEST['filterStatusId'];
            $this->parameters['filterStatusIdDateSet'] = null;
            $this->parameters['filterStatusSetDate'] = null;
        } else {
            $this->parameters['filterStatusId'] = array();
            $this->parameters['filterStatusIdDateSet'] = empty($_REQUEST['filterStatusId']) ? array() : $_REQUEST['filterStatusId'];
            $this->parameters['filterStatusSetDate'] = empty($_REQUEST['filterStatusId']) ? null : $_REQUEST['filterStatusSetDate'];
        }
//        $this->parameters['filterWhoOrders'] = empty($_REQUEST['filterWhoOrders']) ? null : $_REQUEST['filterWhoOrders'];
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
//        if (!empty($_REQUEST['propName']) && in_array($_REQUEST['propName'], array('amount', 'image', 'quantity')))
//            $this->parameters['propName'] = $_REQUEST['propName'];
//        else
//            $this->parameters['propName'] = null;
        $this->parameters['propName'] = empty($_REQUEST['propName']) ? null : $_REQUEST['propName'];
        $this->parameters['selectedItems'] = empty($_REQUEST['selectedItems']) ? array() : $_REQUEST['selectedItems'];
        $this->parameters['sort'] = empty($_REQUEST['sort']) ? '' : $_REQUEST['sort'];
        $this->parameters['token'] = $this->session->data['token'];
        $this->parameters['value'] =
            isset($_REQUEST['value']) && $this->isValidPropValue($this->parameters['propName'], $_REQUEST['value'])
                ? $_REQUEST['value'] : null;
    }

    private function initStatuses()
    {
        $this->data['statuses'] = array();
        foreach (Status::getStatuses(
                     GROUP_REPURCHASE_ORDER_ITEM_STATUS,
                     $this->config->get('config_language_id')) as $statusId => $status)
            $this->data['statuses'][] = array(
                'statusId'    => $statusId,
                'name' => $status
            );
    }

    /**
     * @param $propName
     * @param $propValue
     * @return bool
     */
    private function isValidPropValue($propName, $propValue) {
        switch ($propName) {
            case 'amount':
            case 'quantity':
            case 'price':
            case 'whiteprice':
            case 'shipping':
                return is_numeric($propValue);
            case 'image':
                return exif_imagetype(DIR_IMAGE . $propValue);
            case 'itemName':
            case 'shopName':
                return true;
        }
        return false;
    }

    public function printPage() {
        $this->getData();

        /// Set interface
        $this->data['textAmount'] = $this->getLanguage()->get('AMOUNT');
        $this->data['textComment'] = $this->getLanguage()->get('COMMENT');
        $this->data['textItemImage'] = $this->getLanguage()->get('ITEM_IMAGE');
        $this->data['textOrderId'] = $this->getLanguage()->get('ORDER_ID');
        $this->data['textCustomer'] = $this->getLanguage()->get('CUSTOMER');
        $this->data['textStatus'] = $this->getLanguage()->get('STATUS');
        $this->data['textQuantity'] = $this->getLanguage()->get('QUANTITY');
        $this->data['textWeight'] = $this->getLanguage()->get('WEIGHT');
        $this->data['textFilter'] = $this->getLanguage()->get('FILTER');
        $this->data['textInvoice'] = $this->getLanguage()->get('INVOICE');
        $this->data['textSiteName'] = $this->getLanguage()->get('SITE_NAME');
        $this->data['textWhoOrders'] = $this->getLanguage()->get('WHO_ORDERS');

        $this->template = 'sale/repurchaseOrdersListPrint.tpl.php';
        $this->getResponse()->setOutput($this->render());
    }

    public function recalculateShipping() {
        $selectedOrdersShops = array();
        $json = array();
        foreach ($this->parameters['selectedItems'] as $selectedItemId) {
            $repurchaseOrder = RepurchaseOrderDAO::getInstance()->getOrder($selectedItemId);
            if (in_array($repurchaseOrder['shopName'], $selectedOrdersShops)) {
                RepurchaseOrderDAO::getInstance()->setShipping($selectedItemId, 0);
                $newPrices = RepurchaseOrderDAO::getInstance()->getPrices($selectedItemId);
                $newPrices['itemId'] = $selectedItemId;
                $json[] = $newPrices;
            }
            else {
                $selectedOrdersShops[] = $repurchaseOrder['shopName'];
            }
        }
        $this->getResponse()->setOutput(json_encode($json));
    }

    public function setProperty() {
//        $this->log->write(print_r($_GET, true));
        switch ($this->parameters['propName']) {
            case 'amount':
                RepurchaseOrderDAO::getInstance()->setAmount($this->parameters['orderId'], $this->parameters['value']);
                break;
            case 'image':
                RepurchaseOrderDAO::getInstance()->setImage($this->parameters['orderId'], $this->parameters['value']);
                $json['image'] = $this->load->model('tool/image')->resize($this->parameters['value'], 100, 100);
                break;
            case 'itemName':
                RepurchaseOrderDAO::getInstance()->setItemName($this->parameters['orderId'], $this->parameters['value']);
                break;
            case 'quantity':
                RepurchaseOrderDAO::getInstance()->setQuantity($this->parameters['orderId'], $this->parameters['value']);
                break;
            case 'price':
                RepurchaseOrderDAO::getInstance()->setPrice($this->parameters['orderId'], $this->parameters['value']);
                break;
            case 'whiteprice':
                RepurchaseOrderDAO::getInstance()->setWhitePrice($this->parameters['orderId'], $this->parameters['value']);
                break;
            case 'shipping':
                RepurchaseOrderDAO::getInstance()->setShipping($this->parameters['orderId'], $this->parameters['value']);
                break;
            case 'shopName':
                RepurchaseOrderDAO::getInstance()->setShopName($this->parameters['orderId'], $this->parameters['value']);
                break;
        }
        $row = RepurchaseOrderDAO::getInstance()->getPrices($this->parameters['orderId']);
        $json['itemId'] = $row['order_product_id'];
        $json['price'] = $row['price'];
        $json['whiteprice'] = $row['whiteprice'];
        $json['total'] = $row['total'];
        $json['result'] = 'Done';
//        $this->log->write(print_r($json, true));
        $this->getResponse()->setOutput(json_encode($json));
    }

    protected function setBreadcrumbs()
    {
        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->getLanguage()->get('text_home'),
            'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false
        );
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->getLanguage()->get('HEADING_TITLE'),
            'href'      => $this->url->link('sale/repurchaseOrders', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );
    }

    public function setStatus() {
        if (empty($_REQUEST['statusId'])) {
            return;
        }
        /** @var ModelSaleOrder $modelSaleOrder */
        $modelSaleOrder = $this->load->model('sale/order');
        foreach ($this->parameters['selectedItems'] as $orderId) {
            RepurchaseOrderDAO::getInstance()->setStatus($orderId, $_REQUEST['statusId']);
            $repurchaseOrder = RepurchaseOrderDAO::getInstance()->getOrder($orderId);
            $modelSaleOrder->verifyOrderCompletion($repurchaseOrder['orderId']);
        }

        $json['newStatusName'] = Status::getStatus(
            $_REQUEST['statusId'], $this->config->get('config_language_id'));

        $this->getResponse()->setOutput(json_encode($json));
    }
}

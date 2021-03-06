<?php
use model\sale\CustomerDAO;
use model\sale\InvoiceDAO;
use model\sale\OrderDAO;
use model\sale\OrderItem;
use model\sale\OrderItemDAO;
use model\sale\TransactionDAO;
use model\shipping\ShippingMethodDAO;
use system\engine\AdminController;
use system\helper\ImageService;

/**
 * Created by JetBrains PhpStorm.
 * User: dev
 * Date: 18.7.12
 * Time: 21:43
 * To change this template use File | Settings | File Templates.
 */
class ControllerSaleInvoice extends AdminController {
    /** @var ModelReferenceAddress */
    private $modelReferenceAddress;

    public function __construct($registry) {
        parent::__construct($registry);
        $this->getLoader()->language('sale/invoice');
        //$this->getLoader()->library("Transaction");
        $this->modelReferenceAddress = $this->getLoader()->model('reference/address');

        $this->data['notifications'] = array();
        $this->document->setTitle($this->language->get('headingTitle'));
        $this->data['headingTitle'] = $this->language->get('headingTitle');
    }

    /**
     * @param OrderItem[] $orderItems
     * @return bool
     */
    private function checkAddresses($orderItems) {
        $firstAddress = "";
        foreach ($orderItems as $orderItem)
        {
            $order = OrderDAO::getInstance()->getOrder($orderItem->getId());
            if (!$firstAddress)
                $firstAddress = $this->getOrderAddressString($order);
            else
                if ($this->getOrderAddressString($order) != $firstAddress)
                    return false;
        }
        return true;
    }

    protected function loadStrings() {
        $this->data['textAction'] = $this->language->get('textAction');
        $this->data['textCustomer'] = $this->language->get('textCustomer');
        $this->data['textInvoiceId'] = $this->language->get('textInvoiceId');
        $this->data['textFilter'] = $this->language->get('FILTER');
        $this->data['textShippingCost'] = $this->language->get('textShippingCost');
        $this->data['textShippingMethod'] = $this->language->get('textShippingMethod');
        $this->data['textStatus'] = $this->language->get('STATUS');
        $this->data['textInvoceDate'] = $this->language->get('textInvoceDate');
        $this->data['textPackage'] = $this->language->get('textPackage');
        $this->data['textShippingDate'] = $this->language->get('textShippingDate');
        $this->data['textSubtotal'] = $this->language->get('textSubtotal');
        $this->data['textTotal'] = $this->language->get('textTotal');
        $this->data['textTotalCustomerCurrency'] = $this->language->get('TOTAL_CUSTOMER_CURRENCY');
        $this->data['textWeight'] = $this->language->get('textWeight');
    }

    public function create() {
        $comment = isset($_REQUEST['comment']) ? $_REQUEST['comment'] : '';
        if (!is_numeric($_REQUEST['discount'])) {
            $this->data['notifications']['error'] = $this->language->get("ERROR_INVALID_DISCOUNT_VALUE");
            $this->showCreateForm();
            return;
        } else {
            $discount = $_REQUEST['discount'];
        }
        if (!isset($this->request->request['selectedItems']))
            return;
//        $total = isset($_REQUEST['total']) ? $_REQUEST['total'] : 0;
        if (!is_numeric($this->request->request['totalWeight'])) {
            $this->data['notifications']['error'] = $this->language->get("error_invalid_weight_value");
            $this->showCreateForm();
            return;
        } else {
            $totalWeight = $_REQUEST['totalWeight'];
        }
        $orderItems = OrderItemDAO::getInstance()->getOrderItems(
            array('selected_items' => $_REQUEST['selectedItems']), null, true);
        $newInvoiceId = InvoiceDAO::getInstance()->addInvoice(
            $orderItems[0]->getOrderId(),
            $orderItems,
            $this->parameters['shippingMethod'],
            $totalWeight,
            $discount,
            $comment,
            $_POST['shippingDate']
        );
        $this->handleCredit($newInvoiceId);
        $this->setOrderItemsStatusPacked($orderItems);
        $this->data['notifications']['success'] = sprintf($this->language->get("SUCCESS_INVOICE_CREATED"), $newInvoiceId);
        unset($this->data['notifications']['error']);
        unset($this->data['notifications']['warning']);
        $this->index();
    }

    public function delete() {
        if (!empty($this->parameters['invoiceId'])) {
            $relatedTransaction = TransactionDAO::getInstance()->getTransactionByInvoiceId($this->parameters['invoiceId']);
            if (!empty($relatedTransaction))
                TransactionDAO::getInstance()->deleteTransaction($relatedTransaction['customer_transaction_id']);
            InvoiceDAO::getInstance()->deleteInvoice($this->request->request['invoiceId']);
            $this->data['notifications']['success'] = sprintf(
                $this->language->get('SUCCESS_INVOICE_DELETED'), $this->request->request['invoiceId']);
            $this->index();
        }
    }

    private function getCustomers() {
        $customers = InvoiceDAO::getInstance()->getInvoiceCustomers($this->parameters);
        foreach ($customers as $customer) {
            $tmpResult[$customer['customer_id']] =
                $customer['lastname'] . " " .
                $customer['firstname'] . ' / ' .
                $customer['nickname'];
        }
        natcasesort($tmpResult);
        return $tmpResult;
    }

    private function getData() {
        $this->data['customers'] = $this->getCustomers();
        $data = $this->parameters;

        foreach (InvoiceDAO::getInstance()->getInvoices($data, null, ($this->parameters['page'] - 1) * $this->parameters['limit'], $this->parameters['limit']) as $invoice) {
            $action = array();
            $action[] = array(
                'text' => $this->getLanguage()->get('VIEW'),
                'href' => $this->getUrl()->link('sale/invoice/showForm', 'invoiceId=' . $invoice->getId() . '&token=' . $this->session->data['token'], 'SSL')
            );
            if (!TransactionDAO::getInstance()->getTransactionByInvoiceId($invoice->getId()))
                $action[] = array(
                    'text' => $this->getLanguage()->get('DELETE'),
                    'href' => $this->getUrl()->link('sale/invoice/delete', 'invoiceId=' . $invoice->getId() . '&token=' . $this->session->data['token'], 'SSL')
                );
            else
                $action[] = array(
                    'text' => $this->getLanguage()->get('DELETE'),
                    'onclick' => "confirmDeletion('" .
                        $this->getUrl()->link(
                            'sale/invoice/delete',
                            'invoiceId=' . $invoice->getId() . '&token=' . $this->session->data['token'], 'SSL') .
                        "')"
                );

            $arrTemp = explode(" ", $invoice->getTimeModified());
            $invoiceDate = $arrTemp[0];
            $temp = $invoice->getCustomer();
            $this->data['invoices'][] = array(
                'invoiceId' => $invoice->getId(),
                'action' => $action,
                'customer' => $temp['lastname'] . ' ' . $temp['firstname'],
                'customerId' => $temp['customer_id'],
                'shippingCost' => $this->getCurrentCurrency()->format($invoice->getShippingCost(), $this->getConfig()->get('config_currency')),
                'shippingMethod' => ShippingMethodDAO::getInstance()->getMethod(explode('.', $invoice->getShippingMethod())[0])->
                    getName(),
                'status' => $this->getLoader()->model('localisation/invoice')->getInvoiceStatus($invoice->getStatusId()),
                'subtotal' => $this->getCurrentCurrency()->format($invoice->getSubtotal(), $this->getConfig()->get('config_currency')),
                'total' => $this->getCurrentCurrency()->format($invoice->getTotal(), $this->getConfig()->get('config_currency')),
                'totalCustomerCurrency' => $this->getCurrentCurrency()->format($invoice->getTotalCustomerCurrency(), $invoice->getCurrencyCode(), 1),
                'weight' => $invoice->getWeight(),
                'date' => $invoiceDate,
                'package_number' => $invoice->getPackageNumber(),
                'shipping_date' => $invoice->getShippingDate()
            );
        }
        $this->data = array_merge($this->data, $this->parameters);
//        $this->log->write(print_r($this->parameters, true));
    }

    private function getOrderAddress($order) {
//        $this->log->write(print_r($order, true));
        if (isset($order['address_id']))
            return $this->modelReferenceAddress->getAddress($order['address_id']);
        else
            return
                array(
                    'lastname' => $order['shipping_lastname'],
                    'firstname' => $order['shipping_firstname'],
                    'company' => $order['shipping_company'],
                    'address_1' => $order['shipping_address_1'],
                    'address_2' => $order['shipping_address_2'],
                    'city' => $order['shipping_city'],
                    'postcode' => $order['shipping_postcode'],
                    'zone_id' => $order['shipping_zone_id'],
                    'country_id' => $order['shipping_country_id']
                );
    }

    private function getOrderAddressString($order)
    {
//        $this->log->write(print_r($order, true));
        return $this->modelReferenceAddress->toString($this->getOrderAddress($order));
    }

    /**
     * @param OrderItem[] $orderItems
     * @return string
     */
    private function getOrderItemsHavingInvoice($orderItems) {
        $result = "";
        foreach ($orderItems as $orderItem) {
            $existingInvoices = InvoiceDAO::getInstance()->getInvoicesByOrderItem($orderItem->getId());
            if ($existingInvoices) {
                $result .= $orderItem->getId() . " ==> ";
                foreach ($existingInvoices as $existingInvoice)
                    $result .= $existingInvoice->getId() . ",";
                $result = rtrim($result, ',') . "\n";
            }
        }
        return rtrim($result, "\n");
    }

    public function getShippingCost() {
//        $this->log->write(print_r($this->parameters, true));
        $orderItems = OrderItemDAO::getInstance()->getOrderItems(array('filterOrderItemId' => $this->parameters['orderItemId']), null, true);
        $shippingMethod = explode('.', $this->parameters['method']);
        $cost = ShippingMethodDAO::getInstance()->getMethod($shippingMethod[0])->getCost(
            $shippingMethod[1],
            $orderItems,
            array('weight' => $this->parameters['weight'])
        );
        $json = [
            'cost' => $cost
        ];
        $this->getResponse()->setOutput(json_encode($json));
    }

    private function handleCredit($invoiceId) {
//        $modelTransaction = $this->getLoader()->model('sale/transaction');
        $invoice = InvoiceDAO::getInstance()->getInvoice($invoiceId);
//        $customer = CustomerDAO::getInstance()->getCustomer($invoice['customer_id']);
        $temp = $invoice->getCustomer();
        if ($temp['await_invoice_confirmation']) {
            InvoiceDAO::getInstance()->setInvoiceStatus($invoiceId, IS_AWAITING_CUSTOMER_CONFIRMATION);
        } else {
            $totalToPay = $this->getCurrentCurrency()->convert(
                $invoice->getTotalCustomerCurrency(),
                $invoice->getCurrencyCode(),
                $temp['base_currency_code']);
            if ($temp['balance'] < $totalToPay)
                if ($temp['allow_overdraft'])
                {
                    TransactionDAO::getInstance()->addPayment($temp['customer_id'], $invoiceId);
                    InvoiceDAO::getInstance()->setInvoiceStatus($invoiceId, IS_PAID);
                }
                else
                    InvoiceDAO::getInstance()->setInvoiceStatus($invoiceId, IS_AWAITING_PAYMENT);
            else
            {
                TransactionDAO::getInstance()->addPayment($temp['customer_id'], $invoiceId);
                InvoiceDAO::getInstance()->setInvoiceStatus($invoiceId, IS_PAID);
            }
        }
        $this->getLoader()->model('tool/communication')->sendMessage(
            $temp['customer_id'],
            sprintf(
                $this->getLanguage()->get('INVOICE_STATUS_NOTIFICATION'),
                $this->getLoader()->model('localisation/invoice')->getInvoiceStatus($invoiceId),
                $this->getCurrentCurrency()->format($invoice->getTotalCustomerCurrency(), $invoice->getCurrencyCode(), 1),
                $this->getCurrentCurrency()->format(
                    CustomerDAO::getInstance()->getCustomerBalance($temp['customer_id']),
                    $temp['base_currency_code'],
                    1)
            ),
            SYS_MSG_INVOICE_CREATED
        );
    }

    public function index()
    {
        $this->getData();
        $this->setBreadcrumbs();

        $pagination = new Pagination();
        $pagination->total = InvoiceDAO::getInstance()->getInvoicesCount($this->parameters);
        $pagination->page = $this->parameters['page'];
        $pagination->limit = $this->getConfig()->get('config_admin_limit');
        $pagination->text = $this->language->get('text_pagination');
        //        unset($this->parameters['page']);
        $pagination->url = $this->getUrl()->link('sale/invoice', 'token=' . $this->getSession()->data['token'] . '&page={page}', 'SSL');
        $this->data['pagination'] = $pagination->render();

        $this->children = array(
            'common/header',
            'common/footer'
        );
        $this->getResponse()->setOutput($this->render('sale/invoiceList.tpl.php'));
    }

    protected function initParameters() {
        $this->initParametersWithDefaults([
            'data' => null,
            'filterCustomerId' => [],
            'invoiceId' => null,
            'limit' => $this->getConfig()->get('config_admin_limit'),
            'method' => null,
            'orderItemId' => [],
            'page' => 1,
            'param' => null,
            'selectedItems' => [[], function($v) {return is_array($v);}],
            'shippingMethod' => null,
            'weight' => null
        ]);
        $this->parameters['token'] = $this->getSession()->data['token'];
    }

    public function saveDiscount()
    {
        if (empty($_REQUEST['invoiceId']))
            return;
        else
            $invoiceId = $_REQUEST['invoiceId'];
        if (!is_numeric($_REQUEST['discount']))
        {
            $this->data['notifications']['error'] = $this->language->get("ERROR_INVALID_DISCOUNT_VALUE");
            $this->showCreateForm();
            return;
        }
        else
            $discount = $_REQUEST['discount'];
        InvoiceDAO::getInstance()->setDiscount($invoiceId, $discount);
        $this->getResponse()->setOutput("");
    }

    public function saveTextField()
    {
        if ($this->parameters['param'] == 'comment')
            InvoiceDAO::getInstance()->setComment($this->parameters['invoiceId'], $this->parameters['data']);
        else if ($this->parameters['param'] == 'packageNumber')
            InvoiceDAO::getInstance()->setPackageNumber($this->parameters['invoiceId'], $this->parameters['data']);
        else if ($this->parameters['param'] == 'shippingDate')
            InvoiceDAO::getInstance()->setShippingDate($this->parameters['invoiceId'], $this->parameters['data']);
        $this->getResponse()->setOutput('');
    }

    protected function setBreadcrumbs($breadcrumbs = [])
    {
        $this->data['breadcrumbs'] = array();
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->getUrl()->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false
        );
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('textOrderItems'),
            'href'      => $this->getUrl()->link('sale/order_items', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );
        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('headingTitle'),
            'href' => '',
            'separator' => ' :: '
        );
    }

    /**
     * @param OrderItem[] $orderItems
     */
    private function setOrderItemsStatusPacked($orderItems)
    {
        foreach ($orderItems as $orderItem)
            if ($orderItem->getProductId() == REPURCHASE_ORDER_PRODUCT_ID)
                OrderItemDAO::getInstance()->setStatus($orderItem->getId(), REPURCHASE_ORDER_ITEM_STATUS_PACKED);
            else
                OrderItemDAO::getInstance()->setStatus($orderItem->getId(), ORDER_ITEM_STATUS_PACKED);
    }

    private function showCreateForm() {
        //$this->log->write(print_r($this->request->request, true));
        if (!sizeof($this->parameters['selectedItems'])) {
            return;
        }
        $orderItems = OrderItemDAO::getInstance()->getOrderItems(
            array('selected_items' => $this->parameters['selectedItems']), null, true);


        //print_r($orderItems);
        /// Initialize interface values
        $this->data['button_action'] = $this->language->get('buttonCreate');
        $this->data['readOnly'] = "";

        /// Check whether input for invoice creation is valid
        $this->validateInput($orderItems);
        /// Prepare list
        $totalWeight = 0;
        $total = 0; $totalCustomerCurrency = 0;
        $orderItemIdParam = '';
        $localShipping = [];
        foreach ($orderItems as $orderItem) {
//            $orderItemObject = new OrderItem($this->registry, $orderItem['affiliate_id'],
//                $orderItem['affiliate_transaction_id'], $orderItem['comment'], $orderItem['customer_id'],
//                $orderItem['customer_name'], $orderItem['customer_nick'], $orderItem['order_item_id'],
//                $orderItem['image_path'], $orderItem['internal_model'], $orderItem['model'], $orderItem['name'],
//                $orderItem['order_id'], $orderItem['price'], $orderItem['product_id'], $orderItem['public_comment'],
//                $orderItem['quantity'], $orderItem['shipping'], $orderItem['status_date'], $orderItem['status_id'],
//                $orderItem['supplier_group_id'], $orderItem['supplier_id'], $orderItem['supplier_name'], $orderItem['total'],
//                $orderItem['weight'], $orderItem['weight_class_id']);
            $this->data['orderItems'][$orderItem->getId()] = array(
                'id' => $orderItem->getId(),
                'comment' => $orderItem->getPublicComment(),
                'image_path' => ImageService::getInstance()->getThumbnail($orderItem->getImagePath()),
                'model' => $orderItem->getModel(),
                'name' => $orderItem->getName(),
                'order_id' => $orderItem->getOrderId(),
                'options' => OrderItemDAO::getInstance()->getOrderItemOptionsString($orderItem->getId()),
                'price' => $this->getCurrentCurrency()->format($orderItem->getPrice(), $this->getConfig()->get('config_currency')),
                'quantity' => $orderItem->getQuantity(),
                'shipping' => $orderItem->getShippingCost(),
                'subtotal' => $this->getCurrentCurrency()->format($orderItem->getPrice() * $orderItem->getQuantity() + $orderItem->getShippingCost(), $this->getConfig()->get('config_currency')),
                'subtotalCustomerCurrency' => $this->getCurrentCurrency()->format(
                    $orderItem->getPrice(true) * $orderItem->getQuantity() + $orderItem->getShippingCost(true),
                    $orderItem->getCustomer()['base_currency_code'], 1
                )
            );
            $totalWeight +=
              $this->weight->convert(
                  $orderItem->getWeight(),
                  $orderItem->getWeightClassId(),
                  $this->getConfig()->get('config_weight_class_id')) * $orderItem->getQuantity();
            $total += $orderItem->getPrice() * $orderItem->getQuantity(); // + $orderItem->getShippingCost();
            $totalCustomerCurrency += $orderItem->getPrice(true) * $orderItem->getQuantity(); // + $orderItem->getShippingCost(true);
            $orderItemIdParam .= '&orderItemId[]=' . $orderItem->getId();
            /// Calculate local shipping
            $localShipping = $this->calculateLocalShipping($orderItem, $localShipping);
        }
        /// Check whether suppliers have free shipping
        $this->checkFreeLocalShipping($localShipping, $total, $totalCustomerCurrency);
        /// Set invoice data
        $firstItemOrder = OrderDAO::getInstance()->getOrder($orderItems[0]->getOrderId());
//        $this->log->write(print_r($firstItemOrder, true));
        $customer = CustomerDAO::getInstance()->getCustomer($firstItemOrder['customer_id']);
//        $shippingCost = \Shipping::getCost($orderItems, $firstItemOrder['shipping_method'], array('weight' => $totalWeight), $this->registry);
        $shippingCost = ShippingMethodDAO::getInstance()->getMethod(explode('.', $firstItemOrder['shipping_method'])[0])->
            getCost($firstItemOrder['shipping_method'], $orderItems, ['weight' => $totalWeight]);
        $this->data['comment'] = '';
        $this->data['discount'] = 0;
        $this->data['invoiceId'] = 0;
        $this->data['packageNumber'] = '';
        $this->data['shippingAddress'] = nl2br($this->getOrderAddressString($firstItemOrder)) . " (" . $firstItemOrder['shipping_phone'] . ")";
        $this->data['shippingCost'] =
            $this->getCurrentCurrency()->format($shippingCost, $this->getConfig()->get('config_currency'));
        $this->data['shippingCostRoute'] =
            $this->getUrl()->link(
                'sale/invoice/getShippingCost',
                'token=' . $this->parameters['token'] . $orderItemIdParam,
                'SSL'
            );
        $this->data['shippingMethod'] = $firstItemOrder['shipping_method'];
        $this->data['shippingMethodCode'] = $firstItemOrder['shipping_method'];

        $this->data['total'] = $this->getCurrentCurrency()->format($total, $this->getConfig()->get('config_currency'));
        $this->data['totalRaw'] = $total;
        $this->data['totalWeight'] = $totalWeight;
        $this->data['grandTotal'] = $this->getCurrentCurrency()->format($total + $shippingCost, $this->getConfig()->get('config_currency'));
        $this->data['totalCustomerCurrency'] = $this->getCurrentCurrency()->format(
            $totalCustomerCurrency +
            $this->getCurrentCurrency()->convert($shippingCost, $this->getConfig()->get('config_currency'), $customer['base_currency_code']),
            $customer['base_currency_code'], 1);
        $this->data['shippingMethods'] = ShippingMethodDAO::getInstance()->getShippingOptions($this->getOrderAddress($firstItemOrder));
//        $this->log->write(print_r($this->data, true));
        $this->data['customerCurrencyCode'] = $customer['base_currency_code'];
    }

    private function showEditForm() {
        if (!$this->parameters['invoiceId'])
            return;

        $invoice = InvoiceDAO::getInstance()->getInvoice($this->parameters['invoiceId']);

        /// Initialize interface values
        $this->data['button_action'] = $this->language->get('button_close');
        $this->data['readOnly'] = "disabled";
        $this->data['shippingMethods'] = ShippingMethodDAO::getInstance()->getShippingOptions(
            $this->modelReferenceAddress->getAddress($invoice->getShippingAddressId()));

        $orderItemIdParam = '';

        foreach ($invoice->getOrderItems() as $orderItem) {

            $this->data['orderItems'][] = array(
                'id' => $orderItem->getId(),
                'comment' => $orderItem->getPublicComment(),
                'image_path' => ImageService::getInstance()->getThumbnail($orderItem->getImagePath()),
                'model' => $orderItem->getModel(),
                'name' => $orderItem->getName(),
                'options' => OrderItemDAO::getInstance()->getOrderItemOptionsString($orderItem->getId()),
                'order_id' => $orderItem->getOrderId(),
                'price' => $this->getCurrentCurrency()->format($orderItem->getPrice(), $this->getConfig()->get('config_currency')),
                'quantity' => $orderItem->getQuantity(),
                'subtotal' => $this->getCurrentCurrency()->format($orderItem->getTotal(), $this->getConfig()->get('config_currency')),
                'subtotalCustomerCurrency' => $this->getCurrentCurrency()->format($orderItem->getTotal(true), $orderItem->getCustomer()['base_currency_code'], 1),
                'shipping' => $this->getCurrentCurrency()->format($orderItem->getShippingCost(), $this->getConfig()->get('config_currency'))
            );
            $orderItemIdParam .= '&orderItemId[]=' . $orderItem->getId();
        }

        foreach ($this->data['orderItems'] as $item) {
            $ids[] = $item['id'];
        }

        if (empty($this->data['orderItems'])) {
            $_invoice = InvoiceDAO::getInstance()->getInvoice($this->parameters['invoiceId']);
            foreach (InvoiceDAO::getInstance()->getInvoiceItems($_invoice['invoice_id']) as $invoiceItem) {
                    $orderItem = OrderItemDAO::getInstance()->getOrderItem($invoiceItem['order_item_id']);
                    $this->data['orderItems'][] = array(
                        'id' => $orderItem->getId(),
                        'comment' => $orderItem->getPublicComment(),
                        'image_path' => ImageService::getInstance()->getThumbnail($orderItem->getImagePath()),
                        'model' => $orderItem->getModel(),
                        'name' => $orderItem->getName(),
                        'options' => OrderItemDAO::getInstance()->getOrderItemOptionsString($orderItem->getId()),
                        'order_id' => $orderItem->getOrderId(),
                        'price' => $this->currency->format($orderItem->getPrice(), $this->getConfig()->get('config_currency')),
                        'quantity' => $orderItem->getQuantity(),
                        'subtotal' => $this->currency->format($orderItem->getPrice() * $orderItem->getQuantity(), $this->getConfig()->get('config_currency'))
                    );
                    $orderItemIdParam .= '&orderItemId[]=' . $orderItem->getId();
                }
        }
        $add = $this->modelReferenceAddress->getAddress($invoice->getShippingAddressId());
        $this->getLoader()->model('sale/order');
        $order_info = $this->model_sale_order->getOrderByShippingAddressId($invoice->getShippingAddressId());
        /// Set invoice data
//        $customer = CustomerDAO::getInstance()->getCustomer($invoice['customer_id']);
        $this->data['comment'] = $invoice->getComment();
        $this->data['discount'] = $invoice->getDiscount();
        $this->data['invoiceId'] = $invoice->getId();
        $this->data['packageNumber'] = $invoice->getPackageNumber();
        $this->data['shippingAddress'] = nl2br($this->modelReferenceAddress->toString($invoice->getShippingAddressId())) . "<br />" . $order_info['shipping_phone'];//$add['phone'];
        $this->data['shippingCost'] = $this->getCurrentCurrency()->format($invoice->getShippingCost(), $this->getConfig()->get('config_currency'));
        $this->data['shippingCostRaw'] = $invoice->getShippingCost();
        $this->data['shippingCostRoute'] =
            $this->getUrl()->link(
                'sale/invoice/getShippingCost',
                'token=' . $this->parameters['token'] . $orderItemIdParam,
                'SSL'
            );
        $this->data['shippingMethod'] = $invoice->getShippingMethod();
        $this->data['shippingDate'] = $invoice->getShippingDate();
        $this->data['total'] = $this->getCurrentCurrency()->format($invoice->getSubtotal(), $this->getConfig()->get('config_currency'));
        $this->data['totalRaw'] = $invoice->getSubtotal();
        $this->data['totalCustomerCurrency'] = $this->getCurrentCurrency()->format($invoice->getTotalCustomerCurrency(), $invoice->getCurrencyCode(), 1);
        $this->data['totalWeight'] = $invoice->getWeight();
        $this->data['grandTotal'] =
            $this->getCurrentCurrency()->format(
                $invoice->getTotal(),
                $this->getConfig()->get('config_currency'));
        $this->data['customerCurrencyCode'] = $invoice->getCurrencyCode();
//        $this->log->write(print_r($this->data, true));
    }

    public function showForm() {
        $this->setBreadcrumbs();
        $this->data['buttonRecalculateShippingCost'] = $this->language->get('RECALCULATE_SHIPPING_COST');
        $this->data['textDiscount'] = $this->language->get('DISCOUNT');
        $this->data['textComment'] = $this->language->get('textComment');
        $this->data['textGrandTotal'] = $this->language->get('textGrandTotal');
        $this->data['textItemImage'] = $this->language->get('textItemImage');
        $this->data['textItemName'] = $this->language->get('textItemName');
        $this->data['textOrderId'] = $this->language->get('textOrderId');
        $this->data['textOrderItemId'] = $this->language->get('textOrderItemId');
        $this->data['textPackageNumber'] = $this->language->get("PACKAGE_NUMBER");
        $this->data['textPrice'] = $this->language->get('textPrice');
        $this->data['textQuantity'] = $this->language->get('textQuantity');
        $this->data['textShipping'] = $this->language->get('textShipping');
        $this->data['textShippingCost'] = $this->language->get('textShippingCost');
        $this->data['textShippingAddress'] = $this->language->get('textShippingAddress');
        $this->data['textShippingMethod'] = $this->language->get('textShippingMethod');
        $this->data['textShippingDate'] = $this->language->get('textShippingDate');
        $this->data['textSubtotal'] = $this->language->get('textSubtotal');
        $this->data['textSubtotalCustomerCurrency'] = $this->language->get('SUBTOTAL_CUSTOMER_CURRENCY');
        $this->data['textTotal'] = $this->language->get('textTotal');

        $this->data['textTotalCustomerCurrency'] = $this->language->get('TOTAL_CUSTOMER_CURRENCY');
        $this->data['textWeight'] = $this->language->get('textWeight');

        if ($this->getRequest()->getMethod() == 'GET') {
          $this->data['submitAction'] = "javascript:window.close();";
          $this->showEditForm();
        } elseif ($this->getRequest()->getMethod() == 'POST') {
          $this->data['submitAction'] = $this->getUrl()->link('sale/invoice/create', 'token=' . $this->session->data['token'], 'SSL');
          $this->showCreateForm();
        }

        $this->children = array(
            'common/header',
            'common/footer'
        );
        $this->getResponse()->setOutput($this->render('sale/invoiceForm.tpl.php'));
    }

    private function validateDeletion($invoiceId)
    {
        $relatedTransaction = TransactionDAO::getInstance()->getTransactionByInvoiceId($invoiceId);
        if ($relatedTransaction)
            $this->data['notifications']['error'] = sprintf(
                $this->language->get('ERROR_RELATED_TRANSACTION_EXISTS'), $relatedTransaction['customer_transaction_id'], $invoiceId);
    }

    private function validateInput($orderItems) {
        $this->data['notifications']['warning'] = '';
        if (!$this->checkAddresses($orderItems))
            $this->data['notifications']['warning'] .= $this->language->get('errorDifferentAddresses') . "\n";
        $orderItemsInInvoice = $this->getOrderItemsHavingInvoice($orderItems);
        if ($orderItemsInInvoice)
            $this->data['notifications']['warning'] .= sprintf($this->language->get('errorOrderItemHasInvoice'), $orderItemsInInvoice) . "\n";
        if (!$this->data['notifications']['warning'])
            unset($this->data['notifications']['warning']);
    }

    /**
     * @param array $localShipping
     * @param float $total
     * @param float $totalCustomerCurrency
     */
    private function checkFreeLocalShipping($localShipping, &$total, &$totalCustomerCurrency) {
        foreach ($localShipping as $supplierEntry) {
            /** @var OrderItem $orderItem */
            $orderItem = $supplierEntry['orderItem'];
            if ($supplierEntry['total'] - $orderItem->getShippingCost() >= $orderItem->getSupplier()->getFreeShippingThreshold()) {
                $orderItem->setShippingCost(0);
            } else {
                $total += $orderItem->getShippingCost();
                $totalCustomerCurrency += $orderItem->getShippingCost(true);
            }
            OrderItemDAO::getInstance()->saveOrderItem($orderItem, true);
            $this->data['orderItems'][$orderItem->getId()]['shipping'] = $orderItem->getShippingCost();
            $this->data['orderItems'][$orderItem->getId()]['subtotal'] = $this->getCurrentCurrency()->format(
                $orderItem->getPrice() * $orderItem->getQuantity() + $orderItem->getShippingCost(),
                $this->getConfig()->get('config_currency')
            );
        }
    }

    /**
     * @param OrderItem $orderItem
     * @param array $localShipping
     * @return array
     */
    private function calculateLocalShipping($orderItem, $localShipping) {
        if (array_key_exists($orderItem->getSupplierId(), $localShipping)) {
            $localShipping[$orderItem->getSupplierId()]['total'] += $orderItem->getTotal();
            $orderItem->setShippingCost(0);
            OrderItemDAO::getInstance()->saveOrderItem($orderItem, true);
        } else {
            $localShipping[$orderItem->getSupplierId()]['orderItem'] = $orderItem;
            $localShipping[$orderItem->getSupplierId()]['total'] = $orderItem->getTotal();
            $orderItem->setShippingCost($orderItem->getSupplier()->getShippingCost());
        }
        return $localShipping;
    }
}
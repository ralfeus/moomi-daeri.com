<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dev
 * Date: 18.7.12
 * Time: 21:43
 * To change this template use File | Settings | File Templates.
 */
class ControllerSaleInvoice extends Controller
{
    private $modelReferenceAddress;
    private $modelSaleCustomer;
    private $modelSaleInvoice;
    private $modelSaleOrder;
    private $modelSaleOrderItem;

    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->load->language('sale/invoice');
        $this->load->library("Transaction");
        $this->modelReferenceAddress = $this->load->model('reference/address');
        $this->modelSaleCustomer = $this->load->model('sale/customer');
        $this->modelSaleInvoice = $this->load->model('sale/invoice');
        $this->modelSaleOrder = $this->load->model('sale/order');
        $this->modelSaleOrderItem = $this->load->model('sale/order_item');
        $this->load->model('tool/image');

        $this->data['notifications'] = array();
        $this->document->setTitle($this->language->get('headingTitle'));
        $this->data['headingTitle'] = $this->language->get('headingTitle');
    }

    private function checkAddresses($orderItems)
    {
        $firstAddress = "";
        foreach ($orderItems as $orderItem)
        {
            $order = $this->registry->get('model_sale_order')->getOrder($orderItem['order_id']);
            if (!$firstAddress)
                $firstAddress = $this->getOrderAddressString($order);
            else
                if ($this->getOrderAddressString($order) != $firstAddress)
                    return false;
        }
        return true;
    }

    public function create()
    {
//        $this->log->write(print_r($_REQUEST, true));
        $comment = isset($_REQUEST['comment']) ? $_REQUEST['comment'] : '';
        if (!is_numeric($_REQUEST['discount']))
        {
            $this->data['notifications']['error'] = $this->language->get("ERROR_INVALID_DISCOUNT_VALUE");
            $this->showCreateForm();
            return;
        }
        else
            $discount = $_REQUEST['discount'];
        if (!isset($this->request->request['selectedItems']))
            return;
        $total = isset($_REQUEST['total']) ? $_REQUEST['total'] : 0;
        if (!is_numeric($this->request->request['totalWeight']))
        {
            $this->data['notifications']['error'] = $this->language->get("error_invalid_weight_value");
            $this->showCreateForm();
            return;
        }
        else
            $totalWeight = $_REQUEST['totalWeight'];

        $orderItems = $this->modelSaleOrderItem->getOrderItems(array(
            'selected_items' => $_REQUEST['selectedItems']
        ));
        $newInvoiceId = $this->modelSaleInvoice->addInvoice(
            $orderItems[0]['order_id'],
            $orderItems,
            $this->parameters['shippingMethod'],
            $totalWeight,
            $discount,
            $comment,
            $total
        );
        $this->handleCredit($newInvoiceId);
        $this->setOrderItemsStatusPacked($orderItems);
        $this->data['notifications']['success'] = sprintf($this->language->get("SUCCESS_INVOICE_CREATED"), $newInvoiceId);
        unset($this->data['notifications']['error']);
        unset($this->data['notifications']['warning']);
        $this->index();
    }

    public function delete()
    {
        if (!empty($this->parameters['invoiceId']))
        {
            $relatedTransaction = Transaction::getTransactionByInvoiceId($this->parameters['invoiceId']);
            if (!empty($relatedTransaction))
                Transaction::deleteTransaction($relatedTransaction['customer_transaction_id']);
            $this->modelSaleInvoice->deleteInvoice($this->request->request['invoiceId']);
            $this->data['notifications']['success'] = sprintf(
                $this->language->get('SUCCESS_INVOICE_DELETED'), $this->request->request['invoiceId']);
            $this->index();
        }
    }

    private function getCustomers()
    {
        $data = array();
        foreach ($this->parameters as $key => $value)
        {
            if (strpos($key, 'filter') === false)
                continue;
            $data[$key] = $value;
        }
        unset($data['filterCustomerId']);
        $tmpResult = array();
        foreach ($this->modelSaleInvoice->getInvoices($data) as $invoice)
            if (!in_array($invoice['customer_id'], $tmpResult))
                $tmpResult[$invoice['customer_id']] = $invoice['lastname'] . " " . $invoice['firstname'] . ' / ' . $invoice['nickname'];
        natcasesort($tmpResult);
        return $tmpResult;
    }

    private function getData()
    {
        $modelSaleInvoice = $this->registry->get('model_sale_invoice');

        /// Initialize interface values
        $this->data['textAction'] = $this->language->get('textAction');
        $this->data['textCustomer'] = $this->language->get('textCustomer');
        $this->data['textInvoiceId'] = $this->language->get('textInvoiceId');
        $this->data['textFilter'] = $this->language->get('FILTER');
        $this->data['textShippingCost'] = $this->language->get('textShippingCost');
        $this->data['textShippingMethod'] = $this->language->get('textShippingMethod');
        $this->data['textStatus'] = $this->language->get('STATUS');
        $this->data['textPackage'] = $this->language->get('textPackage');
        $this->data['textSubtotal'] = $this->language->get('textSubtotal');
        $this->data['textTotal'] = $this->language->get('textTotal');
        $this->data['textTotalCustomerCurrency'] = $this->language->get('TOTAL_CUSTOMER_CURRENCY');
        $this->data['textWeight'] = $this->language->get('textWeight');

        $this->data['customers'] = $this->getCustomers();
        $data = $this->parameters;
        foreach ($modelSaleInvoice->getInvoices($data) as $invoice)
        {
            $action = array();
            $action[] = array(
                'text' => $this->language->get('VIEW'),
                'href' => $this->url->link('sale/invoice/showForm', 'invoiceId=' . $invoice['invoice_id'] . '&token=' . $this->session->data['token'], 'SSL')
            );
            if (!$this->load->model('sale/transaction')->getTransactionByInvoiceId($invoice['invoice_id']))
                $action[] = array(
                    'text' => $this->language->get('DELETE'),
                    'href' => $this->url->link('sale/invoice/delete', 'invoiceId=' . $invoice['invoice_id'] . '&token=' . $this->session->data['token'], 'SSL')
                );
            else
                $action[] = array(
                    'text' => $this->language->get('DELETE'),
                    'onclick' => "confirmDeletion('" .
                        $this->url->link(
                            'sale/invoice/delete',
                            'invoiceId=' . $invoice['invoice_id'] . '&token=' . $this->session->data['token'], 'SSL') .
                        "')"
                );

            //$this->log->write(print_r($invoice, true));
//            $customer = $this->modelSaleCustomer->getCustomer($invoice['customer_id']);
            $this->data['invoices'][] = array(
                'invoiceId' => $invoice['invoice_id'],
                'action' => $action,
                'customer' => $invoice['lastname'] . ' ' . $invoice['firstname'],
                'customerId' => $invoice['customer_id'],
                'shippingCost' => $this->currency->format($invoice['shipping_cost'], $this->config->get('config_currency')),
                'shippingMethod' => Shipping::getName($invoice['shipping_method'], $this->registry),
                'status' => $this->load->model('localisation/invoice')->getInvoiceStatus($invoice['invoice_status_id']),
                'subtotal' => $this->currency->format($invoice['subtotal'], $this->config->get('config_currency')),
                'total' => $this->currency->format($invoice['total'], $this->config->get('config_currency')),
                'totalCustomerCurrency' => $this->currency->format($invoice['total'], $invoice['base_currency_code']),
                'weight' => $invoice['weight'],
                'package_number' => $invoice['package_number']
            );
        }
        $this->data = array_merge($this->data, $this->parameters);
//        $this->log->write(print_r($this->parameters, true));
    }

    private function getOrderAddress($order)
    {
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

    private function getOrderItemsHavingInvoice($orderItems)
    {
        $result = "";
        foreach ($orderItems as $orderItem)
        {
            $modelSaleInvoice = $this->load->model('sale/invoice');
            $existingInvoices = $modelSaleInvoice->getInvoicesByOrderItem($orderItem['order_product_id']);
            if ($existingInvoices)
            {
                $result .= $orderItem['order_product_id'] . " ==> ";
                foreach ($existingInvoices as $existingInvoice)
                    $result .= $existingInvoice['invoice_id'] . ",";
                $result = rtrim($result, ',') . "\n";
            }
        }
        return rtrim($result, "\n");
    }

    public function getShippingCost()
    {
        $this->log->write(print_r($this->parameters, true));
        $orderItems = $this->modelSaleOrderItem->getOrderItems(array('filterOrderItemId' => $this->parameters['orderItemId']));
        $cost = Shipping::getCost(
            $orderItems,
            $this->parameters['method'],
            array('weight' => $this->parameters['weight']),
            $this->registry
        );
        $json = array(
            'cost' => $cost
        );
        $this->response->setOutput(json_encode($json));
    }

    private function handleCredit($invoiceId)
    {
        $modelTransaction = $this->load->model('sale/transaction');
        $invoice = $this->modelSaleInvoice->getInvoice($invoiceId);
        $customer = $this->modelSaleCustomer->getCustomer($invoice['customer_id']);
//        $this->log->write(print_r($invoice, true));
//        $this->log->write(print_r($customer, true));
        if ($customer['await_invoice_confirmation'])
            $this->modelSaleInvoice->setInvoiceStatus($invoiceId, IS_AWAITING_CUSTOMER_CONFIRMATION);
        else
        {
            $totalToPay = $this->currency->convert(
                $invoice['total'],
                $this->config->get('config_currency'),
                $customer['base_currency_code']);
            if ($customer['balance'] < $totalToPay)
                if ($customer['allow_overdraft'])
                {
//                    $modelTransaction->addTransaction(
//                        $invoiceId,
//                        $customer['customer_id'],
//                        $this->currency->convert(
//                            $invoice['total'],
//                            $this->config->get('config_currency'),
//                            $customer['base_currency_code']),
//                        $customer['base_currency_code']
//                    );
                    Transaction::addPayment($customer['customer_id'], $invoiceId, $this->registry);
                    $this->modelSaleInvoice->setInvoiceStatus($invoiceId, IS_PAID);
                }
                else
                    $this->modelSaleInvoice->setInvoiceStatus($invoiceId, IS_AWAITING_PAYMENT);
            else
            {
//                $modelTransaction->addTransaction(
//                    $invoiceId,
//                    $customer['customer_id'],
//                    $this->currency->convert(
//                        $invoice['total'],
//                        $this->config->get('config_currency'),
//                        $customer['base_currency_code']),
//                    $customer['base_currency_code']
//                );
                Transaction::addPayment($customer['customer_id'], $invoiceId, $this->registry);
                $this->modelSaleInvoice->setInvoiceStatus($invoiceId, IS_PAID);
            }
        }
        $this->load->model('tool/communication')->sendMessage(
            $customer['customer_id'],
            sprintf(
                $this->language->get('INVOICE_STATUS_NOTIFICATION'),
                $this->load->model('localisation/invoice')->getInvoiceStatus($invoiceId),
                $this->currency->format($invoice['total'], $customer['base_currency_code']),
                $this->currency->format(
                    $this->modelSaleCustomer->getCustomerBalance($customer['customer_id']),
                    $customer['base_currency_code'],
                    1)
            ),
            SYS_MSG_INVOICE_CREATED
        );
    }

    public function index()
    {
        $this->getData();
        $this->setBreadcrumbs();
        $this->template = 'sale/invoiceList.tpl';
        $this->children = array(
            'common/header',
            'common/footer'
        );
        $this->response->setOutput($this->render());
    }

    protected function initParameters()
    {
        $this->parameters['data'] = empty($_REQUEST['data']) ? null : $_REQUEST['data'];
        $this->parameters['filterCustomerId'] = empty($_REQUEST['filterCustomerId']) ? array() : $_REQUEST['filterCustomerId'];
        $this->parameters['invoiceId'] = empty($_REQUEST['invoiceId']) ? null : $_REQUEST['invoiceId'];
        $this->parameters['method'] = empty($_REQUEST['method']) ? null : $_REQUEST['method'];
        $this->parameters['orderItemId'] = empty($_REQUEST['orderItemId']) || !is_array($_REQUEST['orderItemId']) ? array() : $_REQUEST['orderItemId'];
        $this->parameters['param'] = empty($_REQUEST['param']) ? null : $_REQUEST['param'];
        $this->parameters['shippingMethod'] = empty($_REQUEST['shippingMethod']) ? null : $_REQUEST['shippingMethod'];
        $this->parameters['token'] = $this->session->data['token'];
        $this->parameters['weight'] = empty($_REQUEST['weight']) ? null : $_REQUEST['weight'];
    }

    public function printInvoice()
    {

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
        $this->modelSaleInvoice->setDiscount($invoiceId, $discount);
        $this->response->setOutput("");
    }

    public function saveTextField()
    {
        if ($this->parameters['param'] == 'comment')
            $this->modelSaleInvoice->setComment($this->parameters['invoiceId'], $this->parameters['data']);
        else if ($this->parameters['param'] == 'packageNumber')
            $this->modelSaleInvoice->setPackageNumber($this->parameters['invoiceId'], $this->parameters['data']);
        $this->response->setOutput('');
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
            'text'      => $this->language->get('textOrderItems'),
            'href'      => $this->url->link('sale/order_items', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );
        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('headingTitle'),
            'href' => '',
            'separator' => ' :: '
        );
    }

    private function setOrderItemsStatusPacked($orderItems)
    {
        foreach ($orderItems as $orderItem)
            if ($orderItem['product_id'] == REPURCHASE_ORDER_PRODUCT_ID)
                $this->modelSaleOrderItem->setOrderItemStatus($orderItem['order_product_id'], REPURCHASE_ORDER_ITEM_STATUS_PACKED);
            else
                $this->modelSaleOrderItem->setOrderItemStatus($orderItem['order_product_id'], ORDER_ITEM_STATUS_PACKED);
    }

    private function showCreateForm()
    {
        //$this->log->write(print_r($this->request->request, true));
        $this->setBreadcrumbs();
        if (!isset($this->request->request['selectedItems']))
            return;
        $orderItems = $this->modelSaleOrderItem->getOrderItems(array(
            'selected_items' => $this->request->request['selectedItems']
        ));

        /// Initialize interface values
        $this->data['button_action'] = $this->language->get('buttonCreate');
        $this->data['readOnly'] = "";

        /// Check whether input for invoice creation is valid
        $this->validateInput($orderItems);
        /// Prepare list
        $totalWeight = 0;
        $total = 0;
        $orderItemIdParam = '';
        foreach ($orderItems as $orderItem)
        {
            $this->data['orderItems'][] = array(
                'id' => $orderItem['order_product_id'],
                'comment' => $orderItem['public_comment'],
                'image_path' => $this->registry->get('model_tool_image')->getImage($orderItem['image_path']),
                'model' => $orderItem['model'],
                'name' => $orderItem['name'],
                'order_id' => $orderItem['order_id'],
                'options' => $this->modelSaleOrderItem->getOrderItemOptionsString($orderItem['order_item_id']),
                'price' => $this->currency->format($orderItem['price'], $this->config->get('config_currency')),
                'quantity' => $orderItem['quantity'],
                'subtotal' => $this->currency->format($orderItem['price'] * $orderItem['quantity'], $this->config->get('config_currency'))
            );
            $totalWeight +=
                $this->weight->convert(
                    $orderItem['weight'],
                    $orderItem['weight_class_id'],
                    $this->config->get('config_weight_class_id')) * $orderItem['quantity'];
            $total += $orderItem['total']; //$orderItem['price'] * $orderItem['quantity'];
            $orderItemIdParam .= '&orderItemId[]=' . $orderItem['order_product_id'];
        }
        /// Set invoice data
        $firstItemOrder = $this->modelSaleOrder->getOrder($orderItems[0]['order_id']);
//        $this->log->write(print_r($firstItemOrder, true));
        $customer = $this->modelSaleCustomer->getCustomer($firstItemOrder['customer_id']);
        $this->data['comment'] = '';
        $this->data['discount'] = 0;
        $this->data['invoiceId'] = 0;
        $this->data['packageNumber'] = '';
        $this->data['shippingAddress'] = nl2br($this->getOrderAddressString($firstItemOrder));
        $this->data['shippingCost'] =
            $this->currency->format(
                Shipping::getCost($orderItems, $firstItemOrder['shipping_method'], array('weight' => $totalWeight), $this->registry),
                $this->config->get('config_currency'));
        $this->data['shippingCostRoute'] =
            $this->url->link(
                'sale/invoice/getShippingCost',
                'token=' . $this->parameters['token'] . $orderItemIdParam,
                'SSL'
            );
        $this->data['shippingMethod'] = $firstItemOrder['shipping_method'];
        $this->data['shippingMethodCode'] = $firstItemOrder['shipping_method'];

        $this->data['total'] = $this->currency->format($total, $this->config->get('config_currency'));
        $this->data['totalRaw'] = $total;
        $this->data['totalWeight'] = $totalWeight;
        $this->data['grandTotal'] =
            $this->currency->format(
                $total + Shipping::getCost($orderItems, $firstItemOrder['shipping_method'], array('weight' => $totalWeight), $this->registry),
                $this->config->get('config_currency'));
        $this->data['totalCustomerCurrency'] = $this->currency->format(
            $total + Shipping::getCost(
                $orderItems,
                $firstItemOrder['shipping_method'],
                array('weight' => $totalWeight),
                $this->registry),
            $customer['base_currency_code']);
        $this->data['shippingMethods'] = Shipping::getShippingMethods(
            $this->getOrderAddress($firstItemOrder), $this->registry);
        $this->log->write(print_r($this->data, true));
        $this->template = 'sale/invoiceForm.tpl';
        $this->children = array(
            'common/header',
            'common/footer'
        );
        $this->response->setOutput($this->render());
    }

    private function showEditForm()
    {
        if (!$this->parameters['invoiceId'])
            return;

        $this->setBreadcrumbs();
        $invoice = $this->modelSaleInvoice->getInvoice($this->request->request['invoiceId']);

        /// Initialize interface values
        $this->data['button_action'] = $this->language->get('button_close');
        $this->data['readOnly'] = "disabled";
        $this->data['shippingMethods'] = Shipping::getShippingMethods(
            $this->modelReferenceAddress->getAddress($invoice['shipping_address_id']), $this->registry);

        /// Prepare list
        //$this->log->write(print_r($modelSaleInvoice->getInvoiceItems($invoice['invoice_id']), true));
        $orderItemIdParam = '';
        foreach ($this->modelSaleInvoice->getInvoiceItems($invoice['invoice_id']) as $invoiceItem)
        {
            $orderItem = $this->modelSaleOrderItem->getOrderItem($invoiceItem['order_item_id']);
            //$this->log->write(print_r($orderItem, true));
            $this->data['orderItems'][] = array(
                'id' => $orderItem['order_product_id'],
                'comment' => $orderItem['public_comment'],
                'image_path' => $this->registry->get('model_tool_image')->getImage($orderItem['image_path']),
                'model' => $orderItem['model'],
                'name' => $orderItem['name'],
                'options' => $this->modelSaleOrderItem->getOrderItemOptionsString($invoiceItem['order_item_id']),
                'order_id' => $orderItem['order_id'],
                'price' => $this->currency->format($orderItem['price'], $this->config->get('config_currency')),
                'quantity' => $orderItem['quantity'],
                'subtotal' => $this->currency->format($orderItem['price'] * $orderItem['quantity'], $this->config->get('config_currency'))
            );
            $orderItemIdParam .= '&orderItemId[]=' . $invoiceItem['order_item_id'];
        }
        /// Set invoice data
        $customer = $this->modelSaleCustomer->getCustomer($invoice['customer_id']);
        $this->data['comment'] = $invoice['comment'];
        $this->data['discount'] = $invoice['discount'];
        $this->data['invoiceId'] = $invoice['invoice_id'];
        $this->data['packageNumber'] = $invoice['package_number'];
        $this->data['shippingAddress'] = nl2br($this->modelReferenceAddress->toString($invoice['shipping_address_id']));
        $this->data['shippingCost'] = $this->currency->format($invoice['shipping_cost'], $this->config->get('config_currency'));
        $this->data['shippingCostRaw'] = $invoice['shipping_cost'];
        $this->data['shippingCostRoute'] =
            $this->url->link(
                'sale/invoice/getShippingCost',
                'token=' . $this->parameters['token'] . $orderItemIdParam,
                'SSL'
            );
        $this->data['shippingMethod'] = $invoice['shipping_method'] ;//Shipping::getName($invoice['shipping_method'], $this->registry);
        $this->data['total'] = $this->currency->format($invoice['subtotal'], $this->config->get('config_currency'));
        $this->data['totalRaw'] = $invoice['subtotal'];
        $this->data['totalCustomerCurrency'] = $this->currency->format($invoice['total'], $customer['base_currency_code']);
        $this->data['totalWeight'] = $invoice['weight'];
        $this->data['grandTotal'] =
            $this->currency->format(
                $invoice['total'],
                $this->config->get('config_currency'));
//        $this->log->write(print_r($this->data, true));

        $this->template = 'sale/invoiceForm.tpl';
        $this->children = array(
            'common/header',
            'common/footer'
        );
        $this->response->setOutput($this->render());
    }

    public function showForm()
    {
//        $this->log->write(print_r($this->request->request, true));
        /// Set common interface values
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
        $this->data['textShippingCost'] = $this->language->get('textShippingCost');
        $this->data['textShippingAddress'] = $this->language->get('textShippingAddress');
        $this->data['textShippingMethod'] = $this->language->get('textShippingMethod');
        $this->data['textSubtotal'] = $this->language->get('textSubtotal');
        $this->data['textTotal'] = $this->language->get('textTotal');

        $this->data['textTotalCustomerCurrency'] = $this->language->get('TOTAL_CUSTOMER_CURRENCY');
        $this->data['textWeight'] = $this->language->get('textWeight');

        if ($this->request->server['REQUEST_METHOD'] == 'GET')
        {
            $this->data['submitAction'] = "javascript:window.close();";
            $this->showEditForm();
        }
        elseif ($this->request->server['REQUEST_METHOD'] == 'POST')
        {
            $this->data['submitAction'] = $this->url->link('sale/invoice/create', 'token=' . $this->session->data['token'], 'SSL');
            $this->showCreateForm();
        }
    }

    private function validateDeletion($invoiceId)
    {
        $relatedTransaction = $this->load->model('sale/transaction')->getTransactionByInvoiceId($invoiceId);
        if ($relatedTransaction)
            $this->data['notifications']['error'] = sprintf(
                $this->language->get('ERROR_RELATED_TRANSACTION_EXISTS'), $relatedTransaction['customer_transaction_id'], $invoiceId);
    }

    private function validateInput($orderItems)
    {
        $this->data['notifications']['warning'] = '';
        if (!$this->checkAddresses($orderItems))
            $this->data['notifications']['warning'] .= $this->language->get('errorDifferentAddresses') . "\n";
        $orderItemsInInvoice = $this->getOrderItemsHavingInvoice($orderItems);
        if ($orderItemsInInvoice)
            $this->data['notifications']['warning'] .= sprintf($this->language->get('errorOrderItemHasInvoice'), $orderItemsInInvoice) . "\n";
        if (!$this->data['notifications']['warning'])
            unset($this->data['notifications']['warning']);
    }
}
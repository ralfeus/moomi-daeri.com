<?php
use model\sale\InvoiceDAO;

/**
 * Created by JetBrains PhpStorm.
 * User: dev
 * Date: 18.7.12
 * Time: 21:43
 * To change this template use File | Settings | File Templates.
 */
class ControllerAccountInvoice extends Controller {
    public function __construct($registry)
    {
        parent::__construct($registry);

        if (!$this->getCustomer()->isLogged()) {
            $this->session->data['redirect'] = $this->selfUrl;

            $this->redirect($this->url->link('account/login', '', 'SSL'));
        }

        $this->load->language('account/invoice');
        $this->load->library('Transaction');
        $this->load->model('reference/address');
        $this->load->model('account/order');
        $this->load->model('account/order_item');
        $this->load->model('tool/image');

        $this->data['notifications'] = array();
        $this->document->setTitle($this->language->get('headingTitle'));
        $this->data['headingTitle'] = $this->language->get('headingTitle');
    }

    public function confirm() {
        $this->log->write('Starting');
        if (!isset($this->request->request['invoiceId']))
            $json['error'] = "Unexpected error";
        else
        {
            Transaction::addPayment(
                $this->customer->getId(),
                $this->request->request['invoiceId'],
                $this->registry);
            $invoice = InvoiceDAO::getInstance()->getInvoice($this->request->request['invoiceId']);
            $json['newStatus'] = $this->load->model('localisation/invoice')->getInvoiceStatus(
                $invoice->getStatusId(),
                $this->session->data['language_id']
            );
        }
//        $this->log->write(print_r($json, true));
        $this->response->setOutput(json_encode($json));
    }

    private function getOrderAddress($order)
    {
        $modelReferenceAddress = $this->registry->get('model_Reference_Address');
        if (isset($order['address_id']))
            return $modelReferenceAddress->toString($order['address_id']);
        else
            return
                $modelReferenceAddress->toString(array(
                    'lastname' => $order['shipping_lastname'],
                    'firstname' => $order['shipping_firstname'],
                    'company' => $order['shipping_company'],
                    'address_1' => $order['shipping_address_1'],
                    'address_2' => $order['shipping_address_2'],
                    'city' => $order['shipping_city'],
                    'postcode' => $order['shipping_postcode'],
                    'zone_id' => $order['shipping_zone_id'],
                    'country_id' => $order['shipping_country_id']
                ));
    }

    public function index()
    {
//        $modelAccountInvoice = $this->registry->get('model_account_invoice');
        //$modelSaleCustomer = $this->load->model('sale/customer');
        $invoices = InvoiceDAO::getInstance()->getInvoices(array('filterCustomerId' => array($this->getCustomer()->getId())), "invoice_id DESC");
        if ($invoices)
        {
            foreach ($invoices as $invoice)
            {
                $action = array();
                $action[] = array(
                    'text' => $this->language->get('textView'),
                    'href' => $this->url->link('sale/invoice/showForm', 'invoiceId=' . $invoice->getId(), 'SSL')
                );
                $this->data['invoices'][] = array(
                    'invoiceId' => $invoice->getId(),
                    'action' => $action,
                    'customer' => $this->customer->getLastName() . ' ' . $this->customer->getFirstname(),
                    'timeModified' =>$invoice->getTimeModified(),
                    'href' => $this->url->link('account/invoice/showForm', 'invoiceId=' . $invoice->getId(), 'SSL'),
                    'itemsCount' => InvoiceDAO::getInstance()->getInvoiceItemsCount($invoice->getId()),
                    'shippingCost' => $this->getCurrency()->format($invoice->getShippingCost()),
                    'shippingMethod' =>\Shipping::getName($invoice->getShippingMethod(), $this->registry),
                    'status' => $this->load->model('localisation/invoice')->getInvoiceStatus(
                        $invoice->getStatusId(),
                        $this->session->data['language_id']),
                    'subtotal' => $this->getCurrency()->format($invoice->getSubtotal()),
                    'total' => $this->getCurrency()->format($invoice->getTotalCustomerCurrency(), $this->getCurrency()->getCode(), 1),
                    'transaction' => $invoice->getStatusId() == IS_PAID ? Transaction::getTransactionByInvoiceId($invoice->getId()) : null,
                    'weight' => $invoice->getWeight(),
                    'package_number' => $invoice->getPackageNumber()
                );
            }
        }
        else
            $this->data['textNoItems'] = $this->language->get('NO_ITEMS');

        /// Initialize interface values
        $this->data['buttonView'] = $this->language->get('VIEW');
        $this->data['textDateAdded'] = $this->language->get('TIME_ADDED');
        $this->data['textInvoiceId'] = $this->language->get('INVOICE_ID');
        $this->data['textItemsCount'] = $this->language->get('textItemsCount');
        $this->data['textShippingMethod'] = $this->language->get('textShippingMethod');
        $this->data['textTotal'] = $this->language->get('textTotal');
        $this->data['textTransaction'] = $this->language->get('TRANSACTION');

        $this->load->language('account/invoice');
        $this->data['textPackage'] = $this->language->get('textPackage');

        $templateName = '/template/account/invoiceList.tpl';
        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . $templateName))
            $this->template = $this->config->get('config_template') . $templateName;
        else
            $this->template = 'default' . $templateName;
        $this->setBreadcrumbs();
        $this->children = array(
            'common/header',
            'common/footer',
            'common/content_top',
            'common/content_bottom',
            'common/column_left',
            'common/column_right'
        );
        $this->response->setOutput($this->render());
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
            'text'      => $this->language->get('textAccount'),
            'href'      => $this->url->link('account/account', '', 'SSL'),
            'separator' => ' :: '
        );
        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('headingTitle'),
            'href' => '',
            'separator' => ' :: '
        );
    }

    public function showForm()
    {
//        $this->log->write(print_r($this->session, true));
        if (!isset($this->request->request['invoiceId']))
            return;

        $modelOrderItem = $this->load->model('account/order_item');
        $modelReferenceAddress = $this->load->model('reference/address');
        $invoice = InvoiceDAO::getInstance()->getInvoice($this->request->request['invoiceId']);

        /// Initialize interface values
        $this->data['headingTitle'] = sprintf($this->language->get('INVOICE'), $invoice->getId());
        $this->data['button_action'] = $this->language->get('button_close');
        $this->data['submit_action'] = $this->url->link('account/invoice/close', '', 'SSL');
        $this->data['textComment'] = $this->language->get('textComment');
        $this->data['textConfirm'] = $this->language->get('CONFIRM');
        $this->data['textGrandTotal'] = $this->language->get('textGrandTotal');
        $this->data['textItemImage'] = $this->language->get('textItemImage');
        $this->data['textItemName'] = $this->language->get('textItemName');
        $this->data['textOrderId'] = $this->language->get('textOrderId');
        $this->data['textOrderItemId'] = $this->language->get('textOrderItemId');
        $this->data['textPackageNumber'] = $this->language->get('PACKAGE_NUMBER');
        $this->data['textPrice'] = $this->language->get('textPrice');
        $this->data['textQuantity'] = $this->language->get('textQuantity');
        $this->data['textShippingAddress'] = $this->language->get('textShippingAddress');
        $this->data['textShippingCost'] = $this->language->get('textShippingCost');
        $this->data['textShippingMethod'] = $this->language->get('textShippingMethod');
        $this->data['textSubtotal'] = $this->language->get('SUBTOTAL');
        $this->data['textTotal'] = $this->language->get('TOTAL');
        $this->data['textWeight'] = $this->language->get('WEIGHT');

        if ($invoice->getCustomer()['customer_id'] != $this->getCustomer()->getId()) {
            $invoice = null;
            $this->data['notifications']['error'] = $this->language->get('errorAccessDenied');
        } else {
            /// Prepare list
            foreach ($invoice->getOrderItems() as $orderItem) {
//                $orderItem = $modelOrderItem->getOrderItem($invoiceItem['order_item_id']);
//                $this->log->write(print_r($orderItem, true));
                $this->data['orderItems'][] = array(
                    'id' => $orderItem->getId(),
                    'comment' => $orderItem->getPublicComment(),
                    'image_path' => $this->registry->get('model_tool_image')->getImage($orderItem->getImagePath()),
                    'model' => $orderItem->getModel(),
                    'name' => $orderItem->getName(),
                    'options' => $modelOrderItem->getOrderItemOptionsString($orderItem->getId()),
                    'order_id' => $orderItem->getOrderId(),
                    'price' => $this->getCurrency()->format($orderItem->getPrice()),
                    'quantity' => $orderItem->getQuantity(),
                    'subtotal' => $this->getCurrency()->format($orderItem->getPrice() * $orderItem->getQuantity())
                );
            }
            /// Set invoice data
            $this->data['invoiceId'] = $invoice->getId();
            $this->data['packageNumber'] = $invoice->getPackageNumber();
            $this->data['shippingAddress'] = nl2br($modelReferenceAddress->toString($invoice->getShippingAddressId()));
            $this->data['shippingCost'] = $this->getCurrency()->format($invoice->getShippingCost());
            $this->data['shippingMethod'] =\Shipping::getName($invoice->getShippingMethod(), $this->registry);
            $this->data['status'] = $this->load->model('localisation/invoice')->getInvoiceStatus(
                $invoice->getStatusId(),
                $this->session->data['language_id']);
            $this->data['statusId'] = $invoice->getStatusId();
            $this->data['total'] = $this->getCurrency()->format($invoice->getSubtotal());
            $this->data['totalWeight'] = $invoice->getWeight();
            $this->data['grandTotal'] = $this->getCurrency()->format($invoice->getTotalCustomerCurrency(), $this->getCustomer()->getBaseCurrency(), 1);
        }
        $templateName = '/template/account/invoiceForm.tpl';
        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . $templateName))
            $this->template = $this->config->get('config_template') . $templateName;
        else
            $this->template = 'default' . $templateName;
        $this->setBreadcrumbs();
        $this->children = array(
            'common/header',
            'common/footer',
            'common/content_top',
            'common/content_bottom',
            'common/column_left',
            'common/column_right'
        );
        $this->response->setOutput($this->render());
    }
}
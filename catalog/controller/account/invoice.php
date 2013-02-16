<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dev
 * Date: 18.7.12
 * Time: 21:43
 * To change this template use File | Settings | File Templates.
 */
class ControllerAccountInvoice extends Controller
{
    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->load->language('account/invoice');
        $this->load->library('Transaction');
        $this->load->model('reference/address');
        $this->load->model('account/invoice');
        $this->load->model('account/order');
        $this->load->model('account/order_item');
        $this->load->model('tool/image');

        $this->data['notifications'] = array();
        $this->document->setTitle($this->language->get('headingTitle'));
        $this->data['headingTitle'] = $this->language->get('headingTitle');
    }

    public function confirm()
    {
        $this->log->write('Starting');
        if (!isset($this->request->request['invoiceId']))
            $json['error'] = "Unexpected error";
        else
        {
            Transaction::addPayment(
                $this->customer->getId(),
                $this->request->request['invoiceId'],
                $this->registry);
            $invoice = $this->load->model('account/invoice')->getInvoice($this->request->request['invoiceId']);
            $json['newStatus'] = $this->load->model('localisation/invoice')->getInvoiceStatus(
                $invoice['invoice_status_id'],
                $this->session->data['language_id']
            );
        }
        $this->log->write(print_r($json, true));
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
        $modelAccountInvoice = $this->registry->get('model_account_invoice');
        //$modelSaleCustomer = $this->load->model('sale/customer');
        $invoices = $modelAccountInvoice->getInvoices($this->customer->getId());
        if ($invoices)
        {
            foreach ($invoices as $invoice)
            {
                $action = array();
                $action[] = array(
                    'text' => $this->language->get('textView'),
                    'href' => $this->url->link('sale/invoice/showForm', 'invoiceId=' . $invoice['invoice_id'], 'SSL')
                );
                $this->data['invoices'][] = array(
                    'invoiceId' => $invoice['invoice_id'],
                    'action' => $action,
                    'customer' => $this->customer->getLastName() . ' ' . $this->customer->getFirstname(),
                    'timeModified' =>$invoice['time_modified'],
                    'href' => $this->url->link('account/invoice/showForm', 'invoiceId=' . $invoice['invoice_id'], 'SSL'),
                    'itemsCount' => $modelAccountInvoice->getInvoiceItemsCount($invoice['invoice_id']),
                    'shippingCost' => $this->currency->format($invoice['shipping_cost']),
                    'shippingMethod' => Shipping::getName($invoice['shipping_method'], $this->registry),
                    'status' => $this->load->model('localisation/invoice')->getInvoiceStatus(
                        $invoice['invoice_status_id'],
                        $this->session->data['language_id']),
                    'subtotal' => $this->currency->format($invoice['subtotal']),
                    'total' => $this->currency->format($invoice['total']),
                    'transaction' => $invoice['invoice_status_id'] == IS_PAID ? Transaction::getTransactionByInvoiceId($invoice['invoice_id']) : null,
                    'weight' => $invoice['weight']
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

    private function setBreadcrumbs()
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
        $this->log->write(print_r($this->session, true));
        if (!isset($this->request->request['invoiceId']))
            return;

        $modelInvoice = $this->load->model('account/invoice');
        $modelOrderItem = $this->load->model('account/order_item');
        $modelReferenceAddress = $this->load->model('reference/address');
        $invoice = $modelInvoice->getInvoice($this->request->request['invoiceId']);

        /// Initialize interface values
        $this->data['headingTitle'] = sprintf($this->language->get('INVOICE'), $invoice['invoice_id']);
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

        if ($invoice['customer_id'] != $this->customer->getId())
        {
            $invoice = null;
            $this->data['notifications']['error'] = $this->language->get('errorAccessDenied');
        }
        else
        {
            /// Prepare list
            //$this->log->write(print_r($modelSaleInvoice->getInvoiceItems($invoice['invoice_id']), true));
            foreach ($modelInvoice->getInvoiceItems($invoice['invoice_id']) as $invoiceItem)
            {
                $orderItem = $modelOrderItem->getOrderItem($invoiceItem['order_item_id']);
                //$this->log->write(print_r($orderItem, true));
                $this->data['orderItems'][] = array(
                    'id' => $orderItem['order_product_id'],
                    'comment' => $orderItem['comment'],
                    'image_path' => $this->registry->get('model_tool_image')->getImage($orderItem['image_path']),
                    'model' => $orderItem['model'],
                    'name' => $orderItem['name'],
                    'options' => $modelOrderItem->getOrderItemOptionsString($orderItem['order_item_id']),
                    'order_id' => $orderItem['order_id'],
                    'price' => $this->currency->format($orderItem['price']),
                    'quantity' => $orderItem['quantity'],
                    'subtotal' => $this->currency->format($orderItem['price'] * $orderItem['quantity'])
                );
            }
            /// Set invoice data
            $this->data['invoiceId'] = $invoice['invoice_id'];
            $this->data['packageNumber'] = $invoice['package_number'];
            $this->data['shippingAddress'] = nl2br($modelReferenceAddress->toString($invoice['shipping_address_id']));
            $this->data['shippingCost'] = $this->currency->format($invoice['shipping_cost']);
            $this->data['shippingMethod'] = Shipping::getName($invoice['shipping_method'], $this->registry);
            $this->data['status'] = $this->load->model('localisation/invoice')->getInvoiceStatus(
                $invoice['invoice_status_id'],
                $this->session->data['language_id']);
            $this->data['statusId'] = $invoice['invoice_status_id'];
            $this->data['total'] = $this->currency->format($invoice['subtotal']);
            $this->data['totalWeight'] = $invoice['weight'];
            $this->data['grandTotal'] = $this->currency->format($invoice['total']);
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
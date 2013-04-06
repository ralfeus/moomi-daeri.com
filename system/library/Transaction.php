<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dev
 * Date: 27.7.12
 * Time: 15:05
 * To change this template use File | Settings | File Templates.
 */
include_once("ILibrary.php");
class Transaction extends OpenCartBase implements ILibrary
{
    private static $instance;
    private $modelTransaction;

    protected function __construct($registry)
    {
        parent::__construct($registry);
        $this->modelTransaction = $this->load->model('sale/transaction', 'admin');
    }

    public static function addCredit($customerId, $amount, $currency, $registry, $description = "")
    {
//        Transaction::$instance->log->write("Starting");
        $modelCustomer = Transaction::$instance->load->model('sale/customer', 'admin');
        $customer = $modelCustomer->getCustomer($customerId);
//        Transaction::$instance->log->write("Adding transaction");
        Transaction::addTransaction(0, $customerId, -$amount, $currency, $description);

        /// Try to pay all payment awaiting invoices
        $modelInvoice = Transaction::$instance->load->model('sale/invoice', 'admin');
        $invoices = $modelInvoice->getInvoices(array(
            "filterCustomerId" => array((int)$customerId),
            "filterInvoiceStatusId" => array(IS_AWAITING_PAYMENT))
        );
        if ($invoices)
            foreach ($invoices as $invoice)
            {
                Transaction::addPayment($customerId, $invoice['invoice_id'], $registry);
            }
    }

    public static function addPayment($customerId, $invoiceId, $registry, $description = "")
    {
        Transaction::$instance->log->write("Starting");
        $modelCustomer = Transaction::$instance->load->model('sale/customer', 'admin');
        $modelInvoice = Transaction::$instance->load->model('sale/invoice', 'admin');
        $currency = $registry->get('currency');
        $config = $registry->get('config');
        $customer = $modelCustomer->getCustomer($customerId);
        $invoice = $modelInvoice->getInvoice($invoiceId);
        $transactionAmount = $currency->convert($invoice['total'], $config->get('config_currency'), $customer['base_currency_code']);
        if (($customer['balance'] < $transactionAmount) && !$customer['allow_overdraft'])
            $modelInvoice->setInvoiceStatus($invoiceId, IS_AWAITING_PAYMENT);
        else
        {
            Transaction::addTransaction($invoiceId, $customerId, $invoice['total'], $config->get('config_currency'), $description);
            $modelInvoice->setInvoiceStatus($invoiceId, IS_PAID);
        }
    }

    public static function addTransaction($invoiceId, $customer, $amount, $currency_code, $description = '')
    {
        if (is_numeric($customer)) /// customer ID is passed. Need to get whole customer
        {
            $modelCustomer = Transaction::$instance->load->model('sale/customer', 'admin');
            $customer = $modelCustomer->getCustomer($customer);

        }

        /// Now need to convert transaction amount to customer base currency
        $currency = Transaction::$instance->registry->get('currency');
        $amountInCustomerCurrency = (float)$currency->convert($amount, $currency_code, $customer['base_currency_code']);
        $newCustomerBalance = $customer['balance'] - $amountInCustomerCurrency;
        Transaction::$instance->db->query("
            INSERT INTO " . DB_PREFIX . "customer_transaction
            SET
                customer_id = " . (int)$customer['customer_id'] . ",
                invoice_id = " . (int)$invoiceId . ",
                description = '" . Transaction::$instance->db->escape($description) . "',
                amount = $amountInCustomerCurrency,
                currency_code = '" . $customer['base_currency_code'] . "',
                date_added = NOW(),
                balance = $newCustomerBalance
        ");
        $transactionId = Transaction::$instance->db->getLastId();
        /// Update customer's balance
        Transaction::$instance->db->query("
                UPDATE " . DB_PREFIX . "customer
                SET
                    balance = $newCustomerBalance
                WHERE customer_id = " . $customer['customer_id']
        );
    }

    public static function deleteTransaction($transactionId)
    {
        Transaction::$instance->modelTransaction->deleteTransaction($transactionId);
    }

    public static function getInstance($registry)
    {
//        $registry->get('log')->write("Starting");
        if (!isset(Transaction::$instance))
            Transaction::$instance = new Transaction($registry);
        return Transaction::$instance;
    }

    public static function getTransactionByInvoiceId($invoiceId = 0)
    {
        return Transaction::$instance->modelTransaction->getTransactionByInvoiceId($invoiceId);
    }
}

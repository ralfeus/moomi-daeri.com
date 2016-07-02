<?php
use model\sale\CustomerDAO;
use model\sale\InvoiceDAO;

include_once("ILibrary.php");
class Transaction extends OpenCartBase implements ILibrary {
    /** @var  Transaction */
    private static $instance;
    /** @var  ModelSaleTransaction */
    private $modelTransaction;

    protected function __construct($registry) {
        parent::__construct($registry);
        $this->modelTransaction = $this->load->model('sale/transaction', 'admin');
    }

    public static function addCredit($customerId, $amount, $currency, $registry, $description = "")
    {
//        Transaction::$instance->log->write("Starting");
//        $customer = CustomerDAO::getInstance()->getCustomer($customerId);
//        Transaction::$instance->log->write("Adding transaction");
        Transaction::addTransaction(0, $customerId, -$amount, $currency, $description);

        /// Try to pay all payment awaiting invoices
        $invoices = InvoiceDAO::getInstance()->getInvoices(array(
            "filterCustomerId" => array((int)$customerId),
            "filterInvoiceStatusId" => array(IS_AWAITING_PAYMENT))
        );
        if ($invoices)
            foreach ($invoices as $invoice)
            {
                Transaction::addPayment($customerId, $invoice['invoice_id'], $registry);
            }
    }

    /**
     * @param int $customerId
     * @param int $invoiceId
     * @param Registry $registry
     * @param string $description
     */
    public static function addPayment($customerId, $invoiceId, $registry, $description = "") {
        Transaction::$instance->log->write("Starting");
        $customer = CustomerDAO::getInstance()->getCustomer($customerId);
        $invoice = InvoiceDAO::getInstance()->getInvoice($invoiceId);
        $transactionAmount = $invoice->getTotalCustomerCurrency();
        if (($customer['balance'] < $transactionAmount) && !$customer['allow_overdraft']) {
            InvoiceDAO::getInstance()->setInvoiceStatus($invoiceId, IS_AWAITING_PAYMENT);
        } else {
        	$temp = $invoice->getCustomer();
            Transaction::addTransaction(
                $invoiceId,
                $customerId,
                $invoice->getTotalCustomerCurrency(),
                $temp['base_currency_code'],
                $description
            );
            InvoiceDAO::getInstance()->setInvoiceStatus($invoiceId, IS_PAID);
        }
    }

    public static function addTransaction($invoiceId, $customer, $amount, $currency_code, $description = '') {
        if (is_numeric($customer)) { /// customer ID is passed. Need to get customer object
            $customer = CustomerDAO::getInstance()->getCustomer($customer);
        }

        /// Now need to convert transaction amount to customer base currency
        $currency = Transaction::$instance->registry->get('currency');
        $amountInCustomerCurrency = (float)$currency->convert($amount, $currency_code, $customer['base_currency_code']);
        $newCustomerBalance = $customer['balance'] - $amountInCustomerCurrency;
        Transaction::$instance->db->query("
            INSERT INTO customer_transaction
            SET
                customer_id = " . (int)$customer['customer_id'] . ",
                invoice_id = " . (int)$invoiceId . ",
                description = '" . Transaction::$instance->db->escape($description) . "',
                amount = $amountInCustomerCurrency,
                currency_code = '" . $customer['base_currency_code'] . "',
                date_added = NOW(),
                balance = $newCustomerBalance,
                balance_currency = '" . Transaction::$instance->db->escape($customer['base_currency_code']) . "'
        ");
        $transactionId = Transaction::$instance->db->getLastId();
        /// Update customer's balance
        Transaction::$instance->db->query("
                UPDATE customer
                SET
                    balance = $newCustomerBalance
                WHERE customer_id = " . $customer['customer_id']
        );
        if (Transaction::$instance->user->isLogged()) {
            Audit::getInstance(Transaction::$instance->getRegistry())->addAdminEntry(Transaction::$instance->user->getId(), AUDIT_ADMIN_TRANSACTION_ADD, $_REQUEST);
        } elseif (Transaction::$instance->customer->isLogged()) {
            Audit::getInstance(Transaction::$instance->getRegistry())->addUserEntry(Transaction::$instance->customer->getId(), AUDIT_ADMIN_TRANSACTION_ADD, $_REQUEST);
        }
    }

    public static function deleteTransaction($transactionId) {
        $transaction = Transaction::$instance->getTransaction($transactionId);
//        Transaction::$instance->modelTransaction->deleteTransaction($transactionId);
        Transaction::$instance->addTransaction(
            $transaction['invoice_id'],
            $transaction['customer_id'],
            -$transaction['amount'],
            $transaction['currency_code'],
            'Cancellation of transaction #' . (int)$transactionId
        );
    }

    public static function getInstance($registry)
    {
//        $registry->get('log')->write("Starting");
        if (!isset(Transaction::$instance))
            Transaction::$instance = new Transaction($registry);
        return Transaction::$instance;
    }

    private function getTransaction($transactionId) {
        return Transaction::$instance->modelTransaction->getTransaction($transactionId);
    }

    public static function getTransactionByInvoiceId($invoiceId = 0)
    {
        return Transaction::$instance->modelTransaction->getTransactionByInvoiceId($invoiceId);
    }
}

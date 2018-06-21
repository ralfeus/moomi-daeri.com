<?php
namespace system\library;

use model\DAO;
use model\sale\CustomerDAO;
use model\sale\InvoiceDAO;
use ModelSaleTransaction;
use system\engine\Registry;

class Transaction extends DAO {
    /** @var  ModelSaleTransaction */
    private $modelTransaction;

    public function __construct($registry) {
        parent::__construct($registry);
        $this->modelTransaction = $this->getLoader()->model('sale/transaction', 'admin');
    }

    public function addCredit($customerId, $amount, $currency, $registry, $description = "") {
//        $this->log->write("Starting");
//        $customer = CustomerDAO::getInstance()->getCustomer($customerId);
//        $this->log->write("Adding transaction");
        $this->addTransaction(0, $customerId, -$amount, $currency, $description);

        /// Try to pay all payment awaiting invoices
        $invoices = InvoiceDAO::getInstance()->getInvoices(array(
                "filterCustomerId" => array((int)$customerId),
                "filterInvoiceStatusId" => array(IS_AWAITING_PAYMENT))
        );
        if ($invoices)
            foreach ($invoices as $invoice) {
                Transaction::addPayment($customerId, $invoice['invoice_id'], $registry);
            }
    }

    /**
     * @param int $customerId
     * @param int $invoiceId
     * @param Registry $registry
     * @param string $description
     */
    public function addPayment($customerId, $invoiceId, $registry, $description = "") {
        $this->log->write("Starting");
        $customer = CustomerDAO::getInstance()->getCustomer($customerId);
        $invoice = InvoiceDAO::getInstance()->getInvoice($invoiceId);
        $transactionAmount = $invoice->getTotalCustomerCurrency();
        if (($customer['balance'] < $transactionAmount) && !$customer['allow_overdraft']) {
            InvoiceDAO::getInstance()->setInvoiceStatus($invoiceId, IS_AWAITING_PAYMENT);
        } else {
            $temp = $invoice->getCustomer();
            $this->addTransaction(
                $invoiceId,
                $customerId,
                $invoice->getTotalCustomerCurrency(),
                $temp['base_currency_code'],
                $description
            );
            InvoiceDAO::getInstance()->setInvoiceStatus($invoiceId, IS_PAID);
        }
    }

    public function addTransaction($invoiceId, $customer, $amount, $currency_code, $description = '') {
        if (is_numeric($customer)) { /// customer ID is passed. Need to get customer object
            $customer = CustomerDAO::getInstance()->getCustomer($customer);
        }

        /// Now need to convert transaction amount to customer base currency
        $currency = $this->getRegistry()->get('currency');
        $amountInCustomerCurrency = (float)$currency->convert($amount, $currency_code, $customer['base_currency_code']);
        $newCustomerBalance = $customer['balance'] - $amountInCustomerCurrency;
        $this->getDb()->query("
            INSERT INTO customer_transaction
            SET
                customer_id = " . (int)$customer['customer_id'] . ",
                invoice_id = " . (int)$invoiceId . ",
                description = '" . $this->getDb()->escape($description) . "',
                amount = $amountInCustomerCurrency,
                currency_code = '" . $customer['base_currency_code'] . "',
                date_added = NOW(),
                balance = $newCustomerBalance,
                balance_currency = '" . $this->getDb()->escape($customer['base_currency_code']) . "'
        ");
        //$transactionId = $this->getDb()->getLastId();
        /// Update customer's balance
        $this->getDb()->query("
                UPDATE customer
                SET
                    balance = $newCustomerBalance
                WHERE customer_id = " . $customer['customer_id']
        );
        $this->getCache()->delete('customer.' . $customer['customer_id']);
        if ($this->user->isLogged()) {
            Audit::getInstance($this->getRegistry())->addAdminEntry($this->user->getId(), AUDIT_ADMIN_TRANSACTION_ADD, $_REQUEST);
        } elseif ($this->customer->isLogged()) {
            Audit::getInstance($this->getRegistry())->addUserEntry($this->customer->getId(), AUDIT_ADMIN_TRANSACTION_ADD, $_REQUEST);
        }
    }

    public function deleteTransaction($transactionId) {
        $transaction = $this->getTransaction($transactionId);
//        $this->modelTransaction->deleteTransaction($transactionId);
        $this->addTransaction(
            $transaction['invoice_id'],
            $transaction['customer_id'],
            -$transaction['amount'],
            $transaction['currency_code'],
            'Cancellation of transaction #' . (int)$transactionId
        );
    }

    public function getTransaction($transactionId) {
        return $this->modelTransaction->getTransaction($transactionId);
    }

    public function getTransactionByInvoiceId($invoiceId = 0) {
        return $this->modelTransaction->getTransactionByInvoiceId($invoiceId);
    }
}

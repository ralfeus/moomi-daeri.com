<?php
namespace model\sale;

use model\DAO;
use system\library\Audit;

/**
 * Created by JetBrains PhpStorm.
 * User: dev
 * Date: 26.7.12
 * Time: 22:46
 * To change this template use File | Settings | File Templates.
 */
class TransactionDAO extends DAO {
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
        $transactionId = $this->getDb()->getLastId();
        /// Update customer's balance
        $this->getDb()->query("
                UPDATE customer
                SET
                    balance = $newCustomerBalance
                WHERE customer_id = " . $customer['customer_id']
        );
        $this->getCache()->delete('customer.' . $customer['customer_id']);
        if (isset($this->user) && $this->user->isLogged()) {
            Audit::getInstance($this->getRegistry())->addAdminEntry($this->user->getId(), AUDIT_ADMIN_TRANSACTION_ADD, $_REQUEST);
        } elseif (isset($this->customer) && $this->customer->isLogged()) {
            Audit::getInstance($this->getRegistry())->addUserEntry($this->customer->getId(), AUDIT_ADMIN_TRANSACTION_ADD, $_REQUEST);
        }
        return $transactionId;
    }
//    public function addTransaction($invoiceId, $customerId, $amount, $currency_code, $description = '') {
////        $this->log->write("Adding transaction");
////        $this->log->write($description);
//
//        $this->getDb()->query("
//            INSERT INTO customer_transaction
//            SET
//                customer_id = " . (int)$customerId . ",
//                invoice_id = " . (int)$invoiceId . ",
//                description = '" . $this->getDb()->escape($description) . "',
//                amount = " . (float)$amount . ",
//                currency_code = '" . $this->getDb()->escape($currency_code) . "',
//                date_added = NOW()
//        ");
////        $transactionId = $this->getDb()->getLastId();
//        /// Update customer's balance
//        $this->getDb()->query("
//                UPDATE customer
//                SET
//                    balance = balance - " . (float)$amount . "
//                WHERE customer_id = " . (int)$customerId
//        );
//        $this->getCache()->delete('customer.' . $customerId);
//    }
//
    /**
     * @param int $transactionId
     */
//    public function deleteTransaction($transactionId) {
//        $transaction = $this->getTransaction($transactionId);
//        $customer = CustomerDAO::getInstance()->getCustomer($transaction['customer_id']);
//        $amountToReturn = $this->getCurrentCurrency()->convert($transaction['amount'], $transaction['currency_code'], $customer['base_currency_code']);
//        $this->getDb()->query("
//            DELETE FROM customer_transaction
//            WHERE customer_transaction_id = " . (int)$transactionId
//        );
//        $this->getDb()->query("
//            UPDATE customer
//            SET balance = balance + $amountToReturn
//            WHERE customer_id = " . $transaction['customer_id']
//        );
//
//        $this->getCache()->delete('customer.' . $transaction['customer_id']);
//    }
    /**
     * Creates a transaction cancelling provided one
     * @param $transactionId
     */
    public function deleteTransaction($transactionId) {
        $transaction = TransactionDAO::getInstance()->getTransaction($transactionId);
//        $this->modelTransaction->deleteTransaction($transactionId);
        $this->addTransaction(
            $transaction['invoice_id'],
            $transaction['customer_id'],
            -$transaction['amount'],
            $transaction['currency_code'],
            'Cancellation of transaction #' . (int)$transactionId
        );
    }

    /**
     * @param int $transactionId
     * @return array Associated array with transaction data
     */
    public function getTransaction($transactionId) {
        $query = $this->getDb()->query("
            SELECT *
            FROM customer_transaction
            WHERE customer_transaction_id = " . (int)$transactionId
        );

        if ($query->num_rows)
            return $query->row;
        else
            return null;
    }

    public function getTransactionByInvoiceId($invoiceId) {
        $query = $this->getDb()->query("
            SELECT *
            FROM customer_transaction
            WHERE invoice_id = " . (int)$invoiceId
        );

        if ($query->num_rows)
            return $query->row;
        else
            return null;
    }

    /**
     * @param $customerId
     * @param $sortColumn
     * @param $sortOrder
     * @param $start
     * @param $limit
     * @return array
     */
    public function getTransactions($customerId, $sortColumn = 'date_added', $sortOrder = 'DESC', $start = 0, $limit = 20) {
        $sortData = array(
            'amount',
            'customer_transaction_id',
            'description',
            'date_added'
        );
        $sortColumn = in_array($sortColumn, $sortData) ? $sortColumn : 'date_added';
        $sortOrder = $sortOrder == 'ASC' ? 'ASC' : 'DESC';
        $limit = $this->buildLimitString($start, $limit);
        $query = $this->getDb()->query("
            SELECT *
            FROM customer_transaction
            WHERE customer_id = :customerId
            ORDER BY $sortColumn $sortOrder
            $limit
        ", [
            ':customerId' => $customerId
        ]);

        if ($query->num_rows) {
            return $query->rows;
        } else {
            return null;
        }
    }

    public function getTransactionsCount($customerId) {
        $result = $this->getDb()->queryScalar("
            SELECT COUNT(*) AS total 
            FROM `customer_transaction` 
            WHERE customer_id = :customerId
        ", [
            ':customerId' => $customerId
        ]);

        return $result;
    }


    /**
     * @param int $customerId
     * @param int $invoiceId
     * @param string $description
     */
    public function addPayment($customerId, $invoiceId, $description = "") {
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

    /**
     * Adds credit to customer's deposit
     * @param $customerId
     * @param $amount
     * @param $currency
     * @param string $description
     */
    public function addCredit($customerId, $amount, $currency, $description = "") {
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
                $this->addPayment($customerId, $invoice['invoice_id']);
            }
    }
}

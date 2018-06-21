<?php
namespace model\sale;

use model\DAO;

/**
 * Created by JetBrains PhpStorm.
 * User: dev
 * Date: 26.7.12
 * Time: 22:46
 * To change this template use File | Settings | File Templates.
 */
class TransactionDAO extends DAO {
    public function addTransaction($invoiceId, $customerId, $amount, $currency_code, $description = '') {
//        $this->log->write("Adding transaction");
//        $this->log->write($description);
        $this->getDb()->query("
            INSERT INTO customer_transaction
            SET
                customer_id = " . (int)$customerId . ",
                invoice_id = " . (int)$invoiceId . ",
                description = '" . $this->getDb()->escape($description) . "',
                amount = " . (float)$amount . ",
                currency_code = '" . $this->getDb()->escape($currency_code) . "',
                date_added = NOW()
        ");
//        $transactionId = $this->getDb()->getLastId();
        /// Update customer's balance
        $this->getDb()->query("
                UPDATE customer
                SET
                    balance = balance - " . (float)$amount . "
                WHERE customer_id = " . (int)$customerId
        );
        $this->getCache()->delete('customer.' . $customerId);
    }

    /**
     * @param int $transactionId
     */
    public function deleteTransaction($transactionId) {
        $transaction = $this->getTransaction($transactionId);
        $customer = CustomerDAO::getInstance()->getCustomer($transaction['customer_id']);
        $amountToReturn = $this->getCurrentCurrency()->convert($transaction['amount'], $transaction['currency_code'], $customer['base_currency_code']);
        $this->getDb()->query("
            DELETE FROM customer_transaction
            WHERE customer_transaction_id = " . (int)$transactionId
        );
        $this->getDb()->query("
            UPDATE customer
            SET balance = balance + $amountToReturn
            WHERE customer_id = " . $transaction['customer_id']
        );

        $this->getCache()->delete('customer.' . $transaction['customer_id']);
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

    public function getTransactions($customerId) {
        $query = $this->getDb()->query("
            SELECT *
            FROM customer_transaction
            WHERE customer_id = " . (int)$customerId
        );
        if ($query->num_rows)
            return $query->rows;
        else
            return null;
    }
}

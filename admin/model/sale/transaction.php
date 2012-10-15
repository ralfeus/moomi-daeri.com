<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dev
 * Date: 26.7.12
 * Time: 22:46
 * To change this template use File | Settings | File Templates.
 */
class ModelSaleTransaction extends Model
{
    public function addTransaction($invoiceId, $customerId, $amount, $currency_code, $description = '')
    {
//        $this->log->write("Adding transaction");
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "customer_transaction
            SET
                customer_id = " . (int)$customerId . ",
                invoice_id = " . (int)$invoiceId . ",
                description = '" . $this->db->escape($description) . "',
                amount = " . (float)$amount . ",
                currency_code = '" . $this->db->escape($currency_code) . "',
                date_added = NOW()
        ");
        $transactionId = $this->db->getLastId();
        /// Update customer's balance
        $this->db->query("
                UPDATE " . DB_PREFIX . "customer
                SET
                    balance = balance - " . (float)$amount . "
                WHERE customer_id = " . (int)$customerId
        );
    }

    public function deleteTransaction($transactionId)
    {
        $transaction = $this->getTransaction($transactionId);
        $customer = $this->load->model('sale/customer')->getCustomer($transaction['customer_id']);
        $amountToReturn = $this->currency->convert($transaction['amount'], $transaction['currency_code'], $customer['base_currency_code']);
        $this->db->query("
            DELETE FROM " . DB_PREFIX . "customer_transaction
            WHERE customer_transaction_id = " . (int)$transactionId
        );
        $this->db->query("
            UPDATE " . DB_PREFIX . "customer
            SET balance = balance + $amountToReturn
            WHERE customer_id = " . $transaction['customer_id']
        );
    }

    public function getTransaction($transactionId)
    {
        $query = $this->db->query("
            SELECT *
            FROM " . DB_PREFIX . "customer_transaction
            WHERE customer_transaction_id = " . (int)$transactionId
        );

        if ($query->num_rows)
            return $query->row;
        else
            return null;
    }

    public function getTransactionByInvoiceId($invoiceId)
    {
        $query = $this->db->query("
            SELECT *
            FROM " . DB_PREFIX . "customer_transaction
            WHERE invoice_id = " . (int)$invoiceId
        );

        if ($query->num_rows)
            return $query->row;
        else
            return null;
    }

    public function getTransactions($customerId)
    {
        $query = $this->db->query("
            SELECT *
            FROM " . DB_PREFIX . "customer_transaction
            WHERE customer_id = " . (int)$customerId
        );
        if ($query->num_rows)
            return $query->rows;
        else
            return null;
    }
}

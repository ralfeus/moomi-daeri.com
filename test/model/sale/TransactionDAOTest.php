<?php
/**
 * Created by PhpStorm.
 * User: ralfeus
 * Date: 21.06.2018
 * Time: 10:57
 */

namespace model\sale;


use test\model\Test;

class TransactionDAOTest extends Test {
    /**
     * @test
     * @covers TransactionDAO::addTransaction()
     */
    public function addTransaction() {
        $customerId = 2;
        $customer = CustomerDAO::getInstance()->getCustomer($customerId);
        $customerBalance = $customer['balance'];
        $customerBaseCurrencyCode = $customer['base_currency_code'];
        $transactionAmount = 100;

        $transactionId = TransactionDAO::getInstance()->addTransaction(0, $customerId, $transactionAmount, $customerBaseCurrencyCode, "Test");
        $this->assertTrue(isset($transactionId));
        $customer = CustomerDAO::getInstance()->getCustomer($customerId);
        $this->assertEquals($customerBalance - $transactionAmount, $customer['balance']);
    }

    /**
     * @test
     * @covers TransactionDAO::getTransactions()
     */
    public function getTransactions() {
        $customerId = 2;
        $transactions = TransactionDAO::getInstance()->getTransactions($customerId);
        //print_r($transactions);
        self::assertNotNull($transactions);
        self::assertEquals($customerId, $transactions[0]['customer_id']);
    }

    /**
     * @test
     * @covers TransactionDAO::getTransactionsCount()
     */
    public function getTransactionsCount() {
        $customerId = 2;
        $result = TransactionDAO::getInstance()->getTransactionsCount($customerId);
        self::assertTrue(!is_null($result));
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: dev
 * Date: 19.11.2014
 * Time: 23:59
 */

namespace test\model\sale;

use model\sale\CustomerDAO;
use PHPUnit\Framework\Error\Notice;

/**
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */
class CustomerDAOTest extends Test {
    /**
     * @test
     */
    public function getCustomer() {
        Notice::$enabled = false;
        $customerId = 54;
        $customer = CustomerDAO::getInstance()->getCustomer($customerId);
        $this->assertTrue(is_array($customer));
    }

    /*
     * @test
     */
    public function editToken() {
        $customerId = 54;
        $token = md5(mt_rand());
        CustomerDAO::getInstance()->editToken($customerId, $token);
        $customer = CustomerDAO::getInstance()->getCustomer($customerId);
        $this->assertEquals($token, $customer['token']);
    }
}
 
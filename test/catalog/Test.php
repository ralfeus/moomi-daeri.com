<?php
namespace test\catalog;

use Customer;
use model\sale\CustomerDAO;
use PHPUnit\Framework\Error\Notice;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Error\Warning;

abstract class Test extends TestCase {
    protected $registry;

    public function __construct($name = null, $data = array(), $dataName = '') {
        parent::__construct($name, $data, $dataName);
        require_once('/var/www/moomi-daeri.com/test/initTestEnvironment.php');
        $this->registry = initTestEnvironment('');

        Notice::$enabled = FALSE;
        Warning::$enabled = true;
    }

    protected function logIn() {
        $customerId = 54; // My customer ID
        $customer = CustomerDAO::getInstance()->getCustomer($customerId);

        if ($customer) {
            $token = md5(mt_rand());
            CustomerDAO::getInstance()->editToken($customerId, $token);
            $this->registry->get('session')->data['customer_id'] = $customerId;
            $customer = new \Customer($this->registry, '0.0.0.0');
            $this->registry->set('customer', $customer);
        }
    }
} 
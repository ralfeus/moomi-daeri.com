<?php
namespace test\admin\controller\sale;
use PHPUnit\Framework\Error\Notice;

/**
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */
class ControllerSaleOrderItemsTest extends AdminControllerSaleTest {
    public function __construct($name = null, $data = array(), $dataName = '') {
        parent::__construct($name, $data, $dataName);
        Notice::$enabled = false; //TODO: Remove after removing OpenCartBase::getCustomer()
        require_once(DIR_APPLICATION . "/controller/sale/order_items.php");
    }

    /**
     * @test
     * @covers ControllerSaleOrderItems::index
     */
    public function index() {
        $class = new \ControllerSaleOrderItems($this->registry);
        $class->index();
        $this->assertAttributeContains('>&gt;|</a>', 'output', runMethod($class, 'getResponse'));
    }
}
 
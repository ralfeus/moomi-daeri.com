<?php
namespace test\admin\controller\sale;
use model\sale\OrderItemDAO;
use PHPUnit\Framework\Error\Notice;


/**
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */
class ControllerSaleInvoiceTest extends AdminControllerSaleTest {
    public function __construct($name = null, $data = array(), $dataName = '') {
        parent::__construct($name, $data, $dataName);
        Notice::$enabled = false; //TODO: Remove after removing OpenCartBase::getCustomer()
        require_once(DIR_APPLICATION . "/controller/sale/invoice.php");
    }

    protected function setUp() {
        parent::setUp();
        unset($_GET);
        unset($_POST);
    }


    /**
     * @test
     * @covers ControllerSaleInvoice::index
     */
    public function index() {
        $this->class = new \ControllerSaleInvoice($this->registry);
        $this->class->index();
        $this->assertGreaterThan(0, strlen($this->readAttribute(runMethod($this->class, 'getResponse'), 'output')));
    }

    /**
     * @test
     * @covers ControllerSaleInvoice::showForm
     */
    public function showForm() {
        $orderItems = OrderItemDAO::getInstance()->getOrderItems(['start' => 0, 'limit' => 1], null, true);
        $_POST['selectedItems'] = array_map(
            function($item) {
                return $item->getId();
            },
            $orderItems
        );
        $_SERVER['REQUEST_METHOD'] = 'POST';
        try {
            $this->class = new \ControllerSaleInvoice($this->registry);
            $this->class->showForm();
        } catch (\Exception $exc) {
            $this->fail($exc->getMessage() . "\n" . $exc->getTraceAsString());
        }
    }
}
 
<?php
namespace test\admin\controller\module;

/**
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */
class AccountTest extends Test {
    public function __construct($name = null, $data = array(), $dataName = '') {
        parent::__construct($name, $data, $dataName);
        require_once(DIR_APPLICATION . "/controller/module/account.php");
    }

    /**
     * @test
     * @covers ControllerCatalogProduct::index
     */
    public function index() {
        $class = new \ControllerModuleAccount($this->registry);
        $class->index();
        $this->assertAttributeNotEmpty('output', runMethod($class, 'getResponse'));
    }
}
 
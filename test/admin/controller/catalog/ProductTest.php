<?php
namespace test\admin\controller\catalog;

/**
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */
class ProductTest extends Test {
    public function __construct($name = null, $data = array(), $dataName = '') {
        parent::__construct($name, $data, $dataName);
        require_once(DIR_APPLICATION . "/controller/catalog/product.php");
    }

    /**
     * @test
     * @covers ControllerCatalogProduct::index
     */
    public function index() {
        $class = new \ControllerCatalogProduct($this->registry, 'index');
        $class->index();
        $this->assertAttributeContains('>&gt;|</a>', 'output', runMethod($class, 'getResponse'));
    }

    /**
     * @test
     * @covers ControllerCatalogProduct::update
     */
    public function update() {
        $class = new \ControllerCatalogProduct($this->registry, 'update');
        $class->update();
        $this->assertAttributeNotEmpty('output', runMethod($class, 'getResponse'));
    }

    /**
     * @test
     * @covers ControllerCatalogProduct::delete
     */
    public function delete() {
        $class = $this->getMockBuilder("ControllerCatalogProduct")
                ->setConstructorArgs([$this->registry, 'delete'])
                ->setMethods(['redirect'])
                ->getMock();
        $class->expects($this->any())->method('redirect')->will($this->returnValue(null));
        $class->delete();
        $this->assertAttributeNotEmpty($class->session->data['success']);
    }
}
 
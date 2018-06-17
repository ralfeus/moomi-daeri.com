<?php
namespace test\admin\controller\catalog;

/**
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */
class AttributeTest extends Test {
    public function __construct($name = null, $data = array(), $dataName = '') {
        parent::__construct($name, $data, $dataName);
        require_once(DIR_APPLICATION . "/controller/catalog/attribute.php");
    }

    /**
     * @test
     * @covers ControllerCatalogProduct::index
     */
    public function index() {
        $class = new \ControllerCatalogAttribute($this->registry);
        $class->index();
        $this->assertAttributeNotEmpty('output', runMethod($class, 'getResponse'));
    }

    /**
     * @test
     * @covers ControllerCatalogProduct::update
     */
    public function update() {
        $class = new \ControllerCatalogAttribute($this->registry);
        $class->update();
        $this->assertAttributeNotEmpty('output', runMethod($class, 'getResponse'));
    }
}
 
<?php
/**
 * Created by PhpStorm.
 * User: dev
 * Date: 12.11.2014
 * Time: 15:40
 */

namespace test\catalog\controller\product;

/**
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */
class ControllerProductManufacturerTest extends CatalogControllerProductTest {
    public function __construct($name = null, $data = array(), $dataName = '') {
        parent::__construct($name, $data, $dataName );
    }
    /**
     * @test
     * @covers ControllerProductManufacturer::index
     */
    public function index() {
        $class = new \ControllerProductManufacturer($this->registry);
        $class->index();
    }
}
 
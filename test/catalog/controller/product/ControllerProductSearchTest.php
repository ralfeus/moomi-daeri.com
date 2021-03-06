<?php
/**
 * Created by PhpStorm.
 * User: dev
 * Date: 12.11.2014
 * Time: 15:40
 */

namespace test\catalog\controller\product;

use test\catalog\Test;

/**
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */
class ControllerProductSearchTest extends Test {
    public function __construct($name = null, $data = array(), $dataName = '') {
        parent::__construct($name, $data, $dataName );
    }
    /**
     * @test
     * @covers ControllerProductSearch::index
     */
    public function index() {
        $class = new \ControllerProductSearch($this->registry);
        $class->index();
        $this->assertTrue(true);
        self::assertAttributeNotEmpty('output', runMethod($class, 'getResponse'));
    }
}
 
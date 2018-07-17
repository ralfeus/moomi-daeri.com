<?php
/**
 * Created by PhpStorm.
 * User: dev
 * Date: 12.11.2014
 * Time: 15:40
 */

namespace test\catalog\controller\product;

use catalog\controller\common\Header;
use test\catalog\Test;

/**
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */
class ControllerCommonHeaderTest extends Test {
    public function __construct($name = null, $data = array(), $dataName = '') {
        parent::__construct($name, $data, $dataName );
    }
    /**
     * @test
     * @covers Header::index
     */
    public function index() {
        $class = new Header($this->registry);
        runMethod($class, 'index');
        self::assertNotEmpty(self::readAttribute($class, 'output'));
    }
}
 
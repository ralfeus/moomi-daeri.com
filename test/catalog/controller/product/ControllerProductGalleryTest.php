<?php
/**
 * Created by PhpStorm.
 * User: dev
 * Date: 12.11.2014
 * Time: 15:40
 */

namespace test\catalog\controller\product;
use PHPUnit\Framework\Error\Warning;
use test\catalog\Test;

/**
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */
class ControllerProductGalleryTest extends Test {
    public function __construct($name = null, $data = array(), $dataName = '') {
        parent::__construct($name, $data, $dataName );
    }
    /**
     * @test
     * @covers ControllerProductGallery::index
     */
    public function index() {
        Warning::$enabled = false;
        $class = new \ControllerProductGallery($this->registry);
        $class->index();
        Warning::$enabled = true;
        self::assertAttributeNotEmpty('output', runMethod($class, 'getResponse'));
    }
}
 
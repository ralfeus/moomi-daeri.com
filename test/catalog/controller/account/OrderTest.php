<?php
/**
 * Created by PhpStorm.
 * User: dev
 * Date: 12.11.2014
 * Time: 15:40
 */

namespace catalog\controller\account;

use ControllerAccountOrder;
use PHPUnit\Framework\Error\Notice;
use PHPUnit\Framework\Error\Warning;
use test\catalog\Test;

/**
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */
class OrderTest extends Test {
    public function __construct($name = null, $data = array(), $dataName = '') {
        parent::__construct($name, $data, $dataName );
    }

    /**
     * @test
     * @covers ControllerAccountOrder::index()
     * @expectedException \system\exception\NotLoggedInException
     */
    public function testIndexAnonymously() {
        $mock = new ControllerAccountOrder($this->registry);
        $mock->index();
    }

    /*
     * @test
     * @covers ControllerAccountOrder::index
    */
    public function testIndexLoggedIn() {
        Warning::$enabled = false;
        Notice::$enabled = false;
        $this->logIn();
        $class = new ControllerAccountOrder($this->registry);
        $class->index();
        self::assertAttributeNotEmpty('output', runMethod($class, 'getResponse'));
    }
}
 
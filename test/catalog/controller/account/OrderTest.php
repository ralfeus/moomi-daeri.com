<?php
/**
 * Created by PhpStorm.
 * User: dev
 * Date: 12.11.2014
 * Time: 15:40
 */

namespace test\catalog\controller\account;

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
     * @covers ControllerAccountOrder::index
     * @expectedException \Exception
     * @expectedExceptionMessage Not logged in
     */
    public function testIndexAnonymously() {
        $mockBuilder = $this->getMockBuilder('ControllerAccountOrder');
        $mockBuilder->setConstructorArgs(['redirect', $this->registry]);
        $mock = $mockBuilder->getMock();
        $mock->expects($this->never())->method('redirect');
        $mock->index();
    }

    /*
     * @test
     * @covers ControllerAccountOrder::index
    */
    public function testIndexLoggedIn() {
        $this->logIn();
        $mockBuilder = $this->getMockBuilder('ControllerAccountOrder');
        $mockBuilder->setConstructorArgs(['redirect', $this->registry]);
        $mock = $mockBuilder->getMock();
        $mock->expects($this->never())->method('redirect');
        $mock->index();
    }
}
 
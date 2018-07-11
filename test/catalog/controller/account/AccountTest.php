<?php
/**
 * Created by PhpStorm.
 * User: dev
 * Date: 12.11.2014
 * Time: 15:40
 */

namespace catalog\controller\account;
use ControllerAccountAccount;
use PHPUnit\Framework\Error\Warning;
use test\catalog\Test;

/**
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */
class AccountTest extends Test {
    /**
     * @test
     * @covers ControllerAccountAccount::index
     * @expectedException \system\exception\NotLoggedInException
     */
    public function testIndexAnonymously() {
        $mock = new ControllerAccountAccount($this->registry);
        $mock->index();
    }

    /*
     * @test
     * @covers ControllerAccountAccount::index
    */
    public function testIndexLoggedIn() {
        Warning::$enabled = false;
        $this->logIn();
        $class = new ControllerAccountAccount($this->registry);
        $class->index();
        self::assertAttributeNotEmpty('output', runMethod($class, 'getResponse'));
    }
}
 
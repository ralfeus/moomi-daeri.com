<?php
/**
 * Created by PhpStorm.
 * User: ralfeus
 * Date: 10.07.2018
 * Time: 16:37
 */

namespace catalog\controller\account;


use ControllerAccountTransaction;
use test\catalog\Test;


class ControllerAccountTransactionTest extends Test {
    /*
     * @test
     * @covers ControllerAccountAccount::index
    */
    public function testIndexLoggedIn() {
        $this->logIn();
        $class = new ControllerAccountTransaction($this->registry);
        $class->index();
        self::assertAttributeNotEmpty('output', runMethod($class, 'getResponse'));
    }
}

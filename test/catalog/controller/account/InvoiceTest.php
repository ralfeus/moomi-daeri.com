<?php
/**
 * Created by PhpStorm.
 * User: dev
 * Date: 12.11.2014
 * Time: 15:40
 */

namespace test\catalog\controller\account;
use ControllerAccountAccount;
use test\catalog\Test;

/**
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */
class InvoiceTest extends Test {
    public function __construct($name = null, $data = array(), $dataName = '') {
        parent::__construct($name, $data, $dataName );
    }

    /*
     * @test
     */
    public function testIndexLoggedIn() {
        $this->logIn();
        $class = new ControllerAccountAccount($this->registry);
        $class->index();
        self::assertAttributeNotEmpty('output', runMethod($class, 'getResponse'));
    }
}
 
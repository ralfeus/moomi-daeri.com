<?php
/**
 * Created by PhpStorm.
 * User: dev
 * Date: 12.11.2014
 * Time: 15:40
 */

namespace test\catalog\controller\account;
use ControllerAccountAccount;

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
        $this->class = new ControllerAccountAccount($this->registry);
        $this->class->index();
    }
}
 
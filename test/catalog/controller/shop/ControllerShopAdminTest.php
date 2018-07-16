<?php
/**
 * Created by PhpStorm.
 * User: ralfeus
 * Date: 16. 7. 2018
 * Time: 13:53
 */

namespace catalog\controller\shop;


use catalog\controller\shop\ControllerShopAdmin;
use test\catalog\Test;


class ControllerShopAdminTest extends Test {
    /**
     * @test
     * @covers ControllerShopAdmin::showPage()
     */
    public function showPage() {
        $class = new ControllerShopAdmin($this->registry);
        $class->showPage();
        self::assertAttributeNotEmpty('output', runMethod($class, 'getResponse'));
    }

    /**
     * @test
     * @covers ControllerShopAdmin::hasAction()()
     */
    public function hasAction() {
        $class = new ControllerShopAdmin($this->registry);
        $class->hasAction();
        self::assertTrue(true);
    }
}

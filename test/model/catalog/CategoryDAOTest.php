<?php
/**
 * Created by PhpStorm.
 * User: ralfeus
 * Date: 18. 7. 2018
 * Time: 5:52
 */

namespace model\catalog;


use test\model\Test;

class CategoryDAOTest extends Test {
    /**
     * @test
     * @covers CategoryDAO::getCategories()
     */
    public function getCategories() {
        $result = CategoryDAO::getInstance()->getCategories(0, 1);
        self::assertLessThan(100, sizeof($result));
        $result = CategoryDAO::getInstance()->getCategories();
        self::assertGreaterThan(100, sizeof($result));
    }
}

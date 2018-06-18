<?php
/**
 * Created by PhpStorm.
 * User: ralfeus
 * Date: 18.06.2018
 * Time: 18:48
 */

namespace model\catalog;


use test\model\Test;

class CategoryTest extends Test {
    /**
     * @test
     * @covers Category::getDescriptions()
     */
    public function getDescriptions() {
        $category = CategoryDAO::getInstance()->getCategory(88);
        $descriptions = $category->getDescriptions();
        $this->assertNotNull($descriptions);
    }
}

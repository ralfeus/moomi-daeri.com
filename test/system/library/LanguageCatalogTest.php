<?php
/**
 * Created by PhpStorm.
 * User: ralfeus
 * Date: 15.06.2018
 * Time: 22:14
 */

namespace system\library;

use test\system\Test;

class LanguageCatalogTest extends Test {
    /**
     * @test
     * @covers Language::load()
     */
    public function loadCatalog() {
        $language = new Language('chinese');
        $language->load('module/wkproduct_auction');
        self::assertTrue(!empty($language->get("heading_title")));
    }
}

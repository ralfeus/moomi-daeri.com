<?php
/**
 * Created by PhpStorm.
 * User: ralfeus
 * Date: 15.06.2018
 * Time: 22:14
 */

namespace system\library;

use PHPUnit\Framework\Error\Notice;
use PHPUnit\Framework\Error\Warning;
use PHPUnit\Framework\TestCase;
use test\library\Test;

require_once('/var/www/moomi-daeri.com/test/initTestEnvironment.php');

class LanguageCatalogTest extends Test {
    /**
     * @test
     * @covers Language::load()
     */
    public function loadCatalog() {
        $language = new Language('chinese');
        $language->load('module/wkproduct_auction');
        $this->assertTrue(!empty($language->get("heading_title")));
    }
}

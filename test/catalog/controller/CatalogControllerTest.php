<?php
namespace test\catalog\controller;
use test\catalog\CatalogTest;

abstract class CatalogControllerTest extends CatalogTest {
    public function __construct($name = null, $data = array(), $dataName = '') {
        parent::__construct($name, $data, $dataName);
    }
} 
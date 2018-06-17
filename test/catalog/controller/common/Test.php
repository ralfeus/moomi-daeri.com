<?php
namespace test\catalog\controller\common;
use test\catalog\controller\CatalogControllerTest;

abstract class Test extends CatalogControllerTest{
    public function __construct($name = null, $data = array(), $dataName = '') {
        parent::__construct($name, $data, $dataName);
    }
} 
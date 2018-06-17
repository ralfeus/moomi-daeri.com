<?php
namespace test\catalog\controller\product;
use test\catalog\controller\CatalogControllerTest;

abstract class CatalogControllerProductTest extends CatalogControllerTest{
    public function __construct($name = null, $data = array(), $dataName = '') {
        parent::__construct($name, $data, $dataName);
    }
} 
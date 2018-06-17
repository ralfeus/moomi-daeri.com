<?php
namespace test\admin\controller\sale;
use test\admin\controller\AdminControllerTest;

abstract class AdminControllerSaleTest extends AdminControllerTest {
    protected $registry;

    public function __construct($name = null, $data = array(), $dataName = '') {
        parent::__construct($name, $data, $dataName);
    }
} 
<?php
namespace test\admin\controller\catalog;
use test\admin\controller\AdminControllerTest;

abstract class Test extends AdminControllerTest {
//    protected $registry;

    public function __construct($name = null, $data = array(), $dataName = '') {
        parent::__construct($name, $data, $dataName);
    }
} 
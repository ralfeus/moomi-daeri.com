<?php
namespace test\admin\controller;
use test\admin\AdminTest;

abstract class AdminControllerTest extends AdminTest {
    public function __construct($name = null, $data = array(), $dataName = '') {
        parent::__construct($name, $data, $dataName);
    }
} 
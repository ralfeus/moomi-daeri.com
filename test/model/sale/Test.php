<?php
namespace test\model\sale;

abstract class Test extends \test\model\Test {
    public function __construct($name = null, $data = array(), $dataName = '') {
        parent::__construct($name, $data, $dataName);
    }
} 
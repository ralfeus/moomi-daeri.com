<?php
namespace test\model;
use PHPUnit\Framework\Error\Notice;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Error\Warning;

require_once('/var/www/moomi-daeri.com/test/initTestEnvironment.php');


abstract class Test extends TestCase {
    protected $registry;

    public function __construct($name = null, $data = array(), $dataName = '') {
        parent::__construct($name, $data, $dataName);
        Notice::$enabled = true;
        Warning::$enabled = true;
        $_SERVER['HTTP_HOST'] = 'ubuntu.home.local';

        $this->registry = initTestEnvironment('admin');
    }
} 
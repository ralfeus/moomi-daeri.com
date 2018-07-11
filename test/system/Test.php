<?php
namespace test\system;
use PHPUnit\Framework\Error\Notice;
use PHPUnit\Framework\Error\Warning;
use PHPUnit\Framework\TestCase;

require_once('/var/www/moomi-daeri.com/test/initTestEnvironment.php');


abstract class Test extends TestCase {
    protected $registry;

    public function __construct($name = null, $data = array(), $dataName = '') {
        parent::__construct($name, $data, $dataName);
        Notice::$enabled = true;
        Warning::$enabled = true;
        $_SERVER['HTTP_HOST'] = 'ubuntu.home.local';

        $this->registry = initTestEnvironment('');
    }
}
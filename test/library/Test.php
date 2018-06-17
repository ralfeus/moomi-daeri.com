<?php
namespace test\library;
use model\DAO;
use PHPUnit\Framework\Error\Notice;
use PHPUnit\Framework\Error\Warning;

require_once('/var/www/moomi-daeri.com/test/initTestEnvironment.php');

abstract class Test /*extends \PHPUnit_Framework_TestCase */{
    protected $registry;
    protected $class;

    public function __construct($name = null, $data = array(), $dataName = '') {
        parent::__construct($name, $data, $dataName);
        $this->registry = initTestEnvironment('admin');

        Notice::$enabled = FALSE;
        Warning::$enabled = true;
    }
}
<?php
namespace test\admin;
use PHPUnit\Framework\Error\Notice;
use PHPUnit\Framework\Error\Warning;
use PHPUnit\Framework\TestCase;
use system\library\User;

require_once('/var/www/moomi-daeri.com/test/initTestEnvironment.php');


abstract class AdminTest extends TestCase {
    protected $registry;
    protected $class;

    public function __construct($name = null, $data = array(), $dataName = '') {
        parent::__construct($name, $data, $dataName);
        Notice::$enabled = true;
        Warning::$enabled = true;
        $_SERVER['HTTP_HOST'] = 'ubuntu.home.local';

        $this->registry = initTestEnvironment('admin');
        $user = new User($this->registry);
        $this->registry->set('user', $user);
        $user->login('ralfeus', 'ujcdsex');
        $this->registry->get('session')->data['token'] = 'PHPUnit';
        //ob_start(null, null, true);
    }
} 
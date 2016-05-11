<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Mykhaylo Khodorev
 * Date: 18.07.2012
 * Time: 18:59
 * Base of MVCL architecture classes - models and controllers. Views and languages are not classes
 */
require_once(DIR_SYSTEM . 'engine/registry.php');
require_once(DIR_SYSTEM . 'library/db.php');
class OpenCartBase {
    /** @var Config */
    protected $config;
    /** @var DBDriver */
    protected $db;
    /** @var Loader */
    protected $load;
    /** @var Log */
    protected $log;
    /** @var Registry */
    protected $registry;

    protected function __construct($registry) {
        if (!isset($registry)) {
            $registry = new Registry();
            $registry->set('db', DB::getDB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE));
        }
        $this->registry = $registry;
        $this->config = $this->registry->get('config');
        $this->db = $this->registry->get('db');
        $this->load = $this->registry->get('load');
        $this->log = $this->registry->get("log");
    }

    public function __get($key) {
        return $this->registry->get($key);
    }

    public function __set($key, $value) {
        $this->registry->set($key, $value);
    }

    public function __sleep() {
        return ['log'];
    }

    /**
     * @return Cache
     */
    protected function getCache() {
        return $this->registry->get('cache');
    }

    protected function getCurrentCurrency() {
        return $this->registry->get('currency');
    }
    /**
     * @return Currency
     */
    protected function getCurrency() {
        return $this->getCurrentCurrency();
    }

    /**
     * @return \Customer
     */
    protected  function getCurrentCustomer() {
        return $this->registry->get('customer');
    }

//    private function getCustomer() {
//        return $this->getCurrentCustomer();
//    }

    /**
     * @return DBDriver
     */
    protected function getDb() {
        return $this->db;
    }

    /**
     *  @return Language
     */
    protected function getLanguage() {
        return $this->registry->get('language');
    }

    /**
     * @return Log
     */
    protected function getLogger() {
        return $this->log;
    }

    /**
     * @return Session
     */
    protected function getSession() {
        return $this->registry->get('session');
    }
}

<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dev
 * Date: 18.7.12
 * Time: 18:59
 * Base of MVCL architecture classes - models and controllers. Views and languages are not classes
 */
class OpenCartBase
{
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

    protected function __construct($registry)
    {
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

    /**
     * @return Currency
     */
    protected function getCurrency() {
        return $this->registry->get('currency');
    }

    /**
     * @return \Customer
     */
    protected  function getCustomer() {
        return $this->registry->get('customer');
    }

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
}

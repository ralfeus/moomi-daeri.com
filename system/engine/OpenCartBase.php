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
    /** @deprecated */
    protected $config;
    /** @var Config */
    protected static $newConfig;
    /** @var DBDriver */
    /** @deprecated */
    protected static $db;
    /** @var Loader */
    /** @deprecated */
    protected $load;
    /** @var Loader */
    protected static $loader;
    /** @var Log */
    protected $log;
    /** @var Registry */
    /** @deprecated */
    protected $registry;

    protected function __construct($registry) {
        if (!isset($registry)) {
            $registry = new Registry();
            $registry->set('db', DB::getDB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE));
        }
        $this->registry = $registry;
        if (is_null(static::$newConfig)) { static::$newConfig = $this->registry->get('config'); }
        $this->config = static::$newConfig;
        if (is_null(static::$db)) { static::$db = $this->registry->get('db'); }
        if (is_null(static::$loader)) { static::$loader = $this->registry->get('load'); }
        $this->load = static::$loader;
        $this->log = $this->registry->get("log");
    }

    public function __get($key) {
        return $this->registry->get($key);
    }

    public function __set($key, $value) {
        $this->registry->set($key, $value);
    }

    public function __sleep() {
        return array_diff(
            array_keys(get_object_vars($this)),
            ['config', 'db', 'load', 'registry']
        );
    }

    public function __wakeup() {
        $this->registry = new Registry();
        $this->registry->set('db', static::$db);
    }

    /**
     * @return Cache
     */
    protected function getCache() {
        return $this->registry->get('cache');
    }

    protected function getConfig() {
        return static::$newConfig;
    }

    /**
     * @return Currency
     */
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
        return static::$db;
    }

    /**
     *  @return Language
     */
    protected function getLanguage() {
        return $this->registry->get('language');
    }

    /**
     * @param Language $value
     */
    protected function setLanguage($value) {
        $this->registry->set('language', $value);
    }

    /**
     * @return Loader
     */
    protected function getLoader() {
        return static::$loader;
    }

    /**
     * @return Log
     */
    protected function getLogger() {
        return $this->log;
    }

    /**
     * @return Registry
     */
    protected function getRegistry() {
        return $this->registry;
    }

    /**
     * @return Session
     */
    protected function getSession() {
        return $this->registry->get('session');
    }

    /**
     * @param Session $value
     */
    protected function setSession($value) {
        $this->registry->set('session', $value);
    }
}

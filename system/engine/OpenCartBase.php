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
    protected $config;
    protected $db;
    protected $load;
    protected $log;
    protected $registry;

    protected function __construct($registry)
    {
        $this->registry = new Registry();
        $this->registry = $registry;
        $this->config = new Config();
        $this->config = $this->registry->get('config');
        $this->db = $this->registry->get('db');
        $this->load = new Loader(null);
        $this->load = $this->registry->get('load');
        $this->log = new Log("");
        $this->log = $this->registry->get("log");
    }

    public function __get($key) {
        return $this->registry->get($key);
    }

    public function __set($key, $value) {
        $this->registry->set($key, $value);
    }
}

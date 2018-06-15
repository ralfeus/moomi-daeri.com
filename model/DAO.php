<?php
namespace model;
use system\engine\Model;

//require_once(DIR_ROOT . 'system/engine/model.php');

class DAO extends \system\engine\Model{
    /** @var DAO[] */
    protected static $instances;

    /**
     * @return static
     */
    public static function getInstance() {
        global $registry;
        $class = get_called_class();

        if (!isset(self::$instances[$class])) {
            self::$instances[$class] = new $class($registry);
        }
        return self::$instances[$class];
    }
}
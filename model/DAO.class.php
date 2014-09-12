<?php
namespace model;

class DAO extends \Model{
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
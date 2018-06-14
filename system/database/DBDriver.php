<?php
namespace system\database;
use system\exception\NotImplementedException;

abstract class DBDriver {
    /**
     * @param string $driver Database driver to be used
     * @param string $hostname DB server host name
     * @param string $username DB user name
     * @param string $password DB user password
     * @param string $database Database to connect to
     * @return DBDriver
     * @throws NotImplementedException
     */
    public static function getDriver($driver, $hostname, $username, $password, $database) {
        if (strtolower($driver) == "mysql") {
            return new MySQL($hostname, $username, $password, $database);
        } else {
            throw new NotImplementedException("The driver '$driver' is not implemented");
        }
    }

    /**
     * @return bool
     * Begins transaction
     */
    public abstract function beginTransaction();

    /**
     * @return bool
     */
    public abstract function commitTransaction();
    /**
     * @return int
     */
    public abstract function countAffected();

    /**
     * @param string $string
     * @return string
     */
    public abstract function escape($string);

    /**
     * @return int
     */
    public abstract function getLastId();

    /**
     * @param string $sql
     * @param array $params
     * @param bool $log
     * @param bool $noCache
     * @return \stdClass
     */
    public abstract function query($sql, $params = array(), $log = false, $noCache = false);

    /**
     * @param string $sql
     * @param array $params
     * @param bool $log
     * @return mixed|bool Returns false is no rows is returned
     */
    public abstract function queryScalar($sql, $params = array(), $log = false);

    /**
     * @return bool
     */
    public abstract function rollbackTransaction();
}
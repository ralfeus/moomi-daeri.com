<?php
interface DBDriver {
    /**
     * @return bool
     * Begins transaction
     */
    public function beginTransaction();

    /**
     * @return bool
     */
    public function commitTransaction();
    /**
     * @return int
     */
    public function countAffected();

    /**
     * @param string $string
     * @return string
     */
    public function escape($string);

    /**
     * @return int
     */
    public function getLastId();

    /**
     * @param string $sql
     * @param array $params
     * @param bool $log
     * @param bool $invalidateCache
     * @return stdClass
     */
    public function query($sql, $params = array(), $log = false, $invalidateCache = true);

    /**
     * @param string $sql
     * @param array $params
     * @param bool $log
     * @return mixed|bool Returns false is no rows is returned
     */
    public function queryScalar($sql, $params = array(), $log = false);

    /**
     * @return bool
     */
    public function rollbackTransaction();
}
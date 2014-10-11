<?php
interface DBDriver {
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
     * @return stdClass
     */
    public function query($sql, $params = array(), $log = false);

    /**
     * @param string $sql
     * @param array $params
     * @param bool $log
     * @return mixed|bool Returns false is no rows is returned
     */
    public function queryScalar($sql, $params = array(), $log = false);
}
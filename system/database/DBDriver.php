<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dev
 * Date: 21.11.13
 * Time: 22:04
 * To change this template use File | Settings | File Templates.
 */

interface DBDriver {
    public function countAffected();
    public function escape($string);
    public function getLastId();
    public function query($sql, $params = array(), $log = false);
    public function queryScalar($sql, $params = array(), $log = false);
}
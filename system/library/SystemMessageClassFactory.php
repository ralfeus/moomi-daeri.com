<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dev
 * Date: 29.7.12
 * Time: 20:52
 * To change this template use File | Settings | File Templates.
 */
namespace system\library;

class SystemMessageClassFactory extends LibraryClass {
    public static function createInstance($systemMessageTypeId, $loader) {
        if ($systemMessageTypeId == 1)
            return \AddCreditRequest::getInstance(self::getRegistry());
        else
            return null;
    }

    public static function getInstance($registry) {
        // Do nothing
    }
}

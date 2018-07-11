<?php
/**
 * Created by PhpStorm.
 * User: ralfeus
 * Date: 11.07.2018
 * Time: 07:35
 */

namespace system\exception;

use Exception;

class NotLoggedInException extends Exception {
    public $returnUrl;

    public function __construct($returnUrl) {
        $this->returnUrl = $returnUrl;
    }
}
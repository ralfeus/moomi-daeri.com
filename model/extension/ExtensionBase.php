<?php
/**
 * Created by PhpStorm.
 * User: ralfeus
 * Date: 2/17/2016
 * Time: 11:36 AM
 */
namespace model\extension;

use system\engine\OpenCartBase;

abstract class ExtensionBase extends OpenCartBase {
    protected $extensionCode;

    public function __construct($registry, $code) {
        parent::__construct($registry);
        $this->extensionCode = $code;
    }

    public function isEnabled() {
        try {
            return self::isEnabled();
        } catch (\Exception $exc) {
            return boolval($this->config->get($this->extensionCode . '_status'));
        }
    }

    public function getCode() {
        return $this->extensionCode;
    }
}
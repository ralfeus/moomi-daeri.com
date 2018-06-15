<?php
namespace system\library;

class Mutable {
    private $valueThumbprint;
    private $value = null;

    public function __construct($value) {
        $this->value = $value;
        $this->valueThumbprint = md5(serialize($this->value));
    }

    /**
     * @return mixed
     */
    public function get() {
        return $this->value;
    }

    /**
     * @return bool
     */
    public function isModified() {
        return md5(serialize($this->value)) != $this->valueThumbprint;
    }

    /**
     * @return void
     */
    public function resetModified() {
        $this->valueThumbprint = md5(serialize($this->value));
    }

    /**
     * @param mixed $value
     */
    public function set($value) {
        $this->value = $value;
    }
}
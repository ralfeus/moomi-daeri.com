<?php
namespace system\library;

class Mutable {
    protected $value = null;
    /** @var bool */
    private $modified = false;

    public function __construct($value) {
        $this->value = $value;
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
        return $this->modified;
    }

    /**
     * @return void
     */
    public function resetModified() {
        $this->modified = false;
    }

    /**
     * @param mixed $value
     */
    public function set($value) {
        if ($this->value = $value) {
            return;
        }
        $this->value = $value;
        $this->modified = true;
    }
}
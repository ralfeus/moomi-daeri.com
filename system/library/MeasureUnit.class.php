<?php
namespace system\library;

class MeasureUnit {
    private $id;
    private $code;
    private $names;
    private $rateToDefault;
    private $unitType;

    public function __construct($id, $unitType) {
        $this->id = $id;
        $this->unitType = $unitType;
    }

    /**
     * @return string
     */
    public function getCode() {
        if (!isset($this->code)) {
            $this->code = MeasureUnitDAO::getInstance()->getCode($this->id, $this->unitType);
        }
        return $this->code;
    }

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param int $language Language ID
     * @return string
     */
    public function getName($language) {
        if (!isset($this->names)) {
            $this->names = MeasureUnitDAO::getInstance()->getNames($this->id, $this->unitType);
        }
        return $this->names[$language];
    }

    /**
     * @return float
     */
    public function getRateToDefault() {
        if (!isset($this->rateToDefault)) {
            $this->rateToDefault = MeasureUnitDAO::getInstance()->getRateToDefault($this->id, $this->unitType);
        }
        return $this->rateToDefault;
    }

    /**
     * @return string
     */
    public function getType() {
        return $this->unitType;
    }
}

class MeasureUnitArgumentException extends \Exception {
    public function __construct($argument) {
        parent::__construct("Unknown measure unit :" . print_r($argument, true));
    }
}

class UnacceptableMeasureUnitException extends \Exception {
    public function __construct($unitType) {
        parent::__construct("Unacceptable measure unit type: $unitType");
    }
}
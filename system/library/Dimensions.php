<?php
namespace system\library;

class Dimensions {

    private $height;
    private $length;
    private $unit;
    private $width;

    /**
     * @param int|MeasureUnit $unit
     * @param float $height
     * @param float $length
     * @param float $width
     * @throws MeasureUnitArgumentException
     * @throws UnacceptableMeasureUnitException
     */
    public function __construct($unit, $height, $length, $width) {
        if (is_numeric($unit)) {
            $this->unit = new MeasureUnit($unit, 'length');
        } elseif ($unit instanceof MeasureUnit) {
            if ($unit->getType() == 'length') {
                $this->unit = $unit;
            } else {
                throw new UnacceptableMeasureUnitException($unit->getType());
            }
        } else {
            throw new MeasureUnitArgumentException($unit);
        }
        $this->height = $height;
        $this->length = $length;
        $this->width = $width;
    }

    /**
     * @return float
     */
    public function getHeight() {
        return $this->height;
    }

    /**
     * @return float
     */
    public function getLength() {
        return $this->length;
    }

    /**
     * @return float
     */
    public function getWidth() {
        return $this->width;
    }

    /**
     * @return MeasureUnit
     */
    public function getUnit() {
        return $this->unit;
    }
}
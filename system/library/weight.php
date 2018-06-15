<?php
namespace system\library;

class Weight {
    private $weight;
    private $unit;

    /**
     * @param int|MeasureUnit $unit
     * @param float $weight
     * @throws MeasureUnitArgumentException
     * @throws UnacceptableMeasureUnitException
     */
    public function __construct($unit, $weight) {
        if (is_numeric($unit)) {
            $this->unit = new MeasureUnit($unit, 'weight');
        } elseif ($unit instanceof MeasureUnit) {
            if ($unit->getType() == 'weight') {
                $this->unit = $unit;
            } else {
                throw new UnacceptableMeasureUnitException($unit->getType());
            }
        } else {
            throw new MeasureUnitArgumentException($unit);
        }
        $this->weight = $weight;
    }

    /**
     * @return MeasureUnit
     */
    public function getUnit() {
        return $this->unit;
    }

    /**
     * @return float
     */
    public function getWeight() {
        return $this->weight;
    }
}
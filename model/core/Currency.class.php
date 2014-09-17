<?php
namespace model\core;

class Currency {
    /** @var string */
    private $code;
    /** @var float[] */
    private $rateHistory;
    private $fractionDigits;
    private $symbolLeft;
    private $symbolRight;
    private $title;

    public function __construct($currencyCode, $title, $fractionDigits, $symbolLeft, $symbolRight) {
        $this->code = $currencyCode;
        $this->fractionDigits = $fractionDigits;
        $this->symbolLeft = $symbolLeft;
        $this->symbolRight = $symbolRight;
        $this->title = $title;
    }

    public function getRate($date = null) {
        if (!isset($this->rateHistory)) {
            $this->rateHistory = CurrencyDAO::GetInstance()->getCurrencyRateHistory($this->code);
        }
        if (is_null($date)) {
            return $this->rateHistory[0];
        } else {
            foreach ($this->rateHistory as $rateDate => $rate) {
                if ((new \DateTime($date))->diff(new \DateTime($rateDate))->invert) {
                    return $rate;
                }
            }
            throw new \Exception("No currency " . $this->code . " rate found for " . $date);
        }
    }

    public function getString($value) {
        $string = '';

        if (!empty($this->symbolLeft)) {
            $string .= $this->symbolLeft;
        }

        $string .= number_format(round($value, $this->fractionDigits), $this->fractionDigits);

        if (!empty($this->symbolRight)) {
            $string .= $this->symbolRight;
        }

        return $string;
    }
} 
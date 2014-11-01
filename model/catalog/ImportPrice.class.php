<?php
namespace model\catalog;

class ImportPrice {
    private $price;
    private $promoPrice;

    function __construct($price, $promoPrice = null)
    {
        $this->price = $price;
        $this->promoPrice = $promoPrice;
    }

    /**
     * @return float
     */
    public function getPrice() { return $this->price; }

    /**
     * @return float
     */
    public function getPromoPrice() { return $this->promoPrice; }
}
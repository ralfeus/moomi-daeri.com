<?php
namespace model\checkout;

class CartItem {
    private $product;
    private $optionValues;

    public function __construct($product, $optionValues) {
        $this->product = $product;
        $this->optionValues = $optionValues;
    }
} 
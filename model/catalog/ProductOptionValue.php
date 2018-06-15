<?php
/**
 * Created by PhpStorm.
 * User: ralfeus
 * Date: 3/9/2016
 * Time: 10:17 AM
 */

namespace model\catalog;

class ProductOptionValue {
    private $id;
    private $productOption;
    private $optionValue;
    private $quantity;
    private $subtract;
    private $price;
    private $points;
    private $weight;
    private $afcId;

    /**
     * ProductOptionValue constructor.
     * @param int $id
     * @param ProductOption $productOption
     * @param OptionValue $optionValue
     * @param int $quantity
     * @param int $subtract
     * @param float $price
     * @param int $points
     * @param float $weight
     * @param int $afcId
     */
    public function __construct($id, $productOption, $optionValue, $quantity, $subtract, $price, $points, $weight, $afcId) {
        $this->id = $id;
        $this->productOption = $productOption;
        $this->optionValue = $optionValue;
        $this->quantity = $quantity;
        $this->subtract = $subtract;
        $this->price = $price;
        $this->points = $points;
        $this->weight = $weight;
        $this->afcId = $afcId;
    }

    public function __destruct() {
        $this->productOption = null;
        $this->optionValue = null;
    }

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return ProductOption
     */
    public function getProductOption() {
        return $this->productOption;
    }

    /**
     * @return OptionValue
     */
    public function getOptionValue() {
        return $this->optionValue;
    }

    /**
     * @return int
     */
    public function getQuantity() {
        return $this->quantity;
    }

    /**
     * @return int
     */
    public function getSubtract() {
        return $this->subtract;
    }

    /**
     * @return float
     */
    public function getPrice() {
        return $this->price;
    }

    /**
     * @return int
     */
    public function getPoints() {
        return $this->points;
    }

    /**
     * @return float
     */
    public function getWeight() {
        return $this->weight;
    }
}
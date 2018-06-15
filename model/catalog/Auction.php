<?php
/**
 * Created by PhpStorm.
 * User: ralfeus
 * Date: 22.07.2016
 * Time: 14:45
 */

namespace model\catalog;


class Auction {
    private $id;
    private $product;
    private $name;
    private $isAuction;
    private $min;
    private $max;
    private $startDate;
    private $endDate;

    /**
     * Auction constructor.
     * @param $id
     * @param $product
     * @param $name
     * @param $isAuction
     * @param $min
     * @param $max
     * @param $startDate
     * @param $endDate
     */
    public function __construct($id, $product, $name, $isAuction, $min, $max, $startDate, $endDate) {
        $this->id = $id;
        $this->product = $product;
        $this->name = $name;
        $this->isAuction = $isAuction;
        $this->min = $min;
        $this->max = $max;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }


    /**
     * @return mixed
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getProduct() {
        return $this->product;
    }

    /**
     * @param mixed $product
     */
    public function setProduct($product) {
        $this->product = $product;
    }

    /**
     * @return mixed
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * @return bool
     */
    public function isAuction() {
        return $this->isAuction;
    }

    /**
     * @param mixed $isAuction
     */
    public function setIsAuction($isAuction) {
        $this->isAuction = $isAuction;
    }

    /**
     * @return mixed
     */
    public function getMin() {
        return $this->min;
    }

    /**
     * @param mixed $min
     */
    public function setMin($min) {
        $this->min = $min;
    }

    /**
     * @return mixed
     */
    public function getMax() {
        return $this->max;
    }

    /**
     * @param mixed $max
     */
    public function setMax($max) {
        $this->max = $max;
    }

    /**
     * @return mixed
     */
    public function getStartDate() {
        return $this->startDate;
    }

    /**
     * @param mixed $startDate
     */
    public function setStartDate($startDate) {
        $this->startDate = $startDate;
    }

    /**
     * @return mixed
     */
    public function getEndDate() {
        return $this->endDate;
    }

    /**
     * @param mixed $endDate
     */
    public function setEndDate($endDate) {
        $this->endDate = $endDate;
    }
}
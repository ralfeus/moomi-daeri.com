<?php
namespace automation;

class CarProduct extends Product{
    public $brand;
    public $partNumbers;
    public $supplier;

    /**
     * @param CarProductSource $sourceSite
     * @param string[] $categoryIds
     * @param string $sourceProductId
     * @param string[] $partNumbers
     * @param string $name
     * @param string $url
     * @param string $thumbnail
     * @param float $price
     * @param string $brand
     * @param string $supplier
     * @param string $description
     * @param float $weight
     */
    public function __construct(
        $sourceSite, $categoryIds, $sourceProductId, $partNumbers = array(), $name, $url, $thumbnail, $price,
        $brand, $supplier,
        $description = null, $weight = null
    ) {
        parent::__construct($sourceSite, $categoryIds, $sourceProductId, $name, $url, $thumbnail, $price, $description, $weight);
        $this->brand = $brand;
        $this->partNumbers = $partNumbers;
        $this->supplier = $supplier;
    }
}


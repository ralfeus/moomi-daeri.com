<?php
namespace automation;

class CarProduct extends Product{
    public $partNumbers;

    /**
     * @param CarProductSource $sourceSite
     * @param string[] $categoryIds
     * @param string $sourceProductId
     * @param string[] $partNumbers
     * @param string $name
     * @param string $url
     * @param string $thumbnail
     * @param float $price
     * @param string $description
     * @param float $weight
     */
    public function __construct(
        $sourceSite, $categoryIds, $sourceProductId, $partNumbers = array(), $name, $url, $thumbnail, $price, $description = null, $weight = null
    ) {
        parent::__construct($sourceSite, $categoryIds, $sourceProductId, $name, $url, $thumbnail, $price, $description, $weight);
        $this->partNumbers = $partNumbers;
    }
}


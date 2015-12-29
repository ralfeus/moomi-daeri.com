<?php
namespace automation;

class Product {
    public $id;
    public $categoryIds;
    public $images = array();
    public $description;
    public $minimalAmount;
    public $name;
    public $price;
    public $promoPrice = null;
    public $sourceProductId;
    public $sourceSite;
    public $thumbnail;
    public $url;
    public $weight;

    /**
     * @param ProductSource $sourceSite
     * @param string[] $categoryIds
     * @param string $sourceProductId
     * @param string $name
     * @param string $url
     * @param string $thumbnail
     * @param float $price
     * @param string $description
     * @param float $weight
     * @param int $minimalAmount
     */
    public function __construct(
        $sourceSite, $categoryIds, $sourceProductId, $name, $url, $thumbnail, $price, $description = null, $weight = null, $minimalAmount = 0
    ) {
        $this->categoryIds = $categoryIds;
        $this->description = $description;
        $this->minimalAmount = $minimalAmount;
        $this->name = $name;
        $this->price = $price;
        $this->sourceProductId = $sourceProductId;
        $this->sourceSite = $sourceSite;
        $this->thumbnail = $thumbnail;
        $this->url = $url;
        $this->weight = $weight;
    }

    /**
     * @return string[]
     */
    public function getCategories() {
        return is_array($this->categoryIds) ? $this->categoryIds : [] ;
    }

    /**
     * @return string[]
     */
    public function getImages() {
        return is_array($this->images) ? $this->images : [];
    }
}


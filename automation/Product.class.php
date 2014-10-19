<?php
namespace automation;

class Product {
    public $id;
    public $categoryId;
    public $images = array();
    public $description;
    public $name;
    public $price;
    public $promoPrice = null;
    public $sourceProductId;
    public $sourceSite;
    public $thumbnail;
    public $url;
    public $weight;

    public function __construct(
        ProductSource $sourceSite, $categoryId, $sourceProductId, $name, $url, $thumbnail, $price, $description = null, $weight = null
    ) {
        $this->categoryId = $categoryId;
        $this->description = $description;
        $this->name = $name;
        $this->price = $price;
        $this->sourceProductId = $sourceProductId;
        $this->sourceSite = $sourceSite;
        $this->thumbnail = $thumbnail;
        $this->url = $url;
        $this->weight = $weight;
    }

    public function getImages() {
        return $this->images;
    }
}


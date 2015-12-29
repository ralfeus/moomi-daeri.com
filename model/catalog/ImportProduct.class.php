<?php
namespace model\catalog;

use model\extension\ImportSourceSite;

class ImportProduct {
    private $id;
    private $name;
    private $categories;
    private $description;
    private $images;
    private $isActive;
    private $localPrice;
    private $minimalAmount;
    private $sourcePrice;
    private $localProductId;
    private $sourceProductId;
    private $sourceSite;
    private $sourceUrl;
    private $thumbnailUrl;
    private $timeModified;
    private $weight;

    /**
     * @param int $id
     * @param string $sourceProductId
     * @param int $localProductId
     * @param string $name
     * @param int[] $categories
     * @param string $description
     * @param ImportPrice $localPrice
     * @param ImportPrice $sourcePrice
     * @param ImportSourceSite $sourceSite
     * @param string $sourceUrl
     * @param string $thumbnailUrl
     * @param string[] $images
     * @param float $weight
     * @param string $timeModified
     * @param bool $isActive
     * @param int $minimalAmount
     */
    public function __construct($id, $sourceProductId = null, $localProductId = null, $name = null, $categories = null,
                                $description = null, $localPrice = null, $sourcePrice = null, $sourceSite = null,
                                $sourceUrl = null, $thumbnailUrl = null, $images = null, $weight = null,
                                $timeModified = null, $isActive = null, $minimalAmount = 0) {
        $this->id = $id;
        $this->localProductId = empty($localProductId) ? null : $localProductId;
        $this->categories = $categories;
        $this->minimalAmount = $minimalAmount;
        $this->name = $name;
        $this->description = $description;
        $this->isActive = $isActive;
        $this->localPrice = $localPrice;
        $this->sourcePrice = $sourcePrice;
        $this->sourceSite = $sourceSite;
        $this->sourceUrl = $sourceUrl;
        $this->sourceProductId = $sourceProductId;
        $this->thumbnailUrl = $thumbnailUrl;
        $this->images = $images;
        $this->timeModified = $timeModified;
        $this->weight = $weight;
    }

    /**
     * Returns categories of the imported product.
     * In case of absence of categories specific for certain product returns categories for source site
     *
     * @return int[]
     */
    public function getCategories() {
        return sizeof($this->categories) ? $this->categories : $this->sourceSite->getDefaultCategories();
    }

    /**
     * @return int
     */
    public function getLocalProductId() {
        if (!isset($this->localProductId)) {
            $this->localProductId = ImportProductDAO::getInstance()->getLocalProductId($this->id);
        }
        return $this->localProductId;
    }

    public function getThumbnailUrl() {
        if (!isset($this->thumbnailUrl)) {
            $this->thumbnailUrl = ImportProductDAO::getInstance()->getThumbnailUrl($this->id);
        }
        return $this->thumbnailUrl;
    }
    /**
     * @return string
     */
    public function getDescription() {
        if (!isset($this->description)) {
            $this->description = ImportProductDAO::getInstance()->getDescription($this->id);
        }

        return $this->description;
    }

    /**
     * @return integer
     */
    public function getId() { return $this->id; }

    /**
     * @return array
     */
    public function getImages() {
        if (!isset($this->images)) {
            $this->images = ImportProductDAO::getInstance()->getImages($this->id);
        }
        return $this->images;
    }

    /**
     * @return bool
     */
    public function getIsActive() {
        if (!isset($this->isActive)) {
            $this->isActive = ImportProductDAO::getInstance()->getIsActive($this->id);
        }
        return $this->isActive;
    }

    /**
     * @return ImportPrice
     */
    public function getLocalPrice() { return $this->localPrice; }

    /**
     * @return int
     */
    public function getMinimalAmount() { return $this->minimalAmount; }

    /**
     * @return string
     */
    public function getName() { return $this->name; }

    /**
     * @return ImportPrice
     */
    public function getSourcePrice() { return $this->sourcePrice; }

    /**
     * @return int
     */
    public function getSourceProductId() { return $this->sourceProductId; }

    /**
     * @return ImportSourceSite
     */
    public function getSourceSite() { return $this->sourceSite; }

    /**
     * @return string
     */
    public function getSourceUrl() { return $this->sourceUrl; }

    /**
     * @return string
     */
    public function getTimeModified() { return $this->timeModified; }

    /**
     * @return float
     */
    public function getWeight() { return $this->weight; }
}
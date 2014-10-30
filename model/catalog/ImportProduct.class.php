<?php
namespace model\catalog;

use model\extension\ImportSourceSite;
use Price;

class ImportProduct {
    private $id;
    private $name;
    private $categories;
    private $description;
    private $images;
    private $isActive;
    private $localPrice;
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
     * @param Price $localPrice
     * @param Price $sourcePrice
     * @param ImportSourceSite $sourceSite
     * @param string $sourceUrl
     * @param string $thumbnailUrl
     * @param string[] $images
     * @param float $weight
     * @param string $timeModified
     * @param bool $isActive
     */
    public function __construct(
        $id, $sourceProductId, $localProductId, $name, $categories, $description, $localPrice = null, $sourcePrice, $sourceSite,
        $sourceUrl, $thumbnailUrl, array $images, $weight, $timeModified, $isActive
    ) {
        $this->id = $id;
        $this->localProductId = empty($localProductId) ? null : $localProductId;
        $this->categories = $categories;
        $this->name = $name;
        $this->description = $description;
        $this->isActive = $isActive;
        $this->localPrice = empty($localPrice) ? null : $localPrice;
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

    public function getLocalProductId() { return $this->localProductId; }
    public function getThumbnailUrl() { return $this->thumbnailUrl; }
    /**
     * @return string
     */
    public function getDescription() { return $this->description; }

    /**
     * @return integer
     */
    public function getId() { return $this->id; }

    /**
     * @return array
     */
    public function getImages() { return $this->images; }

    /**
     * @return bool
     */
    public function getIsActive() { return $this->isActive; }

    /**
     * @return Price
     */
    public function getLocalPrice() { return $this->localPrice; }

    /**
     * @return string
     */
    public function getName() { return $this->name; }

    /**
     * @return Price
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
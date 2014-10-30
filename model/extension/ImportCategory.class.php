<?php
namespace model\extension;

use system\library\Mutable;

class ImportCategory {
    private $sourceSiteCategoryId;
    private $localCategoryIds;
    private $priceUpperLimit;

    function __construct($sourceSiteCategoryId, $localCategoryIds, $priceUpperLimit) {
        $this->sourceSiteCategoryId = $sourceSiteCategoryId;
        $this->localCategoryIds = new Mutable($localCategoryIds);
        $this->priceUpperLimit = $priceUpperLimit;
    }

    /**
     * @return int[]
     */
    public function getLocalCategoryIds() {
        return $this->localCategoryIds;
    }

    /**
     * @param int[] $localCategoryIds
     */
    public function setLocalCategoryIds($localCategoryIds) {
        $this->localCategoryIds = $localCategoryIds;
    }

    /**
     * @return float
     */
    public function getPriceUpperLimit() {
        return $this->priceUpperLimit;
    }

    /**
     * @param mixed $priceUpperLimit
     */
    public function setPriceUpperLimit($priceUpperLimit) {
        $this->priceUpperLimit = $priceUpperLimit;
    }

    /**
     * @return int
     */
    public function getSourceSiteCategoryId() {
        return $this->sourceSiteCategoryId;
    }
}
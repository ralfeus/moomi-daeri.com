<?php
namespace model\catalog;

use automation\ProductSource;
use model\extension\ImportSourceSite;
use system\library\Mutable;

class ImportCategory {
    private $sourceSiteCategoryId;
    private $localCategoryIds;
    private $priceUpperLimit;
    /** @var ProductSource */
    private $sourceSite;
    private $url;

    /**
     * ImportCategory constructor.
     * @param ProductSource $sourceSite
     * @param string $sourceSiteCategoryId
     * @param int[] $localCategoryIds
     * @param float $priceUpperLimit
     * @param string $url
     */
    function __construct($sourceSite, $sourceSiteCategoryId, $localCategoryIds, $priceUpperLimit, $url = null) {
        $this->sourceSite = $sourceSite;
        $this->sourceSiteCategoryId = $sourceSiteCategoryId;
        $this->localCategoryIds = $localCategoryIds;
        $this->priceUpperLimit = $priceUpperLimit;
        if (!is_null($url)) { $this->url = $url; }
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
     * @param ProductSource $value
     */
    public function setSourceSite($value) {
        $this->sourceSite = $value;
    }

    /**
     * @return int
     */
    public function getSourceSiteCategoryId() {
        return $this->sourceSiteCategoryId;
    }

    /**
     * @return string
     */
    public function getUrl() {
        if (is_null($this->url)) {
            $this->url = $this->sourceSite->getCategoryUrl($this->getSourceSiteCategoryId());
        }
        return $this->url;
    }
}
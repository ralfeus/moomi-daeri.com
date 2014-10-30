<?php
namespace model\catalog;

use automation\ProductSource;
use system\library\Mutable;

class ImportCategory {
    private $sourceSiteCategoryId;
    private $localCategoryIds;
    private $priceUpperLimit;
    /** @var ProductSource */
    private $sourceSite;
    private $url;

    function __construct($sourceSite, $sourceSiteCategoryId, $localCategoryIds, $priceUpperLimit, $url = null) {
        $this->sourceSite = $sourceSite;
        $this->sourceSiteCategoryId = $sourceSiteCategoryId;
        $this->localCategoryIds = new Mutable($localCategoryIds);
        $this->priceUpperLimit = $priceUpperLimit;
        if (!is_null($url)) { $this->url = $url; }
    }

    /**
     * @return int[]
     */
    public function getLocalCategoryIds() {
        return $this->localCategoryIds->get();
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
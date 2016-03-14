<?php
namespace model\catalog;

use model\localization\DescriptionCollection;
use system\library\Dimensions;
use system\library\Mutable;
use system\library\Weight;

class Product {
    private $id;
    /** @var Mutable */
    private $attributes;
    /** @var  Mutable */
    private $categories;
    private $defaultLanguageId;
    /** @var Mutable */
    private $description;
    private $dimension;
    /** @var  Mutable */
    private $discounts;
    /** @var Mutable */
    private $downloads;
    private $imageDescription;
    /** @var  Mutable */
    private $images;
    private $imagePath;
    private $keyword;
    private $koreanName;
    /** @var  Mutable */
    private $layouts;
    private $model;
    /** @var Mutable */
    private $productOptions;
    private $sku;
    private $upc;
    private $location;
    private $quantity;
    private $stockStatusId;
    /** @var Mutable */
    private $stores;
    private $manufacturerId;
    /** @var  Mutable */
    private $related;
    /** @var  Mutable */
    private $rewards;
    private $shipping;
    /** @var  Mutable */
    private $specials;
    private $supplier;
    private $supplierUrl;
    /** @var Mutable  */
    private $tags;
    private $price;
    private $points;
    private $dateAvailable;
    private $subtract;
    private $minimum;
    private $sortOrder;
    private $status;
    private $dateAdded;
    private $dateModified;
    private $viewed;
    private $userId;
    private $afcId;
    private $affiliateCommission;
    private $weight;

    /**
     * @param $id
     * @param int $afcId
     * @param mixed $affiliateCommission
     * @param string $dateAdded
     * @param string $dateAvailable
     * @param string $dateModified
     * @param DescriptionCollection $description
     * @param Dimensions $dimension
     * @param string $imagePath
     * @param string $keyword
     * @param string $koreanName
     * @param string $location
     * @param int $manufacturerId
     * @param float $minimum
     * @param string $model
     * @param ProductOption[] $productOptions
     * @param int $points
     * @param float $price
     * @param int $quantity
     * @param float $shipping
     * @param mixed $sku
     * @param int $sortOrder
     * @param mixed $status
     * @param int $stockStatusId
     * @param int[] $stores
     * @param int $subtract
     * @param Supplier $supplier
     * @param string $supplierUrl
     * @param array $tag
     * @param mixed $upc
     * @param int $userId
     * @param int $viewed
     * @param Weight $weight
     * @param array $attributes
     * @param array $discounts
     * @param array $specials
     * @param array $downloads
     * @param array $categories
     * @param int[] $related
     * @param array $layouts
     * @param array $rewards
     * @param null $imageDescription
     * @param int $defaultLanguageId
     */
    public function __construct($id, $afcId = null, $affiliateCommission = null, $dateAdded = null, $dateAvailable = null, $dateModified = null,
                                $description = null, $dimension = null, $imagePath = null, $keyword = null, $koreanName = null, $location = null,
                                $manufacturerId = null, $minimum = null, $model = null, $productOptions = null, $points = null,
                                $price = null, $quantity = null, $shipping = null, $sku = null, $sortOrder = null, $status = null,
                                $stockStatusId = null, $stores = null, $subtract = null, $supplier = null, $supplierUrl = null,
                                $tag = null, $upc = null, $userId = null, $viewed = null, $weight = null, $attributes = null,
                                $discounts = null, $specials = null, $downloads = null, $categories = null, $related = null,
                                $layouts = null, $rewards = null, $imageDescription = null, $defaultLanguageId = null) {
        $this->id = $id;
        if (!is_null($afcId)) { $this->afcId = $afcId; }
        if (!is_null($affiliateCommission)) { $this->affiliateCommission = new Mutable($affiliateCommission); }
        if (!is_null($attributes)) { $this->attributes = new Mutable($attributes); }
        if (!is_null($categories)) { $this->categories = new Mutable($categories); }
        if (!is_null($dateAdded)) { $this->$dateAdded = $dateAdded; }
        if (!is_null($dateAvailable)) { $this->$dateAvailable = $dateAvailable; }
        if (!is_null($dateModified)) { $this->dateModified = $dateModified; }
        if (!is_null($description)) { $this->description = new Mutable($description); }
        if (!is_null($dimension)) { $this->dimension = new Mutable($dimension); }
        if (!is_null($discounts)) { $this->discounts = new Mutable($discounts); }
        if (!is_null($downloads)) { $this->downloads = new Mutable($downloads); }
        if (!is_null($imageDescription)) { $this->imageDescription = $imageDescription; }
        if (!is_null($imagePath)) { $this->imagePath = new Mutable($imagePath); }
        if (!is_null($keyword)) { $this->koreanName = new Mutable($keyword); }
        if (!is_null($koreanName)) { $this->koreanName = new Mutable($koreanName); }
        if (!is_null($defaultLanguageId)) { $this->defaultLanguageId = $defaultLanguageId; }
        if (!is_null($location)) { $this->location = $location; }
        if (!is_null($manufacturerId)) { $this->manufacturerId = new Mutable($manufacturerId); }
        if (!is_null($minimum)) { $this->minimum = new Mutable($minimum); }
        if (!is_null($model)) { $this->model = new Mutable($model); }
        if (!is_null($productOptions)) {
            $tempProductOptions = new ProductOptionCollection();
            foreach ($productOptions as $option) {
                $tempProductOptions->attach($option);
            }
            $this->productOptions = new Mutable($tempProductOptions);
        }
        if (!is_null($points)) { $this->points = new Mutable($points); }
        if (!is_null($price)) { $this->price = new Mutable($price); }
        if (!is_null($quantity)) { $this->quantity = new Mutable($quantity); }
        if (!is_null($related)) { $this->related = new Mutable($related); }
        if (!is_null($rewards)) { $this->rewards = new Mutable($rewards); }
        if (!is_null($shipping)) { $this->shipping = new Mutable($shipping); }
        if (!is_null($sku)) { $this->sku = new Mutable($sku); }
        if (!is_null($sortOrder)) { $this->sortOrder = new Mutable($sortOrder); }
        if (!is_null($specials)) { $this->specials = new Mutable($specials); }
        if (!is_null($status)) { $this->status = new Mutable($status); }
        if (!is_null($stockStatusId)) { $this->stockStatusId= new Mutable($stockStatusId); }
        if (!is_null($stores)) { $this->stores= new Mutable($stores); }
        if (!is_null($subtract)) { $this->subtract = new Mutable($subtract); }
        if (!is_null($supplier)) { $this->supplier = new Mutable($supplier); }
        if (!is_null($supplierUrl)) { $this->supplierUrl = new Mutable($supplierUrl); }
        if (!is_null($tag)) { $this->tags = new Mutable($tag); }
        if (!is_null($upc)) { $this->upc = new Mutable($upc); }
        if (!is_null($userId)) { $this->userId = new Mutable($userId); }
        if (!is_null($viewed)) { $this->viewed = new Mutable($viewed); }
        if (!is_null($weight)) { $this->weight = new Mutable($weight); }
    }

    /**
     * @return int
     */
    public function getAfcId() {
        if (!isset($this->afcId)) {
            $this->afcId = ProductDAO::getInstance()->getAfcId($this->id);
        }
        return $this->afcId;
    }

    public function setAfcId($value) {
        $this->afcId = $value;
    }

    /**
     * @return float
     */
    public function getAffiliateCommission() {
        if (!isset($this->affiliateCommission)) {
            $this->affiliateCommission = new Mutable(ProductDAO::getInstance()->getAffiliateCommission($this->id));
        }
        return $this->affiliateCommission->get();
    }

    public function setAffiliateCommission($value) {
        $this->affiliateCommission->set($value);
    }

    /**
     * @return array
     */
    public function getAttributes() {
        if (!isset($this->attributes)) {
            $this->attributes = new Mutable(ProductDAO::getInstance()->getProductAttributes($this->id));
        }
        return $this->attributes->get();
    }

    /**
     * @return bool
     */
    public function isAttributesModified() {
        return !is_null($this->attributes) && $this->attributes->isModified();
    }

    /**
     * @return array
     */
    public function getCategories() {
        if (!isset($this->categories)) {
            $this->categories = new Mutable(ProductDAO::getInstance()->getCategories($this->id));
        }
        return $this->categories->get();
    }

    /**
     * @return bool
     */
    public function isCategoriesModified() {
        return !is_null($this->categories) && $this->categories->isModified();
    }

    /**
     * @return string
     */
    public function getDateAdded() {
        if (!isset($this->dateAdded)) {
            $this->dateAdded = ProductDAO::getInstance()->getDateAdded($this->id);
        }
        return $this->dateAdded;
    }

    /**
     * @return string
     */
    public function getDateAvailable() {
        if (!isset($this->dateAvailable)) {
            $this->dateAvailable = ProductDAO::getInstance()->getDateAvailable($this->id);
        }
        return $this->dateAvailable;
    }

    /**
     * @param string $value
     */
    public function setDateAvailable($value) {
        $this->dateAvailable = $value;
    }

    /**
     * @return string
     */
    public function getDateModified() {
        if (!isset($this->dateModified)) {
            $this->dateModified = ProductDAO::getInstance()->getDateModified($this->id);
        }
        return $this->dateModified;
    }

    /**
     * @return DescriptionCollection
     */
    public function getDescription() {
        if (!isset($this->description)) {
            $this->description = new Mutable(ProductDAO::getInstance()->getDescription($this->id));
        }
        return $this->description->get();
    }

    /**
     * @return bool
     */
    public function isDescriptionModified() {
        return !is_null($this->description) && $this->description->isModified();
    }

    /**
     * @return Dimensions
     */
    public function getDimension() {
        if (!isset($this->dimension)) {
            $this->dimension = new Mutable(ProductDAO::getInstance()->getDimension($this->id));
        }
        return $this->dimension->get();
    }

    /**
     * @param Dimensions $value
     */
    public function setDimension($value) {
        $this->dimension->set($value);
    }

    /**
     * @return array
     */
    public function getDiscounts() {
        if (!isset($this->discounts)) {
            $this->discounts = new Mutable(ProductDAO::getInstance()->getProductDiscounts($this->id));
        }
        return $this->discounts->get();
    }

    /**
     * @return bool
     */
    public function isDiscountsModified() {
        return !is_null($this->discounts) && $this->discounts->isModified();
    }

    /**
     * @return array
     */
    public function getDownloads() {
        if (!isset($this->downloads)) {
            $this->downloads = new Mutable(ProductDAO::getInstance()->getProductDownloads($this->id));
        }
        return $this->downloads->get();
    }

    /**
     * @return bool
     */
    public function isDownloadsModified() {
        return !is_null($this->downloads) && $this->downloads->isModified();
    }

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getImageDescription() {
        if (is_null($this->imageDescription)) {
            $this->imageDescription = ProductDAO::getInstance()->getImageDescription($this->id);
        }
        return $this->imageDescription;
    }

    /**
     * @return string
     */
    public function getImagePath() {
        if (!isset($this->imagePath)) {
            $this->imagePath = new Mutable(ProductDAO::getInstance()->getImage($this->id));
        }
        return $this->imagePath->get();
    }

    public function setImagePath($value) {
        $this->imagePath->set($value);
    }

    /**
     * @return string[]
     */
    public function getImages() {
        if (!isset($this->images)) {
            $this->images = new Mutable(ProductDAO::getInstance()->getProductImages($this->id));
        }
        return $this->images->get();
    }

    /**
     * @return bool
     */
    public function isImagesModified() {
        return !is_null($this->images) && $this->images->isModified();
    }

    public function setImages($value) {
        $this->images->set($value);
    }

    public function getKeyword() {
        if (!isset($this->keyword)) {
            $this->keyword = new Mutable(ProductDAO::getInstance()->getKeyword($this->id));
        }
        return $this->keyword->get();
    }

    /**
     * @return string
     */
    public function getKoreanName() {
        if (!isset($this->koreanName)) {
            $this->koreanName = new Mutable(ProductDAO::getInstance()->getKoreanName($this->id));
        }
        return $this->koreanName->get();
    }

    public function setKoreanName($value) {
        $this->koreanName->set($value);
    }

    public function getLayouts() {
        if (!isset($this->layouts)) {
            $this->layouts = new Mutable(ProductDAO::getInstance()->getProductLayoutId($this->id));
        }
        return $this->layouts;
    }

    /**
     * @return bool
     */
    public function isLayoutsModified() {
        return !is_null($this->layouts) && $this->layouts->isModified();
    }

    /**
     * @return string
     */
    public function getLocation() {
        if (!isset($this->location)) {
            $this->location = ProductDAO::getInstance()->getLocation($this->id);
        }
        return $this->location;
    }

    public function setLocation($value) {
        $this->location = $value;
    }

    /**
     * @return int
     */
    public function getManufacturerId() {
        if (!isset($this->manufacturerId)) {
            $this->manufacturerId = new Mutable(ProductDAO::getInstance()->getManufacturerId($this->id));
        }
        return $this->manufacturerId->get();
    }

    public function setManufacturerId($value) {
        $this->manufacturerId->set($value);
    }

    /**
     * @return float
     */
    public function getMinimum() {
        if (!isset($this->minimum)) {
            $this->minimum = new Mutable(ProductDAO::getInstance()->getMinimum($this->id));
        }
        return $this->minimum->get();
    }

    public function setMinimum($value) {
        $this->minimum->set($value);
    }

    /**
     * @return string
     */
    public function getModel() {
        if (!isset($this->model)) {
            $this->model = new Mutable(ProductDAO::getInstance()->getModel($this->id));
        }
        return $this->model->get();
    }

    public function setModel($value) {
        $this->model->set($value);
    }

    /**
     * @param int $languageId
     * @return string
     */
    public function getName($languageId = null) {
        if (is_null($languageId)) {
            $languageId = $this->defaultLanguageId;
        }
        return $this->getDescription()->getDescription($languageId)->getName();
    }

    /**
     * @return ProductOptionCollection
     */
    public function getOptions() {
        if (!isset($this->productOptions)) {
            $tempProductOptions = new ProductOptionCollection();
            foreach (ProductDAO::getInstance()->getProductOptions($this->id) as $option) {
                $tempProductOptions->attach($option);
            }
            $this->productOptions = new Mutable($tempProductOptions);
        }
        return $this->productOptions->get();
    }

    /**
     * @return bool
     */
    public function isOptionsModified() {
        return !is_null($this->productOptions) && $this->productOptions->isModified();
    }

    /**
     * @return int
     */
    public function getPoints() {
        if (!isset($this->points)) {
            $this->points = new Mutable(ProductDAO::getInstance()->getPoints($this->id));
        }
        return $this->points->get();
    }

    public function setPoints($value) {
        $this->points->set($value);
    }

    /**
     * @return float
     */
    public function getPrice() {
        if (!isset($this->price)) {
            $this->price = new Mutable(ProductDAO::getInstance()->getPrice($this->id));
        }
        return $this->price->get();
    }

    public function setPrice($value) {
        $this->price->set($value);
    }

    /**
     * @return int|null
     */
    public function getQuantity() {
        if (!isset($this->quantity)) {
            $this->quantity = new Mutable(ProductDAO::getInstance()->getQuantity($this->id));
        }
        return $this->quantity->get();
    }

    public function setQuantity($value) {
        $this->quantity->set($value);
    }

    /**
     * @return int[]
     */
    public function getRelated() {
        if (!isset($this->related)) {
            $this->related = new Mutable(ProductDAO::getInstance()->getProductRelated($this->id));
        }
        return $this->related->get();
    }

    /**
     * @return bool
     */
    public function isRelatedModified() {
        return !is_null($this->related) && $this->related->isModified();
    }

    /**
     * @return array
     */
    public function getRewards() {
        if (!isset($this->rewards)) {
            $this->rewards = new Mutable(ProductDAO::getInstance()->getProductRewards($this->id));
        }
        return $this->rewards->get();
    }

    /**
     * @return bool
     */
    public function isRewardsModified() {
        return !is_null($this->rewards) && $this->rewards->isModified();
    }

    /**
     * @return float
     */
    public function getShipping() {
        if (!isset($this->shipping)) {
            $this->shipping = new Mutable(ProductDAO::getInstance()->getShipping($this->id));
        }
        return $this->shipping->get();
    }

    public function setShipping($value) {
        $this->shipping->set($value);
    }

    /**
     * @return mixed
     */
    public function getSku() {
        if (!isset($this->sku)) {
            $this->sku = new Mutable(ProductDAO::getInstance()->getSku($this->id));
        }
        return $this->sku->get();
    }

    public function setSku($value) {
        $this->sku->set($value);
    }

    /**
     * @return int
     */
    public function getSortOrder() {
        if (!isset($this->sortOrder)) {
            $this->sortOrder = new Mutable(ProductDAO::getInstance()->getSortOrder($this->id));
        }
        return $this->sortOrder->get();
    }

    public function setSortOrder($value) {
        $this->sortOrder->set($value);
    }

    /**
     * @return array
     */
    public function getSpecials() {
        if (!isset($this->specials)) {
            $this->specials = new Mutable(ProductDAO::getInstance()->getProductSpecials($this->id));
        }
        return $this->specials->get();
    }

    /**
     * @return bool
     */
    public function isSpecialsModified() {
        return !is_null($this->specials) && $this->specials->isModified();
    }

    /**
     * @return mixed
     */
    public function getStatus() {
        if (!isset($this->status)) {
            $this->status = new Mutable(ProductDAO::getInstance()->getStatus($this->id));
        }
        return $this->status->get();
    }

    public function setStatus($value) {
        $this->status->set($value);
    }

    /**
     * @return int
     */
    public function getStockStatusId() {
        if (!isset($this->stockStatusId)) {
            $this->stockStatusId = new Mutable(ProductDAO::getInstance()->getStockStatusId($this->id));
        }
        return $this->stockStatusId->get();
    }

    public function setStockStatusId($value) {
        $this->stockStatusId->set($value);
    }


    /**
     * @return int[]
     */
    public function getStores() {
        if (!isset($this->stores)) {
            $this->stores = new Mutable(ProductDAO::getInstance()->getStores($this->id));
        }
        return $this->stores->get();
    }

    public function isStoresModified() {
        return !is_null($this->stores) && $this->stores->isModified();
    }

    public function setStores($value) {
        $this->stores->set($value);
    }

    /**
     * @return int
     */
    public function getSubtract() {
        if (!isset($this->subtract)) {
            $this->subtract = new Mutable(ProductDAO::getInstance()->getSubtract($this->id));
        }
        return $this->subtract->get();
    }

    public function setSubtract($value) {
        $this->subtract->set($value);
    }

    /**
     * @return Supplier
     */
    public function getSupplier() {
        if (!isset($this->supplier)) {
            $this->supplier = new Mutable(ProductDAO::getInstance()->getSupplier($this->id));
        }
        return $this->supplier->get();
    }

    public function getSupplierUrl() {
        if (!isset($this->supplierUrl)) {
            $this->supplierUrl = new Mutable(ProductDAO::getInstance()->getSupplierUrl($this->id));
        }
        return $this->supplierUrl->get();
    }

    public function setSupplierUrl($value) {
        $this->supplierUrl->set($value);
    }

    /**
     * @return string[]
     */
    public function getTags() {
        if (!isset($this->tags)) {
            $this->tags = new Mutable(ProductDAO::getInstance()->getProductTags($this->id));
        }
        return $this->tags->get();

    }

    /**
     * @return bool
     */
    public function isTagsModified() {
        return !is_null($this->tags) && $this->tags->isModified();
    }

    /**
     * @return mixed
     */
    public function getUpc() {
        if (!isset($this->upc)) {
            $this->upc = new Mutable(ProductDAO::getInstance()->getUpc($this->id));
        }
        return $this->upc->get();
    }

    public function setUpc($value) {
        $this->upc->set($value);
    }

    /**
     * @return int
     */
    public function getUserId() {
        if (!isset($this->userId)) {
            $this->userId = new Mutable(ProductDAO::getInstance()->getUserId($this->id));
        }
        return $this->userId->get();
    }

    /**
     * @param int $value
     */
    public function setUserId($value) {
        $this->userId->set($value);
    }

    /**
     * @return int
     */
    public function getViewed() {
        if (!isset($this->viewed)) {
            $this->viewed = new Mutable(ProductDAO::getInstance()->getViewed($this->id));
        }
        return $this->viewed->get();
    }

    /**
     * @param int $value
     */
    public function setViewed($value) {
        $this->viewed->set($value);
    }

    /**
     * @return Weight
     */
    public function getWeight() {
        if (!isset($this->weight)) {
            $this->weight = new Mutable(ProductDAO::getInstance()->getWeight($this->id));
        }
        return $this->weight->get();
    }

    /**
     * @param Weight $value
     */
    public function setWeight($value) {
        $this->weight->set($value);
    }
}
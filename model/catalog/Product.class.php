<?php
namespace model\catalog;

use Exception;
use model\localization\DescriptionCollection;
use model\user\UserDAO;
use system\helper\ImageService;
use system\library\Dimensions;
use system\library\Mutable;
use system\library\Weight;

class Product {
    private $id;
    /** @var Mutable */
    private $attributes;
    /** @var Mutable */
    private $auctions;
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
    private $manufacturerId;
    private $model;
    private $points;
    /** @var Mutable */
    private $productOptions;
    private $rating;
    /** @var  Mutable */
    private $related;
    /** @var  Mutable */
    private $rewards;
    private $sku;
    private $upc;
    private $location;
    private $quantity;
    private $stockStatusId;
    /** @var Mutable */
    private $stores;
    private $shipping;
    /** @var  Mutable */
    private $specials;
    private $supplier;
    private $supplierUrl;
    /** @var Mutable  */
    private $tags;
    private $price;
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

    private $saved;

    /**
     * @param $id
     * @param int $defaultLanguageId
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
     * @param ProductCategory[] $categories
     * @param Product[]|int[] $related
     * @param array $layouts
     * @param array $rewards
     * @param string $imageDescription
     * @param Auction[] $auctions
     */
    public function __construct($id, $defaultLanguageId, $afcId = null, $affiliateCommission = null, $dateAdded = null, $dateAvailable = null, $dateModified = null,
                                $description = null, $dimension = null, $imagePath = null, $keyword = null, $koreanName = null, $location = null,
                                $manufacturerId = null, $minimum = null, $model = null, $productOptions = null, $points = null,
                                $price = null, $quantity = null, $shipping = null, $sku = null, $sortOrder = null, $status = null,
                                $stockStatusId = null, $stores = null, $subtract = null, $supplier = null, $supplierUrl = null,
                                $tag = null, $upc = null, $userId = null, $viewed = null, $weight = null, $attributes = null,
                                $discounts = null, $specials = null, $downloads = null, $categories = null, $related = null,
                                $layouts = null, $rewards = null, $imageDescription = null, $auctions = null) {
        $this->id = $id;
        $this->saved = $this->id != 0;
        if (!is_null($afcId)) { $this->afcId = $afcId; }
        if (!is_null($affiliateCommission)) { $this->affiliateCommission = $affiliateCommission; }
        if (!is_null($attributes)) { $this->attributes = new Mutable($attributes); }
        if (!is_null($auctions)) { $this->auctions = new Mutable($auctions); }
        if (!is_null($categories)) { $this->categories = new Mutable($categories); }
        if (!is_null($dateAdded)) { $this->dateAdded = $dateAdded; }
        if (!is_null($dateAvailable)) { $this->dateAvailable = $dateAvailable; }
        if (!is_null($dateModified)) { $this->dateModified = $dateModified; }
        $this->defaultLanguageId = $defaultLanguageId;
        if (!is_null($description)) { $this->description = new Mutable($description); }
        if (!is_null($dimension)) { $this->dimension = $dimension; }
        if (!is_null($discounts)) { $this->discounts = new Mutable($discounts); }
        if (!is_null($downloads)) { $this->downloads = new Mutable($downloads); }
        if (!is_null($imageDescription)) { $this->imageDescription = $imageDescription; }
        if (!is_null($imagePath)) { $this->imagePath = $imagePath; }
        if (!is_null($keyword)) { $this->keyword = $keyword; }
        if (!is_null($koreanName)) { $this->koreanName = $koreanName; }
        if (!is_null($location)) { $this->location = $location; }
        if (!is_null($manufacturerId)) { $this->manufacturerId = $manufacturerId; }
        if (!is_null($minimum)) { $this->minimum = $minimum; }
        if (!is_null($model)) { $this->model = $model; }
        if (!is_null($productOptions)) {
            $tempProductOptions = new ProductOptionCollection();
            foreach ($productOptions as $option) {
                $tempProductOptions->attach($option);
            }
            $this->productOptions = new Mutable($tempProductOptions);
        }
        if (!is_null($points)) { $this->points = $points; }
        if (!is_null($price)) { $this->price = $price; }
        if (!is_null($quantity)) { $this->quantity = $quantity; }
        if (!is_null($related) && is_array($related)) {
            if ($related[0] instanceof Product) {
                $this->related = new Mutable($related);
            } else {
                $relatedProducts = [];
                foreach ($related as $relatedProductId) {
                    $relatedProducts[] = new Product($relatedProductId, $defaultLanguageId);
                }
                $this->related = new Mutable($relatedProducts);
            }
        }
        if (!is_null($rewards)) { $this->rewards = new Mutable($rewards); }
        if (!is_null($shipping)) { $this->shipping = $shipping; }
        if (!is_null($sku)) { $this->sku = $sku; }
        if (!is_null($sortOrder)) { $this->sortOrder = $sortOrder; }
        if (!is_null($specials)) { $this->specials = new Mutable($specials); }
        if (!is_null($status)) { $this->status = $status; }
        if (!is_null($stockStatusId)) { $this->stockStatusId= $stockStatusId; }
        if (!is_null($stores)) { $this->stores= new Mutable($stores); }
        if (!is_null($subtract)) { $this->subtract = $subtract; }
        if (!is_null($supplier)) { $this->supplier = $supplier; }
        if (!is_null($supplierUrl)) { $this->supplierUrl = $supplierUrl; }
        if (!is_null($tag)) { $this->tags = new Mutable($tag); }
        if (!is_null($upc)) { $this->upc = $upc; }
        if (!is_null($userId)) { $this->userId = $userId; }
        if (!is_null($viewed)) { $this->viewed = $viewed; }
        if (!is_null($weight)) { $this->weight = $weight; }
    }
    
    public function __destruct() {
        $this->attributes = null;
        $this->categories = null;
        $this->description = null;
        $this->discounts = null;
        $this->downloads = null;
        $this->images = null;
        $this->layouts = null;
        $this->productOptions = null;
        $this->stores = null;
        $this->related = null;
        $this->rewards = null;
        $this->specials = null;
        $this->tags = null;
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
            $this->affiliateCommission = ProductDAO::getInstance()->getAffiliateCommission($this->id);
        }
        return $this->affiliateCommission;
    }

    public function setAffiliateCommission($value) {
        $this->affiliateCommission = $value;
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
        return !$this->saved || !is_null($this->attributes) && $this->attributes->isModified();
    }

    /**
     * @param array $value
     */
    public function setAttributes($value) {
        if (!isset($this->attributes)) {
            $this->attributes = new Mutable($value);
        } else {
            $this->attributes->set($value);
        }
    }

    /**
     * @return Auction[]
     */
    public function getAuctions() {
        if (!isset($this->auctions)) {
            $this->auctions = new Mutable(ProductDAO::getInstance()->getAuctions($this->id));
        }
        return $this->auctions->get();
    }

    /**
     * @return bool
     */
    public function isAuctionsModified() {
        return !$this->saved || !is_null($this->auctions) && $this->auctions->isModified();
    }

    /**
     * @param Auction[] $value
     */
    public function setAuctions($value) {
        if (!isset($this->auctions)) {
            $this->auctions = new Mutable($value);
        } else {
            $this->auctions->set($value);
        }
    }
    /**
     * @return ProductCategory[]
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
        return !$this->saved || !is_null($this->categories) && $this->categories->isModified();
    }

    /**
     * @param array $value
     */
    public function setCategories($value) {
        if (!isset($this->categories)) {
            $this->categories = new Mutable($value);
        } else {
            $this->categories->set($value);
        }
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
        return !$this->saved || !is_null($this->description) && $this->description->isModified();
    }

    /**
     * @param DescriptionCollection $value
     */
    public function setDescription($value) {
        if (!isset($this->description)) {
            $this->description = new Mutable($value);
        } else {
            $this->description->set($value);
        };
    }

    /**
     * @return Dimensions
     */
    public function getDimension() {
        if (!isset($this->dimension)) {
            $this->dimension = ProductDAO::getInstance()->getDimension($this->id);
        }
        return $this->dimension;
    }

    /**
     * @param Dimensions $value
     */
    public function setDimension($value) {
        $this->dimension = $value;
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
        return !$this->saved || !is_null($this->discounts) && $this->discounts->isModified();
    }

    /**
     * @param array $value
     */
    public function setDiscounts($value) {
        if (!isset($this->discounts)) {
            $this->discounts = new Mutable($value);
        } else {
            $this->discounts->set($value);
        };
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
        return !$this->saved || !is_null($this->downloads) && $this->downloads->isModified();
    }

    /**
     * @param array $value
     */
    public function setDownloads($value) {
        if (!isset($this->downloads)) {
            $this->downloads = new Mutable($value);
        } else {
            $this->downloads->set($value);
        }    }

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param int $value
     * @throws Exception
     */
    public function setId($value) {
        if ($this->id != 0) {
            throw new Exception("Can not change existing product ID");
        }
        $this->id = $value;
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
     * @param string $value
     */
    public function setImageDescription($value) {
        $this->imageDescription = $value;
    }

    /**
     * @return string
     */
    public function getImagePath() {
        if (!isset($this->imagePath)) {
            $this->imagePath = ProductDAO::getInstance()->getImage($this->id);
        }
        return $this->imagePath;
    }

    public function setImagePath($value) {
        $this->imagePath = $value;
    }

    /**
     * @return ProductImage[]
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
        return !$this->saved || !is_null($this->images) && $this->images->isModified();
    }

    public function setImages($value) {
        if (!isset($this->images)) {
            $this->images = new Mutable($value);
        } else {
            $this->images->set($value);
        }
    }

    public function getKeyword() {
        if (!isset($this->keyword)) {
            $this->keyword = ProductDAO::getInstance()->getKeyword($this->id);
        }
        return $this->keyword;
    }

    /**
     * @return string
     */
    public function getKoreanName() {
        if (!isset($this->koreanName)) {
            $this->koreanName = ProductDAO::getInstance()->getKoreanName($this->id);
        }
        return $this->koreanName;
    }

    public function setKoreanName($value) {
        $this->koreanName = $value;
    }

    /**
     * @return mixed
     */
    public function getLayouts() {
        if (!isset($this->layouts)) {
            $this->layouts = new Mutable(ProductDAO::getInstance()->getProductLayoutId($this->id));
        }
        return $this->layouts->get();
    }

    /**
     * @return bool
     */
    public function isLayoutsModified() {
        return !$this->saved || !is_null($this->layouts) && $this->layouts->isModified();
    }

    /**
     * @param array $value
     */
    public function setLayouts($value) {
        if (!isset($this->layouts)) {
            $this->layouts = new Mutable($value);
        } else {
            $this->layouts->set($value);
        }
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
     * @return Category
     */
    public function getMainCategory() {
        foreach ($this->getCategories() as $category) {
            if ($category->isMain()) {
                return $category->getCategory();
            }
        }
        return null;
    }

    /**
     * @param Category|int $category
     * @return bool
     */
    public function isMainCategory($category) {
        if ($category instanceof Category) {
            $category = $category->getId();
        }
        return !is_null($this->getMainCategory()) && ($this->getMainCategory()->getId() == $category);
    }

    /**
     * @return int
     */
    public function getManufacturerId() {
        if (!isset($this->manufacturerId)) {
            $this->manufacturerId = ProductDAO::getInstance()->getManufacturerId($this->id);
        }
        return $this->manufacturerId;
    }

    public function setManufacturerId($value) {
        $this->manufacturerId = $value;
    }

    /**
     * @return float
     */
    public function getMinimum() {
        if (!isset($this->minimum)) {
            $this->minimum = ProductDAO::getInstance()->getMinimum($this->id);
        }
        return $this->minimum;
    }

    public function setMinimum($value) {
        $this->minimum = $value;
    }

    /**
     * @return string
     */
    public function getModel() {
        if (!isset($this->model)) {
            $this->model = ProductDAO::getInstance()->getModel($this->id);
        }
        return $this->model;
    }

    public function setModel($value) {
        $this->model = $value;
    }

    /**
     * @param int $languageId
     * @return string
     */
    public function getName($languageId = null) {
        if (is_null($languageId)) {
            $languageId = $this->defaultLanguageId;
        }
        return is_null($this->getDescription()->getDescription($languageId)) ? '' : $this->getDescription()->getDescription($languageId)->getName();
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
        return !$this->saved || !is_null($this->productOptions) && $this->productOptions->isModified();
    }

    public function setOptions($value) {
        if (!isset($this->productOptions)) {
            $this->productOptions = new Mutable($value);
        } else {
            $this->productOptions->set($value);
        }
    }
    /**
     * @return int
     */
    public function getPoints() {
        if (!isset($this->points)) {
            $this->points = ProductDAO::getInstance()->getPoints($this->id);
        }
        return $this->points;
    }

    /**
     * @param int $value
     */
    public function setPoints($value) {
        $this->points = $value;
    }

    /**
     * @return float
     */
    public function getPrice() {
        if (!isset($this->price)) {
            $this->price = ProductDAO::getInstance()->getPrice($this->id);
        }
        return $this->price;
    }

    public function setPrice($value) {
        $this->price = $value;
    }

    /**
     * @return int|null
     */
    public function getQuantity() {
        if (!isset($this->quantity)) {
            $this->quantity = ProductDAO::getInstance()->getQuantity($this->id);
        }
        return $this->quantity;
    }

    public function setQuantity($value) {
        $this->quantity = $value;
    }

    /**
     * @return bool|float False if rating can't be calculated yet
     */
    public function getRating() {
        if (!isset($this->rating)) {
            $this->rating = ProductDAO::getInstance()->getProductRating($this->id);
        }
        return $this->rating;
    }

    /**
     * @return Product[]
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
        return !$this->saved || !is_null($this->related) && $this->related->isModified();
    }

    /**
     * @param array $value
     */
    public function setRelated($value) {
        if (!isset($this->related)) {
            $this->related = new Mutable($value);
        } else {
            $this->related->set($value);
        }
    }

    /**
     * @return int
     */
    public function getReviewsCount() {
        if (!isset($this->reviewsCount)) {
            $this->reviewsCount = ProductDAO::getInstance()->getProductReviewsCount($this->id);
        }
        return $this->reviewsCount;
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
        return !$this->saved || !is_null($this->rewards) && $this->rewards->isModified();
    }

    /**
     * @param array $value
     */
    public function setRewards($value) {
        if (!isset($this->rewards)) {
            $this->related = new Mutable($value);
        } else {
            $this->rewards->set($value);
        }    }

    /**
     * @return float
     */
    public function getShipping() {
        if (!isset($this->shipping)) {
            $this->shipping = ProductDAO::getInstance()->getShipping($this->id);
        }
        return $this->shipping;
    }

    public function setShipping($value) {
        $this->shipping = $value;
    }

    /**
     * @return mixed
     */
    public function getSku() {
        if (!isset($this->sku)) {
            $this->sku = ProductDAO::getInstance()->getSku($this->id);
        }
        return $this->sku;
    }

    public function setSku($value) {
        $this->sku = $value;
    }

    /**
     * @return int
     */
    public function getSortOrder() {
        if (!isset($this->sortOrder)) {
            $this->sortOrder = ProductDAO::getInstance()->getSortOrder($this->id);
        }
        return $this->sortOrder;
    }

    public function setSortOrder($value) {
        $this->sortOrder = $value;
    }

    public function getSpecialPrice($customerGroupId) {
        return ProductDAO::getInstance()->getProductActiveSpecial($this->id, $customerGroupId);
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
        return !$this->saved || !is_null($this->specials) && $this->specials->isModified();
    }

    /**
     * @param array $value
     */
    public function setSpecials($value) {
        if (!isset($this->specials)) {
            $this->specials = new Mutable($value);
        } else {
            $this->specials->set($value);
        }    }

    /**
     * @return mixed
     */
    public function getStatus() {
        if (!isset($this->status)) {
            $this->status = ProductDAO::getInstance()->getStatus($this->id);
        }
        return $this->status;
    }

    public function setStatus($value) {
        $this->status = $value;
    }

    /**
     * @return int
     */
    public function getStockStatusId() {
        if (!isset($this->stockStatusId)) {
            $this->stockStatusId = ProductDAO::getInstance()->getStockStatusId($this->id);
        }
        return $this->stockStatusId;
    }

    public function setStockStatusId($value) {
        $this->stockStatusId = $value;
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
        return !$this->saved || !is_null($this->stores) && $this->stores->isModified();
    }

    /**
     * @param int[] $value
     */
    public function setStores($value) {
        if (!isset($this->stores)) {
            $this->stores = new Mutable($value);
        } else {
            $this->stores->set($value);
        }
    }

    /**
     * @return int
     */
    public function getSubtract() {
        if (!isset($this->subtract)) {
            $this->subtract = ProductDAO::getInstance()->getSubtract($this->id);
        }
        return $this->subtract;
    }

    public function setSubtract($value) {
        $this->subtract = $value;
    }

    /**
     * @return Supplier
     */
    public function getSupplier() {
        if (!isset($this->supplier)) {
            $this->supplier = ProductDAO::getInstance()->getSupplier($this->id);
        }
        return $this->supplier;
    }

    /**
     * @param Supplier $value
     */
    public function setSupplier($value) {
        $this->supplier = $value;
    }

    public function getSupplierUrl() {
        if (!isset($this->supplierUrl)) {
            $this->supplierUrl = ProductDAO::getInstance()->getSupplierUrl($this->id);
        }
        return $this->supplierUrl;
    }

    public function setSupplierUrl($value) {
        $this->supplierUrl = $value;
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
        return !$this->saved || !is_null($this->tags) && $this->tags->isModified();
    }

    /**
     * @return string Path to generated thumbnail
     */
    public function getThumb() {
        if (file_exists(DIR_IMAGE . $this->imagePath)) {
            return ImageService::getInstance()->resize($this->imagePath, 100, 100);
        } else {
            return ImageService::getInstance()->resize('no_image.jpg', 100, 100);
        }
    }

    /**
     * @param array $value
     */
    public function setTags($value) {
        if (!isset($this->tags)) {
            $this->tags = new Mutable($value);
        } else {
            $this->tags->set($value);
        }    }

    /**
     * @return mixed
     */
    public function getUpc() {
        if (!isset($this->upc)) {
            $this->upc = ProductDAO::getInstance()->getUpc($this->id);
        }
        return $this->upc;
    }

    public function setUpc($value) {
        $this->upc = $value;
    }

    /**
     * @return int
     */
    public function getUserId() {
        if (!isset($this->userId)) {
            $this->userId = ProductDAO::getInstance()->getUserId($this->id);
        }
        return $this->userId;
    }

    public function getUser() {
        return UserDAO::getInstance()->getUserById($this->getUserId());
    }

    /**
     * @param int $value
     */
    public function setUserId($value) {
        $this->userId = $value;
    }

    /**
     * @return int
     */
    public function getViewed() {
        if (!isset($this->viewed)) {
            $this->viewed = ProductDAO::getInstance()->getViewed($this->id);
        }
        return $this->viewed;
    }

    /**
     * @param int $value
     */
    public function setViewed($value) {
        $this->viewed = $value;
    }

    /**
     * @return Weight
     */
    public function getWeight() {
        if (!isset($this->weight)) {
            $this->weight = ProductDAO::getInstance()->getWeight($this->id);
        }
        return $this->weight;
    }

    /**
     * @param Weight $value
     */
    public function setWeight($value) {
        $this->weight = $value;
    }
}
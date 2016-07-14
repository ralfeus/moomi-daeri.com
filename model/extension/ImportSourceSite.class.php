<?php
namespace model\extension;

use model\catalog\ImportCategory;
use model\catalog\Manufacturer;
use model\catalog\Supplier;
use system\library\Mutable;

class ImportSourceSite {
//    private $id;
    private $categoriesMap;
    private $className;
    private $defaultCategories;
    private $defaultItemWeight;
    private $defaultManufacturer;
    private $defaultSupplier;
    private $importMappedCategoriesOnly;
    private $name;
    private $regularCustomerPriceRate;
    private $stores;
    private $wholesaleCustomerPriceRate;

    /**
     * @param string $className
     * @param ImportCategory[] $categoriesMap
     * @param int[] $defaultCategories
     * @param int $defaultItemWeight
     * @param int|Manufacturer $defaultManufacturer
     * @param int $defaultSupplier
     * @param bool $importMappedCategoriesOnly
     * @param string $name
     * @param float $regularCustomerPriceRate
     * @param int[] $stores
     * @param float $wholesaleCustomerPriceRate
     */
    function __construct($className, $categoriesMap = null, $defaultCategories = null, $defaultManufacturer = null,
                         $defaultSupplier = null, $importMappedCategoriesOnly = null, $name = null, $regularCustomerPriceRate = null,
                         $stores = null, $wholesaleCustomerPriceRate = null, $defaultItemWeight = null) {
        $this->className = $className;
        if (!is_null($categoriesMap)) {
            $this->categoriesMap = $categoriesMap;
            foreach ($this->categoriesMap as $category) {
                $category->setSourceSite($this);
            }
        }
        if (!is_null($defaultCategories)) {
            $this->defaultCategories = new Mutable($defaultCategories);
        }
        if (!is_null($defaultManufacturer)) {
            if ($defaultManufacturer instanceof Manufacturer) {
                $this->defaultManufacturer = new Mutable($defaultManufacturer);
            } else {
                $this->defaultManufacturer = new Mutable(new Manufacturer($defaultManufacturer));
            }
        }
        if (!is_null($defaultSupplier)) {
            if ($defaultSupplier instanceof Supplier) {
                $this->defaultSupplier = new Mutable($defaultSupplier);
            } else {
                $this->defaultSupplier = new Mutable(new Supplier($defaultSupplier));
            }
        }
        if (!is_null($importMappedCategoriesOnly)) {
            $this->importMappedCategoriesOnly = $importMappedCategoriesOnly;
        }
        if (!is_null($name)) {
            $this->name = new Mutable($name);
        }
        $this->regularCustomerPriceRate = floatval($regularCustomerPriceRate)
            ? new Mutable($regularCustomerPriceRate)
            : new Mutable(IMPORT_PRICE_RATE_NORMAL_CUSTOMERS);
        if (!is_null($stores)) {
            $this->stores = new Mutable($stores);
        }
        $this->wholesaleCustomerPriceRate = floatval($wholesaleCustomerPriceRate)
            ? new Mutable($wholesaleCustomerPriceRate)
            : new Mutable(IMPORT_PRICE_RATE_WHOLESALES_CUSTOMERS);
        if (!is_null($defaultItemWeight)) {
            $this->defaultItemWeight = $defaultItemWeight;
        }
    }

    /**
     * @return ImportCategory[]
     */
    public function getCategoriesMap() {
        if (!isset($this->categoriesMap)) {
            $this->categoriesMap = ImportSourceSiteDAO::getInstance()->getCategoriesMap($this->className);
        }
        return $this->categoriesMap;
    }

    /**
     * @param array $value
     */
    public function setCategoriesMap($value) {
        $this->categoriesMap->set($value);
    }

    /**
     * @return string
     */
    public function getClassName() {
        return $this->className;
    }

    /**
     * @return int[]
     */
    public function getDefaultCategories() {
        if (!isset($this->defaultCategories)) {
            $this->defaultCategories = new Mutable(ImportSourceSiteDAO::getInstance()->getDefaultCategories($this->className));
        }
        return $this->defaultCategories->get();
    }

    /**
     * @param int[] $value
     */
    public function setDefaultCategories($value) {
        $this->categoriesMap->set($value);
    }

    /**
     * @return Manufacturer
     */
    public function getDefaultManufacturer() {
        if (!isset($this->defaultManufacturer)) {
            $this->defaultManufacturer = new Mutable(ImportSourceSiteDAO::getInstance()->getDefaultManufacturer($this->className));
        }
        return $this->defaultManufacturer->get();
    }

    /**
     * @param int $value
     */
    public function setDefaultManufacturer($value) {
        $this->defaultManufacturer->set($value);
    }

    /**
     * @return Supplier
     */
    public function getDefaultSupplier() {
        if (!isset($this->defaultSupplier)) {
            $this->defaultSupplier = new Mutable(ImportSourceSiteDAO::getInstance()->getDefaultSupplier($this->className));
        }
        return $this->defaultSupplier->get();
    }

    /**
     * @param array $value
     */
    public function setDefaultSupplier($value) {
        $this->defaultSupplier->set($value);
    }
//
//    /**
//     * @return int
//     */
//    public function getId() {
//        return $this->id;
//    }

    /**
     * @return bool
     */
    public function isImportMappedCategoriesOnly() {
        if (!isset($this->importMappedCategoriesOnly)) {
            $this->importMappedCategoriesOnly = ImportSourceSiteDAO::getInstance()->getImportMappedCategoriesOnly($this->className);
        }
        return $this->importMappedCategoriesOnly;
    }

    /**
     * @param bool $importMappedCategoriesOnly
     */
    public function setImportMappedCategoriesOnly($importMappedCategoriesOnly) {
        $this->importMappedCategoriesOnly = $importMappedCategoriesOnly;
    }

    /**
     * @return string
     */
    public function getName() {
        if (!isset($this->name)) {
            $this->name = new Mutable(ImportSourceSiteDAO::getInstance()->getName($this->className));
        }
        return $this->name->get();
    }

    /**
     * @param string $value
     */
    public function setName($value) {
        $this->name->set($value);
    }

    /**
     * @return float
     */
    public function getRegularCustomerPriceRate() {
        if (!isset($this->regularCustomerPriceRate)) {
            $this->regularCustomerPriceRate = new Mutable(ImportSourceSiteDAO::getInstance()->getRegularCustomerPriceRate($this->className));
        }
        return $this->regularCustomerPriceRate->get();
    }

    /**
     * @param float $value
     */
    public function setRegularCustomerPriceRate($value) {
        $this->regularCustomerPriceRate->set($value);
    }

    /**
     * @return int[]
     */
    public function getStores() {
        if (!isset($this->stores)) {
            $this->stores = new Mutable(ImportSourceSiteDAO::getInstance()->getStores($this->className));
        }
        return $this->stores->get();
    }

    /**
     * @param int[] $value
     */
    public function setStores($value) {
        $this->stores->set($value);
    }

    /**
     * @return float
     */
    public function getWholesaleCustomerPriceRate() {
        if (!isset($this->wholesaleCustomerPriceRate)) {
            $this->wholesaleCustomerPriceRate = new Mutable(ImportSourceSiteDAO::getInstance()->getWholesaleCustomerPriceRate($this->className));
        }
        return $this->wholesaleCustomerPriceRate->get();
    }

    /**
     * @param float $value
     */
    public function setWholesaleCustomerPriceRate($value) {
        $this->wholesaleCustomerPriceRate->set($value);
    }

    /**
     * @return int
     */
    public function getDefaultItemWeight() {
        if (!isset($this->defaultItemWeight)) {
            $this->defaultItemWeight = new Mutable(ImportSourceSiteDAO::getInstance()->getDefaultItemWeight($this->className));
        }
        return $this->defaultItemWeight;
    }

    /**
     * @param int $value
     */
    public function setDefaultItemWeight($value) {
        $this->defaultItemWeight = $value;
    }
}

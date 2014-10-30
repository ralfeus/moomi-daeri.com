<?php
namespace model\extension;

use model\catalog\ImportCategory;
use model\catalog\Manufacturer;
use model\catalog\Supplier;
use system\library\Mutable;

class ImportSourceSite {
    private $id;
    private $categoriesMap;
    private $className;
    private $defaultCategories;
    private $defaultManufacturer;
    private $defaultSupplier;
    private $importMappedCategoriesOnly;
    private $name;
    private $regularCustomerPriceRate;
    private $stores;
    private $wholesaleCustomerPriceRate;

    function __construct($id, $categoriesMap = null, $className = null, $defaultCategories = null, $defaultManufacturer = null,
                         $defaultSupplier = null, $importMappedCategoriesOnly = null, $name = null, $regularCustomerPriceRate = null,
                         $stores = null, $wholesaleCustomerPriceRate = null) {
        $this->id = $id;
        if (!is_null($categoriesMap)) { $this->categoriesMap = new Mutable($categoriesMap); }
        if (!is_null($className)) { $this->className = $className; }
        if (!is_null($defaultCategories)) { $this->defaultCategories = new Mutable($defaultCategories); }
        if (!is_null($defaultManufacturer)) { $this->defaultManufacturer = new Mutable($defaultManufacturer); }
        if (!is_null($defaultSupplier)) { $this->defaultSupplier = new Mutable($defaultSupplier); }
        if (!is_null($importMappedCategoriesOnly)) { $this->importMappedCategoriesOnly = new Mutable($importMappedCategoriesOnly); }
        if (!is_null($name)) { $this->name = new Mutable($name); }
        $this->regularCustomerPriceRate = floatval($regularCustomerPriceRate)
            ? new Mutable($regularCustomerPriceRate)
            : new Mutable(IMPORT_PRICE_RATE_NORMAL_CUSTOMERS);
        if (!is_null($stores)) { $this->stores = new Mutable($stores); }
        $this->wholesaleCustomerPriceRate = floatval($wholesaleCustomerPriceRate)
            ? new Mutable($wholesaleCustomerPriceRate)
            : new Mutable(IMPORT_PRICE_RATE_WHOLESALES_CUSTOMERS);
    }

    /**
     * @return ImportCategory[]
     */
    public function getCategoriesMap() {
        if (!isset($this->categoriesMap)) {
            $this->categoriesMap = new Mutable(ImportSourceSiteDAO::getInstance()->getCategoriesMap($this->id));
        }
        return $this->categoriesMap->get();
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
        if (!isset($this->className)) {
            $this->className = ImportSourceSiteDAO::getInstance()->getClassName($this->id);
        }
        return $this->className;
    }

    /**
     * @return int[]
     */
    public function getDefaultCategories() {
        if (!isset($this->defaultCategories)) {
            $this->defaultCategories = new Mutable(ImportSourceSiteDAO::getInstance()->getDefaultCategories($this->id));
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
            $this->defaultManufacturer = new Mutable(ImportSourceSiteDAO::getInstance()->getDefaultManufacturer($this->id));
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
            $this->defaultSupplier = new Mutable(ImportSourceSiteDAO::getInstance()->getDefaultSupplier($this->id));
        }
        return $this->defaultSupplier->get();
    }

    /**
     * @param array $value
     */
    public function setDefaultSupplier($value) {
        $this->defaultSupplier->set($value);
    }

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return Mutable
     */
    public function getImportMappedCategoriesOnly() {
        if (!isset($this->importMappedCategoriesOnly)) {
            $this->importMappedCategoriesOnly = new Mutable(ImportSourceSiteDAO::getInstance()->getImportMappedCategoriesOnly($this->id));
        }
        return $this->importMappedCategoriesOnly->get();
    }

    /**
     * @param Mutable $importMappedCategoriesOnly
     */
    public function setImportMappedCategoriesOnly($importMappedCategoriesOnly) {
        $this->importMappedCategoriesOnly->set($importMappedCategoriesOnly);
    }

    /**
     * @return string
     */
    public function getName() {
        if (!isset($this->name)) {
            $this->name = new Mutable(ImportSourceSiteDAO::getInstance()->getName($this->id));
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
            $this->regularCustomerPriceRate = new Mutable(ImportSourceSiteDAO::getInstance()->getRegularCustomerPriceRate($this->id));
        }
        return $this->regularCustomerPriceRate->get();
    }

    /**
     * @param array $value
     */
    public function setRegularCustomerPriceRate($value) {
        $this->regularCustomerPriceRate->set($value);
    }

    /**
     * @return int[]
     */
    public function getStores() {
        if (!isset($this->stores)) {
            $this->stores = new Mutable(ImportSourceSiteDAO::getInstance()->getStores($this->id));
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
     * @return string
     */
    public function getWholesaleCustomerPriceRate() {
        if (!isset($this->wholesaleCustomerPriceRate)) {
            $this->wholesaleCustomerPriceRate = new Mutable(ImportSourceSiteDAO::getInstance()->getWholesaleCustomerPriceRate($this->id));
        }
        return $this->wholesaleCustomerPriceRate->get();
    }

    /**
     * @param array $value
     */
    public function setWholesaleCustomerPriceRate($value) {
        $this->wholesaleCustomerPriceRate->set($value);
    }
}
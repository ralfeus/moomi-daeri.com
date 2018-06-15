<?php
namespace model\extension;

use automation\ProductSource;
use model\catalog\ImportCategory;
use model\catalog\Manufacturer;
use model\catalog\ManufacturerDAO;
use model\catalog\Supplier;
use model\catalog\SupplierDAO;
use model\DAO;

class ImportSourceSiteDAO extends DAO {
    /**
     * @param ImportSourceSite $sourceSite
     */
    public function addSourceSite($sourceSite) {
        $this->getDb()->query(<<<SQL
            INSERT INTO imported_source_sites
            (class_name, default_category_id, default_manufacturer_id, default_store_id, default_supplier_id, name,
            regular_customer_price_rate, wholesale_customer_price_rate, default_weight)
            VALUES(
                :className, :defaultCategoryId, :defaultManufacturerId, :defaultStoreId, :defaultSupplierId, :name, 
                :regularCustomerPriceRate, :wholesaleCustomerPriceRate, :defaultWeight)
SQL
            , array(
                ':className' => $sourceSite->getClassName(),
                ':defaultCategoryId' => implode(',', $sourceSite->getDefaultCategories()),
                ':defaultManufacturerId' => $sourceSite->getDefaultManufacturer()->getId(),
                ':defaultStoreId' => implode(',', $sourceSite->getStores()),
                ':defaultSupplierId' => $sourceSite->getDefaultSupplier()->getId(),
                ':defaultWeight' => $sourceSite->getDefaultItemWeight(),
                ':name' => $sourceSite->getName(),
                ':regularCustomerPriceRate' => $sourceSite->getRegularCustomerPriceRate(),
                ':wholesaleCustomerPriceRate' => $sourceSite->getWholesaleCustomerPriceRate()
            )
        );
        if (sizeof($sourceSite->getCategoriesMap())) {
            foreach ($sourceSite->getCategoriesMap() as $category) {
                $this->getDb()->query(<<<SQL
                    INSERT INTO imported_product_categories
                    (source_site_class_name, source_site_category_id, local_category_id, price_upper_limit)
                    VALUES (:sourceSiteClassName, :sourceSiteCategoryId, :localCategoryId, :priceUpperLimit)
SQL
                    , array(
                        ':sourceSiteClassName' => $sourceSite->getClassName(),
                        ':sourceSiteCategoryId' => $category->getSourceSiteCategoryId(),
                        ':localCategoryId' => implode(',', $category->getLocalCategoryIds()),
                        ':priceUpperLimit' => $category->getPriceUpperLimit()
                    )
                );
            }
        }
    }

    /**
     * @param string $className
     * @return ImportCategory[]
     */
    public function getCategoriesMap($className) {
        $sourceSiteClassName = 'automation\\SourceSite\\' . $className;
        /** @var ProductSource $sourceSiteClassName */
        $sourceSite = $sourceSiteClassName::getInstance();
        $query = $this->getDb()->query("
            SELECT source_site_category_id, local_category_id, price_upper_limit
            FROM imported_product_categories
            WHERE source_site_class_name = :className
            ", array(':className' => $className)
        );
        $result = array();
        foreach ($query->rows as $categoryMappingEntry) {
            $result[$categoryMappingEntry['source_site_category_id']] = new ImportCategory(
                $sourceSite,
                $categoryMappingEntry['source_site_category_id'],
                explode(',', $categoryMappingEntry['local_category_id']),
                $categoryMappingEntry['price_upper_limit'],
                null
            );
        }
        return $result;
    }

//    /**
//     * @param int $siteId
//     * @return string
//     */
//    public function getClassName($siteId) {
//        return
//            $this->getDb()->queryScalar("
//                SELECT class_name
//                FROM imported_source_sites
//                WHERE class_name = :className
//                ", array(':className' => $className)
//            );
//    }

    /**
     * @param string $className
     * @return int[]
     */
    public function getDefaultCategories($className) {
        $query = $this->getDb()->queryScalar("
            SELECT default_category_id
            FROM imported_source_sites
            WHERE class_name = :className
            ", array(':className' => $className)
        );
        return preg_split('/,/', $query);
    }

    /**
     * @param string $className
     * @return int
     */
    public function getDefaultItemWeight($className) {
        $query = $this->getDb()->queryScalar("
            SELECT default_weight
            FROM imported_source_sites
            WHERE class_name = :className
            ", array(':className' => $className)
        );
        return $query;
    }

    /**
     * @param string $className
     * @return Manufacturer
     */
    public function getDefaultManufacturer($className) {
        $manufacturerId = $this->getDb()->queryScalar("
            SELECT default_manufacturer_id
            FROM imported_source_sites
            WHERE class_name = :className
            ", array(':className' => $className)
        );
        return ManufacturerDAO::getInstance()->getManufacturer($manufacturerId, true);
    }

    /**
     * @param string $className
     * @return Supplier
     */
    public function getDefaultSupplier($className) {
        $supplierId = $this->getDb()->queryScalar("
            SELECT default_supplier_id
            FROM imported_source_sites
            WHERE class_name = :className
            ", array(':className' => $className)
        );
        return SupplierDAO::getInstance()->getSupplier($supplierId, true);
    }

    /**
     * @param string $className
     * @return bool
     */
    public function getImportMappedCategoriesOnly($className) {
        return
            $this->getDb()->queryScalar("
                SELECT import_mapped_categories_only
                FROM imported_source_sites
                WHERE class_name = :className
                ", array(':className' => $className)
            );
    }

    /**
     * @param string $className
     * @return string
     */
    public function getName($className) {
        return
            $this->getDb()->queryScalar("
                SELECT name
                FROM imported_source_sites
                WHERE class_name = :className
                ", array(':className' => $className)
            );
    }

    /**
     * @param string $className
     * @return float
     */
    public function getRegularCustomerPriceRate($className) {
        return
            $this->getDb()->queryScalar("
                SELECT regular_customer_price_rate
                FROM imported_source_sites
                WHERE class_name = :className
                ", array(':className' => $className)
            );
    }

    /**
     * @param string $sourceSiteClassName
     * @return ImportSourceSite
     */
    public function getSourceSite($sourceSiteClassName) {
//        if  (is_numeric($sourceSite)) {
//            $searchedColumn = "imported_source_site_id";
//        } else {
            $sourceSiteClassName = str_replace('automation\\SourceSite\\', '', $sourceSiteClassName);
            $searchedColumn = 'class_name';
//        }
        $recordSet = $this->getDb()->query("
            SELECT *
            FROM imported_source_sites
            WHERE $searchedColumn = :searchedValue
            ", array(":searchedValue" => $sourceSiteClassName)
        );
        return
            new ImportSourceSite(
                $recordSet->row['class_name'],
                null,
                explode(',', $recordSet->row['default_category_id']),
                ManufacturerDAO::getInstance()->getManufacturer($recordSet->row['default_manufacturer_id'], true),
                SupplierDAO::getInstance()->getSupplier($recordSet->row['default_supplier_id'], true),
                $recordSet->row['import_mapped_categories_only'],
                $recordSet->row['name'],
                $recordSet->row['regular_customer_price_rate'],
                explode(',', $recordSet->row['default_store_id']),
                $recordSet->row['wholesale_customer_price_rate'],
                $recordSet->row['default_weight']
            );
    }

    /**
     * @return ImportSourceSite[]
     */
    public function getSourceSites() {
        $query = $this->getDb()->query("SELECT * FROM imported_source_sites");
        $result = array();
        foreach ($query->rows as $siteEntry) {
            $result[] = new ImportSourceSite(
                $siteEntry['class_name'],
                null,
                preg_split('/,/', $siteEntry['default_category_id']),
                ManufacturerDAO::getInstance()->getManufacturer($siteEntry['default_manufacturer_id'], true),
                SupplierDAO::getInstance()->getSupplier($siteEntry['default_supplier_id'], true),
                $siteEntry['import_mapped_categories_only'],
                $siteEntry['name'],
                $siteEntry['regular_customer_price_rate'],
                preg_split('/,/', $siteEntry['default_store_id']),
                $siteEntry['wholesale_customer_price_rate'],
                $siteEntry['default_weight']
            );
        }
        return $result;
    }

    /**
     * @param string $className
     * @return int[]
     */
    public function getStores($className) {
        $query = $this->getDb()->queryScalar("
            SELECT default_store_id
            FROM imported_source_sites
            WHERE class_name = :className
            ", array(':className' => $className)
        );
        return preg_split('/,/', $query);
    }

    /**
     * @param string $className
     * @return float
     */
    public function getWholesaleCustomerPriceRate($className) {
        return
            $this->getDb()->queryScalar("
                SELECT wholesale_customer_price_rate
                FROM imported_source_sites
                WHERE class_name = :className
                ", array(':className' => $className)
            );
    }

    /**
     * @param ImportSourceSite|string $sourceSite
     * @return void
     */
    public function removeSourceSite($sourceSite) {
        if ($sourceSite instanceof ImportSourceSite) {
            $sourceSite = $sourceSite->getClassName();
        }
        $this->getDb()->query(<<<SQL
            DELETE iss, ipc
            FROM
                imported_source_sites AS iss
                LEFT JOIN imported_product_categories AS ipc ON iss.class_name = ipc.source_site_class_name
            WHERE iss.class_name = :className
SQL
            , array(':className' => $sourceSite)
        );
    }

    /**
     * @param ImportSourceSite $sourceSite
     * @return void
     */
    public function saveSourceSite($sourceSite) {
        $this->getDb()->query(<<<SQL
            UPDATE imported_source_sites
            SET
              default_category_id = :defaultCategoryId,
              default_manufacturer_id = :defaultManufacturerId,
              default_store_id = :defaultStoreId,
              default_supplier_id = :defaultSupplierId,
              import_mapped_categories_only = :importMappedCategoriesOnly,
              name = :name,
              regular_customer_price_rate = :regularCustomerPriceRate,
              wholesale_customer_price_rate = :wholesaleCustomerPriceRate,
              default_weight = :defaultItemWeight
            WHERE class_name = :className
SQL
            , array(
                ':defaultCategoryId' => implode(',', $sourceSite->getDefaultCategories()),
                ':defaultItemWeight' => $sourceSite->getDefaultItemWeight(),
                ':defaultManufacturerId' => $sourceSite->getDefaultManufacturer()->getId(),
                ':defaultStoreId' => implode(',', $sourceSite->getStores()),
                ':defaultSupplierId' => $sourceSite->getDefaultSupplier()->getId(),
                ':importMappedCategoriesOnly' => $sourceSite->isImportMappedCategoriesOnly(),
                ':name' => $sourceSite->getName(),
                ':regularCustomerPriceRate' => $sourceSite->getRegularCustomerPriceRate(),
                ':wholesaleCustomerPriceRate' => $sourceSite->getWholesaleCustomerPriceRate(),
                ':className' => $sourceSite->getClassName()
            )
        );
        $this->getDb()->query("
            DELETE FROM imported_product_categories WHERE source_site_class_name = :className",
            [':className' => $sourceSite->getClassName()]
        );
        if (sizeof($sourceSite->getCategoriesMap())) {
            foreach ($sourceSite->getCategoriesMap() as $category) {
                $this->getDb()->query(<<<SQL
                    INSERT INTO imported_product_categories
                    (source_site_class_name, source_site_category_id, local_category_id, price_upper_limit)
                    VALUES (:className, :sourceSiteCategoryId, :localCategoryId, :priceUpperLimit)
SQL
                    , array(
                        ':className' => $sourceSite->getClassName(),
                        ':sourceSiteCategoryId' => $category->getSourceSiteCategoryId(),
                        ':localCategoryId' => implode(',', $category->getLocalCategoryIds()),
                        ':priceUpperLimit' => $category->getPriceUpperLimit()
                    )
                );
            }
        }
    }
}
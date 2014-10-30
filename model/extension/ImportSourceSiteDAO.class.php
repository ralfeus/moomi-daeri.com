<?php
namespace model\extension;

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
            regular_customer_price_rate, wholesale_customer_price_rate)
            VALUES(?, ?, ?, ?, ?, ?, ?, ?)
SQL
            , array(
                's:' . $sourceSite->getClassName(),
                's:' . implode(',', $sourceSite->getDefaultCategories()),
                'i:' . $sourceSite->getDefaultManufacturer()->getId(),
                's:' . implode(',', $sourceSite->getStores()),
                'i:' . $sourceSite->getDefaultSupplier()->getId(),
                's:' . $sourceSite->getName(),
                'd:' . $sourceSite->getRegularCustomerPriceRate(),
                'd:' . $sourceSite->getWholesaleCustomerPriceRate()
            )
        );
        $sourceSiteId = $this->getDb()->getLastId();
        if (sizeof($sourceSite->getCategoriesMap())) {
            foreach ($sourceSite->getCategoriesMap() as $category) {
                $this->getDb()->query(<<<SQL
                    INSERT INTO imported_product_categories
                    (source_site_id, source_site_category_id, local_category_id, price_upper_limit)
                    VALUES (?, ?, ?, ?)
SQL
                    , array(
                        "i:$sourceSiteId",
                        "s:" . $category->getSourceSiteCategoryId(),
                        "s:" . implode(',', $category->getLocalCategoryIds()),
                        "d:" . $category->getPriceUpperLimit()
                    )
                );
            }
        }
    }

    /**
     * @param int $siteId
     * @return ImportCategory[]
     */
    public function getCategoriesMap($siteId) {
        $sourceSiteClassName = $this->getDb()->queryScalar(<<<SQL
            SELECT class_name
            FROM imported_source_sites
            WHERE imported_source_site_id = ?
SQL
            , array("i:$siteId")
        );
        $sourceSiteClassName = 'automation\\SourceSite\\' . $sourceSiteClassName;
        $sourceSite = $sourceSiteClassName::getInstance();
        $query = $this->getDb()->query("
            SELECT source_site_category_id, local_category_id, price_upper_limit
            FROM imported_product_categories
            WHERE source_site_id = ?
            ", array("i:$siteId")
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

    /**
     * @param int $siteId
     * @return string
     */
    public function getClassName($siteId) {
        return
            $this->getDb()->queryScalar("
                SELECT class_name
                FROM imported_source_sites
                WHERE imported_source_site_id = ?
                ", array("i:$siteId")
            );
    }

    /**
     * @param int $siteId
     * @return int[]
     */
    public function getDefaultCategories($siteId) {
        $query = $this->getDb()->queryScalar("
            SELECT default_category_id
            FROM imported_source_sites
            WHERE imported_source_site_id = ?
            ", array("i:$siteId")
        );
        return preg_split('/,/', $query);
    }

    /**
     * @param int $siteId
     * @return Manufacturer
     */
    public function getDefaultManufacturer($siteId) {
        $manufacturerId = $this->getDb()->queryScalar("
            SELECT default_manufacturer_id
            FROM imported_source_sites
            WHERE imported_source_site_id = ?
            ", array("i:$siteId")
        );
        return ManufacturerDAO::getInstance()->getManufacturer($manufacturerId, true);
    }

    /**
     * @param int $siteId
     * @return Supplier
     */
    public function getDefaultSupplier($siteId) {
        $supplierId = $this->getDb()->queryScalar("
            SELECT default_supplier_id
            FROM imported_source_sites
            WHERE imported_source_site_id = ?
            ", array("i:$siteId")
        );
        return SupplierDAO::getInstance()->getSupplier($supplierId, true);
    }

    /**
     * @param int $siteId
     * @return bool
     */
    public function getImportMappedCategoriesOnly($siteId) {
        return
            $this->getDb()->queryScalar("
                SELECT import_mapped_categories_only
                FROM imported_source_sites
                WHERE imported_source_site_id = ?
                ", array("i:$siteId")
            );
    }

    /**
     * @param int $siteId
     * @return string
     */
    public function getName($siteId) {
        return
            $this->getDb()->queryScalar("
                SELECT name
                FROM imported_source_sites
                WHERE imported_source_site_id = ?
                ", array("i:$siteId")
            );
    }

    /**
     * @param int $siteId
     * @return float
     */
    public function getRegularCustomerPriceRate($siteId) {
        return
            $this->getDb()->queryScalar("
                SELECT regular_customer_price_rate
                FROM imported_source_sites
                WHERE imported_source_site_id = ?
                ", array("i:$siteId")
            );
    }

    /**
     * @param int|string $sourceSite
     * @return ImportSourceSite
     */
    public function getSourceSite($sourceSite) {
        if  (is_int($sourceSite)) {
            $searchedColumn = "imported_source_site_id";
            $columnType = 'i';
        } else {
            $sourceSite = str_replace('automation\\SourceSite\\', '', $sourceSite);
            $searchedColumn = 'class_name';
            $columnType = 's';
        }
        $recordSet = $this->getDb()->query("
            SELECT *
            FROM imported_source_sites
            WHERE $searchedColumn = ?
            ", array("$columnType:$sourceSite")
        );
        return
            new ImportSourceSite(
                $recordSet->row['imported_source_site_id'],
                null,
                $recordSet->row['class_name'],
                explode(',', $recordSet->row['default_category_id']),
                ManufacturerDAO::getInstance()->getManufacturer($recordSet->row['default_manufacturer_id'], true),
                SupplierDAO::getInstance()->getSupplier($recordSet->row['default_supplier_id'], true),
                $recordSet->row['import_mapped_categories_only'],
                $recordSet->row['name'],
                $recordSet->row['regular_customer_price_rate'],
                explode(',', $recordSet->row['default_store_id']),
                $recordSet->row['wholesale_customer_price_rate']
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
                $siteEntry['imported_source_site_id'],
                null,
                $siteEntry['class_name'],
                preg_split('/,/', $siteEntry['default_category_id']),
                ManufacturerDAO::getInstance()->getManufacturer($siteEntry['default_manufacturer_id'], true),
                SupplierDAO::getInstance()->getSupplier($siteEntry['default_supplier_id'], true),
                $siteEntry['import_mapped_categories_only'],
                $siteEntry['name'],
                $siteEntry['regular_customer_price_rate'],
                preg_split('/,/', $siteEntry['default_store_id']),
                $siteEntry['wholesale_customer_price_id']
            );
        }
        return $result;
    }

    /**
     * @param int $siteId
     * @return int[]
     */
    public function getStores($siteId) {
        $query = $this->getDb()->queryScalar("
            SELECT default_store_id
            FROM imported_source_sites
            WHERE imported_source_site_id = ?
            ", array("i:$siteId")
        );
        return preg_split('/,/', $query);
    }

    /**
     * @param int $siteId
     * @return float
     */
    public function getWholesaleCustomerPriceRate($siteId) {
        return
            $this->getDb()->queryScalar("
                SELECT wholesale_customer_price_rate
                FROM imported_source_sites
                WHERE imported_source_site_id = ?
                ", array("i:$siteId")
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
            DELETE imported_source_sites, imported_product_categories
            FROM
                imported_source_sites AS iss
                JOIN imported_product_categories AS ipc ON iss.imported_source_site_id = ipc.source_site_id
            WHERE iss.class_name = ?
SQL
            , array('i:' . $sourceSite)
        );
    }
}
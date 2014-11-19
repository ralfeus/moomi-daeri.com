<?php
namespace model\catalog;

use model\DAO;
use model\extension\ImportSourceSiteDAO;
use \ModelCatalogProduct;

class ImportProductDAO extends DAO {
    /**
     * @param array $data
     * @return null|\stdClass
     */
    private function buildFilter(array $data) {
        $filter = ""; $params = array();
        if (isset($data['selectedItems'])) {
            $this->buildSimpleFieldFilterEntry('ip.imported_product_id', $data['selectedItems'], $filter, $params);
        }
        if (isset($data['filterIsActive'])) {
            $this->buildSimpleFieldFilterEntry('ip.active', $data['filterIsActive'], $filter, $params);
//            $filter .= ($filter ? " AND" : "") . " ip.active = " . (int)$data['filterIsActive'];
        }
        if (!empty($data['filterItem'])) {
            $filter .= ($filter ? " AND" : "") . " ip.name LIKE '%" . $this->db->escape($data['filterItem']) . "%' " .
                "OR ip.description LIKE '%" . $this->db->escape($data['filterItem']) . "%'";
        }
        if (isset($data['filterLocalProductId'])) {
            if ($data['filterLocalProductId'] == '*') {
                $filter .= ($filter ? " AND " : "") .  "product_id IS NOT NULL";
            } else {
                $this->buildSimpleFieldFilterEntry("product_id", $data['filterLocalProductId'], $filter, $params);
            }
        }
        if (!empty($data['filterSourceSiteId'])) {
            $filter .= ($filter ? " AND" : "") . " ip.source_site_id IN (" . implode(', ', $data['filterSourceSiteId']) . ")";
        }
        if (!$filter) {
            return null;
        }
        $result = new \stdClass();
        $result->filterString = $filter;
        $result->params = $params;
        return $result;
    }

    /**
     * @param ImportProduct[] $products
     */
    public function deleteImportedProducts($products) {
        /** @var \ModelCatalogProduct $modelCatalogProduct */
        $modelCatalogProduct = $this->registry->get('load')->model('catalog/product');
        foreach ($products as $productToDelete) {
            foreach ($modelCatalogProduct->getProductImages($productToDelete->getLocalProductId()) as $image) {
                if (file_exists(DIR_IMAGE . $image['image'])) {
                    unlink(DIR_IMAGE . $image['image']);
                }
            }
            $localProduct = $modelCatalogProduct->getProduct($productToDelete->getLocalProductId());
            if (file_exists(DIR_IMAGE . $localProduct['image'])) {
                unlink(DIR_IMAGE . $localProduct['image']);
            }
            $modelCatalogProduct->deleteProduct($productToDelete->getLocalProductId());
            $this->unpairImportedProduct($productToDelete->getId());
        }
    }

    /**
     * @param int $importProductId
     * @param string $columnName
     * @return mixed
     */
    private function getSingleValue($importProductId, $columnName) {
        return $this->getDb()->queryScalar("SELECT $columnName FROM imported_products WHERE imported_product_id = ?", array("i:$importProductId"));
    }

    private function getCorrespondingProductById($productId) {
        /** @var ModelCatalogProduct $modelCatalogProduct */
        $modelCatalogProduct = $this->load->model('catalog/product');
        $correspondingProduct = $productId ? $modelCatalogProduct->getProduct($productId) : null;
        if ($correspondingProduct) {
            $correspondingProductPromoPrices = $modelCatalogProduct->getProductSpecials($productId);
            $currentPromoPrice = null;
            foreach ($correspondingProductPromoPrices as $promoPrice) {
                if ($promoPrice['customer_group_id'] == 8 /* Default group */ &&
                    (strtotime($promoPrice['date_start']) < time()) &&
                    (strtotime($promoPrice['date_end']) + 86400 > time())) {
                    $currentPromoPrice = $promoPrice['price'];
                    break;
                }
            }
            $correspondingProduct['promoPrice'] = $currentPromoPrice;
        }
        return $correspondingProduct;
    }

    /**
     * @param int $importedProductId
     * @return ImportProduct
     */
    public function getImportedProduct($importedProductId) {
        $result = $this->getDb()->query("
            SELECT *
            FROM imported_products AS ip
            WHERE imported_product_id = ?
            ", array("i:$importedProductId")
        );
        if (!$result->num_rows)
            return null;
        $correspondingProduct = $this->getCorrespondingProductById($result->row['product_id']);
        return new ImportProduct(
            $result->row['imported_product_id'],
            $result->row['source_product_id'],
            $result->row['product_id'],
            $result->row['name'],
            $this->getMatchingCategories($importedProductId, $result->row['source_site_id']),
            $result->row['description'],
            $correspondingProduct ? new ImportPrice($correspondingProduct['price'], $correspondingProduct['promoPrice']) : null,
            new ImportPrice($result->row['price'], $result->row['price_promo']),
            ImportSourceSiteDAO::getInstance()->getSourceSite($result->row['source_site_id']),
            $result->row['source_url'],
            $result->row['image_url'],
            $this->getProductImages($result->row['imported_product_id']),
            $result->row['weight'],
            $result->row['time_modified'],
            $result->row['active']
        );
    }

    /**
     * @param array $data
     * @param bool $shallow
     * @return ImportProduct[]
     */
    public function getImportedProducts(array $data, $shallow = false) {
        $filter = $this->buildFilter($data);
        $limit = isset($data['start']) && isset($data['limit']) ? $this->buildLimitString($data['start'], $data['limit']) : '';
        $sql = "
            SELECT *
            FROM imported_products AS ip
            " . ($filter ? "WHERE " . $filter->filterString : '') . "
            $limit"
        ;
        $result = array();
        foreach ($this->getDb()->query($sql, $filter ? $filter->params : null)->rows as $row) {
            if ($shallow) {
                $result[] = new ImportProduct($row['imported_product_id']);
            } else {
                $correspondingProduct = $this->getCorrespondingProductById($row['product_id']);
                $result[] = new ImportProduct(
                    $row['imported_product_id'],
                    $row['source_product_id'],
                    $row['product_id'],
                    $row['name'],
                    $this->getMatchingCategories($row['imported_product_id'], $row['source_site_id']),
                    $row['description'],
                    $correspondingProduct ? new ImportPrice($correspondingProduct['price'], $correspondingProduct['promoPrice']) : null,
                    new ImportPrice($row['price'], $row['price_promo']),
                    ImportSourceSiteDAO::getInstance()->getSourceSite($row['source_site_id']),
                    $row['source_url'],
                    $row['image_url'],
                    $this->getProductImages($row['imported_product_id']),
                    $row['weight'],
                    $row['time_modified'],
                    $row['active']
                );
            }
        }
        return $result;
    }

    public function getImportedProductsQuantity(array $data) {
        $filter = $this->buildFilter($data);
        $sql = "
            SELECT COUNT(*) AS quantity
            FROM imported_products AS ip
            " . ($filter ? "WHERE " . $filter->filterString : '')
        ;
        $result = $this->getDb()->query($sql, $filter ? $filter->params : null);
        return $result->row['quantity'];
    }

    /**
     * @param int $importProductId
     * @return bool
     */
    public function getIsActive($importProductId) {
        return boolval($this->getSingleValue($importProductId, 'active'));
    }

    /**
     * @param $productId
     * @param $siteId
     * @return int[]
     */
    private function getMatchingCategories($productId, $siteId) {
        $productCategories = array();
        $productSourceCategories = ImportProductDAO::getInstance()->getSourceCategories($productId);
        $sourceSiteCategoriesMapping = ImportSourceSiteDAO::getInstance()->getCategoriesMap($siteId);
        if (sizeof($sourceSiteCategoriesMapping)) {
            foreach ($productSourceCategories as $productSourceCategory) {
                foreach ($sourceSiteCategoriesMapping as $siteCategory) {
                    if ($productSourceCategory == $siteCategory->getSourceSiteCategoryId()) {
                        $productCategories = array_merge($productCategories, $siteCategory->getLocalCategoryIds());
                        break;
                    }
                }
            }
            if (sizeof($productCategories)) {
                $tmp = array();
                foreach ($productCategories as $productCategory) {
                    if (!array_key_exists($productCategory, $tmp)) {
                        $tmp[$productCategory] = null;
                    }
                }
                $productCategories = array_keys($tmp);
            }
            sort($productCategories);
        } else {
            $productCategories = ImportSourceSiteDAO::getInstance()->getDefaultCategories($siteId);
        }
        return $productCategories;
    }

    /**
     * @param int $importProductId
     * @return int
     */
    public function getLocalProductId($importProductId) {
        return $this->getSingleValue($importProductId, 'product_id');
    }

    /**
     * @param int $importProductId
     * @return string[]
     */
    public function getSourceCategories($importProductId) {
        $result = $this->getDb()->query(<<<SQL
            SELECT source_category_id
            FROM imported_product_source_categories
            WHERE imported_product_id = ?
SQL
            , array("i:$importProductId")
        );
        $categories = array();
        foreach ($result->rows as $categoryEntry) {
            $categories[] = $categoryEntry['source_category_id'];
        }
        return $categories;
    }

    private function getProductImages($productId) {
        $sql = "
            SELECT *
            FROM imported_product_images
            WHERE imported_product_id = $productId
        ";
        $result = array();
        foreach ($this->db->query($sql)->rows as $row)
            $result[] = $row['url'];
        return $result;
    }

    public function pairImportedProduct($importedProductId, $productId)
    {
        $sql = "
            UPDATE imported_products
            SET product_id = $productId
            WHERE imported_product_id = $importedProductId
        ";
        $this->db->query($sql);
    }

    public function unpairImportedProduct($importedProductId) {
        $sql = "
            UPDATE imported_products
            SET product_id = NULL
            WHERE imported_product_id = $importedProductId
        ";
        $this->db->query($sql);
    }
}
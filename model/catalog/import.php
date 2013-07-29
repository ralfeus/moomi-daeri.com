<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dev
 * Date: 26.7.13
 * Time: 13:41
 * To change this template use File | Settings | File Templates.
 */

class ModelCatalogImport extends Model{
    private function buildFilterString(array $data)
    {
        $filter = "";
        if (!empty($data['filterSourceSiteId']))
            $filter .= ($filter ? " AND" : "") . " ip.source_site_id IN (" . implode(', ', $data['filterSourceSiteId']) . ")";
        if (!empty($data['filterItem']))
            $filter .= ($filter ? " AND" : "") . " ip.name LIKE '%" . $this->db->escape($data['filterItem']) . "%' " .
            "OR ip.description LIKE '%" . $this->db->escape($data['filterItem']) . "%'";

        return $filter;
    }

    public function getImportedProduct($importedProductId) {
        $sql = "
            SELECT
                ip.*,
                iss.imported_source_site_id, iss.name AS source_site_name, iss.default_category_id, iss.default_manufacturer_id, iss.default_supplier_id
            FROM
                " . DB_PREFIX . "imported_products AS ip
                JOIN " . DB_PREFIX . "imported_source_sites AS iss ON ip.source_site_id = iss.imported_source_site_id
            WHERE imported_product_id = $importedProductId
        ";
        $result = $this->db->query($sql);
        if (!$result->num_rows)
            return null;
        $modelCatalogProduct = $this->load->model('catalog/product');
        $correspondingProduct = $result->row['product_id'] ? $modelCatalogProduct->getProduct($result->row['product_id']) : null;
        return new ImportedProduct(
            $result->row['imported_product_id'],
            $result->row['source_product_id'],
            $result->row['product_id'],
            $result->row['name'],
            $result->row['description'],
            $correspondingProduct ? $correspondingProduct['price'] : null,
            $result->row['price'],
            new SourceSite(
                $result->row['imported_source_site_id'],
                $result->row['source_site_name'],
                $result->row['default_category_id'],
                $result->row['default_manufacturer_id'],
                $result->row['default_supplier_id']
            ),
            $result->row['source_url'],
            $result->row['image_url'],
            $this->getProductImages($result->row['imported_product_id']),
            $result->row['time_modified']
        );
    }

    public function getImportedProducts(array $data) {
        $modelCatalogProduct = $this->load->model('catalog/product');
        $filter = $this->buildFilterString($data);
        $sql = "
            SELECT
                ip.*,
                iss.imported_source_site_id, iss.name AS source_site_name, iss.default_category_id, iss.default_manufacturer_id, iss.default_supplier_id
            FROM
                " . DB_PREFIX . "imported_products AS ip
                JOIN " . DB_PREFIX . "imported_source_sites AS iss ON ip.source_site_id = iss.imported_source_site_id
            " . ($filter ? "WHERE $filter" : '') . "
            LIMIT " . $data['start'] . ", " . $data['limit']
        ;
        $result = array();
        foreach ($this->db->query($sql)->rows as $row)
        {
            $correspondingProduct = $row['product_id'] ? $modelCatalogProduct->getProduct($row['product_id']) : null;
            $result[] = new ImportedProduct(
                $row['imported_product_id'],
                $row['source_product_id'],
                $row['product_id'],
                $row['name'],
                $row['description'],
                $correspondingProduct ? $correspondingProduct['price'] : null,
                $row['price'],
                new SourceSite(
                    $row['imported_source_site_id'],
                    $row['source_site_name'],
                    $row['default_category_id'],
                    $row['default_manufacturer_id'],
                    $row['default_supplier_id']
                ),
                $row['source_url'],
                $row['image_url'],
                $this->getProductImages($row['imported_product_id']),
                $row['time_modified']
            );
        }
        return $result;
    }

    public function getImportedProductsQuantity(array $data) {
        $filter = $this->buildFilterString($data);
        $sql = "
            SELECT COUNT(*) AS quantity
            FROM " . DB_PREFIX . "imported_products AS ip
            " . ($filter ? "WHERE $filter" : '')
        ;
        $result = $this->db->query($sql);
        return $result->row['quantity'];
    }

    private function getProductImages($productId) {
        $sql = "
            SELECT *
            FROM " . DB_PREFIX . "imported_product_images
            WHERE imported_product_id = $productId
        ";
        $result = array();
        foreach ($this->db->query($sql)->rows as $row)
            $result[] = $row['url'];
        return $result;
    }

    public function getSourceSites() {
        $sql = "
            SELECT *
            FROM " . DB_PREFIX . "imported_source_sites
        ";
        $result = array();
        foreach ($this->db->query($sql)->rows as $row)
            $result[] = new SourceSite(
                $row['imported_source_site_id'],
                $row['name'],
                $row['default_category_id'],
                $row['default_manufacturer_id'],
                $row['default_supplier_id']
            );
        return $result;
    }

    public function pairImportedProduct($importedProductId, $productId)
    {
        $sql = "
            UPDATE " . DB_PREFIX . "imported_products
            SET product_id = $productId
            WHERE imported_product_id = $importedProductId
        ";
        $this->db->query($sql);
    }

    public function unpairImportedProduct($importedProductId) {
        $sql = "
            UPDATE " . DB_PREFIX . "imported_products
            SET product_id = NULL
            WHERE imported_product_id = $importedProductId
        ";
        $this->db->query($sql);
    }
}

class ImportedProduct {
    private $id;
    private $name;
    private $description;
    private $images;
    private $localPrice;
    private $sourcePrice;
    private $localProductId;
    private $sourceProductId;
    private $sourceSite;
    private $sourceUrl;
    private $thumbnailUrl;
    private $timeModified;

    public function __construct(
        $id, $sourceProductId, $localProductId, $name, $description, $localPrice, $sourcePrice, SourceSite $sourceSite,
        $sourceUrl, $thumbnailUrl, array $images, $timeModified
    ) {
        $this->id = $id;
        $this->localProductId = empty($localProductId) ? null : $localProductId;
        $this->name = $name;
        $this->description = $description;
        $this->localPrice = empty($localPrice) ? null : $localPrice;
        $this->sourcePrice = $sourcePrice;
        $this->sourceSite = $sourceSite;
        $this->sourceUrl = $sourceUrl;
        $this->sourceProductId = $sourceProductId;
        $this->thumbnailUrl = $thumbnailUrl;
        $this->images = $images;
        $this->timeModified = $timeModified;
    }

    public function getLocalProductId() { return $this->localProductId; }
    public function getThumbnailUrl() { return $this->thumbnailUrl; }
    /**
     * @return string
     */
    public function getDescription() { return $this->description; }

    /**
     * @return integer
     */
    public function getId() { return $this->id; }

    /**
     * @return array
     */
    public function getImages() { return $this->images; }

    /**
     * @return decimal
     */
    public function getLocalPrice() { return $this->localPrice; }

    /**
     * @return string
     */
    public function getName() { return $this->name; }

    /**
     * @return decimal
     */
    public function getSourcePrice() { return $this->sourcePrice; }

    /**
     * @return integer
     */
    public function getSourceProductId() { return $this->sourceProductId; }

    /**
     * @return SourceSite
     */
    public function getSourceSite() { return $this->sourceSite; }

    /**
     * @return string
     */
    public function getSourceUrl() { return $this->sourceUrl; }

    /**
     * @return datetime
     */
    public function getTimeModified() { return $this->timeModified; }
}

class SourceSite {
    private $id;
    private $name;
    private $defaultCategoryId;
    private $defaultManufacturerId;
    private $defaultStoreId;
    private $defaultSupplierId;

    function __construct($id, $name, $defaultCategoryId, $defaultManufacturerId, $defaultSupplierId)
    {
        $this->defaultCategoryId = $defaultCategoryId;
        $this->defaultManufacturerId = $defaultManufacturerId;
        $this->defaultSupplierId = $defaultSupplierId;
        $this->id = $id;
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getDefaultCategoryId() { return $this->defaultCategoryId; }

    /**
     * @return int
     */
    public function getDefaultManufacturerId() { return $this->defaultManufacturerId; }

    /**
     *  @return int
     */
    public function getDefaultStoreId() { return $this->defaultStoreId; }

    /**
     * @return int
     */
    public function getDefaultSupplierId() { return $this->defaultSupplierId; }

    /**
     * @return int
     */
    public function getId() { return $this->id; }

    /**
     * @return string
     */
    public function getName() { return $this->name; }
}
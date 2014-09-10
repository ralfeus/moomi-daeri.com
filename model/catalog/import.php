<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dev
 * Date: 26.7.13
 * Time: 13:41
 * To change this template use File | Settings | File Templates.
 */

class ModelCatalogImport extends Model{
    private $sourceSites;

    public function __construct($registry) {
        parent::__construct($registry);
//        $this->sourceSites = self::getSourceSites();
    }

    private function buildFilterString(array $data)
    {
        $filter = "";
        if (isset($data['filterIsActive']))
            $filter .= ($filter ? " AND" : "") . " ip.active = " . (int)$data['filterIsActive'];
        if (!empty($data['filterItem']))
            $filter .= ($filter ? " AND" : "") . " ip.name LIKE '%" . $this->db->escape($data['filterItem']) . "%' " .
                "OR ip.description LIKE '%" . $this->db->escape($data['filterItem']) . "%'";
        if (!empty($data['filterSourceSiteId']))
            $filter .= ($filter ? " AND" : "") . " ip.source_site_id IN (" . implode(', ', $data['filterSourceSiteId']) . ")";

        return $filter;
    }

    private function getCorrespondingProductById($productId) {
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

    public function getImportedProduct($importedProductId) {
        $sql = "
            SELECT
                ip.*,
                iss.imported_source_site_id, iss.name AS source_site_name, iss.default_category_id, iss.default_manufacturer_id, iss.default_store_id, iss.default_supplier_id, iss.regular_customer_price_rate, iss.wholesale_customer_price_rate
            FROM
                imported_products AS ip
                JOIN imported_source_sites AS iss ON ip.source_site_id = iss.imported_source_site_id
            WHERE imported_product_id = $importedProductId
        ";
        $result = $this->db->query($sql);
        if (!$result->num_rows)
            return null;
        $correspondingProduct = $this->getCorrespondingProductById($result->row['product_id']);
        return new ImportedProduct(
            $result->row['imported_product_id'],
            $result->row['source_product_id'],
            $result->row['product_id'],
            $result->row['name'],
            $result->row['source_category_id'] ? self::getMatchingCategories($result->row['source_category_id']) : array(),
            $result->row['description'],
            $correspondingProduct ? new Price($correspondingProduct['price'], $correspondingProduct['promoPrice']) : null,
            new Price($result->row['price'], $result->row['price_promo']),
            new SourceSite(
                $result->row['imported_source_site_id'],
                $result->row['source_site_name'],
                $result->row['default_category_id'],
                $result->row['default_manufacturer_id'],
                $result->row['default_store_id'],
                $result->row['default_supplier_id'],
                $result->row['regular_customer_price_rate'],
                $result->row['wholesale_customer_price_rate']
            ),
            $result->row['source_url'],
            $result->row['image_url'],
            $this->getProductImages($result->row['imported_product_id']),
            $result->row['weight'],
            $result->row['time_modified'],
            $result->row['active']
        );
    }

    public function getImportedProducts(array $data) {
        $filter = $this->buildFilterString($data);
        $sql = "
            SELECT
                ip.*,
                iss.imported_source_site_id, iss.name AS source_site_name, iss.default_category_id, iss.default_manufacturer_id, iss.default_store_id, iss.default_supplier_id, iss.regular_customer_price_rate, iss.wholesale_customer_price_rate
            FROM
                imported_products AS ip
                JOIN imported_source_sites AS iss ON ip.source_site_id = iss.imported_source_site_id
            " . ($filter ? "WHERE $filter" : '') . "
            LIMIT " . $data['start'] . ", " . $data['limit']
        ;
        $result = array();
        foreach ($this->db->query($sql)->rows as $row)
        {
            $correspondingProduct = $this->getCorrespondingProductById($row['product_id']);
            $result[] = new ImportedProduct(
                $row['imported_product_id'],
                $row['source_product_id'],
                $row['product_id'],
                $row['name'],
                $row['source_category_id'] ? self::getMatchingCategories($row['source_category_id']) : array(),
                $row['description'],
                $correspondingProduct ? new Price($correspondingProduct['price'], $correspondingProduct['promoPrice']) : null,
                new Price($row['price'], $row['price_promo']),
                new SourceSite(
                    $row['imported_source_site_id'],
                    $row['source_site_name'],
                    $row['default_category_id'],
                    $row['default_manufacturer_id'],
                    $row['default_store_id'],
                    $row['default_supplier_id'],
                    $row['regular_customer_price_rate'],
                    $row['wholesale_customer_price_rate']
                ),
                $row['source_url'],
                $row['image_url'],
                $this->getProductImages($row['imported_product_id']),
                $row['weight'],
                $row['time_modified'],
                $row['active']
            );
        }
        return $result;
    }

    public function getImportedProductsQuantity(array $data) {
        $filter = $this->buildFilterString($data);
        $sql = "
            SELECT COUNT(*) AS quantity
            FROM imported_products AS ip
            " . ($filter ? "WHERE $filter" : '')
        ;
        $result = $this->db->query($sql);
        return $result->row['quantity'];
    }

    /**
     * @param $sourceCategoryId
     * @return array
     */
    private function getMatchingCategories($sourceCategoryId) {
        $sql = "
            SELECT local_category_id
            FROM imported_product_categories
            WHERE source_site_category_id = '" . $this->db->escape($sourceCategoryId) . "'
        ";
        $categories = array();
        $result = $this->db->query($sql);
        foreach ($result->rows as $row)
            $categories[] = $row['local_category_id'];

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

    public function getSourceSites() {
        $sql = "
            SELECT *
            FROM imported_source_sites
        ";
        $result = array();
        foreach ($this->db->query($sql)->rows as $row)
            $result[] = new SourceSite(
                $row['imported_source_site_id'],
                $row['name'],
                $row['default_category_id'],
                $row['default_manufacturer_id'],
                $row['default_store_id'],
                $row['default_supplier_id'],
                $row['regular_customer_price_rate'],
                $row['wholesale_customer_price_rate']
            );
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

class ImportedProduct {
    private $id;
    private $name;
    private $categories;
    private $description;
    private $images;
    private $isActive;
    private $localPrice;
    private $sourcePrice;
    private $localProductId;
    private $sourceProductId;
    private $sourceSite;
    private $sourceUrl;
    private $thumbnailUrl;
    private $timeModified;
    private $weight;

    public function __construct(
        $id, $sourceProductId, $localProductId, $name, $categories, $description, Price $localPrice = null, Price $sourcePrice, SourceSite $sourceSite,
        $sourceUrl, $thumbnailUrl, array $images, $weight, $timeModified, $isActive
    ) {
        $this->id = $id;
        $this->localProductId = empty($localProductId) ? null : $localProductId;
        $this->categories = $categories;
        $this->name = $name;
        $this->description = $description;
        $this->isActive = $isActive;
        $this->localPrice = empty($localPrice) ? null : $localPrice;
        $this->sourcePrice = $sourcePrice;
        $this->sourceSite = $sourceSite;
        $this->sourceUrl = $sourceUrl;
        $this->sourceProductId = $sourceProductId;
        $this->thumbnailUrl = $thumbnailUrl;
        $this->images = $images;
        $this->timeModified = $timeModified;
        $this->weight = $weight;
    }

    /**
     * Returns categories of the imported product.
     * In case of absence of categories specific for certain product returns categories for source site
     *
     * @return array
     */
    public function getCategories() {
        return sizeof($this->categories) ? $this->categories : $this->sourceSite->getDefaultCategoryId();
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
     * @return bool
     */
    public function getIsActive() { return $this->isActive; }

    /**
     * @return Price
     */
    public function getLocalPrice() { return $this->localPrice; }

    /**
     * @return string
     */
    public function getName() { return $this->name; }

    /**
     * @return Price
     */
    public function getSourcePrice() { return $this->sourcePrice; }

    /**
     * @return int
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

    /**
     * @return float
     */
    public function getWeight() { return $this->weight; }
}

class SourceSite {
    private $id;
    private $name;
    private $defaultCategoryId;
    private $defaultManufacturerId;
    private $defaultStoreId;
    private $defaultSupplierId;
    private $regularCustomerPriceRate;
    private $wholesaleCustomerPriceRate;

    function __construct($id, $name, $defaultCategoryId, $defaultManufacturerId, $defaultStoreId, $defaultSupplierId,
                         $regularCustomerPriceRate = null, $wholesaleCustomerPriceRate = null) {
        $this->defaultCategoryId = explode(',', $defaultCategoryId);
        $this->defaultManufacturerId = $defaultManufacturerId;
        $this->defaultStoreId = explode(',', $defaultStoreId);
        $this->defaultSupplierId = $defaultSupplierId;
        $this->id = $id;
        $this->name = $name;
        $this->regularCustomerPriceRate = !floatval($regularCustomerPriceRate) ? IMPORT_PRICE_RATE_NORMAL_CUSTOMERS : $regularCustomerPriceRate;
        $this->wholesaleCustomerPriceRate = !floatval($wholesaleCustomerPriceRate) ? IMPORT_PRICE_RATE_WHOLESALES_CUSTOMERS : $wholesaleCustomerPriceRate;
    }

    /**
     * @return array
     */
    public function getDefaultCategoryId() { return $this->defaultCategoryId; }

    /**
     * @return int
     */
    public function getDefaultManufacturerId() { return $this->defaultManufacturerId; }

    /**
     *  List of default stores assigned to the site
     *  @return array
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

    /**
     * @return float
     */
    public function getRegularCustomerPriceRate() { return $this->regularCustomerPriceRate; }

    /**
     * @return float
     */
    public function getWholesaleCustomerPriceRate() { return $this->wholesaleCustomerPriceRate; }
}

class Price
{
    private $price;
    private $promoPrice;

    function __construct($price, $promoPrice = null)
    {
        $this->price = $price;
        $this->promoPrice = $promoPrice;
    }

    /**
     * @return float
     */
    public function getPrice() { return $this->price; }

    /**
     * @return float
     */
    public function getPromoPrice() { return $this->promoPrice; }
}
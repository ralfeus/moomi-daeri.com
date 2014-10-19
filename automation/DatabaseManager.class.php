<?php
namespace automation;

use PDO;

class DatabaseManager {
    /** @var PDO */
    private $connection;

    private static $instance;
    private function __construct() {
        $this->dbConnect();
    }

    private function dbConnect() {
        $this->connection = new PDO(
            'mysql:host=' . DB_HOSTNAME . ';dbname=' . DB_DATABASE,
            DB_USERNAME,
            DB_PASSWORD,
            array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
        );
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    }

    public static function getInstance() {
        if (!self::$instance)
            self::$instance = new DatabaseManager();
        return self::$instance;
    }

    private function addImages(Product $product) {
        $sql = "
            INSERT INTO imported_product_images
            SET
                imported_product_id = :productId,
                url = :url
            ON DUPLICATE KEY UPDATE imported_product_image_id = imported_product_image_id
        ";
        $statement = $this->connection->prepare($sql);
        foreach ($product->getImages() as $imageUrl) {
            $statement->execute(array(
                ':productId' => $product->id,
                ':url' => $imageUrl
            ));
        }
    }

    public function addProducts(ProductSource $site) {
        $sql = '
                INSERT INTO imported_products
                SET
                    source_site_id = :sourceSiteId,
                    source_category_id = :sourceCategoryId,
                    source_url = :sourceUrl,
                    source_product_id = :sourceProductId,
                    image_url = :thumbnail,
                    name = :name,
                    description = :description,
                    price = :price,
                    price_promo = :promoPrice,
                    time_modified = NOW(),
                    weight = :weight
                ON DUPLICATE KEY UPDATE
                    source_category_id = :sourceCategoryId,
                    source_url = :sourceUrl,
                    image_url = :thumbnail,
                    name = :name,
                    description = :description,
                    price = :price,
                    price_promo = :promoPrice,
                    active = TRUE,
                    time_modified = NOW(),
                    weight = :weight
            ';
        $products = $site->getProducts();
        $this->dbConnect();
        $statement = $this->connection->prepare($sql);
        echo date('Y-m-d H:i:s') . " Adding to the database " . count($products) . "\n";
        foreach ($products as $product) {
            echo date('Y-m-d H:i:s') . " Adding " . $product->sourceProductId . "\n";
            $statement->execute(array(
                ':sourceSiteId' => $site->getSite()->getId(),
                ':sourceCategoryId' => $product->categoryId,
                ':sourceUrl' => $product->url,
                ':sourceProductId' => $product->sourceProductId,
                ':thumbnail' => $product->thumbnail,
                ':name' => $product->name,
                ':description' => $product->description,
                ':price' => $product->price,
                ':promoPrice' => $product->promoPrice,
                ':weight' => $product->weight
            ));
            $product->id = $this->connection->lastInsertId();
            if ($product->id)
                self::addImages($product);
        }
        echo date('Y-m-d H:i:s') . " Added data to database\n";
    }

    /**
     * @param ProductSource $sourceSite
     * @param string $syncTime
     */
    public function cleanup($sourceSite, $syncTime) {
        $statement = $this->connection->prepare('
            UPDATE imported_products
            SET active = FALSE
            WHERE time_modified < :lastUpdateTime AND source_site_id = :sourceSiteId
        ');
        $statement->execute(array(
            ':lastUpdateTime' => date('Y-m-d H:i:s', $syncTime),
            ':sourceSiteId' => $sourceSite->getSite()->getId()
        ));
    }
}
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

    /**
     * @param Product $product
     */
    private function addCategories($product) {
        $statement = $this->connection->prepare(<<<SQL
            DELETE FROM imported_product_source_categories
            WHERE imported_product_id = :productId;
SQL
        );
        $statement->execute(array(
            ':productId' => $product->id
        ));
        $sql = <<<SQL
            INSERT INTO imported_product_source_categories
            SET
                imported_product_id = :productId,
                source_category_id = :categoryId
SQL;
        $statement = $this->connection->prepare($sql);
        foreach ($product->getCategories() as $categoryId) {
            $statement->execute(array(
                ':productId' => $product->id,
                ':categoryId' => $categoryId
            ));
        }
    }

    /**
     * @param Product $product
     */
    private function addImages($product) {
        $deleteStatement = $this->connection->prepare(<<<SQL
            DELETE FROM imported_product_images
            WHERE imported_product_id = :productId;
SQL
        );
        $deleteStatement->execute([':productId' => $product->id]);

        $sql = <<<SQL
            INSERT INTO imported_product_images
            SET
                imported_product_id = :productId,
                url = :url
            ON DUPLICATE KEY UPDATE imported_product_image_id = imported_product_image_id
SQL;
        $statement = $this->connection->prepare($sql);
        foreach ($product->getImages() as $imageUrl) {
            $statement->execute(array(
                ':productId' => $product->id,
                ':url' => $imageUrl
            ));
        }
    }

    public function addProducts(ProductSource $site) {
        $sql = <<<SQL
                INSERT INTO imported_products
                SET
                    source_site_id = :sourceSiteId,
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
                    source_url = :sourceUrl,
                    image_url = :thumbnail,
                    name = :name,
                    description = :description,
                    price = :price,
                    price_promo = :promoPrice,
                    active = TRUE,
                    time_modified = NOW(),
                    weight = :weight
SQL;
        $products = $site->getProducts();
        $this->dbConnect();
        $statement = $this->connection->prepare($sql);
        echo date('Y-m-d H:i:s') . " Adding to the database " . count($products) . " products\n";
        foreach ($products as $product) {
//            echo date('Y-m-d H:i:s') . " Adding " . $product->sourceProductId . "\n";
            $statement->execute(array(
                ':sourceSiteId' => $site->getSite()->getId(),
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
            if ($product->id) {
                $this->addImages($product);
                $this->addCategories($product);
            }
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
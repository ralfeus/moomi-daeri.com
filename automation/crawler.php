<?php
require_once("simple_html_dom.php");
require_once('../config.php');
require_once('missha.php');
require_once('natureRepublic.php');

function handleError($errno, $errstr, $errfile, $errline, array $errcontext)
{
    // error was suppressed with the @-operator
    if (0 === error_reporting()) {
        return false;
    }

    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}

abstract class ProductSource
{
    protected static $instance;

    /**
     * @param Product $product
     * @param array $list
     * @return bool
     */
    protected function addProductToList(Product $product, array &$list) {
        if ($this->getProductBySourceId($list, $product->sourceProductId) == null) {
            $list[] = $product;
            return true;
        }
        else
            return false;
    }

    /**
     * Gets HTML page by URL.
     * Represents a wrapper around the file_get_html($url) function with retry functionality
     * @param string $url
     * @return simple_html_dom
     * @throws ErrorException
     */
    protected function getHtmlDocument($url) {
        set_error_handler('handleError');
        $finalException = null;
        for ($retriesLeft = 5; $retriesLeft > 0; $retriesLeft--) {
            try {
                $html = file_get_html($url);
                restore_error_handler();
                return $html;
            }
            catch (ErrorException $exception) {
                echo "Non-fatal error has occurred. Operation will be retried\n";
                print_r($exception);
                $finalException = $exception;
            }
        }
        restore_error_handler();
        throw $finalException;
    }

    /**
     * @param array $list
     * @param int $sourceProductId
     * @return Product
     */
    protected function getProductBySourceId(array $list, $sourceProductId) {
        foreach ($list as $product) {
            if ($product->sourceProductId == $sourceProductId)
                return $product;
        }
        return null;
    }

    public abstract function getProducts();

    /**
     * @return ProductSource
     */
    public abstract static function getInstance();

    /**
     * @return stdClass
     */
    public abstract function getSite();
    public abstract function getUrl();
}

class Product
{
    public $id;
    public $images = array();
    public $description;
    public $name;
    public $price;
    public $promoPrice = null;
    public $sourceProductId;
    public $sourceSite;
    public $thumbnail;
    public $url;
    public $weight;

    public function __construct(
        ProductSource $sourceSite, $sourceProductId, $name, $url, $thumbnail, $price, $description = null, $weight = null
    )
    {
        $this->description = $description;
        $this->name = $name;
        $this->price = $price;
        $this->sourceProductId = $sourceProductId;
        $this->sourceSite = $sourceSite;
        $this->thumbnail = $thumbnail;
        $this->url = $url;
        $this->weight = $weight;
    }

    public function getImages() {
        return $this->images;
    }
}

class DatabaseManager
{
    private $connection;

    private static $instance;
    private function __construct() {
        $this->connection = new PDO(
            'mysql:host=' . DB_HOSTNAME . ';dbname=' . DB_DATABASE,
            DB_USERNAME,
            DB_PASSWORD,
            array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
        );
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

    public function addProducts(ProductSource $site)
    {
        $sql = '
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
                    time_modified = NOW(),
                    weight = :weight
            ';
        $statement = $this->connection->prepare($sql);
        foreach ($site->getProducts() as $product)
        {
            print_r($product);
            break;
            $statement->execute(array(
                ':sourceSiteId' => $site->getSite()->id,
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

    public function cleanup($syncTime) {
        $statement = $this->connection->prepare('
            UPDATE imported_products
            SET active = FALSE
            WHERE time_modified < :lastUpdateTime
        ');
        $statement->execute(array(':lastUpdateTime' => date('Y-m-d H:i:s', $syncTime)));
    }
}

if (file_exists('start') && (shell_exec('ps axo cmd | grep -c "^php crawler.php"') == 1)) {
    echo "Starting\n";
    $startTime = time();
    //DatabaseManager::getInstance()->addProducts(NatureRepublic::getInstance());
    DatabaseManager::getInstance()->addProducts(Missha::getInstance());
    //DatabaseManager::getInstance()->cleanup($startTime);
    unlink('start');
}
else {
    echo "Nothing to do\n";
}
<?php
require_once("simple_html_dom.php");
require_once('../config.php');
require_once('etudeHouse.php');
require_once('holikaHolika.php');
require_once('missha.php');
require_once('mizon.php');
require_once('natureRepublic.php');
require_once('tonyMoly.php');

function handleError($errno, $errstr, $errfile, $errline, array $errcontext)
{
    // error was suppressed with the @-operator
    if (0 === error_reporting()) {
        return false;
    }

    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}

abstract class ProductSource {
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

    protected function getPage($url, $method = null, $params = null, $headers = null) {
        if ($params) {
            if (is_array($params)) {
                $paramString = '';
                foreach ($params as $key => $value) {
                    $paramString .= '&' . urlencode($key) . '=' . urlencode($value);
                }
                $params = substr($paramString, 1);
            }
            $params = ' --data "' . $params . '"';
        }
        $strHeaders = '';
        if (is_array($headers)) {
            foreach ($headers as $header => $value) {
                $strHeaders .= " --header $header:$value";
            }
        }
        $get = ($method == 'GET') ? ' --get' : '';
        $command = 'curl ' . $url . $params . $strHeaders . $get;
        $result = shell_exec($command);
        return $result;
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

class Product {
    public $id;
    public $categoryId;
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
        ProductSource $sourceSite, $categoryId, $sourceProductId, $name, $url, $thumbnail, $price, $description = null, $weight = null
    )
    {
        $this->categoryId = $categoryId;
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

class DatabaseManager {
    private $connection;

    private static $instance;
    private function __construct() {
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
                    time_modified = NOW(),
                    weight = :weight
            ';
        $statement = $this->connection->prepare($sql);
        $products = $site->getProducts();
        foreach ($products as $product) {
            $statement->execute(array(
                ':sourceSiteId' => $site->getSite()->id,
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

    public function cleanup($syncTime) {
        $statement = $this->connection->prepare('
            UPDATE imported_products
            SET active = FALSE
            WHERE time_modified < :lastUpdateTime
        ');
        $statement->execute(array(':lastUpdateTime' => date('Y-m-d H:i:s', $syncTime)));
    }

    /**
     * @param string $sourceSitesId
     * @return array
     */
    public function getSourceSitesById($sourceSitesId) {
        $sql = "
            SELECT *
            FROM imported_source_sites
            WHERE imported_source_site_id IN ($sourceSitesId)
        ";
        $statement = $this->connection->query($sql);
        return $statement->fetchAll(PDO::FETCH_CLASS);
    }
}

if ($sites = file_get_contents("crawler.lck")) {
    unlink("crawler.lck");
    fclose(STDIN);
    fclose(STDOUT);
    fclose(STDERR);
    $STDIN = fopen('/dev/null', 'r');
    $STDOUT = fopen('import.log', 'wb');
    $STDERR = fopen('import.error.log', 'wb');

    echo date('Y-m-d H:i:s') . " Starting\n";
    $startTime = time();
    foreach (DatabaseManager::getInstance()->getSourceSitesById($sites) as $sourceSite) {
        $className = $sourceSite->class_name;
        echo date('Y-m-d H:i:s') . " Crawling $className\n'";
        DatabaseManager::getInstance()->addProducts($className::getInstance());
    }
}
//    DatabaseManager::getInstance()->addProducts(NatureRepublic::getInstance());
//    DatabaseManager::getInstance()->addProducts(Missha::getInstance());
//    DatabaseManager::getInstance()->addProducts(TonyMoly::getInstance());
//    DatabaseManager::getInstance()->addProducts(Mizon::getInstance());
//    DatabaseManager::getInstance()->addProducts(HolikaHolika::getInstance());
//    DatabaseManager::getInstance()->addProducts(EtudeHouse::getInstance());
//    DatabaseManager::getInstance()->cleanup($startTime);
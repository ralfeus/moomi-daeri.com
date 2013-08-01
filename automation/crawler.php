<?php
require_once("simple_html_dom.php");
require_once('../config.php');

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

    public function __construct(
        ProductSource $sourceSite, $sourceProductId, $name, $url, $thumbnail, $price, $description = null
    )
    {
        $this->description = $description;
        $this->name = $name;
        $this->price = $price;
        $this->sourceProductId = $sourceProductId;
        $this->sourceSite = $sourceSite;
        $this->thumbnail = $thumbnail;
        $this->url = $url;
    }

    public function getImages() {
        return $this->images;
    }
}

class NatureRepublic extends ProductSource
{
    private function __construct() {}

    public static function getInstance() {
        if (!self::$instance)
            self::$instance = new NatureRepublic();
        return self::$instance;
    }

    private function fillDetails(Product $product) {
        $html = $this->getHtmlDocument($product->url);
        /// Get images
        $items = $html->find('div.detail_imgView a[rel=thumbnail]');
        foreach ($items as $item)
            $product->images[] = $item->attr['href'];

        /// Get description
        $items = $html->find('div.jeon_ingredient>dl>dd.center_t');
        $product->description = mb_convert_encoding(trim($items[0]->innertext), 'utf-8', 'euc-kr');
    }

    private function getCategoryProducts($categoryUrl)
    {
        $products = array();
        $html = $this->getHtmlDocument($categoryUrl);
        $pages = $html->find('a[href^=javascript:paging]');
        $pagesNum =  sizeof($pages) + 1;
        $matches = array();
        for ($currPage = 1; $currPage <= $pagesNum; $currPage++)
        {
            echo "Page $currPage of $pagesNum\n"; $tmp = 1;
            $items = $html->find('a[href^=/product/nr_prod_detail.jsp]');
            foreach ($items as $item) {
                echo date('H:i:s') . "\tItem " . $tmp++ . " of " . sizeof($items) . "\n";
                //$matches = array();
                //$pid = preg_match('/(?<=pid=).*(?=&)', $item->attr['href'], $matches) ? $matches[0] : null;
                $product = new Product(
                    $this,
                    preg_match('/(?<=pid=).*?(?=&)/', $item->attr['href'], $matches) ? $matches[0] : null,
                    mb_convert_encoding(trim($item->find('text', 0)->plaintext), 'utf-8', 'euc-kr'),
                    'http://' . $this->getSite()->name . $item->attr['href'],
                    $item->parent->first_child()->attr['src'],
                    preg_replace('/\D+/', '', $item->find('.price', 0)->plaintext)//,
                    // here will be description extraction code
                );
                if ($this->addProductToList($product, $products)) {
                    if (sizeof($item->find('strike')))
                        $product->promoPrice = preg_replace('/\D+/', '', $item->find('strike', 0)->next_sibling()->plaintext);
                    self::fillDetails($product);
                }
//                $products[] = $product;
            }
            if ($currPage < $pagesNum)
                $html = $this->getHtmlDocument($categoryUrl . '&sPage=' . ($currPage + 1));
        }
        echo "Got " . sizeof($products) . " products\n";
        return $products;
    }

    private function getCategoryUrls()
    {
        $categories = array();
        $html = $this->getHtmlDocument(self::getUrl());
        $items = $html->find('a[href^=/category/nr_ctgr_list.jsp]');
        foreach ($items as $categoryAElement)
            $categories[] = 'http://www.naturerepublic.co.kr' . $categoryAElement->attr['href'];
        return $categories;
    }

    public function getProducts()
    {
        echo date('Y-m-d H:i:s') . "\n";
        $products = array();
        $urls = self::getCategoryUrls(); $currCategory = 1;
        foreach ($urls as $url) {
            echo "Crawling " . $currCategory++ . " of " . sizeof($urls) . "\n";
            $products = array_merge($products, self::getCategoryProducts($url));
//            break;
        }
        echo "Totally found " . sizeof($products) . " products\n";
        echo date('Y-m-d H:i:s') . " --- Finished\n";
        return $products;
    }

    public function getSite() { return (object) array('id' => 1, 'name' => 'www.naturerepublic.co.kr'); }
    public function getUrl() { return 'http://www.naturerepublic.co.kr/etc/sitemap.jsp'; }
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
                    time_modified = NOW()
                ON DUPLICATE KEY UPDATE
                    source_url = :sourceUrl,
                    image_url = :thumbnail,
                    name = :name,
                    description = :description,
                    price = :price,
                    price_promo = :promoPrice,
                    time_modified = NOW()
            ';
        $statement = $this->connection->prepare($sql);
        foreach ($site->getProducts() as $product)
        {
//            print_r($product);
            $statement->execute(array(
                ':sourceSiteId' => $site->getSite()->id,
                ':sourceUrl' => $product->url,
                ':sourceProductId' => $product->sourceProductId,
                ':thumbnail' => $product->thumbnail,
                ':name' => $product->name,
                ':description' => $product->description,
                ':price' => $product->price,
                ':promoPrice' => $product->promoPrice
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
$startTime = time();
DatabaseManager::getInstance()->addProducts(NatureRepublic::getInstance());
DatabaseManager::getInstance()->cleanup($startTime);
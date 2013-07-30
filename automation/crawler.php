<?php
require_once("simple_html_dom.php");
require_once('../config.php');

abstract class ProductSource
{
    protected static $instance;

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
        $html = file_get_html($product->url);
        /// Get images
        $items = $html->find('div.detail_imgView a[rel=thumbnail]');
        foreach ($items as $item)
            $product->images[] = $item->attr['href'];

        /// Get description
        $items = $html->find('div.jeon_ingredient>dl>dd.center_t');
        $product->description = trim($items[0]->innertext);
    }

    private function getCategoryProducts($categoryUrl)
    {
        $products = array();
        $html = file_get_html($categoryUrl);
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
                if (sizeof($item->find('strike')))
                    $product->promoPrice = preg_replace('/\D+/', '', $item->find('strike', 0)->next_sibling()->plaintext);
                self::fillDetails($product);
                $products[] = $product;
            }
            if ($currPage < $pagesNum)
                $html = file_get_html($categoryUrl . '&sPage=' . ($currPage + 1));
        }
        echo "Got " . sizeof($products) . " products\n";
        return $products;
    }

    private function getCategoryUrls()
    {
        $categories = array();
        $html = file_get_html(self::getUrl());
        $items = $html->find('a[href^=/category/nr_ctgr_list.jsp]');
        foreach ($items as $categoryAElement)
            $categories[] = 'http://www.naturerepublic.co.kr' . $categoryAElement->attr['href'];
        return $categories;
    }

    public function getProducts()
    {
        echo date('Y-m-d H:i:s');
        $products = array();
        $urls = self::getCategoryUrls();
        echo "\nGot " . sizeof($urls) . " categories\n";
        foreach ($urls as $url)
        {
            $products = array_merge($products, self::getCategoryProducts($url));
            break;
        }
        echo "Totally found " . sizeof($products) . " products\n";
        echo date('Y-m-d H:i:s');
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
        $this->connection = new PDO('mysql:host=' . DB_HOSTNAME . ';dbname=' . DB_DATABASE, DB_USERNAME, DB_PASSWORD);
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
        $sql = "
                INSERT INTO imported_products
                SET
                    source_site_id = :sourceSiteId,
                    source_url = :sourceUrl,
                    source_product_id = :sourceProductId,
                    image_url = :thumbnail,
                    name = :name,
                    description = :description,
                    price = :price,
                    time_modified = NOW()
                ON DUPLICATE KEY UPDATE
                    source_url = :sourceUrl,
                    image_url = :thumbnail,
                    name = :name,
                    description = :description,
                    price = :price,
                    time_modified = NOW()
            ";
        $statement = $this->connection->prepare($sql);
        foreach ($site->getProducts() as $product)
        {
            print_r($product);
            $statement->execute(array(
                ':sourceSiteId' => $site->getSite()->id,
                ':sourceUrl' => $product->url,
                ':sourceProductId' => $product->sourceProductId,
                ':thumbnail' => $product->thumbnail,
                ':name' => $product->name,
                ':description' => $product->description,
                ':price' => $product->price
            ));
            $product->id = $this->connection->lastInsertId();
            if ($product->id)
                self::addImages($product);
        }
    }
}

DatabaseManager::getInstance()->addProducts(NatureRepublic::getInstance());
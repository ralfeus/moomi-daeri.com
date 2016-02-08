<?php
namespace automation;
use ErrorException;
use Exception;
use model\catalog\ImportCategory;
use model\extension\ImportSourceSite;
use model\extension\ImportSourceSiteDAO;
use simple_html_dom;

abstract class ProductSource {
    /** @var ProductSource[] */
    protected static $instances;
    protected $curlSession;
    /** @var ImportSourceSite */
    protected $sourceSite;

    /**
     * @return static
     */
    public static function getInstance() {
        $class = get_called_class();
         if (!isset(self::$instances[$class])) {
            self::$instances[$class] = new static;
            self::$instances[$class]->curlSession = curl_init();
        }
        return self::$instances[$class];
    }

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
     * @param Product $product
     */
    protected abstract function fillDetails($product);

    /**
     * @return ImportCategory[]
     */
    protected abstract function getAllCategories();

    /**
     * @param ImportCategory $category
     * @return Product[]
     * @throws Exception
    */
    protected abstract function getCategoryProducts($category);

    /**
     * @param ImportCategory $category
     * @return int
     */
    protected abstract function getCategoryProductsCount($category);

    public function getCategories() {
        if ($this->getSite()->getImportMappedCategoriesOnly()) {
            return $this->getSite()->getCategoriesMap();
        } else {
            return $this->getAllCategories();
        }
    }

    /**
     * @param string $sourceSiteCategoryId
     * @return string
     */
    abstract public function getCategoryUrl($sourceSiteCategoryId);

    /**
     * Gets HTML page by URL.
     * Represents a wrapper around the file_get_html($url) function with retry functionality
     * @param string $url
     * @param string $method
     * @param array $data
     * @return simple_html_dom
     * @throws ErrorException
     * @throws Exception
     * @throws null
     */
    protected function getHtmlDocument($url, $method = 'GET', $data = array()) {
        set_error_handler('handleError');
        $finalException = null;
        for ($retriesLeft = 5; $retriesLeft > 0; $retriesLeft--) {
            try {
                if ($method == 'GET') {
                    $html = file_get_html($url);
                } elseif ($method == 'POST') {
                    curl_setopt($this->curlSession, CURLOPT_POST, true);
                    curl_setopt($this->curlSession, CURLOPT_POSTFIELDS, $data);
                    curl_setopt($this->curlSession, CURLOPT_RETURNTRANSFER, true);
                    $html = new \simple_html_dom(curl_exec($this->curlSession));
                } else {
                    throw new Exception("The HTTP method '$method' is not implemented");
                }
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
     * @param string $url
     * @param string $method
     * @param string[string]|string $params
     * @param string[string] $headers
     * @param string[string] $cookies
     * @return string
     */
    protected function getPage($url, $method = null, $params = null, $headers = [], $cookies = []) {
        $strParams = '';
        if (!empty($params)) {
            if (is_array($params)) {
                 foreach ($params as $key => $value) {
                     $strParams .= '&' . urlencode($key) . '=' . urlencode($value);
                 }
                 $params = substr($strParams, 1);
             }
             $strParams = ' --data "' . $params . '"';
        }
        $strHeaders = '';
        if (is_array($headers)) {
            foreach ($headers as $header => $value) {
                $strHeaders .= " --header \"$header:$value\"";
            }
        }
        $strCookies = '';
        if (!empty($cookies)) {
            if (is_array($cookies)) {
                foreach ($cookies as $cookie => $value) {
                    $strCookies .= "$cookie=$value;";
                }
            }
            $strCookies = " --cookie \"$strCookies\"";
        }
        $get = ($method == 'GET') ? ' --get' : '';
        $command = "curl $get $strParams $strHeaders $strCookies \"$url\" 2>/dev/null";
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

    /**
     * @return Product[]
     */
    public function getProducts() {
        echo date('Y-m-d H:i:s') . "\n";
        $categories = $this->getCategories();
        //echo date('Y-m-d H:i:s ') . print_r($this->getSite()->getCategoriesMap(), true);
        echo date('Y-m-d H:i:s ') . "Got " . sizeof($categories) . " category URLs\n";
        $urlCount = 1;
        $products = array();
        foreach ($categories as $category) {
            echo date('Y-m-d H:i:s ') . "Crawling category " . $urlCount++ . " of " . count($categories) . "\n";
            $productsCount = $this->getCategoryProductsCount($category);
            echo date('Y-m-d H:i:s') . " $productsCount products are to be imported\n";
            $categoryProducts = $this->getCategoryProducts($category);
            $tmp = 1;
            foreach ($categoryProducts as $product) {
                echo date('H:i:s') . "\tItem " . $tmp++ . " of " . sizeof($categoryProducts) . "\t- ";
                if ((is_null($category->getPriceUpperLimit()) || ($product->price <= $category->getPriceUpperLimit())) &&
                    $this->addProductToList($product, $products)) {
                    $this->fillDetails($product);
                    echo $product->sourceProductId . "\n";
                } else {
                    echo "skipped\n";
                }
            }
//            break; //TODO: Disable for production
        }
        echo date('Y-m-d H:i:s') . " --- Finished\n";
        return $products;
    }

    /**
     * @return string
     */
    protected function getRootUrl() {
        $matches = array();
        preg_match('/(https?:\/\/[^\/]+)\/?/', $this->getUrl(), $matches);
        return $matches[1] . '/';
    }
     /**
      * @return ImportSourceSite
      */
     public function getSite() {
         if (is_null($this->sourceSite)) {
            //error_log(static::class);
                $this->sourceSite = ImportSourceSiteDAO::getInstance()->getSourceSite(static::class);
            //error_log(print_r($this->sourceSite, true));
         }
         return $this->sourceSite;
     }

    public abstract function getUrl();

    /**
     * @return ImportSourceSite
     * @throws Exception
     */
    public static abstract function createDefaultImportSourceSiteInstance();
}

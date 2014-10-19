<?php
namespace automation;
use ErrorException;
use model\extension\ImportSourceSite;
use model\extension\ImportSourceSiteDAO;
use simple_html_dom;

abstract class ProductSource {
    /** @var ProductSource[] */
    protected static $instances;
    protected $curlSession;

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

    protected abstract function getAllCategoriesUrls();

    public function getCategoryUrls() {
        if ($this->getSite()->getImportMappedCategoriesOnly()) {
            return $this->getMappedCategoriesUrls();
        } else {
            return $this->getAllCategoriesUrls();
        }
    }

    protected abstract function getMappedCategoriesUrls();

     /**
      * Gets HTML page by URL.
      * Represents a wrapper around the file_get_html($url) function with retry functionality
      * @param string $url
      * @param string $method
      * @param array $data
      * @return simple_html_dom
      * @throws ErrorException
      * @throws Exception
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
         return ImportSourceSiteDAO::getInstance()->getSourceSite(static::class);
     }

     public abstract function getUrl();
}

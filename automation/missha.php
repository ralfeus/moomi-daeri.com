<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dev
 * Date: 4.8.13
 * Time: 15:00
 * To change this template use File | Settings | File Templates.
 */

class Missha extends ProductSource {
    private function __construct() {}

    private function fillDetails(Product $product) {
        $html = $this->getHtmlDocument($product->url);
        $matches = array();
        /// Get images
        $items = $html->find('td.M_s_img_b img');
        foreach ($items as $item)
            if (preg_match('/(?<=loadImage\(\').+?(?=\'\))', $item->attr['onmouseover'], $matches))
                $product->images[] =  $matches[0];

        /// Get description
        $items = $html->find('td.M_g_detail');
        $product->description = mb_convert_encoding(trim($items[0]->innertext), 'utf-8', 'euc-kr');
        $html->clear();
    }

    private function getCategoryProducts($categoryUrl)
    {
        $products = array();
        echo $categoryUrl . "\n";
        $html = $this->getHtmlDocument($categoryUrl);
        $pages = $html->find('div.pg2', 0)->find('a[href*=category_large.php\?pagenum]');
        $pagesNum =  sizeof($pages) + 1;
        $matches = array();
        for ($currPage = 1; $currPage <= $pagesNum; $currPage++)
        {
            echo "Page $currPage of $pagesNum\n"; $tmp = 1;
            $items = $html->find('table#mainTable table.news03');
            foreach ($items as $item) {
                echo date('H:i:s') . "\tItem " . $tmp++ . " of " . sizeof($items) . "\n";
                $aElement = $item->find('a[href*=category_detail.php]', 0);
                $product = new Product(
                    $this,
                    preg_match('/(?<=id=)\d*?/', $aElement->attr['href'], $matches) ? $matches[0] : null,
                    mb_convert_encoding(trim($item->find('a[href*=category_detail.php]', 1)->find('text', 0)->plaintext), 'utf-8', 'euc-kr'),
                    'http://shop.beautynet.co.kr/' . $aElement->attr['href'],
                    $aElement->find('image', 0)->attr['src'],
                    preg_replace('/\D+/', '', $item->find('td.won', 0)->plaintext),
                    null,
                    0.25
                );
                if ($this->addProductToList($product, $products)) {
                    if (sizeof($item->find('strike')))
                        $product->promoPrice = preg_replace('/\D+/', '', $item->find('strike', 0)->next_sibling()->plaintext);
                    self::fillDetails($product);
                }
//                $products[] = $product;
            }
            $html->clear();
            if ($currPage < $pagesNum)
                $html = $this->getHtmlDocument($categoryUrl . '&pagenum=' . ($currPage + 1));
        }
        echo "Got " . sizeof($products) . " products\n";
        return $products;
    }

    private function getCategoryUrls()
    {
        $categories = array();
        $html = $this->getHtmlDocument(self::getUrl());
        $items = $html->find('a[href*=\/missha\/category_large.php]');
        foreach ($items as $categoryAElement)
            $categories[] = 'http://shop.beautynet.co.kr/' . $categoryAElement->attr['href'];
        $html->clear();
        return array_unique($categories);
    }

    public function getProducts() {
        echo date('Y-m-d H:i:s') . "\n";
        $products = array();
        $urls = self::getCategoryUrls(); $currCategory = 1;
        foreach ($urls as $url) {
            echo "Crawling " . $currCategory++ . " of " . sizeof($urls) . "\n";
            $products = array_merge($products, self::getCategoryProducts($url));
            break;
        }
        echo "Totally found " . sizeof($products) . " products\n";
        echo date('Y-m-d H:i:s') . " --- Finished\n";
        return $products;
    }

    /**
     * @return ProductSource
     */
    public static function getInstance() {
        if (!self::$instance)
            self::$instance = new Missha();
        return self::$instance;
    }

    /**
     * @return stdClass
     */
    public function getSite() { return (object)array( 'id' => 2, 'name' => 'shop.beautynet.co.kr/missha/'); }

    public function getUrl() { return 'http://shop.beautynet.co.kr/missha/category_large.php?part_code=115001001000'; }
}
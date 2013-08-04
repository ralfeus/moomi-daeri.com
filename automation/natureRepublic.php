<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dev
 * Date: 4.8.13
 * Time: 17:12
 * To change this template use File | Settings | File Templates.
 */
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
                    preg_replace('/\D+/', '', $item->find('.price', 0)->plaintext),
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
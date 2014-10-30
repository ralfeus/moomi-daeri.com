<?php
namespace automation\SourceSite;

use automation\Product;
use automation\ProductSource;

class TonyMoly extends ProductSource {
    protected function fillDetails($product) {
        $matches = array();
        /// Get images
        $html = $this->getHtmlDocument('http://etonymoly.com/common/pop_zoom_goods.asp?guid=' . $product->sourceProductId);
        $items = $html->find('div.thumb img');
        foreach ($items as $item) {
            $product->images[] = $this->getRootUrl() . '/' . substr($item->attr['onmouseover'], 17, strlen($item->attr['onmouseover']) - 19);
        }
        $html->clear();

        $html = $this->getHtmlDocument($product->url);
        /// Get description
        $item = $html->find('div#ProdInfo>div.detail', 0);
        $product->description = trim($item->innertext);
        $product->description = preg_replace('/(?<=src=\")\//', $this->getRootUrl() . '/', $product->description);

        /// Get price and promo price
        $product->price = preg_replace('/\D+/', '', $html->find('div.FixHeight li td.major2', 0)->plaintext);
        $item = $html->find('div.FixHeight li.desc>strike', 0);
        if (!is_null($item)) {
            $product->promoPrice = $product->price;
            $product->price = preg_replace('/\D+/', '', $item->plaintext);
        }

        $html->clear();
    }

    protected function getCategoryProducts($categoryUrl) {
        $products = array();
        $categoryId = preg_match('/(?<=cate=)\d+/', $categoryUrl, $matches) ? $matches[0] : null;
        $categoryUrl = 'http://etonymoly.com/common/ajax/exec_getProdList.asp?cate=' . $categoryId;
        $html = $this->getHtmlDocument($categoryUrl);
        $pages = $html->find('a.page_num');
        $pagesNum =  sizeof($pages);
        $matches = array();
        for ($currPage = 1; $currPage <= $pagesNum; $currPage++)
        {
            echo "Page $currPage of $pagesNum\n"; $tmp = 1;
            $items = $html->find('ul a');
            foreach ($items as $item) {
                echo date('H:i:s') . "\tItem " . $tmp++ . " of " . sizeof($items) . "\t - ";
                if (!sizeof($item->find('img')))
                    continue;
//                preg_match('/(?<=guid=)\d+/', $item->attr['href'], $matches);
                $product = new Product(
                    $this,
                    preg_match('/(?<=cate=)\d+/', $item->attr['href'], $matches) ? $matches[0] : null,
                    preg_match('/(?<=guid=)\d+/', $item->attr['href'], $matches) ? $matches[0] : null,
                    mb_convert_encoding($item->first_child()->attr['title'],  'utf-8', 'euc-kr'),
                    $item->attr['href'],
                    $this->getRootUrl() . $item->first_child()->attr['src'],
                    null,
                    null,
                    0.25
                );
                if ($this->addProductToList($product, $products)) {
                    self::fillDetails($product);
                }
                echo $product->sourceProductId . "\n";
//                $products[] = $product;
            }
            if ($currPage < $pagesNum)
                $html = $this->getHtmlDocument($categoryUrl . '&page=' . ($currPage + 1));
        }
        echo "Got " . sizeof($products) . " products\n";
        return $products;
    }

    protected function getAllCategoriesUrl() {
        $categories = array();
        $html = $this->getHtmlDocument(self::getUrl());
        $items = $html->find('div[class=posR TopCategory] .SubCate a');
        foreach ($items as $categoryAElement)
            $categories[] = $categoryAElement->attr['href'];
        return $categories;
    }

    protected function getMappedCategoryUrl($categoryUrl) {
        //TODO: Implement for mapped categories
    }

    public function getProducts()
    {
        echo date('Y-m-d H:i:s') . "\n";
        $products = array();
        $urls = $this->getCategories(); $currCategory = 1;
        foreach ($urls as $url) {
            echo "Crawling " . $currCategory++ . " of " . sizeof($urls) . "\n";
            $products = array_merge($products, $this->getCategoryProducts($url));
            break;
        }
        echo "Totally found " . sizeof($products) . " products\n";
        echo date('Y-m-d H:i:s') . " --- Finished\n";
        return $products;
    }

    //public function getSite() { return (object) array('id' => 3, 'name' => 'etonymoly.com'); }
    public function getUrl() { return 'http://etonymoly.com'; }

    /**
     * @param string $categoryUrl
     * @return int
     */
    protected function getCategoryProductsCount($categoryUrl) {
        // TODO: Implement getCategoryProductsCount() method.
    }
}

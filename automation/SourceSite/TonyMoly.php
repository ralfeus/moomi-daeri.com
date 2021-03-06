<?php
namespace automation\SourceSite;

use automation\Product;
use automation\ProductSource;
use model\catalog\ImportCategory;
use model\catalog\Manufacturer;
use model\catalog\Supplier;
use model\extension\ImportSourceSite;

class TonyMoly extends GMarketCoKr {
    public function __construct() {
        $this->shopId = 'TI4MR38jMzgxOc01NjUyODA0MTh/Rw==';
    }

    public static function createDefaultImportSourceSiteInstance() {
        return new ImportSourceSite(
            explode("\\", get_class())[2],
            [],
            [],
            new Manufacturer(0),
            new Supplier(0),
            false,
            "TonyMoly",
            1,
            [0, 2],
            1
        );
    }
//    protected function fillDetails($product) {
//        $matches = array();
//        /// Get images
//        $html = $this->getHtmlDocument('http://etonymoly.com/common/pop_zoom_goods.asp?guid=' . $product->sourceProductId);
//        $items = $html->find('div.thumb img');
//        foreach ($items as $item) {
//            $product->images[] = $this->getRootUrl() . '/' . substr($item->attr['onmouseover'], 17, strlen($item->attr['onmouseover']) - 19);
//        }
//        $html->clear();
//
//        $html = $this->getHtmlDocument($product->url);
//        /// Get description
//        $item = $html->find('div.product_info', 0);
//        $product->description = trim($item->innertext);
//        $product->description = preg_replace('/(?<=src=\")\//', $this->getRootUrl() . '/', $product->description);
//
//        /// Get price and promo price
//        $product->price = preg_replace('/\D+/', '', $html->find('tr.prd-price td', 0)->plaintext);
//        $item = $html->find('td del', 0);
//        if (!is_null($item)) {
//            $product->promoPrice = $product->price;
//            $product->price = preg_replace('/\D+/', '', $item->plaintext);
//        }
//
//        $html->clear();
//    }
//
//    protected function getCategoryProducts($category) {
//        $products = array();
//        $categoryId = preg_match('/(?<=cate=)\d+/', $category->getUrl(), $matches) ? $matches[0] : null;
//        $categoryUrl = 'http://etonymoly.com/common/ajax/exec_getProdList.asp?cate=' . $categoryId;
//        $html = $this->getHtmlDocument($categoryUrl);
//        $pages = $html->find('a.page_num');
//        $pagesNum =  sizeof($pages);
//        $matches = array();
//        for ($currPage = 1; $currPage <= $pagesNum; $currPage++) {
//            echo "Page $currPage of $pagesNum\n"; $tmp = 1;
//            $items = $html->find('ul a');
//            foreach ($items as $item) {
//                if (!sizeof($item->find('img')))
//                    continue;
//                $sourceProductId = preg_match('/(?<=guid=)\d+/', $item->attr['href'], $matches) ? $matches[0] : null;
//                $products[] = new Product(
//                    $this,
//                    array(preg_match('/(?<=cate=)\d+/', $item->attr['href'], $matches) ? $matches[0] : $categoryId),
//                    $sourceProductId,
//                    mb_convert_encoding($item->first_child()->attr['title'],  'utf-8', 'euc-kr'),
//                    $this->getRootUrl() . "/html/ItemDetail.asp?guid=$sourceProductId&cate=$categoryId",
//                    $this->getRootUrl() . $item->first_child()->attr['src'],
//                    null,
//                    null,
//                    0.25
//                );
//            }
//            if ($currPage < $pagesNum)
//                $html = $this->getHtmlDocument($categoryUrl . '&page=' . ($currPage + 1));
//        }
//        echo "Got " . sizeof($products) . " products\n";
//        return $products;
//    }
//
//    protected function getAllCategories() {
//        $categories = array(); $matches = [];
//        echo "Getting categories from " . self::getUrl() . "\n";
//        $html = $this->getHtmlDocument(self::getUrl());
//        $items = $html->find('a[href^="http://etonymoly.com/html/cpp_image.asp?cate="]');
//        foreach ($items as $categoryAElement) {
//            $categoryId = preg_match('/(?<=cate=)\d+/', $categoryAElement->attr['href'], $matches) ? $matches[0] : null;
//            $categories[] = new ImportCategory($this, $categoryId, null, null, $categoryAElement->attr['href']);
//        }
//        return $categories;
//    }
//
//    public function getCategoryUrl($sourceSiteCategoryId) {
//        return $this->getUrl() . "/html/cpp_image.asp?cate=$sourceSiteCategoryId";
//    }
//
//    public function getUrl() { return 'http://etonymoly.com'; }
//
//    /**
//     * @param string $categoryUrl
//     * @return int
//     */
//    protected function getCategoryProductsCount($categoryUrl) {
//        return null;
//    }
}

<?php
namespace automation\SourceSite;

use automation\Product;
use automation\ProductSource;
use model\catalog\ImportCategory;
use model\catalog\Manufacturer;
use model\catalog\Supplier;
use model\extension\ImportSourceSite;

class BBPrincess extends ProductSource {

    /**
     * @return ImportCategory[]
     */
    protected function getAllCategories() {
        return $this->getAllCategoriesByParams(
            'div#snb li.xans-record- a[href^="/product/list"]',
            '/(?<=cate_no=)\d+/'
        );
    }

    public function getCategoryUrl($sourceSiteCategoryId) {
        return $this->getUrl() . '/product/list.html?cate_no=' . $sourceSiteCategoryId;
    }

    public function getUrl() {
        return 'http://bbprincess.com';
    }

    protected function getCategoryProducts($category) {
        $products = array(); $matches = array();
        $html = $this->getHtmlDocument($category->getUrl());
        $lastPage = $html->find('div.xans-product-normalpaging a[href$="page"]', -1);
        $pagesNum = (!is_null($lastPage) && preg_match('/(?<=page=)\d+/', $lastPage->attr['href'], $matches))
            ? $matches[0] : 1;
        for ($currPage = 1; $currPage <= $pagesNum; $currPage++) {
            echo "Page $currPage of $pagesNum\n"; $tmp = 1;
            /** @var \simple_html_dom_node[] $items */
            $items = $html->find('div.xans-product li.item');
            foreach ($items as $item) {
                echo date('H:i:s') . "\tItem " . $tmp++ . " of " . sizeof($items) . "\n";
                if (!sizeof($item->find('img')))
                    continue;
                $itemId = preg_match('/(?<=anchorBoxId_)\d+/', $item->attr['id'], $matches) ? $matches[0] : null;
                /** @var \simple_html_dom_node[] $priceItems */
                $priceItems = $item->find('ul.xans-product-listitem span');
                foreach ($priceItems as $priceItem) {
                    if (preg_match('/\d+,\d+/', $priceItem->text(), $matches)) {
                        $price = str_replace(',', '', $matches[0]);
                    }
                }
                $products[] = new Product(
                    $this,
                    [$category->getSourceSiteCategoryId()],
                    $itemId,
                    trim($item->findOne('p.name')->text()),
                    $this->getUrl() . "/product/detail.html?product_no=$itemId",
                    $item->findOne('img.thumb')->attr['src'],
                    $price,
                    null,
                    0.25
                );
            }
            if ($currPage < $pagesNum)
                $html = $this->getHtmlDocument($category->getUrl() . "&pageNum=" . ($currPage + 1));
        }
        echo "Got " . sizeof($products) . " products\n";
        return $products;
    }

    protected function fillDetails($product) {
        $matches = array();
        $html = $this->getHtmlDocument($product->url);

        /// Get images
        //TODO: Currently simple_html_dom doesn't support > sign in selector as immediate child of the element
        //TODO: Until that should use no > sign in the selectors
//        $imageElements = $html->find('div.layer_imageZoom>ul.small_pic li img[onclick!=viewThumb(\'http://image.etude.co.kr/upload/product/\', \'imageZoom\');return false;');
        $imageElement = $html->findOne('img.BigImage');
        $product->images[] = $imageElement->attr['src'];

        /// Get description
        $item = $html->findOne('div#prdDetail');
        $product->description = trim($item->innertext);
        $product->description = preg_replace('/(?<=src=\")\//', $this->getRootUrl(), $product->description);

        /// Get price and promo price
//        $product->price = preg_replace('/\D+/', '', $html->find('dd.special em', 0)->plaintext);
//        $item = $html->find('span.Sprice', 0);
//        if ($item != null) {
//            $product->promoPrice = preg_replace('/\D+/', '', $item->plaintext);
//        }

        $html->clear();
    }

    /**
     * @param ImportCategory $category
     * @return int
     */
    protected function getCategoryProductsCount($category) {
        $html = $this->getHtmlDocument($category->getUrl());
        $matches = [];
        if (preg_match('/\d+/', $html->findOne('p.prdCount')->text(), $matches)) {
            return $matches[0];
        } else {
            return null;
        }
    }

    public static function createDefaultImportSourceSiteInstance() {
        return new ImportSourceSite(
            explode("\\", get_class())[2],
            [],
            [],
            new Manufacturer(0),
            new Supplier(0),
            false,
            "B.B Princess",
            1,
            [0, 2],
            1
        );
    }
}

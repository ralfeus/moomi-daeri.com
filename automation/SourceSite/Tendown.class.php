<?php
namespace automation\SourceSite;

use automation\CarProduct;
use automation\CarProductSource;
use model\catalog\ImportCategory;
use model\catalog\Manufacturer;
use model\catalog\Supplier;
use model\extension\ImportSourceSite;

class Tendown extends CarProductSource {

    /**
     * @return ImportCategory[]
     */
    protected function getAllCategories() {
        $html = $this->getHtmlDocument($this->getRootUrl());
        $links = $html->find('table.cate tbody tr td a.catem');
        $categories = array();

        foreach ($links as $link) {
            if (preg_match('/(?<=cate_no=)\d+/', $link->attr['href'], $matches)) {
                $categoryId = $matches[0];
            } else {
                $categoryId = null;
            }
            $categories[] = new ImportCategory(
                $this,
                $categoryId, null, null, $this->getRootUrl() . $link->attr['href']
            );
        }
        return $categories;
    }

    public function getCategoryUrl($sourceSiteCategoryId) {
        return $this->getRootUrl() . '/front/php/category.php?cate_no=' . $sourceSiteCategoryId;
    }

    public function getUrl() {
        return 'http://www.tendown.co.kr/';
    }

    protected function getCategoryProducts($category) {
        $products = array(); $matches = array();
        $html = $this->getHtmlDocument($category->getUrl());
        $pages = $html->find('a[href*=page=]');
        $pagesNum = !is_null($pages) ? sizeof($pages) + 1 : 1;
        for ($currPage = 1; $currPage <= $pagesNum; $currPage++) {
            echo "Page $currPage of $pagesNum\n"; $tmp = 1;
            /** @var \simple_html_dom_node[] $itemImages */
            $itemImages = $html->find('a[href^=/front/php/product.php] img[src*=web]');
            foreach ($itemImages as $itemImage) {
                $item = $itemImage->parent();
                echo date('H:i:s') . "\tItem " . $tmp++ . " of " . sizeof($itemImages) . "\n";
//                preg_match('/(?<=guid=)\d+/', $item->attr['href'], $matches);
                $itemId = preg_match('/(?<=product_no=)\d+/', $item->attr['href'], $matches) ? $matches[0] : null;
                $products[] = new CarProduct(
                    $this,
                    array($category->getSourceSiteCategoryId()),
                    $itemId,
                    null,
                    null,
                    $this->getUrl() . $item->attr['href'],
                    $item->findOne('img')->attr['src'],
                    null,
                    "Hyundai",
                    "Tendown",
                    null,
                    0
                );
            }
            if ($currPage < $pagesNum) {
                $html = $this->getHtmlDocument($category->getUrl() . $pages[$currPage - 1]->attr['href']);
            }
        }
        echo "Got " . sizeof($products) . " products\n";
        return $products;
    }

    /**
     * @param CarProduct $product
     * @throws \ErrorException
     * @throws \Exception
     * @throws null
     */
    protected function fillDetails($product) {
        $matches = array();
        $html = $this->getHtmlDocument($product->url);
        /// Get name
        $product->name = mb_convert_encoding($html->findOne('input[name=product_name]')->attr['value'],  'utf-8', 'euc-kr');

        /// Get description
        $item = $html->findOne('img[src=/web/upload/sub_view_title1.gif]')->parent->parent->nextSibling()->firstChild();
        $product->description = mb_convert_encoding(trim($item->innertext()), 'utf-8', 'euc-kr');
        $product->description = preg_replace('/(?<=src=\")\//', $this->getRootUrl(), $product->description);
        /// Get images
        //TODO: Currently simple_html_dom doesn't support > sign in selector as immediate child of the element
        //TODO: Until that should use no > sign in the selectors
//        $imageElements = $html->find('div.layer_imageZoom>ul.small_pic li img[onclick!=viewThumb(\'http://image.etude.co.kr/upload/product/\', \'imageZoom\');return false;');
        $imageElements = $item->findOne('img');
        $product->images[] = $this->getRootUrl() . $imageElements->attr['src'];

        /// Get price
        $product->price = preg_replace('/\D/', '', $html->findOne('input[name=product_price]')->attr['value']);

        /// Get part number
        $descItems = $item->find('p');
        foreach ($descItems as $p) {
            if (preg_match('/\d{5} \d\w{4}(?=(\W|$))/', $p->innertext(), $matches)) {
                $product->partNumbers[] = $matches[0];
            }
        }

        $html->clear();
    }

    /**
     * @param string $categoryUrl
     * @return int
     */
    protected function getCategoryProductsCount($categoryUrl) {
        return null;
    }

    public static function createDefaultImportSourceSiteInstance() {
        return new ImportSourceSite(
            explode("\\", get_class())[2],
            [],
            [],
            new Manufacturer(34),
            new Supplier(92),
            false,
            "Tendown",
            1,
            [0],
            1
        );
    }
}

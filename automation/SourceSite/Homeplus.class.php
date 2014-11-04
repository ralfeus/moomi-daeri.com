<?php
namespace automation\SourceSite;

use automation\Product;
use automation\ProductSource;
use Exception;
use model\catalog\ImportCategory;

class HomePlus extends GMarketCoKr {
    private $pageSize = 200;
    /**
     * @return ImportCategory[]
     */
    protected function getAllCategories() {
        return
            array(
                new ImportCategory($this, null, null, null, $this->getUrl() . '/List')
            );
    }

    /**
     * @param ImportCategory $category
     * @return Product[]
     * @throws Exception
     */
    protected function getCategoryProducts($category) {
        $products = array(); $matches = array();
        /** @var \simple_html_dom $html */
        $html = $this->getHtmlDocument($category->getUrl() . (strpos($category->getUrl(), 'List?') ? "&" : '?') . 'PageSize=' . $this->pageSize);
        $pages = preg_match('/(?<=Page=)\d+/', $html->findOne('div.paging span.last a')->attr['href'], $matches)
            ? $matches[0] + 1 : 1;
        for ($currPage = 1; $currPage <= $pages; $currPage++) {
            echo "Page $currPage of $pages\n";
            /** @var \simple_html_dom_node[] $items */
            $items = $html->find('div.prod_list ul.type1 li');
            foreach ($items as $item) {
                if (!sizeof($item->find('img')))
                    continue;
                $itemId = preg_match('/(?<=goodscode=)\d+/', $item->firstChild()->firstChild()->attr['href'], $matches) ? $matches[0] : null;
                $products[] = new Product(1,
                    $this,
                    null,
                    $itemId,
                    $item->findOne('div.prd_info p.prd_name a')->text(),
                    $item->firstChild()->firstChild()->attr['href'],
                    $item->firstChild()->firstChild()->firstChild()->attr['data-original'],
                    intval(preg_replace('/\D+/', '', $item->findOne('p.prd_price em strong')->text())),
                    null,
                    0.25
                );
            }
            if ($currPage < $pages) {
                $html = $this->getHtmlDocument(
                    $category->getUrl() .
                    (strpos($category->getUrl(), 'List?') ? "&" : '?') . "Page=" . $currPage . '&PageSize=' . $this->pageSize);
            }
        }
        echo "Got " . sizeof($products) . " products\n";
        return $products;
    }

    /**
     * @param ImportCategory $category
     * @return int
     */
    protected function getCategoryProductsCount($category) {
        /** @var \simple_html_dom $html */
        $html = $this->getHtmlDocument($category->getUrl() . (strpos($category->getUrl(), 'List?') ? '&' : '?') .
            'Page=0&PageSize=1');
        $matches = array();
        return preg_match('/(?<=Page=)\d+/', $html->findOne('div.paging span.last a')->attr['href'], $matches) ? $matches[0] + 1 : null;
    }

    /**
     * @param string $sourceSiteCategoryId
     * @return string
     */
    public function getCategoryUrl($sourceSiteCategoryId) {
        return $this->getUrl() . "/List?category=$sourceSiteCategoryId";
    }

    public function getUrl() {
        return 'http://minishop.gmarket.co.kr/homeplusonline';
    }

    /**
     * @param Product &$product
     * @param \simple_html_dom $html
     */
    protected function parseProductCategories(&$product, $html) {
        $categoryItems = $html->find("div#headerCate span");
        $GdlcCd = preg_match("/(?<=\(')\d+/", $categoryItems[0]->attr['onmouseover'], $matches) ? $matches[0] : null;
        $GdmcCd = preg_match("/(?<=\(')\d+/", $categoryItems[1]->attr['onmouseover'], $matches) ? $matches[0] : null;
        $GdscCd = preg_match("/(?<=\(')\d+/", $categoryItems[2]->attr['onmouseover'], $matches) ? $matches[0] : null;
        $product->categoryIds[] = $GdlcCd;
        $product->categoryIds[] = "$GdlcCd/$GdmcCd";
        $product->categoryIds[] = "$GdlcCd/$GdmcCd/$GdscCd";
    }
}

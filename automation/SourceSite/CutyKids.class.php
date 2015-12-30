<?php
namespace automation\SourceSite;

use automation\Product;
use automation\ProductSource;
use model\catalog\ImportCategory;

abstract class CutyKids extends ProductSource {
    /**
     * @var int $supplierId
     * Defines supplier ID on the site. Currently there are 8 suppliers numbered 0 through 8
     */
    protected $supplierId;
    protected $excludedBrands = [];

    protected function fillDetails($product) {
        $matches = array();
        $html = $this->getHtmlDocument($product->url);
//        /// Get name
//        $product->name = $html->find('font.text13 b', 0)->innertext;
        /// Get images
        $items = $html->find('table.table a[href="#."] img');
        foreach ($items as $item) {
            $product->images[] = $item->attr['src'];
        }
        /// Get description
        $item = $html->find('form div[align=center]', 0);
        $product->description = trim($item->innertext);

        /// Get price and promo price
        $product->price = preg_replace('/\D+/', '', $this->getElementWithText($html->find('table.spec td'), "시장가")->nextSibling()->plaintext);
        $promoPrice = $product->price = preg_replace('/\D+/', '', $this->getElementWithText($html->find('font[color="ff6100"] b'), "원")->plaintext);
        if ($product->price != $promoPrice) {
            $product->promoPrice = $promoPrice;
        }
        /// Get minimal amount
        $product->minimalAmount = sizeof($html->find('input.input_enable'));
        $html->clear();
    }

    protected function getBrandProducts($brandUrl, &$products) {
        $html = $this->getHtmlDocument($brandUrl);
        $lastPageLink = $this->getElementWithText($html->find('table.paging a'), "맨끝");
        $pagesNum = (!is_null($lastPageLink) && preg_match('/(?<=&pg=)\d+/', $lastPageLink->attr['href'], $matches)) ? $matches[0] : 1;
        $matches = [];
        /** @var \simple_html_dom_node $item */
        for ($currPage = 1; $currPage <= $pagesNum; $currPage++) {
            echo "Page $currPage of $pagesNum\n";
            $items = $html->find('div.w1150 a[href^="list.php?ai_id="]');
            foreach ($items as $item) {
                $sourceProductId = preg_match('/(?<=ai_id=)\d+/', $item->attr['href'], $matches) ? $matches[0] : null;
                $products[] = new Product(
                    $this,
                    null,
                    $sourceProductId,
                    (preg_match('/(?<=comp_head=).+/', $brandUrl, $matches) ? $matches[0] : '') . ' - '.
                    $item->parentNode()->parentNode()->nextSibling()->find('font[color="#6a6a6a"]', 0)->innertext(), //mb_convert_encoding($item->first_child()->attr['title'],  'utf-8', 'euc-kr'),
                    $this->getRootUrl() . '/' . $item->attr['href'],
                    $item->first_child()->attr['src'],
                    null,
                    null,
                    0.25
                );
            }
            if ($currPage < $pagesNum) {
                $html = $this->getHtmlDocument($brandUrl . '&pg=' . ($currPage + 1));
            }
        }
        $html->clear();
    }

    protected function getCategoryProducts($category) {
        $products = array();
        $html = $this->getHtmlDocument($category->getUrl() . "/index_user.php");
        $brands = $html->find('div#idMenu' . $this->supplierId . ' a[href^="./main.php?comp_head="]');
        $currBrand = 1;
        foreach ($brands as $brand) {
            $brandUrl = $category->getUrl() . $brand->attr['href'];
            if (in_array($brandUrl, $this->excludedBrands)) {
                $currBrand++;
                continue;
            }
            echo "Brand " . $brand->plaintext . "(" . $currBrand++ . " of " . sizeof($brands) . ") - ~" . $this->getBrandProductsCount($brandUrl) . " products\n";
            $this->getBrandProducts($brandUrl, $products);
        }
        $html->clear();
        echo "Got " . sizeof($products) . " products\n";
        return $products;
    }

    protected function getAllCategories() {
        return [new ImportCategory($this, null, null, null, $this->getRootUrl())];
    }

    public function getCategoryUrl($sourceSiteCategoryId) {
        return $this->getUrl() . "/main.php?ac_id=$sourceSiteCategoryId";
    }

    public function getUrl() { return 'http://cutykids.com'; }

    /**
     * @param string $brandUrl
     * @return int
     * The amount isn't precise as last page may contain less items than the rest of pages
     */
    protected function getBrandProductsCount($brandUrl) {
        $matches = [];
        $html = $this->getHtmlDocument($brandUrl);
        $lastPageLink = $this->getElementWithText($html->find('table.paging a'), "맨끝");
        $lastPage = (!is_null($lastPageLink) && preg_match('/(?<=&pg=)\d+/', $lastPageLink->attr['href'], $matches)) ? $matches[0] : 1;
        $itemsPerPage = sizeof($html->find('a[href^="list.php?ai_id="]'));
        $html->clear();
        return $itemsPerPage * $lastPage;
    }

    protected function getCategoryProductsCount($category) {
        return null;
    }

    protected function getHtmlDocument($url, $method = 'GET', $data = array()) {
        return
            new \simple_html_dom($this->getPage($url, $method, $data, [], [
                'member_id' => 'moomi',
                'member_pw' => '20452045',
                'mbr_level' => '1',
                'vat_gubun' => '1',
                'na3_member' => 'moomi'
            ]));
    }

//    public static function createDefaultImportSourceSiteInstance() {
//        return new ImportSourceSite(
//            explode("\\", get_class())[2],
//            [],
//            [],
//            new Manufacturer(0),
//            new Supplier(0),
//            false,
//            "Cuty Kids",
//            1,
//            [0, 2],
//            1
//        );
//    }

    /**
     * @param \simple_html_dom_node[] $elements
     * @param string $text
     * @return \simple_html_dom_node
     */
    private function getElementWithText($elements, $text) {
        foreach ($elements as $element) {
            if (strpos($element->plaintext, $text) !== false) {
                return $element;
            }
        }
        return null;
    }
}

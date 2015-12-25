<?php
namespace automation\SourceSite;

use automation\Product;
use automation\ProductSource;
use model\catalog\ImportCategory;
use model\catalog\Manufacturer;
use model\catalog\Supplier;
use model\extension\ImportSourceSite;

class CutyKids extends ProductSource {

    protected function fillDetails($product) {
        $matches = array();
        $html = $this->getHtmlDocument($product->url);
        /// Get name
        $product->name = $html->find('font.text13 b', 0)->innertext;
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

        $html->clear();
    }

    protected function getCategoryProducts($category) {
        $products = array();
        $html = $this->getHtmlDocument($category->getUrl());
        $lastPageLink = $this->getElementWithText($html->find('table.paging a'), "맨끝");
        $pagesNum = preg_match('/(?<=&pg=)\d+/', $lastPageLink->attr['href'], $matches) ? $matches[0] : 1;
        $matches = array();
        for ($currPage = 1; $currPage <= $pagesNum; $currPage++) {
            echo "Page $currPage of $pagesNum\n"; $tmp = 1;
            $items = $html->find('a[href^="list.php?ai_id="]');
            /** @var \simple_html_dom_node $item */
            foreach ($items as $item) {
                $sourceProductId = preg_match('/(?<=ai_id=)\d+/', $item->attr['href'], $matches) ? $matches[0] : null;
                $products[] = new Product(
                    $this,
                    array(preg_match('/(?<=ac_id=)\d+/', $item->attr['href'], $matches) ? $matches[0] : $category->getSourceSiteCategoryId()),
                    $sourceProductId,
                    null, //mb_convert_encoding($item->first_child()->attr['title'],  'utf-8', 'euc-kr'),
                    $this->getRootUrl() . '/' . $item->attr['href'],
                    $item->first_child()->attr['src'],
                    null,
                    null,
                    0.25
                );
            }
            if ($currPage < $pagesNum) {
                $html = $this->getHtmlDocument($category->getUrl() . '&pg=' . ($currPage + 1));
            }
        }
        echo "Got " . sizeof($products) . " products\n";
        $html->clear();
        return $products;
    }

    protected function getAllCategories() {
        $categories = array(); $matches = [];
        echo "Getting categories from " . self::getUrl() . "\n";
        $html = $this->getHtmlDocument(self::getUrl() . "/index_user.php");
        $items = $html->find('div.w1150 a[href^="./main.php?ac_id="]');
        foreach ($items as $categoryAElement) {
            $categoryId = preg_match('/\d+/', $categoryAElement->attr['href'], $matches) ? $matches[0] : null;
            $categories[] = new ImportCategory($this, $categoryId, null, null, $this->getCategoryUrl($categoryId));
        }
        $html->clear();
        return $categories;
    }

    public function getCategoryUrl($sourceSiteCategoryId) {
        return $this->getUrl() . "/main.php?ac_id=$sourceSiteCategoryId";
    }

    public function getUrl() { return 'http://cutykids.com'; }

    /**
     * @param ImportCategory $category
     * @return int
     * The amount isn't precise as last page may contain less items than the rest of pages
     */
    protected function getCategoryProductsCount($category) {
        $matches = [];
        $html = $this->getHtmlDocument($category->getUrl());
        $lastPageLink = $this->getElementWithText($html->find('table.paging a'), "맨끝");
        $lastPage = preg_match('/(?<=&pg=)\d+/', $lastPageLink->attr['href'], $matches) ? $matches[0] : 1;
        $itemsPerPage = sizeof($html->find('a[href^="list.php?ai_id="]'));
        $html->clear();
        return $itemsPerPage * $lastPage;
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

    public static function createDefaultImportSourceSiteInstance() {
        return new ImportSourceSite(
            explode("\\", get_class())[2],
            [],
            [],
            new Manufacturer(0),
            new Supplier(0),
            false,
            "Cuty Kids",
            1,
            [0, 2],
            1
        );
    }

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

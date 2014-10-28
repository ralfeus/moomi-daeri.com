<?php
namespace automation\SourceSite;

//class EtudeHouse extends GMarketCoKr {
//    public function __construct() {
//        $this->shopId = 'TE1NR38zNjMxOExwMTYwNjI0MDl/Rw==';
//    }
//
//    /**
//     * @return stdClass
//     */
//    public function getSite() {
//        return (object)array( 'id' => 6, 'name' => 'Etude House');
//    }
//}

use automation\Product;
use automation\ProductSource;

class EtudeHouse extends ProductSource {

    protected function getAllCategoriesUrls() {
        $categories = array(); $xml = array();
        xml_parse_into_struct(
            xml_parser_create(),
            file_get_contents($this->getRootUrl() . "/images/flash/data/menu.xml"),
            $xml);
        foreach ($xml as $element) {
            if (($element['level'] == 2) && ($element['type'] == 'close')) {
                break;
            } elseif (($element['tag'] == 'MENU')
                && (($element['level'] == 3) && ($element['type'] == 'open'))) {
                $categories[] = $this->getRootUrl() . $element['attributes']['URL'];
            }
        }
        return $categories;
    }

    protected function getMappedCategoriesUrls() {
        $categories = array();
        foreach (array_keys($this->getSite()->getCategoriesMap()) as $sourceSiteCategory) {
//                foreach ($xml as $element) {
//                    if (($element['level'] == 2) && ($element['type'] == 'close')) {
//                        break;
//                    } elseif (($element['tag'] == 'MENU')
//                        && ((($element['level'] == 3) && ($element['type'] == 'open')) || (($element['level'] == 4) && ($element['type'] == 'complete')))
//                        && preg_match("/$sourceSiteCategory/", $element['attributes']['URL'])) {
//                        $categories[] = $this->getRootUrl() . $element['attributes']['URL'];
//                        break;
//                    }
//                }
            if (preg_match('/\d{3}000/', $sourceSiteCategory)) {
                $categories[] = $this->getRootUrl() . 'product.do?method=submain&catCd1=' . $sourceSiteCategory;
            } elseif (preg_match('/\d{6}/', $sourceSiteCategory)) {
                $categories[] = $this->getRootUrl() . 'product.do?method=list&catCd2=' . $sourceSiteCategory;
            }
        }
        return $categories;
    }

    public function getProducts() {
        echo date('Y-m-d H:i:s') . "\n";
        $products = array();
        $urls = $this->getCategoryUrls(); $currCategory = 1;
        foreach ($urls as $url) {
            echo "Crawling " . $currCategory++ . " of " . sizeof($urls) . ": $url\n";
            $products = array_merge($products, $this->getCategoryProducts($url));
            //break;
        }
        echo "Totally found " . sizeof($products) . " products\n";
        echo date('Y-m-d H:i:s') . " --- Finished\n";
        return $products;    }

//    /**
//     * @return stdClass
//     */
//    public function getSite() { return (object) array('id' => 6, 'name' => 'EtudeHouse'); }

    public function getUrl() {
        return 'http://www.etude.co.kr';
    }

    /**
     * @param string $categoryUrl
     * @return  array
     */
    private function getCategoryProducts($categoryUrl) {
        $products = array(); $matches = array();

        $categoryId = preg_match('/(?<=catCd2=)\d+/', $categoryUrl, $matches)
            ? $matches[0]
            : preg_match('/(?<=catCd1=)\d+/', $categoryUrl, $matches)
                ? $matches[0]
                : null;
//        $categoryUrl = 'http://etonymoly.com/common/ajax/exec_getProdList.asp?cate=' . $categoryId;
        $html = $this->getHtmlDocument($categoryUrl);
        $pages = $html->find('a[href^=javascript:searchByTarget]', -1);
        $pagesNum = (!is_null($pages) && preg_match('/(?<=searchByTarget\(\')\\d+/', $pages->attr['href'], $matches))
            ? $matches[0] : 1;
        for ($currPage = 1; $currPage <= $pagesNum; $currPage++) {
            echo "Page $currPage of $pagesNum\n"; $tmp = 1;
            /** @var \simple_html_dom_node[] $items */
            $items = $html->find('ul.NBPrdList1 li.prdList');
            foreach ($items as $item) {
                echo date('H:i:s') . "\tItem " . $tmp++ . " of " . sizeof($items) . "\n";
                if (!sizeof($item->find('img')))
                    continue;
//                preg_match('/(?<=guid=)\d+/', $item->attr['href'], $matches);
                $itemId = preg_match('/(?<=View\(\')\d+/', $item->findOne('a[href^=javascript:productView]')->attr['href'], $matches) ? $matches[0] : null;
                $product = new Product(
                    $this,
                    $categoryId,
                    $itemId,
                    $item->findOne('dl.prd_info dt.title a')->text(),
                    $this->getUrl() . "/product.do?method=view&prdCd=$itemId",
                    $item->findOne('div.thumArea a.thum img')->attr['src'],
                    null,
                    null,
                    0.25
                );
                if ($this->addProductToList($product, $products)) {
                    $this->fillDetails($product);
                }
//                $products[] = $product;
            }
            if ($currPage < $pagesNum)
                $html = $this->getHtmlDocument($categoryUrl . "&pageNum=" . ($currPage + 1));
        }
        echo "Got " . sizeof($products) . " products\n";
        return $products;
    }

    private function fillDetails($product) {
        $matches = array();
        $html = $this->getHtmlDocument($product->url);

        /// Get images
        //TODO: Currently simple_html_dom doesn't support > sign in selector as immediate child of the element
        //TODO: Until that should use no > sign in the selectors
//        $imageElements = $html->find('div.layer_imageZoom>ul.small_pic li img[onclick!=viewThumb(\'http://image.etude.co.kr/upload/product/\', \'imageZoom\');return false;');
        $imageElements = $html->find('ul#content2_sub li a[onclick^=viewThumb(\'http://image.etude.co.kr/upload/product/]');
        foreach ($imageElements as $item) {
            if (preg_match('/(?<=viewThumb\(\')(.+)320_([^\']+)/', $item->attr['onclick'], $matches)) {
                $product->images[] = $matches[1] . $matches[2];
            }
        }

        /// Get description
        $item = $html->find('div.detail_view', 0);
        $product->description = trim($item->innertext);
        $product->description = preg_replace('/(?<=src=\")\//', $this->getRootUrl(), $product->description);

        /// Get price and promo price
        $product->price = preg_replace('/\D+/', '', $html->find('dd.special em', 0)->plaintext);
        $item = $html->find('span.Sprice', 0);
        if ($item != null) {
            $product->promoPrice = preg_replace('/\D+/', '', $item->plaintext);
        }

        $html->clear();
    }
}

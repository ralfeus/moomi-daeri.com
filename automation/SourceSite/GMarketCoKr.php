<?php
namespace automation\SourceSite;

use automation\ProductSource;
use automation\Product;
use model\catalog\ImportCategory;

abstract class GMarketCoKr extends ProductSource {
    protected $shopId;

    protected function fillDetails($product) {
        $html = $this->getHtmlDocument($product->url);
        $matches = array();
        $this->parseProductCategories($product, $html);
        /// Get images
        $product->images[] = $html->findOne('img#GoodsImageElem')->attr['src'];

        /// Get description
        $product->description = '';
        $details = $this->getPage(
            "http://item2.gmarket.co.kr/Item/detailview/ItemDetail.aspx?goodscode=" . $product->sourceProductId,
            null,
            null,
            array('Referer' => $product->url)
        );
        $descHtml = str_get_html($details);
        $descNode = $descHtml->find('div.seller_goods', 0);
        if ($descNode) {
            $product->description = $descNode->outertext;
        }
        $descHtml->clear();
        $details = $this->getPage(
            "http://item2.gmarket.co.kr/Item/detailview/ItemDetail1.aspx?goodscode=" . $product->sourceProductId,
            null,
            null,
            array('Referer' => $product->url)
        );
        $descHtml = str_get_html($details);
        $descNode = $descHtml->find('div.seller_goods', 0);
        if ($descNode) {
            $product->description .= $descNode->outertext;
        }
        $descHtml->clear();

        /// Get price and promo price
        $originalPriceElement = $html->find('tr#trCostPrice>td>p>del', 0);
        if ($originalPriceElement) {
            $product->promoPrice = $product->price;
            $product->price = preg_replace('/\D+/', '', $originalPriceElement->plaintext);
        }

        $html->clear();
    }

    protected function getAllCategories() {
        return
            array(
                new ImportCategory(
                    $this, null, null, null,
                    "http://gshop.gmarket.co.kr/SearchService/SeachListTemplateAjax?GdlcCd=&GdmcCd=&GdscCd=&type=LIST&searchType=LIST&isDiscount=False&isGmileage=False&isGStamp=False&listType=LIST&IsBookCash=False&CustNo=" . urlencode($this->shopId) . "&CurrPage=minishop"
                )
            );
    }

    protected function getCategoryProducts($category) {
        $page = 0; $productsToGet = $this->getCategoryProductsCount($category);
        $categoryProducts = array();
        list($address, $query) = explode('?', $category->getUrl());
        do {
            $chunk = min($productsToGet, 200); $page++; $productsToGet -= $chunk;
            $output = $this->getPage(
                $address,
                null,
                "$query&page=$page&pageSize=$chunk",
                array("Content-Type" => "application/x-www-form-urlencoded; charset=UTF-8")
            );
            $json = json_decode($output);
            $htmlDom = str_get_html($json->message);
            $items = $htmlDom->find('tr');
            /** @var \simple_html_dom_node $item */
            $tmp = 0;
            foreach ($items as $item) {
                //            $aElement = $item->find('a[href*=category_detail.php]', 0);
                $categoryProducts[] = new Product(
                    $this,
                    null,
                    preg_match('/(?<=goodscode=)\d+/', $item->first_child()->first_child()->first_child()->attr['href'], $matches) ? $matches[0] : null,
                    $item->first_child()->first_child()->first_child()->first_child()->attr['alt'],
                    preg_match('/\(\'(http.+)\'/', $item->first_child()->first_child()->first_child()->attr['href'], $matches) ? $matches[1] : null,
                    $item->first_child()->first_child()->first_child()->first_child()->attr['src'],
                    preg_replace('/\D+/', '', $item->find('li.discount_price', 0)->plaintext),
                    null,
                    0.3
                );
//                if ($tmp++ > 5) break;
            }
            $htmlDom->clear();
        } while ($productsToGet);
        return $categoryProducts;
    }

    /**
     * @param ImportCategory $category
     * @throws \Exception
     * @return int
     */
    protected function getCategoryProductsCount($category) {
        list($address, $query) = explode('?', $category->getUrl());
        $html = $this->getPage(
            $address,
            null,
            "$query&page=1&pageSize=40",
            array("Content-Type" => "application/x-www-form-urlencoded; charset=UTF-8")
        );
        $json = json_decode($html);
        if (isset($json->totalCount)) {
            return $json->totalCount;
        } else {
            throw new \Exception("No product count is defined");
        }
    }

    public function getCategoryUrl($sourceSiteCategoryId) {
        echo date('Y-m-d H:i:s ') . $sourceSiteCategoryId . "\n";
        @list($GdlcCd, $GdmcCd, $GdscCd) = explode('/', $sourceSiteCategoryId);
        return "http://gshop.gmarket.co.kr/SearchService/SeachListTemplateAjax?GdlcCd=$GdlcCd&GdmcCd=$GdmcCd&GdscCd=$GdscCd&type=LIST&searchType=LIST&isDiscount=False&isGmileage=False&isGStamp=False&listType=LIST&IsBookCash=False&CustNo=" . urlencode($this->shopId) . "&CurrPage=minishop";
    }

    /**
     * @param Product $product
     * @param \simple_html_dom $html
     */
    protected function parseProductCategories(&$product, $html) {
        $categoryItem = $html->find("div#headerCate span.last a", 0);
        $GdlcCd = preg_match('/(?<=GdlcCd=)\d+/', $categoryItem->attr['href'], $matches) ? $matches[0] : null;
        $GdmcCd = preg_match('/(?<=GdmcCd=)\d+/', $categoryItem->attr['href'], $matches) ? $matches[0] : null;
        $GdscCd = preg_match('/(?<=GdscCd=)\d+/', $categoryItem->attr['href'], $matches) ? $matches[0] : null;
        $product->categoryIds[] = $GdlcCd;
        $product->categoryIds[] = "$GdlcCd/$GdmcCd";
        $product->categoryIds[] = "$GdlcCd/$GdmcCd/$GdscCd";
    }


//    public function getProducts() {
//        echo date('Y-m-d H:i:s') . "\n";
//	//echo date('Y-m-d H:i:s ') . print_r($this->getSite()->getCategoriesMap(), true);
//	if ($this->getSite()->getImportMappedCategoriesOnly()) {
//		$urls = $this->getCategoryUrl();
//	} else {
//		$urls = $this->getAllCategoriesUrl();
//		echo date('Y-m-d H:i:s ') . "Got " . sizeof($urls) . " category URLs\n";
//	}
//	$urlCount = 1;
//    $products = array();
//	foreach ($urls as $url) {
//	    list($address, $query) = explode('?', $url);
//            $productsCount = $this->getProductsCount($url);
//	    if (count($urls) > 1) {
//		    echo date('Y-m-d H:i:s ') . "Crawling category " . $urlCount++ . " of " . count($urls) . "\n";
//	    }
//            echo date('Y-m-d H:i:s') . " $productsCount products are to be imported\n";
//            $page = 0; $productsToGet = $productsCount;
//            $tmp = 1;
//            do {
//                $chunk = min($productsToGet, 200); $page++; $productsToGet -= $chunk;
//                $output = $this->getPage(
//                    $address,
//                    null,
//                    "$query&page=$page&pageSize=$chunk",
//                    array("Content-Type" => "application/x-www-form-urlencoded; charset=UTF-8")
//                );
//                $json = json_decode($output);
//                $htmlDom = str_get_html($json->message);
//                $items = $htmlDom->find('tr');
//                /** @var \simple_html_dom_node $item */
//                foreach ($items as $item) {
//                    echo date('H:i:s') . "\tItem " . $tmp++ . " of " . $productsCount . "\t- ";
//        //            $aElement = $item->find('a[href*=category_detail.php]', 0);
//                    $product = new Product(
//                        $this,
//                        null,
//                        preg_match('/(?<=goodscode=)\d+/', $item->first_child()->first_child()->first_child()->attr['href'], $matches) ? $matches[0] : null,
//                        $item->first_child()->first_child()->first_child()->first_child()->attr['alt'],
//                        preg_match('/\(\'(http.+)\'/', $item->first_child()->first_child()->first_child()->attr['href'], $matches) ? $matches[1] : null,
//                        $item->first_child()->first_child()->first_child()->first_child()->attr['src'],
//                        preg_replace('/\D+/', '', $item->find('li.discount_price', 0)->plaintext),
//                        null,
//                        0.3
//                    );
//                    if ($this->addProductToList($product, $products)) {
//                        self::fillDetails($product);
//                    }
//                    echo $product->sourceProductId . "\n";
//    //                if ($tmp > 5) break;
//                }
//                $htmlDom->clear();
//            } while ($productsToGet);
//	}
//        echo date('Y-m-d H:i:s') . " --- Finished\n";
//        return $products;
//    }

//    /**
//     * @return int
//     * @throws \Exception
//     */
//    private function getProductsCount($url) {
//	    list($address, $query) = explode('?', $url);
//        $html = $this->getPage(
//            $address,
//            null,
//            "$query&page=1&pageSize=40",
//            array("Content-Type" => "application/x-www-form-urlencoded; charset=UTF-8")
//        );
//        $json = json_decode($html);
//        if (isset($json->totalCount)) {
//            return $json->totalCount;
//        } else {
//            throw new \Exception("No product count is defined");
//        }
//    }

    public function getUrl() { return 'http://gshop.gmarket.co.kr/Minishop/GlobalMinishop?CustNo=' . $this->shopId; }
}

<?php
namespace automation\SourceSite;

use automation\ProductSource;
use automation\Product;

abstract class GMarketCoKr extends ProductSource {
    protected $shopId;

    private function fillDetails(Product $product) {
        $html = $this->getHtmlDocument($product->url);
//        $matches = array();
        /// Get images
        $product->images[] = $product->thumbnail;

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

    protected function getAllCategoriesUrls() { 
	return array ( "http://gshop.gmarket.co.kr/SearchService/SeachListTemplateAjax?GdlcCd=&GdmcCd=&GdscCd=&type=LIST&searchType=LIST&isDiscount=False&isGmileage=False&isGStamp=False&listType=LIST&IsBookCash=False&CustNo=" . urlencode($this->shopId) . "&CurrPage=minishop");
    }

    protected function getMappedCategoriesUrls() {
	$categories = array(); 
	foreach (array_keys($this->getSite()->getCategoriesMap()) as $sourceSiteCategory) {
		echo date('Y-m-d H:i:s ') . $sourceSiteCategory . "\n";
		list($GdlcCd, $GdmcCd, $GdscCd) = explode('/', $sourceSiteCategory);
		$categories[] = "http://gshop.gmarket.co.kr/SearchService/SeachListTemplateAjax?GdlcCd=$GdlcCd&GdmcCd=$GdmcCd&GdscCd=$GdscCd&type=LIST&searchType=LIST&isDiscount=False&isGmileage=False&isGStamp=False&listType=LIST&IsBookCash=False&CustNo=" . urlencode($this->shopId) . "&CurrPage=minishop";
	}
	return $categories;
    }


    public function getProducts() {
        echo date('Y-m-d H:i:s') . "\n";
	//echo date('Y-m-d H:i:s ') . print_r($this->getSite()->getCategoriesMap(), true);
	if ($this->getSite()->getImportMappedCategoriesOnly()) {
		$urls = $this->getMappedCategoriesUrls();
	} else {
		$urls = $this->getAllCategoriesUrls();
		echo date('Y-m-d H:i:s ') . "Got " . sizeof($urls) . " category URLs\n";
	}
	$urlCount = 1;
        $products = array();
	foreach ($urls as $url) {
	    list($address, $query) = explode('?', $url);
            $productsCount = $this->getProductsCount($url);
	    if (count($urls > 1)) {
		echo date('Y-m-d H:i:s ') . "Crawling category " . $urlCount++ . " of " . count($urls) . "\n";
	    }
            echo date('Y-m-d H:i:s') . " $productsCount products are to be imported\n";
            $page = 0; $productsToGet = $productsCount;
            $tmp = 1;
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
                foreach ($items as $item) {
                    echo date('H:i:s') . "\tItem " . $tmp++ . " of " . $productsCount . "\t- ";
        //            $aElement = $item->find('a[href*=category_detail.php]', 0);
                    $product = new Product(
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
                    if ($this->addProductToList($product, $products)) {
                        self::fillDetails($product);
                    }
                    echo $product->sourceProductId . "\n";
    //                if ($tmp > 5) break;
                }
                $htmlDom->clear();
            } while ($productsToGet);
	}
        echo date('Y-m-d H:i:s') . " --- Finished\n";
        return $products;
    }

    /**
     * @return int
     * @throws \Exception
     */
    private function getProductsCount($url) {
	list($address, $query) = explode('?', $url);
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

    public function getUrl() { return 'http://gshop.gmarket.co.kr/Minishop/GlobalMinishop?CustNo=' . $this->shopId; }
}

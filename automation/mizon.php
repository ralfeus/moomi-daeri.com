<?php

class Mizon extends ProductSource {
    private function __construct() {}

    private function fillDetails(Product $product) {
        $html = $this->getHtmlDocument($product->url);
        $matches = array();
        /// Get images
        $product->images[] = $product->thumbnail;

        /// Get description
        $product->description = '';
        $details = $this->getPage(
            "http://item2.gmarket.co.kr/Item/detailview/ItemDetail.aspx?goodscode=" . $product->id,
            null,
            null,
            array('Referer' => $product->url)
        );
        if (preg_match('/<center>.*<\/center>/s', $details, $matches)) {
            $product->description = $matches[0];
        }
        $details = $this->getPage(
            "http://item2.gmarket.co.kr/Item/detailview/ItemDetail1.aspx?goodscode=" . $product->id,
            null,
            null,
            array('Referer' => $product->url)
        );
        if (preg_match('/<center>.*<\/center>/s', $details, $matches)) {
            $product->description .= $matches[0];
        }

        /// Get price and promo price
        $originalPriceElement = $html->find('tr#trCostPrice>td>p>del', 0);
        if ($originalPriceElement) {
            $product->promoPrice = $product->price;
            $product->price = preg_replace('/\D+/', '', $originalPriceElement->plaintext);
        }

        $html->clear();
    }

    public function getProducts() {
        echo date('Y-m-d H:i:s') . "\n";
        $productsCount = $this->getProductsCount();
        echo date('Y-m-d H:i:s') . " $productsCount are to be imported\n";
        $output = $this->getPage(
            'http://gshop.gmarket.co.kr/SearchService/SeachListTemplateAjax',
            null,
            "type=LIST&page=1&pageSize=$productsCount&GdlcCd=&GdmcCd=&GdscCd=&searchType=LIST&isDiscount=False&isGmileage=False&isGStamp=False&listType=LIST&IsBookCash=False&CustNo=TI5MR38DMTUxNY1zOTUzMzUxNjB%2FRw%3D%3D&CurrPage=minishop",
            array("Content-Type" => "application/x-www-form-urlencoded; charset=UTF-8")
        );
        $json = json_decode($output);
        $htmlDom = str_get_html($json->message);
        $items = $htmlDom->find('tr');
        $products = array();
        $tmp = 1;
        foreach ($items as $item) {
            echo date('H:i:s') . "\tItem " . $tmp++ . " of $productsCount\n";
//            $aElement = $item->find('a[href*=category_detail.php]', 0);
            $product = new Product(
                $this,
                null,
                preg_match('/(?<=goodscode=)\d+/', $item->first_child()->first_child()->first_child()->attr['href'], $matches) ? $matches[0] : null,
                $item->first_child()->first_child()->first_child()->first_child()->attr['alt'],
                $item->first_child()->first_child()->first_child()->attr['href'],
                $item->first_child()->first_child()->first_child()->first_child()->attr['src'],
                preg_replace('/\D+/', '', $item->find('li.discount_price', 0)->plaintext),
                null,
                0.3
            );
            if ($this->addProductToList($product, $products)) {
                self::fillDetails($product);
            }
        }
        $htmlDom->clear();
        echo date('Y-m-d H:i:s') . " --- Finished\n";
        return $products;
    }

    private function getProductsCount() {
        $html = $this->getPage(
            'http://gshop.gmarket.co.kr/SearchService/SeachListTemplateAjax',
            null,
            "type=LIST&page=1&pageSize=40&GdlcCd=&GdmcCd=&GdscCd=&searchType=LIST&isDiscount=False&isGmileage=False&isGStamp=False&listType=LIST&IsBookCash=False&CustNo=TI5MR38DMTUxNY1zOTUzMzUxNjB%2FRw%3D%3D&CurrPage=minishop",
            array("Content-Type" => "application/x-www-form-urlencoded; charset=UTF-8")
        );
        $json = json_decode($html);
        if (isset($json->totalCount)) {
            return $json->totalCount;
        } else {
            throw new Exception("No product count is defined");
        }
    }

    /**
     * @return Mizon
     */
    public static function getInstance() {
        if (!self::$instance)
            self::$instance = new Mizon();
        return self::$instance;
    }

    /**
     * @return stdClass
     */
    public function getSite() { return (object)array( 'id' => 4, 'name' => 'mizon'); }

    public function getUrl() { return 'http://gshop.gmarket.co.kr/Minishop/GlobalMinishop?CustNo=TI5MR38DMTUxNY1zOTUzMzUxNjB/Rw=='; }
}
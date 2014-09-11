<?php
require_once('gmarket.co.kr.php');
class ItsSkin extends GMarketCoKr {
    public function __construct() {
        $this->shopId = 'jIwMR38DODcxNU12MzQ1MjkxNDJ/Rw==';
    }

    /**
     * @return stdClass
     */
    public function getSite() { return (object)array( 'id' => 17, 'name' => 'ItsSkin'); }
}
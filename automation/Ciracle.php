<?php
require_once('gmarket.co.kr.php');
class Ciracle extends GMarketCoKr {
    public function __construct() {
        $this->shopId = 'zE3MR38DNzkxMk02NjExNzg4OTh/Rw==';
    }

    /**
     * @return stdClass
     */
    public function getSite() { return (object)array( 'id' => 13, 'name' => 'Ciracle'); }
}
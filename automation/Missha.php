<?php
require_once('gmarket.co.kr.php');
class Missha extends GMarketCoKr {
    public function __construct() {
        $this->shopId = 'TAzMR38TODMxME3zODg4NTkzMjR/Rw==';
    }

    /**
     * @return stdClass
     */
    public function getSite() { return (object)array( 'id' => 2, 'name' => 'Missha'); }
}
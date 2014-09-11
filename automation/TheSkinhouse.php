<?php
require_once('gmarket.co.kr.php');
class TheSkinhouse extends GMarketCoKr {
    public function __construct() {
        $this->shopId = 'jI4MR38DNDcxMg51NzUxODg5MzF/Rw==';
    }

    /**
     * @return stdClass
     */
    public function getSite() { return (object)array( 'id' => 11, 'name' => 'TheSkinhouse'); }
}
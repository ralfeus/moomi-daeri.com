<?php
require_once('gmarket.co.kr.php');
class HolikaHolika extends GMarketCoKr {
    public function __construct() {
        $this->shopId = 'TE5NR38zOTgxOM32OTc0Mzk5NTJ/Rw==';
    }

    /**
     * @return stdClass
     */
    public function getSite() {
        return (object)array( 'id' => 5, 'name' => 'HolikaHolika');
    }
}
<?php
require_once('gmarket.co.kr.php');
class Skinfod extends GMarketCoKr {
    public function __construct() {
        $this->shopId = 'TE3NR38DNjExOk20MzY1MTc3ODl/Rw==';
    }

    /**
     * @return stdClass
     */
    public function getSite() { return (object)array( 'id' => 9, 'name' => 'Skinfod'); }
}
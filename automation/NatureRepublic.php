<?php
require_once('gmarket.co.kr.php');
class NatureRepublic extends GMarketCoKr {
    public function __construct() {
        $this->shopId = 'zEyOR38TOTAxMQwzMzYzMzk2NzF/Rw==';
    }

    /**
     * @return stdClass
     */
    public function getSite() { return (object)array( 'id' => 1, 'name' => 'NatureRepublic'); }
}
<?php
require_once('gmarket.co.kr.php');
class TheFaceshop extends GMarketCoKr {
    public function __construct() {
        $this->shopId = 'jA0MR38zMzcxNUz3NzY2NjcyOTh/Rw==';
    }

    /**
     * @return TheFaceshop
     */
    public static function getInstance() {
        if (!self::$instance || !(self::$instance instanceof TheFaceshop))
            self::$instance = new self();
        return self::$instance;
    }

    /**
     * @return stdClass
     */
    public function getSite() { return (object)array( 'id' => 7, 'name' => 'The Faceshop'); }
}
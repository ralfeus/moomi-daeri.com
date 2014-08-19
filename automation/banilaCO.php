<?php
require_once('gmarket.co.kr.php');
class BanilaCO extends GMarketCoKr {
    public function __construct() {
        $this->shopId = 'jI4MR38jNjIxNY21MDgzODY1NjN/Rw==';
    }

    /**
     * @return BanilaCO
     */
    public static function getInstance() {
        if (!self::$instance || !(self::$instance instanceof BanilaCO))
            self::$instance = new self();
        return self::$instance;
    }

    /**
     * @return stdClass
     */
    public function getSite() { return (object)array( 'id' => 12, 'name' => 'BanilaCO'); }
}
<?php
require_once('gmarket.co.kr.php');
class HolikaHolika extends GMarketCoKr {
    public function __construct() {
        $this->shopId = 'zE4OR38TMjcxMI2wODU3ODcwNjR/Rw==';
    }

    /**
     * @return ProductSource
     */
    public static function getInstance() {
        if (!self::$instance || !(self::$instance instanceof HolikaHolika))
            self::$instance = new self();
        return self::$instance;
    }

    /**
     * @return stdClass
     */
    public function getSite() {
        return (object)array( 'id' => 5, 'name' => 'HolikaHolika');
    }
}
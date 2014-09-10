<?php
require_once('gmarket.co.kr.php');
class Lush extends GMarketCoKr {
    public function __construct() {
        $this->shopId = 'TE3OR38DNDQxNc5zNTgwNDU5NTl/Rw==';
    }

    /**
     * @return Lush
     */
    public static function getInstance() {
        if (!self::$instance || !(self::$instance instanceof Lush))
            self::$instance = new self();
        return self::$instance;
    }

    /**
     * @return stdClass
     */
    public function getSite() { return (object)array( 'id' => 16, 'name' => 'Lush'); }
}
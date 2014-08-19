<?php
require_once('gmarket.co.kr.php');
class Innisfree extends GMarketCoKr {
    public function __construct() {
        $this->shopId = 'jE3OR38TNDcxMEw4MzE4Njg1MDl/Rw==';
    }

    /**
     * @return Innisfree
     */
    public static function getInstance() {
        if (!self::$instance || !(self::$instance instanceof Innisfree))
            self::$instance = new self();
        return self::$instance;
    }

    /**
     * @return stdClass
     */
    public function getSite() { return (object)array( 'id' => 8, 'name' => 'Innisfree'); }
}
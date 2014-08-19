<?php
require_once('gmarket.co.kr.php');
class Ciracle extends GMarketCoKr {
    public function __construct() {
        $this->shopId = 'zE3MR38DNzkxMk02NjExNzg4OTh/Rw==';
    }

    /**
     * @return Ciracle
     */
    public static function getInstance() {
        if (!self::$instance || !(self::$instance instanceof Ciracle))
            self::$instance = new self();
        return self::$instance;
    }

    /**
     * @return stdClass
     */
    public function getSite() { return (object)array( 'id' => 13, 'name' => 'Ciracle'); }
}
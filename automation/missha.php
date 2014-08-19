<?php
require_once('gmarket.co.kr.php');
class Missha extends GMarketCoKr {
    public function __construct() {
        $this->shopId = 'TAzMR38TODMxME3zODg4NTkzMjR/Rw==';
    }

    /**
     * @return Missha
     */
    public static function getInstance() {
        if (!self::$instance || !(self::$instance instanceof Missha))
            self::$instance = new self();
        return self::$instance;
    }

    /**
     * @return stdClass
     */
    public function getSite() { return (object)array( 'id' => 2, 'name' => 'Missha'); }
}
<?php
require_once('gmarket.co.kr.php');
class NatureRepublic extends GMarketCoKr {
    public function __construct() {
        $this->shopId = 'zEyOR38TOTAxMQwzMzYzMzk2NzF/Rw==';
    }

    /**
     * @return NatureRepublic
     */
    public static function getInstance() {
        if (!self::$instance || !(self::$instance instanceof NatureRepublic))
            self::$instance = new self();
        return self::$instance;
    }

    /**
     * @return stdClass
     */
    public function getSite() { return (object)array( 'id' => 1, 'name' => 'NatureRepublic'); }
}
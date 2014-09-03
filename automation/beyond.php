<?php
require_once('gmarket.co.kr.php');
class Beyond extends GMarketCoKr {
    public function __construct() {
        $this->shopId = 'TI1MR38zNjIxMY05MTU5NTE2NDV/Rw==';
    }

    /**
     * @return Beyond
     */
    public static function getInstance() {
        if (!self::$instance || !(self::$instance instanceof Beyond))
            self::$instance = new self();
        return self::$instance;
    }

    /**
     * @return stdClass
     */
    public function getSite() { return (object)array( 'id' => 14, 'name' => 'Beyond'); }
}
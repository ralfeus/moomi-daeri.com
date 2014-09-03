<?php
require_once('gmarket.co.kr.php');
class SecretKey extends GMarketCoKr {
    public function __construct() {
        $this->shopId = 'DE3NR38jMDcxOA1yMDI1MjMxODd/Rw==';
    }

    /**
     * @return Beyond
     */
    public static function getInstance() {
        if (!self::$instance || !(self::$instance instanceof SecretKey))
            self::$instance = new self();
        return self::$instance;
    }

    /**
     * @return stdClass
     */
    public function getSite() { return (object)array( 'id' => 15, 'name' => 'SecretKey'); }
}
<?php
require_once('gmarket.co.kr.php');
class TheSaem extends GMarketCoKr {
    public function __construct() {
        $this->shopId = 'DI5NR38DODExOUzzNzY3MTU3ODh/Rw==';
    }

    /**
     * @return TheSaem
     */
    public static function getInstance() {
        if (!self::$instance || !(self::$instance instanceof TheSaem))
            self::$instance = new self();
        return self::$instance;
    }

    /**
     * @return stdClass
     */
    public function getSite() { return (object)array( 'id' => 10, 'name' => 'TheSaem'); }
}
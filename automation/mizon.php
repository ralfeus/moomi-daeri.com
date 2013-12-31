<?php
require_once('gmarket.co.kr.php');
class Mizon extends GMarketCoKr {
    public function __construct() {
        $this->shopId = 'TI5MR38DMTUxNY1zOTUzMzUxNjB/Rw==';
    }

    /**
     * @return Mizon
     */
    public static function getInstance() {
        if (!self::$instance)
            self::$instance = new self();
        return self::$instance;
    }

    /**
     * @return stdClass
     */
    public function getSite() { return (object)array( 'id' => 4, 'name' => 'Mizon'); }
}
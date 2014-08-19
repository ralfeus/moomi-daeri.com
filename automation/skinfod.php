<?php
require_once('gmarket.co.kr.php');
class Skinfod extends GMarketCoKr {
    public function __construct() {
        $this->shopId = 'TE3NR38DNjExOk20MzY1MTc3ODl/Rw==';
    }

    /**
     * @return Mizon
     */
    public static function getInstance() {
        if (!self::$instance || !(self::$instance instanceof Skinfod))
            self::$instance = new self();
        return self::$instance;
    }

    /**
     * @return stdClass
     */
    public function getSite() { return (object)array( 'id' => 9, 'name' => 'Skinfod'); }
}
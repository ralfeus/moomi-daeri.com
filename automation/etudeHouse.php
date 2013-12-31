<?php
require_once('gmarket.co.kr.php');
class EtudeHouse extends GMarketCoKr {
    public function __construct() {
        $this->shopId = 'TE1NR38zNjMxOExwMTYwNjI0MDl/Rw==';
    }

    /**
     * @return ProductSource
     */
    public static function getInstance() {
        if (!self::$instance)
            self::$instance = new self();
        return self::$instance;
    }

    /**
     * @return stdClass
     */
    public function getSite() {
        return (object)array( 'id' => 6, 'name' => 'Etude House');
    }
}
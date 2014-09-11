<?php
require_once('gmarket.co.kr.php');
class EtudeHouse extends GMarketCoKr {
    public function __construct() {
        $this->shopId = 'TE1NR38zNjMxOExwMTYwNjI0MDl/Rw==';
    }

    /**
     * @return stdClass
     */
    public function getSite() {
        return (object)array( 'id' => 6, 'name' => 'Etude House');
    }
}
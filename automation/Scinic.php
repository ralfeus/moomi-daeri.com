<?php
require_once('gmarket.co.kr.php');
class Scinic extends GMarketCoKr {
    public function __construct() {
        $this->shopId = 'DAxNR38DNTgxMQ54MzAzOTcwMjN/Rw==';
    }

    /**
     * @return Scinic
     */
    public static function getInstance() {
        if (!self::$instance || !(self::$instance instanceof Scinic))
            self::$instance = new self();
        return self::$instance;
    }

    /**
     * @return stdClass
     */
    public function getSite() { return (object)array( 'id' => 18, 'name' => 'Scinic'); }
}
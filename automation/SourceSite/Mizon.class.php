<?php
namespace automation\SourceSite;

class Mizon extends GMarketCoKr {
    public function __construct() {
        $this->shopId = 'TI5MR38DMTUxNY1zOTUzMzUxNjB/Rw==';
    }

    /**
     * @return \stdClass
     */
    public function getSite() { return (object)array( 'id' => 4, 'name' => 'Mizon'); }
}

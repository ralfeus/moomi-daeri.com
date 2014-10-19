<?php
namespace automation\SourceSite;

class Homeplus extends GMarketCoKr {
    public function __construct() {
        $this->shopId = 'jI4MR38jNjIxNY21MDgzODY1NjN/Rw==';
    }

    /**
     * @return \stdClass
     */
    public function getSite() { return (object)array( 'id' => 12, 'name' => 'Homeplus'); }
}
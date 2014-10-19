<?php
namespace automation\SourceSite;

class Lotte extends GMarketCoKr {
    public function __construct() {
        $this->shopId = 'zI3MR38TOTkxMcx5NzE5MTEyODR/Rw==';
    }

    /**
     * @return \stdClass
     */
    public function getSite() { return (object)array( 'id' => 19, 'name' => 'Lotte'); }
}
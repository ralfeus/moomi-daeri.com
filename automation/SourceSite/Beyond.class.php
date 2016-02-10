<?php
namespace automation\SourceSite;

use model\catalog\Manufacturer;
use model\catalog\Supplier;
use model\extension\ImportSourceSite;

class Beyond extends GMarketCoKr {
    public function __construct() {
        $this->shopId = 'TI1MR38zNjIxMY05MTU5NTE2NDV/Rw==';
    }

    ///**
    // * @return \stdClass
    // */
    //public function getSite() { return (object)array( 'id' => 14, 'name' => 'Beyond'); }

    public static function createDefaultImportSourceSiteInstance() {
        return new ImportSourceSite(
            explode("\\", get_class())[2],
            [],
            [],
            new Manufacturer(0),
            new Supplier(0),
            false,
            "Beyond",
            1,
            [0, 2],
            1
        );
    }
}

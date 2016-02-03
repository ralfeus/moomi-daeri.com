<?php
namespace automation\SourceSite;

use model\catalog\Manufacturer;
use model\catalog\Supplier;
use model\extension\ImportSourceSite;

class Vivajane extends GMarketCoKr {
    public function __construct() {
        $this->shopId = 'zA0OR38DODIxNMw2OTk1MjAxMzR/Rw==';
    }

    public static function createDefaultImportSourceSiteInstance() {
        return new ImportSourceSite(
            explode("\\", get_class())[2],
            [],
            [],
            new Manufacturer(0),
            new Supplier(0),
            false,
            "Vivajane",
            1,
            [0, 2],
            1
        );
    }

//    /**
//     * @return \stdClass
//     */
//    public function getSite() { return (object)array( 'id' => 12, 'name' => 'BanilaCO'); }
}

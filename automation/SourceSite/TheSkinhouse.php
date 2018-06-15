<?php
namespace automation\SourceSite;

use model\catalog\Manufacturer;
use model\catalog\Supplier;
use model\extension\ImportSourceSite;

class TheSkinhouse extends GMarketCoKr {
    public function __construct() {
        $this->shopId = 'jI4MR38DNDcxMg51NzUxODg5MzF/Rw==';
    }

    public static function createDefaultImportSourceSiteInstance() {
        return new ImportSourceSite(
            explode("\\", get_class())[2],
            [],
            [],
            new Manufacturer(0),
            new Supplier(0),
            false,
            "The Skin house",
            1,
            [0, 2],
            1
        );
    }
//    /**
//     * @return \stdClass
//     */
//    public function getSite() { return (object)array( 'id' => 11, 'name' => 'TheSkinhouse'); }
}

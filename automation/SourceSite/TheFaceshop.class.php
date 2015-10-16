<?php
namespace automation\SourceSite;

use model\catalog\Manufacturer;
use model\catalog\Supplier;
use model\extension\ImportSourceSite;

class TheFaceshop extends GMarketCoKr {
    public function __construct() {
        $this->shopId = 'jA0MR38zMzcxNUz3NzY2NjcyOTh/Rw==';
    }

    public static function createDefaultImportSourceSiteInstance() {
        return new ImportSourceSite(
            explode("\\", get_class())[2],
            [],
            [73, 377],
            new Manufacturer(34),
            new Supplier(92),
            false,
            "The Faceshop",
            1,
            [0, 2],
            1
        );
    }

//    /**
//     * @return \stdClass
//     */
//    public function getSite() { return (object)array( 'id' => 7, 'name' => 'The Faceshop'); }
}

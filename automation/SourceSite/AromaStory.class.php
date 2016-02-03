<?php
namespace automation\SourceSite;

use model\catalog\Manufacturer;
use model\catalog\Supplier;
use model\extension\ImportSourceSite;

class AromaStory extends GMarketCoKr {
    public function __construct() {
        $this->shopId = 'TI2NR38jNTUxMY22Nzk0OTYyMDF/Rw==';
    }

    public static function createDefaultImportSourceSiteInstance() {
        return new ImportSourceSite(
            explode("\\", get_class())[2],
            [],
            [],
            new Manufacturer(0),
            new Supplier(0),
            false,
            "Aroma story",
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

<?php
namespace automation\SourceSite;

use model\catalog\Manufacturer;
use model\catalog\Supplier;
use model\extension\ImportSourceSite;

class TheBodyShop extends GMarketCoKr {
    public function __construct() {
        $this->shopId = 'zAyMR38zODIxMg14MjIzMzIxODd/Rw==';
    }

    public static function createDefaultImportSourceSiteInstance() {
        return new ImportSourceSite(
            explode("\\", get_class())[2],
            [],
            [],
            new Manufacturer(0),
            new Supplier(0),
            false,
            "The Body Shop",
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

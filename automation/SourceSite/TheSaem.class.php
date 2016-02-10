<?php
namespace automation\SourceSite;

use model\catalog\Manufacturer;

class TheSaem extends GMarketCoKr {
    public function __construct() {
        $this->shopId = 'DI5NR38DODExOUzzNzY3MTU3ODh/Rw==';
    }

    public static function createDefaultImportSourceSiteInstance() {
        return new \model\extension\ImportSourceSite(
            explode("\\", get_class())[2],
            [],
            [],
            new \model\catalog\Manufacturer(0),
            new \model\catalog\Supplier(0),
            false,
            "The Saem",
            1,
            [0, 2],
            1
        );
    }
//    /**
//     * @return \stdClass
//     */
//    public function getSite() { return (object)array( 'id' => 10, 'name' => 'TheSaem'); }
}

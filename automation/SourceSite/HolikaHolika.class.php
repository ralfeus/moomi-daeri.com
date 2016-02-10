<?php
namespace automation\SourceSite;

class HolikaHolika extends GMarketCoKr {
    public function __construct() {
        $this->shopId = 'TE5NR38zOTgxOM32OTc0Mzk5NTJ/Rw==';
    }

    public static function createDefaultImportSourceSiteInstance() {
        return new \model\extension\ImportSourceSite(
            explode("\\", get_class())[2],
            [],
            [],
            new \model\catalog\Manufacturer(0),
            new \model\catalog\Supplier(0),
            false,
            "Holika Holika",
            1,
            [0, 2],
            1
        );
    }

//    /**
//     * @return \stdClass
//     */
//    public function getSite() {
//        return (object)array( 'id' => 5, 'name' => 'HolikaHolika');
//    }
}

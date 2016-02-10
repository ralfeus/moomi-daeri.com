<?php
namespace automation\SourceSite;

class Lush extends GMarketCoKr {
    public function __construct() {
        $this->shopId = 'TE3OR38DNDQxNc5zNTgwNDU5NTl/Rw==';
    }

    public static function createDefaultImportSourceSiteInstance() {
        return new \model\extension\ImportSourceSite(
            explode("\\", get_class())[2],
            [],
            [],
            new \model\catalog\Manufacturer(0),
            new \model\catalog\Supplier(0),
            false,
            "Lush",
            1,
            [0, 2],
            1
        );
    }

//    /**
//     * @return \stdClass
//     */
//    public function getSite() { return (object)array( 'id' => 16, 'name' => 'Lush'); }
}

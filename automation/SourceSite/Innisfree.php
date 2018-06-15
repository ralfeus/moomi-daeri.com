<?php
namespace automation\SourceSite;

class Innisfree extends GMarketCoKr {
    public function __construct() {
        $this->shopId = 'jE3OR38TNDcxMEw4MzE4Njg1MDl/Rw==';
    }

    public static function createDefaultImportSourceSiteInstance() {
        return new \model\extension\ImportSourceSite(
            explode("\\", get_class())[2],
            [],
            [],
            new \model\catalog\Manufacturer(0),
            new \model\catalog\Supplier(0),
            false,
            "Innis free",
            1,
            [0, 2],
            1
        );
    }

//    /**
//     * @return \stdClass
//     */
//    public function getSite() { return (object)array( 'id' => 8, 'name' => 'Innisfree'); }
}

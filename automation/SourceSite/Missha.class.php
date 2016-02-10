<?php
namespace automation\SourceSite;

class Missha extends GMarketCoKr {
    public function __construct() {
        $this->shopId = 'TAzMR38TODMxME3zODg4NTkzMjR/Rw==';
    }

    public static function createDefaultImportSourceSiteInstance() {
        return new \model\extension\ImportSourceSite(
            explode("\\", get_class())[2],
            [],
            [],
            new \model\catalog\Manufacturer(0),
            new \model\catalog\Supplier(0),
            false,
            "Missha",
            1,
            [0, 2],
            1
        );
    }

//    /**
//     * @return \stdClass
//     */
//    public function getSite() { return (object)array( 'id' => 2, 'name' => 'Missha'); }
}

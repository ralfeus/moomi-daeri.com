<?php
namespace automation\SourceSite;

class NatureRepublic extends GMarketCoKr {
    public function __construct() {
        $this->shopId = 'zEyOR38TOTAxMQwzMzYzMzk2NzF/Rw==';
    }

    public static function createDefaultImportSourceSiteInstance() {
        return new \model\extension\ImportSourceSite(
            explode("\\", get_class())[2],
            [],
            [],
            new \model\catalog\Manufacturer(0),
            new \model\catalog\Supplier(0),
            false,
            "Nature Republic",
            1,
            [0, 2],
            1
        );
    }

//    /**
//     * @return \stdClass
//     */
//    public function getSite() { return (object)array( 'id' => 1, 'name' => 'NatureRepublic'); }
}

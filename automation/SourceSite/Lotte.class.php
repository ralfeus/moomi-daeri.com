<?php
namespace automation\SourceSite;

class Lotte extends GMarketCoKr {
    public function __construct() {
        $this->shopId = 'zI3MR38TOTkxMcx5NzE5MTEyODR/Rw==';
    }

    public static function createDefaultImportSourceSiteInstance() {
        return new \model\extension\ImportSourceSite(
            explode("\\", get_class())[2],
            [],
            [],
            new \model\catalog\Manufacturer(0),
            new \model\catalog\Supplier(0),
            false,
            "Lotte",
            1,
            [0, 2],
            1
        );
    }

//    /**
//     * @return \stdClass
//     */
//    public function getSite() { return (object)array( 'id' => 19, 'name' => 'Lotte'); }
}

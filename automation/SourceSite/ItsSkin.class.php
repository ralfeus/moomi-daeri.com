<?php
namespace automation\SourceSite;

class ItsSkin extends GMarketCoKr {
    public function __construct() {
        $this->shopId = 'jIwMR38DODcxNU12MzQ1MjkxNDJ/Rw==';
    }

    public static function createDefaultImportSourceSiteInstance() {
        return new \model\extension\ImportSourceSite(
            explode("\\", get_class())[2],
            [],
            [],
            new \model\catalog\Manufacturer(0),
            new \model\catalog\Supplier(0),
            false,
            "It's skin",
            1,
            [0, 2],
            1
        );
    }

//    /**
//     * @return \stdClass
//     */
//    public function getSite() { return (object)array( 'id' => 17, 'name' => 'ItsSkin'); }
}

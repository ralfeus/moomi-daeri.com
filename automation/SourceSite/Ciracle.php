<?php
namespace automation\SourceSite;

class Ciracle extends GMarketCoKr {
    public function __construct() {
        $this->shopId = 'zE3MR38DNzkxMk02NjExNzg4OTh/Rw==';
    }

    public static function createDefaultImportSourceSiteInstance() {
        return new \model\extension\ImportSourceSite(
            explode("\\", get_class())[2],
            [],
            [],
            new \model\catalog\Manufacturer(0),
            new \model\catalog\Supplier(0),
            false,
            "Ciracle",
            1,
            [0, 2],
            1
        );
    }

//    /**
//     * @return \stdClass
//     */
//    public function getSite() { return (object)array( 'id' => 13, 'name' => 'Ciracle'); }
}

<?php
namespace automation\SourceSite;

class Scinic extends GMarketCoKr {
    public function __construct() {
        $this->shopId = 'DAxNR38DNTgxMQ54MzAzOTcwMjN/Rw==';
    }

    public static function createDefaultImportSourceSiteInstance() {
        return new \model\extension\ImportSourceSite(
            explode("\\", get_class())[2],
            [],
            [],
            new \model\catalog\Manufacturer(0),
            new \model\catalog\Supplier(0),
            false,
            "Scinic",
            1,
            [0, 2],
            1
        );
    }

//    /**
//     * @return \stdClass
//     */
//    public function getSite() { return (object)array( 'id' => 18, 'name' => 'Scinic'); }
}

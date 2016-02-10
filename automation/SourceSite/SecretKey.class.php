<?php
namespace automation\SourceSite;

class SecretKey extends GMarketCoKr {
    public function __construct() {
        $this->shopId = 'DE3NR38jMDcxOA1yMDI1MjMxODd/Rw==';
    }

    public static function createDefaultImportSourceSiteInstance() {
        return new \model\extension\ImportSourceSite(
            explode("\\", get_class())[2],
            [],
            [],
            new \model\catalog\Manufacturer(0),
            new \model\catalog\Supplier(0),
            false,
            "Secret key",
            1,
            [0, 2],
            1
        );
    }

//    /**
//     * @return \stdClass
//     */
//    public function getSite() { return (object)array( 'id' => 15, 'name' => 'SecretKey'); }
}

<?php
namespace automation\SourceSite;

class Chef extends GMarketCoKr {
    public function __construct() {
        $this->shopId = 'DI1MR38TOTIxOU0yNzA5MDUxODJ/Rw==';
    }

    public static function createDefaultImportSourceSiteInstance() {
        return new \model\extension\ImportSourceSite(
            explode("\\", get_class())[2],
            [],
            [],
            new \model\catalog\Manufacturer(0),
            new \model\catalog\Supplier(0),
            false,
            "Chef",
            1,
            [0, 2],
            1
        );
    }

//    /**
//     * @return \stdClass
//     */
//    public function getSite() { return (object)array( 'id' => 12, 'name' => 'BanilaCO'); }
}

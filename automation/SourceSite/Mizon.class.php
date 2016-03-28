<?php
namespace automation\SourceSite;

class Mizon extends GMarketCoKr {
    public function __construct() {
        $this->shopId = 'DI2OR38DNzQxNUwyOTk1NzMzNDh/Rw==';
    }

    public static function createDefaultImportSourceSiteInstance() {
        return new \model\extension\ImportSourceSite(
            explode("\\", get_class())[2],
            [],
            [],
            new \model\catalog\Manufacturer(0),
            new \model\catalog\Supplier(0),
            false,
            "Mizon",
            1,
            [0, 2],
            1
        );
    }

//    /**
//     * @return \stdClass
//     */
//    public function getSite() { return (object)array( 'id' => 4, 'name' => 'Mizon'); }
}

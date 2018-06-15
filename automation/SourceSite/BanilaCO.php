<?php
namespace automation\SourceSite;

use Exception;
use model\catalog\Manufacturer;
use model\catalog\Supplier;
use model\extension\ImportSourceSite;

class BanilaCO extends GMarketCoKr {
    public function __construct() {
        $this->shopId = 'jI4MR38jNjIxNY21MDgzODY1NjN/Rw==';
    }

//    /**
//     * @return \stdClass
//     */
//    public function getSite() { return (object)array( 'id' => 12, 'name' => 'BanilaCO'); }
    /**
     * @return ImportSourceSite
     * @throws Exception
     */
    public static function createDefaultImportSourceSiteInstance() {
        return new ImportSourceSite(
            explode("\\", get_class())[2],
            [],
            [],
            new Manufacturer(0),
            new Supplier(0),
            false,
            "Banila CO",
            1,
            [0, 2],
            1
        );
    }
}

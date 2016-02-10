<?php
namespace automation\SourceSite;

class Skinfod extends GMarketCoKr {
    public function __construct() {
        $this->shopId = 'TE3NR38DNjExOk20MzY1MTc3ODl/Rw==';
    }

    public static function createDefaultImportSourceSiteInstance() {
        return new \model\extension\ImportSourceSite(
            explode("\\", get_class())[2],
            [],
            [],
            new \model\catalog\Manufacturer(0),
            new \model\catalog\Supplier(0),
            false,
            "Skinfod",
            1,
            [0, 2],
            1
        );
    }
}

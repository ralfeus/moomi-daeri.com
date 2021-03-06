<?php
/**
 * Created by PhpStorm.
 * User: ralfeus
 * Date: 28. 12. 2015
 * Time: 15:56
 */

namespace automation\SourceSite;

use model\catalog\Manufacturer;
use model\catalog\Supplier;
use model\extension\ImportSourceSite;

class Chotykadjon extends CutyKids {
    protected $supplierId = 6;

    public static function createDefaultImportSourceSiteInstance() {
        return new ImportSourceSite(
            explode("\\", get_class())[2],
            [],
            [],
            new Manufacturer(0),
            new Supplier(0),
            false,
            "Cuty Kids / Chotykadjon",
            1,
            [0, 2],
            1
        );
    }
}


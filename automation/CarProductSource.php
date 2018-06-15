<?php
namespace automation;


abstract class CarProductSource extends ProductSource {
    /** @var CarProductSource[] */
    protected static $instances;

    public function getCategories() {
//        if ($this->getSite()->getImportMappedCategoriesOnly()) {
//            return $this->getSite()->getCategoriesMap();
//        } else {
            return $this->getAllCategories();
//        }
    }
}

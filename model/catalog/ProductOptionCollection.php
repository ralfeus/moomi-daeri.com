<?php
/**
 * Created by PhpStorm.
 * User: ralfeus
 * Date: 3/9/2016
 * Time: 4:12 PM
 */

namespace model\catalog;


use system\library\Collection;

/**
 * Class ProductOptionCollection
 * @package model\catalog
 * @method ProductOption current()
 */
class ProductOptionCollection extends Collection {
    /**
     * @param int $optionId
     * @return ProductOption
     */
    public function getByOptionId($optionId) {
        foreach ($this as $productOption) {
            if ($productOption->getOption()->getId() == $optionId) {
                return $productOption;
            }
        }
        return null;
    }
}
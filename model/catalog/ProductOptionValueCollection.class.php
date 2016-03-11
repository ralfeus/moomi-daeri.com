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
 * Class ProductOptionValueCollection
 * @package model\catalog
 * @method ProductOptionValue current()
 */
class ProductOptionValueCollection extends Collection {
    /**
     * @param ProductOptionValue $object
     */
    public function attach($object) {
        parent::attach($object, null);
    }

    /**
     * @param int $productOptionValueId
     * @return ProductOptionValue
     */
    public function getById($productOptionValueId) {
        foreach ($this as $optionValue) {
            if ($optionValue->getId() == $productOptionValueId) {
                return $optionValue;
            }
        }
        return null;
    }

    /**
     * @param int $optionValueId
     * @return ProductOptionValue
     */
    public function getByOptionValueId($optionValueId) {
        foreach ($this as $optionValue) {
            if ($optionValue->getOptionValue()->getId() == $optionValueId) {
                return $optionValue;
            }
        }
        return null;
    }
}
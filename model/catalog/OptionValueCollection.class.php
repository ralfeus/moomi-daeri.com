<?php
/**
 * Created by PhpStorm.
 * User: ralfeus
 * Date: 3/9/2016
 * Time: 4:12 PM
 */

namespace model\catalog;


use SplObjectStorage;

class OptionValueCollection extends SplObjectStorage {
    /**
     * @param OptionValue $object
     */
    public function attach($object) {
        parent::attach($object, null);
    }

    /**
     * @return OptionValue
     */
    public function current() {
        return parent::current();
    }

    /**
     * @param int $optionValueId
     * @return OptionValue
     */
    public function getById($optionValueId) {
        foreach ($this as $optionValue) {
            if ($optionValue->getId() == $optionValueId) {
                return $optionValue;
            }
        }
        return null;
    }
}
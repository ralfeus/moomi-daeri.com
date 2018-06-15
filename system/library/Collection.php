<?php
/**
 * Created by PhpStorm.
 * User: ralfeus
 * Date: 3/10/2016
 * Time: 11:51 AM
 */
namespace system\library;

class Collection extends \SplObjectStorage {
    /**
     * @return self
     */
    public function current() {
        return parent::current();
    }

    /**
     * @return self[]
     */
    public function toArray() {
        $temp = [];
        foreach ($this as $item) {
            $temp[] = $item;
        }
        return $temp;
    }
}
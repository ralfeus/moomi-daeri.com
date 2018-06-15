<?php
/**
 * Base class for objects, which may be listed
 * User: ralfeus
 * Date: 23.07.2016
 * Time: 18:57
 */

namespace system\library;


abstract class ListedObject {
    private $sortOrder;

    /**
     * @return int
     */
    public function getSortOrder() {
        return $this->sortOrder;
    }

    /**
     * @param int $sortOrder
     */
    public function setSortOrder($sortOrder) {
        $this->sortOrder = $sortOrder;
    }
}
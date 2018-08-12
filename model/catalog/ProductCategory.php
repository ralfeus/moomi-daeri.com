<?php
/**
 * Created by PhpStorm.
 * User: ralfeus
 * Date: 23.07.2016
 * Time: 17:36
 */

namespace model\catalog;


class ProductCategory {
    private $category;
    private $isMain;

    /**
     * ProductCategory constructor.
     * @param Category $category
     * @param bool $isMain
     */
    public function __construct($category, $isMain) {
        $this->category = $category;
        $this->isMain = $isMain;
    }

    /**
     * @return Category
     */
    public function getCategory() {
        return $this->category;
    }

    /**
     * @return boolean
     */
    public function isMain() {
        return (bool)$this->isMain;
    }
}
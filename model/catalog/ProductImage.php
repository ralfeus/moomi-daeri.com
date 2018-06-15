<?php
/**
 * Created by PhpStorm.
 * User: ralfeus
 * Date: 23.07.2016
 * Time: 19:00
 */

namespace model\catalog;


use system\library\ListedObject;

class ProductImage extends ListedObject{
    private $imagePath;

    /**
     * ProductImage constructor.
     * @param string $imagePath
     * @param int $sortOrder
     */
    public function __construct($imagePath, $sortOrder) {
        $this->imagePath = $imagePath;
        $this->setSortOrder($sortOrder);
    }

    /**
     * @return string
     */
    public function getImagePath() {
        return $this->imagePath;
    }
}
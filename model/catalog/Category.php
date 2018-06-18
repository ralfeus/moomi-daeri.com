<?php
/**
 * Created by PhpStorm.
 * User: ralfeus
 * Date: 3/14/2016
 * Time: 10:42 AM
 */

namespace model\catalog;


use model\localization\Description;
use model\localization\DescriptionCollection;

class Category {
    private $id;
    private $image;
    private $parentCategory;
    private $top;
    private $column;
    private $sortOrder;
    private $status;
    private $dateAdded;
    private $dateModified;
    private $afcId;
    private $affiliateCommission;
    private $description;
    /** @var  DescriptionCollection */
    private $descriptions;

    /**
     * Category constructor.
     * @param int $id
     * @param string $image
     * @param Category $parentCategory
     * @param bool $top
     * @param int $column
     * @param int $sortOrder
     * @param bool $status
     * @param string $dateAdded
     * @param string $dateModified
     * @param int $afcId
     * @param float $affiliateCommission
     * @param Description $description
     */
    public function __construct($id, $image = null, $parentCategory = null, $top = null, $column = null, $sortOrder = null,
                                $status = null, $dateAdded = null, $dateModified = null, $afcId = null, $affiliateCommission = null,
                                $description = null) {
        $this->id = $id;
        $this->image = $image;
        $this->parentCategory = $parentCategory;
        $this->top = $top;
        $this->column = $column;
        $this->sortOrder = $sortOrder;
        $this->status = $status;
        $this->dateAdded = $dateAdded;
        $this->dateModified = $dateModified;
        $this->afcId = $afcId;
        $this->affiliateCommission = $affiliateCommission;
        $this->description = $description;
    }

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return null|string
     */
    public function getImage() {
        return $this->image;
    }

    /**
     * @return Category|null
     */
    public function getParentCategory() {
        return $this->parentCategory;
    }

    /**
     * @return bool|null
     */
    public function getTop() {
        return $this->top;
    }

    /**
     * @return int|null
     */
    public function getColumn() {
        return $this->column;
    }

    /**
     * @return int|null
     */
    public function getSortOrder() {
        return $this->sortOrder;
    }

    /**
     * @return bool|null
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * @return null|string
     */
    public function getDateAdded() {
        return $this->dateAdded;
    }

    /**
     * @return null|string
     */
    public function getDateModified() {
        return $this->dateModified;
    }

    /**
     * @return int|null
     */
    public function getAfcId() {
        return $this->afcId;
    }

    /**
     * @return float|null
     */
    public function getAffiliateCommission() {
        return $this->affiliateCommission;
    }

    /**
     * @return Description|null
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * @return DescriptionCollection
     */
    public function getDescriptions(): DescriptionCollection {
        if (empty($this->descriptions)) {
            $this->descriptions = CategoryDAO::getInstance()->getCategoryDescriptions($this->id);
        }
        return $this->descriptions;
    }
}
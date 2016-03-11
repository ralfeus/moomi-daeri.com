<?php
/**
 * Created by PhpStorm.
 * User: ralfeus
 * Date: 3/10/2016
 * Time: 8:22 AM
 */

namespace model\catalog;


use model\localization\DescriptionCollection;

class OptionValue {
    /** @var  Option */
    private $option;
    private $id;
    private $image;
    private $sortOrder;
    private $afcId;
    private $descriptions;

    public function __construct($option, $id, $image = null, $sortOrder = null, $afcId = null, $descriptions = null) {
        $this->option = $option;
        $this->id = $id;
        $this->image = $image;
        $this->sortOrder = $sortOrder;
        $this->afcId = $afcId;
        $this->descriptions = $descriptions;
    }

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param int $value
     */
    public function setId($value) {
        $this->id = $value;
    }
    public function getSortOrder() {
        return $this->sortOrder;
    }

    /**
     * @return DescriptionCollection
     */
    public function getDescriptions() {
        if (is_null($this->descriptions)) {
            $this->descriptions = OptionDAO::getInstance()->getOptionValueDescriptions($this->id);
        }
        return $this->descriptions;
    }

    /**
     * @return string
     */
    public function getImage() {
        return $this->image;
    }

    /**
     * @return string
     */
    public function getName() {
        $description = $this->getDescriptions()->getDescription($this->option->getDefaultLanguageId());
        return (is_null($description)) ? '' : $description->getName();
    }

    /**
     * @return Option
     */
    public function getOption() {
        return $this->option;
    }
}
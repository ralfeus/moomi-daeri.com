<?php
/**
 * Created by PhpStorm.
 * User: ralfeus
 * Date: 3/9/2016
 * Time: 10:13 AM
 */
namespace model\catalog;

class ProductOption {
    private $id;
    private $product;
    private $option;
    private $parentOption;
    private $value;
    private $required;
    private $afcId;

    /**
     * ProductOption constructor.
     * @param int $id
     * @param Product $product
     * @param Option $option
     * @param Option $parentOption
     * @param bool $required
     * @param int $afcId
     */
    public function __construct($id, $product, $option, $parentOption, $required, $afcId) {
        $this->id = $id;
        $this->product = $product;
        $this->option = $option;
        $this->parentOption = $parentOption;
        $this->required = $required;
        $this->afcId = $afcId;
        $this->value = ProductDAO::getInstance()->getProductOptionValues($this);
    }

    public function getOption() {
        return $this->option;
    }

    /**
     * @param ProductOptionValue|null $value
     */
    public function deleteValue($value = null) {
        if ($value instanceof ProductOptionValue) {
            $productOptionValue = $this->value->getByOptionValueId($value->getOptionValue()->getId());
            if (!is_null($productOptionValue)) {
                $this->value->detach($productOptionValue);
            }
        } else {
            $this->value = '';
        }
    }

    /**
     * Returns single value in case of single value type
     * or collection in case of multi values type
     * @return ProductOptionValueCollection|string
     * @throws \Exception
     */
    public function getValue() {
        /// Value must be initialized in constructor
//        if (!isset($this->value)) {
//            $this->value = ProductDAO::getInstance()->getProductOptionValues($this->id);
//        }
        return $this->value;
    }

    /**
     * @param ProductOptionValue|string $value
     */
    public function setValue($value) {
        if ($value instanceof ProductOptionValue) {
            $productOptionValue = $this->value->getByOptionValueId($value->getOptionValue()->getId());
            if (!is_null($productOptionValue)) {
                $this->value->detach($productOptionValue);
            }
            $this->value->attach($value);
        } else {
            $this->value = $value;
        }
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

    /**
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @return Option
     */
    public function getParentOption()
    {
        return $this->parentOption;
    }

    /**
     * @return boolean
     */
    public function isRequired()
    {
        return $this->required;
    }

    public function getType() {
        return $this->option->getType();
    }
}
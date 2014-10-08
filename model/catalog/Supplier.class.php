<?php
namespace model\catalog;

class Supplier {
    private $id;
    private $group;
    private $groupId;
    private $name;
    private $internalModel;
    private $shippingCost;
    private $freeShippingThreshold;

    public function __construct($id, $groupId = null, $internalModel = null, $name = null, $shippingCost = null, $freeShippingThreshold = null) {
        $this->id = $id; ///TODO: Перевірити, чи може товар існувати без постачальника
        if (!is_null($groupId)) { $this->groupId = $groupId; }
        if (!is_null($internalModel)) { $this->internalModel = $internalModel; }
        if (!is_null($name)) { $this->name = $name; }
        if (!is_null($shippingCost)) { $this->shippingCost = $shippingCost; }
        if (!is_null($freeShippingThreshold)) { $this->freeShippingThreshold = $freeShippingThreshold ; }
    }

    public function getGroup() {
        if (!isset($this->group)) {
            $this->group = SupplierGroupDAO::getInstance()->getSupplierGroup($this->getGroupId());
        }
        return $this->group;
    }

    /**
     * @return mixed
     */
    public function getGroupId() {
        if (!isset($this->groupId)) {
            $this->groupId = SupplierDAO::getInstance()->getGroupId($this->id);
        }
        return $this->groupId;
    }

    /**
     * @return mixed
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getInternalModel() {
        if (!isset($this->internalModel)) {
            $this->internalModel = SupplierDAO::getInstance()->getInternalModel($this->id);
        }
        return $this->internalModel;
    }

    /**
     * @return mixed
     */
    public function getName() {
        if (!isset($this->name)) {
            $this->name = SupplierDAO::getInstance()->getName($this->id);
        }
        return $this->name;
    }

    /**
     * @return float
     */
    public function getShippingCost() {
        if (!isset($this->shippingCost)) {
            $this->shippingCost = SupplierDAO::getInstance()->getShippingCost($this->id);
        }
        return $this->shippingCost;
    }

    /**
     * @return float
     */
    public function getFreeShippingThreshold() {
        if (!isset($this->freeShippingThreshold)) {
            $this->freeShippingThreshold = SupplierDAO::getInstance()->getFreeShippingThreshold($this->id);
        }
        return $this->freeShippingThreshold;
    }
}
<?php
namespace model\catalog;

use system\library\Mutable;

class Supplier {
    private $id;
    private $group;
    private $groupId;
    private $name;
    private $internalModel;
    private $shippingCost;
    private $freeShippingThreshold;
    private $relatedManufacturer;

    /*
     * @param int $id
     * @param int $groupId
     * @param string $intenalModel
     * @param string @name
     * @param float $shippingCost
     * @param float $freeShippingThreshold
     * @param Manufacturer $relatedManufacturer
     */
    public function __construct($id, $groupId = null, $internalModel = null, $name = null, $shippingCost = null,
                                $freeShippingThreshold = null, $relatedManufacturer = null) {
        $this->id = $id; ///TODO: Перевірити, чи може товар існувати без постачальника
        if (!is_null($groupId)) { $this->groupId = new Mutable($groupId); }
        if (!is_null($internalModel)) { $this->internalModel = new Mutable($internalModel); }
        if (!is_null($name)) { $this->name = new Mutable($name); }
        if (!is_null($relatedManufacturer)) { $this->relatedManufacturer = new Mutable($relatedManufacturer); }
        if (!is_null($shippingCost)) { $this->shippingCost = new Mutable($shippingCost); }
        if (!is_null($freeShippingThreshold)) { $this->freeShippingThreshold = new Mutable($freeShippingThreshold); }
    }

    public function getGroup() {
        if (!isset($this->group)) {
            $this->group = new Mutable(SupplierGroupDAO::getInstance()->getSupplierGroup($this->getGroupId()));
        }
        return $this->group->get();
    }

    /**
     * @return mixed
     */
    public function getGroupId() {
        if (!isset($this->groupId)) {
            $this->groupId = new Mutable(SupplierDAO::getInstance()->getGroupId($this->id));
        }
        return $this->groupId->get();
    }

    /**
     * @param int $groupId
     */
    public function setGroupId($groupId) {
        if (isset($this->groupId)) {
            $this->groupId->set($groupId);
        } else {
            $this->groupId = new Mutable($groupId);
        }
    }

    /**
     * @return mixed
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getInternalModel() {
        if (!isset($this->internalModel)) {
            $this->internalModel = new Mutable(SupplierDAO::getInstance()->getInternalModel($this->id));
        }
        return $this->internalModel->get();
    }

    /**
     * @param string $internalModel
     */
    public function setInternalModel($internalModel) {
        if (isset($this->internalModel)) {
            $this->internalModel->set($internalModel);
        } else {
            $this->internalModel = new Mutable($internalModel);
        }
    }

    /**
     * @return string
     */
    public function getName() {
        if (!isset($this->name)) {
            $this->name = new Mutable(SupplierDAO::getInstance()->getName($this->id));
        }
        return $this->name->get();
    }

    /**
     * @param string $name
     */
    public function setName($name) {
        if (isset($this->name)) {
            $this->name->set($name);
        } else {
            $this->name = new Mutable($name);
        }
    }

    /**
     * @return float
     */
    public function getShippingCost() {
        if (!isset($this->shippingCost)) {
            $this->shippingCost = new Mutable(SupplierDAO::getInstance()->getShippingCost($this->id));
        }
        return $this->shippingCost->get();
    }

    /**
     * @param float $shippingCost
     */
    public function setShippingCost($shippingCost) {
        if (isset($this->shippingCost)) {
            $this->shippingCost->set($shippingCost);
        } else {
            $this->shippingCost = new Mutable($shippingCost);
        }
    }

    /**
     * @return float
     */
    public function getFreeShippingThreshold() {
        if (!isset($this->freeShippingThreshold)) {
            $this->freeShippingThreshold = new Mutable(SupplierDAO::getInstance()->getFreeShippingThreshold($this->id));
        }
        return $this->freeShippingThreshold->get();
    }

    /**
     * @param float $freeShippingThreshold
     */
    public function setFreeShippingThreshold($freeShippingThreshold) {
        if (isset($this->freeShippingThreshold)) {
            $this->freeShippingThreshold->set($freeShippingThreshold);
        } else {
            $this->freeShippingThreshold = new Mutable($freeShippingThreshold);
        }
    }

    /**
     * @return Manufacturer
     */
    public function getRelatedManufacturer() {
        if (!isset($this->relatedManufacturer)) {
            $this->relatedManufacturer = new Mutable(SupplierDAO::getInstance()->getRelatedManufacturer($this->id));
        }
        return $this->relatedManufacturer->get();
    }

    /**
     * @param mixed $relatedManufacturer
     */
    public function setRelatedManufacturer($relatedManufacturer) {
        if (isset($this->relatedManufacturer)) {
            $this->relatedManufacturer->set($relatedManufacturer);
        } else {
            $this->relatedManufacturer = new Mutable($relatedManufacturer);
        }
    }
}
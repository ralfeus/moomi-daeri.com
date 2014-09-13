<?php
namespace model\catalog;

class Supplier {
    private $id;
    private $group;
    private $groupId;
    private $name;
    private $internalModel;

    public function __construct($groupId, $id, $internalModel, $name) {
        $this->groupId = $groupId;
        $this->id = $id;
        $this->internalModel = $internalModel;
        $this->name = $name;
    }

    public function getGroup() {
        if (!isset($this->group)) {
            $this->group = SupplierGroupDAO::getInstance()->getSupplierGroup($this->groupId);
        }
        return $this->group;
    }
    /**
     * @return mixed
     */
    public function getGroupId()
    {
        return $this->groupId;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getInternalModel()
    {
        return $this->internalModel;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }
}
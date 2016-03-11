<?php
namespace model\catalog;

use system\library\Mutable;

class Manufacturer {
    private $id;
    private $afcId;
    private $description;
    private $imagePath;
    private $name;
    private $sortOrder;
    private $stores;

    function __construct($id, $afcId = null, $description = null, $imagePath = null, $name = null,
                         $sortOrder = null, $stores = null) {
        $this->id = $id;
        if (!is_null($afcId)) { $this->afcId = new Mutable($afcId); }
        if (!is_null($description)) { $this->description = new Mutable($description); }
        if (!is_null($imagePath)) { $this->imagePath = new Mutable($imagePath); }
        if (!is_null($name)) { $this->name = new Mutable($name); }
        if (!is_null($sortOrder)) { $this->sortOrder = new Mutable($sortOrder); }
        if (!is_null($stores)) { $this->stores = new Mutable($stores); }
    }

    /**
     * @return int
     */
    public function getAfcId() {
        if (!isset($this->afcId)) {
            $this->afcId = new Mutable(ManufacturerDAO::getInstance()->getAfcId($this->id));
        }
        return $this->afcId->get();
    }

    /**
     * @param int $afcId
     */
    public function setAfcId($afcId) {
        $this->afcId->set($afcId);
    }

    /**
     * @return string[]
     */
    public function getDescription() {
        if (!isset($this->description)) {
            $this->description = new Mutable(ManufacturerDAO::getInstance()->getDescription($this->id));
        }
        return $this->description->get();
    }

    /**
     * @param Mutable $description
     */
    public function setDescription($description) {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * @return Mutable
     */
    public function getImagePath() {
        if (!isset($this->imagePath)) {
            $this->imagePath = new Mutable(ManufacturerDAO::getInstance()->getImagePath($this->id));
        }
        return $this->imagePath->get();
    }

    /**
     * @param Mutable $imagePath
     */
    public function setImagePath($imagePath) {
        $this->imagePath = $imagePath;
    }

    /**
     * @return string
     */
    public function getName() {
        if (!isset($this->name)) {
            $this->name = new Mutable(ManufacturerDAO::getInstance()->getName($this->id));
        }
        return $this->name->get();
    }

    /**
     * @param Mutable $name
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * @return Mutable
     */
    public function getSortOrder() {
        if (!isset($this->sortOrder)) {
            $this->sortOrder = new Mutable(ManufacturerDAO::getInstance()->getSortOrder($this->id));
        }
        return $this->sortOrder->get();
    }

    /**
     * @param Mutable $sortOrder
     */
    public function setSortOrder($sortOrder) {
        $this->sortOrder = $sortOrder;
    }

    /**
     * @return Mutable
     */
    public function getStores() {
        if (!isset($this->stores)) {
            $this->stores = new Mutable(ManufacturerDAO::getInstance()->getManufacturerStores($this->id));
        }
        return $this->stores->get();
    }

    /**
     * @param Mutable $stores
     */
    public function setStores($stores) {
        $this->stores = $stores;
    }


}
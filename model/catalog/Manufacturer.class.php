<?php
namespace model\catalog;

use system\library\Mutable;

class Manufacturer implements \ArrayAccess{
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


    /**
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset) {
        return in_array($offset, ['id','name', 'image', 'sort_order', 'afc_id', 'keyword']);
    }

    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset) {
        switch ($offset) {
            case 'id':
                return $this->getId();
            case 'name':
                return $this->getName();
            case 'image':
                return $this->getImagePath();
            case 'sort_order':
                return $this->getSortOrder();
            case 'afc_id':
                return $this->getAfcId();
            case 'keyword': 
                return ''; //TODO: implement
            default:
                throw new \InvalidArgumentException("Wrong index '$offset'");
        }
    }

    /**
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value) {
        // TODO: Implement offsetSet() method.
    }

    /**
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset) {
        // TODO: Implement offsetUnset() method.
    }
}
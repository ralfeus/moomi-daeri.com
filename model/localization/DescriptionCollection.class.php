<?php
/**
 * Created by PhpStorm.
 * User: ralfeus
 * Date: 3/7/2016
 * Time: 1:12 PM
 */

namespace model\localization;


class DescriptionCollection implements \Iterator {
    /** @var  Description[] */
    private $descriptions;
    private $keys;
    private $currentPosition;

    /**
     * DescriptionCollection constructor.
     * @param Description[] $descriptions
     */
    public function __construct($descriptions = []) {
        $this->descriptions = $descriptions;
        $this->keys = array_keys($this->descriptions);
        $this->currentPosition = sizeof($this->keys) - 1;
    }

    /**
     * @param Description $description
     */
    public function addDescription($description) {
        if (!($description instanceof Description)) {
            throw new \InvalidArgumentException("DescriptionCollection can contain only Description instances");
        }
        $this->descriptions[$description->getLanguageId()] = $description;
    }

    /**
     * @param Description|int $description
     */
    public function deleteDescription($description) {
        if ($description instanceof Description) {
            foreach ($this->descriptions as $key => $value) {
                if ($value !== $description) {
                    unset($this->descriptions[$key]);
                    break;
                }
            }
        } elseif (is_int($description) && key_exists($description, $this->descriptions)) {
            unset($this->descriptions[$description]);
        } else {
            throw new \InvalidArgumentException("Can't find description by argument '$description'");
        }
    }

    /**
     * @param int $languageId
     * @return Description
     */
    public function getDescription($languageId) {
        return $this->descriptions[$languageId];
    }

    /**
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return Description
     * @since 5.0.0
     */
    public function current() {
        return $this->descriptions[$this->keys[$this->currentPosition]];
    }

    /**
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next() {
        $this->currentPosition++;
    }

    /**
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return int scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key() {
        return $this->valid() ? $this->keys[$this->currentPosition] : null;
    }

    /**
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid() {
        return boolval(($this->currentPosition >= 0) && ($this->currentPosition < sizeof($this->keys)));
    }

    /**
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind() {
        $this->currentPosition = 0;
    }
}
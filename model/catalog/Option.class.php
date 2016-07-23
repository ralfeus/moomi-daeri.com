<?php
namespace model\catalog;

use model\localization\Description;
use model\localization\DescriptionCollection;

class Option {
    private $id;
    private $defaultLanguageId;
    /** @var DescriptionCollection */
    private $descriptions;
    private $sortOrder;
    private $type;
    /** @var  OptionValueCollection */
    private $values;

    private $singleValueTypes = ['text', 'textarea', 'file', 'date', 'datetime', 'time'];
    private $multiValueTypes = ['select', 'radio', 'checkbox', 'image'];

    /**
     * ProductOption constructor.
     * @param int $id
     * @param Description[] $descriptions
     * @param int $sortOrder
     * @param string $type
     * @param int $defaultLanguageId
     */
    public function __construct($id, $descriptions = null, $sortOrder = null, $type = null, $defaultLanguageId = null) {
        $this->id = $id;
        $this->defaultLanguageId = $defaultLanguageId;
        if (is_array($descriptions)) {
            foreach ($descriptions as $description) {
                $this->descriptions->addDescription($description);
            }
        }
        $this->sortOrder = $sortOrder;
        $this->type = $type;
    }

    public function __destruct() {
        $this->descriptions = null;
        $this->values = null;
    }

    /**
     * @param int $languageId
     * @param string $name
     */
    public function addDescription($languageId, $name) {
        $this->descriptions->addDescription(new Description($languageId, $name));
    }

    /**
     * @param int $languageId
     * @return string
     */
    public function getName($languageId = null) {
        if (is_null($languageId)) {
            $languageId = $this->defaultLanguageId;
        }
        return $this->getDescriptions()->getDescription($languageId)->getName();
    }

    /**
     * @return int
     */
    public function getDefaultLanguageId() {
        return $this->defaultLanguageId;
    }

    /**
     * @return DescriptionCollection
     */
    public function getDescriptions() {
        if (!isset($this->descriptions)) {
            $this->descriptions = OptionDAO::getInstance()->getOptionDescriptions($this->id);
        }
        return $this->descriptions;
    }

    public function getId() {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getSortOrder() {
        return $this->sortOrder;
    }

    /**
     * @return string
     */
    public function getType() {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function isMultiValueType() {
        return in_array($this->type, $this->multiValueTypes);
    }

    /**
     * @return bool
     */
    public function isSingleValueType() {
        return in_array($this->type, $this->singleValueTypes);
    }

    public function addValue($value) {
        $this->values[] = $value;
    }

    /**
     * @return OptionValueCollection
     */
    public function getValues() {
        if (!isset($this->values)) {
            $this->values = OptionDAO::getInstance()->getOptionValues($this->id);
        }
        return $this->values;
    }
}
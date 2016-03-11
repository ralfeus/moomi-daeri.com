<?php
/**
 * Created by PhpStorm.
 * User: ralfeus
 * Date: 3/7/2016
 * Time: 1:09 PM
 */

namespace model\localization;

class Description {
    private $languageId;
    private $name;
    private $description;
    private $metaDescription;
    private $metaKeyword;
    private $seoTitle;
    private $seoH1;

    /**
     * Description constructor.
     * @param int $languageId
     * @param string $name
     * @param string $description
     * @param string $metaDescription
     * @param string $metaKeyword
     * @param string $seoTitle
     * @param string $seoH1
     */
    public function __construct($languageId, $name, $description = null, $metaDescription = null, $metaKeyword = null, $seoTitle = null, $seoH1 = null) {
        if (empty($languageId)) {
            throw new \InvalidArgumentException("Language ID must be ID of existing language");
        }
        $this->languageId = $languageId;
        $this->name = $name;
        $this->description = $description;
        $this->metaDescription = $metaDescription;
        $this->metaKeyword = $metaKeyword;
        $this->seoTitle = $seoTitle;
        $this->seoH1 = $seoH1;
    }

    /**
     * @return int
     */
    public function getLanguageId() {
        return $this->languageId;
    }
    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getMetaDescription()
    {
        return $this->metaDescription;
    }

    /**
     * @return string
     */
    public function getMetaKeyword()
    {
        return $this->metaKeyword;
    }

    /**
     * @return string
     */
    public function getSeoTitle()
    {
        return $this->seoTitle;
    }

    /**
     * @return string
     */
    public function getSeoH1()
    {
        return $this->seoH1;
    }
}
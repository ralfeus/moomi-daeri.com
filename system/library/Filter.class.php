<?php
namespace system\library;

class Filter {
    private $filterString;
    private $params;

    /**
     * @param string $filterString
     * @param array $params
     */
    public function __construct($filterString = null, $params = null) {
        $this->filterString = "";
        $this->params = array();
        if (!is_null($filterString)) {
            $this->addChunk($filterString, $params);
        }
    }

    /**
     * @param string|Filter $filter
     * @param null $params
     */
    public function addChunk($filter, $params = null) {
        if ($filter instanceof Filter) {
            $this->addChunk($filter->getFilterString(), $filter->getParams());
        } elseif ($filter) {
            if ($this->filterString) {
                $this->filterString .= ' AND ';
            }
            $this->filterString .= $filter;
            if (!is_null($params) && is_array($params)) {
                $this->params = array_merge($this->params, $params);
            }
        }
    }

    /**
     * @param bool $complete
     * @return string
     */
    public function getFilterString($complete = false) {
        if ($complete) {
            if ($this->isFilterSet()) {
                return "\r\nWHERE " . $this->filterString;
            } else {
                return '';
            }
        } else {
            return $this->filterString;
        }
    }

    /**
     * @return array
     */
    public function getParams() {
        return $this->params;
    }

    public function isFilterSet() {
        return boolval(strlen($this->filterString) > 0);
    }
} 
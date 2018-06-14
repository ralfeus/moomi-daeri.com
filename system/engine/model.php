<?php
namespace system\engine;
use system\library\Filter;

abstract class Model extends OpenCartBase {
    public function __construct($registry) {
        parent::__construct($registry);
    }

    /**
     * @param int $start
     * @param int $limit
     * @return string
     */
    protected function buildLimitString($start = null, $limit = null) {
        if (isset($start) && isset($limit)) {
            return "\r\nLIMIT $start, $limit";
        } else {
            return '';
        }
    }

    /**
     * @param string $fieldName
     * @param mixed $filterValues
     * @param string $filterString
     * @param array $params
     * @param string $entryType Not used. Kept for back compatibility. Will be removed
     * @return Filter
     */
    protected function buildSimpleFieldFilterEntry($fieldName, $filterValues, &$filterString, &$params, $entryType = null) {
        if (isset($filterValues)) {
//            $this->getLogger()->write(print_r($filterValues, true));
            $paramName = ':' . preg_replace('/\W+/', '', $fieldName);
            if (is_array($filterValues)) {
                if (sizeof($filterValues)) {
                    $tmp = 0; $tmpFilterString = ''; $tmpParams = array();
                    foreach ($filterValues as $filterValue) {
                        $tmpFilterString .= ", $paramName$tmp";
                        $tmpParams["$paramName$tmp"] = $filterValue; // TODO: Remove as all is moved to Filter usage
                        $params["$paramName$tmp"] = $filterValue;
                        $tmp++;
                    }
                    $filterString .= ($filterString ? " AND " : "");
                    $tmpFinalFilterString = "$fieldName IN (" . substr($tmpFilterString, 2) . ')';
                    $filterString .= $tmpFinalFilterString;
                    return new Filter($tmpFinalFilterString, $tmpParams);
                } else {
                    return null;
                }
            } else {
                $filterString .= ($filterString ? " AND " : "") . "$fieldName = $paramName";
                $params[$paramName] = $filterValues;
                return new Filter("$fieldName = $paramName", [$paramName => $filterValues]);
            }
        }
        throw new \InvalidArgumentException("No filter values are provided");
    }
}
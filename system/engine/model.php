<?php
namespace system\engine;
use system\library\Filter;

abstract class Model extends OpenCartBase {
    public function __construct($registry) {
        parent::__construct($registry);
    }

    /**
     * @param array|string $param1 Either array containing 'start' and 'limit' keys or a string containing 'start' value
     * @param string $limit Limit of the output
     * @return string Filter string
     */
    protected function buildLimitString($param1 = null, $limit = null) {
        if (is_array($param1)) {
            $start = key_exists('start', $param1) ? $param1['start'] : null;
            $limit = key_exists('limit', $param1) ? $param1['limit'] : null;
        } else {
            $start = $param1;
        }
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
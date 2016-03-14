<?php
use system\library\Filter;

require_once('OpenCartBase.php');
abstract class Model extends OpenCartBase {
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
                }
            } else {
                $filterString .= ($filterString ? " AND " : "") . "$fieldName = $paramName";
                $params[$paramName] = $filterValues;
                return new Filter("$fieldName = $paramName", [$paramName => $filterValues]);
            }
        }
    }
}
<?php
require_once('OpenCartBase.php');
abstract class Model extends OpenCartBase {
    /**
     * @param int $start
     * @param int $limit
     * @return string
     */
    protected function buildLimitString($start = null, $limit = null) {
        if (isset($start) && isset($limit)) {
            return "LIMIT $start, $limit";
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
     */
    protected function buildSimpleFieldFilterEntry($fieldName, $filterValues, &$filterString, &$params, $entryType = null) {
        if (isset($filterValues) || is_null($filterValues)) {
            $paramName = ':' . preg_replace('/\W+/', '', $fieldName);
            if (is_array($filterValues)) {
                if (sizeof($filterValues)) {
                    $tmp = 0;
                    $filterString .= $filterString ? " AND " : "";
                    foreach ($filterValues as $filterValue) {
                        $filterString .= " OR $fieldName = $paramName$tmp";
                        $params["$paramName$tmp"] = $filterValue;
                        $tmp++;
                    }
                    $filterString = sizeof($filterValues) > 1
                        ? '(' . substr($filterString, 4) . ')'
                        : substr($filterString, 4);
                }
            } elseif (is_null($filterValues)) {
                $filterString .= ($filterString ? " AND " : "") . "$fieldName IS NULL" ;
            } else {
                $filterString .= ($filterString ? "AND " : "") . "$fieldName = $paramName";
                $params[$paramName] = $filterValues;
            }
        }
    }
}
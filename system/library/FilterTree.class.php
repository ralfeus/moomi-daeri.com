<?php
/**
 * Created by PhpStorm.
 * User: ralfeus
 * Date: 10.08.2016
 * Time: 08:14
 */

namespace system\library;


class FilterTree {
    /** @var  bool */
    private $atomic;
    /** @var  array */
    private $condition;
    /** @var  FilterTree */
    private $filter;
    /** @var  string */
    private $relation;

    /**
     * FilterTree constructor.
     * @param array $condition
     * @param bool $atomic
     * @param string $relation
     * @param FilterTree $filter
     */
    public function __construct($condition, $relation = null, $filter = null, $atomic = false) {
        if (!is_null($filter) && !($filter instanceof FilterTree)) {
            throw new \InvalidArgumentException("Wrong next filter");
        }
        if (!is_null($filter) && (is_null($relation) || !in_array($relation, ['OR', 'AND']))) {
            throw new \InvalidArgumentException("No relation between this condition and next one");
        }
        $this->atomic = $atomic;
        $this->condition = $condition;
        $this->filter = $filter;
        $this->relation = $relation;

    }

    /**
     * @param callable $callback
     * @return Filter
     */
    public function buildFilter($callback) {
        /** @var Filter $filter */
        $filter = $callback($this->condition);
        if (!is_null($this->filter)) {
            $tmpFilter = $this->filter->buildFilter($callback);
            $filter->addChunk($tmpFilter->getFilterString(false, $this->atomic), $tmpFilter->getParams(), $this->relation);
        }
        return $filter;
    }
}
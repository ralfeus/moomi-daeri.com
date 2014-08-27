<?php
require_once('controller.php');
abstract class TableController extends Controller {
    private static function compareSortCriterias($sortCriteria1, $sortCriteria2) {
        return $sortCriteria1->order - $sortCriteria2->order;
    }

    public function __construct($registry) {
        parent::__construct($registry);
        if (array_key_exists('sort', $_REQUEST) && is_array($_REQUEST['sort'])) {
            usort($_REQUEST['sort'], 'compareSortCriterias');
            foreach ($_REQUEST['sort'] as $sortCriteria) {
                $this->parameters['sort'][] = (object)$sortCriteria;
            }
        }
    }
} 
<?php
namespace model\catalog;

use model\DAO;

class ImportProductDAO extends DAO {
    /**
     * @param int $importProductId
     * @return string[]
     */
    public function getSourceCategories($importProductId) {
        $result = $this->getDb()->query(<<<SQL
            SELECT source_category_id
            FROM imported_product_source_categories
            WHERE imported_product_id = ?
SQL
            , array("i:$importProductId")
        );
        $categories = array();
        foreach ($result->rows as $categoryEntry) {
            $categories[] = $categoryEntry['source_category_id'];
        }
        return $categories;
    }
} 
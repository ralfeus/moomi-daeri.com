<?php
/**
 * Created by PhpStorm.
 * User: ralfeus
 * Date: 5/10/2016
 * Time: 8:12 PM
 */

namespace model\design;

use model\DAO;

class LayoutDAO extends DAO {
    public function getLayout($route) {
        $layoutId = $this->getCache()->get('layout.' . $route);
        if (is_null($layoutId)) {
            $query = $this->getDb()->query("
                SELECT * FROM layout_route 
                WHERE 
                    :route LIKE CONCAT(route, '%') 
                    AND store_id = :storeId 
                ORDER BY route ASC 
                LIMIT 1
            ", [
                ':route' => $route,
                ':storeId' => $this->config->get('config_store_id')
            ]);

            if ($query->num_rows) {
                $layoutId = $query->row['layout_id'];
            } else {
                $layoutId = 0;
            }
            $this->getCache()->set('layout.' . $route, $layoutId);
        }
        return $layoutId;
    }
}
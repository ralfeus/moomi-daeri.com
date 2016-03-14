<?php
class ModelModuleMpchanges extends Model {
    public function getProductsByCategoryId($category_id) {
        $query = $this->db->query("SELECT * FROM product p LEFT JOIN product_description pd ON (p.product_id = pd.product_id) LEFT JOIN product_to_category p2c ON (p.product_id = p2c.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p2c.category_id = '" . (int)$category_id . "' ORDER BY pd.name ASC");
        return $query->rows;
    }

    public function getProductsByManufacturerId($manufacturer_id) {
        $query = $this->db->query("SELECT * FROM product p LEFT JOIN product_description pd ON (p.product_id = pd.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.manufacturer_id = '" . (int)$manufacturer_id . "' ORDER BY pd.name ASC");
        return $query->rows;
    }

    public function editProductPrice($product_id, $price) {
        $this->db->query("UPDATE product SET price = '" . (float)$price . "' WHERE product_id = " . (int)$product_id);
    }

    public function editProductStorePrice($product_id, $store_id, $price) {
        $this->db->query("UPDATE product_to_store SET store_price = '" . (float)$price . "' WHERE store_id = " . $store_id . " AND product_id = " . (int)$product_id);
    }

    public function editProductQuantity($product_id, $quantity) {
        $this->db->query("UPDATE product SET quantity = '" . (int)$quantity . "' WHERE product_id = " . (int)$product_id);
    }

    public function editProductStoreQuantity($product_id, $store_id, $quantity) {
        $this->db->query("UPDATE product_to_store SET store_quantity = " . (int)$quantity . " WHERE store_id = " . $store_id . " AND product_id = " . (int)$product_id);
    }

    public function getProductCurrentSpecials($product_id) {
        $query = $this->db->query("SELECT * FROM product_special WHERE date_start <= curdate() and curdate() <= date_end and product_id = '" . (int)$product_id . "' ORDER BY priority, price");

        return $query->rows;
    }

    public function getProductCurrentDiscounts($product_id) {
        $query = $this->db->query("SELECT * FROM product_discount WHERE date_start <= curdate() and curdate() <= date_end and product_id = '" . (int)$product_id . "' ORDER BY quantity, priority, price");

        return $query->rows;
    }

    public function updateProductSpecial($product_special){
        $sql = "UPDATE product_special SET customer_group_id = '" . (int)$product_special['customer_group_id'] . "', priority = '" . (int)$product_special['priority'] . "', price = '" . (float)$product_special['price'] . "', date_start = '" . $this->db->escape($product_special['date_start']) . "', date_end = '" . $this->db->escape($product_special['date_end']) . "' WHERE product_special_id = " . (int)$product_special['product_special_id'];
        $this->db->query($sql);
    }

    public function updateProductDiscount($product_discount){
        $sql = "UPDATE product_discount SET customer_group_id = '" . (int)$product_discount['customer_group_id'] . "', quantity = '" . (int)$product_discount['quantity'] . "', priority = '" . (int)$product_discount['priority'] . "', price = '" . (float)$product_discount['price'] . "', date_start = '" . $this->db->escape($product_discount['date_start']) . "', date_end = '" . $this->db->escape($product_discount['date_end']) . "' WHERE product_discount_id = " . (int)$product_discount['product_discount_id'];
        $this->db->query($sql);
    }
    
    public function cleanCache($cache) {
        $this->cache->delete($cache);
    }

    public function addProductSpecial($product_special){
        $this->db->query("INSERT INTO product_special SET product_id = '" . (int)$product_special['product_id'] . "', customer_group_id = '" . (int)$product_special['customer_group_id'] . "', priority = '" . (int)$product_special['priority'] . "', price = '" . (float)$product_special['price'] . "', date_start = '" . $this->db->escape($product_special['date_start']) . "', date_end = '" . $this->db->escape($product_special['date_end']) . "'");
    }
    public function addProductDiscount($product_discount){
        $this->db->query("INSERT INTO product_discount SET product_id = '" . (int)$product_discount['product_id'] . "', customer_group_id = '" . (int)$product_discount['customer_group_id'] . "', quantity = '" . (int)$product_discount['quantity'] . "' , priority = '" . (int)$product_discount['priority'] . "', price = '" . (float)$product_discount['price'] . "', date_start = '" . $this->db->escape($product_discount['date_start']) . "', date_end = '" . $this->db->escape($product_discount['date_end']) . "'");
    }
    public function getProductMainCategoryId($product_id) {
        $query = $this->db->query("SELECT category_id FROM product_to_category WHERE product_id = '" . (int)$product_id . "' AND main_category = '1' LIMIT 1");

        return ($query->num_rows ? (int)$query->row['category_id'] : 0);
    }

    public function removeSpecials($product_id) {
        $this->db->query("DELETE FROM product_special WHERE product_id = " . (int)$product_id);
    }
    public function removeDiscounts($product_id) {
        $this->db->query("DELETE FROM product_discount WHERE product_id = " . (int)$product_id);
    }
    public function deleteProduct($product_id) {
        $this->db->query("DELETE FROM product WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM product_attribute WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM product_description WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM product_discount WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM product_image WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM product_option WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM product_option_value WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM product_related WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM product_related WHERE related_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM product_reward WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM product_special WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM product_tag WHERE product_id='" . (int)$product_id. "'");
        $this->db->query("DELETE FROM product_to_category WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM product_to_download WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM product_to_layout WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM product_to_store WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM review WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM url_alias WHERE query = 'product_id=" . (int)$product_id. "'");
    }

    public function getStores($data = array()) {
        $store_data = $this->getCache()->get('store-mpc');
        if (!$store_data) {
            $query = $this->getDb()->query("SELECT * FROM store ORDER BY url");
            foreach ($query->rows as $row) {$store_data[$row['store_id']] = $row;}
            $this->getCache()->set('store-mpc', $store_data);
        }
        return $store_data;
    }

    public function setEnabled($product_id, $status){
        $sql = "UPDATE product SET status = " . (int)$status . " WHERE product_id = " . (int)$product_id;
        $this->db->query($sql);
    }

    public function setProductToStore($product_id, $store_id, $stores){
        $query = $this->db->query("SELECT * FROM product_to_store WHERE product_id = " . (int)$product_id . " AND store_id != " . $store_id);
        $currentStores = array();
        if ($query->num_rows){
            foreach($query->rows as $store){
                $currentStores[] = $store['store_id'];
            }
        }
        $add = array();
        foreach ($stores as $store){
            if (!in_array($store, $currentStores)){
                $add[] = "(" . $product_id . ", ". $store . ")";
                unset($currentStores[$store]);
            }

        }

        $sql = "DELETE FROM product_to_store WHERE product_id = " . (int)$product_id . " AND store_id in( " . implode(',', $currentStores) . ")";
        $this->db->query($sql);

        if (!empty($add)){
            $this->db->query("INSERT INTO product_to_store (product_id, store_id) VALUES ".implode(",", $add));
        }
    }

    public function getProducts($data = array()) {
        $sql = "
            SELECT pd.name, p.product_id, p.price, p.quantity, !isnull(pd2.price) as discount, !isnull(ps.price) as special
            FROM
                product AS p
                LEFT JOIN product_description AS pd ON p.product_id = pd.product_id
                LEFT JOIN product_to_category AS p2c ON p.product_id = p2c.product_id
                RIGHT JOIN product_to_store AS p2s ON p.product_id = p2s.product_id
                LEFT JOIN product_discount AS pd2 ON p.product_id = pd2.product_id
                LEFT JOIN product_special AS ps ON p.product_id = ps.product_id
                WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'
        ";
        if (isset($data['filter_price_from'])) {
            $sql .= " AND p.price >= " . $data['filter_price_from'] . " ";}

        if (isset($data['filter_price_to'])) {
            $sql .= " AND p.price <= " . $data['filter_price_to'] . " ";}

        if (!empty($data['filter_manufacturer_id'])) {
            $sql .= " AND manufacturer_id = " . (int)$data['filter_manufacturer_id'] . " ";}

        if (!empty($data['filter_supplier_id'])) {
            $sql .= " AND supplier_id = " . (int)$data['filter_supplier_id'] . " ";}

        if (!empty($data['filter_name'])) {
            $sql .= " AND LCASE(pd.name) LIKE '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "%'";}

        if (!empty($data['filterModel'])) {
            $sql .= " AND LCASE(p.model) LIKE '" . $this->db->escape(utf8_strtolower($data['filterModel'])) . "%'";}

        if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
            $sql .= " AND p.status = '" . (int)$data['filter_status'] . "'";}

        if (!empty($data['filter_category_id'])) {
            if ($data['filter_sub_category']) {
                $implode_data = array();

                $implode_data[] = "category_id = '" . (int)$data['filter_category_id'] . "'";

                $this->load->model('catalog/category');

                $categories = $this->getCategories($data['filter_category_id']);

                foreach ($categories as $category) {
                    $implode_data[] = "p2c.category_id = '" . (int)$category['category_id'] . "'";
                }

                $sql .= " AND (" . implode(' OR ', $implode_data) . ")";
            } else {
                $sql .= " AND p2c.category_id = '" . (int)$data['filter_category_id'] . "'";
            }
        }

        if (!empty($data['filter_store_id'])) {
            $sql .= " AND p2s.store_id = " . (int)$data['filter_store_id'];
        }

        $sql .= " GROUP BY p.product_id";

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        $query = $this->db->query($sql);

        $product_data = array();

        $stores = $this->getStores();
        $stores[0] = array('store_id' => 0, 'name' => 'def');

        if (!empty($data['filter_store_id'])) {
            $stores = array($stores[$data['filter_store_id']]);
        }

        foreach($query->rows as $row){
            $product_data[$row['product_id']] = $row;
        }

        return $product_data;
    }

    public function getTotalProducts($data = array()) {
        $count = $this->getCache()->get(md5(serialize($data)));
        if (!is_null($count)) {
            return $count;
        }
        $sql = "SELECT COUNT(DISTINCT p.product_id) AS total FROM product p LEFT JOIN product_description pd ON (p.product_id = pd.product_id)";

        if (!empty($data['filter_category_id'])) {
            $sql .= " LEFT JOIN product_to_category p2c ON (p.product_id = p2c.product_id)";}
        if (!empty($data['filter_store_id'])) {
            $sql .= " RIGHT JOIN product_to_store p2s ON (p.product_id = p2s.product_id)";}

        $sql .= " WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

        if (isset($data['filter_price_from'])) {
            $sql .= " AND p.price >= " . $data['filter_price_from'] . " ";}

        if (isset($data['filter_price_to'])) {
            $sql .= " AND p.price <= " . $data['filter_price_to'] . " ";}

        if (!empty($data['filter_manufacturer_id'])) {
            $sql .= " AND manufacturer_id = " . (int)$data['filter_manufacturer_id'] . " ";}

        if (!empty($data['filter_name'])) {
            $sql .= " AND pd.name LIKE '" . $data['filter_name'] . "%'";}

        if (!empty($data['filterModel'])) {
            $sql .= " AND LCASE(p.model) LIKE '" . $this->db->escape(utf8_strtolower($data['filterModel'])) . "%'";}

        if (!empty($data['filter_price'])) {
            $sql .= " AND p.price LIKE '" . $this->db->escape($data['filter_price']) . "%'";}

        if (isset($data['filter_quantity']) && !is_null($data['filter_quantity'])) {
            $sql .= " AND p.quantity = '" . $this->db->escape($data['filter_quantity']) . "'";}

        if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
            $sql .= " AND p.status = '" . (int)$data['filter_status'] . "'";}

        if (!empty($data['filter_category_id'])) {
            if ($data['filter_sub_category']) {
                $implode_data = array();

                $implode_data[] = "category_id = '" . (int)$data['filter_category_id'] . "'";

                $this->load->model('catalog/category');

                $categories = $this->getCategories($data['filter_category_id']);

                foreach ($categories as $category) {
                    $implode_data[] = "p2c.category_id = '" . (int)$category['category_id'] . "'";
                }

                $sql .= " AND (" . implode(' OR ', $implode_data) . ")";
            } else {
                $sql .= " AND p2c.category_id = '" . (int)$data['filter_category_id'] . "'";
            }
        }

        if (!empty($data['filter_store_id'])) {
            $sql .= " AND p2s.store_id = " . (int)$data['filter_store_id'];}

        $query = $this->getDb()->query($sql);
        $this->getCache()->set(md5(serialize($data)), $query->row['total']);
        return $query->row['total'];
    }

    public function getStorePrices($product_id, $stores) {
        $prices = array();
        foreach ($stores as $store) {
            $query = $this->db->query("select * from product_to_store where product_id=".$product_id." and store_id = ".$store['store_id']);
            if (isset($query->row['store_price'])){
                $prices[$store['store_id']] = $query->row['store_price'];
            }
            else{
                $prices[$store['store_id']] = 0;
            }
        }
        return $prices;
    }

    public function getStoreQuantities($product_id, $stores) {
        $quantities = array();
        foreach ($stores as $store) {
            $query = $this->db->query("select * from product_to_store where product_id=".$product_id." and store_id = ".$store['store_id']);
            if (isset($query->row['store_quantity'])){
                $quantities[$store['store_id']] = $query->row['store_quantity'];
            }
            else{
                $quantities[$store['store_id']] = 0;
            }
        }
        return $quantities;
    }

    public function getCategories($parent_id = 0) {
        $category_data = $this->cache->get('category.' . (int)$this->config->get('config_language_id') . '.' . (int)$parent_id);

        if (!$category_data) {
            $category_data = array();

            $query = $this->db->query("SELECT * FROM category c LEFT JOIN category_description cd ON (c.category_id = cd.category_id) WHERE c.parent_id = '" . (int)$parent_id . "' AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY c.sort_order, cd.name ASC");

            foreach ($query->rows as $result) {
                $category_data[] = array(
                    'category_id' => $result['category_id'],
                    'name'        => $this->getPath($result['category_id'], $this->config->get('config_language_id')),
                    'status'  	  => $result['status'],
                    'sort_order'  => $result['sort_order']
                );

                $category_data = array_merge($category_data, $this->getCategories($result['category_id']));
            }

            $this->cache->set('category.' . (int)$this->config->get('config_language_id') . '.' . (int)$parent_id, $category_data);
        }

        return $category_data;
    }
}
?>
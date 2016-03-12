<?php
namespace model\catalog;

use model\DAO;
use model\localization\Description;
use model\localization\DescriptionCollection;
use system\exception\NotImplementedException;
use system\library\Dimensions;
use system\library\MeasureUnit;
use system\library\Weight;

class ProductDAO extends DAO {
    private function getSingleValue($productId, $columnName) {
        return $this->getDb()->queryScalar("SELECT $columnName FROM product WHERE product_id = ?", array("i:$productId"));
    }

    public function updateViewed($productId) {
        $this->getDb()->query("
            UPDATE product
            SET viewed = (viewed + 1)
            WHERE product_id = ?"
        , array("i:$productId"))
        ;
    }

    /**
     * @param int $productId
     * @param bool $shallow Defines whether all product data should be fetched immediately or just stub object created
     * @param bool $object Defines whether object or array should be returned.
     * @return Product|array
     */
    public function getProduct($productId, $shallow = false, $object = false) { //TODO: Перевести на об'єкт
        if ($shallow) {
            return new Product($productId);
        }
        $customerGroupId = $this->getCurrentCustomer()->isLogged()
            ? $this->getCurrentCustomer()->getCustomerGroupId()
            : $this->config->get('config_customer_group_id');

        $query = $this->getDb()->query(<<<SQL
		    SELECT DISTINCT
		        *, pd.name AS name, p.image, m.name AS manufacturer,
		        (
		            SELECT price
		            FROM product_discount pd2
		            WHERE
		                pd2.product_id = p.product_id
		                AND pd2.customer_group_id = ?
		                AND pd2.quantity = 1
		                AND (
		                    (pd2.date_start = '0000-00-00' OR pd2.date_start < ?)
		                    AND (pd2.date_end = '0000-00-00' OR pd2.date_end > ?)
                        )
                    ORDER BY pd2.priority ASC, pd2.price ASC
                    LIMIT 1
                ) AS discount,
                (
                    SELECT price
                    FROM product_special ps
                    WHERE
                        ps.product_id = p.product_id
                        AND ps.customer_group_id = ?
                        AND ((ps.date_start = '0000-00-00' OR ps.date_start < ?)
                        AND (ps.date_end = '0000-00-00' OR ps.date_end > ?))
                    ORDER BY ps.priority ASC, ps.price ASC
                    LIMIT 1
                ) AS special,
                (
                    SELECT points
                    FROM product_reward pr
                    WHERE pr.product_id = p.product_id AND customer_group_id = ?
                ) AS reward,
                (
                    SELECT ss.name
                    FROM stock_status ss
                    WHERE ss.stock_status_id = p.stock_status_id AND ss.language_id = ?
                ) AS stock_status,
                (
                    SELECT wcd.unit
                    FROM weight_class_description wcd
                    WHERE p.weight_class_id = wcd.weight_class_id AND wcd.language_id = ?
                ) AS weight_class,
                (
                    SELECT lcd.unit
                    FROM length_class_description lcd
                    WHERE p.length_class_id = lcd.length_class_id AND lcd.language_id = ?
                ) AS length_class,
                (
                    SELECT AVG(rating) AS total
                    FROM review r1
                    WHERE r1.product_id = p.product_id AND r1.status = '1'
                    GROUP BY r1.product_id
                ) AS rating,
                (
                    SELECT COUNT(*) AS total
                    FROM review r2
                    WHERE r2.product_id = p.product_id AND r2.status = '1'
                    GROUP BY r2.product_id
                ) AS reviews,
                p.sort_order
            FROM
                product p
                LEFT JOIN product_description pd ON (p.product_id = pd.product_id)
                LEFT JOIN product_to_store p2s ON (p.product_id = p2s.product_id)
                LEFT JOIN manufacturer m ON (p.manufacturer_id = m.manufacturer_id)
                LEFT JOIN url_alias AS ua ON ua.query =  'product_id=' + p.product_id

            WHERE
                p.product_id = ?
                AND pd.language_id = ?
                AND p.status = '1'
                AND p.date_available <= ?
                AND p2s.store_id = ?
SQL
            , array(
                "i:$customerGroupId",
                's:' . date('Y-m-d H:00:00'),
                's:' . date('Y-m-d H:00:00', strtotime('+1 hour')),
                "i:$customerGroupId",
                's:' . date('Y-m-d H:00:00'),
                's:' . date('Y-m-d H:00:00', strtotime('+1 hour')),
                "i:$customerGroupId",
                'i:' . $this->config->get('config_language_id'),
                'i:' . $this->config->get('config_language_id'),
                'i:' . $this->config->get('config_language_id'),
                "i:$productId",
                's:' . $this->config->get('config_language_id'),
                's:' . date('Y-m-d H:00:00'),
                'i:' . $this->config->get('config_store_id')
            )
        );

        if ($query->num_rows) {
            $query->row['price'] = ($query->row['discount'] ? $query->row['discount'] : $query->row['price']);
            $query->row['rating'] = (int)$query->row['rating'];

            if ($object) {
                return
                    new Product(
                        $query->row['product_id'], $query->row['afc_id'], $query->row['affiliate_commission'],
                        $query->row['date_added'], $query->row['date_available'], $query->row['date_modified'], array(),
                        new Dimensions($query->row['length_class_id'], $query->row['height'], $query->row['length'], $query->row['width']),
                        $query->row['image'], $query->row['keyword'], $query->row['korean_name'], $query->row['location'],
                        $query->row['manufacturer_id'], $query->row['minimum'], $query->row['model'], null, $query->row['points'],
                        $query->row['price'], $query->row['quantity'], $query->row['shipping'], $query->row['sku'],
                        $query->row['sort_order'], $query->row['status'], $query->row['stock_status_id'], null, $query->row['subtract'],
                        SupplierDAO::getInstance()->getSupplier($query->row['supplier_id'], true),
                        $query->row['supplier_url'], null, $query->row['upc'], $query->row['user_id'], $query->row['viewed'],
                        new Weight($query->row['weight_class_id'], $query->row['weight']), null, null, null, null, null,
                        null, null, null, $query->row['image_description']
                    );
            } else {
                return $query->row;
            }
        } else {
            return false;
        }
    }

    public function getProducts($data = array()) {
        if ($this->getCurrentCustomer()->isLogged()) {
            $customer_group_id = $this->getCurrentCustomer()->getCustomerGroupId();
        } else {
            $customer_group_id = $this->config->get('config_customer_group_id');
        }

        $cache = md5(http_build_query($data));

        if (isset($data['nocache']))
            $product_data = 0;
        else
            $product_data = $this->getCache()->get('product.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . (int)$customer_group_id . '.' . $cache);

        if (!$product_data) {
            $sql = "
			    SELECT
			        p.product_id,
			        (
			            SELECT AVG(rating) AS total
			            FROM review r1
			            WHERE r1.product_id = p.product_id AND r1.status = '1'
			            GROUP BY r1.product_id
                    ) AS rating
                FROM
                    product p
                    LEFT JOIN product_description pd ON (p.product_id = pd.product_id)
                    LEFT JOIN product_to_store p2s ON (p.product_id = p2s.product_id)
            ";

            if (!empty($data['filter_tag'])) {
                $sql .= " LEFT JOIN product_tag pt ON (p.product_id = pt.product_id)";
            }

            if (!empty($data['filter_category_id'])) {
                $sql .= " LEFT JOIN product_to_category p2c ON (p.product_id = p2c.product_id)";
            }

            $sql .= "
			    WHERE
			        pd.language_id = '" . (int)$this->config->get('config_language_id') . "'
			        AND p.status = '1'
			        AND p.date_available <= '" . date('Y-m-d H:00:00') . "'
			        AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'
            ";

            if (!empty($data['filter_name']) || !empty($data['filter_tag'])) {
                $sql .= " AND (";

                if (!empty($data['filter_name'])) {
                    $implode = array();

                    $words = explode(' ', $data['filter_name']);

                    foreach ($words as $word) {
                        if (!empty($data['filter_description'])) {
                            $implode[] = "LCASE(pd.name) LIKE '%" . $this->getDb()->escape(utf8_strtolower($word)) . "%' OR LCASE(pd.description) LIKE '%" . $this->getDb()->escape(utf8_strtolower($word)) . "%'";
                        } else {
                            $implode[] = "LCASE(pd.name) LIKE '%" . $this->getDb()->escape(utf8_strtolower($word)) . "%'";
                        }
                    }

                    if ($implode) {
                        $sql .= " " . implode(" OR ", $implode) . "";
                    }
                }

                if (!empty($data['filter_name']) && !empty($data['filter_tag'])) {
                    $sql .= " OR ";
                }

                if (!empty($data['filter_tag'])) {
                    $implode = array();

                    $words = explode(' ', $data['filter_tag']);

                    foreach ($words as $word) {
                        $implode[] = "LCASE(pt.tag) LIKE '%" . $this->getDb()->escape(utf8_strtolower($data['filter_tag'])) . "%' AND pt.language_id = '" . (int)$this->config->get('config_language_id') . "'";
                    }

                    if ($implode) {
                        $sql .= " " . implode(" OR ", $implode) . "";
                    }
                }

                $sql .= ")";
            }

            if (!empty($data['filter_category_id'])) {
                if (!empty($data['filter_sub_category'])) {
                    $implode_data = array();

                    $implode_data[] = "p2c.category_id = '" . (int)$data['filter_category_id'] . "'";

                    $this->load->model('catalog/category');

                    $categories = $this->model_catalog_category->getCategoriesByParentId($data['filter_category_id']);

                    foreach ($categories as $category_id) {
                        $implode_data[] = "p2c.category_id = '" . (int)$category_id . "'";
                    }

                    $sql .= " AND (" . implode(' OR ', $implode_data) . ")";
                } else {
                    $sql .= " AND p2c.category_id IN (" . $data['filter_category_id'] . ")";
                }
            }

            if (!empty($data['filter_manufacturer_id'])) {
                $sql .= " AND p.manufacturer_id = '" . (int)$data['filter_manufacturer_id'] . "'";
            }

            $sql .= " GROUP BY p.product_id";

            $sort_data = array(
                'pd.name',
                'p.model',
                'p.quantity',
                'p.price',
                'rating',
                'p.sort_order',
                'p.date_added'
            );

            if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
                if ($data['sort'] == 'pd.name' || $data['sort'] == 'p.model') {
                    $sql .= " ORDER BY LCASE(" . $data['sort'] . ")";
                } else {
                    $sql .= " ORDER BY " . $data['sort'];
                }
            } else {
                $sql .= " ORDER BY p.sort_order";
            }

            if (isset($data['order']) && ($data['order'] == 'DESC')) {
                $sql .= " DESC";
            } else {
                $sql .= " ASC";
            }

            if (isset($data['start']) || isset($data['limit'])) {
                if ($data['start'] < 0) {
                    $data['start'] = 0;
                }

                if ($data['limit'] < 1) {
                    $data['limit'] = 20;
                }

                $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
            }

            $product_data = array();
            //print_r($sql);exit();
            $query = $this->getDb()->query($sql);

            foreach ($query->rows as $result) {
                $product_data[$result['product_id']] = $this->getProduct($result['product_id']);
            }

            $this->cache->set('product.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . (int)$customer_group_id . '.' . $cache, $product_data);
        }

        return $product_data;
    }

    public function getProductSpecials($data = array()) {
        if ($this->getCurrentCustomer()->isLogged()) {
            $customer_group_id = $this->getCurrentCustomer()->getCustomerGroupId();
        } else {
            $customer_group_id = $this->config->get('config_customer_group_id');
        }

        $sql = "
		    SELECT DISTINCT
		        ps.product_id,
		        (
                    SELECT AVG(rating)
                    FROM review r1
                    WHERE
                        r1.product_id = ps.product_id
                        AND r1.status = '1'
                    GROUP BY r1.product_id
                ) AS rating
            FROM
                product_special ps
                LEFT JOIN product p ON (ps.product_id = p.product_id)
                LEFT JOIN product_description pd ON (p.product_id = pd.product_id)
                LEFT JOIN product_to_store p2s ON (p.product_id = p2s.product_id)
            WHERE
                p.status = '1'
                AND p.date_available <= '" . date('Y-m-d H:00:00') . "'
                AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'
                AND ps.customer_group_id = '" . (int)$customer_group_id . "'
                AND ((ps.date_start = '0000-00-00' OR ps.date_start < '" . date('Y-m-d H:00:00') . "')
                AND (ps.date_end = '0000-00-00' OR ps.date_end > '" . date('Y-m-d H:00:00', strtotime('+1 hour')) . "'))
            GROUP BY ps.product_id
        ";

        $sort_data = array(
            'pd.name',
            'p.model',
            'ps.price',
            'rating',
            'p.sort_order'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            if ($data['sort'] == 'pd.name' || $data['sort'] == 'p.model') {
                $sql .= " ORDER BY LCASE(" . $data['sort'] . ")";
            } else {
                $sql .= " ORDER BY " . $data['sort'];
            }
        } else {
            $sql .= " ORDER BY p.sort_order";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        $product_data = array();

        $query = $this->getDb()->query($sql);

        foreach ($query->rows as $result) {
            $product_data[$result['product_id']] = $this->getProduct($result['product_id']);
        }

        return $product_data;
    }

    public function getLatestProducts($limit) {
        $product_data = $this->cache->get('product.latest.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . (int)$limit);

        if (!$product_data) {
            $query = $this->getDb()->query(<<<SQL
			    SELECT p.product_id
			    FROM
			        product p
			        LEFT JOIN product_to_store p2s ON (p.product_id = p2s.product_id)
                WHERE
                    p.status = '1' AND p.date_available <= ?
                    AND p2s.store_id = ?
                ORDER BY p.date_added DESC
                LIMIT ?
SQL
                , array(
                    's:' . date('Y-m-d H:00:00'),
                    'i:' . $this->config->get('config_store_id'),
                    "i:$limit"
                )
            );

            foreach ($query->rows as $result) {
                $product_data[$result['product_id']] = $this->getProduct($result['product_id']);
            }

            $this->getCache()->set('product.latest.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . (int)$limit, $product_data);
        }

        return $product_data;
    }

    /**
     * @param int $productId
     * @return string
     */
    public function getModel($productId) {
        return $this->getSingleValue($productId, 'model');
    }

    public function getPopularProducts($limit) {
        $product_data = array();

        $query = $this->getDb()->query("
		    SELECT p.product_id
		    FROM
		        product p
		        LEFT JOIN product_to_store p2s ON (p.product_id = p2s.product_id)
            WHERE
                p.status = '1' AND p.date_available <= ?
                AND p.product_id <> " . REPURCHASE_ORDER_PRODUCT_ID . "
                AND p2s.store_id = ?
            ORDER BY p.viewed, p.date_added DESC
            LIMIT ?
            ", array(
                's:' . date('Y-m-d H:00:00'),
                'i:' . $this->config->get('config_store_id'),
                "i:$limit"
            )
        );

        foreach ($query->rows as $result) {
            $product_data[$result['product_id']] = $this->getProduct($result['product_id']);
        }

        return $product_data;
    }

    public function getAfcId($productId) {
        return $this->getSingleValue($productId, 'afc_id');
    }

    public function getAffiliateCommission($productId) {
        return $this->getSingleValue($productId, 'affiliate_commission');
    }

    public function getBestSellerProducts($limit) {
        $product_data = $this->cache->get('product.bestseller.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . (int)$limit);

        if (!$product_data) {
            $product_data = array();

            $query = $this->getDb()->query("
			    SELECT op.product_id, COUNT(*) AS total
			    FROM
			        order_product op
			        LEFT JOIN `order` o ON (op.order_id = o.order_id)
			        LEFT JOIN `product` p ON (op.product_id = p.product_id)
			        LEFT JOIN product_to_store p2s ON (p.product_id = p2s.product_id)
                WHERE
                    o.order_status_id > '0' AND p.status = '1' AND p.date_available <= ?
                    AND op.product_id <> " . REPURCHASE_ORDER_PRODUCT_ID . "
                    AND p2s.store_id = ?
                GROUP BY op.product_id
                ORDER BY total DESC
                LIMIT ?
                ", array(
                    's:' . date('Y-m-d H:00:00'),
                    'i:' . $this->config->get('config_store_id'),
                    "i:$limit"
                )
            );

            foreach ($query->rows as $result) {
                $product_data[$result['product_id']] = $this->getProduct($result['product_id']);
            }

            $this->cache->set('product.bestseller.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . (int)$limit, $product_data);
        }

        return $product_data;
    }

    public function getProductAttributes($product_id) {
        $product_attribute_group_data = array();

        $product_attribute_group_query = $this->getDb()->query("
            SELECT ag.attribute_group_id, agd.name
            FROM
                product_attribute pa
                LEFT JOIN attribute a ON (pa.attribute_id = a.attribute_id)
                LEFT JOIN attribute_group ag ON (a.attribute_group_id = ag.attribute_group_id)
                LEFT JOIN attribute_group_description agd ON (ag.attribute_group_id = agd.attribute_group_id)
            WHERE pa.product_id = '" . (int)$product_id . "' AND agd.language_id = '" . (int)$this->config->get('config_language_id') . "'
            GROUP BY ag.attribute_group_id
            ORDER BY ag.sort_order, agd.name"
        );

        foreach ($product_attribute_group_query->rows as $product_attribute_group) {
            $product_attribute_data = array();

            $product_attribute_query = $this->getDb()->query("SELECT a.attribute_id, ad.name, pa.text FROM product_attribute pa LEFT JOIN attribute a ON (pa.attribute_id = a.attribute_id) LEFT JOIN attribute_description ad ON (a.attribute_id = ad.attribute_id) WHERE pa.product_id = '" . (int)$product_id . "' AND a.attribute_group_id = '" . (int)$product_attribute_group['attribute_group_id'] . "' AND ad.language_id = '" . (int)$this->config->get('config_language_id') . "' AND pa.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY a.sort_order, ad.name");

            foreach ($product_attribute_query->rows as $product_attribute) {
                $product_attribute_data[] = array(
                    'attribute_id' => $product_attribute['attribute_id'],
                    'name'         => $product_attribute['name'],
                    'text'         => $product_attribute['text']
                );
            }

            $product_attribute_group_data[] = array(
                'attribute_group_id' => $product_attribute_group['attribute_group_id'],
                'name'               => $product_attribute_group['name'],
                'attribute'          => $product_attribute_data
            );
        }

        return $product_attribute_group_data;
    }

    public function getProductDownloads($product_id) {
        $product_download_data = array();

        $query = $this->db->query("SELECT * FROM product_to_download WHERE product_id = '" . (int)$product_id . "'");

        foreach ($query->rows as $result) {
            $product_download_data[] = $result['download_id'];
        }

        return $product_download_data;
    }

    /**
     * @param int $productId
     * @return ProductOption[]
     */
    public function getProductOptions($productId) {
        $product_option_query = $this->getDb()->query(
            "SELECT * FROM product_option WHERE product_id = :productId",
            [':productId' => $productId]
        );

        $productOptions = [];
        foreach ($product_option_query->rows as $productOptionRow) {
            $productOption = new ProductOption(
                $productOptionRow['product_option_id'],
                $this->getProduct($productId, true),
                OptionDAO::getInstance()->getOptionById($productOptionRow['option_id']),
                OptionDAO::getInstance()->getOptionById($productOptionRow['parent_option_id']),
                $productOptionRow['required'],
                $productOptionRow['afc_id']
            );
            $productOptions[$productOption->getId()] = $productOption;
        }
        return $productOptions;
    }

    /**
     * @param ProductOption $productOption
     * @return ProductOptionValueCollection|string
     */
    public function getProductOptionValues($productOption) {
        if ($productOption->getOption()->isSingleValueType()) {
            return $this->getDb()->queryScalar(
                "SELECT option_value FROM product_option WHERE product_option_id = :productOptionId",
                [':productOptionId' => $productOption->getId()]
            );
        } elseif ($productOption->getOption()->isMultiValueType()) {
            $product_option_value_query = $this->getDb()->query("
                    SELECT *
                    FROM
                        product_option_value AS pov
                        LEFT JOIN option_value AS ov ON pov.option_value_id = ov.option_value_id
                        LEFT JOIN option_value_description AS ovd ON ov.option_value_id = ovd.option_value_id
                    WHERE
                        pov.product_id = :productId
                        AND pov.product_option_id = :productOptionId
                        AND ovd.language_id = :languageId
                    ORDER BY ov.sort_order
                    ", [
                ':productId' => $productOption->getProduct()->getId(),
                ':productOptionId' => $productOption->getId(),
                ':languageId' => $this->config->get('config_language_id')
            ]);

            $productOptionValues = new ProductOptionValueCollection();
            foreach ($product_option_value_query->rows as $productOptionValueRow) {
                $productOptionValues->attach(new ProductOptionValue(
                    $productOptionValueRow['product_option_value_id'],
                    $productOption,
                    OptionDAO::getInstance()->getOptionValues($productOption->getOption()->getId())->getById($productOptionValueRow['option_value_id']),
                    $productOptionValueRow['quantity'],
                    $productOptionValueRow['subtract'],
                    $productOptionValueRow['price_prefix'] == '+' ? $productOptionValueRow['price'] : -$productOptionValueRow['price'],
                    $productOptionValueRow['points_prefix'] == '+' ? $productOptionValueRow['points'] : -$productOptionValueRow['points'],
                    $productOptionValueRow['weight_prefix'] == '+' ? $productOptionValueRow['weight'] : -$productOptionValueRow['weight'],
                    $productOptionValueRow['afc_id']
                ));
            }
            return $productOptionValues;
        } else {
            throw new \InvalidArgumentException("Unknown option value type '" . $productOption->getOption()->getType() . "'");
        }
    }
    /**
     * @param Product $product_id
     * @return array
     */
    public function getProductDiscounts($product_id) {
        if ($this->getCurrentCustomer()->isLogged()) {
            $customer_group_id = $this->getCurrentCustomer()->getCustomerGroupId();
        } else {
            $customer_group_id = $this->config->get('config_customer_group_id');
        }

        $query = $this->getDb()->query("
		    SELECT * FROM product_discount
		    WHERE
		        product_id = '" . (int)$product_id . "'
                AND customer_group_id = '" . (int)$customer_group_id . "'
                AND quantity > 1 AND ((date_start = '0000-00-00' OR date_start < '" . date('Y-m-d H:00:00') . "')
                AND (date_end = '0000-00-00' OR date_end > '" . date('Y-m-d H:00:00', strtotime('+1 hour')) . "'))
            ORDER BY quantity ASC, priority ASC, price ASC
        ");

        return $query->rows;
    }

    public function getProductImages($product_id) {
        $query = $this->getDb()->query("
            SELECT *
            FROM product_image
            WHERE product_id = :productId
            ORDER BY sort_order ASC
            ", [':productId' => $product_id]
        );
        $result = [];
        foreach ($query->rows as $row) {
            $result[] = $row['image'];
        }
        return $result;
    }

    public function getProductRelated($product_id) {
        $product_data = array();

        $query = $this->getDb()->query("
		    SELECT *
		    FROM
		        product_related pr
		        LEFT JOIN product p ON (pr.related_id = p.product_id)
		        LEFT JOIN product_to_store p2s ON (p.product_id = p2s.product_id)
            WHERE
                pr.product_id = '" . (int)$product_id . "'
                AND p.status = '1'
                AND p.date_available <= '" . date('Y-m-d H:00:00') . "'
                AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'
        ");

        foreach ($query->rows as $result) {
            $product_data[$result['related_id']] = $this->getProduct($result['related_id']);
        }

        return $product_data;
    }

    /**
     * @param int $product_id
     * @return array
     */
    public function getProductTags($product_id) {
        $query = $this->getDb()->query("SELECT * FROM product_tag WHERE product_id = '" . (int)$product_id . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");

        return $query->rows;
    }

    public function getProductLayoutId($product_id) {
        $query = $this->getDb()->query("SELECT * FROM product_to_layout WHERE product_id = '" . (int)$product_id . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "'");

        if ($query->num_rows) {
            return $query->row['layout_id'];
        } else {
            return  $this->config->get('config_layout_product');
        }
    }

    public function getCategories($product_id) {
        $query = $this->getDb()->query("SELECT * FROM product_to_category WHERE product_id = '" . (int)$product_id . "'");

        return $query->rows;
    }

    public function getDateAdded($productId) {
        return $this->getSingleValue($productId, 'date_added');
    }

    public function getDateAvailable($productId) {
        return $this->getSingleValue($productId, 'date_available');
    }

    public function getDateModified($productId) {
        return $this->getSingleValue($productId, 'date_modified');
    }

    /**
     * @param int $productId
     * @return array
     */
    public function getDescription($productId) {
        $query = $this->getDb()->query(<<<SQL
            SELECT *
            FROM product_description
            WHERE product_id = :productId
SQL
            , [':productId' => $productId]
        );
        $result = new DescriptionCollection();
        foreach ($query->rows as $row) {
            $result->addDescription(new Description(
                $row['language_id'],
                $row['name'],
                $row['description'],
                $row['meta_description'],
                $row['meta_keyword'],
                $row['seo_title'],
                $row['seo_h1']
            ));
        }
        return $result;
    }

    /**
     * @param int $productId
     * @return Dimensions
     */
    public function getDimension($productId) {
        $query = $this->getDb()->query("
            SELECT length, width, height, length_class_id
            FROM product
            WHERE product_id = ?
            ", array("i:$productId")
        );
        return
            new Dimensions(
                new MeasureUnit($query->row['length_class_id'], 'length'),
                $query->row['height'],
                $query->row['length'],
                $query->row['width']
            );
    }

    public function getImage($productId) {
        return $this->getSingleValue($productId, 'image');
    }

    /**
     * @param int $productId
     * @return bool|string
     */
    public function getImageDescription($productId) {
        return $this->getSingleValue($productId, 'image_description');
    }

    /**
     * @param int $productId
     * @return string
     */
    public function getKeyword($productId) {
        return $this->getDb()->queryScalar(<<<SQL
            SELECT keyword
            FROM url_alias
            WHERE query = 'product_id'+?
SQL
            , array("i:$productId")
        );
    }

    public function getKoreanName($productId) {
        return $this->getSingleValue($productId, 'korean_name');
    }

    public function getLocation($productId) {
        return $this->getSingleValue($productId, 'location');
    }

    public function getManufacturerId($productId) {
        return $this->getSingleValue($productId, 'manufacturer_id');
    }

    public function getMinimum($productId) {
        return $this->getSingleValue($productId, 'minimum');
    }

    public function getPoints($productId) {
        return $this->getSingleValue($productId, 'points');
    }

    public function getPrice($productId) {
        return $this->getSingleValue($productId, 'price');
    }

    public function getQuantity($productId) {
        return $this->getSingleValue($productId, 'quantity');
    }

    public function getShipping($productId) {
        return $this->getSingleValue($productId, 'shipping');
    }

    public function getSku($productId) {
        return $this->getSingleValue($productId, 'sku');
    }

    public function getSortOrder($productId) {
        return $this->getSingleValue($productId, 'sort_order');
    }

    public function getStatus($productId) {
        return $this->getSingleValue($productId, 'status');
    }

    public function getStockStatusId($productId) {
        return $this->getSingleValue($productId, 'stock_status_id');
    }

    /**
     * @param int $productId
     * @return array
     */
    public function getStores($productId) {
        $query = $this->getDb()->query("SELECT store_id FROM product_to_store WHERE product_id = ?", array("i:$productId"));
        $result = array();
        foreach ($query->rows as $row) {
            $result[] = $row['store_id'];
        }
        return $result;
    }

    public function getSubtract($productId) {
        return $this->getSingleValue($productId, 'subtract');
    }

    /**
     * @param int $productId
     * @return Supplier
     */
    public function getSupplier($productId) {
        $supplierId = $this->getSingleValue($productId, 'supplier_id');
        return SupplierDAO::getInstance()->getSupplier($supplierId);
    }

    public function getSupplierUrl($productId) {
        return $this->getSingleValue($productId, 'supplier_url');
    }

    public function getTotalProducts($data = array()) {
        /// Cache implementation
        $dataHash = base64_encode(serialize($data)) . $this->config->get('config_store_id');
        $result = $this->cache->get("productsCount.$dataHash");
        if (isset($result))
            return $result;
//        $this->log->write("Go to DB");
        $sql = "
		    SELECT COUNT(DISTINCT p.product_id) AS total
            FROM
                product p
                LEFT JOIN product_description pd ON (p.product_id = pd.product_id)
                LEFT JOIN product_to_store p2s ON (p.product_id = p2s.product_id)";

        if (!empty($data['filter_category_id'])) {
            $sql .= " LEFT JOIN product_to_category p2c ON (p.product_id = p2c.product_id)";
        }

        if (!empty($data['filter_tag'])) {
            $sql .= " LEFT JOIN product_tag pt ON (p.product_id = pt.product_id)";
        }

        $sql .= "
		    WHERE
		        pd.language_id = '" . (int)$this->config->get('config_language_id') . "'
		        AND p.status = '1'
		        AND p.date_available <= '" . date('Y-m-d H:00:00') . "'
		        AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";
        if (!empty($data['filter_name']) || !empty($data['filter_tag'])) {
            $sql .= " AND (";

            if (!empty($data['filter_name'])) {
                $implode = array();

                $words = explode(' ', $data['filter_name']);

                foreach ($words as $word) {
                    if (!empty($data['filter_description'])) {
                        $implode[] = "LCASE(pd.name) LIKE '%" . $this->getDb()->escape(utf8_strtolower($word)) . "%' OR LCASE(pd.description) LIKE '%" . $this->getDb()->escape(utf8_strtolower($word)) . "%'";
                    } else {
                        $implode[] = "LCASE(pd.name) LIKE '%" . $this->getDb()->escape(utf8_strtolower($word)) . "%'";
                    }
                }

                if ($implode) {
                    $sql .= " " . implode(" OR ", $implode) . "";
                }
            }

            if (!empty($data['filter_name']) && !empty($data['filter_tag'])) {
                $sql .= " OR ";
            }

            if (!empty($data['filter_tag'])) {
                $implode = array();

                $words = explode(' ', $data['filter_tag']);

                foreach ($words as $word) {
                    $implode[] = "LCASE(pt.tag) LIKE '%" . $this->getDb()->escape(utf8_strtolower($data['filter_tag'])) . "%' AND pt.language_id = '" . (int)$this->config->get('config_language_id') . "'";
                }

                if ($implode) {
                    $sql .= " " . implode(" OR ", $implode) . "";
                }
            }

            $sql .= ")";
        }

        if (!empty($data['filter_category_id'])) {
            if (!empty($data['filter_sub_category'])) {
                $implode_data = array();

                $implode_data[] = "p2c.category_id = '" . (int)$data['filter_category_id'] . "'";

                $this->load->model('catalog/category');

                $categories = $this->model_catalog_category->getCategoriesByParentId($data['filter_category_id']);

                foreach ($categories as $category_id) {
                    $implode_data[] = "p2c.category_id = '" . (int)$category_id . "'";
                }

                $sql .= " AND (" . implode(' OR ', $implode_data) . ")";
            } else {
                $sql .= " AND p2c.category_id = '" . (int)$data['filter_category_id'] . "'";
            }
        }

        if (!empty($data['filter_manufacturer_id'])) {
            $sql .= " AND p.manufacturer_id = '" . (int)$data['filter_manufacturer_id'] . "'";
        }

        //print_r($sql);exit();

        $query = $this->getDb()->query($sql);
        $this->cache->set("productsCount.$dataHash", $query->row['total']);
        return $query->row['total'];
    }

    public function getTotalProductSpecials() {
        if ($this->getCurrentCustomer()->isLogged()) {
            $customer_group_id = $this->getCurrentCustomer()->getCustomerGroupId();
        } else {
            $customer_group_id = $this->config->get('config_customer_group_id');
        }

        $query = $this->getDb()->query("
		    SELECT COUNT(DISTINCT ps.product_id) AS total
		    FROM
		        product_special ps
		        LEFT JOIN product p ON (ps.product_id = p.product_id)
		        LEFT JOIN product_to_store p2s ON (p.product_id = p2s.product_id)
            WHERE
                p.status = '1'
                AND p.date_available <= '" . date('Y-m-d H:00:00') . "'
                AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'
                AND ps.customer_group_id = '" . (int)$customer_group_id . "'
                AND ((ps.date_start = '0000-00-00' OR ps.date_start < '" . date('Y-m-d H:00:00') . "')
                AND (ps.date_end = '0000-00-00' OR ps.date_end > '" . date('Y-m-d H:00:00', strtotime('+1 hour')) . "'))
        ");

        if (isset($query->row['total'])) {
            return $query->row['total'];
        } else {
            return 0;
        }
    }

    public function getUpc($productId) {
        return $this->getSingleValue($productId, 'upc');
    }

    public function getUserId($productId) {
        return $this->getSingleValue($productId, 'user_id');
    }

    public function getViewed($productId) {
        return $this->getSingleValue($productId, 'viewed');
    }

    /**
     * @param int $productId
     * @return Weight
     */
    public function getWeight($productId) {
        $query = $this->getDb()->query("
            SELECT weight, weight_class_id
            FROM product
            WHERE product_id = ?
            ", array("i:$productId")
        );
        return
            new Weight(
                new MeasureUnit($query->row['weight_class_id'], 'weight'),
                $query->row['weight']
            );
    }


    public function getProductsM($data = array()) {
//        $this->log->write(print_r($data, true));
        if ($this->getCurrentCustomer()->isLogged()) {
            $customer_group_id = $this->getCurrentCustomer()->getCustomerGroupId();
        } else {
            $customer_group_id = $this->config->get('config_customer_group_id');
        }

        $cache = md5(http_build_query($data));

        if (isset($data['nocache']))
            $product_data = 0;
        else
            $product_data = $this->cache->get('product.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . (int)$customer_group_id . '.' . $cache);

        if (!$product_data) {
            $sql = "
			    SELECT
			        p.product_id,
			        (
			            SELECT AVG(rating) AS total
			            FROM review r1
			            WHERE r1.product_id = p.product_id AND r1.status = '1'
			            GROUP BY r1.product_id
                    ) AS rating
                FROM
                    product p
                    LEFT JOIN product_description pd ON (p.product_id = pd.product_id)
                    LEFT JOIN product_to_store p2s ON (p.product_id = p2s.product_id)
            ";

            if (!empty($data['filter_tag'])) {
                $sql .= " LEFT JOIN product_tag pt ON (p.product_id = pt.product_id)";
            }

            if (!empty($data['filter_category_id'])) {
                $sql .= " LEFT JOIN product_to_category p2c ON (p.product_id = p2c.product_id)";
            }

            $sql .= "
			    WHERE
			        pd.language_id = '" . (int)$this->config->get('config_language_id') . "'
			        AND p.status = '1'
			        AND p.date_available <= '" . date('Y-m-d H:00:00') . "'
			        AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "
            '";

            if (!empty($data['filter_name']) || !empty($data['filter_tag'])) {
                $sql .= " AND (";

                if (!empty($data['filter_name'])) {
                    $implode = array();

                    $words = explode(' ', $data['filter_name']);

                    foreach ($words as $word) {
                        if (!empty($data['filter_description'])) {
                            $implode[] = "LCASE(pd.name) LIKE '%" . $this->getDb()->escape(utf8_strtolower($word)) . "%' OR LCASE(pd.description) LIKE '%" . $this->getDb()->escape(utf8_strtolower($word)) . "%'" . "OR LCASE(p.model) LIKE '%" . $this->getDb()->escape(utf8_strtolower($word)) . "%' OR LCASE(pd.description) LIKE '%" . $this->getDb()->escape(utf8_strtolower($word)) . "%'";;

                        } else {
                            $implode[] = "LCASE(pd.name) LIKE '%" . $this->getDb()->escape(utf8_strtolower($word)) . "%'" . "OR LCASE(p.model) LIKE '%" . $this->getDb()->escape(utf8_strtolower($word)) . "%'";

                        }
                    }

                    if ($implode) {
                        $sql .= " " . implode(" OR ", $implode) . "";
                    }
                }

                if (!empty($data['filter_name']) && !empty($data['filter_tag'])) {
                    $sql .= " OR ";
                }

                if (!empty($data['filter_tag'])) {
                    $implode = array();

                    $words = explode(' ', $data['filter_tag']);

                    foreach ($words as $word) {
                        $implode[] = "LCASE(pt.tag) LIKE '%" . $this->getDb()->escape(utf8_strtolower($data['filter_tag'])) . "%' AND pt.language_id = '" . (int)$this->config->get('config_language_id') . "'";
                    }

                    if ($implode) {
                        $sql .= " " . implode(" OR ", $implode) . "";
                    }
                }

                $sql .= ")";
            }

            if (!empty($data['filter_category_id'])) {
                if (!empty($data['filter_sub_category'])) {
                    $implode_data = array();

                    $implode_data[] = "p2c.category_id = '" . (int)$data['filter_category_id'] . "'";

                    $this->load->model('catalog/category');

                    $categories = $this->model_catalog_category->getCategoriesByParentId($data['filter_category_id']);

                    foreach ($categories as $category_id) {
                        $implode_data[] = "p2c.category_id = '" . (int)$category_id . "'";
                    }

                    $sql .= " AND (" . implode(' OR ', $implode_data) . ")";
                } else {
                    $sql .= " AND p2c.category_id = '" . (int)$data['filter_category_id'] . "'";
                }
            }

            if (!empty($data['filter_manufacturer_id'])) {
                $sql .= " AND p.manufacturer_id = '" . (int)$data['filter_manufacturer_id'] . "'";
            }

            $sql .= " GROUP BY p.product_id";

            $sort_data = array(
                'pd.name',
                'p.model',
                'p.quantity',
                'p.price',
                'rating',
                'p.sort_order',
                'p.date_added'
            );

            if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
                if ($data['sort'] == 'pd.name' || $data['sort'] == 'p.model') {
                    $sql .= " ORDER BY LCASE(" . $data['sort'] . ")";
                } else {
                    $sql .= " ORDER BY " . $data['sort'];
                }
            } else {
                $sql .= " ORDER BY p.sort_order";
            }

            if (isset($data['order']) && ($data['order'] == 'DESC')) {
                $sql .= " DESC";
            } else {
                $sql .= " ASC";
            }

            if (isset($data['start']) || isset($data['limit'])) {
                if ($data['start'] < 0) {
                    $data['start'] = 0;
                }

                if ($data['limit'] < 1) {
                    $data['limit'] = 20;
                }

//				$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
            }

            $product_data = array();
            //print_r($sql);exit();
            $query = $this->getDb()->query($sql);

            foreach ($query->rows as $result) {
                $product_data[$result['product_id']] = $this->getProduct($result['product_id']);
            }

            $this->cache->set('product.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . (int)$customer_group_id . '.' . $cache, $product_data);
        }

        return $product_data;
    }


    public function getProductRewards($product_id) {
        $product_reward_data = array();

        $query = $this->db->query("SELECT * FROM product_reward WHERE product_id = '" . (int)$product_id . "'");

        foreach ($query->rows as $result) {
            $product_reward_data[$result['customer_group_id']] = array('points' => $result['points']);
        }

        return $product_reward_data;
    }

    /**
     * @param Product $product
     */
    public function saveProduct($product) {
        $this->getDb()->beginTransaction();
        try {
            $this->getDb()->query("
		    UPDATE product
		    SET
		        model = :model,
		        sku = :sku,
		        upc = :upc,
		        location = :location,
		        minimum = :minimum,
		        subtract = :subtract,
		        stock_status_id = :stockStatusId,
		        date_available = :dateAvailable,
		        manufacturer_id = :manufacturerId,
		        supplier_id = :supplierId,
		        shipping = :shipping,
		        price = :price,
		        points = :points,
		        weight = :weight,
		        weight_class_id = :weightClassId,
		        length = :length,
		        width = :width,
		        height = :height,
		        length_class_id = :lengthClassId,
		        status = :status,
		        sort_order = :sortOrder,
                affiliate_commission = :affiliateCommission,
                image = :image,
		        date_modified = NOW(),
		        korean_name = :koreanName,
		        supplier_url = :supplierUrl,
		        image_description = :imageDescription
            WHERE product_id = :productId
            ", array(
                    ':model' => $product->getModel(),
                    ':sku' => $product->getSku(),
                    ':upc' => $product->getUpc(),
                    ':location' => $product->getLocation(),
                    ':minimum' => $product->getMinimum(),
                    ':subtract' => $product->getSubtract(),
                    ':stockStatusId' => $product->getStockStatusId(),
                    ':dateAvailable' => $product->getDateAvailable(),
                    ':manufacturerId' => $product->getManufacturerId(),
                    ':supplierId' => $product->getSupplier()->getId(),
                    ':shipping' => $product->getShipping(),
                    ':price' => $product->getPrice(),
                    ':points' => $product->getPoints(),
                    ':weight' => $product->getWeight()->getWeight(),
                    ':weightClassId' => $product->getWeight()->getUnit()->getId(),
                    ':length' => $product->getDimension()->getLength(),
                    ':width' => $product->getDimension()->getWidth(),
                    ':height' => $product->getDimension()->getHeight(),
                    ':lengthClassId' => $product->getDimension()->getUnit(),
                    ':status' => $product->getStatus(),
                    ':sortOrder' => $product->getSortOrder(),
                    ':affiliateCommission' => $product->getAffiliateCommission(),
                    ':image' => $product->getImagePath(),
                    ':productId' => $product->getId(),
                    ':koreanName' => $product->getKoreanName(),
                    ':supplierUrl' => $product->getSupplierUrl(),
                    ':imageDescription' => $product->getImageDescription()
                )
            );


            $this->saveDescription($product);
            $this->saveStores($product);
            $this->saveAttributes($product);
            $this->saveOptions($product);
            $this->saveDiscounts($product);
            $this->saveSpecials($product);
            $this->saveImages($product);
            $this->saveDownloads($product);
            $this->saveCategories($product);
            $this->saveRelated($product);
            $this->saveRewards($product);
            $this->saveLayouts($product);
            $this->saveTags($product);
            $this->saveUrlAliases($product);
            $this->saveWKAuction($product->getId());

            $this->getCache()->delete('product');
            $this->getDb()->commitTransaction();
        } catch (\Exception $e) {
            $this->getLogger()->write($e->getMessage());
            $this->getLogger()->write($e->getTraceAsString());
            $this->getDb()->rollbackTransaction();
        }
    }

    /**
     * @param int $productId
     */
    private function saveWKAuction($productId) {
        if($this->config->get('wk_auction_timezone_set')) {
            if (isset($data['auction_min']) && isset($data['auction_max']) && isset($data['auction_end'])) {
                $auct=$this->db->query("SELECT * FROM " . DB_PREFIX . "wkauction WHERE product_id = '" . (int)$productId . "'");

                $auct=$auct->row;

                if(count($auct)!=0){
                    $this->db->query("UPDATE " . DB_PREFIX . "wkauction SET product_id = '" . (int)$productId . "', name = '" .$data['auction_name']. "', min = '" .$data['auction_min'].  "', isauction = '" .$data['isauction'] ."', start_date = '" .$data['auction_start']."', max = '" .$data['auction_max'] . "', end_date = '" .$data['auction_end'] . "' WHERE id ='" .(int)$auct['id'] . "'");
                }
                else{
                    $this->db->query("INSERT INTO " . DB_PREFIX . "wkauction SET product_id = '" . (int)$productId . "', name = '" .$data['auction_name']. "', min = '" .$data['auction_min'].  "', isauction = '" .$data['isauction'] ."', max = '" .$data['auction_max'] ."', start_date = '" .$data['auction_start'] . "', end_date = '" .$data['auction_end'] . "'");
                }
            }
        }
    }

    /**
     * @param Product $product
     */
    private function saveOptions($product) {
        if ($product->isOptionsModified()) {
            $this->getDb()->query("DELETE FROM product_option WHERE product_id = :productId", [':productId' => $product->getId()]);
            $this->getDb()->query("DELETE FROM product_option_value WHERE product_id = :productId", [':productId' => $product->getId()]);

            foreach ($product->getOptions() as $productOption) {
                $params = [
                    ':productOptionId' => $productOption->getId(),
                    ':productId' => $product->getId(),
                    ':optionId' => $productOption->getOption()->getId(),
                    ':isRequired' => $productOption->isRequired(),
                    ':optionValue' => $productOption->getValue()
                ];
                if ($params[':optionValue'] instanceof ProductOptionValueCollection) {
                    $params[':optionValue'] = '';
                }
                $this->getDb()->query("
                    INSERT INTO product_option
                    SET
                        product_option_id = :productOptionId,
                        product_id = :productId,
                        option_id = :optionId,
                        required = :isRequired,
                        option_value = :optionValue
                    ", $params);
                $productOption->setId($this->getDb()->getLastId());

                //TODO: Should work regardless of type based on values only
//                if ($productOption->getType() == 'select' || $productOption->getType() == 'radio' ||
//                    $productOption->getType() == 'checkbox' || $productOption->getType() == 'image') {
                foreach ($productOption->getValue() as $productOptionValue) {
                    $this->getDb()->query("
                        INSERT INTO product_option_value
                        SET
                            product_option_value_id = :productOptionValueId,
                            product_option_id = :productOptionId,
                            product_id = :productId,
                            option_id = :optionId,
                            option_value_id = :optionValueId,
                            quantity = :quantity,
                            subtract = :subtract,
                            price = :price,
                            price_prefix = :pricePrefix,
                            points = :points,
                            points_prefix = :pointsPrefix,
                            weight = :weight,
                            weight_prefix = :weightPrefix
                    ", [
                        ':productOptionValueId' => $productOptionValue->getId(),
                        ':productOptionId' => $productOption->getId(),
                        ':productId' => $product->getId(),
                        ':optionId' => $productOption->getOption()->getId(),
                        ':optionValueId' => $productOptionValue->getOptionValue()->getId(),
                        ':quantity' => $productOptionValue->getQuantity(),
                        ':subtract' => $productOptionValue->getSubtract(),
                        ':price' => abs($productOptionValue->getPrice()),
                        ':pricePrefix' => $productOptionValue->getPrice() < 0 ? '-' : '+',
                        ':points' => abs($productOptionValue->getPoints()),
                        ':pointsPrefix' => $productOptionValue->getPoints() < 0 ? '-' : '+',
                        ':weight' => abs($productOptionValue->getWeight()),
                        ':weightPrefix' => $productOptionValue->getWeight() < 0 ? '-' : '+'
                    ]);
                }
            }
        }
    }

    /**
     * @param Product $product
     */
    private function saveDescription($product) {
        if ($product->isDescriptionModified()) {
            $this->getDb()->query("DELETE FROM product_description WHERE product_id = :productId", [':productId' => $product->getId()]);
            foreach ($product->getDescription() as $description) {
                $this->db->query("
                    INSERT INTO product_description
                    SET
                        product_id = :productId,
                        language_id = :languageId,
                        name = :name,
                        meta_keyword = :metaKeyword,
                        meta_description = :metaDescription,
                        description = :description,
                        seo_title = :seoTitle,
                        seo_h1 = :seoH1
                    ", [
                    ':productId' => $product->getId(),
                    ':languageId' => $description->getLanguageId(),
                    ':name' => $description->getName(),
                    ':metaKeyword' => $description->getMetaKeyword(),
                    ':metaDescription' => $description->getMetaDescription(),
                    ':description' => $description->getDescription(),
                    ':seoTitle' => $description->getSeoTitle(),
                    ':seoH1' => $description->getSeoH1()
                ]);
            }
        }
    }

    /**
     * @param Product $product
     */
    private function saveStores(Product $product) {
        if ($product->isStoresModified()) {
            $this->getDb()->query("DELETE FROM product_to_store WHERE product_id = :productId", [':productId' => $product->getId()]);
            foreach ($product->getStores() as $store) {
                $this->getDb()->query("
                    INSERT INTO product_to_store
                    SET
                        product_id = :productId,
                        store_id = :storeId
                    ", [':productId' => $product->getId(), ':storeId' => $store]
                );
            }
        }
    }

    /**
     * @param Product $product
     * @return void
     */
    private function saveAttributes(Product $product) {
        if ($product->isAttributesModified()) {
            $this->getDb()->query("DELETE FROM product_attribute WHERE product_id = :productId", [':productId' => $product->getId()]);
            foreach ($product->getAttributes() as $attribute) {
                if ($attribute['attribute_id']) {
                    $this->getDb()->query(
                        "DELETE FROM product_attribute WHERE product_id = :productId AND attribute_id = :attributeId",
                        [':productId' => $product->getId(), ':attributeId' => $attribute['attribute_id']]
                    );

                    foreach ($attribute['product_attribute_description'] as $language_id => $product_attribute_description) {
                        $this->getDb()->query("
                            INSERT INTO product_attribute
                            SET
                                product_id = :productId,
                                attribute_id = :attributeId,
                                language_id = :languageId,
                                `text` = :text
                            ", [
                            ':productId' => $product->getId(),
                            ':attributeId' => $attribute['attribute_id'],
                            ':languageId' => $language_id,
                            ':text' => $product_attribute_description['text']
                        ]);
                    }
                }
            }
        }
    }

    /**
     * @param Product $product
     */
    private function saveDiscounts($product) {
        if ($product->isDiscountsModified()) {
            $this->db->query("DELETE FROM product_discount WHERE product_id = '" . (int)$product->getId() . "'");
            foreach ($product->getDiscounts() as $product_discount) {
                $this->db->query("INSERT INTO product_discount SET product_id = '" . (int)$product->getId() . "', customer_group_id = '" . (int)$product_discount['customer_group_id'] . "', quantity = '" . (int)$product_discount['quantity'] . "', priority = '" . (int)$product_discount['priority'] . "', price = '" . (float)$product_discount['price'] . "', date_start = '" . $this->db->escape($product_discount['date_start']) . "', date_end = '" . $this->db->escape($product_discount['date_end']) . "'");
            }
        }
    }

    /**
     * @param Product $product
     */
    private function saveSpecials($product) {
        if ($product->isSpecialsModified()) {
            $this->db->query("DELETE FROM product_special WHERE product_id = '" . (int)$product->getId() . "'");

            foreach ($product->getSpecials() as $product_special) {
                $this->db->query("INSERT INTO product_special SET product_id = '" . (int)$product->getId() . "', customer_group_id = '" . (int)$product_special['customer_group_id'] . "', priority = '" . (int)$product_special['priority'] . "', price = '" . (float)$product_special['price'] . "', date_start = '" . $this->db->escape($product_special['date_start']) . "', date_end = '" . $this->db->escape($product_special['date_end']) . "'");
            }
        }
    }

    /**
     * @param Product $product
     */
    private function saveImages($product) {
        if ($product->isImagesModified()) {
            $this->db->query("DELETE FROM product_image WHERE product_id = '" . (int)$product->getId() . "'");
            foreach ($product->getImages() as $product_image) {
                $this->db->query("INSERT INTO product_image SET product_id = '" . (int)$product->getId() . "', image = '" . $this->db->escape($product_image['image']) . "', sort_order = '" . (int)$product_image['sort_order'] . "'");
            }
        }
    }

    /**
     * @param Product $product
     */
    private function saveDownloads($product) {
        if ($product->isDownloadsModified()) {
            $this->db->query("DELETE FROM product_to_download WHERE product_id = '" . (int)$product->getId() . "'");
            foreach ($product->getDownloads() as $download_id) {
                $this->db->query("INSERT INTO product_to_download SET product_id = '" . (int)$product->getId() . "', download_id = '" . (int)$download_id . "'");
            }
        }
    }

    /**
     * @param Product $product
     */
    private function saveCategories($product) {
        if ($product->isCategoriesModified()) {
            $this->getDb()->query("DELETE FROM product_to_category WHERE product_id = :productId", [':productId' => $product->getId()]);
            foreach ($product->getCategories() as $category) {
                $this->db->query("
                    INSERT INTO product_to_category
                    SET
                        product_id = :productId,
                        category_id = :categoryId,
                        main_category = :isMainCategory

                    ", [
                    ':productId' => $product->getId(),
                    ':categoryId' => $category['category_id'],
                    ':isMainCategory' => !empty($category['main_category'])
                ]);
            }
        }
    }

    /**
     * @param Product $product
     */
    private function saveRelated($product) {
        if ($product->isRelatedModified()) {
            foreach ($product->getRelated() as $related) {
                $this->getDb()->query("
                    DELETE FROM product_related
                    WHERE
                        (product_id = :productId AND related_id = :relatedId)
                        OR (related_id = :productId AND product_id = :relatedId)
                    ", [
                    ':productId' => $product->getId(),
                    ':relatedId' => $related
                ]);
                $this->getDb()->query(
                    "INSERT INTO product_related VALUES (:productId, :relatedId), (:relatedId, :productId)",
                    [':productId' => $product->getId(), ':relatedId' => $related]
                );
            }
        }
    }

    /**
     * @param Product $product
     */
    private function saveRewards($product) {
        if ($product->isRewardsModified()) {
            $this->db->query("DELETE FROM product_reward WHERE product_id = '" . (int)$product->getId() . "'");
            foreach ($product->getRewards() as $customer_group_id => $value) {
                $this->db->query("INSERT INTO product_reward SET product_id = '" . (int)$product->getId() . "', customer_group_id = '" . (int)$customer_group_id . "', points = '" . (int)$value['points'] . "'");
            }
        }
    }

    /**
     * @param Product $product
     */
    private function saveLayouts($product) {
        if ($product->isLayoutsModified()) {
            $this->db->query("DELETE FROM product_to_layout WHERE product_id = '" . (int)$product->getId() . "'");
            foreach ($product->getLayouts() as $store_id => $layout) {
                if ($layout['layout_id']) {
                    $this->db->query("INSERT INTO product_to_layout SET product_id = '" . (int)$product->getId() . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout['layout_id'] . "'");
                }
            }
        }
    }

    /**
     * @param Product $product
     */
    private function saveTags($product) {
        if ($product->isTagsModified()) {
            $this->db->query("DELETE FROM product_tag WHERE product_id = '" . (int)$product->getId() . "'");
            foreach ($product->getTags() as $language_id => $value) {
                if ($value) {
                    $tags = explode(',', $value);

                    foreach ($tags as $tag) {
                        $this->db->query("INSERT INTO product_tag SET product_id = '" . (int)$product->getId() . "', language_id = '" . (int)$language_id . "', tag = '" . $this->db->escape(trim($tag)) . "'");
                    }
                }
            }
        }
    }

    /**
     * @param Product $product
     * @throws NotImplementedException
     */
    private function saveUrlAliases($product) {
//        if ($product->isUrlAliasesModified()) {
//            $this->db->query("DELETE FROM url_alias WHERE query = 'product_id=" . (int)$product->getId() . "'");

//            if ($data['keyword']) {
//                $this->db->query("INSERT INTO url_alias SET query = 'product_id=" . (int)$product->getId() . "', keyword = '" . $this->db->escape($data['keyword']) . "'");
//            }
//        }
    }
} 
<?php
namespace model\catalog;

use model\DAO;
use model\localization\Description;
use model\localization\DescriptionCollection;
use system\exception\NotImplementedException;
use system\library\Dimensions;
use system\library\Filter;
use system\library\MeasureUnit;
use system\library\Weight;

class ProductDAO extends DAO {
    /**
     * @param array $data
     * @return Filter
     */
    private function buildFilter(array $data) {
        $filter = new Filter(); $tmp0 = $tmp1 = '';
        if (isset($data['selectedItems'])) {
            $filter->addChunk($this->buildSimpleFieldFilterEntry('p.product_id', $data['selectedItems'], $tmp0, $tmp1));
        }
        if (!empty($data['filterCategoryId'])) {
            $categories = [];
            $categories[] = $data['filterCategoryId'];
            if (!empty($data['filterSubCategories'])) {
                foreach (CategoryDAO::getInstance()->getCategoriesByParentId($data['filterCategoryId']) as $category) {
                    $categories[] = $category->getId();
                }
            }
            $filter->addChunk($this->buildSimpleFieldFilterEntry('p2c.category_id', $categories, $tmp0, $tmp1));
        }
        if (!empty($data['filterDateAddedFrom']))
            $filter->addChunk("p.date_added > :dateAddedFrom", [":dateAddedFrom" => $data['filterDateAddedFrom']]);
        if (!empty($data['filterDateAddedTo']))
            $filter->addChunk("p.date_added < :dateAddedTo", [":dateAddedTo" => date('Y-m-d', strtotime($data['filterDateAddedTo']) + 86400)]);
        if (!is_null($data['filterEnabled'])) {
            $filter->addChunk("p.status = :enabled", [":enabled" => $data['filterEnabled']]);
        }
        if (!empty($data['filterId']) && is_numeric($data['filterId'])) {
            $filter->addChunk('p.product_id = :productId', [':productId' => $data['filterId']]);
        }
        if (!empty($data['filterKoreanName'])) {
            $filter->addChunk("p.korean_name LIKE CONCAT('%', :koreanName, '%')", [':koreanName' => $data['filterKoreanName']]);
        }
        if (!empty($data['filterLanguageId'])) {
            $filter->addChunk("pd.language_id = :languageId", [":languageId" => $data['filterLanguageId']]);
        }
        if (!empty($data['filterManufacturerId'])) {
            $filter->addChunk($this->buildSimpleFieldFilterEntry('p.manufacturer_id', $data['filterManufacturerId'], $tmp0, $tmp1));
        }
        if (!empty($data['filterModel'])) {
            $filter->addChunk("p.model LIKE CONCAT('%', :model, '%')", [':model' => $data['filterModel']]);
        }
        if (!empty($data['filterName'])) {
            $words = explode(' ', $data['filterName']); $filterString = ''; $filterParams = [];
            for ($i = 0; $i < sizeof($words); $i++) {
                $filterString .= " OR pd.name LIKE CONCAT('%', :name$i, '%') OR pd.description LIKE CONCAT('%', :name$i, '%')";
                $filterParams[":name$i"] = $words[$i];
            }
            $filter->addChunk("(" . substr($filterString, 4) . ")", $filterParams);
        }
        if (!empty($data['filterPriceRange'])) {
            $filterString = '';
            if (!is_null($data['filterPriceRange'][0])) {
                $filterString = " AND p.price >= :priceFrom";
            }
            if (!is_null($data['filterPriceRange'][1])) {
                $filterString .= "AND p.price <= :priceTo";
            }
            $filter->addChunk(substr($filterString, 4), [':priceFrom' => $data['filterPriceRange'][0], ':priceTo' => $data['filterPriceRange'][1]]);
        }
        if (!empty($data['filterStoreId'])) {
            $filter->addChunk($this->buildSimpleFieldFilterEntry('p2s.store_id', $data['filterStoreId'], $tmp0, $tmp1));
        }
        if (!empty($data['filterSupplierId'])) {
            $filter->addChunk($this->buildSimpleFieldFilterEntry('p.supplier_id', $data['filterSupplierId'], $tmp0, $tmp1));
        }
        if (!empty($data['filterTag'])) {
            $words = explode(' ', $data['filterTag']); $filterString = ''; $filterParams = [];
            for ($i = 0; $i < sizeof($words); $i++) {
                $filterString .= " OR pt.tag LIKE CONCAT('%', :tag$i, '%')";
                $filterParams[":tag$i"] = $words[$i];
            }
            $filter->addChunk("(" . substr($filterString, 4) . ")", $filterParams);
        }
        if (!empty($data['filterUserNameId']) && is_array($data['filterUserNameId']) && sizeof($data['filterUserNameId'])) {
            $filter->addChunk($this->buildSimpleFieldFilterEntry('p.user_id', $data['filterUserNameId'], $tmp0, $tmp1));
//            $iDSet = array();
//            $filterUserName = array();
//            foreach ($data['filterUserNameId'] as $usernameId)
//                if ($usernameId)
//                    $iDSet[] = $usernameId;
//                else
//                    $filterUserName['null'] = "u.user_id IS NULL";
//            if (sizeof($iDSet))
//                $filterUserName['ids'] = "u.user_id IN (" . implode(', ', $iDSet) . ")";
//            $filter .= ($filter ? " AND" : "") . ' (' . implode(' OR ', $filterUserName) . ')';
        }

        return $filter;
    }

    public function addProduct($data) {
        $this->getDb()->query("
		    INSERT INTO product
		    (model, user_id, sku, upc, location, quantity, minimum, subtract, stock_status_id, date_available,
            manufacturer_id, supplier_id, shipping, price, points, weight, weight_class_id, length, width, height,
            length_class_id, status, sort_order, date_added, affiliate_commission, korean_name, supplier_url, image_description)
            VALUES (
              :model, :userId, :sku, :upc, :location, 9999, :minimum, :subtract, :stockStatusId, :dateAvailable,
              :manufacturerId, :supplierId, :shipping, :price, :points, :weight, :weightClassId, :length, :width, :height,
              :lengthClassId, :status, :sortOrder, NOW(), :affiliateCommission, :koreanName, :supplierUrl, :imageDescription
            )
            ", [
                ':model' => $data['model'],
                ':userId' => $data['user_id'],
                ':sku' => $data['sku'],
                ':upc' => $data['upc'],
                ':location' => $data['location'],
                ':minimum' => $data['minimum'],
                ':subtract' => $data['subtract'],
                ':stockStatusId' => $data['stock_status_id'],
                ':dateAvailable' => $data['date_available'],
                ':manufacturerId' => $data['manufacturer_id'],
                ':supplierId' => $data['supplier_id'],
                ':shipping' => $data['shipping'],
                ':price' => $data['price'],
                ':points' => $data['points'],
                ':weight' => $data['weight'],
                ':weightClassId' => $data['weight_class_id'],
                ':length' => $data['length'],
                ':width' => $data['width'],
                ':height' => $data['height'],
                ':lengthClassId' => $data['length_class_id'],
                ':status' => $data['status'],
                ':sortOrder' => $data['sort_order'],
                ':affiliateCommission' => (isset($data['affiliate_commission']) ? $data['affiliate_commission'] : 0),
                ':koreanName' => $data['koreanName'],
                ':supplierUrl' => $data['supplierUrl'],
                ':imageDescription' => $data['image_description']
        ]);
        $productId = $this->getDb()->getLastId();

        if($this->getConfig()->get('wk_auction_timezone_set')){
            if (isset($data['auction_min']) && isset($data['auction_max']) && isset($data['auction_end'])) {
                $this->getDb()->query("
                    INSERT INTO wkauction 
                    (product_id, name, min, isauction, max, start_date, end_date)
                    VALUES (:productId, :name, :min, :isAuction, :max, :startDate, :endDate)
                ", [
                    ':productId' => $productId,
                    ':name' => $data['auction_name'],
                    ':min' => $data['auction_min'],
                    ':isAuction' => $data['isauction'],
                    ':max' => $data['auction_max'],
                    ':startDate' => $data['auction_start'],
                    ':endDate' => $data['auction_end']
                ]);
            }
        }


        if (isset($data['image'])) {
            $this->getDb()->query("UPDATE product SET image = :image WHERE product_id = :productId", [':image' => $data['image'], ':productId' => $productId ]);
        }

        foreach ($data['product_description'] as $language_id => $value) {
            $this->getDb()->query("
			    INSERT INTO product_description
			    SET
			        product_id = '" . (int)$productId . "',
			        language_id = '" . (int)$language_id . "',
			        name = '" . $this->getDb()->escape($value['name']) . "',
			        meta_keyword = '" . (isset($value['meta_keyword']) ? $this->getDb()->escape($value['meta_keyword']) : '') . "',
			        meta_description = '" . (isset($value['meta_description']) ? $this->getDb()->escape($value['meta_description']) : '') . "',
			        description = '" . $this->getDb()->escape($value['description']) . "',
			        seo_title = '" . (isset($value['seo_title']) ? $this->getDb()->escape($value['seo_title']) : '') . "',
			        seo_h1 = '" . (isset($value['seo_h1']) ? $this->getDb()->escape($value['seo_h1']) : '') . "'
            ");
        }

        if (isset($data['product_store'])) {
            foreach ($data['product_store'] as $store_id) {
                $this->getDb()->query("INSERT INTO product_to_store SET product_id = '" . (int)$productId . "', store_id = '" . (int)$store_id . "'");
            }
        }

        if (isset($data['product_attribute'])) {
            foreach ($data['product_attribute'] as $product_attribute) {
                if ($product_attribute['attribute_id']) {
                    $this->getDb()->query("DELETE FROM product_attribute WHERE product_id = '" . (int)$productId . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "'");

                    foreach ($product_attribute['product_attribute_description'] as $language_id => $product_attribute_description) {
                        $this->getDb()->query("INSERT INTO product_attribute SET product_id = '" . (int)$productId . "', attribute_id = '" . (int)$product_attribute['attribute_id'] . "', language_id = '" . (int)$language_id . "', text = '" .  $this->getDb()->escape($product_attribute_description['text']) . "'");
                    }
                }
            }
        }

        if (isset($data['product_option'])) {
            foreach ($data['product_option'] as $product_option) {
                if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image') {
                    $this->getDb()->query("INSERT INTO product_option SET product_id = '" . (int)$productId . "', option_id = '" . (int)$product_option['option_id'] . "', required = '" . (int)$product_option['required'] . "'");

                    $product_option_id = $this->getDb()->getLastId();

                    if (isset($product_option['product_option_value'])) {
                        foreach ($product_option['product_option_value'] as $product_option_value) {
                            $this->getDb()->query("INSERT INTO product_option_value SET product_option_id = '" . (int)$product_option_id . "', product_id = '" . (int)$productId . "', option_id = '" . (int)$product_option['option_id'] . "', option_value_id = '" . $this->getDb()->escape($product_option_value['option_value_id']) . "', quantity = '" . (int)$product_option_value['quantity'] . "', subtract = '" . (int)$product_option_value['subtract'] . "', price = '" . (float)$product_option_value['price'] . "', price_prefix = '" . $this->getDb()->escape($product_option_value['price_prefix']) . "', points = '" . (int)$product_option_value['points'] . "', points_prefix = '" . $this->getDb()->escape($product_option_value['points_prefix']) . "', weight = '" . (float)$product_option_value['weight'] . "', weight_prefix = '" . $this->getDb()->escape($product_option_value['weight_prefix']) . "'");
                        }
                    }
                } else {
                    $this->getDb()->query("INSERT INTO product_option SET product_id = '" . (int)$productId . "', option_id = '" . (int)$product_option['option_id'] . "', option_value = '" . $this->getDb()->escape($product_option['option_value']) . "', required = '" . (int)$product_option['required'] . "'");
                }
            }
        }

        if (isset($data['product_discount'])) {
            foreach ($data['product_discount'] as $product_discount) {
                $this->getDb()->query("
				    INSERT INTO product_discount
				    SET
				        product_id = '" . (int)$productId . "',
				        customer_group_id = '" . (int)$product_discount['customer_group_id'] . "',
				        quantity = '" . (int)$product_discount['quantity'] . "',
				        priority = '" . (int)$product_discount['priority'] . "',
				        price = '" . (float)$product_discount['price'] . "',
				        date_start = '" . $this->getDb()->escape($product_discount['date_start']) . "',
				        date_end = '" . $this->getDb()->escape($product_discount['date_end']) . "'
                ");
            }
        }

        if (isset($data['product_special'])) {
            foreach ($data['product_special'] as $product_special) {
                $this->getDb()->query("INSERT INTO product_special SET product_id = '" . (int)$productId . "', customer_group_id = '" . (int)$product_special['customer_group_id'] . "', priority = '" . (int)$product_special['priority'] . "', price = '" . (float)$product_special['price'] . "', date_start = '" . $this->getDb()->escape($product_special['date_start']) . "', date_end = '" . $this->getDb()->escape($product_special['date_end']) . "'");
            }
        }

        if (isset($data['product_image'])) {
            foreach ($data['product_image'] as $product_image) {
                $this->getDb()->query("
				    INSERT INTO product_image
				    SET
				        product_id = '" . (int)$productId . "',
				        image = '" . $this->getDb()->escape($product_image['image']) . "',
				        sort_order = '" . (isset($product_image['sort_order']) ? (int)$product_image['sort_order'] : 0) . "'
                ");
            }
        }

        if (isset($data['product_download'])) {
            foreach ($data['product_download'] as $download_id) {
                $this->getDb()->query("INSERT INTO product_to_download SET product_id = '" . (int)$productId . "', download_id = '" . (int)$download_id . "'");
            }
        }

        if (isset($data['product_category'])) {
            foreach ($data['product_category'] as $category_id) {
                $this->getDb()->query("INSERT INTO product_to_category SET product_id = '" . (int)$productId . "', category_id = '" . (int)$category_id . "'");
            }
        }

        if (isset($data['main_category_id']) && $data['main_category_id'] > 0) {
            $this->getDb()->query("DELETE FROM product_to_category WHERE product_id = '" . (int)$productId . "' AND category_id = '" . (int)$data['main_category_id'] . "'");
            $this->getDb()->query("INSERT INTO product_to_category SET product_id = '" . (int)$productId . "', category_id = '" . (int)$data['main_category_id'] . "', main_category = 1");
        } elseif (isset($data['product_category'][0])) {
            $this->getDb()->query("UPDATE product_to_category SET main_category = 1 WHERE product_id = '" . (int)$productId . "' AND category_id = '" . (int)$data['product_category'][0] . "'");
        }

        if (isset($data['product_related'])) {
            foreach ($data['product_related'] as $related_id) {
                $this->getDb()->query("DELETE FROM product_related WHERE product_id = '" . (int)$productId . "' AND related_id = '" . (int)$related_id . "'");
                $this->getDb()->query("INSERT INTO product_related SET product_id = '" . (int)$productId . "', related_id = '" . (int)$related_id . "'");
                $this->getDb()->query("DELETE FROM product_related WHERE product_id = '" . (int)$related_id . "' AND related_id = '" . (int)$productId . "'");
                $this->getDb()->query("INSERT INTO product_related SET product_id = '" . (int)$related_id . "', related_id = '" . (int)$productId . "'");
            }
        }

        if (isset($data['product_reward'])) {
            foreach ($data['product_reward'] as $customer_group_id => $product_reward) {
                $this->getDb()->query("INSERT INTO product_reward SET product_id = '" . (int)$productId . "', customer_group_id = '" . (int)$customer_group_id . "', points = '" . (int)$product_reward['points'] . "'");
            }
        }

        if (isset($data['product_layout'])) {
            foreach ($data['product_layout'] as $store_id => $layout) {
                if ($layout['layout_id']) {
                    $this->getDb()->query("INSERT INTO product_to_layout SET product_id = '" . (int)$productId . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout['layout_id'] . "'");
                }
            }
        }

        if (isset($data['product_tag'])) {
            foreach ($data['product_tag'] as $language_id => $value) {
                if ($value) {
                    $tags = explode(',', $value);

                    foreach ($tags as $tag) {
                        $this->getDb()->query("INSERT INTO product_tag SET product_id = '" . (int)$productId . "', language_id = '" . (int)$language_id . "', tag = '" . $this->getDb()->escape(trim($tag)) . "'");
                    }
                }
            }
        }

        if (isset($data['keyword'])) {
            $this->getDb()->query("INSERT INTO url_alias SET query = 'product_id=" . (int)$productId . "', keyword = '" . $this->getDb()->escape($data['keyword']) . "'");
        }

        $this->getCache()->delete('product');
        return $productId;
    }


    public function deleteProduct($productId) {
        $this->getDb()->query("DELETE FROM product WHERE product_id = :productId", [ ':productId' => $productId ]);
        $this->getDb()->query("DELETE FROM product_attribute WHERE product_id = :productId", [ ':productId' => $productId ]);
        $this->getDb()->query("DELETE FROM product_description WHERE product_id = :productId", [ ':productId' => $productId ]);
        $this->getDb()->query("DELETE FROM product_discount WHERE product_id = :productId", [ ':productId' => $productId ]);
        $this->getDb()->query("DELETE FROM product_image WHERE product_id = :productId", [ ':productId' => $productId ]);
        $this->getDb()->query("DELETE FROM product_option WHERE product_id = :productId", [ ':productId' => $productId ]);
        $this->getDb()->query("DELETE FROM product_option_value WHERE product_id = :productId", [ ':productId' => $productId ]);
        $this->getDb()->query("DELETE FROM product_related WHERE product_id = :productId", [ ':productId' => $productId ]);
        $this->getDb()->query("DELETE FROM product_related WHERE related_id = :productId", [ ':productId' => $productId ]);
        $this->getDb()->query("DELETE FROM product_reward WHERE product_id = :productId", [ ':productId' => $productId ]);
        $this->getDb()->query("DELETE FROM product_special WHERE product_id = :productId", [ ':productId' => $productId ]);
        $this->getDb()->query("DELETE FROM product_tag WHERE product_id = :productId", [ ':productId' => $productId ]);
        $this->getDb()->query("DELETE FROM product_to_category WHERE product_id = :productId", [ ':productId' => $productId ]);
        $this->getDb()->query("DELETE FROM product_to_download WHERE product_id = :productId", [ ':productId' => $productId ]);
        $this->getDb()->query("DELETE FROM product_to_layout WHERE product_id = :productId", [ ':productId' => $productId ]);
        $this->getDb()->query("DELETE FROM product_to_store WHERE product_id = :productId", [ ':productId' => $productId ]);
        $this->getDb()->query("DELETE FROM review WHERE product_id = :productId", [ ':productId' => $productId ]);
        $this->getDb()->query("DELETE FROM url_alias WHERE query = 'product_id = :productId'", [ ':productId' => $productId ]);

        $this->getCache()->delete('product');
    }


    private function getSingleValue($productId, $columnName) {
        return $this->getDb()->queryScalar("SELECT $columnName FROM product WHERE product_id = ?", array("i:$productId"));
    }

    public function getAuctions($productId) {
        $data = $this->getDb()->query("SELECT * FROM wkauction WHERE product_id = :productId  GROUP BY id", [ ':productId' => $productId ]);

        return $data->rows;
    }

    public function updateViewed($productId) {
        $this->getDb()->query("
            UPDATE product
            SET viewed = (viewed + 1)
            WHERE product_id = :productId"
        , [ ':productId' => $productId ], false, true)
        ;
    }

    /**
     * @param Product|int $product
     * @return Product
     */
    public function duplicateProduct($product) {
        if (!($product instanceof Product)) {
            $product = $this->getProduct($product, false, true);
        }
        $newProduct = new Product(
            0, $this->getLanguage()->getId(), $product->getAfcId(), $product->getAffiliateCommission(),
            $product->getDateAdded(), $product->getDateAvailable(), $product->getDateModified(), $product->getDescription(),
            $product->getDimension(), $product->getImagePath(), $product->getKeyword(), $product->getKoreanName(), 
            $product->getLocation(), $product->getManufacturerId(), $product->getMinimum(), $product->getModel(),
            $product->getOptions(), $product->getPoints(), $product->getPrice(), $product->getQuantity(), $product->getShipping(),
            $product->getSku(), $product->getSortOrder(), 0, $product->getStockStatusId(),
            $product->getStores(), $product->getSubtract(), $product->getSupplier(), $product->getSupplierUrl(),
            $product->getTags(), $product->getUpc(), $product->getUserId(), $product->getViewed(), $product->getWeight(),
            $product->getAttributes(), $product->getDiscounts(), $product->getSpecials(), $product->getDownloads(), 
            $product->getCategories(), $product->getRelated(), $product->getLayouts(), $product->getRewards(),
            $product->getImageDescription());
        return $this->saveProduct($newProduct);
    }

    /**
     * @param int $productId
     * @param bool $shallow Defines whether all product data should be fetched immediately or just stub object created
     * @param bool $object Defines whether object or array should be returned.
     * @return Product|array
     */
    public function getProduct($productId, $shallow = false, $object = false) { //TODO: Перевести на об'єкт
        if ($shallow) {
            return new Product($productId, $this->getLanguage()->getId());
        }
//        $customerGroupId = (!is_null($this->getCurrentCustomer()) && $this->getCurrentCustomer()->isLogged())
//            ? $this->getCurrentCustomer()->getCustomerGroupId()
//            : $this->getConfig()->get('config_customer_group_id');

        $query = $this->getDb()->query(<<<SQL
		    SELECT *
            FROM product AS p
            WHERE p.product_id = :productId
SQL
            , [
//                ':customerGroupId' => $customerGroupId,
//                ':dateStart' => date('Y-m-d H:00:00'),
//                ':dateEnd' => date('Y-m-d H:00:00', strtotime('+1 hour')),
//                ':languageId' => $this->getConfig()->get('config_language_id'),
                ':productId' => $productId,
//                ':storeId' => $this->getConfig()->get('config_store_id')
        ], false, true);

        if ($query->num_rows) {
//            $query->row['price'] = ($query->row['discount'] ? $query->row['discount'] : $query->row['price']);
//            $query->row['rating'] = (int)$query->row['rating'];

            if ($object) {
                return
                    new Product(
                        $query->row['product_id'], $this->getLanguage()->getId(), $query->row['afc_id'], $query->row['affiliate_commission'],
                        $query->row['date_added'], $query->row['date_available'], $query->row['date_modified'], null,
                        new Dimensions($query->row['length_class_id'], $query->row['height'], $query->row['length'], $query->row['width']),
                        $query->row['image'], null, $query->row['korean_name'], $query->row['location'],
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
            throw new \InvalidArgumentException("No product with ID $productId");
        }
    }

    /**
     * @param array $data
     * @return array
     * @throws \CacheNotInstalledException
     */
    public function getProductIds($data = []) {
        $cacheKey = 'product.' . md5(serialize($data));
        $result = $this->getCache()->get($cacheKey);
        if (!is_null($result)) {
            return $result;
        }
        $filter = $this->buildFilter($data);


        $sql = <<<SQL
            SELECT
                p.product_id,
                (
                    SELECT AVG(rating) AS total
                    FROM review r1
                    WHERE r1.product_id = p.product_id AND r1.status = 1
                    GROUP BY r1.product_id
                ) AS rating
            FROM
                product p
                LEFT JOIN product_description pd ON (p.product_id = pd.product_id)
                LEFT JOIN product_to_store p2s ON (p.product_id = p2s.product_id)
                LEFT JOIN product_tag pt ON (p.product_id = pt.product_id)
                LEFT JOIN product_to_category p2c ON (p.product_id = p2c.product_id)
SQL
        ;
        $sql .= $filter->getFilterString(true) . '
            GROUP BY p.product_id';

        $sort_data = array(
            'pd.name',
            'p.model',
            'p.quantity',
            'p.price',
            'rating',
            'p.sort_order',
            'p.date_added',
            'p.product_id'
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

        $sql .= $this->buildLimitString($data['start'], $data['limit']);

        //print_r($sql);exit();
        $query = $this->getDb()->query($sql, $filter->getParams());
        $result = [];
        foreach ($query->rows as $row) {
            $result[$row['product_id']] = $row;
        }
        $this->getCache()->set($cacheKey, $result);
        return $result;
    }

    /**
     * @param array $data
     * @param bool $shallow
     * @return Product[]
     */
    public function getProducts($data = array(), $shallow = false) {
        $product_data = [];
        foreach ($this->getProductIds($data) as $result) {
            try {
                $product_data[$result['product_id']] = $this->getProduct($result['product_id'], $shallow, true);
            } catch (\InvalidArgumentException $exc) {
                $this->getLogger()->write($exc->getMessage());
            }
        }

        return $product_data;
    }

    /**
     * @param array $data
     * @return int
     * @throws \CacheNotInstalledException
     */
    public function getProductsCount($data = array()) {
        $cacheKey = 'productCount.' . md5(serialize($data));
        $result = $this->getCache()->get($cacheKey);
        if (!is_null($result)) {
            return $result;
        }
        $filter = $this->buildFilter($data);
        $sql = "
            SELECT COUNT(DISTINCT p.product_id) AS total
            FROM
                product AS p
                LEFT JOIN product_description pd ON (p.product_id = pd.product_id)
                LEFT JOIN product_to_store p2s ON (p.product_id = p2s.product_id)
                LEFT JOIN product_tag pt ON (p.product_id = pt.product_id)
                LEFT JOIN product_to_category p2c ON (p.product_id = p2c.product_id)
        ";
        $sql .= $filter->getFilterString(true);
        $result = $this->getDb()->queryScalar($sql, $filter->getParams());
        $this->getCache()->set($cacheKey, $result);
        return $result;
    }

    /**
     * @param int $productId
     * @return mixed
     */
    public function getProductSpecials($productId) {
        $query = "
            SELECT *
            FROM product_special AS p
            WHERE product_id = :productId
            ORDER BY priority, price
    ";
//		$this->log->write($query);
        return $this->getDb()->query($query, [':productId' => $productId])->rows;
    }

    /**
     * @param int $productId
     * @return bool|mixed
     */
    public function getProductSpecialsCount($productId) {
        $query = "
            SELECT COUNT(product_special_id) AS total
            FROM product_special AS p
            WHERE product_id = :productId
    ";
//		$this->log->write($query);
        return $this->getDb()->queryScalar($query, [':productId' => $productId]);
    }

    /**
     * @param array $data
     * @return array
     */
    public function getProductSuppliers($data = array()) {
        $filter = $this->buildFilter($data);
        $sql = "
            SELECT DISTINCT s.supplier_id AS supplier_id, s.name AS supplier_name, p.product_id, n.text AS link, a.text AS korean_name, u.user_id, u.username AS user_name
            FROM
                product AS p
                LEFT JOIN product_description AS pd ON (p.product_id = pd.product_id)
                LEFT JOIN supplier AS s ON p.supplier_id = s.supplier_id
                LEFT JOIN manufacturer AS m ON p.manufacturer_id = m.manufacturer_id
                LEFT JOIN product_attribute AS n ON (p.product_id = n.product_id AND n.attribute_id=43)
                LEFT JOIN product_attribute AS a ON (p.product_id = a.product_id AND a.attribute_id=42)
                LEFT JOIN user AS u ON p.user_id = u.user_id
                LEFT JOIN product_to_category p2c ON (p.product_id = p2c.product_id)
        ";
        $sql .= $filter->getFilterString(true) .
            " GROUP BY p.product_id" .
            $this->buildLimitString($data['start'], $data['limit']);

        return $this->getDb()->query($sql, $filter->getParams())->rows;

    }

    public function getLatestProducts($limit) {
        $product_data = $this->getCache()->get('product.latest.' . (int)$this->getConfig()->get('config_language_id') . '.' . (int)$this->getConfig()->get('config_store_id') . '.' . (int)$limit);

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
                    'i:' . $this->getConfig()->get('config_store_id'),
                    "i:$limit"
                )
            );

            foreach ($query->rows as $result) {
                $product_data[$result['product_id']] = $this->getProduct($result['product_id']);
            }

            $this->getCache()->set('product.latest.' . (int)$this->getConfig()->get('config_language_id') . '.' . (int)$this->getConfig()->get('config_store_id') . '.' . (int)$limit, $product_data);
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
                'i:' . $this->getConfig()->get('config_store_id'),
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
        $product_data = $this->getCache()->get('product.bestseller.' . (int)$this->getConfig()->get('config_language_id') . '.' . (int)$this->getConfig()->get('config_store_id') . '.' . (int)$limit);

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
                    'i:' . $this->getConfig()->get('config_store_id'),
                    "i:$limit"
                )
            );

            foreach ($query->rows as $result) {
                $product_data[$result['product_id']] = $this->getProduct($result['product_id']);
            }

            $this->getCache()->set('product.bestseller.' . (int)$this->getConfig()->get('config_language_id') . '.' . (int)$this->getConfig()->get('config_store_id') . '.' . (int)$limit, $product_data);
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
            WHERE pa.product_id = '" . (int)$product_id . "' AND agd.language_id = '" . (int)$this->getConfig()->get('config_language_id') . "'
            GROUP BY ag.attribute_group_id
            ORDER BY ag.sort_order, agd.name"
        );

        foreach ($product_attribute_group_query->rows as $product_attribute_group) {
            $product_attribute_data = array();

            $product_attribute_query = $this->getDb()->query("SELECT a.attribute_id, ad.name, pa.text FROM product_attribute pa LEFT JOIN attribute a ON (pa.attribute_id = a.attribute_id) LEFT JOIN attribute_description ad ON (a.attribute_id = ad.attribute_id) WHERE pa.product_id = '" . (int)$product_id . "' AND a.attribute_group_id = '" . (int)$product_attribute_group['attribute_group_id'] . "' AND ad.language_id = '" . (int)$this->getConfig()->get('config_language_id') . "' AND pa.language_id = '" . (int)$this->getConfig()->get('config_language_id') . "' ORDER BY a.sort_order, ad.name");

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

        $query = $this->getDb()->query("SELECT * FROM product_to_download WHERE product_id = :productId", [ ':productId' => $product_id ], false, true);

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
                ':languageId' => $this->getConfig()->get('config_language_id')
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
        if (!is_null($this->getCurrentCustomer()) && $this->getCurrentCustomer()->isLogged()) {
            $customer_group_id = $this->getCurrentCustomer()->getCustomerGroupId();
        } else {
            $customer_group_id = $this->getConfig()->get('config_customer_group_id');
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

    /**
     * @param int $productId
     * @return bool|mixed
     */
    public function getProductDiscountsCount($productId) {
        if (!is_null($this->getCurrentCustomer()) && $this->getCurrentCustomer()->isLogged()) {
            $customer_group_id = $this->getCurrentCustomer()->getCustomerGroupId();
        } else {
            $customer_group_id = $this->getConfig()->get('config_customer_group_id');
        }
        $query = $this->getDb()->queryScalar("
		    SELECT COUNT(product_discount_id) AS total
		    FROM product_discount
		    WHERE
		        product_id = :productId
                AND customer_group_id = :customerGroupId
                AND quantity > 1 AND ((date_start = '0000-00-00' OR date_start < :startTime)
                AND (date_end = '0000-00-00' OR date_end > :endTime))
        ", [
            ':productId' => $productId,
            ':customerGroupId' => $customer_group_id,
            ':startTime' => date('Y-m-d H:00:00'),
            ':endTime' => date('Y-m-d H:00:00', strtotime('+1 hour'))
        ]);
        return $query;
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

    public function getProductManufacturers($data = array()) {
        $filter = $this->buildFilter($data);
        $sql = "
            SELECT DISTINCT m.manufacturer_id AS manufacturer_id, p.product_id, m.name AS manufacturer_name, n.text AS link, a.text AS korean_name, u.user_id, u.username AS user_name
            FROM
                product AS p
                LEFT JOIN product_description AS pd ON (p.product_id = pd.product_id)
                LEFT JOIN supplier AS s ON p.supplier_id = s.supplier_id
                LEFT JOIN manufacturer AS m ON p.manufacturer_id = m.manufacturer_id
                LEFT JOIN product_attribute AS n ON (p.product_id = n.product_id AND n.attribute_id=43)
                LEFT JOIN product_attribute AS a ON (p.product_id = a.product_id AND a.attribute_id=42)
                LEFT JOIN user AS u ON p.user_id = u.user_id
                LEFT JOIN product_to_category p2c ON (p.product_id = p2c.product_id)
        ";
        $sql .= $filter->getFilterString(true) .
            " GROUP BY p.product_id" .
            $this->buildLimitString($data['start'], $data['limit']);

        return $this->getDb()->query($sql, $filter->getParams())->rows;
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
                AND p2s.store_id = '" . (int)$this->getConfig()->get('config_store_id') . "'
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
        $query = $this->getDb()->query("SELECT * FROM product_tag WHERE product_id = '" . (int)$product_id . "' AND language_id = '" . (int)$this->getConfig()->get('config_language_id') . "'");

        return $query->rows;
    }

    public function getProductLayoutId($product_id) {
        $query = $this->getDb()->query("SELECT * FROM product_to_layout WHERE product_id = '" . (int)$product_id . "' AND store_id = '" . (int)$this->getConfig()->get('config_store_id') . "'");

        if ($query->num_rows) {
            return $query->row['layout_id'];
        } else {
            return  $this->getConfig()->get('config_layout_product');
        }
    }

    public function getCategories($product_id) {
        $query = $this->getDb()->query("SELECT * FROM product_to_category WHERE product_id = :productId", [ ':productId' => $product_id ]);

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
     * @return DescriptionCollection
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

    public function getTotalProductSpecials() {
        if ($this->getCurrentCustomer()->isLogged()) {
            $customer_group_id = $this->getCurrentCustomer()->getCustomerGroupId();
        } else {
            $customer_group_id = $this->getConfig()->get('config_customer_group_id');
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
                AND p2s.store_id = '" . (int)$this->getConfig()->get('config_store_id') . "'
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
            $customer_group_id = $this->getConfig()->get('config_customer_group_id');
        }

        $cache = md5(http_build_query($data));

        if (isset($data['nocache']))
            $product_data = 0;
        else
            $product_data = $this->getCache()->get('product.' . (int)$this->getConfig()->get('config_language_id') . '.' . (int)$this->getConfig()->get('config_store_id') . '.' . (int)$customer_group_id . '.' . $cache);

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
			        pd.language_id = '" . (int)$this->getConfig()->get('config_language_id') . "'
			        AND p.status = '1'
			        AND p.date_available <= '" . date('Y-m-d H:00:00') . "'
			        AND p2s.store_id = '" . (int)$this->getConfig()->get('config_store_id') . "
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
                        $implode[] = "LCASE(pt.tag) LIKE '%" . $this->getDb()->escape(utf8_strtolower($word)) . "%' AND pt.language_id = '" . (int)$this->getConfig()->get('config_language_id') . "'";
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

                    $categories = CategoryDAO::getInstance()->getCategoriesByParentId($data['filter_category_id']);
                    foreach ($categories as $category) {
                        $implode_data[] = "p2c.category_id = '" . (int)$category->getId() . "'";
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

            $this->getCache()->set('product.' . (int)$this->getConfig()->get('config_language_id') . '.' . (int)$this->getConfig()->get('config_store_id') . '.' . (int)$customer_group_id . '.' . $cache, $product_data);
        }

        return $product_data;
    }


    public function getProductRewards($product_id) {
        $product_reward_data = array();

        $query = $this->getDb()->query("SELECT * FROM product_reward WHERE product_id = :productId", [ ':productId' => $product_id ]);

        foreach ($query->rows as $result) {
            $product_reward_data[$result['customer_group_id']] = array('points' => $result['points']);
        }

        return $product_reward_data;
    }

    /**
     * @param array $data
     * @return array
     */
    public function getProductUserNames($data = array()) {
        $filter = $this->buildFilter($data);
        $sql = "
            SELECT DISTINCT m.manufacturer_id AS manufacturer_id, p.product_id, m.name AS manufacturer_name, n.text AS link, a.text AS korean_name, u.user_id, u.username AS user_name
            FROM
                product AS p
                LEFT JOIN product_description AS pd ON (p.product_id = pd.product_id)
                LEFT JOIN supplier AS s ON p.supplier_id = s.supplier_id
                LEFT JOIN manufacturer AS m ON p.manufacturer_id = m.manufacturer_id
                LEFT JOIN product_attribute AS n ON (p.product_id = n.product_id AND n.attribute_id=43)
                LEFT JOIN product_attribute AS a ON (p.product_id = a.product_id AND a.attribute_id=42)
                JOIN user AS u ON p.user_id = u.user_id
                LEFT JOIN product_to_category AS p2c ON (p.product_id = p2c.product_id)
        ";
        $sql .= $filter->getFilterString(true) .
                " GROUP BY p.product_id" .
                $this->buildLimitString($data['start'], $data['limit']);

        return $this->getDb()->query($sql, $filter->getParams())->rows;
    }

    /**
     * @param Product $product
     * @return Product
     */
    public function saveProduct($product) {
        $this->getDb()->beginTransaction();
        try {
            if ($product->getId() == 0) {
                $this->getDb()->query("
                    INSERT INTO product
                    (model, sku, upc, location, quantity, stock_status_id, image, manufacturer_id, supplier_id, shipping,
                    price, points, tax_class_id, date_available, weight, weight_class_id, length, width, height, 
                    length_class_id, subtract, minimum, sort_order, status, date_added, date_modified, viewed, user_id,
                    afc_id, affiliate_commission, korean_name, supplier_url, image_description)
                    VALUES (
                        :model, :sku, :upc, :location, :quantity, :stockStatusId, :image, :manufacturerId, :supplierId, 
                        :shipping, :price, :points, 0, :dateAvailable, :weight, :weightClassId, :length, :width, :height, 
                        :lengthClassId, :subtract, :minimum, :sortOrder, :status, NOW(), NOW(), 0, :userId, :afcId, 
                        :affiliateCommission, :koreanName, :supplierUrl, :imageDescription
                    )
                    ", [
                    ':model' => $product->getModel(),
                    ':sku' => $product->getSku(),
                    ':upc' => $product->getUpc(),
                    ':location' => $product->getLocation(),
                    ':quantity' => $product->getQuantity(),
                    ':stockStatusId' => $product->getStockStatusId(),
                    ':image' => $product->getImagePath(),
                    ':manufacturerId' => $product->getManufacturerId(),
                    ':supplierId' => $product->getSupplier()->getId(),
                    ':shipping' => $product->getShipping(),
                    ':price' => $product->getPrice(),
                    ':points' => $product->getPoints(),
                    ':dateAvailable' => $product->getDateAvailable(),
                    ':weight' => $product->getWeight()->getWeight(),
                    ':weightClassId' => $product->getWeight()->getUnit()->getId(),
                    ':length' => $product->getDimension()->getLength(),
                    ':width' => $product->getDimension()->getWidth(),
                    ':height' => $product->getDimension()->getHeight(),
                    ':lengthClassId' => $product->getDimension()->getUnit()->getId(),
                    ':subtract' => $product->getSubtract(),
                    ':minimum' => $product->getMinimum(),
                    ':sortOrder' => $product->getSortOrder(),
                    ':status' => $product->getStatus(),
                    ':userId' => $product->getUserId(),
                    ':afcId' => $product->getAfcId(),
                    ':affiliateCommission' => $product->getAffiliateCommission(),
                    ':koreanName' => $product->getKoreanName(),
                    ':supplierUrl' => $product->getSupplierUrl(),
                    ':imageDescription' => $product->getImageDescription()
                ]);
                $product->setId($this->getDb()->getLastId());
            } else {
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
                        ':lengthClassId' => $product->getDimension()->getUnit()->getId(),
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
            }

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

            $this->getCache()->deleteAll('/^product/');
            $this->getDb()->commitTransaction();
        } catch (\Exception $e) {
            $this->getLogger()->write($e->getMessage());
            $this->getLogger()->write($e->getTraceAsString());
            $this->getDb()->rollbackTransaction();
        }
        return $product;
    }

    /**
     * @param int $productId
     */
    private function saveWKAuction($productId) {
        if($this->getConfig()->get('wk_auction_timezone_set')) {
            if (isset($data['auction_min']) && isset($data['auction_max']) && isset($data['auction_end'])) {
                $auct=$this->getDb()->query("SELECT * FROM wkauction WHERE product_id = '" . (int)$productId . "'");

                $auct=$auct->row;

                if(count($auct)!=0){
                    $this->getDb()->query("UPDATE wkauction SET product_id = '" . (int)$productId . "', name = '" .$data['auction_name']. "', min = '" .$data['auction_min'].  "', isauction = '" .$data['isauction'] ."', start_date = '" .$data['auction_start']."', max = '" .$data['auction_max'] . "', end_date = '" .$data['auction_end'] . "' WHERE id ='" .(int)$auct['id'] . "'");
                }
                else{
                    $this->getDb()->query("INSERT INTO wkauction SET product_id = '" . (int)$productId . "', name = '" .$data['auction_name']. "', min = '" .$data['auction_min'].  "', isauction = '" .$data['isauction'] ."', max = '" .$data['auction_max'] ."', start_date = '" .$data['auction_start'] . "', end_date = '" .$data['auction_end'] . "'");
                }
            }
        }
    }

    /**
     * @param Product $product
     */
    private function saveOptions($product) {
        if ($product->isOptionsModified()) {
            $productOptionsExisted = boolval($this->getDb()->query("DELETE FROM product_option WHERE product_id = :productId", [':productId' => $product->getId()]));
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
                if ($productOptionsExisted) {
                    $this->getDb()->query("
                        INSERT INTO product_option
                        SET
                            product_option_id = :productOptionId,
                            product_id = :productId,
                            option_id = :optionId,
                            required = :isRequired,
                            option_value = :optionValue
                        ", $params);
                } else {
                    unset($params[':productOptionId']);
                    $this->getDb()->query("
                        INSERT INTO product_option
                        SET
                            product_id = :productId,
                            option_id = :optionId,
                            required = :isRequired,
                            option_value = :optionValue
                        ", $params);
                }
                $productOption->setId($this->getDb()->getLastId());

                //TODO: Should work regardless of type based on values only
//                if ($productOption->getType() == 'select' || $productOption->getType() == 'radio' ||
//                    $productOption->getType() == 'checkbox' || $productOption->getType() == 'image') {
                $this->saveOptionValues($product, $productOption, $productOptionsExisted);
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
                $this->getDb()->query("
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
            $this->getDb()->query("DELETE FROM product_discount WHERE product_id = '" . (int)$product->getId() . "'");
            foreach ($product->getDiscounts() as $product_discount) {
                $this->getDb()->query("INSERT INTO product_discount SET product_id = '" . (int)$product->getId() . "', customer_group_id = '" . (int)$product_discount['customer_group_id'] . "', quantity = '" . (int)$product_discount['quantity'] . "', priority = '" . (int)$product_discount['priority'] . "', price = '" . (float)$product_discount['price'] . "', date_start = '" . $this->getDb()->escape($product_discount['date_start']) . "', date_end = '" . $this->getDb()->escape($product_discount['date_end']) . "'");
            }
        }
    }

    /**
     * @param Product $product
     */
    private function saveSpecials($product) {
        if ($product->isSpecialsModified()) {
            $this->getDb()->query("DELETE FROM product_special WHERE product_id = '" . (int)$product->getId() . "'");

            foreach ($product->getSpecials() as $product_special) {
                $this->getDb()->query("INSERT INTO product_special SET product_id = '" . (int)$product->getId() . "', customer_group_id = '" . (int)$product_special['customer_group_id'] . "', priority = '" . (int)$product_special['priority'] . "', price = '" . (float)$product_special['price'] . "', date_start = '" . $this->getDb()->escape($product_special['date_start']) . "', date_end = '" . $this->getDb()->escape($product_special['date_end']) . "'");
            }
        }
    }

    /**
     * @param Product $product
     */
    private function saveImages($product) {
        if ($product->isImagesModified()) {
            $this->getDb()->query("DELETE FROM product_image WHERE product_id = '" . (int)$product->getId() . "'");
            foreach ($product->getImages() as $product_image) {
                $this->getDb()->query("INSERT INTO product_image SET product_id = '" . (int)$product->getId() . "', image = '" . $this->getDb()->escape($product_image['image']) . "', sort_order = '" . (int)$product_image['sort_order'] . "'");
            }
        }
    }

    /**
     * @param Product $product
     */
    private function saveDownloads($product) {
        if ($product->isDownloadsModified()) {
            $this->getDb()->query("DELETE FROM product_to_download WHERE product_id = '" . (int)$product->getId() . "'");
            foreach ($product->getDownloads() as $download_id) {
                $this->getDb()->query("INSERT INTO product_to_download SET product_id = '" . (int)$product->getId() . "', download_id = '" . (int)$download_id . "'");
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
                $this->getDb()->query("
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
            $this->getDb()->query("DELETE FROM product_reward WHERE product_id = '" . (int)$product->getId() . "'");
            foreach ($product->getRewards() as $customer_group_id => $value) {
                $this->getDb()->query("INSERT INTO product_reward SET product_id = '" . (int)$product->getId() . "', customer_group_id = '" . (int)$customer_group_id . "', points = '" . (int)$value['points'] . "'");
            }
        }
    }

    /**
     * @param Product $product
     */
    private function saveLayouts($product) {
        if ($product->isLayoutsModified()) {
            $this->getDb()->query("DELETE FROM product_to_layout WHERE product_id = '" . (int)$product->getId() . "'");
            foreach ($product->getLayouts() as $store_id => $layout) {
                if ($layout['layout_id']) {
                    $this->getDb()->query("INSERT INTO product_to_layout SET product_id = '" . (int)$product->getId() . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout['layout_id'] . "'");
                }
            }
        }
    }

    /**
     * @param Product $product
     */
    private function saveTags($product) {
        if ($product->isTagsModified()) {
            $this->getDb()->query("DELETE FROM product_tag WHERE product_id = '" . (int)$product->getId() . "'");
            foreach ($product->getTags() as $language_id => $value) {
                if ($value) {
                    $tags = explode(',', $value);

                    foreach ($tags as $tag) {
                        $this->getDb()->query("INSERT INTO product_tag SET product_id = '" . (int)$product->getId() . "', language_id = '" . (int)$language_id . "', tag = '" . $this->getDb()->escape(trim($tag)) . "'");
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
//            $this->getDb()->query("DELETE FROM url_alias WHERE query = 'product_id=" . (int)$product->getId() . "'");

//            if ($data['keyword']) {
//                $this->getDb()->query("INSERT INTO url_alias SET query = 'product_id=" . (int)$product->getId() . "', keyword = '" . $this->getDb()->escape($data['keyword']) . "'");
//            }
//        }
    }

    /**
     * @param Product $product
     * @param ProductOption $productOption
     * @param bool $productOptionsExisted
     */
    private function saveOptionValues($product, $productOption, $productOptionsExisted) {
        foreach ($productOption->getValue() as $productOptionValue) {
            $productOptionValueParams = [
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
            ];
            if ($productOptionsExisted) {
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
                ", $productOptionValueParams);
            } else {
                unset($productOptionValueParams[':productOptionValueId']);
                $this->getDb()->query("
                    INSERT INTO product_option_value
                    SET
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
                ", $productOptionValueParams);
            }
        }
    }
} 
<?php
/**
 * Created by PhpStorm.
 * User: ralfeus
 * Date: 4/3/2016
 * Time: 9:53 PM
 */

namespace model\sale;

use model\catalog\ProductDAO;
use model\DAO;

class CouponDAO extends DAO{
    public function addCoupon($data) {
        $this->getDb()->beginTransaction();
        $this->getDb()->query("
            INSERT INTO coupon 
            (name, code, discount, type, total, logged, shipping, date_start, date_end, uses_total, uses_customer, status, date_added, applies_to_categories)
            VALUES (:name, :code, :discount, :type, :total, :logged, :shipping, :dateStart, :dateEnd, :usesTotal, :usesCustomer, :status, NOW(), :appliesToCategories)
            ", [
                ":name" => $data['name'],
                ":code" => $data["code"],
                ":discount" => $data["discount"],
                ":type" => $data["type"],
                ":total" => $data["total"],
                ":logged" => $data["logged"],
                ":shipping" => $data["shipping"],
                ":dateStart" => $data["date_start"],
                ":dateEnd" => $data["date_end"],
                ":usesTotal" => $data["uses_total"],
                ":usesCustomer" => $data["uses_customer"],
                ":status" => $data["status"],
                ":appliesToCategories" => isset($data['category'])
        ]);
        $couponId = $this->getDb()->getLastId();
        if (isset($data['category'])) {
            foreach ($data['category'] as $categoryId) {
                $this->getDb()->query("
                    INSERT INTO coupon_product
                    (coupon_id, product_id)
                    VALUES (:couponId, :categoryId)
                    ", [
                        ":couponId" => $couponId,
                        ":categoryId" => $categoryId
                ]);
            }
        } elseif (isset($data['coupon_product'])) {
            foreach ($data['coupon_product'] as $productId) {
                $this->getDb()->query("
                    INSERT INTO coupon_product
                    (coupon_id, product_id)
                    VALUES (:couponId, :productId)
                    ", [
                    ":couponId" => $couponId,
                    ":productId" => $productId
                ]);
            }
        }
        $this->getDb()->commitTransaction();
    }

    /**
     * Returns coupon info in case it's applicable. Used in customer section
     * @param string $code
     * @param bool $chosenProducts
     * @return array
     */
    public function applyCoupon($code, $chosenProducts = false) {
        $status = true;

        $coupon_query = $this->getDb()->query("
		    SELECT *
		    FROM coupon
		    WHERE
		        code = :code
		        AND ((date_start = '0000-00-00' OR date_start < :dateStart)
		        AND (date_end = '0000-00-00' OR date_end > :dateEnd))
		        AND status = 1
            ", [
            ":code" => $code,
            ":dateStart" => date('Y-m-d H:00:00'),
            ":dateEnd" => date('Y-m-d H:00:00', strtotime('+1 hour'))
        ]);

        if ($coupon_query->num_rows) {
            if ($coupon_query->row['total'] >= $this->cart->getSubTotal($chosenProducts)) {
                $status = false;
            }

            $coupon_history_query = $this->getDb()->query("SELECT COUNT(*) AS total FROM `coupon_history` ch WHERE ch.coupon_id = '" . (int)$coupon_query->row['coupon_id'] . "'");

            if ($coupon_query->row['uses_total'] > 0 && ($coupon_history_query->row['total'] >= $coupon_query->row['uses_total'])) {
                $status = false;
            }

            if ($coupon_query->row['logged'] && !$this->customer->getId()) {
                $status = false;
            }

            if ($this->customer->getId()) {
                $coupon_history_query = $this->getDb()->query("SELECT COUNT(*) AS total FROM `coupon_history` ch WHERE ch.coupon_id = '" . (int)$coupon_query->row['coupon_id'] . "' AND ch.customer_id = '" . (int)$this->customer->getId() . "'");

                if ($coupon_query->row['uses_customer'] > 0 && ($coupon_history_query->row['total'] >= $coupon_query->row['uses_customer'])) {
                    $status = false;
                }
            }

            $coupon_product_query = $this->getDb()->query("SELECT * FROM coupon_product WHERE coupon_id = '" . (int)$coupon_query->row['coupon_id'] . "'");
            $coupon_product_data = array_map(
                function($item) {
                    return $item['product_id'];
                },
                $coupon_product_query->rows
            );

            if ($coupon_product_data) {
                $coupon_product = false;
                $productsCouponAppliesTo = [];
                foreach ($this->cart->getProducts($chosenProducts) as $product) {
                    if ($coupon_query->row['applies_to_categories']) {
                        $productCategories = array_map(
                            function ($item) {
                                return $item['category_id'];
                            },
                            ProductDAO::getInstance()->getCategories($product['product_id'])
                        );
                        if (sizeof(array_intersect($productCategories, $coupon_product_data))) {
                            $coupon_product = true;
                            $productsCouponAppliesTo[] = $product['product_id'];
                        }
                    } else {
                        if (in_array($product['product_id'], $coupon_product_data)) {
                            $coupon_product = true;
                            $productsCouponAppliesTo[] = $product['product_id'];
                        }
                    }
                }

                if (!$coupon_product) {
                    $status = false;
                }
            }
        } else {
            $status = false;
        }

        if ($status) {
            return array(
                'coupon_id'     => $coupon_query->row['coupon_id'],
                'code'          => $coupon_query->row['code'],
                'name'          => $coupon_query->row['name'],
                'type'          => $coupon_query->row['type'],
                'discount'      => $coupon_query->row['discount'],
                'shipping'      => $coupon_query->row['shipping'],
                'total'         => $coupon_query->row['total'],
                'product'       => $productsCouponAppliesTo,
                'date_start'    => $coupon_query->row['date_start'],
                'date_end'      => $coupon_query->row['date_end'],
                'uses_total'    => $coupon_query->row['uses_total'],
                'uses_customer' => $coupon_query->row['uses_customer'],
                'status'        => $coupon_query->row['status'],
                'date_added'    => $coupon_query->row['date_added']
            );
        }
    }

    public function editCoupon($couponId, $data) {
        $this->getDb()->beginTransaction();
        $this->getDb()->query("
            UPDATE coupon 
            SET 
                name = :name,
                code = :code,
                discount = :discount,
                type = :type,
                total = :total,
                logged = :logged,
                shipping = :shipping,
                date_start = :dateStart,
                date_end = :dateEnd,
                uses_total = :usesTotal,
                uses_customer = :usesCustomer,
                status = :status,
                applies_to_categories = :appliesToCategories
            WHERE coupon_id = :couponId
            ", [
                ":couponId" => $couponId,
                ":name" => $data['name'],
                ":code" => $data["code"],
                ":discount" => $data["discount"],
                ":type" => $data["type"],
                ":total" => $data["total"],
                ":logged" => $data["logged"],
                ":shipping" => $data["shipping"],
                ":dateStart" => $data["date_start"],
                ":dateEnd" => $data["date_end"],
                ":usesTotal" => $data["uses_total"],
                ":usesCustomer" => $data["uses_customer"],
                ":status" => $data["status"],
                ":appliesToCategories" => isset($data['category'])
        ]);

        $this->getDb()->query("DELETE FROM coupon_product WHERE coupon_id = :couponId", [":couponId" => $couponId]);

        if (isset($data['category'])) {
            foreach ($data['category'] as $categoryId) {
                $this->getDb()->query("
                    INSERT INTO coupon_product
                    (coupon_id, product_id)
                    VALUES (:couponId, :categoryId)
                    ", [
                    ":couponId" => $couponId,
                    ":categoryId" => $categoryId
                ]);
            }
        } elseif (isset($data['coupon_product'])) {
            foreach ($data['coupon_product'] as $productId) {
                $this->getDb()->query("
                    INSERT INTO coupon_product
                    (coupon_id, product_id)
                    VALUES (:couponId, :productId)
                    ", [
                    ":couponId" => $couponId,
                    ":productId" => $productId
                ]);
            }
        }
        $this->getDb()->commitTransaction();
    }

    public function deleteCoupon($coupon_id) {
        $this->db->query("DELETE FROM coupon WHERE coupon_id = '" . (int)$coupon_id . "'");
        $this->db->query("DELETE FROM coupon_product WHERE coupon_id = '" . (int)$coupon_id . "'");
        $this->db->query("DELETE FROM coupon_history WHERE coupon_id = '" . (int)$coupon_id . "'");
    }

    public function getCoupon($coupon_id) {
        $query = $this->db->query("SELECT DISTINCT * FROM coupon WHERE coupon_id = '" . (int)$coupon_id . "'");

        return $query->row;
    }

    public function getCoupons($data = array()) {
        $sql = "SELECT coupon_id, name, code, discount, date_start, date_end, status, applies_to_categories FROM coupon";

        $sort_data = array(
            'name',
            'code',
            'discount',
            'date_start',
            'date_end',
            'status'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY name";
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

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getCouponProducts($coupon_id) {
        $coupon_product_data = array();

        $query = $this->db->query("SELECT * FROM coupon_product WHERE coupon_id = '" . (int)$coupon_id . "'");

        foreach ($query->rows as $result) {
            $coupon_product_data[] = $result['product_id'];
        }

        return $coupon_product_data;
    }

    public function getTotalCoupons() {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM coupon");

        return $query->row['total'];
    }

    public function getCouponHistories($coupon_id, $start = 0, $limit = 10) {
        $query = $this->db->query("SELECT ch.order_id, CONCAT(c.firstname, ' ', c.lastname) AS customer, ch.amount, ch.date_added FROM coupon_history ch LEFT JOIN customer c ON (ch.customer_id = c.customer_id) WHERE ch.coupon_id = '" . (int)$coupon_id . "' ORDER BY ch.date_added ASC LIMIT " . (int)$start . "," . (int)$limit);

        return $query->rows;
    }

    public function getTotalCouponHistories($coupon_id) {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM coupon_history WHERE coupon_id = '" . (int)$coupon_id . "'");

        return $query->row['total'];
    }

    public function redeem($coupon_id, $order_id, $customer_id, $amount) {
        $this->getDb()->query("INSERT INTO `coupon_history` SET coupon_id = '" . (int)$coupon_id . "', order_id = '" . (int)$order_id . "', customer_id = '" . (int)$customer_id . "', amount = '" . (float)$amount . "', date_added = NOW()");
    }
}
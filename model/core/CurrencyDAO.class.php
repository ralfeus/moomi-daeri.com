<?php
namespace model\core;

use model\DAO;

class CurrencyDAO extends DAO {
    /**
     * @param string $currencyCode
     * @return Currency
     */
    public function getCurrency($currencyCode) {
        $tmp = $this->getDb()->query(<<<SQL
            SELECT *
            FROM currency
            WHERE code = ?
SQL
            , array("s:$currencyCode")
        );
        if ($tmp->rows) {
            return new Currency($tmp->row['code'], $tmp->row['title'], $tmp->row['decimal_place'],
                $tmp->row['symbol_left'], $tmp->row['symbol_right']);
        } else {
            return null;
        }
    }

    /**
     * @param string $currencyCode
     * @return float[]
     */
    public function getCurrencyRateHistory($currencyCode) {
        $tmp = $this->getDb()->query(<<<SQL
            SELECT date_added, rate
            FROM
                currency_history AS ch JOIN currency AS c ON c.currency_id = ch.currency_id
            WHERE c.code = ?
            ORDER BY date_added DESC
SQL
            , array("s:$currencyCode")
        );
        $result = array();
        foreach ($tmp->rows as $rateEntry) {
            $result[$rateEntry['date_added']] = $rateEntry['rate'];
        }
        return $result;
    }
} 
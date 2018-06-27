<?php
namespace model\core;

use model\DAO;

class CurrencyDAO extends DAO {
    /**
     * @param string $currencyCode
     * @return Currency
     */
    public function getCurrency($currencyCode = null) { //TODO: Remove optionality of currency code when parent's getCurrency is removed
        $tmp = $this->getDb()->query(<<<SQL
            SELECT *
            FROM currency
            WHERE code = :currencyCode
SQL
            , [ ":currencyCode" => $currencyCode ]
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


    /**
     * @param bool $onlyEnabled Set to get only enabled currencies
     * @return array
     */
    public function getCurrencies($onlyEnabled = false) {
        $currency_data = $this->getCache()->get('currencies');

        if (!$currency_data) {
            $currency_data = array();

            $query = $this->getDb()->query("SELECT * FROM currency ORDER BY title ASC");

            foreach ($query->rows as $result) {
                $currency_data[$result['code']] = array(
                    'currency_id'   => $result['currency_id'],
                    'title'         => $result['title'],
                    'code'          => $result['code'],
                    'symbol_left'   => $result['symbol_left'],
                    'symbol_right'  => $result['symbol_right'],
                    'decimal_place' => $result['decimal_place'],
                    'value'         => $result['value'],
                    'status'        => $result['status'],
                    'date_modified' => $result['date_modified']
                );
            }

            $this->getCache()->set('currencies', $currency_data);
        }
        if ($onlyEnabled)
        {
            $tmpCurrencies = array();
            foreach ($currency_data as $currency)
                if ($currency['status'])
                    $tmpCurrencies[] = $currency;
            $currency_data = $tmpCurrencies;
        }

        return $currency_data;
    }
} 
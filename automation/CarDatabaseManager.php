<?php
/**
 * Created by PhpStorm.
 * User: ralfeus
 * Date: 27. 10. 2015
 * Time: 11:29
 */

namespace automation;


class CarDatabaseManager extends DatabaseManager {
    private function __construct() {
        $this->dbName = 'test_cars';
        $this->dbConnect();
    }

    public function addProducts($products) {
        parent::addProducts($products);
        $this->dbName = 'cars';
        $sql = <<<SQL
                INSERT INTO TDM_PRICES
                SET
                    BKEY = :bkey,
                    AKEY = :akey,
                    ARTICLE = :article,
                    ALT_NAME = :alt_name,
                    BRAND = :brand,
                    PRICE = :price,
                    TYPE = 1,
                    CURRENCY = 'KRW',
                    `DAY` = 0,
                    AVAILABLE = 0,
                    SUPPLIER = :supplier,
                    STOCK = '',
                    `OPTIONS` = '0;0;0;0;0;0;0;0;;;0;0;0;0',
                    `CODE` = :code,
                    `DATE` = UNIX_TIMESTAMP(DATE(NOW()))
                ON DUPLICATE KEY UPDATE
                    ARTICLE = :article,
                    ALT_NAME = :alt_name,
                    BRAND = :brand,
                    PRICE = :price,
                    `DATE` = UNIX_TIMESTAMP(DATE(NOW()))
SQL;
        $statement = $this->connection->prepare($sql);
        echo date('Y-m-d H:i:s') . " Adding to the cars database " . count($products) . " products\n";
        /** @var CarProduct $product */
        foreach ($products as $product) {
//            echo date('Y-m-d H:i:s') . " Adding " . $product->sourceProductId . "\n";
            $statement->execute(array(
                ':bkey' => strtoupper($product->brand),
                ':akey' => preg_replace('/\s+/', '', $product->partNumbers[0]),
                ':article' => $product->partNumbers[0],
                ':alt_name' => $product->name,
                ':brand' => $product->brand,
                ':price' => $product->price,
                ':supplier' => $product->supplier,
                ':code' => strtolower($product->supplier)
            ));
        }
        echo date('Y-m-d H:i:s') . " Added data to cars database\n";
    }
}
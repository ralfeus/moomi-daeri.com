CREATE TABLE  `imported_product_images` (
 `imported_product_image_id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
 `imported_product_id` INT( 11 ) NOT NULL ,
 `url` VARCHAR( 512 ) NOT NULL ,
PRIMARY KEY (  `imported_product_image_id` ) ,
KEY  `imported_product_id` (  `imported_product_id` )
);

CREATE TABLE  `imported_products` (
  `imported_product_id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
  `product_id` INT( 11 ) DEFAULT NULL ,
  `source_site_id` INT( 11 ) NOT NULL ,
  `source_url` VARCHAR( 512 ) NOT NULL ,
  `source_product_id` VARCHAR( 64 ) NOT NULL ,
  `image_url` VARCHAR( 512 ) NOT NULL ,
  `name` VARCHAR( 128 ) NOT NULL ,
  `description` VARCHAR( 1024 ) DEFAULT NULL ,
  `price` INT( 11 ) NOT NULL ,
  `time_modified` DATETIME NOT NULL ,
  PRIMARY KEY (  `imported_product_id` ) ,
  KEY  `source_site_id` (  `source_site_id` )
);

CREATE TABLE  `imported_source_sites` (
  `imported_source_site_id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR( 64 ) NOT NULL ,
  `default_manufacturer_id` INT( 11 ) NOT NULL ,
  `default_supplier_id` INT( 11 ) NOT NULL ,
  `default_category_id` INT( 11 ) NOT NULL ,
  `default_store_id` INT( 11 ) NOT NULL ,
  PRIMARY KEY (  `imported_source_site_id` ) ,
  KEY  `default_category_id` (  `default_category_id` ) ,
  KEY  `default_store_id` (  `default_store_id` ) ,
  KEY  `default_manufacturer_id` (  `default_manufacturer_id` ) ,
  KEY  `default_supplier_id` (  `default_supplier_id` )
);
INSERT INTO `imported_source_sites` (`imported_source_site_id`, `name`) VALUES (NULL, 'www.naturerepublic.co.kr');
UPDATE `imported_source_sites` SET `default_manufacturer_id` = '21' WHERE `imported_source_sites`.`imported_source_site_id` = 1;
UPDATE `imported_source_sites` SET `default_supplier_id` = '37' WHERE `imported_source_sites`.`imported_source_site_id` = 1;
UPDATE `imported_source_sites` SET `default_category_id` = '73' WHERE `imported_source_sites`.`imported_source_site_id` = 1;


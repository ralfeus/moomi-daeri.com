<?php
define('URL_PATTERN', '/https?:\/\/([\w\-\.]+)/');
/// Add credit requests statuses
define('ADD_CREDIT_STATUS_PENDING', 0x00010001);
define('ADD_CREDIT_STATUS_ACCEPTED', 0x00010002);
define('ADD_CREDIT_STATUS_REJECTED', 0x00010003);

/// Invoice statuses
define('IS_AWAITING_CUSTOMER_CONFIRMATION', 1);
define('IS_AWAITING_PAYMENT', 2);
define('IS_PAID', 3);

/// Order statuses (since used by scripts should be hard coded. Visual presentation in different languages are in database)
define('ORDER_STATUS_IN_PROGRESS', 2);
define('ORDER_STATUS_FINISHED', 17);

/// System messages identifiers
define('SYS_MSG_ADD_CREDIT', 1); // add credit request
define('SYS_MSG_INVOICE_CREATED', 2); // invoice creation notification

/// Order item statuses
define('GROUP_ORDER_ITEM_STATUS', 0x0005);
define('ORDER_ITEM_STATUS_WAITING', 0x00050001);
define('ORDER_ITEM_STATUS_PREPARE', 0x00050002);
define('ORDER_ITEM_STATUS_READY', 0x00050003);
define('ORDER_ITEM_STATUS_FINISH', 0x00050004);
define('ORDER_ITEM_STATUS_ORDERED', 0x00050005);
define('ORDER_ITEM_STATUS_PACKED', 0x00050006);
define('ORDER_ITEM_STATUS_SOLDOUT', 0x00050064);
define('ORDER_ITEM_STATUS_CANCELLED', 0x00050065);

define('REPURCHASE_ORDER_MODEL_NAME', 'Agent service');
define('GROUP_REPURCHASE_ORDER_ITEM_STATUS', 0x0006);
define('REPURCHASE_ORDER_ITEM_STATUS_WAITING', 0x00060001);
define('REPURCHASE_ORDER_ITEM_STATUS_OFFER', 0x00060002);
define('REPURCHASE_ORDER_ITEM_STATUS_ACCEPTED', 0x00060003);
define('REPURCHASE_ORDER_ITEM_STATUS_REJECTED', 0x00060004);
define('REPURCHASE_ORDER_ITEM_STATUS_ORDERED', 0x00060007);
define('REPURCHASE_ORDER_ITEM_STATUS_READY', 0x00060005);
define('REPURCHASE_ORDER_ITEM_STATUS_PACKED', 0x00060009);
define('REPURCHASE_ORDER_ITEM_STATUS_FINISH', 0x00060006);
define('REPURCHASE_ORDER_ITEM_STATUS_SOLDOUT', 0x00060008);
define('REPURCHASE_ORDER_ITEM_STATUS_CANCELLED', 0x00060065);

define("REPURCHASE_ORDER_PRODUCT_ID", 8608); // ID of the product serving as repurchase order
define("REPURCHASE_ORDER_IMAGE_URL_OPTION_ID", 14967); // ID of the product option representing image URL
define("REPURCHASE_ORDER_ITEM_NAME_OPTION_ID", 103067); // ID of the product option representing item name
define("REPURCHASE_ORDER_ITEM_URL_OPTION_ID", 14968); // ID of the product option representing item URL
define("REPURCHASE_ORDER_COMMENT_OPTION_ID", 14969); // ID of the product option representing customer comment
define("REPURCHASE_ORDER_COLOR_OPTION_ID", 14970); // ID of the product option representing item color
define("REPURCHASE_ORDER_SHOP_NAME_OPTION_ID", 103066); // ID of the product option representing source shop name
define("REPURCHASE_ORDER_SIZE_OPTION_ID", 14971); // ID of the product option representing item size
define("REPURCHASE_ORDER_WHO_BUYS_OPTION_ID", 18518); // ID of the product option representing buying side
define('REPURCHASE_ORDER_CUSTOMER_BUYS_OPTION_VALUE_ID', 35883); // ID of the product option value representing customer buying
define('REPURCHASE_ORDER_SHOP_BUYS_OPTION_VALUE_ID', 35882); // ID of the product option value representing shop buying

define('CMS_ORDER_CONFIRMATION', 0x00070001);
define('CMS_SITE_GUIDE', 0x00070002);

define('GROUP_ORDER_STATUS', 0x0008);
define('ORDER_STATUS_FINISH', 0x00080011);
define('ORDER_STATUS_READY_TO_SHIP', 0x00080012
);

/// Definition of search string weight in different product properties
define('SEARCH_WEIGHT_FULL_PHRASE_MODEL', 10000); // Full search phrase in product model
define('SEARCH_WEIGHT_FULL_PHRASE_NAME', 10000); // Full search phrase in product name
define('SEARCH_WEIGHT_FULL_PHRASE_TAG', 1000); // Full search phrase in product tag
define('SEARCH_WEIGHT_FULL_PHRASE_DESCR', 100); // Full search phrase in product description
define('SEARCH_WEIGHT_WORD_MODEL', 10); // One of search words in product model
define('SEARCH_WEIGHT_WORD_NAME', 10); // One of search words in product name
define('SEARCH_WEIGHT_WORD_TAG', 5); // One of search words in product tag
define('SEARCH_WEIGHT_WORD_DESCR', 1); // One of search words in product description

/// Options
define('OPTION_MEMO_OPTION_ID', 18);

/// Miscellanious
define('IMPORT_PRICE_RATE_NORMAL_CUSTOMERS', 1.03);
define('IMPORT_PRICE_RATE_WHOLESALES_CUSTOMERS', 1);
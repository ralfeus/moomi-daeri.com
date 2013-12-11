<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dev
 * Date: 14.1.13
 * Time: 20:56
 * To change this template use File | Settings | File Templates.
 */
/// 0x AA BB CC DD
///    || || || ||
///    || || || --- reserved for future use
///    || || --- operation code
///    || --- component code
///    --- user category code ('admin', 'user')
define('AUDIT_ADMIN_PRODUCT_CREATE', 0x01010100);
define('AUDIT_ADMIN_PRODUCT_UPDATE', 0x01010200);
define('AUDIT_ADMIN_PRODUCT_DELETE', 0x01010300);
define('AUDIT_ADMIN_PRODUCT_ENABLE', 0x01010400);
define('AUDIT_ADMIN_PRODUCT_DISABLE', 0x01010500);
define('AUDIT_ADMIN_TRANSACTION_ADD', 0x01020100);
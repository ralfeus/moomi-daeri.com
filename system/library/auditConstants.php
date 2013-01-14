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
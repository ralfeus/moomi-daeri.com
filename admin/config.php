<?php
// HTTP
define('HTTP_SERVER', '/admin/');
define('HTTP_CATALOG', '/');
define('HTTP_IMAGE', '/image/');

// HTTPS
define('HTTPS_SERVER', '/admin/');
define('HTTPS_CATALOG', '/');
define('HTTPS_IMAGE', '/image/');

// DIR
define('DIR_APPLICATION', $_SERVER["DOCUMENT_ROOT"] . '/admin/');
define('DIR_SYSTEM', $_SERVER["DOCUMENT_ROOT"] . '/system/');
define('DIR_DATABASE', $_SERVER["DOCUMENT_ROOT"] . '/system/database/');
define('DIR_LANGUAGE', $_SERVER["DOCUMENT_ROOT"] . '/admin/language/');
define('DIR_TEMPLATE', $_SERVER["DOCUMENT_ROOT"] . '/admin/view/template/');
define('DIR_CONFIG', $_SERVER["DOCUMENT_ROOT"] . '/system/config/');
define('DIR_IMAGE', $_SERVER["DOCUMENT_ROOT"] . '/image/');
define('DIR_CACHE', $_SERVER["DOCUMENT_ROOT"] . '/system/cache/');
define('DIR_DOWNLOAD', $_SERVER["DOCUMENT_ROOT"] . '/download/');
define('DIR_LOGS', $_SERVER["DOCUMENT_ROOT"] . '/system/logs/');
define('DIR_CATALOG', $_SERVER["DOCUMENT_ROOT"] . '/catalog/');

// DB
define('DB_DRIVER', 'mysql');
define('DB_HOSTNAME', 'eu-cdbr-azure-west-a.cloudapp.net');
define('DB_USERNAME', 'b583cee7586d89');
define('DB_PASSWORD', '182afe14');
define('DB_DATABASE', 'moomidae');
define('DB_PREFIX', '');
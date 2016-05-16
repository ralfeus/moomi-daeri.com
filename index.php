<?php

//ini_set('display_errors', 1);
//ini_set('log_errors', 1);
//error_reporting(E_ALL);

// Version
define('VERSION', '1.5.1.3');
// Config
require_once('config.php');
/// Constants
require_once(DIR_SYSTEM . 'engine/constants.php');

// Install
if (!defined('DIR_APPLICATION')) {
	header('Location: install/index.php');
	exit;
}

// Startup
require_once(DIR_SYSTEM . 'startup.php');

// Application Classes
require_once(DIR_SYSTEM . 'library/customer.php');
require_once(DIR_SYSTEM . 'library/affiliate.php');
require_once(DIR_SYSTEM . 'library/currency.php');
require_once(DIR_SYSTEM . 'library/tax.php');
require_once(DIR_SYSTEM . 'library/weight.php');
require_once(DIR_SYSTEM . 'library/length.php');
require_once(DIR_SYSTEM . 'library/cart.php');

/** Register loader for class files. The function is called when
 * new operator is called but the class definition is not found.
 */
spl_autoload_register(function($class) {
	if ((strpos($class, '\\') !== false) && (strpos($class, '\\') > 0)) {
		$classPath = DIR_ROOT . preg_replace('/\\\\/', '/', $class) . '.class.php';
	} else if (strpos($class, '\\') == 0) { // legacy classes
		$classPath = DIR_SYSTEM . 'library/' . str_replace('\\', '', strtolower($class)) . '.php';
	} else {
		return false;
	}
	include($classPath);
	if (!class_exists($class)) {
		throw new ErrorException("Class $class was not found");
	}
	return true;
});


// Registry
$registry = new Registry();

// Cache
$cache = new Cache();
$registry->set('cache', $cache);

// Loader
$loader = new Loader($registry);
$registry->set('load', $loader);

// Config
$config = new Config();
$registry->set('config', $config);

// Database
$db = DB::getDB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
$registry->set('db', $db);

// Store
if (isset($_SERVER['HTTPS']) && (($_SERVER['HTTPS'] == 'on') || ($_SERVER['HTTPS'] == '1'))) {
	$store_query = $db->query("
		SELECT * FROM store WHERE `ssl` = :url
		", [':url' => 'https://' . str_replace('www.', '', $_SERVER['HTTP_HOST']) . rtrim(dirname($_SERVER['PHP_SELF']), '/.\\') . '/']
	);
} else {
	$store_query = $db->query("
		SELECT * FROM store WHERE `url` = :url
		", [':url' => 'http://' . str_replace('www.', '', $_SERVER['HTTP_HOST']) . rtrim(dirname($_SERVER['PHP_SELF']), '/.\\') . '/']
	);
}
if ($store_query->num_rows) {
	$config->set('config_store_id', $store_query->row['store_id']);
} else {
	$config->set('config_store_id', 0);
}

// Settings
$settingsRows = $cache->get('setting.' . $config->get('config_store_id'));
if (is_null($settingsRows)) {
	$query = $db->query("SELECT * FROM setting WHERE store_id = '0' OR store_id = '" . (int)$config->get('config_store_id') . "' ORDER BY store_id ASC");
	$settingsRows = $query->rows;
	$cache->set('setting.' . $config->get('config_store_id'), $settingsRows);
}
foreach ($settingsRows as $setting) {
	if (!$setting['serialized']) {
		$config->set($setting['key'], $setting['value']);
	} else {
		$config->set($setting['key'], unserialize($setting['value']));
	}
}

if (!$store_query->num_rows) {
	$config->set('config_url', HTTP_SERVER);
	$config->set('config_ssl', HTTPS_SERVER);
}

// Url
$url = new Url($config->get('config_url'), $config->get('config_use_ssl') ? $config->get('config_ssl') : $config->get('config_url'));
$registry->set('url', $url);

// Log
$log = new Log($config->get('config_error_filename'));
$registry->set('log', $log);

function error_handler($errno, $errstr, $errfile, $errline) {
	global $log, $config;

	switch ($errno) {
		case E_NOTICE:
		case E_USER_NOTICE:
			$error = 'Notice';
			break;
		case E_WARNING:
		case E_USER_WARNING:
			$error = 'Warning';
			break;
		case E_ERROR:
		case E_USER_ERROR:
			$error = 'Fatal Error';
			break;
		default:
			$error = 'Unknown';
			break;
	}

	if (error_reporting() && $config->get('config_error_display')) {
		echo '<b>' . $error . '</b>: ' . $errstr . ' in <b>' . $errfile . '</b> on line <b>' . $errline . '</b>';
	}

	if ($config->get('config_error_log')) {
		$log->write('PHP ' . $error . ':  ' . $errstr . ' in ' . $errfile . ' on line ' . $errline);
	}

	return true;
}

// Error Handler
set_error_handler('error_handler');

// Request
$request = new Request();
$registry->set('request', $request);

// Response
$response = new Response();
$response->addHeader('Content-Type: text/html; charset=utf-8');
$response->setCompression($config->get('config_compression'));
$registry->set('response', $response);



// Session
$session = new Session();
$registry->set('session', $session);

// Language Detection
$languages = $cache->get('languages');
if (is_null($languages)) {
	$query = $db->query("SELECT * FROM language");
	$languages = [];
	foreach ($query->rows as $result) {
		$languages[$result['code']] = $result;
	}
	$cache->set('languages', $languages);
}

$detect = '';

if (isset($request->server['HTTP_ACCEPT_LANGUAGE']) && ($request->server['HTTP_ACCEPT_LANGUAGE'])) {
	$browser_languages = explode(',', $request->server['HTTP_ACCEPT_LANGUAGE']);

	foreach ($browser_languages as $browser_language) {
		foreach ($languages as $key => $value) {
			if ($value['status']) {
				$locale = explode(',', $value['locale']);

				if (in_array($browser_language, $locale)) {
					$detect = $key;
				}
			}
		}
	}
}

if (isset($request->get['language']) && array_key_exists($request->get['language'], $languages) && $languages[$request->get['language']]['status']) {
	$code = $request->get['language'];
} elseif (isset($session->data['language']) && array_key_exists($session->data['language'], $languages)) {
	$code = $session->data['language'];
} elseif (isset($request->cookie['language']) && array_key_exists($request->cookie['language'], $languages)) {
	$code = $request->cookie['language'];
} elseif ($detect) {
	$code = $detect;
} else {
	$code = $config->get('config_language');
}

if (!isset($session->data['language']) || $session->data['language'] != $code) {
	$session->data['language'] = $code;
}

if (!isset($request->cookie['language']) || $request->cookie['language'] != $code) {
	setcookie('language', $code, time() + 60 * 60 * 24 * 30, '/', $request->server['HTTP_HOST']);
}

$config->set('config_language_id', $languages[$code]['language_id']);
$config->set('config_language', $languages[$code]['code']);

// Language
$language = new Language($languages[$code]['directory'], $languages[$code]['language_id']);
try {
    $language->load($languages[$code]['filename']);
} catch (Exception $exc) {
    $language = new Language($languages[$config->get('config_admin_language')]['directory'], $languages[$config->get('config_admin_language')]['language_id']);
    $language->load($languages[$config->get('config_admin_language')]['filename']);
}
$registry->set('language', $language);

// Document
$document = new Document();
$registry->set('document', $document);

// Customer
$registry->set('customer', new Customer($registry));

// Affiliate
$affiliate = new Affiliate($registry);
$registry->set('affiliate', $affiliate);

if (isset($request->get['tracking']) && !isset($request->cookie['tracking'])) {
	setcookie('tracking', $request->get['tracking'], time() + 3600 * 24 * 1000, '/');
}
// Currency
$registry->set('currency', new Currency($registry));

// Tax
$tax = new Tax($registry);
$registry->set('tax', $tax);

// Weight
$registry->set('weight', new Weight($registry));

// Length
$registry->set('length', new Length($registry));

// Cart
$registry->set('cart', new Cart($registry));

// Front Controller
$controller = new Front($registry);

// Maintenance Mode
$controller->addPreAction(new Action('common/maintenance'));

// SEO URL's
if (!$seo_type = $config->get('config_seo_url_type')) {
	$seo_type = 'seo_url';
}
$controller->addPreAction(new Action('common/' . $seo_type));
$controller->addPreAction(new Action('common/up/cc'));

// Router
if (isset($request->get['route'])) {
	$action = new Action($request->get['route']);
} else {
	$action = new Action('common/home');
}

// Dispatch
$controller->dispatch($action, new Action('error/not_found'));

// Output
$response->output();
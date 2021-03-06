<?php 
// Version
use system\engine\Loader;
use system\engine\Registry;
use system\library\Config;
use system\library\Currency;
use system\library\DB;
use system\library\Language;
use system\library\Log;
use system\library\Session;
use system\library\User;
use system\library\WeightOld;

define('VERSION', '1.5.1.3');
// ============= Class autoload ===============================================
require_once('../vendor/autoload.php');
$class_map = require_once '../vendor/composer/autoload_classmap.php';
$new_class_map = array();
foreach ($class_map as $class => $file)
    $new_class_map [strtolower($class)] = $file;
unset($class_map);
spl_autoload_register(function ($class)use($new_class_map)
{
    $class = strtolower($class);
    if (isset($new_class_map[$class]))
    {
        require_once $new_class_map[$class];
        return true;
    }
    else
        return false;
}, true, false);
unset($new_class_map);
// ============================================================================
// Configuration
require_once('config.php');
/// Constants
require_once(DIR_SYSTEM . 'engine/constants.php');

// Install 
if (!defined('DIR_APPLICATION')) {
	header('Location: ../install/index.php');
	exit;
}

/** Register loader for class files. The function is called when
 * new operator is called but the class definition is not found.
 */
//spl_autoload_register(function($class) {
//    if ((strpos($class, '\\') !== false) && (strpos($class, '\\') > 0)) {
//        $classPath = DIR_ROOT . preg_replace('/\\\\/', '/', $class) . '.class.php';
//    } else if (strpos($class, '\\') == 0) { // legacy classes
//		$classPath = DIR_SYSTEM . 'library/' . str_replace('\\', '', strtolower($class)) . '.php';
//    } else {
//        return false;
//    }
//    include($classPath);
//	if (!class_exists($class)) {
//		throw new ErrorException("Class $class was not found");
//	}
//	return true;
//});

// Startup
require_once(DIR_SYSTEM . 'startup.php');

// Application Classes
require_once(DIR_SYSTEM . 'library/currency.php');
require_once(DIR_SYSTEM . 'library/user.php');
//require_once(DIR_SYSTEM . 'library/WeightOld.php');
require_once(DIR_SYSTEM . 'library/length.php');

// Registry
$registry = new Registry();

// Loader
$loader = new Loader($registry);
$registry->set('load', $loader);

// Config
$config = new Config();
$registry->set('config', $config);

// Database
$db = DB::getDB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
$registry->set('db', $db);
		
// Settings
$query = $db->query("SELECT * FROM setting WHERE store_id = '0'");
 
foreach ($query->rows as $setting) {
	if (!$setting['serialized']) {
		$config->set($setting['key'], $setting['value']);
	} else {
		$config->set($setting['key'], unserialize($setting['value']));
	}
}

// Url
$url = new Url(HTTP_SERVER, $config->get('config_use_ssl') ? HTTPS_SERVER : HTTP_SERVER);	
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
		
	if ($config->get('config_error_display')) {
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
$registry->set('response', $response); 

// Cache
$cache = (new ReflectionClass('system\library\\' . CACHE))->newInstance();
$registry->set('cache', $cache); 

// Session
$session = new Session();
$registry->set('session', $session); 

// Language
$languages = array();

$query = $db->query("SELECT * FROM language");

foreach ($query->rows as $result) {
	$languages[$result['code']] = $result;
}

$config->set('config_language_id', $languages[$config->get('config_admin_language')]['language_id']);

// Language	
$language = new Language($languages[$config->get('config_admin_language')]['directory'], $languages[$config->get('config_admin_language')]['language_id']);
$language->load($languages[$config->get('config_admin_language')]['filename']);
$registry->set('language', $language); 		

// Document
$document = new Document();
$registry->set('document', $document); 		
		
// Currency
$registry->set('currency', new Currency($registry));		
		
// Weight
$registry->set('weight', new WeightOld($registry));

// Length
$registry->set('length', new Length($registry));

// User
$registry->set('user', new User($registry));
						
// Front Controller
$controller = new Front($registry);

// Login
$controller->addPreAction(new Action('common/home/login'));

// Permission
$controller->addPreAction(new Action('common/home/permission'));

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
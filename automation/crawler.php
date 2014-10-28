<?php
use automation\DatabaseManager;
use model\extension\ImportSourceSiteDAO;

require_once("simple_html_dom.php");
require_once('../config.php');

spl_autoload_register(function($class) {
    if (strpos($class, '\\') !== false) {
        $class = DIR_ROOT . preg_replace('/\\\\/', '/', $class) . '.class.php';
    } else {
        return false;
    }
    include($class);
    return true;
});

function handleError($errno, $errstr, $errfile, $errline, array $errcontext) {
    // error was suppressed with the @-operator
    if (0 === error_reporting()) {
        return false;
    }

    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}

if ($sites = trim(file_get_contents("crawler.lck"))) {
    unlink("crawler.lck");
    fclose(STDIN);
    fclose(STDOUT);
    fclose(STDERR);
    $STDIN = fopen('/dev/null', 'r');
    $STDOUT = fopen('import.log', 'wb');
    $STDERR = fopen('import.error.log', 'wb');

    echo date('Y-m-d H:i:s') . " Starting\n";
    $startTime = time();
    foreach (explode(',', $sites) as $siteId) {
        $site = ImportSourceSiteDAO::getInstance()->getSourceSite((int)$siteId);
        echo date('Y-m-d H:i:s') . " Crawling " . $site->getClassName() . "\n";
        $className = 'automation\\SourceSite\\' . $site->getClassName();
        DatabaseManager::getInstance()->addProducts($className::getInstance());
        DatabaseManager::getInstance()->cleanup($className::getInstance(), $startTime);
    }
}

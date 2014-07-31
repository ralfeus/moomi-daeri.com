<?php
if (php_sapi_name() != 'cli') {
    die();
}
chdir(dirname(__FILE__));
$log = date("YmdHis.") . getmypid() . '.log';
shell_exec("php -f crawler.php > logs/$log 2>&1");
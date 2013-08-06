<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dev
 * Date: 6.8.13
 * Time: 10:27
 * To change this template use File | Settings | File Templates.
 */
if (!shell_exec('ps axo cmd | grep "^php crawler.php"')) {
    shell_exec('cd /var/www/moomi-daeri.com/automation && nohup php crawler.php &');
}
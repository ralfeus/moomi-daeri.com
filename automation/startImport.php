<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dev
 * Date: 6.8.13
 * Time: 10:27
 * To change this template use File | Settings | File Templates.
 */
echo shell_exec('ps axo cmd | grep "^php crawler.php"');
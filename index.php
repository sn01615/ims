<?php
$yii = dirname(__FILE__) . '/framework/yii.php';
$config = dirname(__FILE__) . '/protected/config/main.php';

// 是否开启调试模式
defined('YII_DEBUG') or define('YII_DEBUG', true);

define('BASE_PATH', dirname(__FILE__));

// $alowIps = array(
//     '::1',
//     '127.0.0.1',
//     '192.168.1.30',
//     '192.168.1.134',
//     '192.168.188.1',
//     '192.168.188.128',
//     '192.168.188.130'
// );

// if (array_search($_SERVER["REMOTE_ADDR"], $alowIps) === false) {
//     die(':(');
// }

require_once ($yii);

Yii::createWebApplication($config)->run();

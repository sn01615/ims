<?php
$yii = dirname(__FILE__) . '/framework/yii.php';
$config = dirname(__FILE__) . '/protected/config/main.php';

// 是否开启调试模式
defined('YII_DEBUG') or define('YII_DEBUG', false);

define('BASE_PATH', dirname(__FILE__));

set_time_limit(600);

php_sapi_name() == 'cli' or die(0);

require_once ($yii);

if (isset($argv[1])) {
    $_GET['r'] = $argv[1];
} else {
    die('缺少参数');
}

Yii::createWebApplication($config)->run();

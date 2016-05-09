<?php
$yii = dirname(__FILE__) . '/framework/yii.php';
$config = dirname(__FILE__) . '/protected/config/main.php';

define('BASE_PATH', dirname(__FILE__));

set_time_limit(800);

php_sapi_name() == 'cli' or die(0);

require_once ($yii);

if (isset($argv[1])) {
    $_GET['r'] = $argv[1];
} else {
    die('缺少参数');
}

// die('ims updateing.');

Yii::createWebApplication($config)->run();

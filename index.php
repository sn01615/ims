<?php
$yii = dirname(__FILE__) . '/framework/yii.php';
$config = dirname(__FILE__) . '/protected/config/main.php';

define('BASE_PATH', dirname(__FILE__));

require_once ($yii);

// header("Content-type: text/html; charset=utf-8");
// die('系统维护中。。。');

Yii::createWebApplication($config)->run();

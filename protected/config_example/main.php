<?php
return array(
    'basePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..',
    'name' => 'IMS',
    'defaultController' => 'Home',
    'onBeginRequest' => array(
        'imsTool',
        'ipAlow'
    ),
    'import' => array(
        'application.models.*',
        'application.components.*',
        'application.extensions.*',
        'application.extensions.smarty.sysplugins.*',
        'application.enum.*',
        'application.dao.*',
        'ext.sdk_trading_905.*',
        'ext.phpQuery.*',
        'ext.QueryPath.*'
    ),
    'language' => 'zh_cn',
    'modules' => array(),
    'preload' => array(
        'log'
    ),
    'components' => array(
        'user' => array(
            'allowAutoLogin' => true
        ),
        'smarty' => array(
            'class' => 'application.extensions.CSmarty'
        ),
        
        'urlManager' => array(
            'caseSensitive' => false
        ),
        'db' => array(
            'connectionString' => 'mysql:host=localhost;dbname=ims',
            'emulatePrepare' => true,
            'username' => '',
            'password' => '',
            'charset' => 'utf8',
            'tablePrefix' => ''
        ),
        'log' => array(
            'class' => 'CLogRouter',
            'routes' => array(
                array(
                    'class' => 'CFileLogRoute',
                    'levels' => 'info, error, warning'
                ),
                array(
                    'class' => 'CEmailLogRoute2',
                    'levels' => 'info, error, warning',
                    'emails' => array(
                        '姓名' => 'long.yang@xytinc.com'
                    )
                )
            )
        ),
        'session' => array(
            'class' => 'EMongoDbHttpSession',
            'connectionString' => '127.0.0.1:27017',
            'dbName' => 'ImsSession',
            'collectionName' => 'yiisession',
            'idColumn' => 'id',
            'dataColumn' => 'data',
            'expireColumn' => 'expire',
            'mongoTimeout' => 30000
        )
    ),
    'params' => require (dirname(__FILE__) . '/params.php')
);

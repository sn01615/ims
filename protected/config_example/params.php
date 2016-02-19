<?php
return array(
    'server_desc' => 'Dev-localhost',
    'home_url' => 'http://127.0.0.1/ims/',
    'ecs_api_url' => 'http://ecs.ecsfu.com/index.php',
    'imsApiUrl' => 'http://127.0.0.1/ims/',
    'CK1ApiUrl' => 'http://dms.ecsfu.com/logistics/ck1part',
    'TopicApiUrl' => 'http://dms.ecsfu.com:8001/sandbox/nlp/ims/getmessagetopic',
    'upload_url' => 'uploads/',
    'picture_url' => 'datapic/',
    'ebay_api_production' => true, // 调用ebay API时是否使用production配置
    'imsTokenTool_mkey' => '', // 密匙
    'imsTokenTool_error' => 300, // 允许误差
    'entriesPerPage' => 50,
    'online_env' => false,
    'mongodb_conn' => array(
        'default' => array(
            "username" => 'admin',
            "password" => '123456',
            "ip" => '127.0.0.1',
            "port" => '27017'
        )
    ),
    'redis_conn' => array(
        'default' => array(
            'ip' => '127.0.0.1',
            'port' => '6379',
            'password' => ''
        )
    ),
    'memcache_conn' => array(
        'default' => array(
            'ip' => '127.0.0.1',
            'port' => 11211
        )
    ),
    
    // 开发者账号
    'devIDinfo' => array(
        'devID' => '',
        'appID' => '',
        'certID' => ''
    ),
    'eBayRuName' => '',
    'logmails' => array(
        '姓名' => 'long.yang@xytinc.com'
    ),
    'tongji' => array(
        '姓名' => 'long.yang@xytinc.com'
    ),
    'smtp_config' => array(
        'default' => array(
            'server' => 'smtp.exmail.qq.com',
            'port' => 25,
            'username' => 'ims@xytinc.com',
            'password' => '',
            'from' => 'ims@xytinc.com'
        )
    )
);

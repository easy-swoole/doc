<?php
return [
    'SERVER_NAME' => "EasySwoole",
    'MAIN_SERVER' => [
        'LISTEN_ADDRESS' => '0.0.0.0',
        'PORT' => 9501,
        'SERVER_TYPE' => EASYSWOOLE_WEB_SERVER, //可选为 EASYSWOOLE_SERVER  EASYSWOOLE_WEB_SERVER EASYSWOOLE_WEB_SOCKET_SERVER,EASYSWOOLE_REDIS_SERVER
        'SOCK_TYPE' => SWOOLE_TCP,
        'RUN_MODEL' => SWOOLE_PROCESS,
        'SETTING' => [
            'worker_num' => swoole_cpu_num(),
            'reload_async' => true,
            'max_wait_time'=>3,
            'document_root'            => EASYSWOOLE_ROOT . '/Static',
            'enable_static_handler'    => true,
        ],
        'TASK'=>[
            'workerNum'=>0,
            'maxRunningNum'=>128,
            'timeout'=>15
        ]
    ],
    'TEMP_DIR' => null,
    'LOG_DIR' => null,
    "DOC" => [
        "ALLOW_LANGUAGE" => ["Cn" => "简体中文","En" => "English"],
        "DEFAULT_LANGUAGE" => "Cn"
    ]
];

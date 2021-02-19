<?php


return [
    'rbac' => [
        'model' => [
            'config_type' => 'file',
            'config_file_path' => BASE_PATH . '/config/autoload/casbin-rbac-model.conf',
            'config_text' => '',
        ],
        'adapter' => [
            'class' => \Donjan\Casbin\Adapters\DatabaseAdapter::class,
            'table_name' => 'casbin_rule_rbac',
            'connection' => 'default'
        ],
        //...
    ],
    'restful' => [
        'model' => [
            'config_type' => 'file',
            'config_file_path' => BASE_PATH . '/config/autoload/casbin-restful-model.conf',
            'config_text' => '',
        ],
        'adapter' => [
            'class' => \Donjan\Casbin\Adapters\DatabaseAdapter::class,
            'table_name' => 'casbin_rule_restful',
            'connection' => 'default'
        ],
        //...
    ],
];
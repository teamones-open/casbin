<?php


return [
    'default' => [
        'model' => [
            'config_type' => 'file',
            'config_file_path' => config_path() . '/casbin-restful-model.conf',
            'config_text' => '',
        ],
        'adapter_rule_model' => \app\model\Rule::class
    ],
];
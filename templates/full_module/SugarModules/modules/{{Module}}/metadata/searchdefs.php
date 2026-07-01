<?php

$searchdefs['{{Module}}'] = [
    'templateMeta' => [
        'maxColumns' => '3',
        'widths' => ['label' => '10', 'field' => '30'],
    ],
    'layout' => [
        'basic_search' => [
            'name' => ['name' => 'name', 'default' => true, 'width' => '10%'],
            'status' => ['name' => 'status', 'default' => true, 'width' => '10%'],
            'current_user_only' => [
                'name' => 'current_user_only',
                'label' => 'LBL_CURRENT_USER_FILTER',
                'type' => 'bool',
                'default' => true,
                'width' => '10%',
            ],
        ],
        'advanced_search' => [
            'name' => ['name' => 'name', 'default' => true, 'width' => '10%'],
            'status' => ['name' => 'status', 'default' => true, 'width' => '10%'],
            'assigned_user_id' => [
                'name' => 'assigned_user_id',
                'label' => 'LBL_ASSIGNED_TO',
                'type' => 'enum',
                'function' => ['name' => 'get_user_array', 'params' => [false]],
                'default' => true,
                'width' => '10%',
            ],
        ],
    ],
];

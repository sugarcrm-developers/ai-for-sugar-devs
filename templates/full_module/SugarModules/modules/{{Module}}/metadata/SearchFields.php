<?php

$searchFields['{{Module}}'] = [
    'name' => ['query_type' => 'default'],
    'status' => ['query_type' => 'default', 'options' => '{{module_lower}}_status_list', 'template_var' => 'STATUS_OPTIONS'],
    'assigned_user_id' => ['query_type' => 'default'],
    'current_user_only' => [
        'query_type' => 'default',
        'db_field' => ['assigned_user_id'],
        'my_items' => true,
        'vname' => 'LBL_CURRENT_USER_FILTER',
        'type' => 'bool',
    ],
];

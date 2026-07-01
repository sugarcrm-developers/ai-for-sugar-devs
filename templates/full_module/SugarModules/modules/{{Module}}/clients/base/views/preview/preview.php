<?php

$viewdefs['{{Module}}']['base']['view']['preview'] = [
    'panels' => [
        [
            'name' => 'panel_header',
            'header' => true,
            'fields' => ['name'],
        ],
        [
            'name' => 'panel_body',
            'label' => 'LBL_PANEL_1',
            'columns' => 1,
            'labelsOnTop' => true,
            'placeholders' => true,
            'fields' => ['status', 'assigned_user_name', 'description'],
        ],
    ],
];

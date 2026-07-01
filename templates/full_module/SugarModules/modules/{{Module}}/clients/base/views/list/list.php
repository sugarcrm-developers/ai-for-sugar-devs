<?php

$viewdefs['{{Module}}']['base']['view']['list'] = [
    'panels' => [
        [
            'name' => 'panel_body',
            'label' => 'LBL_PANEL_1',
            'fields' => [
                ['name' => 'name', 'label' => 'LBL_NAME', 'default' => true, 'enabled' => true, 'link' => true],
                ['name' => 'status', 'label' => 'LBL_STATUS', 'default' => true, 'enabled' => true],
                ['name' => 'assigned_user_name', 'label' => 'LBL_ASSIGNED_USER', 'default' => true, 'enabled' => true],
                ['name' => 'date_modified', 'label' => 'LBL_DATE_MODIFIED', 'default' => true, 'enabled' => true],
            ],
        ],
    ],
];

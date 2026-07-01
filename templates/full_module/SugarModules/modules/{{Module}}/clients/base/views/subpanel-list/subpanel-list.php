<?php

$viewdefs['{{Module}}']['base']['view']['subpanel-list'] = [
    'panels' => [
        [
            'name' => 'panel_body',
            'label' => 'LBL_PANEL_1',
            'fields' => [
                ['name' => 'name', 'label' => 'LBL_NAME', 'enabled' => true, 'default' => true, 'link' => true],
                ['name' => 'status', 'label' => 'LBL_STATUS', 'enabled' => true, 'default' => true],
                ['name' => 'date_modified', 'label' => 'LBL_DATE_MODIFIED', 'enabled' => true, 'default' => true, 'readonly' => true],
            ],
        ],
    ],
    'rowactions' => [
        'actions' => [
            ['type' => 'rowaction', 'name' => 'edit_button', 'icon' => 'sicon-edit', 'label' => 'LBL_EDIT_BUTTON', 'event' => 'list:editrow:fire', 'acl_action' => 'edit'],
            ['type' => 'unlinkaction', 'name' => 'unlink_button', 'icon' => 'sicon-x-circle-lg', 'label' => 'LBL_UNLINK_BUTTON', 'event' => 'list:unlinkrow:fire'],
        ],
    ],
];

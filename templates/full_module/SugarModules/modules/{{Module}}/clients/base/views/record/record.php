<?php

$viewdefs['{{Module}}']['base']['view']['record'] = [
    'buttons' => [
        ['type' => 'button', 'name' => 'cancel_button', 'label' => 'LBL_CANCEL_BUTTON_LABEL', 'css_class' => 'btn-invisible btn-link', 'showOn' => 'edit'],
        ['type' => 'rowaction', 'event' => 'button:save_button:click', 'name' => 'save_button', 'label' => 'LBL_SAVE_BUTTON_LABEL', 'css_class' => 'btn btn-primary', 'showOn' => 'edit', 'acl_action' => 'edit'],
        [
            'type' => 'actiondropdown',
            'name' => 'main_dropdown',
            'primary' => true,
            'showOn' => 'view',
            'buttons' => [
                ['type' => 'rowaction', 'event' => 'button:edit_button:click', 'name' => 'edit_button', 'label' => 'LBL_EDIT_BUTTON_LABEL', 'acl_action' => 'edit'],
                ['type' => 'shareaction', 'name' => 'share', 'label' => 'LBL_RECORD_SHARE_BUTTON', 'acl_action' => 'view'],
                ['type' => 'pdfaction', 'name' => 'download-pdf', 'label' => 'LBL_PDF_VIEW', 'action' => 'download', 'acl_action' => 'view'],
                ['type' => 'divider'],
                ['type' => 'rowaction', 'event' => 'button:delete_button:click', 'name' => 'delete_button', 'label' => 'LBL_DELETE_BUTTON_LABEL', 'acl_action' => 'delete'],
            ],
        ],
        ['type' => 'sidebartoggler'],
    ],
    'panels' => [
        [
            'name' => 'panel_header',
            'header' => true,
            'fields' => [
                ['name' => 'picture', 'type' => 'avatar', 'size' => 'large', 'dismiss_label' => true, 'readonly' => true],
                'name',
                ['name' => 'favorite', 'label' => 'LBL_FAVORITE', 'type' => 'favorite', 'dismiss_label' => true],
                ['name' => 'follow', 'label' => 'LBL_FOLLOW', 'type' => 'follow', 'readonly' => true, 'dismiss_label' => true],
            ],
        ],
        [
            'name' => 'panel_body',
            'label' => 'LBL_RECORD_BODY',
            'columns' => 2,
            'labelsOnTop' => true,
            'placeholders' => true,
            'newTab' => false,
            'panelDefault' => 'expanded',
            'fields' => [
                'status',
                'assigned_user_name',
                'team_name',
                'description',
            ],
        ],
    ],
];

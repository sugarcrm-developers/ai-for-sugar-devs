<?php
// Legacy edit view metadata. Sidecar's record view (in edit mode) is the primary surface.

$viewdefs['{{Module}}']['EditView'] = [
    'templateMeta' => [
        'maxColumns' => '2',
        'widths' => [
            ['label' => '10', 'field' => '30'],
            ['label' => '10', 'field' => '30'],
        ],
    ],
    'panels' => [
        'default' => [
            [
                ['name' => 'name', 'label' => 'LBL_NAME'],
                ['name' => 'status', 'label' => 'LBL_STATUS'],
            ],
            [
                ['name' => 'description', 'label' => 'LBL_DESCRIPTION'],
                ['name' => 'assigned_user_name', 'label' => 'LBL_ASSIGNED_TO_NAME'],
            ],
        ],
    ],
];

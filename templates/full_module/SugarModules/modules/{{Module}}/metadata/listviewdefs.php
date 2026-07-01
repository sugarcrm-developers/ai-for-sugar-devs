<?php
// Legacy list view metadata. Still used by reports + some legacy export paths.

$listViewDefs['{{Module}}'] = [
    'NAME' => [
        'width' => '30',
        'label' => 'LBL_NAME',
        'link' => true,
        'default' => true,
    ],
    'STATUS' => [
        'width' => '15',
        'label' => 'LBL_STATUS',
        'default' => true,
    ],
    'ASSIGNED_USER_NAME' => [
        'width' => '15',
        'label' => 'LBL_ASSIGNED_USER',
        'default' => true,
    ],
    'DATE_MODIFIED' => [
        'width' => '15',
        'label' => 'LBL_DATE_MODIFIED',
        'default' => true,
    ],
];

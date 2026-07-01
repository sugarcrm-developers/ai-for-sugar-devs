<?php

$popupMeta = [
    'moduleMain' => '{{Bean}}',
    'varName' => '{{Bean}}',
    'orderBy' => '{{module_table}}.name',
    'whereClauses' => [
        'name' => '{{module_table}}.name',
    ],
    'searchInputs' => ['name'],
    'searchdefs' => [
        'name' => ['name' => 'name', 'width' => '10%'],
    ],
    'listviewdefs' => [
        'NAME' => [
            'width' => '40',
            'label' => 'LBL_NAME',
            'link' => true,
            'default' => true,
        ],
        'STATUS' => [
            'width' => '15',
            'label' => 'LBL_STATUS',
            'default' => true,
        ],
    ],
];

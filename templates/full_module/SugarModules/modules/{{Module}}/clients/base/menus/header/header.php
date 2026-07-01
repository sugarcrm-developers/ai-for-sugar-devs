<?php

$viewdefs['{{Module}}']['base']['menu']['header'] = [
    [
        'route' => '#{{Module}}/create',
        'label' => 'LNK_NEW_RECORD',
        'acl_action' => 'create',
        'acl_module' => '{{Module}}',
        'icon' => 'sicon-plus',
    ],
    [
        'route' => '#{{Module}}',
        'label' => 'LNK_LIST',
        'acl_action' => 'list',
        'acl_module' => '{{Module}}',
        'icon' => 'sicon-search',
    ],
    [
        'route' => '#bwc/index.php?module=Import&action=Step1&import_module={{Module}}',
        'label' => 'LBL_IMPORT_{{Module}}',
        'acl_action' => 'import',
        'acl_module' => '{{Module}}',
        'icon' => 'sicon-upload',
    ],
];

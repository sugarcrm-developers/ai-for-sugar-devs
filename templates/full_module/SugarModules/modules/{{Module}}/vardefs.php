<?php

$dictionary['{{Bean}}'] = [
    'table' => '{{module_table}}',
    'audited' => true,
    'unified_search' => true,
    'full_text_search' => true,
    'unified_search_default_enabled' => true,
    // `basic` provides id, name, date_entered, date_modified, modified_user_id,
    // created_by, description, deleted + the standard PK + audit indices.
    // Do NOT redeclare these — duplicate-index error at install.
    'uses' => ['basic', 'assignable', 'team_security'],
    'fields' => [
        'status' => [
            'name' => 'status',
            'vname' => 'LBL_STATUS',          // vname, NOT label
            'type' => 'enum',
            'options' => '{{module_lower}}_status_list',  // app_list_strings dropdown — application scope
            'len' => 100,
            'default' => 'open',
        ],
    ],
    'relationships' => [],
    'indices' => [],  // empty — `basic` adds standard indices
    'optimistic_locking' => true,
];

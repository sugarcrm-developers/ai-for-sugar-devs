<?php
// Link / relate / id triplet on RHS (child). All three required.

// 1. link field
$dictionary['{{rhs_bean}}']['fields']['{{rel}}'] = [
    'name' => '{{rel}}',
    'type' => 'link',
    'relationship' => '{{rel}}',
    'source' => 'non-db',
    'module' => '{{lhs_module}}',
    'bean_name' => '{{lhs_bean}}',
    'vname' => 'LBL_{{rel_upper}}_FROM_{{lhs_module}}_TITLE',
    'side' => 'right',
];

// 2. relate field — displays the parent's name on the child's record view
$dictionary['{{rhs_bean}}']['fields']['{{rel}}_name'] = [
    'name' => '{{rel}}_name',
    'type' => 'relate',
    'source' => 'non-db',
    'vname' => 'LBL_{{rel_upper}}_FROM_{{lhs_module}}_TITLE',
    'save' => true,
    'id_name' => '{{rel}}{{lhs_module_lower}}_ida',
    'link' => '{{rel}}',
    'table' => '{{lhs_table}}',
    'module' => '{{lhs_module}}',
    'rname' => 'name',
];

// 3. id field — the FK on the child
$dictionary['{{rhs_bean}}']['fields']['{{rel}}{{lhs_module_lower}}_ida'] = [
    'name' => '{{rel}}{{lhs_module_lower}}_ida',
    'type' => 'link',
    'relationship' => '{{rel}}',
    'source' => 'non-db',
    'reportable' => false,
    'side' => 'right',
    'vname' => 'LBL_{{rel_upper}}_FROM_{{lhs_module}}_TITLE_ID',
];

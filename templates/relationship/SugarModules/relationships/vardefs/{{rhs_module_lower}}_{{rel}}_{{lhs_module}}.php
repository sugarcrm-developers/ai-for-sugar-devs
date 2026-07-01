<?php
// Link vardef on LHS (parent). Exposes the relationship to the {{lhs_bean}} bean.

$dictionary['{{lhs_bean}}']['fields']['{{rel}}'] = [
    'name' => '{{rel}}',
    'type' => 'link',
    'relationship' => '{{rel}}',
    'source' => 'non-db',
    'module' => '{{rhs_module}}',
    'bean_name' => '{{rhs_bean}}',
    'vname' => 'LBL_{{rel_upper}}_FROM_{{rhs_module}}_TITLE',
];

<?php
// MB-style relationship metadata.
// MB writes M:M-with-join-table even for declared 1:M — that's the convention.
// `true_relationship_type` declares the conceptual intent and drives UI cardinality.

$relationships['{{rel}}'] = [
    'lhs_module' => '{{lhs_module}}',
    'lhs_table' => '{{lhs_table}}',
    'lhs_key' => 'id',
    'rhs_module' => '{{rhs_module}}',
    'rhs_table' => '{{rhs_table}}',
    'rhs_key' => 'id',
    'relationship_type' => 'many-to-many',
    'join_table' => '{{rel}}_c',
    'join_key_lhs' => '{{rel}}{{lhs_module_lower}}_ida',
    'join_key_rhs' => '{{rel}}{{rhs_module_lower}}_idb',
    'true_relationship_type' => 'one-to-many',
];

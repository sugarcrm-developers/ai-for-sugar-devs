<?php
// Application-scope language. Registered with to_module => 'application'.
// REQUIRED for:
//   - moduleList: sidebar entry
//   - moduleListSingular: singular display
//   - moduleIconList: icon registration
//   - <module>_status_list and any other enum dropdowns
//   - parent_type_display['<Module>'] for Notes / Tasks / Calls / Meetings parent picker
// See skills/sugar-application-language/SKILL.md.

$app_list_strings['moduleList']['{{Module}}'] = '{{module_label}}';
$app_list_strings['moduleListSingular']['{{Module}}'] = '{{module_label_singular}}';
$app_list_strings['moduleIconList']['{{Module}}'] = '{{Module}}';

$app_list_strings['{{module_lower}}_status_list'] = [
    '' => '',
    'open' => 'Open',
    'in_progress' => 'In Progress',
    'closed' => 'Closed',
];

// Uncomment if you want Notes / Tasks etc. to allow this module as a parent:
// $app_list_strings['parent_type_display']['{{Module}}'] = '{{module_label_singular}}';
// $app_list_strings['record_type_display']['{{Module}}'] = '{{module_label_singular}}';
// $app_list_strings['record_type_display_notes']['{{Module}}'] = '{{module_label_singular}}';

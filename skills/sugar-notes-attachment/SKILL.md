---
name: sugar-notes-attachment
description: Attach SugarCRM Notes (1:M) to a custom module via parent_id/parent_type with relationship_role_column. Notes are the EXCEPTION to MB's M:M-with-join-table rule. Requires record_type_display, parent_type_display, record_type_display_notes registrations.
when_to_use:
  - "attach Notes to my custom module"
  - "Notes 1:M relationship to a custom module"
  - "Notes parent_type / parent_id setup"
  - "Notes flex relate"
not_for:
  - Normal 1:M / M:M relationships (use the join-table approach) — see sugar-relationship
related_skills:
  - sugar-relationship
  - sugar-application-language
  - sugar-new-module
  - sugar-package-build
---

## When to use this skill

Use this skill when you want a custom module to "own" Notes records as a 1:M subpanel — the same way Accounts, Cases, Opportunities own Notes. Notes uses a SPECIAL relationship pattern: it doesn't use a join table; instead Notes' `parent_id` is the FK and `parent_type` is the discriminator. This is the only documented exception to MB's M:M-with-join-table convention.

## What makes Notes different

Normal MB relationship (1:M, declared):
- Generates a join table `<rel>_c` with `<rel><lhs>_ida` / `<rel><rhs>_idb` columns
- Both sides write to the join table

Notes 1:M:
- NO join table
- Notes.parent_id stores the FK to your module's id
- Notes.parent_type stores the string identifying your module ("Foos", "Bars", etc.)
- The relationship config has `relationship_role_column => 'parent_type'` and `relationship_role_column_value => '<YourModule>'`

## Concrete example: Foos has many Notes

```php
<?php
// src/SugarModules/relationships/relationships/foos_notes.php

$relationships['foos_notes'] = [
    'lhs_module' => 'Foos',
    'lhs_table' => 'foos',
    'lhs_key' => 'id',
    'rhs_module' => 'Notes',
    'rhs_table' => 'notes',
    'rhs_key' => 'parent_id',
    'relationship_type' => 'one-to-many',
    'relationship_role_column' => 'parent_type',
    'relationship_role_column_value' => 'Foos',
];
```

vardef on the Foos side (link field exposes the relationship to the bean):

```php
<?php
// src/SugarModules/relationships/vardefs/foos_foos_notes_Foos.php

$dictionary['Foo']['fields']['notes'] = [
    'name' => 'notes',
    'type' => 'link',
    'relationship' => 'foos_notes',
    'module' => 'Notes',
    'bean_name' => 'Note',
    'source' => 'non-db',
    'vname' => 'LBL_NOTES',
];
```

No vardef is needed on the Notes side — `parent_id` / `parent_type` already exist on Notes core.

## Application-scope language strings (REQUIRED)

For Notes to display your module as a parent option, register in `SugarModules/language/application/<lang>.lang.php`:

```php
<?php
// src/SugarModules/language/application/en_us.lang.php

// Notes parent_type dropdown — what shows in the "Related to" picker
$app_list_strings['parent_type_display']['Foos'] = 'Foo';

// Generic record_type_display — Sugar uses this for many UIs
$app_list_strings['record_type_display']['Foos'] = 'Foo';

// Notes-specific override
$app_list_strings['record_type_display_notes']['Foos'] = 'Foo';
```

Register pack.php entries with `'to_module' => 'application'`. See `[[sugar-application-language]]`.

## Subpanel layout (Sidecar)

To show Notes as a subpanel on your module's record view:

```php
<?php
// src/SugarModules/modules/Foos/clients/base/layouts/subpanels/default.php

$viewdefs['Foos']['base']['layout']['subpanels'] = [
    'components' => [
        [
            'layout' => 'subpanel',
            'label' => 'LBL_NOTES_SUBPANEL_TITLE',
            'context' => [
                'link' => 'notes',
            ],
        ],
    ],
];
```

This must register in `installdefs['sidecar']`, NOT `copy`. See `[[sugar-mlp-anatomy]]`.

## Language label for the subpanel

```php
<?php
// src/SugarModules/modules/Foos/language/en_us.lang.php

$mod_strings['LBL_NOTES_SUBPANEL_TITLE'] = 'Notes';
$mod_strings['LBL_NOTES'] = 'Notes';
```

## Common gotchas

| Symptom | Root cause | Fix |
|---------|------------|-----|
| Notes subpanel empty but records exist | `relationship_role_column_value` doesn't match `parent_type` in Notes table | Match exactly to `module_name` (PLURAL) |
| Foos doesn't show in Notes "Related to" picker | `parent_type_display['Foos']` missing | Register application-scope (see above) |
| Subpanel doesn't show at all | Sidecar layout missing or registered under `copy` | Move to `installdefs['sidecar']` |
| Creating a Note from the subpanel doesn't set parent | Link vardef missing `relationship`/`module`/`bean_name` | All three required |

## References

- `[[sugar-relationship]]` — normal M:M with join table
- `[[sugar-application-language]]` — required for parent_type_display
- `[[sugar-mlp-anatomy]]` — sidecar vs copy routing
- `[[sugar-new-module]]` — context for the module being attached to

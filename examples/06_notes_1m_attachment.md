# Example 06 — Notes 1:M Attachment to a Custom Module

Minimal example of attaching Notes 1:M to an existing custom module `Foos`. This is the special-case relationship where Notes uses `parent_id` / `parent_type` rather than a join table — the only documented exception to MB's M:M-with-join-table convention.

Companion skill: `[[sugar-notes-attachment]]`.

## Feature request (input)

```
Feature Type: Notes 1:M Attachment
Module: Foos
Display Singular: Foo
Package Name: Acme_FoosNotes
```

## Required files (minimal set)

For Notes 1:M attachment on a custom module Foos, you need exactly these files:

```
build/Acme_FoosNotes/
├── version                                                          # "1.0.0"
├── pack.php                                                          # full-section scanner from templates/minimal_mlp/pack.stub.php
├── releases/.keep
└── src/
    └── SugarModules/
        ├── relationships/
        │   └── relationships/
        │       └── foos_notes.php                                    # 1. relationship metadata
        ├── modules/
        │   └── Foos/
        │       ├── vardefs.php                                        # 2. extends $dictionary['Foo']['fields'] with the link
        │       ├── clients/base/layouts/subpanels/default.php         # 3. subpanel registration
        │       └── language/en_us.lang.php                            # 4. LBL_NOTES_SUBPANEL_TITLE, LBL_NOTES
        └── language/
            └── application/
                └── en_us.lang.php                                     # 5. parent_type_display['Foos']
```

## File 1: relationship metadata

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

Note: NO `join_table` — Notes uses its own table directly via `parent_id`.

## File 2: link vardef on Foos

```php
<?php
// src/SugarModules/modules/Foos/vardefs.php (excerpt — add to existing $dictionary['Foo']['fields'])

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

No vardef is needed on the Notes side — `parent_id` and `parent_type` already exist on Notes core.

## File 3: subpanel layout

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
    'type' => 'subpanels',
    'span' => 12,
];
```

CRITICAL: this file must be registered in `installdefs['sidecar']`, NOT `installdefs['copy']`. The 7-section scanner in `templates/minimal_mlp/pack.stub.php` handles this automatically based on path.

## File 4: module-scope language

```php
<?php
// src/SugarModules/modules/Foos/language/en_us.lang.php (excerpt — add to existing $mod_strings)

$mod_strings['LBL_NOTES_SUBPANEL_TITLE'] = 'Notes';
$mod_strings['LBL_NOTES'] = 'Notes';
```

## File 5: application-scope language

```php
<?php
// src/SugarModules/language/application/en_us.lang.php

// REQUIRED: makes Foos show up in Notes' "Related to" picker
$app_list_strings['parent_type_display']['Foos'] = 'Foo';

// Also recommended for consistency across Sugar UI
$app_list_strings['record_type_display']['Foos'] = 'Foo';
$app_list_strings['record_type_display_notes']['Foos'] = 'Foo';
```

Register pack.php entry with `'to_module' => 'application'` (the scanner handles this automatically for files under `SugarModules/language/application/`).

## Install + verify

```bash
cd build/Acme_FoosNotes
php pack.php 1.0.0
```

Upload via Module Loader → Install. Quick Repair & Rebuild.

Verify:

1. Open any Foos record → confirm **Notes subpanel** appears
2. Click **Create** in the Notes subpanel → confirm new note links automatically to the Foo
3. Open Notes module separately → create a note → confirm **"Foo"** is in the **Related to** dropdown

## Gotcha quick-reference

| Symptom | Fix |
|---------|-----|
| Subpanel doesn't appear | sidecar layout missing OR in `copy` instead of `sidecar`. See `[[sugar-mlp-anatomy]]`. |
| Foo missing from Notes "Related to" picker | `parent_type_display['Foos']` not registered at application scope. See `[[sugar-application-language]]`. |
| Created note doesn't link to parent | `relationship_role_column_value` mismatch — must be `'Foos'` (PLURAL form) |
| Subpanel shows zero records even though notes exist | Notes.parent_type column doesn't have `'Foos'` (you bypassed the relationship API) |

## See also

- `[[sugar-notes-attachment]]` — full skill reference
- `[[sugar-relationship]]` — non-Notes M:M-with-join-table pattern
- `[[sugar-application-language]]` — required for parent_type_display
- `[[sugar-mlp-anatomy]]` — sidecar vs copy installdefs routing
- `examples/05_full_module_package.md` — broader multi-module example that includes a Notes 1:M

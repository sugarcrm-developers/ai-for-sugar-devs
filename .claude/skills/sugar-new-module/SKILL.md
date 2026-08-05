---
name: sugar-new-module
description: Create a brand-new SugarCRM module (MB-style) — bean class + sugar parent + vardefs + 8 metadata files + 5 Sidecar views + 2 filters + 2 menus + subpanel + dashboards. Enforces Escalation singular/plural pattern.
when_to_use:
  - "create a new custom module"
  - "scaffold an entire MB-style module"
  - "add module Foo to Sugar"
  - "build a module from scratch (not Studio)"
not_for:
  - Adding fields to an existing module — use sugar-custom-field
  - Adding a relationship between existing modules — use sugar-relationship
  - Starting from a Module Builder export zip — use sugar-mb-export-flow first
related_skills:
  - sugar-mb-export-flow
  - sugar-relationship
  - sugar-module-icons
  - sugar-application-language
  - sugar-notes-attachment
  - sugar-package-build
---

## When to use this skill

Use this skill when a developer wants a complete new module written from scratch in MB-style layout (no Module Builder UI). A complete module is roughly 22 files: bean PHP, sugar parent PHP, vardefs.php, 8 metadata files, 5 Sidecar views, 2 filter files, 2 menu files, a default subpanel, focus + record dashboards, and a language file.

If they already have a Module Builder export zip, route through `[[sugar-mb-export-flow]]` first to strip doubled prefixes.

## The Escalation singular/plural pattern (MANDATORY)

This is the single most common mistake in new modules. Sugar requires:

| Token | Form | Example |
|-------|------|---------|
| Module folder | PLURAL | `modules/Escalations/` |
| `module_dir` / `module_name` | PLURAL | `'Escalations'` |
| Database table | PLURAL | `escalations` |
| `table_name` | PLURAL | `'escalations'` |
| Bean class name | SINGULAR | `class Escalation extends SugarBean` |
| `object_name` | SINGULAR | `'Escalation'` |
| `$dictionary` key in vardefs.php | SINGULAR | `$dictionary['Escalation'] = [...]` |
| Filename of bean | SINGULAR | `Escalation.php` |

Canonical reference: `data/app/sugar/modules/Escalations/Escalation.php` (singular class, plural everything else).

## Required files (MB-style layout)

Place all files under `src/SugarModules/modules/<Module>/` (PLURAL `<Module>`). For a module called `Foos` with bean `Foo`:

```
src/SugarModules/modules/Foos/
├── Foo.php                                      # bean class (SINGULAR)
├── SugarFoo.php                                 # optional sugar parent (extends Foo for hooks)
├── vardefs.php                                  # $dictionary['Foo'] = [...]
├── metadata/
│   ├── detailviewdefs.php                       # legacy detail (rarely shown)
│   ├── editviewdefs.php                         # legacy edit
│   ├── listviewdefs.php                         # legacy list (still used by some exporters)
│   ├── searchdefs.php                           # legacy search
│   ├── popupdefs.php                            # popup picker
│   ├── studio.php                               # mark for Studio integration
│   ├── SearchFields.php                         # field types for search
│   └── subpaneldefs.php                         # subpanel registration container
├── clients/base/views/
│   ├── record/record.php                        # Sidecar record view
│   ├── list/list.php                            # Sidecar list view
│   ├── preview/preview.php                      # Sidecar preview pane
│   ├── subpanel-list/subpanel-list.php          # Subpanel list view
│   └── selection-list/selection-list.php        # Picker
├── clients/base/filters/
│   ├── basic/basic.php                          # default filter
│   └── default/default.php                      # default filter set
├── clients/base/menus/
│   ├── header/header.php                        # module header menu
│   └── quickcreate/quickcreate.php              # quick-create menu
├── clients/base/layouts/subpanels/default.php   # default subpanel layout
├── dashboards/focus/                            # focus drawer dashboards
└── dashboards/record/                           # record dashboards
```

Plus application-scope language registration:

```
src/SugarModules/language/application/en_us.lang.php
src/SugarModules/modules/Foos/language/en_us.lang.php
```

See `[[sugar-application-language]]` for the application-scope rules.

## Concrete example: bean class

```php
<?php
// src/SugarModules/modules/Foos/Foo.php

class Foo extends SugarBean
{
    public $object_name = 'Foo';
    public $table_name = 'foos';
    public $module_dir = 'Foos';
    public $module_name = 'Foos';
    public $importable = true;

    public $name;
    public $description;
    public $assigned_user_id;
    public $assigned_user_name;
    public $team_id;
    public $team_name;
}
```

## Concrete example: vardefs.php

```php
<?php
// src/SugarModules/modules/Foos/vardefs.php

$dictionary['Foo'] = [
    'table' => 'foos',
    'audited' => true,
    'unified_search' => true,
    'full_text_search' => true,
    'unified_search_default_enabled' => true,
    'uses' => ['basic', 'assignable', 'team_security'],  // pulls in id, name, etc + audit indices
    'fields' => [
        // Add custom fields here. Do NOT redeclare `id` — `basic` template provides it.
        'status' => [
            'name' => 'status',
            'vname' => 'LBL_STATUS',          // vname, NOT label
            'type' => 'enum',
            'options' => 'foo_status_list',   // app_list_strings dropdown, application-scope
            'len' => 100,
            'default' => 'open',
        ],
        'priority' => [
            'name' => 'priority',
            'vname' => 'LBL_PRIORITY',
            'type' => 'int',
            'len' => 5,
        ],
    ],
    'relationships' => [],
    'indices' => [],  // empty — `basic` adds the primary + standard indices automatically
    'optimistic_locking' => true,
];

// `uses` is the key: `basic` adds id, name, date_entered, date_modified, modified_user_id, created_by, description, deleted, plus the standard PK + audit indices. Do not redeclare these.
```

## Concrete example: Sidecar record view

```php
<?php
// src/SugarModules/modules/Foos/clients/base/views/record/record.php

$viewdefs['Foos']['base']['view']['record'] = [
    'buttons' => [
        ['type' => 'button', 'name' => 'cancel_button', 'label' => 'LBL_CANCEL_BUTTON_LABEL', 'css_class' => 'btn-invisible btn-link', 'showOn' => 'edit'],
        ['type' => 'rowaction', 'event' => 'button:save_button:click', 'name' => 'save_button', 'label' => 'LBL_SAVE_BUTTON_LABEL', 'css_class' => 'btn btn-primary', 'showOn' => 'edit', 'acl_action' => 'edit'],
        ['type' => 'actiondropdown', 'name' => 'main_dropdown', 'primary' => true, 'showOn' => 'view', 'buttons' => [
            ['type' => 'rowaction', 'event' => 'button:edit_button:click', 'name' => 'edit_button', 'label' => 'LBL_EDIT_BUTTON_LABEL', 'acl_action' => 'edit'],
        ]],
    ],
    'panels' => [
        [
            'name' => 'panel_header',
            'header' => true,
            'fields' => [
                ['name' => 'picture', 'type' => 'avatar', 'size' => 'large', 'dismiss_label' => true, 'readonly' => true],
                'name',
            ],
        ],
        [
            'name' => 'panel_body',
            'label' => 'LBL_RECORD_BODY',
            'columns' => 2,
            'labelsOnTop' => true,
            'placeholders' => true,
            'newTab' => false,
            'panelDefault' => 'expanded',
            'fields' => [
                'status',
                'priority',
                'assigned_user_name',
                'team_name',
            ],
        ],
    ],
];
```

## Required field set (typical)

Most new modules need:
- `id` (provided by `basic`)
- `name` (provided by `basic`)
- `date_entered`, `date_modified`, `modified_user_id`, `created_by` (provided by `basic`)
- `description` (provided by `basic`)
- `deleted` (provided by `basic`)
- `assigned_user_id` + `assigned_user_name` (provided by `assignable`)
- `team_id`, `team_name`, `team_set_id`, `team_count` (provided by `team_security`)

So `uses => ['basic', 'assignable', 'team_security']` covers ~80% of what most modules need without manual declaration.

## Application-scope language (REQUIRED)

For the module to appear in the sidebar and module dropdowns, you MUST register strings in `SugarModules/language/application/<lang>.lang.php`:

```php
<?php
// src/SugarModules/language/application/en_us.lang.php

$app_list_strings['moduleList']['Foos'] = 'Foos';
$app_list_strings['moduleListSingular']['Foos'] = 'Foo';
$app_list_strings['moduleIconList']['Foos'] = 'Foos';

// enum dropdown for Foo status
$app_list_strings['foo_status_list'] = [
    'open' => 'Open',
    'closed' => 'Closed',
    'in_progress' => 'In Progress',
];
```

Register in pack.php with `'to_module' => 'application'`. See `[[sugar-application-language]]`.

## Common gotchas

| Symptom | Root cause | Fix |
|---------|------------|-----|
| Sidebar shows "Foos" with no icon | Icons missing or `image_dir` in wrong place | See `[[sugar-module-icons]]` |
| Sidebar missing the module entirely | `moduleList` not registered application-scope | See `[[sugar-application-language]]` |
| "Bean class not found" | Bean class name not SINGULAR or filename mismatch | Match the Escalation pattern |
| Duplicate-index error on install | Redeclared `idx_<module>_pk` already provided by `basic` | Remove the manual index declaration |
| Status dropdown empty | `foo_status_list` registered as module-scope instead of application-scope | Move to `SugarModules/language/application/` |
| Subpanel doesn't appear | Sidecar subpanel layout missing or in `copy` not `sidecar` | See `[[sugar-mlp-anatomy]]` for routing |

## References

- `[[sugar-mb-export-flow]]` — start from a Module Builder zip
- `[[sugar-relationship]]` — link new module to other modules
- `[[sugar-notes-attachment]]` — attach Notes via parent_id/parent_type
- `[[sugar-module-icons]]` — icon set + image_dir
- `[[sugar-application-language]]` — sidebar + dropdown registration
- `[[sugar-mlp-anatomy]]` — installdefs section routing
- `[[sugar-package-build]]` — pack.php
- `reference/module_anatomy.md` — full file-by-file walkthrough
- Canonical: `data/app/sugar/modules/Escalations/Escalation.php`

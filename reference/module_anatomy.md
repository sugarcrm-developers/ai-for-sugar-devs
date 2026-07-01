# Module Anatomy

File-by-file walkthrough of a complete MB-style SugarCRM module. Companion skill: [`skills/sugar-new-module/SKILL.md`](../skills/sugar-new-module/SKILL.md).

For a module named `Foos` (plural module folder/table) with bean `Foo` (singular class/object_name/dictionary key) — the Escalation pattern from `data/app/sugar/modules/Escalations/Escalation.php`.

## The ~22 files

```
src/SugarModules/modules/Foos/
├── Foo.php                                              # 1. bean class (SINGULAR)
├── SugarFoo.php                                         # 2. optional sugar parent
├── vardefs.php                                          # 3. $dictionary['Foo']
├── metadata/
│   ├── detailviewdefs.php                               # 4. legacy detail view
│   ├── editviewdefs.php                                 # 5. legacy edit view
│   ├── listviewdefs.php                                 # 6. legacy list view
│   ├── searchdefs.php                                   # 7. legacy search
│   ├── popupdefs.php                                    # 8. popup picker
│   ├── studio.php                                       # 9. Studio integration marker
│   ├── SearchFields.php                                 # 10. field types for search
│   └── subpaneldefs.php                                 # 11. subpanel registration container
├── clients/base/views/
│   ├── record/record.php                                # 12. Sidecar record view
│   ├── list/list.php                                    # 13. Sidecar list view
│   ├── preview/preview.php                              # 14. Sidecar preview pane
│   ├── subpanel-list/subpanel-list.php                  # 15. subpanel list
│   └── selection-list/selection-list.php                # 16. picker
├── clients/base/filters/
│   ├── basic/basic.php                                  # 17. default filter
│   └── default/default.php                              # 18. default filter set
├── clients/base/menus/
│   ├── header/header.php                                # 19. module header menu
│   └── quickcreate/quickcreate.php                      # 20. quick-create menu
├── clients/base/layouts/subpanels/default.php           # 21. subpanel layout
├── dashboards/focus/                                    # focus drawer dashboards
├── dashboards/record/                                   # record dashboards
└── language/en_us.lang.php                              # 22. module-scope mod_strings
```

Plus application-scope:

```
src/SugarModules/language/application/en_us.lang.php    # moduleList + dropdowns
```

Plus optional icons:

```
src/SugarModules/icons/
├── Foos.gif
├── icon_Foos_32.png
├── icon_Foos_64.png
└── icon_Foos_128.png
```

## File-by-file purpose

### 1. `Foo.php` (bean class) — REQUIRED

```php
<?php
class Foo extends SugarBean
{
    public $object_name = 'Foo';
    public $table_name = 'foos';
    public $module_dir = 'Foos';
    public $module_name = 'Foos';
    public $importable = true;
    // ...field properties
}
```

Singular class name + filename. Plural module/table. Required for bean registration.

### 2. `SugarFoo.php` (sugar parent) — OPTIONAL

A subclass of `Foo` used for module-specific logic that shouldn't pollute the bean. Pattern from Sugar core where `SugarBean` is bean, and some modules have `Account.php` extending `SugarAccount.php`. Optional for most cases.

### 3. `vardefs.php` — REQUIRED

`$dictionary['Foo'] = [...]` defining table, fields, indices, relationships, optimistic_locking, uses (`['basic', 'assignable', 'team_security']`).

Note: `'uses' => ['basic']` automatically adds id/name/date_*/modified_user_id/created_by/description/deleted plus the standard PK + audit indices. Do NOT redeclare those — install will fail with duplicate-index.

### 4-11. `metadata/` (8 files) — REQUIRED

Legacy metadata. Some are still consumed by Sugar's view stack even though Sidecar replaced most of them:

- `detailviewdefs.php` — referenced by some legacy panels and Studio
- `editviewdefs.php` — same
- `listviewdefs.php` — referenced by reports + legacy lists
- `searchdefs.php` — search bar metadata
- `popupdefs.php` — popup picker config
- `studio.php` — marker that Studio recognizes this module
- `SearchFields.php` — typing info for search fields
- `subpaneldefs.php` — container for legacy subpanel registration

Module Builder always emits all 8. Skipping any will likely cause Studio quirks.

### 12-16. `clients/base/views/` (5 files) — REQUIRED for Sidecar

Sidecar views that drive the modern UI:

- `record/record.php` — record view (the main editing surface)
- `list/list.php` — list view
- `preview/preview.php` — preview pane
- `subpanel-list/subpanel-list.php` — used when this module is on another module's subpanel
- `selection-list/selection-list.php` — used in pickers (e.g., when Foo is a relate-target)

Each defines `$viewdefs['Foos']['base']['view'][<name>] = [...]`.

### 17-18. `clients/base/filters/` (2 files) — REQUIRED

- `basic/basic.php` — the basic filter (search the list view)
- `default/default.php` — the default filter set (saved filters)

### 19-20. `clients/base/menus/` (2 files) — REQUIRED

- `header/header.php` — module header dropdown actions
- `quickcreate/quickcreate.php` — quick-create menu entry

### 21. `clients/base/layouts/subpanels/default.php` — REQUIRED

The subpanel layout for the module's own record view, defining which subpanels (Notes, related modules) appear. Goes into `installdefs['sidecar']`.

### 22. `language/en_us.lang.php` (module-scope) — REQUIRED

`$mod_strings['LBL_*']` for the module's own labels. Registered with `to_module => 'Foos'`.

### Application-scope `language/application/en_us.lang.php` — REQUIRED

`$app_list_strings['moduleList']`, `moduleListSingular`, `moduleIconList`, and any enum dropdowns. Registered with `to_module => 'application'`. See [`skills/sugar-application-language/SKILL.md`](../skills/sugar-application-language/SKILL.md).

### Icons (4 files) — REQUIRED

`Foos.gif`, `icon_Foos_32.png`, `icon_Foos_64.png`, `icon_Foos_128.png`. Registered via `installdefs['image_dir']`, NOT `$manifest`. See [`skills/sugar-module-icons/SKILL.md`](../skills/sugar-module-icons/SKILL.md).

### Dashboards — OPTIONAL

`dashboards/focus/` and `dashboards/record/` define focus-drawer and record-page dashboards. MB always emits a stub.

## Required vs optional summary

| File group | Required? | Notes |
|------------|-----------|-------|
| Bean class | Yes | Singular |
| vardefs.php | Yes | |
| 8 metadata files | Yes | MB always emits |
| 5 Sidecar views | Yes (for Sidecar UI) | record + list mandatory; others highly recommended |
| 2 filter files | Yes | empty is fine but file must exist |
| 2 menu files | Yes | empty is fine |
| default subpanel | Yes | |
| dashboards/ | Recommended | MB emits |
| module language | Yes | |
| application language | Yes | for sidebar |
| 4 icons | Yes for sidebar UI | else "No Image" |
| SugarFoo.php parent | Optional | rarely needed |

## See also

- [`skills/sugar-new-module/SKILL.md`](../skills/sugar-new-module/SKILL.md) — skill-form summary
- [`templates/full_module/`](../templates/full_module/) — parameterized scaffold of these files
- [`reference/installdefs_cheatsheet.md`](installdefs_cheatsheet.md) — how each file maps to installdefs sections
- Canonical core reference: `data/app/sugar/modules/Escalations/`

---
name: sugar-application-language
description: Register SugarCRM application-scope strings — `moduleList`, `moduleListSingular`, `moduleIconList`, enum `$app_list_strings` dropdowns, and Notes `parent_type_display` — in `SugarModules/language/application/<lang>.lang.php` with `to_module=>'application'`.
when_to_use:
  - "module not appearing in sidebar"
  - "enum dropdown empty"
  - "register $app_list_strings"
  - "Notes Related-to picker missing my module"
  - "moduleList, moduleListSingular, moduleIconList registration"
not_for:
  - Per-module `$mod_strings` labels (LBL_*) — those go in module-scope language
  - View customization — use sugar-ui-customization
related_skills:
  - sugar-notes-attachment
  - sugar-new-module
  - sugar-mlp-anatomy
  - sugar-studio-debugging
---

## When to use this skill

Use this skill any time a SugarCRM customization needs to register strings at the APPLICATION scope — i.e., strings that don't belong to one module but are looked up globally:

- `$app_list_strings['moduleList']['<Module>']` — sidebar + module dropdowns
- `$app_list_strings['moduleListSingular']['<Module>']` — singular display
- `$app_list_strings['moduleIconList']['<Module>']` — icon registration for the dropdown
- `$app_list_strings['<your_enum>']` — enum dropdown values for any `enum` vardef
- `$app_list_strings['parent_type_display']['<Module>']` — Notes "Related to" picker entries
- `$app_list_strings['record_type_display']['<Module>']` and `record_type_display_notes['<Module>']` — generic record-type display

These MUST be in `SugarModules/language/application/<lang>.lang.php` and registered with `to_module => 'application'`. Module-scope language files won't surface them.

## The distinction (CRITICAL)

| Where | What goes there | to_module |
|-------|-----------------|-----------|
| `SugarModules/modules/<Module>/language/<lang>.lang.php` | `$mod_strings['LBL_*']` for the module's own labels | `<Module>` |
| `SugarModules/language/application/<lang>.lang.php` | `$app_list_strings[...]` global lookups + moduleList* | `application` |

Sugar resolves dropdown options and the sidebar entirely via application-scope. If you put `moduleList` in a module-scope language file, it's silently invisible.

## Concrete example: full application-scope file

```php
<?php
// src/SugarModules/language/application/en_us.lang.php

// Sidebar + module dropdowns
$app_list_strings['moduleList']['Foos'] = 'Foos';
$app_list_strings['moduleListSingular']['Foos'] = 'Foo';
$app_list_strings['moduleIconList']['Foos'] = 'Foos';

$app_list_strings['moduleList']['Bars'] = 'Bars';
$app_list_strings['moduleListSingular']['Bars'] = 'Bar';
$app_list_strings['moduleIconList']['Bars'] = 'Bars';

// Enum dropdowns referenced by vardefs `'options' => 'foo_status_list'`
$app_list_strings['foo_status_list'] = [
    '' => '',
    'open' => 'Open',
    'in_progress' => 'In Progress',
    'closed' => 'Closed',
];

$app_list_strings['bar_priority_list'] = [
    '' => '',
    'low' => 'Low',
    'medium' => 'Medium',
    'high' => 'High',
];

// Notes parent_type_display — make Foos / Bars selectable as parent for a Note
$app_list_strings['parent_type_display']['Foos'] = 'Foo';
$app_list_strings['parent_type_display']['Bars'] = 'Bar';

// Generic record_type_display — Sugar uses this in many UIs
$app_list_strings['record_type_display']['Foos'] = 'Foo';
$app_list_strings['record_type_display']['Bars'] = 'Bar';

// Notes-specific
$app_list_strings['record_type_display_notes']['Foos'] = 'Foo';
$app_list_strings['record_type_display_notes']['Bars'] = 'Bar';
```

## installdefs registration (in pack.php)

```php
<?php
// inside pack.php, for each language file under SugarModules/language/application/
$installdefs['language'][] = [
    'from' => '<basepath>/SugarModules/language/application/en_us.lang.php',
    'to_module' => 'application',  // CRITICAL — application, not <Module>
    'language' => 'en_us',
];

// for each module-scope language file
$installdefs['language'][] = [
    'from' => '<basepath>/SugarModules/modules/Foos/language/en_us.lang.php',
    'to_module' => 'Foos',
    'language' => 'en_us',
];
```

## Concrete: vardef referencing the dropdown

```php
<?php
// vardefs.php on the Foos module
$dictionary['Foo']['fields']['status'] = [
    'name' => 'status',
    'vname' => 'LBL_STATUS',
    'type' => 'enum',
    'options' => 'foo_status_list',  // resolves to $app_list_strings['foo_status_list']
    'len' => 50,
];
```

If `foo_status_list` is not at application scope, the dropdown will be empty in Studio and on the record view.

## Common gotchas

| Symptom | Root cause | Fix |
|---------|------------|-----|
| Module missing from sidebar | `moduleList` not registered application-scope | Add to `SugarModules/language/application/` with `to_module=>'application'` |
| Module in sidebar but no icon | `moduleIconList` missing — OR `image_dir` misconfigured | Add both |
| Enum dropdown empty | List registered at module scope or missing | Move to application-scope |
| Notes Related-to picker missing your module | `parent_type_display['<Module>']` missing | Add to application-scope |
| Studio displays "Foos" as the singular too | `moduleListSingular` missing | Add it |

## References

- `[[sugar-notes-attachment]]` — uses parent_type_display from this skill
- `[[sugar-new-module]]` — every new module needs this registration
- `[[sugar-mlp-anatomy]]` — installdefs language section
- `[[sugar-studio-debugging]]` — symptom-to-fix table for related issues

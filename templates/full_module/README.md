# templates/full_module/

Parameterized scaffold of a complete MB-style SugarCRM module. Companion skill: [`skills/sugar-new-module/SKILL.md`](../../skills/sugar-new-module/SKILL.md). Reference walkthrough: [`reference/module_anatomy.md`](../../reference/module_anatomy.md).

## Placeholders

A future script (or hand-substitution) replaces these tokens:

| Token | Meaning | Example |
|-------|---------|---------|
| `{{Module}}` | PLURAL module folder + module_dir + module_name | `Foos` |
| `{{module_table}}` | PLURAL DB table name (lowercase) | `foos` |
| `{{Bean}}` | SINGULAR bean class + object_name + $dictionary key | `Foo` |
| `{{module_lower}}` | lowercase plural for app_list_strings keys | `foos` |
| `{{module_label}}` | human-readable plural label | `Foos` |
| `{{module_label_singular}}` | human-readable singular label | `Foo` |

The Escalation pattern from `data/app/sugar/modules/Escalations/Escalation.php` is enforced: PLURAL folder/table, SINGULAR bean class.

## File count

This template emits ~22 files matching `reference/module_anatomy.md`:

- 1 bean class
- 1 vardefs.php
- 8 metadata files
- 5 Sidecar views
- 2 filters
- 2 menus
- 1 subpanel layout
- 2 dashboards
- 1 module language
- 1 application language entry

Plus optional icons (you provide; see [`skills/sugar-module-icons/SKILL.md`](../../skills/sugar-module-icons/SKILL.md)).

## Usage (manual substitution)

```bash
# Copy the template and substitute
cp -r templates/full_module my_module
find my_module -type f -exec sed -i '' \
  -e 's/{{Module}}/Foos/g' \
  -e 's/{{module_table}}/foos/g' \
  -e 's/{{Bean}}/Foo/g' \
  -e 's/{{module_lower}}/foos/g' \
  -e 's/{{module_label}}/Foos/g' \
  -e 's/{{module_label_singular}}/Foo/g' \
  {} +

# rename the {{Module}} directory
mv my_module/SugarModules/modules/'{{Module}}' my_module/SugarModules/modules/Foos
```

(On Linux, drop the `''` after `-i`.)

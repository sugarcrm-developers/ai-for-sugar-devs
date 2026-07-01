---
name: sugar-mb-export-flow
description: Turn a Module Builder export zip into a clean MLP — extract, identify the doubled prefix, strip it, apply singular-bean-class override, switch `label` to `vname`, remove `help` text, fix metadata file count.
when_to_use:
  - "I have a Module Builder export zip — make it installable"
  - "use Module Builder output as the starting point"
  - "MB-style scaffold"
  - "strip doubled prefix from MB export"
  - "convert MB package to MLP"
not_for:
  - Creating a module from scratch — use sugar-new-module
related_skills:
  - sugar-new-module
  - sugar-package-build
  - sugar-mlp-anatomy
  - sugar-viewdef-editing
---

## When to use this skill

Use this skill when a developer has a Module Builder export zip and wants to turn it into a deployable MLP. Module Builder export is the most reliable starting point — it produces all 22 files of a complete module — but it has several quirks that must be fixed before the package will install cleanly.

## The 6 fix-up steps

### 1. Extract and inspect

```bash
mkdir -p src/SugarModules
unzip mb-export.zip -d mb-extracted
ls mb-extracted/SugarModules/modules/  # see what modules and the prefix MB used
```

### 2. Detect the doubled prefix

Module Builder normally prefixes module/table names with the package short name once (e.g., for package `acme`, table = `acme_foos`). Sometimes — especially when re-exporting — MB doubles the prefix: `acme_acme_foos`. Detect:

```bash
ls mb-extracted/SugarModules/modules/ | grep -E '^([a-z]+)_\1' 
# any hit means a doubled prefix is present
```

If you see hits, decide on the canonical prefix (use the shorter form `acme_<module>`) and rename everything — files, directories, table names in vardefs.php, `module_dir`/`module_name`/`table_name` on the bean class, `$dictionary` key, all `to_module` references in language and relationship files.

### 3. Apply the singular-bean-class override

MB sometimes outputs `class acme_Foos extends SugarBean` (plural class). Sugar wants the SINGULAR form (`class Foo extends SugarBean`). For each module:

- Rename the bean PHP file to the SINGULAR form: `Foo.php`
- Change `class acme_Foos` to `class Foo`
- Set `public $object_name = 'Foo';` (singular)
- Keep `public $module_dir = 'Foos';` `public $module_name = 'Foos';` `public $table_name = 'foos';` (plural)
- In `vardefs.php`, change `$dictionary['acme_Foos']` to `$dictionary['Foo']`

See `[[sugar-new-module]]` for the Escalation pattern.

### 4. Switch `label` to `vname` in every vardef

```bash
# scan for 'label' keys in vardefs (must become vname)
grep -rn "'label'" mb-extracted/SugarModules/modules/*/vardefs.php
grep -rn "'label'" mb-extracted/SugarModules/relationships/vardefs/
```

For every match in a vardef (NOT in viewdefs or layoutdefs — those still use 'label'), change `'label' => 'LBL_X'` to `'vname' => 'LBL_X'`. Studio resolves the display label via `vname`.

### 5. Remove all `'help'` text from vardefs

```bash
grep -rn "'help'" mb-extracted/SugarModules/modules/*/vardefs.php
```

Delete any `'help' => '...'` entries from vardefs. Labels carry the help via `LBL_*` entries; adding `'help'` keys creates inconsistencies between MB and Studio.

### 6. Confirm metadata file count

Each module should have exactly these in `metadata/`:
- detailviewdefs.php
- editviewdefs.php
- listviewdefs.php
- searchdefs.php
- popupdefs.php
- studio.php
- SearchFields.php
- subpaneldefs.php

If MB skipped any, copy from a sibling module and adapt. See `[[sugar-new-module]]` for the canonical list.

## Concrete fixup script (illustrative)

```php
<?php
// scripts/fixup_mb_export.php
$src = 'mb-extracted/SugarModules/modules';
foreach (glob("$src/*") as $moduleDir) {
    $plural = basename($moduleDir);                          // e.g., 'acme_Foos'
    // Detect doubled prefix
    if (preg_match('/^([a-z]+)_\1_(.+)$/', $plural, $m)) {
        $newPlural = $m[1] . '_' . $m[2];                    // 'acme_Foos'
        rename($moduleDir, "$src/$newPlural");
        $plural = $newPlural;
    }
    // Detect bean class file (any file matching the module name)
    $beanFile = "$moduleDir/$plural.php";
    if (file_exists($beanFile)) {
        $singular = preg_replace('/s$/', '', $plural);       // crude — usually fine for English
        $contents = file_get_contents($beanFile);
        $contents = str_replace("class $plural", "class $singular", $contents);
        $contents = str_replace("\$object_name = '$plural'", "\$object_name = '$singular'", $contents);
        file_put_contents("$moduleDir/$singular.php", $contents);
        unlink($beanFile);
    }
    // Switch label→vname in vardefs.php
    $vardefs = "$moduleDir/vardefs.php";
    if (file_exists($vardefs)) {
        $c = file_get_contents($vardefs);
        $c = preg_replace("/'label'\s*=>/", "'vname' =>", $c);
        $c = preg_replace("/'help'\s*=>\s*'[^']*'\s*,?\s*/", '', $c);  // strip help
        file_put_contents($vardefs, $c);
    }
}
```

## Common gotchas

| Symptom | Root cause | Fix |
|---------|------------|-----|
| `acme_acme_foos` table | MB doubled the prefix | Strip one prefix (step 2) |
| "Bean class not found" | Plural class name remained | Apply singular override (step 3) |
| Studio shows raw `LBL_X` | Vardef has `label` instead of `vname` | Step 4 |
| Help text overrides label | `'help'` key still present | Step 5 |
| "Sidebar shows the module but icons missing" | Icons folder from MB not registered as `image_dir` in installdefs | See `[[sugar-module-icons]]` |
| Sidecar viewdef from MB has gaps in numeric keys after editing | MB sometimes writes non-sequential numeric keys | Renumber. See `[[sugar-viewdef-editing]]` |

## References

- `[[sugar-new-module]]` — target end-state structure
- `[[sugar-module-icons]]` — `image_dir` setup
- `[[sugar-viewdef-editing]]` — safe Sidecar viewdef edits
- `[[sugar-mlp-anatomy]]` — installdefs section routing
- `[[sugar-package-build]]` — pack.php

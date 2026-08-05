---
name: sugar-studio-debugging
description: Symptom-to-root-cause table for SugarCRM install/Studio gotchas — "No Image", raw LBL_, missing sidebar entry, empty dropdown, missing subpanel, Studio doesn't show new field, duplicate-index error, doubled prefix.
when_to_use:
  - "module shows No Image"
  - "raw LBL_ string instead of label"
  - "module missing from sidebar"
  - "dropdown empty"
  - "subpanel missing"
  - "field doesn't appear in Studio"
  - "duplicate index error at install"
  - "package installs but module broken"
not_for:
  - Designing new behavior — use the topic skill
related_skills:
  - sugar-module-icons
  - sugar-application-language
  - sugar-mlp-anatomy
  - sugar-mb-export-flow
  - sugar-new-module
  - sugar-viewdef-editing
---

## When to use this skill

Use this skill as a triage cheat sheet whenever a package installs but the result in Studio / on the record view / in the sidebar isn't what was expected. Each row points to the topic skill with the fix.

## The triage table

| Symptom | Most-likely root cause | Skill to consult |
|---------|------------------------|------------------|
| Sidebar shows the new module name with a "No Image" placeholder | `image_dir` is in `$manifest` instead of `$installdefs`. Sugar's ModuleInstaller.php:1120 only reads from installdefs. | `[[sugar-module-icons]]` |
| Studio shows raw `LBL_XYZ` instead of the human label | Either the language file isn't registered, OR the vardef uses `'label'` instead of `'vname'`. | `[[sugar-custom-field]]` (vname) / `[[sugar-mb-export-flow]]` (MB exports keep label=) |
| Module not in sidebar at all (after install) | `moduleList` not registered at application scope — must be in `SugarModules/language/application/<lang>.lang.php` with `to_module=>'application'`. | `[[sugar-application-language]]` |
| Enum dropdown empty on record view | `$app_list_strings['your_list']` registered at module scope instead of application scope. | `[[sugar-application-language]]` |
| Subpanel doesn't show on record view | Either the Sidecar layout file is missing, OR it was placed in `installdefs['copy']` instead of `installdefs['sidecar']`. | `[[sugar-mlp-anatomy]]` |
| New field added to OOB module doesn't appear on record view | Sidecar record views don't auto-place new fields — admin must drag-place via Studio. | `[[sugar-custom-field]]` |
| Install fails with "duplicate index" error | Manually redeclared an index that `uses=>['basic']` or `is_sync_key=true` already auto-creates. | `[[sugar-new-module]]` |
| Sidebar shows two prefixes (e.g., `acme_acme_foos`) | Module Builder doubled the prefix on export. | `[[sugar-mb-export-flow]]` |
| Notes "Related to" picker doesn't list my custom module | `$app_list_strings['parent_type_display']['<Module>']` missing at application scope. | `[[sugar-application-language]]` and `[[sugar-notes-attachment]]` |
| Notes subpanel installed but creating a note doesn't link to the parent | `relationship_role_column_value` doesn't match the module name (PLURAL form). | `[[sugar-notes-attachment]]` |
| "Bean class not found" at runtime | Bean class name/filename plural instead of singular (Escalation pattern violated). | `[[sugar-new-module]]` |
| Address fields render as 5 stacked single fields instead of grouped | Missing `'type' => 'fieldset'` wrapper in the record view (vardef `'group'` alone isn't enough). | `[[sugar-address-grouping]]` |
| Sidecar viewdef edit corrupts the file | Used non-greedy regex on a numeric-keyed array. | `[[sugar-viewdef-editing]]` |
| New module sidebar entry appears but clicking it 404s | Module routes not registered — likely `module_dir` mismatch with folder name. | `[[sugar-new-module]]` |
| `help` text appearing in tooltips inconsistently | Vardef has `'help'` key — remove it; labels carry the help via `LBL_*`. | `[[sugar-mb-export-flow]]` |

## Diagnostic checklist

When something is wrong, run through these in order:

1. **Was Quick Repair & Rebuild done after install?** Most metadata changes need it.
2. **Inspect `sugarcrm.log`** — Sugar logs ModuleInstaller activity and fatal errors there.
3. **Check the unpacked install** — Sugar extracts the MLP to `cache/upload/upgrades/module/`. Inspect to see what actually landed.
4. **Compare `installdefs` to the manifest in the zip** — `unzip -p <zip> manifest.php | head -100`. Look for `image_dir` in installdefs, `sidecar` entries, `language` entries with correct `to_module`.
5. **Check application-scope language file actually loaded** — `cache/include/language/application/<lang>.lang.php` after Repair should contain your strings.

## References

- `[[sugar-mlp-anatomy]]` — installdefs section reference
- `[[sugar-module-icons]]` — image_dir specifics
- `[[sugar-application-language]]` — moduleList/parent_type_display/$app_list_strings
- `[[sugar-mb-export-flow]]` — MB export quirks (doubled prefix, label→vname, help)
- `[[sugar-new-module]]` — Escalation singular/plural pattern
- `[[sugar-notes-attachment]]` — Notes-specific gotchas
- `[[sugar-address-grouping]]` — Quotes fieldset pattern
- `[[sugar-viewdef-editing]]` — numeric-key safety
- `reference/common_gotchas.md` — broader gotcha reference
- `data/app/sugar/ModuleInstall/ModuleInstaller.php:1120` — image_dir reader

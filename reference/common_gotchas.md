# Common Gotchas

Symptom → root cause → fix table for ~15 known traps. Companion skill: [`skills/sugar-studio-debugging/SKILL.md`](../skills/sugar-studio-debugging/SKILL.md).

| # | Symptom | Root Cause | Fix |
|---|---------|------------|-----|
| 1 | Sidebar shows new module name but "No Image" placeholder | `image_dir` placed in `$manifest` instead of `$installdefs` — `ModuleInstaller.php:1120` only reads from installdefs | Move `image_dir` into `$installdefs`. See [sugar-module-icons](../skills/sugar-module-icons/SKILL.md). |
| 2 | Module Builder export has table names like `acme_acme_foos` | MB prefixed twice (re-export quirk) | Strip one prefix; rename files, dirs, `module_dir`, `table_name`, `$dictionary` key consistently. See [sugar-mb-export-flow](../skills/sugar-mb-export-flow/SKILL.md). |
| 3 | Studio displays raw `LBL_X` instead of the human label | Vardef uses `'label'` instead of `'vname'` (Studio resolves via `vname`) | Replace `'label' =>` with `'vname' =>` in all vardefs (NOT viewdefs/layoutdefs — those use `'label'`). |
| 4 | Module missing from sidebar entirely | `moduleList` not registered at application scope | Register in `SugarModules/language/application/<lang>.lang.php` with `to_module=>'application'`. See [sugar-application-language](../skills/sugar-application-language/SKILL.md). |
| 5 | Enum dropdown empty on a vardef-driven field | `$app_list_strings['<your_list>']` at module scope or missing | Move to application-scope language file. |
| 6 | Subpanel missing on record view | Sidecar layout file routed via `installdefs['copy']` instead of `installdefs['sidecar']` | Re-route via `installdefs['sidecar']`. See [sugar-mlp-anatomy](../skills/sugar-mlp-anatomy/SKILL.md). |
| 6b | Subpanel completely absent on the parent side of a relationship (even after install + QRR) | The 3 subpanel files for that side weren't generated. Easy to miss when batch-generating relationships — the relate field + vardefs install cleanly, but the parent record has no list of children anywhere. | For each relationship, run the "subpanel decision step" — almost always yes on the parent (lhs) side. Generate `layoutdefs/<rel>_<Lhs>.php` + `clients/base/layouts/subpanels/<rel>_<Lhs>.php` + `clients/mobile/layouts/subpanels/<rel>_<Lhs>.php`. See [sugar-relationship](../skills/sugar-relationship/SKILL.md). |
| 7 | New field on OOB module doesn't appear on record view | Sidecar record views don't auto-place new fields | Admin must drag-place via Studio. Document this in skill summaries. |
| 8 | Install fails with "duplicate index" error | Manually declared an index that `uses=>['basic']` or `is_sync_key=true` already auto-creates | Remove the manual declaration. |
| 9 | "Bean class not found" at runtime | Bean class name plural instead of singular, or filename doesn't match class | Apply Escalation pattern: SINGULAR class/filename, PLURAL folder/table. See [sugar-new-module](../skills/sugar-new-module/SKILL.md). |
| 10 | Notes "Related to" picker doesn't list custom module | `$app_list_strings['parent_type_display']['<Module>']` missing at application scope | Register application-scope. See [sugar-notes-attachment](../skills/sugar-notes-attachment/SKILL.md). |
| 11 | Notes subpanel exists but creating a note doesn't link to parent | `relationship_role_column_value` doesn't match the module name (must be PLURAL form, e.g., `Foos`) | Match exactly. |
| 12 | Address fields render as 5 stacked rows | Vardef has `'group'` set but record-view fieldset wrapper missing | Add `'type' => 'fieldset'` wrapper in record view. See [sugar-address-grouping](../skills/sugar-address-grouping/SKILL.md). |
| 13 | Sidecar viewdef edit corrupts the file | Used non-greedy regex on a nested numeric-keyed array | Use `array_values()` to renumber after edits, or a paren-balanced parser. See [sugar-viewdef-editing](../skills/sugar-viewdef-editing/SKILL.md). |
| 14 | Help text inconsistent between Studio and MB | Vardef has `'help'` key (MB sometimes emits it) | Remove all `'help'` keys; labels carry the help via `LBL_*`. |
| 15 | Pack.php generates zip but Sugar rejects install | Manifest missing `regex_matches` under `acceptable_sugar_versions` | Add regex entry like `'(26|25|14)\\..*$'`. See [sugar-package-build](../skills/sugar-package-build/SKILL.md). |
| 16 | HTTP code fatals with `Call to undefined method ::getInstance()` | `ExternalResourceClient::getInstance()` used — that method doesn't exist | Use `new ExternalResourceClient()`. See [sugar-external-resource-client](../skills/sugar-external-resource-client/SKILL.md). |
| 17 | Application-scope language strings not picked up | Registered with `to_module => '<Module>'` instead of `'application'` | Match `to_module` to the lookup scope: `application` for `$app_list_strings`, `<Module>` for `$mod_strings`. |
| 18 | Sugar logs "PHP Fatal error" but module installs | Generated PHP has syntax error; ModuleInstaller doesn't always halt | Lint every PHP file: `find src -name '*.php' -exec php -l {} +`. |

## Quick diagnostic recipe

When something is broken after install:

1. Run **Admin → Repair → Quick Repair & Rebuild**
2. Check `sugarcrm.log` for ModuleInstaller errors
3. Look at the unpacked install in `cache/upload/upgrades/module/`
4. Verify the in-zip `manifest.php`:
   ```bash
   unzip -p releases/<package>.zip manifest.php | head -100
   ```
   Look for: `image_dir` in installdefs, `sidecar` entries, language entries with correct `to_module`.
5. Verify `cache/include/language/application/<lang>.lang.php` after Repair contains your `$app_list_strings`

## See also

- [`skills/sugar-studio-debugging/SKILL.md`](../skills/sugar-studio-debugging/SKILL.md) — skill-form of this table with deeper links
- [`reference/installdefs_cheatsheet.md`](installdefs_cheatsheet.md) — section routing
- [`reference/module_anatomy.md`](module_anatomy.md) — file-by-file module walkthrough

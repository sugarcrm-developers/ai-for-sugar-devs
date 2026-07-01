# AGENTS.md — Authoritative Execution Contract for AI Agents

**For humans**: Read `README.md` instead.

**For AI agents**: This is the binding execution contract. All package generation must comply with these specifications.

---

## Project Purpose

A production-grade, AI-driven system for generating installable, upgrade-safe SugarCRM Module Loadable Packages (MLPs) from structured feature requests. Designed for deterministic, agent-agnostic execution (Claude, Copilot, ChatGPT, Codex, etc).

---

## Recent Mandates (v2)

The following rules were captured from real MLP work and apply to every generated package. They take precedence over older guidance in this document if there is ever a conflict.

1. **`_c` suffix on custom field names is mandatory** — Sugar core convention. ALL fields added to OOB modules must end in `_c`. Studio rejects fields without it.
2. **Use `vname` not `label` in vardefs** — Studio resolves the display label via `vname` → `LBL_*` lookup; `label` is for older Bean/MB-internal use and will be ignored on Sidecar metadata.
3. **NO `'help'` text on vardefs** — labels carry the help via `LBL_*` entries; adding `'help'` keys creates inconsistency between MB and Studio.
4. **`image_dir` belongs in `installdefs`, NOT `$manifest`** — Sugar's `ModuleInstaller.php:1120` only reads `image_dir` from `installdefs`. Placing it in `$manifest` silently does nothing → "No Image" placeholder forever.
5. **Singular/plural (Escalation pattern)** — Module folder name and DB table name are PLURAL (`Escalations`, `escalations`); bean class name, `object_name`, and `$dictionary` key are SINGULAR (`Escalation`). Mismatch breaks bean lookup.
6. **Relationships: lhs = parent, rhs = child** — `lhs_module` is the target of the relate (e.g., Accounts); `rhs_module` is the side with the link/relate/id triplet (e.g., Contacts). `id_name` = `<rel><lhs_lower>_ida`.
7. **M:M-with-join-table even for declared 1:M (MB convention)** — Module Builder generates a join table for every relationship, even ones declared 1:M. The only exception is Notes 1:M.
8. **Notes 1:M: parent_id/parent_type with `relationship_role_column`** — Notes attach via `parent_type` discriminator + `parent_id` FK, NOT a join table. Set `relationship_role_column => 'parent_type'`.
9. **`$app_list_strings` for module list + dropdowns is application-scope** — Place `moduleList`, `moduleListSingular`, `moduleIconList`, and any enum dropdowns in `SugarModules/language/application/<lang>.lang.php` registered with `to_module => 'application'`. Module-scope language files won't surface them.
10. **Sidecar subpanel layouts** (`clients/base/layouts/subpanels/...`) must be registered in `installdefs['sidecar']` — not `copy` — for them to be picked up.
11. **`basic` template auto-adds primary + standard indices** — When `uses => ['basic']` is in vardefs, do NOT redeclare `id` primary index or standard auditing indices; that causes duplicate-key errors at install.
12. **`is_sync_key=true` auto-adds a unique index** — Don't redeclare it; install will fail with duplicate index name.
13. **Sidecar viewdefs use explicit numeric array keys** — Editing requires careful renumbering; use a paren-balanced parser, not non-greedy regex. See `[[sugar-viewdef-editing]]`.
14. **Address grouping** — to visually group address sub-fields like Quotes, set `'group' => '<address_prefix>'` AND `'group_label' => 'LBL_<...>'` on each vardef AND wrap them in a `'type' => 'fieldset'` block in the record view. Vardef grouping alone is not enough.
15. **Drop doubled prefix when MB doubles it** — Module Builder sometimes prepends the package short name twice (`acme_acme_<module>`). Strip one prefix before packaging or names won't match the Studio expectations.
16. **OOB modules: new fields don't auto-appear on Sidecar record views** — After installing a new custom field on an OOB module, an admin must drag the field onto the record view via Studio. Note this in every skill summary that targets an OOB module.

---

## Atomic, Autonomous, and Deterministic Generation (Mandatory)

- **All package generation must be atomic, self-contained, and require no user review or stepwise confirmation.**
- **Agents must generate, verify, and complete the entire package in one operation**, with no file-by-file or interactive output.
- **All required files, directories, and content must be created and validated by the agent before finishing.**
- **No user prompts, explanations, or manual steps are allowed.**
- **Output must be a single, deterministic, raw file entry list** as specified in Output Format Contract section.

---

## Input Format Specification

Users provide feature requests in the following format (defined in `/prompts/feature_request_format.md`):

```
Feature Type: <Type>                   # Logic Hook, Custom Field, Relationship, etc.
Module: <Module Name>                  # e.g., Accounts, Contacts
Trigger: <Event>                       # e.g., after_save, before_delete (if applicable)
Condition:
  <field>: <value>                     # Optional filtering
Action:
  type: <action_type>                  # webhook, update_field, etc.
  method: <HTTP method>                # POST, GET, etc. (if applicable)
  url: <URL>                           # e.g., https://webhooks.com/mywebhook
  payload: <payload_type>              # full bean, custom JSON, etc.
Package Name: <MLP Name>               # Unique package identifier
```

---

## Execution Workflow

1. **Parse feature request** using format specified above
2. **Consult relevant prompt file** from `/prompts/<feature_type>.md`
3. **Consult `/reference/MASTER_REFERENCE.md`** for SugarCRM extension framework specifications
4. **Generate complete package** in `/build/<PackageName>/` with all required files
5. **Output raw file entry list** per Output Format Contract (do not output explanations or markdown)

---

## Required Directory Structure

All packages must follow this structure:

```
/build/<PackageName>/
    src/                                    # All Sugar files (Extension Framework only)
        custom/
            Extension/
                modules/
                    <Module>/Ext/
                        LogicHooks/
                        Vardefs/
                        Language/
                fields/
                    <fieldtype>/
                application/Ext/
                    TableDictionary/
            metadata/
    pack.php                                # Executable package builder (shebang)
    version                                 # Single line: semantic version
    releases/
        .keep                               # Directory marker
```

---

## Required Sugar File Structure (Extension Framework ONLY)

**MANDATORY RULES:**

1. **All customizations must use the Extension Framework only** (`custom/Extension/...`, `custom/metadata/...`)
2. **It is FORBIDDEN to write directly to `src/modules/` or any non-Extension Framework path**
3. **All files must be under `src/custom/Extension/...`, `src/custom/modules/...`, or `src/custom/metadata/`**
4. **No core file overrides or modifications are permitted**
5. **All files must be upgrade-safe and installable via Module Loader**

---

## Logic Hook Registration (Mandatory for Logic Hooks)

1. **Logic hook registration must be placed in:** `custom/Extension/modules/<Module>/Ext/LogicHooks/<filename>.php`
2. **It is FORBIDDEN to register hooks directly in `logic_hooks.php`** (including `custom/modules/<Module>/logic_hooks.php` for distributed packages)
3. **Hook definition must reference a class and method** (namespaced or non-namespaced per `/prompts/logic_hook.md`)
4. **Consult `/reference/MASTER_REFERENCE.md` for all 12 hook types and signatures**

---

## Custom Fields (Mandatory Specifications)

**For simple fields (Vardefs):**
- **Consult `/prompts/custom_field.md`** for complete specifications
- **Field names MUST end with `_c` suffix** (e.g., `customer_priority_c`)
- **Vardef source property MUST be `'custom_fields'`**
- **Properties must be set individually** (dictionary key assignment, NOT array merge)

**For complex fields (Dropdowns, Multiselect, Encrypt):**
- **Use ModuleInstaller approach** (see `/prompts/custom_field.md`)
- **DO NOT use vardef properties directly**

**For custom field types (Highlight, Color Picker, etc.):**
- **Consult `/prompts/custom_field_type.md`** for specifications
- **Requires PHP class, JavaScript, and Handlebars templates**

---

## Manifest and Packaging (Mandatory)

1. **Each package MUST generate a `manifest.php` at the root of the zip**
2. **Manifest MUST include all required fields:**
   - `id`, `name`, `description`, `version`, `type`, `author`
   - `is_uninstallable` (string 'true', not boolean)
   - `published_date`, `acceptable_sugar_versions`, `acceptable_sugar_flavors`
3. **`installdefs['copy']` MUST be fully auto-populated** for every file under `src/` (no static/manual entries)
4. **Manifest MUST be generated dynamically** inside the zip by `pack.php`, never hardcoded
5. **The `releases/` directory MUST always exist** for zip output
6. **acceptable_sugar_versions MUST use regex:** `(26|25|14)\\..*$`
7. **acceptable_sugar_flavors MUST include:** `'ENT'`, `'ULT'`, `'PRO'` (or specify as needed)

---

## pack.php Specification (Mandatory)

Every generated package **MUST** include a proper executable `pack.php` that:

1. **Has shebang header:** `#!/usr/bin/env php`
2. **Is executable** (PHP file that can be run from command line)
3. **Reads version** from command-line argument or `version` file
4. **Creates `releases/` directory** if it doesn't exist
5. **Uses ZipArchive to create zip file** with naming: `sugarcrm-{packageID}-{version}.zip`
6. **Recursively scans `src/` directory** for all files
7. **Auto-populates `installdefs['copy']`** by scanning src/ directory (dynamic, not static)
8. **Generates manifest.php dynamically** inside the zip (not hardcoded)
9. **Outputs to stdout** during execution
10. **Exits with success message** after zip creation

**Reference template:** `/templates/minimal_mlp/pack.stub.php`

---

## HTTP Requests (MANDATORY)

**CRITICAL - ALL HTTP requests in generated PHP code MUST use ExternalResourceClient:**

1. **Instantiation:** `$client = new ExternalResourceClient();` (constructor only)
2. **NEVER call** `ExternalResourceClient::getInstance()` (this method does NOT exist)
3. **NEVER use:** curl, file_get_contents, fopen, stream_get_contents, or any direct HTTP methods
4. **Reference:** https://support.sugarcrm.com/documentation/sugar_developer/sugar_developer_guide_25.1/integration/externalresourceclient/
5. **MANDATORY import (must appear in every file that uses it):** 
use Sugarcrm\Sugarcrm\Security\HttpClient\ExternalResourceClient;
---

## Output Format Contract (Mandatory)

**All package generation MUST output ONLY raw file entries with NO explanations, markdown, or commentary:**

- **First line MUST begin with:** `File: build/<PackageName>/`
- **Each file MUST be prefixed with:** `File: build/<PackageName>/<path>`
- **Content is the literal file content** (no escaping unless needed)
- **No markdown, no headers, no explanations**
- **No stray whitespace** before or after entries
- **No user prompts or confirmation requests**

**Example output format:**
```
File: build/MyPackage/version
1.0.0

File: build/MyPackage/pack.php
#!/usr/bin/env php
<?php
...content...

File: build/MyPackage/src/custom/Extension/modules/Accounts/Ext/LogicHooks/...
...content...
```

---

## Reference Documentation (Agents MUST Consult)

| Location | Purpose | Mandatory For |
|----------|---------|---------------|
| `/prompts/logic_hook.md` | Logic hook specifications | Logic hook packages |
| `/prompts/custom_field.md` | Custom field vardef specs | Custom field packages |
| `/prompts/custom_field_type.md` | Custom field type specs | Field type packages |
| `/prompts/relationship.md` | Relationship specs | Relationship packages |
| `/reference/MASTER_REFERENCE.md` | Complete SugarCRM specs | All packages (validation) |
| `/reference/sugar_developer_guide_25.2_md/` | Official SugarCRM docs | Specification references |
| `/prompts/feature_request_format.md` | Input format | Input parsing |
| `/prompts/feature_generator.md` | Master generation prompt | All packages |

---

## Prohibited Actions (MANDATORY ENFORCEMENT)

**Agents MUST NOT:**

- ❌ Require user review, approval, or file-by-file confirmation
- ❌ Output or create files stepwise or interactively
- ❌ Copy or embed instructional content from templates or reference
- ❌ Override or modify core files (src/modules/ paths forbidden)
- ❌ Output sugar_developer_guide content or markdown
- ❌ Use a global or shared pack.php
- ❌ Create fields without `_c` suffix
- ❌ Skip language files for any customization
- ❌ Use `ExternalResourceClient::getInstance()` or direct HTTP methods
- ❌ Hardcode manifest.php (must be generated dynamically)
- ❌ Use non-Extension Framework paths
- ❌ Place hook definitions in logic_hooks.php (use Extension Framework)
- ❌ Provide explanations or markdown in output
- ❌ Include user prompts in output

---

## Code Quality Standards (All Generated Code)

- **Avoid dynamic properties** — use typed properties with return types on all methods
- **Use strict comparisons** — `===`, `!==` (not `==`, `!=`)
- **Validate all array keys** before access
- **Use typed exceptions** — avoid bare `Exception` class
- **Use namespaced classes** for hooks when possible (automatic PSR-4 path resolution)
- **Include proper error handling** — try/catch with logged exceptions
- **Log all external interactions** — webhooks, API calls, database modifications

---

## Quality Assurance Checklist (Agent Verification)

Before finishing, agents MUST verify:

- [ ] All files under `src/custom/Extension/` or `src/custom/metadata/` (never `src/modules/`)
- [ ] Custom field names end with `_c` suffix
- [ ] Language files included for all customizations
- [ ] pack.php is executable with shebang and uses ZipArchive
- [ ] Manifest generated dynamically in pack.php (not hardcoded)
- [ ] HTTP requests use ExternalResourceClient (not curl or file_get_contents)
- [ ] Logic hook definitions in Extension Framework (never logic_hooks.php for plugins)
- [ ] version file exists with semantic version
- [ ] releases/.keep file exists
- [ ] Output is raw file entries (no markdown or explanations)
- [ ] No core file overrides

---

## Execution Verification

**Agent execution is COMPLETE when:**

1. ✅ All required files generated in `/build/<PackageName>/`
2. ✅ All files verified against extension framework requirements
3. ✅ `pack.php` is executable and functional
4. ✅ Manifest will be generated dynamically by pack.php
5. ✅ Output is raw file entry list (no explanations)

**Execution is FAILED if:**

- ❌ Any files created outside Extension Framework paths
- ❌ Core files modified or overridden
- ❌ Language files missing
- ❌ Custom fields lack `_c` suffix
- ❌ pack.php not executable or hardcoded manifest
- ❌ Output includes explanations or markdown
- ❌ HTTP requests use curl or direct methods

---

## This Document is Authoritative

This file is **the binding execution contract** for any AI agent generating Sugar MLPs from this project.

**All outputs MUST be atomic, deterministic, and self-contained per package.**

**Deviations from this contract are violations of the specification.**

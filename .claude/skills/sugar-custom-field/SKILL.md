---
name: sugar-custom-field
description: Add a simple custom field to a SugarCRM module via Vardef Extension Framework. Field name MUST end with `_c`. Use when a developer wants to add a varchar/text/int/bool/datetime/decimal/relate field to an existing module.
when_to_use:
  - "add a custom field to Accounts"
  - "create a varchar/text/datetime/bool field"
  - "add field <name> to module <Module>"
  - "extend an OOB module with a new field"
not_for:
  - Highlight / color-picker / template-driven field types — use sugar-custom-field-type
  - Creating a whole new module — use sugar-new-module
  - Relationships — use sugar-relationship
related_skills:
  - sugar-custom-field-type
  - sugar-application-language
  - sugar-package-build
  - sugar-studio-debugging
---

## When to use this skill

Use this skill when a developer needs to add a simple field (varchar, text, int, bool, datetime, decimal, relate) to an existing Sugar module via the Vardef Extension Framework. Custom field names MUST end with the `_c` suffix — this is mandatory in Sugar core convention and required for the field to be treated as a custom field by Studio. For new-module-internal fields (where the whole module is custom), see `[[sugar-new-module]]`.

# Custom Field (Vardef) Generator Prompt for Sugar MLP Generator

You are an AI agent generating installable, upgrade-safe SugarCRM Module Loadable Packages (MLPs) for custom fields using Vardefs from structured feature requests.

## CRITICAL DISTINCTION
**This prompt is for CUSTOM FIELDS via Vardefs (simple field definitions using standard field types).**

For custom field types (highlight, custom classes with templates/JS), see `custom_field_type.md`.

**REFERENCE**: https://support.sugarcrm.com/documentation/sugar_developer/sugar_developer_guide_25.1/data_framework/vardefs/manually_creating_custom_fields/#Using_the_Vardef_Extensions

## Instructions (Default)
- Generate the entire package atomically and autonomously, with no user review, file-by-file output, or stepwise confirmation.
- All required files, directories, and content must be created and validated before finishing.
- Output must be a single, deterministic, raw file entry list as specified below.
- No explanations, markdown, or user prompts.

## Custom Field Implementation (Vardef Extension Framework)

### Required Files Per Custom Field Package

1. **Vardef File** (`custom/Extension/modules/<Module>/Ext/Vardefs/<filename>.php`)
   - Define field properties using `$vardefs['fields'][<field_name_c>]` array
   - Field name MUST end with `_c` suffix
   - Use ONLY standard field types: varchar, text, datetime, int, bool, decimal, relate, etc.
   - DO NOT use `custom_type` parameter (that's for custom field types)
   - Include properties: `name`, `type`, `label`, `required`, `len` (if applicable)

2. **Language File** (`custom/Extension/modules/<Module>/Ext/Language/en_us.lang.php`)
   - Define language string keys (e.g., `LBL_FIELD_NAME`)
   - Use `$GLOBALS['app_list_strings']` ONLY for dropdown options if field requires them
   - Define labels and descriptions for the field

3. **pack.php** (executable package builder)
   - Read version from command-line argument or `version` file
   - Create releases/ directory if it doesn't exist
   - Build zip file with dynamic manifest and file copying
   - Auto-populate installdefs['copy'] by scanning src/ directory recursively
   - Generate manifest.php inside zip with proper metadata

4. **version** file
   - Single line containing semantic version (e.g., 1.0.0)

5. **releases/.keep** file
   - Empty marker file to preserve directory in version control

## Vardef Field Definition Standard
```php
$vardefs['fields']['field_name_c'] = array(
    'name' => 'field_name_c',
    'type' => 'varchar',  // or text, int, bool, datetime, decimal, relate, etc.
    'label' => 'LBL_FIELD_NAME',
    'required' => false,
    'len' => 255,  // for varchar/text types
    'default' => '',  // optional
);
```

## Output Format
- The first line of output must begin with: File: build/<PackageName>/
- Each file must be prefixed with: File: build/<PackageName>/<path>
- No markdown, explanations, or commentary.
- No stray whitespace or user prompts.

## Prohibited Actions
- Never use `custom_type` parameter in vardef.
- Never add template files (.hbs, .js) for vardef-only fields.
- Never skip the language file.
- Never create fields without `_c` suffix.
- Never override core files.
- Never use non-Extension Framework paths.
- Never create dropdown lists without explicit requirement.

## Common gotchas

| Symptom | Fix |
|---------|-----|
| Field doesn't show in Studio after install | Drag the field onto the record view via Studio — OOB modules don't auto-place new fields on Sidecar record views |
| Field name without `_c` rejected | Sugar core convention: ALL custom field names must end in `_c` |
| Studio displays raw `LBL_*` instead of label | Confirm `Ext/Language/<lang>.lang.php` is registered for the module and uses `vname` reference, not `label` |
| Dropdown empty | Enum dropdowns belong in `$app_list_strings`, application-scope — see `[[sugar-application-language]]` |

## References

- `[[sugar-custom-field-type]]` — when you need JS/HBS templating (highlight, color picker)
- `[[sugar-application-language]]` — application-scope $app_list_strings for enums
- `[[sugar-studio-debugging]]` — symptom→fix table for Studio surprises
- `[[sugar-package-build]]` — wrap into deployable zip

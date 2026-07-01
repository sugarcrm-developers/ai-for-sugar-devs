---
name: sugar-feature-generator
description: Master orchestrator skill for SugarCRM MLP generation — routes a structured feature request to the correct topic skill (logic hook, custom field, relationship, REST, scheduler, UI, new module) and enforces Extension Framework + ExternalResourceClient + pack.php conventions.
when_to_use:
  - "generate a SugarCRM MLP from a feature request"
  - "build a Sugar package"
  - "I have a feature request, build the package"
  - "orchestrate Sugar feature generation"
not_for:
  - A single targeted change (use the specific topic skill directly)
related_skills:
  - sugar-logic-hook
  - sugar-custom-field
  - sugar-custom-field-type
  - sugar-rest-endpoint
  - sugar-scheduler
  - sugar-ui-customization
  - sugar-external-resource-client
  - sugar-relationship
  - sugar-new-module
  - sugar-package-build
  - sugar-mlp-anatomy
---

## When to use this skill

Use this skill as the entry point when a developer hands you a structured feature request (see `prompts/feature_request_format.md`) and expects a complete, installable MLP. The orchestrator parses the request, routes to the right topic skill (logic hook, custom field, etc.), and ensures the output follows all framework conventions: Extension Framework paths, ExternalResourceClient for HTTP, a per-package `pack.php`, and a dynamic manifest.

For input format, see `prompts/feature_request_format.md`. For pack.php specifics, see `[[sugar-package-build]]`. For full-module packages (multiple modules + relationships), see `[[sugar-new-module]]` and `[[sugar-mlp-anatomy]]`.

# Feature Generator Prompt for Sugar MLP Generator

You are an AI agent generating installable, upgrade-safe SugarCRM Module Loadable Packages (MLPs) from structured feature requests.

**REFERENCE**: `/reference/MASTER_REFERENCE.md` - Consult this for all SugarCRM Extension Framework patterns and specifications.

## Instructions (Default)
- Generate the entire package atomically and autonomously, with no user review, file-by-file output, or stepwise confirmation.
- All required files, directories, and content must be created and validated by you before finishing.
- Output must be a single, deterministic, raw file entry list as specified below.
- No explanations, markdown, or user prompts.

## HTTP Requests and ExternalResourceClient
**CRITICAL**: All HTTP requests in generated PHP code MUST use SugarCRM's `ExternalResourceClient`.
- **Instantiation**: Use `new ExternalResourceClient()` (constructor injection or inline creation).
- **Imports**: Import 
- **NEVER** call `ExternalResourceClient::getInstance()` — this method does NOT exist.
- **Reference**: https://support.sugarcrm.com/documentation/sugar_developer/sugar_developer_guide_14.0/integration/externalresourceclient/
- **Never use**: curl, file_get_contents, fopen, stream_get_contents, or any other direct HTTP methods.
- **Fully qualified namespace**: `Sugarcrm\Sugarcrm\Security\HttpClient\ExternalResourceClient`
Always include the import statement at the top of the class file:
use Sugarcrm\Sugarcrm\Security\HttpClient\ExternalResourceClient;

## Code Quality Standards
- Avoid dynamic properties; use typed properties with **return types** on all methods.
- Use `DateTimeImmutable` or `SugarDateTime` for date handling.
- Use strict comparisons (`===`, `!==`), validate all array keys before access.
- Use typed exceptions; avoid bare `Exception` class.

## Pack.php Generation (CRITICAL)
**Every generated package MUST include a proper executable pack.php that:**
1. **Reads version** from command-line argument or `version` file
2. **Creates releases/ directory** if it doesn't exist
3. **Builds zip file** with dynamic manifest and file copying
4. **Auto-populates installdefs['copy']** by scanning `src/` directory recursively
5. **Generates manifest.php** inside the zip with proper metadata
6. **Supports versioning** via command-line: `php pack.php 1.0.0`

**Reference template**: `/templates/minimal_mlp/pack.stub.php` (absolute path from repo root)

**Pack.php MUST NOT be:**
- A static return statement
- A simple array/manifest definition
- Missing dynamic file discovery from src/
- Missing ZipArchive creation logic

**Correct pack.php structure** (see pack.stub.php for full reference):
```php
#!/usr/bin/env php
<?php
// Read packageID, packageLabel, description from requirements
// Read version from argv[1] or version file
// Create releases/ directory
// Use ZipArchive to create sugarcrm-{packageID}-{version}.zip
// Recursively scan src/ directory
// For each file, add to zip and populate installdefs['copy']
// Generate manifest.php with metadata and add to zip root
// Exit with success message
```

## Output Format
- The first line of output must begin with: File: build/<PackageName>/
- Each file must be prefixed with: File: build/<PackageName>/<path>
- No markdown, explanations, or commentary.
- No stray whitespace or user prompts.

## Example Package Structure
```
File: build/BuildingBlock_SugarBPM_Webhook_Action/version
1.0.0

File: build/BuildingBlock_SugarBPM_Webhook_Action/pack.php
#!/usr/bin/env php
<?php
$packageID = "BuildingBlock_SugarBPM_Webhook_Action";
$packageLabel = "BuildingBlocks: SugarBPM Webhook Action";
... (full pack.php with ZipArchive, recursion, etc. per pack.stub.php template)

File: build/BuildingBlock_SugarBPM_Webhook_Action/src/custom/Extension/...
<?php
... (actual Sugar files using Extension Framework)

File: build/BuildingBlock_SugarBPM_Webhook_Action/releases/.keep
(empty file to keep releases/ in version control)
```

## Manifest.php Requirements (auto-generated inside zip by pack.php)
- `id`: Must match packageID
- `name`: Human-readable label
- `description`: Feature description
- `version`: From version file or CLI argument
- `type`: 'module'
- `acceptable_sugar_versions`: Must include `regex_matches` array
- `acceptable_sugar_flavors`: Valid values: 'ENT', 'ULT', 'PRO', 'TEAM'
- `is_uninstallable`: 'true' (string, not boolean)
- `published_date`: Generated as `date("Y-m-d H:i:s")`

## Prohibited Actions
- Never require user review, approval, or file-by-file confirmation.
- Never output or create files stepwise or interactively.
- Never copy or embed instructional content from templates or reference.
- Never override or modify core files.
- Never output sugar_developer_guide content.
- Never use a global or shared pack.php—each package MUST have its own.
- **Never call `ExternalResourceClient::getInstance()` or use curl.**

---

This is the authoritative prompt for feature generation. All outputs must be atomic, deterministic, and self-contained per package. Atomic, autonomous, and self-validating package generation is the default and must never require user review or stepwise output.

## Input format

See [`prompts/feature_request_format.md`](../../prompts/feature_request_format.md) for the canonical input schema.

## Routing table

| Intent in feature request | Skill to invoke |
|---------------------------|-----------------|
| Logic Hook / before_save / after_save | `[[sugar-logic-hook]]` |
| Custom Field (varchar/text/int/etc.) | `[[sugar-custom-field]]` |
| Custom Field Type (highlight, color picker) | `[[sugar-custom-field-type]]` |
| Relationship (1:M or M:M between modules) | `[[sugar-relationship]]` |
| Notes 1:M attachment (parent_id/parent_type) | `[[sugar-notes-attachment]]` |
| REST Endpoint | `[[sugar-rest-endpoint]]` |
| Scheduler / cron job | `[[sugar-scheduler]]` |
| UI Customization (view/dashlet/subpanel) | `[[sugar-ui-customization]]` |
| New module (full MB-style) | `[[sugar-new-module]]` |
| Outbound HTTP detail | `[[sugar-external-resource-client]]` |
| pack.php specifics | `[[sugar-package-build]]` |
| MB-export starting point | `[[sugar-mb-export-flow]]` |

## References

- `[[sugar-mlp-anatomy]]` — the 7 installdefs sections + file-pattern routing
- `[[sugar-package-build]]` — full pack.php with all 7 sections
- `prompts/feature_request_format.md` — canonical input schema

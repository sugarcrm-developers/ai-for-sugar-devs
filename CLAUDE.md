# CLAUDE.md — Project Guide for Claude Code

This repository is a **Sugar developer skills library** for generating installable, upgrade-safe SugarCRM Module Loadable Packages (MLPs). It supports anything from a single logic-hook MLP up to a full multi-module package with relationships, sidecar layouts, icons, and language registration.

## Available skills

All skills live under [`skills/`](skills/). Each skill is a `SKILL.md` file with YAML frontmatter (`name`, `description`, `when_to_use`, `not_for`, `related_skills`) plus a body containing examples, rules, gotchas, and cross-references.

Claude Code can:
- Invoke a skill via the Skill tool when frontmatter matches developer intent
- Or read `skills/sugar-<topic>/SKILL.md` directly for the spec

## Quick routing table

| Developer intent | Skill |
|------------------|-------|
| "add a logic hook to Accounts" | `sugar-logic-hook` |
| "add a custom field" (varchar/text/int/bool) | `sugar-custom-field` |
| "build a highlight / color picker / custom field type" | `sugar-custom-field-type` |
| "link two modules together" (1:M or M:M) | `sugar-relationship` |
| "attach Notes to a module" (parent_id/parent_type) | `sugar-notes-attachment` |
| "create a REST endpoint" | `sugar-rest-endpoint` |
| "schedule a cron job" | `sugar-scheduler` |
| "customize a Sidecar view / dashlet / subpanel" | `sugar-ui-customization` |
| "edit an existing viewdef safely" | `sugar-viewdef-editing` |
| "make outbound HTTP from PHP" | `sugar-external-resource-client` |
| "create a brand-new module" | `sugar-new-module` |
| "I have a Module Builder export — turn it into an MLP" | `sugar-mb-export-flow` |
| "add module icons" | `sugar-module-icons` |
| "register module sidebar + dropdown strings" | `sugar-application-language` |
| "group address fields like Quotes" | `sugar-address-grouping` |
| "Studio thing isn't showing — why?" | `sugar-studio-debugging` |
| "explain MLP installdefs sections" | `sugar-mlp-anatomy` |
| "generate the pack.php for a package" | `sugar-package-build` |
| "I have a structured feature request — build the whole MLP" | `sugar-feature-generator` |

## Entry points for AI agents

- **[AGENTS.md](AGENTS.md)** — the binding execution contract (mandatory specifications, prohibited actions, code quality standards). Read this before generating any package.
- **[skills/sugar-feature-generator/SKILL.md](skills/sugar-feature-generator/SKILL.md)** — orchestrator skill that routes a structured feature request to the right topic skill.
- **[prompts/feature_request_format.md](prompts/feature_request_format.md)** — canonical input schema for feature requests.

## Reference material

- **[reference/master_reference.md](reference/master_reference.md)** — complete Sugar Extension Framework specifications.
- **[reference/installdefs_cheatsheet.md](reference/installdefs_cheatsheet.md)** — one-page table of the 7 installdefs sections (added in Tier 3).
- **[reference/module_anatomy.md](reference/module_anatomy.md)** — file-by-file walkthrough of a complete MB-style module (added in Tier 3).
- **[reference/common_gotchas.md](reference/common_gotchas.md)** — symptom → root-cause table (added in Tier 3).
- **[reference/sugar_developer_guide_25.2_md/](reference/sugar_developer_guide_25.2_md/)** — official Sugar Developer Guide (read-only mirror).

## Templates

- **[templates/minimal_mlp/pack.stub.php](templates/minimal_mlp/pack.stub.php)** — reference pack.php (enhanced in Tier 3 with 7-section glob scanner).
- **[templates/full_module/](templates/full_module/)** — parameterized full-module skeleton (added in Tier 3).
- **[templates/relationship/](templates/relationship/)** — 5-file relationship template (added in Tier 3).

## Examples

See [`examples/`](examples/) for copy/paste feature requests and walkthroughs:
- single-feature MLPs (logic hook, custom field, relationship, REST endpoint)
- multi-module packages (added in Tier 4)
- Notes 1:M attachments (added in Tier 4)

## Conventions enforced everywhere

These appear in every skill where relevant — see [AGENTS.md](AGENTS.md) "Recent Mandates (v2)" for the complete list:

1. Custom field names MUST end in `_c`
2. Vardefs use `vname`, never `label`
3. No `'help'` text on vardefs — labels carry it
4. `image_dir` belongs in `installdefs`, never in `$manifest`
5. Singular/plural Escalation pattern (module folder/table plural; bean class/object_name/dictionary key singular)
6. Relationships: lhs = parent (the linked-to), rhs = child (has the link)
7. HTTP requests use `Sugarcrm\Sugarcrm\Security\HttpClient\ExternalResourceClient` only — never curl/file_get_contents/fopen/stream_get_contents
8. `$app_list_strings` for module list and dropdowns belong in application-scope language files
9. After install: new fields on OOB modules need Studio drag-place on the record view

# AGENTS.md — AI Execution Contract for Sugar MLP Generator

## Project Purpose
A production-grade, AI-driven system for generating installable, upgrade-safe SugarCRM Module Loadable Packages (MLPs) from structured feature requests. Designed for deterministic, agent-agnostic execution (Claude, Copilot, ChatGPT, Codex, etc).

## Atomic, Autonomous, and Deterministic Generation (Default)
- All package generation must be atomic, self-contained, and require no user review or stepwise confirmation.
- Agents must generate, verify, and complete the entire package in one operation, with no file-by-file or interactive output.
- All required files, directories, and content must be created and validated by the agent before finishing.
- No user prompts, explanations, or manual steps are allowed.
- Output must be a single, deterministic, raw file entry list as specified below.

## Required Directory Structure
```
/build/<PackageName>/
    src/ (all Sugar files using Extension Framework)
    pack.php
    version
    releases/ (.keep if needed)
```

## Required Sugar File Structure
- All customizations must use the Extension Framework only (e.g. `custom/Extension/...`, `custom/modules/...`)
- **It is forbidden to write directly to `src/modules/` or any non-Extension Framework path. All files must be under `src/custom/Extension/...` or `src/custom/modules/...`.**
- No core file overrides or modifications.
- All files must be upgrade-safe and installable via Module Loader.

## Logic Hook Registration
- **Logic hook registration must be placed under `custom/Extension/modules/<Module>/Ext/LogicHooks/`.**
- **It is forbidden to register hooks directly in any `logic_hooks.php` file (including `custom/modules/<Module>/logic_hooks.php`).**

## Manifest and Packaging
- Each package must generate a `manifest.php` at the root of the zip.
- Manifest must include all required fields and use regex for `acceptable_sugar_versions` and correct flavors.
- `installdefs['copy']` must be fully auto-populated for every file under `src/` (no static/manual entries).
- The `releases/` directory must always exist for output.

## Output Format Contract
- Output must be a single, deterministic, raw file entry list.
- The first line of output must begin with: `File: build/<PackageName>/`
- Each file must be prefixed with: `File: build/<PackageName>/<path>`
- No markdown, explanations, or commentary.
- No stray whitespace or user prompts.

## Prohibited Actions
- Never require user review, approval, or file-by-file confirmation.
- Never output or create files stepwise or interactively.
- Never copy or embed instructional content from templates or reference.
- Never override or modify core files.
- Never output sugar_developer_guide content.
- Never use a global or shared pack.php.

---

This file is the authoritative execution contract for any AI agent generating Sugar MLPs from this project. All outputs must be atomic, deterministic, and self-contained per package by default.

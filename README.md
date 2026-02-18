# AI for Sugar Devs 🤖🍬

**AI for Sugar Devs** is a deterministic, agent-agnostic system for generating **installable, upgrade-safe SugarCRM Module Loadable Packages (MLPs)** using AI.

Designed for **Sugar developers**, it automates module generation while enforcing **Extension Framework compliance**, upgrade safety, and deterministic outputs.

---

## 🚀 Features

* Generate full MLPs: Logic Hooks, Custom Fields, Relationships, REST Endpoints, Scheduler Jobs, UI Customizations
* **Extension Framework only** — no core overrides
* Self-contained per-package builds in `/build/<PackageName>/`
* Production-ready `pack.php` per package
* Deterministic output — raw file entries, upgrade-safe, ready for Module Loader
* Agent-agnostic — works with Claude, Copilot, ChatGPT, Codex, etc.

---

## 🧠 Architecture Overview

Each feature lives in an isolated directory:

```
/build/<PackageName>/
    src/                      # All Sugar files (Extension Framework only)
    pack.php                  # Self-contained build script
    version                   # Version file
    releases/                 # Generated zip output
```

Running:

```bash
cd build/<PackageName>
php pack.php 1.0.0
```

Produces:

```
build/<PackageName>/releases/sugarcrm-<PackageName>-1.0.0.zip
```

Zip is ready for **Module Loader** installation.

---

## 🔐 Execution Contract

All AI agents must follow:

* `AGENTS.md` — authoritative rules
* `/prompts/feature_request_format.md` — deterministic input schema
* `/prompts/feature_generator.md` — generation instructions

**Rules enforced:**

* Extension Framework only (`custom/Extension/...`)
* No core overrides
* No OOTB module modification
* Upgrade-safe only
* No instructional/template noise in outputs
* No embedded Sugar Developer Guide content
* Deterministic file output format

---

## 🛠 How to Use

### 1️⃣ Upload Repo to AI Agent

Upload the full repository to your AI coding agent (Claude, Copilot, ChatGPT, Codex, etc.).

---

### 2️⃣ Submit a Feature Request

Use the **structured format** defined in `/prompts/feature_request_format.md`.

**Example Feature Requests & Updates**

#### Logic Hook → Webhook (Accounts)

```text
Read and follow AGENTS.md strictly.
Read and follow prompts/feature_generator.md strictly.
No explanations. No markdown. Output raw file entries only.

Feature Type: Logic Hook
Module: Accounts
Trigger: after_save
Condition:
  field: account_type = 'Customer' or
  field: account_type = 'Prospect'
Action:
  type: webhook
  method: POST
  url: https://webhooks.com/mywebhook
  payload: full bean
  extract the response if http 200 log the result and return 'myresponse'
Package Name: Custom_AccountsCustomerWebhook
```

#### Custom Field → New Account Field

```text
Feature Type: Custom Field
Module: Accounts
Field Name: customer_priority
Field Type: varchar
Label: Customer Priority
Package Name: OOTB_AccountsCustomField
```

#### REST Endpoint → Custom Contacts API

```text
Feature Type: REST Endpoint
Module: Contacts
Endpoint: /custom/contacts/summary
Method: GET
Action: return summary of contact activity
Package Name: OOTB_ContactsSummaryEndpoint
```

#### Updating an Existing Package

```text
Feature Type: Logic Hook
Module: Accounts
Trigger: after_save
Condition:
  field: account_status
  equals: Active
Action:
  type: webhook
  method: POST
  url: https://webhooks.com/active-customer
  payload: full bean
Package Name: OOTB_AccountsCustomerWebhook
Update: true
```

**Update Workflow:**

1. Increment `/version` in the package directory.
2. Submit feature request with `Update: true`.
3. AI generates only the new/modified files.
4. Run `php pack.php <version>` to produce new zip.
5. Upload via Module Loader — upgrade-safe.

---

### 3️⃣ Build the Package

After AI generates the package:

```bash
cd build/<PackageName>
php pack.php 1.0.0
```

This produces the installable zip in:

```
build/<PackageName>/releases/sugarcrm-<PackageName>-1.0.0.zip
```

---

### 4️⃣ Install in Sugar

Upload the generated zip via **Admin → Module Loader**.

Install — fully upgrade-safe, no manual corrections required.

---

## 🏗 Project Structure

```
/AGENTS.md                        # Execution rules and standards
/prompts/
    feature_request_format.md     # Deterministic AI input schema
    feature_generator.md          # Generation instructions
/templates/minimal_mlp/           # Pack stub for AI reference
/build/                            # Generated packages live here
/output/                           # Optional staging of zips
/reference/                        # Sugar Developer Guide (never included in output)
/README.md
/LICENSE
```

---

## ⚙️ Developer Workflow Summary (1-Page Cheat Sheet)

| Step                   | Command / Action                                                | Notes                                                |
| ---------------------- | --------------------------------------------------------------- | ---------------------------------------------------- |
| Upload repo to AI      | —                                                               | Any agent: Claude, Copilot, ChatGPT, Codex           |
| Submit feature request | See structured format                                           | Include `Update: true` for updates                   |
| Check output           | `/build/<PackageName>/src/`, `pack.php`, `version`              | All files deterministic and Extension Framework only |
| Build zip              | `cd build/<PackageName>`<br>`php pack.php 1.0.0`                | Version file determines zip                          |
| Install                | Module Loader → Upload zip                                      | Upgrade-safe, no manual corrections                  |
| Update package         | Increment version<br>Submit feature request with `Update: true` | Only new files generated; old files remain intact    |

**Supported Feature Types:** Logic Hooks, Custom Fields, Relationships, REST Endpoints, Scheduler Jobs, UI Customizations

---

## 🔐 Why This Matters

AI can generate Sugar code, but without structure it produces **inconsistent and unsafe packages**.

This system ensures:

* **Extension Framework purity**
* **Upgrade safety by contract**
* **Deterministic builds** — reproducible, testable packages
* **Stateless, parallelizable execution**

It transforms AI from a code assistant into a **controlled, production-ready Sugar MLP compiler**.
